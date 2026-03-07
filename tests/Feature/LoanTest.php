<?php

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    // Run the Permission Seeder to have the roles available for tests
    $this->seed(PermissionSeeder::class);
});

// Helper function to create users with a specific role
function createUserWithRole(string $roleName)
{
    $user = User::factory()->create();
    $role = Role::findByName($roleName, 'api');
    $user->assignRole($role);
    return $user;
}

it('cannot make loan while logged out', function () {
    $response = $this->postJson('/api/v1/loans', [
        'requester_name' => 'John Doe',
        'book_id' => 1,
    ]);

    $response->assertUnauthorized();
});

it('cannot return a book while logged out', function () {
    $response = $this->postJson('/api/v1/loans/1/return');

    $response->assertUnauthorized();
});

it('cannot view loan history while logged out', function () {
    $response = $this->getJson('/api/v1/loans');

    $response->assertUnauthorized();
});

it('can loan book as a student', function () {
    $student = createUserWithRole('estudiante');
    $book = Book::factory()->create([
        'available_copies' => 5,
        'is_available' => true,
    ]);

    $response = $this->actingAs($student)->postJson('/api/v1/loans', [
        'requester_name' => 'Student Name',
        'book_id' => $book->id,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('loans', [
        'requester_name' => 'Student Name',
        'book_id' => $book->id,
    ]);
});

it('can loan book as a teacher', function () {
    $teacher = createUserWithRole('docente');
    $book = Book::factory()->create([
        'available_copies' => 2,
        'is_available' => true,
    ]);

    $response = $this->actingAs($teacher)->postJson('/api/v1/loans', [
        'requester_name' => 'Teacher Name',
        'book_id' => $book->id,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('loans', [
        'requester_name' => 'Teacher Name',
        'book_id' => $book->id,
    ]);
});

it('can return book as a student', function () {
    $student = createUserWithRole('estudiante');
    $book = Book::factory()->create([
        'available_copies' => 4,
        'is_available' => true,
    ]);

    $loan = Loan::create([
        'requester_name' => 'Student Name',
        'book_id' => $book->id,
    ]);

    $response = $this->actingAs($student)->postJson("/api/v1/loans/{$loan->id}/return");

    $response->assertOk();
    $this->assertDatabaseMissing('loans', [
        'id' => $loan->id,
        'return_at' => null,
    ]);
});

it('can return book as a teacher', function () {
    $teacher = createUserWithRole('docente');
    $book = Book::factory()->create([
        'available_copies' => 4,
        'is_available' => true,
    ]);

    $loan = Loan::create([
        'requester_name' => 'Teacher Name',
        'book_id' => $book->id,
    ]);

    $response = $this->actingAs($teacher)->postJson("/api/v1/loans/{$loan->id}/return");

    $response->assertOk();
    $this->assertDatabaseMissing('loans', [
        'id' => $loan->id,
        'return_at' => null,
    ]);
});

it('can update available books after a return', function () {
    $student = createUserWithRole('estudiante');
    $book = Book::factory()->create([
        'available_copies' => 0,
        'is_available' => false,
    ]);

    $loan = Loan::create([
        'requester_name' => 'Student Name',
        'book_id' => $book->id,
    ]);

    $response = $this->actingAs($student)->postJson("/api/v1/loans/{$loan->id}/return");

    $response->assertOk();

    // Check book is now available and has 1 copy
    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'available_copies' => 1,
        'is_available' => 1,
    ]);
});

it('can view loan history as a student', function () {
    $student = createUserWithRole('estudiante');

    $response = $this->actingAs($student)->getJson('/api/v1/loans');

    $response->assertOk();
});

it('can view loan history as a teacher', function () {
    $teacher = createUserWithRole('docente');

    $response = $this->actingAs($teacher)->getJson('/api/v1/loans');

    $response->assertOk();
});

it('can view loan history as a librarian', function () {
    $librarian = createUserWithRole('bibliotecario');

    $response = $this->actingAs($librarian)->getJson('/api/v1/loans');

    $response->assertOk();
});

it('can return an empty list when there are no loans registered', function () {
    $librarian = createUserWithRole('bibliotecario');

    $response = $this->actingAs($librarian)->getJson('/api/v1/loans');

    $response->assertOk();

    $json = $response->json();
    if (isset($json['data'])) {
        $response->assertJsonCount(0, 'data');
    } else {
        $this->assertEmpty($json);
    }
});
