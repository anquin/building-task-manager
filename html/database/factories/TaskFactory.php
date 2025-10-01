<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Building;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'building_id' => Building::factory(),
            'creator' => User::factory(),
            'assignee' => null,
            'status' => TaskStatus::OPEN->value,
            'summary' => $this->faker->sentence,
            'comments' => [],
        ];
    }
}
