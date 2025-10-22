<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-pills"></i> Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Código:</strong></td>
                                <td><code class="text-primary">{{ $medicamento->codigo }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>{{ $medicamento->nombre }}</td>
                            </tr>
                            <tr>
                                <td><strong>Forma farmacéutica:</strong></td>
                                <td>{{ $medicamento->forma_farmaceutica ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Concentración:</strong></td>
                                <td>{{ $medicamento->concentracion ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Presentación:</strong></td>
                                <td>{{ $medicamento->presentacion ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Laboratorio:</strong></td>
                                <td>{{ $medicamento->laboratorio ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Registro sanitario:</strong></td>
                                <td>{{ $medicamento->registro_sanitario ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Código de barras:</strong></td>
                                <td>{{ $medicamento->codigo_barra ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Categoría:</strong></td>
                                <td>
                                    @if($medicamento->categoria)
                                        <span class="badge badge-info">{{ $medicamento->categoria->nombre }}</span>
                                    @else
                                        <span class="text-muted">Sin categoría</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $medicamento->activo ? 'success' : 'danger' }}">
                                        {{ $medicamento->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Creado por:</strong></td>
                                <td>{{ $medicamento->usuario->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha creación:</strong></td>
                                <td>{{ $medicamento->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($medicamento->descripcion)
                <hr>
                <div class="form-group">
                    <label><strong>Descripción:</strong></label>
                    <p class="text-muted">{{ $medicamento->descripcion }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-image"></i> Imagen</h6>
            </div>
            <div class="card-body text-center">
                @if($medicamento->imagen_path)
                    <img src="{{ asset('storage/'.$medicamento->imagen_path) }}" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-height: 200px; object-fit: cover;"
                         alt="{{ $medicamento->nombre }}">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" 
                         style="height: 200px;">
                        <div class="text-center">
                            <i class="fas fa-pills fa-3x text-muted"></i>
                            <p class="text-muted mt-2">Sin imagen</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $sucursalesMedicamento->count() }}</h4>
                        <small class="text-muted">Sucursales</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ $lotes->count() }}</h4>
                        <small class="text-muted">Lotes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

