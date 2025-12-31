@extends('adminlte::page')
@section('title', 'Nuevo Proveedor')
@section('content_header')
<h1><i class="fas fa-plus-circle mr-2"></i>Nuevo Proveedor</h1>
@stop
@section('content')
@include('inventario.proveedores._form', [
'route' => route('inventario.proveedores.store'),
'method' => 'POST',
'submitText' => 'Guardar',
'proveedor' => new \App\Models\Compras\Proveedor(['activo' => true])
])
@stop

@section('css')
<style>
    /* === MODO OSCURO (Activado por la clase AdminLTE 'dark-mode' en el body) === */
    body.dark-mode .card.shadow-sm {
        background-color: #343a40 !important;
        border-color: #495057 !important;
        color: #d1d9e0 !important;
    }

    body.dark-mode .card-footer.bg-white {
        background-color: #3e444a !important;
        border-top-color: #495057 !important;
    }

    /* Estilos para inputs en modo oscuro */
    body.dark-mode .form-control {
        background-color: #2b3035;
        color: #d1d9e0;
        border-color: #5d6874;
    }

    body.dark-mode .form-control::placeholder {
        color: #9da5af;
    }

    body.dark-mode .form-control:focus {
        background-color: #2b3035;
        color: #d1d9e0;
        border-color: #6c757d;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }

    /* Etiqueta de Activo/Inactivo y texto atenuado */
    body.dark-mode .custom-control-label {
        color: #d1d9e0 !important;
    }

    body.dark-mode .text-muted {
        color: #a0aec0 !important;
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ruc = document.getElementById('ruc');
        const tel = document.getElementById('telefono');
        const form = document.querySelector('form');
        const submitBtn = form ? form.querySelector('button[type="submit"], input[type="submit"]') : null;

        const rucRegex = /^(10|15|16|17|20)\d{9}$/; // RUC Perú típico
        const telRegex = /^\d{9}$/; // Celular 9 dígitos

        // --- FUNCIONES AUXILIARES ---

        // 1. Fuerza que solo haya números (elimina letras/símbolos al instante)
        function soloNumeros(e) {
            // Reemplaza cualquier caracter que NO sea dígito (\D) por vacío
            e.target.value = e.target.value.replace(/\D/g, '');

            // Al escribir, limpiamos los errores visuales para que el usuario pueda corregir
            e.target.classList.remove('is-invalid');

            if (e.target.id === 'telefono') {
                const box = document.getElementById('telefono_js_error');
                if (box) box.classList.add('d-none');
            }

            // Rehabilitamos el botón por si estaba bloqueado
            if (submitBtn) submitBtn.disabled = false;
        }

        function setInvalid(el, msg) {
            if (!el) return;
            el.classList.add('is-invalid');
            if (el.id === 'telefono') {
                const box = document.getElementById('telefono_js_error');
                if (box) {
                    box.classList.remove('d-none');
                    box.querySelector('span').textContent = msg;
                }
            }
            if (submitBtn) submitBtn.disabled = true;
        }

        function clearInvalid(el) {
            if (!el) return;
            el.classList.remove('is-invalid');
            if (el.id === 'telefono') {
                const box = document.getElementById('telefono_js_error');
                if (box) box.classList.add('d-none');
            }
            if (submitBtn) submitBtn.disabled = false;
        }

        // --- LÓGICA PARA TELÉFONO ---
        if (tel) {
            // A. Solo permite números mientras escribes
            tel.addEventListener('input', soloNumeros);

            // B. Evitar que peguen un RUC en el teléfono
            tel.addEventListener('paste', function(e) {
                // Obtenemos el texto pegado
                let text = (e.clipboardData || window.clipboardData).getData('text') || '';
                // Limpiamos lo que no sea número
                let digits = text.replace(/\D/g, '');

                // Si son 11 dígitos y parece un RUC, bloqueamos
                if (digits.length === 11 && rucRegex.test(digits)) {
                    e.preventDefault();
                    tel.value = ''; // Borramos
                    setInvalid(tel, 'Eso parece un RUC (11 dígitos). Aquí va un celular de 9 dígitos.');
                }
            });

            // C. Validación final al salir del campo (blur)
            tel.addEventListener('blur', function() {
                const v = tel.value; // Ya sabemos que son solo números gracias al evento 'input'
                if (!v) {
                    clearInvalid(tel);
                    return;
                } // teléfono es opcional

                if (!telRegex.test(v)) {
                    setInvalid(tel, 'Teléfono inválido: debe tener 9 dígitos.');
                } else {
                    clearInvalid(tel);
                }
            });
        }

        // --- LÓGICA PARA RUC ---
        if (ruc) {
            // A. Solo permite números mientras escribes
            ruc.addEventListener('input', soloNumeros);

            // B. Validación al salir del campo
            ruc.addEventListener('blur', function() {
                const v = ruc.value;
                if (!v) return; // Si está vacío, lo atrapa el 'required' del HTML

                if (!rucRegex.test(v)) {
                    ruc.classList.add('is-invalid');
                    // Nota: No mostramos mensaje JS específico para RUC porque el input ya se pone rojo
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    ruc.classList.remove('is-invalid');
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        // --- VALIDACIÓN FINAL ANTES DE ENVIAR ---
        if (form) {
            form.addEventListener('submit', function(e) {
                const r = ruc ? ruc.value : '';
                const t = tel ? tel.value : '';

                let ok = true;

                // Verificamos RUC
                if (ruc && r && !rucRegex.test(r)) ok = false;

                // Verificamos Teléfono
                if (tel && t && !telRegex.test(t)) ok = false;

                if (!ok) {
                    e.preventDefault(); // Detiene el envío
                    // Forzamos el estado visual de error
                    if (ruc && !rucRegex.test(r)) ruc.classList.add('is-invalid');
                    if (tel && !telRegex.test(t)) setInvalid(tel, 'Revise el número de celular.');

                    if (submitBtn) submitBtn.disabled = false; // Dejamos el botón activo para que intenten de nuevo
                }
            });
        }
    });
</script>
@stop