<?php

namespace App\Services\Ebps;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class EbpsClient
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('ebps.base_url'),
            'headers'  => ['Accept' => 'application/json'],
            'verify'   => config('ebps.verify_ssl'),
            'timeout'  => 20,
        ]);
    }

    /**
     * Get cached token; refresh if missing/expired.
     */
    public function token(): string
    {
        return Cache::remember(
            'ebps_access_token',
            now()->addMinutes(config('ebps.token_cache_minutes')),
            function () {
                $response = $this->http->post('api/token', [
                    'form_params' => [
                        'username'   => config('ebps.username'),
                        'password'   => config('ebps.password'),
                        'grant_type' => 'password',
                    ],
                ]);

                $data = json_decode((string) $response->getBody(), true);

                if (!is_array($data) || empty($data['access_token'])) {
                    throw new \RuntimeException('EBPS token response missing access_token');
                }

                return $data['access_token'];
            }
        );
    }

    /**
     * Perform authorized GET request and return decoded JSON.
     */
    public function get(string $uri): array
    {
        $token = $this->token();

        $response = $this->http->get($uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ],
        ]);

        $decoded = json_decode((string) $response->getBody(), true);

        if (!is_array($decoded)) {
            throw new \RuntimeException("EBPS response is not valid JSON array/object for: {$uri}");
        }

        return $decoded;
    }

    public function buildingApprovalReport(): array
    {
        return $this->get('api/Report/BuildingApprovalReport');
    }

    public function sanitationRequestReport(): array
    {
        return $this->get('api/Report/SanitationRequestReport');
    }
}
