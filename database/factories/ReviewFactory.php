<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $locations = ['Main Office', 'Health Center', 'Public Market', 'Park', 'Sports Complex'];

        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->randomElement($locations),
            'review_message' => $this->faker->paragraphs(2, true),
            'rating' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'is_anonymous' => $this->faker->boolean(15),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function highRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'rating' => $this->faker->numberBetween(4, 5),
            ];
        });
    }

    public function lowRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'rating' => $this->faker->numberBetween(1, 2),
            ];
        });
    }
}