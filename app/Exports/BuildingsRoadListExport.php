<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class BuildingsRoadListExport implements FromView, WithTitle, WithEvents
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
     // Construct the query to select buildings based on road codes
    $building_query = "SELECT bin, ST_AsText(geom) AS geom"
                        . " FROM building_info.buildings"
                        . " WHERE road_code IN (" . implode(',', $roadCodes) . ")";
    $results = DB::select($building_query);
    
   
 // Construct a more detailed query to select building information including joins
        $buildingQuery = "SELECT b.*,c.id as containment, st.type AS structuretype, fu.name AS functionaluse, ws.source AS watersource, ss.sanitation_system AS sanitationsystem, bo.owner_name, bo.owner_gender, bo.owner_contact FROM building_info.buildings b"
                . " LEFT JOIN building_info.owners bo ON b.bin = bo.bin"
                . " LEFT JOIN building_info.structure_types st ON b.structure_type_id = st.id"
                . " LEFT JOIN building_info.functional_uses fu ON b.functional_use_id = fu.id"
                . " LEFT JOIN building_info.build_contains bc ON b.bin = bc.bin"
                . " LEFT JOIN fsm.containments c ON bc.containment_id = c.id"
                . " LEFT JOIN building_info.sanitation_systems ss ON b.sanitation_system_id = ss.id" 
                . " LEFT JOIN building_info.water_sources ws ON b.water_source_id = ws.id"
                . " WHERE b.road_code IN (" . implode(',', $roadCodes) . ")"
                . " AND b.deleted_at IS NULL"
                . " ORDER BY b.bin ASC";
        
        $buildingResults = DB::select($buildingQuery);
   
         
        return view('exports.buildings-list', compact('buildingResults'));
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
        return 'Buildings List';
    }
}