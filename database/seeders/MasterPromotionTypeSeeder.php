<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterPromotionType;
use App\Models\User;

class MasterPromotionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $promotionTypes = [
            [
                'code' => 'biasa',
                'name' => 'Kenaikan Pangkat Biasa/Reguler',
                'description' => 'Kenaikan pangkat reguler yang diberikan kepada setiap pegawai tanpa melihat jabatan yang dipangkunya dan harus melalui syarat-syarat yang telah ditentukan (PP Pasal 11).',
                'requirements' => 'Kenaikan pangkat biasa diberikan kepada setiap pegawai tanpa melihat jabatan yang dipangkunya. Pegawai yang diangkat sebagai pegawai harus melalui syarat-syarat yang ditentukan (Pasal 11).',
            ],
            [
                'code' => 'pilihan',
                'name' => 'Kenaikan Pangkat Pilihan',
                'description' => 'Kenaikan pangkat yang diberikan kepada pegawai yang memangku jabatan dan telah memenuhi syarat-syarat yang ditentukan (PP Pasal 12).',
                'requirements' => 'Kenaikan pangkat pilihan diberikan kepada pegawai yang memangku jabatan dan telah memenuhi syarat-syarat yang ditentukan. Diberikan dalam batas-batas jenjang pangkat yang ditentukan dalam jabatan yang bersangkutan (Pasal 12).',
            ],
            [
                'code' => 'penyesuaian',
                'name' => 'Kenaikan Pangkat Penyesuaian',
                'description' => 'Kenaikan pangkat yang diberikan kepada pegawai karena memperoleh Surat Tanda Tamat Belajar dari perusahaan (PP Pasal 13).',
                'requirements' => 'Kenaikan pangkat penyesuaian diberikan kepada pegawai karena memperoleh Surat Tanda Tamat Belajar yang didapatkan melalui tugas belajar dari perusahaan. Memerlukan: Foto Copy Ijasah dan Transkrip nilai yang telah dilegalisir, Foto Copy surat keterangan ijin belajar dari Direktur, Foto Copy Absensi, Daftar Penilaian Kerja (DPK) bernilai baik dalam 1 (satu) tahun terakhir, Telah memiliki masa kerja minimal 4 (empat) tahun (Pasal 13).',
            ],
            [
                'code' => 'istimewa',
                'name' => 'Kenaikan Pangkat Istimewa',
                'description' => 'Kenaikan pangkat yang diberikan kepada Pegawai yang menunjukan prestasi kerja luar biasa atau menemukan penemuan baru yang bermanfaat bagi perusahaan (PP Pasal 14).',
                'requirements' => 'Prestasi kerja luar biasa adalah prestasi kerja yang ditunjukan oleh seorang pegawai yang memiliki Daftar Penilaian Kerja yang istimewa dalam 2 (dua) tahun terakhir dan dijadikan teladan bagi pegawai lainnya atau tidak mempunyai absensi selama 2 (dua) tahun terakhir. Penemuan baru adalah karya cipta pegawai yang membuat terobosan baru yang bermanfaat, menambah kualitas dan menambah kemajuan perusahaan (Pasal 14).',
            ],
            [
                'code' => 'pengabdian',
                'name' => 'Kenaikan Pangkat Pengabdian',
                'description' => 'Kenaikan pangkat yang diberikan setingkat lebih tinggi pangkatnya kepada pegawai yang akan memasuki masa pensiun (PP Pasal 15).',
                'requirements' => 'Kenaikan pangkat pengabdian diberikan setingkat lebih tinggi pangkatnya kepada pegawai yang akan memasuki masa pensiun karena batas usia pensiun/normal yaitu 56 tahun. Dengan ketentuan sekurang-kurangnya telah 3 (tiga) tahun dalam pangkat terakhir (Pasal 15).',
            ],
            [
                'code' => 'anumerta',
                'name' => 'Kenaikan Pangkat Anumerta',
                'description' => 'Kenaikan pangkat yang diberikan setingkat lebih tinggi kepada pegawai yang meninggal dunia dalam melaksanakan tugas (PP Pasal 16).',
                'requirements' => 'Kenaikan pangkat anumerta diberikan setingkat lebih tinggi kepada pegawai yang meninggal dunia dalam melaksanakan tugas (Pasal 16).',
            ],
        ];

        foreach ($promotionTypes as $type) {
            MasterPromotionType::updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, [
                    'is_active' => true,
                    'users_id' => $user->id,
                ])
            );
        }

        $this->command->info('✅ Master Promotion Types seeded successfully based on PP Pasal 10-16!');
        $this->command->info('Total types created: ' . MasterPromotionType::count());
    }
}
