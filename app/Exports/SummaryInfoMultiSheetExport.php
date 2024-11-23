<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SummaryInfoMultiSheetExport implements WithMultipleSheets
{
    private $bufferPolygonGeom;
    private $bufferPolygonDistance;

    /**
     * Constructor method for the SummaryInfoMultiSheetExport class.
     *
     * @param  $bufferPolygonGeom The geometry of the buffered polygon.
     * @param  $bufferPolygonDistance The distance used for buffering the polygon.
     * @return void
     */

    public function __construct($bufferPolygonGeom, $bufferPolygonDistance)
    {
        $this->geom = $bufferPolygonGeom;
        $this->distance = $bufferPolygonDistance;
    }

    /**
     * Returns an array of sheets for the export.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        
            $sheets[] = new BuildingsExport($this->geom, $this->distance);
            $sheets[] = new BuildingsListExport($this->geom, $this->distance);
            $sheets[] = new ContainmentsListExport($this->geom, $this->distance);
            
        return $sheets;
    }
}