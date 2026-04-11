@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endonce

<div 
    x-data="{ 
        init() { 
            const lat = {{ $getRecord()->check_latitude ?? $getRecord()->latitude ?? 'null' }};
            const lng = {{ $getRecord()->check_longitude ?? $getRecord()->longitude ?? 'null' }};
            
            if (!lat || !lng) return;
            
            // Wait until L is defined (in case of async loading)
            const checkL = setInterval(() => {
                if (typeof L !== 'undefined') {
                    clearInterval(checkL);
                    this.renderMap(lat, lng);
                }
            }, 100);
        },
        renderMap(lat, lng) {
            this.$nextTick(() => {
                const map = L.map(this.$refs.map, {
                    zoomControl: false,
                    attributionControl: false,
                    dragging: false,
                    touchZoom: false,
                    scrollWheelZoom: false,
                    doubleClickZoom: false,
                }).setView([lat, lng], 14);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                L.marker([lat, lng]).addTo(map);
                
                setTimeout(() => { map.invalidateSize(); }, 500);
            });
        }
    }"
    class="flex justify-center p-1"
>
    <div 
        x-ref="map" 
        style="height: 50px; width: 70px; z-index: 0; position: relative;" 
        class="rounded border border-gray-200 shadow-sm overflow-hidden"
        wire:ignore
    >
        @if(!($getRecord()->check_latitude ?? $getRecord()->latitude))
            <div class="flex items-center justify-center h-full bg-gray-50 text-[10px] text-gray-300 italic">
                N/A
            </div>
        @endif
    </div>
</div>
