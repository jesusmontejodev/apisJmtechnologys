<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'plan' => 'pro',
            'api_calls_count' => 0,
        ]);

        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_get_projects_list(): void
    {
        Project::create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'slug' => 'test-project',
            'endpoint_destino' => 'https://example.com/endpoint',
            'recaptcha_type' => 'v2',
            'recaptcha_site_key' => encrypt('site-key'),
            'recaptcha_secret_key' => encrypt('secret-key'),
            'project_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Projects retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'project_token', 'is_active']
                ]
            ]);
    }

    public function test_create_project(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/projects', [
                'name' => 'New Project',
                'endpoint_destino' => 'https://example.com/api',
                'recaptcha_type' => 'v3',
                'recaptcha_site_key' => 'test-site-key',
                'recaptcha_secret_key' => 'test-secret-key',
                'recaptcha_min_score' => 0.7,
                'allowed_origins' => ['https://example.com'],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Project created successfully',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'project_token', 'is_active']
            ]);

        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'name' => 'New Project',
        ]);
    }

    public function test_get_project_by_slug(): void
    {
        $project = Project::create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'slug' => 'test-project',
            'endpoint_destino' => 'https://example.com/endpoint',
            'recaptcha_type' => 'v2',
            'recaptcha_site_key' => encrypt('site-key'),
            'recaptcha_secret_key' => encrypt('secret-key'),
            'project_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/projects/{$project->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $project->id,
                    'name' => 'Test Project',
                    'slug' => 'test-project',
                ]
            ]);
    }

    public function test_update_project(): void
    {
        $project = Project::create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'slug' => 'test-project',
            'endpoint_destino' => 'https://example.com/endpoint',
            'recaptcha_type' => 'v2',
            'recaptcha_site_key' => encrypt('site-key'),
            'recaptcha_secret_key' => encrypt('secret-key'),
            'project_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->putJson("/api/projects/{$project->slug}", [
                'name' => 'Updated Project',
                'is_active' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Project',
                    'is_active' => false,
                ]
            ]);
    }

    public function test_delete_project(): void
    {
        $project = Project::create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'slug' => 'test-project',
            'endpoint_destino' => 'https://example.com/endpoint',
            'recaptcha_type' => 'v2',
            'recaptcha_site_key' => encrypt('site-key'),
            'recaptcha_secret_key' => encrypt('secret-key'),
            'project_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/projects/{$project->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_regenerate_token(): void
    {
        $project = Project::create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'slug' => 'test-project',
            'endpoint_destino' => 'https://example.com/endpoint',
            'recaptcha_type' => 'v2',
            'recaptcha_site_key' => encrypt('site-key'),
            'recaptcha_secret_key' => encrypt('secret-key'),
            'project_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $oldToken = $project->project_token;

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/projects/{$project->slug}/regenerate-token");

        $response->assertStatus(200);

        $project->refresh();
        $this->assertNotEquals($oldToken, $project->project_token);
    }

    public function test_get_project_stats(): void
    {
        $project = Project::create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'slug' => 'test-project',
            'endpoint_destino' => 'https://example.com/endpoint',
            'recaptcha_type' => 'v2',
            'recaptcha_site_key' => encrypt('site-key'),
            'recaptcha_secret_key' => encrypt('secret-key'),
            'project_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/projects/{$project->slug}/stats");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => ['total', 'passed', 'blocked', 'errors', 'block_rate', 'daily_stats']
            ]);
    }
}
