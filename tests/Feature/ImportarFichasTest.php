<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportarFichasTest extends TestCase
{
  use RefreshDatabase;

  private function excel(): string
  {
    $libro = new Spreadsheet();
    $hoja = $libro->getActiveSheet();
    $hoja->setTitle('6');
    $hoja->setCellValue('K1', '=SUM(K3:K5)');
    $hoja->fromArray(['M', 'PN', '*', 'FE', 'OR', 'PL', 'CA', 'T/B', 'K', 'P', 'I', 'D', 'N', 'FP', 'PP', 'O'], null, 'A2');
    $hoja->fromArray([1007, 1066, null, 20200212, null, null, 30, 'T', 26400, 19.5, 576576, 'F', 2981, 20200407, 60, null, 'MALSU S.A.'], null, 'A3');
    $hoja->fromArray([1007, null, null, 20202126, null, null, null, null, null, null, -576576, 'R', 142659, null, null, 'pagó'], null, 'A4');
    $hoja->fromArray([1007, '*', null, 20200301, '1d2', null, 'SA', 'B25', 100, 548, 54800, 'F', 3000, null, null, null], null, 'A5');
    $listas = $libro->createSheet();
    $listas->setTitle('LISTAS');
    $listas->fromArray([[null, '1001'], [null, '1002'], [null, '1003']], null, 'A1');
    $ruta = tempnam(sys_get_temp_dir(), 'fichas') . '.xlsx';
    (new Xlsx($libro))->save($ruta);
    return $ruta;
  }

  public function test_importa_sin_perder_datos_y_no_duplica(): void
  {
    $archivo = $this->excel();

    $this->artisan('fichas:importar', ['archivo' => $archivo])->assertSuccessful();
    $this->assertSame(3, DB::table('fichas')->count());  // la hoja LISTAS no se importa

    $primera = DB::table('fichas')->where('fila', 3)->first();
    $this->assertSame('2020-02-12', substr($primera->fe, 0, 10));
    $this->assertEquals(576576, $primera->i);
    $this->assertSame('MALSU S.A.', json_decode($primera->extra, true)['col_Q']);  // columna a la derecha de O

    $mal = DB::table('fichas')->where('fila', 4)->first();
    $this->assertNull($mal->fe);
    $this->assertSame('20202126', json_decode($mal->extra, true)['FE']);  // fecha mal tipeada, guardada tal cual

    $bolsas = DB::table('fichas')->where('fila', 5)->first();
    $this->assertEquals(548, $bolsas->p);
    $this->assertSame('*', json_decode($bolsas->extra, true)['PN']);

    // Correrlo de nuevo no duplica
    $this->artisan('fichas:importar', ['archivo' => $archivo])->assertSuccessful();
    $this->assertSame(3, DB::table('fichas')->count());
    $this->assertTrue(DB::table('auditoria')->where('tabla', 'fichas')->exists());

    @unlink($archivo);
  }

  public function test_la_pantalla_muestra_el_valor_original(): void
  {
    $archivo = $this->excel();
    $this->artisan('fichas:importar', ['archivo' => $archivo]);
    @unlink($archivo);

    Permission::firstOrCreate(['name' => 'permiso_7', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->givePermissionTo('permiso_7');

    $this->actingAs($user)->get('/expedicion/fichas?origen_tabla=6')->assertOk()
      ->assertSee('20202126')->assertSee('MALSU S.A.')->assertSee('576.576,00');
  }
}
