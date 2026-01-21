<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Http\Requests\StoreMahasiswaRequest;
use App\Http\Requests\UpdateMahasiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource with pagination, search, filter, and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Mahasiswa::query();

        // Search functionality
        if ($request->has('q') && $request->q) {
            $query->search($request->q);
        }

        // Filter by prodi
        if ($request->has('prodi') && $request->prodi) {
            $query->filterProdi($request->prodi);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->filterStatus($request->status);
        }

        // Filter by angkatan
        if ($request->has('angkatan') && $request->angkatan) {
            $query->filterAngkatan($request->angkatan);
        }

        // Sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $sortDir = $request->get('sortDir', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['nim', 'nama', 'email', 'prodi', 'angkatan', 'status', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min($perPage, 100); // Max 100 items per page

        $mahasiswas = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $mahasiswas->items(),
            'pagination' => [
                'current_page' => $mahasiswas->currentPage(),
                'last_page' => $mahasiswas->lastPage(),
                'per_page' => $mahasiswas->perPage(),
                'total' => $mahasiswas->total(),
                'from' => $mahasiswas->firstItem(),
                'to' => $mahasiswas->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMahasiswaRequest $request): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa berhasil ditambahkan',
                'data' => $mahasiswa
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $mahasiswa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMahasiswaRequest $request, string $id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            $mahasiswa->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa berhasil diperbarui',
                'data' => $mahasiswa->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            $mahasiswa->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
