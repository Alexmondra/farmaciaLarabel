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

use Greenter\Model\Sale\Note;
use App\Models\Ventas\NotaCredito;

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

            // --- CAMBIO 1: GUARDAR EN CARPETA 'FACTURAS' ---
            $rutaXml = 'sunat/xml/facturas/' . $nombreArchivo . '.xml';
            Storage::put($rutaXml, $xml);

            $venta->ruta_xml = $rutaXml;
            $venta->hash = $this->getHashFromXml($xml);
            $venta->save();

            // 2. Enviar a SUNAT
            $result = $see->sendXml(get_class($invoice), $invoice->getName(), $xml);

            if ($result->isSuccess()) {
                // --- CAMBIO 2: GUARDAR CDR EN CARPETA 'FACTURAS' ---
                $rutaCdr = 'sunat/cdr/facturas/R-' . $nombreArchivo . '.zip';

                Storage::put($rutaCdr, $result->getCdrZip());

                $venta->ruta_cdr = $rutaCdr;
                $venta->codigo_error_sunat = 0;
                $venta->mensaje_sunat = $result->getCdrResponse()->getDescription();
                $venta->estado = 'ACEPTADA';
            } else {
                $error = $result->getError();
                $venta->codigo_error_sunat = $error->getCode();
                $venta->mensaje_sunat = $error->getMessage();
            }

            $venta->save();
            return $result->isSuccess();
        } catch (\Exception $e) {
            // ... (Manejo de errores igual) ...
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

        // Detectar si la Sucursal está en Amazonía (IGV 0%) para la Leyenda
        $sucursalEnAmazonia = ($sucursal->impuesto_porcentaje == 0);

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
            // Nota: El cálculo del factorDivisor aquí es complejo si es mixto.
            // Para simplificar, asumimos que si hay IGV global > 0, usamos factor 1.18, sino 1.00
            $factorDivisor = ($venta->total_igv > 0) ? 1.18 : 1.00;
            $descuentoBase = $venta->total_descuento / $factorDivisor;

            $cargo = new Charge();
            $cargo->setCodTipo('02') // Descuento global
                ->setFactor(1)
                ->setMonto(round($descuentoBase, 2))
                ->setMontoBase(round(($venta->op_gravada + $venta->op_exonerada) + $descuentoBase, 2));

            $invoice->setDescuentos([$cargo]);
        }

        // Totales (Usamos lo calculado en VentaService)
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

            // Lógica por Ítem: Si tiene IGV guardado, es Gravado (10). Si es 0, es Exonerado (20).
            $tieneIgv = ($det->igv > 0.00);

            // CÓDIGOS DE AFECTACIÓN IGV
            // 10: Gravado - Operación Onerosa
            // 20: Exonerado - Operación Onerosa
            $tipoAfectacion = $tieneIgv ? '10' : '20';
            $porcentaje     = $tieneIgv ? 18.00 : 0.00;

            // Base imponible del ítem (Cantidad * ValorUnitario)
            $baseItem = round((float)$det->subtotal_bruto, 2);

            $item->setCodProducto('MED-' . $det->medicamento_id)
                ->setUnidad('NIU')
                ->setCantidad($det->cantidad)
                ->setDescripcion($det->medicamento->nombre ?? 'PRODUCTO')
                ->setMtoBaseIgv($baseItem)
                ->setPorcentajeIgv($porcentaje)
                ->setIgv($det->igv)
                ->setTipAfeIgv($tipoAfectacion) // ¡Clave para mixto!
                ->setTotalImpuestos($det->igv)
                ->setMtoValorVenta($baseItem)
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

        // 2. Leyenda Obligatoria SOLO para zona de Amazonía
        if ($sucursalEnAmazonia) {
            $leyendas[] = (new Legend())
                ->setCode('2000')
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



    public function transmitirNotaCredito(NotaCredito $nota, Venta $ventaOriginal)
    {
        try {
            $see = $this->getSee();

            // 1. Generar objeto Note
            $note = $this->generarObjetoNota($nota, $ventaOriginal);

            // 2. Firmar XML
            $xml = $see->getXmlSigned($note);
            $nombreArchivo = $note->getName();

            // --- CAMBIO 1: CONFIRMAMOS CARPETA 'NC' PARA XML ---
            $rutaXml = 'sunat/xml/nc/' . $nombreArchivo . '.xml';
            Storage::put($rutaXml, $xml);

            $nota->ruta_xml = $rutaXml;
            $nota->hash = $this->getHashFromXml($xml);
            $nota->save();

            // 3. Enviar a SUNAT
            $result = $see->sendXml(get_class($note), $note->getName(), $xml);

            if ($result->isSuccess()) {
                // --- CAMBIO 2: GUARDAR CDR TAMBIÉN EN CARPETA 'NC' ---
                $rutaCdr = 'sunat/cdr/nc/R-' . $nombreArchivo . '.zip';

                Storage::put($rutaCdr, $result->getCdrZip());

                $nota->ruta_cdr = $rutaCdr;
                $nota->sunat_exito = true;
                $nota->mensaje_sunat = $result->getCdrResponse()->getDescription();
            } else {
                $error = $result->getError();
                $nota->sunat_exito = false;
                $nota->codigo_error_sunat = $error->getCode();
                $nota->mensaje_sunat = $error->getMessage();
            }

            $nota->save();
            return $result->isSuccess();
        } catch (\Exception $e) {
            // ... (Manejo de errores igual) ...
            Log::error("Error SUNAT Nota Crédito {$nota->id}: " . $e->getMessage());
            $nota->mensaje_sunat = "Error Interno: " . $e->getMessage();
            $nota->save();
            return false;
        }
    }

    public function generarObjetoNota(NotaCredito $nota, Venta $venta)
    {
        $config = Configuracion::first();
        $sucursal = $venta->sucursal;

        $note = new Note();
        $note
            ->setUblVersion('2.1')
            ->setTipDocAfectado($venta->tipo_comprobante == 'FACTURA' ? '01' : '03')
            ->setNumDocfectado($venta->serie . '-' . $venta->numero) // Referencia a la factura original
            ->setCodMotivo($nota->cod_motivo)
            ->setDesMotivo($nota->descripcion_motivo)
            ->setTipoDoc('07') // Tipo Nota de Crédito
            ->setSerie($nota->serie)
            ->setCorrelativo($nota->numero)
            ->setFechaEmision(new \DateTime($nota->fecha_emision))
            ->setTipoMoneda('PEN');

        // Emisor (Igual que la factura)
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
        $note->setCompany($emisor);

        // Cliente (Igual que la factura)
        $client = new Client();
        $client->setTipoDoc(strlen($venta->cliente->documento) == 11 ? '6' : '1')
            ->setNumDoc($venta->cliente->documento)
            ->setRznSocial($venta->cliente->nombre_completo);
        $note->setClient($client);

        // Totales (Reutilizamos los de la venta porque es anulación total)
        $note->setMtoOperGravadas($venta->op_gravada)
            ->setMtoOperExoneradas($venta->op_exonerada)
            ->setMtoIGV($venta->total_igv)
            ->setTotalImpuestos($venta->total_igv)
            ->setValorVenta($venta->op_gravada + $venta->op_exonerada)
            ->setSubTotal($venta->total_neto)
            ->setMtoImpVenta($venta->total_neto);

        // Detalles (Deben ser idénticos a la venta original)
        $items = [];
        foreach ($venta->detalles as $det) {
            $item = new SaleDetail();

            $baseItem = $det->valor_unitario * $det->cantidad;

            $item->setCodProducto('MED-' . $det->medicamento_id)
                ->setUnidad('NIU')
                ->setCantidad($det->cantidad)
                ->setDescripcion($det->medicamento->nombre ?? 'PRODUCTO') // Ojo: asegúrate de cargar la relación medicamento en el controller
                ->setMtoBaseIgv($baseItem)
                ->setPorcentajeIgv($det->igv > 0 ? 18.00 : 0.00)
                ->setIgv($det->igv)
                ->setTipAfeIgv($det->tipo_afectacion ?? ($det->igv > 0 ? '10' : '20'))
                ->setTotalImpuestos($det->igv)
                ->setMtoValorVenta($baseItem)
                ->setMtoValorUnitario($det->valor_unitario)
                ->setMtoPrecioUnitario($det->precio_unitario);

            $items[] = $item;
        }
        $note->setDetails($items);

        $formatter = new NumeroALetras();
        $textoMonto = $formatter->toInvoice($venta->total_neto, 2, 'SOLES');
        $note->setLegends([
            (new Legend())->setCode('1000')->setValue('SON: ' . $textoMonto)
        ]);

        return $note;
    }
}
