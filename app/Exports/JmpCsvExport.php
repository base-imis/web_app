<?php

namespace App\Exports;

use App\User;
use App\Models\Cwis\DataJmp;
use App\Models\Cwis\DataSource;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use DB;


class JmpCsvExport implements FromView, WithEvents, WithColumnWidths, withDrawings
{

    private $year;

    public function __construct(int $year)
    {
        $this->year = $year;
    }
    public function view(): View
    {   
        $years = $this->year;
        $jmp_query = "SELECT * FROM cwis.data_jmp 
        inner join cwis.data_source on cwis.data_jmp.source_id= cwis.data_source.id WHERE year= '$years';";
        $results = DB::select($jmp_query);
        return view('exports.CWIS-JMP', compact('results','years') 
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                // Wraptext
             $event->sheet->getStyle('B')->getAlignment()->setWrapText(true);
          
            //  row height
             $event->sheet->getRowDimension(1)->setRowHeight(75);
             $event->sheet->getRowDimension(2)->setRowHeight(45);
             $event->sheet->getRowDimension(3)->setRowHeight(30);
             $event->sheet->getRowDimension(4)->setRowHeight(15);
            //  cell merge
             $event->sheet->mergeCells('B1:D1');
             $event->sheet->mergeCells('A2:D2');
            // font size
            
            $event->sheet->getDelegate()->getStyle('1')->getFont()->setSize(48);  
            $event->sheet->getDelegate()->getStyle('2')->getFont()->setSize(22);  
            $event->sheet->getDelegate()->getStyle('3')->getFont()->setSize(16);  
            $event->sheet->getDelegate()->getStyle('4')->getFont()->setSize(12); 

            //  set bg colors
            $event->sheet->getStyle('A1:D1')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => '538dd5'],]);
            $event->sheet->getStyle('A2:D2')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'b8cce4'],]);
            $event->sheet->getStyle('A3:D3')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'c5d9f1'],]);
            $event->sheet->getStyle('A4:D4')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'E2ECF8'],]);
                // set alignments
            $event->sheet->getDelegate()->getStyle('A1:D1')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $event->sheet->getDelegate()->getStyle('C2')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);    
            
            $event->sheet->getDelegate()->getStyle('A2:B2')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            // set borders
            $event->sheet->getStyle('A1:D24')->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['argb' => '000000'],
                    ],
                    
                ],
            ]);
            
            }
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 60,
            'C' => 52,
            'D'=> 15,
            'E' => 15
        ];
    }

    public function drawings()
{
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setPath(public_path('/img/logo.png'));
    $drawing->setHeight(90);
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(10);
    return $drawing;
}
   
}