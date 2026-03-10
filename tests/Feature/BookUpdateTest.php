<?php
use App\Models\Book;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

#Test #36: It can update a book
test("It can update a book", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create([
        'title' => 'Original Title',
        'available_copies' => 5,
        'total_copies' => 5,
    ]);

    $updateData = [
        'title' => 'Updated Title',
        'available_copies' => 10,
        'total_copies' => 10,
    ];

    $response = $this->patchJson("api/v1/books/{$book->id}", $updateData);

    $response->assertStatus(200);
    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => 'Updated Title'
    ]);
})->with(['bibliotecario']);

#Test #37: It cannot update a book with invalid information
test("It cannot update a book with invalid information", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create();

    $updateData = [
        'ISBN' => 'wrong-format-not-13-digits',
        'total_copies' => -5,
    ];

    $response = $this->patchJson("api/v1/books/{$book->id}", $updateData);

    // This anticipates that there should be validation (e.g., UpdateBookRequest)
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['ISBN', 'total_copies']);
})->with(['bibliotecario']);

#Test #38: It cannot update a book if not a 'bibliotecario'
test("It cannot update a book if not a 'bibliotecario'", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create();

    $updateData = [
        'title' => 'Another Updated Title'
    ];

    $response = $this->patchJson("api/v1/books/{$book->id}", $updateData);

    $response->assertStatus(403);
})->with(['estudiante', 'docente']);

