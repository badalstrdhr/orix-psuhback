<?php

// Endpoint for BookingTripStartDetails...
require 'classes.php';
$response = file_get_contents('php://input');
$data = json_decode($response); 
$BookingTripStartDetails = orixPushback::BookingTripStartDetails($data);

$return = [];
$curlReturn = 1;
if($BookingTripStartDetails['status']) {
    /*Call curl request start*/


    /*Call curl request end*/
    if ($curlReturn) {
        $return['status']  = "success";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $BookingTripStartDetails['data'];
    }else{
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = null;
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $BookingTripStartDetails['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

