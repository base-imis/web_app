<?php
// Last Modified Date: 12-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class PointBuildingsListExport implements FromView, WithTitle, WithEvents
{
    private $longitude;
    private $latitude;
    private $distance;

     /**
     * BuildingsListExport constructor.
     *
     * @param $longitude
     * @param $latitude
     * @param $distance
     * 
     */
    public function __construct($longitude, $latitude, $distance)
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->distance = $distance;
    }
    
        /**
     * Generates the view for exporting.
     *
     * @return View
     */
    public function view(): View
    {
         if($this->distance > 0){
                $distance = $this->distance;
            } else {
                $distance = 0;
            }
             // Construct the SQL query to fetch building information
               $buildingQuery = "SELECT b.*,  c.id as containment, st.type AS structuretype, fu.name AS functionaluse, ws.source AS watersource, ss.sanitation_system AS sanitationsystem, bo.owner_name, bo.owner_gender, bo.owner_contact FROM building_info.buildings b"
                . " LEFT JOIN building_info.owners bo ON b.bin = bo.bin"
                . " LEFT JOIN building_info.structure_types st ON b.structure_type_id = st.id"
                . " LEFT JOIN building_info.build_contains bc ON b.bin = bc.bin"
                . " LEFT JOIN fsm.containments c ON bc.containment_id = c.id"
                . " LEFT JOIN building_info.functional_uses fu ON b.functional_use_id = fu.id"
                . " LEFT JOIN building_info.sanitation_systems ss ON b.sanitation_system_id = ss.id" 
                . " LEFT JOIN building_info.water_sources ws ON b.water_source_id = ws.id"
                . " WHERE (ST_Intersects(ST_Buffer(ST_SetSRID(ST_Point(" . $this->longitude . "," . $this->latitude . "),4326)::GEOGRAPHY, " . $distance . ")::GEOMETRY, b.geom))"
                . " AND ss.map_display is true"
                . " AND b.deleted_at is null";

        
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