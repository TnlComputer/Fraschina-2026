<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    foreach (['permiso_2', 'permiso_7', 'permiso_99'] as $p) {
      Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('permiso_2');
  }

  public function test_marca_los_dias_con_agenda(): void
  {
    $cliente = DB::table('distribucions')->insertGetId(['razonsocial' => 'Cliente', 'status' => 'A']);
    DB::table('distribucion_agenda')->insert([
      ['distribucion_id' => $cliente, 'fecha' => '2026-10-12', 'status' => 'A'],
      ['distribucion_id' => $cliente, 'fecha' => '2026-10-12', 'status' => 'A'],
      ['distribucion_id' => $cliente, 'fecha' => '2026-10-15', 'status' => 'A'],
      ['distribucion_id' => $cliente, 'fecha' => '2026-10-20', 'status' => 'D'],  // eliminada: no cuenta
    ]);

    $this->actingAs($this->user)->getJson('/calendario/dias?fuente=agenda&desde=2026-10-01&hasta=2026-10-31')
      ->assertOk()->assertExactJson(['2026-10-12' => 2, '2026-10-15' => 1]);
  }

  public function test_cada_fuente_respeta_su_permiso(): void
  {
    $this->getJson('/calendario/dias?fuente=agenda&desde=2026-10-01&hasta=2026-10-31')->assertUnauthorized();
    $this->actingAs($this->user)->getJson('/calendario/dias?fuente=auditoria&desde=2026-10-01&hasta=2026-10-31')->assertForbidden();
    $this->actingAs($this->user)->getJson('/calendario/dias?fuente=exp_entregas&desde=2026-10-01&hasta=2026-10-31')->assertForbidden();
    $this->actingAs($this->user)->getJson('/calendario/dias?fuente=otra&desde=2026-10-01&hasta=2026-10-31')->assertNotFound();
  }

  public function test_la_agenda_usa_el_calendario_con_dias_marcados(): void
  {
    $this->actingAs($this->user)->get('/distribucion_agenda')->assertOk()
      ->assertSee('data-dias="agenda"', false)->assertSee('disableMobile: true', false);
  }
}
