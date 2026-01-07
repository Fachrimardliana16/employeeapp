<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-3">
                    <x-filament::icon icon="heroicon-o-book-open" class="w-8 h-8 text-primary-600"/>
                    <span class="text-2xl font-bold">Manual Book - Panduan Karyawan</span>
                </div>
            </x-slot>
            <p class="text-gray-600 dark:text-gray-400">
                Panduan lengkap penggunaan sistem self-service kepegawaian untuk karyawan.
                Pelajari cara mengajukan izin, cuti, melihat data pribadi, dan fitur lainnya.
            </p>
        </x-filament::section>

        {{-- Getting Started --}}
        <x-filament::section>
            <x-slot name="heading">🚀 Memulai</x-slot>
            <div class="prose dark:prose-invert max-w-none">
                <h3>Login & Navigasi</h3>
                <ol>
                    <li>Login menggunakan email & password yang diberikan HR</li>
                    <li>Setelah login, Anda akan melihat menu di sidebar kiri</li>
                    <li>Klik menu yang ingin diakses untuk melihat detail</li>
                    <li>Gunakan tombol di pojok kanan atas untuk logout atau ubah profil</li>
                </ol>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mt-4">
                    <p class="font-semibold text-blue-900 dark:text-blue-100">💡 Tips Login:</p>
                    <ul class="text-sm">
                        <li>Simpan password dengan aman</li>
                        <li>Jangan share akun dengan orang lain</li>
                        <li>Logout setelah selesai menggunakan sistem</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- Data Kepegawaian --}}
        <x-filament::section collapsible>
            <x-slot name="heading">📊 Data Kepegawaian</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Fungsi:</h4>
                <p>Melihat seluruh data kepegawaian Anda dalam satu halaman (read-only).</p>

                <h5 class="font-semibold">Informasi yang Tersedia:</h5>
                <ul class="list-disc ml-6">
                    <li><strong>Informasi Dasar:</strong> Nama, NIK, email, telepon, alamat</li>
                    <li><strong>Data Pekerjaan:</strong> Jabatan, department, grade, status kepegawaian</li>
                    <li><strong>Data Keluarga:</strong> Daftar anggota keluarga yang terdaftar</li>
                    <li><strong>Pendidikan:</strong> Riwayat pendidikan formal</li>
                    <li><strong>Gaji:</strong> Informasi gaji & tunjangan terkini</li>
                    <li><strong>Kontrak/Perjanjian:</strong> Detail kontrak kerja</li>
                    <li><strong>Dokumen:</strong> Daftar dokumen yang telah diupload</li>
                    <li><strong>Training:</strong> Riwayat pelatihan yang pernah diikuti</li>
                    <li><strong>Mutasi:</strong> History perpindahan posisi/department</li>
                    <li><strong>Promosi:</strong> Riwayat kenaikan jabatan</li>
                </ul>

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg mt-4">
                    <p class="font-semibold text-green-900 dark:text-green-100">✅ Cara Menggunakan:</p>
                    <ol class="text-sm list-decimal ml-4">
                        <li>Klik menu "Data Kepegawaian" di sidebar</li>
                        <li>Scroll ke bawah untuk melihat semua section</li>
                        <li>Jika ada data yang salah, hubungi HR untuk koreksi</li>
                    </ol>
                </div>
            </div>
        </x-filament::section>

        {{-- Izin & Cuti --}}
        <x-filament::section collapsible>
            <x-slot name="heading">📅 Izin & Cuti</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Fungsi:</h4>
                <p>Mengajukan permohonan izin atau cuti dan melihat status persetujuan.</p>

                <h5 class="font-semibold">Cara Mengajukan Izin/Cuti:</h5>
                <ol class="list-decimal ml-6">
                    <li>Klik menu "Izin & Cuti" di sidebar</li>
                    <li>Klik tombol "Ajukan Izin/Cuti" di pojok kanan atas</li>
                    <li>Isi form pengajuan:
                        <ul class="list-circle ml-6">
                            <li><strong>Jenis:</strong> Pilih jenis izin (Sakit, Cuti Tahunan, Izin Pribadi, dll)</li>
                            <li><strong>Tanggal Mulai:</strong> Kapan izin dimulai</li>
                            <li><strong>Tanggal Selesai:</strong> Kapan izin berakhir</li>
                            <li><strong>Alasan:</strong> Jelaskan alasan mengajukan izin</li>
                            <li><strong>Dokumen Pendukung:</strong> Upload surat keterangan (opsional, wajib untuk sakit >2 hari)</li>
                        </ul>
                    </li>
                    <li>Klik "Create" untuk submit</li>
                    <li>Tunggu approval dari atasan/HR</li>
                </ol>

                <h5 class="font-semibold">Melihat Status Pengajuan:</h5>
                <ul class="list-disc ml-6">
                    <li><strong>Pending:</strong> Menunggu persetujuan (badge kuning)</li>
                    <li><strong>Approved:</strong> Sudah disetujui (badge hijau)</li>
                    <li><strong>Rejected:</strong> Ditolak (badge merah) - lihat catatan penolakan</li>
                </ul>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg mt-4">
                    <p class="font-semibold text-yellow-900 dark:text-yellow-100">⚠️ Perhatian:</p>
                    <ul class="text-sm">
                        <li>Ajukan izin minimal H-1 untuk izin pribadi</li>
                        <li>Cuti tahunan sebaiknya diajukan H-7</li>
                        <li>Izin sakit wajib ada surat dokter jika >2 hari</li>
                        <li>Cek sisa jatah cuti sebelum mengajukan</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- Laporan Harian --}}
        <x-filament::section collapsible>
            <x-slot name="heading">📝 Laporan Harian</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Fungsi:</h4>
                <p>Submit laporan aktivitas harian pekerjaan Anda.</p>

                <h5 class="font-semibold">Cara Submit Laporan:</h5>
                <ol class="list-decimal ml-6">
                    <li>Klik menu "Laporan Harian" di sidebar</li>
                    <li>Klik tombol "New" untuk membuat laporan baru</li>
                    <li>Isi form:
                        <ul class="list-circle ml-6">
                            <li><strong>Tanggal:</strong> Tanggal laporan (default: hari ini)</li>
                            <li><strong>Aktivitas:</strong> Jelaskan kegiatan yang dilakukan hari ini</li>
                            <li><strong>Progress:</strong> Persentase progress pekerjaan (0-100%)</li>
                            <li><strong>Kendala:</strong> Masalah yang dihadapi (jika ada)</li>
                            <li><strong>Catatan:</strong> Informasi tambahan</li>
                        </ul>
                    </li>
                    <li>Klik "Create" untuk submit</li>
                </ol>

                <h5 class="font-semibold">Tips Menulis Laporan:</h5>
                <ul class="list-disc ml-6">
                    <li>Jelaskan aktivitas dengan spesifik (bukan "Bekerja" tapi "Menyusun proposal X")</li>
                    <li>Update progress secara realistis</li>
                    <li>Laporkan kendala segera agar bisa dibantu</li>
                    <li>Submit sebelum jam kerja berakhir</li>
                </ul>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mt-4">
                    <p class="font-semibold text-blue-900 dark:text-blue-100">💡 Best Practice:</p>
                    <ul class="text-sm">
                        <li>Buat template untuk pekerjaan rutin</li>
                        <li>Screenshot atau dokumentasi sebagai bukti</li>
                        <li>Review laporan minggu lalu untuk evaluasi diri</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- Absensi --}}
        <x-filament::section collapsible>
            <x-slot name="heading">⏰ Absensi</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Fungsi:</h4>
                <p>Check-in dan check-out menggunakan GPS untuk pencatatan kehadiran.</p>

                <h5 class="font-semibold">Cara Absen:</h5>
                <ol class="list-decimal ml-6">
                    <li>Klik menu "Absensi" di sidebar</li>
                    <li>Pastikan GPS di device Anda aktif</li>
                    <li>Allow browser untuk akses lokasi Anda</li>
                    <li>Klik tombol "Check In" saat tiba di kantor</li>
                    <li>Klik tombol "Check Out" saat pulang</li>
                    <li>System akan auto-record waktu & lokasi Anda</li>
                </ol>

                <h5 class="font-semibold">Informasi yang Tercatat:</h5>
                <ul class="list-disc ml-6">
                    <li>Waktu check-in & check-out</li>
                    <li>Koordinat GPS lokasi</li>
                    <li>Total jam kerja (auto-calculate)</li>
                    <li>Status keterlambatan (jika terlambat)</li>
                </ul>

                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg mt-4">
                    <p class="font-semibold text-red-900 dark:text-red-100">⚠️ Penting:</p>
                    <ul class="text-sm">
                        <li>Absen harus dilakukan di area kantor (radius tertentu)</li>
                        <li>GPS harus aktif, jika tidak sistem akan reject</li>
                        <li>Jangan lupa check-out saat pulang</li>
                        <li>Jika lupa absen, segera hubungi HR</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- Pensiun/Resign --}}
        <x-filament::section collapsible>
            <x-slot name="heading">👋 Pensiun/Resign</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Fungsi:</h4>
                <p>Mengajukan permohonan pengunduran diri atau pensiun.</p>

                <h5 class="font-semibold">Cara Mengajukan:</h5>
                <ol class="list-decimal ml-6">
                    <li>Klik menu "Pensiun/Resign" di sidebar</li>
                    <li>Klik tombol "New" untuk buat pengajuan</li>
                    <li>Isi form dengan lengkap:
                        <ul class="list-circle ml-6">
                            <li><strong>Section 1 - Data Pengunduran Diri:</strong>
                                <ul class="list-square ml-6">
                                    <li>Jenis: Pilih Resign/Pensiun/Habis Kontrak/PHK</li>
                                    <li>Tanggal Efektif: Kapan resign berlaku</li>
                                    <li>Hari Kerja Terakhir: Last working day</li>
                                    <li>Alasan: Jelaskan kenapa resign (wajib)</li>
                                    <li>Surat Pengunduran Diri: Upload surat resmi</li>
                                </ul>
                            </li>
                            <li><strong>Section 2 - Serah Terima & Clearance:</strong>
                                <ul class="list-square ml-6">
                                    <li>Catatan Serah Terima: Detail pekerjaan yang diserahkan</li>
                                    <li>Aset Perusahaan: List aset yang dikembalikan (laptop, kunci, ID card, dll)</li>
                                    <li>Dokumen Serah Terima: Upload bukti serah terima</li>
                                </ul>
                            </li>
                            <li><strong>Section 3 - Informasi Tambahan:</strong>
                                <ul class="list-square ml-6">
                                    <li>Alamat Pengiriman: Alamat untuk pengiriman dokumen</li>
                                    <li>Kontak: Telepon & email yang bisa dihubungi</li>
                                    <li>Surat Referensi: Checklist jika butuh</li>
                                    <li>Exit Interview: Setuju untuk wawancara keluar</li>
                                    <li>Feedback: Masukan untuk perusahaan (opsional)</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>Review semua data, lalu klik "Create"</li>
                    <li>Tunggu approval dari HR/Management</li>
                </ol>

                <h5 class="font-semibold">Checklist Sebelum Submit:</h5>
                <ul class="list-disc ml-6">
                    <li>✓ Sudah diskusi dengan atasan langsung</li>
                    <li>✓ Memberikan notice period minimal 30 hari</li>
                    <li>✓ Sudah prepare surat resign resmi</li>
                    <li>✓ List aset perusahaan yang harus dikembalikan</li>
                    <li>✓ Dokumentasi pekerjaan untuk handover</li>
                </ul>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg mt-4">
                    <p class="font-semibold text-purple-900 dark:text-purple-100">📌 Ketentuan:</p>
                    <ul class="text-sm">
                        <li>Notice period: 30 hari kerja (sesuai peraturan perusahaan)</li>
                        <li>Wajib menyelesaikan clearance sebelum hari terakhir</li>
                        <li>Surat referensi akan diberikan jika tidak ada masalah</li>
                        <li>Exit interview membantu perusahaan untuk improve</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- FAQ --}}
        <x-filament::section collapsible>
            <x-slot name="heading">❓ FAQ (Pertanyaan Umum)</x-slot>

            <div class="space-y-3">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold">Q: Saya lupa password, bagaimana?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A: Klik "Forgot Password" di halaman login, atau hubungi HR untuk reset password.</p>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold">Q: Kenapa izin saya ditolak?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A: Lihat catatan penolakan di detail pengajuan. Biasanya karena kurang dokumen pendukung atau bentrok jadwal.</p>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold">Q: Tidak bisa absen, GPS error?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A: Pastikan GPS aktif dan browser punya permission. Jika masih error, coba refresh atau ganti browser. Segera hubungi HR jika tetap tidak bisa.</p>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold">Q: Data kepegawaian saya salah, bagaimana update?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A: Hubungi HR dengan menyebutkan data yang salah. Hanya HR yang bisa update data master karyawan.</p>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold">Q: Berapa lama proses approval izin/cuti?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A: Maksimal 2x24 jam kerja. Jika urgent, hubungi atasan langsung via phone/WhatsApp.</p>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="font-semibold">Q: Lupa check-out kemarin, bagaimana?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A: Segera hubungi HR maksimal H+1 dengan bukti (email, chat, dll) bahwa Anda bekerja sampai jam normal.</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Contact Support --}}
        <x-filament::section>
            <x-slot name="heading">📞 Butuh Bantuan?</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                    <x-filament::icon icon="heroicon-o-envelope" class="w-8 h-8 mx-auto text-blue-600 mb-2"/>
                    <p class="font-semibold">Email</p>
                    <p class="text-sm">hr@company.com</p>
                </div>

                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                    <x-filament::icon icon="heroicon-o-phone" class="w-8 h-8 mx-auto text-green-600 mb-2"/>
                    <p class="font-semibold">WhatsApp</p>
                    <p class="text-sm">08xx-xxxx-xxxx</p>
                </div>

                <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-center">
                    <x-filament::icon icon="heroicon-o-clock" class="w-8 h-8 mx-auto text-purple-600 mb-2"/>
                    <p class="font-semibold">Jam Kerja</p>
                    <p class="text-sm">Senin-Jumat<br>08:00 - 17:00</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
