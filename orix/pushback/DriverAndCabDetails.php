<?php

// Endpoint for DriverAndCabDetails...
require 'classes.php';
$response = file_get_contents('php://input');
$data = json_decode($response); 
$DriverAndCabDetails = orixPushback::DriverAndCabDetails($data);

$return = [];
$curlReturn = 1;
if($DriverAndCabDetails['status']) {
    /*Call curl request start*/


    /*Call curl request end*/
    if ($curlReturn) {
        $return['status']  = "success";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $DriverAndCabDetails['data'];
    }else{
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = null;
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $DriverAndCabDetails['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

