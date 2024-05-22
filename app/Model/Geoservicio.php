<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Geoservicio extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'url', 'tipo'];

    public function informes()
    {
        return $this->hasMany(Informe::class, 'geoservicio_id');
    }

    public function getCapas()
    {
        $response = Http::get($this->url . 'ows', [
            'service' => 'wfs',
            'version' => '2.0.0',
            'request' => 'GetCapabilities',
            'section' => 'FeatureTypeList'
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
        $url = $this->url . 'ows?service=wfs&version=2.0.0&request=GetFeature&outputFormat=application/json&typeName=' . $capa;
        $result = file_get_contents($url);
        $datos = json_decode($result, true);
        return $datos;
    }

    public function getAtributos($capa)
    {
        $url = $this->url . 'wfs?request=DescribeFeatureType&outputFormat=application/json&typeNames=' . $capa;
        $result = file_get_contents($url);
        $datos = json_decode($result, true);
        return $datos;
    }
}
