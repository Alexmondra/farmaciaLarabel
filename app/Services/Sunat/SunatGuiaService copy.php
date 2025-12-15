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

    private function getSeeSigner(Configuracion $config): See
    {
        $see = new See();

        // Certificado (misma lógica que tu FE)
        $rutaCert = null;
        if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
            $rutaCert = Storage::path($config->sunat_certificado_path);
        } else {
            $rutaCert = Storage::path('sunat/certificado_prueba.pem');
        }

        if (!file_exists($rutaCert)) {
            throw new Exception("No se encuentra el certificado en: {$rutaCert}");
        }

        $see->setCertificate(file_get_contents($rutaCert));

        // Servicio dummy (solo para que See esté completo, no se usa send)
        $see->setService('https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService');

        // Clave dummy (no se usa para firmar, pero evita configs raras)
        $see->setClaveSOL('20000000001', 'MODDATOS', 'MODDATOS');

        return $see;
    }

    /**
     * API GRE (REST) - SOLO para PRODUCCIÓN (sunat_produccion=1)
     */
    private function getApi(Configuracion $config): Api
    {
        // Validaciones obligatorias en modo producción
        if (empty($config->empresa_ruc)) {
            throw new Exception("Falta empresa_ruc en configuración.");
        }
        if (empty($config->sunat_sol_user) || empty($config->sunat_sol_pass)) {
            throw new Exception("Faltan credenciales SOL (sunat_sol_user / sunat_sol_pass).");
        }
        if (empty($config->sunat_client_id) || empty($config->sunat_client_secret)) {
            throw new Exception("Faltan credenciales API GRE (sunat_client_id / sunat_client_secret).");
        }

        $api = new Api([
            'auth' => 'https://api-seguridad.sunat.gob.pe/v1',
            'cpe'  => 'https://api-cpe.sunat.gob.pe/v1',
        ]);

        // Certificado
        $rutaCert = null;
        if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
            $rutaCert = Storage::path($config->sunat_certificado_path);
        } else {
            $rutaCert = Storage::path('sunat/certificado_prueba.pem');
        }

        if (!file_exists($rutaCert)) {
            throw new Exception("No se encuentra el certificado en: {$rutaCert}");
        }

        $api->setCertificate(file_get_contents($rutaCert));

        // SOL (usuario y clave de tu RUC real)
        $api->setClaveSOL($config->empresa_ruc, $config->sunat_sol_user, $config->sunat_sol_pass);

        // API credentials (GRE)
        $api->setApiCredentials($config->sunat_client_id, $config->sunat_client_secret);

        return $api;
    }


    public function transmitirGuia(GuiaRemision $guia): GuiaRemision
    {
        try {
            $config = Configuracion::firstOrFail();

            // 1) Generar objeto GRE
            $despatch = $this->generarDespatch($guia, $config);

            // 2) NOMBRE
            $nombreArchivo = $this->getNombreArchivo($despatch);

            // =========================
            // MODO PRUEBA_LOCAL (0)
            // =========================
            if ((int)$config->sunat_produccion === 0) {
                $see = $this->getSeeSigner($config);

                // Firmar XML (solo local)
                $xml = $see->getXmlSigned($despatch);

                // Guardar XML
                $rutaXml = 'sunat/xml/guias/' . $nombreArchivo . '.xml';
                Storage::put($rutaXml, $xml);

                $guia->ruta_xml = $rutaXml;
                $guia->hash = $this->getHashFromXml($xml);

                $guia->sunat_exito = true;
                $guia->codigo_error_sunat = 0;
                $guia->mensaje_sunat = 'PRUEBA_LOCAL: XML generado (no enviado a SUNAT).';
                $guia->estado_traslado = $guia->estado_traslado ?: 'REGISTRADO';
                $guia->save();

                return $guia;
            }

            // =========================
            // MODO PRODUCCIÓN (1)
            // =========================
            $api = $this->getApi($config);

            // Enviar a SUNAT (devuelve ticket)
            $sendRes = $api->send($despatch);

            // Guardar XML REAL enviado (si Greenter lo expone)
            $lastXml = method_exists($api, 'getLastXml') ? $api->getLastXml() : null;
            if (!empty($lastXml)) {
                $rutaXml = 'sunat/xml/guias/' . $nombreArchivo . '.xml';
                Storage::put($rutaXml, $lastXml);
                $guia->ruta_xml = $rutaXml;
                $guia->hash = $this->getHashFromXml($lastXml);
            }

            if (!$sendRes->isSuccess()) {
                $err = $sendRes->getError();
                $guia->sunat_exito = false;
                $guia->codigo_error_sunat = $err ? (int)$err->getCode() : 9999;
                $guia->mensaje_sunat = $err ? $err->getMessage() : 'Error desconocido al enviar GRE.';
                $guia->save();
                return $guia;
            }

            $ticket = method_exists($sendRes, 'getTicket') ? $sendRes->getTicket() : null;
            $guia->ticket_sunat = $ticket;
            $guia->estado_traslado = 'ENVIADO';
            $guia->sunat_exito = true;
            $guia->codigo_error_sunat = 0;
            $guia->mensaje_sunat = "Enviado a SUNAT. Ticket: {$ticket}";
            $guia->save();

            // Consultar estado del ticket para traer CDR (reintentos cortos)
            $statusRes = null;
            for ($i = 1; $i <= 6; $i++) {
                $statusRes = $api->getStatus($ticket);
                if ($statusRes && $statusRes->isSuccess()) {
                    break;
                }
                usleep(500000); // 0.5s
            }

            // Si no hay CDR aún, lo dejamos “EN_PROCESO”
            if (!$statusRes || !$statusRes->isSuccess()) {
                $err = $statusRes ? $statusRes->getError() : null;
                $guia->estado_traslado = 'EN_PROCESO';
                $guia->mensaje_sunat = $err ? ($err->getCode() . ' - ' . $err->getMessage()) : 'SUNAT aún no devuelve CDR (en proceso).';
                $guia->save();
                return $guia;
            }

            // Guardar CDR ZIP
            $cdrZip = method_exists($statusRes, 'getCdrZip') ? $statusRes->getCdrZip() : null;
            if (!empty($cdrZip)) {
                $rutaCdr = 'sunat/cdr/guias/R-' . $nombreArchivo . '.zip';
                Storage::put($rutaCdr, $cdrZip);
                $guia->ruta_cdr = $rutaCdr;
            }

            // Interpretar respuesta CDR
            $cdr = method_exists($statusRes, 'getCdrResponse') ? $statusRes->getCdrResponse() : null;
            if ($cdr) {
                $guia->mensaje_sunat = $cdr->getDescription() ?? 'Respuesta CDR recibida.';
                $guia->estado_traslado = ($cdr->getCode() === '0') ? 'ACEPTADO' : 'OBSERVADO';
            } else {
                $guia->mensaje_sunat = 'CDR recibido.';
                $guia->estado_traslado = 'ACEPTADO';
            }

            $guia->sunat_exito = true;
            $guia->codigo_error_sunat = 0;
            $guia->save();

            return $guia;
        } catch (Throwable $e) {
            Log::error("Error SUNAT Guia {$guia->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $guia->mensaje_sunat = "Error Interno: " . $e->getMessage();
            $guia->sunat_exito = false;
            $guia->save();

            return $guia;
        }
    }

    /**
     * Construcción GRE a partir de tu GuiaRemision + Detalles
     * (usando tus campos reales del modelo)
     */
    private function generarDespatch(GuiaRemision $guia, Configuracion $config): Despatch
    {
        $sucursal = $guia->sucursal;

        if (!$guia->cliente) {
            throw new Exception("La Guía no tiene Cliente asignado.");
        }
        if (!$sucursal) {
            throw new Exception("La Guía no tiene Sucursal asignada.");
        }

        // EMISOR
        $address = new Address();
        $address->setUbigueo($sucursal->ubigeo ?? '150101')
            ->setDepartamento($sucursal->departamento ?? '')
            ->setProvincia($sucursal->provincia ?? '')
            ->setDistrito($sucursal->distrito ?? '')
            ->setDireccion($sucursal->direccion ?? $config->empresa_direccion);

        $company = new Company();
        $company->setRuc($config->empresa_ruc)
            ->setRazonSocial($config->empresa_razon_social)
            ->setNombreComercial($sucursal->nombre ?? $config->empresa_razon_social)
            ->setAddress($address);

        // DESTINATARIO
        $destinatario = new Client();
        $docTipo = (strlen((string)$guia->cliente->documento) == 11) ? '6' : '1';
        $destinatario->setTipoDoc($docTipo)
            ->setNumDoc($guia->cliente->documento)
            ->setRznSocial($guia->cliente->nombre_completo ?? $guia->cliente->nombre);

        // ENVÍO / SHIPMENT
        $shipment = new Shipment();
        $shipment
            ->setCodTraslado($guia->motivo_traslado) // 01 venta, etc
            ->setDesTraslado($guia->descripcion_motivo)
            ->setModTraslado($guia->modalidad_traslado) // 01 público, 02 privado
            ->setFecTraslado(new \DateTime($guia->fecha_traslado->format('Y-m-d')))
            ->setPesoTotal((float)$guia->peso_bruto)
            ->setUndPesoTotal('KGM')
            ->setNumBultos($guia->numero_bultos ?? 1);

        // Partida / Llegada (tus campos)
        $ubigeoPartida = $guia->ubigeo_partida ?? ($sucursal->ubigeo ?? '150101');
        $direccionPartida = $guia->direccion_partida ?? ($sucursal->direccion ?? $config->empresa_direccion);

        $ubigeoLlegada = $guia->ubigeo_llegada;
        $direccionLlegada = $guia->direccion_llegada;

        if (empty($ubigeoLlegada) || empty($direccionLlegada)) {
            throw new Exception("Falta ubigeo_llegada o direccion_llegada en la guía.");
        }

        $shipment->setPartida(new Direction($ubigeoPartida, $direccionPartida));
        $shipment->setLlegada(new Direction($ubigeoLlegada, $direccionLlegada));

        // Transporte: Público / Privado (tu lógica actual)
        if ($guia->modalidad_traslado == '01') {
            // Público
            if (empty($guia->doc_transportista_numero) || empty($guia->razon_social_transportista)) {
                throw new Exception("Transporte público: falta doc_transportista_numero o razon_social_transportista.");
            }
            $transportista = new Transportist();
            $transportista->setTipoDoc($guia->doc_transportista_tipo ?? '6')
                ->setNumDoc($guia->doc_transportista_numero)
                ->setRznSocial($guia->razon_social_transportista);

            $shipment->setTransportista($transportista);
        } else {
            // Privado
            if (empty($guia->doc_chofer_numero)) {
                throw new Exception("Transporte privado: falta doc_chofer_numero.");
            }

            $driver = new Driver();
            $driver->setTipoDoc($guia->doc_chofer_tipo ?? '1')
                ->setNroDoc($guia->doc_chofer_numero);

            if (!empty($guia->licencia_conducir)) {
                $driver->setLicencia($guia->licencia_conducir);
            }

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
