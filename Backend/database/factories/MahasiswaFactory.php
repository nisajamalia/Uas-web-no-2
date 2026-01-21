<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = date('Y');
        $angkatan = $this->faker->numberBetween($currentYear - 10, $currentYear);
        
        return [
            'nim' => $angkatan . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'nama' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'prodi' => $this->faker->randomElement([
                'Teknik Informatika',
                'Sistem Informasi',
                'Manajemen Informatika',
                'Teknik Komputer',
                'Ilmu Komputer'
            ]),
            'angkatan' => $angkatan,
            'status' => $this->faker->randomElement(['aktif', 'cuti', 'lulus', 'dropout'])
        ];
    }
}
