<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central de Pedidos (Expedición).
 *
 * Cada pedido tiene un número correlativo único y una revisión (-0, -1, -2...).
 * Una modificación de un pedido confirmado crea una fila nueva con el mismo número
 * y la revisión siguiente; la anterior queda como histórico (vigente = false).
 * Los pedidos pendientes de confirmación todavía no tienen número.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('exp_pedidos', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('numero')->nullable();
      $table->unsignedSmallInteger('revision')->default(0);
      $table->boolean('vigente')->default(true);
      $table->unsignedBigInteger('expedicion_cliente_id');
      $table->unsignedBigInteger('expedicion_molino_id');
      // pendiente_confirmacion | confirmado | pendiente_cierre | cerrado | anulado
      $table->string('estado', 30)->default('pendiente_confirmacion');
      // normal | muestra (muestra sin cargo)
      $table->string('tipo', 20)->default('normal');
      $table->date('fecha_pedido');
      $table->text('observaciones')->nullable();
      $table->string('archivo')->nullable();
      $table->timestamp('enviado_at')->nullable();
      $table->unsignedBigInteger('user_id')->nullable();
      $table->timestamps();

      $table->unique(['numero', 'revision']);
      $table->index(['estado', 'vigente']);
      $table->foreign('expedicion_cliente_id')->references('id')->on('expedicion_clientes');
      $table->foreign('expedicion_molino_id')->references('id')->on('expedicion_molinos');
    });

    Schema::create('exp_pedido_lineas', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('pedido_id');
      $table->unsignedSmallInteger('linea')->default(1);
      $table->string('calidad', 100);
      $table->unsignedInteger('tolvas')->default(0);
      $table->unsignedInteger('bolsas')->default(0);
      $table->unsignedInteger('kilos')->nullable();
      $table->decimal('precio', 12, 3)->nullable();
      $table->string('condicion', 100)->nullable();
      $table->string('planta', 100)->nullable();
      $table->date('fecha_entrega')->nullable();
      $table->boolean('fecha_tentativa')->default(false);
      $table->string('nota_fecha')->nullable();
      $table->date('fecha_alternativa')->nullable();
      $table->string('hora', 20)->nullable();
      $table->unsignedSmallInteger('plazo_dias')->nullable();
      $table->string('forma_pago', 100)->nullable();
      $table->string('lugar_entrega')->nullable();
      $table->text('observaciones')->nullable();
      $table->timestamps();

      $table->foreign('pedido_id')->references('id')->on('exp_pedidos')->cascadeOnDelete();
      $table->index('fecha_entrega');
    });

    // Historial de cada movimiento de pedidos (reemplaza el Excel "Diaria")
    Schema::create('exp_pedido_movimientos', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('pedido_id');
      $table->unsignedInteger('numero')->nullable();
      $table->unsignedSmallInteger('revision')->default(0);
      $table->string('accion', 40);
      $table->text('detalle')->nullable();
      $table->unsignedBigInteger('user_id')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->foreign('pedido_id')->references('id')->on('exp_pedidos')->cascadeOnDelete();
      $table->index(['numero', 'revision']);
    });

    // Seguimiento de cada entrega (reemplaza la hoja "Ctrol.")
    Schema::create('exp_entregas', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('pedido_linea_id')->unique();
      $table->string('transportista', 100)->nullable();
      $table->string('chofer', 100)->nullable();
      $table->date('fecha_carga')->nullable();
      $table->string('hora_carga', 20)->nullable();
      $table->string('hora_llegada', 20)->nullable();
      $table->text('observaciones')->nullable();
      $table->boolean('descarga_completa')->default(false);
      $table->unsignedInteger('kg_molino')->nullable();
      $table->unsignedInteger('kg_cliente')->nullable();
      $table->string('nro_remito', 50)->nullable();
      $table->boolean('certificado_recibido')->default(false);
      $table->date('certificado_fecha')->nullable();
      $table->timestamps();

      $table->foreign('pedido_linea_id')->references('id')->on('exp_pedido_lineas')->cascadeOnDelete();
    });

    // Destinatarios de avisos por mail/WhatsApp, con activación por evento
    Schema::create('exp_notificacion_destinatarios', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('expedicion_molino_id')->nullable();
      $table->unsignedBigInteger('expedicion_cliente_id')->nullable();
      // mail | whatsapp
      $table->string('canal', 20);
      $table->string('destino', 150);
      // pedido_nuevo | pedido_modificado | consulta_manana | consulta_tarde
      $table->string('evento', 40);
      $table->boolean('activo')->default(true);
      $table->timestamps();

      $table->foreign('expedicion_molino_id')->references('id')->on('expedicion_molinos')->cascadeOnDelete();
      $table->foreign('expedicion_cliente_id')->references('id')->on('expedicion_clientes')->cascadeOnDelete();
    });

    // Configuración por combinación cliente-molino: condiciones habituales y checklist de la planilla
    Schema::table('expedicion_molinos_textos_clientes', function (Blueprint $table) {
      $table->unsignedSmallInteger('plazo_dias')->nullable()->after('expMolino_id');
      $table->string('forma_pago', 100)->nullable()->after('plazo_dias');
      $table->string('condicion', 100)->nullable()->after('forma_pago');
      $table->string('planta', 100)->nullable()->after('condicion');
      $table->boolean('mostrar_precio')->nullable()->after('planta'); // null = según el molino
      $table->boolean('subrayar_precio')->default(false)->after('mostrar_precio');
      $table->boolean('mostrar_fecha_alternativa')->default(true)->after('subrayar_precio');
      $table->boolean('mostrar_especiales')->default(true)->after('mostrar_fecha_alternativa');
    });

    // Certificados de calidad vinculados a la entrega, preparados para carga automática
    Schema::table('expedicion_general', function (Blueprint $table) {
      $table->unsignedBigInteger('entrega_id')->nullable()->after('id');
      $table->string('archivo')->nullable();
      // manual | ia
      $table->string('origen', 20)->default('manual');
      $table->boolean('revisado')->default(true);
      $table->index('entrega_id');
    });
  }

  public function down(): void
  {
    Schema::table('expedicion_general', function (Blueprint $table) {
      $table->dropIndex(['entrega_id']);
      $table->dropColumn(['entrega_id', 'archivo', 'origen', 'revisado']);
    });

    Schema::table('expedicion_molinos_textos_clientes', function (Blueprint $table) {
      $table->dropColumn([
        'plazo_dias', 'forma_pago', 'condicion', 'planta',
        'mostrar_precio', 'subrayar_precio', 'mostrar_fecha_alternativa', 'mostrar_especiales',
      ]);
    });

    Schema::dropIfExists('exp_notificacion_destinatarios');
    Schema::dropIfExists('exp_entregas');
    Schema::dropIfExists('exp_pedido_movimientos');
    Schema::dropIfExists('exp_pedido_lineas');
    Schema::dropIfExists('exp_pedidos');
  }
};
