{{-- MODAL EDITAR VENCIMIENTO --}}
<div class="modal fade" id="modalEditarVencimiento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg-dark text-white shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-alt mr-2 text-primary"></i>Fecha de Vencimiento</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="vencimiento_url">
                <div class="form-group">
                    <label class="small text-muted">SELECCIONAR FECHA</label>
                    <input type="date" class="form-control bg-secondary text-white border-0" id="fecha_vencimiento_input">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-light btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="btnGuardarVencimiento">Actualizar Fecha</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDITAR OFERTA DEL LOTE --}}
{{-- MODAL OFERTA LOTE --}}
<div class="modal fade" id="modalEditarOferta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content bg-dark text-white shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold text-warning"><i class="fas fa-tag mr-2"></i>Precio Oferta</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="oferta_url">
                <div class="form-group">
                    <label class="small text-muted">PRECIO LIQUIDACIÓN (UNIDAD)</label>
                    <input type="number" step="0.01" class="form-control bg-secondary text-white border-0 font-weight-bold text-center" id="oferta_input">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary btn-block" id="btnGuardarOferta">Guardar Oferta</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDITAR UBICACIÓN --}}
<div class="modal fade" id="modalEditarUbicacion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg-dark text-white shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold text-info"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ubicacion_url">
                <div class="form-group">
                    <label class="small text-muted text-uppercase">Espacio en Almacén / Estante</label>
                    <input type="text" class="form-control bg-secondary text-white border-0" id="ubicacion_input" placeholder="Ej: Vitrina 1, Fila 2">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-info btn-block shadow-sm" id="btnGuardarUbicacion">Actualizar Ubicación</button>
            </div>
        </div>
    </div>
</div>