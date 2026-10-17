<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AltaClientesTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    Permission::firstOrCreate(['name' => 'permiso_2', 'guard_name' => 'web']);
    $this->user = $this->conAcciones(User::factory()->create()->givePermissionTo('permiso_2'));
  }

  public function test_mientras_convivan_los_sistemas_no_se_dan_de_alta_clientes(): void
  {
    config(['legado.origen' => 'fraschin_asrl', 'legado.clientes_solo_en_anterior' => true]);

    $this->actingAs($this->user)->get('/distribucion/create')->assertRedirect('/distribucion')->assertSessionHas('warning');
    $this->actingAs($this->user)->post('/distribucion', ['razonsocial' => 'Nuevo'])->assertRedirect('/distribucion');
    $this->assertFalse(DB::table('distribucions')->where('razonsocial', 'Nuevo')->exists());
  }

  public function test_el_cliente_nuevo_sigue_la_numeracion_sin_los_registros_tecnicos(): void
  {
    config(['legado.origen' => null]);
    DB::table('distribucions')->insert([
      ['id' => 694, 'razonsocial' => 'Último real', 'status' => 'A'],
      ['id' => 49999999, 'razonsocial' => 'Sin cliente (sistema anterior)', 'status' => 'D'],
    ]);

    // valores mínimos de las tablas auxiliares obligatorias del formulario
    $aux = ['dire_calle_id' => ['auxcalles', 'calle'], 'barrio_id' => ['auxbarrios', 'nombrebarrio'], 'municipio_id' => ['auxmunicipios', 'ciudadmunicipio'],
      'localidad_id' => ['auxlocalidades', 'localidad'], 'zona_id' => ['auxzonas', 'nombre'], 'rubro_id' => ['auxrubros', 'nombre'],
      'tamanio_id' => ['auxtamanio', 'nombre'], 'modo_id' => ['auxmodos', 'nombre'], 'contacto_id' => ['auxcontacto', 'contacto'],
      'veraz_id' => ['auxveraz', 'estado'], 'estado_id' => ['auxestados', 'nomEstado']];
    $datos = ['razonsocial' => 'Panadería Nueva', 'nomfantasia' => 'La Nueva'];
    foreach ($aux as $campo => [$tabla, $col]) {
      $datos[$campo] = DB::table($tabla)->insertGetId([$col => 'X']);
    }
    $this->actingAs($this->user)->post('/distribucion', $datos)->assertSessionHasNoErrors();
    $this->assertSame(695, (int) DB::table('distribucions')->where('razonsocial', 'Panadería Nueva')->value('id'));
  }
}
