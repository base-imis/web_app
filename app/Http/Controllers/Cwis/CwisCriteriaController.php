<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CwisCriteriaRequest; 
use App\cwis_criteria;
use Laracasts\Flash\Flash;
use Datatables;
use Auth;

class CwisCriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Criteria";
        return view('cwis_criteria.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        $cwis_critera = cwis_criteria::latest('created_at')->get();
        return Datatables::of($cwis_critera)
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['cwis_criteria.destroy', $model->id]]);

                if (Auth::user()->can('Edit Info')) {
                    $content .= '<a title="Edit" href="' . action("CwisCriteriaController@edit", [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-edit"></i></a> ';
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
        return view('cwis_criteria.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CwisCriteriaRequest $request)
    {
        $info = new cwis_criteria();
        $info->category_1 = $request->category_1 ? $request->category_1 : null;
        $info->category_2 = $request->category_2 ? $request->category_2 : null;
        $info->category_3 = $request->category_3 ? $request->category_3 : null;
        $info->save();

        return redirect('cwis_criteria')->with('success','Info created successfully');
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
        $info = cwis_criteria::find($id);
        if ($info) {
            $page_title = "Edit Info";
            return view('cwis_criteria.edit', compact('page_title','info'));
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
    public function update(CwisCriteriaRequest $request, $id)
    {
        $info = cwis_criteria::find($id);
        if ($info) {
            $info->category_1 = $request->category_1 ? $request->category_1 : null;
            $info->category_2 = $request->category_2 ? $request->category_2 : null;
            $info->category_3 = $request->category_3 ? $request->category_3 : null;
            $info->save();

            return redirect('cwis_criteria')->with('success','info updated successfully');
        } else {
            return redirect('cwis_criteria')->with('error','Failed to update info');
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
        $info = cwis_criteria::find($id);
        if ($info) {
            $info->delete();
            return redirect('cwis_criteria')->with('success','info deleted successfully');
            }
        else {
            return redirect('cwis_criteria')->with('error','Failed to delete info');
        }
    }
}
