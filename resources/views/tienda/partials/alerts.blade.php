@php
    $isAdmin = request()->is('tienda-admin*') || request()->routeIs('tienda.admin.*') || request()->is('admin*') || request()->is('sucursales*') || request()->is('dashboard*');
@endphp

@if ($isAdmin)
    @foreach (['success', 'warning', 'error'] as $key)
        @if (session($key))
            <div class="alert alert-{{ $key === 'error' ? 'danger' : ($key === 'warning' ? 'warning' : 'success') }} alert-dismissible fade show" role="alert">
                <strong>{{ $key === 'error' ? 'Error' : ($key === 'warning' ? 'Atención' : 'Éxito') }}:</strong> {!! session($key) !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Revisa la información ingresada:</strong>
            <ul class="mb-0 mt-1 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@else
    @foreach ([
        'success' => [
            'border' => 'border-emerald-500',
            'text' => 'text-emerald-600',
            'bg' => 'bg-emerald-50/50',
            'title' => 'Éxito',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        ],
        'warning' => [
            'border' => 'border-amber-500',
            'text' => 'text-amber-600',
            'bg' => 'bg-amber-50/50',
            'title' => 'Atención',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
        ],
        'error' => [
            'border' => 'border-rose-500',
            'text' => 'text-rose-600',
            'bg' => 'bg-rose-50/50',
            'title' => 'Error',
            'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
        ]
    ] as $key => $alert)
        @if (session($key))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3500)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="position-fixed top-50 start-50 translate-middle p-3"
                 style="z-index: 9999; width: 90%; max-width: 380px;"
                 x-cloak>
                <div class="bg-white border-top border-4 {{ $alert['border'] }} rounded-2xl shadow-2xl p-4 border border-slate-100 text-center">
                    <div class="d-inline-flex p-3 {{ $alert['bg'] }} rounded-full {{ $alert['text'] }} mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.8rem; height: 1.8rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $alert['icon'] }}"></path>
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-800 mb-1" style="font-size: 1.05rem;">{{ $alert['title'] }}</h4>
                    <p class="text-xs text-slate-600 mb-0 leading-relaxed">{!! session($key) !!}</p>
                    <div class="mt-3">
                        <button type="button" @click="show = false" class="btn btn-sm btn-light border-slate-200 rounded-xl px-4 py-1.5 text-xs font-semibold text-slate-600 active:scale-95 transition-all">Cerrar</button>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @if ($errors->any() && (!isset($hideGlobalErrors) || !$hideGlobalErrors))
        <div class="alert alert-danger rounded-2xl border-rose-100 bg-rose-50/30 text-rose-900 p-4 mb-4 small">
            <strong class="text-rose-800">Revisa la información ingresada:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
