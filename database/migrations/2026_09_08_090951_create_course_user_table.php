<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUG 2: unique composite ['course_id', 'user_id'] DIHILANGKAN.
// Tanpa ini, mahasiswa yang sama bisa terdaftar berkali-kali di MK yang sama.
// Pengecekan di controller bisa gagal saat race condition (klik ganda).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            // BUG 2: baris ini sengaja dihapus:
            // $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_user');
    }
};
