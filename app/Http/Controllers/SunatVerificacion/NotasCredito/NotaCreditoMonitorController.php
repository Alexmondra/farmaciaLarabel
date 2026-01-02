<?php

namespace App\Http\Controllers\SunatVerificacion\NotasCredito;

use App\Http\Controllers\Controller;
use App\Models\Ventas\NotaCredito;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotaCreditoMonitorController extends Controller
{
    // MONITOR: Notas con sunat_exito = false
    public function monitor()
    {
        $notas = NotaCredito::with(['venta.cliente', 'sucursal'])
            ->where('sunat_exito', false)
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20);

        return view('rectificacionSunat.notas.monitor', compact('notas'));
    }

    // VISOR: Repositorio de Notas Aceptadas
    public function visor(Request $request)
    {
        $rangoFechas = $request->input('rango_fechas');

        if ($rangoFechas && str_contains($rangoFechas, ' - ')) {
            [$inicio, $fin] = array_pad(explode(' - ', $rangoFechas, 2), 2, null);
            $fechaInicio = Carbon::parse(trim((string) $inicio))->startOfDay();
            $fechaFin    = Carbon::parse(trim((string) $fin))->endOfDay();
        } else {
            $fechaInicio = Carbon::now()->startOfMonth();
            $fechaFin    = Carbon::now()->endOfDay();
            $rangoFechas = $fechaInicio->format('Y-m-d') . ' - ' . $fechaFin->format('Y-m-d');
        }

        $notas = NotaCredito::with(['venta.cliente', 'sucursal'])
            ->where('sunat_exito', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));

                if ($search === '') return;

                if (str_contains($search, '-')) {
                    [$serie, $numero] = array_pad(explode('-', $search, 2), 2, null);
                    $serie  = trim((string) $serie);
                    $numero = trim((string) $numero);

                    $q->where(function ($qq) use ($serie, $numero) {
                        if ($serie !== '') {
                            $qq->where('serie', 'like', "%{$serie}%");
                        }

                        if ($numero !== '' && is_numeric($numero)) {
                            $qq->where('numero', (int) $numero);
                        } elseif ($numero !== '') {
                            $qq->whereRaw('CAST(numero AS CHAR) like ?', ["%{$numero}%"]);
                        }
                    });
                } else {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('serie', 'like', "%{$search}%");

                        if (is_numeric($search)) {
                            $qq->orWhere('numero', (int) $search);
                        } else {
                            $qq->orWhereRaw('CAST(numero AS CHAR) like ?', ["%{$search}%"]);
                        }
                    });
                }
            })
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_emision', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return view('rectificacionSunat.notas.visor', compact('notas', 'rangoFechas'));
    }

    // DESCARGA XML ORIGINAL
    public function descargarXml(NotaCredito $nota)
    {
        if (empty($nota->ruta_xml)) {
            return back()->with('error', 'La nota no tiene ruta XML registrada.');
        }

        $absolutePath = storage_path(
            'app/private/' . ltrim($nota->ruta_xml, '/')
        );

        if (!file_exists($absolutePath)) {
            return back()->with('error', 'No existe el XML en la ruta: ' . $absolutePath);
        }

        return response()->download(
            $absolutePath,
            basename($absolutePath)
        );
    }


    public function descargarCdr(NotaCredito $nota)
    {
        if (empty($nota->ruta_cdr)) {
            return back()->with('error', 'La nota no tiene ruta CDR registrada.');
        }

        $absolutePath = storage_path(
            'app/private/' . ltrim($nota->ruta_cdr, '/')
        );

        if (!file_exists($absolutePath)) {
            return back()->with('error', 'No existe el CDR en la ruta: ' . $absolutePath);
        }

        return response()->download(
            $absolutePath,
            basename($absolutePath)
        );
    }

    public function reenviar(NotaCredito $nota)
    {
        return back()->with('info', "Reenvío en desarrollo para {$nota->serie}-{$nota->numero}.");
    }
}
