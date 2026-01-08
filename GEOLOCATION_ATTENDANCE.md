# Fitur Geolokasi Kehadiran (Attendance Geolocation Feature)

## Overview

Sistem absensi dengan validasi lokasi GPS dan foto selfie langsung dari kamera. Sistem ini memastikan karyawan berada di lokasi kantor yang benar saat melakukan check-in/check-out.

## Fitur Utama

### 1. **Multi-Location Support**

-   Mendukung beberapa lokasi kantor/cabang
-   Setiap lokasi memiliki koordinat GPS dan radius yang dapat dikonfigurasi
-   Sistem otomatis mencari lokasi kantor terdekat dari posisi karyawan

### 2. **GPS Validation**

-   Deteksi otomatis lokasi karyawan menggunakan browser Geolocation API
-   Validasi jarak antara karyawan dengan kantor terdekat
-   Perhitungan jarak menggunakan Haversine formula (akurat untuk jarak di permukaan bumi)
-   Menampilkan jarak real-time dan status (dalam/luar radius)

### 3. **Camera Capture**

-   Foto selfie langsung dari kamera (TIDAK dari file/gallery)
-   Menggunakan kamera depan (front camera) untuk selfie
-   Foto berbeda untuk check-in dan check-out
-   Preview foto sebelum submit
-   Fitur retake jika foto kurang memuaskan

### 4. **Validasi Ketat**

-   Karyawan HARUS berada dalam radius yang ditentukan
-   Jika diluar radius, absensi tidak dapat dilanjutkan
-   Notifikasi jelas tentang jarak dan batas maksimal

## Database Schema

### Tabel: `master_office_locations`

```sql
- id (bigint, primary key)
- name (varchar) - Nama lokasi kantor
- code (varchar, unique) - Kode unik lokasi
- address (text) - Alamat lengkap
- latitude (decimal 10,8) - Koordinat latitude
- longitude (decimal 11,8) - Koordinat longitude
- radius (integer) - Radius dalam meter (default: 100m)
- description (text, nullable) - Deskripsi
- is_active (boolean) - Status aktif/nonaktif
- users_id (foreign key) - User yang membuat
- timestamps
- soft deletes
```

### Tabel: `employee_attendance_records` (fields baru)

```sql
- office_location_id (foreign key) - Referensi ke master_office_locations
- check_latitude (decimal 10,8) - Koordinat karyawan saat absen
- check_longitude (decimal 11,8) - Koordinat karyawan saat absen
- distance_from_office (integer) - Jarak dari kantor dalam meter
- is_within_radius (boolean) - Status dalam/luar radius
- photo_checkin (varchar) - Path foto check in
- photo_checkout (varchar) - Path foto check out
```

## Cara Penggunaan

### A. Setup Lokasi Kantor (Admin)

1. **Login sebagai Admin**
2. **Navigasi ke: Pengaturan Sistem → Lokasi Kantor**
3. **Klik "Buat Baru"**
4. **Isi form:**
    - **Nama**: Nama lokasi (contoh: "Kantor Pusat Jakarta")
    - **Kode**: Kode unik (contoh: "JKT-HQ")
    - **Alamat**: Alamat lengkap
    - **Latitude & Longitude**:
        - Buka Google Maps
        - Cari lokasi kantor
        - Klik kanan → "What's here?"
        - Salin koordinat (format: -6.208763, 106.845599)
        - Paste di field masing-masing
    - **Radius**: Jarak maksimal dalam meter (10-1000m)
    - **Status**: Aktif
5. **Simpan**

### B. Absensi Karyawan (Employee Panel)

1. **Login sebagai Karyawan**
2. **Navigasi ke: Absensi & Kehadiran → Kehadiran**
3. **Klik "Buat Baru"**
4. **Sistem akan otomatis:**

    - Meminta izin akses GPS (klik "Allow")
    - Meminta izin akses kamera (klik "Allow")
    - Mendeteksi lokasi Anda
    - Menghitung jarak ke kantor terdekat
    - Menampilkan status validasi

5. **Isi form:**

    - **PIN Pegawai**: Auto-fill dari user login
    - **Nama Pegawai**: Auto-fill dari user login
    - **Waktu**: Auto-fill waktu sekarang
    - **Status**: Pilih "Check In" atau "Check Out"

6. **Ambil foto selfie:**

    - Klik "Aktifkan Kamera"
    - Posisikan wajah di depan kamera
    - Klik "Ambil Foto"
    - Jika kurang puas, klik "Ambil Ulang"

7. **Validasi lokasi:**

    - Pastikan status menunjukkan "Dalam radius"
    - Jika "Diluar radius", sistem akan menolak absensi
    - Lihat jarak Anda dari kantor

8. **Simpan absensi**

## Konfigurasi Radius

### Rekomendasi Radius:

-   **Kantor kecil/ruangan**: 10-30 meter
-   **Kantor menengah/gedung**: 50-75 meter
-   **Kantor besar/kompleks**: 100-200 meter
-   **Area luas/pabrik**: 200-500 meter

### Tips:

-   Jangan terlalu ketat (≤10m) - GPS tidak 100% akurat
-   Jangan terlalu longgar (≥500m) - validasi tidak efektif
-   Test dengan beberapa karyawan di berbagai titik kantor
-   Sesuaikan radius berdasarkan akurasi GPS di lokasi

## Teknologi yang Digunakan

### Backend:

-   **Laravel 12**: Framework PHP
-   **Filament v3**: Admin panel framework
-   **Haversine Formula**: Perhitungan jarak geografis

### Frontend:

-   **Alpine.js**: JavaScript framework (included in Filament)
-   **Browser Geolocation API**: GPS access
-   **MediaDevices API**: Camera access
-   **Canvas API**: Image capture

### Storage:

-   **Laravel Storage**: File upload handling
-   **Public disk**: Foto disimpan di `storage/app/public/attendance/`

## API Endpoints

### 1. Upload Photo

```
POST /api/attendance/upload-photo

Request:
- photo (file, required, max:5MB)
- field (string, required, values: photo_checkin|photo_checkout)

Response:
{
    "success": true,
    "path": "attendance/photo_checkin/2026/01/08/uuid.jpg",
    "url": "/storage/attendance/photo_checkin/2026/01/08/uuid.jpg"
}
```

### 2. Validate Location

```
POST /api/attendance/validate-location

Request:
{
    "latitude": -6.208763,
    "longitude": 106.845599
}

Response:
{
    "valid": true,
    "office_id": 1,
    "office_name": "Kantor Pusat Jakarta",
    "distance": 45,
    "allowed_radius": 100,
    "within_radius": true,
    "latitude": -6.208763,
    "longitude": 106.845599
}
```

## Troubleshooting

### GPS tidak terdeteksi

**Penyebab:**

-   Browser tidak support Geolocation API
-   User menolak permission
-   GPS tidak aktif di device
-   Indoor dengan sinyal GPS lemah

**Solusi:**

-   Gunakan browser modern (Chrome, Firefox, Safari terbaru)
-   Klik "Allow" saat diminta permission
-   Aktifkan GPS/Location Services di device
-   Coba di area dengan sinyal GPS lebih baik

### Kamera tidak aktif

**Penyebab:**

-   Browser tidak support MediaDevices API
-   User menolak permission
-   Kamera sedang digunakan aplikasi lain
-   Device tidak memiliki kamera

**Solusi:**

-   Gunakan browser dengan HTTPS (required untuk camera access)
-   Klik "Allow" saat diminta permission
-   Tutup aplikasi lain yang menggunakan kamera
-   Gunakan device dengan kamera

### Selalu "Diluar Radius"

**Penyebab:**

-   Koordinat kantor salah
-   Radius terlalu kecil
-   GPS tidak akurat

**Solusi:**

-   Verifikasi koordinat kantor di Google Maps
-   Naikkan radius di setting lokasi kantor
-   Tunggu beberapa detik untuk GPS lock yang lebih akurat
-   Test di area outdoor untuk sinyal GPS lebih baik

### Foto tidak terupload

**Penyebab:**

-   Koneksi internet lambat
-   File terlalu besar
-   Storage penuh
-   Permission storage tidak diberikan

**Solusi:**

-   Pastikan koneksi internet stabil
-   Foto akan di-compress otomatis (quality 0.9)
-   Clear storage atau hubungi admin
-   Check permission di `config/filesystems.php`

## Security & Privacy

### Data Privacy:

-   Koordinat GPS hanya diambil saat absensi
-   Tidak ada tracking GPS real-time
-   Data hanya disimpan di database untuk audit trail
-   Foto tersimpan di server (tidak dishare ke pihak ketiga)

### Security Measures:

-   CSRF token validation pada semua request
-   File type validation (hanya image)
-   File size limit (max 5MB)
-   Geographic validation before save
-   Authenticated users only

## Model Methods

### MasterOfficeLocation

```php
// Calculate distance between two coordinates (Haversine)
MasterOfficeLocation::calculateDistance($lat1, $lon1, $lat2, $lon2)
// Returns: float (distance in meters)

// Check if coordinates within this location's radius
$location->isWithinRadius($latitude, $longitude)
// Returns: boolean

// Find closest active location
MasterOfficeLocation::getClosestLocation($latitude, $longitude)
// Returns: ['location' => MasterOfficeLocation, 'distance' => float]
```

### EmployeeAttendanceRecord

```php
// Validate and set location automatically
$record->validateAndSetLocation($latitude, $longitude)
// Sets: office_location_id, check_latitude, check_longitude, distance_from_office, is_within_radius

// Relationships
$record->officeLocation() // BelongsTo MasterOfficeLocation
$record->employee()       // BelongsTo Employee
```

## Sample Data

Jalankan seeder untuk data contoh:

```bash
php artisan db:seed --class=MasterOfficeLocationSeeder
```

Akan membuat 3 lokasi kantor:

1. Kantor Pusat Jakarta (radius: 100m)
2. Kantor Cabang Bandung (radius: 75m)
3. Kantor Cabang Surabaya (radius: 50m)

## Future Enhancements

Possible improvements:

-   [ ] Offline support (save attendance when offline, sync when online)
-   [ ] Face detection/recognition
-   [ ] QR code verification
-   [ ] Shift schedule integration
-   [ ] Overtime calculation
-   [ ] Leave balance integration
-   [ ] Real-time dashboard untuk manager
-   [ ] Push notification untuk reminder check-in/out
-   [ ] Export report attendance per periode
-   [ ] Analytics dashboard (late attendance, location trends, etc.)

---

**Developed by:** Laravel + Filament Team  
**Version:** 1.0.0  
**Last Updated:** January 8, 2026
