<div class="table-responsive">
    <table class="table table-hover mb-0 text-nowrap">
        <thead class="bg-light" style="/* En dark mode esto se ajusta solo con AdminLTE */">
            <tr>
                <th width="35%" class="border-0">CLIENTE / RAZÓN SOCIAL</th>
                <th width="20%" class="border-0">DOCUMENTO</th>
                <th width="20%" class="border-0">CONTACTO</th>
                <th width="10%" class="text-center border-0">PUNTOS</th>
                <th width="15%" class="text-right border-0">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            <tr>
                <td class="align-middle">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle {{ $cliente->tipo_documento == 'RUC' ? 'avatar-ruc' : '' }}">
                            {{ substr($cliente->nombre_completo, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">
                                {{ $cliente->nombre_completo }}
                            </div>
                            <small class="text-muted">
                                @if($cliente->tipo_documento == 'RUC')
                                <i class="fas fa-building mr-1"></i> Empresa
                                @else
                                <i class="fas fa-user-injured mr-1"></i> Paciente
                                @endif
                            </small>
                        </div>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="d-flex flex-column">
                        <span class="font-weight-bold text-secondary">{{ $cliente->tipo_documento }}</span>
                        <span class="text-dark">{{ $cliente->documento }}</span>
                    </div>
                </td>
                <td class="align-middle">
                    @if($cliente->telefono)
                    <div class="text-muted"><i class="fas fa-phone-alt mr-1 text-success"></i> {{ $cliente->telefono }}</div>
                    @else
                    <span class="text-muted small">--</span>
                    @endif

                    @if($cliente->email)
                    <div class="text-muted small"><i class="fas fa-envelope mr-1"></i> {{ Str::limit($cliente->email, 20) }}</div>
                    @endif
                </td>
                <td class="text-center align-middle">
                    @if($cliente->puntos > 0)
                    <span class="badge badge-success px-3 py-2 rounded-pill">{{ $cliente->puntos }} pts</span>
                    @else
                    <span class="badge badge-light px-3 py-2 rounded-pill text-muted">0 pts</span>
                    @endif
                </td>
                <td class="text-right align-middle">
                    <button class="btn btn-outline-secondary btn-sm rounded-circle mr-1" onclick="openShowModal({{ $cliente->id }})" title="Ver Expediente">
                        <i class="fas fa-eye"></i>
                    </button>
                    @can('clientes.editar')
                    <button class="btn btn-outline-info btn-sm rounded-circle mr-1" onclick="openEditModal({{ $cliente }})" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="text-muted opacity-50">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p class="mb-0 font-weight-bold">No se encontraron resultados</p>
                        <small>Intenta ajustar los filtros o buscar por otro término.</small>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center px-3 py-3">
    <div class="text-muted small">
        Mostrando {{ $clientes->firstItem() }} - {{ $clientes->lastItem() }} de {{ $clientes->total() }} registros
    </div>
    <div>
        {!! $clientes->links() !!}
    </div>
</div>