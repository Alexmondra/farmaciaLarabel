<?php

namespace App\Http\Controllers\SunatVerificacion;

use App\Http\Controllers\Controller;
use App\Models\Ventas\Venta;
use App\Services\SucursalResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SunatArchivosController extends Controller
{

    public function index(Request $request, SucursalResolver $resolver)
    {
        $rangoFechas = $request->input('rango_fechas');
        if ($rangoFechas) {
            $partes = explode(' - ', $rangoFechas);
            $fechaInicio = trim($partes[0]);
            $fechaFin    = trim($partes[1]);
        } else {
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
            $fechaFin    = \Carbon\Carbon::now()->format('Y-m-d');
            $rangoFechas = $fechaInicio . ' - ' . $fechaFin;
        }
        $contexto = $resolver->resolverPara(auth()->user());
        $idsFiltro = $contexto['ids_filtro'];

        $query = Venta::query()
            ->with(['cliente', 'sucursal'])
            ->whereNotNull('ruta_xml')
            ->where('ruta_xml', '!=', '');
        if (!is_null($idsFiltro)) {
            $query->whereIn('sucursal_id', $idsFiltro);
        }
        if ($request->filled('search')) {
            $busqueda = trim($request->search);

            $query->where(function ($q) use ($busqueda) {
                // 1. Búsqueda exacta F001-450
                if (strpos($busqueda, '-') !== false) {
                    $partes = explode('-', $busqueda);
                    if (count($partes) == 2) {
                        $q->where('serie', 'like', "%" . trim($partes[0]) . "%")
                            ->where('numero', 'like', "%" . trim($partes[1]) . "%");
                        return;
                    }
                }
                $q->where('serie', 'like', "%$busqueda%")
                    ->orWhere('numero', 'like', "%$busqueda%")
                    ->orWhereHas('cliente', function ($c) use ($busqueda) {
                        $c->where('documento', 'like', "%$busqueda%")
                            ->orWhere('razon_social', 'like', "%$busqueda%")
                            // Búsqueda por Nombre Completo (CONCAT)
                            ->orWhereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%{$busqueda}%"])
                            ->orWhere('nombre', 'like', "%$busqueda%")
                            ->orWhere('apellidos', 'like', "%$busqueda%");
                    });
            });
        } else {
            $query->whereDate('fecha_emision', '>=', $fechaInicio)
                ->whereDate('fecha_emision', '<=', $fechaFin);
        }
        $ventas = $query->orderBy('fecha_emision', 'desc')->paginate(15);

        return view('rectificacionSunat.xml-crd.index', compact('ventas', 'rangoFechas'));
    }

    public function descargarXml(Venta $venta)
    {
        if (!$venta->ruta_xml) {
            return back()->with('error', 'No hay ruta de XML registrada.');
        }

        if (!Storage::exists($venta->ruta_xml)) {
            return back()->with('error', 'El archivo XML físico no se encuentra en el servidor.');
        }

        $nombreDescarga = $venta->serie . '-' . $venta->numero . '.xml';
        return Storage::download($venta->ruta_xml, $nombreDescarga);
    }

    public function descargarCdr(Venta $venta)
    {
        if (!$venta->ruta_cdr) {
            return back()->with('warning', 'El CDR aún no ha sido generado o descargado de SUNAT.');
        }

        if (!Storage::exists($venta->ruta_cdr)) {
            return back()->with('error', 'El archivo CDR físico no se encuentra en el servidor.');
        }

        $nombreDescarga = 'R-' . $venta->serie . '-' . $venta->numero . '.zip';
        return Storage::download($venta->ruta_cdr, $nombreDescarga);
    }
}
