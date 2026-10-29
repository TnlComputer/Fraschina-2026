<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Ubica una dirección en el mapa con OpenStreetMap (Nominatim). Solo se envía la dirección y la localidad.
 * Si no la encuentra o el servicio no responde, devuelve null y la ubicación se carga a mano.
 */
class Geocodificador
{
  public function ubicar(string $direccion, string $localidad): ?array
  {
    try {
      $respuesta = Http::withHeaders(['User-Agent' => 'Fraschina/1.0 (' . config('app.url') . ')'])
        ->timeout(10)
        ->get('https://nominatim.openstreetmap.org/search', [
          'q' => trim("$direccion, $localidad", ', '),
          'format' => 'json',
          'limit' => 1,
          'countrycodes' => 'ar',
          'accept-language' => 'es',
        ]);
      $lugar = $respuesta->successful() ? ($respuesta->json()[0] ?? null) : null;
    } catch (\Throwable $e) {
      report($e);
      return null;
    }

    return $lugar ? ['latitud' => (float) $lugar['lat'], 'longitud' => (float) $lugar['lon']] : null;
  }
}
