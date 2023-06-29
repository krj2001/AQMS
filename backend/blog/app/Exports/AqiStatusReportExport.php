<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class AqiStatusReportExport implements FromCollection,WithHeadings
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
        return  collect($this->query);
    }

    public function headings(): array
    {
        return [
            'sample_date_time',
            'stateName',
            'branchName',
            'facilityName',
            'buildingName',
            'floorName',
            'Zone',
            'deviceName',
            'alertType'
        ];
    }




}
