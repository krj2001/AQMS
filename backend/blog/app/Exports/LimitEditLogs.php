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



class LimitEditLogsExport implements FromCollection,WithHeadings,WithColumnWidths,WithStyles, WithEvents
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

                $sheet->mergeCells('A1:Q1');
                $sheet->setCellValue('A1', "Limit edit logs");
                $sheet->mergeCells('A2:Q2');
                $sheet->setCellValue('A2', " ");
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                
                $cellRange = 'A1:K1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                // $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
            },
        ];
    }
    
    public function headings(): array
    {
        return [
            ['Limit edit logs'],
            [' '],
            [
            'Date',
            'Time',
            'Email',
            'State Name',
            'Branch Name',
            'Facility Name',
            'Building Name',
            'Floor Name',
            'Zone',
            'Device Name',
            'Sensor Tag',
            'Critical Min Value',
            'Critical Max Value',
            'Warning Min Value',
            'Warning Max Value',
            'Out of Range Min Value',
            'Out Of Range Max Value'
            ]
        ];
    }
    
        public function columnWidths(): array
        {
            return [
                'A' => 15,
                'B' => 15, 
                'C'=> 30, 
                'D'=>17,
                'E'=>17,
                'F'=>17,
                'G'=>17,
                "H"=>20,
                "I"=>17,
                "J"=>20,
                "K"=>20,
                "L"=>27,
                "M"=>27,
                "N"=>27,
                "O"=>27,
                "P"=>27,
                "Q"=>27
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
