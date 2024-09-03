<?php

// Endpoint for BookingTracking...
require '../db_config.php';
require 'classes.php';
$response = file_get_contents('php://input');
$data = json_decode($response); 
$BookingTracking = orixPushback::BookingTracking($data);
$dutyStatus = $BookingTracking['data'] ? $BookingTracking['data']->locations[0]['current_trip_status'] : "";
$result = [];
if($BookingTracking['status']) {
    /* 1. Driver location */
    /*Call curl request start*/
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CURL_URL.'driver_location');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($BookingTracking['data']));
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'rqid: '.orixPushback::Rqid($BookingTracking['data']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $result = json_decode($result);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    /*Call curl request end*/

    if ($dutyStatus == "dispatch") {
        $DriverDispatch = orixPushback::DriverDispatch($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, CURL_URL.'dispatch');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($DriverDispatch['data']));
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'rqid: '.orixPushback::Rqid($DriverDispatch['data']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $result = json_decode($result);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }
    if ($dutyStatus == "arrived") {
        $DriverArrived = orixPushback::DriverArrived($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, CURL_URL.'dispatch');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($DriverArrived['data']));
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'rqid: '.orixPushback::Rqid($DriverArrived['data']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $result = json_decode($result);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }
} else {
    $result['status']  = "failed";
    $result['msg']  = $BookingTracking['msg'];
    $result['requestTime'] = date("Y-m-d h:i:s");
    $result['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

