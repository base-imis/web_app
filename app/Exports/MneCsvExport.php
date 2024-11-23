<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Cwis\DataJmp;
use App\Models\Cwis\DataSource;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use DB;


class MneCsvExport implements FromView, WithEvents, WithColumnWidths, ShouldAutoSize
{
    private $year;

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function view(): View
    {
        $years = $this->year;
    
        // Use LEFT JOIN to include all records from data_source even if data_value is null
        $jmp_query = "
            SELECT 
                ds.outcome, 
                ds.indicator_code, 
                ds.label, 
                dc.year, 
                dc.data_value 
            FROM 
                cwis.data_source ds
            LEFT JOIN 
                cwis.data_cwis dc ON dc.indicator_code = ds.indicator_code AND dc.year = ?
            WHERE 
                ds.indicator_code IS NOT NULL
        ";
    
        // Use parameter binding to prevent SQL injection
        $results = DB::select($jmp_query, [$years]);
   
        return view('exports.CWIS-MNE', compact('results', 'years'));
    }
    

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                // Wraptext
                $event->sheet->getStyle('B')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('C')->getAlignment()->setWrapText(true);

                //  row height
                $event->sheet->getRowDimension(1)->setRowHeight(40);
                $event->sheet->getRowDimension(2)->setRowHeight(25);
                $event->sheet->getRowDimension(3)->setRowHeight(20);
                $event->sheet->getRowDimension(4)->setRowHeight(25);
                // $event->sheet->getRowDimension(3)->setRowHeight(30);
                // $event->sheet->getRowDimension(4)->setRowHeight(15);
                //  cell merge
                $event->sheet->mergeCells('A1:C1');
                 $event->sheet->mergeCells('A2:C2');

                // $event->sheet->mergeCells('A2:D2');
                // font size
                $event->sheet->getDelegate()->getStyle('1')->getFont()->setSize(20);
                $event->sheet->getDelegate()->getStyle('2')->getFont()->setSize(22);
                $event->sheet->getDelegate()->getStyle('3')->getFont()->setSize(15);
                $event->sheet->getDelegate()->getStyle('4')->getFont()->setSize(15);


                // $event->sheet->getDelegate()->getStyle('3')->getFont()->setSize(16);
                // $event->sheet->getDelegate()->getStyle('4')->getFont()->setSize(12);


                $event->sheet->getStyle('A1:C1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => '538dd5'],]);
                $event->sheet->getStyle('A2:C2')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'b8cce4'],]);
                $event->sheet->getStyle('A3:C3')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'FFFFFF'],]);

                // $event->sheet->getStyle('A3:D3')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'c5d9f1'],]);
                // $event->sheet->getStyle('A4:D4')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'E2ECF8'],]);

                // SET ALIGN
                $event->sheet->getDelegate()->getStyle('A1:C1')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getDelegate()->getStyle('A2:C2')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A2:C2')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getDelegate()->getStyle('A3:C3')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getDelegate()->getStyle('A4:C4')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                // $event->sheet->getDelegate()->getStyle('D3')
                //     ->getAlignment()
                //     ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                // $event->sheet->getDelegate()->getStyle('A3:B3')
                //     ->getAlignment()
                //     ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                // set borders
                $event->sheet->getStyle('A1:C25')->applyFromArray([
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
            'A' => 50,
            'B' => 20,
            'C' => 18,
        ];
    }

    public function drawings()

    {

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/img/logo-imis.jpg'));
        $drawing->setHeight(100);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(100);
        return $drawing;
    }
}
