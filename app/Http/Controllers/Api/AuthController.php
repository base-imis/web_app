<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * Login The User
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request){
        try {
            $validateUser = Validator::make($request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our records.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

/*              'building-survey' => (bool)$user->can('Access Building Survey API'),
                'save-assessment' => (bool)$user->can('Access Supervisor API'),
                'save-emptying-service' => (bool)$user->can('Access Emptying Service API'),*/

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully.',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'data' => [
                    "name" => $user->name,
                    "gender" => $user->gender,
                    "treatment_plant" => $user->treatment_plant->name??null,
                    "help_desk" => $user->help_desk->name??null,
                    "service_provider" => $user->service_provider->company_name??null,
                    "permissions" => [
                                            "building-survey"=> (bool)$user->can('Access Building Survey API'),
                                            "save-assessment"=> (bool)$user->can('Access Supervisor API'),
                                            "save-emptying-service"=> (bool)$user->can('Access Emptying Service API'),
                                            "sewer-connection"=> (bool)$user->can('Access Sewer Connection API')
                                        ],

                ]
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(){
        try {
            Auth::user()->tokens()->delete();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }
}
