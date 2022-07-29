<?php


class SmartyFacesSecurity
{

	static $allowedClasses = [];

	static function allow($class) {
		self::$allowedClasses[]=$class;
	}

	static function checkAction($sf_action) {
		$arr = explode("::", $sf_action);
		$class = $arr[0];
		$method = $arr[1];
		if(strpos($method, "(")!== false) {
			$method = substr($method, 0, strpos($method, "("));
		}
		if(!class_exists($class)) {
			self::sendResponseCode(400, "Unknown action class $class");
		}
		if(!method_exists($class, $method)) {
			self::sendResponseCode(400, "Unknown class method $class::$method");
		}
		if(!self::checkClassAccess($class, $method)) {
			self::sendResponseCode(400, "Not allowed action class $class::$method");
		}
	}

	static function sendResponseCode($code, $msg = null) {
		http_response_code($code);
		if($msg) {
			echo $msg;
		}
		exit;
	}

	private static function checkClassAccess($class, $method) {
		foreach(self::$allowedClasses as $row) {
			if(!is_array($row)) {
				//allowed whole class
				if($class == $row) {
					return true;
				}
			} else {
				//[class,[methods]]
				$check_class = $row[0];
				$check_methods = $row[1];
				if($check_methods && !is_array($check_methods)) {
					$check_methods = [$check_methods];
				}
				if($check_class == $class && in_array($method, $check_methods)) {
					return true;
				}
			}
		}
		return false;
	}

}
