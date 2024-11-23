<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Fsm\TreatmentplantPerformanceTestRequest;
use App\Models\Fsm\CwisSetting;
use App\Services\Fsm\TreatmentplantPerformanceTestService;
use App\Services\Fsm\CwisSettingService;
use App\Models\Fsm\TreatmentPlantPerformanceTest;
use App\Models\SiteSetting;
use DB;

class CwisSettingController extends Controller
{
    protected CwisSettingService $cwissetting;
    public function __construct(CwisSettingService $cwissetting)
    {
        $this->middleware('auth');
        $this->cwissetting = $cwissetting;
    }

    public function index()
    {

        $page_title = "CWIS Setting";

        $data = CwisSetting::where('category','cwis_setting')->pluck('value','name');
        return view('fsm/cwis-setting.index', compact('page_title', 'data'));
    }


    public function store(Request $request)
    {

        $data = $request->all();
        $result = $this->cwissetting->storeOrUpdate($data);
        return redirect('fsm/cwis-setting')->with('success', ' Site Setting updated successfully');
    }

}
