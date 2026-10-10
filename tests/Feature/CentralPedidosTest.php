<?php

namespace Tests\Feature;

use App\Mail\ConsultaMolinoMail;
use App\Mail\PedidoMolinoMail;
use App\Models\ExpEntrega;
use App\Models\ExpNotificacionDestinatario;
use App\Models\ExpPedido;
use App\Models\ExpPedidoMovimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Permission;
use Tests\TestCase;

class CentralPedidosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private int $cliente;
    private int $molino;

    protected function setUp(): void
    {
        parent::setUp();

        config(['expedicion.numero_inicial' => 5000]);
        Storage::fake('local');
        Mail::fake();

        Permission::firstOrCreate(['name' => 'permiso_7', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('permiso_7');

        $this->cliente = DB::table('expedicion_clientes')->insertGetId(['sigla' => 'ARG', 'linea1' => 'Entregar con turno']);
        $this->molino = DB::table('expedicion_molinos')->insertGetId(['sigla' => 'LUJ', 'calificacion' => '1', 'contMolino' => 'Juan']);
    }

    private function datosPedido(array $cambios = []): array
    {
        return array_merge([
            'expedicion_cliente_id' => $this->cliente,
            'expedicion_molino_id' => $this->molino,
            'tipo' => 'normal',
            'fecha_pedido' => '2026-10-10',
            'lineas' => [
                ['calidad' => '000 Panadera', 'tolvas' => 2, 'precio' => 350.5, 'fecha_entrega' => now()->toDateString(), 'plazo_dias' => 30],
                ['calidad' => '0000', 'bolsas' => 100, 'fecha_entrega' => now()->addDay()->toDateString(), 'fecha_tentativa' => 1],
            ],
        ], $cambios);
    }

    public function test_pedido_pendiente_no_tiene_numero_hasta_confirmarse(): void
    {
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 0]))->assertRedirect();

        $pedido = ExpPedido::firstOrFail();
        $this->assertNull($pedido->numero);
        $this->assertSame(ExpPedido::PENDIENTE_CONFIRMACION, $pedido->estado);
        $this->assertSame('PEND-' . $pedido->id, $pedido->codigo);
        $this->assertCount(2, $pedido->lineas);

        $this->actingAs($this->user)->post("/expedicion_pedido/{$pedido->id}/confirmar")->assertRedirect();
        $this->assertSame('5000-0', $pedido->fresh()->codigo);

        // El siguiente confirmado toma el número correlativo
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 1]));
        $this->assertSame(5001, ExpPedido::latest('id')->first()->numero);
    }

    public function test_cada_linea_debe_tener_tolvas_o_bolsas(): void
    {
        $datos = $this->datosPedido();
        $datos['lineas'][0]['tolvas'] = 0;

        $this->actingAs($this->user)->post('/expedicion_pedido', $datos)->assertSessionHasErrors('lineas.0.tolvas');
        $this->assertSame(0, ExpPedido::count());
    }

    public function test_enviar_archiva_planilla_y_manda_mail_y_modificar_crea_revision(): void
    {
        ExpNotificacionDestinatario::create([
            'expedicion_molino_id' => $this->molino, 'canal' => 'mail', 'destino' => 'pedidos@molino.com', 'evento' => 'pedido_nuevo',
        ]);
        ExpNotificacionDestinatario::create([
            'expedicion_molino_id' => $this->molino, 'canal' => 'mail', 'destino' => 'pedidos@molino.com', 'evento' => 'pedido_modificado',
        ]);

        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 1]));
        $pedido = ExpPedido::firstOrFail();

        // Antes de enviar, editar corrige en el lugar
        $datos = $this->datosPedido();
        $datos['lineas'][0]['tolvas'] = 3;
        $this->actingAs($this->user)->put("/expedicion_pedido/{$pedido->id}", $datos);
        $this->assertSame(1, ExpPedido::count());
        $this->assertSame(3, $pedido->fresh()->lineas->first()->tolvas);

        // Enviar
        $this->actingAs($this->user)->post("/expedicion_pedido/{$pedido->id}/enviar")->assertRedirect()->assertSessionHas('success');
        $pedido->refresh();
        $this->assertNotNull($pedido->enviado_at);
        Storage::disk('local')->assertExists($pedido->archivo);
        $this->assertStringContainsString('5000-0_LUJ_ARG_', $pedido->archivo);
        Mail::assertSent(PedidoMolinoMail::class, fn($m) => $m->hasTo('pedidos@molino.com') && $m->pedido->is($pedido));

        // Una entrega ya cargada en la línea 1
        $linea1 = $pedido->lineas->first();
        ExpEntrega::create(['pedido_linea_id' => $linea1->id, 'transportista' => 'Pérez', 'kg_molino' => 28000]);

        // Después de enviar, modificar crea la revisión -1
        $datos['lineas'][1]['fecha_entrega'] = now()->addDays(3)->toDateString();
        $this->actingAs($this->user)->put("/expedicion_pedido/{$pedido->id}", $datos)->assertRedirect();

        $this->assertFalse($pedido->fresh()->vigente);
        $nueva = ExpPedido::where('numero', 5000)->where('revision', 1)->firstOrFail();
        $this->assertTrue($nueva->vigente);
        $this->assertNull($nueva->enviado_at);
        $this->assertSame('Pérez', $nueva->lineas->first()->entrega->transportista, 'La entrega pasa a la revisión nueva');

        // La planilla de la modificación se puede ver y menciona el cambio de fecha
        $this->actingAs($this->user)->get("/expedicion_pedido/{$nueva->id}/planilla")
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->assertTrue(ExpPedidoMovimiento::where('pedido_id', $nueva->id)->where('accion', 'revision')->exists());
        $this->assertGreaterThanOrEqual(5, ExpPedidoMovimiento::count());
    }

    public function test_condiciones_habituales_del_cliente_molino(): void
    {
        DB::table('expedicion_molinos_textos_clientes')->insert([
            'expClie_id' => $this->cliente, 'expMolino_id' => $this->molino, 'plazo_dias' => 45, 'forma_pago' => 'Cheque',
        ]);

        $this->actingAs($this->user)
            ->getJson("/expedicion_pedido/condiciones?cliente_id={$this->cliente}&molino_id={$this->molino}")
            ->assertOk()
            ->assertJson(['plazo_dias' => 45, 'forma_pago' => 'Cheque', 'configurado' => true]);
    }

    public function test_control_de_entregas_y_certificados_pendientes(): void
    {
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 1]));
        $linea = ExpPedido::firstOrFail()->lineas->first();

        $this->actingAs($this->user)->put("/expedicion/control/{$linea->id}", [
            'transportista' => 'Pérez', 'kg_molino' => 28000, 'kg_cliente' => 27950,
            'nro_remito' => 'R-1', 'descarga_completa' => 1,
        ])->assertRedirect();

        $entrega = $linea->fresh()->entrega;
        $this->assertSame(-50, $entrega->kg_dif);
        $this->assertTrue($entrega->descarga_completa);

        $this->actingAs($this->user)->get('/expedicion/informes/certificados')->assertOk()->assertSee('R-1');
        $this->actingAs($this->user)->get('/expedicion')->assertOk()->assertSee('Certificados de calidad pendientes');

        $this->actingAs($this->user)->put("/expedicion/control/{$linea->id}", ['descarga_completa' => 1, 'certificado_recibido' => 1]);
        $this->assertNotNull($linea->fresh()->entrega->certificado_fecha);
        $this->actingAs($this->user)->get('/expedicion/informes/certificados')->assertOk()->assertSee('No hay certificados pendientes');
    }

    public function test_pantallas_renderizan(): void
    {
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 1]));
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 0]));
        $pedido = ExpPedido::whereNotNull('numero')->firstOrFail();

        foreach ([
            '/expedicion', '/expedicion_pedido', '/expedicion_pedido/create', "/expedicion_pedido/{$pedido->id}",
            "/expedicion_pedido/{$pedido->id}/edit", '/expedicion/planificacion', '/expedicion/control', '/expedicion/diaria',
            '/expedicion/informes/tolvas', '/expedicion/informes/certificados', '/expedicion/avisos',
            '/expedicion_MTC', '/expedicion_MTC/create', '/expedicion_cliente', '/expedicion_cliente/create',
            '/expedicion_molino', '/expedicion_molino/create', '/expedicion/fichas',
        ] as $url) {
            $this->actingAs($this->user)->get($url)->assertOk();
        }

        $this->actingAs($this->user)->get('/expedicion/planificacion')->assertSee('000 Panadera');
        $this->actingAs($this->user)->get('/expedicion/informes/tolvas?desde=' . now()->subDay()->toDateString() . '&hasta=' . now()->addDays(2)->toDateString())
            ->assertOk()->assertSee('LUJ');
    }

    public function test_respaldo_y_consultas_programadas(): void
    {
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 1]));
        ExpNotificacionDestinatario::create([
            'expedicion_molino_id' => $this->molino, 'canal' => 'mail', 'destino' => 'logistica@molino.com', 'evento' => 'consulta_manana',
        ]);

        $this->artisan('expedicion:respaldo')->assertSuccessful();
        Storage::disk('local')->assertExists('expedicion/respaldos/expedicion-' . now()->format('Y-m-d') . '.xlsx');

        $this->artisan('expedicion:consultas manana')->assertSuccessful();
        Mail::assertSent(ConsultaMolinoMail::class, fn($m) => $m->hasTo('logistica@molino.com'));

        // Sin destinatarios para la tarde: no se manda nada
        $this->artisan('expedicion:consultas tarde')->expectsOutput('Consultas enviadas: 0')->assertSuccessful();
    }

    public function test_precio_en_planilla_segun_molino_o_configuracion(): void
    {
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 1]));
        $pedido = ExpPedido::firstOrFail();
        $planilla = app(\App\Services\PlanillaPedido::class);

        // Sin configuración: manda el molino (calificacion = 1 muestra precio)
        $this->assertTrue($planilla->datos($pedido)['mostrarPrecio']);

        // Configuración "según el molino" (vacío) sigue al molino; "No mostrar" lo oculta
        $this->actingAs($this->user)->post('/expedicion_MTC', [
            'expClie_id' => $this->cliente, 'expMolino_id' => $this->molino, 'mostrar_precio' => '', 'plazo_dias' => 30,
        ])->assertRedirect(route('expedicion_MTC.index'));
        $this->assertTrue($planilla->datos($pedido->fresh())['mostrarPrecio']);

        $config = \App\Models\ExpedicionMolinoTextoCliente::firstOrFail();
        $this->actingAs($this->user)->put("/expedicion_MTC/{$config->id}", [
            'expClie_id' => $this->cliente, 'expMolino_id' => $this->molino, 'mostrar_precio' => '0',
        ])->assertRedirect();
        $this->assertFalse($planilla->datos($pedido->fresh())['mostrarPrecio']);

        // No se puede duplicar la combinación
        $this->actingAs($this->user)->post('/expedicion_MTC', [
            'expClie_id' => $this->cliente, 'expMolino_id' => $this->molino,
        ])->assertSessionHasErrors('expMolino_id');
    }

    public function test_nada_se_borra_fisicamente(): void
    {
        ExpNotificacionDestinatario::create([
            'expedicion_molino_id' => $this->molino, 'canal' => 'mail', 'destino' => 'x@molino.com', 'evento' => 'pedido_nuevo',
        ]);
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido(['confirmar' => 0]));
        $pedido = ExpPedido::firstOrFail();

        // Quitar una línea del pedido: se oculta pero queda en la base
        $datos = $this->datosPedido();
        unset($datos['lineas'][1]);
        $this->actingAs($this->user)->put("/expedicion_pedido/{$pedido->id}", $datos);
        $this->assertCount(1, $pedido->fresh()->lineas);
        $this->assertSame(2, \App\Models\ExpPedidoLinea::withTrashed()->where('pedido_id', $pedido->id)->count());

        // Eliminar molino, cliente y destinatario: borrado lógico
        $destinatario = ExpNotificacionDestinatario::firstOrFail();
        $this->actingAs($this->user)->delete("/expedicion/avisos/{$destinatario->id}");
        $this->actingAs($this->user)->delete("/expedicion_molino/{$this->molino}");
        $this->actingAs($this->user)->delete("/expedicion_cliente/{$this->cliente}");

        $this->assertSoftDeleted('exp_notificacion_destinatarios', ['id' => $destinatario->id]);
        $this->assertSoftDeleted('expedicion_molinos', ['id' => $this->molino]);
        $this->assertSoftDeleted('expedicion_clientes', ['id' => $this->cliente]);

        // El pedido sigue mostrando su cliente y molino, pero ya no se pueden elegir para pedidos nuevos
        $this->actingAs($this->user)->get("/expedicion_pedido/{$pedido->id}")->assertOk()->assertSee('LUJ');
        $this->actingAs($this->user)->get('/expedicion_molino')->assertOk()->assertDontSee('LUJ');
        $this->actingAs($this->user)->post('/expedicion_pedido', $this->datosPedido())->assertSessionHasErrors('expedicion_molino_id');
    }

    public function test_sin_permiso_de_expedicion_no_entra(): void
    {
        $otro = User::factory()->create();
        $this->actingAs($otro)->get('/expedicion')->assertForbidden();
    }
}
