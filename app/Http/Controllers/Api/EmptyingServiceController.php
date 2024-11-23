<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\EmptyingApiRequest;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Fsm\EmployeeInfo;
use App\Models\Fsm\Emptying;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\TreatmentPlant;
use App\Models\Fsm\VacutugType;
use App\Models\User;
use DateTimeZone;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use stdClass;

class EmptyingServiceController extends Controller
{
    public function getAssessedApplications(){
        try {
            $applications = Application::select('applications.*', 'buildings.house_number as building_house_number')
            ->join('building_info.buildings', function ($join) {
                $join->on(DB::raw('CAST(applications.bin AS VARCHAR)'), '=', 'buildings.bin');
            })
            ->where('applications.emptying_status', false)
            ->get();
            foreach ($applications as $application){
                $application->geometry = json_decode($application->buildings()
                        ->select(DB::raw('public.ST_AsGeoJSON(geom) AS coordinates'))
                        ->pluck('coordinates')
                        ->get(0))??null;
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

    public function getTreatmentPlants(){
        try {
            $treatmentplants = TreatmentPlant::Operational()->latest()->select('id', 'name')->get();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'treatment-plants' => $treatmentplants
            ],
            'message' => 'Treatment Plants',
        ]);
    }

    public function getVacutugs(){
        try {
            $vacutugs = VacutugType::where(function($q){
                $q->where("status","=",true)
                    ->where("service_provider_id",'=',Auth::user()->service_provider_id);
            })
                ->orderBy('capacity')->select('id','license_plate_number', 'width', 'capacity')->get();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => $vacutugs,
            'message' => 'Vacutugs'
        ];
    }

    public function getDrivers(){
        try {
            $drivers = EmployeeInfo::Active()->where(function($q) {
                    $q->where('employee_type','=','Driver')
                    ->where("service_provider_id",'=',Auth::user()->service_provider_id);
                })
                ->pluck('name','id')
                ->toArray();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => $drivers,
            'message' => 'Drivers.'
        ];
    }

    public function getEmptiers(){
        try {
            $emptiers = EmployeeInfo::Active()->where(function($q){
                $q->where('employee_type','=','Cleaner/Emptier')
                ->where("service_provider_id",'=',Auth::user()->service_provider_id);
            })
                ->pluck('name','id')
                ->toArray();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => $emptiers,
            'message' => 'Emptiers.'
        ];
    }
    
    /**
    * Save an emptying service record along with related data.
    *
    * @param  EmptyingApiRequest  $request
    * @return array
    */
    public function save(EmptyingApiRequest $request){
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);
        // Begin a database transaction
        DB::beginTransaction();
        $emptying = null;
        try {
            // Validate the request data
            if ($request->validated()){

                $emptying = Emptying::create($request->all());
                $application = Application::findOrFail($request->application_id);

                $containments = Containment::leftJoin('building_info.build_contains', 'building_info.build_contains.containment_id', '=', 'fsm.containments.id')
                ->where('building_info.build_contains.bin', $application->bin)
                ->get(['fsm.containments.*']); 

                if ($application->emptying_status && $emptying){
                    if ($emptying){
                        $emptying->forceDelete();

                    }

                    return response()->json([
                        'status' => false,
                        'message' => "Emptying service is already done for this application."
                    ], 500);

                }
                // Loop through each containment and update
                foreach ($containments as $containment) {
                    $containment->last_emptied_date = $emptying->emptied_date = now();
                    $containment->next_emptying_date = now()->addYears(3);
                    $containment->emptied_status = true;
                    $containment->no_of_times_emptied = $containment->no_of_times_emptied + 1;
                    if($request->distance_closest_well){
                        $containment->distance_closest_well = $request->distance_closest_well ? $request->distance_closest_well : null;
                    }
                    $containment->save();
                }
                
                $emptying->service_provider_id = $application->service_provider_id;
                $emptying->user_id = \Auth::user()->id;
                // Check image upload
                $allowedFileExt = ['jpg', 'jpeg', 'png', 'PNG', 'JPG', 'JPEG'];
                $extension_receipt = $request->receipt_image->getClientOriginalExtension();
                $extension_house = $request->house_image->getClientOriginalExtension();
                $dateTime = now();
                $dateTime->setTimezone(new DateTimeZone('Asia/Kathmandu'));
                $dateTime =  $dateTime->format('Y_m_d_H_i_s');
                $check = in_array($extension_receipt, $allowedFileExt) && in_array($extension_house, $allowedFileExt);

                if ($check) {
                    try {
                      
                        $filename_receipt = $emptying->id . '_' . $emptying->application_id . '_' . $dateTime . '.' . $extension_receipt;
                        $filename_house = $emptying->id . '_' . $emptying->application_id . '_' . $dateTime . '.' . $extension_house;

                        // Resize images if they are larger than 2MB
                        $storeReceiptImg = Image::make($request->receipt_image);
                                               

                        if ($storeReceiptImg->filesize() > 2 * 1024 * 1024) {
                            $storeReceiptImg->resize(null, 1080, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode($extension_receipt, 50);
                        }
                        $storeReceiptImg->save(Storage::disk('local')->path('/public/emptyings/receipts/' . $filename_receipt));

                        // Repeat the same for the house image
                        $storeHouseImg = Image::make($request->house_image);
                        if ($storeHouseImg->filesize() > 2 * 1024 * 1024) {
                            $storeHouseImg->resize(null, 1080, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode($extension_house, 50);
                        }
                        $storeHouseImg->save(Storage::disk('local')->path('/public/emptyings/houses/' . $filename_house));

                        $emptying->receipt_image = $filename_receipt;
                        $emptying->house_image = $filename_house;

                    } catch (\Throwable $th) {
                                                  
                        if ($emptying) {
                            $emptying->forceDelete();
                            $application->emptying_status = false;
                            $application->save();
                        }
                        return response()->json([
                            'status' => false,
                            'message' => "Error! Unable to save images."
                        ], 500);
                    }
                } else {
                    if ($emptying){
                        $emptying->forceDelete();
                        $application->emptying_status = false;
                        $application->save();
                    }
                    return response()->json([
                        'status' => false,
                        'message' => "Error! Invalid image format."
                    ], 500);
                }
                //end check image
                $emptying->save();
                $application->emptying_status = true;
                $application->save();
            }
                DB::commit();

        } catch (\Throwable $th){
            DB::rollBack();
            if ($emptying){
                $emptying->forceDelete();
                $application->emptying_status = false;
                $application->save();
            }
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'message' => 'Emptying service saved successfully.'
        ];
    }

}
