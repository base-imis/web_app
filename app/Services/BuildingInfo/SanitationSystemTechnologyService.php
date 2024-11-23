<?php

namespace App\Services\BuildingInfo;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\SanitationSystemTechnology;
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

class SanitationSystemTechnologyService {

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
     * Get all the All Employee Info.
     *
     *
     * @return EmployeeInfo[]|Collection
     */
    public function getAllData($data)
    {
        $sanitationSystemTechnologies = SanitationSystemTechnology::latest()->whereNull('deleted_at');
        return Datatables::of($sanitationSystemTechnologies)
            ->filter(function ($query) use ($data) {
                if ($data['sub_type']){
                    $query->where('sub_type','ILIKE','%'.$data['sub_type'].'%');
                }
                if ($data['sanitation_type_id']){
                    $query->where('sanitation_type_id',$data['sanitation_type_id']);
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE',

                'route' => ['sanitation-system-technologies.destroy', $model->id]]);

                if (Auth::user()->can('Edit Sanitation System Technology')) {
                    $content .= '<a title="Edit" href="' . action("BuildingInfo\SanitationSystemTechnologyController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View Sanitation System Technology')) {
                    $content .= '<a title="History" href="' . action("BuildingInfo\SanitationSystemTechnologyController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete Sanitation System Technology')) {
                    $content .= '<a href="#" title="Delete"  class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
            })
            ->editColumn('sanitation_type_id',function($model){
                return $model->sanitationSystem->type;
            })
            ->make(true);
    }
    /**
     * Store or update a newly created resource in storage.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function storeOrUpdate($id,$data)
    {
        if(is_null($id)){
            $sanitationSystemTechnology = new SanitationSystemTechnology();
            $sanitationSystemTechnology->sub_type = $data['sub_type'] ? $data['sub_type'] : null;
            $sanitationSystemTechnology->sanitation_type_id = $data['sanitation_type_id'] ? $data['sanitation_type_id'] : null;
            $sanitationSystemTechnology->save();
        }
        else{
            $sanitationSystemTechnology = SanitationSystemTechnology::find($id);
             $sanitationSystemTechnology->sub_type = $data['sub_type'] ? $data['sub_type'] : null;
            $sanitationSystemTechnology->sanitation_type_id = $data['sanitation_type_id'] ? $data['sanitation_type_id'] : null;
            $sanitationSystemTechnology->save();
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
        $sanitation_type_id = $data['sanitation_type_id'] ? $data['sanitation_type_id'] : null;
        $sub_type = $data['sub_type'] ? $data['sub_type'] : null;
        $columns = ['ID', 'Type', 'Sanitation System'];
        $query = SanitationSystemTechnology::select('id', 'sub_type', 'sanitation_type_id')->whereNull('deleted_at');

        if(!empty($sanitation_type_id)){
            $query->where('sanitation_type_id',$sanitation_type_id);
        }

        if(!empty($sub_type)){
            $query->where('sub_type','ILIKE','%'.$sub_type.'%');
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Sanitation System Technology.CSV')
            ->addRowWithStyle($columns, $style); //Top row of excel

       $query->chunk(5000, function ($sanitationSystemTechnologies) use ($writer) {
            foreach($sanitationSystemTechnologies as $data) {
                $values = [];
                $values[] = $data->id;
                $values[] = $data->sub_type;
                $values[] = $data->SanitationSystem->type;

                $writer->addRow($values);
            }
        });

        $writer->close();

    }

}
