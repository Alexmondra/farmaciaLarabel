<?php

namespace App\Services\Sunat;

use App\Models\Guias\GuiaRemision;
use App\Models\Configuracion;
use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
// IMPORTS CRÍTICOS PARA GUÍAS
use Greenter\Model\Despatch\DespatchAdvice;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Vehicle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class SunatGuiaService
{
    /**
     * Configuración del servicio (Igual que en SunatService)
     */
    public function getSee()
    {
        $config = Configuracion::firstOrFail();
        $see = new See();

        // 1. Lógica de Certificado idéntica a Ventas (Storage)
        if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
            $rutaCert = Storage::path($config->sunat_certificado_path);
        } else {
            // Fallback por defecto
            $rutaCert = Storage::path('sunat/certificado_prueba.pem');
        }

        if (!file_exists($rutaCert)) {
            // Intento final de búsqueda local si falla Storage
            $rutaLocal = storage_path('app/certificados/certificado.pem');
            if (file_exists($rutaLocal)) {
                $rutaCert = $rutaLocal;
            } else {
                throw new Exception("No se encuentra el certificado en: " . $rutaCert);
            }
        }

        $see->setCertificate(file_get_contents($rutaCert));

        // 2. Definir si es Producción o Pruebas
        if ($config->sunat_produccion) {
            $see->setService('https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService');
            $see->setClaveSOL($config->empresa_ruc, $config->sunat_sol_user, $config->sunat_sol_pass);
        } else {
            $see->setService('https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService');
            $see->setClaveSOL('20000000001', 'MODDATOS', 'MODDATOS');
        }

        return $see;
    }

    /**
     * Transmisión (Estructura idéntica a transmitirAComprobante)
     */
    public function transmitirGuia(GuiaRemision $guia)
    {
        try {
            $see = $this->getSee();

            // 1. Generar el objeto Despatch (XML Structure)
            $despatch = $this->generarDespatch($guia);

            // 2. Firmar XML
            $xml = $see->getXmlSigned($despatch);
            $nombreArchivo = $despatch->getName();

            // --- GUARDAR EN CARPETA 'GUIAS' (Igual que 'facturas') ---
            $rutaXml = 'sunat/xml/guias/' . $nombreArchivo . '.xml';
            Storage::put($rutaXml, $xml);

            $guia->ruta_xml = $rutaXml;
            $guia->hash = $this->getHashFromXml($xml);
            $guia->save();

            // 3. Enviar a SUNAT
            $result = $see->send($despatch);

            if ($result->isSuccess()) {
                // --- GUARDAR CDR EN CARPETA 'GUIAS' ---
                $rutaCdr = 'sunat/cdr/guias/R-' . $nombreArchivo . '.zip';
                Storage::put($rutaCdr, $result->getCdrZip());

                $guia->ruta_cdr = $rutaCdr;
                $guia->codigo_error_sunat = 0;
                $guia->mensaje_sunat = $result->getCdrResponse()->getDescription();
                $guia->estado_traslado = 'ACEPTADO'; // O el estado que uses en tu BD
                $guia->sunat_exito = true;
            } else {
                // Error de SUNAT (rechazo lógico)
                $error = $result->getError();
                $guia->codigo_error_sunat = $error->getCode();
                $guia->mensaje_sunat = $error->getMessage();
                $guia->sunat_exito = false;
            }

            $guia->save();
            return $guia;
        } catch (Throwable $e) {
            // Captura errores fatales (Class not found) y Excepciones
            Log::error("Error SUNAT Guia {$guia->id}: " . $e->getMessage());
            $guia->mensaje_sunat = "Error Interno: " . $e->getMessage();
            $guia->sunat_exito = false;
            $guia->save();
            return $guia;
        }
    }

    /**
     * Construcción del XML (DespatchAdvice)
     */
    private function generarDespatch(GuiaRemision $guia)
    {
        $config = Configuracion::first();
        $sucursal = $guia->sucursal;

        // Validación preventiva
        if (!$guia->cliente) {
            throw new Exception("La Guía no tiene Cliente asignado.");
        }

        // EMISOR (Tu empresa)
        $address = new Address();
        $address->setUbigueo($sucursal->ubigeo ?? '150101')
            ->setDepartamento($sucursal->departamento)
            ->setProvincia($sucursal->provincia)
            ->setDistrito($sucursal->distrito)
            ->setDireccion($sucursal->direccion);

        if (method_exists($address, 'setCodigo')) {
            $address->setCodigo($sucursal->codigo ?? '0000');
        }

        $company = new Company();
        $company->setRuc($config->sunat_produccion ? $config->empresa_ruc : '20000000001')
            ->setRazonSocial($config->empresa_razon_social)
            ->setNombreComercial($sucursal->nombre)
            ->setAddress($address);

        // DESTINATARIO (Cliente)
        $destinatario = new Client();
        $docTipo = strlen($guia->cliente->documento) == 11 ? '6' : '1';
        $destinatario->setTipoDoc($docTipo)
            ->setNumDoc($guia->cliente->documento)
            ->setRznSocial($guia->cliente->nombre_completo ?? $guia->cliente->nombre);

        // DATOS DEL TRASLADO
        $shipment = new Shipment();
        $shipment
            ->setCodTraslado($guia->motivo_traslado) // ej: 01 (Venta)
            ->setDesTraslado($guia->descripcion_motivo)
            ->setModTraslado($guia->modalidad_traslado) // 01: Público, 02: Privado
            ->setFecTraslado(\DateTime::createFromFormat('Y-m-d', $guia->fecha_traslado->format('Y-m-d')))
            ->setPesoTotal($guia->peso_bruto)
            ->setUndPesoTotal('KGM')
            ->setNumBultos($guia->numero_bultos ?? 1);

        // Puntos de Partida y Llegada
        // Validamos que existan datos, si no, usamos los de la sucursal/cliente
        $ubigeoPartida = $guia->ubigeo_partida ?? $sucursal->ubigeo;
        $direccionPartida = $guia->direccion_partida ?? $sucursal->direccion;

        $ubigeoLlegada = $guia->ubigeo_llegada; // Obligatorio en BD
        $direccionLlegada = $guia->direccion_llegada; // Obligatorio en BD

        $shipment->setPartida(new Direction($ubigeoPartida, $direccionPartida));
        $shipment->setLlegada(new Direction($ubigeoLlegada, $direccionLlegada));

        // TRANSPORTE
        if ($guia->modalidad_traslado == '01') {
            // Transporte PÚBLICO
            $transportista = new Transportist();
            $transportista->setTipoDoc('6')
                ->setNumDoc($guia->doc_transportista_numero)
                ->setRznSocial($guia->razon_social_transportista);
            $shipment->setTransportista($transportista);
        } else {
            // Transporte PRIVADO
            $driver = new Driver();
            $driver->setTipoDoc($guia->doc_chofer_tipo ?? '1')
                ->setNroDoc($guia->doc_chofer_numero);

            if ($guia->licencia_conducir) {
                $driver->setLicencia($guia->licencia_conducir);
            }

            $shipment->setChoferes([$driver]);

            if ($guia->placa_vehiculo) {
                $vehicle = new Vehicle();
                $vehicle->setPlaca($guia->placa_vehiculo);
                $shipment->setVehiculo($vehicle);
            }
        }

        // OBJETO PRINCIPAL (DespatchAdvice)
        // Este es el objeto que daba error de "Class not found"
        // Asegúrate que 'use Greenter\Model\Despatch\DespatchAdvice;' esté arriba
        $despatch = new DespatchAdvice();
        $despatch->setUblVersion('2.1')
            ->setTipoDoc('09') // Guía de Remisión
            ->setSerie($guia->serie)
            ->setCorrelativo($guia->numero)
            ->setFechaEmision(\DateTime::createFromFormat('Y-m-d', $guia->fecha_emision->format('Y-m-d')))
            ->setCompany($company)
            ->setDestinatario($destinatario)
            ->setEnvio($shipment);

        // DETALLES (Items)
        $details = [];
        foreach ($guia->detalles as $det) {
            $detail = new DespatchDetail();
            $detail->setCantidad($det->cantidad)
                ->setUnidad('NIU') // Unidad de medida (Unidad)
                ->setDescripcion($det->descripcion)
                ->setCodigo($det->codigo_producto ?? 'GEN'); // Código interno
            $details[] = $detail;
        }
        $despatch->setDetails($details);

        return $despatch;
    }

    private function getHashFromXml($xml)
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $digestValue = $dom->getElementsByTagName('DigestValue')->item(0);
        return $digestValue ? $digestValue->nodeValue : null;
    }
}
