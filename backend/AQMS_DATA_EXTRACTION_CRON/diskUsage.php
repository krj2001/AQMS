<?php

include("xmlapi.php");

$ip = "localhost";

# The access has can be found on your server under WHM's "Setup remote access hash" section or at /root/.accesshash
$root_hash = 'MY HASH CODE HERE';

$xmlapi = new xmlapi($ip);
$xmlapi->hash_auth("MY WHM ACCOUNT USERNAME",$root_hash);
$xmlapi->return_xml(1);
$xmlapi->set_debug(1);

$username = CpanelUsername;

$xmlapi->accountsummary($username);

print $xmlapi->accountsummary($username);

?>