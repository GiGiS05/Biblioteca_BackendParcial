<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BookLoggedOutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        //Make sure there is no active session
        Auth::logout();
        $this->flushSession();
        session()->invalidate();
        session()->regenerateToken();
    }

    //Test #9: It cannot create books while logged out
    public function test_it_cannot_create_books_while_logged_out(): void
    {
        //Execution
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ',
            'Accept' => 'application/json',
        ])->post('/api/v1/books', [
            'title'=> 'Test Book',
            'description'=> 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Laudantium, adipisci!',
            'ISBN'=>'1234567891123',
            'total_copies'=>10,
            'available_copies'=>5,
            'is_available'=>true,
        ]);

        //Verification
        $response->assertUnauthorized();
    }

    //Test #10: It cannot update books while logged out
    public function test_it_cannot_update_books_while_logged_out(): void
    {
        //Execution
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ',
            'Accept' => 'application/json',
        ])->patch('/api/v1/books/1', [
            'title'=> 'Example Test - Update Title',
        ]);

        //Verification
        $response->assertUnauthorized();
    }

    //Test #7: It cannot list books while logged out
    public function test_it_cannot_list_books_while_logged_out(): void
    {
        //Execution
        $response = $this->withHeaders([
            'Authorization'=>'Bearer ',
            'Accept'=>'application/json',
        ])->get('/api/v1/books');

        //Verification
        $response->assertUnauthorized();
    }

    //Test #8: It cannot get books details while logged out
    public function test_it_cannot_get_book_details_while_logged_out(): void
    {
        //Execution
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ',
            'Accept'=>'application/json',
        ])->get('/api/v1/books/1');

        //Verification
        $response->assertUnauthorized();
    }
}