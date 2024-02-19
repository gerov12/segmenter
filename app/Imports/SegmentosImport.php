<?php

namespace App\Imports;

use App\Model\Segmento;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
// use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\Importable;
use App\Notifications\ImportHasFailedNotification;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithProgressBar;


class SegmentosImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithCustomCsvSettings
{
    use Importable;
    public function model(array $row)
    {
        // No es necesario usar dd($row) aquí.
        return new Segmento([
            'prov' => $row['prov'],
            'nom_prov' => $row['nom_prov'] ?? $row['nomprov'],
            'dpto' => $row['dpto'] ?? $row['depto'],
            'nom_dpto' => $row['nom_dpto'] ?? $row['nomdepto'],
            'codloc' => $row['codloc'],
            'nom_loc' => $row['nom_loc'] ?? $row['nomloc'],
            'codent' => $row['codent'] ?? '1',
            'nom_ent' => $row['nom_ent'] ?? '1',
            'frac' => $row['frac'],
            'radio' => $row['radio'],
            'tipo' => $row['tipo'] ?? 'I',
            'seg' => $row['seg'],
            'vivs' => $row['vivs'] ?? $row['viviendas'] ?? '1',
        ]);
    }
    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8'
        ];
    }
}
