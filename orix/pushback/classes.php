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
		return 'Syncing with orixPushback api to myf reciever api';
	}
	public static function Rqid($payloaddata) {
		$payload = array();
		if(is_array($payloaddata) || is_object($payloaddata)){
			foreach ($payloaddata as $key => $value) {
				array_push($payload, $key."=".$value);
			}
		}
		// print_r($payload);
		$finalpayload = implode("&", $payload);
		$combined_string = $finalpayload . '||' . SECURITY_SALT;
		// echo "\nFinalstrnig: {$combined_string}\n";
		$hash_value = hash('sha256', $combined_string);
		$rqid = strtolower($hash_value);
		// echo "\nFinalstrnig: {$rqid}\n";
		return $rqid;
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
    public static function AcceptanceStatus($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$bookingId = $requestdata->bookingId;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number = '$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				if($requestdata->serviceProviderResponse == 'ACCEPT') {
					$requestDataNew->event_name = "booking_confirmation";
					$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
					$requestDataNew->seller_code = SELLER_CODE;
					$requestDataNew->booking_id = $queryData['client_referance_number'];
					$requestDataNew->ext_booking_number = $bookingId;
					$requestDataNew->accept = "yes";
				}elseif($requestdata->serviceProviderResponse == 'REJECT') {
					$requestDataNew->event_name = "booking_confirmation";
					$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
					$requestDataNew->seller_code = SELLER_CODE;
					$requestDataNew->booking_id = $queryData['client_referance_number'];
					$requestDataNew->ext_booking_number = $bookingId;
					$requestDataNew->accept = "no";
				}else{
					$status = 1;
					$msg = "Invalid request";
				}
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
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function DriverAndCabDetails($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$bookingId = $requestdata->data->bookingId;
			$driverName = $requestdata->data->driverName;
			$driverMobile = $requestdata->data->driverMobile;
			$plateNo = $requestdata->data->plateNo;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number ='$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				if($bookingId) {
					$requestDataNew->event_name = "assigned";
					$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
					$requestDataNew->seller_code = SELLER_CODE;
					$requestDataNew->booking_id = $queryData['client_referance_number'];;
					$requestDataNew->supplier_id = SUPPLIER_ID;
					$requestDataNew->driver_type = "oncall";
					$requestDataNew->driver_name = $driverName;
					$requestDataNew->driver_phone = $driverMobile;
					$requestDataNew->driving_license = "N/A";
					$requestDataNew->car_number = $plateNo;
					$requestDataNew->model_id = $original_param->model_id;
					$requestDataNew->car_model = "N/A";
					$requestDataNew->car_fuel_type = "hybrid";
					$requestDataNew->dispatch_datetime = date("Y-m-d h:i:s",time());
					$requestDataNew->car_changed = "no_change";
					$requestDataNew->reassign = "no";
					$requestDataNew->reassign_reason_id = "N/A";
					$requestDataNew->reassign_reason = "N/A";
				}
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
			}else {
				$status = 0;
				$msg = "Invalid booking details";
			}
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function DriverDispatch($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			// $bookingId = $requestdata->data->bookingId;
			// $driverName = $requestdata->data->driverName;
			// $driverMobile = $requestdata->data->driverMobile;
			// $plateNo = $requestdata->data->plateNo;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number ='$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				if($bookingId) {
					$requestDataNew->event_name = "assigned";
					$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
					$requestDataNew->seller_code = SELLER_CODE;
					$requestDataNew->booking_id = $queryData['client_referance_number'];;
					$requestDataNew->auto_driver_confirm = 1;
					$requestDataNew->current_lat = 0;
					$requestDataNew->current_lng = 0;
					$requestDataNew->dispatch_center_lat = 0;
					$requestDataNew->dispatch_center_lng = 0;
					$requestDataNew->qc_parameter = array("list"=>array(array("parameter_id"=>1,"parameter_value"=>"no"), array("parameter_id"=>2,"parameter_value"=>"yes")));
				}
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
			}else {
				$status = 0;
				$msg = "Invalid booking details";
			}
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function DriverArrived($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			// $bookingId = $requestdata->data->bookingId;
			// $driverName = $requestdata->data->driverName;
			// $driverMobile = $requestdata->data->driverMobile;
			// $plateNo = $requestdata->data->plateNo;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number ='$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				if($bookingId) {
					$requestDataNew->event_name = "arrived";
					$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
					$requestDataNew->seller_code = SELLER_CODE;
					$requestDataNew->booking_id = $queryData['client_referance_number'];;
					$requestDataNew->current_address = "N/A";
					$requestDataNew->current_lat = 0;
					$requestDataNew->current_lng = 0;
				}
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
			}else {
				$status = 0;
				$msg = "Invalid booking details";
			}
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function BookingTripStartDetails($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$bookingId = $requestdata->data->bookingId;
			$eventDatetime = (int)($requestdata->data->eventDatetime);
			$currentLat = $requestdata->data->currentLat;
			$currentLng = $requestdata->data->currentLng;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number = '$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				$requestDataNew->event_name = 'start';
				$requestDataNew->event_datetime = date("Y-m-d h:i:s", $eventDatetime);
				$requestDataNew->seller_code = SELLER_CODE;
				$requestDataNew->booking_id = $queryData['client_referance_number'];;
				$requestDataNew->garage_pickup_distance = 0;
				$requestDataNew->garage_pickup_time = 0;
				$requestDataNew->current_address = "N/A";
				$requestDataNew->current_lat = $currentLat;
				$requestDataNew->current_lng = $currentLng;
				$requestDataNew->meter_reading = 0;
				$requestDataNew->passcode = $original_param->start_trip_passcode;

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
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function BookingTripEndDetails($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$bookingId = $requestdata->data->bookingId;
			$eventDatetime = (int)($requestdata->data->eventDatetime);
			$currentLat = $requestdata->data->currentLat;
			$currentLng = $requestdata->data->currentLng;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number = '$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				$requestDataNew->event_name = 'end';
				$requestDataNew->event_datetime = date("Y-m-d h:i:s", $eventDatetime);
				$requestDataNew->seller_code = SELLER_CODE;
				$requestDataNew->booking_id = $queryData['client_referance_number'];;
				$requestDataNew->current_address = "N/A";
				$requestDataNew->current_lat = $currentLat;
				$requestDataNew->current_lng = $currentLng;
				$requestDataNew->meter_reading = 0;
				$requestDataNew->drop_garage_distance = 0;
				$requestDataNew->drop_garage_time = 0;
				$requestDataNew->waiting_time = 0;
				$requestDataNew->pickup_drop_distance = 0;
				$requestDataNew->passcode = $original_param->end_trip_passcode;
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
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function BookingTracking($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$bookingId = $requestdata->data->bookingId;
			$dutyStatus = $requestdata->data->dutyStatus;
			$lat = $requestdata->data->lat;
			$lng = $requestdata->data->lng;
			$gpsTime = $requestdata->data->gpsTime;
			$query = "SELECT `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number = '$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				$requestDataNew->event_name = "driver_location";
				$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
				$requestDataNew->seller_code = SELLER_CODE;
				$requestDataNew->booking_id = $queryData['client_referance_number'];;
				$requestDataNew->locations = array(
					array(
						"current_trip_status"=>$dutyStatus,
						"lat"=>$lat,
						"lng"=>$lng,
						"time"=>date("Y-m-d h:i:s",time()),
						"gps_time"=>$gpsTime,
						"location_accuracy"=>"",
						"speed"=>"",
						"provider"=>"",
						"bearing"=>"",
						"altitude"=>""
					)
				);
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
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
	}
	public static function BookingInvoice($requestdata) {
		global $CFG;
		$requestDataNew = new stdClass();
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$bookingId = $requestdata->data->bookingId;
			$query = "SELECT `id`, `original_param`, `ext_booking_number`, `client_referance_number` FROM booking_creation WHERE ext_booking_number = '$bookingId'";
			if($queryData = mysqli_fetch_assoc(mysqli_query($CFG, $query))) {
				$original_param = json_decode($queryData['original_param']);
				$requestDataNew->event_name = "generate_invoice";
				$requestDataNew->event_datetime = date("Y-m-d h:i:s",time());
				$requestDataNew->seller_code = SELLER_CODE;
				$requestDataNew->booking_id = $queryData['client_referance_number'];
				$ext_bill_number = date("y")."/ORX/".$queryData['id']."/".$queryData['client_referance_number'];
				$requestDataNew->ext_bill_number = $ext_bill_number;
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
		} else {
        	$status = 0;
        	$msg = 'Method is not allowed';
        }
		return self::handleReturn($requestDataNew, $status, $msg);
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
