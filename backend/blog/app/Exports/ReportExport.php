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


class ReportExport implements FromCollection,WithHeadings,WithColumnWidths,WithStyles, WithEvents
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

                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', "Alarm Report");
                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', " ");
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                
                $cellRange = 'A1:M1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A3:M3')->applyFromArray($styleArray);
            },
        ];
    }
    
    public function headings(): array
        {
            // return [
            //     ['Alarm Report'],
            //     [' '],
            //     ['Alarm Date',
            //     'Alarm Time',
            //     'Device Name',
            //      'Location',
            //      'Branch Name',
            //      'Facility Name',
            //      'Building Name',
            //      'Floor Name',
            //      'Zone',
            //      'Sensor Tag',
            //      'Alert Type',
            //      'Message',
            //      'Reason',
            //      'Duration']
            // ];
            
            return [
                ['Alarm Report'],
                [' '],
                ['From Date & Time',
                 'To Date & Time',
                 'Location ',
                 'Branch ',
                 'Facility ',
                 'Building ',
                 'Floor ',
                 'Zone',
                 'Devices ',
                 'Sensor Tag',
                 'Alert Type',
                 'Message',
                 'Reason',]
            ];
        }
    
    public function columnWidths(): array
        {
            return [
                'A' => 22,
                'B' => 20, 
                'C'=> 20, 
                'D'=>15,
                'E'=>15,
                'F'=>15,
                'G'=>20,
                'H'=>17,
                'I'=>20,
                'J'=>24,
                'K'=>21,
                'L'=>30,
                'M'=>27,
                'N'=>24,
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
                'A1'  => ['font' => ['bold' => true, 'size' => 14]],
            ];
        }
}
