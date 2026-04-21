@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('leafletMap', (config) => ({
                map: null,
                init() {
                    this.map = L.map(this.$refs.map, {
                        center: [config.lat, config.lng],
                        zoom: config.zoom || 13,
                        dragging: false,
                        touchZoom: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        keyboard: false,
                        zoomControl: false,
                        attributionControl: false
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(this.map);

                    L.marker([config.lat, config.lng]).addTo(this.map);

                    if (config.radius) {
                        L.circle([config.lat, config.lng], {
                            color: '#3b82f6',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.2,
                            radius: config.radius
                        }).addTo(this.map);
                    }
                    
                    // Force refresh layout after small delay to ensure container size is correct
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 100);
                }
            }))
        })
    </script>
@endonce

<div class="flex flex-col gap-2 p-2">
    <div 
        x-data="leafletMap({ 
            lat: {{ $getRecord()->latitude }}, 
            lng: {{ $getRecord()->longitude }}, 
            radius: {{ $getRecord()->radius }},
            zoom: 13
        })"
        class="h-24 w-48 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden bg-gray-50 dark:bg-gray-800"
    >
        <div x-ref="map" class="w-full h-full z-0"></div>
    </div>
    
    <div class="flex items-center gap-1.5 px-1 font-mono text-[10px] text-gray-500">
        {{ round($getRecord()->latitude, 6) }}, {{ round($getRecord()->longitude, 6) }}
    </div>

    <div class="flex items-center gap-1.5 px-1">
        <div class="p-1 rounded-md bg-primary-50 dark:bg-primary-900/30">
            <x-filament::icon
                icon="heroicon-m-arrows-pointing-out"
                class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400"
            />
        </div>
        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
            Radius: <span class="text-primary-600 dark:text-primary-400">{{ $getRecord()->radius }}m</span>
        </span>
    </div>
</div>
