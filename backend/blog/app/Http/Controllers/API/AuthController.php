<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\userLog;
use App\Models\Customer;
use App\Models\labDepartment;
use App\Models\Floor;
use App\Models\Building;
use App\Models\aidealabCompany;
use App\Models\ApplicationVersion;
use RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotify;
use DateTime;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use App\Http\Controllers\UtilityController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    // protected $maxAttempts = 5; // Default is 5
    // protected $decayMinutes = 2; // Default is 1

    private $current_time;
    protected $companyCode = "";
    protected $userId = "";
    protected $userRole = "";



    function __construct(Request $request){
        $date = new DateTime('Asia/Kolkata');
        $this->current_time = $date->format('Y-m-d H:i:s');

        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();
        $this->userId = $getData->getUserId();
        $this->userRole = $getData->getUserRole();
    }

    public function hide_email($email){
        $split_email = explode("@", $email);
        $length = floor(strlen($split_email[0]) / 2);
        return substr($split_email[0],0,$length).str_repeat("*",$length)."@".$split_email[1];
    }

    public function getPassword($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    /**
     * register  function use to register the data
     *
     */

    public function register(Request $request){

        $data = $request->validate([
            'name'=>'required|string|max:191',
            'email'=>'required|string|max:191',
            'phoneNo'=>'required|string'
        ]);

       $user = User::where('email', $data['email'])->first();

       if($user){
            $response = [
                "message"=>"superAdmin with EmailId already exist"
            ];
            $status = 302;
       }
       else{
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobileno = $request->phoneNo;
            $user->employeeId = $this->userId;

            $password = $this->getPassword(10);
            $encryptedPassword = Hash::make($password);

            $user->password = $encryptedPassword;
            $user->user_role = $this->userRole;
            $user->companyCode = $this->companyCode;

            $image = $request->companyLogo;  // your base64 encoded
            $imagePath = "";
            if($image){
                $image = str_replace('data:image/png;base64,', '', $request->companyLogo);
                $image = str_replace(' ', '+', $image);
                $imageName =  $request->name."_Logo.png";
                //$picture   = date('His').'-'.$filename;
                $path = "SuperAdmin/logo";
                $imagePath = $path."/".$imageName;
                Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));
            }

            $user->companyLogo = $imagePath;

            $data = [
                'userid'=>$user->email,
                'subject' => 'Application employee Credentials',
                'body' =>$password
            ];

            Mail::send('credentialmail',$data, function($messages) use ($user){
                $messages->to($user->email);
                $messages->subject('Application login credentials');
            });

            if($user->save()){
                $aidealabCompany = new aidealabCompany();
                $aidealabCompany->companyName = $this->companyCode;
                $aidealabCompany->email = $request->email;
                $aidealabCompany->periodicBackupInterval = 25;
                $aidealabCompany->dataRetentionPeriodInterval = 30;
                $aidealabCompany->save();

                $response = [
                    "userId"=>$this->userId,
                    "userName"=>$request->name,
                    "userEmail"=>$request->email,
                    "userRole"=>$this->userRole,
                    "password"=>$password,
                    "message"=>"data successfully added"
                ];
                $status = 200;
            }
       }
       return response($response,$status);
    }

    /**
     *
     * login function for  sending the token
     *
     */

    public function login(Request $request){

        $data = $request->validate([
            'email' => 'required|string|max:191',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();


        if(!$user){
            $response = [
                'error' => 'Entered email has not been registered. Please enter the registered email id'
            ];
            $status = 404;
            //throw new CustomException("Entered email has not been registered. Please enter the registered email id");
            // abort(404);
        }
        else{
            if($user['blocked']){
                $response = [
                    'message'=>"User is blocked, please contact admin",
                    'user'=>$user->email
                ];
                $status = 403;
            }
            elseif(!Hash::check($data['password'], $user->password)){

                /**
                 * checking the count user of login attempts
                 * blocking the user if count exceeds
                 */

                $user = User::where('email',$data['email'])->first();

                $fail_attempts_count = $user->login_fail_attempt;
                if($fail_attempts_count == 4){
                    $user->blocked = 1;
                    $user->update();
                }
                else{
                    $user->login_fail_attempt = $fail_attempts_count + 1;
                    $user->update();
                }
                $response = [
                    'message' => 'Invalid credentials',
                    'username' =>$user->email,
                    'fail_attempts' => $user->login_fail_attempt
                ];
                $status = 401;

            }
            else{

                $sec_level_auth = $user->sec_level_auth;
                //$data = DB::table('users')->where('id',$user->id)->first();

                $userLog = new userLog();
                $userLog->userId =$user->id;
                $userLog->userEmail =$user->email;
                $userLog->location_id =$user->location_id;
                $userLog->branch_id =$user->branch_id;
                $userLog->facility_id =$user->facility_id;
                $userLog->building_id =$user->building_id;
                $userLog->floor_id =$user->floor_id;
                $userLog->lab_id =$user->lab_id;
                $userLog->companyCode =$user->companyCode;
                $userLog->action = "LoggedIn";
                $userLog->save();


                $logoPath = "";
                $customerImage = "";
                if($user->user_role == "superAdmin"){
                    $users = User::where('companyCode', $user->companyCode)->first();
                    $companyName = $users->name;
                    $logoPath = $users->companyLogo;
                    $customerImage = $users->customerImage;

                    $aidelabsCompanyDetails = aidealabCompany::where('companyName',$user->companyCode)->first();
                    if($aidelabsCompanyDetails){
                        $alertLogInterval = "";
                        $deviceLogInterval = "";
                        $sensorLogInterval = "";
                        $periodicBackupInterval = $aidelabsCompanyDetails->periodicBackupInterval;
                        $dataRetentionPeriodInterval = $aidelabsCompanyDetails->dataRetentionPeriodInterval;
                        $expireDateReminder="";
                    }else{
                        $alertLogInterval = "";
                        $deviceLogInterval = "";
                        $sensorLogInterval = "";
                        $periodicBackupInterval = "";
                        $dataRetentionPeriodInterval = "";
                        $expireDateReminder="";
                    }

                }else{
                    $customer = Customer::where('customerId', $user->companyCode)->first();
                    $companyName = $customer->customerName;
                    $logoPath = $customer->customerLogo;
                    $customerImage = $customer->customerImage;
                    $alertLogInterval = $customer->alertLogInterval;
                    $deviceLogInterval = $customer->deviceLogInterval;
                    $sensorLogInterval = $customer->sensorLogInterval;
                    $periodicBackupInterval = $customer->periodicBackupInterval;
                    $dataRetentionPeriodInterval = $customer->dataRetentionPeriodInterval;
                    $expireDateReminder = $customer->expireDateReminder;
                }


                if($sec_level_auth == 0){
                    $user_feature = "false";
                    $token = $user->createToken($user->email)->plainTextToken;

                    $user->login_fail_attempt = 0;
                    $user->last_login_ativity = $this->current_time;
                    $user->update();

                    // $imageURL = '';
                    $imageBuildingURL = '';
                    $imageFloorURL = '';
                    $imageLabURL = '';

                    if($user->lab_id != ''){
                        // fetch lab image
                        $lab = labDepartment::where('id', $user->lab_id)->first();
                        if($lab){
                            $imageLabURL = $lab->labDepMap;
                        }
                    } else if($user->floor_id != ''){
                        // fetch floor image Floor
                        $floor = Floor::where('id', $user->floor_id)->first();
                        if($floor){
                            $imageFloorURL = $floor->floorMap;
                        }
                    } else if($user->building_id != ''){
                        // fetch building image
                        $building = Building::where('id', $user->building_id)->first();
                        if($building){
                            $imageBuildingURL = $building->buildingImg;
                        }
                    } else {
                        //  $imageURL = '';
                    }

                    // $lab = labDepartment::where('id', $user->lab_id)->first();
                    // $imageURL = $lab->labDepMap;


                    $appVersion = ApplicationVersion::latest()->first();
                    $appVer = $appVersion->versionNumber;
                    $appVerDate = $appVersion->updated_at;

                    $response = [
                        'userDetails'=>[
                            // 'email'=>$this->hide_email($user->email),
                            'emailId'=>$user->email,
                            'secondLevelAuthorization'=>$user_feature,
                            'userName'=>$user->name,
                            'userRole'=>$user->user_role,
                            'companyCode'=>$user->companyCode,
                            'companyName'=>$companyName,
                            'companyLogo'=>$logoPath,
                            'customerImage' => $customerImage,
                            'forcePasswordReset'=>$user->changePassword,
                            'notificationId' => $user->notificationId,
                            'lastAcknowledgedAlert' => $user->lastAcknowledgedAlert
                        ],
                        'intervalDetails'=>[
                            'alertLogInterval'=>$alertLogInterval,
                            'deviceLogInterval'=>$deviceLogInterval,
                            'sensorLogInterval'=>$sensorLogInterval,
                            'periodicBackupInterval'=>$periodicBackupInterval,
                            'dataRetentionPeriodInterval'=>$dataRetentionPeriodInterval,
                            'expireDateReminder'=>$expireDateReminder

                        ],
                        'locationDetails'=>[
                            'location_id'=>$user->location_id,
                            'branch_id'=>$user->branch_id,
                            'facility_id'=>$user->facility_id,
                            'building_id'=>$user->building_id,
                            'floor_id' =>$user->floor_id ,
                            'lab_id' =>$user->lab_id,
                            'imageBuildingURL' => $imageBuildingURL,
                            'imageFloorURL' => $imageFloorURL,
                            'imageLabURL' => $imageLabURL,
                        ],
                        'applicationDetails'=>[
                            'applicationVersion'=>$appVer,
                            'releaseDate'=>$appVerDate
                        ],

                        'user_token'=>$token,
                        'lastLoginActivity'=>$this->current_time,
                    ];
                    $status = 201;
                }
                else{
                    $user_feature = "true";
                    $token = $user->createToken($user->email)->plainTextToken;

                    $user->login_fail_attempt = 0;
                    $user->last_login_ativity = $this->current_time;
                    $user->update();

                    $imageBuildingURL = '';
                    $imageFloorURL = '';
                    $imageLabURL = '';

                    if($user->lab_id != ''){
                        // fetch lab image
                        $lab = labDepartment::where('id', $user->lab_id)->first();
                        if($lab){
                            $imageLabURL = $lab->labDepMap;
                        }
                    } else if($user->floor_id != ''){
                        // fetch floor image Floor
                        $floor = Floor::where('id', $user->floor_id)->first();
                        if($floor){
                            $imageFloorURL = $floor->floorMap;
                        }
                    } else if($user->building_id != ''){
                        // fetch building image
                        $building = Building::where('id', $user->building_id)->first();
                        if($building){
                            $imageBuildingURL = $building->buildingImg;
                        }
                    } else {
                        //  $imageURL = '';
                    }

                     $appVersion = ApplicationVersion::latest()->first();
                             $appVer = $appVersion->versionNumber;
                              $appVerDate = $appVersion->updated_at;


                    $response = [
                        'userDetails'=>[
                            // 'email'=>$this->hide_email($user->email),
                            'emailId'=>$user->email,
                            'secondLevelAuthorization'=>$user_feature,
                            'userName'=>$user->name,
                            'userRole'=>$user->user_role,
                            'companyCode'=>$user->companyCode,
                            'companyName'=>$companyName,
                            'companyLogo'=>$logoPath,
                            'forcePasswordReset'=>$user->changePassword
                        ],
                        'intervalDetails'=>[
                            'alertLogInterval'=>$alertLogInterval,
                            'deviceLogInterval'=>$deviceLogInterval,
                            'sensorLogInterval'=>$sensorLogInterval,
                            'periodicBackupInterval'=>$periodicBackupInterval,
                            'dataRetentionPeriodInterval'=>$dataRetentionPeriodInterval,
                            'expireDateReminder'=>$expireDateReminder
                        ],
                        'locationDetails'=>[
                            'location_id'=>$user->location_id,
                            'branch_id'=>$user->branch_id,
                            'facility_id'=>$user->facility_id,
                            'building_id'=>$user->building_id,
                            'floor_id' =>$user->floor_id ,
                            'lab_id' =>$user->lab_id,
                            'imageBuildingURL' => $imageBuildingURL,
                            'imageFloorURL' => $imageFloorURL,
                            'imageLabURL' => $imageLabURL,
                        ],
                        'applicationDetails'=>[
                            'applicationVersion'=>$appVer,
                            'releaseDate'=>$appVerDate
                        ],
                        'user_token'=>$token,
                        'lastLoginActivity'=>$this->current_time,
                    ];
                    $status = 201;
                }


                $a = $response['userDetails']['userName'];

                if($a == 'systemSpecialist')
                {
                    $response = [
                        'userDetails'=>[
                            // 'email'=>$this->hide_email($user->email),
                            'emailId'=>$user->email,
                            'secondLevelAuthorization'=>$user_feature,
                            'userName'=>'System Specialist',
                            'userRole'=>$user->user_role,
                            'companyCode'=>$user->companyCode,
                            'companyName'=>$companyName,
                            'companyLogo'=>$logoPath,
                            'forcePasswordReset'=>$user->changePassword
                        ],
                        'intervalDetails'=>[
                            'alertLogInterval'=>$alertLogInterval,
                            'deviceLogInterval'=>$deviceLogInterval,
                            'sensorLogInterval'=>$sensorLogInterval,
                            'periodicBackupInterval'=>$periodicBackupInterval,
                            'dataRetentionPeriodInterval'=>$dataRetentionPeriodInterval,
                            'expireDateReminder'=>$expireDateReminder

                        ],
                        'locationDetails'=>[
                            'location_id'=>$user->location_id,
                            'branch_id'=>$user->branch_id,
                            'facility_id'=>$user->facility_id,
                            'building_id'=>$user->building_id,
                            'floor_id' =>$user->floor_id ,
                            'lab_id' =>$user->lab_id,
                            'imageBuildingURL' => $imageBuildingURL,
                            'imageFloorURL' => $imageFloorURL,
                            'imageLabURL' => $imageLabURL,
                        ],
                        'applicationDetails'=>[
                            'applicationVersion'=>$appVer,
                            'releaseDate'=>$appVerDate
                        ],

                        'user_token'=>$token,
                        'lastLoginActivity'=>$this->current_time,
                    ];
                    $status = 201;

                }else{
                    $response = $response;
                    $status = 201;
                }
            }
        }
        return response($response,$status);
    }


    public function sendOtp(Request $request){
        if($request->email == ""  && $request->mobileno == ""){
            $response = [
                "message"=>"Please enter either email or number or both to get OTP"
            ];
            $status = 401;
        }
        else{
            // modified by vaishak 486-510
            $email = $request->header('userId');
            $mblNo = DB::table('users')->where('email',$email)->where('mobileno', $request->mobileno)->select('mobileno')->get();

            if($request->email == "" && count($mblNo)<=0){
                $response = [
                    "message"=>"Incorrect Phone"
                ];
                $status = 401;

            }else if($request->email != "" && $email != $request->email){
                $response = [
                    "message"=>"Incorrect Email"
                ];
                $status = 401;

            }else{

                if($request->email != ""  && $request->mobileno !=""){

                    $user = User::where('email', $request->email)->first();

                    if(!$user){
                        $response = [
                            "mailId"=>$request->email,
                            "message"=>"Email does not exist"
                        ];
                        $status = 404;
                    }
                    else if($user->mobileno != $request->mobileno){
                        $response = [
                            "mobileno"=>$request->mobileno,
                            "message"=>"MobileNo does not exist"
                        ];
                        $status = 404;
                    }
                    else{
                        $otp = rand(1000,9999);
                        $otpgenerated_at = date('Y-m-d H:i:s');//otp generating time

                        $email = $user->email;
                        $contact_no = $user->mobileno;
                        $msg = "Your AILAB AQMI Registration OTP is {$otp}.- RDL TECHNOLOGY PVT LTD";

                        if($user){
                            $user->otpno = $otp;
                            $user->otpgenerated_at = $otpgenerated_at;
                            $user->isverified=0;
                            $user->update();
                        }

                        //for mobile no code
                        $this->sms($msg,$contact_no);     //sending otp to mobilenumber

                        $data = [
                            'userid'=>$user->name,
                            'subject' => 'Application OTP',
                            'body' => 'Your OTP is : '. $otp
                        ];

                        Mail::send('mail',$data, function($messages) use ($user){
                            $messages->to($user->email);
                            $messages->subject('OTP verification');
                        });

                        $response = [
                            'message'=>"OTP is sent successfully to Mail and Mobileno",
                        ];
                        $status = 201;
                    }

                }
                elseif($request->email != ""){
                    $user = User::where('email', $request->email)->first();

                    if(!$user){
                        $response = [
                            "mailId"=>$request->email,
                            "message"=>"Email does not exist"
                        ];
                        $status = 404;
                    }
                    else{
                        $otp = rand(1000,9999);
                        $otpgenerated_at = date('Y-m-d H:i:s');//otp generating time

                        if($user){
                            $user->otpno = $otp;
                            $user->otpgenerated_at = $otpgenerated_at;
                            $user->isverified=0;
                            $user->update();
                        }

                        $data = [
                            'userid'=>$user->name,
                            'subject' => 'Application OTP',
                            'body' => 'Your OTP is : '. $otp
                        ];

                        Mail::send('mail',$data, function($messages) use ($user){
                            $messages->to($user->email);
                            $messages->subject('OTP verification To enter into application');
                        });

                        $response = [
                            'mailId'=>$request->email,
                            'message'=>"OTP is sent successfully to Mail",
                        ];
                        $status = 201;
                    }
                }
                elseif($request->mobileno != ""){

                    $user = User::where('mobileno', $request->mobileno)->first();

                    if(!$user){
                        $response = [
                            "mobileno"=>$request->mobileno,
                            "message"=>"Mobile does not exist"
                        ];
                        $status = 404;
                    }
                    // elseif($user->mobileno != $request->mobileno){
                    //     $response = [
                    //         "mobileno"=>$request->mobileno,
                    //         "message"=>"Mobile does not exist"
                    //     ];
                    //     $status = 404;
                    // }
                    else{
                        $otp = rand(1000,9999);
                        $otpgenerated_at = date('Y-m-d H:i:s');//otp generating time

                        $contact_no = $user->mobileno;
                        $msg = "Your AILAB AQMI Registration OTP is {$otp}.- RDL TECHNOLOGY PVT LTD";

                        if($user){
                            $user->otpno = $otp;
                            $user->otpgenerated_at = $otpgenerated_at;
                            $user->isverified=0;
                            $user->update();
                        }

                        //for mobile no code
                        $this->sms($msg,$contact_no);     //sending otp to mobilenumber

                        $response = [
                            'message'=>"OTP is sent successfully to Mobileno",
                        ];
                        $status = 201;
                    }
                }
            }
        }
        return response($response, $status);
    }


    public function requestToken(Request $request){

        $otp = $request->otp;
        $email = $request->email;
        $phone = $request->phone;
        if($otp == ""){
            $response = [
                "message" => "Please enter the otp"
            ];
            $status = 404;
        }
        else{
            if($email != ""){
                 $user = User::where('email', $email)->first();
            }
            else{
                 $user = User::where('mobileno', $phone)->first();
            }


            $newotp = $otp;

            $isverified = $user->isverified;
            $dbotp = $user->otpno;
            $otpgerated_at = $user->otpgenerated_at;

            $current_time = date('Y-m-d H:i:s');

            $to_time = strtotime($current_time);
            $from_time = strtotime($otpgerated_at);
            $total_min = round(abs($to_time - $from_time) / 60,2);

            if($isverified == 1){
                //return response with  otp expired
                $response = [
                    "message" => "OTP has expired, please try again"
                ];
                $status = 401;
            }
            else if($newotp != $dbotp && $total_min < 5 || $newotp != $dbotp && $total_min > 5){
                $response = [

                    "message" => "OTP entered is incorrect"
                ];
                $status = 401;
            }
            else if($newotp == $dbotp  && $total_min > 5){
                $response = [
                    "status"=>401,
                    "message" => "OTP has expired, please try again"
                ];
                $status = 401;
            }
            else{
                $user->sec_level_auth = 0;  // set sec_level_auth to 0 and is_verified to 1.
                $user->isverified = 1;
                $user->last_login_ativity = $this->current_time;
                $user->update();

                $user_feature = "true";

                $response = [
                    'userDetails'=>[
                        'userId'=>$user->email,
                        'secondLevelAuthorization'=>$user_feature,
                        'userRole'=>$user->user_role,
                    ],
                    'lastLoginActivity'=>$this->current_time,
                ];
                $status = 201;

            }
        }

        return response($response, $status);
    }

    public function blockedUserPasswordAutogenerate(Request $request){

        $userId = $this->userId;
        $user = User::where('email', $userId)->first();
        $email = $request->email;
        $password = $request->password;
        if($email == ""){
            $response = [
                "message"=>"Please Enter the Email Id",
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
                $userUpdate = User::where('email', $email)->first();

                $password = $this->getPassword(10);
                $encryptedPassword = Hash::make($password);

                $data = [
                    'userid'=>$email,
                    'subject' => 'Application employee Credentials',
                    'body' =>$password
                ];

                Mail::send('credentialmail',$data, function($messages) use ($userUpdate){
                    $messages->to($userUpdate->email);
                    $messages->subject('Application login credentials');
                });

                if($userUpdate){
                    $userUpdate->blocked = 0;
                    $userUpdate->login_fail_attempt = 0;
                    $userUpdate->password = $encryptedPassword;
                    $userUpdate->changePassword = 1;
                    $userUpdate->update();
                }

                $response = [
                    "password"=>$password,
                    "message"=>"Email sent Successfully"
                ];
                $status = 200;

        }

        return response($response, $status);

    }


    public function resetUserPassword(Request $request){


        $email = $this->userId;
        $data = $request->validate([

            'oldPassword' => 'required|string',
            'newPassword' => 'required|string'
        ]);

        $user = User::where('email', $email)->first();

        if(!Hash::check($data['oldPassword'], $user->password))
        {
            $response = [
                "message"=>"Old password is incorrect"
            ];
            $status = 401;
        }
        else{
            $password = $data['newPassword'];
            $encryptedPassword = Hash::make($password);
            $url = env('APPLICATION_URL');

            $data = [
                'userid'=>$user->name,
                'subject' => 'Password Updated',
                'body' => 'Succesfully updated',
                'url' => $url
            ];

            Mail::send('passwordUpdate',$data, function($messages) use ($user){
                $messages->to($user->email);
                $messages->subject('Password updation');
            });

            if($user){
                $user->changePassword = 0;
                $user->isverified = 1;
                $user->sec_level_auth = 0;
                $user->password = $encryptedPassword;
                $user->update();
            }
            $response = [
                "message"=>"Password is updated"
            ];
            $status = 201;
        }
        return response($response,$status);
    }


    public function sms($msg,$contact_no){
        $user_name="rdl";
        $api_password="6c83405kwtpsd6wtg";
        $sender="KEWRDL";
        $to=$contact_no;
        $message=rawurlencode($msg);
        $priority="11";
        $entity_id="1201163177490663081";
        $tag_id="1207164690740453778";
        $sms_http_api_url="http://sms.foosms.com/pushsms.php"."?username=".$user_name."&api_password=".$api_password."&sender=".$sender."&to=".$to."&priority=".$priority."&e_id=".$entity_id."&t_id=".$tag_id."&message=".$message;
        $url = $sms_http_api_url;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);
    }

    /**
     *
     * logout function  and destroying authid
     */

    public function logout(Request $request){

        $email = $request->header('userId');
        $data = DB::table('users')->where('email',$email)->first();

        $userEmail = Auth::user()->email;
        $userId = Auth::user()->id;
        $companyCode = Auth::user()->companyCode;
        $userLog = new userLog();
        $userLog->userId = $userId;
        $userLog->userEmail = $userEmail;
        $userLog->location_id = $data->location_id;
        $userLog->branch_id = $data->branch_id;
        $userLog->facility_id = $data->facility_id;
        $userLog->building_id = $data->building_id;
        $userLog->floor_id = $data->floor_id;
        $userLog->lab_id = $data->lab_id;
        $userLog->companyCode = $companyCode;
        $userLog->action = "LoggedOut";
        $userLog->save();

        auth()->user()->tokens()->delete();

        $response = [
            "message"=>"Logged out successfully"
        ];
        $status = 200;
        return response($response,$status);
    }

    public function UserLogDetails(Request $request){

        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $userId = $request->userId;

       if($request->userId != ""){
            $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->where('companyCode','=',$this->companyCode)
                        ->where('userId','=',$userId)
                        ->orderBy('id','DESC');

            if($startDate === $endDate){
                $query->whereDate('created_at','=',$startDate);
            }
            else{

               $dateEnd = date('Y-m-d', strtotime("+1 day", strtotime($endDate)));
               $query->whereBetween('created_at', [$startDate,  $dateEnd]);
            }

            $response = [
                "data"=>$query->get()
            ];

            $status = 200;

        }else{

            if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != "" && $request->building_id != "" && $request->floor_id != "" && $request->lab_id != ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('location_id',$request->location_id)
                        ->where('branch_id',$request->branch_id)
                        ->where('facility_id',$request->facility_id)
                        ->where('building_id',$request->building_id)
                        ->where('floor_id',$request->floor_id)
                        ->where('lab_id',$request->lab_id)
                        ->orderBy('id','DESC')
                        ->get();

            }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != "" && $request->building_id != "" && $request->floor_id != "" && $request->lab_id == ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('location_id',$request->location_id)
                        ->where('branch_id',$request->branch_id)
                        ->where('facility_id',$request->facility_id)
                        ->where('building_id',$request->building_id)
                        ->where('floor_id',$request->floor_id)
                        ->orderBy('id','DESC')
                        ->get();

            }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != "" && $request->building_id != "" && $request->floor_id == "" && $request->lab_id == ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('location_id',$request->location_id)
                        ->where('branch_id',$request->branch_id)
                        ->where('facility_id',$request->facility_id)
                        ->where('building_id',$request->building_id)
                        ->orderBy('id','DESC')
                        ->get();

            }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != "" && $request->building_id == "" && $request->floor_id == "" && $request->lab_id == ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('location_id',$request->location_id)
                        ->where('branch_id',$request->branch_id)
                        ->where('facility_id',$request->facility_id)
                        ->orderBy('id','DESC')
                        ->get();

            }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id == "" && $request->building_id == "" && $request->floor_id == "" && $request->lab_id == ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('location_id',$request->location_id)
                        ->where('branch_id',$request->branch_id)
                        ->orderBy('id','DESC')
                        ->get();

            }else if($request->location_id != "" && $request->branch_id == "" && $request->facility_id == "" && $request->building_id == "" && $request->floor_id == "" && $request->lab_id == ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('location_id',$request->location_id)
                        ->orderBy('id','DESC')
                        ->get();

            }else if($request->location_id == "" && $request->branch_id == "" && $request->facility_id == "" && $request->building_id == "" && $request->floor_id == "" && $request->lab_id == ""){

                $query = DB::table('user_logs')
                        ->select(DB::raw('*, DATE_FORMAT(created_at,"%d-%m-%Y") as createdDate, TIME(created_at) as createdTime'))
                        ->whereBetween('created_at', [$startDate,  $endDate])
                        ->where('companyCode','=',$this->companyCode)
                        ->where('userEmail','!=',$request->header('userId'))
                        ->orderBy('id','DESC')
                        ->get();
            }

            $response = [
                    "data"=> $query
            ];

            $status =200;
        }

            return response($response,$status);
    }


    public function userListDetails(Request $request)
    {
        $query = User::select('name','id');

        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;

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
                    ->where('user_role','<>','Admin')
                    //->where('user_role','<>','systemSpecialist')
                    ->where('user_role','<>','superAdmin');
            }
            elseif($userRole == "Manager"){
                $query->where('companyCode','=',$companyCode)
                    ->where('user_role','<>','Admin')
                    ->where('user_role','<>','systemSpecialist')
                    ->where('user_role','<>','superAdmin');
            }
           elseif($userRole == "User"){
                $query->where('companyCode','=',$companyCode)
                    ->where('user_role','<>','Manager')
                    ->where('user_role','<>','Admin')
                    ->where('user_role','<>','systemSpecialist')
                    ->where('user_role','<>','superAdmin');
            }
        }


       if($location_id != "" && $branch_id != "" && $facility_id != ""  && $building_id != "" && $floor_id != "" && $lab_id !="" )
        {
            $query->where('facility_id','=',$facility_id);
            $query->where('branch_id','=',$branch_id);
            $query->where('location_id','=',$location_id);
            $query->where('building_id','=',$building_id);
            $query->where('floor_id','=',$floor_id);
            $query->where('lab_id','=',$lab_id);

        }



        if($location_id != "" && $branch_id != "" && $facility_id != ""  && $building_id != "" && $floor_id != "")
        {
            $query->where('facility_id','=',$facility_id);
            $query->where('branch_id','=',$branch_id);
            $query->where('location_id','=',$location_id);
            $query->where('building_id','=',$building_id);
            $query->where('floor_id','=',$floor_id);
        }


        if($location_id != "" && $branch_id != "" && $facility_id != ""  && $building_id != "")
        {
            $query->where('facility_id','=',$facility_id);
            $query->where('branch_id','=',$branch_id);
            $query->where('location_id','=',$location_id);
            $query->where('building_id','=',$building_id);
        }

        if($location_id != "" && $branch_id != "" && $facility_id != "")
        {
            $query->where('facility_id','=',$facility_id);
            $query->where('branch_id','=',$branch_id);
            $query->where('location_id','=',$location_id);
        }

        if($location_id != "" && $branch_id != "")
        {
            $query->where('branch_id','=',$branch_id);
            $query->where('location_id','=',$location_id);
        }

        if($location_id != "")
        {
            $query->where('location_id','=',$location_id);
        }
        else{
            // $query->whereNull('facility_id');
            // $query->whereNull('branch_id');
            // $query->whereNull('location_id');
        }

        $getData = new DataUtilityController($request,$query);
        $response =   $query->get();

        $status = 200;

        return response($response,$status);
    }

    public function sendMessage(Request $request){
        $email = "abhishek@rdltech.in";
        $data = [
            'userid'=>$email,
            'subject' => 'Application employee Credentials',
            'body' =>"123456"
        ];

        Mail::send('credentialmail',$data, function($messages) use ($email){
            $messages->to($email);
            $messages->subject('Application login credentials');
        });
    }


    public function updateNotification(Request $request)
    {
        $email = $request->email;

        try{
            $user = User::where('email', $email)->first();

            if(!$user){
                throw new Exception("user not found");
            }

            $user->notificationId = $request->notificationId;
            $user->lastAcknowledgedAlert = $request->lastAcknowledgedAlert;
            // $user->notification_timestamp = $request->notification_timestamp;
            // $user->deviceDetails = $request->deviceDetails;
            // $user->recorded_timestamp = $request->recorded_timestamp;
            $user->update();

            $response = [
               "message" => 'Updated successfully',
               "status" => 200
            ];
            $status = 200;


        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage(),
                "status" => 406
            ];
            $status = 406;

        }catch(QueryException $e){
            $response = [
                "message" => $e->errorInfo,
                "status" => 406
            ];
            $status = 406;
        }

        return response($response,$status);
    }
}
