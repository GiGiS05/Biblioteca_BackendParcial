<?php

use App\Models\Book;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});
// Test #19: It can list books
test("It can list books", function (string $role) {

    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(5)->create();

    $this->getJson("api/v1/books")->assertOk()->assertJsonCount(5);

})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #20: It can list available books
test("It can list available books", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(5)->create(['is_available' => true]);
    Book::factory()->count(6)->create(['is_available' => false]);

    $this->getJson("api/v1/books?is_available=1")->assertOk()->assertJsonCount(5);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #21: It can list unavailable books
test("It can list unavailable books", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(5)->create(['is_available' => true]);
    Book::factory()->count(6)->create(['is_available' => false]);

    $this->getJson("api/v1/books?is_available=0")->assertOk()->assertJsonCount(6);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #22: It can list books filtered by their title
test("It can list books filtered by their title", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(3)->create(['title' => "El principito"]);
    Book::factory()->count(2)->create(['title' => "Flamenco"]);

    $this->getJson("api/v1/books?title=El principito")->assertOk()->assertJsonCount(3);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #23: It can return an empty list when there are no title matches
test("It can return an empty list when there are no title matches", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(3)->create(['title' => "El principito"]);
    Book::factory()->count(2)->create(['title' => "Flamenco"]);

    $this->getJson("api/v1/books?title=El sapo")->assertOk()->assertJsonCount(0);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #24: It can list books filtered by their ISBN
test("It can list books filtered by their ISBN", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(1)->create(["ISBN" => "12345678912"]);
    Book::factory()->count(5)->create();

    $this->getJson("api/v1/books?isbn=12345678912")->assertOk()->assertJsonCount(1);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #25: It can return an empty list when there are no ISBN matches
test("It can return an empty list when there are no ISBN matches", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(1)->create(["ISBN" => "12345678912"]);

    $this->getJson("api/v1/books?isbn=12345678910")->assertOk()->assertJsonCount(0);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #26: It can return a 422 error when wrong type of data is input in the request when filtering by availability 
test("It can return a 422 error when wrong type of data is input in the request when filtering by availability", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $this->getJson("api/v1/books?is_available=a")->assertStatus(422);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #27: It can return a 422 error when wrong type of data is input in the request when filtering by ISBN
 test("It can return a 422 error when wrong type of data is input in the request when filtering by ISBN", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $this->getJson("api/v1/books?isbn=a")->assertStatus(422);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #28: It can get books details
test("It can get books details", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    $book = Book::factory()->create([
        'title' => 'El principito',
        'description' => 'Un libro muy famoso',
        'ISBN' => '12345678912',
        'total_copies' => 8,
        'available_copies' => 5,
        'is_available' => true,
    ]);

    $this->getJson("api/v1/books/{$book->id}")
        ->assertOk()
        ->assertJsonFragment([
            'id' => $book->id,
            'title' => 'El principito',
            'description' => 'Un libro muy famoso',
            'ISBN' => '12345678912',
            'total_copies' => 8,
            'available_copies' => 5,
            'is_available' => 'Disponible', 
        ]);


})->with(['estudiante', 'docente', 'bibliotecario']);

// Test #29: It can notify when there are no matches for the searched book.
test("It can notify when there are no matches for the searched book", function (string $role) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user);

    Book::factory()->count(1)->create();

    $this->getJson("api/v1/books/2")
    ->assertStatus(404)
    ->assertJson([
        "message" => "There are no matches for the searched book"
    ]);


})->with(['estudiante', 'docente', 'bibliotecario']);
