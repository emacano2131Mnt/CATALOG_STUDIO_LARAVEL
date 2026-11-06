<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_hashed_password(): void
    {
        $response = $this->post('/registro', ['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'secreta123', 'password_confirmation' => 'secreta123']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertNotSame('secreta123', $user->password);
        $this->assertTrue(Hash::check('secreta123', $user->password));
    }

    public function test_registration_validates_input(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);
        $this->post('/registro', ['name' => '', 'email' => 'dup@example.com', 'password' => 'corta', 'password_confirmation' => 'otra'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'secreta123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secreta123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        $user = User::factory()->create(['password' => 'secreta123']);
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guests_are_redirected_from_protected_routes(): void
    {
        foreach (['/dashboard', '/catalogs', '/products', '/categories', '/perfil', '/disenador', '/importacion', '/informes', '/api-consumo'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }

    public function test_authenticated_users_cannot_see_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard');
    }

    public function test_password_reset_flow(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'vieja12345']);

        $this->post('/recuperar-contrasena', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::createToken($user);
        $this->get('/restablecer-contrasena/'.$token.'?email='.$user->email)->assertOk()->assertSee('Nueva contraseña');

        $this->post('/restablecer-contrasena', ['token' => $token, 'email' => $user->email, 'password' => 'nueva12345', 'password_confirmation' => 'nueva12345'])
            ->assertRedirect('/login');
        $this->assertTrue(Hash::check('nueva12345', $user->fresh()->password));
    }

    public function test_reset_with_invalid_token_fails(): void
    {
        $user = User::factory()->create();
        $this->post('/restablecer-contrasena', ['token' => 'falso', 'email' => $user->email, 'password' => 'nueva12345', 'password_confirmation' => 'nueva12345'])
            ->assertSessionHasErrors('email');
    }

    public function test_forgot_password_does_not_reveal_unknown_emails(): void
    {
        $this->post('/recuperar-contrasena', ['email' => 'nadie@example.com'])->assertSessionHas('status')->assertSessionHasNoErrors();
    }

    public function test_profile_can_be_viewed_and_updated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/perfil')->assertOk()->assertSee($user->email);

        $this->actingAs($user)->put('/perfil', ['name' => 'Nuevo Nombre', 'email' => 'nuevo@example.com'])->assertRedirect('/perfil');
        $this->assertSame('Nuevo Nombre', $user->fresh()->name);
        $this->assertSame('nuevo@example.com', $user->fresh()->email);
    }

    public function test_profile_email_must_be_unique(): void
    {
        $other = User::factory()->create();
        $this->actingAs(User::factory()->create())->put('/perfil', ['name' => 'X', 'email' => $other->email])->assertSessionHasErrors('email');
    }

    public function test_password_can_be_changed_only_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'actual12345']);

        $this->actingAs($user)->put('/perfil/contrasena', ['current_password' => 'mala', 'password' => 'nueva12345', 'password_confirmation' => 'nueva12345'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/perfil/contrasena', ['current_password' => 'actual12345', 'password' => 'nueva12345', 'password_confirmation' => 'nueva12345'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('nueva12345', $user->fresh()->password));
    }
}
