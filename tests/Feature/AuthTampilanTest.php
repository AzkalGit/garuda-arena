<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTampilanTest extends TestCase
{
    public function test_halaman_masuk_memakai_desain_garuda(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke akun')
            ->assertSee('Siap main?')
            ->assertSee('css/auth.css', false)
            ->assertSee(route('register'), false)
            ->assertDontSee('@garudaarena.test');
    }

    public function test_halaman_daftar_memakai_desain_garuda(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat akun')
            ->assertSee('name="name"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('css/auth.css', false)
            ->assertSee(route('login'), false);
    }

    public function test_view_auth_bersih_dari_gaya_terlarang(): void
    {
        $pola = '/class="[^"]*(bg-|text-|px-|py-|rounded|border-gray|flex|grid-cols|space-)|style=|<style/';

        foreach (['auth/login', 'auth/register'] as $berkas) {
            $this->assertDoesNotMatchRegularExpression(
                $pola,
                file_get_contents(resource_path("views/{$berkas}.blade.php")),
                $berkas
            );
        }

        $this->assertDoesNotMatchRegularExpression(
            $pola,
            file_get_contents(resource_path('views/components/auth-layout.blade.php'))
        );
    }
}
