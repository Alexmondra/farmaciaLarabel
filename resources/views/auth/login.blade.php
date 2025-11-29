<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia - Acceso Seguro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f1f5f9;
            /* Slate-100: Suave y profesional */
        }

        /* Corrección de color para autocompletado de Chrome */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #f8fafc inset !important;
            -webkit-text-fill-color: #475569 !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* PERSONAJE */
        .avatar-svg {
            width: 120px;
            height: 120px;
            position: absolute;
            top: -60px;
            /* Sale por arriba */
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .arm {
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-origin: bottom center;
        }

        /* Posición inicial de las manos (escondidas abajo) */
        .arm-left {
            transform: translateY(110px) rotate(-10deg);
        }

        .arm-right {
            transform: translateY(110px) rotate(10deg);
        }

        /* Estado: Taparse los ojos */
        .cover-eyes .arm-left {
            transform: translateY(15px) translateX(5px) rotate(10deg);
        }

        .cover-eyes .arm-right {
            transform: translateY(15px) translateX(-5px) rotate(-10deg);
        }

        /* Estado: Mirar (Email) */
        .pupil {
            transition: transform 0.2s;
        }

        .looking .pupil {
            transform: translateY(2px);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4 py-12">

    <div class="relative w-full max-w-[380px] bg-white rounded-3xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] px-8 pb-10 pt-20 border border-slate-100 mt-8">

        <svg class="avatar-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
            <circle cx="60" cy="60" r="50" fill="#ffedd5" stroke="#334155" stroke-width="3" />

            <path d="M10 60 Q 60 20 110 60 L 110 50 Q 60 10 10 50 Z" fill="#ffffff" stroke="#334155" stroke-width="3" />
            <path d="M56 35 h8 v8 h-8 z M60 31 v16" stroke="#0d9488" stroke-width="3" stroke-linecap="round" />

            <g id="eyes">
                <circle cx="45" cy="75" r="5" fill="#1e293b" />
                <circle cx="75" cy="75" r="5" fill="#1e293b" />
                <circle class="pupil" cx="47" cy="73" r="1.5" fill="white" />
                <circle class="pupil" cx="77" cy="73" r="1.5" fill="white" />
            </g>

            <path id="mouth" d="M45 90 Q 60 100 75 90" fill="none" stroke="#be123c" stroke-width="3" stroke-linecap="round" />

            <g class="arm arm-left">
                <path d="M10 80 Q 20 50 45 60 Q 30 90 20 100 Z" fill="#e2e8f0" stroke="#334155" stroke-width="3" />
            </g>
            <g class="arm arm-right">
                <path d="M110 80 Q 100 50 75 60 Q 90 90 100 100 Z" fill="#e2e8f0" stroke="#334155" stroke-width="3" />
            </g>
        </svg>

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-slate-700">Bienvenido</h2>
            <p class="text-sm text-slate-400">Sistema de Farmacia</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase ml-2">Correo Electrónico</label>
                <div class="relative">
                    <input id="email" type="email" name="email" required autofocus
                        class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all text-slate-600 font-medium placeholder-slate-300"
                        placeholder="ejemplo@farmacia.com">
                </div>
                @error('email')
                <span class="text-red-500 text-xs ml-2">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase ml-2">Contraseña</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required
                        class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all text-slate-600 font-medium placeholder-slate-300 tracking-wider"
                        placeholder="••••••••">
                </div>
                @error('password')
                <span class="text-red-500 text-xs ml-2">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm px-1">
                <label class="flex items-center text-slate-500 cursor-pointer hover:text-slate-700 transition">
                    <input type="checkbox" name="remember" class="mr-2 w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500">
                    Recuérdame
                </label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-teal-600 font-semibold hover:text-teal-700">¿Ayuda?</a>
                @endif
            </div>

            <button type="submit" class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl shadow-lg shadow-teal-600/20 transform active:scale-[0.98] transition-all duration-200 text-lg">
                INGRESAR
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Gestión Segura v1.0</p>
        </div>
    </div>

    <script>
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const svgContainer = document.querySelector('.avatar-svg');

        // Email (Mirar)
        emailInput.addEventListener('focus', () => {
            svgContainer.classList.add('looking');
        });
        emailInput.addEventListener('blur', () => {
            svgContainer.classList.remove('looking');
        });

        // Password (Tapar ojos)
        passwordInput.addEventListener('focus', () => {
            svgContainer.classList.remove('looking');
            svgContainer.classList.add('cover-eyes');
        });
        passwordInput.addEventListener('blur', () => {
            svgContainer.classList.remove('cover-eyes');
        });
    </script>
</body>

</html>