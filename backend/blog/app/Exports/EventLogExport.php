<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class EventLogExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $query;
    
    public function __construct($query){
        $this->query = $query;
    }
    
    public function collection()
    {
        return $this->query;
    }
    
    public function headings() :array
    {
        return [
            ['EVENT LOGS'],
            [''],
            ['DATE',
            'TIME',
            'USER',
            'EVENT NAME',
            'EVENT DETAILS',
            ]
        ];
    }
    
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            3    => ['font' => ['bold' => true]]
        ];
    }
    
    
    public function registerEvents(): array {
           
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', "EVENT LOGS");
                $sheet->mergeCells('A2:E2');

                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
              
                $event->sheet->getDelegate()->getStyle('A1:E1')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A3:E3')->applyFromArray($styleArray);
            },
        ];
    }
    
    
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 25,
            'D' => 25,
            'E' => 50,
        ];
    }
}