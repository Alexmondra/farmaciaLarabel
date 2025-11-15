<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Selecciona una sucursal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap simple por CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f3f4f6;
        }

        .sucursal-card {
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
            border-radius: 0.75rem;
        }

        .sucursal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.75rem 1.5rem rgba(15, 23, 42, 0.15);
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>

    <div class="page-wrapper">
        <div class="container" style="max-width: 900px;">
            <div class="text-center mb-4">
                <h1 class="h4 fw-bold mb-2">Selecciona una sucursal</h1>
                <p class="text-muted mb-0">
                    Elige con qué sucursal quieres trabajar.
                </p>
            </div>

            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            @error('sucursal_id')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
            @enderror

            <div class="row g-3">
                @foreach ($sucursales as $sucursal)
                <div class="col-12 col-md-4">
                    <form action="{{ route('sucursales.guardar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">

                        <button type="submit" class="btn p-0 w-100 text-start" style="border: none; background: none;">
                            <div class="card sucursal-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-1">
                                        {{ $sucursal->nombre }}
                                    </h5>

                                    @if (!empty($sucursal->direccion) || !empty($sucursal->ciudad))
                                    <p class="card-text text-muted small mb-0">
                                        {{ $sucursal->direccion ?? '' }}
                                        @if(!empty($sucursal->direccion) && !empty($sucursal->ciudad))
                                        ·
                                        @endif
                                        {{ $sucursal->ciudad ?? '' }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</body>

</html>