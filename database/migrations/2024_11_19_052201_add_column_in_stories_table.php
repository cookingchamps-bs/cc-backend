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
        Schema::table('stories', function (Blueprint $table) {
            $table->foreignId('kid_id')
                ->nullable()
                ->constrained('kids')
                ->cascadeOnDelete()
                ->after('user_id');
            $table->string('role')->nullable()->after('kid_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropForeign(['kid_id']);
            $table->dropColumn(['kid_id', 'role']);
        });
    }
};
