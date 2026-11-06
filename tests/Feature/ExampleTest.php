<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_dashboard_is_available(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Panel');
    }
}
