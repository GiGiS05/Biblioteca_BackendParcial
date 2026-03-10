<?php

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

#Test #32: It can create a book
test("It can create a book", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $bookData = [
        'title' => 'Clean Code',
        'description' => 'A Handbook of Agile Software Craftsmanship',
        'ISBN' => '9780132350884',
        'total_copies' => 5,
        'available_copies' => 5,
        'is_available' => true,
    ];

    $response = $this->postJson('api/v1/books', $bookData);

    $response->assertStatus(200);
    $this->assertDatabaseHas('books', ['ISBN' => '9780132350884']);
})->with(['bibliotecario']);

#Test #33: It cannot create a book with incomplete information
test("It cannot create a book with incomplete information", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $bookData = [
        'title' => 'Incomplete Book',
        // Missing ISBN, description, etc.
    ];

    $response = $this->postJson('api/v1/books', $bookData);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['description', 'ISBN', 'total_copies', 'available_copies', 'is_available']);
})->with(['bibliotecario']);

#Test #34: It cannot create a book if not a 'bibliotecario'
test("It cannot create a book if not a 'bibliotecario'", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $bookData = [
        'title' => 'Clean Code',
        'description' => 'A Handbook of Agile Software Craftsmanship',
        'ISBN' => '9780132350884',
        'total_copies' => 5,
        'available_copies' => 5,
        'is_available' => true,
    ];

    $response = $this->postJson('api/v1/books', $bookData);

    $response->assertStatus(403);
})->with(['estudiante', 'docente']);

#Test #35: It cannot create a book with invalid information
test("It cannot create a book with invalid information", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $bookData = [
        'title' => 123, // Invalid: should be string
        'description' => 456, // Invalid: should be string
        'ISBN' => 'not-a-number', // Invalid: should be exact 13 digits
        'total_copies' => 'invalid-integer', // Invalid: should be integer
        'available_copies' => -5, // Invalid: should be >=0
        'is_available' => 'not-a-boolean', // Invalid: should be boolean
    ];

    $response = $this->postJson('api/v1/books', $bookData);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['title', 'description', 'ISBN', 'total_copies', 'available_copies', 'is_available']);
})->with(['bibliotecario']);
