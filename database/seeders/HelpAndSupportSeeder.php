<?php

namespace Database\Seeders;

use App\Models\HelpAndSupport;
use Illuminate\Database\Seeder;

class HelpAndSupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $help_and_support = [
            'id' => 1, 'title' => 'Help and Support', 'description' => 'Lorem ipsum is simply dummy text!',
        ];

        HelpAndSupport::insert($help_and_support);
    }
}
