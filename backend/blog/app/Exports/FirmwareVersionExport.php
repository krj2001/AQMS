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


class FirmwareVersionExport implements FromCollection,WithHeadings,WithColumnWidths,WithStyles, WithEvents
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

                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', "Firmware Version Report");
                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', " ");
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                
                $styleArray1 = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ];
                
                $cellRange = 'A1:L1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A3:L3')->applyFromArray($styleArray);
                
                $event->sheet->getDelegate()->getStyle('J4:J200')->applyFromArray($styleArray1);

            },
        ];
    }
    
    
    
    

    public function headings(): array
    {
        return [
                ['Firmware Version'],
                [' '],
                [
                    'Date',
                    'Time',
                    'State',
                    'Branch ',
                    'Facility ',
                    'Building ',
                    'Floor ',
                    'Zone',
                    'Device Name',
                    'Firmware Version',
                    'User',
                    'Status',
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
                    'E'=> 20, 
                    'F'=>20,
                    'G'=>20,
                    'H'=>20,
                    'I'=>20,
                    'J'=>20,
                    'K'=> 22,
                    'L' => 20
                ];
        }
    
    public function styles(Worksheet $sheet)
        {
            
            
            return [
                'A1'    => ['font' => ['bold' => true, 'size' => 12]],
                
                // Style the first row as bold text.
                3    => ['font' => ['bold' => true, 'size' => 12]],
    
                // Styling a specific cell by coordinate.
                // 'B2' => ['font' => ['italic' => true]],
    
                // Styling an entire column.
                // 'A'  => ['font' => ['bold' => true]],
            ];
        }
}
