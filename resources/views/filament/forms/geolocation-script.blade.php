<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationStatus = document.getElementById('location-status');

    if (!locationStatus) return;

    // Check if Geolocation is supported
    if (!navigator.geolocation) {
        locationStatus.innerHTML = `
            <div class="flex items-center gap-2 text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Browser Anda tidak mendukung GPS</span>
            </div>
        `;
        return;
    }

    // Get user's current position
    navigator.geolocation.getCurrentPosition(
        async function(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            // Set hidden fields
            const latField = document.querySelector('input[name="check_latitude"]');
            const lonField = document.querySelector('input[name="check_longitude"]');

            if (latField) {
                latField.value = latitude;
                latField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lonField) {
                lonField.value = longitude;
                lonField.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // Validate location with backend
            try {
                const response = await fetch('/api/attendance/validate-location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ latitude, longitude })
                });

                const data = await response.json();

                if (data.valid) {
                    // Set location data
                    const officeIdField = document.querySelector('input[name="office_location_id"]');
                    const distanceField = document.querySelector('input[name="distance_from_office"]');
                    const radiusField = document.querySelector('input[name="is_within_radius"]');

                    if (officeIdField) officeIdField.value = data.office_id;
                    if (distanceField) distanceField.value = data.distance;
                    if (radiusField) radiusField.value = data.within_radius ? '1' : '0';

                    // Show success message
                    locationStatus.innerHTML = `
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-green-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Lokasi Terdeteksi</span>
                            </div>
                            <div class="text-sm space-y-1">
                                <p><strong>Lokasi:</strong> ${data.office_name}</p>
                                <p><strong>Jarak:</strong> ${data.distance} meter dari kantor</p>
                                <p><strong>Status:</strong>
                                    <span class="${data.within_radius ? 'text-green-600' : 'text-red-600'}">
                                        ${data.within_radius ? '✓ Dalam radius' : '✗ Diluar radius'}
                                    </span>
                                </p>
                                <p class="text-gray-500">Akurasi: ±${Math.round(accuracy)} meter</p>
                            </div>
                        </div>
                    `;

                    if (!data.within_radius) {
                        locationStatus.innerHTML += `
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                                <strong>Peringatan:</strong> Anda berada diluar radius yang diizinkan (${data.allowed_radius}m). Absensi tidak dapat dilanjutkan.
                            </div>
                        `;
                    }
                } else {
                    locationStatus.innerHTML = `
                        <div class="flex items-center gap-2 text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>${data.message || 'Tidak ada lokasi kantor yang ditemukan'}</span>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Location validation error:', error);
                locationStatus.innerHTML = `
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-yellow-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Lokasi terdeteksi (belum divalidasi)</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <p>Koordinat: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}</p>
                            <p>Akurasi: ±${Math.round(accuracy)} meter</p>
                        </div>
                    </div>
                `;
            }
        },
        function(error) {
            let message = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Anda menolak akses lokasi. Silakan aktifkan izin lokasi di pengaturan browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                    break;
                case error.TIMEOUT:
                    message = 'Waktu tunggu habis. Silakan coba lagi.';
                    break;
                default:
                    message = 'Terjadi kesalahan saat mengakses lokasi.';
            }

            locationStatus.innerHTML = `
                <div class="flex items-center gap-2 text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>${message}</span>
                </div>
            `;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
});
</script>
