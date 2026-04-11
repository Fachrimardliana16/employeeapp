<div 
    x-data="{ 
        init() { 
            const lat = {{ $getRecord()->check_latitude ?? 'null' }};
            const lng = {{ $getRecord()->check_longitude ?? 'null' }};
            
            if (!lat || !lng) return;
            
            this.$nextTick(() => {
                const map = L.map(this.$refs.map).setView([lat, lng], 16);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup('<b>Lokasi Presensi</b><br>{{ addslashes($getRecord()->employee_name) }}')
                    .openPopup();

                // Fix map size check
                setTimeout(() => {
                    map.invalidateSize();
                }, 200);
            });
        } 
    }"
    class="w-full"
>
    <!-- Load Leaflet Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Map Container -->
    <div 
        x-ref="map" 
        style="height: 350px;" 
        class="rounded-xl shadow-inner z-0 border border-gray-200"
        wire:ignore
    >
        @if(!$getRecord()->check_latitude || !$getRecord()->check_longitude)
            <div class="flex items-center justify-center h-full bg-gray-50 text-gray-400">
                <span>Data koordinat GPS tidak tersedia untuk record ini.</span>
            </div>
        @endif
    </div>
</div>
