<?php

namespace Database\Seeders;

use App\Models\Admin;
use Hash;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plainPassword = env('ADMIN_SEED_PASSWORD');
        if (empty($plainPassword)) {
            $plainPassword = bin2hex(random_bytes(16));
            $this->command?->warn('ADMIN_SEED_PASSWORD was not set; generated a random admin password for this run only.');
        }

        $email = env('ADMIN_SEED_EMAIL', 'admin@example.com');

        $password = Hash::make($plainPassword);
        $adminRecords = [
            ['id' => 1, 'name' => 'admin', 'email' => $email, 'image' => '', 'password' => $password],
        ];

        Admin::insert($adminRecords);
    }
}
