<?php

// Endpoint for BookingInvoice...
require 'classes.php';
$response = file_get_contents('php://input');
$data = json_decode($response); 
$BookingInvoice = orixPushback::BookingInvoice($data);

$return = [];
$curlReturn = 1;
if($BookingInvoice['status']) {
    /*Call curl request start*/


    /*Call curl request end*/
    if ($curlReturn) {
        $return['status']  = "success";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = $BookingInvoice['data'];
    }else{
        $return['status']  = "failed";
        $return['requestTime'] = date("Y-m-d h:i:s");
        $return['data'] = null;
    }
} else {
    $return['status']  = "failed";
    $return['msg']  = $BookingInvoice['msg'];
    $return['requestTime'] = date("Y-m-d h:i:s");
    $return['data'] = null;
}

header('Content-Type: application/json');
echo $final_response = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

