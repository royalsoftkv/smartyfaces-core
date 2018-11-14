<?php 

class SFSession {
	
	/**
	 * 
	 * @var SFSession
	 */
	private static $_instance;
	public $session;
	public $write=false;
	private $session_vars;
	private $delete_vars = [];
	private $non_blocking=true;
	
	function __construct() {
		$this->registerShutdown();
	}
	
	public static function instance() {
		if(self::$_instance === null) {
			self::$_instance = new SFSession();
		}
		return self::$_instance;
	}

	function registerShutdown() {
		register_shutdown_function(function() {
			$a=$_SERVER['REQUEST_URI'];
			SFSession::write();
		});
	}
	
	function start() {
		ob_start();
		session_start();
		if($this->non_blocking) {
			$this->session=$_SESSION;
			session_write_close();
		}
	}
	
	function get_from_session($name,$default=null) {
		if($this->non_blocking) {
			if(is_array($name)) {
				$res = $this->get_array($this->session, $name);
				if(is_null($res)) {
					return $default;
				} else {
					return $res;
				}
			} else {
				if(isset($this->session[$name])) {
					return $this->session[$name];
				} else {
					return $default;
				}
			}
		} else {
			if(is_array($name)) {
				return $this->get_array($_SESSION, $name);
			} else {
				if(isset($_SESSION[$name])) {
					return $_SESSION[$name];
				} else {
					return $default;
				}
			}
		}
	}
	
	function set_to_session($name, $value) {
		if($this->non_blocking) {
			if(is_array($name)) {
				$this->set_array($this->session, $name, $value);
			} else {
				$this->session[$name]=$value;
			}
			$this->session_vars[]=array("path"=>$name,"value"=>$value);
			$this->write=true;
		} else {
			if(is_array($name)) {
				$this->set_array($_SESSION,$name, $value);
			} else {
				$_SESSION[$name]=$value;
			}
		}
	}
	
	function set_array(&$object, $path, $value) {
		$temp = &$object;
	
		foreach($path as $key) {
			$temp =& $temp[$key];
		}
		$temp = $value;
	}
	
	function get_array($obj, $path) {
		$temp = &$obj;
	
		foreach($path as $key) {
			$temp =& $temp[$key];
		}
		return $temp;
	}

	function unset_array(&$array, $path) {
		$temp = & $array;

		foreach($path as $key) {
			if(isset($temp[$key])){
				if(!($key == end($path))) $temp = &$temp[$key];
			} else {
				return false; //invalid path
			}
		}
		unset($temp[end($path)]);
	}

	function exists_array($array, $path) {
		$temp = & $array;

		foreach($path as $key) {
			if(isset($temp[$key])){
				$temp = &$temp[$key];
			} else {
				return false; //invalid path
			}
		}
		return true;
	}

	function write_session() {
		if($this->non_blocking) {
			if($this->write) {
				
				session_start();
				if(is_array($this->session_vars)) {
					foreach($this->session_vars as $session_var) {
						if(is_array($session_var['path'])) {
							$this->set_array($_SESSION, $session_var['path'], $session_var['value']);
						} else {

							$_SESSION[$session_var['path']]=$session_var['value'];
						}
					}
				}
				if(is_array($this->delete_vars)) {
					foreach($this->delete_vars as $path) {
						if(is_array($path)) {
							$this->unset_array($_SESSION, $path);
						} else {
							unset($_SESSION[$path]);
						}
					}
				}
				$this->session_vars=array();
				$this->delete_vars=array();
				$this->session=$_SESSION;
				$this->write=false;
			}
		}
		session_write_close();
	}
	
	function exists_in_session($name) {
		if($this->non_blocking) {
			if(is_array($name)) {
				return $this->exists_array($this->session, $name);
			} else {
				return isset($this->session[$name]);
			}
		} else {
			return isset($_SESSION[$name]);
		}
	}
	
	function remove_from_session($key){
		if($this->non_blocking) {
			$this->write=true;
			if(is_array($key)) {
				$this->unset_array($this->session, $key);
			} else {
				unset($this->session[$key]);
			}
			$this->delete_vars[]=$key;
		}  else {
			unset($_SESSION[$key]);
		}
	}
	
	function clear_session() {
		if($this->non_blocking) {
			$this->write=true;
			$this->session=null;
		} else {
			unset($_SESSION);
		}
	}
	
	static function get($name, $default=null) {
		$instance = SFSession::instance();
		return $instance->get_from_session($name,$default);
	}
	
	static function set($name, $value) {
		$instance = SFSession::instance();
		$instance->set_to_session($name, $value);
	}
	
	static function write() {
		$instance = SFSession::instance();
		$instance->write_session();
	}
	
	static function exists($name) {
		$instance = SFSession::instance();
		return $instance->exists_in_session($name);
	}
	
	static function delete($key) {
		$instance = SFSession::instance();
		$instance->remove_from_session($key);
	}
	
	static function clear() {
		$instance = SFSession::instance();
		$instance->clear_session();
	}

	static function addToArray($name, $value, $default=null) {
		$sess_arr = SFSession::get($name,$default);
		$sess_arr[]=$value;
		SFSession::set($name, $sess_arr);
	}

	static function setArray($arr) {
		foreach ($arr as $name=>$value) {
			self::set($name, $value);
		}
	}

	static function toggle($name) {
		if(self::get($name, false)) {
			self::set($name, false);
		} else {
			self::set($name, true);
		}
	}

	static function setIfNotExists($name, $value) {
		if(!self::exists($name)) {
			self::set($name, $value);
		}
		return self::get($name);
	}

}



?>