<?php

namespace Database\Factories;

use App\Models\VisitorInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisitorInterest>
 */
class VisitorInterestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interest_type' => fake()->randomElement(array_keys(VisitorInterest::typeOptions())),
            'status' => VisitorInterest::STATUS_NEW,
            'source' => VisitorInterest::SOURCE_WEB,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'company' => fake()->optional()->company(),
            'role' => fake()->optional()->jobTitle(),
            'location' => fake()->optional()->city(),
            'investment_range' => fake()->optional()->randomElement([
                'LKR 500k - 1M',
                'LKR 1M - 5M',
                'LKR 5M+',
            ]),
            'partnership_area' => fake()->optional()->words(2, true),
            'message' => fake()->paragraph(),
            'created_by_user_id' => null,
        ];
    }
}
