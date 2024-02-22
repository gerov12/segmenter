<?php

namespace App\Imports;

use App\Model\Segmento;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\Importable;
use App\Notifications\ImportHasFailedNotification;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Illuminate\Support\Facades\Log;

class SegmentosImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithCustomCsvSettings
{
    use Importable;

    protected $errores = [];

    public function model(array $row)
    {
        try {
            // Map missing or incorrect fields to expected names
            $data = [
                'prov' => $row['prov'],
                'nom_prov' => $row['nom_prov'] ?? $row['nomprov'] ,
                'dpto' => $row['dpto'] ?? $row['depto'] ,
                'nom_dpto' => $row['nom_dpto'] ?? $row['nomdepto'] ,
                'codloc' => $row['codloc'] ?? null,
                'nom_loc' => $row['nom_loc'] ?? $row['nomloc'] ,
                'codent' => $row['codent'] ?? '1',
                'nom_ent' => $row['nom_ent'] ?? '1',
                'frac' => $row['frac'] ,
                'radio' => $row['radio'],
                'tipo' => $row['tipo'] ?? 'I',
                'seg' => $row['seg'] ,
                'vivs' => $row['vivs'] ?? $row['viviendas'] ?? '1',
            ];
            return new Segmento($data);
        } catch (\ErrorException $e) {
            dd ($e);
            Log::error('Error durante la importación: ' . $e);
            // Obtener información sobre el campo y el valor recibido
            $campo = $e->getMessage(); // Puede ser necesario un análisis más específico del mensaje de error
            $valorRecibido = 'texto'; //$row[$campo]'';
            // Agregar un mensaje a la colección de errores
            $this->errores[] = "Error en el campo '$campo': valor recibido '$valorRecibido' no es válido.";
            // Retornar un valor nulo para omitir esta fila
            flash('error en el '. $campo  )-> error();
            return null;
        }
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

    // Método para obtener los errores registrados
    public function getErrores(): array
    {
        return $this->errores;
    }
}
