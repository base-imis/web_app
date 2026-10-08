<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$measuredRuns = 5;
$warmupRuns = 1;
$role = 'Municipality - Super Admin';

$user = User::role($role)
    ->whereNull('deleted_at')
    ->orderBy('id')
    ->first();

if (!$user) {
    fwrite(STDERR, "No active user with role {$role} was found.\n");
    exit(1);
}

Auth::login($user);

$request = Request::create('/dashboard', 'GET');
$router = $app->make('router');
$route = $router->getRoutes()->match($request);
$request->setRouteResolver(fn () => $route);
$app->instance('request', $request);

$currentRouteProperty = new ReflectionProperty($router, 'current');
$currentRouteProperty->setAccessible(true);
$currentRouteProperty->setValue($router, $route);

$currentQueries = null;

DB::listen(function ($query) use (&$currentQueries): void {
    if ($currentQueries === null) {
        return;
    }

    $sql = preg_replace('/\s+/', ' ', trim($query->sql));

    $currentQueries[] = [
        'time_ms' => round((float) $query->time, 3),
        'sql' => mb_substr($sql, 0, 700),
    ];
});

$runs = [];

for ($index = 0; $index < $warmupRuns + $measuredRuns; $index++) {
    $currentQueries = [];
    $startedAt = hrtime(true);

    $response = $app->make(HomeController::class)->index();

    $controllerWallTimeMs = (hrtime(true) - $startedAt) / 1_000_000;
    $rendered = method_exists($response, 'render') ? $response->render() : (string) $response;

    $wallTimeMs = (hrtime(true) - $startedAt) / 1_000_000;
    $queries = $currentQueries;
    $currentQueries = null;

    usort($queries, fn (array $left, array $right): int => $right['time_ms'] <=> $left['time_ms']);

    $result = [
        'run' => $index < $warmupRuns ? 'warmup' : $index - $warmupRuns + 1,
        'controller_wall_ms' => round($controllerWallTimeMs, 3),
        'controller_and_render_wall_ms' => round($wallTimeMs, 3),
        'sql_count' => count($queries),
        'database_time_ms' => round(array_sum(array_column($queries, 'time_ms')), 3),
        'slowest_queries' => array_slice($queries, 0, 5),
        'response_type' => is_object($response) ? get_class($response) : gettype($response),
        'response_bytes' => strlen($rendered),
    ];

    if ($index >= $warmupRuns) {
        $runs[] = $result;
    }
}

$wallTimes = array_column($runs, 'controller_wall_ms');
$renderedWallTimes = array_column($runs, 'controller_and_render_wall_ms');
$databaseTimes = array_column($runs, 'database_time_ms');
$queryCounts = array_column($runs, 'sql_count');

sort($wallTimes);
sort($renderedWallTimes);
sort($databaseTimes);
sort($queryCounts);

$middle = intdiv($measuredRuns, 2);

$output = [
    'captured_at' => now()->toIso8601String(),
    'environment' => [
        'app_env' => config('app.env'),
        'php_version' => PHP_VERSION,
        'laravel_version' => $app->version(),
        'database' => DB::connection()->getDatabaseName(),
        'database_version' => DB::selectOne('select version() as version')->version,
        'cache_driver' => config('cache.default'),
        'session_driver' => config('session.driver'),
        'queue_connection' => config('queue.default'),
    ],
    'request_conditions' => [
        'role' => $role,
        'user_id' => $user->id,
        'service_provider_id' => $user->service_provider_id,
        'treatment_plant_id' => $user->treatment_plant_id,
        'year' => null,
        'filters' => [],
        'database_buffers' => 'warm after one discarded warm-up run',
        'dashboard_cache' => 'not implemented in current dashboard request',
    ],
    'summary' => [
        'measured_runs' => $measuredRuns,
        'controller_wall_ms_min' => min($wallTimes),
        'controller_wall_ms_median' => $wallTimes[$middle],
        'controller_wall_ms_max' => max($wallTimes),
        'controller_and_render_wall_ms_min' => min($renderedWallTimes),
        'controller_and_render_wall_ms_median' => $renderedWallTimes[$middle],
        'controller_and_render_wall_ms_max' => max($renderedWallTimes),
        'database_time_ms_min' => min($databaseTimes),
        'database_time_ms_median' => $databaseTimes[$middle],
        'database_time_ms_max' => max($databaseTimes),
        'sql_count_min' => min($queryCounts),
        'sql_count_median' => $queryCounts[$middle],
        'sql_count_max' => max($queryCounts),
    ],
    'runs' => $runs,
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
