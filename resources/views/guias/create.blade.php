@extends('adminlte::page')

@section('title', 'Nueva Guía de Remisión')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-navy font-weight-bold">
        <i class="fas fa-dolly text-teal mr-2"></i> Nueva Guía de Remisión
    </h1>
    <a href="{{ route('guias.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-arrow-left mr-2"></i> Volver al listado
    </a>
</div>
@stop

@section('content')
<div class="pb-5"> {{-- Padding bottom para que los botones no peguen al borde --}}

    {{-- Formulario Principal --}}
    {{-- El ID 'formGuia' es importante para el botón de guardar --}}
    <form action="{{ route('guias.store') }}" method="POST" id="formGuia" autocomplete="off">
        @csrf
        <input type="hidden" name="venta_id" value="{{ $venta->id ?? '' }}">
        @include('guias._form')

        {{-- BARRA DE ACCIONES (Botones grandes al final) --}}
        <div class="glass-panel p-4 mt-4 text-right bg-white">
            <a href="{{ route('guias.index') }}" class="btn btn-lg btn-secondary rounded-pill px-5 mr-3">
                Cancelar
            </a>

            <button type="button" class="btn btn-lg btn-gradient-teal rounded-pill px-5 shadow-lg hover-scale" onclick="confirmarGuardado()">
                <i class="fas fa-save mr-2"></i> Generar Guía
            </button>
        </div>

    </form>
</div>
@stop

@section('js')
<script>
    // 1. Manejo de Errores de Validación (después del reload)
    document.addEventListener('DOMContentLoaded', function() {
        const errorData = '{!! json_encode($errors->all()) !!}';

        // Ahora parseamos la cadena JSON segura
        const laravelErrors = JSON.parse(errorData);

        if (laravelErrors.length > 0) {
            const htmlErrors = `<ul>${laravelErrors.map(e => `<li>${e}</li>`).join('')}</ul>`;
            Swal.fire({
                title: '¡Error de Validación!',
                icon: 'error',
                html: htmlErrors,
                confirmButtonColor: '#e74c3c', // Rojo
                confirmButtonText: 'Entendido'
            });
        }
    });

    function confirmarGuardado() {
        // 1. LIMPIEZA INICIAL
        // Quitamos las marcas rojas de intentos anteriores
        $('.is-invalid').removeClass('is-invalid');
        let errores = [];

        // =======================================================
        // 2. VALIDACIÓN DE ITEMS (Lo más importante)
        // =======================================================
        // Usamos la variable global 'itemsGuia' que tienes en scripts.blade.php
        let itemsJson = $('#inputItemsJson').val();
        let items = [];
        try {
            items = JSON.parse(itemsJson);
        } catch (e) {
            items = [];
        }

        if (items.length === 0) {
            errores.push("⚠️ No has agregado ningún producto a la guía.");
        }

        // =======================================================
        // 3. VALIDACIÓN DE DESTINO (Llegada)
        // =======================================================

        // Destinatario
        if ($('#inputDestinatario').val().trim() === '') {
            $('#inputDestinatario').addClass('is-invalid');
            errores.push("Falta la <b>Razón Social</b> del destinatario.");
        }

        // Documento Destinatario
        if ($('#inputDocDestinatario').val().trim() === '') {
            $('#inputDocDestinatario').addClass('is-invalid');
            errores.push("Falta el <b>RUC/DNI</b> del destinatario.");
        }

        // UBIGEO (Tu requisito principal: 6 dígitos obligatorios)
        let ubigeo = $('#inputUbigeo').val().trim();
        if (ubigeo.length !== 6 || isNaN(ubigeo)) {
            $('#inputUbigeo').addClass('is-invalid');
            errores.push("El <b>Ubigeo</b> debe tener exactamente 6 números.");
        }

        // Dirección
        if ($('#inputDireccion').val().trim().length < 5) {
            $('#inputDireccion').addClass('is-invalid');
            errores.push("La <b>Dirección de Llegada</b> es obligatoria y detallada.");
        }

        // =======================================================
        // 4. VALIDACIÓN DE TRASLADO (Público vs Privado)
        // =======================================================
        let modalidad = $('#selectModalidad').val();

        if (modalidad === '01') {
            // ---> PÚBLICO
            let rucTrans = $('input[name="doc_transportista_numero"]').val().trim();

            // Validar que no esté vacío
            if (rucTrans === '') {
                $('input[name="doc_transportista_numero"]').addClass('is-invalid');
                errores.push("Falta el RUC del Transportista.");
            }
            // Validar longitud EXACTA de 11
            else if (rucTrans.length !== 11) {
                $('input[name="doc_transportista_numero"]').addClass('is-invalid');
                errores.push("El <b>RUC del Transportista</b> debe tener 11 dígitos exactos.");
            }

            if ($('input[name="razon_social_transportista"]').val().trim() === '') {
                $('input[name="razon_social_transportista"]').addClass('is-invalid');
                errores.push("Falta la Razón Social del transportista.");
            }

        } else {
            // ---> PRIVADO
            if ($('input[name="placa_vehiculo"]').val().trim() === '') {
                $('input[name="placa_vehiculo"]').addClass('is-invalid');
                errores.push("Falta la Placa del Vehículo.");
            }

            let dniChofer = $('input[name="doc_chofer_numero"]').val().trim();

            if (dniChofer === '') {
                $('input[name="doc_chofer_numero"]').addClass('is-invalid');
                errores.push("Falta el DNI del Conductor.");
            }
            // Validar longitud EXACTA de 8
            else if (dniChofer.length !== 8) {
                $('input[name="doc_chofer_numero"]').addClass('is-invalid');
                errores.push("El <b>DNI del Conductor</b> debe tener 8 dígitos exactos.");
            }

            if ($('input[name="nombre_chofer"]').val().trim() === '') {
                $('input[name="nombre_chofer"]').addClass('is-invalid');
                errores.push("Falta el Nombre del Conductor.");
            }
        }
        // =======================================================
        // 5. VALIDACIÓN DE PESO
        // =======================================================
        let peso = parseFloat($('input[name="peso_bruto"]').val());
        if (isNaN(peso) || peso <= 0) {
            $('input[name="peso_bruto"]').addClass('is-invalid');
            errores.push("El <b>Peso Bruto</b> debe ser mayor a 0.");
        }


        // =======================================================
        // 6. RESULTADO FINAL
        // =======================================================

        if (errores.length > 0) {
            // Si hay errores, mostramos la alerta y NO enviamos
            Swal.fire({
                title: 'Faltan Datos Importantes',
                icon: 'warning',
                html: `<div class="text-left small">${errores.join('<br>')}</div>`,
                confirmButtonColor: '#f39c12',
                confirmButtonText: 'Entendido, voy a corregir'
            });
        } else {
            // Si todo está OK, procedemos con la confirmación de guardado
            Swal.fire({
                title: '¿Generar Guía?',
                text: "Verifique que todos los datos sean correctos.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20c997',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Generar',
                cancelButtonText: 'Revisar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formGuia').submit();
                }
            })
        }
    }
</script>
@stop

@section('css')
<style>
    /* Ajuste para el botón guardar */
    .btn-gradient-teal {
        background: linear-gradient(135deg, #20c997 0%, #0c8f6b 100%);
        color: white;
        border: none;
    }

    .hover-scale:hover {
        transform: scale(1.02);
        transition: transform 0.2s;
    }

    .form-control-futuristic.is-invalid {
        border-color: #e74c3c !important;
        background-image: none !important;
        /* Quita el icono de error default de bootstrap si molesta */
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.15) !important;
    }
</style>
@stop