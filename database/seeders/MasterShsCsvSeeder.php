<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterShsCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing data to prevent duplicates
        Schema::disableForeignKeyConstraints();
        DB::table('master_standar_harga_satuans')->truncate();
        Schema::enableForeignKeyConstraints();

        $sections = $this->getShsData();
        
        DB::beginTransaction();
        try {
            foreach ($sections as $currentSection => $items) {
                foreach ($items as $data) {
                    // Normalize data length to avoid offset notices
                    $data = array_pad($data, 8, null);
                    
                    $insertData = [
                        'code' => null,
                        'name' => '',
                        'category' => null,
                        'location' => null,
                        'spesifikasi' => null,
                        'amount' => 0,
                        'unit' => null,
                        'description' => null,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($currentSection === 0) {
                        // Section 1: "NO","URAIAN","SATUAN","INDEK (RP)","KETERANGAN"
                        $parts = explode(' - ', $data[1], 2);
                        $insertData['code'] = $data[0];
                        $insertData['name'] = trim($parts[0] ?? '');
                        $insertData['spesifikasi'] = isset($parts[1]) ? trim($parts[1]) : null;
                        $insertData['category'] = 'Honorarium / Uang Hadir / Lembur';
                        $insertData['unit'] = $data[2];
                        $insertData['amount'] = ($data[3] !== '-' && $data[3] !== null) ? (float) preg_replace('/[^0-9]/', '', explode('-', $data[3])[0]) : 0;
                        $insertData['description'] = $data[4];
                    } elseif ($currentSection === 1) {
                        // Section 2: "KATEGORI","ZONA / WILAYAH","JABATAN","Harga (RP)","KETERANGAN"
                        $insertData['category'] = $data[0];
                        $insertData['location'] = $data[1];
                        $insertData['spesifikasi'] = $data[2];
                        $insertData['name'] = ($data[0] ?? '') . ' - ' . ($data[1] ?? '');
                        $insertData['amount'] = ($data[3] !== '-' && $data[3] !== null) ? (float) preg_replace('/[^0-9]/', '', explode('-', $data[3])[0]) : 0;
                        $insertData['description'] = $data[4];
                        $insertData['unit'] = 'Per Hari';
                    } elseif ($currentSection === 2) {
                        // Section 3: "KATEGORI","NO","URAIAN BARANG/JASA","SPESIFIKASI","SATUAN","HARGA (RP)","KETERANGAN"
                        $insertData['category'] = $data[0];
                        $insertData['code'] = $data[1];
                        $insertData['name'] = $data[2] ?? '';
                        $insertData['spesifikasi'] = $data[3];
                        $insertData['unit'] = $data[4];
                        $insertData['amount'] = ($data[5] !== '-' && $data[5] !== null) ? (float) preg_replace('/[^0-9]/', '', explode('-', $data[5])[0]) : 0;
                        $insertData['description'] = $data[6];
                    } elseif ($currentSection === 3) {
                        // Section 4: "KATEGORI","NO","URAIAN BARANG","MERK / SPESIFIKASI","SATUAN","HARGA (RP)","KETERANGAN"
                        $insertData['category'] = $data[0];
                        $insertData['code'] = $data[1];
                        $insertData['name'] = $data[2] ?? '';
                        $insertData['spesifikasi'] = $data[3];
                        $insertData['unit'] = $data[4];
                        $insertData['amount'] = ($data[5] !== '-' && $data[5] !== null) ? (float) preg_replace('/[^0-9]/', '', explode('-', $data[5])[0]) : 0;
                        $insertData['description'] = $data[6];
                    }
                    
                    DB::table('master_standar_harga_satuans')->insert($insertData);
                }
            }
            DB::commit();
            $this->command->info('Data SHS berhasil di-seed dari data hardcoded!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error saat melakukan seed: ' . $e->getMessage());
        }
    }

    /**
     * Returns the SHS data hardcoded.
     */
    private function getShsData(): array
    {
        return array (
  0 => 
  array (
    0 => 
    array (
      0 => '1a',
      1 => 'Uang Hadir Rapat PDAM dengan pihak luar - Dewan Pengawas',
      2 => 'per rapat',
      3 => '200000',
      4 => '',
    ),
    1 => 
    array (
      0 => '1b',
      1 => 'Uang Hadir Rapat PDAM dengan pihak luar - Direksi',
      2 => 'per rapat',
      3 => '200000',
      4 => '',
    ),
    2 => 
    array (
      0 => '1c',
      1 => 'Uang Hadir Rapat PDAM dengan pihak luar - Kabag/Kepala Cabang/Ka SPI',
      2 => 'per rapat',
      3 => '150000',
      4 => '',
    ),
    3 => 
    array (
      0 => '1d',
      1 => 'Uang Hadir Rapat PDAM dengan pihak luar - Kasubag/Ka.Unit/AMD.K',
      2 => 'per rapat',
      3 => '100000',
      4 => '',
    ),
    4 => 
    array (
      0 => '1e',
      1 => 'Uang Hadir Rapat PDAM dengan pihak luar - Staf',
      2 => 'per rapat',
      3 => '75000',
      4 => '',
    ),
    5 => 
    array (
      0 => '2.1',
      1 => 'Uang Hadir Rapat Pihak Luar DPRD - Ketua DPRD',
      2 => 'per rapat',
      3 => '300000',
      4 => '',
    ),
    6 => 
    array (
      0 => '2.2',
      1 => 'Uang Hadir Rapat Pihak Luar DPRD - Wakil Ketua DPRD',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    7 => 
    array (
      0 => '2.3',
      1 => 'Uang Hadir Rapat Pihak Luar DPRD - Sekretaris',
      2 => 'per rapat',
      3 => '200000',
      4 => '',
    ),
    8 => 
    array (
      0 => '2.4',
      1 => 'Uang Hadir Rapat Pihak Luar DPRD - Anggota DPRD',
      2 => 'per rapat',
      3 => '200000',
      4 => '',
    ),
    9 => 
    array (
      0 => '2.5',
      1 => 'KOMISI DPRD - Ketua Komisi DPRD',
      2 => 'per rapat',
      3 => '300000',
      4 => '',
    ),
    10 => 
    array (
      0 => '2.6',
      1 => 'KOMISI DPRD - Wakil Komisi DPRD',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    11 => 
    array (
      0 => '2.7',
      1 => 'KOMISI DPRD - Sekretaris Komisi DPRD',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    12 => 
    array (
      0 => '2.8',
      1 => 'KOMISI DPRD - Anggota Komisi DPRD',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    13 => 
    array (
      0 => '2.9',
      1 => 'Uang Hadir Rapat Pihak Luar - pihak-pihak lain',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    14 => 
    array (
      0 => '3a',
      1 => 'Honorarium Panitia Penyusunan makalah rapat/paparan - Penasihat/pengarah',
      2 => 'per rapat',
      3 => '600000',
      4 => '',
    ),
    15 => 
    array (
      0 => '3b',
      1 => 'Honorarium Panitia Penyusunan makalah rapat/paparan - Pengolah Data',
      2 => 'per rapat',
      3 => '500000',
      4 => '',
    ),
    16 => 
    array (
      0 => '3c',
      1 => 'Honorarium Panitia Penyusunan makalah rapat/paparan - Nara Sumber',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    17 => 
    array (
      0 => '3d',
      1 => 'Honorarium Panitia Penyusunan makalah rapat/paparan - Anggota',
      2 => 'per rapat',
      3 => '250000',
      4 => '',
    ),
    18 => 
    array (
      0 => '4a',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Penanggungjawab/Pejabat Pembuat Komitmen (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '500000',
      4 => '',
    ),
    19 => 
    array (
      0 => '4b',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Penanggungjawab/Pejabat Pembuat Komitmen (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '600000',
      4 => '',
    ),
    20 => 
    array (
      0 => '4c',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Penanggungjawab/Pejabat Pembuat Komitmen (diatas 500 juta s/d 700 juta)',
      2 => 'per pagu/bulan',
      3 => '700000',
      4 => '',
    ),
    21 => 
    array (
      0 => '4d',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Kuasa Pengguna Anggaran (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '500000',
      4 => '',
    ),
    22 => 
    array (
      0 => '4e',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Kuasa Pengguna Anggaran (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '600000',
      4 => '',
    ),
    23 => 
    array (
      0 => '4f',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Kuasa Pengguna Anggaran (diatas 500 juta s/d 700 juta)',
      2 => 'per pagu/bulan',
      3 => '700000',
      4 => '',
    ),
    24 => 
    array (
      0 => '4g',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Ketua (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '400000',
      4 => 'Honorarium diberikan per orang/bulan sesuai dengan nilai pagu SPK/per bulan',
    ),
    25 => 
    array (
      0 => '4h',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Ketua (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '500000',
      4 => 'Honorarium diberikan per orang/bulan sesuai dengan nilai pagu SPK/per bulan',
    ),
    26 => 
    array (
      0 => '4i',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Ketua (diatas 500 juta s/d 700 juta)',
      2 => 'per pagu/bulan',
      3 => '600000',
      4 => 'Honorarium diberikan per orang/bulan sesuai dengan nilai pagu SPK/per bulan',
    ),
    27 => 
    array (
      0 => '4j',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Sekretaris (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '350000',
      4 => '',
    ),
    28 => 
    array (
      0 => '4k',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Sekretaris (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '450000',
      4 => '',
    ),
    29 => 
    array (
      0 => '4l',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Sekretaris (diatas 500 juta s/d 700 juta)',
      2 => 'per pagu/bulan',
      3 => '550000',
      4 => '',
    ),
    30 => 
    array (
      0 => '4m',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Anggota (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '250000',
      4 => '',
    ),
    31 => 
    array (
      0 => '4n',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Anggota (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '350000',
      4 => '',
    ),
    32 => 
    array (
      0 => '4o',
      1 => 'Honorarium Panitia Pengadaan Barang/Jasa - Anggota (diatas 500 juta s/d 700 juta)',
      2 => 'per pagu/bulan',
      3 => '450000',
      4 => '',
    ),
    33 => 
    array (
      0 => '5a',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Ketua (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '200000',
      4 => 'Honorarium diberikan per orang/bulan sesuai dengan nilai pagu SPK/per bulan',
    ),
    34 => 
    array (
      0 => '5b',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Ketua (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '250000',
      4 => '',
    ),
    35 => 
    array (
      0 => '5c',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Ketua (diatas 500 juta s/d 2 Miliar)',
      2 => 'per pagu/bulan',
      3 => '350000',
      4 => '',
    ),
    36 => 
    array (
      0 => '5d',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Sekretaris (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '175000',
      4 => '',
    ),
    37 => 
    array (
      0 => '5e',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Sekretaris (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '225000',
      4 => '',
    ),
    38 => 
    array (
      0 => '5f',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Sekretaris (diatas 500 juta s/d 2 Miliar)',
      2 => 'per pagu/bulan',
      3 => '300000',
      4 => '',
    ),
    39 => 
    array (
      0 => '5g',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Anggota (0 s/d 200 juta)',
      2 => 'per pagu/bulan',
      3 => '150000',
      4 => '',
    ),
    40 => 
    array (
      0 => '5h',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Anggota (diatas 200 juta s/d 500 juta)',
      2 => 'per pagu/bulan',
      3 => '200000',
      4 => '',
    ),
    41 => 
    array (
      0 => '5i',
      1 => 'Honorarium Panitia Pemeriksa Barang/Jasa - Anggota (diatas 500 juta s/d 2 Miliar)',
      2 => 'per pagu/bulan',
      3 => '250000',
      4 => '',
    ),
    42 => 
    array (
      0 => '6a',
      1 => 'Honorarium Tim PPTK - Ketua',
      2 => 'orang/bulan',
      3 => '300000',
      4 => 'Honorarium diberikan apabila ada kegiatan tim',
    ),
    43 => 
    array (
      0 => '6b',
      1 => 'Honorarium Tim PPTK - Sekretaris',
      2 => 'orang/bulan',
      3 => '250000',
      4 => '',
    ),
    44 => 
    array (
      0 => '6c',
      1 => 'Honorarium Tim PPTK - Koordinator',
      2 => 'orang/bulan',
      3 => '200000',
      4 => '',
    ),
    45 => 
    array (
      0 => '6d',
      1 => 'Honorarium Tim PPTK - Anggota/Pendukung',
      2 => 'orang/bulan',
      3 => '175000',
      4 => '',
    ),
    46 => 
    array (
      0 => '6e',
      1 => 'Honorarium Tim PPTK - Pendamping 1',
      2 => 'orang/bulan',
      3 => '140000',
      4 => '',
    ),
    47 => 
    array (
      0 => '6f',
      1 => 'Honorarium Tim PPTK - Pendamping 2',
      2 => 'orang/bulan',
      3 => '122500',
      4 => '',
    ),
    48 => 
    array (
      0 => '7a',
      1 => 'Honorarium Tim Pelaksana Kerjasama Bidang Hukum Perdata dan TUN dengan Kejaksaan - Penanggungjawab',
      2 => 'orang/bulan',
      3 => '2250000',
      4 => '',
    ),
    49 => 
    array (
      0 => '7b',
      1 => 'Honorarium Tim Pelaksana Kerjasama Bidang Hukum Perdata dan TUN dengan Kejaksaan - Ketua',
      2 => 'orang/bulan',
      3 => '1750000',
      4 => '',
    ),
    50 => 
    array (
      0 => '7c',
      1 => 'Honorarium Tim Pelaksana Kerjasama Bidang Hukum Perdata dan TUN dengan Kejaksaan - Anggota Kasubag/Kasi',
      2 => 'orang/bulan',
      3 => '1200000',
      4 => '',
    ),
    51 => 
    array (
      0 => '7d',
      1 => 'Honorarium Tim Pelaksana Kerjasama Bidang Hukum Perdata dan TUN dengan Kejaksaan - Anggota Jaksa Fungsional',
      2 => 'orang/bulan',
      3 => '1000000',
      4 => '',
    ),
    52 => 
    array (
      0 => '7e',
      1 => 'Honorarium Tim Pelaksana Kerjasama Bidang Hukum Perdata dan TUN dengan Kejaksaan - Anggota Staf',
      2 => 'orang/bulan',
      3 => '500000',
      4 => '',
    ),
    53 => 
    array (
      0 => '8a',
      1 => 'Honorarium Tim Kegiatan Lainnya - Penasehat / Penanggungjawab',
      2 => 'orang/bulan/pekerjaan',
      3 => '400000',
      4 => 'Honorarium diberikan apabila ada kegiatan tim',
    ),
    54 => 
    array (
      0 => '8b',
      1 => 'Honorarium Tim Kegiatan Lainnya - Ketua',
      2 => 'orang/bulan/pekerjaan',
      3 => '325000',
      4 => '',
    ),
    55 => 
    array (
      0 => '8c',
      1 => 'Honorarium Tim Kegiatan Lainnya - Sekretaris',
      2 => 'orang/bulan/pekerjaan',
      3 => '275000',
      4 => '',
    ),
    56 => 
    array (
      0 => '8d',
      1 => 'Honorarium Tim Kegiatan Lainnya - Koordinator',
      2 => 'orang/bulan/pekerjaan',
      3 => '225000',
      4 => '',
    ),
    57 => 
    array (
      0 => '8e',
      1 => 'Honorarium Tim Kegiatan Lainnya - Anggota/Pendukung',
      2 => 'orang/bulan/pekerjaan',
      3 => '175000',
      4 => '',
    ),
    58 => 
    array (
      0 => '9a',
      1 => 'Honorarium Tim Pengadaan Tanah - Penanggungjawab',
      2 => 'orang/bulan',
      3 => '350000',
      4 => 'Honorarium diberikan apabila ada kegiatan pengadaan tanah',
    ),
    59 => 
    array (
      0 => '9b',
      1 => 'Honorarium Tim Pengadaan Tanah - Ketua',
      2 => 'orang/bulan',
      3 => '300000',
      4 => '',
    ),
    60 => 
    array (
      0 => '9c',
      1 => 'Honorarium Tim Pengadaan Tanah - Sekretaris',
      2 => 'orang/bulan',
      3 => '250000',
      4 => '',
    ),
    61 => 
    array (
      0 => '9d',
      1 => 'Honorarium Tim Pengadaan Tanah - Anggota',
      2 => 'orang/bulan',
      3 => '200000',
      4 => '',
    ),
    62 => 
    array (
      0 => '10a',
      1 => 'HONORARIUM TIM PENGELOLAAN ASET PERUSAHAAN - Penanggungjawab',
      2 => 'orang/kegiatan',
      3 => '350000',
      4 => 'Honorarium diberikan apabila ada kegiatan pengadaan penghapusan/penjualan aset perusahaan',
    ),
    63 => 
    array (
      0 => '10b',
      1 => 'HONORARIUM TIM PENGELOLAAN ASET PERUSAHAAN - Ketua',
      2 => 'orang/kegiatan',
      3 => '300000',
      4 => '',
    ),
    64 => 
    array (
      0 => '10c',
      1 => 'HONORARIUM TIM PENGELOLAAN ASET PERUSAHAAN - Sekretaris',
      2 => 'orang/kegiatan',
      3 => '250000',
      4 => '',
    ),
    65 => 
    array (
      0 => '10d',
      1 => 'HONORARIUM TIM PENGELOLAAN ASET PERUSAHAAN - Anggota',
      2 => 'orang/kegiatan',
      3 => '200000',
      4 => '',
    ),
    66 => 
    array (
      0 => '11a',
      1 => 'UPAH KERJA LEMBUR - Golongan III',
      2 => 'upah lembur/jam',
      3 => '16000',
      4 => 'Pembayaran uang lembur dibayarkan setelah ada kelengkapan administrasi yang ditemukan.',
    ),
    67 => 
    array (
      0 => '11b',
      1 => 'UPAH KERJA LEMBUR - Golongan II',
      2 => 'upah lembur/jam',
      3 => '15000',
      4 => '',
    ),
    68 => 
    array (
      0 => '11c',
      1 => 'UPAH KERJA LEMBUR - Golongan I/Capeg',
      2 => 'upah lembur/jam',
      3 => '14000',
      4 => '',
    ),
    69 => 
    array (
      0 => '11d',
      1 => 'UPAH KERJA LEMBUR - Honorer/Kontrak',
      2 => 'upah lembur/jam',
      3 => '13000',
      4 => '',
    ),
    70 => 
    array (
      0 => '11e',
      1 => 'Uang makan lembur',
      2 => '-',
      3 => '25000',
      4 => 'Uang makan lembur dibayarkan setelah ada kelengkapan administrasi yang ditentukan',
    ),
    71 => 
    array (
      0 => '12a',
      1 => 'EKSTRA FOODING (Surat tugas dalam wilayah, Penaburan bahan kimia, Penataan arsip, dll)',
      2 => 'per orang/hari',
      3 => '25000',
      4 => '1. Ekstra Fooding diberikan dalam bentuk makan\n2. Ekstra Fooding untuk pelayanan rekening diberikan tiap tanggal 20',
    ),
    72 => 
    array (
      0 => '12b',
      1 => 'EKSTRA FOODING - Petugas satpam / Kebersihan kantor',
      2 => 'per orang/hari',
      3 => '10000',
      4 => '',
    ),
    73 => 
    array (
      0 => '13a',
      1 => 'INSENTIF PIKET - Piket Hari Kerja - Teknik / Ahli',
      2 => 'per orang/hari',
      3 => '35000',
      4 => '',
    ),
    74 => 
    array (
      0 => '13b',
      1 => 'INSENTIF PIKET - Piket Hari Kerja - Umum',
      2 => 'per orang/hari',
      3 => '30000',
      4 => '',
    ),
    75 => 
    array (
      0 => '13c',
      1 => 'INSENTIF PIKET - Piket Hari Minggu - Teknik / Ahli',
      2 => 'per orang/hari',
      3 => '45000',
      4 => '',
    ),
    76 => 
    array (
      0 => '13d',
      1 => 'INSENTIF PIKET - Piket Hari Minggu - Umum',
      2 => 'per orang/hari',
      3 => '40000',
      4 => '',
    ),
    77 => 
    array (
      0 => '13e',
      1 => 'INSENTIF PIKET - Libur Nasional / Cuti Bersama - Teknik / Ahli, Umum, Koordinator',
      2 => 'per orang/hari',
      3 => '60000',
      4 => '',
    ),
    78 => 
    array (
      0 => '13f',
      1 => 'INSENTIF PIKET - Hari Raya Idul Fitri / Idul Adha - Teknik / Ahli, Umum, Koordinator',
      2 => 'per orang/hari',
      3 => '100000',
      4 => '',
    ),
    79 => 
    array (
      0 => '13g',
      1 => 'INSENTIF PIKET - Pindah/Pengaturan Valve, Pengukuran Debit Air (Diluar Jam Kerja)',
      2 => 'per orang/hari',
      3 => '30000',
      4 => '',
    ),
    80 => 
    array (
      0 => '13h',
      1 => 'INSENTIF PIKET - Inspeksi Jaringan Malam - Kabag / Kacab',
      2 => 'per orang/hari',
      3 => '70000',
      4 => '',
    ),
    81 => 
    array (
      0 => '13i',
      1 => 'INSENTIF PIKET - Inspeksi Jaringan Malam - Kasubag / Kasie',
      2 => 'per orang/hari',
      3 => '60000',
      4 => '',
    ),
    82 => 
    array (
      0 => '13j',
      1 => 'INSENTIF PIKET - Inspeksi Jaringan Malam - Pegawai / Honor Tetap',
      2 => 'per orang/hari',
      3 => '50000',
      4 => '',
    ),
    83 => 
    array (
      0 => '13k',
      1 => 'INSENTIF PIKET - Inspeksi Jaringan Malam - Kontrak',
      2 => 'per orang/hari',
      3 => '40000',
      4 => '',
    ),
    84 => 
    array (
      0 => '13l',
      1 => 'INSENTIF PIKET - Perbaikan Pipa/Aliran Dalam Keadaan Darurat - Kabag / Kacab',
      2 => 'per orang/hari',
      3 => '70000',
      4 => 'Hari Libur + Rp. 10.000,-',
    ),
    85 => 
    array (
      0 => '13m',
      1 => 'INSENTIF PIKET - Perbaikan Pipa/Aliran Dalam Keadaan Darurat - Kasubag / Kasie',
      2 => 'per orang/hari',
      3 => '50000',
      4 => 'Hari Libur + Rp. 10.000,-',
    ),
    86 => 
    array (
      0 => '13n',
      1 => 'INSENTIF PIKET - Perbaikan Pipa/Aliran Dalam Keadaan Darurat - Pegawai / Honor Tetap',
      2 => 'per orang/hari',
      3 => '50000',
      4 => 'Hari Libur + Rp. 10.000,-',
    ),
    87 => 
    array (
      0 => '13o',
      1 => 'INSENTIF PIKET - Perbaikan Pipa/Aliran Dalam Keadaan Darurat - Kontrak',
      2 => 'per orang/hari',
      3 => '40000',
      4 => 'Hari Libur + Rp. 10.000,-',
    ),
    88 => 
    array (
      0 => '14a',
      1 => 'TUGAS BELAJAR DIPLOMA 3 - SPP',
      2 => 'orang/semester',
      3 => '-',
      4 => 'sesuai dengan ketentuan setempat',
    ),
    89 => 
    array (
      0 => '14b',
      1 => 'TUGAS BELAJAR DIPLOMA 3 - Uang saku',
      2 => 'orang/bulan',
      3 => '350000',
      4 => '',
    ),
    90 => 
    array (
      0 => '14c',
      1 => 'TUGAS BELAJAR DIPLOMA 3 - Bantuan buku',
      2 => 'orang/semester',
      3 => '150000',
      4 => '',
    ),
    91 => 
    array (
      0 => '14d',
      1 => 'TUGAS BELAJAR DIPLOMA 3 - Bantuan skripsi/tugas akhir',
      2 => 'orang/kegiatan',
      3 => '350000',
      4 => '',
    ),
    92 => 
    array (
      0 => '14e',
      1 => 'TUGAS BELAJAR DIPLOMA 3 - Bantuan study lapangan/riset',
      2 => 'orang/kegiatan',
      3 => '250000',
      4 => '',
    ),
    93 => 
    array (
      0 => '14f',
      1 => 'TUGAS BELAJAR DIPLOMA 4 - Uang saku',
      2 => 'orang/bulan',
      3 => '450000',
      4 => '',
    ),
    94 => 
    array (
      0 => '14g',
      1 => 'TUGAS BELAJAR DIPLOMA 4 - Bantuan buku',
      2 => 'orang/semester',
      3 => '200000',
      4 => '',
    ),
    95 => 
    array (
      0 => '14h',
      1 => 'TUGAS BELAJAR DIPLOMA 4 - Bantuan skripsi/tugas akhir',
      2 => 'orang/kegiatan',
      3 => '500000',
      4 => '',
    ),
    96 => 
    array (
      0 => '14i',
      1 => 'TUGAS BELAJAR DIPLOMA 4 - Bantuan study lapangan/riset',
      2 => 'orang/kegiatan',
      3 => '250000',
      4 => '',
    ),
    97 => 
    array (
      0 => '14j',
      1 => 'TUGAS BELAJAR STRATA 1 / S1 - Uang saku',
      2 => 'orang/bulan',
      3 => '500000',
      4 => '',
    ),
    98 => 
    array (
      0 => '14k',
      1 => 'TUGAS BELAJAR STRATA 1 / S1 - Bantuan buku',
      2 => 'orang/semester',
      3 => '200000',
      4 => '',
    ),
    99 => 
    array (
      0 => '14l',
      1 => 'TUGAS BELAJAR STRATA 1 / S1 - Bantuan skripsi/tugas akhir',
      2 => 'orang/kegiatan',
      3 => '500000',
      4 => '',
    ),
    100 => 
    array (
      0 => '14m',
      1 => 'TUGAS BELAJAR STRATA 1 / S1 - Bantuan study lapangan/riset',
      2 => 'orang/kegiatan',
      3 => '250000',
      4 => '',
    ),
    101 => 
    array (
      0 => '15a',
      1 => 'TUGAS BELAJAR (dengan Surat Tugas) KEJAR.PAKET.C - STRATA 1/S1',
      2 => 'orang/semester',
      3 => '2500000',
      4 => '1. SPP strata 1 dan 2 diberikan per orang/semester dan atau separe jumlah SPP dari semester yang akan datang\n2. Ijin belajar yang dibiayai oleh perusahaan adalah ijin belajar yang sesuai dan dibutuhkan oleh perusahaan',
    ),
    102 => 
    array (
      0 => '15b',
      1 => 'TUGAS BELAJAR (dengan Surat Tugas) KEJAR.PAKET.C - STRATA 2/S2',
      2 => 'orang/semester',
      3 => '4000000',
      4 => '',
    ),
  ),
  1 => 
  array (
    0 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Bupati / Wakil Bupati',
      3 => '100000',
      4 => '',
    ),
    1 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '90000',
      4 => '',
    ),
    2 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '75000',
      4 => '',
    ),
    3 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '65000',
      4 => '',
    ),
    4 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '55000',
      4 => '',
    ),
    5 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Staf - Gol III',
      3 => '50000',
      4 => '',
    ),
    6 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Staf - Gol II / Driver',
      3 => '45000',
      4 => '',
    ),
    7 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Staf - Gol I / Capeg',
      3 => '40000',
      4 => '',
    ),
    8 => 
    array (
      0 => 'DALAM WILAYAH PURBALINGGA (min. 15 Km)',
      1 => 'Dalam Wilayah Purbalingga',
      2 => 'Staf - Kontrak / Honorer',
      3 => '35000',
      4 => '',
    ),
    9 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Bupati / Wakil Bupati',
      3 => '250000',
      4 => '4 star ****',
    ),
    10 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '200000',
      4 => '4 star ****',
    ),
    11 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '150000',
      4 => '3 star ***',
    ),
    12 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '150000',
      4 => '3 star ***',
    ),
    13 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '100000',
      4 => '2 star **',
    ),
    14 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Staf - Gol III',
      3 => '75000',
      4 => 'klas melati',
    ),
    15 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Staf - Gol II / Driver',
      3 => '75000',
      4 => 'klas melati',
    ),
    16 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Staf - Gol I / Capeg',
      3 => '60000',
      4 => 'klas melati',
    ),
    17 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA I (Banyumas dan Banjarnegara)',
      2 => 'Staf - Kontrak / Honorer',
      3 => '50000',
      4 => 'klas melati',
    ),
    18 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II (Jateng: Jepara, Rembang, Kudus, Pati, Blora, Wonogiri, Karanganyar, Sragen, Klaten, Sukoharjo, Surakarta, Boyolali, Semarang, Demak, Grobogan, Salatiga, Kendal)',
      2 => 'Bupati / Wakil Bupati',
      3 => '700000',
      4 => '4 star ****',
    ),
    19 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '650000',
      4 => '4 star ****',
    ),
    20 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '600000',
      4 => '3 star ***',
    ),
    21 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '550000',
      4 => '3 star ***',
    ),
    22 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '500000',
      4 => '2 star **',
    ),
    23 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Staf - Gol III',
      3 => '420000',
      4 => 'klas melati',
    ),
    24 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Staf - Gol II / Driver',
      3 => '420000',
      4 => 'klas melati',
    ),
    25 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Staf - Gol I / Capeg',
      3 => '350000',
      4 => 'klas melati',
    ),
    26 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA II',
      2 => 'Staf - Kontrak / Honorer',
      3 => '300000',
      4 => 'klas melati',
    ),
    27 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III (Temanggung, Wonosobo, Kebumen, Purworejo, Batang)',
      2 => 'Bupati / Wakil Bupati',
      3 => '350000',
      4 => '4 star ****',
    ),
    28 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '500000',
      4 => '4 star ****',
    ),
    29 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '450000',
      4 => '3 star ***',
    ),
    30 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '400000',
      4 => '3 star ***',
    ),
    31 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '350000',
      4 => '2 star **',
    ),
    32 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Staf - Gol III',
      3 => '300000',
      4 => 'klas melati',
    ),
    33 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Staf - Gol II / Driver',
      3 => '300000',
      4 => 'klas melati',
    ),
    34 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Staf - Gol I / Capeg',
      3 => '250000',
      4 => 'klas melati',
    ),
    35 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA III',
      2 => 'Staf - Kontrak / Honorer',
      3 => '200000',
      4 => 'klas melati',
    ),
    36 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV (Pekalongan, Pemalang, Brebes, Tegal, Cilacap)',
      2 => 'Bupati / Wakil Bupati',
      3 => '550000',
      4 => '4 star ****',
    ),
    37 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '450000',
      4 => '4 star ****',
    ),
    38 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '400000',
      4 => '3 star ***',
    ),
    39 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '350000',
      4 => '3 star ***',
    ),
    40 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '300000',
      4 => '2 star **',
    ),
    41 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Staf - Gol III',
      3 => '250000',
      4 => 'klas melati',
    ),
    42 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Staf - Gol II / Driver',
      3 => '250000',
      4 => 'klas melati',
    ),
    43 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Staf - Gol I / Capeg',
      3 => '200000',
      4 => 'klas melati',
    ),
    44 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IV',
      2 => 'Staf - Kontrak / Honorer',
      3 => '150000',
      4 => 'klas melati',
    ),
    45 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V (Yogyakarta / DIY, Magelang)',
      2 => 'Bupati / Wakil Bupati',
      3 => '770000',
      4 => '4 star ****',
    ),
    46 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '650000',
      4 => '4 star ****',
    ),
    47 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '600000',
      4 => '3 star ***',
    ),
    48 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '550000',
      4 => '3 star ***',
    ),
    49 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '500000',
      4 => '2 star **',
    ),
    50 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Staf - Gol III',
      3 => '420000',
      4 => 'klas melati',
    ),
    51 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Staf - Gol II / Driver',
      3 => '420000',
      4 => 'klas melati',
    ),
    52 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Staf - Gol I / Capeg',
      3 => '370000',
      4 => 'klas melati',
    ),
    53 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA V',
      2 => 'Staf - Kontrak / Honorer',
      3 => '320000',
      4 => 'klas melati',
    ),
    54 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI (DKI Jakarta, Banten, Bogor, Bandung)',
      2 => 'Bupati / Wakil Bupati',
      3 => '800000',
      4 => '4 star ****',
    ),
    55 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '750000',
      4 => '4 star ****',
    ),
    56 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '700000',
      4 => '3 star ***',
    ),
    57 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '650000',
      4 => '3 star ***',
    ),
    58 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '600000',
      4 => '2 star **',
    ),
    59 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Staf - Gol III',
      3 => '530000',
      4 => 'klas melati',
    ),
    60 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Staf - Gol II / Driver',
      3 => '530000',
      4 => 'klas melati',
    ),
    61 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Staf - Gol I / Capeg',
      3 => '480000',
      4 => 'klas melati',
    ),
    62 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VI',
      2 => 'Staf - Kontrak / Honorer',
      3 => '430000',
      4 => 'klas melati',
    ),
    63 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII (Wilayah Jawa Barat)',
      2 => 'Bupati / Wakil Bupati',
      3 => '750000',
      4 => '4 star ****',
    ),
    64 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '700000',
      4 => '4 star ****',
    ),
    65 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '650000',
      4 => '3 star ***',
    ),
    66 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '600000',
      4 => '3 star ***',
    ),
    67 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '550000',
      4 => '2 star **',
    ),
    68 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Staf - Gol III',
      3 => '480000',
      4 => 'klas melati',
    ),
    69 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Staf - Gol II / Driver',
      3 => '480000',
      4 => 'klas melati',
    ),
    70 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Staf - Gol I / Capeg',
      3 => '430000',
      4 => 'klas melati',
    ),
    71 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VII',
      2 => 'Staf - Kontrak / Honorer',
      3 => '430000',
      4 => 'klas melati',
    ),
    72 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII (Jawa Timur)',
      2 => 'Bupati / Wakil Bupati',
      3 => '750000',
      4 => '4 star ****',
    ),
    73 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '700000',
      4 => '4 star ****',
    ),
    74 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '650000',
      4 => '3 star ***',
    ),
    75 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '600000',
      4 => '3 star ***',
    ),
    76 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '550000',
      4 => '2 star **',
    ),
    77 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Staf - Gol III',
      3 => '480000',
      4 => 'klas melati',
    ),
    78 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Staf - Gol II / Driver',
      3 => '480000',
      4 => 'klas melati',
    ),
    79 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Staf - Gol I / Capeg',
      3 => '430000',
      4 => 'klas melati',
    ),
    80 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA VIII',
      2 => 'Staf - Kontrak / Honorer',
      3 => '430000',
      4 => 'klas melati',
    ),
    81 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX (Ke luar Jawa - Kecuali Papua)',
      2 => 'Bupati / Wakil Bupati',
      3 => '690000',
      4 => '4 star ****',
    ),
    82 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '640000',
      4 => '4 star ****',
    ),
    83 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '590000',
      4 => '3 star ***',
    ),
    84 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '540000',
      4 => '3 star ***',
    ),
    85 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '490000',
      4 => '2 star **',
    ),
    86 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Staf - Gol III',
      3 => '440000',
      4 => 'klas melati',
    ),
    87 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Staf - Gol II / Driver',
      3 => '440000',
      4 => 'klas melati',
    ),
    88 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Staf - Gol I / Capeg',
      3 => '390000',
      4 => 'klas melati',
    ),
    89 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA IX',
      2 => 'Staf - Kontrak / Honorer',
      3 => '340000',
      4 => 'klas melati',
    ),
    90 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X (Papua)',
      2 => 'Bupati / Wakil Bupati',
      3 => '830000',
      4 => '4 star ****',
    ),
    91 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '780000',
      4 => '4 star ****',
    ),
    92 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '730000',
      4 => '3 star ***',
    ),
    93 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Kepala Bagian / Ka.SPI / Kepala Cabang',
      3 => '680000',
      4 => '3 star ***',
    ),
    94 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Kasubag / Ka Unit / AMD',
      3 => '630000',
      4 => '2 star **',
    ),
    95 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Staf - Gol III / Kasubag',
      3 => '580000',
      4 => 'klas melati',
    ),
    96 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Staf - Gol II / Driver',
      3 => '580000',
      4 => 'klas melati',
    ),
    97 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Staf - Gol I / Capeg',
      3 => '530000',
      4 => 'klas melati',
    ),
    98 => 
    array (
      0 => 'PERJALANAN DINAS LUAR WILAYAH',
      1 => 'ZONA X',
      2 => 'Staf - Kontrak / Honorer',
      3 => '480000',
      4 => 'klas melati',
    ),
    99 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'Jateng, DIY (Jepara, Rembang, Kudus, Pati, Blora, Wonogiri, Karanganyar, Sragen, Klaten, Sukoharjo, Surakarta, Boyolali, Semarang, Demak, Grobogan, Salatiga)',
      2 => 'Bupati / Wakil Bupati',
      3 => '300000',
      4 => '',
    ),
    100 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'Jateng, DIY (Jepara, Rembang, Kudus, Pati, Blora, Wonogiri, Karanganyar, Sragen, Klaten, Sukoharjo, Surakarta, Boyolali, Semarang, Demak, Grobogan, Salatiga)',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '250000',
      4 => '',
    ),
    101 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'Jateng, DIY (Jepara, Rembang, Kudus, Pati, Blora, Wonogiri, Karanganyar, Sragen, Klaten, Sukoharjo, Surakarta, Boyolali, Semarang, Demak, Grobogan, Salatiga)',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '200000',
      4 => '',
    ),
    102 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'Magelang, Temanggung, Wonosobo, Kebumen, Purworejo, Batang, Pekalongan, Brebes, Tegal, Cilacap',
      2 => 'Bupati / Wakil Bupati',
      3 => '250000',
      4 => '',
    ),
    103 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'Magelang, Temanggung, Wonosobo, Kebumen, Purworejo, Batang, Pekalongan, Brebes, Tegal, Cilacap',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '200000',
      4 => '',
    ),
    104 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'Magelang, Temanggung, Wonosobo, Kebumen, Purworejo, Batang, Pekalongan, Brebes, Tegal, Cilacap',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '150000',
      4 => '',
    ),
    105 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL DKI Jakarta, Banten, Bogor',
      2 => 'Bupati / Wakil Bupati',
      3 => '350000',
      4 => '',
    ),
    106 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL DKI Jakarta, Banten, Bogor',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '300000',
      4 => '',
    ),
    107 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL DKI Jakarta, Banten, Bogor',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '250000',
      4 => '',
    ),
    108 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL DKI Jawa Barat Kecuali Bogor',
      2 => 'Bupati / Wakil Bupati',
      3 => '300000',
      4 => '',
    ),
    109 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL DKI Jawa Barat Kecuali Bogor',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '250000',
      4 => '',
    ),
    110 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL DKI Jawa Barat Kecuali Bogor',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '200000',
      4 => '',
    ),
    111 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL JAWA TIMUR',
      2 => 'Bupati / Wakil Bupati',
      3 => '400000',
      4 => '',
    ),
    112 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL JAWA TIMUR',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '350000',
      4 => '',
    ),
    113 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'WIL JAWA TIMUR',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '300000',
      4 => '',
    ),
    114 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'KE LUAR JAWA',
      2 => 'Bupati / Wakil Bupati',
      3 => '500000',
      4 => '',
    ),
    115 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'KE LUAR JAWA',
      2 => 'Direktur / Setda Ketua DPRD',
      3 => '400000',
      4 => '',
    ),
    116 => 
    array (
      0 => 'REPRESENTASI PERJALANAN DINAS',
      1 => 'KE LUAR JAWA',
      2 => 'Dewan Pengawas / Asisten / Anggota DPRD',
      3 => '350000',
      4 => '',
    ),
  ),
  2 => 
  array (
    0 => 
    array (
      0 => 'Honorarium Juri Lomba',
      1 => '1',
      2 => 'Juri Lomba',
      3 => 'Internal',
      4 => 'OK',
      5 => '200000',
      6 => '',
    ),
    1 => 
    array (
      0 => 'Honorarium Juri Lomba',
      1 => '2',
      2 => 'Juri Lomba',
      3 => 'Lokal',
      4 => 'OK',
      5 => '300000',
      6 => '',
    ),
    2 => 
    array (
      0 => 'Honorarium Juri Lomba',
      1 => '3',
      2 => 'Juri Lomba',
      3 => 'Regional',
      4 => 'OK',
      5 => '500000',
      6 => '',
    ),
    3 => 
    array (
      0 => 'Honorarium Juri Lomba',
      1 => '4',
      2 => 'Juri Lomba',
      3 => 'Nasional',
      4 => 'OK',
      5 => '1000000',
      6 => '',
    ),
    4 => 
    array (
      0 => 'Honorarium Instruktur Senam',
      1 => '1',
      2 => 'Instruktur Senam',
      3 => 'Lokal',
      4 => 'OH',
      5 => '150000',
      6 => '',
    ),
    5 => 
    array (
      0 => 'Honorarium Instruktur Senam',
      1 => '2',
      2 => 'Instruktur Senam',
      3 => 'Regional',
      4 => 'OH',
      5 => '250000',
      6 => '',
    ),
    6 => 
    array (
      0 => 'Honorarium Instruktur Senam',
      1 => '3',
      2 => 'Instruktur Senam',
      3 => 'Event',
      4 => 'OH',
      5 => '500000',
      6 => '',
    ),
    7 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '1',
      2 => 'Perkara Hukum Pidana',
      3 => 'Tahap Penyidikan',
      4 => 'Keg',
      5 => '2000000',
      6 => '',
    ),
    8 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '2',
      2 => 'Perkara Hukum Pidana',
      3 => 'Tahap Persidangan di Pengadilan Tk. I',
      4 => 'Keg',
      5 => '3000000',
      6 => '',
    ),
    9 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '3',
      2 => 'Perkara Hukum Pidana',
      3 => 'Tahap Persidangan di Pengadilan Tk. Banding',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    10 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '4',
      2 => 'Perkara Hukum Pidana',
      3 => 'Tahap Persidangan di Pengadilan Tk. Kasasi',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    11 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '5',
      2 => 'Perkara Hukum Pidana',
      3 => 'Tahap Peninjauan Kembali',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    12 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '6',
      2 => 'Perkara Hukum Perdata',
      3 => 'Tahap Gugatan',
      4 => 'Keg',
      5 => '2000000',
      6 => '',
    ),
    13 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '7',
      2 => 'Perkara Hukum Perdata',
      3 => 'Tahap Persidangan di Pengadilan Tk. I',
      4 => 'Keg',
      5 => '3000000',
      6 => '',
    ),
    14 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '8',
      2 => 'Perkara Hukum Perdata',
      3 => 'Tahap Persidangan di Pengadilan Tk. Banding',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    15 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '9',
      2 => 'Perkara Hukum Perdata',
      3 => 'Tahap Persidangan di Pengadilan Tk. Kasasi',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    16 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '10',
      2 => 'Perkara Hukum Perdata',
      3 => 'Tahap Peninjauan Kembali',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    17 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '11',
      2 => 'Perkara Hukum Tata Usaha Negara',
      3 => 'Tahap Pemeriksaan Pendahuluan',
      4 => 'Keg',
      5 => '2000000',
      6 => '',
    ),
    18 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '12',
      2 => 'Perkara Hukum Tata Usaha Negara',
      3 => 'Tahap Persidangan di Pengadilan Tk. I',
      4 => 'Keg',
      5 => '3000000',
      6 => '',
    ),
    19 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '13',
      2 => 'Perkara Hukum Tata Usaha Negara',
      3 => 'Tahap Persidangan di Pengadilan Tk. Banding',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    20 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '14',
      2 => 'Perkara Hukum Tata Usaha Negara',
      3 => 'Tahap Persidangan di Pengadilan Tk. Kasasi',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    21 => 
    array (
      0 => 'Honorarium Perkara Hukum',
      1 => '15',
      2 => 'Perkara Hukum Tata Usaha Negara',
      3 => 'Tahap Peninjauan Kembali',
      4 => 'Keg',
      5 => '1000000',
      6 => '',
    ),
    22 => 
    array (
      0 => 'Honorarium Narasumber / Pembahas',
      1 => '1',
      2 => 'Narasumber/Pembahas',
      3 => 'Menteri/Pejabat Setingkat Menteri/Pejabat Negara Lainnya',
      4 => 'OJ',
      5 => '1700000',
      6 => '',
    ),
    23 => 
    array (
      0 => 'Honorarium Narasumber / Pembahas',
      1 => '2',
      2 => 'Narasumber/Pembahas',
      3 => 'Kepala Daerah/Pejabat Setingkat Kepala Daerah/Pejabat Daerah Lainnya yang Disetarakan',
      4 => 'OJ',
      5 => '1400000',
      6 => '',
    ),
    24 => 
    array (
      0 => 'Honorarium Narasumber / Pembahas',
      1 => '3',
      2 => 'Narasumber/Pembahas',
      3 => 'Pejabat Eselon I atau yang Disetarakan',
      4 => 'OJ',
      5 => '1200000',
      6 => '',
    ),
    25 => 
    array (
      0 => 'Honorarium Narasumber / Pembahas',
      1 => '4',
      2 => 'Narasumber/Pembahas',
      3 => 'Pejabat Eselon II atau yang Disetarakan',
      4 => 'OJ',
      5 => '1000000',
      6 => '',
    ),
    26 => 
    array (
      0 => 'Honorarium Narasumber / Pembahas',
      1 => '5',
      2 => 'Narasumber/Pembahas',
      3 => 'Pejabat Eselon III atau yang Disetarakan',
      4 => 'OJ',
      5 => '900000',
      6 => '',
    ),
    27 => 
    array (
      0 => 'Honorarium Narasumber / Pembahas',
      1 => '6',
      2 => 'Narasumber/Pembahas',
      3 => 'Anggota Masyarakat yang Memiliki Kapasitas',
      4 => 'OJ',
      5 => '900000',
      6 => '',
    ),
    28 => 
    array (
      0 => 'Honorarium Moderator, Pembawa Acara, dan Rohaniawan',
      1 => '1',
      2 => 'Moderator',
      3 => '-',
      4 => 'OK',
      5 => '350000',
      6 => '',
    ),
    29 => 
    array (
      0 => 'Honorarium Moderator, Pembawa Acara, dan Rohaniawan',
      1 => '2',
      2 => 'Pembawa Acara',
      3 => 'Acara yang Melibatkan Bupati',
      4 => 'OK',
      5 => '200000',
      6 => '',
    ),
    30 => 
    array (
      0 => 'Honorarium Moderator, Pembawa Acara, dan Rohaniawan',
      1 => '3',
      2 => 'Rohaniawan Pihak Dalam',
      3 => '-',
      4 => 'OK',
      5 => '250000',
      6 => '',
    ),
    31 => 
    array (
      0 => 'Honorarium Moderator, Pembawa Acara, dan Rohaniawan',
      1 => '4',
      2 => 'Rohaniawan Pihak Luar',
      3 => '-',
      4 => 'OK',
      5 => '500000 - 750000',
      6 => '',
    ),
    32 => 
    array (
      0 => 'Honorarium Pemberi Keterangan Ahli / Saksi Ahli dan Beracara',
      1 => '1',
      2 => 'Pemberi Keterangan Ahli / Saksi Ahli',
      3 => '-',
      4 => 'OK',
      5 => '1800000',
      6 => '',
    ),
    33 => 
    array (
      0 => 'Honorarium Pemberi Keterangan Ahli / Saksi Ahli dan Beracara',
      1 => '2',
      2 => 'Beracara Bagi Pejabat/ASN yang Mewakili Pemda di Luar Tupoksinya',
      3 => '-',
      4 => 'OK',
      5 => '1800000',
      6 => '',
    ),
    34 => 
    array (
      0 => 'Honorarium Tim Pengelola Teknologi Informasi / Website',
      1 => '1',
      2 => 'Tim Pengelola Teknologi Informasi/Pengelola Website',
      3 => 'Penanggungjawab',
      4 => 'OB',
      5 => '500000',
      6 => '',
    ),
    35 => 
    array (
      0 => 'Honorarium Tim Pengelola Teknologi Informasi / Website',
      1 => '2',
      2 => 'Tim Pengelola Teknologi Informasi/Pengelola Website',
      3 => 'Redaktur',
      4 => 'OB',
      5 => '450000',
      6 => '',
    ),
    36 => 
    array (
      0 => 'Honorarium Tim Pengelola Teknologi Informasi / Website',
      1 => '3',
      2 => 'Tim Pengelola Teknologi Informasi/Pengelola Website',
      3 => 'Editor',
      4 => 'OB',
      5 => '400000',
      6 => '',
    ),
    37 => 
    array (
      0 => 'Honorarium Tim Pengelola Teknologi Informasi / Website',
      1 => '4',
      2 => 'Tim Pengelola Teknologi Informasi/Pengelola Website',
      3 => 'Web Admin',
      4 => 'OB',
      5 => '350000',
      6 => '',
    ),
    38 => 
    array (
      0 => 'Honorarium Tim Pengelola Teknologi Informasi / Website',
      1 => '5',
      2 => 'Tim Pengelola Teknologi Informasi/Pengelola Website',
      3 => 'Web Developer',
      4 => 'OB',
      5 => '300000',
      6 => '',
    ),
    39 => 
    array (
      0 => 'Honorarium Tim Pengelola Teknologi Informasi / Website',
      1 => '6',
      2 => 'Tim Pengelola Teknologi Informasi/Pengelola Website',
      3 => 'Pembuat Artikel',
      4 => 'Org/Hari',
      5 => '100000',
      6 => '',
    ),
    40 => 
    array (
      0 => 'Honorarium Tenaga Ahli',
      1 => '1',
      2 => 'Tenaga Ahli',
      3 => 'Bidang Hukum',
      4 => 'OB',
      5 => '5000000',
      6 => '',
    ),
    41 => 
    array (
      0 => 'Honorarium Tenaga Ahli',
      1 => '2',
      2 => 'Tenaga Ahli',
      3 => 'Bidang Pariwisata',
      4 => 'OB',
      5 => '2500000',
      6 => '',
    ),
    42 => 
    array (
      0 => 'Honorarium Tenaga Ahli',
      1 => '3',
      2 => 'Tenaga Ahli',
      3 => 'Kebijakan Publik',
      4 => 'OB',
      5 => '5000000',
      6 => '',
    ),
    43 => 
    array (
      0 => 'Honorarium Tenaga Ahli',
      1 => '4',
      2 => 'Tenaga Ahli Lainnya',
      3 => 'Sesuai Tugas yang Dilaksanakan',
      4 => 'OB',
      5 => '3250000',
      6 => '',
    ),
  ),
  3 => 
  array (
    0 => 
    array (
      0 => 'ISI STAPLES',
      1 => '1',
      2 => 'Isi staples',
      3 => 'hecht neiches;besar; 24/6',
      4 => 'pak',
      5 => '5000',
      6 => '',
    ),
    1 => 
    array (
      0 => 'ISI STAPLES',
      1 => '2',
      2 => 'Isi staples',
      3 => 'hecht neiches;kecil; 10',
      4 => 'pak',
      5 => '3000',
      6 => '',
    ),
    2 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '1',
      2 => 'Jepitan kertas',
      3 => '',
      4 => 'Buah',
      5 => '5000',
      6 => '',
    ),
    3 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '2',
      2 => 'Clips Paper',
      3 => 'Dus',
      4 => 'Dus',
      5 => '4000',
      6 => '',
    ),
    4 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '3',
      2 => 'Paper Clip',
      3 => 'Jumbo',
      4 => 'Buah',
      5 => '6000',
      6 => '',
    ),
    5 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '4',
      2 => 'Binder clip',
      3 => 'uk : 260',
      4 => 'Pak',
      5 => '28000',
      6 => '',
    ),
    6 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '5',
      2 => 'Binder clip',
      3 => 'uk : 200',
      4 => 'Pak',
      5 => '17000',
      6 => '',
    ),
    7 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '6',
      2 => 'Binder clip',
      3 => 'uk : 155',
      4 => 'Pak',
      5 => '11000',
      6 => '',
    ),
    8 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '7',
      2 => 'Binder clip',
      3 => 'uk : 111',
      4 => 'Pak',
      5 => '7000',
      6 => '',
    ),
    9 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '8',
      2 => 'Binder clip',
      3 => 'uk : 107',
      4 => 'Pak',
      5 => '6000',
      6 => '',
    ),
    10 => 
    array (
      0 => 'PENJEPIT KERTAS',
      1 => '9',
      2 => 'Binder clip',
      3 => 'uk : 105',
      4 => 'Pak',
      5 => '4000',
      6 => '',
    ),
    11 => 
    array (
      0 => 'PENGHAPUS/KOREKTOR',
      1 => '1',
      2 => 'Karet Penghapus',
      3 => 'Besar',
      4 => 'Buah',
      5 => '4000',
      6 => '',
    ),
    12 => 
    array (
      0 => 'PENGHAPUS/KOREKTOR',
      1 => '2',
      2 => 'Karet Penghapus',
      3 => 'Kecil',
      4 => 'Buah',
      5 => '2000',
      6 => '',
    ),
    13 => 
    array (
      0 => 'PENGHAPUS/KOREKTOR',
      1 => '3',
      2 => 'White Board',
      3 => '',
      4 => 'Buah',
      5 => '7500',
      6 => '',
    ),
    14 => 
    array (
      0 => 'PENGHAPUS/KOREKTOR',
      1 => '4',
      2 => 'Tip Ex',
      3 => 'Cair kecil',
      4 => 'Buah',
      5 => '6000',
      6 => '',
    ),
    15 => 
    array (
      0 => 'PENGHAPUS/KOREKTOR',
      1 => '5',
      2 => 'Penghapus pita mesin tik',
      3 => '',
      4 => 'Gulung',
      5 => '15000',
      6 => '',
    ),
    16 => 
    array (
      0 => 'PENGGARIS',
      1 => '1',
      2 => 'Penggaris Besi',
      3 => '60 cm',
      4 => 'Buah',
      5 => '33000',
      6 => '',
    ),
    17 => 
    array (
      0 => 'PENGGARIS',
      1 => '2',
      2 => 'Penggaris Mica',
      3 => 'uk : 30 cm',
      4 => 'Buah',
      5 => '4000',
      6 => '',
    ),
    18 => 
    array (
      0 => 'PENGGARIS',
      1 => '3',
      2 => 'Penggaris Mica',
      3 => 'uk : 60 cm',
      4 => 'Buah',
      5 => '6000',
      6 => '',
    ),
    19 => 
    array (
      0 => 'CUTTER (ALAT TULIS KANTOR)',
      1 => '1',
      2 => 'Gunting kertas',
      3 => 'sedang',
      4 => 'buah',
      5 => '10000',
      6 => '',
    ),
    20 => 
    array (
      0 => 'CUTTER (ALAT TULIS KANTOR)',
      1 => '2',
      2 => 'Gunting kertas',
      3 => 'besar',
      4 => 'buah',
      5 => '16000',
      6 => '',
    ),
    21 => 
    array (
      0 => 'CUTTER (ALAT TULIS KANTOR)',
      1 => '3',
      2 => 'Pisau Cutter',
      3 => 'besar (L-500)',
      4 => 'buah',
      5 => '20000',
      6 => '',
    ),
    22 => 
    array (
      0 => 'CUTTER (ALAT TULIS KANTOR)',
      1 => '4',
      2 => 'Isi Pisau Cutter',
      3 => 'besar',
      4 => 'pak',
      5 => '8000',
      6 => '',
    ),
    23 => 
    array (
      0 => 'CUTTER (ALAT TULIS KANTOR)',
      1 => '5',
      2 => 'Isi Pisau Cutter',
      3 => 'kecil',
      4 => 'pak',
      5 => '5000',
      6 => '',
    ),
    24 => 
    array (
      0 => 'KERTAS HVS',
      1 => '1',
      2 => 'Kertas HVS',
      3 => 'warna; 60 gsm',
      4 => 'rim',
      5 => '86000',
      6 => '',
    ),
    25 => 
    array (
      0 => 'KERTAS HVS',
      1 => '2',
      2 => 'Kertas HVS',
      3 => 'warna; 70 gsm',
      4 => 'rim',
      5 => '96000',
      6 => '',
    ),
    26 => 
    array (
      0 => 'KERTAS HVS',
      1 => '3',
      2 => 'Kertas HVS',
      3 => 'warna; 80 gsm',
      4 => 'rim',
      5 => '132000',
      6 => '',
    ),
    27 => 
    array (
      0 => 'KERTAS HVS',
      1 => '4',
      2 => 'Kertas HVS',
      3 => 'F4; 60 gsm',
      4 => 'rim',
      5 => '58000',
      6 => '',
    ),
    28 => 
    array (
      0 => 'KERTAS HVS',
      1 => '5',
      2 => 'Kertas HVS',
      3 => 'F4; 80 gsm',
      4 => 'rim',
      5 => '79000',
      6 => '',
    ),
    29 => 
    array (
      0 => 'KERTAS HVS',
      1 => '6',
      2 => 'Kertas HVS',
      3 => 'A4; 60 gsm',
      4 => 'rim',
      5 => '61000',
      6 => '',
    ),
    30 => 
    array (
      0 => 'KERTAS HVS',
      1 => '7',
      2 => 'Kertas HVS',
      3 => 'A4; 70 gsm',
      4 => 'rim',
      5 => '60000',
      6 => '',
    ),
    31 => 
    array (
      0 => 'KERTAS HVS',
      1 => '8',
      2 => 'Kertas HVS',
      3 => 'A4; 80 gsm',
      4 => 'rim',
      5 => '67000',
      6 => '',
    ),
    32 => 
    array (
      0 => 'KERTAS HVS',
      1 => '9',
      2 => 'Kertas HVS',
      3 => 'A3; 70 gsm',
      4 => 'rim',
      5 => '146000',
      6 => '',
    ),
    33 => 
    array (
      0 => 'KERTAS HVS',
      1 => '10',
      2 => 'Kertas HVS',
      3 => 'A3; 80 gsm',
      4 => 'rim',
      5 => '152000',
      6 => '',
    ),
    34 => 
    array (
      0 => 'KERTAS HVS',
      1 => '11',
      2 => 'Kertas HVS',
      3 => 'F4; 70 gsm',
      4 => 'rim',
      5 => '63000',
      6 => '',
    ),
    35 => 
    array (
      0 => 'BUKU TULIS',
      1 => '1',
      2 => 'Block note',
      3 => 'garis 1/2 folio 100 lembar',
      4 => 'eksemplar',
      5 => '6000',
      6 => '',
    ),
    36 => 
    array (
      0 => 'BUKU TULIS',
      1 => '2',
      2 => 'Buku Ekspedisi',
      3 => 'isi 100 lembar',
      4 => 'eksemplar',
      5 => '15000',
      6 => '',
    ),
    37 => 
    array (
      0 => 'BUKU TULIS',
      1 => '3',
      2 => 'Buku Ekspedisi',
      3 => 'isi 200 lembar',
      4 => 'eksemplar',
      5 => '31000',
      6 => '',
    ),
    38 => 
    array (
      0 => 'BUKU TULIS',
      1 => '4',
      2 => 'Buku Folio',
      3 => 'bergaris;isi: 100lembar',
      4 => 'eksemplar',
      5 => '26000',
      6 => '',
    ),
    39 => 
    array (
      0 => 'BUKU TULIS',
      1 => '5',
      2 => 'Buku Folio',
      3 => 'bergaris;isi: 200lembar',
      4 => 'eksemplar',
      5 => '55000',
      6 => '',
    ),
    40 => 
    array (
      0 => 'BUKU TULIS',
      1 => '6',
      2 => 'Buku Folio',
      3 => '',
      4 => 'eksemplar',
      5 => '26000',
      6 => '',
    ),
    41 => 
    array (
      0 => 'BUKU TULIS',
      1 => '7',
      2 => 'Buku Kotak',
      3 => '',
      4 => 'eksemplar',
      5 => '5000',
      6 => '',
    ),
    42 => 
    array (
      0 => 'BUKU TULIS',
      1 => '8',
      2 => 'Buku kwarto',
      3 => 'isi: 100lembar',
      4 => 'eksemplar',
      5 => '20000',
      6 => '',
    ),
    43 => 
    array (
      0 => 'BUKU TULIS',
      1 => '9',
      2 => 'Buku kwarto',
      3 => 'isi: 50 lembar',
      4 => 'eksemplar',
      5 => '10000',
      6 => '',
    ),
    44 => 
    array (
      0 => 'BUKU TULIS',
      1 => '10',
      2 => 'Buku Polos',
      3 => '',
      4 => 'eksemplar',
      5 => '7000',
      6 => '',
    ),
    45 => 
    array (
      0 => 'BUKU TULIS',
      1 => '11',
      2 => 'Buku Tulis',
      3 => 'isi: 38 lembar',
      4 => 'eksemplar',
      5 => '5000',
      6 => '',
    ),
    46 => 
    array (
      0 => 'BUKU TULIS',
      1 => '12',
      2 => 'Buku Tulis',
      3 => 'isi: 58 lembar',
      4 => 'eksemplar',
      5 => '6000',
      6 => '',
    ),
    47 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '1',
      2 => 'Isolasi',
      3 => 'biasa;uk:1/2 x 72',
      4 => 'buah',
      5 => '6000',
      6 => '',
    ),
    48 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '2',
      2 => 'Isolasi',
      3 => 'biasa;uk;1 x 72 asli',
      4 => 'buah',
      5 => '10000',
      6 => '',
    ),
    49 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '3',
      2 => 'Isolasi',
      3 => 'panfiks;uk 1/2 x 72asli',
      4 => 'buah',
      5 => '5000',
      6 => '',
    ),
    50 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '4',
      2 => 'Isolasi',
      3 => 'panfiks;uk 1 x 72 asli',
      4 => 'buah',
      5 => '10000',
      6 => '',
    ),
    51 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '5',
      2 => 'Isolasi',
      3 => 'kecil',
      4 => 'buah',
      5 => '2000',
      6 => '',
    ),
    52 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '6',
      2 => 'Isolasi',
      3 => 'besar',
      4 => 'buah',
      5 => '11000',
      6 => '',
    ),
    53 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '7',
      2 => 'lem',
      3 => 'cair uk;kecil',
      4 => 'buah',
      5 => '4000',
      6 => '',
    ),
    54 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '8',
      2 => 'lem',
      3 => 'cair uk;tanggung',
      4 => 'buah',
      5 => '6000',
      6 => '',
    ),
    55 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '9',
      2 => 'lem',
      3 => 'kental;uk besar',
      4 => 'buah',
      5 => '9000',
      6 => '',
    ),
    56 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '10',
      2 => 'lem',
      3 => 'kental;uk tanggung',
      4 => 'buah',
      5 => '4000',
      6 => '',
    ),
    57 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '11',
      2 => 'lem',
      3 => 'kental;uk kecil',
      4 => 'buah',
      5 => '2000',
      6 => '',
    ),
    58 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '12',
      2 => 'lakban',
      3 => 'besar',
      4 => 'gulung',
      5 => '17000',
      6 => '',
    ),
    59 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '13',
      2 => 'lakban',
      3 => 'kain;2',
      4 => 'gulung',
      5 => '12000',
      6 => '',
    ),
    60 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '14',
      2 => 'lakban',
      3 => 'kecil',
      4 => 'gulung',
      5 => '13000',
      6 => '',
    ),
    61 => 
    array (
      0 => 'ALAT PEREKAT',
      1 => '15',
      2 => 'lakban',
      3 => 'tanggung',
      4 => 'gulung',
      5 => '24000',
      6 => '',
    ),
    62 => 
    array (
      0 => 'ALAT TULIS',
      1 => '1',
      2 => 'Isi Pensil',
      3 => '',
      4 => 'Pensil Mekanik',
      5 => 'buah',
      6 => '6000',
      7 => '',
    ),
    63 => 
    array (
      0 => 'ALAT TULIS',
      1 => '2',
      2 => 'Pensil',
      3 => '2B, 3B, 5B',
      4 => 'buah',
      5 => '6000',
      6 => '',
    ),
    64 => 
    array (
      0 => 'ALAT TULIS',
      1 => '3',
      2 => 'Sign Pen',
      3 => '',
      4 => '',
      5 => 'buah',
      6 => '19000',
      7 => '',
    ),
    65 => 
    array (
      0 => 'ALAT TULIS',
      1 => '4',
      2 => 'Spidol',
      3 => 'besar:whiteboard',
      4 => 'pak',
      5 => '121400',
      6 => '',
    ),
    66 => 
    array (
      0 => 'ALAT TULIS',
      1 => '5',
      2 => 'Spidol',
      3 => 'besar,marker',
      4 => 'buah',
      5 => '9000',
      6 => '',
    ),
    67 => 
    array (
      0 => 'ALAT TULIS',
      1 => '6',
      2 => 'Spidol',
      3 => '',
      4 => '',
      5 => 'buah',
      6 => '18000',
      7 => '',
    ),
    68 => 
    array (
      0 => 'ALAT TULIS',
      1 => '7',
      2 => 'Spidol',
      3 => 'kecil',
      4 => 'buah',
      5 => '2000',
      6 => '',
    ),
    69 => 
    array (
      0 => 'ALAT TULIS',
      1 => '8',
      2 => 'Ballpoint',
      3 => 'Hero',
      4 => '',
      5 => 'pak',
      6 => '17400',
      7 => '',
    ),
    70 => 
    array (
      0 => 'ALAT TULIS',
      1 => '9',
      2 => 'Spidol',
      3 => 'sign pen, 12 warna',
      4 => 'set',
      5 => '21000',
      6 => '',
    ),
    71 => 
    array (
      0 => 'ALAT TULIS',
      1 => '10',
      2 => 'Spidol',
      3 => 'kecil,sign pen;6 warna',
      4 => 'set',
      5 => '10000',
      6 => '',
    ),
    72 => 
    array (
      0 => 'ALAT TULIS',
      1 => '11',
      2 => 'Spidol',
      3 => 'transparan 6 warna',
      4 => 'set',
      5 => '69000',
      6 => '',
    ),
    73 => 
    array (
      0 => 'ALAT TULIS',
      1 => '12',
      2 => 'Spidol',
      3 => 'Permanent besar',
      4 => 'buah',
      5 => '9000',
      6 => '',
    ),
    74 => 
    array (
      0 => 'TINTA TULIS, TINTA STEMPEL',
      1 => '1',
      2 => 'Tinta Stempel',
      3 => '',
      4 => '',
      5 => 'Botol',
      6 => '15000',
      7 => '',
    ),
    75 => 
    array (
      0 => 'TINTA TULIS, TINTA STEMPEL',
      1 => '2',
      2 => 'Tinta Tulis',
      3 => 'Parker',
      4 => 'Kecil',
      5 => 'Botol',
      6 => '71000',
      7 => '',
    ),
    76 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '1',
      2 => 'Box arsip besar',
      3 => '',
      4 => 'P:370 x L:190 x T:270 mm',
      5 => 'Buah',
      6 => '24000',
      7 => '',
    ),
    77 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '2',
      2 => 'Box arsip kecil',
      3 => '',
      4 => 'P: 370 x L: 90 x T: 270 mm',
      5 => 'Buah',
      6 => '24000',
      7 => '',
    ),
    78 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '3',
      2 => 'Busines File',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '4000',
      7 => '',
    ),
    79 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '4',
      2 => 'Folder',
      3 => 'uk: standar',
      4 => 'Buah',
      5 => '28000',
      6 => '',
    ),
    80 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '5',
      2 => 'Folder surat arsip',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '9000',
      7 => '',
    ),
    81 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '6',
      2 => 'Hang map',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '6100',
      7 => '',
    ),
    82 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '7',
      2 => 'Latonap folder',
      3 => 'dengan plastik klip',
      4 => 'Buah',
      5 => '61000',
      6 => '',
    ),
    83 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '8',
      2 => 'Map',
      3 => 'plastik',
      4 => 'Buah',
      5 => '4000',
      6 => '',
    ),
    84 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '9',
      2 => 'Map Biasa',
      3 => 'folio',
      4 => 'Buah',
      5 => '2000',
      6 => '',
    ),
    85 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '10',
      2 => 'Map folder',
      3 => 'uk: 48 x 35 cm',
      4 => 'Buah',
      5 => '4000',
      6 => '',
    ),
    86 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '11',
      2 => 'Map gantung',
      3 => 'uk: 37,5 cm x 24 cm',
      4 => 'Buah',
      5 => '7000',
      6 => '',
    ),
    87 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '12',
      2 => 'Ordner',
      3 => 'folio',
      4 => 'Buah',
      5 => '37000',
      6 => '',
    ),
    88 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '13',
      2 => 'Snellhecter biasa',
      3 => 'Folio',
      4 => 'Buah',
      5 => '4000',
      6 => '',
    ),
    89 => 
    array (
      0 => 'ORDNER DAN MAP',
      1 => '14',
      2 => 'Tikcler file',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '10000',
      7 => '',
    ),
    90 => 
    array (
      0 => 'STAPLES',
      1 => '1',
      2 => 'Hecht Machine',
      3 => 'besar no.24/6',
      4 => 'buah',
      5 => '93000',
      6 => '',
    ),
    91 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '1',
      2 => 'Isi Ballpoint pentel',
      3 => '',
      4 => 'asli',
      5 => 'buah',
      6 => '30000',
      7 => '',
    ),
    92 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '2',
      2 => 'Sekat kartu kendali',
      3 => '',
      4 => '',
      5 => 'buah',
      6 => '4500',
      7 => '',
    ),
    93 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '3',
      2 => 'Sekat surat/arsip',
      3 => '',
      4 => '',
      5 => 'buah',
      6 => '9000',
      7 => '',
    ),
    94 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '4',
      2 => 'Buku Kas',
      3 => 'folio isi 100 lembar',
      4 => 'eksemplar',
      5 => '30000',
      6 => '',
    ),
    95 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '5',
      2 => 'Buku Tabelaris',
      3 => '',
      4 => '',
      5 => 'eksemplar',
      6 => '75200',
      7 => '',
    ),
    96 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '6',
      2 => 'Kapur Tulis',
      3 => 'berwarna',
      4 => 'dus',
      5 => '12000',
      6 => '',
    ),
    97 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '7',
      2 => 'Kapur Tulis',
      3 => 'putih asli',
      4 => 'dus',
      5 => '7500',
      6 => '',
    ),
    98 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '8',
      2 => 'Buku Kwitansi',
      3 => 'kecil',
      4 => 'eksemplar',
      5 => '4000',
      6 => '',
    ),
    99 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '9',
      2 => 'Buku Kwitansi',
      3 => 'sedang',
      4 => 'eksemplar',
      5 => '6000',
      6 => '',
    ),
    100 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '10',
      2 => 'Buku Kwitansi',
      3 => 'besar; isi 100 lembar',
      4 => 'eksemplar',
      5 => '9000',
      6 => '',
    ),
    101 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '11',
      2 => 'Buku Memo',
      3 => '',
      4 => '',
      5 => 'eksemplar',
      6 => '60000',
      7 => '',
    ),
    102 => 
    array (
      0 => 'ALAT TULIS KANTOR LAINNYA',
      1 => '12',
      2 => 'Pita (Label Maker Emboss)',
      3 => 'dymo',
      4 => 'gulung',
      5 => '2000',
      6 => '',
    ),
    103 => 
    array (
      0 => 'KERTAS COVER',
      1 => '1',
      2 => 'Kertas Cover',
      3 => '',
      4 => '',
      5 => 'pak',
      6 => '47000',
      7 => '',
    ),
    104 => 
    array (
      0 => 'AMPLOP',
      1 => '1',
      2 => 'Amplop cokelat',
      3 => 'besar',
      4 => 'dus',
      5 => '66000',
      6 => '',
    ),
    105 => 
    array (
      0 => 'AMPLOP',
      1 => '2',
      2 => 'Amplop cokelat',
      3 => 'kecil',
      4 => 'dus',
      5 => '27000',
      6 => '',
    ),
    106 => 
    array (
      0 => 'AMPLOP',
      1 => '3',
      2 => 'Amplop cokelat',
      3 => 'tanggung',
      4 => 'dus',
      5 => '20000',
      6 => '',
    ),
    107 => 
    array (
      0 => 'AMPLOP',
      1 => '4',
      2 => 'Amplop polos putih',
      3 => 'kecil',
      4 => 'dus',
      5 => '13000',
      6 => '',
    ),
    108 => 
    array (
      0 => 'AMPLOP',
      1 => '5',
      2 => 'Amplop polos putih',
      3 => 'panjang',
      4 => 'dus',
      5 => '28000',
      6 => '',
    ),
    109 => 
    array (
      0 => 'AMPLOP',
      1 => '6',
      2 => 'Amplop polos putih',
      3 => 'sedang',
      4 => 'dus',
      5 => '18000',
      6 => '',
    ),
    110 => 
    array (
      0 => 'AMPLOP',
      1 => '7',
      2 => 'Amplop panjang',
      3 => '',
      4 => '',
      5 => 'lembar',
      6 => '3000',
      7 => '',
    ),
    111 => 
    array (
      0 => 'AMPLOP',
      1 => '8',
      2 => 'Amplop tanggung',
      3 => '',
      4 => '',
      5 => 'lembar',
      6 => '2000',
      7 => '',
    ),
    112 => 
    array (
      0 => 'MATERAI',
      1 => '1',
      2 => 'Materai',
      3 => '',
      4 => '',
      5 => 'buah',
      6 => '10000',
      7 => '',
    ),
    113 => 
    array (
      0 => 'Continuous Form',
      1 => '1',
      2 => 'Continuous Form',
      3 => '1 Ply; Besar',
      4 => 'Boks',
      5 => '404600',
      6 => '',
    ),
    114 => 
    array (
      0 => 'Continuous Form',
      1 => '2',
      2 => 'Continuous Form',
      3 => '2 Ply; Besar',
      4 => 'Boks',
      5 => '705000',
      6 => '',
    ),
    115 => 
    array (
      0 => 'Continuous Form',
      1 => '3',
      2 => 'Continuous Form',
      3 => '14 7/8 X 11 Inch; 3 Ply',
      4 => 'Boks',
      5 => '615000',
      6 => '',
    ),
    116 => 
    array (
      0 => 'Continuous Form',
      1 => '4',
      2 => 'Continuous Form',
      3 => '14 7/8 X 11 Inch; 4 Ply',
      4 => 'Boks',
      5 => '584000',
      6 => '',
    ),
    117 => 
    array (
      0 => 'Continuous Form',
      1 => '5',
      2 => 'Continuous Form',
      3 => '9 1/2 X 11 Inch; 2 Ply',
      4 => 'Boks',
      5 => '211000',
      6 => '',
    ),
    118 => 
    array (
      0 => 'Pita Printer',
      1 => '1',
      2 => 'Pita Printer',
      3 => 'isi ulang',
      4 => 'Buah',
      5 => '103000',
      6 => '',
    ),
    119 => 
    array (
      0 => 'Pita Printer',
      1 => '2',
      2 => 'Ribbon Cartridge printer',
      3 => 'LQ 2070',
      4 => 'Buah',
      5 => '223000',
      6 => '',
    ),
    120 => 
    array (
      0 => 'Pita Printer',
      1 => '3',
      2 => 'Ribbon Cartridge printer',
      3 => 'LQ 310',
      4 => 'Buah',
      5 => '34000',
      6 => '',
    ),
    121 => 
    array (
      0 => 'Pita Printer',
      1 => '4',
      2 => 'Ribbon printer',
      3 => 'Toner catridge',
      4 => 'Buah',
      5 => '1210000',
      6 => '',
    ),
    122 => 
    array (
      0 => 'Pita Printer',
      1 => '5',
      2 => 'Tinta Deskjet',
      3 => 'Besar',
      4 => 'Botol',
      5 => '349700',
      6 => '',
    ),
    123 => 
    array (
      0 => 'Pita Printer',
      1 => '6',
      2 => 'Tinta Foto Copy',
      3 => '',
      4 => '',
      5 => 'Botol',
      6 => '510000',
      7 => '',
    ),
    124 => 
    array (
      0 => 'Pita Printer',
      1 => '7',
      2 => 'Tinta Foto Copy',
      3 => '',
      4 => '',
      5 => 'Botol',
      6 => '1167500',
      7 => '',
    ),
    125 => 
    array (
      0 => 'Pita Printer',
      1 => '8',
      2 => 'Tinta Refill Printer',
      3 => 'Warna 664',
      4 => 'Botol',
      5 => '127200',
      6 => '',
    ),
    126 => 
    array (
      0 => 'Pita Printer',
      1 => '9',
      2 => 'Tinta Refill Printer',
      3 => 'Warna 673',
      4 => 'Botol',
      5 => '167700',
      6 => '',
    ),
    127 => 
    array (
      0 => 'Pita Printer',
      1 => '10',
      2 => 'Tinta Refill Printer',
      3 => 'Hitam',
      4 => 'Botol',
      5 => '115600',
      6 => '',
    ),
    128 => 
    array (
      0 => 'Pita Printer',
      1 => '11',
      2 => 'Toner',
      3 => '',
      4 => '',
      5 => 'Botol',
      6 => '133000',
      7 => '',
    ),
    129 => 
    array (
      0 => 'Pita Printer',
      1 => '12',
      2 => 'Tinta Refill Deskjet',
      3 => '',
      4 => 'Cup',
      5 => '50900',
      6 => '',
    ),
    130 => 
    array (
      0 => 'Pita Printer',
      1 => '13',
      2 => 'Tinta Refill Deskjet',
      3 => '',
      4 => 'Mtr',
      5 => '1100',
      6 => '',
    ),
    131 => 
    array (
      0 => 'Pita Printer',
      1 => '14',
      2 => 'Printer Black Ink',
      3 => 'Stylog Pro-C1',
      4 => 'Set',
      5 => '113300',
      6 => '',
    ),
    132 => 
    array (
      0 => 'Pita Printer',
      1 => '15',
      2 => 'Printer Color Ink Cartridge',
      3 => 'Stylog Pro-C2',
      4 => 'Set',
      5 => '161900',
      6 => '',
    ),
    133 => 
    array (
      0 => 'Pita Printer',
      1 => '16',
      2 => 'Flash Disk',
      3 => '4 GB',
      4 => 'Buah',
      5 => '69400',
      6 => '',
    ),
    134 => 
    array (
      0 => 'Pita Printer',
      1 => '17',
      2 => 'Flash Disk',
      3 => '8 GB',
      4 => 'Buah',
      5 => '92500',
      6 => '',
    ),
    135 => 
    array (
      0 => 'Pita Printer',
      1 => '18',
      2 => 'Flash Disk',
      3 => '16 GB',
      4 => 'Buah',
      5 => '115600',
      6 => '',
    ),
    136 => 
    array (
      0 => 'Pita Printer',
      1 => '19',
      2 => 'Flash Disk',
      3 => '32 GB',
      4 => 'Buah',
      5 => '144500',
      6 => '',
    ),
    137 => 
    array (
      0 => 'Pita Printer',
      1 => '20',
      2 => 'Memory Card Foto',
      3 => 'Besar',
      4 => 'Buah',
      5 => '520200',
      6 => '',
    ),
    138 => 
    array (
      0 => 'Pita Printer',
      1 => '21',
      2 => 'Mouse',
      3 => 'Optic',
      4 => 'Buah',
      5 => '173400',
      6 => '',
    ),
    139 => 
    array (
      0 => 'Pita Printer',
      1 => '22',
      2 => 'Mouse',
      3 => 'Wireless',
      4 => 'Buah',
      5 => '208100',
      6 => '',
    ),
    140 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '1',
      2 => 'Sapu Cemara',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '27000',
      7 => '',
    ),
    141 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '2',
      2 => 'Sapu Ijuk',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '27000',
      7 => '',
    ),
    142 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '3',
      2 => 'Sapu Lantai',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '35000',
      7 => '',
    ),
    143 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '4',
      2 => 'Sapu Lidi',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '11000',
      7 => '',
    ),
    144 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '5',
      2 => 'Sapu Lidi tanpa Tangkai',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '7000',
      7 => '',
    ),
    145 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '6',
      2 => 'Sapu Lowo-Lowo',
      3 => 'Sapu Panjang',
      4 => 'Buah',
      5 => '25000',
      6 => '',
    ),
    146 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '7',
      2 => 'Sikat Kawat',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '12000',
      7 => '',
    ),
    147 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '8',
      2 => 'Sikat Plastik',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '27000',
      7 => '',
    ),
    148 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '9',
      2 => 'Sikat Tangan',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '7000',
      7 => '',
    ),
    149 => 
    array (
      0 => 'Kerumahtanggaan',
      1 => '10',
      2 => 'Sikat WC',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '34000',
      7 => '',
    ),
    150 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '1',
      2 => 'Kain Majun',
      3 => 'Besar',
      4 => 'Buah',
      5 => '31000',
      6 => '',
    ),
    151 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '2',
      2 => 'Kain Majun',
      3 => 'Kecil',
      4 => 'Buah',
      5 => '17000',
      6 => '',
    ),
    152 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '3',
      2 => 'Kain Pel',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '75000',
      7 => '',
    ),
    153 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '4',
      2 => 'Kanebo',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '25000',
      7 => '',
    ),
    154 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '5',
      2 => 'Lap/Handuk Gantungan',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '47000',
      7 => '',
    ),
    155 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '6',
      2 => 'Lap Katun',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '21000',
      7 => '',
    ),
    156 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '7',
      2 => 'Lap Lembut',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '15000',
      7 => '',
    ),
    157 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '8',
      2 => 'Pel Lantai Karet',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '73000',
      7 => '',
    ),
    158 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '9',
      2 => 'Tongkat Pel',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '137000',
      7 => '',
    ),
    159 => 
    array (
      0 => 'ALAT-ALAT PEL DAN LAP',
      1 => '10',
      2 => 'Wiper Lantai',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '55000',
      7 => '',
    ),
    160 => 
    array (
      0 => 'EMBER, SELANG, DAN TEMPAT AIR LAINNYA',
      1 => '1',
      2 => 'Ember',
      3 => 'Bhn: Plastik',
      4 => 'Buah',
      5 => '32000',
      6 => '',
    ),
    161 => 
    array (
      0 => 'EMBER, SELANG, DAN TEMPAT AIR LAINNYA',
      1 => '2',
      2 => 'Ember Plastik',
      3 => 'Isi 60 Liter',
      4 => 'Buah',
      5 => '80000',
      6 => '',
    ),
    162 => 
    array (
      0 => 'EMBER, SELANG, DAN TEMPAT AIR LAINNYA',
      1 => '3',
      2 => 'Ember Plastik',
      3 => 'Isi 80 Liter',
      4 => 'Buah',
      5 => '162000',
      6 => '',
    ),
    163 => 
    array (
      0 => 'EMBER, SELANG, DAN TEMPAT AIR LAINNYA',
      1 => '4',
      2 => 'Ember Plastik',
      3 => 'Isi: 6 Galon',
      4 => 'Buah',
      5 => '52000',
      6 => '',
    ),
    164 => 
    array (
      0 => 'EMBER, SELANG, DAN TEMPAT AIR LAINNYA',
      1 => '5',
      2 => 'Ember Plastik HTL',
      3 => 'Kap: 8 Liter',
      4 => 'Buah',
      5 => '23000',
      6 => '',
    ),
    165 => 
    array (
      0 => 'EMBER, SELANG, DAN TEMPAT AIR LAINNYA',
      1 => '6',
      2 => 'Selang Buang',
      3 => '8 inch',
      4 => 'Meter',
      5 => '22000',
      6 => '',
    ),
    166 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '1',
      2 => 'Cikrak',
      3 => 'Bhn Bambu; Besar',
      4 => 'Buah',
      5 => '25000',
      6 => '',
    ),
    167 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '2',
      2 => 'Cikrak Bambu',
      3 => 'Kecil',
      4 => 'Buah',
      5 => '12000',
      6 => '',
    ),
    168 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '3',
      2 => 'Cikrak Plastik',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '31000',
      7 => '',
    ),
    169 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '4',
      2 => 'Keranjang Sampah',
      3 => 'Bhn: Plastik',
      4 => 'Buah',
      5 => '24000',
      6 => '',
    ),
    170 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '5',
      2 => 'Keranjang Sampah Bambu',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '20000',
      7 => '',
    ),
    171 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '6',
      2 => 'Keset',
      3 => 'Uk: 40 X 120 Cm',
      4 => 'Buah',
      5 => '54000',
      6 => '',
    ),
    172 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '7',
      2 => 'Keset',
      3 => 'Bhn: Karpet/aim; Uk: 40 X 60 Cm',
      4 => 'Buah',
      5 => '58000',
      6 => '',
    ),
    173 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '8',
      2 => 'Tempat Sampah',
      3 => 'Bhn: Plastik; Sedang',
      4 => 'Buah',
      5 => '99000',
      6 => '',
    ),
    174 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '9',
      2 => 'Tempat Sampah',
      3 => 'Besar',
      4 => 'Buah',
      5 => '170000',
      6 => '',
    ),
    175 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '10',
      2 => 'Tong Sampah',
      3 => 'Dengan Penutup; Kap: 1',
      4 => 'Buah',
      5 => '113800',
      6 => '',
    ),
    176 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '11',
      2 => 'Tong Sampah',
      3 => 'Bhn: Kayu',
      4 => 'Buah',
      5 => '94800',
      6 => '',
    ),
    177 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '12',
      2 => 'Tong Sampah',
      3 => 'Bhn: Drum',
      4 => 'Buah',
      5 => '158000',
      6 => '',
    ),
    178 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '13',
      2 => 'Tong Sampah',
      3 => '240 liter',
      4 => 'Buah',
      5 => '848000',
      6 => '',
    ),
    179 => 
    array (
      0 => 'KESET DAN TEMPAT SAMPAH',
      1 => '14',
      2 => 'Tong Sampah',
      3 => '10 liter',
      4 => 'Buah',
      5 => '52000',
      6 => '',
    ),
    180 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '1',
      2 => 'Grendel Pintu',
      3 => 'Untuk Zona 1 Konstruksi',
      4 => 'Buah',
      5 => '73900',
      6 => '',
    ),
    181 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '2',
      2 => 'Grendel Pintu',
      3 => 'Untuk Zona 2 Konstruksi',
      4 => 'Buah',
      5 => '74300',
      6 => '',
    ),
    182 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '3',
      2 => 'Grendel Pintu',
      3 => 'Untuk Zona 3 Konstruksi',
      4 => 'Buah',
      5 => '74500',
      6 => '',
    ),
    183 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '4',
      2 => 'Kran Sink T 30-AR13-N7/NB',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '383000',
      7 => '',
    ),
    184 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '5',
      2 => 'Kunci/Grendel Tanam',
      3 => 'Untuk Zona 1 Konstruksi',
      4 => 'Buah',
      5 => '72000',
      6 => '',
    ),
    185 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '6',
      2 => 'Kunci/Grendel Tanam',
      3 => 'Untuk Zona 2 Konstruksi',
      4 => 'Buah',
      5 => '72400',
      6 => '',
    ),
    186 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '7',
      2 => 'Kunci/Grendel Tanam',
      3 => 'Untuk Zona 3 Konstruksi',
      4 => 'Buah',
      5 => '72600',
      6 => '',
    ),
    187 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '8',
      2 => 'Nozzle Air Mancing',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '250000',
      7 => '',
    ),
    188 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '9',
      2 => 'Kran Dinding Type T 23 B13V7NB',
      3 => '',
      4 => '',
      5 => 'Unit',
      6 => '247000',
      7 => '',
    ),
    189 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '10',
      2 => 'Kran Ledeng',
      3 => '',
      4 => '',
      5 => 'Unit',
      6 => '31800',
      7 => '',
    ),
    190 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '11',
      2 => 'Shower Spray',
      3 => 'Panjang selang 120 cm',
      4 => 'Unit',
      5 => '154000',
      6 => '',
    ),
    191 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '12',
      2 => 'Shower Set',
      3 => 'Shower hand panjang selang 1,5 m',
      4 => 'Unit',
      5 => '349000',
      6 => '',
    ),
    192 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '13',
      2 => 'Variable Noxxle Spray',
      3 => '',
      4 => '',
      5 => 'Unit',
      6 => '300000',
      7 => '',
    ),
    193 => 
    array (
      0 => 'KUNCI, KRAN, DAN SEMPROTAN',
      1 => '14',
      2 => 'Water Sprinkler',
      3 => '',
      4 => '',
      5 => 'Unit',
      6 => '70000',
      7 => '',
    ),
    194 => 
    array (
      0 => 'ALAT PENGIKAT',
      1 => '1',
      2 => 'Tali Tiang Bendera',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '252000',
      7 => '',
    ),
    195 => 
    array (
      0 => 'ALAT PENGIKAT',
      1 => '2',
      2 => 'Tali Rafia',
      3 => 'Besar; 1 Kg',
      4 => 'Gulung',
      5 => '31000',
      6 => '',
    ),
    196 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '1',
      2 => 'Antiseptik Gel',
      3 => '',
      4 => '',
      5 => 'Botol',
      6 => '84000',
      7 => '',
    ),
    197 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '2',
      2 => 'Kreolin Wangi',
      3 => '',
      4 => '',
      5 => 'Botol',
      6 => '21000',
      7 => '',
    ),
    198 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '3',
      2 => 'Pembersih Lantai',
      3 => '',
      4 => '',
      5 => 'Buah',
      6 => '33000',
      7 => '',
    ),
    199 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '4',
      2 => 'Pembersih Kloset',
      3 => 'Go Geter',
      4 => 'Galon',
      5 => '37400',
      6 => '',
    ),
    200 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '5',
      2 => 'Pembersih Lantai',
      3 => 'Brite Plus',
      4 => 'Galon',
      5 => '275000',
      6 => '',
    ),
    201 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '6',
      2 => 'Pembersih Kaca A',
      3 => 'Besar',
      4 => 'Buah',
      5 => '17000',
      6 => '',
    ),
    202 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '7',
      2 => 'Pembersih Kaca B',
      3 => 'Kecil',
      4 => 'Buah',
      5 => '13000',
      6 => '',
    ),
    203 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '8',
      2 => 'Bahan Bubuk',
      3 => '',
      4 => '',
      5 => 'Kg',
      6 => '32000',
      7 => '',
    ),
    204 => 
    array (
      0 => 'PERABOT KANTOR',
      1 => '9',
      2 => 'Bahan Pembersih Kaca',
      3 => '',
      4 => '',
      5 => 'Liter',
      6 => '8000',
      7 => '',
    ),
  ),
);
    }
}
