<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Geometria extends Model
{
    //
    protected $table='geometrias';
    protected $primaryKey = 'id';
    protected $fillable = ['poligono', 'punto', 'topogeometria'];


    /**
     * Relación con Entidad.
     *
     */

    public function entidad()
    {
        return $this->hasMany('App\Model\Entidad');
    }

      // return SVG Geometria poligono
    public function getSVG() {
      //    Utiliza tablas listado_geo, r3 junto a segmentacion y manzanas.
          $width = 600;
          $escalar = false;
          $perimeter = 2400;
          $height = 400;
          $stroke = 2;
          $extent = DB::select("SELECT box2d(st_collect(poligono)) box FROM
          ".$this->table."
          WHERE id='".$this->id."' ");
          $extent = $extent[0]->box;
          list($x0, $y0, $x1, $y1) = sscanf($extent,'BOX(%f %f,%f %f)');
          $Dx = $x1 - $x0;
          $Dy = $y1 - $y0;
          if (!isSet($height) and $width)
              $height = round($width*$Dy/$Dx);
          if ($height and !isSet($width))
              $width = round($height*$Dx/$Dy);
          if (!isSet($height) and !isSet($width)) {
              $width = round($perimeter*$Dx/2/($Dx + $Dy));
              $height=round($width*$Dy/$Dx);
          }
          $dx = $Dx/$width;
          $dy = $Dy/$height;
          $epsilon = min($dx, $dy)/15; // mínima resolución, ancho y alto de lo que representa un pixel
          if ($escalar) {
              $viewBox = "0 0 $width $height";
              $stroke = 2;
          } else {
              $viewBox = $this->viewBox($extent,$epsilon,$height,$width);
              $stroke = 2*$epsilon;
          }
/*          if (Schema::hasTable($this->esquema.'.manzanas')){
              $mzas = "
                  UNION
                  ( SELECT st_buffer(wkb_geometry,-5) geom, -1*mza::integer, 'mza' tipo
                      FROM ".$this->esquema.".manzanas
                      WHERE    prov||dpto||frac||radio='".$this->codigo."'
                  ) ";
              $mzas_labels = "
                  UNION (SELECT '<text x=\"'||st_x(st_centroid(wkb_geometry))||'\"
                  y=\"-'||st_y(st_centroid(wkb_geometry))||'\">'||mza||'</text>'
                  as svg ,20 as orden
                  FROM ".$this->esquema.".manzanas
                  WHERE    prov||dpto||frac||radio='".$this->codigo."' )";
          } else {
              Log::debug('No se encontro grafica de manzanas. ');$mzas='';$mzas_labels='';
          }
          */
/*
          if (Schema::hasTable($this->esquema.'.r3')) {
              $r3_seg = 'r3.seg::integer';
              $r3_join = "LEFT JOIN ".$this->esquema.".r3 ON s.segmento_id=r3.segmento_id";
          } else {Log::debug('No se encontro R3. ');
              $r3_seg = "'99'";
              $r3_join = '';
          }
*/
          // Consulta que arma SVG de Radio, con lo que encuentra en esquema viendo tablas: listado_geo, manzanas, r3
          /* Colores según javascript en grafo
           let clusterColors = ['#FF0', '#0FF', '#F0F', '#4139dd', '#d57dba', '#8dcaa4'
                  ,'#555','#CCC','#A00','#0A0','#00A','#F00','#0F0','#00F','#008','#800','#080'];
           */
          $svg = DB::select("
WITH shapes (geom, attribute, tipo) AS
  ( SELECT st_buffer(st_collect(poligono),1,'endcap=flat join=round') wkb_geometry, ".$this->id." as attribute, 'A'::text as tipo FROM
  ".$this->table."
  WHERE id='".$this->id."'
  ),
  paths (svg,orden) as (
   SELECT * FROM (
   (SELECT concat(
       '<path d= \"',
       ST_AsSVG(st_buffer(geom,3),0), '\" ',
       CASE WHEN attribute = 0 THEN 'stroke=\"gray\" stroke-width=\"2\"
       fill=\"gray\"'
              WHEN tipo='mza' THEN 'stroke=\"white\"
              stroke-width=\"1\" fill=\"#BBBBC5\"'
              WHEN attribute < 5 THEN 'stroke=\"gray\"
              stroke-width=\"".$stroke."\" fill=\"#' || attribute*20 || 'AAAA\"'
              WHEN attribute < 10 THEN 'stroke=\"none\"
       stroke-width=\"".$stroke."\" fill=\"#00' || (attribute-5)*20 || '00\"'
              WHEN attribute < 15 THEN 'stroke=\"none\"
       stroke-width=\"".$stroke."\" fill=\"#AA' || (attribute-10)*20 || '00\"'
              WHEN attribute = 80 THEN 'stroke=\"none\"
       stroke-width=\"".$stroke."\" fill=\"#00BB00\"'
              WHEN attribute = 81 THEN 'stroke=\"none\"
       stroke-width=\"".$stroke."\" fill=\"#0BA\"'
       ELSE
          'stroke=\"black\" stroke-width=\"".$stroke."\" fill=\"#22' ||
          attribute*10 || '88\"'
       END,
          ' entidad=\"',attribute,'\"',
          ' title=\"Entidad ',attribute,'\"',
          ' />') as svg,
          CASE WHEN tipo='A' then 1
          ELSE 10 END as orden
   FROM shapes
   ORDER BY attribute asc)
   ) foo order by orden asc
)
SELECT concat(
       '<svg id=\"geometria_".$this->codigo."_botonera\"xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 70 70\" height=\"80\" width=\"".$width."\">',
 '<circle style=\"opacity: 10%;\" class=\"compass\" cx=\"".(+30)."\" cy=\"".(30)."\" r=\"28\"></circle>
       <circle style=\"opacity: 20%;\" class=\"button\" cx=\"".(+30)."\" cy=\"".(36)."\"
       r=\"7\"
       onclick=\"zoom(0.9)\"/>
      <circle style=\"opacity: 20%;\" class=\"button\" cx=\"".(30)."\" cy=\"".(+24)."\"
r=\"7\"
onclick=\"zoom(1.1)\"/>
<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(0, 25)\" d=\"M".(+30)." ".(+5)." l6 10 a20 35 0 0 0 -12 0z\" />
<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(25, 0)\" d=\"M".(+5)." ".(+30)." l10 -6 a35 20 0 0 0 0 12z\" />
<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(0,-25)\" d=\"M".(+30)." ".(55)." l6 -10 a20 35 0 0,1 -12,0z\" />
<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(-25, 0)\" d=\"M".(+55)." ".(+30)." l-10 -6 a35 20 0 0 1 0 12z\" />
',
 '</svg>',
       '<svg id=\"geometria_".$this->codigo."\"xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"".$viewBox.
 "\" height=\"".$height."\" width=\"".$width."\">',
 ' <g id=\"matrix-group\" transform=\"matrix(1 0 0 1 0 0)\">',
 array_to_string(array_agg(svg),''),
 '</g></svg>'
    )
FROM paths;
");
          return $svg[0]->concat;
/*      } else {
          return "Por el momento no se puede previsualizar la geometria.";
      }
      */
  }

  private function viewBox($extent,$epsilon,$height,$width) {
      list ($x0, $y0, $x1, $y1) = sscanf ( $extent, 'BOX(%f %f,%f %f)' );
      $Dx = $x1 - $x0;
      $Dy = $y1 - $y0;
      $m_izq=.1*$Dx;
      $m_der=.1*$Dx;
      $m_arr=.1*$Dy;
      $m_aba=.1*$Dy;
      $viewBox = ($x0 - $m_izq) . " " . (- $y1 - $m_arr) . " " . ($Dx + $m_izq + $m_der) . " " . ($Dy + $m_arr + $m_aba);
      if (!$height and !$width)
          $height = 600;
      if (!$height)
          $height = $width*$Dy/$Dx;
      if (!$width)
           $width = $height*$Dx/$Dy;
      $epsilon = min($Dx/$width, $Dy/$height);
      return $viewBox;
      }

}
