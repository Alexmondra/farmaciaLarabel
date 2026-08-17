@extends('tienda.layout')

@section('title', 'Nuestras Sucursales')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Diseño dividido responsivo */
    .sucursales-split {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 1.5rem;
        min-height: calc(100vh - 220px);
    }
    
    .sucursales-panel {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        padding-right: 0.5rem;
    }
    
    /* Personalización del scrollbar en el panel */
    .sucursales-panel::-webkit-scrollbar {
        width: 6px;
    }
    .sucursales-panel::-webkit-scrollbar-track {
        background: transparent;
    }
    .sucursales-panel::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 99px;
    }

    .sucursales-map-container {
        height: calc(100vh - 220px);
        border-radius: 1.5rem;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.03);
    }
    
    #map_sucursales {
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    /* Tarjetas de sucursal premium */
    .sucursal-card-pub {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.25rem;
        padding: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .sucursal-card-pub:hover, .sucursal-card-pub.active {
        border-color: #0d9488;
        box-shadow: 0 10px 20px -8px rgba(13, 148, 136, 0.15);
        transform: translateY(-2px);
    }
    .sucursal-card-pub.active {
        background: #f0fdfa;
    }

    .sucursal-title-pub {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    .sucursal-card-pub.active .sucursal-title-pub {
        color: #0f766e;
    }

    .sucursal-info-item {
        font-size: 0.85rem;
        color: #64748b;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .sucursal-info-item i {
        margin-top: 2px;
        color: #0d9488;
    }

    .badge-recojo-disponible {
        background: #f0fdfa;
        color: #0f766e;
        font-weight: 600;
        font-size: 0.72rem;
        padding: 0.25rem 0.6rem;
        border-radius: 99px;
        display: inline-block;
        margin-top: 0.75rem;
        border: 1px solid rgba(13, 148, 136, 0.1);
    }

    /* Estilos del Popup de Leaflet */
    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        padding: 0.25rem;
        font-family: inherit;
        border: 0;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1);
    }
    .leaflet-popup-content {
        margin: 12px 16px;
    }
    .popup-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
        font-size: 0.95rem;
    }
    .popup-address {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 8px;
    }
    .popup-action-btn {
        background: #0d9488;
        color: white !important;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 0.5rem;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
    }
    .popup-action-btn:hover {
        background: #0f766e;
    }

    /* Responsivo para móviles */
    @media (max-width: 991.98px) {
        .sucursales-split {
            grid-template-columns: 1fr;
            min-height: auto;
            gap: 1rem;
        }
        .sucursales-panel {
            max-height: none;
            overflow-y: visible;
            padding-right: 0;
            order: 2;
        }
        .sucursales-map-container {
            height: 320px;
            order: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1 text-slate-800">Nuestras Sucursales</h1>
    <p class="muted-copy mb-0">Ubica la farmacia más cercana para recoger tus pedidos o realizar consultas.</p>
</div>

<div class="sucursales-split">
    <!-- Panel izquierdo: Listado de sucursales -->
    <div class="sucursales-panel d-flex flex-column gap-3">
        <button id="btn-detectar-ubicacion" class="btn btn-outline-teal d-flex align-items-center justify-content-center gap-2 py-2.5 px-3 rounded-xl mb-1 shadow-sm transition-all" style="border: 1.5px solid #0d9488; color: #0d9488; background: transparent; font-weight: 600; font-size: 0.85rem;">
            <i class="fas fa-location-arrow"></i>
            <span>Buscar sucursal más cercana</span>
        </button>

        @forelse($sucursales as $sucursal)
            <div class="sucursal-card-pub" 
                 id="card-{{ $sucursal->id }}" 
                 onclick="focusSucursal({{ $sucursal->id }}, {{ $sucursal->latitud ?? 'null' }}, {{ $sucursal->longitud ?? 'null' }})">
                
                <h3 class="sucursal-title-pub">{{ $sucursal->nombre }}</h3>
                
                <div class="sucursal-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $sucursal->direccion ?? 'Sin dirección' }}, {{ $sucursal->distrito }}, {{ $sucursal->provincia }}</span>
                </div>
                
                @if($sucursal->telefono)
                    <div class="sucursal-info-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ $sucursal->telefono }}</span>
                    </div>
                @endif
                
                @if($sucursal->email)
                    <div class="sucursal-info-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $sucursal->email }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge-recojo-disponible">
                        <i class="fas fa-check-circle me-1" style="font-size: 0.85em;"></i> Recojo en tienda disponible
                    </span>
                    <a href="{{ route('tienda.index', ['sucursal' => $sucursal->id]) }}" 
                       class="btn btn-sm btn-outline-teal mt-3 text-xs rounded-xl"
                       onclick="event.stopPropagation();"
                       style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.75rem; border: 1.5px solid #0d9488; color: #0d9488; background: transparent;">
                       Ver catálogo
                    </a>
                </div>
            </div>
        @empty
            <div class="store-card bg-white p-4 text-center">
                <p class="muted-copy mb-0">No se encontraron sucursales activas en este momento.</p>
            </div>
        @endforelse
    </div>

    <!-- Contenedor derecho: Mapa -->
    <div class="sucursales-map-container">
        <div id="map_sucursales"></div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    var map = null;
    var markers = {};
    var sucursalesData = @json($sucursalesJson);

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el mapa centrado en Perú por defecto
        var defaultLat = -9.189967;
        var defaultLng = -75.015152;
        var defaultZoom = 6;

        // Si tenemos sucursales con coordenadas válidas, centramos en la primera
        var validSucursales = sucursalesData.filter(s => s.latitud && s.longitud);
        if (validSucursales.length > 0) {
            defaultLat = validSucursales[0].latitud;
            defaultLng = validSucursales[0].longitud;
            defaultZoom = 13;
        }

        map = L.map('map_sucursales').setView([defaultLat, defaultLng], defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Diseñar un marcador SVG personalizado con el estilo de tiendafarma-ux (Verde Esmeralda)
        var customIcon = L.divIcon({
            html: `<div style="background-color: #0d9488; width: 36px; height: 36px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 10px rgba(13, 148, 136, 0.4); display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-clinic-medical" style="font-size: 14px;"></i>
                   </div>`,
            className: 'custom-map-marker',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -18]
        });

        // Crear marcadores
        validSucursales.forEach(function(s) {
            var marker = L.marker([s.latitud, s.longitud], { icon: customIcon }).addTo(map);
            
            // Popup informativo estilizado
            var popupContent = `
                <div class="popup-title">${s.nombre}</div>
                <div class="popup-address">${s.direccion}, ${s.distrito}</div>
                <a href="${s.url_catalogo}" class="popup-action-btn"><i class="fas fa-shopping-basket me-1"></i> Comprar aquí</a>
            `;
            
            marker.bindPopup(popupContent);
            
            // Guardar referencia del marcador
            markers[s.id] = marker;

            // Evento al hacer clic en el marcador: activa la tarjeta correspondiente
            marker.on('click', function() {
                highlightCard(s.id);
            });
        });

        // Si hay una sucursal activa en los filtros del request, enfocarla de inmediato
        var urlParams = new URLSearchParams(window.location.search);
        var activeSucursalId = urlParams.get('ver');
        if (activeSucursalId && markers[activeSucursalId]) {
            var activeSuc = validSucursales.find(s => s.id == activeSucursalId);
            if (activeSuc) {
                setTimeout(function() {
                    focusSucursal(activeSuc.id, activeSuc.latitud, activeSuc.longitud);
                }, 500);
            }
        } else if (validSucursales.length > 0) {
            // Activar la primera tarjeta visualmente por defecto
            highlightCard(validSucursales[0].id);
        }

        // Detectar ubicación del usuario
        const btnDetectar = document.getElementById('btn-detectar-ubicacion');
        let userLocationMarker = null;

        function recalcularUbicacionUsuario(userLat, userLng) {
            // Calcular distancia a cada sucursal
            let sucursalesConDistancia = sucursalesData.map(function(s) {
                if (s.latitud && s.longitud) {
                    s.distancia = calcularDistancia(userLat, userLng, s.latitud, s.longitud);
                } else {
                    s.distancia = Infinity;
                }
                return s;
            });

            // Ordenar por distancia de menor a mayor
            let sortable = [...sucursalesConDistancia];
            sortable.sort(function(a, b) {
                return a.distancia - b.distancia;
            });

            // Mostrar distancias en las tarjetas correspondientes
            sucursalesConDistancia.forEach(function(s) {
                const card = document.getElementById('card-' + s.id);
                if (card && s.distancia !== Infinity) {
                    let distSpan = card.querySelector('.sucursal-distancia-badge');
                    if (!distSpan) {
                        distSpan = document.createElement('div');
                        distSpan.className = 'sucursal-distancia-badge mt-2 text-xs font-semibold';
                        distSpan.style.color = '#3b82f6';
                        distSpan.style.fontSize = '0.75rem';
                        card.insertBefore(distSpan, card.querySelector('.d-flex.justify-content-between'));
                    }
                    distSpan.innerHTML = `<i class="fas fa-route me-1"></i> A aprox. <strong>${s.distancia.toFixed(1)} km</strong> de tu ubicación`;
                }
            });

            // Enfocar en la sucursal más cercana si existe una
            if (sortable.length > 0 && sortable[0].distancia !== Infinity) {
                const closest = sortable[0];
                focusSucursal(closest.id, closest.latitud, closest.longitud);
                
                // Ajustar vista del mapa para englobar la ubicación del usuario y la sucursal más cercana
                const bounds = L.latLngBounds([
                    [userLat, userLng],
                    [closest.latitud, closest.longitud]
                ]);
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        if (btnDetectar) {
            btnDetectar.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    alert('La geolocalización no está soportada por tu navegador.');
                    return;
                }

                btnDetectar.disabled = true;
                btnDetectar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo ubicación...';

                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;

                    btnDetectar.innerHTML = '<i class="fas fa-check-circle"></i> Ubicación obtenida';
                    btnDetectar.classList.remove('btn-outline-teal');
                    btnDetectar.style.borderColor = '#10b981';
                    btnDetectar.style.color = '#ffffff';
                    btnDetectar.style.backgroundColor = '#10b981';

                    // Agregar o mover marcador del usuario en el mapa (DRAGGABLE)
                    const userIcon = L.divIcon({
                        html: `<div style="background-color: #3b82f6; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4); display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-user-circle" style="font-size: 11px;"></i>
                               </div>`,
                        className: 'user-map-marker',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    if (userLocationMarker) {
                        userLocationMarker.setLatLng([userLat, userLng]);
                    } else {
                        userLocationMarker = L.marker([userLat, userLng], { 
                            icon: userIcon,
                            draggable: true
                        }).addTo(map);

                        userLocationMarker.bindPopup('<strong>Tu ubicación actual</strong><br><span style="font-size:0.82em;color:#64748b;">(Arrastra este pin para precisar tu ubicación)</span>').openPopup();

                        // Escuchar evento de arrastre para recalcular
                        userLocationMarker.on('dragend', function() {
                            const newPos = userLocationMarker.getLatLng();
                            userLocationMarker.bindPopup('<strong>Ubicación ajustada</strong><br><span style="font-size:0.82em;color:#64748b;">(Puedes volver a arrastrarlo)</span>').openPopup();
                            recalcularUbicacionUsuario(newPos.lat, newPos.lng);
                        });
                    }

                    recalcularUbicacionUsuario(userLat, userLng);

                }, function(error) {
                    btnDetectar.disabled = false;
                    btnDetectar.innerHTML = '<i class="fas fa-location-arrow"></i> Buscar sucursal más cercana';
                    alert('No pudimos acceder a tu ubicación. Asegúrate de otorgar permisos.');
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            });
        }

        function calcularDistancia(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
    });

    function focusSucursal(id, lat, lng) {
        if (!map) return;

        highlightCard(id);

        if (lat && lng) {
            map.panTo([lat, lng]);
            map.setZoom(16);
            
            if (markers[id]) {
                markers[id].openPopup();
            }
        }
    }

    function highlightCard(id) {
        // Remover clase activa de todas las tarjetas
        document.querySelectorAll('.sucursal-card-pub').forEach(function(card) {
            card.classList.remove('active');
        });

        // Añadir clase activa a la seleccionada
        var card = document.getElementById('card-' + id);
        if (card) {
            card.classList.add('active');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
</script>
@endpush
@endsection
