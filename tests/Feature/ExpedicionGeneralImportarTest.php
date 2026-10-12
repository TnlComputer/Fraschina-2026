<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpedicionGeneralImportarTest extends TestCase
{
  use RefreshDatabase;

  public function test_agrega_solo_los_nuevos_y_no_duplica(): void
  {
    // Ya migrado (la ceniza quedó con 2 decimales)
    DB::table('expedicion_general')->insert(['fecha' => '2015-01-03', 'mo' => '1001', 'cl' => 'G.Tor.', 'prod' => '"000 pan"', 'pl' => 1.65, 'w' => 251,
      'gh' => 23, 'hum' => 14.15, 'cz' => 0.660, 'fn' => 376, 'status' => 'A', 'origen' => 'manual', 'revisado' => 1]);

    $csv = tempnam(sys_get_temp_dir(), 'gen');
    file_put_contents($csv, implode("\n", [
      'fila,fecha,mo,cl,modo,prod,p,l,pl,w,gh,gs,gi,hum,cz,est,abs,fn,punt,particularidades',
      '128,2015-01-03,1001,G.Tor.,Granel,"""000 pan""",,,1.65,251,23,9.2,,14.15,0.659,,,376,,muestra*',   // ya está
      '274,2015-02-26,ta,Mau,Granel,"""000 Rotativa""",109,58,1.88,251,,,,,,,,,,',                       // anterior, no coincide
      '9001,2025-03-01,1005,Mau,Granel,"""000 Rotativa""",68,94,0.72,231,24.5,,,14.24,0.63,,,371,,',      // nuevo
    ]));

    $this->artisan('expedicion:general', ['archivo' => $csv])->assertSuccessful();
    $this->assertSame(2, DB::table('expedicion_general')->count());
    $nuevo = DB::table('expedicion_general')->where('fecha', '2025-03-01')->first();
    $this->assertSame('planilla', $nuevo->origen);
    $this->assertEquals(231, $nuevo->w);
    $this->assertFalse(DB::table('expedicion_general')->where('mo', 'ta')->exists());

    $this->artisan('expedicion:general', ['archivo' => $csv])->assertSuccessful();
    $this->assertSame(2, DB::table('expedicion_general')->count());
    @unlink($csv);
  }
}
