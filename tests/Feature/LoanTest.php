<?php

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    // to have the roles available for tests
    $this->seed(PermissionSeeder::class);
});

// to create users with a specific role
function createUserWithRole(string $roleName)
{
    $user = User::factory()->create();
    $role = Role::findByName($roleName, 'api');
    $user->assignRole($role);
    return $user;
}

// to create books with specific available copies
function createAvailableBook(int $copies = 5)
{
    return Book::factory()->create([
        'available_copies' => $copies,
        'is_available' => $copies > 0,
    ]);
}

//Test #40: It can loan a book as a student
it('can loan book as a student', function () {
    $student = createUserWithRole('estudiante');
    $book = createAvailableBook(5);

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

//Test #41: It can loan a book as a teacher
it('can loan book as a teacher', function () {
    $teacher = createUserWithRole('docente');
    $book = createAvailableBook(2);

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

//Test #42: It can return a book as a student
it('can return book as a student', function () {
    $student = createUserWithRole('estudiante');
    $book = createAvailableBook(4);

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

//Test #43: It can return a book as a teacher
it('can return book as a teacher', function () {
    $teacher = createUserWithRole('docente');
    $book = createAvailableBook(4);

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

//Test #44: It can update available books after a return
it('can update available books after a return', function () {
    $student = createUserWithRole('estudiante');
    $book = createAvailableBook(0); // just to show no books available

    $loan = Loan::create([
        'requester_name' => 'Student Name',
        'book_id' => $book->id,
    ]);

    $response = $this->actingAs($student)->postJson("/api/v1/loans/{$loan->id}/return");

    $response->assertOk();

    // to check book is now available and has 1 copy
    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'available_copies' => 1,
        'is_available' => 1,
    ]);
});

//Test #45: It can view loan history as a student
it('can view loan history as a student', function () {
    $student = createUserWithRole('estudiante');

    $response = $this->actingAs($student)->getJson('/api/v1/loans');

    $response->assertOk();
});

//Test #46: It can view loan history as a teacher
it('can view loan history as a teacher', function () {
    $teacher = createUserWithRole('docente');

    $response = $this->actingAs($teacher)->getJson('/api/v1/loans');

    $response->assertOk();
});

//Test #47: It can view loan history as a librarian
it('can view loan history as a librarian', function () {
    $librarian = createUserWithRole('bibliotecario');

    $response = $this->actingAs($librarian)->getJson('/api/v1/loans');

    $response->assertOk();
});

//Test #48: It can return an empty list when there are no loans registered
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
