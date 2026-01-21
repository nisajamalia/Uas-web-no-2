<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MahasiswaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create sample data for testing
        Mahasiswa::factory()->create([
            'nim' => '2021001',
            'nama' => 'Ahmad Rizki',
            'email' => 'ahmad.rizki@email.com',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2021,
            'status' => 'aktif'
        ]);
    }

    public function test_can_get_mahasiswa_list()
    {
        $response = $this->getJson('/api/mahasiswa');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'nim',
                            'nama',
                            'email',
                            'prodi',
                            'angkatan',
                            'status',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to'
                    ]
                ]);
    }

    public function test_can_search_mahasiswa()
    {
        $response = $this->getJson('/api/mahasiswa?q=Ahmad');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('pagination.total', 1)
                ->assertJsonPath('data.0.nama', 'Ahmad Rizki');
    }

    public function test_can_filter_mahasiswa_by_prodi()
    {
        // Create another mahasiswa with different prodi
        Mahasiswa::factory()->create([
            'nim' => '2021002',
            'nama' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@email.com',
            'prodi' => 'Sistem Informasi',
            'angkatan' => 2021,
            'status' => 'aktif'
        ]);

        $response = $this->getJson('/api/mahasiswa?prodi=Teknik Informatika');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('pagination.total', 1)
                ->assertJsonPath('data.0.prodi', 'Teknik Informatika');
    }

    public function test_can_sort_mahasiswa()
    {
        // Create another mahasiswa
        Mahasiswa::factory()->create([
            'nim' => '2021002',
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@email.com',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2021,
            'status' => 'aktif'
        ]);

        $response = $this->getJson('/api/mahasiswa?sortBy=nama&sortDir=asc');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.0.nama', 'Ahmad Rizki')
                ->assertJsonPath('data.1.nama', 'Budi Santoso');
    }

    public function test_can_get_single_mahasiswa()
    {
        $mahasiswa = Mahasiswa::first();

        $response = $this->getJson("/api/mahasiswa/{$mahasiswa->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.id', $mahasiswa->id)
                ->assertJsonPath('data.nim', $mahasiswa->nim);
    }

    public function test_can_create_mahasiswa()
    {
        $data = [
            'nim' => '2023001',
            'nama' => 'Test Mahasiswa',
            'email' => 'test@email.com',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2023,
            'status' => 'aktif'
        ];

        $response = $this->postJson('/api/mahasiswa', $data);

        $response->assertStatus(201)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.nim', '2023001')
                ->assertJsonPath('data.nama', 'Test Mahasiswa');

        $this->assertDatabaseHas('mahasiswas', [
            'nim' => '2023001',
            'nama' => 'Test Mahasiswa',
            'email' => 'test@email.com'
        ]);
    }

    public function test_cannot_create_mahasiswa_with_duplicate_nim()
    {
        $data = [
            'nim' => '2021001', // Already exists
            'nama' => 'Test Mahasiswa',
            'email' => 'test@email.com',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2023,
            'status' => 'aktif'
        ];

        $response = $this->postJson('/api/mahasiswa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nim']);
    }

    public function test_cannot_create_mahasiswa_with_invalid_angkatan()
    {
        $data = [
            'nim' => '2023001',
            'nama' => 'Test Mahasiswa',
            'email' => 'test@email.com',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2030, // Future year
            'status' => 'aktif'
        ];

        $response = $this->postJson('/api/mahasiswa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['angkatan']);
    }

    public function test_can_update_mahasiswa()
    {
        $mahasiswa = Mahasiswa::first();
        
        $data = [
            'nim' => $mahasiswa->nim,
            'nama' => 'Updated Name',
            'email' => $mahasiswa->email,
            'prodi' => 'Sistem Informasi',
            'angkatan' => $mahasiswa->angkatan,
            'status' => 'cuti'
        ];

        $response = $this->putJson("/api/mahasiswa/{$mahasiswa->id}", $data);

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.nama', 'Updated Name')
                ->assertJsonPath('data.prodi', 'Sistem Informasi')
                ->assertJsonPath('data.status', 'cuti');

        $this->assertDatabaseHas('mahasiswas', [
            'id' => $mahasiswa->id,
            'nama' => 'Updated Name',
            'prodi' => 'Sistem Informasi',
            'status' => 'cuti'
        ]);
    }

    public function test_can_soft_delete_mahasiswa()
    {
        $mahasiswa = Mahasiswa::first();

        $response = $this->deleteJson("/api/mahasiswa/{$mahasiswa->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true);

        // Check that record is soft deleted
        $this->assertSoftDeleted('mahasiswas', [
            'id' => $mahasiswa->id
        ]);

        // Verify it's not in normal queries
        $this->assertDatabaseMissing('mahasiswas', [
            'id' => $mahasiswa->id,
            'deleted_at' => null
        ]);
    }

    public function test_returns_404_for_nonexistent_mahasiswa()
    {
        $response = $this->getJson('/api/mahasiswa/999');

        $response->assertStatus(404)
                ->assertJsonPath('success', false);
    }

    public function test_pagination_works_correctly()
    {
        // Create more mahasiswa for pagination test
        Mahasiswa::factory()->count(20)->create();

        $response = $this->getJson('/api/mahasiswa?per_page=5');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('pagination.per_page', 5)
                ->assertJsonPath('pagination.current_page', 1)
                ->assertJsonCount(5, 'data');
    }

    public function test_validates_required_fields()
    {
        $response = $this->postJson('/api/mahasiswa', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'nim', 'nama', 'email', 'prodi', 'angkatan', 'status'
                ]);
    }

    public function test_validates_email_format()
    {
        $data = [
            'nim' => '2023001',
            'nama' => 'Test Mahasiswa',
            'email' => 'invalid-email',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2023,
            'status' => 'aktif'
        ];

        $response = $this->postJson('/api/mahasiswa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_validates_status_enum()
    {
        $data = [
            'nim' => '2023001',
            'nama' => 'Test Mahasiswa',
            'email' => 'test@email.com',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2023,
            'status' => 'invalid_status'
        ];

        $response = $this->postJson('/api/mahasiswa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }
}