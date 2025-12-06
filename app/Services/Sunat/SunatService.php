<?php

namespace App\Services\Sunat;

use Greenter\See;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Client\Client;
use App\Models\Configuracion;
use App\Models\Ventas\Venta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SunatService
{
    public function getSee()
    {
        $config = Configuracion::firstOrFail();
        $see = new See();

        // 1. INTENTAMOS OBTENER LA RUTA REAL USANDO "STORAGE"
        // Storage::path() encuentra el archivo automáticamente, esté en 'private' o donde sea.
        if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
            $rutaCert = Storage::path($config->sunat_certificado_path);
        } else {
            $rutaCert = Storage::path('sunat/certificado_prueba.pem');
        }

        // Validación final de seguridad
        if (!file_exists($rutaCert)) {
            // Este error te ayudará a saber qué pasa si vuelve a fallar
            throw new Exception("No se encuentra el certificado en: " . $rutaCert);
        }

        $see->setCertificate(file_get_contents($rutaCert));

        // 2. Definir si es Producción o Pruebas
        if ($config->sunat_produccion) {
            $see->setService('https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService');
            $see->setClaveSOL($config->empresa_ruc, $config->sunat_sol_user, $config->sunat_sol_pass);
        } else {
            $see->setService('https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService');
            $see->setClaveSOL('20000000001', 'MODDATOS', 'MODDATOS');
        }

        return $see;
    }

    public function transmitirAComprobante(Venta $venta)
    {
        try {
            $see = $this->getSee();
            $invoice = $this->generarComprobante($venta);

            // 1. Firmar XML
            $xml = $see->getXmlSigned($invoice);
            $nombreArchivo = $invoice->getName();

            // 2. Guardar XML
            Storage::put('sunat/xml/' . $nombreArchivo . '.xml', $xml);

            $venta->ruta_xml = 'sunat/xml/' . $nombreArchivo . '.xml';
            $venta->hash = $this->getHashFromXml($xml);
            $venta->save();

            // 3. Enviar a SUNAT
            $result = $see->sendXml(get_class($invoice), $invoice->getName(), $xml);

            if ($result->isSuccess()) {
                $cdr = $result->getCdrResponse();
                Storage::put('sunat/cdr/R-' . $nombreArchivo . '.zip', $result->getCdrZip());

                $venta->ruta_cdr = 'sunat/cdr/R-' . $nombreArchivo . '.zip';
                $venta->codigo_error_sunat = 0;
                $venta->mensaje_sunat = $cdr->getDescription();
                $venta->estado = 'ACEPTADA';
            } else {
                // Error de validación de SUNAT (Como el 3244)
                $error = $result->getError();
                $venta->codigo_error_sunat = $error->getCode();
                $venta->mensaje_sunat = $error->getMessage();
            }

            $venta->save();
            return $result->isSuccess();
        } catch (\Exception $e) {
            // Error de Conexión (Como el de tu boleta anterior)
            Log::error("Error SUNAT Venta {$venta->id}: " . $e->getMessage());
            $venta->mensaje_sunat = "Error Conexión: " . $e->getMessage();
            $venta->save();
            return false;
        }
    }

    public function generarComprobante(Venta $venta)
    {
        $config = Configuracion::first();
        $sucursal = $venta->sucursal;
        $tipoDoc = $venta->tipo_comprobante == 'FACTURA' ? '01' : '03';

        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipoDoc)
            ->setSerie($venta->serie)
            ->setCorrelativo($venta->numero)
            ->setFechaEmision(new \DateTime($venta->fecha_emision))
            ->setFormaPago(new FormaPagoContado()) // <--- 2. ESTO ARREGLA EL ERROR 3244
            ->setTipoMoneda('PEN');

        // Emisor
        $address = new Address();
        $address->setUbigueo($sucursal->ubigeo ?? '150101')
            ->setDepartamento($sucursal->departamento ?? 'LIMA')
            ->setProvincia($sucursal->provincia ?? 'LIMA')
            ->setDistrito($sucursal->distrito ?? 'LIMA')
            ->setDireccion($sucursal->direccion);

        if (method_exists($address, 'setCodigo')) {
            $address->setCodigo($sucursal->codigo ?? '0000');
        }

        $emisor = new Company();
        $emisor->setRuc($config->sunat_produccion ? $config->empresa_ruc : '20000000001')
            ->setRazonSocial($config->empresa_razon_social)
            ->setNombreComercial($sucursal->nombre)
            ->setAddress($address);

        $invoice->setCompany($emisor);

        // Cliente
        $client = new Client();
        $client->setTipoDoc(strlen($venta->cliente->documento) == 11 ? '6' : '1')
            ->setNumDoc($venta->cliente->documento)
            ->setRznSocial($venta->cliente->nombre_completo);
        $invoice->setClient($client);

        // Totales
        $invoice->setMtoOperGravadas($venta->op_gravada)
            ->setMtoOperExoneradas($venta->op_exonerada)
            ->setMtoIGV($venta->total_igv)
            ->setTotalImpuestos($venta->total_igv)
            ->setValorVenta($venta->op_gravada + $venta->op_exonerada)
            ->setSubTotal($venta->total_neto)
            ->setMtoImpVenta($venta->total_neto);

        // Detalles
        $items = [];
        foreach ($venta->detalles as $det) {
            $item = new SaleDetail();

            $base = $det->valor_unitario * $det->cantidad;
            $igv = ($det->igv > 0) ? ($base * 0.18) : 0;

            $item->setCodProducto('MED-' . $det->medicamento_id)
                ->setUnidad('NIU')
                ->setCantidad($det->cantidad)
                ->setDescripcion($det->medicamento->nombre)
                ->setMtoBaseIgv($base)
                ->setPorcentajeIgv($det->igv > 0 ? 18.00 : 0.00)
                ->setIgv($igv)
                ->setTipAfeIgv($det->igv > 0 ? '10' : '20')
                ->setTotalImpuestos($igv)
                ->setMtoValorVenta($base)
                ->setMtoValorUnitario($det->valor_unitario)
                ->setMtoPrecioUnitario($det->precio_unitario);

            $items[] = $item;
        }
        $invoice->setDetails($items);

        // Leyenda
        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue('SON: ' . $this->numeroALetras($venta->total_neto) . ' SOLES');
        $invoice->setLegends([$legend]);

        return $invoice;
    }

    // ... (Mantén las funciones getHashFromXml, numeroALetras y enteroALetras igual que antes) ...
    // Solo estoy pegando las partes cambiadas para ahorrar espacio, pero asegúrate 
    // de tener las funciones auxiliares abajo.
    private function getHashFromXml($xml)
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $digestValue = $dom->getElementsByTagName('DigestValue')->item(0);
        return $digestValue ? $digestValue->nodeValue : null;
    }

    private function numeroALetras($monto)
    {
        $monto = str_replace(',', '', $monto);
        $entero = floor($monto);
        $decimal = round(($monto - $entero) * 100);
        return strtoupper($this->enteroALetras($entero)) . " CON $decimal/100";
    }

    private function enteroALetras($n)
    {
        // ... (Tu función de siempre) ...
        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas  = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $n = (int)$n;
        if ($n == 0) return 'CERO';
        if ($n < 10) return $unidades[$n];
        if ($n < 20) {
            $esp = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
            return $esp[$n - 10];
        }
        if ($n < 100) {
            $d = floor($n / 10);
            $u = $n % 10;
            return ($d == 2 && $u > 0 ? 'VEINTI' . $unidades[$u] : $decenas[$d] . ($u > 0 ? ' Y ' . $unidades[$u] : ''));
        }
        if ($n < 1000) {
            $c = floor($n / 100);
            $r = $n % 100;
            return ($n == 100 ? 'CIEN' : $centenas[$c] . ($r > 0 ? ' ' . $this->enteroALetras($r) : ''));
        }
        if ($n < 1000000) {
            $m = floor($n / 1000);
            $r = $n % 1000;
            return ($m == 1 ? 'MIL' : $this->enteroALetras($m) . ' MIL') . ($r > 0 ? ' ' . $this->enteroALetras($r) : '');
        }
        return 'NUMERO GRANDE';
    }
}
