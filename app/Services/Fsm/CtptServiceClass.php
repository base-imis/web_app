<?php
// Last Modified Date: 19-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Services\Fsm;

use Illuminate\Http\Request;
use App\Models\Fsm\Ctpt;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Auth;
use DataTables;
use DB;
use App\Models\BuildingInfo\Building;
use App\Enums\CtptStatus;
use App\Enums\CtptStatusOperational;

class CtptServiceClass{


    public function fetchData(Request $request){
        
        $cwis_general = Ctpt::select('toilets.*', 'building_info.buildings.house_number AS house_address')
        ->leftJoin('building_info.buildings', 'building_info.buildings.bin', '=', 'toilets.bin')
        ->whereNull('toilets.deleted_at');
       
        return Datatables::of($cwis_general)
            ->filter(function ($query) use ($request) {
                if ($request->toilet_id) {
                    $query->where('id', '=',trim($request->toilet_id));
                }
                if ($request->name) {
                    $query->where('toilets.name', 'ILIKE', '%' .  trim($request->name) . '%');
                }
                if ($request->ward) {
                    $query->where('toilets.ward', $request->ward);
                }
                if ($request->caretaker_name) {
                    $query->where('toilets.caretaker_name', 'ILIKE', '%' .  trim($request->caretaker_name) . '%');
                }
                if ($request->bin) {
                    $query->where('toilets.bin', 'ILIKE', '%' .  trim($request->bin) . '%');
                }
                if ($request->house_address){
                    $query->where('building_info.buildings.house_number', 'ILIKE', '%' . $request->house_address . '%');
                }
                if ($request->type) {
                    $query->where('toilets.type', '=',$request->type);

                }
                if ($request->sanitary_supplies_disposal_facility) {
                    $query->where('toilets.sanitary_supplies_disposal_facility',$request->sanitary_supplies_disposal_facility);
                }

                if ($request->status) {
                    $query->where('toilets.status', $request->status);
                }
                })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['ctpt.destroy', $model->id]]);

                if (Auth::user()->can('Edit CT/PT General Information')) {
                    $content .= '<a title="Edit" href="' . action("Fsm\CtptController@edit", [$model->id]) .
                     '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('View CT/PT General Information')) {
                    $content .= '<a title="Detail" href="' . action("Fsm\CtptController@show", [$model->id]) .
                    '"class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }
                if (Auth::user()->can('View CT/PT History')) {
                    $content .= '<a title="History" href="' . action("Fsm\CtptController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }
                if (Auth::user()->can('Delete CT/PT General Information')) {
                    $content .= '<a title="Delete"  class="delete btn btn-danger btn-xs btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }
                if (Auth::user()->can('View CT/PT General Information on Map')) {
                    $content .= '<a title="Map" href="' . action("MapsController@index", ['layer' => 'toilets_layer', 'field' => 'id', 'val' => $model->id]) .
                    '" class="btn btn-info btn-sm mb-1"><i class="fa fa-map-marker"></i></a> ';
                }
                $content .= \Form::close();
                return $content;
            })
            ->editColumn('male_or_female_facility',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                if ($model->male_or_female_facility === true) {
                    $content .= 'Yes';
                } elseif ($model->male_or_female_facility === false) {
                    $content .= 'No';
                } else {
                    $content .= '<i class="fa fa-minus"></i>';
                }

                $content .= '</div>';
                return $content;
            })

            ->editColumn('handicap_facility',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                if ($model->handicap_facility === true) {
                    $content .= 'Yes';
                } elseif ($model->handicap_facility === false) {
                    $content .= 'No';
                } else {
                    $content .= '<i class="fa fa-minus"></i>';
                }
                $content .= '</div>';
                return $content;
            })

            ->editColumn('children_facility',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                if ($model->children_facility === true) {
                    $content .= 'Yes';
                } elseif ($model->children_facility === false) {
                    $content .= 'No';
                } else {
                    $content .= '<i class="fa fa-minus"></i>';
                }
                $content .= '</div>';
                return $content;
            })
            ->editColumn('sanitary_supplies_disposal_facility',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                if ($model->sanitary_supplies_disposal_facility === true) {
                    $content .= 'Yes';
                } elseif ($model->sanitary_supplies_disposal_facility === false) {
                    $content .= 'No';
                } else {
                    $content .= '<i class="fa fa-minus"></i>';
                }

                $content .= '</div>';
                return $content;
            })
            ->editColumn('status', function ($model) {
                switch ($model->status) {
                    case CtptStatusOperational::NotOperational:
                        return 'Not Operational';
                    case CtptStatusOperational::Operational:
                        return 'Operational';
                }
            })
            ->rawColumns(['male_or_female_facility','handicap_facility','children_facility', 'sanitary_supplies_disposal_facility', 'action'])
            ->make(true);
    }

    public function storeCtptData($request)
    {

        $info = new ctpt();
        $info->type = $request->type ? $request->type : null;
        $info->name = $request->name ? $request->name : null;
        $info->ward = $request->ward ? $request->ward : null;
        $info->location_name = $request->location_name ? $request->location_name : null;
        $info->bin= $request->bin ? $request->bin : null;
        $info->owner= $request->owner ? $request->owner : null;
        $info->owning_institution_name= $request->owning_institution_name ? $request->owning_institution_name : null;
        $info->operator_or_maintainer= $request->operator_or_maintainer ? $request->operator_or_maintainer : null;
        $info->operator_or_maintainer_name= $request->operator_or_maintainer_name ? $request->operator_or_maintainer_name : null;
        $info->caretaker_name= $request->caretaker_name? $request->caretaker_name: null;
        $info->caretaker_gender= $request->caretaker_gender? $request->caretaker_gender: null;
        $info->caretaker_contact_number = $request->caretaker_contact_number ? $request->caretaker_contact_number : null;
        $info->total_no_of_toilets= $request->total_no_of_toilets ? $request->total_no_of_toilets : null;
        $info->total_no_of_urinals= $request->total_no_of_urinals ? $request->total_no_of_urinals : null;
        $info->access_frm_nearest_road= $request->access_frm_nearest_road ? $request->access_frm_nearest_road : null;
        $info->separate_facility_with_universal_design= $request->separate_facility_with_universal_design ?? null;
        $info->male_or_female_facility= $request->male_or_female_facility ?? null;
        $info->handicap_facility = $request->handicap_facility ?? null;
        $info->children_facility= $request->children_facility ?? null;
        $info->male_seats= $request->male_seats? $request->male_seats: null;
        $info->female_seats= $request->female_seats? $request->female_seats: null;
        $info->pwd_seats= $request->pwd_seats? $request->pwd_seats: null;
        $info->sanitary_supplies_disposal_facility = $request->sanitary_supplies_disposal_facility ?? null;
        $info->status= $request->status ?? null;
        $info->indicative_sign= $request->indicative_sign ?? null;
        $info->fee_collected= $request->fee_collected ?? null;
        $info->amount_of_fee_collected= $request->amount_of_fee_collected ? $request->amount_of_fee_collected : null;
        $info->frequency_of_fee_collected= $request->frequency_of_fee_collected ? $request->frequency_of_fee_collected : null;
        $centroid = DB::select(DB::raw("SELECT (ST_AsText(st_centroid(st_union(geom)))) AS central_point FROM building_info.buildings WHERE bin = '$request->bin'"));
        $info->geom = DB::raw("ST_GeomFromText('".$centroid[0]->central_point."', 4326)");
       
        $info->save();
        return redirect('fsm/ctpt')->with('success','Public / Community Toilets Added Successfully ');
    }

    public function updateCtptData($request, $id)
    {

        $info = Ctpt::find($id);
        if ($info) {
            $info->type = $request->type ?? null;
            $info->name = $request->name ?? null;
            $info->ward = $request->ward ?? null;
            $info->location_name = $request->location_name ?? null;
            $info->bin = $request->bin ?? null;
            $info->owner = $request->owner ?? null;
            $info->operator_or_maintainer = $request->operator_or_maintainer ?? null;
            $info->caretaker_name = $request->caretaker_name ?? null;
            $info->caretaker_gender = $request->caretaker_gender ?? null;
            $info->caretaker_contact_number = $request->caretaker_contact_number ?? null;
            $info->owning_institution_name= $request->owning_institution_name ?? null;
            $info->operator_or_maintainer= $request->operator_or_maintainer ?? null;
            $info->total_no_of_toilets= $request->total_no_of_toilets ?? null;
            $info->total_no_of_urinals= $request->total_no_of_urinals ?? null;
            $info->access_frm_nearest_road = $request->access_frm_nearest_road ?? null;
            $info->separate_facility_with_universal_design = $request->separate_facility_with_universal_design ?? null;
            $info->male_or_female_facility = $request->male_or_female_facility ?? null;
            $info->handicap_facility = $request->handicap_facility ?? null;
            $info->children_facility = $request->children_facility ?? null;
            $info->male_seats = $request->male_seats ?? null;
            $info->female_seats = $request->female_seats ??  null;
            $info->pwd_seats = $request->pwd_seats ?? null;
            $info->sanitary_supplies_disposal_facility = $request->sanitary_supplies_disposal_facility ?? null;
            $info->status = $request->status ?? null;
            $info->indicative_sign = $request->indicative_sign ?? null;
            $info->fee_collected = $request->fee_collected ?? null;
            $info->amount_of_fee_collected= $request->amount_of_fee_collected ?? null;
            $info->frequency_of_fee_collected= $request->frequency_of_fee_collected ?? null;
            $centroid = DB::select(DB::raw("SELECT (ST_AsText(st_centroid(st_union(geom)))) AS central_point FROM building_info.buildings WHERE bin = '$request->bin'"));
            $info->geom = DB::raw("ST_GeomFromText('".$centroid[0]->central_point."', 4326)");
            $info->save();

            return redirect('fsm/ctpt')->with('success', 'Public / Community Toilets Updated Successfully');
        } else {
            return redirect('fsm/ctpt')->with('error', 'Failed to update CT / PT General info');
        }

    }

    public function exportData($data)
    {
        
        $name = $data['name'] ? $data['name'] : null;
        $type = $data['type'] ? $data['type'] : null;
        $house_address = $data['house_address'] ? $data['house_address'] : null;
        $ward = $data['ward'] ? $data['ward'] : null;
        $bin = $data['bin'] ? $data['bin'] : null;
        $status = $data['status'] ? $data['status'] : null;
        $caretaker_name = $data['caretaker_name'] ? $data['caretaker_name'] : null;
        $sanitary_supplies_disposal_facility = $data['sanitary_supplies_disposal_facility'] ? $data['sanitary_supplies_disposal_facility'] : null;

        $columns = ['ID','Toilet Type','Toilet Name',
        'Ward Number', 'Location', 'BIN', 'House Number', 'Distance from Nearest Road (in m)','Status', 'Caretaker Name','Caretaker Gender', 'Caretaker Contact','Owning Institution','Name of Owning Institution', 'Operator and Maintained By','Name of Operate and Maintained by','No. of Households Served','Total Number of Seats ', 'Total Number of Urinals','Separate Facility for Male and Female','No. of Seats for Male Users','No. of Seats for Female Users ',
         'No. of Male Users', 'No. of Female Users','Separate Facility for People with Disability','No. of People with Disability', 'No. of seats for People with Disability','Separate Facility for Children','No. of Children Users', 
        'Adherence with Universal Design Principles ','Presence of Indicative Sign', 'Sanitary Supplies and Disposal Facilities', 'Uses Fee Collection', 'Uses Fee Rate', 'Frequency of Fee Collection'];

        $query = DB::table('fsm.toilets as t')
        ->leftJoin('building_info.buildings as b', 'b.bin', '=', 't.bin')
        ->select(
            't.id', 't.name', 't.type', 't.ward', 't.location_name', 't.bin',
            'b.house_number AS house_address', 't.owner', 't.owning_institution_name','t.operator_or_maintainer','t.operator_or_maintainer_name',
            't.caretaker_name', 't.caretaker_gender', 't.caretaker_contact_number',
            't.total_no_of_toilets', 't.total_no_of_urinals',
            't.separate_facility_with_universal_design', 't.access_frm_nearest_road',
            't.male_or_female_facility', 't.male_seats', 't.female_seats',
            't.handicap_facility', 't.pwd_seats', 't.children_facility',
            't.sanitary_supplies_disposal_facility',
            't.status', 't.indicative_sign', 't.fee_collected',
            't.amount_of_fee_collected', 't.frequency_of_fee_collected'
        )
        ->orderBy('t.bin')
        ->whereNull('t.deleted_at');


        if(!empty($bin)){
            $query->where('t.bin',$bin);
        }
     
        if(!empty($house_address)){
            $query->where('b.house_number', 'ILIKE', '%' .  trim($house_address) . '%');
        }

        if(!empty($name)){
            $query->where('name', 'ILIKE', '%' .  trim($name) . '%');
        }
        if(!empty($type)){
            $query->where('type', $type);
        }
        if(!empty($ward)){
            $query->where('ward', '=', $ward);
        }
        if(!empty($status)){
            $query->where('status', '=', $status);
        }
        if(!empty($caretaker_name)){
            $query->where('caretaker_name', 'ILIKE', '%' .  trim($caretaker_name) . '%');
        }
        if(!empty($sanitary_supplies_disposal_facility)){

            $query->where('sanitary_supplies_disposal_facility',$sanitary_supplies_disposal_facility);
        }
        $style = (new StyleBuilder())
        ->setFontBold()
        ->setFontSize(13)
        ->setBackgroundColor(Color::rgb(228, 228, 228))
        ->build();

    $writer = WriterFactory::create(Type::CSV);

    $writer->openToBrowser('Public or Community Toilets.CSV')
        ->addRowWithStyle($columns, $style); //Top row of excel

    $query->chunk(5000, function ($info) use ($writer) {

        foreach($info as $ctpt) {
            $values = [];
            $values[] = $ctpt->id;
            $values[] = $ctpt->type;
            $values[] = $ctpt->name;
            $values[] = $ctpt->ward;
            $values[] = $ctpt->location_name;
            $values[] = $ctpt->bin;
            $values[] = $ctpt->house_address;
            $values[] = $ctpt->access_frm_nearest_road;
            $values[] = CtptStatusOperational::getDescription($ctpt->status);
            $values[] = $ctpt->caretaker_name;
            $values[] = $ctpt->caretaker_gender;
            $values[] = $ctpt->caretaker_contact_number;
            $values[] = $ctpt->owner;
            $values[] = $ctpt->owning_institution_name;
            $values[] = $ctpt->operator_or_maintainer;
            $values[] = $ctpt->operator_or_maintainer_name;
            $values[] = $ctpt->total_no_of_toilets;
            $values[] = $ctpt->total_no_of_urinals;
            $values[] = CtptStatus::getDescription($ctpt->male_or_female_facility);
            $values[] = $ctpt->male_seats;
            $values[] = $ctpt->female_seats;
            $values[] = CtptStatus::getDescription($ctpt->handicap_facility);
            $values[] = $ctpt->pwd_seats;
            $values[] = CtptStatus::getDescription($ctpt->children_facility);
            $values[] = CtptStatus::getDescription($ctpt->separate_facility_with_universal_design);
            $values[] = CtptStatus::getDescription($ctpt->indicative_sign);
            $values[] = CtptStatus::getDescription($ctpt->sanitary_supplies_disposal_facility);
            $values[] = CtptStatus::getDescription($ctpt->fee_collected);
            $values[] = $ctpt->amount_of_fee_collected;
            $values[] = $ctpt->frequency_of_fee_collected;
            
            $writer->addRow($values);
        }

    });

    $writer->close();
     }

}
