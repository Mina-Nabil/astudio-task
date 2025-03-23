<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EAV\Attribute;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JsonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create languages, locations, categories and attributes from json files
        $locations = json_decode(file_get_contents(resource_path('json/locations.json')), true);
        $categories = json_decode(file_get_contents(resource_path('json/categories.json')), true);
        $languages = json_decode(file_get_contents(resource_path('json/languages.json')), true);
        $attributes = json_decode(file_get_contents(resource_path('json/attributes.json')), true);

        foreach ($locations as $location) {
            Location::create($location);
        }

        foreach ($categories as $category) {
            Category::create($category);
        }

        foreach ($languages as $language) {
            Language::create($language);
        }

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }

        $this->call(JobSeeder::class);
    }
}
