<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tasks', [
            'title' => 'Ship Q2 roadmap',
            'description' => 'Align all teams by Friday.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Ship Q2 roadmap',
            'status' => Task::STATUS_INBOX,
        ]);
    }

    public function test_triage_yes_keeps_and_assigns_category(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)->post("/tasks/{$task->id}/triage", [
            'answer' => 'yes',
            'only_you_category' => 'strategy',
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => Task::STATUS_KEEP,
            'only_you_category' => 'strategy',
        ]);
    }

    public function test_triage_no_moves_to_delegate(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)->post("/tasks/{$task->id}/triage", ['answer' => 'no'])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => Task::STATUS_DELEGATE,
        ]);
    }

    public function test_user_cannot_triage_other_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->post("/tasks/{$task->id}/triage", ['answer' => 'yes']);
        $this->assertContains($response->status(), [403, 404]);
    }
}
