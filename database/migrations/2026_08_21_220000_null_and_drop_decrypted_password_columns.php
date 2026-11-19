<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('kids', 'decrypted_password')) {
            DB::table('kids')->update(['decrypted_password' => null]);
            Schema::table('kids', function (Blueprint $table) {
                $table->dropColumn('decrypted_password');
            });
        }

        if (Schema::hasColumn('vendors', 'decrypted_password')) {
            DB::table('vendors')->update(['decrypted_password' => null]);
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('decrypted_password');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('kids', 'decrypted_password')) {
            Schema::table('kids', function (Blueprint $table) {
                $table->string('decrypted_password')->nullable()->after('password');
            });
        }

        if (! Schema::hasColumn('vendors', 'decrypted_password')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->string('decrypted_password')->nullable()->after('password');
            });
        }
    }
};
