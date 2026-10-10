<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Tests\TestCase;

class ExpedicionGeneralTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'permiso_7', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('permiso_7');
        $this->conAcciones($this->user);

        DB::table('expedicion_general')->insert([
            ['fecha' => '2025-02-10', 'mo' => 'LUJ', 'cl' => 'ARG', 'particularidades' => 'febrero', 'status' => 'A'],
            ['fecha' => '2025-03-05', 'mo' => 'LUJ', 'cl' => 'ARG', 'particularidades' => 'marzo', 'status' => 'A'],
            ['fecha' => '2025-02-20', 'mo' => 'LUJ', 'cl' => 'ARG', 'particularidades' => 'borrado', 'status' => 'D'],
        ]);
    }

    public function test_filtra_por_anio_y_mes(): void
    {
        $this->actingAs($this->user)->get('/expedicion_general?fecha=202502')
            ->assertOk()
            ->assertSee('febrero')
            ->assertDontSee('marzo');
    }

    public function test_no_muestra_registros_dados_de_baja(): void
    {
        $this->actingAs($this->user)->get('/expedicion_general?fecha=2025')
            ->assertOk()
            ->assertSee('febrero')
            ->assertDontSee('borrado');
    }

    public function test_guarda_gi(): void
    {
        $this->actingAs($this->user)->post('/expedicion_general', [
            'fecha' => '2025-04-01', 'mo' => 'LUJ', 'cl' => 'ARG', 'gi' => 27,
        ])->assertRedirect(route('expedicion_general.index'));

        $this->assertDatabaseHas('expedicion_general', ['fecha' => '2025-04-01', 'gi' => 27]);
    }
}
