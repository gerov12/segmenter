<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class Geoservicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', //null para los geoservicios temporales (conexión rápida)
        'nombre', 
        'descripcion', 
        'url'
    ];

    public function informes()
    {
        return $this->hasMany(Informe::class, 'geoservicio_id');
    }

    public function testConnection()
    {
        try {
            $response = Http::get($this->url, [
                'request' => 'GetCapabilities'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error en la solicitud HTTP: ' . $response->status());
            }
    
            // verifico que el contenido sea XML
            $contentType = $response->header('Content-Type');
            if (strpos($contentType, 'application/xml') === false && strpos($contentType, 'text/xml') === false) {
                throw new \Exception('La respuesta no es XML');
            }
            // intento cargar el contenido como xml
            libxml_use_internal_errors(true); //permite el manejo de errores internos de xml (necesario para $exceptionText)
            $xml = simplexml_load_string($response->body());
            if ($xml === false) {
                throw new \Exception('Error al parsear la respuesta XML');
            }

            // si la petición es incorrecta tendrá la sección ExceptionReport, sino tendra la sección WFS_Capabilities
            if ($xml->getName() == 'ExceptionReport') {
                $namespaces = $xml->getNamespaces(true);
                $ows = $xml->children($namespaces['ows']);
                $exceptionText = (string) $ows->Exception->ExceptionText;
                throw new \Exception('Petición invalida: '.$exceptionText);
            } else if ($xml->getName() == 'WFS_Capabilities') {
                return ["status" => true];
            }
            
        } catch (\Exception $e) {
            Log::error('Error al conectar con el geoservicio. ' . $e->getMessage());
            return ["status" => false, "message" => 'Error al conectar con el geoservicio. ' . $e->getMessage()];
        }
    }

    public function getCapas()
    {   
        $response = Http::get($this->url, [
            'request' => 'GetCapabilities',
            // 'section' => 'FeatureTypeList' //para algunos geoservicios no funciona el parametro section, se busca en toda la response y listo.
        ]);

        $xml = $response->body();

        // Parse XML
        $xmlObject = new \SimpleXMLElement($xml);

        // Convert SimpleXMLElement object to array
        $array = json_decode(json_encode($xmlObject), true);

        // Get FeatureTypeList section
        $featureTypeList = $array['FeatureTypeList']['FeatureType'];

        return $featureTypeList;
    }

    public function getCapa($capa)
    {
        $response = Http::get($this->url, [
            'request' => 'GetFeature',
            'outputFormat' => 'application/json',
            'typeName' => $capa
        ]);
        $result = $response->body();
        $datos = json_decode($result, true);
        return $datos;
    }

    public function getAtributos($capa)
    {
        $response = Http::get($this->url, [
            'request' => 'DescribeFeatureType',
            'outputFormat' => 'application/json',
            'typeNames' => $capa
        ]);
        $result = $response->body();
        $datos = json_decode($result, true);
        return $datos;
    }
}
