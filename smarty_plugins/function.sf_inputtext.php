<?php

function smarty_function_sf_inputtext($params, $template)
{
    $tag="sf_inputtext";
    $attributes_list=array("value","id","required","size","type","validator",
    		"class","style","disabled","rendered","attachMessage","converter","title","events","custom");
    $registered_events=array("onchange","onkeyup");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list, $registered_events);
    $attributes['placeholder']=array(
    		"required"=>false,
    		"default"=>"",
    		"desc"=>"Text that will be displayed in component when is empty");
    $attributes['block']=array(
    		"required"=>false,
    		"default"=>false,
    		"type"=>'bool',
    		"desc"=>"Display text input in all avaialable width (Bootstrap skin only)");
    $attributes['readonly']=array(
			'required'=>false,
			'default'=>false,
			'type'=>'bool',
			'desc'=>'If set to true renders component as readonly');

    if(!isset($params['attachMessage']) && isset($params['required']) && $params['required']===true) {
	    $params['attachMessage']=true;
    }

    if($params==null and $template==null) return $attributes;
    
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
	if(!$rendered) return;
	
    
	$id=SmartyFacesComponent::checkNested($id,$template);
	
	$events=SmartyFacesComponent::processEvents($id,$events,$params);
	
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    if(!is_null($validator)) {
    	SmartyFacesContext::addValidator($id,$validator);
    }
	if(strlen($converter ?? "")>0) {
    	SmartyFacesContext::addConverter($id,$converter);
    }
    
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-inputtext";
    if(SmartyFaces::$skin=="bootstrap") $class.=" form-control";
    
    SmartyFacesContext::$bindings[$id]=$value;
    if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = isset(SmartyFacesContext::$formData[$id]) ? SmartyFacesContext::$formData[$id] : null;
    	if(SmartyFacesComponent::validationFailed($id)) {
    		if(SmartyFaces::$skin=="default") $class.=" sf-vf";
    	}
    } else {
    	$value=  SmartyFaces::evalExpression($value);
		if(strlen($converter ?? "")>0) {
	    	$value=$converter::toString($value);
	    }
    }
    $value=htmlentities($value ?? "",ENT_QUOTES,"UTF-8");

    $events=SmartyFacesComponent::encodeEvents($events,$params,$registered_events);
    
    if(SmartyFaces::$skin=="bootstrap") {
    	$span=new TagRenderer("span",true);
    	$span->setAttributeIfExists("class", SmartyFacesComponent::getFormControlValidationClass($id));
    }
    
    $input=new TagRenderer("input");
    $input->setCustom($custom);
    $input->setAttributeIfExists("style", $style);
    $input->setAttribute("type", $type);
    $input->setAttributeIfExists("size", $size);
    if($disabled){
    	$input->setAttribute("disabled", $disabled);
    }
    if($readonly){
    	$input->setAttribute("readonly", "readonly");
    }
    $input->setAttributeIfExists("title", $title);
    $input->setIdAndName($id);
    $input->setValue($value);
    $input->setAttributeIfExists("class", $class);
    $input->passAttributes($attributes_values, array("placeholder"));
    $input->setCustom($events);
    if(SmartyFAces::$skin=="bootstrap" && !$block) {
    	$input->appendAttribute("class", " width-auto");
    }
    
    if(SmartyFaces::$skin=="bootstrap") {
    	$span->addHtml($input->render()); 
    	
    	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
    		$msg_span=new TagRenderer("span",true);
    		$msg_span->setAttribute("class", "help-block");
    		$msg_span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$span->addHtml($msg_span->render());
    	}
    	
    	$s=$span->render();
    	
    } else {
	    $s=$input->render();
	    if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
    }
    
    
    return $s;
}

?>