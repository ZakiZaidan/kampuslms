<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
    }

    // BUG 4: down() dikosongkan — migrate:refresh / migrate:rollback
    // akan gagal karena tabel tidak terhapus.
    // Harusnya: Schema::dropIfExists('assignments');
    public function down(): void
    {
        // BUG 4: kosong sengaja — migrasi tidak reversible
    }
};
