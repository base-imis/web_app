<?php

namespace App\Services\UtilityInfo;

use App\Models\UtilityInfo\Roadline;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class RoadlineService {

    protected $session;
    protected $instance;

    /**
     * Constructs a new LandfillSite object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/


    }

    /**
     * Get all list of resource.
     *
     *
     * @return array[]|Collection
     */
    public function getAllData($data)
    {
        $roadlineData = Roadline::select('*');

        return Datatables::of($roadlineData)
                ->filter(function ($query) use ($data) {
                if ($data['code']) {
                    $query->where('code', 'ILIKE', '%' .  $data['code'] . '%');
                }

                if ($data['hierarchy']) {
                    $query->whereRaw('LOWER(hierarchy) LIKE ? ', [trim(strtolower($data['hierarchy']))]);
                }

                if ($data['surface_type']) {
                    $query->whereRaw('LOWER(surface_type) LIKE ? ', [trim(strtolower($data['surface_type']))]);
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['roadlines.destroy', $model->code]]);

                if (Auth::user()->can('Edit Roadline')) {
                    $content .= '<a title="Edit" href="' . action("UtilityInfo\RoadlineController@edit", [$model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View Roadline')) {
                    $content .= '<a title="Detail" href="' . action("UtilityInfo\RoadlineController@show", [$model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View Roadline History')) {
                    $content .= '<a title="History" href="' . action("UtilityInfo\RoadlineController@history", [$model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete Roadline')) {
                    $content .= '<a href="#" title="Delete"  class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                if (Auth::user()->can('View Road On Map')) {
                    $content .= '<a title="Map" href="' . action("MapsController@index", ['layer' => 'roadlines_layer', 'field' => 'code', 'val' => $model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-map-marker"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
            })
            ->make(true);
    }
    /**
     * Store or update a newly created resource in storage.
     *
     * @param character $code
     * @param array $data
     * @return bool
     */
    public function storeOrUpdate($code = null,$data)
    {
        if(empty($code)){
            /*$roadlineTemp = DB::select("SELECT ST_AsText(geom) AS geom FROM roadline_temp");
            $geom = ($roadlineTemp[0]->geom);*/

            $maxcode = Roadline::withTrashed()->max('code');
            $maxcode = str_replace('R', '', $maxcode);
            $roadline = new Roadline();
            $roadline->code = 'R' . sprintf('%04d', $maxcode + 1);
            $roadline->name = $data['name'] ? $data['name'] : null;
            $roadline->hierarchy = $data['hierarchy'] ? $data['hierarchy'] : null;
            $roadline->surface_type = $data['surface_type'] ? $data['surface_type'] : null;
            $roadline->length = $data['length'] ? $data['length'] : null;
            $roadline->right_of_way = $data['right_of_way'] ? $data['right_of_way'] : null;
            $roadline->carrying_width = $data['carrying_width'] ? $data['carrying_width'] : null;
            $roadline->geom = $data['geom'] ? DB::raw("ST_Multi(ST_GeomFromText('" . $data['geom'] . "', 4326))") : null;
            $roadline->save();
        }
        else{
            $roadline = Roadline::find($code);
            $roadline->name = $data['name'] ? $data['name'] : null;
            $roadline->hierarchy = $data['hierarchy'] ? $data['hierarchy'] : null;
            $roadline->surface_type = $data['surface_type'] ? $data['surface_type'] : null;
            $roadline->length = $data['length'] ? $data['length'] : null;
            $roadline->right_of_way = $data['right_of_way'] ? $data['right_of_way'] : null;
            $roadline->carrying_width = $data['carrying_width'] ? $data['carrying_width'] : null;
            $roadline->save();
            /*$containment = Containment::select('*')->where('code', $id)->first();
            if ($containment) {
                $containment->roadnm = $request->roadnam ? $request->roadnam : null;
                $containment->width = $request->width ? $request->width : null;
                $containment->rdcarwdth = $request->rdcarwdth ? $request->rdcarwdth : null;
                $containment->save();
            }*/
        }
    }

    /**
     * Download a listing of the specified resource from storage.
     *
     * @param array $data
     * @return null
     */
    public function download($data)
    {
        $searchData = $data['searchData'] ? $data['searchData'] : null;

        $code = $data['code'] ? $data['code'] : null;
        $surface_type = $data['surface_type'] ? $data['surface_type'] : null;
        $hierarchy = $data['hierarchy'] ? $data['hierarchy'] : null;

        $columns = ['Code', 'Road Name', 'Hierarchy', 'Right of Way (m)' , 'Carrying Width (m)', 'Surface Type', 'Road Length (m)'];

        $query = Roadline::select('code', 'name', 'hierarchy', 'right_of_way', 'carrying_width', 'surface_type', 'length')
            ->whereNull('deleted_at');


        if (!empty($code)) {
            $query->where('code', $code);
        }


        if (!empty($hierarchy)) {
            $query->whereRaw('LOWER(hierarchy) LIKE ? ', [trim(strtolower($data['hierarchy']))]);

        }

        if (!empty($surface_type)) {
            $query->whereRaw('LOWER(surface_type) LIKE ? ', [trim(strtolower($data['surface_type']))]);

        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);

        $writer->openToBrowser('Road Network.CSV')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($roadlines) use ($writer) {
            $writer->addRows($roadlines->toArray());

        });

        $writer->close();

    }

}
