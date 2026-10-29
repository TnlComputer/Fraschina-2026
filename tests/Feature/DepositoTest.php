<?php

namespace Tests\Feature;

use App\Models\Deposito;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DepositoTest extends TestCase
{
  use RefreshDatabase;

  private User $usuario;

  protected function setUp(): void
  {
    parent::setUp();
    foreach (['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13'] as $p) {
      Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web'], ['description' => $p]);
    }
    $rol = Role::firstOrCreate(['name' => 'gerencia', 'guard_name' => 'web']);
    $rol->syncPermissions(['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13']);
    $this->usuario = User::factory()->create()->assignRole($rol);
  }

  private function datos(array $cambios = []): array
  {
    return array_merge(['nombre' => 'Depósito Ciudadela', 'direccion' => 'Av. Rivadavia 12000', 'localidad' => 'Ciudadela, Tres de Febrero'], $cambios);
  }

  public function test_el_primero_queda_principal_y_se_ubica_en_el_mapa(): void
  {
    Http::fake(['nominatim.openstreetmap.org/*' => Http::response([['lat' => '-34.6380', 'lon' => '-58.5400']])]);

    $this->actingAs($this->usuario)->get('/tools/depositos/create')->assertOk();
    $this->actingAs($this->usuario)->post('/tools/depositos', $this->datos())->assertRedirect('/tools/depositos');

    $deposito = Deposito::first();
    $this->assertTrue($deposito->principal);
    $this->assertEqualsWithDelta(-34.638, $deposito->latitud, 0.0001);
    $this->assertEqualsWithDelta(-58.54, $deposito->longitud, 0.0001);
    $this->actingAs($this->usuario)->get('/tools/depositos')->assertSee('Principal')->assertSee('Ver en el mapa');
  }

  public function test_sin_encontrar_la_direccion_avisa_y_acepta_la_ubicacion_a_mano(): void
  {
    Http::fake(['nominatim.openstreetmap.org/*' => Http::response([])]);

    $this->actingAs($this->usuario)->post('/tools/depositos', $this->datos())->assertSessionHas('success', fn($m) => str_contains($m, 'a mano'));
    $this->assertFalse(Deposito::first()->tieneUbicacion());

    $deposito = Deposito::first();
    $this->actingAs($this->usuario)->put("/tools/depositos/{$deposito->id}", $this->datos(['latitud' => '-34.64', 'longitud' => '-58.53']));
    $this->assertEqualsWithDelta(-34.64, $deposito->fresh()->latitud, 0.0001);
  }

  public function test_un_solo_principal_y_el_principal_no_se_elimina(): void
  {
    Http::fake(['nominatim.openstreetmap.org/*' => Http::response([['lat' => '-34.6', 'lon' => '-58.5']])]);

    $this->actingAs($this->usuario)->post('/tools/depositos', $this->datos());
    $this->actingAs($this->usuario)->post('/tools/depositos', $this->datos(['nombre' => 'Depósito 2', 'principal' => 1]));

    $this->assertSame(['Depósito 2'], Deposito::where('principal', true)->pluck('nombre')->all());
    $principal = Deposito::where('principal', true)->first();
    $this->actingAs($this->usuario)->from('/tools/depositos')->delete("/tools/depositos/{$principal->id}")->assertSessionHas('error');
    $this->assertNotSoftDeleted($principal);

    $otro = Deposito::where('principal', false)->first();
    $this->actingAs($this->usuario)->delete("/tools/depositos/{$otro->id}")->assertSessionHas('success');
    $this->assertSoftDeleted($otro);
  }
}
