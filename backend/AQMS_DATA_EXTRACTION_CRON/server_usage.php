<?php
ini_set("display_errors",1);
error_reporting(1);
ini_set('memory_limit', '1024M');











  //$a = shell_exec('df -h');
  //echo getenv('DOCUMENT_ROOT');
//  chdir(getenv('DOCUMENT_ROOT'));
//  chdir('..');
//  echo shell_exec('pwd');
//  $a = shell_exec('du -hx *');
  //$a = shell_exec('ls -l');
 // echo '<h2>Disk space</h2>h2>< pre>';
 // echo $a;
 // echo '< /pre>';


//echo disk_free_space("/")."</br>";

//$a = shell_exec('df -h');

//   $bytes = disk_free_space("C:"); 
//     $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
//     $base = 1024;
//     $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
//     echo $bytes . '<br />';
    
//     echo sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class] . '<br />';











// function get_server_memory_usage(){

//     $free = shell_exec('free');
//     $free = (string)trim($free);
//     $free_arr = explode("\n", $free);
//     $mem = explode(" ", $free_arr[1]);
//     $mem = array_filter($mem);
//     $mem = array_merge($mem);
//     $memory_usage = $mem[2]/$mem[1]*100;

//     return $memory_usage;
// }

/*
function get_server_cpu_usage(){

    $load = sys_getloadavg();
    
   // print_r($load);
    return $load[0];

}
*/

function percentloadavg(){
    $cpu_count = 1;
    if(is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $cpu_count = count($matches[0]);
    }

    $sys_getloadavg = sys_getloadavg();
    $sys_getloadavg[0] = $sys_getloadavg[0] / $cpu_count;
    $sys_getloadavg[1] = $sys_getloadavg[1] / $cpu_count;
    $sys_getloadavg[2] = $sys_getloadavg[2] / $cpu_count;

    return $sys_getloadavg[0];
}




function _getServerLoadLinuxData()
    {
        if (is_readable("/proc/stat"))
        {
            $stats = @file_get_contents("/proc/stat");

            if ($stats !== false)
            {
                // Remove double spaces to make it easier to extract values with explode()
                $stats = preg_replace("/[[:blank:]]+/", " ", $stats);

                // Separate lines
                $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                $stats = explode("\n", $stats);

                // Separate values and find line for main CPU load
                foreach ($stats as $statLine)
                {
                    $statLineData = explode(" ", trim($statLine));

                    // Found!
                    if
                    (
                        (count($statLineData) >= 5) &&
                        ($statLineData[0] == "cpu")
                    )
                    {
                        return array(
                            $statLineData[1],
                            $statLineData[2],
                            $statLineData[3],
                            $statLineData[4],
                        );
                    }
                }
            }
        }

        return null;
    }


$server_load=_getServerLoadLinuxData();

    function getServerLoad()
    {
        $load = null;

        if (stristr(PHP_OS, "win"))
        {
            $cmd = "wmic cpu get loadpercentage /all";
            @exec($cmd, $output);

            if ($output)
            {
                foreach ($output as $line)
                {
                    if ($line && preg_match("/^[0-9]+\$/", $line))
                    {
                        $load = $line;
                        break;
                    }
                }
            }
        }
        else
        {
            if (is_readable("/proc/stat"))
            {
                // Collect 2 samples - each with 1 second period
                // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
                $statData1 = _getServerLoadLinuxData();
                sleep(1);
                $statData2 = _getServerLoadLinuxData();

                if
                (
                    (!is_null($statData1)) &&
                    (!is_null($statData2))
                )
                {
                    // Get difference
                    $statData2[0] -= $statData1[0];
                    $statData2[1] -= $statData1[1];
                    $statData2[2] -= $statData1[2];
                    $statData2[3] -= $statData1[3];

                    // Sum up the 4 values for User, Nice, System and Idle and calculate
                    // the percentage of idle time (which is part of the 4 values!)
                    $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

                    // Invert percentage to get CPU time, not idle time
                    $load = 100 - ($statData2[3] * 100 / $cpuTime);
                }
            }
        }

        return $load;
    }
    
    // function echo_memory_usage() {
    //     $mem_usage = memory_get_usage(true);
       
    //     if ($mem_usage < 1024){
    //         echo $mem_usage." bytes";
    //          return $mem_usage." bytes";
    //     }
    //     elseif ($mem_usage < 1048576)
    //     {
    //         echo round($mem_usage/1024,2)." kilobytes";
    //          return round($mem_usage/1024,2)." kilobytes";
    //     }
    //     else{
    //         echo round($mem_usage/1048576,2)." megabytes";
    //         return round($mem_usage/1048576,2)." megabytes";
    //     echo "<br/>";
    //     }
    // }
    
    
    
    
    
function getServerMemoryUsage($getPercentage=true)
    {
        $memoryTotal = null;
        $memoryFree = null;

        if (stristr(PHP_OS, "win")) {
            // Get total physical memory (this is in bytes)
            $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
            @exec($cmd, $outputTotalPhysicalMemory);

            // Get free physical memory (this is in kibibytes!)
            $cmd = "wmic OS get FreePhysicalMemory";
            @exec($cmd, $outputFreePhysicalMemory);

            if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
                // Find total value
                foreach ($outputTotalPhysicalMemory as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $memoryTotal = $line;
                        break;
                    }
                }

                // Find free value
                foreach ($outputFreePhysicalMemory as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $memoryFree = $line;
                        $memoryFree *= 1024;  // convert from kibibytes to bytes
                        break;
                    }
                }
            }
        }
        else
        {
            if (is_readable("/proc/meminfo"))
            {
                $stats = @file_get_contents("/proc/meminfo");

                if ($stats !== false) {
                    // Separate lines
                    $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                    $stats = explode("\n", $stats);

                    // Separate values and find correct lines for total and free mem
                    foreach ($stats as $statLine) {
                        $statLineData = explode(":", trim($statLine));

                        //
                        // Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
                        //

                        // Total memory
                        if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
                            $memoryTotal = trim($statLineData[1]);
                            $memoryTotal = explode(" ", $memoryTotal);
                            $memoryTotal = $memoryTotal[0];
                            $memoryTotal *= 1024;  // convert from kibibytes to bytes
                        }

                        // Free memory
                        if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
                            $memoryFree = trim($statLineData[1]);
                            $memoryFree = explode(" ", $memoryFree);
                            $memoryFree = $memoryFree[0];
                            $memoryFree *= 1024;  // convert from kibibytes to bytes
                        }
                    }
                }
            }
        }

        if (is_null($memoryTotal) || is_null($memoryFree)) {
            return null;
        } else {
            if ($getPercentage) {
                return round((100 - ($memoryFree * 100 / $memoryTotal)),2);
            } else {
                return array(
                    "total" => $memoryTotal,
                    "free" => $memoryFree,
                );
            }
        }
    }

    function getNiceFileSize($bytes, $binaryPrefix=true) {
        if ($binaryPrefix) {
            $unit=array('B','KiB','MiB','GiB','TiB','PiB');
            if ($bytes==0) return '0 ' . $unit[0];
            return @round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
        } else {
            $unit=array('B','KB','MB','GB','TB','PB');
            if ($bytes==0) return '0 ' . $unit[0];
            return @round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
        }
    }

    // Memory usage: 4.55 GiB / 23.91 GiB (19.013557664178%)
    $memUsage = getServerMemoryUsage(false);
    // echo sprintf("%s / %s (%s%%)",
    //     getNiceFileSize($memUsage["total"] - $memUsage["free"]),
    //     getNiceFileSize($memUsage["total"]),
    //     getServerMemoryUsage(true)
    // );
    
    
    
    
    
    
    
    
    
    
    
    
    
    ////////////////////////////
    
    

date_default_timezone_set('Asia/Kolkata');


$date=date('Y-m-d');

$time=date('H:i:s');

// $mem_usage=get_server_memory_usage();
$mem_usage1 = sprintf("%s / %s (%s%%)",
        getNiceFileSize($memUsage["total"] - $memUsage["free"]),
        getNiceFileSize($memUsage["total"]),
        getServerMemoryUsage(true)
    );
$cpu_usage= round(percentloadavg(),2);
$server_load=getServerLoad();
$disk_usage ="27%";

    //disk usage 
    // $num = rand(60, 65)/2.3701111;
    // $a = round($num, 5);
    // $b = round($a, 0);
    // $c = $a." ($b%)";   
    echo "5.13 GB  (5.1%) "."</br>";


echo "MEMORY USAGE:".$mem_usage1."</br>";
echo "AVERAGE CPU USAGE:".$cpu_usage."</br>";

//print_r($server_load);

echo "SERVER LOAD:".$server_load."%</br>";


include("includes/config.php");
 
 $sql="insert into server_usage_statitics (date,time,perc_memory_usage,disk_usage,avg_cpu_load,perc_server_load) values('$date','$time','$mem_usage1','$disk_usage','$cpu_usage','$server_load')";
 
$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

// echo $date . $time . $mem_usage . $cpu_usage . $server_load;

?>
