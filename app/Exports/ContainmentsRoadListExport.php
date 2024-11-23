<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class ContainmentsRoadListExport implements FromView, WithTitle, WithEvents
{
    private $codes;
    
      /**
     * BuildingsExport constructor.
     *
     * @param $codes
     */
    public function __construct($codes)
    {
        $this->codes = $codes;
    }

      /**
     * Generates the view for exporting.
     *
     * @return View
     */
    public function view(): View
    {
    $roadCodesStr = $this->codes;
    
    $roadCodes = explode (",", $roadCodesStr); 
    $roadCodes = array_map(function($value) { return "'" . $value . "'"; }, $roadCodes);
    
    // Construct SQL query to select buildings based on road codes
    $building_query = "SELECT bin, ST_AsText(geom) AS geom"
                        . " FROM building_info.buildings"
                        . " WHERE road_code IN (" . implode(',', $roadCodes) . ")";
    $results = DB::select($building_query);
    


         // Construct SQL query to select containments associated with the buildings
              $containmentQuery = "SELECT c.*, ct.type as type, b.bin as bin  FROM fsm.containments c "
            . "LEFT JOIN building_info.build_contains bc ON bc.containment_id = c.id "
	        . "LEFT JOIN fsm.containment_types ct ON ct.id = c.type_id "
            . "LEFT JOIN building_info.buildings b ON b.bin = bc.bin "
                . " WHERE b.road_code IN (" . implode(',', $roadCodes) . ")"
                . " GROUP BY c.id, b.bin, ct.type";

            $containmentResults = DB::select($containmentQuery);
         
    
        return view('exports.containments-list', compact('containmentResults'));
    }

     /**
     * Registers events for the export.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            }
        ];
    }

     /**
     * @return string
     */
    public function title(): string
    {
        return 'Containment List';
    }
}