<?php

namespace Tests\Feature;

use App\Models\AuxBarrios;
use App\Models\AuxPrioridades;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuxiliaresTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    Permission::firstOrCreate(['name' => 'permiso_9', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('permiso_9');
  }

  public function test_alta_y_edicion_de_un_barrio(): void
  {
    $this->actingAs($this->user)->get('/tools/barrios/create')->assertOk()->assertSee('Nuevo barrio')->assertSee('Guardar');

    $this->actingAs($this->user)->post('/tools/barrios', ['nombrebarrio' => 'Palermo'])->assertRedirect('/tools/barrios');
    $barrio = AuxBarrios::where('nombrebarrio', 'Palermo')->firstOrFail();

    $this->actingAs($this->user)->get("/tools/barrios/{$barrio->id}/edit")->assertOk()->assertSee('Palermo');
    $this->actingAs($this->user)->put("/tools/barrios/{$barrio->id}", ['nombrebarrio' => 'Palermo Soho'])->assertRedirect('/tools/barrios');
    $this->assertSame('Palermo Soho', $barrio->fresh()->nombrebarrio);

    // Repetido: no se acepta
    $this->actingAs($this->user)->post('/tools/barrios', ['nombrebarrio' => 'Palermo Soho'])->assertSessionHasErrors('nombrebarrio');
  }

  public function test_baja_logica_y_no_se_elimina_si_esta_en_uso(): void
  {
    $libre = AuxPrioridades::create(['nombre' => 'Baja', 'color' => 'Verde']);
    $usada = AuxPrioridades::create(['nombre' => 'Alta', 'color' => 'Rojo']);
    $cliente = DB::table('distribucions')->insertGetId(['razonsocial' => 'Cliente', 'status' => 'A']);
    DB::table('distribucion_agenda')->insert(['prioridad_id' => $usada->id, 'distribucion_id' => $cliente]);

    $this->actingAs($this->user)->from('/tools/prioridades')->delete("/tools/prioridades/{$usada->id}")
      ->assertSessionHas('error');
    $this->assertNull($usada->fresh()->deleted_at);

    $this->actingAs($this->user)->delete("/tools/prioridades/{$libre->id}")->assertSessionHas('success');
    $this->assertSoftDeleted('auxprioridades', ['id' => $libre->id]);  // queda en la base
    $this->actingAs($this->user)->get('/tools/prioridades')->assertDontSee('Baja');
  }

  public function test_valor_eliminado_se_sigue_ofreciendo_solo_a_quien_lo_tiene(): void
  {
    $a = AuxBarrios::create(['nombrebarrio' => 'Activo']);
    $b = AuxBarrios::create(['nombrebarrio' => 'Viejo']);
    $b->delete();

    $this->assertEquals(['Activo'], tabla_aux('auxbarrios')->pluck('nombrebarrio')->all());
    $this->assertEqualsCanonicalizing(['Activo', 'Viejo'], tabla_aux('auxbarrios', $b->id)->pluck('nombrebarrio')->all());
  }

  public function test_unir_omite_partes_vacias(): void
  {
    $this->assertSame('Pérez - Centro', unir('Pérez', '', null, ' Centro '));
    $this->assertSame('Centro', unir('', 'Centro'));
    $this->assertSame('', unir(null, ''));
  }

  public function test_la_opcion_sin_dato_no_se_modifica_ni_se_elimina(): void
  {
    $sinDato = AuxBarrios::create(['nombrebarrio' => '']);

    $this->actingAs($this->user)->get("/tools/barrios/{$sinDato->id}/edit")->assertRedirect('/tools/barrios')->assertSessionHas('info');
    $this->actingAs($this->user)->put("/tools/barrios/{$sinDato->id}", ['nombrebarrio' => 'Otro'])->assertSessionHas('info');
    $this->actingAs($this->user)->delete("/tools/barrios/{$sinDato->id}")->assertSessionHas('info');
    $this->assertSame('', $sinDato->fresh()->nombrebarrio);
    $this->assertNull($sinDato->fresh()->deleted_at);

    $this->actingAs($this->user)->get('/tools/barrios')->assertSee('(sin dato)');
    $this->assertSame('(sin dato)', sin_dato('  '));
  }
}
