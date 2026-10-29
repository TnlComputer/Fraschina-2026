<?php

namespace Tests\Feature;

use App\Models\Chofer;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChoferTest extends TestCase
{
  use RefreshDatabase;

  public function test_alta_de_chofer_y_aparece_para_elegir_en_reparto(): void
  {
    foreach (['permiso_2', 'permiso_9'] as $p) {
      Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }
    $user = $this->conAcciones(User::factory()->create()->givePermissionTo(['permiso_2', 'permiso_9']));

    // La migración deja cargado a H (Romualdo González); el código se guarda en mayúsculas y no se repite
    $this->actingAs($user)->get('/tools/choferes')->assertOk()->assertSee('Romualdo González');
    $this->actingAs($user)->post('/tools/choferes', ['codigo' => 'jh', 'nombre' => 'Juan H'])->assertRedirect('/tools/choferes');
    $this->assertSame('JH', Chofer::where('nombre', 'Juan H')->value('codigo'));
    $this->actingAs($user)->post('/tools/choferes', ['codigo' => 'H', 'nombre' => 'Otro'])->assertSessionHasErrors('codigo');

    $this->actingAs($user)->get('/distribucion_reparto')->assertOk()
      ->assertSee('<option value="H">Romualdo González</option>', false)->assertSee('<option value="JH">Juan H</option>', false);
  }
}
