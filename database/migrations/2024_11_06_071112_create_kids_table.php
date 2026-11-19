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
        Schema::create('kids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('kid_image')->nullable();
            $table->smallInteger('day')->nullable();
            $table->string('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('grade')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kids');
    }
};
