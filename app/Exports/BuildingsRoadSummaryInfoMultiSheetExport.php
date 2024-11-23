<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BuildingsRoadSummaryInfoMultiSheetExport implements WithMultipleSheets
{
    private $bufferPolygonGeom;
    
    /**
     * Constructor method for the SummaryInfoMultiSheetExport class.
     *
     * @param  $bufferPolygonGeom The geometry of the buffered polygon.
     * @return void
     */
    public function __construct($roadCode)
    {
        $this->code = $roadCode;
    }

    /**
     * Returns an array of sheets for the export.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        
            $sheets[] = new BuildingsRoadExport($this->code);
            $sheets[] = new BuildingsRoadListExport($this->code);
            $sheets[] = new ContainmentsRoadListExport($this->code);
        return $sheets;
    }
}