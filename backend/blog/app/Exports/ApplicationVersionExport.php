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

class ApplicationVersionExport implements FromCollection,WithHeadings,WithColumnWidths,WithStyles, WithEvents
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

                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', "Application Report");
                $sheet->mergeCells('A2:D2');
                $sheet->setCellValue('A2', " ");
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                
                $cellRange = 'A1:D1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                // $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
            },
        ];
    }
    
    public function headings(): array
        {
            return [
            ['Application Version'],
            [' '],
            [   
                'Date',
                'Time',
                'Version Number',
                'Summary'
            ]
            ];
            
        }
    
    
    public function columnWidths(): array
        {
            return [
                    'A' => 15,
                    'B' => 15, 
                    'C'=> 20, 
                    'D'=>20,
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
