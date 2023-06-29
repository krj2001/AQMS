<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use \Maatwebsite\Excel\Sheet;


class serverUtilizationReportExport implements FromCollection,WithHeadings,WithColumnWidths,WithStyles, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $query;
    public function __construct($query){
        $this->query = $query;
      
    }

    public function collection()
    {
        return $this->query->get();
    }
        
        
    public function registerEvents(): array {
        
    return [
        AfterSheet::class => function(AfterSheet $event) {
            /** @var Sheet $sheet */
            $sheet = $event->sheet;

            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', "SERVER UTILIZATION");
            $sheet->mergeCells('A2:E2');
            $sheet->setCellValue('A2', " ");
            $styleArray = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ];
            
            $cellRange = 'A1:E1'; // All headers
            $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
            // $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
        },
    ];
    }
    

    public function headings(): array
    {
        return [
            ['Server Utilization'],
            [' '],
            [
            'DATE',
            'TIME',
            'PHYSICAL MEMORY ( AVG RAM % )',
            'DISK USAGE',
            'AVG CPU %'
            ]
        ];
    }
    
    
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 13, 
            'C'=> 40, 
            'D'=>17,
            'E'=>17
        ];
    }

    
    
    public function styles(Worksheet $sheet)
    {
        
        return [
            // Style the first row as bold text.
            3    => ['font' => ['bold' => true, 'size' => 12]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            'A'  => ['font' => ['bold' => true]],
        ];
    }
}
