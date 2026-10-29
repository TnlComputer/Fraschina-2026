<?php

namespace Tests\Feature;

use App\Models\Deposito;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SugerirOrdenTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  /** Ubicaciones de prueba: el depósito y calles cada vez más lejos hacia el este. */
  private const UBICACIONES = ['Cerca' => [-34.66, -58.51], 'Medio' => [-34.62, -58.45], 'Lejos' => [-34.60, -58.37]];

  protected function setUp(): void
  {
    parent::setUp();
    Permission::firstOrCreate(['name' => 'permiso_2', 'guard_name' => 'web']);
    $this->user = $this->conAcciones(User::factory()->create()->givePermissionTo('permiso_2'));
    app(\App\Services\RutaReparto::class)->pausa = 0;
    $this->app->resolving(\App\Services\RutaReparto::class, fn($ruta) => $ruta->pausa = 0);

    Http::fake(function (Request $request) {
      foreach (self::UBICACIONES as $calle => [$lat, $lon]) {
        if (str_contains($request['q'], $calle)) {
          return Http::response([['lat' => (string) $lat, 'lon' => (string) $lon]]);
        }
      }
      return Http::response([]);
    });
  }

  private function pedido(string $calle, ?string $chofer = null, array $horario = []): int
  {
    $calleId = DB::table('auxcalles')->insertGetId(['calle' => mb_strtoupper($calle)]);
    $cliente = DB::table('distribucions')->insertGetId(array_merge(
      ['razonsocial' => "Cliente $calle", 'nomfantasia' => $calle, 'dire_calle_id' => $calleId, 'dire_nro' => '100', 'status' => 'A'], $horario));
    $pedido = DB::table('distribucion_nropedidos')->insertGetId(['distribucion_id' => $cliente, 'fechaEntrega' => '2026-10-28', 'chofer' => $chofer, 'tipo' => 'P', 'status' => 'A']);
    DB::table('distribucion_linea_pedidos')->insert(['pedido_id' => $pedido, 'distribucion_id' => $cliente, 'cantidad' => 1]);
    return $pedido;
  }

  private function orden(int $pedido): ?int
  {
    return DB::table('distribucion_nropedidos')->where('id', $pedido)->value('orden');
  }

  public function test_carga_el_recorrido_mas_corto_desde_el_deposito(): void
  {
    Deposito::create(['nombre' => 'Depósito', 'direccion' => 'X 1', 'localidad' => 'Y', 'principal' => true, 'latitud' => -34.6637, 'longitud' => -58.5201]);
    $lejos = $this->pedido('Lejos');
    $cerca = $this->pedido('Cerca');
    $medio = $this->pedido('Medio');

    $this->actingAs($this->user)->post('/distribucion/reparto/sugerir-orden', ['fecha' => '2026-10-28'])
      ->assertRedirect(route('distribucion_reparto.index', ['fecha' => '2026-10-28']))->assertSessionHas('success');

    $this->assertSame([1, 2, 3], [$this->orden($cerca), $this->orden($medio), $this->orden($lejos)]);
    // La ubicación queda guardada en la ficha: la segunda vez no se vuelve a buscar
    $this->assertSame(3, DB::table('distribucions')->whereNotNull('latitud')->count());
    Http::assertSentCount(3);
    $this->actingAs($this->user)->post('/distribucion/reparto/sugerir-orden', ['fecha' => '2026-10-28']);
    Http::assertSentCount(3);
  }

  public function test_respeta_el_horario_cada_chofer_por_separado_y_los_sin_ubicar_al_final(): void
  {
    Deposito::create(['nombre' => 'Depósito', 'direccion' => 'X 1', 'localidad' => 'Y', 'principal' => true, 'latitud' => -34.6637, 'longitud' => -58.5201]);
    $cerca = $this->pedido('Cerca', '1');
    $lejosTemprano = $this->pedido('Lejos', '1', ['desde' => '07:00:00', 'hasta' => '08:00:00']);
    $sinUbicar = $this->pedido('Inexistente', '1');
    $otroChofer = $this->pedido('Medio', '2');

    $this->actingAs($this->user)->post('/distribucion/reparto/sugerir-orden', ['fecha' => '2026-10-28'])
      ->assertSessionHas('warning', fn($m) => str_contains($m, 'Sin ubicar') && str_contains($m, 'Inexistente'));

    $this->assertSame([1, 2, 3], [$this->orden($lejosTemprano), $this->orden($cerca), $this->orden($sinUbicar)]);
    $this->assertSame(1, $this->orden($otroChofer));
  }

  public function test_con_un_solo_chofer_se_asigna_a_los_pedidos_sin_chofer(): void
  {
    Deposito::create(['nombre' => 'Depósito', 'direccion' => 'X 1', 'localidad' => 'Y', 'principal' => true, 'latitud' => -34.6637, 'longitud' => -58.5201]);
    $sinChofer = $this->pedido('Cerca');
    $otro = $this->pedido('Medio', 'J');

    // La migración deja cargado solo a H
    $this->actingAs($this->user)->post('/distribucion/reparto/sugerir-orden', ['fecha' => '2026-10-28'])
      ->assertSessionHas('success', fn($m) => str_contains($m, 'Se asignó el chofer H a 1 pedido'));
    $this->assertSame('H', DB::table('distribucion_nropedidos')->where('id', $sinChofer)->value('chofer'));
    $this->assertSame('J', DB::table('distribucion_nropedidos')->where('id', $otro)->value('chofer'));

    // Con dos choferes cargados no asigna nada
    DB::table('choferes')->insert(['codigo' => 'J', 'nombre' => 'Otro']);
    $nuevo = $this->pedido('Lejos');
    $this->actingAs($this->user)->post('/distribucion/reparto/sugerir-orden', ['fecha' => '2026-10-28']);
    $this->assertNull(DB::table('distribucion_nropedidos')->where('id', $nuevo)->value('chofer'));
  }

  public function test_sin_deposito_avisa_y_no_cambia_nada(): void
  {
    $pedido = $this->pedido('Cerca');
    $this->actingAs($this->user)->post('/distribucion/reparto/sugerir-orden', ['fecha' => '2026-10-28'])->assertSessionHas('error');
    $this->assertNull($this->orden($pedido));
  }
}
