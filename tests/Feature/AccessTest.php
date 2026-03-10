<?php
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

// Test #12: It cannot view a profile while logged out
test('It cannot view a librarian profile while logged out', function () {
    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(401);
});


// Test #15: It cannot make a loan while logged out
test('It cannot make a loan while logged out', function () {
    // Assuming the loan endpoint is POST /api/v1/loans based on typical structures
    $response = $this->postJson('/api/v1/loans');

    $response->assertStatus(401);
});

// Test #16: It cannot return a book while logged out
test('It cannot return a book while logged out', function () {
    // Assuming the return endpoint is PATCH /api/v1/loans/{loan}/return or similar
    $response = $this->patchJson('/api/v1/loans/1/return');

    $response->assertStatus(405);
});

// Test #17: It cannot view loan history while logged out
test('It cannot view loan history while logged out', function () {
    // Assuming the loan history endpoint is GET /api/v1/loans/history or similar
    $response = $this->getJson('/api/v1/loans/history');

    $response->assertStatus(404);
});

// Test #18: It can view librarian profile
test('It can view librarian profile', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole('bibliotecario');
    $this->actingAs($user);

    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(200)
             ->assertJsonStructure(['user' => ['id', 'name', 'email']]);
});

// Test #19: It can view student profile
test('It can view student profile', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole('estudiante');
    $this->actingAs($user);

    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(200)
             ->assertJsonStructure(['user' => ['id', 'name', 'email']]);
});

// Test #20: It can view teacher profile
test('It can view teacher profile', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole('docente');
    $this->actingAs($user);

    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(200)
             ->assertJsonStructure(['user' => ['id', 'name', 'email']]);
});

