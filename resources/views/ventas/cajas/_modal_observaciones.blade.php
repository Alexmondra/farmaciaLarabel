<div class="modal fade" id="modalObservaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            {{-- HEADER --}}
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="far fa-clipboard mr-2"></i> Bitácora de Observaciones
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            {{-- BODY --}}
            <div class="modal-body bg-light">
                <div class="row h-100">

                    {{-- COLUMNA IZQUIERDA: APERTURA --}}
                    <div class="col-md-6 border-right">
                        <div class="d-flex align-items-center mb-2 text-primary">
                            <i class="fas fa-play-circle mr-2"></i>
                            <strong class="text-uppercase small" style="letter-spacing: 1px;">Al Abrir Caja</strong>
                        </div>

                        <div class="p-3 bg-white rounded border shadow-sm h-100">
                            @if(!empty($obsApertura))
                            <p class="mb-0 text-dark" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $obsApertura }}</p>
                            @else
                            <div class="text-center py-4 text-muted opacity-50">
                                <i class="far fa-comment-alt mb-2"></i>
                                <p class="mb-0 small font-italic">Sin observaciones registradas al inicio.</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: CIERRE --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2 text-danger">
                            <i class="fas fa-stop-circle mr-2"></i>
                            <strong class="text-uppercase small" style="letter-spacing: 1px;">Al Cerrar Caja</strong>
                        </div>

                        <div class="p-3 bg-white rounded border shadow-sm h-100">
                            @if(!empty($obsCierre))
                            <p class="mb-0 text-dark" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $obsCierre }}</p>
                            @else
                            <div class="text-center py-4 text-muted opacity-50">
                                <i class="fas fa-lock mb-2"></i>
                                <p class="mb-0 small font-italic">
                                    @if(isset($cajaSesion) && $cajaSesion->estado === 'ABIERTO')
                                    La caja sigue abierta.
                                    @else
                                    Sin observaciones al cierre.
                                    @endif
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer py-1 bg-white">
                <button type="button" class="btn btn-sm btn-secondary px-4" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>