<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Comprobantes</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0062cc 0%, #00d2ff 100%);
            /* Degradado Azul Farmacia */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card-consulta {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 550px;
        }

        .card-header-custom {
            background: #fff;
            padding: 30px 20px 10px 20px;
            text-align: center;
            border-bottom: none;
        }

        .icon-header {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0062cc 0%, #00d2ff 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 30px;
            box-shadow: 0 5px 15px rgba(0, 98, 204, 0.3);
        }

        .form-control {
            border-radius: 10px;
            height: 45px;
            border: 1px solid #e0e0e0;
            background-color: #f8f9fa;
            font-size: 14px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #00d2ff;
            background-color: #fff;
        }

        label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .btn-buscar {
            background: linear-gradient(135deg, #0062cc 0%, #00d2ff 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0, 98, 204, 0.4);
            transition: transform 0.2s;
        }

        .btn-buscar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 98, 204, 0.5);
            color: white;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div style="width: 100%; max-width: 550px;">

        <div class="card card-consulta">
            <div class="card-header-custom">
                <div class="icon-header">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h4 style="font-weight: 600; color: #333;">Consulta tu Comprobante</h4>
                <p class="text-muted small">Ingresa los datos de tu ticket físico para descargar el PDF.</p>
            </div>

            <div class="card-body p-4">

                {{-- MENSAJES DE RESPUESTA --}}
                @if(session('error'))
                <div class="alert alert-danger" style="border-radius: 10px; font-size: 14px;">
                    <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                </div>
                @endif

                @if(session('exito'))
                <div class="text-center py-4 animated fadeIn">
                    <div class="mb-3 text-success">
                        <i class="fas fa-check-circle fa-4x"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark">¡Encontrado!</h5>
                    <p class="text-muted small mb-4">Tu documento está listo.</p>

                    <a href="{{ session('url_descarga') }}" target="_blank" class="btn btn-success btn-block btn-lg shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-download mr-2"></i> DESCARGAR PDF
                    </a>

                    <hr class="my-4">
                    <a href="{{ route('publico.buscar_vista') }}" class="text-primary font-weight-bold small" style="text-decoration: none;">
                        <i class="fas fa-redo mr-1"></i> Realizar otra consulta
                    </a>
                </div>
                @else
                {{-- FORMULARIO DE BÚSQUEDA --}}
                <form action="{{ route('publico.buscar_post') }}" method="POST">
                    @csrf

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label>Tipo Documento</label>
                            <select name="tipo" class="form-control">
                                <option value="BOLETA">BOLETA</option>
                                <option value="FACTURA">FACTURA</option>
                            </select>
                        </div>
                        <div class="col-6 form-group">
                            <label>Fecha Emisión</label>
                            <input type="date" name="fecha" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-5 form-group">
                            <label>Serie (Ej: B001)</label>
                            <input type="text" name="serie" class="form-control text-uppercase" placeholder="B001" required>
                        </div>
                        <div class="col-7 form-group">
                            <label>Número (Ej: 450)</label>
                            <input type="number" name="numero" class="form-control" placeholder="450" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Monto Total del Ticket (S/)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="border-radius: 10px 0 0 10px;">S/</span>
                            </div>
                            <input type="number" step="0.01" name="total" class="form-control border-left-0" style="border-radius: 0 10px 10px 0;" placeholder="0.00" required>
                        </div>
                        <small class="text-muted ml-1" style="font-size: 11px;">* Debe coincidir exactamente con el total impreso.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-buscar mt-4">
                        <i class="fas fa-search mr-2"></i> BUSCAR COMPROBANTE
                    </button>
                </form>
                @endif
            </div>
        </div>

        <div class="footer-text">
            &copy; {{ date('Y') }} Sistema de Farmacia. Todos los derechos reservados.
        </div>
    </div>

</body>

</html>