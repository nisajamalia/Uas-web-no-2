<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->unique();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('prodi');
            $table->year('angkatan');
            $table->enum('status', ['aktif', 'cuti', 'lulus', 'dropout'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for optimization
            $table->index(['prodi', 'status']);
            $table->index('angkatan');
            $table->index('nim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
