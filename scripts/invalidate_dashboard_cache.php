<?php

declare(strict_types=1);

use App\Services\DashboardService;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$app->make(DashboardService::class)->invalidateDashboardCache();

echo "Dashboard cache namespace invalidated." . PHP_EOL;
