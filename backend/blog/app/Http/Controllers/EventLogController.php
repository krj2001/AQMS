<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EventLog;
use App\Models\FirmwareVersionChangeLog;
use App\Exports\EventLogExport;
use Exception;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as BaseExcel;
use Illuminate\Support\Facades\Mail;

class EventLogController extends Controller
{
    
    public function addLog($request, $eventName, $eventDetails)
    {
       
        $formattedDate = Carbon::now()->format('Y-m-d');
        $formattedTime = Carbon::now()->format('H:i:s');
        
        $locations = $this->locationDetails($eventName, $eventDetails);
        
        $log = new EventLog;
        $log->date = $formattedDate;
        $log->time = $formattedTime;
        $log->companyCode = $request->header('Companycode');
        $log->locationId = $request->location_id ?? $locations->locationId;;
        $log->branchId = $request->branch_id ??  $locations->branchId;
        $log->facilityId = $request->facility_id ?? $request->facilityId ?? $locations->facilityId;
        $log->buildingId = $request->building_id ?? $request->buildingId ?? $locations->buildingId;
        $log->floorId = $request->floor_id ?? $locations->floorId;
        $log->labId = $request->labid ?? $request->lab_id ?? $locations->labId;
        $log->eventName = $eventName;
        $log->eventDetails = json_encode($eventDetails);
        $log->user = $request->header('Userid');
        $log->save();
    }
    
    
    
    // Get location details based on deviceName
    
    public function locationDetails($eventName, $eventDetails)
    {
        if($eventName == 'Enable / Disable Mode' || $eventName == 'Configuration' || $eventName == 'Alarm Clearance') {
            $deviceName = $eventDetails['deviceName'];
            $device = DB::table('devices as db')
                ->where('db.deviceName', $deviceName)
                ->join('locations as l', 'l.id', '=', 'db.location_id')
                ->join('branches as br', 'br.id', '=', 'db.branch_id')
                ->join('facilities as fac', 'fac.id', '=', 'db.facility_id')
                ->join('buildings as b', 'b.id', '=', 'db.building_id')
                ->join('floors as f', 'f.id', '=', 'db.floor_id')
                ->join('lab_departments as lb', 'lb.id', '=', 'db.lab_id')
                ->select('l.id as locationId', 'br.id as branchId', 'fac.id as facilityId', 'b.id as buildingId', 'f.id as floorId', 'lb.id as labId')
                ->first();
                
        } else {
            $locations = [
                "locationId" => null,
                "branchId" => null,
                "facilityId" => null,
                "buildingId" => null,
                "floorId" => null,
                "labId" => null,
            ];
            $device = (object) $locations;
        }
            
        return $device;
    }
    
    
    
    
    // // Display event log
    // public function showEventLog(Request $request)
    // {
    //     $jsonData = DB::table('event_logs')
    //         ->where('companyCode', $request->header('Companycode'))
    //         ->where('created_at', '>=', $request->fromDate)
    //         ->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($request->toDate))))
    //         ->get();
            
    //     $dataArray = json_decode($jsonData, true);  // Decode JSON array into PHP array
        
    //     foreach ($dataArray as &$item) {
    //         $eventDetails = json_decode($item['eventDetails'], true);  // Decode the 'eventDetails' property into PHP array
            
    //         $formattedEventDetails = [];
    //         foreach ($eventDetails as $key => $value) {
    //             $formattedEventDetails[] = "{$key}: {$value}";
    //         }
            
    //         $item['eventDetails'] = implode(', ', $formattedEventDetails);  // Format the 'eventDetails' property as desired
    //     }
        
    //     $response['data'] = $dataArray;

    //     return $response;
    // }
    
    
    // Display event log
    public function showEventLog(Request $request)
    {
        $location = $request->location_id;
        $branch = $request->branch_id;
        $facility = $request->facility_id;
        $building = $request->building_id;
        $floor = $request->floor_id;
        $lab = $request->lab_id;
        $eventName = $request->eventName;
        
        
        $eventLogs = DB::table('event_logs')->where('companyCode', $request->header('Companycode'));
        
        if($location != "" && $branch != "" && $facility != "" && $building != "" && $floor != "" && $lab != "") {
            $eventLogs->where('locationId', $location)->where('branchId', $branch)->where('facilityId', $facility)->where('buildingId', $building)->where('floorId', $floor)->where('labId', $lab);
            
        }else if($location != "" && $branch != "" && $facility != "" && $building != "" && $floor != "") {
            $eventLogs->where('locationId', $location)->where('branchId', $branch)->where('facilityId', $facility)->where('buildingId', $building)->where('floorId', $floor);
             
        }else if($location != "" && $branch != "" && $facility != "" && $building != "") {
            $eventLogs->where('locationId', $location)->where('branchId', $branch)->where('facilityId', $facility)->where('buildingId', $building);
            
        }else if($location != "" && $branch != "" && $facility != "") {
            $eventLogs->where('locationId', $location)->where('branchId', $branch)->where('facilityId', $facility);
            
        }else if($location != "" && $branch != "") {
            $eventLogs->where('locationId', $location)->where('branchId', $branch);
            
        }else if($location != "") {
            $eventLogs->where('locationId', $location);
            
        }
        
        if($eventName) {
            $eventLogs = $eventLogs->select('event_logs.*', DB::raw('DATE_FORMAT(date, "%d-%m-%Y") as date'))
                ->where('created_at', '>=', $request->fromDate)
                ->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($request->toDate))))
                ->where('eventName', $eventName)
                ->get();
                
        } else {
            $eventLogs = $eventLogs->select('event_logs.*', DB::raw('DATE_FORMAT(date, "%d-%m-%Y") as date'))
                ->where('created_at', '>=', $request->fromDate)
                ->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($request->toDate))))
                ->get();
        }
            
        $dataArray = json_decode($eventLogs, true);  // Decode JSON array into PHP array
        
        foreach ($dataArray as &$item) {
            $eventDetails = json_decode($item['eventDetails'], true);  // Decode the 'eventDetails' property into PHP array
            
            $formattedEventDetails = [];
            foreach ($eventDetails as $key => $value) {
                $formattedEventDetails[] = "{$key}: {$value}";
            }
            
            $item['eventDetails'] = implode(', ', $formattedEventDetails);  // Format the 'eventDetails' property as desired
        }
        
        $response['data'] = $dataArray;

        return $response;
    }
    
    
    
    // Event log export
    public function eventLogExport(Request $request)
    {
        $data = $this->showEventLog($request);
        
        foreach($data as $k => $v) {
            $latest = $v;
        }
        
        $details = collect($latest)->map(function ($item) {
            return [
                'date' => $item['date'],
                'time' => $item['time'],
                'user' => $item['user'],
                'eventName' => $item['eventName'],
                'eventDetails' => $item['eventDetails'],
            ];
        });
        
        return Excel::download(new EventLogExport($details), 'EventLog.xlsx');
        // return $latest;
    }
    
    
    
    // Sending mail
    public function eventLogMail(Request $request)
    {
        $data = $this->showEventLog($request);
        
        foreach($data as $k => $v) {
            $latest = $v;
        }
        
        $details = collect($latest)->map(function ($item) {
            return [
                'date' => $item['date'],
                'time' => $item['time'],
                'user' => $item['user'],
                'eventName' => $item['eventName'],
                'eventDetails' => $item['eventDetails'],
            ];
        });
        
        $attachment = Excel::raw(new EventLogExport($details), BaseExcel::XLSX);
        
        $url = env('APPLICATION_URL');
        $email = $request->header('Userid');
        // $email = 'developer5@rdltech.in';
        $data = [
            'meassage' => 'Event Logs Report',
            'url' => $url
        ];
        
        Mail::send('EventLog', $data, function($messages) use ($email, $attachment){
            $messages->to($email);
            $messages->subject('Event Logs Report');    
            $messages->attachData($attachment, 'EventLogs.xlsx',[
            ]);
        });
        
        $response = [      
            "message"=>"Report sent Successfully"
        ];
        $status = 200;

        return response($response, $status);
    }
    
    
    
    public function insertFirmwareLog($request, $id, $status)
    {
        $device = DB::table('devices')->where('id', $id)->first();
        
        if($device) {
            $log = new FirmwareVersionChangeLog;
            $log->companyCode = $request->header('Companycode');
            $log->device_id = $id;
            $log->deviceName = $device->deviceName;
            $log->firmwareVersion = $device->firmwareVersion;
            $log->userEmail = $request->header('Userid');
            $log->status = $status;
            $log->save();
        }
    }
    
    

    
    
    
    
    
    
    
    
    
    
}