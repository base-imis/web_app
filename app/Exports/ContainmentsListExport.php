<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class ContainmentsListExport implements FromView, WithTitle, WithEvents
{
    private $bufferPolygonGeom; 
    private $bufferPolygonDistance; 
    
     /**
     * ContainmentsExport constructor.
     *
     * @param $bufferPolygonGeom
     * @param $bufferPolygonDistance
     */
    public function __construct($bufferPolygonGeom, $bufferPolygonDistance)
    {
        $this->geom = $bufferPolygonGeom;
        $this->distance = $bufferPolygonDistance;
    }

     /**
     * Generates the view for exporting.
     *
     * @return View
     */
    public function view(): View
    {
      
         if($this->distance > 0){
                $bufferDisancePolygon = $this->distance;
            } else {
                $bufferDisancePolygon = 0;
            }
       
            $containmentQuery = "SELECT c.* ,ct.type as type, b.bin as bin FROM fsm.containments c "
            . "LEFT JOIN building_info.build_contains bc ON bc.containment_id = c.id "
            . "LEFT JOIN building_info.buildings b ON b.bin = bc.bin "
	        . "LEFT JOIN fsm.containment_types ct ON ct.id = c.type_id "
            . "WHERE (ST_Intersects(ST_Buffer(ST_GeomFromText('" . $this->geom . "', 4326)::GEOGRAPHY, " . $bufferDisancePolygon . ")::GEOMETRY, b.geom)) "
            . "AND b.deleted_at is null AND c.deleted_at is null and c.id is not null "
            . "GROUP BY c.id, b.bin, ct.type";
            $containmentResults = DB::select($containmentQuery);

        return view('exports.containments-list', compact('containmentResults'));
    }

    /**
     * registerEvents method: Register events for styling Excel sheet after generation.
     *
     * @return array An array of events to be registered.
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