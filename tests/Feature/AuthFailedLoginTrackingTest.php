<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthFailedLoginTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_increments_attempts_and_locks()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'correct-password',
            'is_active' => true,
            'is_locked' => false,
            'failed_login_attempts' => 0,
        ]);

        // Attempt 1
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong'
        ])->assertSessionHasErrors('email');
        $this->assertEquals(1, $user->fresh()->failed_login_attempts);

        // Attempt 2, 3, 4
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);

        $user->refresh();
        $this->assertEquals(4, $user->failed_login_attempts);
        $this->assertFalse($user->is_locked);

        // Attempt 5 (Lock threshold)
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertTrue($user->is_locked);
        $this->assertNotNull($user->locked_at);

        // Subsequent login blocked by lock
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'correct-password' // Even with correct password, account is locked
        ])->assertSessionHasErrors('email');
    }

    public function test_successful_login_resets_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => 'correct-password',
            'is_active' => true,
            'is_locked' => false,
            'failed_login_attempts' => 3,
        ]);

        $this->post('/login', [
            'email' => 'test2@example.com',
            'password' => 'correct-password'
        ])->assertRedirect(); // successful login redirects

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNotNull($user->last_login_at);
    }
}
