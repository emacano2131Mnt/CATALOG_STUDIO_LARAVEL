<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_landing_page_is_public_and_has_auth_buttons(): void
    {
        $this->get('/')->assertOk()->assertSee('Catalog Studio')->assertSee('Registrarse')->assertSee('Iniciar sesión');
    }
}
