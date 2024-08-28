<?php

// Endpoint for AcceptanceStatus...
require 'classes.php';
$client = isset($_GET['client']) ? $_GET['client'] : ''; 
$serviceProviderResponse = isset($_GET['serviceProviderResponse']) ? $_GET['serviceProviderResponse'] : ''; 
$bookingId = isset($_GET['bookingId']) ? $_GET['bookingId'] : '';

$data = []; 
$data['client'] = $client; 
$data['serviceProviderResponse'] = $serviceProviderResponse; 
$data['bookingId'] = $bookingId; 
$AcceptanceStatus = orixPushback::AcceptanceStatus($data);
$return = [];
$curlReturn = 1;

if($AcceptanceStatus['status']) {
    /*Call curl request start*/


    /*Call curl request end*/
    if ($curlReturn) {
        $return['status']  = "success";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $AcceptanceStatus['data'];
    }else{
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = null;
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $AcceptanceStatus['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

