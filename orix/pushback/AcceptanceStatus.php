<?php

// Endpoint for AcceptanceStatus...
require '../db_config.php';
require 'classes.php';
$client = isset($_GET['client']) ? $_GET['client'] : ''; 
$serviceProviderResponse = isset($_GET['serviceProviderResponse']) ? $_GET['serviceProviderResponse'] : ''; 
$bookingId = isset($_GET['bookingId']) ? $_GET['bookingId'] : '';
$data = new stdClass(); 
$data->client = $client; 
$data->serviceProviderResponse = $serviceProviderResponse; 
$data->bookingId = $bookingId; 
$AcceptanceStatus = orixPushback::AcceptanceStatus($data);
$return = [];
if($AcceptanceStatus['status']) {
    /* 1. Booking Confirmation*/
    /*Call curl request start*/
    $request_payload = json_encode($AcceptanceStatus['data']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CURL_URL.'booking_confirmation');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_payload);
    $headers = [];
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'rqid: '.orixPushback::Rqid($request_payload);
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

    }else{
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $result;
        $return['required_param_myf'] = $AcceptanceStatus['data'];
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $AcceptanceStatus['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

