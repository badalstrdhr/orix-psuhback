<?php

/**
 * orix pushback api
 */

include_once 'index.php';

class orixPushback {
	protected $token = null;
	protected $expire_at = null;
	protected $functionCalled = null;
	protected $data = [];
	public $CFG;
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
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
	        if($_SERVER['PHP_AUTH_USER'] == USER) {
	        	if($_SERVER['PHP_AUTH_PW'] == SECRET) {
	        		$token = $headers_encoded . '.' . $payload_encoded .'.'. $signature_encoded;
			        $expire_at = date("Y-m-d h:i:s",$headers['expire']);
			        if($token) {
			        	$status = 1;
			        	$msg = 'Token generated';
			        	$data = array('token'=>$token,'expire_at'=>$expire_at);
			        }
	        	} else {
	        		$status = 0;
		        	$msg = 'Password is wrong';
		        	$data = array('token'=>null,'expire_at'=>null);
	        	}
	        } else {
	        	$status = 0;
	        	$msg = 'Username is wrong';
	        	$data = array('token'=>null,'expire_at'=>null);
	        }
        } else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        	$data = array('token'=>null,'expire_at'=>null);
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
		global $CFG;
		$query = "SELECT `original_param` FROM booking_creation WHERE ext_booking_number = '$requestdata->booking_id'";
		if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
			$original_param = json_decode($queryData['original_param']);
			$requestdata->event_name = 'start';
			$requestdata->event_datetime = $requestdata->event_datetime;
			$requestdata->seller_code = SELLER_CODE;
			$requestdata->booking_id = $requestdata->booking_id;
			// $requestdata->garage_pickup_distance = $original_param->garage_pickup_distance;
			// $requestdata->garage_pickup_time = $original_param->garage_pickup_time;
			// $requestdata->current_address = $original_param->current_address;
			$requestdata->current_lat = $requestdata->current_lat;
			$requestdata->current_lng = $requestdata->current_lng;
			// $requestdata->meter_reading = $original_param->meter_reading;
			$requestdata->passcode = $original_param->start_trip_passcode;
			if($getBearerToken = self::getBearerToken()) {
				if($payload = self::Verify($getBearerToken, KEY)) {
					if($payload['id'] == "]OwHd&I;@*fwkc/") {
						$status = 1;
						$msg = "Token validated";
					} else {
						$status = 0;
						$msg = "Token validatation failed";
					}
				} else {
					$status = 0;
					$msg = "Missing payload";
				}
			} else {
				$status = 0;
				$msg = "Missing bearer token";
			}
		} else {
			$status = 0;
			$msg = "Invalid booking details";
		}
		return self::handleReturn($requestdata, $status, $msg);
	}
	public static function BookingTripEndDetails($requestdata) {
		global $CFG;
		$query = "SELECT `original_param` FROM booking_creation WHERE ext_booking_number = '$requestdata->booking_id'";
		if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
			$original_param = json_decode($queryData['original_param']);
			$requestdata->event_name = 'end';
			$requestdata->event_datetime = $requestdata->event_datetime;
			$requestdata->seller_code = SELLER_CODE;
			$requestdata->booking_id = $requestdata->booking_id;
			// $requestdata->current_address = $original_param->current_address;
			$requestdata->current_lat = $requestdata->current_lat;
			$requestdata->current_lng = $requestdata->current_lng;
			// $requestdata->meter_reading = $original_param->meter_reading;
			// $requestdata->drop_garage_distance = $original_param->drop_garage_distance;
			// $requestdata->drop_garage_time = $original_param->drop_garage_time;
			// $requestdata->waiting_time = $original_param->waiting_time;
			// $requestdata->pickup_drop_distance = $original_param->waiting_time;
			$requestdata->passcode = $original_param->end_trip_passcode;
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
		} else {
			$status = 0;
			$msg = "Invalid booking details";
		}
		return self::handleReturn($requestdata, $status, $msg);
	}
	public static function AcceptanceStatus($requestdata) {
		global $CFG;
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
	public static function DriverAndCabDetails($requestdata) {
		global $CFG;
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
	public static function BookingTracking($requestdata) {
		global $CFG;
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
	public static function BookingInvoice($requestdata) {
		global $CFG;
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
