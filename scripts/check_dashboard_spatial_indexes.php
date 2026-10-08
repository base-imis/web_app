<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$indexes = DB::select("
    SELECT schemaname, tablename, indexname, indexdef
    FROM pg_indexes
    WHERE schemaname IN ('layer_info', 'utility_info')
      AND tablename IN ('wards', 'roads', 'sewers', 'drains', 'water_supplys')
    ORDER BY tablename, indexname
");

$geometry = DB::select("
    SELECT f_table_schema, f_table_name, f_geometry_column, srid, type
    FROM geometry_columns
    WHERE f_table_schema IN ('layer_info', 'utility_info')
      AND f_table_name IN ('wards', 'roads', 'sewers', 'drains', 'water_supplys')
    ORDER BY f_table_name
");

echo json_encode([
    'geometry_columns' => $geometry,
    'indexes' => $indexes,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
