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


class sensorStatusReportExport implements FromCollection,WithHeadings, WithEvents,WithStyles,WithColumnWidths
{
   
    protected $query;
    protected $headerData;
    protected $locationDetail;
    
    public function __construct($query, $locationDetail){
        $this->query = $query;
        $this->locationDetail = $locationDetail;
    }  


    public function collection()
    {
        //return collect($this->query['data']);
        
        $a= $this->query['data'];
            
        $rowColumn = array();
        $excelData = array();
        
        return collect($a);
    }
    
    
    public function collection1($locDetail)
    {
        return $this->locationDetail[$locDetail];
    }

    
 
    public function registerEvents(): array {
        
        return [
            AfterSheet::class => function(AfterSheet $event) {
                /** @var Sheet $sheet */
                $sheet = $event->sheet;
                $sheet->mergeCells('A1:X1');
                $sheet->setCellValue('A1', "Sensor Status Report");
                $sheet->mergeCells('A2:X2');
                $sheet->setCellValue('A2', " ");
                $sheet->setCellValue('A3', "Location");
                $sheet->mergeCells('B3:C3');
                $sheet->setCellValue('B3', $this->location());
                $sheet->setCellValue('A4', "Branch");
                $sheet->mergeCells('B4:C4');
                $sheet->setCellValue('B4', $this->branch());
                $sheet->setCellValue('A5', "Facility");
                $sheet->mergeCells('B5:C5');
                $sheet->setCellValue('B5', $this->facility());
                $sheet->setCellValue('A6', "Builiding");
                $sheet->mergeCells('B6:C6');
                $sheet->setCellValue('B6', $this->building());
                $sheet->setCellValue('A7', "Floor");
                $sheet->mergeCells('B7:C7');
                $sheet->setCellValue('B7', $this->floor());
                $sheet->setCellValue('A8', "Zone");
                $sheet->mergeCells('B8:C8');
                $sheet->setCellValue('B8', $this->zone());
                $sheet->setCellValue('A9', "Device");
                $sheet->mergeCells('B9:C9');
                $sheet->setCellValue('B9', $this->device());
                $sheet->mergeCells('A10:X10');
                $sheet->setCellValue('A10', " ");
                
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                
                $cellRange = 'A1:U1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                // $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
            },
        ];
    }
    
    public function headings(): array
    {
        $a= $this->query['headerItem']['gasCollection'];
        
        $header = array();
     
        for($i=0;$i<count($a);$i++){
            if($i == 0)
            {
              $header[] = "DATE";
              $header[] = " ";
            }
            
            $header[] = $a[$i];
        } 
        
        return array([" "],[" "],[" "],[" "],[" "],[" "],[" "],[" "],[" "],[" "], $header);
    }
        
        
    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 10, 
            'C'=> 15, 
            'D'=>15,
            'E'=>15,
            'F'=>15,
            'G'=>15,
            'H'=>15,
            'I'=>15,
            'J'=>15,
            'K'=>15,
            'L'=>15,
            'M'=>20,
            'N'=>20,
            'O'=>15,
            'P'=>15,
            'Q'=>15,
            'R'=>15,
            'S'=>15,
            'T'=>15,
            'U'=>15,
            'v'=>15,
            'W'=>15,
            'X'=>15,
        ];
    }
    
    
    
    public function styles(Worksheet $sheet)
    {
            
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true, 'size' => 14]],
            3    => ['font' => ['bold' => true, 'size' => 12]],
            4    => ['font' => ['bold' => true, 'size' => 12]],
            5    => ['font' => ['bold' => true, 'size' => 12]],
            6    => ['font' => ['bold' => true, 'size' => 12]],
            7    => ['font' => ['bold' => true, 'size' => 12]],
            8    => ['font' => ['bold' => true, 'size' => 12]],
            9    => ['font' => ['bold' => true, 'size' => 12]],
            11    => ['font' => ['bold' => true, 'size' => 12]]


            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'A'  => ['font' => ['bold' => true]],
        ];
    }
    
    public function location()
    {
        $a = $this->collection1('location');
        return $a;
    }
    
    public function branch()
    {
        $a = $this->collection1('branch');
        return $a;
    }
    
    public function facility()
    {
       $a = $this->collection1('facility');
        return $a;
    }
    
    public function building()
    {
        $a = $this->collection1('building');
        return $a;
    }
    
    public function floor()
    {
        $a = $this->collection1('floor');
        return $a;
    }
    
    public function zone()
    {
        $a = $this->collection1('zone');
        return $a;
    }
    
    public function device()
    {
        $a = $this->collection1('device');
        return $a;
    }
}
