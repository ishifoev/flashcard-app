<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Flashcard;

class FlashcardFactory extends Factory
{
    protected $model = Flashcard::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "question" => $this->faker->sentence(),
            "answer" => $this->faker->sentence(),
        ];
    }
}
