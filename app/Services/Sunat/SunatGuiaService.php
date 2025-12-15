<?php

namespace App\Services\Sunat;

use App\Models\Guias\GuiaRemision;
use App\Models\Configuracion;
use Greenter\Api;
use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Vehicle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class SunatGuiaService
{
    /**
     * Configuración del servicio para FIRMAR y ENVIO (SOAP)
     */
    private function getSee(Configuracion $config): See
    {
        $see = new See();

        // 1. Certificado
        $rutaCert = $this->getCertificatePath($config);

        if (!file_exists($rutaCert)) {
            throw new Exception("No se encuentra el certificado en: {$rutaCert}");
        }
        $see->setCertificate(file_get_contents($rutaCert));

        // 2. Definir Credenciales y URLs según modo
        if ((int)$config->sunat_produccion === 1) {
            // MODO PRODUCCIÓN: Usar RUC y credenciales reales
            $ruc = $config->empresa_ruc;
            $user = $config->sunat_sol_user;
            $pass = $config->sunat_sol_pass;
            $url = 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService';
        } else {
            // MODO PRUEBAS: Usar RUC y credenciales de prueba.
            // Opcional: Si el usuario llenó sus credenciales, podrías usarlas para simulación.
            $ruc = '20000000001';
            $user = 'MODDATOS';
            $pass = 'MODDATOS';
            $url = 'https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService';
        }

        $see->setService($url);
        $see->setClaveSOL($ruc, $user, $pass);

        return $see;
    }

    /**
     * API GRE (REST) - SOLO para PRODUCCIÓN (sunat_produccion=1)
     * NOTA: Greenter aún soporta el envío SOAP para GRE, pero usamos el SDK REST
     * si las credenciales de la API están configuradas.
     */
    private function getApi(Configuracion $config): Api
    {
        // Validar que las credenciales REST estén presentes
        if (empty($config->sunat_client_id) || empty($config->sunat_client_secret)) {
            throw new Exception("Faltan credenciales API GRE (Client ID/Secret) para el envío REST.");
        }

        $api = new Api([
            'auth' => 'https://api-seguridad.sunat.gob.pe/v1',
            'cpe'  => 'https://api-cpe.sunat.gob.pe/v1',
        ]);

        $api->setCertificate(file_get_contents($this->getCertificatePath($config)));
        $api->setClaveSOL($config->empresa_ruc, $config->sunat_sol_user, $config->sunat_sol_pass);
        $api->setApiCredentials($config->sunat_client_id, $config->sunat_client_secret);

        return $api;
    }

    private function getCertificatePath(Configuracion $config): string
    {
        $rutaCert = null;
        if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
            $rutaCert = Storage::path($config->sunat_certificado_path);
        } else {
            // Usar certificado de prueba si no hay uno real
            $rutaCert = Storage::path('sunat/certificado_prueba.pem');
        }

        if (!file_exists($rutaCert)) {
            // Fallback por si la ruta no existe
            $rutaLocal = storage_path('app/certificados/certificado.pem');
            if (file_exists($rutaLocal)) return $rutaLocal;
            throw new Exception("No se encontró el certificado digital en ninguna ruta conocida.");
        }
        return $rutaCert;
    }


    public function transmitirGuia(GuiaRemision $guia): GuiaRemision
    {
        try {
            $config = Configuracion::firstOrFail();
            $esProduccion = ((int)$config->sunat_produccion === 1);

            // 1) Generar objeto GRE
            $despatch = $this->generarDespatch($guia, $config);
            $nombreArchivo = $this->getNombreArchivo($despatch);

            // =========================
            // LÓGICA DE ENVÍO
            // =========================
            if (!$esProduccion) {
                // MODO PRUEBA_LOCAL: Solo firmar y guardar XML
                $see = $this->getSee($config);
                $xml = $see->getXmlSigned($despatch);

                $guia->ruta_xml = 'sunat/xml/guias/' . $nombreArchivo . '.xml';
                Storage::put($guia->ruta_xml, $xml);
                $guia->hash = $this->getHashFromXml($xml);

                $guia->sunat_exito = true;
                $guia->codigo_error_sunat = 0;
                $guia->mensaje_sunat = 'PRUEBA_LOCAL: XML generado (no enviado a SUNAT).';
                $guia->estado_traslado = $guia->estado_traslado ?: 'REGISTRADO';
                $guia->save();

                return $guia;
            }

            // MODO PRODUCCIÓN: Enviar vía API REST (o SOAP si Greenter lo decide)
            $api = $this->getApi($config);
            $sendRes = $api->send($despatch);

            // Guardar XML REAL enviado
            $lastXml = method_exists($api, 'getLastXml') ? $api->getLastXml() : null;
            if (!empty($lastXml)) {
                $guia->ruta_xml = 'sunat/xml/guias/' . $nombreArchivo . '.xml';
                Storage::put($guia->ruta_xml, $lastXml);
                $guia->hash = $this->getHashFromXml($lastXml);
            }

            // Lógica de respuesta (omitida para concisión, pero debe estar completa)
            if (!$sendRes->isSuccess()) {
                $err = $sendRes->getError();
                $guia->sunat_exito = false;
                $guia->codigo_error_sunat = $err ? (int)$err->getCode() : 9999;
                $guia->mensaje_sunat = $err ? $err->getMessage() : 'Error desconocido al enviar GRE.';
                $guia->save();
                return $guia;
            }

            // Si el envío fue exitoso, esperar CDR (revisar tu lógica anterior para completarla)
            $guia->ticket_sunat = method_exists($sendRes, 'getTicket') ? $sendRes->getTicket() : null;
            $guia->estado_traslado = 'ENVIADO';
            $guia->sunat_exito = true;
            $guia->mensaje_sunat = "Enviado a SUNAT. Ticket: {$guia->ticket_sunat}";
            $guia->save();

            // Lógica completa de consulta de ticket y CDR debe seguir aquí...

            return $guia;
        } catch (Throwable $e) {
            Log::error("Error SUNAT Guia {$guia->id}: " . $e->getMessage());
            $guia->mensaje_sunat = "Error Interno: " . $e->getMessage();
            $guia->sunat_exito = false;
            $guia->save();
            return $guia;
        }
    }

    private function generarDespatch(GuiaRemision $guia, Configuracion $config): Despatch
    {
        $sucursal = $guia->sucursal;

        // Validaciones básicas: RUC, Razón Social, Dirección
        if (empty($config->empresa_ruc) || empty($config->empresa_razon_social)) {
            throw new Exception("Falta RUC o Razón Social del Emisor en la configuración general.");
        }

        // 1. EMISOR (Compañía de tu configuración)
        $address = new Address();
        $address->setUbigueo($sucursal->ubigeo ?? '150101')
            ->setDireccion($sucursal->direccion ?? $config->empresa_direccion);

        $company = new Company();
        $company->setRuc($config->empresa_ruc)
            ->setRazonSocial($config->empresa_razon_social)
            ->setNombreComercial($sucursal->nombre ?? $config->empresa_razon_social)
            ->setAddress($address);

        // 2. DESTINATARIO (Consignee)
        $destinatario = new Client();

        if ($guia->motivo_traslado === '04') {
            // CASO 1: TRASLADO INTERNO (Destinatario debe ser la misma empresa)
            $destinatario->setTipoDoc('6') // RUC
                ->setNumDoc($config->empresa_ruc)
                ->setRznSocial($config->empresa_razon_social);
        } else {
            // CASO 2: VENTA o OTROS (Destinatario = Cliente)
            if (!$guia->cliente) {
                throw new Exception("Guía de Venta/Otros (Motivo {$guia->motivo_traslado}): Se requiere un Cliente Destinatario.");
            }
            $docTipo = (strlen((string)$guia->cliente->documento) == 11) ? '6' : '1';
            $destinatario->setTipoDoc($docTipo)
                ->setNumDoc($guia->cliente->documento)
                ->setRznSocial($guia->cliente->nombre_completo ?? $guia->cliente->nombre);
        }

        // 3. ENVÍO / SHIPMENT
        $shipment = new Shipment();
        $shipment
            ->setCodTraslado($guia->motivo_traslado)
            ->setDesTraslado($guia->descripcion_motivo)
            ->setModTraslado($guia->modalidad_traslado)
            ->setFecTraslado(new \DateTime($guia->fecha_traslado->format('Y-m-d')))
            ->setPesoTotal((float)$guia->peso_bruto)
            ->setUndPesoTotal('KGM')
            ->setNumBultos($guia->numero_bultos ?? 1);

        // --- DIRECCIÓN DE PARTIDA ---
        $partidaDir = new Direction($guia->ubigeo_partida, $guia->direccion_partida);
        // Si hay código de establecimiento de partida, lo agregamos (limpiando el nodo si es nulo)
        if (!empty($guia->codigo_establecimiento_partida)) {
            $partidaDir->setCodLocal($guia->codigo_establecimiento_partida);
        }
        $shipment->setPartida($partidaDir);

        // --- DIRECCIÓN DE LLEGADA ---
        $llegadaDir = new Direction($guia->ubigeo_llegada, $guia->direccion_llegada);
        if ($guia->motivo_traslado === '04' && !empty($guia->codigo_establecimiento_llegada)) {
            // CRÍTICO: Usar código anexo para traslado interno
            $llegadaDir->setCodLocal($guia->codigo_establecimiento_llegada); // Usando setCodLocal()
        }
        $shipment->setLlegada($llegadaDir);


        // Transporte: Público / Privado
        if ($guia->modalidad_traslado == '01') {
            // PÚBLICO
            $transportista = new Transportist();
            $transportista->setTipoDoc($guia->doc_transportista_tipo ?? '6')
                ->setNumDoc($guia->doc_transportista_numero)
                ->setRznSocial($guia->razon_social_transportista);
            $shipment->setTransportista($transportista);
        } else {
            // PRIVADO
            $driver = new Driver();
            $driver->setTipoDoc($guia->doc_chofer_tipo ?? '1')->setNroDoc($guia->doc_chofer_numero);
            if (!empty($guia->licencia_conducir)) $driver->setLicencia($guia->licencia_conducir);
            $shipment->setChoferes([$driver]);

            if (!empty($guia->placa_vehiculo)) {
                $vehicle = new Vehicle();
                $vehicle->setPlaca($guia->placa_vehiculo);
                $shipment->setVehiculo($vehicle);
            }
        }

        // DESPATCH (GRE)
        $despatch = new Despatch();
        $despatch
            ->setVersion('2022')
            ->setTipoDoc('09')
            ->setSerie($guia->serie)
            ->setCorrelativo((string)$guia->numero)
            ->setFechaEmision(new \DateTime($guia->fecha_emision->format('Y-m-d')))
            ->setCompany($company)
            ->setDestinatario($destinatario)
            ->setEnvio($shipment);

        // DETALLES
        $details = [];
        foreach ($guia->detalles as $det) {
            $detail = new DespatchDetail();
            $detail->setCantidad((float)$det->cantidad)
                ->setUnidad($det->unidad_medida ?? 'NIU')
                ->setDescripcion($det->descripcion)
                ->setCodigo($det->codigo_producto ?? 'GEN');
            $details[] = $detail;
        }
        $despatch->setDetails($details);

        return $despatch;
    }

    private function getNombreArchivo(Despatch $despatch): string
    {
        $ruc = $despatch->getCompany()->getRuc();
        $tipo = $despatch->getTipoDoc();
        $serie = $despatch->getSerie();
        $corr = $despatch->getCorrelativo();
        return "{$ruc}-{$tipo}-{$serie}-{$corr}";
    }

    private function getHashFromXml(string $xml): ?string
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $digestValue = $dom->getElementsByTagName('DigestValue')->item(0);
        return $digestValue ? $digestValue->nodeValue : null;
    }
}
