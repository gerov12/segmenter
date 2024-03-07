<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\SegmentosImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Segmento;
use Maatwebsite\Excel\Concerns\Importable;
use App\Model\Archivo;
use Auth;

class CargarSegmentos extends Controller
{
    public function index()
    {
        $data=null;
        return view('segmentos.cargar',['data' => $data]);
    }

    public function procesar(Request $request)
    {
        //Procesar solo si está logueado y tiene permiso 
        if (!Auth::check()){
               flash('Debe estar logueado para realizar esta acción')->error() ->important();
               return  redirect('/');  
        }

        // Verificar si el archivo se ha subido
        if (!$request->hasFile('tabla_segmentos')) {
            return redirect('/')->with('error', 'No se ha seleccionado ningún archivo para importar.');
        }

        // Obtener el archivo subido
        $archivo = $request->file('tabla_segmentos');
        $oArchivoCargado = Archivo::cargar($archivo, Auth::user());


        // Verificar el tipo MIME del archivo
        $tipoMIME = $oArchivoCargado->mime;
            // flash para mostrar tipo mime (quitar luego)
            flash('tipo de archivo ' . $tipoMIME)->important();
        if ($tipoMIME != 'application/vnd.ms-excel' && $tipoMIME != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            flash('Error: Solo se pueden cargar archivos de tipo Excel - entendido?')->error()->important();
            return back();
        }
 
        // Guardar el archivo en el almacenamiento
        $nombreArchivo = $archivo->store('segmentos');

        // Insertar los datos en la base de datos
        if (CargarSegmentos::insertOrIgnore($nombreArchivo)) {
            return view('segmentos.cargar',['data' => null])->with('success', '¡Importación realizada con éxito!');
        } else {
            return view('segmentos.cargar',['data' => null])->with('error', 'Hubo un error durante la importación.');
        }

       // Definir la variable $contenido
       $contenido = $request->toArray();

       if (array_key_exists('tabla_segmentos', $contenido)) {
           $datosImportados = $contenido['tabla_segmentos'];
       } else {
           // Mostrar mensaje de error
           return back()->with('error', 'No se encontraron datos en la solicitud.');
       }
       
    }
    
    public function insertOrIgnore($nombreArchivo) {
    // Verificar la extensión del archivo
    //   $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
    //   if ($extension != 'xls' && $extension != 'xlsx') {
    //       flash('Error: Solo se pueden cargar archivos .xls o .xlsx')->error();
    //       return;
    //   }
    try {
        // Implementar la lógica para procesar el archivo utilizando el nombre del archivo
        Excel::import(new SegmentosImport, $nombreArchivo);
       // flash('Importación exitosa.')->success();
    } catch (\Exception $e) {
        // Manejar la excepción
        flash('Error durante la importación: ' . $e->getMessage())->error();
    }
}
}