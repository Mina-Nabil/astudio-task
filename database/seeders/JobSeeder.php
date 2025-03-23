<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EAV\Attribute;
use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = Attribute::all();
        $categories = Category::all();
        $locations = Location::all();
        $languages = Language::all();



        Job::factory(100)->create()->each(function ($job) use ($attributes, $categories, $locations, $languages) {
            $job->categories()->attach($categories->random(random_int(1, 3)));
            //location can be full remote or local 
            $job->locations()->attach($locations->random(random_int(0, 3)));
            $job->languages()->attach($languages->random(random_int(1, 3)));
            for ($i = 0; $i < random_int(1, 12); $i++) {
                $attribute = $attributes->random(1)->first();
                $attribute_value = match ($attribute->type) {
                    'select' => fake()->randomElement(json_decode($attribute->options)),
                    'boolean' => fake()->boolean(),
                    'number' => fake()->numberBetween(1, 100),
                    'date' => fake()->date(),
                    'text' => fake()->word(),
                };
                $job->attributes()->attach($attribute, ['value' => $attribute_value]);
            }
        });
    }
}
