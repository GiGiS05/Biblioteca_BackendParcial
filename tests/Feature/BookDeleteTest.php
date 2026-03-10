<?php
use App\Models\Book;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

#Test #39: It can delete a book
test("It can delete a book", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->deleteJson("api/v1/books/{$book->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('books', ['id' => $book->id]);
})->with(['bibliotecario']);

#Test #40: It cannot delete an already deleted book
test("It cannot delete an already deleted book", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create();
    $bookId = $book->id;
    
    // Delete the book first
    $book->delete();

    // Attempt to delete it again via the endpoint
    $response = $this->deleteJson("api/v1/books/{$bookId}");

    // The route model binding will not find the book and return 404
    $response->assertStatus(404);
})->with(['bibliotecario']);

#Test #41: It cannot delete a book if not a 'bibliotecario'
test("It cannot delete a book if not a 'bibliotecario'", function (string $role) {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->deleteJson("api/v1/books/{$book->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('books', ['id' => $book->id]);
})->with(['estudiante', 'docente']);
