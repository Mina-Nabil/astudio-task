<?php

namespace Database\Factories;

use App\Models\EAV\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AttributeFactory extends Factory
{

    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(Attribute::TYPES);
        $options_count = fake()->numberBetween(1, 5); //random number of options if select type
        //if select type, generate random options
        $options = match ($type) {
            Attribute::TYPE_SELECT => json_encode(fake()->words($options_count)),
            default => null,
        };
        return [
            'name' => fake()->word(),
            'type' => $type,
            'options' => $options,
        ];
    }
}
