<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EAV\Attribute;
use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //create random number of languages, locations, categories and attributes before jobs creation
        Language::factory(random_int(10, 20))->create();
        Location::factory(random_int(10, 20))->create();
        Category::factory(random_int(10, 20))->create();
        Attribute::factory(random_int(10, 20))->create();
        $this->call(JobSeeder::class);

    }
}
