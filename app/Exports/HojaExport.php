<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Hoja genérica: título, encabezados y filas ya armadas.
 */
class HojaExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
  public function __construct(
    private string $titulo,
    private array $encabezados,
    private Collection $filas,
  ) {}

  public function collection(): Collection
  {
    return $this->filas;
  }

  public function headings(): array
  {
    return $this->encabezados;
  }

  public function title(): string
  {
    return mb_substr($this->titulo, 0, 31);
  }
}
