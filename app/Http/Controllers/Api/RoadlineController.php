<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\Auth\EbpsAuthService;
use Carbon\Carbon;

class RoadlineController extends Controller
{
    /* protected EbpsAuthService $ebpsAuthService;
    public function __construct(EbpsAuthService $ebpsAuthService){

    } */
    private function getAuthToken()
    {
        try {
            $tokenResponse = Http::timeout(15)
                ->asForm()
                ->post(env('EBPS_TOKEN_ENDPOINT'), [
                    'grant_type' => 'password',
                    'username' => env('EBPS_USERNAME'),
                    'password' => env('EBPS_PASSWORD')
                ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception("Token request failed: {$tokenResponse->body()}");
            }

            $token = $tokenResponse->json()['access_token'] ?? null;
            if (!$token) {
                throw new \Exception('Token not found in response');
            }

            \Log::channel('roadline')->info('Auth token obtained successfully');
            return $token;
        } catch (\Exception $e) {
            \Log::channel('roadline')->error("Auth token error: {$e->getMessage()}");
            throw $e;
        }
    }


    public function sendRoadData(Request $request)
    {
        try {
            $roadData = $request->all();

            // Log incoming request data in a clean format
            \Log::channel('roadline')->info('New road data request', [
                'road_code' => $roadData['code'] ?? 'not_provided',
                'road_name' => $roadData['name'] ?? 'not_provided'
            ]);

            if (empty($roadData)) {
                throw new \Exception('No data received');
            }

          $token = $this->getAuthToken();
            /* $token = $this->ebpsAuthService->getToken(); */

            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->post(env('ROADLINE_API_URL'), [
                    'RoadCodeId' => $roadData['code'] ?? null,
                    'RoadName' => $roadData['name'] ?? null,
                    'Hierarchy' => $roadData['hierarchy'] ?? null,
                    'SurfaceType' => $roadData['surface_type'] ?? null,
                    'Length' => $roadData['length'] ?? null,
                    'RightOfWay' => $roadData['right_of_way'] ?? null,
                    'CarryingWidth' => $roadData['carrying_width'] ?? null
                ]);

            // Log API response status
        \Log::channel('roadline')->info('API Response', [
                'road_code' => $roadData['code'] ?? 'not_provided',
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            return response()->json([
                'success' => true,
                'responseCode' => 200,
                'content' => "Data processed successfully",
                'data' => $roadData
            ], 200);

        } catch (\Exception $e) {
            // Simplified error logging
            \Log::channel('roadline')->error('Road data error', [
                'message' => $e->getMessage(),
                'road_code' => $roadData['code'] ?? 'not_provided',
                'location' => basename($e->getFile()) . ':' . $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'responseCode' => 500,
                'content' => "Error processing request: {$e->getMessage()}"
            ], 500);
        }
    }
    /* public function sendRoadData(Request $request)
    {
        try {
            $roadData = $request->all();

            // Log incoming data
            \Log::channel('roadline')->info('New road data request', [
                'road_code' => $roadData['code'] ?? 'not_provided',
                'road_name' => $roadData['name'] ?? 'not_provided'
            ]);

            if (empty($roadData)) {
                throw new \Exception('No data received');
            }

            // Use the service to get the token
            $authService = new EbpsAuthService();
            $token = $authService->getToken();

            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->post(env('ROADLINE_API_URL'), [
                    'RoadCodeId'    => $roadData['code'] ?? null,
                    'RoadName'      => $roadData['name'] ?? null,
                    'Hierarchy'     => $roadData['hierarchy'] ?? null,
                    'SurfaceType'   => $roadData['surface_type'] ?? null,
                    'Length'        => $roadData['length'] ?? null,
                    'RightOfWay'    => $roadData['right_of_way'] ?? null,
                    'CarryingWidth' => $roadData['carrying_width'] ?? null
                ]);

            \Log::channel('roadline')->info('API Response', [
                'road_code' => $roadData['code'] ?? 'not_provided',
                'status'    => $response->status(),
                'success'   => $response->successful()
            ]);

            return response()->json([
                'success'      => true,
                'responseCode' => 200,
                'content'      => "Data processed successfully",
                'data'         => $roadData
            ], 200);

        } catch (\Exception $e) {
            \Log::channel('roadline')->error('Road data error', [
                'message'   => $e->getMessage(),
                'road_code' => $roadData['code'] ?? 'not_provided',
                'location'  => basename($e->getFile()) . ':' . $e->getLine()
            ]);

            return response()->json([
                'success'      => false,
                'responseCode' => 500,
                'content'      => "Error processing request: {$e->getMessage()}"
            ], 500);
        }
    } */
}
