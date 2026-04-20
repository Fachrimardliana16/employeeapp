<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-3">
                    <x-filament::icon icon="heroicon-o-book-open" class="w-8 h-8 text-primary-600"/>
                    <span class="text-2xl font-bold">Manual Book - Panduan Sistem Employee Panel</span>
                </div>
            </x-slot>
            <p class="text-gray-600 dark:text-gray-400">
                Panduan lengkap penggunaan sistem manajemen kepegawaian untuk Staff HR/Admin.
                Dokumen ini berisi alur sistem, cara penggunaan setiap menu, dan tips untuk efisiensi kerja.
            </p>
        </x-filament::section>

        {{-- Alur Sistem Umum --}}
        <x-filament::section>
            <x-slot name="heading">📋 Alur Sistem Umum</x-slot>
            <div class="prose dark:prose-invert max-w-none">
                <h3>Flow Kerja Sistem:</h3>
                <ol>
                    <li><strong>Data Master</strong> → Setup data referensi (Department, Grade, Position, dll)</li>
                    <li><strong>Rekrutmen</strong> → Kelola lamaran & interview process</li>
                    <li><strong>Data Karyawan</strong> → Input & manage employee records</li>
                    <li><strong>Approval</strong> → Proses persetujuan izin, cuti, resign</li>
                    <li><strong>Payroll</strong> → Hitung & proses gaji karyawan</li>
                    <li><strong>Laporan</strong> → Generate reports & analytics</li>
                </ol>
            </div>
        </x-filament::section>

        {{-- Menu Kompensasi & Tunjangan --}}
        <x-filament::section collapsible>
            <x-slot name="heading">💰 Kompensasi & Tunjangan</x-slot>

            <div class="space-y-6">
                <div class="p-4 bg-primary-50 dark:bg-primary-900/10 rounded-xl border border-primary-200">
                    <h4 class="font-bold text-lg text-primary-900 dark:text-primary-100 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-calculator" class="w-5 h-5"/>
                        Memahami 3 Lapisan Payroll (Hierarki)
                    </h4>
                    <p class="text-sm mt-2 text-gray-700 dark:text-gray-300">
                        Sistem penggajian kita menggunakan 3 lapisan data agar fleksibel dan otomatis:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div class="p-3 bg-white dark:bg-gray-800 rounded border-l-4 border-gray-400">
                            <span class="text-xs font-bold text-gray-500 uppercase">1. Global (Otomatis)</span>
                            <p class="text-[11px] mt-1 italic text-gray-500">Berlaku untuk semua (Rumus)</p>
                            <p class="text-xs mt-2">Dihitung otomatis lewat rumus sistem. Contoh: BPJS (4%/1%), Tunjangan Keluarga, IWP.</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded border-l-4 border-blue-400">
                            <span class="text-xs font-bold text-blue-500 uppercase">2. Jabatan (Master)</span>
                            <p class="text-[11px] mt-1 italic text-blue-400">Berlaku per Jabatan (Fixed)</p>
                            <p class="text-xs mt-2">Nominal tetap yang melekat pada jabatan. Contoh: TKK, Tunjangan Jabatan, DAPENMA.</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded border-l-4 border-purple-400">
                            <span class="text-xs font-bold text-purple-500 uppercase">3. Pribadi (Khusus)</span>
                            <p class="text-[11px] mt-1 italic text-purple-400">Hanya untuk orang tertentu</p>
                            <p class="text-xs mt-2">Ditetapkan khusus di profil pegawai. Contoh: Hutang Bank, Cicilan Koperasi, Rekening Air.</p>
                        </div>
                    </div>
                </div>

                <h4 class="font-bold text-lg">1. Gaji & Payroll</h4>
                <p><strong>Fungsi:</strong> Kelola data gaji karyawan dan history perubahan gaji.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Input Gaji:</strong> Klik "New" → Pilih karyawan → Isi gaji pokok → Set tanggal efektif → Save</li>
                    <li><strong>Edit:</strong> Klik icon pensil → Ubah nominal → Save</li>
                    <li><strong>Filter:</strong> Gunakan search box untuk cari karyawan, atau filter berdasarkan status aktif</li>
                    <li><strong>Tips:</strong> Selalu cek effective date agar gaji tidak overlap</li>
                </ul>

                <h4 class="font-bold text-lg">2. Proses Payroll</h4>
                <p><strong>Fungsi:</strong> Generate dan proses payroll bulanan otomatis.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Generate Payroll:</strong> Pilih periode → Klik "Generate Payroll" → System auto-calculate</li>
                    <li><strong>Review:</strong> Cek detail perhitungan (base salary, allowances, deductions)</li>
                    <li><strong>Approve:</strong> Klik "Approve" setelah review selesai</li>
                    <li><strong>Payment:</strong> Set payment_status = "paid" dan isi payment_date</li>
                    <li><strong>Filter:</strong> Filter berdasarkan periode, status pembayaran, atau karyawan</li>
                </ul>

                <h4 class="font-bold text-lg">3. Potongan Gaji</h4>
                <p><strong>Fungsi:</strong> Kelola potongan gaji (cicilan, denda, dll) dengan sistem installment.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Input Potongan:</strong> Pilih karyawan → Pilih jenis (cicilan/denda) → Set amount & tanggal</li>
                    <li><strong>Cicilan:</strong> Isi installment_months untuk auto-tracking pembayaran</li>
                    <li><strong>Monitor:</strong> System auto-update paid_months setiap payroll process</li>
                    <li><strong>Deactivate:</strong> Toggle "is_active" untuk stop potongan</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Menu Dokumen & Arsip --}}
        <x-filament::section collapsible>
            <x-slot name="heading">📁 Dokumen & Arsip</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Dokumen Karyawan</h4>
                <p><strong>Fungsi:</strong> Upload dan manage dokumen penting karyawan (KTP, NPWP, Ijazah, dll).</p>

                <h5 class="font-semibold">Cara Input:</h5>
                <ol class="list-decimal ml-6">
                    <li>Klik "New" → Pilih karyawan</li>
                    <li>Pilih jenis dokumen dari dropdown (KTP, KK, NPWP, BPJS, Ijazah, dll)</li>
                    <li>Isi nomor dokumen (opsional)</li>
                    <li>Upload file (PDF/gambar max 10MB)</li>
                    <li>Set tanggal terbit & kadaluarsa (jika ada)</li>
                    <li>Pilih "Diupload oleh": HR atau Karyawan</li>
                    <li>Save</li>
                </ol>

                <h5 class="font-semibold">Filter & Search:</h5>
                <ul class="list-disc ml-6">
                    <li><strong>Search:</strong> Cari by nama karyawan atau nama dokumen</li>
                    <li><strong>Filter Jenis:</strong> Filter dokumen by type (KTP, NPWP, dll)</li>
                    <li><strong>Filter Uploader:</strong> Lihat dokumen by HR atau karyawan</li>
                    <li><strong>Alert Kadaluarsa:</strong>
                        <ul class="list-circle ml-6">
                            <li>Toggle "Kadaluarsa" → Lihat dokumen expired</li>
                            <li>Toggle "Akan Kadaluarsa" → Lihat dokumen &lt;30 hari expired</li>
                        </ul>
                    </li>
                </ul>

                <h5 class="font-semibold">Tips:</h5>
                <ul class="list-disc ml-6">
                    <li>Selalu set expiry_date untuk dokumen ber-masa berlaku</li>
                    <li>Check filter "Akan Kadaluarsa" setiap bulan untuk reminder</li>
                    <li>Download original file dari icon download di table</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Menu Kepegawaian --}}
        <x-filament::section collapsible>
            <x-slot name="heading">👥 Data Kepegawaian</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">1. Izin & Cuti</h4>
                <p><strong>Fungsi:</strong> Approve/Reject pengajuan izin dan cuti dari karyawan.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Review:</strong> Lihat list pending requests → Klik "View" untuk detail</li>
                    <li><strong>Approve:</strong> Klik action "Approve" → Isi catatan (opsional) → Confirm</li>
                    <li><strong>Reject:</strong> Klik "Reject" → Wajib isi alasan penolakan → Confirm</li>
                    <li><strong>Filter:</strong> Filter by status (Pending/Approved/Rejected)</li>
                    <li><strong>Tips:</strong> Check kalendar before approve untuk avoid clash jadwal</li>
                </ul>

                <h4 class="font-bold text-lg">2. Pensiun/Resign</h4>
                <p><strong>Fungsi:</strong> Proses pengajuan resign/pensiun karyawan.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Review Pengajuan:</strong> Check jenis (resign/pension/contract_end/PHK)</li>
                    <li><strong>Verify Documents:</strong> Pastikan surat resign & handover documents uploaded</li>
                    <li><strong>Check Clearance:</strong> Review serah terima pekerjaan & aset perusahaan</li>
                    <li><strong>Approve/Reject:</strong> Sama seperti approval izin</li>
                    <li><strong>Exit Process:</strong> Jika need_reference_letter = Yes, prepare surat referensi</li>
                </ul>

                <h4 class="font-bold text-lg">3. Laporan Harian</h4>
                <p><strong>Fungsi:</strong> Monitor laporan harian aktivitas karyawan.</p>
                <ul class="list-disc ml-6">
                    <li><strong>View:</strong> Lihat per karyawan atau per tanggal</li>
                    <li><strong>Filter:</strong> Filter by date range atau department</li>
                    <li><strong>Export:</strong> Export to Excel untuk monthly report</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Menu Rekrutmen --}}
        <x-filament::section collapsible>
            <x-slot name="heading">🎯 Rekrutmen</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Proses Interview</h4>
                <p><strong>Fungsi:</strong> Tracking interview process untuk job applicants.</p>

                <h5 class="font-semibold">Cara Input:</h5>
                <ol class="list-decimal ml-6">
                    <li>Pilih aplikasi lamaran dari dropdown</li>
                    <li>Set interview stage (HR Interview, User Interview, Final Interview)</li>
                    <li>Schedule tanggal & waktu interview</li>
                    <li>Isi interviewer name</li>
                    <li>After interview: Update result (Pass/Fail) & score</li>
                    <li>Isi notes/feedback</li>
                </ol>

                <h5 class="font-semibold">Filter:</h5>
                <ul class="list-disc ml-6">
                    <li>Filter by stage untuk tracking progress</li>
                    <li>Filter by result untuk lihat candidates passed</li>
                    <li>Search by applicant name</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Menu Penilaian Kinerja --}}
        <x-filament::section collapsible>
            <x-slot name="heading">⭐ Penilaian Kinerja</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Performance Appraisal</h4>
                <p><strong>Fungsi:</strong> Input hasil penilaian kinerja karyawan periodic.</p>

                <h5 class="font-semibold">Cara Input:</h5>
                <ol class="list-decimal ml-6">
                    <li>Pilih karyawan & set period (Q1/Q2/Q3/Q4 atau Annual)</li>
                    <li>Isi criteria scores (JSON format atau use builder)</li>
                    <li>System auto-calculate final grade</li>
                    <li>Set appraisal status (Draft/Submitted/Approved)</li>
                    <li>Isi feedback & development notes</li>
                </ol>

                <h5 class="font-semibold">Tips:</h5>
                <ul class="list-disc ml-6">
                    <li>Gunakan template criteria yang consistent</li>
                    <li>Save as Draft dulu sebelum final submit</li>
                    <li>Review historical appraisals untuk comparison</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Pengaturan Payroll --}}
        <x-filament::section collapsible>
            <x-slot name="heading">⚙️ Pengaturan Payroll (Admin Only)</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">1. Formula Payroll</h4>
                <p><strong>Fungsi:</strong> Setup formula perhitungan gaji by status/grade/position.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Create Formula:</strong> Set applies_to (Status/Grade/Position/All)</li>
                    <li><strong>Select Components:</strong> Checklist komponen yang masuk formula</li>
                    <li><strong>Set Multiplier:</strong> Isi percentage untuk CAPEG (80%) atau lainnya</li>
                    <li><strong>Example:</strong> Formula CAPEG → Applies to Status "CAPEG" → 80% multiplier</li>
                </ul>

                <h4 class="font-bold text-lg">2. Komponen Payroll</h4>
                <p><strong>Fungsi:</strong> Setup komponen tunjangan/potongan/bonus.</p>
                <ul class="list-disc ml-6">
                    <li><strong>Create Component:</strong> Set type (Allowance/Deduction/Bonus)</li>
                    <li><strong>Calculation Method:</strong>
                        <ul class="list-circle ml-6">
                            <li>Fixed Amount → Input nominal tetap</li>
                            <li>Percentage of Base → Input % dari gaji pokok</li>
                            <li>Custom Formula → Write formula (advanced)</li>
                        </ul>
                    </li>
                    <li><strong>Set Taxable:</strong> Toggle jika kena pajak</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Tips & Tricks --}}
        <x-filament::section>
            <x-slot name="heading">💡 Tips & Tricks</x-slot>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h5 class="font-bold text-blue-900 dark:text-blue-100">Keyboard Shortcuts</h5>
                    <ul class="text-sm space-y-1 mt-2">
                        <li>Cmd/Ctrl + K → Quick search</li>
                        <li>Cmd/Ctrl + S → Save form</li>
                        <li>Esc → Close modal</li>
                    </ul>
                </div>

                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <h5 class="font-bold text-green-900 dark:text-green-100">Best Practices</h5>
                    <ul class="text-sm space-y-1 mt-2">
                        <li>✓ Backup data sebelum bulk actions</li>
                        <li>✓ Double-check approval sebelum submit</li>
                        <li>✓ Use filter untuk efficient data viewing</li>
                    </ul>
                </div>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <h5 class="font-bold text-yellow-900 dark:text-yellow-100">Common Issues</h5>
                    <ul class="text-sm space-y-1 mt-2">
                        <li>Q: Payroll tidak calculate? → Check formula active</li>
                        <li>Q: Cannot edit? → Check record status</li>
                        <li>Q: File tidak upload? → Check max size 10MB</li>
                    </ul>
                </div>

                <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <h5 class="font-bold text-purple-900 dark:text-purple-100">Support</h5>
                    <ul class="text-sm space-y-1 mt-2">
                        <li>📧 Email: support@company.com</li>
                        <li>📱 WhatsApp: 08xx-xxxx-xxxx</li>
                        <li>🕐 Working hours: 08:00-17:00</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
