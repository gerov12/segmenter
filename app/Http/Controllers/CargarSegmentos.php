<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Imports\SegmentosImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Segmento;
use Maatwebsite\Excel\Concerns\Importable;
use App\Model\Archivo;
use Auth;
use Spatie\Permission\Models\Permission; 

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
    
        $codprovincia= $this->obtenerProvFile($archivo);
        // Verifica si el usuario tiene permisos para cargar segmentos acorde al filtro provincia
        if (!self::filtroProvincia($codprovincia)){
            flash('Ud no puede cargar segmentos de la prov: '. $codprovincia); 
            return back();
        }
    
        $oArchivoCargado = Archivo::cargar($archivo, Auth::user());
    
        //   dd($oArchivoCargado->nombre_original);
        $nombreArchivo = $oArchivoCargado->nombre;
        // Verificar el tipo MIME del archivo


        $tipoMIME = $oArchivoCargado->mime;
        $tiposMIMEValidos = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet',
        ];

        if (!in_array($tipoMIME, $tiposMIMEValidos)) {
            flash('Error: Solo se pueden cargar archivos de tipo Excel')->error()->important();
            return back();
        }
        //dd($oArchivoCargado);
       if($oArchivoCargado->wasRecentlyCreated){
        flash('el archivo es nuevo... se procesa')->important()->success();
        // Insertar los datos en la base de datos
        if (CargarSegmentos::insertOrIgnore($nombreArchivo)) {
          return view('segmentos.cargar',['data' => null])->with('success', '¡Importación realizada con éxito!');
      } else {
          return view('segmentos.cargar',['data' => null])->with('error', 'Hubo un error durante la importación.');
      }

       }else{
        flash('el archivo ya fue visto por aqui... NO se procesa')->error()->important();
        return view('segmentos.cargar',['data' => null])->with('error','El archivo ya fue visto por aqui... NO se procesa');
       }


    }

    private function filtroProvincia($codprovincia ){
        
        $filtro = Permission::where('name',$codprovincia)->first(); 
        
        return ($filtro && Auth::user()->hasPermissionTo($codprovincia, 'filters'));

    }

    private function obtenerProvFile($nombreArchivo){
           
            $archivo = Excel::toArray(new SegmentosImport, $nombreArchivo);

             $codprovincia = $archivo[0][1]['prov'];
             $nompr = $archivo[0][1]['nomprov'];
 
            flash('la  provincia del archivo es '. $codprovincia . ' - ' . $nompr);

            return $codprovincia;
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

