<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salary_min = fake()->numberBetween(10000, 100000);
        $salary_max = fake()->numberBetween($salary_min, $salary_min + 100000);
     
        return [
            'title' => fake()->sentence(),
            'company_name' => fake()->company(),
            'description' => fake()->paragraph(),
            'job_type' => fake()->randomElement(Job::JOB_TYPE_LIST),
            'salary_min' => $salary_min,
            'salary_max' => $salary_max,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => fake()->randomElement(Job::STATUS_LIST),
            'is_remote' => fake()->boolean()            
        ];
    }
}
