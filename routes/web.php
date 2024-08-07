<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/tetete', function () {
    return view('test3');
});

Route::get('/tete', function () {
    return view('test2');
});

Route::get('/testeando', function () {
    return view('test');
});

Route::get('/gracias', function () {
    return view('goodbye');
});

Route::get('/colaboradores', function () {
    return view('user.list',['users'=>App\User::all()]);
});

Auth::routes();

/**
* Estructura mas ordenada de aplicacion, layout...
*/
Route::get('/home', function()
{
    return View::make('pages.home');
});
Route::get('/about', function()
{
    return View::make('pages.about');
});
Route::get('projects', function()
{
    return View::make('pages.projects');
});
Route::get('/contact', function()
{
    return View::make('pages.contact');
});
Route::get('/serverinfo', function()
{
    return View::make('pages.serverinfo');
});

Route::get('/guia', function()
{
    return View::make('segmentacion.guia');
});
Route::get('/sala', 'SalaController@index')->name('sala');

Route::get('/setup', 'SetupController@index')->name('setup');
Route::get('/setup/test', 'SetupController@testFlash')->name('setup.test');
Route::get('/setup/{esquema}', 'SetupController@permisos')->name('setup.permisos');
Route::get('/setup/fixSRID/{esquema}/{srid}',
'SetupController@setSRIDSchema')->name('setup.srid.topologia');
Route::get('/setup/topo/pais',
'SetupController@cargarTopologiasPais')->name('setup.topologia.pais');
Route::get('/setup/topo/{esquema}',
'SetupController@cargarTopologia')->name('setup.topologia');
Route::get('/setup/topo/{esquema}/{tolerancia}',
'SetupController@cargarTopologia')->name('setup.topologia');
Route::get('/setup/topo_drop/{esquema}',
'SetupController@dropTopologia')->name('setup.drop.topologia');
Route::get('/setup/index/{esquema}',
'SetupController@addIndexListado')->name('setup.index');
Route::get('/setup/index/id/{tabla}',
'SetupController@addIndexId')->name('setup.indexId');
Route::get('/setup/geo/{esquema}',
'SetupController@georeferenciarEsquema')->name('setup.geo');
Route::get('/setup/geo/{esquema}/{n}',
'SetupController@georeferenciarEsquema')->name('setup.geo.force');
Route::get('/setup/geo/{esquema}/{n}/{frac}',
'SetupController@georeferenciarEsquema')->name('setup.geo.force.frac');
Route::get('/setup/geo/{esquema}/{n}/{frac}/{radio}',
'SetupController@georeferenciarEsquema')->name('setup.geo.force.frac.radio');
Route::get('/setup/geoseg/{esquema}',
'SetupController@georeferenciarSegmentacionEsquema')->name('setup.geoseg');
Route::get('/setup/segmenta/{esquema}',
'SetupController@segmentarEsquema')->name('setup.segmenta');
Route::get('/setup/limpia/{esquema}',
'SetupController@limpiarEsquema')->name('setup.limpia');
Route::get('/setup/muestrea/{esquema}',
'SetupController@muestreaEsquema')->name('setup.muestrea');
Route::get('/setup/limpiar/Temporales',
'SetupController@limpiaEsquemasTemporales')->name('setup.limpia.temporales');
Route::get('/setup/junta/{esquema}',
'SetupController@juntarSegmentos')->name('setup.junta');
Route::get('/setup/junta/{esquema}/{frac}/{radio}',
'SetupController@juntarSegmentos')->name('setup.junta.frac.radio');
Route::get('/setup/index/{esquema}/{tabla}/{cols}',
'SetupController@createIndex')->name('setup.create.index');
Route::get('/setup/grupogeoestadistica/{usuario}',
'SetupController@grupoGeoestadistica')->name('setup.grupogeo');
Route::get('/setup/grupogeoestadistica/tabla/{tabla}',
'SetupController@grupoGeoestadisticaTabla')->name('setup.grupogeo.tabla');
Route::get('/setup/duplicadosLSV/{esquema}',
'SetupController@limpiaListado')->name('setup.limpialistado');
Route::get('/setup/updateTipoViv/{esquema}',
'SetupController@tipoVivdeDescripcion')->name('setup.tipovivdescripcion');
Route::get('/setup/update/LS',
'SetupController@juntaListadosSegmentados')->name('setup.junta.listados');
Route::get('/setup/update/R3',
'SetupController@juntaR3')->name('setup.juntaR3');
Route::get('/setup/update/Manzanas',
'SetupController@juntaManzanas')->name('setup.manzanas');
Route::get('/setup/update/Vias',
'SetupController@juntaVias')->name('setup.juntaVias');
Route::get('/setup/update/Localidades',
'SetupController@juntaLocalidades')->name('setup.juntaLocalidades');
Route::get('/setup/update/localidad_srid',
'SetupController@cargaSrids')->name('setup.cargaSrids');
Route::get('/setup/update/corrige_localidad_srid',
'SetupController@corrigeSrids')->name('setup.corrigeSrids');
Route::get('/setup/update/Cuadras',
'SetupController@juntaCuadras')->name('setup.cuadras');
Route::get('/setup/update/RadiosDeListados',
'SetupController@radiosDeListados')->name('setup.radiosDeListados');
Route::get('/setup/update/RadiosDeArcs',
'SetupController@radiosDeArcs')->name('setup.radiosDeArcs');


Route::get('/setup/adyacencias/{esquema}',
'SetupController@generarAdyacenciasEsquema')->name('setup.adyacencias');
Route::get('/setup/juntaMenores/{esquema}/{frac}/{radio}/{n}',
'SetupController@juntarSegmentosMenores')->name('setup.junta_menores');

Route::get('/inicio', 'HomeController@index')->name('inicio');
Route::resource('/listado', 'ListadoController',['only' => [
   'index', 'show', 'save'
]]);

Route::post('/domicilio/guardar/','DomicilioController@save');
/**
 * Segmenter
 */
Route::get('/segmentador', 'SegmenterController@index')->name('segmentador');
Route::post('/segmentador/guardar', 'SegmenterController@store');

Route::get('/', function () {
    flash(' Bienvenides !')->success();
    return view('welcome');
});

Route::post('/import', ['as'=>'import', 'uses'=>'Controller@import']);

Route::get('csv_file', 'CsvFile@index');

Route::get('csv_file/export', 'CsvFile@csv_export')->name('export');

Route::post('csv_file/import', 'CsvFile@csv_import')->name('import');

Route::get('search_provincia', 'AutoCompleteProvinciaController@index');
Route::get('autocomplete_provincia', 'AutoCompleteProvinciaController@search');
Route::get('provincia','ProvinciaController@index');

/**
 * Segmentos
 */
Route::resource('/segmentos', 'SegmentoController',['only' => [
  'index', 'show', 'save',
]]);
Route::get('segs-list', 'SegmentoController@segsList');
Route::get('segs','SegmentoController@index');

Route::get('segmento/{segmento}','SegmentoController@show');
Route::post('segmento/{segmento}','SegmentoController@show');


/**
* Segmentos con viviendas
*/
Route::get('cargarSegmentos', 'CargarSegmentos@index');
Route::post('cargarSegmentos', 'CargarSegmentos@procesar');

// ---------- PERFIL ----------
Route::middleware(['auth'])->group(function () {
    Route::get('perfil', 'UserController@mostrarPerfil')->name('perfil');
    Route::post('perfil/edit-username', 'UserController@editarUsername')->name('editarUsername');
    Route::post('perfil/edit-email', 'UserController@editarEmail')->name('editarEmail');
    Route::post('perfil/edit-password', 'UserController@editarContraseña')->name('editarContraseña');
    Route::post('perfil/edit-profile-pic', 'UserController@editarFoto')->name('editarFoto');
});

// ---------- USUARIOS ----------
Route::get('users', 'UserController@listarUsuarios')->name('admin.listarUsuarios');
Route::middleware(['auth'])->group(function () {
    Route::get('users/{user}/roles', 'UserController@editarRolUsuario')->name('admin.editarRolUsuario');
    Route::get('users/{user}/permission', 'UserController@editarPermisoUsuario')->name('admin.editarPermisoUsuario');
    Route::get('users/{user}/filter', 'UserController@editarFiltroUsuario')->name('admin.editarFiltroUsuario');
});

// ---------- EMAIL ----------
use Illuminate\Foundation\Auth\EmailVerificationRequest;
Route::get('/email/verify', function () {
    return redirect('perfil');
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('perfil')->with('message', 'Se ha verificado el email correctamente!');
})->middleware(['auth','signed'])->name('verification.verify');
use Illuminate\Http\Request;
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Se ha enviado un mail de verificación!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ---------- FILTROS ----------
Route::middleware(['auth'])->group(function () {
    Route::get('filtros', 'FilterController@listarFiltros')->name('admin.listarFiltros');
    Route::get('filtros/{filter}/rename', 'FilterController@renombrarFiltro')->name('admin.renombrarFiltro');
    Route::get('filtros/new', 'FilterController@crearFiltro')->name('admin.crearFiltro');
    Route::get('filtros/provs/edit', 'FilterController@editarFiltrosProvs')->name('admin.editarFiltrosProvs');
    Route::get('filtros/provs', 'FilterController@listarFiltrosProvs')->name('admin.listarFiltrosProvs');
    Route::delete('filtros/{filter}', 'FilterController@eliminarFiltro')->name('admin.eliminarFiltro');
});

// ---------- ROLES ----------
Route::middleware(['auth'])->group(function () {
    Route::get('roles', 'RoleController@listarRoles')->name('admin.listarRoles');
    Route::get('roles/{role}/edit', 'RoleController@editarRol')->name('admin.editarRol');
    Route::get('roles/new', 'RoleController@crearRol')->name('admin.crearRol');
    Route::get('roles/{role}/detail', 'RoleController@detallesRol')->name('admin.detallesRol');
    Route::delete('roles/{role}', 'RoleController@eliminarRol')->name('admin.eliminarRol');
});

// ---------- COMPARACIONES BD-GEOSERVICIOS ---------- //
Route::middleware(['auth'])->group(function () {
    Route::get('compare/menu/', 'CompareController@verMenu')->name('compare.menu');
    Route::get('compare/informes/', 'CompareController@listarInformes')->name('compare.informes');
    Route::get('compare/informes/{informe}', 'CompareController@verInforme')->name('compare.verInforme');
    Route::get('compare/informes/rerun/{informe}', 'CompareController@repetirInforme')->name('compare.repetirInforme');
    Route::get('compare/geoservicios/', 'CompareController@listarGeoservicios')->name('compare.geoservicios');
    Route::post('compare/geoservicios/store', 'GeoservicioController@store')->name('compare.storeGeoservicio');
    Route::post('compare/geoservicios/initialize', 'CompareController@inicializarGeoservicio')->name('compare.initGeoservicio');
    Route::post('compare/geoservicios/store-and-connect', 'GeoservicioController@storeAndConnect')->name('compare.storeGeoservicioAndConnect');
    Route::post('compare/geoservicios/delete', 'GeoservicioController@delete')->name('compare.deleteGeoservicio');
    Route::get('compare/capas/', 'CompareController@listarCapas')->name('compare.capas');
    Route::post('compare/atributos/', 'CompareController@listarAtributos')->name('compare.atributos');
    Route::post('compare/validar/{capa}', 'CompareController@validar')->name('compare.validar');
    Route::post('compare/geom_import', 'CompareController@importarGeometria')->name('compare.importarGeom');
    Route::post('compare/store_informe', 'CompareController@storeInforme')->name('compare.storeInforme');
    Route::post('compare/store_resultados', 'CompareController@storeResultados')->name('compare.storeResultados');
});

// ---------- PROVINCIAS --------
Route::get('provs-list', 'ProvinciaController@provsList');
Route::get('provs','ProvinciaController@index');
Route::get('prov/{provincia}','ProvinciaController@show');
Route::get('provincia/{provincia}','ProvinciaController@show');
Route::post('prov/{provincia}','ProvinciaController@show_post');

Route::delete('provincia/{provincia}','ProvinciaController@destroy')->name('provincia.delete');

// ---------- DEPARTAMENTOS --------

Route::get('prov/deptos/{provincia?}','DepartamentoController@index');
Route::get('prov/list/{provincia}','DepartamentoController@list');
Route::get('depto/{departamento}','DepartamentoController@show');
Route::post('depto/{departamento}','DepartamentoController@show_post');

// ---------- LOCALIDADES --------
Route::get('locas-list', 'LocalidadController@locasList');
Route::post('locas-list', 'LocalidadController@locasList');
Route::get('localidades','LocalidadController@list');
Route::get('localidades_json','LocalidadController@index');
Route::get('localidad/{localidad}','LocalidadController@show');
Route::get('localidad/codigo/{codigo}','LocalidadController@show_codigo');
Route::post('localidad/{localidad}','LocalidadController@segmenta_post');
Route::post('localidad-segmenta/{localidad}','LocalidadController@segmenta_post');
Route::get('localidad-segmenta/{localidad}','LocalidadController@segmenta_post');
Route::post('localidad-segmenta-run/{localidad}','LocalidadController@run_segmentar');
//Route::post('localidad/{localidad}','LocalidadController@show_post');
// PxSeg Localidad
Route::get('localidad/{localidad}/pxseg','LocalidadController@ver_pxseg')->name('localidad-ver-segmentacion-pxseg');
Route::get('localidad/{localidad}/segmentacion','LocalidadController@ver_segmentacion')->name('localidad-ver-segmentacion');
Route::get('localidad/{localidad}/segmentacion-lados','LocalidadController@ver_segmentacion_lados')->name('localidad-ver-segmentacion-lados');
Route::get('localidad/{localidad}/grafico','LocalidadController@ver_segmentacion_grafico')->name('localidad-ver-segmentacion-grafico');
Route::post('localidad/{localidad}/grafico','LocalidadController@ver_segmentacion_grafico_resumen')->name('localidad-ver-segmentacion-grafico');

// ---------- AGLOMERADOS --------
Route::get('aglos-list', 'AglomeradoController@aglosList');
Route::post('aglos-list', 'AglomeradoController@aglosList');
Route::get('aglos','AglomeradoController@index');
Route::get('aglo/{aglomerado}','AglomeradoController@show')->name('aglo-ver');
Route::post('aglo/{aglomerado}','AglomeradoController@show_post');
Route::post('aglo-segmenta/{aglomerado}','AglomeradoController@segmenta_post');
Route::get('aglo-segmenta/{aglomerado}','AglomeradoController@segmenta_post');
Route::post('aglo-segmenta-run/{aglomerado}','AglomeradoController@run_segmentar');

// ---------- ENTIDADES ----------
Route::get('entidades', 'EntidadController@index')->name('entidades');
Route::get('entidad/{entidad}','EntidadController@show');
Route::get('ents-list', 'EntidadController@entsList');
Route::post('entidad/{entidad}','EntidadController@show');
Route::get('ents','EntidadController@index');

Route::middleware(['auth'])->group(function () {
  Route::get('entidades/cargar', 'EntidadController@cargar')->name('entidades.cargar');
  Route::post('entidades/cargar', 'EntidadController@store');
});

// ---------- OPERATIVOS ----------
Route::get('operativos', 'OperativoController@index')->name('operativos');
Route::get('operativo/{operativo}','OperativoController@show');
Route::get('operativos-list', 'OperativoController@operativosList');
Route::post('operativo/{operativo}','OperativoController@show');
Route::get('operativo/seleccionar/{operativo}','OperativoController@seleccionar')->name('operativo.seleccionar');


// --------- SEGMENTACION X AGLOMERADO ---------
Route::get('aglo/{aglomerado}/pxseg','AglomeradoController@ver_pxseg')->name('ver-segmentacion-pxseg');
Route::get('ver-segmentacion/{aglomerado}','AglomeradoController@ver_segmentacion')->name('ver-segmentacion');
Route::get('ver-segmentacion-lados/{aglomerado}','AglomeradoController@ver_segmentacion_lados')->name('ver-segmentacion-lados');
Route::get('ver-segmentacion/grafico/{aglomerado}','AglomeradoController@ver_segmentacion_grafico')->name('ver-segmentacion-grafico');
Route::post('ver-segmentacion-grafico/{aglomerado}','AglomeradoController@ver_segmentacion_grafico')->name('ver-segmentacion-grafico');

Route::get('ver-segmentacion/grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_grafico_resumen')->name('ver-segmentacion-grafico-resumen');
Route::post('ver-segmentacion-grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_grafico_resumen')->name('ver-segmentacion-grafico-resumen');
//ver_segmentacion_lados_grafico_resumen
Route::get('ver-segmentacion-lados/grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_lados_grafico_resumen')->name('ver-segmentacion-lados-grafico-resumen');
Route::post('ver-segmentacion-lados-grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_lados_grafico_resumen')->name('ver-segmentacion-lados-grafico-resumen');

// ---------- RADIOS Localidad Depto --------
// Para CABA
Route::get('radios/{localidad}/{departamento}','RadiosController@show');
Route::get('radio/{radio}','RadioController@show');
Route::get('radio/codigo/{codigo}','RadioController@show_codigo');

// ---------- GRAFOS AGLOMERADOS --------
Route::get('grafo/{aglomerado}','SegmentacionController@index')->name('index');
Route::get('grafo/{aglomerado}/{radio}/','SegmentacionController@ver_grafo_legacy')->name('ver-grafo-redirect');
Route::get('radio/{localidad}/{radio}/','SegmentacionController@ver_grafo')->name('ver-grafo');

// ---------- ARCHIVOS --------
Route::middleware(['auth'])->group(function () {
    Route::post('archivos','ArchivoController@index');
    Route::get('archivos','ArchivoController@index')->name('archivos');
    Route::get('archivo/{archivo}','ArchivoController@show');
    Route::post('archivo/{archivo}','ArchivoController@show');
    Route::delete('archivo/{archivo}','ArchivoController@destroy');
    Route::get('archivo/{archivo}/eliminar','ArchivoController@destroy');
    Route::put('archivo/{archivo}/detach','ArchivoController@detach');
    Route::get('archivo/{archivo}/descargar','ArchivoController@descargar');
    Route::get('archivo/{archivo}/procesar','ArchivoController@procesar');
    Route::get('archivo/{archivo}/pasar_data','ArchivoController@pasarData');

    Route::post('archivos/limpiar/{archivo_id?}','ArchivoController@eliminar_repetidos')->name('limpiar_archivos'); //el parametro es opcional
    Route::post('archivos/limpiar/{archivo_id}/copias','ArchivoController@eliminar_repetidos')->name('limpiar_copias'); //eliminar las copias de un archivo
    Route::post('archivos/recalcular_cs/{archivo_id?}','ArchivoController@recalcular_checksums')->name('recalcular_checksums'); //el parametro es opcional
    Route::post('archivos/sincronizar_cs/{archivo_id?}','ArchivoController@sincronizar_checksums')->name('sincronizar_checksums'); //el parametro es opcional
    Route::post('archivos/contar_autorizados','ArchivoController@update_owned_count')->name('contar_owned');
    Route::get('archivos/contar','ArchivoController@updateCounts')->name('contar_archivos');
    Route::get('archivos/repetidos','ArchivoController@listar_repetidos')->name('archivos_repetidos');
    Route::get('archivos/checksums_obsoletos','ArchivoController@listar_checksums_obsoletos')->name('checksums_obsoletos');
    Route::get('archivos/checksums_erroneos','ArchivoController@listar_checksums_erroneos')->name('checksums_erroneos');
    Route::get('archivos/checksums_no_calculados','ArchivoController@listar_checksums_no_calculados')->name('checksums_no_calculados');

    Route::get('archivo/{archivo}/copias','ArchivoController@getCopias');
    Route::get('archivo/{archivo}/original','ArchivoController@getOriginal');
});

// ---------- TABLERO ---------

Route::get('informe/prov','TableroController@GraficoProvincias');
Route::post('informe/prov','TableroController@GraficoProvincias');
Route::get('informe/avances','TableroController@GraficoAvances');
Route::post('informe/avances','TableroController@GraficoAvances');
Route::get('informe/avance','TableroController@GraficoAvance');
Route::post('informe/avance','TableroController@GraficoAvance');
Route::get('informe/bd','TableroController@EstadisticasBD')->name('database.statistics');

//Route::get('mail', 'MailCsvController@index');

/* Logout via GET */
Route::get('/logout', 'Auth\LoginController@logout');

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('Admin')->name('admin/')->group(static function() {
        Route::prefix('admin-users')->name('admin-users/')->group(static function() {
            Route::get('/',                                             'AdminUsersController@index')->name('index');
            Route::get('/create',                                       'AdminUsersController@create')->name('create');
            Route::post('/',                                            'AdminUsersController@store')->name('store');
            Route::get('/{adminUser}/edit',                             'AdminUsersController@edit')->name('edit');
            Route::post('/{adminUser}',                                 'AdminUsersController@update')->name('update');
            Route::delete('/{adminUser}',                               'AdminUsersController@destroy')->name('destroy');
            Route::get('/{adminUser}/resend-activation',                'AdminUsersController@resendActivationEmail')->name('resendActivationEmail');
        });
    });
});

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('Admin')->name('admin/')->group(static function() {
        Route::get('/profile',                                      'ProfileController@editProfile')->name('edit-profile');
        Route::post('/profile',                                     'ProfileController@updateProfile')->name('update-profile');
        Route::get('/password',                                     'ProfileController@editPassword')->name('edit-password');
        Route::post('/password',                                    'ProfileController@updatePassword')->name('update-password');
    });
});
Auth::routes();

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

Route::get('/home', 'HomeController@index')->name('home');


/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->name('admin/')->group(static function() {
        Route::prefix('provincia')->name('provincia/')->group(static function() {
            Route::get('/',                                             'ProvinciaController@index')->name('index');
            Route::get('/create',                                       'ProvinciaController@create')->name('create');
            Route::post('/',                                            'ProvinciaController@store')->name('store');
            Route::get('/{provincium}/edit',                            'ProvinciaController@edit')->name('edit');
            Route::post('/bulk-destroy',                                'ProvinciaController@bulkDestroy')->name('bulk-destroy');
            Route::post('/{provincium}',                                'ProvinciaController@update')->name('update');
            Route::delete('/{provincium}',                              'ProvinciaController@destroy')->name('destroy');
        });
    });
});

/* Auto-generated admin routes */
/*
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->name('admin/')->group(static function() {
        Route::prefix('tipo-de-radios')->name('tipo-de-radios/')->group(static function() {
            Route::get('/',                                             'TipoDeRadioController@index')->name('index');
            Route::get('/create',                                       'TipoDeRadioController@create')->name('create');
            Route::post('/',                                            'TipoDeRadioController@store')->name('store');
            Route::get('/{tipoDeRadio}/edit',                           'TipoDeRadioController@edit')->name('edit');
            Route::post('/bulk-destroy',                                'TipoDeRadioController@bulkDestroy')->name('bulk-destroy');
            Route::post('/{tipoDeRadio}',                               'TipoDeRadioController@update')->name('update');
            Route::delete('/{tipoDeRadio}',                             'TipoDeRadioController@destroy')->name('destroy');
        });
    });
});
*/
/* Auto-generated admin routes */

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->name('admin/')->group(static function() {
        Route::prefix('departamentos')->name('departamentos/')->group(static function() {
            Route::get('/',                                             'DepartamentoController@index')->name('index');
            Route::get('/create',                                       'DepartamentoController@create')->name('create');
            Route::post('/',                                            'DepartamentoController@store')->name('store');
            Route::get('/{departamento}/edit',                          'DepartamentoController@edit')->name('edit');
            Route::post('/bulk-destroy',                                'DepartamentoController@bulkDestroy')->name('bulk-destroy');
            Route::post('/{departamento}',                              'DepartamentoController@update')->name('update');
            Route::delete('/{departamento}',                            'DepartamentoController@destroy')->name('destroy');
        });
    });
});

/* Auto-generated admin routes */

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->name('admin/')->group(static function() {
        Route::prefix('localidades')->name('localidads/')->group(static function() {
            Route::get('/',                                             'LocalidadController@index')->name('index');
            Route::get('/create',                                       'LocalidadController@create')->name('create');
            Route::post('/',                                            'LocalidadController@store')->name('store');
            Route::get('/{localidad}/edit',                             'LocalidadController@edit')->name('edit');
            Route::post('/bulk-destroy',                                'LocalidadController@bulkDestroy')->name('bulk-destroy');
            Route::post('/{localidad}',                                 'LocalidadController@update')->name('update');
            Route::delete('/{localidad}',                               'LocalidadController@destroy')->name('destroy');
        });
    });
});
