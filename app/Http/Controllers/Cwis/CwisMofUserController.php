<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CwisMofUserRequest; 
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use App\ctpt_mofusers;
use App\ctpt_generalinfo;
use Laracasts\Flash\Flash;
use Datatables;
use Auth;


class CwisMofUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List Male or Female Users', ['only' => ['index']]);
        $this->middleware('permission:View Male or Female User', ['only' => ['show']]);
        $this->middleware('permission:Add Male or Female User', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Male or Female User', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Male or Female User', ['only' => ['destroy']]);
        $this->middleware('permission:Export Male or Female Users', ['only' => ['export']]);
    }

    public function index()
    {
        $page_title = "Male and Female Users";
        $name = ctpt_generalinfo::pluck('name');
        return view('ctpt-mofusers.index', compact('page_title', 'name'));
    }



    public function getData(Request $request)
    {
        $cwis_mof = ctpt_mofusers::latest('created_at')->whereNull('deleted_at');
        return Datatables::of($cwis_mof)
            ->filter(function ($query) use ($request) {
                if ($request->toiletname) {
                    
                    $query->where('toiletname', 'ILIKE', '%' .  trim($request->toiletname) . '%');
                }
                if ($request->year) {
                    
                    $query->where('year', '=',  trim($request->year));
                }
                })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['mof-users.destroy', $model->id]]);

                if (Auth::user()->can('Edit Info')) {
                    $content .= '<a title="Edit" href="' . action("CwisMofUserController@edit", [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('Delete Info')) {
                    $content .= '<a title="Delete"  class="delete btn btn-danger btn-xs">&nbsp;<i class="fa fa-trash"></i>&nbsp;</a> ';
                }
                $content .= \Form::close();
                return $content;
            })
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
        $name = ctpt_generalinfo::pluck('name','name');
        return view('ctpt-mofusers.create', compact('page_title', 'name'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CwisMofUserRequest $request)
    {
                if(ctpt_mofusers::where('toiletname', $request->toiletname)->where('year', $request->year)->where('deleted_at', null)->exists()){
                    return redirect('mof-users')->with('error','The record of toilet'.$request->toiletname.' for the year '.$request->year.'already exists!!');
                }
                else{
                    $info = new ctpt_mofusers();
                    $info->no_m_user = $request->no_m_user ? $request->no_m_user : null;
                    $info->no_fem_user = $request->no_fem_user ? $request->no_fem_user : null;
                    $info->total_user = $request->total_user ? $request->total_user : null;
                    $info->avgvisitor = $request->avgvisitor ? $request->avgvisitor : null;
                    $info->year = $request->year ? $request->year : null;
                    $info->toiletname = $request->toiletname ? $request->toiletname : null;
                    $info->save();
            
                    return redirect('mof-users')->with('success','Info created successfully');
                }
        
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
        $info = ctpt_mofusers::find($id);
        if ($info) {
            $page_title = "Edit Info";
            $name = ctpt_mofusers::orderBy('toiletname', 'asc')->pluck('toiletname', 'toiletname')->all();
            $users = ctpt_mofusers::where('id','=',$id)->latest()->first();
            return view('ctpt-mofusers.edit', compact('page_title','info', 'name', 'users'));
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
    public function update(CwisMofUserRequest $request, $id)
    {
        $info = ctpt_mofusers::find($id);
        if ($info) {
            $info->no_m_user = $request->no_m_user ? $request->no_m_user : null;
            $info->no_fem_user = $request->no_fem_user ? $request->no_fem_user : null;
            $info->total_user = $request->total_user ? $request->total_user : null;
            $info->avgvisitor = $request->avgvisitor ? $request->avgvisitor : null;
            $info->year = $request->year ? $request->year : null;
            $info->toiletname = $request->toiletname ? $request->toiletname : null;
            $info->save();
            return redirect('mof-users')->with('success','Info updated successfully');
        } else {
            return redirect('mof-users')->with('error','Failed to update info');
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
        $info = ctpt_mofusers::find($id);
        if ($info) {
            $info->delete();
            return redirect('mof-users')->with('success','Info deleted successfully');
            }
        else {
            return redirect('mof-users')->with('error','Failed to delete info');
        }
    }

    public function export()
    {
        $searchData = $_GET['searchData'] ?? null;
        $toiletname = $_GET['toiletname'] ?? null;
        $year = $_GET['year'] ?? null;

        $columns = ['Toilet Name', 'No. of Male Users', 'No. of Female Users', 'Total Users', 'Average Daily Visitors', 'Year'];

        $query = ctpt_mofusers::select('toiletname', 'no_m_user', 'no_fem_user', 'total_user', 'avgvisitor', 'year')->whereNull('deleted_at');

        if (!empty($searchData)) {
            $searchColumns = ['toiletname', 'year'];

            foreach ($searchColumns as $column) {
                $query->orWhereRaw("lower(cast(" . $column . " AS varchar)) LIKE lower('%" . $searchData . "%')");
            }
        }

        if (!empty($toiletname)){
            $query->where('toiletname', 'ILIKE', '%' .  trim($toiletname) . '%');
        }

        if (!empty($year)){
            $query->where('year', '=',  trim($year));
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser('UserInformation.xlsx')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($ctpt_users) use ($writer) {
            $writer->addRows(json_decode(json_encode($ctpt_users), true));
        });

        $writer->close();
    }
}
