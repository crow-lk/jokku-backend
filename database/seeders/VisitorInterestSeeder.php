<?php

namespace Database\Seeders;

use App\Models\VisitorInterest;
use Illuminate\Database\Seeder;

class VisitorInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (VisitorInterest::query()->exists()) {
            return;
        }

        VisitorInterest::factory()->count(5)->create();
    }
}
