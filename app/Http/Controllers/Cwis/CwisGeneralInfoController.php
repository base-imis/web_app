<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CwisGeneralInfoRequest;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use App\ctpt_generalinfo;
use App\Ward;
use Laracasts\Flash\Flash;
use Datatables;
use Auth;
use DB;


class CwisGeneralInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List CT/PT General Informations', ['only' => ['index']]);
        $this->middleware('permission:View CT/PT General Information', ['only' => ['show']]);
        $this->middleware('permission:Add CT/PT General Information', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit CT/PT General Information', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete CT/PT General Information', ['only' => ['destroy']]);
        $this->middleware('permission:Export CT/PT General Informations', ['only' => ['export']]);
    }
    
    public function index()
    {
        $page_title = "CT/PT General Information";
        return view('ctpt-generalinfo.index', compact('page_title'));
    }
    
    public function getData(Request $request)
    {
        $cwis_general = ctpt_generalinfo::latest('created_at')->whereNull('deleted_at');
        return Datatables::of($cwis_general)
            ->filter(function ($query) use ($request) {
                if ($request->toilet_id) {
                    $query->where('id', '=',trim($request->toilet_id));
                }
                if ($request->toilet_name) {
                    $query->where('name', 'ILIKE', '%' .  trim($request->toilet_name) . '%');
                }
                if ($request->ward) {
                    $query->where('ward', '=', $request->ward);
                }
                if ($request->caretaker) {
                    $query->where('caretaker', 'ILIKE', '%' .  trim($request->caretaker) . '%');
                }
                if ($request->category) {
                    $query->where('category', 'ILIKE', '%' .  trim($request->category) . '%');
                }
                if ($request->toilet_type) {
                    $query->where('type', 'ILIKE', '%' .  trim($request->toilet_type) . '%');
                }
                if ($request->facility_mof) {
                    $query->where('facility_mof',$request->facility_mof);
                }
                if ($request->facility_hndicap) {
                    $query->where('facility_hndicap',$request->facility_hndicap);
                }
                if ($request->facility_cldrn) {
                    $query->where('facility_cldrn',$request->facility_cldrn);
                }
                if ($request->sansuplyndsposlfac) {
                    $query->where('sansuplyndsposlfac',$request->sansuplyndsposlfac);
                }
                })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['general-info.destroy', $model->id]]);

                if (Auth::user()->can('Edit Info')) {
                    $content .= '<a title="Edit" href="' . action("CwisGeneralInfoController@edit", [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('Delete Info')) {
                    $content .= '<a title="Delete"  class="delete btn btn-danger btn-xs">&nbsp;<i class="fa fa-trash"></i>&nbsp;</a> ';
                }

                if (Auth::user()->can('view-map')) {
                    $content .= '<a title="Map" href="' . action("MapsController@index", ['layer' => 'toilets_layer', 'field' => 'id', 'val' => $model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-map-marker"></i></a> ';
                }
                // if (Auth::user()->can('Edit Male or Female User')) {
                //     $content .= '<a title="Users" href="' . action("CwisMofUserController@edit", [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-user"></i></a> ';
                // }

                $content .= \Form::close();
                return $content;
            })
            ->editColumn('facility_mof',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                $content .= $model->facility_mof===null?'<i class="fa fa-minus"></i>' : ($model->facility_mof?'<i class="fa fa-check"></i>':'<i class="fa fa-times"></i>');
                $content .= '</div>';
                return $content;
            })
            ->editColumn('facility_hndicap',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                $content .= $model->facility_hndicap===null?'<i class="fa fa-minus"></i>' : ($model->facility_hndicap?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
                $content .= '</div>';
                return $content;
            })
            ->editColumn('facility_cldrn',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                $content .= $model->facility_cldrn===null?'<i class="fa fa-minus"></i>' : ($model->facility_cldrn?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
                $content .= '</div>';
                return $content;
            })
            ->editColumn('sansuplyndsposlfac',function($model){
                $content = '<div style="display:flex;align-items: center;justify-content: space-between;align-content: center;">';
                $content .= $model->sansuplyndsposlfac===null?'<i class="fa fa-minus"></i>' : ($model->sansuplyndsposlfac?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
                $content .= '</div>';
                return $content;
            })
            ->rawColumns(['facility_mof','facility_hndicap','facility_cldrn', 'sansuplyndsposlfac', 'action'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Info";
        $ward = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        return view('ctpt-generalinfo.create', compact('page_title','ward'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CwisGeneralInfoRequest $request)
    {
        $info = new ctpt_generalinfo();
        $info->name = $request->name ? $request->name : null;
        $info->ward = $request->ward ? $request->ward : null;
        $info->caretaker = $request->caretaker ? $request->caretaker : null;
        $info->phone = $request->phone ? $request->phone : null;
        $info->category = $request->category ? $request->category : null;
        $info->type = $request->type ? $request->type : null;
        $info->accsfrmnrsstrd= $request->accsfrmnrsstrd ? $request->accsfrmnrsstrd : null;
        $info->caters_mf= $request->caters_mf ? $request->caters_mf : null;
        $info->toiletno_male= $request->toiletno_male ? $request->toiletno_male : null;
        $info->toiletno_female= $request->toiletno_female ? $request->toiletno_female : null;
        $info->facility_mof= $request->facility_mof ? $request->facility_mof : null;
        $info->facility_hndicap= $request->facility_hndicap ? $request->facility_hndicap : null;
        $info->facility_cldrn= $request->facility_cldrn ? $request->facility_cldrn : null;
        $info->sansuplyndsposlfac= $request->sansuplyndsposlfac ? $request->sansuplyndsposlfac : null;
        $info->owner= $request->owner ? $request->owner : null;
        $info->oprtrmntnr= $request->oprtrmntnr ? $request->oprtrmntnr : null;
        $info->notusedtime= $request->notusedtime ? $request->notusedtime : null;
        $info->opengtime= $request->opengtime ? $request->opengtime : null;
        $info->closingtime= $request->closingtime ? $request->closingtime : null;
        $info->indctvsign= $request->indctvsign ? $request->indctvsign : null;
        $info->feecollected= $request->feecollected ? $request->feecollected : null;
        if($request->longitude && $request->latitude) {
            $info->geom = DB::raw("ST_GeomFromText('POINT(" . $request->longitude . " " . $request->latitude .  ")', 4326)");
        }
        $info->save();

        return redirect('general-info')->with('success','CT / PT General Info created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $info = ctpt_generalinfo::find($id);
        if ($info) {
            $page_title = "Edit Info";
            $ward = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
            // $name = ctpt_generalinfo::pluck('name', 'name')->all();
            return view('ctpt-generalinfo.edit', compact('page_title','info', 'ward'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CwisGeneralInfoRequest $request, $id)
    {
        $info = ctpt_generalinfo::find($id);
        if ($info) {
            $info->name = $request->name ? $request->name : null;
            $info->ward = $request->ward ? $request->ward : null;
            $info->caretaker = $request->caretaker ? $request->caretaker : null;
            $info->phone = $request->phone ? $request->phone : null;
            $info->category = $request->category ? $request->category : null;
            $info->type = $request->type ? $request->type : null;
            $info->accsfrmnrsstrd= $request->accsfrmnrsstrd ? $request->accsfrmnrsstrd : null;
            $info->caters_mf= $request->caters_mf ? $request->caters_mf : null;
            $info->toiletno_male= $request->toiletno_male ? $request->toiletno_male : null;
            $info->toiletno_female= $request->toiletno_female ? $request->toiletno_female : null;
            $info->facility_mof= $request->facility_mof ? $request->facility_mof : null;
            $info->facility_hndicap= $request->facility_hndicap ? $request->facility_hndicap : null;
            $info->facility_cldrn= $request->facility_cldrn ? $request->facility_cldrn : null;
            $info->sansuplyndsposlfac= $request->sansuplyndsposlfac ? $request->sansuplyndsposlfac : null;
            $info->owner= $request->owner ? $request->owner : null;
            $info->oprtrmntnr= $request->oprtrmntnr ? $request->oprtrmntnr : null;
            $info->notusedtime= $request->notusedtime ? $request->notusedtime : null;
            $info->opengtime= $request->opengtime ? $request->opengtime : null;
            $info->closingtime= $request->closingtime ? $request->closingtime : null;
            $info->indctvsign= $request->indctvsign ? $request->indctvsign : null;
            $info->feecollected= $request->feecollected ? $request->feecollected : null;
            if($request->longitude && $request->latitude) {
                $info->geom = DB::raw("ST_GeomFromText('POINT(" . $request->longitude . " " . $request->latitude .  ")', 4326)");
            }
            $info->save();

            return redirect('general-info')->with('success','CT / PT General info updated successfully');
        } else {
            return redirect('general-info')->with('error','Failed to update CT / PT General info');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $info = ctpt_generalinfo::find($id);
        if ($info) {
            $info->delete();
            return redirect('general-info')->with('success','Info deleted successfully');
            }
        else {
            return redirect('general-info')->with('error','Failed to delete info');
        }
    }

    public function export()
    {
        $searchData = $_GET['searchData'] ?? null;
        $toilet_id = $_GET['toilet_id'] ?? null;
        $toilet_name = $_GET['toilet_name'] ?? null;
        $ward = $_GET['ward'] ?? null;
        $caretaker= $_GET['caretaker'] ?? null;
        $category = $_GET['category'] ?? null;
        $type = $_GET['type'] ?? null;
        $facility_mof = $_GET['facility_mof'] ?? null;
        $facility_hndicap = $_GET['facility_hndicap'] ?? null;
        $facility_cldrn = $_GET['facility_cldrn'] ?? null;
        $sansuplyndsposlfac = $_GET['sansuplyndsposlfac'] ?? null;

        $columns = ['ID','Ward','Toilet Name','Caretaker','Phone Number','Category','Type','Access From Nearest Road (in m)','Caters For Both Male/Female','Toilet Numbers for Male','Toilet Numbers For Female','Separate Facility For Male/Female','Separate Facility For Handicapped People','Separate Facility For Children','Sanitary Supplies And Disposal Facilities','Owner','Operate And Maintained By','If Toilet Not Being Used As Toilet, then Indicative Month','Opening Time','Closing time','Presence Of Indicative Sign','Fee Collected'];

        $query = ctpt_generalinfo::select('id','ward','name','caretaker','phone','category','type','accsfrmnrsstrd','caters_mf','toiletno_male','toiletno_female','facility_mof','facility_hndicap','facility_cldrn','sansuplyndsposlfac','owner','oprtrmntnr','notusedtime','opengtime','closingtime','indctvsign','feecollected')->whereNull('deleted_at');

        if (!empty($searchData)) {
            $searchColumns = ['id','ward','name','caretaker','phone','category','type','accsfrmnrsstrd','caters_mf','toiletno_male','toiletno_female','facility_mof','facility_hndicap','facility_cldrn','sansuplyndsposlfac','owner','oprtrmntnr','notusedtime','opengtime','closingtime','indctvsign','feecollected'];

            foreach ($searchColumns as $column) {
                $query->orWhereRaw("lower(cast(" . $column . " AS varchar)) LIKE lower('%" . $searchData . "%')");
            }
        }

        if(!empty($toilet_id)){
            $query->where('id', '=',trim($toilet_id));
        }

        if(!empty($toilet_name)){
            $query->where('name', 'ILIKE', '%' .  trim($toilet_name) . '%');
        }

        if(!empty($ward)){
            $query->where('ward', '=', $ward);
        }

        if(!empty($caretaker)){
            $query->where('caretaker', 'ILIKE', '%' .  trim($caretaker) . '%');
        }

        if(!empty($category)){
            $query->where('category', 'ILIKE', '%' .  trim($category) . '%');
        }

        if(!empty($type)){
            $query->where('type', 'ILIKE', '%' .  trim($type) . '%');
        }

        if(!empty($facility_mof)){
            $query->where('facility_mof',$facility_mof);
        }

        if(!empty($facility_hndicap)){
            $query->where('facility_hndicap',$facility_hndicap);
        }

        if(!empty($facility_cldrn)){
            $query->where('facility_cldrn',$facility_cldrn);
        }

        if(!empty($sansuplyndsposlfac)){
            $query->where('sansuplyndsposlfac',$sansuplyndsposlfac);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser('CTPT_generalinfo.xlsx')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($ctptGeneralInfos) use ($writer) {
            $writer->addRows($ctptGeneralInfos->toArray());
        });

        $writer->close();
    }
}
