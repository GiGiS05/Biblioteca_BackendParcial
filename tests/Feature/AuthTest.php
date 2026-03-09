<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    //Variable necesaria para los tests
    protected $user;

    //Test set up
    protected function setUp(): void
    {
        parent::setUp();
        // Preparacion
        $this->user = User::factory()->create([
            'password' => bcrypt('test123'),
        ]);
    }

    //Cleaning and tear down
    protected function tearDown(): void {
        $this->user->delete();
        $this->user = null;
        parent::tearDown();
    }

    //TEST #1: IT CAN LOGIN
    public function test_it_can_login()
    {
        // Execution
        $response = $this->post('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'test123',
        ]);

        // Verificacion
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'user',
        ]);

        $this->assertAuthenticatedAs($this->user);
    }

    //TEST #4: IT CANNOT LOGIN IN WITH CREDENTIALS THAT DON'T MATCH
    public function test_it_cannot_login_with_credentials_that_do_not_match(): void
    {
        //Execution
        $response = $this->post('/api/v1/login', [
            'email'=> $this->user->email,
            'password'=> 'wrongPassword789',
        ]);

        //Verification
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message'=>'Invalid credentials'
        ]);
    }
}
