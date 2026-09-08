<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUG 3: onDelete salah — seharusnya restrictOnDelete(), bukan cascadeOnDelete().
// Dengan cascade, menghapus satu dosen akan MENGHAPUS SEMUA mata kuliah yang
// diampu. Desain yang benar: tolak penghapusan dosen selama masih mengampu MK.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sks');
            $table->foreignId('lecturer_id')
                  ->constrained('users')
                  ->cascadeOnDelete();     // BUG 3: harusnya restrictOnDelete()
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
