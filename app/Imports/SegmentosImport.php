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
    protected $errorMostrado = false;
    protected $contadorRegistros = 0;

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
        $this->contadorRegistros++; //debe informar cantidad de registros importados exitosamente
        return new Segmento($data);
    } catch (\ErrorException $e) {
        Log::error('Error durante la importación: ' . $e);
        // Obtener información sobre el campo y el valor recibido
        $campo = $e->getMessage();
        $valorRecibido = $row[$campo] ?? 'No proporcionado';
        // Agregar un mensaje a la colección de errores
        $this->errores[] = "Error en el campo '$campo': valor recibido '$valorRecibido' no es válido.";
        // Retornar un valor nulo para omitir esta fila
        if (!$this->errorMostrado) { // Solo mostrar el error si no se ha mostrado antes
            flash('Error en el campo '. $campo . ': valor recibido ' . $valorRecibido . ' no es válido')->error();
            $this->errorMostrado = true; // Marcar que ya se ha mostrado un error
        }
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
    public function getContadorRegistros():int
    {
        return $this->contadorRegistros;
    }
    public function mostrarMensajeExito()
    {
        if ($this->contadorRegistros > 0 && !$errorMostrado) {
            flash ('Proceso eixtoso. Se importaron ' . $this->contadorRegistros . ' registros.' ) ->success();
        }
    }
}
