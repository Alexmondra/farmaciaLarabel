<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Selecciona una sucursal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- 1. Fuente Google Fonts (Inter) para un look más limpio --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    {{-- 2. Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- 3. FontAwesome para iconos --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            /* Fondo suave grisáceo, o puedes poner una imagen de fondo sutil */
            background-color: #f8f9fa;
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Títulos */
        .main-title {
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }

        .sub-title {
            font-weight: 400;
            color: #6b7280;
        }

        /* Estilo de la Tarjeta de Sucursal */
        .btn-sucursal {
            text-decoration: none;
            color: inherit;
            display: block;
            border: none;
            background: transparent;
            width: 100%;
            padding: 0;
            transition: all 0.3s ease;
        }

        .sucursal-card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Icono de la izquierda */
        .icon-box {
            width: 50px;
            height: 50px;
            background-color: #eff6ff;
            /* Azul muy suave */
            color: #3b82f6;
            /* Azul moderno */
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        /* Contenido texto */
        .card-info h5 {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 4px;
            color: #374151;
        }

        .card-info p {
            font-size: 0.875rem;
            color: #9ca3af;
            margin: 0;
        }

        .arrow-action {
            margin-left: auto;
            color: #d1d5db;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }

        /* === HOVER EFFECTS === */
        .btn-sucursal:hover .sucursal-card {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #bfdbfe;
            /* Borde azul suave */
        }

        .btn-sucursal:hover .icon-box {
            background-color: #3b82f6;
            color: #ffffff;
        }

        .btn-sucursal:hover .card-info h5 {
            color: #2563eb;
            /* Azul más oscuro al hover */
        }

        .btn-sucursal:hover .arrow-action {
            opacity: 1;
            transform: translateX(0);
            color: #3b82f6;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: #1f2937;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .developer-branding {
            opacity: 0.7;
            transition: opacity 0.3s ease;
            cursor: default;
        }

        .developer-branding:hover {
            opacity: 1;
        }

        .dev-logo {
            height: 24px;
            width: auto;
            filter: grayscale(100%);
            transition: all 0.3s ease;
            opacity: 0.8;
        }

        .developer-branding:hover .dev-logo {
            filter: grayscale(0%);
            transform: scale(1.05);
            opacity: 1;
        }
    </style>
</head>

<body>

    <div class="page-wrapper">

        {{-- Logo o Icono Principal --}}
        <div class="brand-logo">
            <i class="fas fa-store"></i>
        </div>

        <div class="text-center mb-5">
            <h1 class="h3 main-title">Bienvenido de nuevo</h1>
            <p class="sub-title">Selecciona la sucursal para iniciar operaciones</p>
        </div>

        {{-- Alertas --}}
        <div class="container" style="max-width: 900px;">
            @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            </div>
            @endif

            @error('sucursal_id')
            <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
            </div>
            @enderror

            {{-- Grid de Sucursales --}}
            <div class="row g-4 justify-content-center">
                @foreach ($sucursales as $sucursal)
                <div class="col-12 col-md-6 col-lg-5">
                    <form action="{{ route('sucursales.guardar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">

                        <button type="submit" class="btn-sucursal">
                            <div class="sucursal-card">
                                {{-- 1. Icono dinámico o estático --}}
                                <div class="icon-box">
                                    <i class="fas fa-building"></i>
                                </div>

                                {{-- 2. Información --}}
                                <div class="card-info text-start">
                                    <h5>{{ $sucursal->nombre }}</h5>
                                    @if (!empty($sucursal->direccion) || !empty($sucursal->ciudad))
                                    <p>
                                        <i class="fas fa-map-marker-alt me-1" style="font-size: 0.7em;"></i>
                                        {{ Str::limit($sucursal->direccion ?? 'Ubicación principal', 25) }}
                                    </p>
                                    @else
                                    <p>Sucursal Principal</p>
                                    @endif
                                </div>

                                {{-- 3. Flecha de acción (aparece al hover) --}}
                                <div class="arrow-action">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            {{-- FOOTER CON BRANDING --}}
            <div class="mt-5 text-center">
                <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                    &copy; {{ date('Y') }} Sistema de Gestión Interno
                </p>

                <div class="d-flex justify-content-center align-items-center gap-2 developer-branding">
                    <span class="text-muted small fw-bold" style="font-size: 0.7rem; letter-spacing: 1px; text-transform: uppercase;">
                        S1NT4X SYSTEM
                    </span>

                    {{-- Asegúrate que el archivo esté en public/logo_s1nt4x.png --}}
                    <img src="{{ asset('logo_desarrolladora.png') }}"
                        alt="S1nt4x System"
                        class="dev-logo"
                        title="S1nt4x System Development">
                </div>
            </div>
        </div>
    </div>

</body>

</html>