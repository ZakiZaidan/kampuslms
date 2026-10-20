<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('original_name');
            // BUG 7 (w09): is_late seharusnya dihitung server saat store().
            // Kolom ini ada, tapi di controller nilai diterima dari request ($request->is_late)
            $table->boolean('is_late')->default(false)->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'file_size', 'is_late']);
        });
    }
};

