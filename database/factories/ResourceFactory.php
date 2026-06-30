<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4, false);

        return [
            'title'       => rtrim($title, '.'),
            'description' => fake()->paragraphs(2, true),
            'status'      => fake()->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'signature'   => null,
            'created_by'  => null, // Override in tests/seeders
            'updated_by'  => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Draft resource.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'      => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Pending review resource.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'      => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Approved resource with signature.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'      => 'approved',
            'signature'   => hash('sha256', $attributes['title'] . $attributes['description'] . now()->toIso8601String()),
            'approved_at' => now(),
        ]);
    }

    /**
     * Rejected resource.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'      => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }
}
