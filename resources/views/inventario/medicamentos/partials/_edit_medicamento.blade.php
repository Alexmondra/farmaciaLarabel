<div class="card card-outline card-primary shadow-sm bg-dark">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold text-uppercase">Ficha Técnica del Producto</h3>
    </div>
    <div class="card-body">
        <form id="formEditRapido" enctype="multipart/form-data">
            @csrf
            {{-- El método PUT se añade vía JS en FormData, pero lo dejamos aquí por respaldo --}}
            <input type="hidden" name="id" value="{{ $medicamento->id }}">

            {{-- SECCIÓN SUPERIOR: IDENTIDAD Y FOTO --}}
            <div class="d-flex align-items-start mb-3">
                <div class="text-center mr-3" style="width: 110px;">
                    <div id="preview_container" class="border border-secondary rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 110px; height: 110px;">
                        @if($medicamento->imagen_path)
                        <img id="img_preview" src="{{ asset('storage/'.$medicamento->imagen_path) }}" class="img-fluid" style="object-fit: cover; height: 100%;">
                        @else
                        <i class="fas fa-pills fa-2x text-muted" id="img_placeholder"></i>
                        <img id="img_preview" src="" class="img-fluid d-none" style="object-fit: cover; height: 100%;">
                        @endif
                    </div>
                    <label class="btn btn-xs btn-outline-primary mt-2 w-100" style="cursor: pointer;">
                        <i class="fas fa-camera"></i> Cambiar Foto
                        <input type="file" name="imagen" id="input_imagen" hidden accept="image/*">
                    </label>
                </div>

                <div class="flex-grow-1">
                    <label class="small mb-0 text-muted">CÓDIGO INTERNO (SKU):</label>
                    <input type="text" name="codigo" class="form-control form-control-sm bg-secondary text-white border-0 font-weight-bold mb-2" value="{{ $medicamento->codigo }}" readonly>

                    <label class="small mb-0 text-primary font-weight-bold">CÓDIGO DIGEMID:</label>
                    <input type="text" name="codigo_digemid" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->codigo_digemid }}" placeholder="Ej: 12345">
                </div>
            </div>

            {{-- DATOS PRINCIPALES (SIEMPRE VISIBLES) --}}
            <div class="form-group mb-2">
                <label class="small font-weight-bold text-warning">NOMBRE COMERCIAL *</label>
                <input type="text" name="nombre" class="form-control form-control-sm bg-secondary text-white border-0 font-weight-bold" value="{{ $medicamento->nombre }}" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">LABORATORIO</label>
                        <input type="text" name="laboratorio" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->laboratorio }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-success">UNIDADES / CAJA *</label>
                        <input type="number" name="unidades_por_envase" class="form-control form-control-sm bg-secondary text-white border-0 text-center font-weight-bold" value="{{ $medicamento->unidades_por_envase }}" required min="1">
                    </div>
                </div>
            </div>

            {{-- BOTÓN EXPANDIR --}}
            <button type="button" id="btnExpandirEdicion" class="btn btn-outline-light btn-xs btn-block my-3">
                <i class="fas fa-plus mr-1"></i> Ver más campos (Registro, Blíster, etc.)
            </button>

            {{-- SECCIÓN DESPLEGABLE --}}
            <div id="seccionMasDatos" style="display: none;" class="mt-2 pt-3 border-top border-secondary">

                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-info">UNIDADES / BLÍSTER</label>
                            <input type="number" name="unidades_por_blister" class="form-control form-control-sm bg-secondary text-white border-0 text-center" value="{{ $medicamento->unidades_por_blister }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-primary">REG. SANITARIO</label>
                            <input type="text" name="registro_sanitario" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->registro_sanitario }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">FORMA FARM.</label>
                            <input type="text" name="forma_farmaceutica" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->forma_farmaceutica }}" placeholder="Ej: Tableta">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">PRESENTACIÓN</label>
                            <input type="text" name="presentacion" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->presentacion }}" placeholder="Caja x 100">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="small font-weight-bold">CONCENTRACIÓN</label>
                    <input type="text" name="concentracion" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->concentracion }}" placeholder="Ej: 500mg">
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted"><i class="fas fa-barcode"></i> BARRAS CAJA</label>
                            <input type="text" name="codigo_barra" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->codigo_barra }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted"><i class="fas fa-barcode"></i> BARRAS BLÍSTER</label>
                            <input type="text" name="codigo_barra_blister" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ $medicamento->codigo_barra_blister }}">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="small font-weight-bold">CATEGORÍA</label>
                    <div class="d-flex">
                        <select name="categoria_id" id="select_categoria" class="select2-categoria" style="width: 100%;">
                            <option value="">-- Buscar categoría --</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ $medicamento->categoria_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>

                    </div>
                </div>


                <div class="form-group mb-2">
                    <label class="small font-weight-bold">DESCRIPCIÓN / NOTAS</label>
                    <textarea name="descripcion" class="form-control form-control-sm bg-secondary text-white border-0" rows="2">{{ $medicamento->descripcion }}</textarea>
                </div>

                <div class="d-flex justify-content-around bg-light rounded p-2 mt-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="afecto_igv" value="1" class="custom-control-input" id="sw_igv" {{ $medicamento->afecto_igv ? 'checked' : '' }}>
                        <label class="custom-control-label small font-weight-bold text-dark" for="sw_igv text-dark">Afecto IGV</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="receta_medica" value="1" class="custom-control-input" id="sw_receta" {{ $medicamento->receta_medica ? 'checked' : '' }}>
                        <label class="custom-control-label small font-weight-bold text-danger" for="sw_receta">Pide Receta</label>
                    </div>
                </div>
            </div>

            {{-- BOTÓN GUARDAR --}}
            <div class="mt-4">
                <button type="submit" id="btnGuardarCambios" class="btn btn-primary btn-block shadow-lg font-weight-bold py-2">
                    <i class="fas fa-save mr-1"></i> GUARDAR TODOS LOS CAMBIOS
                </button>
            </div>
        </form>
    </div>
</div>