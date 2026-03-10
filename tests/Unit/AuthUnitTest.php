<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthUnitTest extends TestCase
{
    use RefreshDatabase;

    //Test #2: It can validate email
    public function test_login_can_validate_email(): void
    {
        //Execution
        $response = $this->post('/api/v1/login', [
            'email'=> 'notvalid@email@.com',
            'password'=> 'anyPasswordTest123'
        ]);

        //Verification
        $response->assertInvalid(['email']);
    }

    //Test #3: It can return token
    public function test_can_return_token():void
    {
        $user = User::factory()->create([
            'password'=> bcrypt('test123'),
        ]);

        $response = $this->post('/api/v1/login', [
            'email'=> $user->email,
            'password'=> 'test123',
        ]);

        $response->assertJsonStructure([
            'access_token',
        ]);

        $user->delete();
    }
}
