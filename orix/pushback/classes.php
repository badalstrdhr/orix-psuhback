<?php

/**
 * orix pushback api
 */

include_once 'index.php';

class orixPushback {
	protected $token = null;
	protected $functionCalled = null;
	protected $data = [];
	public function __construct() {
		return 'this is orixPushback api';
	}
	public static function Sign($payload, $key, $expire = null) {
        // Header
        $headers = ['algo'=>'HS256', 'type'=>'JWT', 'expire' => time()+$expire];
        if($expire){
            $headers['expire'] = time()+$expire;
        }
        $headers_encoded = base64_encode(json_encode($headers));

        // Payload
        $payload['time'] = time();
        $payload_encoded = base64_encode(json_encode($payload));

        // Signature
        $signature = hash_hmac('SHA256',$headers_encoded.$payload_encoded,$key);
        $signature_encoded = base64_encode($signature);

        // Token
        $token = $headers_encoded . '.' . $payload_encoded .'.'. $signature_encoded;
        $status = '';
        $expire_at = date("Y-m-d h:i:s",$headers['expire']);
        if($token) {
        	$status = 1;
        	$msg = 'Token generated';
        	$data = array('token'=>$token, 'expire_at'=>$expire_at);
        }
        return self::handleReturn($data, $status, $msg);
    }
    public static function Verify($token, $key) {

        // Break token parts
        $token_parts = explode('.', $token);

        // Verigy Signature
        $signature = base64_encode(hash_hmac('SHA256',$token_parts[0].$token_parts[1],$key));
        if($signature != $token_parts[2]){
            return false;
        }

        // Decode headers & payload
        $headers = json_decode(base64_decode($token_parts[0]), true);
        $payload = json_decode(base64_decode($token_parts[1]), true);

        // Verify validity
        if(isset($headers['expire']) && $headers['expire'] < time()){
            return false;
        }

        // If token successfully verified
        return $payload;
    }
	public static function BookingTripStartDetails($requestdata) {
		if($getBearerToken = self::getBearerToken()) {
			if($payload = self::Verify($getBearerToken, KEY)) {
				if($payload['id'] == "]OwHd&I;@*fwkc/") {
					$status = 1;
					$msg = "Token validated";
				} else {
					$status = 0;
					$msg = "Token validatation failed";
				}
			}else{
				$status = 0;
				$msg = "Missing payload";
			}
		}else{
			$status = 0;
			$msg = "Missing bearer token";
		}
		return self::handleReturn($requestdata, $status, $msg);
	}
	public static function getBearerToken() {
		if($headers = getallheaders()) {
			 if (isset($headers['Authorization'])) {
		        $authHeader = $headers['Authorization'];
		        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
		            return $matches[1];
		        }
		    }
		}
	}
	public static function handleReturn($data, $status, $msg) {
		$return = [
			 "status"=>$status,
			 "msg"=>$msg,
			 "requestTime"=>date("Y-m-d h:i:s"),
			 "data"=>$data
		];
		return $return;
	}
}
