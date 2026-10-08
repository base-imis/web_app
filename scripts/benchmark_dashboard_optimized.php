<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$user = User::role('Municipality - Super Admin')
    ->whereNull('deleted_at')
    ->orderBy('id')
    ->firstOrFail();

Auth::login($user);

$controller = $app->make(HomeController::class);
$dashboardService = $app->make(DashboardService::class);
$currentQueries = null;

DB::listen(function ($query) use (&$currentQueries): void {
    if ($currentQueries !== null) {
        $currentQueries[] = [
            'time_ms' => round((float) $query->time, 3),
            'sql' => mb_substr(preg_replace('/\s+/', ' ', trim($query->sql)), 0, 500),
        ];
    }
});

$bindRequest = function (string $uri, string $routeName) use ($app, $user): Request {
    $request = Request::create($uri, 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->setUserResolver(fn () => $user);
    $route = $app->make('router')->getRoutes()->getByName($routeName);
    $route->bind($request);
    $request->setRouteResolver(fn () => $route);
    $router = $app->make('router');
    $currentRouteProperty = new ReflectionProperty($router, 'current');
    $currentRouteProperty->setAccessible(true);
    $currentRouteProperty->setValue($router, $route);
    $app->instance('request', $request);
    return $request;
};

$measure = function (callable $action) use (&$currentQueries): array {
    $currentQueries = [];
    $startedAt = hrtime(true);
    $response = $action();
    $body = method_exists($response, 'getContent')
        ? $response->getContent()
        : (method_exists($response, 'render') ? $response->render() : (string) $response);
    $wallMs = (hrtime(true) - $startedAt) / 1_000_000;
    $queries = $currentQueries;
    $currentQueries = null;
    usort($queries, fn (array $a, array $b): int => $b['time_ms'] <=> $a['time_ms']);

    return [
        'wall_ms' => round($wallMs, 3),
        'sql_count' => count($queries),
        'database_time_ms' => round(array_sum(array_column($queries, 'time_ms')), 3),
        'response_bytes' => strlen($body),
        'slowest_queries' => array_slice($queries, 0, 5),
    ];
};

$shellRuns = [];
for ($i = 0; $i < 5; $i++) {
    $bindRequest('/dashboard', 'home');
    $shellRuns[] = $measure(fn () => $controller->index());
}

$dashboardService->invalidateDashboardCache();
$contentRequest = $bindRequest('/dashboard/content', 'dashboard.content');
$coldContent = $measure(fn () => $controller->content($contentRequest));

$warmContentRuns = [];
for ($i = 0; $i < 5; $i++) {
    $contentRequest = $bindRequest('/dashboard/content', 'dashboard.content');
    $warmContentRuns[] = $measure(fn () => $controller->content($contentRequest));
}

$median = function (array $runs, string $field) {
    $values = array_column($runs, $field);
    sort($values);
    return $values[intdiv(count($values), 2)];
};

echo json_encode([
    'captured_at' => now()->toIso8601String(),
    'conditions' => [
        'role' => 'Municipality - Super Admin',
        'user_id' => $user->id,
        'service_provider_id' => $user->service_provider_id,
        'treatment_plant_id' => $user->treatment_plant_id,
        'cache_driver' => config('cache.default'),
        'cache_ttl_seconds' => $dashboardService->dashboardCacheTtl(),
    ],
    'shell' => [
        'median_wall_ms' => $median($shellRuns, 'wall_ms'),
        'median_sql_count' => $median($shellRuns, 'sql_count'),
        'runs' => $shellRuns,
    ],
    'content_cold_cache' => $coldContent,
    'content_warm_cache' => [
        'median_wall_ms' => $median($warmContentRuns, 'wall_ms'),
        'median_sql_count' => $median($warmContentRuns, 'sql_count'),
        'runs' => $warmContentRuns,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
