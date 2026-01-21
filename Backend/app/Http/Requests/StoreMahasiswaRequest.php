<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMahasiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentYear = date('Y');
        $minYear = $currentYear - 10; // 10 tahun ke belakang
        
        return [
            'nim' => 'required|string|max:20|unique:mahasiswas,nim',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:mahasiswas,email',
            'prodi' => 'required|string|max:255',
            'angkatan' => "required|integer|digits:4|between:{$minYear},{$currentYear}",
            'status' => 'required|in:aktif,cuti,lulus,dropout'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi',
            'nim.unique' => 'NIM sudah terdaftar',
            'nama.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'prodi.required' => 'Program studi wajib diisi',
            'angkatan.required' => 'Angkatan wajib diisi',
            'angkatan.digits' => 'Angkatan harus 4 digit',
            'angkatan.between' => 'Angkatan tidak valid',
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status harus salah satu dari: aktif, cuti, lulus, dropout'
        ];
    }
}
