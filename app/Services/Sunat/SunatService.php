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
use Greenter\Model\Sale\Charge;
use Luecano\NumeroALetras\NumeroALetras;
use App\Models\Configuracion;
use App\Models\Ventas\Venta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class SunatService
{
    public function getSee()
    {
        $config = Configuracion::firstOrFail();
        $see = new See();

        // 1. INTENTAMOS OBTENER LA RUTA REAL USANDO "STORAGE"
        if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
            $rutaCert = Storage::path($config->sunat_certificado_path);
        } else {
            // Fallback por defecto
            $rutaCert = Storage::path('sunat/certificado_prueba.pem');
        }

        if (!file_exists($rutaCert)) {
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
                // Error de validación de SUNAT
                $error = $result->getError();
                $venta->codigo_error_sunat = $error->getCode();
                $venta->mensaje_sunat = $error->getMessage();
            }

            $venta->save();
            return $result->isSuccess();
        } catch (\Exception $e) {
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

        // 1. Detectar si es operación en Amazonía (IGV 0)
        // Si el porcentaje guardado en la venta es 0, asumimos Exonerado (Amazonía)
        $esAmazonia = ($venta->porcentaje_igv == 0);

        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipoDoc)
            ->setSerie($venta->serie)
            ->setCorrelativo($venta->numero)
            ->setFechaEmision(new \DateTime($venta->fecha_emision))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN');

        // Emisor
        $address = new Address();
        $address->setUbigueo($sucursal->ubigeo ?? '150101')
            ->setDepartamento($sucursal->departamento)
            ->setProvincia($sucursal->provincia)
            ->setDistrito($sucursal->distrito)
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

        // Descuentos Globales
        if ($venta->total_descuento > 0) {
            // Si es Amazonía (Exonerado), el factor es 1.00, si es Lima es 1.18
            $factorDivisor = $esAmazonia ? 1.00 : 1.18;
            $descuentoBase = $venta->total_descuento / $factorDivisor;

            $cargo = new Charge();
            $cargo->setCodTipo('02')
                ->setFactor(1)
                ->setMonto(round($descuentoBase, 2))
                ->setMontoBase(round(($esAmazonia ? $venta->op_exonerada : $venta->op_gravada) + $descuentoBase, 2));

            $invoice->setDescuentos([$cargo]);
        }

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

            // Si es Amazonía, el valor base es igual al precio, y el IGV es 0
            $base = $det->valor_unitario * $det->cantidad;
            $igvCalculado = ($esAmazonia) ? 0.00 : ($base * 0.18);

            // CÓDIGOS DE AFECTACIÓN IGV
            // 10: Gravado - Operación Onerosa (Lima)
            // 20: Exonerado - Operación Onerosa (Amazonía)
            $tipoAfectacion = $esAmazonia ? '20' : '10';

            $item->setCodProducto('MED-' . $det->medicamento_id)
                ->setUnidad('NIU')
                ->setCantidad($det->cantidad)
                ->setDescripcion($det->medicamento->nombre ?? 'PRODUCTO') // Asegurar nombre
                ->setMtoBaseIgv($base)
                ->setPorcentajeIgv($esAmazonia ? 0.00 : 18.00)
                ->setIgv($igvCalculado)
                ->setTipAfeIgv($tipoAfectacion)
                ->setTotalImpuestos($igvCalculado)
                ->setMtoValorVenta($base)
                ->setMtoValorUnitario($det->valor_unitario)
                ->setMtoPrecioUnitario($det->precio_unitario);

            $items[] = $item;
        }
        $invoice->setDetails($items);

        // LEYENDAS
        $leyendas = [];

        // 1. Monto en Letras
        $formatter = new NumeroALetras();
        $textoMonto = $formatter->toInvoice($venta->total_neto, 2, 'SOLES');
        $leyendas[] = (new Legend())->setCode('1000')->setValue('SON: ' . $textoMonto);

        // 2. Leyenda Obligatoria para Amazonía
        if ($esAmazonia) {
            $leyendas[] = (new Legend())
                ->setCode('2000') // Código genérico o usar 2001
                ->setValue('BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA');
        }

        // 3. Referencia de Pago
        if ($venta->referencia_pago) {
            $leyendas[] = (new Legend())
                ->setCode('2001')
                ->setValue('MEDIO: ' . $venta->medio_pago . ' | REF: ' . $venta->referencia_pago);
        }

        $invoice->setLegends($leyendas);

        return $invoice;
    }

    private function getHashFromXml($xml)
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $digestValue = $dom->getElementsByTagName('DigestValue')->item(0);
        return $digestValue ? $digestValue->nodeValue : null;
    }
}
