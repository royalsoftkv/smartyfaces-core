<?php

class SmartyFacesContext {

	public static $scopes =array("event","page","session");

	public static $components = array();

	public static $bindings=array();

	public static $validators=array();

	public static $converters=array();

	public static $regions=array();

	public static $formData=array();

	public static $formVars=array();

	public static $storestate="server";
	public static $store_states=array("server","client");
	public static $state = array();

	public static $actionData;
	public static $ajaxEvents=array();
	public static $popupsCount;
	public static $hasPopups;
    public static $default_button = array();

	public static function clearState(){
		self::$components=array();
		self::$bindings=array();
	}


	public static function storeState(){
		$state_id= SmartyFacesComponent::$current_state_id;
		$view=SmartyFacesComponent::$current_view;
		$sf_view_id=SmartyFacesComponent::$current_view_id;

		self::$state["components"]=self::$components;
		self::$state["bindings"]=self::$bindings;
		self::$state["validators"]=self::$validators;
		self::$state["converters"]=self::$converters;
		self::$state["regions"]=self::$regions;
		self::$state["timestamp"]=date("r");
		self::$state["ajax"]=(SmartyFaces::$ajax ? 1 : 0);

		if(SmartyFacesContext::$storestate=="server") {
			$state=self::getState();
			$state[$view][$sf_view_id][$state_id]=self::$state;
			self::setState($state);
		}
		self::storeSessionState();
		SmartyFacesLogger::log("Stored state for session id=".session_id());

	}

	public static function storeSessionState() {
		if(isset(self::$components['session']) && count(self::$components['session'])>0) {
			SFSession::set(['SF_SESSION','components'], self::$components['session']);
		}
	}

	public static function restoreState(){
		$state_id= SmartyFacesComponent::$current_state_id;
		$view=SmartyFacesComponent::$current_view;
		$sf_view_id=SmartyFacesComponent::$current_view_id;

		if(SmartyFacesContext::$storestate=="server") {
			$state=self::getState();
			if(isset($state[$view][$sf_view_id][$state_id])) {
				self::$state=$state[$view][$sf_view_id][$state_id];
			} else {
				jQuery::addMessage("Your view is expired. Page will be reloaded!");
				SFSession::delete('SF_SESSION');
				SmartyFaces::reload();
				jQuery::getResponse();
				exit();
			}
		} else {
			if(SmartyFaces::$config['compress_state']) {
				self::$state=unserialize(gzinflate(base64_decode(self::$formData['sf_state_id'])));
			} else {
				self::$state=unserialize(base64_decode(self::$formData['sf_state_id']));
			}
		}

		self::$components=self::$state["components"];
		self::$bindings=self::$state["bindings"];
		self::$validators=self::$state["validators"];
		self::$converters=self::$state["converters"];
		self::$regions=self::$state["regions"];

		if(is_array(self::$components) and count(self::$components)>0) {
			foreach(self::$components as $scope=>$components) {
				if(is_array($components)) {
					foreach($components as $name => $component) {
						if(is_object($component)) {
							SmartyFaces::$GLOBALS[$name]=$component;
						}
					}
				}
			}
		}
		self::restoreSessionState();
	}

	public static function restoreSessionState(){
		$components = SFSession::get(['session','components'], []);
		if(isset(self::$components['session'])) {
			self::$components['session']=array_merge(self::$components['session'],$components);
		} else {
			self::$components['session']=$components;
		}
		if(is_array($components)) {
			foreach($components as $name=>$component){
				if(is_object($component)) {
					SmartyFaces::$GLOBALS[$name]=$component;
				}
			}
		}
	}

	public static function lookup($name,$scope){
		$obj=null;
		if($scope==null){
			foreach(self::$scopes as $s){
				if(isset(self::$components[$s][$name])){
					$obj= self::$components[$s][$name];
					return $obj;
				}
			}
		} else {
			if(isset(self::$components[$scope][$name])){
				$obj= self::$components[$scope][$name];
				return $obj;
			}
		}
		return $obj;
	}

	public static function addRequiredValidator($id){
		self::$validators[$id][]="SmartyFacesValidator::validateRequired";
	}

	public static function addValidator($id,$validator){
		self::$validators[$id][]=$validator;
	}

	public static function addConverter($id,$validator){
		self::$converters[$id][]=$validator;
	}

	static function getFormVar($name) {
		$value=SmartyFacesContext::$formVars[$name];
		return $value;
	}

	static function getFormData($name, $default=null) {
		if(isset(SmartyFacesContext::$formData[$name])) {
			$value=SmartyFacesContext::$formData[$name];
		} else {
			$value = $default;
		}
		return $value;
	}

	static function setFormVar($name,$value) {
		self::$formVars[$name]=$value;
	}

	static function getFormVars() {
		return SmartyFacesContext::$formVars;
	}

	static function getSubmittedValue($name) {
		$sf_form_data=$_POST['sf_form_data'];
		parse_str($sf_form_data, $formData);
		return $formData[$name];
	}

	static function reset() {
		self::$components = array();
		self::$bindings=array();
		self::$validators=array();
		self::$converters=array();
		self::$regions=array();
		self::$formData=array();
		self::$formVars=array();
		self::$ajaxEvents=array();
	}

	//NOTE: slow function
	static function getStateInfo() {
		$state=self::getState();
		$data['view']=SmartyFacesComponent::$current_view;
		$data['view_id']=SmartyFacesComponent::$current_view_id;
		$data['state']=SmartyFacesComponent::$current_state_id;
		$data['cookies']=$_COOKIE;
		$data['get']=$_GET;
		$data['post']=$_POST;
		$data['views_count']=count($state);
		$data['view_ids_count']=0;
		$data['state_ids_count']=0;
		$data['SmartyFaces::config']=SmartyFaces::$config;
		if(is_array($state)) {
			foreach($state as $view=>$view_ids){
				$data['view_ids_count']+=count($view_ids);
				if(is_array($view_ids)) {
					foreach($view_ids as $view_id=>$state_ids) {
						$data['state_ids_count']+=count($state_ids);
					}
				}
			}
		}
		if(SmartyFaces::$config['compress_state']) {
			$rawState = SFSession::get(['SF_SESSION'],['state']);
			$data['size']=strlen($rawState);
			$data['compress_state']['show']=$rawState;
		} else {
			$data['size']=strlen(serialize($state));
			$data['compress_state']="";
		}
		$data['tree']=$state;

		$out = print_r($data, true);
		 
		// replace something like '[element] => <newline> (' with <a href="javascript:toggleDisplay('...');">...</a><div id="..." style="display: none;">
		$out = @preg_replace('/([ \t]*)(\[[^\]]+\][ \t]*\=\>[ \t]*[a-z0-9 \t_]+)\n[ \t]*\(/iUe',"'\\1<a href=\"javascript:toggleDisplay(\''.(\$id = substr(md5(rand().'\\0'), 0, 7)).'\');\">\\2</a><div id=\"'.\$id.'\" style=\"display: none;\">'", $out);
		 
		// replace ')' on its own on a new line (surrounded by whitespace is ok) with '</div>
		$out = preg_replace('/^\s*\)\s*$/m', '</div>', $out);
		 
		// print the javascript function toggleDisplay() and then the transformed output
		return '<script language="Javascript">function toggleDisplay(id) { document.getElementById(id).style.display = (document.getElementById(id).style.display == "block") ? "none" : "block"; }</script>'."\n<pre>$out</pre>";

	}

	static function getState() {
		if(SmartyFaces::$config['compress_state']) {
			$state = SFSession::get(['SF_SESSION','state'],[]);
			if(is_array($state)) return array();
			return unserialize(gzinflate($state));
		} else {
			$state = SFSession::get(['SF_SESSION','state'], []);
			return $state;
		}
	}

	static function setState($state) {
		if(SmartyFaces::$config['compress_state']) {
			$state = gzdeflate(serialize($state));
		}
		SFSession::set(['SF_SESSION','state'], $state);
	}

	static function startSocket($host, $port) {
		//TODO check if daemon is running
		$params = 'SF_SOCKET_HOST='.$host. ' SF_SOCKET_PORT='.$port. ' ';
		$socket_server_file = __DIR__ . '/socket/server.php';
		$cmd="$params php $socket_server_file start -d";
		shell_exec($cmd);
	}

}

?>
