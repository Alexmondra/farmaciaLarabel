@extends('adminlte::page')

@section('title', 'Monitor de Notas')

@section('content_header')
<h1 class="m-0 text-dark"><i class="fas fa-exclamation-circle text-yellow mr-2"></i> Monitor de Notas (NC/ND)</h1>
@stop

@section('content')
<div class="card card-outline card-yellow shadow-lg">
    <div class="card-header">
        <h3 class="card-title">Notas Pendientes o con Error</h3>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Nota</th>
                    <th>Ref. Comprobante</th>
                    <th>Error SUNAT</th>
                    <th class="text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notas as $nota)
                <tr>
                    <td>{{ optional($nota->fecha_emision)->format('d/m/Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $nota->serie }}-{{ $nota->numero }}</span></td>
                    <td>
                        <small class="text-muted">Afecta a:</small>
                        {{ optional($nota->venta)->serie }}-{{ optional($nota->venta)->numero }}
                    </td>
                    <td>
                        @if($nota->codigo_error_sunat)
                        <span class="badge badge-danger">{{ $nota->codigo_error_sunat }}</span>
                        @else
                        <span class="badge badge-warning">PENDIENTE</span>
                        @endif
                        <div class="text-muted">{{ $nota->mensaje_sunat ?? 'Sin mensaje' }}</div>
                    </td>
                    <td class="text-right">
                        <div class="btn-group">


                            @if(!empty($nota->ruta_xml))
                            <a href="{{ route('notas.download.xml', $nota) }}" class="btn btn-sm btn-default" title="Descargar XML">
                                <i class="fas fa-file-code text-info"></i> XML
                            </a>
                            @endif

                            @if(!empty($nota->ruta_cdr))
                            <a href="{{ route('notas.download.cdr', $nota) }}" class="btn btn-sm btn-default" title="Descargar CDR">
                                <i class="fas fa-file-archive text-success"></i> CDR
                            </a>
                            @endif

                            <form action="{{ route('notas.monitor.reenviar', $nota) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" title="Reenviar a SUNAT">
                                    <i class="fas fa-paper-plane"></i> Reenviar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay notas pendientes o con error.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $notas->links() }}
    </div>
</div>
@stop