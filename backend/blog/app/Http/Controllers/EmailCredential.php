<?php

function getCredential($companyCode) {
    $defaultEmail = env('MAIL_USERNAME');
    $defaultPassword = env('MAIL_PASSWORD');

    $customer = DB::table('customers')->where('customerId', $companyCode)->first();
    $systemEmail = $customer->systemEmail;
    $systemPassword = $customer->systemPassword;
    
    $senderEmail = (!empty($systemEmail)) ? $systemEmail : $defaultEmail;
    $password = (!empty($systemPassword)) ? $systemPassword : $defaultPassword;
    
    $data = [ $senderEmail, $password ];
    
    return $data;
}