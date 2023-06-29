<?php

namespace App\Exports;

use App\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoleExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $rolename;
    public function __construct($rolename){
        //$this->rolename = $request->input(key:'rolename'); 
        $this->rolename = $rolename;
    }    
    
    public function collection()
    {
        return Role::select('*')->where('rolename','=',$this->rolename)->get();
    }

    public function headings(): array
    {
        return [
            'customerId',
            'rolename',
            'rolecode',
            'date',
            'time'

        ];
    }
}
