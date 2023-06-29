<?php

    //creates sql file by taking backup in specific folder databaseBackup
    include("includes/config.php");
    date_default_timezone_set('Asia/Kolkata');
    
    $dt=date("Y-m-d");
    $tm=date("H:i:s");
    $currentDateTime =$dt." ".$tm;
    
    $getAidealabDataBackUpdateDateAndPeriodicInterval = "SELECT * FROM `aidealab_companies` Limit 1";
    $getData = mysqli_query($mysqli,$getAidealabDataBackUpdateDateAndPeriodicInterval) or die(mysqli_error($mysqli));
    if(mysqli_num_rows($getData)>0){
        $result = mysqli_fetch_assoc($getData);
        echo $result['companyName']." ".$result['periodicBackupInterval']." ".$result['lastPeriodicBackupDate']." ".$currentDateTime."<br>";
        
        $diff = abs(strtotime($currentDateTime) - strtotime($result['lastPeriodicBackupDate']));
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); //method 1
        
        $days2 = round(abs(strtotime($currentDateTime) - strtotime($result['lastPeriodicBackupDate']))/86400); //method 2
        
        if($result['lastPeriodicBackupDate'] > $currentDateTime) {
            echo 'periodic date is greater than  current date and current date should be less than priodic date to  take backup';
        }else{
            //days difference should be euqal to take backup
            if($days2 == $result['periodicBackupInterval']){
                echo "take Backup";
                Export_Database($host,$dbuser,$dbpass,$db,  $tables=false, $backup_name=false,$currentDateTime);
                $updateLastPeriodicBackupDateTodaysDate = "UPDATE aidealab_companies SET lastPeriodicBackupDate = '$currentDateTime'  order by id desc Limit 1";
                $updateSql = mysqli_query($mysqli,$updateLastPeriodicBackupDateTodaysDate) or die(mysqli_error($conn));
                if($updateSql){
                    echo "Date Updated";
                }else{
                    echo "somthing went wrong";
                }
            }else{
                echo "Dont take backup because current date not met periodicBackupInterval";
            }
        }
    }else{
        echo "Nodata Found";
    }
    
    
    
    //Export_Database($host,$dbuser,$dbpass,$db,  $tables=false, $backup_name=false,$currentDateTime);
    
    function Export_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false,$currentDateTime)
    {
        $mysqli = new mysqli($host,$user,$pass,$name); 
        $mysqli->select_db($name); 
        $mysqli->query("SET NAMES 'utf8'");
        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0]; 
        }   
        if($tables !== false) 
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        
        print_r($target_tables);
        
        
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table." LIMIT 2");  
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";
            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        
        $dir = "databaseBackup";
        $backup_name = $name.$currentDateTime.".sql";
        
        echo $backup_name;
        
        if( is_dir($dir) === false )
        {
            mkdir($dir);
        }
        
        $file = fopen($dir . '/' . $backup_name,"w");
        fwrite($file, $content);
        fclose($file);
        
        // header('Content-Type: application/octet-stream');   
        // header("Content-Transfer-Encoding: Binary"); 
        // header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
        echo $content; exit;
      
    } 
    
?>