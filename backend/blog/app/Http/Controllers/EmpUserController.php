<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use Illuminate\Http\Request;
use App\Models\EmpUser;
use App\Models\User;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotify;
use DateTime;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\EventLogController;


class EmpUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    protected $companyCode = "";    
    protected $userId = "";
    protected $userRole = "";
    
    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();  
        
        if($request->hasHeader('userId')){
            $this->userId = $request->Header('userId');
        }
        if($request->hasHeader('userRole')){
            $this->userRole = $request->Header('userRole');
        }
    }
    
    public function index(Request $request)    { 
      
        $query = User::query();

        $userRole = "";
        $userId = "";
        $companyCode = "";

        if($request->hasHeader('companyCode')) {
            $companyCode = $request->Header('companyCode');
        }

        if($request->hasHeader('userId')){
            $userId = $request->Header('userId');
        }

        if($request->hasHeader('userRole')){
            $userRole = $request->Header('userRole');
        }

        if($companyCode!="" || $userRole!= "" || $userId != ""){
            if($userRole == "superAdmin"){
                $query->where('companyCode','=',$companyCode)
                    ->where('employeeId','<>','0000');             
            }
            elseif($userRole == "systemSpecialist"){
                $query->where('companyCode','=',$companyCode)
                   // ->where('user_role','<>','Admin')
                    ->where('user_role','<>','systemSpecialist')
                    ->where('user_role','<>','superAdmin');           
            }
            
            elseif($userRole == "Admin"){
                $query->where('companyCode','=',$companyCode)
                   // ->where('user_role','<>','Admin')
                    ->where('user_role','<>','systemSpecialist')
                    ->where('user_role','<>','superAdmin');              
            }
            
            elseif($userRole == "Manager"){
                $query->where('companyCode','=',$companyCode)
                    ->where('user_role','<>','Admin')
                    ->where('user_role','<>','systemSpecialist')
                    ->where('user_role','<>','superAdmin');            
            }
        }
        

        $getData = new DataUtilityController($request,$query);
        $response = $getData->getData();
        $status = 200;
        
        return response($response,$status);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


   
    public function getPassword($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
      
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
      
        return $randomString;
    }

    public function store(Request $request)
    {       
        $empuser = User::where('email', $request->email)->first();  
        $empId = User::where('employeeId', $request->empId)->first();   
        
        if($empuser){    
            $response = [
                "message" => "This Email ID exists under a different username"
            ];
            $status = 409;   
            
        }else if($empId){
            $response = [
                "message" => "Employee ID already exists"
            ];
            $status = 409; 
            
        } else{                   
            $password = $this->getPassword(10);  
            $encryptedPassword = Hash::make($password);                   
          
            $user = new User;
            $user->name = $request->empName;
            $user->email = $request->email;
            $user->mobileno = $request->phoneNo;
            $user->employeeId = $request->empId;
            $user->password = $encryptedPassword;
            $user->user_role = $request->empRole;
            $user->companyCode = $this->companyCode;
            $user->location_id = $request->location_id;
            $user->branch_id = $request->branch_id;
            $user->facility_id = $request->facility_id;
            $user->building_id = $request->building_id;
            $user->floor_id = $request->floor_id;
            $user->lab_id = $request->lab_id;
            $user->empNotification = $request->empNotification; 
            
            $url = env('APPLICATION_URL');
            $data = [
                'userid'=>$user->email,
                'subject' => 'Application employee Credentials',
                'body' =>$password,
                'url' => $url
            ];        
            
            Mail::send('credentialmail', $data, function($messages) use ($user){
                $messages->to($user->email);
                $messages->subject('Application login credentials');        
            });  
            $user->save();   
            
            $response = [
                "message"=>"User added successfully"
            ];
            $status = 201;        
            
            // Event logs //31-05-2023
            $logController = new EventLogController();
            $eventDetails = [
                "User name" => $request->empName,
                "email" => $request->email,
                "phoneNo" => $request->phoneNo,
                "role" => $request->empRole
                
            ];
            
            $logController->addLog($request, 'New user', $eventDetails);
        }
        
        return response($response, $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(["message" => "Requirement not given, so not implemented"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $user = User::find($id);
        if($user){
            $user->name = $request->empName;
            $user->email = $request->email;
            $user->mobileno = $request->phoneNo;
            $user->employeeId = $request->empId;           
            $user->user_role = $request->empRole;
            $user->companyCode = $this->companyCode;   
            $user->location_id = $request->location_id;
            $user->branch_id = $request->branch_id;
            $user->facility_id = $request->facility_id;
            $user->building_id = $request->building_id;
            $user->floor_id = $request->floor_id;
            $user->lab_id = $request->lab_id;
            $user->empNotification = $request->empNotification; 
            $user->update();
            $response = [
                "message" => "User updated successfully"
            ];
            $status = 200;            
        }
        else{
            $response = [
                "message" => "Data Not found"
            ];
            $status = 404;            
        }
        return response($response,$status); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $userId = $this->userId;
        $user = User::where('email', $userId)->first();
        $password = $request->password;
        if($password == ""){
            $response = [
                "message"=>"Please Enter the Password",
            ];
            $status = 401;
        }
        elseif(!Hash::check($password, $user->password)){
            $response = [
                "message"=>"Invalid credential",
            ];
            $status = 401;
        }
        else{
            $user = User::find($id);         
            if($user){       
                $user->delete();                            
                $response = [
                    "message" => "User deleted successfully"
                ];
                $status = 200;            
            }   
            else{
                $response = [
                    "message" => "User not found"
                ];
                $status = 404;                        
            }
            
        }
        return response($response,$status);
    }

    public function CustomData(Request $request){
        
        //includes search, sort, and pagination which is page data

        $query = User::query();

        if($s = $request->input(key:'s')){
            $query->whereRaw(sql:"name LIKE '%". $s ."%'")
                ->orWhereRaw(sql:"email LIKE  '%". $s ."%'");
        }

        if($sort = $request->input(key:'sort')){
            $query->orderBy('id',$sort);
        }

        $perPage = 10;
        $page = $request->input(key:'page', default:1);
        $total = $query->count();

        $result = $query->offset(value:($page - 1) * $perPage)->limit($perPage)->get();        
        //return response()->json([
        //     "data"=>$query->get()
        //]);
        $response =  [
            'data' => $result,
            'totalData'=>$total,            
            'page'=>$page,
            'lastPage'=>ceil(num:$total/ $perPage)
        ];
        $status = 200;
        return response($response,$status);
    }
}
