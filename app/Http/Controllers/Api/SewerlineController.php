<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class SewerNetworkController extends Controller
{
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

    public function sendSewerData(Request $request)
    {
        try {
            $sewerData = $request->all();

            // Log incoming request data in a clean format
            \Log::channel('roadline')->info('New sewer data request', [
                'sewer_code' => $sewerData['code'] ?? 'not_provided',
                'sewer_name' => $sewerData['name'] ?? 'not_provided'
            ]);

            if (empty($sewerData)) {
                throw new \Exception('No data received');
            }

            $token = $this->getAuthToken();

            $response = Http::withToken($token)
                ->timeout(30)
                ->post(env('EBPS_SEWER_ENDPOINT'), $sewerData);

            if (!$response->successful()) {
                throw new \Exception("Sewer data submission failed: {$response->body()}");
            }

            \Log::channel('roadline')->info('Sewer data submitted successfully', [
                'response' => $response->json()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sewer data submitted successfully',
                'data' => $response->json()
            ], 200);
        } catch (\Exception $e) {
            \Log::channel('roadline')->error("Sewer data error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
