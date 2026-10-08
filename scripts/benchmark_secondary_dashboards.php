<?php

declare(strict_types=1);

use App\Models\User;
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

$dashboards = [
    'building' => [
        'controller' => App\Http\Controllers\BuildingInfo\BuildingDashboardController::class,
        'route' => 'buildingdashboard',
        'uri' => '/building-info/buildings/buildingdashboard',
    ],
    'fsm' => [
        'controller' => App\Http\Controllers\Fsm\FsmDashboardController::class,
        'route' => 'fsmdashboard',
        'uri' => '/fsm/fsmdashboard',
    ],
    'utility' => [
        'controller' => App\Http\Controllers\UtilityInfo\UtilityDashboardController::class,
        'route' => 'utilitydashboard',
        'uri' => '/utilityinfo/utilitydashboard',
    ],
];

$currentQueries = null;
DB::listen(function ($query) use (&$currentQueries): void {
    if ($currentQueries !== null) {
        $currentQueries[] = [
            'time_ms' => round((float) $query->time, 3),
            'sql' => mb_substr(preg_replace('/\s+/', ' ', trim($query->sql)), 0, 600),
        ];
    }
});

$bindRequest = function (array $dashboard) use ($app, $user): Request {
    $request = Request::create($dashboard['uri'], 'GET');
    $request->setUserResolver(fn () => $user);
    $route = $app->make('router')->getRoutes()->getByName($dashboard['route']);
    $route->bind($request);
    $request->setRouteResolver(fn () => $route);

    $router = $app->make('router');
    $currentRoute = new ReflectionProperty($router, 'current');
    $currentRoute->setAccessible(true);
    $currentRoute->setValue($router, $route);
    $app->instance('request', $request);

    return $request;
};

$results = [];
foreach ($dashboards as $name => $dashboard) {
    $controller = $app->make($dashboard['controller']);
    $runs = [];

    for ($index = 0; $index < 4; $index++) {
        $bindRequest($dashboard);
        $currentQueries = [];
        $startedAt = hrtime(true);
        $response = $controller->index();
        $controllerMs = (hrtime(true) - $startedAt) / 1_000_000;
        $html = $response->render();
        $completeMs = (hrtime(true) - $startedAt) / 1_000_000;
        $queries = $currentQueries;
        $currentQueries = null;
        usort($queries, fn (array $a, array $b): int => $b['time_ms'] <=> $a['time_ms']);

        if ($index > 0) {
            $runs[] = [
                'run' => $index,
                'controller_ms' => round($controllerMs, 3),
                'controller_and_render_ms' => round($completeMs, 3),
                'sql_count' => count($queries),
                'database_ms' => round(array_sum(array_column($queries, 'time_ms')), 3),
                'response_bytes' => strlen($html),
                'slowest_queries' => array_slice($queries, 0, 5),
            ];
        }
    }

    $median = function (string $field) use ($runs) {
        $values = array_column($runs, $field);
        sort($values);
        return $values[1];
    };

    $results[$name] = [
        'median_controller_ms' => $median('controller_ms'),
        'median_controller_and_render_ms' => $median('controller_and_render_ms'),
        'median_sql_count' => $median('sql_count'),
        'median_database_ms' => $median('database_ms'),
        'runs' => $runs,
    ];
}

echo json_encode([
    'captured_at' => now()->toIso8601String(),
    'role' => 'Municipality - Super Admin',
    'user_id' => $user->id,
    'year' => null,
    'dashboards' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
