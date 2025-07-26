<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Knowledge>
 */
class KnowledgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['note', 'solution', 'command', 'snippet'];

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'summary' => $this->faker->sentence(),
            'type' => $this->faker->randomElement($types),
            'metadata' => [
                'source' => $this->faker->randomElement(['manual', 'imported', 'git']),
                'tags' => $this->faker->words(2),
            ],
            'is_public' => $this->faker->boolean(20), // 20% chance of being public
            'captured_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function byType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
