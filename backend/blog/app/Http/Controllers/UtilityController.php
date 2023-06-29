<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;


class UtilityController extends Controller{
    
    protected $companyCode = "";  
    protected $userId = "";   
    protected $userRole = "";     
    
    protected $alertColor = [
        "WARNING"=>"#db8404",
        "CRITICAL"=>"#cc1616",
        "OUTOFRANGE"=>"#a821bf",
        "NORMAL"=>"#1b5e20",
        "WARNINGLIGHTCOLOR"=>"#f5d3a2",
        "CRITICALLIGHTCOLOR"=>"#fca2a2",
        "NORMALLIGHTCOLOR"=>"#c4eec7",
        "OUTOFRANGELIGHTCOLOR"=>"#eca6f7"
    ]; 
    
    function __construct(Request $request) {
        if($request->hasHeader('companyCode')) {
            $this->companyCode = $request->Header('companyCode');
        }

        if($request->hasHeader('userId')){
            $this->userId = $request->Header('userId');
        }

        if($request->hasHeader('userRole')){
            $this->userRole = $request->Header('userRole');
        }
    }

    function getCompanyCode(){
        return $this->companyCode;
    }

    function getUserId(){
        return $this->userId;
    }

    function getUserRole(){
        return $this->userRole;
    }
    
    function getAlertColors(){
        return $this->alertColor;
    }


}

?>