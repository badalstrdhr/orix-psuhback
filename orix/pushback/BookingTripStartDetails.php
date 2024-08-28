<?php

// Endpoint for BookingTripStartDetails...
require '../db_config.php';
require 'classes.php';
$response = file_get_contents('php://input');
$data = json_decode($response); 
$BookingTripStartDetails = orixPushback::BookingTripStartDetails($data);

$return = [];
if($BookingTripStartDetails['status']) {
    
    /*Call curl request start*/
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CURL_URL.'start');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($BookingTripStartDetails['data']));

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    // $headers[] = 'rqid: b7d03a6947b217efb6f3ec3bd3504582';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $result = json_decode($result);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    /*Call curl request end*/

    if ($result->status != "error") {
        $return['status']  = "success";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $result;
    } else {
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $result;
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $BookingTripStartDetails['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

