<?php

namespace Tests\Feature;

use App\Models\DistribucionLineaPedidos;
use App\Models\DistribucionNroPedidos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Tests\TestCase;

class DistribucionPedidoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private int $clienteId;
    private int $harina;   // IVA 10.5
    private int $aceite;   // IVA 21

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'permiso_2', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('permiso_2');
        $this->conAcciones($this->user);

        $this->clienteId = DB::table('distribucions')->insertGetId(['razonsocial' => 'Panadería Test', 'nomfantasia' => 'La Test', 'status' => 'A']);
        $this->harina = DB::table('productos_c_d_a')->insertGetId(['productoCDA' => 'Harina 000', 'ivacda' => '10.5', 'status' => 'A']);
        $this->aceite = DB::table('productos_c_d_a')->insertGetId(['productoCDA' => 'Aceite', 'ivacda' => '21', 'status' => 'A']);

        foreach ([$this->harina => 1000, $this->aceite => 500] as $productoId => $precio) {
            DB::table('distribucion_productos')->insert([
                'distribucion_id' => $this->clienteId,
                'producto_id' => $productoId,
                'precio' => $precio,
                'status' => 'A',
            ]);
        }
    }

    public function test_crear_pedido_calcula_totales_con_iva(): void
    {
        $this->actingAs($this->user)->post('/distribucion_pedido', [
            'cliente_id' => $this->clienteId,
            'fecha_entrega' => '2026-10-15',
            'productos' => [$this->harina, $this->aceite],
            'tipos' => ['P', 'P'],
            'cantidades' => [2, 3],
            'precios' => [1000, 500],
        ])->assertRedirect(route('distribucion_agenda.index'));

        $pedido = DistribucionNroPedidos::firstOrFail();
        // 2 x 1000 x 1.105 = 2210 ; 3 x 500 x 1.21 = 1815
        $this->assertEquals(2210.00, (float) $pedido->lineasPedidos()->where('producto_id', $this->harina)->value('totalLinea'));
        $this->assertEquals(1815.00, (float) $pedido->lineasPedidos()->where('producto_id', $this->aceite)->value('totalLinea'));
        $this->assertEquals(4025.00, (float) $pedido->totalPedido);
        $this->assertSame('P', $pedido->tipo);
    }

    public function test_producto_ajeno_al_cliente_no_deja_pedido_a_medias(): void
    {
        $otro = DB::table('productos_c_d_a')->insertGetId(['productoCDA' => 'No asignado', 'status' => 'A']);

        $this->actingAs($this->user)->post('/distribucion_pedido', [
            'cliente_id' => $this->clienteId,
            'fecha_entrega' => '2026-10-15',
            'productos' => [$this->harina, $otro],
            'tipos' => ['P', 'P'],
            'cantidades' => [1, 1],
            'precios' => [1000, 100],
        ])->assertSessionHasErrors('error');

        $this->assertSame(0, DistribucionNroPedidos::count());
        $this->assertSame(0, DistribucionLineaPedidos::count());
    }

    public function test_editar_pedido_recalcula_y_no_toca_lineas_de_otro_pedido(): void
    {
        $pedido = $this->crearPedido();
        $otroPedido = $this->crearPedido();
        $lineaPropia = $pedido->lineasPedidos()->first();
        $lineaAjena = $otroPedido->lineasPedidos()->first();

        $this->actingAs($this->user)->put("/distribucion_pedido/{$pedido->id}", [
            'fecha_entrega' => '2026-10-20',
            // Se intenta colar una línea de otro pedido
            'existing_product_line_ids' => [$lineaPropia->id, $lineaAjena->id],
            'existing_precios' => [2000, 1],
            'existing_cantidades' => [1, 1],
            'new_productos' => [$this->aceite],
            'new_precios' => [500],
            'new_cantidades' => [2],
        ])->assertRedirect();

        $pedido->refresh();
        // 1 x 2000 x 1.105 = 2210 ; 2 x 500 x 1.21 = 1210
        $this->assertEquals(3420.00, (float) $pedido->totalPedido);
        $this->assertSame('2026-10-20', (string) $pedido->fechaEntrega);
        $this->assertEquals(2, $pedido->lineasPedidos()->max('linea'));

        // La línea del otro pedido quedó intacta
        $this->assertEquals(1000, (float) $lineaAjena->fresh()->precio_unitario);

        // Quitar una línea la oculta (borrado lógico), no la borra
        $this->actingAs($this->user)->put("/distribucion_pedido/{$pedido->id}", [
            'fecha_entrega' => '2026-10-20',
            'existing_product_line_ids' => [$lineaPropia->id],
            'existing_precios' => [2000],
            'existing_cantidades' => [1],
        ])->assertRedirect();
        $this->assertSame(1, $pedido->lineasPedidos()->count());
        $this->assertSame(2, DistribucionLineaPedidos::withTrashed()->where('pedido_id', $pedido->id)->count());
        $this->assertEquals(2210.00, (float) $pedido->fresh()->totalPedido);
    }

    public function test_abrir_y_editar_pedido_vuelve_al_dia_de_reparto(): void
    {
        $pedido = $this->crearPedido();
        $lineaId = $pedido->lineasPedidos()->value('id');

        // Un solo botón por fila: abre el pedido recordando el día de Reparto
        $abrir = route('distribucion_reparto.show', ['distribucion_reparto' => $pedido->id, 'volver' => '2026-10-15']);
        $planilla = $this->actingAs($this->user)->get('/distribucion_reparto?fecha=2026-10-15')->assertSee(e($abrir), false);
        $this->assertSame(1, substr_count($planilla->getContent(), 'title="Ver pedido"'));

        // Ver → Editar y Volver llevan ese día
        $this->actingAs($this->user)->get($abrir)->assertOk()
            ->assertSee(e(route('distribucion_pedido.edit', ['distribucion_pedido' => $pedido->id, 'volver' => '2026-10-15'])), false)
            ->assertSee(e(route('distribucion_reparto.index', ['fecha' => '2026-10-15'])), false);
        $this->actingAs($this->user)->get("/distribucion_pedido/{$pedido->id}/edit?volver=2026-10-15")->assertOk()
            ->assertSee('name="volver" value="2026-10-15"', false);

        // Aunque se cambie la fecha de entrega, al guardar vuelve al día del que se salió
        $this->actingAs($this->user)->put("/distribucion_pedido/{$pedido->id}", [
            'fecha_entrega' => '2026-10-20', 'volver' => '2026-10-15',
            'existing_product_line_ids' => [$lineaId], 'existing_precios' => [1000], 'existing_cantidades' => [1],
        ])->assertRedirect(route('distribucion_reparto.index', ['fecha' => '2026-10-15']));
    }

    public function test_pantallas_de_reparto_y_agenda_renderizan(): void
    {
        $pedido = $this->crearPedido();

        $this->actingAs($this->user)->get('/distribucion_reparto?fecha=2026-10-15')->assertOk()->assertSee('La Test');
        $this->actingAs($this->user)->get("/distribucion_reparto/{$pedido->id}")->assertOk()->assertSee('1.105,00');
        $this->actingAs($this->user)->get('/distribucion/reparto/imprimir?fecha=2026-10-15')
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($this->user)->get('/distribucion/reparto/imprimir?fecha=15-10-2026')->assertSessionHasErrors('fecha');

        DB::table('distribucion_agenda')->insert([
            'fecha' => '2026-10-10', 'distribucion_id' => $this->clienteId, 'temas' => 'Llamar', 'status' => 'A',
        ]);
        $this->actingAs($this->user)->get('/distribucion_agenda')->assertOk()->assertSee('Harina 000');
    }

    public function test_calendario_de_reparto_marca_dias_con_pedidos(): void
    {
        $this->crearPedido();
        $this->crearPedido();

        $this->actingAs($this->user)->getJson('/distribucion/reparto/fechas?desde=2026-10-01&hasta=2026-10-31')
            ->assertOk()
            ->assertExactJson(['2026-10-15' => 2]);

        $this->actingAs($this->user)->getJson('/distribucion/reparto/fechas?desde=2026-11-01&hasta=2026-11-30')
            ->assertOk()->assertExactJson([]);

        $this->actingAs($this->user)->get('/distribucion_reparto?fecha=2026-10-15')
            ->assertOk()->assertSee('flatpickr', false)->assertDontSee('actualizarBtn', false);
    }

    public function test_reparto_muestra_y_guarda_factura_chofer_y_orden(): void
    {
        $pedido = $this->crearPedido();
        DB::table('distribucions')->where('id', $this->clienteId)->update(['fac_imp' => 'NO']);

        // Cada campo de la fila está asociado a su formulario (antes el <form> dentro de la tabla los perdía)
        $this->actingAs($this->user)->get('/distribucion_reparto?fecha=2026-10-15')
            ->assertOk()
            ->assertSee('name="nroFactura" form="distribucion-form-' . $pedido->id . '"', false)
            ->assertSee('name="chofer" form="distribucion-form-' . $pedido->id . '"', false)
            ->assertSee('id="estado-pedido-' . $pedido->id . '"', false)
            ->assertDontSee('fa-times-circle', false)   // sin factura impresa: sin cruz roja
            ->assertDontSee('fa-sync-alt', false);      // sin botón de guardar: se guarda solo

        $this->actingAs($this->user)->put("/distribucion_reparto/{$pedido->id}", [
            'fechaFactura' => '2026-10-14', 'nroFactura' => '16683', 'totalFactura' => 1105,
            'chofer' => 'H', 'orden' => 3, 'fecha' => '2026-10-15',
        ])->assertRedirect();

        $pedido->refresh();
        $this->assertSame(['16683', 'H', 3], [$pedido->nroFactura, $pedido->chofer, (int) $pedido->orden]);
        $this->actingAs($this->user)->get('/distribucion_reparto?fecha=2026-10-15')->assertSee('value="16683"', false);

        // Guardado automático al salir del campo: responde JSON, sin redirigir
        $this->actingAs($this->user)->putJson("/distribucion_reparto/{$pedido->id}", [
            'fechaFactura' => '2026-10-14', 'nroFactura' => '16684', 'totalFactura' => 1105,
            'chofer' => 'J', 'orden' => 4, 'fecha' => '2026-10-15',
        ])->assertOk()->assertExactJson(['ok' => true]);
        $this->assertSame(['16684', 'J', 4], [$pedido->fresh()->nroFactura, $pedido->fresh()->chofer, (int) $pedido->fresh()->orden]);

        // Dato inválido: no se guarda y devuelve el error para mostrarlo
        $this->actingAs($this->user)->putJson("/distribucion_reparto/{$pedido->id}", ['chofer' => 'XYZ'])
            ->assertStatus(422)->assertJsonValidationErrors('chofer');
        $this->assertSame('J', $pedido->fresh()->chofer);

        // Cliente que lleva factura impresa: impresora para imprimir
        DB::table('distribucions')->where('id', $this->clienteId)->update(['fac_imp' => 'SI']);
        $this->actingAs($this->user)->get('/distribucion_reparto?fecha=2026-10-15')->assertSee('Imprimir factura');
    }

    public function test_indicadores_si_no_del_cliente(): void
    {
        foreach (['SI', 'si', 'S', 'on', '1', true] as $valor) {
            $this->assertTrue(\App\Models\Distribucion::esSi($valor), var_export($valor, true));
        }
        foreach (['NO', 'off', '', null, '0', false] as $valor) {
            $this->assertFalse(\App\Models\Distribucion::esSi($valor), var_export($valor, true));
        }
    }

    public function test_formulario_de_cliente_envia_a_la_ruta_de_clientes(): void
    {
        $this->actingAs($this->user)->get('/distribucion/create')
            ->assertOk()
            ->assertSee('action="' . route('distribucion.store') . '"', false)
            ->assertDontSee('Falta alta personal');

        $this->actingAs($this->user)->get("/distribucion/{$this->clienteId}/edit")
            ->assertOk()
            ->assertSee('action="' . route('distribucion.update', $this->clienteId) . '"', false);
    }

    public function test_reparto_vista_chofer(): void
    {
        $this->crearPedido();

        $this->actingAs($this->user)->get('/distribucion/reparto/movil?fecha=2026-10-15')
            ->assertOk()->assertSee('La Test')->assertSee('Harina 000');
        $this->actingAs($this->user)->get('/distribucion_agenda?dia=hoy')->assertOk();
        $this->actingAs($this->user)->get('/distribucion_agenda?dia=2026-10-15')->assertOk();
    }

    private function crearPedido(): DistribucionNroPedidos
    {
        $this->actingAs($this->user)->post('/distribucion_pedido', [
            'cliente_id' => $this->clienteId,
            'fecha_entrega' => '2026-10-15',
            'productos' => [$this->harina],
            'tipos' => ['P'],
            'cantidades' => [1],
            'precios' => [1000],
        ]);

        return DistribucionNroPedidos::latest('id')->firstOrFail();
    }
}
