<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Campos que tienen datos en la base y deben verse y guardarse desde los formularios.
 */
class FormulariosDatosTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    foreach (['permiso_1', 'permiso_2'] as $p) {
      Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }
    $this->user = User::factory()->create();
    $this->user->givePermissionTo(['permiso_1', 'permiso_2']);
  }

  public function test_personal_de_representacion_muestra_y_guarda_todos_sus_datos(): void
  {
    $rep = DB::table('representacions')->insertGetId(['razonsocial' => 'Molinera SA', 'status' => 'A']);
    $area = DB::table('auxareas')->insertGetId(['area' => 'Compras']);
    $cargo = DB::table('auxcargos')->insertGetId(['cargo' => 'Gerente', 'categoria_id' => 50]);
    $persona = DB::table('representacion_personal')->insertGetId([
      'nombre' => 'Ana', 'apellido' => 'Gómez', 'representacion_id' => $rep, 'area_id' => $area, 'cargo_id' => $cargo,
      'email' => 'ana@molinera.com', 'telcelular' => '11 5555-0000', 'fuera' => 0, 'status' => 'A',
    ]);

    $this->actingAs($this->user)->get("/representacion/representacion_personal/{$persona}/edit")->assertOk()
      ->assertSee('ana@molinera.com')->assertSee('11 5555-0000')
      ->assertSee('Compras')->assertSee('Gerente');  // antes las opciones salían vacías

    $this->actingAs($this->user)->put("/representacion/representacion_personal/{$persona}", [
      'nombre' => 'Ana', 'apellido' => 'Gómez', 'representacion_id' => $rep, 'area_id' => $area, 'cargo_id' => $cargo,
      'email' => 'ana@molinera.com; compras@molinera.com', 'telcelular' => '11 5555-0000', 'fuera' => '1',
    ])->assertRedirect("/representacion/{$rep}");

    $fila = DB::table('representacion_personal')->find($persona);
    $this->assertSame('ana@molinera.com; compras@molinera.com', $fila->email);
    $this->assertEquals(1, $fila->fuera);
    $this->assertEquals(50, $fila->categoriacargo_id);  // la categoría acompaña al cargo
    $this->assertSame('A', $fila->status);
  }

  public function test_representacion_guarda_fax_y_email(): void
  {
    $rep = DB::table('representacions')->insertGetId(['razonsocial' => 'Molinera SA', 'status' => 'A', 'fax' => '4444-1111', 'correo' => 'info@molinera.com']);

    $this->actingAs($this->user)->get("/representacion/{$rep}/edit")->assertOk()->assertSee('4444-1111')->assertSee('info@molinera.com');

    $this->actingAs($this->user)->put("/representacion/{$rep}", ['razonsocial' => 'Molinera SA', 'fax' => '4444-2222', 'correo' => 'ventas@molinera.com']);
    $fila = DB::table('representacions')->find($rep);
    $this->assertSame('4444-2222', $fila->fax);
    $this->assertSame('ventas@molinera.com', $fila->correo);
  }

  public function test_agenda_guarda_fecha_de_ultima_cotizacion(): void
  {
    $cliente = DB::table('distribucions')->insertGetId(['razonsocial' => 'Panadería', 'nomfantasia' => 'La Espiga', 'status' => 'A']);
    $prioridad = DB::table('auxprioridades')->insertGetId(['nombre' => 'Alta']);
    $accion = DB::table('auxacciones')->insertGetId(['accion' => 'Llamar', 'status' => 'A']);
    $persona = DB::table('distribucion_personal')->insertGetId(['nombre' => 'Juan', 'distribucion_id' => $cliente, 'status' => 'A']);
    $agenda = DB::table('distribucion_agenda')->insertGetId([
      'distribucion_id' => $cliente, 'fecha' => '2026-10-12', 'hs' => '09:00', 'prioridad_id' => $prioridad, 'accion_id' => $accion,
      'cotizacion' => '000-$22172', 'status' => 'A',
    ]);

    $this->actingAs($this->user)->put("/distribucion_agenda/{$agenda}", [
      'distribucion_id' => $cliente, 'fecha' => '2026-10-12', 'hs' => '09:00', 'prioridad_id' => $prioridad, 'accion_id' => $accion,
      'persona_id' => $persona, 'cotizacion' => '000-$23000', 'fecCot' => '2026-10-01',
    ])->assertRedirect('/distribucion_agenda');

    $fila = DB::table('distribucion_agenda')->find($agenda);
    $this->assertSame('000-$23000', $fila->cotizacion);
    $this->assertStringStartsWith('2026-10-01', (string) $fila->fecCot);
    $this->actingAs($this->user)->get("/distribucion_agenda/{$agenda}/edit")->assertOk()->assertSee('2026-10-01');
  }
}
