<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Building;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    // protected function authenticateUser(?User $user = null): User
    // {
    //     $user = $user ?? User::factory()->create();
    //     $this->actingAs($user, 'sanctum');
    //     return $user;
    // }

    protected function authenticateUser(?User $user = null, UserRole $role = UserRole::EMPLOYEE): User
    {
        if (!$user) {
            $user = User::factory()->state([
                'role' => $role->value,
            ])->create();
        }

        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_index_returns_all_tasks()
    {
        $this->authenticateUser();
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    public function test_index_can_filter_by_assignee()
    {
        $this->authenticateUser();
        $assignee = User::factory()->create();
        Task::factory()->count(2)->create(['assignee' => $assignee->id]);
        Task::factory()->create(); // not assigned

        $response = $this->getJson('/api/tasks?assignee=' . $assignee->id);

        $response->assertOk()
                 ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_date_range()
    {
        $this->authenticateUser();
        Task::factory()->create(['created_at' => now()->subDays(5)]);
        Task::factory()->create(['created_at' => now()->subDays(1)]);

        $response = $this->getJson('/api/tasks?date_from=' . now()->subDays(2)->toDateString());

        $response->assertOk()
                 ->assertJsonCount(1, 'data');
    }

    // public function test_store_owner_creates_a_task()
    // {
    //     $user = $this->authenticateUser(null, UserRole::OWNER);
    //     $this->assertEquals(UserRole::OWNER->value, $user->role);
    //     $building = $user->building;

    //     $payload = [
    //         'building_id' => $building->id,
    //         'summary' => 'New Task Summary',
    //     ];

    //     $response = $this->postJson('/api/tasks', $payload);

    //     $response->assertCreated()
    //              ->assertJsonPath('data.summary', 'New Task Summary');
    // }

        public function test_store_employee_cannot_create_a_task()
    {
        $user = $this->authenticateUser(null, UserRole::EMPLOYEE);
        $this->assertEquals(UserRole::EMPLOYEE->value, $user->role);
        $building = $user->building;

        $payload = [
            'building_id' => $building->id,
            'summary' => 'New Task Summary',
        ];

        $response = $this->postJson('/api/tasks', $payload);

        $response->assertForbidden();
    }

    // public function test_show_returns_a_task()
    // {
    //     $this->authenticateUser();
    //     $task = Task::factory()->create();

    //     $response = $this->getJson("/api/tasks/{$task->id}");

    //     $response->assertOk()
    //              ->assertJsonPath('data.id', $task->id);
    // }

    public function test_update_modifies_a_task()
    {
        $user = $this->authenticateUser();
        $task = Task::factory()->create(['status' => TaskStatus::OPEN->value]);

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'status' => TaskStatus::IN_PROGRESS->value,
            'assignee' => $user->id,
        ]);

        $response->assertOk()
                 ->assertJsonPath('data.status', TaskStatus::IN_PROGRESS->value)
                 ->assertJsonPath('data.assignee', $user->id);
    }

    public function test_destroy_deletes_a_task()
    {
        $this->authenticateUser();
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    // public function test_add_comment_adds_comment_to_task()
    // {
    //     $user = $this->authenticateUser();
    //     $task = Task::factory()->create(['building_id' => $user->building_id]);

    //     $response = $this->postJson("/api/tasks/{$task->id}/comments", [
    //         'text' => 'Test comment',
    //     ]);

    //     $response->assertOk()
    //              ->assertJsonFragment(['text' => 'Test comment']);
    // }
}
