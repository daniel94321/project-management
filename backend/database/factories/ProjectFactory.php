<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['planning', 'active', 'completed', 'cancelled']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'owner_id' => User::factory(),
        ];
    }
}
