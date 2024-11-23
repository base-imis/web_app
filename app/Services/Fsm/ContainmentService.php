<?php
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Services\Fsm;

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Auth;
use DataTables;
use DB;
use DOMDocument;
use DomXpath;
use App\Models\Fsm\Containment;
use App\Models\Fsm\Application;
use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\BuildContain;
use Illuminate\Http\Request;
use App\Services\BuildingInfo\BuildingStructureService;
use App\Models\BuildingInfo\SanitationSystemTechnology;

class ContainmentService
{

    public function fetchData($request)
    {
        $containmentData = Containment::select('*')->whereNull('deleted_at');

        return DataTables::of($containmentData)
            ->filter(function ($query) use ($request) {
                if ($request->containment_id) {
                    $query->where('id', 'ILIKE', '%' .  $request->containment_id . '%');
                }
                if ($request->containment_volume) {
                    $query->where('size', $request->containment_volume);
                }
                if ($request->type_id) {
                    $query->where('type_id', $request->type_id);
                }
                if (!is_null($request->containment_location)) {
                    $selectedOption = $request->containment_location;
                    $query->where('location', $selectedOption);
                }
                if ($request->emptying_status) {
                    $query->where('emptied_status', $request->emptying_status);
                }
                if ($request->roadcd) {

                    $query->where('road_code', 'ILIKE', '%' . $request->roadcd . '%');
                }
                if ($request->bin) {

                    $query->whereHas("buildings", function ($q) use ($request) {
                        $q->where('buildings.bin', 'ILIKE', '%' . $request->bin . '%');
                    });
                }
                if ($request->house_number) {

                    $query->whereHas("buildings", function ($q) use ($request) {
                        $q->where('buildings.house_number', 'ILIKE', '%' . $request->house_number . '%');
                    });
                }
                if ($request->emptying_status) {
                    $query->where('emptied_status',$request->emptying_status);
                }
                if ($request->septic_compliance) {

                    $query->where('septic_criteria', $request->septic_compliance);
                }
                if ($request->const_date) {
                    $query->where('construction_date', '>=', str_split(str_replace(' ', '', str_replace('-', '', $request->const_date)), 10)[0]);
                    $query->where('construction_date', '<=', str_split(str_replace(' ', '', str_replace('-', '', $request->const_date)), 10)[1]);
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['containments.destroy', $model->id]]);

                if (Auth::user()->can('List Containment Buildings')) {
                    $content .= '<a title=" View Building Connected to Containment" href="' . action("Fsm\ContainmentController@listBuildings", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa-solid fa-building"></i></a> ';
                }
                if (Auth::user()->can('Edit Containment')) {
                    $content .= '<a title="Edit" href="' . action("Fsm\ContainmentController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('View Containment')) {
                    $content .= '<a title="Detail" href="' . action("Fsm\ContainmentController@show", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View Containment')) {
                    $content .= '<a title="History" href="' . action("Fsm\ContainmentController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('View Containment')) {
                    $content .= '<a title="Type Change History" href="' . action("Fsm\ContainmentController@typeChangeHistory", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa-sharp fa-solid fa-file-pen"></i></a> ';
                }

                if (Auth::user()->can('Delete Containment')) {
                    $content .= '<a href="#" title="Delete" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                if (Auth::user()->can('View Containment On Map')) {
                    $content .= '<a title="Map" href="' . action("MapsController@index", ['layer' => 'containments_layer', 'field' => 'id', 'val' => $model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-map-marker"></i></a> ';
                }
                if (Auth::user()->can('List Emptying Services')) {
                    $content .= '<a title="Emptying Service History" href="' . action("Fsm\EmptyingController@index", ['containment_code' => $model->id]) . '" class="btn btn-info btn-sm mb-1 ' . (($model->emptyingService()->exists()) ? '"' : 'disabled"') .  '><i class="fa fa-recycle"></i></a> ';
                }

                // if (Auth::user()->can('Add Application')) {
                //     $content .= '<a title="Create Application" href="' . action("ApplicationController@add", ['containcd' => $model->containcd]) . '" class="btn btn-info btn-sm mb-1" '. ($this->checkContainment($model->containcd) && $model->buildings()->exists() && ($model->buildings()->orderBy('bin')->first()->taxcd != null) ? '' : 'disabled') .  '><i class="fa fa-file-text"></i></a> ';
                // }

                if (Auth::user()->can('View Nearest Road To Containment On Map')) {
                    $content .= '<a title="Nearest Road" href="' . action("MapsController@index", ['layer' => 'containments_layer', 'field' => 'id', 'val' => $model->id, 'action' => 'containment-road']) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-road"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
            })
            ->editColumn('type_id',function($model){
                return $model->containmentType->type;
            })

            ->make(true);
    }


    public function fetchBuildingContainmentData($request)
    {
        $containmentData = DB::table('fsm.containments AS c')
            ->leftjoin('building_info.build_contains AS con', 'c.id', '=', 'con.containment_id')
            ->leftjoin('fsm.containment_types AS ct', 'ct.id', '=', 'c.type_id')
            ->select('c.id','ct.type','c.size','c.location',)
            ->where('con.bin', $request->id)
            ->whereNull('con.deleted_at')
            ->get();
        return DataTables::of($containmentData)
            ->addColumn('action', function ($model, Request $request) {

                $content = \Form::open(['method' => 'DELETE', 'action' => ['Fsm\ContainmentController@deleteBuilding', $model->id, $request->id]]);
                if (Auth::user()->can('Edit Containment')) {
                    $content .= '<a title="Edit" href="' . action("Fsm\ContainmentController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('Delete Building from Containment')) {
                    $content .= '<a href="#" title="Delete Connection of Containment from Building" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }
                if (Auth::user()->can('View Containment')) {
                    $content .= '<a title="Detail" href="' . action("Fsm\ContainmentController@show", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }
                if (Auth::user()->can('View Containment')) {
                    $content .= '<a title="History" href="' . action("Fsm\ContainmentController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }
                $content .= \Form::close();
                return $content;
            })
            ->make(true);
    }



    public function getExport($data)
    {
        $searchData = $data['searchData'] ? $data['searchData'] : null;
        $containment_id = $data['containment_id'] ? $data['containment_id'] : null;
        $type_id =  $data['type_id'] ? $data['type_id'] : null;
        $containment_volume =  $data['containment_volume'] ? $data['containment_volume'] : null;
        $containment_location =  $data['containment_location'] ? $data['containment_location'] : null;
        $emptying_status =  $data['emptying_status'] ? $data['emptying_status'] : null;
        $septic_compliance =  $data['septic_compliance'] ? $data['septic_compliance'] : null;

        // $roadcd = isset($_GET['roadcd']) ? $_GET['roadcd'] : null;

        $bin =  $data['bin'] ? $data['bin'] : null;
        $const_date =  $data['const_date'] ? $data['const_date'] : null;

        $columns = [
            'Containment Code', 'Containment Type', 'Containment Volume (m³)', 'Containment Location', 'Septic Tank Standard Compliance',  'Pit Diameter (m)', 'Tank Length (m)', 'Tank Width (m)', 'Depth (m)', 'Construction Date', 'Buildings Served'
        ];

        $query = Containment::select([
            'containments.*',
            'fsm.containment_types.type as containment_type'
        ])
        ->leftJoin('fsm.containment_types', 'containments.type_id', '=', 'fsm.containment_types.id')
        ->whereNull('containments.deleted_at');

        // Apply your filters here
        if (!empty($containment_id)) {
            $query->where('containments.id', $containment_id);
        }

        if (!empty($containment_volume)) {
            $query->where('containments.size', $containment_volume);
        }

        if (!empty($type_id)) {
            $query->where('containments.type_id', $type_id);
        }

        if (!empty($emptying_status)) {
            $query->where('containments.emptied_status', $emptying_status);
        }

        if (!empty($containment_location)) {
            $selectedOption = $_GET['containment_location'];
            $query->where('containments.location', $selectedOption);
        }

        if (!empty($septic_compliance)) {
            $query->where('containments.septic_criteria', $septic_compliance);
        }

        if (!empty($bin)) {
            $query->whereHas("buildings", function ($q) use ($bin) {
                $q->where('buildings.bin', 'ILIKE', '%' . $bin . '%');
            });
        }

        if (!empty($house_number)) {
            $query->whereHas("buildings", function ($q) use ($house_number) {
                $q->where('buildings.house_number', 'ILIKE', '%' . $house_number . '%');
            });
        }

        if (!empty($const_date)) {
            $query->where('containments.construction_date','>=',str_split(str_replace(' ','',str_replace('-', '', $const_date)), 10)[0]);
            $query->where('containments.construction_date','<=',str_split(str_replace(' ','',str_replace('-', '', $const_date)), 10)[1]);
        }

        // Add styles and export logic
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Containments.csv')
            ->addRowWithStyle($columns, $style);

        $query->chunk(5000, function ($containments) use ($writer) {
            foreach ($containments as $containment) {
                $values = [];
                $values[] = $containment->id;
                $values[] = $containment->containment_type; // This will now be the type name
                $values[] = $containment->size;
                $values[] = $containment->location;
                $values[] = $containment->septic_criteria;
                $values[] = $containment->pit_diameter;
                $values[] = $containment->tank_length;
                $values[] = $containment->tank_width;
                $values[] = $containment->depth;
                $values[] = $containment->construction_date;
                $values[] = $containment->buildings_served;
                $writer->addRow($values);
            }
        });

        $writer->close();
    }


    public function fetchContainmentID()
    {
        $query = Containment::select('*');
        if (request()->search) {
            $query->where('id', 'ilike', '%' . request()->search . '%');
        }
        if (request()->type) {
            $query->where('type', 'ilike', '%' . request()->type . '%');
        }
        $total = $query->count();

        $limit = 10;
        if (request()->page) {
            $page  = request()->page;
        } else {
            $page = 1;
        };
        $start_from = ($page - 1) * $limit;

        $total_pages = ceil($total / $limit);
        if ($page < $total_pages) {
            $more = true;
        } else {
            $more = false;
        }
        $house_numbers = $query->offset($start_from)
            ->limit($limit)
            ->get();
        $json = [];
        foreach ($house_numbers as $house_number) {
            $json[] = ['id' => $house_number['id'], 'text' => $house_number['id']];
        }

        return response()->json(['results' => $json, 'pagination' => ['more' => $more]]);
    }
}
