<?php

namespace Tests\Feature;

use App\Models\MigracionDuda;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpedicionHistorialDudasTest extends TestCase
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
  }

  public function test_carga_historial_y_lo_muestra_con_filtros(): void
  {
    $dir = sys_get_temp_dir();
    file_put_contents("$dir/diaria.csv", "fila,f,h,m,p,ra,c,es,fe,he,ord,pl,ca,t,b,precio,d,o\n2,20200424,1300,1005,5117,5109,19,,20200429,800,7d12,,30R,1,,19.6,ConfF,\n3,20200424,1300,1007,1116,1116,18,*,2020-99,,1d2,,30,1,,19.5,PedN,Abona en el dia\n");
    file_put_contents("$dir/ctrol.csv", "fila,fe,m,c,transporte,hc,o,g,kg_mol,kg_cte,kg_dif,remito,cte\n2,20200327,1001,*8 30HC,ACONCAGUA,,OK,,,,,,\n3,20200327,ALBERTI,sin numero,,,,,,,,,\n");

    $this->artisan('expedicion:historia', ['diaria' => "$dir/diaria.csv", 'ctrol' => "$dir/ctrol.csv"])->assertSuccessful();
    $this->artisan('expedicion:historia', ['diaria' => "$dir/diaria.csv", 'ctrol' => "$dir/ctrol.csv"])->assertSuccessful();
    $this->assertSame(2, DB::table('exp_historial_diaria')->count());
    $this->assertSame(8, DB::table('exp_historial_control')->where('fila', 2)->value('cliente_numero'));
    $this->assertNull(DB::table('exp_historial_diaria')->where('fila', 3)->value('fecha_entrega'));
    $this->assertStringContainsString('2020-99', DB::table('exp_historial_diaria')->where('fila', 3)->value('original'));

    $this->actingAs($this->user)->get('/expedicion/historial?accion=PedN')->assertOk()->assertSee('Abona en el dia')->assertDontSee('5109');
    $this->actingAs($this->user)->get('/expedicion/historial?vista=control&cliente=8')->assertOk()->assertSee('ACONCAGUA');
  }

  public function test_las_dudas_se_ven_y_se_responden(): void
  {
    $duda = MigracionDuda::create(['clave' => 'prueba', 'origen' => 'Plan ped', 'tema' => 'Planilla "Que Rico"',
      'pregunta' => '¿Qué número de cliente es?', 'detalle' => ['columnas' => ['Texto'], 'filas' => [['MUCHAS GRACIAS']]]]);

    $this->actingAs($this->user)->get('/expedicion/dudas')->assertOk()->assertSee('Que Rico')->assertSee('MUCHAS GRACIAS');
    $this->actingAs($this->user)->put("/expedicion/dudas/{$duda->id}", ['respuesta' => 'Es el 49', 'resuelta' => '1'])->assertRedirect();
    $this->assertSame('Es el 49', $duda->fresh()->respuesta);
    $this->assertNotNull($duda->fresh()->resuelta_at);
    $this->actingAs($this->user)->get('/expedicion/dudas')->assertOk()->assertDontSee('Que Rico');   // pendientes
    $this->actingAs($this->user)->get('/expedicion/dudas?estado=resueltas')->assertOk()->assertSee('Es el 49');
  }
}
