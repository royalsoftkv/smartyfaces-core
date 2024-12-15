<?php

function smarty_function_sf_selectonemenu($params, $template)
{
    $tag="sf_selectonemenu";
    
    $attributes_list=array("id","value","required","disabled","action","attachMessage","class","onchange","rendered",
    "title","immediate","style");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['values']=array(
    		'required'=>true,
    		'default'=>array(),
    		'desc'=>'Array of values to display'
    );
    $attributes['noselect']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Text to display when no option is selected'
    );
    $attributes['var']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Name of the row iteration variable'
    );
    $attributes['val']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Value for data'
    );
    $attributes['label']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Label for display data'
    );
    $attributes['block']=array(
    		"required"=>false,
    		"default"=>false,
    		"type"=>'bool',
    		"desc"=>"Display text input in all avaialable width (Bootstrap skin only)");
    $attributes['optionClass']=array(
    		"required"=>false,
    		"default"=>"",
    		"desc"=>"Expression to evaulate option class");
    $attributes['autocomplete']=array(
    		"required"=>false,
    		"default"=>false,
    		"desc"=>"Render select box as autocomplete"
    );
    $attributes['free_input']=array(
    		"required"=>false,
    		"default"=>false,
    		"desc"=>"If render as autocomplete allow free entry to input box"
    );
    if($params==null and $template==null) return $attributes;

	if(!isset($params['attachMessage']) && isset($params['required']) && $params['required']===true) {
		$params['attachMessage']=true;
	}

    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
    $id=SmartyFacesComponent::checkNested($id,$template);
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-select";
    
    if(!$rendered) return;
    
    $required=(bool) $required;
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    $stateless=SmartyFacesComponent::$stateless;
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    
    SmartyFacesContext::$bindings[$id]=$value;
    
    if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = SmartyFacesContext::$formData[$id];
    	if($value=="null") $value=null;
    	if(SmartyFacesComponent::validationFailed($id)) {
    		if(SmartyFaces::$skin=="default") $class.=" sf-vf";
    	}
    } else {
    	$value=  SmartyFaces::evalExpression($value);
    }
    
    if(!is_null($action)){
    	if($stateless) {
    		$action="'".$action."'";
    		$data=array();
    		$data['immediate']=$immediate;
    		$data_str=htmlspecialchars(json_encode($data));
    	} else {
    		$action="null";
    		$data_str="null";
    	}
    	if(strlen($onchange ?? "")>0 and substr($onchange, -1, 1)!=";") $onchange.=";";
    	$onchange=$onchange.'SF.a(this,'.$action.','.$data_str.'); return false;';
    }
    
    
    $select=new TagRenderer("select",true);
    $select->setAttributeIfExists("class", $class);
    $select->passAttributes($attributes_values, array("style","title"));
    $select->setDisabled($disabled);
    $select->setIdAndName($id);
    $select->setAttributeIfExists("onchange", $onchange);
    if(SmartyFAces::$skin=="bootstrap" && !$block) {
    	$select->appendAttribute("class", " width-auto");
    }
    
    $selected=false;
    if($noselect!=null) {
    	$selected=($value===null);
    	$option=new TagRenderer("option",true);
    	$option->setAttribute("value", "null");
    	$option->setSelected($selected);
    	$option->setValue($noselect);
    	
    	$select->addHtml($option->render());
    	
    }
    if(is_array($values) and count($values)>0) {
	    foreach($values as $key=>$item){
	    	$v=null;
	    	$l=null;
	    	if($var!=null) {
	    		$$var=$item;
	    		if($val==null) {
	    			$v=$item;
	    		} else {
	    			$v=null;eval("\$v=$val;");
	    		}
	    		if($label==null) {
	    			$l=$item;
	    		} else {
	    			$l=null;eval("\$l=$label;");
	    		}
	    	} else {
	    		$v=$key;
	    		$l=$item;
	    	}
	    	if(is_array($l)) $l="Array";
	    	
	    	$option=new TagRenderer("option",true);
	    	$option->setAttribute("value", $v);
	    	$option->setSelected(!$selected && $v==$value);
	    	$option->setValue($l);	    	
	    	if(!empty($optionClass)) {
	    		$c="";eval("\$c=$optionClass;");
	    		$option->setAttribute("class", $c);
	    	}   	
	    	$select->addHtml($option->render());
	    	
	    }
    }
    
    $_s="";
    if(SmartyFaces::$skin=="default") {
	    $_s.=$select->render();
	    if($attachMessage and !$disabled) $_s.=SmartyFacesComponent::renderMessage($id);
    } else {
    	static $attached_combobox;
    	if(!$attached_combobox && $autocomplete) {
    		$url = SmartyFaces::getResourcesUrl() ."/bootstrap-combobox/bootstrap-combobox.js";
    		$_s.=SmartyFaces::addScript($url, true);
    		$url = SmartyFaces::getResourcesUrl() ."/bootstrap-combobox/bootstrap-combobox.css";
    		$_s.='<link type="text/css" rel="stylesheet" href="'.$url.'">';
    	}
    	$select->appendAttribute("class", "form-control");
    	$div=new TagRenderer("div",true);
    	$div->setAttribute("class", SmartyFacesComponent::getFormControlValidationClass($id));
    	$div->appendAttribute("class", "div-select-".$class);
    	if($autocomplete) {
    		$div->appendAttribute("class", "auto-complete");
	    	if($block) {
	    		$div->appendAttribute("class", "auto-complete-block");
	    	}
    	}
    	$div->addHtml($select->render());
    	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
    		$span=new TagRenderer("span",true);
    		$span->setAttribute("class", "help-block");
    		$span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$div->addHtml($span->render());
    	}
    	$_s.=$div->render();
    	if($autocomplete) {
    		$options=array();
    		if($free_input) {
    			$options['freeInput']=true;
    		}
    		$_s.=SmartyFaces::addScript('$("#'.$id.'").combobox('.json_encode($options).');');
    	}
    }
    return $_s;
}

?>