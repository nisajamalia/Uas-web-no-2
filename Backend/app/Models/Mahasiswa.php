<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nim',
        'nama',
        'email',
        'prodi',
        'angkatan',
        'status'
    ];

    protected $casts = [
        'angkatan' => 'integer',
    ];

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nim', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Scope untuk filter prodi
    public function scopeFilterProdi($query, $prodi)
    {
        return $query->when($prodi, function ($q) use ($prodi) {
            return $q->where('prodi', $prodi);
        });
    }

    // Scope untuk filter status
    public function scopeFilterStatus($query, $status)
    {
        return $query->when($status, function ($q) use ($status) {
            return $q->where('status', $status);
        });
    }

    // Scope untuk filter angkatan
    public function scopeFilterAngkatan($query, $angkatan)
    {
        return $query->when($angkatan, function ($q) use ($angkatan) {
            return $q->where('angkatan', $angkatan);
        });
    }
}
