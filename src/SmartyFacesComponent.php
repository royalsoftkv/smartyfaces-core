<?php

class SmartyFacesComponent {
    
    public static $current_view;
    public static $current_view_id;
    public static $current_state_id;
    public static $current_template_id;
    public static $stateless;
    public static $sf_vars=array();
    public static $action_component;
    
    public static function getRequiredParameter($tag,$name,$params){
        if(!array_key_exists($name, $params)){
            throw new Exception("Required attribute $name for tag $tag is missing!");
        }
        return $params[$name];
    }
    
    public static function getParameter($tag,$name,$default,&$params){
        if(array_key_exists($name, $params)){
            return $params[$name];
        } else {
        	if(defined("SMARTYFACES_PARAM_".strtoupper($name))) {
        		$default = constant("SMARTYFACES_PARAM_".strtoupper($name));
        	}
        	$params[$name]=$default;
            return $default;
        }
    }
    
    public static function getInstance($name,$scope=null,$args=null, $class=null){
        $obj = SmartyFacesContext::lookup($name,$scope);
        if($obj==null) {
        	if($class==null) {
	            $className=strtoupper(substr($name, 0, 1)).substr($name, 1);
        	} else {
        		$className=$class;
        	}
            if($args==null) {
	            $obj=new $className();
            } else {
            	$obj=new $className($args);
            }
            SmartyFacesContext::$components[$scope][$name]=$obj;
        }
        return $obj;
    }
    
    public static function getPassedAttributes($tag, $params, $attr) {
        $arr=array();
        foreach($attr as $a){
            if(array_key_exists($a, $params)){
                $arr[$a]=$params[$a];
            }
        }
        $list=array();
        foreach($arr as $key=>$val){
            $list[]=$key.'="'.$val.'"';
        }
        if(count($list)>0){
            return " ".implode(" ", $list)." ";
        } else {
            return "";
        }
    }
    
    public static function getDefaultId(){
    	//TODO:check if there is duplicate in current view
    	$btr = debug_backtrace();
    	$defaultId=$btr[1]['line'];
    	return "c". $defaultId;
    }
    
    //TESTING
    public static function getDefaultIdNew(){
    	$btr = debug_backtrace();
    	if(strpos($btr[3]['function'],"smarty_template_function")===0) {
    		$defaultId=$btr[3]['line']."-".$defaultId=$btr[2]['line'];
    	} else {
	    	$defaultId=$btr[2]['line'];
    	}
    	$prefix = "c";
    	return $prefix.$defaultId;
    }
    
    public static function createComponent($id,$tag,$params,$additional_server_params=array()){
    	if(SmartyFaces::$config['remove_unused_params']){
	    	$server_params=self::commonServerParameters();
	    	$server_params=array_merge($server_params,$additional_server_params);
	    	$params2=array();
	    	foreach($params as $key=>$val) {
	    		if(in_array($key, $server_params)) {
	    			$params2[$key]=$val;
	    		}
	    	}
    	} else {
    		$params2=$params;
    	}
   		SmartyFacesContext::$components["event"]["component.$id"]=array("tag"=>$tag,"params"=>$params2);
    }
    
    static function commonServerParameters() {
    	return array("action","immediate","update","oncomplete","events","id");
    }
    
    public static function checkInRegion($id,$template){
    	if($template->compile_id!=null) {
    		return $template->compile_id."_".$id;
    	} else {
    		return $id;
    	}
    }
    
    public static function checkNested($id,$template){
    	$repeatable_tags=array("sf_repeat","sf_column");
    	if(isset($template->smarty->_cache['_tag_stack']) && isset($template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][0])) {
    		$parent_tag = $template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][0];
    		if(!in_array($parent_tag, $repeatable_tags)) return $id;
    	
    		if($parent_tag=="sf_repeat") {
    			$index=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2]['index'];
    		} else if ($parent_tag=="sf_column") {
    			$index=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-2][2]['index'];
    		}
    		return $id."-".$index;
    	} else {
    		return $id;
    	}
    }
    
    private static $SESSION_CHECK_INTERVAL=10;
    
    public static function setCurrentStateId() {
    	$ajax=SmartyFaces::$ajax;
    	$newStateId=null;
    	if(!$ajax) {
    		//initial view
    		$limit=SmartyFaces::$config['states_limit'];
    		$state=SmartyFacesContext::getState();
    		if($limit>0 and isset($state[self::$current_view][self::$current_view_id])) {
	    		$oldSessions=$state[self::$current_view][self::$current_view_id];
	    		if(count($oldSessions)>$limit) {
		    		uasort($oldSessions, array('SmartyFacesComponent', 'sortStates'));
		    		$keys=array_keys($oldSessions);
	    			for($j=0;$j<count($oldSessions)-$limit;$j++) {
	    				unset($state[self::$current_view][self::$current_view_id][$keys[$j]]);
	    				SmartyFacesContext::setState($state);
	    			}
	    		}
	    		
    		}
    		$newStateId = null;
    	} else {
    		$newStateId=SmartyFacesComponent::$current_state_id;
    	}
    	if($newStateId==null) {
    		$newStateId=uniqid();
    	}
    	self::$current_state_id=$newStateId;
    }
    
    private static function sortStates($s1,$s2) {
    	$ajax1=$s1['ajax'];
    	$ajax2=$s2['ajax'];
    	$timestamp1=$s1['timestamp'];
    	$timestamp2=$s2['timestamp'];
    	return strcmp($timestamp1,$timestamp2);
    }
    
    static function getFacet($template, $name) {
    	$this_tag_stack=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];
    	if(isset($this_tag_stack['facets'][$name])) {
    		return $this_tag_stack['facets'][$name]['content'];
    	} else {
    		return null;
    	}
    }
    
    static function renderMessage($id) {
    	$s="";
    	if(isset(SmartyFacesMessages::$messages[$id])){
    		$errors=array();
    		foreach(SmartyFacesMessages::$messages[$id] as $message){
    			$type=$message['type'];
    			$m=$message['message'];
    			$msg_class="d-sf-msg-".$type;
				if($type=="error") {
					$msg_class.=" invalid-feedback";
				}
    			$errors[]='<span class="'.$msg_class.'">'.$m.'</span>';
    		}
    		$s=implode("", $errors);
    	}
    	return $s;
    }
    
    static function getFormControlValidationClass($id) {
    	if(isset(SmartyFacesMessages::$messages[$id][0])){
    		$type=SmartyFacesMessages::$messages[$id][0]['type'];
    		if($type==SmartyFacesMessages::ERROR) return "";
    		if($type==SmartyFacesMessages::WARNING) return "";
    		if($type==SmartyFacesMessages::SUCCESS) return "";
    		if($type==SmartyFacesMessages::INFO) return "";
    	}
    }
    
	static function validationFailed($id) {
		return SmartyFacesMessages::errorMessageExists($id);
	}
	
	static function proccessAttributes($tag,$attributes,&$params) {
		//first check
		$param_names=array_keys($params);
		$attr_names=array_keys($attributes);
		foreach($param_names as $param_name) {
			if(!in_array($param_name, $attr_names)) {
				throw new Exception("Unknown parameter $param_name for tag $tag");
			}
		}
		foreach ($attributes as $attr_name=>$attribute) {
			$attr_required=$attribute['required'];
			$attr_default=isset($attribute['default']) ? $attribute['default'] : null;
			if($attr_default==='SmartyFacesComponent::getDefaultId()') {
				$attr_default=SmartyFacesComponent::getDefaultIdNew();
			}
			$attr_type=isset($attribute['type']) ? $attribute['type'] : 'string';
			if($attr_required) {
				$params[$attr_name]=SmartyFacesComponent::getRequiredParameter($tag, $attr_name, $params);
			} else {
				$params[$attr_name]=SmartyFacesComponent::getParameter($tag, $attr_name, $attr_default, $params);
			}
			if($attr_type=='bool') {
				$params[$attr_name]=(bool)($params[$attr_name]);
			}
		}
		return $params;
	}
	
	static function getDefinedAttributes() {
		$attributes=array(
			'value'=> array(
					'required'=>true,
					'desc'=>'Value of the component as string value or expression which binds to some bean property'),
			'id' => array(
					'required'=>false,
					'default'=>'SmartyFacesComponent::getDefaultId()',
					'desc'=>'Id of component, autogenerated if it is not set'),
			'required' => array(
					'required'=>false,
					'default'=>false,
					'type'=>'bool',
					'desc'=>'Indicates if user must enter some value for this component'),
			'size' => array(
					'required'=>false,
					'default'=>'',
					'desc'=>'Size of input field'),
			'type' => array(
					'required'=>false,
					'default'=>'text',
					'desc'=>'Type of input field, can be text or password'),
			'validator' => array(
					'required'=>false,
					'default'=>null,
					'desc'=>'Validator expression attached to this component and processed on submit'),
			'class' => array(
					'required'=>false,
					'default'=>'',
					'desc'=>'CSS style class of component'),
			'style' => array(
					'required'=>false,
					'default'=>'',
					'desc'=>'CSS inline style of component'),
			'disabled' => array(
					'required'=>false,
					'default'=>false,
					'type'=>'bool',
					'desc'=>'If set to true renders component as disabled'),
			'rendered' => array(
					'required'=>false,
					'default'=>true,
					'type'=>'bool',
					'desc'=>'If set to false component will not be rendered'),
			'attachMessage' => array(
					'required'=>false,
					'default'=>false,
					'type'=>'bool',
					'desc'=>'Validation message is displayed beside component if validation failed for this component'),
			'converter' => array(
					'required'=>false,
					'default'=>null,
					'desc'=>'Conversion expression attached to this component and processed on submit and on render'),
			'title' => array(
					'required'=>false,
					'default'=>null,
					'desc'=>'Title attribute of component'),
			'action'=>array(
					'required'=>false,
					'default'=>null,
					'desc'=>'Ajax action'),
			'immediate'=>array(
					'required'=>false,
					'default'=>false,
					'desc'=>'Indicates if invoced action will be immidiately i.e it will skip validation phase'),
			'onclick'=> array(
					'required'=>false,
					'default'=>null,
					'desc'=>'javascript function that will be executed when componentis clicked'),
			'events'=>array(
					'required'=>false,
					'default'=>null,
					'desc'=>'array of ajax event which can be invoked on component'),
			'update'=>array(
					'required'=>false,
					'default'=>null,
					'desc'=>'Id of region which will be updated with action'),
			'custom'=>array(
					'required'=>false,
					'default'=>'',
					'desc'=>'string which will be passed as it is'),
			// events
			'onchange'=>array(
				'required'=>false,
				'default'=>null,
				'desc'=>'javascript function that will be executed on change event'),
			'onkeyup'=>array(
				'required'=>false,
				'default'=>null,
				'desc'=>'javascript function that will be executed on key up event')
				
		);
		return $attributes;
	}

	
	static function resolveAttributtes($list, $registered_events=null) {
		if($registered_events!=null and is_array($registered_events)) {
			$list=array_merge($list,$registered_events);
		}
		$defined_attr=self::getDefinedAttributes();
		$attrs=array();
		foreach($list as $attr) {
			$attrs[$attr]=$defined_attr[$attr];
		}
		return $attrs;
	}
	
	static function encodeEvents($events,$params,$registered_events=null) {
		$s="";
		$event_arr=array();
		if($registered_events!=null and is_array($registered_events)) {
			foreach ($registered_events as $registered_event) {
				if(isset($params[$registered_event])) {
					$event_arr[$registered_event][]=$params[$registered_event];
				}
			}
		}
		if($events!=null){
			foreach($events as $type=>$event) {
				$data=array();
				if(is_array($event)) {
					if(isset($event['actionData'])) $data[]="actionData:".$event['actionData'];
					if(isset($event['oncomplete'])) $data[]="oncomplete:function(){".$event['oncomplete']."}";
				}
				if(count($data)>0) {
					$data="{".implode(",", $data)."}";
					$event_arr["on$type"][]="SF.e(event,$data)";
				} else {
					$event_arr["on$type"][]="SF.e(event)";
				}
			}
		}
		foreach($event_arr as $event_name=>$event_list) {
			$event_string=implode(";",$event_list);
			$s.=" $event_name=\"$event_string\" ";
		}
		return $s;
	}
	
	
	static function processEvents($id,$events,&$params) {
		if(isset(SmartyFacesContext::$ajaxEvents[$id])) {
			$events=SmartyFacesContext::$ajaxEvents[$id];
			$params['events']=$events;
		}
		return $events;
	}
	
	static function buildJsAction($params) {
		$stateless=SmartyFacesComponent::$stateless;
		if($stateless) {
			$action="'".$params['action']."'";
			$data=array();
			$data['immediate']=$params['immediate'];
			$data_str=htmlspecialchars(json_encode($data));
		} else {
			$action="null";
			$data_str="null";
		}
		$s="SF.a(event.target,".$action.",'".$data_str."');";
		return $s;
	}

}

?>
