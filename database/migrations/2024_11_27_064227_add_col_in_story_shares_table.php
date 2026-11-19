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
        Schema::table('story_shares', function (Blueprint $table) {
            $table->unsignedBigInteger('story_share_to')->nullable()->after('role');
            $table->string('story_share_to_role')->nullable()->after('story_share_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('story_shares', function (Blueprint $table) {
            $table->dropColumn(['story_share_to', 'story_share_to_role']);
        });
    }
};
