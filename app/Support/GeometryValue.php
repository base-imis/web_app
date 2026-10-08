<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Convert untrusted WKT to a bound, database-generated EWKB value for model saves. */
class GeometryValue
{
    public static function fromWkt($wkt, ?int $srid = 4326, bool $multi = true, array $types = []): string
    {
        validator(['geom' => $wkt], ['geom' => ['required', 'string']])->validate();
        $expression = $srid === null ? 'ST_GeomFromText(?)' : 'ST_GeomFromText(?, ?)';
        $bindings = $srid === null ? [$wkt] : [$wkt, $srid];
        if ($multi) {
            $expression = 'ST_Multi(' . $expression . ')';
        }

        try {
            // A nested transaction/savepoint also clears a PostgreSQL parse error
            // before callers handle the validation failure in their transaction.
            $geometry = DB::transaction(function () use ($expression, $bindings) {
                return DB::selectOne(
                    "SELECT encode(ST_AsEWKB(g), 'hex') AS value, ST_IsValid(g) AS valid, " .
                    "ST_IsEmpty(g) AS empty, GeometryType(g) AS type FROM (SELECT {$expression} AS g) AS parsed",
                    $bindings
                );
            });
        } catch (QueryException $exception) {
            $state = $exception->errorInfo[0] ?? '';
            if (substr($state, 0, 2) !== '22' && $state !== 'XX000') {
                throw $exception;
            }
            throw ValidationException::withMessages(['geom' => 'Invalid geometry.']);
        }

        if (!$geometry || !$geometry->valid || $geometry->empty ||
            ($types && !in_array($geometry->type, $types, true))) {
            throw ValidationException::withMessages(['geom' => 'Invalid geometry type or shape.']);
        }

        // Eloquent binds this value on save, retaining events and revision tracking.
        return $geometry->value;
    }
}
