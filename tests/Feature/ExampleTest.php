<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_raiz_lleva_al_ingreso_o_al_inicio(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_ingreso_e_inicio_se_muestran(): void
    {
        $this->get('/login')->assertOk()->assertSee('Ingrese con su correo y contraseña');
        $this->get('/forgot-password')->assertOk();

        $this->actingAs(User::factory()->create(['name' => 'Vero']))->get('/dashboard')
            ->assertOk()->assertSee('Vero');
    }
}
