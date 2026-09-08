<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUG 1 (urutan migrasi): File ini punya nama _a_ sehingga dieksekusi
// SEBELUM _b_create_submissions_table. Tapi grades mereferensi submission_id
// yang belum ada → migrate akan gagal dengan "SQLSTATE[HY000]: table not found"
// atau foreign key error tergantung database engine.
//
// Urutan yang benar: submissions dulu → baru grades.
//
// BUG 2b: unique('submission_id') juga DIHILANGKAN dari sini.
// Tanpa ini, satu submission bisa mendapat dua nilai (grades) berbeda.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                  ->constrained('submissions')  // BUG 1: submissions belum ada saat ini dijalankan
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('feedback')->nullable();
            $table->timestamps();

            // BUG 2b: unique('submission_id') dihapus — satu submission bisa dinilai dua kali
            // $table->unique('submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
