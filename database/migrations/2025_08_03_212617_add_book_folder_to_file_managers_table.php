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
        Schema::table('file_managers', function (Blueprint $table) {
            $table->string('book_folder')->nullable()->after('folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_managers', function (Blueprint $table) {
            $table->dropColumn('book_folder');
        });
    }
};
