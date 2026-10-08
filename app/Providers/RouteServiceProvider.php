<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        $webLoginLimitResponse = function (Request $request, array $headers) {
            $retryAfter = (int) ($headers['Retry-After'] ?? 60);
            $message = trans('auth.throttle', ['seconds' => $retryAfter]);

            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => $message])
                ->withHeaders($headers);
        };

        $apiLoginLimitResponse = function (Request $request, array $headers) {
            $retryAfter = (int) ($headers['Retry-After'] ?? 60);

            return response()->json([
                'status' => false,
                'message' => trans('auth.throttle', ['seconds' => $retryAfter]),
            ], 429, $headers);
        };

        $loginLimits = function (
            Request $request,
            string $identifierField,
            string $keyPrefix,
            callable $response
        ) {
            $identifier = Str::lower(trim((string) $request->input($identifierField)));
            $identifierHash = hash('sha256', $identifier);
            $ip = $request->ip();

            $identityIp = config('security.login_rate_limit.identity_ip');
            $ipOnly = config('security.login_rate_limit.ip');
            $identityOnly = config('security.login_rate_limit.identity');

            return [
                Limit::perMinutes(
                    max(1, (int) $identityIp['decay_minutes']),
                    max(1, (int) $identityIp['max_attempts'])
                )->by($keyPrefix.'-identity-ip:'.$identifierHash.'|'.$ip)->response($response),
                Limit::perMinutes(
                    max(1, (int) $ipOnly['decay_minutes']),
                    max(1, (int) $ipOnly['max_attempts'])
                )->by($keyPrefix.'-ip:'.$ip)->response($response),
                Limit::perMinutes(
                    max(1, (int) $identityOnly['decay_minutes']),
                    max(1, (int) $identityOnly['max_attempts'])
                )->by($keyPrefix.'-identity:'.$identifierHash)->response($response),
            ];
        };

        RateLimiter::for('web-login', function (Request $request) use ($loginLimits, $webLoginLimitResponse) {
            return $loginLimits($request, 'username', 'web-login', $webLoginLimitResponse);
        });

        RateLimiter::for('api-login', function (Request $request) use ($loginLimits, $apiLoginLimitResponse) {
            return $loginLimits($request, 'email', 'api-login', $apiLoginLimitResponse);
        });

    }
}
