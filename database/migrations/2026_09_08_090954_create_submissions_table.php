<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel ini seharusnya dibuat SEBELUM grades (karena grades.submission_id → submissions.id).
// Tapi karena nama file _b_ datang setelah _a_ (grades), urutan eksekusi salah.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id']); // unique composite ini BENAR (bukan bug)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
