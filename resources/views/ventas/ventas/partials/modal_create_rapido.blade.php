<style>
    #modalCreateRapido {
        z-index: 2000 !important;
    }

    .modal-backdrop.show {
        z-index: 1065 !important;
    }

    #rapido_nombre {
        text-transform: uppercase;
    }
</style>

<div class="modal fade" id="modalCreateRapido" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-bolt mr-1"></i> Registro Rápido
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="form-create-rapido" autocomplete="off">
                    @csrf
                    <input type="hidden" name="afecto_igv" value="1">
                    <input type="hidden" name="activo" value="1">
                    <input type="hidden" name="receta_medica" value="0">
                    <input type="hidden" name="unidades_por_envase" value="1">
                    <input type="hidden" name="unidades_por_blister" value="1">

                    <div class="form-row">
                        <div class="col-5">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Código Interno</label>
                                <input type="text" class="form-control font-weight-bold text-center bg-light"
                                    name="codigo" id="rapido_codigo" readonly>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Código Barras (Scanner)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control font-weight-bold" name="codigo_barra"
                                        id="rapido_codigo_barra" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-weight-bold form-control-lg" name="nombre" id="rapido_nombre" placeholder="Escriba el nombre aquí..." required>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Categoría (Opcional)</label>
                        <select class="form-control" name="categoria_id">
                            <option value="">-- Sin Categoría --</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary font-weight-bold px-4" id="btn-save-rapido">
                    <i class="fas fa-save mr-1"></i> GUARDAR Y VENDER
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var sufijoSesion = '000';

    window.abrirModalCrearRapido = function(termino) {
        if (typeof $ === 'undefined') return;

        if (typeof window.cerrarResultados === 'function') {
            window.cerrarResultados();
        }

        $('#form-create-rapido')[0].reset();
        $('#rapido_nombre').removeClass('is-invalid');
        sufijoSesion = Math.floor(Math.random() * 900) + 100;

        let soloNumeros = /^\d+$/.test(termino);

        if (soloNumeros && termino.length >= 5) {
            $('#rapido_codigo_barra').val(termino);
            $('#rapido_codigo').val('NEW-' + sufijoSesion);
            setTimeout(() => {
                $('#rapido_nombre').focus().select();
            }, 400);
        } else {
            $('#rapido_nombre').val(termino.toUpperCase());
            $('#rapido_codigo_barra').val('');
            generarCodigoInterno(termino);
            setTimeout(() => {
                $('#rapido_codigo_barra').focus().select();
            }, 400);
        }

        $('#modalCreateRapido').modal('show');
    };

    function generarCodigoInterno(texto) {
        if (typeof $ === 'undefined') return;
        let limpio = texto.toUpperCase().replace(/[^A-Z0-9]/g, '');
        let prefijo = limpio.substring(0, 6) || "NEW";
        $('#rapido_codigo').val(prefijo + '-' + sufijoSesion);
    }

    window.addEventListener('load', function() {
        if (typeof $ === 'undefined') return;

        $('#rapido_nombre').on('input', function() {
            generarCodigoInterno($(this).val());
        });

        $(document).on('click', '#btn-save-rapido', function(e) {
            e.preventDefault();
            let btn = $(this);
            let form = $('#form-create-rapido');
            let nombreInput = $('#rapido_nombre');
            let barraInput = $('#rapido_codigo_barra');

            if (nombreInput.val().trim() === '') {
                nombreInput.addClass('is-invalid').focus();
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            let terminoABuscar = barraInput.val() ? barraInput.val() : nombreInput.val();

            $.ajax({
                url: "{{ route('inventario.medicamentos.storeRapido') }}",
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // 1. Rehabilitar botón y cerrar modal
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> GUARDAR Y VENDER');
                        $('#modalCreateRapido').modal('hide');
                        if (typeof toastr !== 'undefined') toastr.success('Registrado con éxito.');

                        // 2. Escribir en el buscador principal del POS
                        const $buscador = $('#busqueda_medicamento');
                        $buscador.val(terminoABuscar);

                        // 3. Disparar la búsqueda
                        if (window.buscarMedicamentos) {
                            window.buscarMedicamentos();
                        }

                        // 4. LA MAGIA: Esperar un momento a que carguen los resultados y simular teclas
                        setTimeout(() => {
                            // Disparamos Flecha Abajo (40) para seleccionar el primer item
                            let down = jQuery.Event("keydown", {
                                which: 40
                            });
                            $buscador.trigger(down);

                            // Esperamos un instante y disparamos Enter (13)
                            setTimeout(() => {
                                let enter = jQuery.Event("keydown", {
                                    which: 13
                                });
                                $buscador.trigger(enter);
                            }, 200);
                        }, 600); // Pausa de 600ms para dar tiempo al servidor de responder
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> GUARDAR Y VENDER');
                    if (xhr.status === 422) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, function(key, val) {
                            msg += '• ' + val[0] + '\n';
                        });
                        alert('Error:\n' + msg);
                    }
                }
            });
        });

        $('#rapido_nombre').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btn-save-rapido').click();
            }
        });
    });
</script>