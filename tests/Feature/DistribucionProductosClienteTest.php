<?php

namespace Tests\Feature;

use App\Models\DistribucionLineaPedidos;
use App\Models\DistribucionNroPedidos;
use App\Models\DistribucionProducto;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DistribucionProductosClienteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private int $cliente;
    private int $harina;
    private int $aceite;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'permiso_2', 'guard_name' => 'web']);
        $this->user = $this->conAcciones(User::factory()->create()->givePermissionTo('permiso_2'));
        $this->actingAs($this->user);

        $this->cliente = DB::table('distribucions')->insertGetId(['razonsocial' => 'Test SRL', 'nomfantasia' => 'La Test', 'status' => 'A']);
        $this->harina = DB::table('productos_c_d_a')->insertGetId(['productoCDA' => 'Harina 000', 'ivacda' => '10.5', 'status' => 'A']);
        $this->aceite = DB::table('productos_c_d_a')->insertGetId(['productoCDA' => 'Aceite', 'ivacda' => '21', 'status' => 'A']);
        DB::table('distribucion_productos')->insert(['distribucion_id' => $this->cliente, 'producto_id' => $this->harina, 'precio' => 1000, 'status' => 'A']);
    }

    private function agregar(int $producto, $precio = 500)
    {
        return $this->postJson('/distribucion/distribucion_producto', [
            'distribucion_id' => $this->cliente, 'producto_id' => $producto, 'precio' => $precio, 'fecha' => '2026-11-24',
        ]);
    }

    private function pedir(array $extra = [])
    {
        return $this->post('/distribucion_pedido', $extra + [
            'cliente_id' => $this->cliente, 'fecha_entrega' => '2026-11-30', 'productos' => [$this->harina],
            'tipos' => ['P'], 'cantidades' => [1], 'precios' => [1000],
        ]);
    }

    public function test_agrega_al_cliente_desde_el_pedido_y_queda_en_su_lista(): void
    {
        $this->agregar($this->aceite)->assertCreated();
        $this->getJson("/distribucion/{$this->cliente}/productos-tareas")->assertOk()
            ->assertJsonPath('productos.0.nombre', 'Aceite')->assertJsonPath('productos.1.nombre', 'Harina 000');
        $this->pedir(['productos' => [$this->aceite]])->assertSessionHasNoErrors();
        $this->assertSame(1, DistribucionNroPedidos::count());
    }

    public function test_no_repite_y_reactiva_el_dado_de_baja(): void
    {
        $this->agregar($this->harina)->assertStatus(422)->assertJsonValidationErrors('producto_id');

        DistribucionProducto::query()->update(['status' => 'D']);
        $this->pedir()->assertSessionHasErrors('error');
        $this->agregar($this->harina, 1200)->assertCreated();
        $this->assertSame(1, DistribucionProducto::count());
        $this->assertEquals(1200, DistribucionProducto::first()->precio);
        $this->assertSame('A', DistribucionProducto::first()->status);
    }

    public function test_sin_permiso_de_alta_no_agrega(): void
    {
        $this->user->revokePermissionTo('permiso_11');
        $this->agregar($this->aceite)->assertForbidden();
    }

    public function test_sin_cargo_retirar_y_ultima_entrega(): void
    {
        $this->agregar($this->aceite);
        $this->pedir([
            'productos' => [$this->harina, $this->aceite], 'tipos' => ['P', 'P'], 'cantidades' => [2, 1],
            'precios' => [1000, 500], 'flag' => ['1', '0'], 'sin_cargo' => ['0', '1'], 'retirar' => ['0', '0'],
        ])->assertSessionHasNoErrors();

        $this->assertNull(DistribucionLineaPedidos::where('producto_id', $this->harina)->value('cambiar'));
        $this->assertSame('*', DistribucionLineaPedidos::where('producto_id', $this->aceite)->value('cambiar'));
        $this->assertSame('*', DistribucionLineaPedidos::where('producto_id', $this->harina)->value('bandera'));
        // El Flag figura en el pedido pero no en el reparto
        $this->get('/distribucion_reparto/' . DistribucionNroPedidos::value('id'))->assertOk()->assertSee('1.000,00*');
        $this->get('/distribucion_reparto?fecha=2026-11-30')->assertOk()->assertSee('1.000,00')->assertDontSee('1.000,00*');
        // Lo sin cargo no cuenta como última entrega; y no retrocede con un pedido anterior
        $this->assertSame('2026-11-30', (string) DistribucionProducto::where('producto_id', $this->harina)->value('fechaUEnt'));
        $this->assertNull(DistribucionProducto::where('producto_id', $this->aceite)->value('fechaUEnt'));
        $this->pedir(['fecha_entrega' => '2026-11-01']);
        $this->assertSame('2026-11-30', (string) DistribucionProducto::where('producto_id', $this->harina)->value('fechaUEnt'));
    }

    public function test_editar_marca_lineas_y_suma_solo_con_la_misma_marca(): void
    {
        $this->pedir();
        $pedido = DistribucionNroPedidos::firstOrFail();
        $linea = $pedido->lineasPedidos()->first();
        $base = ['fecha_entrega' => '2026-11-30', 'existing_product_line_ids' => [$linea->id], 'existing_precios' => [1000], 'existing_cantidades' => [1]];

        $this->put("/distribucion_pedido/{$pedido->id}", $base + [
            'existing_flag' => ['1'], 'existing_sin_cargo' => ['0'], 'existing_retirar' => ['1'],
            'new_productos' => [$this->harina], 'new_precios' => [1000], 'new_cantidades' => [3], 'new_sin_cargo' => ['1'], 'new_retirar' => ['0'],
        ])->assertSessionHasNoErrors();
        $this->assertSame('*', $linea->fresh()->retirar);
        $this->assertSame('*', $linea->fresh()->bandera);
        $this->assertSame(2, $pedido->lineasPedidos()->count());

        // Sin los campos de marcas no se tocan
        $this->put("/distribucion_pedido/{$pedido->id}", $base);
        $this->assertSame('*', $linea->fresh()->retirar);

        $this->put("/distribucion_pedido/{$pedido->id}", $base + ['new_productos' => [$this->aceite], 'new_precios' => [1], 'new_cantidades' => [1]])
            ->assertSessionHasErrors('error');
    }

    public function test_pantallas(): void
    {
        $this->get("/distribucion/{$this->cliente}")->assertOk()->assertSee('Harina 000')
            ->assertSee('Agregar producto')->assertSee(route('distribucion_pedido.create', ['cliente' => $this->cliente]), false);
        $this->get("/distribucion_pedido/create?cliente={$this->cliente}")->assertOk()
            ->assertSee('value="' . $this->cliente . '" selected', false)->assertSee('Agregar producto al cliente');
        $this->pedir();
        $this->get('/distribucion_pedido/' . DistribucionNroPedidos::value('id') . '/edit')->assertOk()->assertSee('Agregar producto al cliente');
        $this->get("/distribucion/{$this->cliente}/productos/imprimir")->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_catalogo(): void
    {
        $this->get('/distribucion_catalogo')->assertOk()->assertSee('Harina 000');
        $this->get('/distribucion_catalogo/create')->assertOk();
        $this->post('/distribucion_catalogo', ['productoCDA' => 'Semolín', 'ivacda' => '10.5'])->assertRedirect();
        $id = DB::table('productos_c_d_a')->where('productoCDA', 'Semolín')->value('id');
        $this->post('/distribucion_catalogo', ['productoCDA' => 'Semolín', 'ivacda' => '21'])->assertSessionHasErrors('productoCDA');
        $this->get("/distribucion_catalogo/$id/edit")->assertOk();
        $this->put("/distribucion_catalogo/$id", ['productoCDA' => 'Semolín fino', 'ivacda' => '10.5'])->assertRedirect();
        $this->delete("/distribucion_catalogo/$id")->assertRedirect();
        $this->assertSame('D', DB::table('productos_c_d_a')->where('id', $id)->value('status'));
        $this->agregar($id)->assertStatus(422);
    }
}
