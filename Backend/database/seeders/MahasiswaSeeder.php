<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mahasiswas = [
            [
                'nim' => '2021001',
                'nama' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@email.com',
                'prodi' => 'Teknik Informatika',
                'angkatan' => 2021,
                'status' => 'aktif'
            ],
            [
                'nim' => '2021002',
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@email.com',
                'prodi' => 'Sistem Informasi',
                'angkatan' => 2021,
                'status' => 'aktif'
            ],
            [
                'nim' => '2020001',
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'prodi' => 'Teknik Informatika',
                'angkatan' => 2020,
                'status' => 'cuti'
            ],
            [
                'nim' => '2019001',
                'nama' => 'Dewi Sartika',
                'email' => 'dewi.sartika@email.com',
                'prodi' => 'Manajemen Informatika',
                'angkatan' => 2019,
                'status' => 'lulus'
            ],
            [
                'nim' => '2022001',
                'nama' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@email.com',
                'prodi' => 'Teknik Informatika',
                'angkatan' => 2022,
                'status' => 'aktif'
            ],
            [
                'nim' => '2022002',
                'nama' => 'Fitri Handayani',
                'email' => 'fitri.handayani@email.com',
                'prodi' => 'Sistem Informasi',
                'angkatan' => 2022,
                'status' => 'aktif'
            ],
            [
                'nim' => '2018001',
                'nama' => 'Gunawan Wijaya',
                'email' => 'gunawan.wijaya@email.com',
                'prodi' => 'Teknik Informatika',
                'angkatan' => 2018,
                'status' => 'dropout'
            ],
            [
                'nim' => '2023001',
                'nama' => 'Hani Rahmawati',
                'email' => 'hani.rahmawati@email.com',
                'prodi' => 'Sistem Informasi',
                'angkatan' => 2023,
                'status' => 'aktif'
            ]
        ];

        foreach ($mahasiswas as $mahasiswa) {
            Mahasiswa::create($mahasiswa);
        }
    }
}
