<?php

namespace Tests\Unit;

use App\Http\Controllers\BuildingSearchController;
use App\Http\Controllers\Cwis\CwisMneController;
use App\Http\Controllers\MapsController;
use App\Services\Maps\MapsService;
use App\Support\GeometryValue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\TestCase;

/** Isolated tests: do not bootstrap .env, open a database, or mutate fixtures. */
class SqlInjectionRemediationTest extends TestCase
{
    private $container;
    private $capsule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        Container::setInstance($this->container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->container);
        if (!class_exists('DB')) {
            class_alias(DB::class, 'DB');
        }
        $translator = new Translator(new ArrayLoader(), 'en');
        $this->container->instance('translator', $translator);
        $this->container->instance('validator', new Factory($translator, $this->container));
        $this->container->alias('validator', \Illuminate\Contracts\Validation\Factory::class);
        Request::macro('validate', function (array $rules) {
            return validator($this->all(), $rules)->validate();
        });
        $this->capsule = new Capsule($this->container);
        $this->capsule->addConnection(['driver' => 'pgsql', 'database' => 'unused', 'host' => '127.0.0.1', 'port' => 1]);
        $this->capsule->bootEloquent();
        $this->container->instance('db', $this->capsule->getDatabaseManager());
        $response = Mockery::mock(ResponseFactory::class);
        $response->shouldReceive('json')->andReturnUsing(function ($data) { return new JsonResponse($data); });
        $this->container->instance(ResponseFactory::class, $response);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        parent::tearDown();
    }

    private function controller(string $class)
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    public function test_cwis_valid_year_is_bound_and_response_is_preserved(): void
    {
        DB::shouldReceive('select')->once()
            ->with('select * from insert_data_into_cwis_table(?);', ['2025'])
            ->andReturn([(object) ['result' => 1]]);
        $response = $this->controller(CwisMneController::class)->cwis('2025');
        $this->assertSame('[{"result":1}]', $response->getContent());
    }

    /** @dataProvider invalidYears */
    public function test_cwis_invalid_year_never_reaches_sql($year): void
    {
        DB::shouldReceive('select')->never();
        $this->expectException(ValidationException::class);
        $this->controller(CwisMneController::class)->cwis($year);
    }

    public static function invalidYears(): array
    {
        return [[null], [''], ['2025 OR 1=1'], ['CAST((SELECT 1) AS integer)'], [['2025']], ['2025.5']];
    }

    /** @dataProvider searches */
    public function test_building_search_keeps_input_out_of_sql($method, $column): void
    {
        $value = "O'Reilly%_'; SELECT 1 --";
        $queries = $this->capsule->getConnection()->pretend(function () use ($method, $value) {
            $response = $this->controller(BuildingSearchController::class)->$method($value);
            $this->assertSame(200, $response->getData()->status);
            $this->assertSame([], $response->getData()->data);
        });
        $this->assertCount(1, $queries);
        $this->assertStringContainsString($column . ' ILIKE ?', $queries[0]['query']);
        $this->assertStringContainsString('limit 10', $queries[0]['query']);
        $this->assertStringNotContainsString($value, $queries[0]['query']);
        $this->assertSame([$value . '%'], $queries[0]['bindings']);
    }

    public static function searches(): array
    {
        return [
            ['getBuildingBin', 'bin'], ['getBuildingRoadcode', 'road_code'],
            ['getBuildingHouseNumber', 'house_number'], ['getSewerCode', 'sewer_code'],
            ['getBinOfPreconnectedBuilding', 'house_number'], ['getSanitationSystem', 'sanitation_system'],
        ];
    }

    public function test_extent_rejects_unapproved_identifier_before_query(): void
    {
        DB::shouldReceive('select')->never();
        $this->expectException(ValidationException::class);
        (new MapsService())->buildingExtent('bin OR 1=1 --', 'B1');
    }

    /** @dataProvider autocompleteLayers */
    public function test_autocomplete_binds_keywords_without_changing_filters($layer, $column): void
    {
        $value = "O'Reilly%_";
        $this->container->instance('request', Request::create('/', 'GET', ['layer' => $layer, 'keywords' => $value]));
        DB::shouldReceive('select')->once()->withArgs(function ($sql, $bindings) use ($value) {
            return strpos($sql, $value) === false
                && strpos($sql, 'deleted_at is null') !== false
                && strpos($sql, 'geom IS NOT NULL LIMIT 10') !== false
                && $bindings === ['%' . $value . '%'];
        })->andReturn([(object) [$column => 'match']]);
        $this->assertSame(['match'], $this->controller(MapsController::class)->searchAutoComplete());
    }

    public static function autocompleteLayers(): array
    {
        return [['places_layer', 'name'], ['roadlines_layer', 'name'], ['house_number', 'house_number'], ['bin', 'bin']];
    }

    public function test_kpi_rejects_sql_year_before_database_access(): void
    {
        DB::shouldReceive('table')->never();
        $this->expectException(ValidationException::class);
        $this->controller(\App\Http\Controllers\Fsm\KpiDashboardController::class)->data('2025 OR 1=1', null);
    }

    public function test_geometry_parse_error_becomes_validation_error_without_sql_text(): void
    {
        // PDO errorInfo is what PostgreSQL provides for a geometry parse failure.
        DB::swap($database = Mockery::mock());
        $previous = new \PDOException('parse error');
        $previous->errorInfo = ['XX000', 7, 'parse error'];
        $database->shouldReceive('transaction')->once()->andThrow(
            new \Illuminate\Database\QueryException('SELECT private SQL', [], $previous)
        );
        try {
            GeometryValue::fromWkt('invalid');
            $this->fail('Invalid geometry was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame(['geom' => ['Invalid geometry.']], $exception->errors());
        }
    }

    public function test_extent_values_are_bound_and_coordinates_preserved(): void
    {
        $value = "B1' OR '1'='1";
        DB::shouldReceive('select')->times(6)->withArgs(function ($sql, $bindings) use ($value) {
            return strpos($sql, $value) === false && substr($sql, -7) === 'bin = ?' && $bindings === [$value];
        })->andReturn([(object) ['st_xmin' => 1, 'st_ymin' => 2, 'st_xmax' => 3, 'st_ymax' => 4, 'lat' => 5, 'long' => 6]]);
        $this->assertSame(['xmin' => 1, 'ymin' => 2, 'xmax' => 3, 'ymax' => 4, 'lat' => 5, 'long' => 6],
            (new MapsService())->buildingExtent('bin', $value));
    }

    public function test_geometry_conversion_binds_wkt_and_returns_model_value(): void
    {
        $wkt = 'LINESTRING(0 0,1 1)';
        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
        DB::shouldReceive('selectOne')->once()->withArgs(function ($sql, $bindings) use ($wkt) {
            return strpos($sql, $wkt) === false && strpos($sql, 'ST_Multi(ST_GeomFromText(?, ?))') !== false && $bindings === [$wkt, 4326];
        })->andReturn((object) ['value' => '01050000', 'valid' => true, 'empty' => false, 'type' => 'MULTILINESTRING']);
        $this->assertSame('01050000', GeometryValue::fromWkt($wkt, 4326, true, ['MULTILINESTRING']));
    }

    public function test_geometry_rejects_wrong_type(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
        DB::shouldReceive('selectOne')->once()->andReturn((object) [
            'value' => '00', 'valid' => true, 'empty' => false, 'type' => 'MULTIPOINT',
        ]);
        $this->expectException(ValidationException::class);
        GeometryValue::fromWkt('POINT(1 1)', 4326, true, ['MULTILINESTRING']);
    }
}
