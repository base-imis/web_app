<?php

namespace App\Services\BuildingInfo;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\SanitationSystem;
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

class SanitationSystemTypeService {

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
        $sanitationSystemTechnologies = SanitationSystem::latest()->whereNull('deleted_at');
        return Datatables::of($sanitationSystemTechnologies)
            ->filter(function ($query) use ($data) {
                if ($data['type']){
                    $query->where('type','ILIKE','%'.$data['type'].'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE',

                'route' => ['sanitation-system-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit Sanitation System Type')) {
                    $content .= '<a title="Edit" href="' . action("BuildingInfo\SanitationSystemTypeController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1 "><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View Sanitation System Type')) {
                    $content .= '<a title="History" href="' . action("BuildingInfo\SanitationSystemTypeController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1 "><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete Sanitation System Type')) {
                    $content .= '<a href="#" title="Delete"  class="delete btn btn-danger btn-sm mb-1 "><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
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
            $sanitationSystemType = new SanitationSystem();
            $sanitationSystemType->type = $data['type'] ? $data['type'] : null;
            $sanitationSystemType->save();
        }
        else{
            $sanitationSystemType = SanitationSystem::find($id);
            $sanitationSystemType->type = $data['type'] ? $data['type'] : null;
            $sanitationSystemType->save();
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

        $type = $data['type'] ? $data['type'] : null;
        $columns = ['ID', 'Type'];
        $query = SanitationSystem::select('id', 'type')->whereNull('deleted_at');

        if(!empty($type)){
            $query->where('type','ILIKE','%'.$type.'%');
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Sanitation System Type.CSV')
            ->addRowWithStyle($columns, $style); //Top row of excel

       $query->chunk(5000, function ($sanitationSystemTechnologies) use ($writer) {
            foreach($sanitationSystemTechnologies as $data) {
                $values = [];
                $values[] = $data->id;
                $values[] = $data->type;

                $writer->addRow($values);
            }
        });

        $writer->close();

    }

}
