<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_are_loaded_and_users_endpoint_requires_sanctum(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_user_api_resources_never_expose_password_or_remember_token(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonMissingPath('data.0.password')
            ->assertJsonMissingPath('data.0.remember_token');

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    }

    public function test_user_show_returns_403_for_another_users_record(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherUser = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student);

        $this->getJson('/api/v1/users/'.$otherUser->id)
            ->assertForbidden()
            ->assertExactJson(['message' => 'Forbidden.']);
    }

    public function test_course_store_returns_201_and_destroy_returns_empty_204(): void
    {
        $user = User::factory()->create(['role' => 'lecturer']);
        Sanctum::actingAs($user);

        $storeResponse = $this->postJson('/api/v1/courses', [
            'code' => 'API-101',
            'name' => 'API Fundamentals',
            'description' => 'API response contract test',
            'sks' => 3,
            'lecturer_id' => $user->id,
            'status' => 'active',
        ]);

        $storeResponse->assertCreated()->assertJsonPath('data.code', 'API-101');
        $courseId = $storeResponse->json('data.id');

        $this->deleteJson('/api/v1/courses/'.$courseId)
            ->assertNoContent()
            ->assertContent('');
    }

    public function test_login_uses_the_same_error_for_unknown_email_and_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'known@example.test',
            'password' => 'correct-password',
        ]);

        $unknownEmail = $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@example.test',
            'password' => 'incorrect-password',
        ])->assertUnprocessable();

        $wrongPassword = $this->postJson('/api/v1/auth/login', [
            'email' => 'known@example.test',
            'password' => 'incorrect-password',
        ])->assertUnprocessable();

        $this->assertSame($unknownEmail->json('errors.email'), $wrongPassword->json('errors.email'));
        $this->assertSame(['Email atau kata sandi tidak sesuai.'], $unknownEmail->json('errors.email'));
    }

    public function test_successful_login_returns_a_token_and_safe_user_resource(): void
    {
        User::factory()->create([
            'email' => 'api-user@example.test',
            'password' => 'correct-password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api-user@example.test',
            'password' => 'correct-password',
        ])->assertOk();

        $this->assertNotEmpty($response->json('token'));
        $this->assertArrayNotHasKey('password', $response->json('user'));
        $this->assertArrayNotHasKey('remember_token', $response->json('user'));
    }

    public function test_login_route_is_throttled_after_five_attempts_per_minute(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'unknown@example.test',
                'password' => 'incorrect-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@example.test',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_course_list_eager_loads_lecturers_in_a_single_query(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $lecturers = User::factory()->count(3)->create(['role' => 'lecturer']);

        for ($index = 1; $index <= 15; $index++) {
            Course::create([
                'code' => sprintf('N1-%03d', $index),
                'name' => 'N plus one test '.$index,
                'description' => null,
                'sks' => 3,
                'lecturer_id' => $lecturers[($index - 1) % $lecturers->count()]->id,
                'status' => 'active',
            ]);
        }

        Sanctum::actingAs($user);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson('/api/v1/courses')->assertOk();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $lecturerQueries = array_filter($queries, function (array $query): bool {
            return str_contains(strtolower($query['query']), 'users')
                && preg_match('/\bin\s*\(/i', $query['query']) === 1;
        });

        $this->assertCount(15, $response->json('data'));
        $this->assertArrayNotHasKey('password', $response->json('data.0.lecturer'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.0.lecturer'));
        $this->assertCount(1, $lecturerQueries);
    }
}