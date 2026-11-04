<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_login_and_registration_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Selamat Datang Kembali')
            ->assertSee('NusaMart');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat Akun Baru')
            ->assertSee('Konfirmasi kata sandi');
    }

    public function test_new_user_can_register_and_password_is_hashed(): void
    {
        $this->post(route('register'), [
            'name' => 'Pengguna NusaMart',
            'email' => 'pengguna@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'pengguna@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame('Password123!', $user->password);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Selamat Datang di NusaMart')
            ->assertSee('Pengguna NusaMart');
    }

    public function test_users_can_log_in_and_log_out_and_dashboard_requires_authentication(): void
    {
        $user = User::factory()->create([
            'email' => 'pelanggan@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))->assertOk()->assertSee($user->name);

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_invalid_credentials_return_a_validation_error(): void
    {
        $this->post(route('login'), [
            'email' => 'unknown@example.com',
            'password' => 'incorrect',
        ])->assertSessionHasErrors('email');
    }
}
