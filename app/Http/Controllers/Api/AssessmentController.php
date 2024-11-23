<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\AssessmentRequest;
use App\Models\Fsm\Application;
use App\Models\Fsm\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function getApplications(){
      
        try {
            // $applications = Application::all()
            //     ->toQuery()
            //     ->select(
            //         'id',
            //         'house_number',
            //         'road_code',
            //         'application_date',
            //         'proposed_emptying_date',
            //         'service_provider_id',
            //         'customer_name',
            //         'customer_contact',
            //         'ward',
            //     )
            //     ->where('emptying_status','=',false)
            //     ->get();
            $applications = Application::select('applications.*', 'buildings.house_number as building_house_number')
            ->join('building_info.buildings', function ($join) {
                $join->on(DB::raw('CAST(applications.bin AS VARCHAR)'), '=', 'buildings.bin');
            })
            ->where('applications.emptying_status', false)
            ->get();
        
            foreach ($applications as $application){
                $application->service_provider = $application->service_provider->company_name;
                $application->geometry = json_decode($application->buildings()
                        ->select(DB::raw('public.ST_AsGeoJSON(geom) AS coordinates'))->pluck('coordinates')->get(0))??null;
            }
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications
            ],
            'message' => 'Applications',
        ]);
    }

    public function save(AssessmentRequest $request){
        try {
            if ($request->validated()){
                $assessment  = Assessment::create($request->all());
            }
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => '',
            'message' => 'Assessment saved successfully.'
        ];
    }

}
