<?php

/**
 * orix pushback api
 */
class orixPushback {
	public $token = null;
	public $data = [];
	public function __construct() {
		return $this->token;
	}
	public function token() {
		if(!empty($this->token)) {
			$this->data = array('token'=>$this->token, 'time_genarate' =>time(), 'error' =>0, 'msg'=>null);
		} else {
			if($_SERVER['REQUEST_METHOD'] !== null && ($_SERVER['REQUEST_METHOD']=='POST')) {
				if($_SERVER['PHP_AUTH_USER']=='dev-Test1') {
					if($_SERVER['PHP_AUTH_PW']=='dheeraj@1234') {
						$this->token = $this->generateToken(); 
						$this->data = array('token'=>$this->token, 'time_genarate'=>time(), 'error'=>0, 'msg'=>null);
					} else {
						$this->data = array('token'=>$this->token, 'time_genarate' =>time(), 'error' =>1, 'msg'=>'Password is wrong');
					}
				} else {
					$this->data = array('token'=>$this->token, 'time_genarate' =>time(), 'error' =>1, 'msg'=>'Username is wrong');
				}
			} else {
				$this->data = array('token'=>$this->token, 'time_genarate' =>time(), 'error' =>1, 'msg'=>'Method is not allowed');
			}
		}
		return $this->handleReturn();
	}	
	public function BookingTripStartDetails() {

		print_r($_POST);
		// echo "<br>1111111";
		// if($headers = getallheaders()) {
		// 	 if (isset($headers['Authorization'])) {
		//         $authHeader = $headers['Authorization'];
		//         if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
		//             print_r($matches[1]);
		//             if($matches[1] == $this->token) {
		// 			 // $this->data = array('data1'=>'data1', 'data2' =>'data2');
		// 			 // return $this->handleReturn();
		//             }else{
		// 			 // $this->data = array('time_genarate' =>time(), 'error' =>1, 'msg'=>'Bearer token is not matching');
		// 			 // return $this->handleReturn();
		//             }
		//         }
		//     }
		// }

		$this->data = array('time_genarate' =>time(), 'error' =>1, 'msg'=>'kfhsfkk');
		return $this->handleReturn($status);
	}
	public function handleReturn($status) {
		$return = [
		 "status"=> "success",
		 "requestTime"=> "2019-02-21 16:07:25",
		 "data"=>$this->data
		];
		echo json_encode($return, true);
	}
	public function generateToken($length = 40) {
	    return bin2hex(openssl_random_pseudo_bytes($length));
	}
}
