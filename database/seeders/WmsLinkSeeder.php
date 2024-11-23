<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\WmsLink;

class WmsLinkSeeder extends Seeder
{
    /**
     * Run the database seeds
     *
     * @return void
     */
    public function run()
    {
        WmsLink::truncate();

        $wmslayers = [
            [
                'name' => 'wards',
                'link' => 'wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&FORMAT=image/png&TRANSPARENT=true&LAYERS='.env('GEOSERVER_WORKSPACE').'%3Awards_layer&TILED=true&STYLES='.env('GEOSERVER_WORKSPACE').'%3Awards_layer_none&CRS=EPSG%3A3857&FORMAT_OPTIONS=dpi%3A101&WIDTH={width}&HEIGHT={height}&BBOX={minX},{minY},{maxX},{maxY}',
            ],
            [
                'name' => 'roads',
                'link' => 'wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&FORMAT=image%2Fpng&TRANSPARENT=true&LAYERS='.env('GEOSERVER_WORKSPACE').'%3Aroadlines_layer&TILED=true&STYLES='.env('GEOSERVER_WORKSPACE').'%3Aroadlines_layer_width&CRS=EPSG%3A3857&FORMAT_OPTIONS=dpi%3A101&WIDTH={width}&HEIGHT={height}&BBOX={minX},{minY},{maxX},{maxY}',
            ],
            [
                'name' => 'buildings',
                'link' => 'wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&FORMAT=image/png&TRANSPARENT=true&LAYERS='.env('GEOSERVER_WORKSPACE').'%3Abuildings_layer&TILED=true&STYLES='.env('GEOSERVER_WORKSPACE').'%3Abuildings_layer_none&CRS=EPSG%3A3857&FORMAT_OPTIONS=dpi%3A101&WIDTH={width}&HEIGHT={height}&BBOX={minX},{minY},{maxX},{maxY}',
            ],
            [
                'name' => 'containments',
                'link' => 'wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&FORMAT=image%2Fpng&TRANSPARENT=true&LAYERS='.env('GEOSERVER_WORKSPACE').'%3Acontainments_layer&TILED=true&STYLES='.env('GEOSERVER_WORKSPACE').'%3Acontainments_layer_none&CRS=EPSG%3A3857&FORMAT_OPTIONS=dpi%3A101&WIDTH={width}&HEIGHT={height}&BBOX={minX},{minY},{maxX},{maxY}',
            ],
            [
                'name' => 'sewers',
                'link' => 'wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&FORMAT=image/png&TRANSPARENT=true&LAYERS='.env('GEOSERVER_WORKSPACE').'%3Asewerlines_layer&TILED=true&STYLES='.env('GEOSERVER_WORKSPACE').'%3Asewer_none&CRS=EPSG%3A3857&FORMAT_OPTIONS=dpi%3A101&WIDTH={width}&HEIGHT={height}&BBOX={minX},{minY},{maxX},{maxY}',
            ],   
        ];
            
        WmsLink::insert($wmslayers);
    }
}