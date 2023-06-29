<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Exception;
use App\Models\User;

class CheckHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {    
                        
        try{            
            if($request->hasHeader('companyCode') && $request->hasHeader('userId') && $request->hasHeader('userRole')){                
                $companyCode = $request->Header('companyCode');
                $userId = $request->Header('userId');
                $userRole = $request->Header('userRole');
                if($companyCode != "" && $userId != "" && $userRole != ""){     
                    // if($userRole == "user"){
                    //     throw new Exception("Permission not granted");                   
                    // }                     
                    $user = User::where('email', $request->userId)->first();
                    $response = [ 
                        "data"=>"found"
                    ];
                    $status = 200;
                     return $next($request)->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Allow-Methods','GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS')
                        ->header('Access-Control-Expose-Headers', 'Content-Disposition')
                        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
                }
                else if($companyCode == ""){
                    throw new Exception("Company Code is Empty");               
                }
                else if($userId == ""){
                    throw new Exception("User ID is Empty");           
                }
                else{
                    throw new Exception("User Role is Empty");           
                } 
            }
            else{
                throw new Exception("Please provide the Required headers to Process request");
            }                        
        }catch(Exception $e){
            $response = [
                "error"=> $e->getMessage()
            ];
            $status = 404;
        }
        return response($response,$status);                     
    }
}
