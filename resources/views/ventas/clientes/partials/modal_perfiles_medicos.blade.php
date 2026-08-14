<div class="modal fade" id="modalPerfilesMedicos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #10ac84 0%, #00d2d3 100%); border:0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-notes-medical mr-2 animate-pulse"></i> Perfiles Médicos: <span id="pm_cliente_nombre"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closePMModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                
                {{-- Alerta Clínica (tiendafarma-chat / prioridad médica) --}}
                <div class="alert border-0 mb-4 bg-light-green text-teal d-flex align-items-start shadow-sm" style="border-radius: 12px; padding: 15px;">
                    <i class="fas fa-heartbeat mr-3 fa-lg mt-1 text-success"></i>
                    <div>
                        <strong class="text-dark font-weight-bold">Prioridad Médica & Seguridad:</strong> 
                        Estos antecedentes y perfiles médicos son de carácter puramente informativo y orientativo para el chatbot de la tienda y el personal de apoyo. 
                        <span class="text-danger font-weight-bold">Siempre recomiende de manera prioritaria la consulta formal con un médico profesional cualificado antes de tomar decisiones sobre tratamientos o medicamentos.</span>
                    </div>
                </div>

                {{-- Acciones del listado --}}
                <div class="d-flex justify-content-between align-items-center mb-3" id="pm_list_actions">
                    <h6 class="font-weight-bold text-secondary mb-0">Historial Clínico Registrado</h6>
                    <button class="btn btn-sm btn-success rounded-pill px-3 py-2 btn-add-pm" onclick="showPMForm(false)">
                        <i class="fas fa-plus mr-1"></i> Nuevo Perfil
                    </button>
                </div>

                {{-- Formulario Crear/Editar (Oculto por defecto) --}}
                <div id="pm_form_container" class="card border-0 bg-light p-4 mb-4 rounded-xl d-none transition-all duration-300">
                    <h6 class="font-weight-bold text-success mb-3" id="pm_form_title">
                        <i class="fas fa-file-medical-alt mr-1"></i> Registrar Perfil Médico
                    </h6>
                    <form id="pm_form">
                        @csrf
                        <input type="hidden" id="pm_id">
                        <input type="hidden" id="pm_cliente_id">
                        
                        <div class="form-group">
                            <label for="pm_antecedentes" class="font-weight-bold text-dark mb-1">Antecedentes Médicos</label>
                            <textarea class="form-control border-0 shadow-sm rounded-lg" id="pm_antecedentes" rows="4" 
                                      placeholder="Ej. Hipertensión diagnosticada en 2022. Alérgico a los AINEs. En tratamiento actual con Losartán 50mg..." required></textarea>
                            <small class="form-text text-muted">Detalla condiciones crónicas, alergias, tratamientos actuales o antecedentes relevantes.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="pm_device_fingerprint" class="font-weight-bold text-dark mb-1">Huella de Dispositivo (Device Fingerprint)</label>
                            <input type="text" class="form-control border-0 shadow-sm rounded-lg" id="pm_device_fingerprint" 
                                   placeholder="Opcional. Ej: fp_9a12c85b3">
                            <small class="form-text text-muted">Asocia este perfil a un dispositivo específico del chat de la tienda (dejar en blanco para perfiles manuales).</small>
                        </div>
                        
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 mr-2" onclick="hidePMForm()">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 py-2" id="btnSavePM">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Listado de Perfiles --}}
                <div id="pm_list_container" class="transition-all duration-300">
                    <div class="table-responsive rounded-xl shadow-sm border" style="border-radius: 12px; overflow: hidden;">
                        <table class="table table-hover mb-0 text-nowrap align-middle">
                            <thead class="bg-light text-secondary font-weight-bold">
                                <tr>
                                    <th width="50%" class="border-0">Antecedentes</th>
                                    <th width="20%" class="border-0">Dispositivo</th>
                                    <th width="15%" class="border-0 text-center">Registro</th>
                                    <th width="15%" class="border-0 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="pm_table_body">
                                {{-- Se llena dinámicamente mediante AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 py-2" data-dismiss="modal" onclick="closePMModal()">Cerrar</button>
            </div>
            
        </div>
    </div>
</div>
