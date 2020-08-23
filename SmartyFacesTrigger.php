<?php 

class SmartyFacesTrigger {
	
	static $triggers;
	
	const NOT_AUTHORIZED_AJAX = "NOT_AUTHORIZED_AJAX";
	const CUSTOM_REQUEST_PROCESS = "CUSTOM_REQUEST_PROCESS";
	
	static function set_trigger($trigger_name, $callback) {
		self::$triggers[$trigger_name]=$callback;
	}
	
	static function trigger($trigger_name, $params=null) {
		$triggers = self::$triggers;
		if(isset($triggers[$trigger_name])) {
			call_user_func($triggers[$trigger_name], $params);
		}
	}
	
	
	
}

?>
