<?php

// Endpoint for BookingTracking...
require 'classes.php';
$response = file_get_contents('php://input');
$data = json_decode($response); 
$BookingTracking = orixPushback::BookingTracking($data);

$return = [];
$curlReturn = 1;
if($BookingTracking['status']) {
    /*Call curl request start*/


    /*Call curl request end*/
    if ($curlReturn) {
        $return['status']  = "success";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $BookingTracking['data'];
    }else{
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = null;
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $BookingTracking['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

