<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutTest extends TestCase 
{

    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password'=>bcrypt('test123'),
        ]);
        $response = $this->post('api/v1/login', [
            'email'=> $this->user->email,
            'password'=>'test123',
        ]);

        $this->token = $response->json('token');
    }

    protected function tearDown(): void
    {
        $this->user->delete();
        $this->user = null;
        $this->token = null;
        parent::tearDown();
    }
    
    //Test #5: It can log out
    public function test_it_can_logout() {
        //Execution
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/v1/logout');

        //Verification
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message'=>'Logged out successfully',
        ]);
    }

    //Test #6: It cannot logout if not logged in
    public function test_it_cannot_logout_if_not_logged_in()
    {
        //Execution
        Auth::logout();
        $this->flushSession();
        session()->invalidate();
        session()->regenerateToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . ' ',
            'Accept'=>'application/json',
            ])->post('/api/v1/logout');
        
        //Verification
        $response->assertUnauthorized();
    }

}
