<?php

namespace Tests\Feature;

use App\Models\ExpedicionCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpedicionPlanPedTest extends TestCase
{
  use RefreshDatabase;

  public function test_carga_textos_sin_pisar_y_guarda_notas_por_molino(): void
  {
    $torinese = ExpedicionCliente::create(['numero' => 6, 'sigla' => 'GTOR']);
    $mauri = ExpedicionCliente::create(['numero' => 19, 'sigla' => 'MAU', 'linea1' => 'Texto que ya estaba']);

    $json = tempnam(sys_get_temp_dir(), 'pp');
    file_put_contents($json, json_encode([
      'G. Torinese' => ['cliente' => 'G. TORINESE', 'lineas' => ['ENVIAR CERTIFICADO DE CALIDAD.', 'MUCHAS GRACIAS'],
        'notas_derecha' => ['ALBERTI ACLARAR:', '" SIN BLANQUEADOR"'], 'otras_columnas' => []],
      'Mauri' => ['cliente' => 'MAURI', 'lineas' => ['OTRO TEXTO'], 'notas_derecha' => [], 'otras_columnas' => []],
    ]));

    $this->artisan('expedicion:planped', ['json' => $json])->assertSuccessful();
    $this->artisan('expedicion:planped', ['json' => $json])->assertSuccessful();  // repetir no duplica
    @unlink($json);

    $torinese->refresh();
    $this->assertSame(['ENVIAR CERTIFICADO DE CALIDAD.', 'MUCHAS GRACIAS'], $torinese->lineasTexto());
    $this->assertSame(1, substr_count($torinese->obs, '[Plan ped: G. Torinese]'));
    $this->assertStringContainsString('SIN BLANQUEADOR', $torinese->obs);
    $this->assertSame(['Texto que ya estaba'], $mauri->fresh()->lineasTexto());  // no se pisa
  }
}
