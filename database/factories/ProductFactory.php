<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(rand(1, 2), true),
            'description' => $this->faker->sentence(),
            'price'       => $this->faker->numberBetween(100, 1000),
            'country_id'  => Country::inRandomOrder()->first()->id,
        ];
    }
}
