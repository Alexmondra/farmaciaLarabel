<div class="modal fade" id="modalShowCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 15px; overflow: hidden;">

            <div class="modal-header text-white" style="background: linear-gradient(135deg, #00d2d3 0%, #00a8ff 100%); border:0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-id-card-alt mr-2"></i> Expediente del Cliente
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="row no-gutters">

                    <div class="col-md-4 bg-light text-center p-4 border-right profile-sidebar">
                        <div class="mb-3">
                            <div id="show_avatar" class="d-flex align-items-center justify-content-center mx-auto shadow-sm"
                                style="width: 100px; height: 100px; border-radius: 50%; background: #e0f7fa; color: #00bcd4; font-size: 2.5rem; font-weight: bold; border: 4px solid #fff;">
                            </div>
                        </div>

                        <h4 id="show_nombre" class="font-weight-bold text-dark mb-1 text-uppercase">--</h4>

                        <span id="show_tipo_doc" class="badge badge-info px-3 py-1 mb-3" style="font-size: 0.9rem;">--</span>

                        <div class="card bg-white border-0 shadow-sm mt-3 p-3 text-left">
                            <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Puntos Acumulados</small>
                            <div class="d-flex align-items-center mt-1">
                                <i class="fas fa-star text-warning mr-2 fa-lg"></i>
                                <h3 id="show_puntos" class="mb-0 font-weight-bold text-dark">0</h3>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-outline-secondary btn-block btn-sm" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                    <div class="col-md-8 bg-white p-0">

                        <ul class="nav nav-tabs nav-justified" id="clienteTabs" role="tablist" style="border-bottom: 2px solid #f4f6f9;">
                            <li class="nav-item">
                                <a class="nav-link active py-3 font-weight-bold" id="info-tab" data-toggle="tab" href="#info" role="tab">
                                    <i class="fas fa-info-circle mr-2"></i> Información
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3 font-weight-bold" id="history-tab" data-toggle="tab" href="#history" role="tab">
                                    <i class="fas fa-history mr-2"></i> Historial
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-4" id="clienteTabsContent">

                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold">
                                            <i class="fas fa-envelope mr-1"></i> Email
                                        </label>
                                        <p id="show_email" class="font-weight-bold mb-0 text-dark h6">--</p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold">
                                            <i class="fas fa-phone mr-1"></i> Teléfono
                                        </label>
                                        <p id="show_telefono" class="font-weight-bold mb-0 text-dark h6">--</p>
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold">
                                            <i class="fas fa-map-marker-alt mr-1"></i> Dirección
                                        </label>
                                        <p id="show_direccion" class="font-weight-bold mb-0 text-dark h6">--</p>
                                    </div>

                                    <div class="col-md-6 mb-4" id="block-sexo">
                                        <label class="text-muted small text-uppercase font-weight-bold">
                                            <i class="fas fa-venus-mars mr-1"></i> Sexo
                                        </label>
                                        <p id="show_sexo" class="font-weight-bold mb-0 text-dark h6">--</p>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold">
                                            <i class="fas fa-calendar-alt mr-1"></i> Registrado el
                                        </label>
                                        <p id="show_registro" class="font-weight-bold mb-0 text-dark h6">--</p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="history" role="tabpanel">
                                <div class="text-center py-5" id="history-container">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dark-mode .profile-sidebar {
        background-color: #3f474e !important;
        border-right-color: #56606a !important;
    }

    .dark-mode .nav-tabs .nav-link.active {
        background-color: #343a40;
        color: #fff;
        border-color: #56606a #56606a #343a40;
    }

    .dark-mode .nav-tabs {
        border-bottom-color: #56606a !important;
    }

    .dark-mode #show_nombre,
    .dark-mode #show_puntos,
    .dark-mode p.text-dark {
        color: #fff !important;
    }
</style>