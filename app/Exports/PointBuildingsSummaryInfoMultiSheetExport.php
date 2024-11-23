<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PointBuildingsSummaryInfoMultiSheetExport implements WithMultipleSheets
{
    private $longitude;
    private $latitude;
    private $distance;

     /**
     * Constructor method for the PointBuildingsSummaryInfoMultiSheetExport class.
     *
     * @param  $longitude longitude of point.
     * @param  $latitude lattitude of point.
     * @param  $distance The distance used for buffering the point.
     * 
     * @return void
     */
    public function __construct($longitude, $latitude, $distance)
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->distance = $distance;
    }

    /**
     * Returns an array of sheets for the export.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        
            $sheets[] = new PointBuildingsExport($this->longitude, $this->latitude, $this->distance);
            $sheets[] = new PointBuildingsListExport($this->longitude, $this->latitude, $this->distance);
            //$sheets[] = new PointContainmentsExport($this->longitude, $this->latitude, $this->distance);
            $sheets[] = new PointContainmentsListExport($this->longitude, $this->latitude, $this->distance);
        
        return $sheets;
    }
}