<?php

// Name of the file
$filename = 'idealab_database.sql';
// MySQL host
$mysql_host = 'localhost';
// MySQL username
$mysql_username = 'idealab_AssetManagement';
// MySQL password
$mysql_password = 'q1w2e3r4/@1234';
// Database name
$mysql_database = 'idealab_AssetManagement';

// Connect to MySQL server
$conn = mysqli_connect($mysql_host, $mysql_username, $mysql_password) or die('Error connecting to MySQL server: ' . mysqli_error($conn));
// Select database
if($conn){
    echo "Connected";
}else{
    echo "Not connected";
}

mysqli_select_db($conn,$mysql_database) or die('Error selecting MySQL database: ' . mysqli_error($conn));

// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file($filename);
// Loop through each line
foreach ($lines as $line)
{
// Skip it if it's a comment
if (substr($line, 0, 2) == '--' || $line == '')
    continue;

// Add this line to the current segment
$templine .= $line;
// If it has a semicolon at the end, it's the end of the query
if (substr(trim($line), -1, 1) == ';')
{
    // Perform the query
    mysqli_query($conn, $templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($conn) . '<br /><br />');
    // Reset temp variable to empty
    $templine = '';
}
}
 echo "Tables imported successfully";
?>