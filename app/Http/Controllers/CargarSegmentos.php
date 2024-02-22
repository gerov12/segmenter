<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\SegmentosImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Segmento;
use Maatwebsite\Excel\Concerns\Importable;

class CargarSegmentos extends Controller
{
    public function index()
    {
        $data=null;
        return view('segmentos.cargar',['data' => $data]);
    }

    public function procesar(Request $request)
    {
        // Verificar si el archivo se ha subido
        if (!$request->hasFile('tabla_segmentos')) {
            return redirect('/')->with('error', 'No se ha seleccionado ningún archivo para importar.');
        }

        // Obtener el archivo subido
        $archivo = $request->file('tabla_segmentos');

        // Definir la variable $contenido
        $contenido = $request->toArray();

        if (array_key_exists('tabla_segmentos', $contenido)) {
            $datosImportados = $contenido['tabla_segmentos'];
        } else {
            // Mostrar mensaje de error
            return redirect('/')->with('error', 'No se encontraron datos en la solicitud.');
        }

        // Guardar el archivo en el almacenamiento
        $nombreArchivo = $archivo->store('segmentos');

        // Insertar los datos en la base de datos
        CargarSegmentos::insertOrIgnore($nombreArchivo);

        return view('segmentos.cargar',['data' => null])->with('success', '¡Importación realizada con éxito!');
    }

    public function insertOrIgnore($nombreArchivo)
    {
        // Implementar la lógica para procesar el archivo utilizando el nombre del archivo
        Excel::import(new SegmentosImport, $nombreArchivo);
    }
}