<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class CalibrationResultExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
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
            'DEVICE TAG',
            'SENSOR TAG',
            'SENSOR NAME',
            'CALIBRATION DUE DATE',
            'CALIBRATED DATE',
            'CALIBRATED TEST RESULT',
            'NEXT CALIBRATION DUE DATE',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]]
        ];
    }
    
    public function registerEvents(): array {
           
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
              
                $event->sheet->getDelegate()->getStyle('A1:G1')->applyFromArray($styleArray);
            },
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 18,
            'D' => 24,
            'E' => 20,
            'F' => 24,
            'G' => 28,
        ];
    }
}