@extends('adminlte::page')
@section('title', 'Repositorio de Notas')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-secondary shadow-lg">
        <div class="card-header border-0">
            <form action="{{ route('notas.visor.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label>Rango de Fechas:</label>
                        <input type="text" class="form-control" id="reservation" name="rango_fechas" value="{{ old('rango_fechas', $rangoFechas ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label>Buscar (Serie/Número):</label>
                        <input type="text" name="search" class="form-control" placeholder="Ej: BC05-1" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mt-4">
                        <button type="submit" class="btn btn-secondary btn-block">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Emisión</th>
                        <th>Documento NC</th>
                        <th>Doc. Referencia</th>
                        <th>Cliente</th>
                        <th class="text-right">Descargas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notas as $nota)
                    <tr>
                        <td>{{ optional($nota->fecha_emision)->format('d/m/Y') }}</td>
                        <td><span class="badge badge-secondary">{{ $nota->serie }}-{{ $nota->numero }}</span></td>
                        <td>
                            <small>Afecta a:</small>
                            {{ optional($nota->venta)->serie }}-{{ optional($nota->venta)->numero }}
                        </td>
                        <td>{{ optional(optional($nota->venta)->cliente)->nombre_completo ?? 'PÚBLICO GENERAL' }}</td>
                        <td class="text-right">
                            <div class="btn-group">
                                <a href="{{ route('reportes.venta.pdf', $nota->venta->id) }}"
                                    class="btn btn-sm btn-outline-danger font-weight-bold"
                                    target="_blank"
                                    title="Ver PDF">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                                <a href="{{ route('notas.download.xml', $nota) }}" class="btn btn-sm btn-default">
                                    <i class="fas fa-file-code text-info"></i> XML
                                </a>
                                <a href="{{ route('notas.download.cdr', $nota) }}" class="btn btn-sm btn-default">
                                    <i class="fas fa-file-archive text-success"></i> CDR
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay notas en el rango seleccionado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $notas->links() }}
        </div>
    </div>
</div>
@stop