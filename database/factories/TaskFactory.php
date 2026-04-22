<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => null,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(12),
            'status' => Task::STATUS_INBOX,
            'only_you_category' => null,
            'source' => 'manual',
            'due_at' => null,
        ];
    }

    public function keep(): self
    {
        return $this->state(fn () => [
            'status' => Task::STATUS_KEEP,
            'only_you_category' => 'strategy',
        ]);
    }
}
