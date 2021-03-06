<?php
	class REST {
		
		public $_allow = array();
		public $_content_type = "application/json";
		public $_request = array();
		
		private $_method = "";		
		private $_code = 200;
		
		public function __construct(){
			$this->inputs();
			//$this->accessLog();
		}
		
		public function get_referer(){
			return $_SERVER['HTTP_REFERER'];
		}
		
		public function response($data,$status){
			$this->_code = ($status)?$status:200;
			$this->set_headers();
			//LogMetric::dump_log(array_keys($data), array_values($data)); 
			echo $data;
			exit;
		}
		
//		public function array_empty($array){
//
//			foreach($array as $k => $v){
//				if(empty($v))
//					return 1;                                      
//			}
//			return 0;
//		}
//		public static function lettersOnly($value="") {
//			if (!preg_match("/^[\pL]*$/", $value)) 
//					return false;
//			return true;
//
//		} 
//                public static function characters($value="") {
//			if (preg_match("/^[A-Za-z ]*$/", $value)) 
//					return true;
//			return false;
//
//		}
		public function filter_response($array, $required,$resursive = False){
			//print_r($array);
			$return = array();
			foreach($required as $k => $v){
				if(array_key_exists($v, $array)  ){
					if(is_array($array[$v]) && $resursive){
						foreach($array[$v] as $key => $value){
							$return[$v][$key] = $this->filter_response($value, $required);
						}
					}
					else{
						$return[$v] = $array[$v];
					}
				}
			}
			return $return;
		}
                
		private function get_status_message(){
			$status = array(
						100 => 'Continue',  
						101 => 'Switching Protocols',
						105 => 'Invalid Key',  
						200 => 'OK',
						201 => 'Created',  
						202 => 'Accepted',  
						203 => 'Non-Authoritative Information',  
						204 => 'No Content',  
						205 => 'Reset Content',  
						206 => 'Partial Content',  
						300 => 'Multiple Choices',  
						301 => 'Moved Permanently',  
						302 => 'Found',  
						303 => 'See Other',  
						304 => 'Not Modified',  
						305 => 'Use Proxy',  
						306 => '(Unused)',  
						307 => 'Temporary Redirect',  
						400 => 'Bad Request',
						401 => 'Invalid Token',  
						402 => 'Payment Required',  
						403 => 'Forbidden',  
						404 => 'Not Found',  
						405 => 'Method Not Allowed',  
						406 => 'Not Acceptable',  
						407 => 'Proxy Authentication Required',  
						408 => 'Request Timeout',  
						409 => 'Conflict',  
						410 => 'Gone',  
						411 => 'Length Required',  
						412 => 'Precondition Failed',  
						413 => 'Request Entity Too Large',  
						414 => 'Request-URI Too Long',  
						415 => 'Unsupported Media Type',  
						416 => 'Requested Range Not Satisfiable',  
						417 => 'Expectation Failed',  
						500 => 'Internal Server Error',  
						501 => 'Not Implemented',  
						502 => 'Bad Gateway',  
						503 => 'Service Unavailable',  
						504 => 'Gateway Timeout',  
						505 => 'HTTP Version Not Supported');
			return ($status[$this->_code])?$status[$this->_code]:$status[500];
		}
		
		public function get_request_method(){
			return $_SERVER['REQUEST_METHOD'];
		}
		
		private function inputs(){
                    global $REQ;
                    $data = $REQ;
//                    print_r($data);die;
//                    $this->_request = $this->cleanInputs($data);
                    switch($this->get_request_method()){
                        case "POST":
//                            die('here');
                            /* If header type is Json then this code will execute*/
                            if($_SERVER['CONTENT_TYPE']=='application/json'){
                                if(empty($data)){
                                    $msg = array('status' => "Error", "msg" => "Invalid Json format");
                                    $this->response($this->json($msg), 400);
                                }
                                
                            }
                            $this->_request = $this->cleanInputs($data);
                            break;
                        case "GET":
                        case "DELETE":
                            $this->_request = $this->cleanInputs($_GET);
                        break;
                        case "PUT":
                                /* If header type is Json then this code will execute*/
                               if($_SERVER['CONTENT_TYPE']=='application/json'){
                                    if(empty($data)){
                                        $msg = array('status' => "Error", "msg" => "Invalid Json format");
                                        $this->response($this->json($msg), 400);
                                    }
                                                  
                                }
                                
                                $this->_request = $this->cleanInputs($data);

                        break;
                        default:
                            $this->response('',406);
                        break;
                    }
		}
		
		private function cleanInputs($data){
			$clean_input = array();
			if(is_array($data)){
				foreach($data as $k => $v){
					$clean_input[$k] = $this->cleanInputs($v);
				}
			}else{
				if(get_magic_quotes_gpc()){
					$data = trim(stripslashes($data));
				}
				$data = strip_tags($data);
				$clean_input = trim($data);
			}
			return $clean_input;
		}		
		
		private function set_headers(){
			header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
			header("Content-Type:".$this->_content_type);
			if(Registry::get('config.allow_access_control')){
				header("Access-Control-Allow-Origin: *");
			}
		}            
                
                public function json($data){
                    if(is_array($data)){
                        return json_encode($data);
                    }
		}
    /**
     * This method is used for given error message in apis
     * @method setErrorMsg
     * @access public
     * @author Raj Choudhary
     * @param string $status
     * @param string $msg
     * @return void
     */
    public function setErrorMsg($status='failed', $msg='No data found/Invalid request parameter') {
        $finalResponseArray = array();
        $finalResponseArray['status'] = $status;
        $finalResponseArray['msg'] = $msg;
        $finalResponseArray['response'] = array();
        $this->response($this->json($finalResponseArray), 200);
        exit;
    }

	}
?>
