<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('original_name');
            $table->string('url')->nullable()->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'file_size', 'url']);
        });
    }
};

