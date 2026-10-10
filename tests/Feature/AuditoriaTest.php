<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['permiso_7', 'permiso_99'] as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
        $this->admin = User::factory()->create(['name' => 'Vero']);
        $this->admin->givePermissionTo('permiso_99');
    }

    public function test_registra_alta_modificacion_y_eliminacion_con_usuario_y_sector(): void
    {
        $this->actingAs($this->admin)->post('/expedicion_general', ['fecha' => '2026-10-01', 'mo' => 'LUJ', 'cl' => 'ARG', 'w' => 300]);
        $id = DB::table('expedicion_general')->value('id');

        $this->actingAs($this->admin)->put("/expedicion_general/{$id}", ['fecha' => '2026-10-01', 'mo' => 'LUJ', 'cl' => 'ARG', 'w' => 320]);
        $this->actingAs($this->admin)->delete("/expedicion_general/{$id}");

        $log = Auditoria::where('tabla', 'expedicion_general')->where('registro_id', $id)->orderBy('id')->get();
        $this->assertSame(['alta', 'modificacion', 'eliminacion'], $log->pluck('accion')->all());
        $this->assertTrue($log->every(fn($r) => $r->usuario === 'Vero' && $r->sector === 'Expedición'));
        $this->assertSame(['300', '320'], array_map('strval', $log[1]->cambios['w']));
        $this->assertSame(['A', 'D'], $log[2]->cambios['status']);
    }

    public function test_nunca_guarda_contrasenas(): void
    {
        $usuario = User::factory()->create();
        $usuario->update(['password' => bcrypt('otra-clave-123')]);

        $registros = Auditoria::where('tabla', 'users')->where('registro_id', $usuario->id)->get();
        $this->assertNotEmpty($registros);
        $this->assertStringNotContainsString('otra-clave', json_encode($registros->pluck('cambios')));
        $this->assertArrayNotHasKey('password', $registros->firstWhere('accion', 'alta')->cambios);
    }

    public function test_pantalla_con_filtros_solo_para_administrador(): void
    {
        $this->actingAs($this->admin)->post('/expedicion_general', ['fecha' => '2026-10-01', 'mo' => 'LUJ', 'cl' => 'ARG']);

        $this->actingAs($this->admin)->get('/tools/auditoria?sector=Expedición&accion=alta&user_id=' . $this->admin->id . '&desde=2020-01-01&hasta=2030-01-01')
            ->assertOk()->assertSee('ExpedicionGeneral')->assertSee('Vero');
        $this->actingAs($this->admin)->get('/tools/auditoria?sector=Distribución')
            ->assertOk()->assertSee('Sin operaciones con esos filtros');
        $this->actingAs($this->admin)->get('/tools/auditoria/export')->assertOk();

        $otro = User::factory()->create();
        $otro->givePermissionTo('permiso_7');
        $this->actingAs($otro)->get('/tools/auditoria')->assertForbidden();
    }
}
