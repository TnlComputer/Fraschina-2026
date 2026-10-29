<?php

namespace App\Services;

use App\Models\Distribucion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sugiere el orden de entrega de un chofer: sale del depósito, respeta el horario de cada cliente
 * y busca el recorrido más corto. Supone salida 7:00, 15 minutos por entrega y 20 km/h en la ciudad
 * (las calles son 1,3 veces la distancia en línea recta).
 */
class RutaReparto
{
  public const SALIDA = 7 * 60;
  public const PARADA = 15;
  public const KMH = 20;
  public const DESVIO = 1.3;

  /** Pausa entre búsquedas en el mapa (OpenStreetMap pide una por segundo). */
  public int $pausa = 1100000;

  private bool $primeraBusqueda = true;

  public function __construct(private Geocodificador $geocodificador)
  {
  }

  /**
   * @param Collection $pedidos pedidos de un chofer, con distribucion y su dirección cargadas
   * @param array $deposito [latitud, longitud]
   * @return array{orden: array<int,int>, km: float, tarde: array<string>, sinUbicar: array<string>}
   */
  public function sugerir(Collection $pedidos, array $deposito): array
  {
    $paradas = [];
    $sinUbicar = [];
    foreach ($pedidos as $pedido) {
      $posicion = $this->ubicarCliente($pedido->distribucion);
      if ($posicion) {
        $paradas[$pedido->id] = ['pos' => $posicion, 'ventanas' => $this->ventanas($pedido->distribucion), 'nombre' => $this->nombre($pedido)];
      } else {
        $sinUbicar[$pedido->id] = $this->nombre($pedido);
      }
    }

    $ruta = $this->mejorar($this->rutaInicial($paradas, $deposito), $paradas, $deposito);
    $resultado = $this->simular($ruta, $paradas, $deposito);

    // Los que no se pudieron ubicar van al final, en el orden que tenían
    $orden = [];
    foreach ([...$ruta, ...array_keys($sinUbicar)] as $i => $id) {
      $orden[$id] = $i + 1;
    }

    return ['orden' => $orden, 'km' => $resultado['km'], 'tarde' => $resultado['tarde'], 'sinUbicar' => array_values($sinUbicar)];
  }

  /** Posición del cliente; la busca en el mapa solo si no la tiene o si cambió la dirección. */
  public function ubicarCliente(?Distribucion $cliente): ?array
  {
    if (!$cliente) {
      return null;
    }
    [$direccion, $zona] = $this->direccion($cliente);
    if ($direccion === '') {
      return null;
    }
    $texto = "$direccion, $zona";
    if ($cliente->ubicacion_direccion === $texto) {
      return $cliente->latitud !== null ? [(float) $cliente->latitud, (float) $cliente->longitud] : null;
    }

    $ubicacion = $this->buscar($direccion, $zona);
    if (!$ubicacion && str_contains($zona, ',')) {
      $ubicacion = $this->buscar($direccion, trim(substr($zona, strpos($zona, ',') + 1)));  // sin el barrio
    }

    // Sin pasar por la auditoría: es un dato calculado, no una modificación de la ficha
    $datos = ['latitud' => $ubicacion['latitud'] ?? null, 'longitud' => $ubicacion['longitud'] ?? null, 'ubicacion_direccion' => $texto];
    DB::table('distribucions')->where('id', $cliente->id)->update($datos);
    $cliente->forceFill($datos);

    return $ubicacion ? [$ubicacion['latitud'], $ubicacion['longitud']] : null;
  }

  private function buscar(string $direccion, string $zona): ?array
  {
    if (!$this->primeraBusqueda) {
      usleep($this->pausa);
    }
    $this->primeraBusqueda = false;
    return $this->geocodificador->ubicar($direccion, $zona);
  }

  /**
   * "SCALABRINI ORTIZ, R. AV." 802 → ["Scalabrini Ortiz 802", "Villa Crespo, Ciudad Autónoma de Buenos Aires"].
   * Las calles están cargadas como "APELLIDO, NOMBRE, TÍTULO": se dan vuelta y se quitan las abreviaturas.
   */
  public function direccion(Distribucion $cliente): array
  {
    $partes = array_filter(array_map('trim', explode(',', (string) $cliente->auxCalles?->calle)));
    $partes = array_filter($partes, fn($p) => !in_array(mb_strtoupper(rtrim($p, '.')), ['AV', 'AVDA', 'CNEL', 'GRAL', 'DR', 'DRA', 'R. AV', 'PJE', 'TTE'], true));
    $partes = array_map(fn($p) => trim(preg_replace('/\b(AV|AVDA)\.?$/iu', '', $p)), $partes);
    $calle = mb_convert_case(implode(' ', array_reverse($partes)), MB_CASE_TITLE);
    $numero = trim((string) $cliente->dire_nro);
    if ($calle === '') {
      return ['', ''];
    }

    $localidad = trim((string) $cliente->auxlocalidad?->localidad);
    $municipio = trim((string) $cliente->auxmunicipio?->ciudadmunicipio);
    $esCapital = $localidad === '' || preg_match('/C\.?A\.?B\.?A|CAPITAL/i', "$localidad $municipio");
    $ciudad = $esCapital ? 'Ciudad Autónoma de Buenos Aires' : implode(', ', array_filter([$localidad, $municipio !== $localidad ? $municipio : null, 'Buenos Aires']));
    $zona = implode(', ', array_filter([trim((string) $cliente->auxbarrio?->nombrebarrio), $ciudad]));

    return [trim("$calle $numero"), $zona];
  }

  /** Horarios de entrega en minutos: mañana y tarde; sin horario = todo el día. */
  private function ventanas(?Distribucion $cliente): array
  {
    $minutos = fn($h) => $h ? (int) substr($h, 0, 2) * 60 + (int) substr($h, 3, 2) : null;
    $ventanas = [];
    foreach ([['desde', 'hasta'], ['desde1', 'hasta1']] as [$d, $h]) {
      if ($cliente?->$d || $cliente?->$h) {
        $ventanas[] = [$minutos($cliente->$d) ?? 0, $minutos($cliente->$h) ?? 24 * 60];
      }
    }
    return $ventanas ?: [[0, 24 * 60]];
  }

  private function km(array $a, array $b): float
  {
    [$la1, $lo1, $la2, $lo2] = array_map('deg2rad', [$a[0], $a[1], $b[0], $b[1]]);
    $h = sin(($la2 - $la1) / 2) ** 2 + cos($la1) * cos($la2) * sin(($lo2 - $lo1) / 2) ** 2;
    return 6371 * 2 * asin(sqrt($h)) * self::DESVIO;
  }

  /** Recorre la ruta: kilómetros, llegadas fuera de horario y un costo (lo tarde pesa mucho más que la espera y los km). */
  private function simular(array $ruta, array $paradas, array $deposito): array
  {
    $t = self::SALIDA;
    $pos = $deposito;
    $km = $espera = $demora = 0;
    $tarde = [];
    foreach ($ruta as $id) {
      $p = $paradas[$id];
      $d = $this->km($pos, $p['pos']);
      $km += $d;
      $t += $d / self::KMH * 60;
      $abiertas = array_filter($p['ventanas'], fn($v) => $t <= $v[1]);
      if ($abiertas) {
        $inicio = max($t, min(array_column($abiertas, 0)));
        $espera += $inicio - $t;
        $t = $inicio;
      } else {
        $demora += $t - max(array_column($p['ventanas'], 1));
        $tarde[] = $p['nombre'];
      }
      $t += self::PARADA;
      $pos = $p['pos'];
    }
    return ['costo' => $demora * 100 + $espera + $km / self::KMH * 60, 'km' => $km, 'tarde' => $tarde];
  }

  /** Primero el que cierra antes; a igual horario, el más cercano. */
  private function rutaInicial(array $paradas, array $deposito): array
  {
    $ruta = [];
    $pos = $deposito;
    $resto = $paradas;
    while ($resto) {
      uasort($resto, fn($a, $b) => [min(array_column($a['ventanas'], 1)), $this->km($pos, $a['pos'])]
        <=> [min(array_column($b['ventanas'], 1)), $this->km($pos, $b['pos'])]);
      $id = array_key_first($resto);
      $ruta[] = $id;
      $pos = $resto[$id]['pos'];
      unset($resto[$id]);
    }
    return $ruta;
  }

  /** Mejora local: prueba mover cada parada a otro lugar o invertir un tramo, mientras baje el costo. */
  private function mejorar(array $ruta, array $paradas, array $deposito): array
  {
    $mejor = $this->simular($ruta, $paradas, $deposito)['costo'];
    $n = count($ruta);
    do {
      $mejoro = false;
      for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
          if ($i === $j) {
            continue;
          }
          $movida = $ruta;
          [$parada] = array_splice($movida, $i, 1);
          array_splice($movida, $j, 0, [$parada]);
          [$a, $b] = [min($i, $j), max($i, $j)];
          $invertida = array_merge(array_slice($ruta, 0, $a), array_reverse(array_slice($ruta, $a, $b - $a + 1)), array_slice($ruta, $b + 1));
          foreach ([$movida, $invertida] as $candidata) {
            $costo = $this->simular($candidata, $paradas, $deposito)['costo'];
            if ($costo < $mejor - 1e-6) {
              [$ruta, $mejor, $mejoro] = [$candidata, $costo, true];
            }
          }
        }
      }
    } while ($mejoro);
    return $ruta;
  }

  private function nombre($pedido): string
  {
    return (string) ($pedido->distribucion?->nomfantasia ?: $pedido->distribucion?->razonsocial ?: "Pedido {$pedido->id}");
  }
}
