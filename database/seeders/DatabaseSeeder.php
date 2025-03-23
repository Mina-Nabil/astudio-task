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
        //Use either factory seeder or json seeder to create languages, locations, categories and attributes before jobs creation
    
        // $this->call(FactorySeeder::class);
        $this->call(JsonSeeder::class);
        


    }
}
