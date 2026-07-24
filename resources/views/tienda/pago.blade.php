@extends('tienda.layout')

@section('title', 'Pagar pedido ' . $pedido->codigo)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="store-card bg-white p-4 text-center">
            <h1 class="h4 mb-3">Pagar pedido</h1>
            <p class="text-muted mb-2">Codigo: <strong>{{ $pedido->codigo }}</strong></p>
            <p class="text-muted mb-3">Sucursal: {{ $pedido->sucursal->nombre ?? '-' }}</p>

            <hr>

            <div class="mb-3">
                <span class="text-muted">Total a pagar</span>
                <div class="price display-5">S/ {{ number_format((float) $pedido->total, 2) }}</div>
            </div>

            <div class="my-3">
                <div class="quick-banner text-start">
                    <strong>Pago seguro con Culqi</strong>
                    <p class="text-muted small mb-0 mt-1">Aceptamos tarjetas, Yape, PagoEfectivo, billeteras moviles y Cuotealo.<br>Tus datos estan protegidos y no almacenamos informacion de tu tarjeta.</p>
                </div>
            </div>

            <div id="culqiPreparando" class="my-3">
                <div class="spinner-border text-success mb-2" role="status"></div>
                <p class="text-muted small mb-0">Preparando metodos de pago...</p>
            </div>

            <div id="culqiWarning" class="alert alert-danger small text-start mb-3 d-none">
                <strong>Error de configuracion:</strong> <span id="culqiWarningMsg"></span><br>
                <small id="culqiWarningHint"></small>
            </div>

            <button id="btnPagar" class="btn btn-store btn-lg w-100 d-none" disabled>
                Pagar S/ {{ number_format((float) $pedido->total, 2) }}
            </button>

            <div id="pagoCargando" class="d-none mt-3">
                <div class="spinner-border text-success mb-2" role="status"></div>
                <p id="pagoMsg" class="text-muted small mb-0">Procesando tu pago...</p>
            </div>

            <div id="pagoError" class="alert alert-danger mt-3 d-none"></div>
            <div id="pagoPendiente" class="alert alert-info mt-3 d-none"></div>

            <div class="mt-3">
                <a href="{{ route('tienda.pedidos.show', $pedido->codigo) }}" class="text-muted small">
                    Volver al pedido
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.culqi.com/js/v4"></script>
<script>
(function () {
    var btnPagar = document.getElementById('btnPagar');
    var culqiPreparando = document.getElementById('culqiPreparando');
    var culqiWarning = document.getElementById('culqiWarning');
    var culqiWarningMsg = document.getElementById('culqiWarningMsg');
    var pagoCargando = document.getElementById('pagoCargando');
    var pagoMsg = document.getElementById('pagoMsg');
    var pagoError = document.getElementById('pagoError');
    var pagoPendiente = document.getElementById('pagoPendiente');

    var publicKey = @json($publicKey);
    var rsaId = @json($rsaId);
    var rsaPublicKey = @json($rsaPublicKey);
    var procesarUrl = '{{ route('tienda.pago.procesar', $pedido->codigo) }}';
    var crearOrdenUrl = '{{ route('tienda.pago.crearOrden', $pedido->codigo) }}';
    var csrf = @json(csrf_token());
    var amountInCents = {{ (int) round($pedido->total * 100) }};

    var orderId = null;

    if (!publicKey || publicKey.includes('tu_public_key')) {
        culqiPreparando.classList.add('d-none');
        btnPagar.classList.add('d-none');
        pagoError.classList.remove('d-none');
        pagoError.textContent = 'Error: La llave publica de Culqi no esta configurada. Revisa .env';
        return;
    }

    Culqi.publicKey = publicKey;

    var tieneRSA = rsaId && rsaPublicKey && rsaPublicKey.indexOf('tu_rsa') === -1;

    function configurarCulqi(conOrder, orderIdVal) {
        if (conOrder && orderIdVal) {
            var sett = { 
                title: 'Farmacia Online', 
                currency: 'PEN',
                amount: amountInCents,
                order: orderIdVal 
            };
            if (tieneRSA) {
                sett.xculqirsaid = rsaId;
                sett.rsapublickey = rsaPublicKey;
            }
            Culqi.settings(sett);
            Culqi.options({
                lang: 'auto',
                installments: false,
                paymentMethods: {
                    tarjeta: true,
                    yape: true,
                    billetera: true,
                    bancaMovil: true,
                    agente: true,
                    cuotealo: true,
                },
            });
        } else {
            Culqi.settings({
                title: 'Farmacia Online',
                currency: 'PEN',
                amount: amountInCents,
            });
            Culqi.options({
                lang: 'auto',
                installments: false,
                paymentMethods: {
                    tarjeta: true,
                    yape: true,
                    billetera: true,
                    bancaMovil: true,
                    agente: true,
                    cuotealo: true,
                },
            });
        }
    }

    fetch(crearOrdenUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
        },
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
            return;
        }
        if (data.success && data.order_id) {
            orderId = data.order_id;
            configurarCulqi(true, orderId);
            culqiPreparando.classList.add('d-none');
            btnPagar.classList.remove('d-none');
            btnPagar.disabled = false;
        } else {
            configurarCulqi(false, null);
            culqiPreparando.classList.add('d-none');
            btnPagar.classList.remove('d-none');
            btnPagar.disabled = false;

            if (data.error) {
                culqiWarning.classList.remove('d-none');
                culqiWarningMsg.textContent = data.error + '. Solo pago con tarjeta disponible.';
                var hint = document.getElementById('culqiWarningHint');
                if (hint) {
                    hint.textContent = tieneRSA
                        ? 'Verifica en CulqiPanel que Yape, PagoEfectivo, billeteras y Cuotealo esten activados para tu comercio.'
                        : 'Para habilitar Yape y otros metodos, genera tus llaves RSA en CulqiPanel y agregalas en el archivo .env.';
                }
            }
        }
    })
    .catch(function () {
        configurarCulqi(false, null);
        culqiPreparando.classList.add('d-none');
        btnPagar.classList.remove('d-none');
        btnPagar.disabled = false;
        culqiWarning.classList.remove('d-none');
        culqiWarningMsg.textContent = 'No se pudieron cargar todos los metodos de pago. Solo pago con tarjeta disponible.';
        var hint = document.getElementById('culqiWarningHint');
        if (hint) {
            hint.textContent = tieneRSA
                ? 'Verifica en CulqiPanel que Yape, PagoEfectivo, billeteras y Cuotealo esten activados para tu comercio.'
                : 'Para habilitar Yape y otros metodos, genera tus llaves RSA en CulqiPanel y agregalas en el archivo .env.';
        }
    });

    btnPagar.addEventListener('click', function (e) {
        e.preventDefault();
        if (Culqi.token) {
            Culqi.close();
            setTimeout(function () { Culqi.open(); }, 300);
        } else {
            Culqi.open();
        }
    });

    window.culqi = function () {
        if (Culqi.token) {
            var tokenId = Culqi.token.id;

            btnPagar.classList.add('d-none');
            pagoCargando.classList.remove('d-none');
            pagoMsg.textContent = 'Procesando tu pago...';
            pagoError.classList.add('d-none');
            pagoPendiente.classList.add('d-none');

            fetch(procesarUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ token_id: tokenId }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    btnPagar.classList.remove('d-none');
                    pagoCargando.classList.add('d-none');
                    pagoError.classList.remove('d-none');
                    pagoError.textContent = data.message || 'Error al procesar el pago.';
                    Culqi.close();
                }
            })
            .catch(function () {
                btnPagar.classList.remove('d-none');
                pagoCargando.classList.add('d-none');
                pagoError.classList.remove('d-none');
                pagoError.textContent = 'Error de conexion. Intentalo nuevamente.';
                Culqi.close();
            });

        } else if (Culqi.order) {
            var order = Culqi.order;

            btnPagar.classList.add('d-none');
            pagoCargando.classList.remove('d-none');
            pagoError.classList.add('d-none');
            pagoPendiente.classList.add('d-none');

            if (order.state === 'paid') {
                pagoMsg.textContent = 'Verificando pago...';

                fetch(procesarUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order_id: order.id }),
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        btnPagar.classList.remove('d-none');
                        pagoCargando.classList.add('d-none');
                        pagoError.classList.remove('d-none');
                        pagoError.textContent = data.message || 'Error al verificar pago.';
                    }
                })
                .catch(function () {
                    btnPagar.classList.remove('d-none');
                    pagoCargando.classList.add('d-none');
                    pagoError.classList.remove('d-none');
                    pagoError.textContent = 'Error de conexion.';
                });
            } else {
                pagoCargando.classList.add('d-none');
                pagoPendiente.classList.remove('d-none');
                pagoPendiente.textContent = 'Tu pago esta siendo procesado. Te notificaremos cuando se confirme. Puedes revisar el estado de tu pedido en "Mis Pedidos".';
            }

        } else {
            pagoError.classList.remove('d-none');
            pagoError.textContent = Culqi.error ? Culqi.error.user_message : 'Pago cancelado o rechazado.';
            Culqi.close();
        }
    };
})();
</script>
@endpush
