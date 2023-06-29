<?php

namespace App\Http\Controllers\UTILITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsDataUtilityController extends Controller
{
    /* code for paginating data */
    protected $total = 0;
    protected $page = "";
    protected $NextPage = "";
    protected $perPage = 10;
    protected $result = "";  
    protected $cntresult = "";  
    protected $sort = "";  
    protected $column = ""; 
    protected $data = "";
    protected $returnedData = [];
    protected $searchedKey = "";
    
    function __construct($request,$query) {
        if($query) {
            $this->perPage = $request->input(key:'pageSize') == "" ? 10 : $request->input(key:'pageSize');
            $this->sort = $request->input(key:'sortedType') == "" ? "ASC" : $request->input(key:'sortedType');

            // if($request->input(key:'sortColumn') == "Tag"){
            //     $this->column = "sensorId" ;           
            // }elseif ($request->input(key:'sortColumn')== "fromDate") {
            //     $this->column = "a_date";
            // }
            // else {
            //     $this->column = $request->input(key:'column') == "" ? "id" : $request->input(key:'column');   
            // }

            // $query->orderBy($this->column,$this->sort);        
            
               //gets the count of data
            $this->cntresult = $query->get();
            $this->total =count( $this->cntresult); 
            
            $this->page = $request->input(key:'page', default: 1);  //gets the page number of pagination or by default parameter will be one          
            $this->result = $query->offset(value:($this->page ) * $this->perPage)->limit($this->perPage)->get();    //data will be stored in  results
            
        }
    }    

    function getData(){                
        return $returnedData[] = array(          
                "data"=>$this->result,
                "sortedType"=>$this->sort,
                "totalRowCount"=>$this->total,
                "currentPage"=>$this->page ,
                "totalPages"=>ceil(num:$this->total/ $this->perPage)                
        );
    }
}
