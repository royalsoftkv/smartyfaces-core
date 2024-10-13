<?php

function smarty_function_sf_inplace($params, $template)
{
    $tag="sf_inplace";
    $attributes_list=array("id","value","required","disabled","class","rendered","validator",
    		"attachMessage","action");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    
    $attributes['emptytext']=array(
    		"required"=>false,
    		"default"=>"",
    		"desc"=>"Defines text that will be displayed if value is empty");
    $attributes['type']=array(
    		"required"=>false,
    		"default"=>"text",
    		"desc"=>"Defines how component will be displayed. Possible values are text and textarea");
    if($params==null and $template==null) return $attributes;
    
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $id=SmartyFacesComponent::checkNested($id,$template);
    
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    if(!$rendered) return;
    
   
    
    if($validator!=null) {
    	SmartyFacesContext::addValidator($id, $validator);
    }
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    
    SmartyFacesContext::$bindings[$id]=$value;
    
    $class.=" sf-inplace";
    
	if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = SmartyFacesContext::$formData[$id];
		if(SmartyFacesComponent::validationFailed($id)) {
    		$class.=" sf-vf is-invalid";
    	}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
    }


    $action_str="";
    if(!is_null($action)){
    	$stateless=SmartyFacesComponent::$stateless;
    	if($stateless) {
    		$action="'".$action."'";
    		$data=array();
    		$data['immediate']=$immediate;
    		$data_str=htmlspecialchars(json_encode($data));
    	} else {
    		$action="null";
    		$data_str="null";
    	}
    	$action_str='SF.a(this,'.$action.','.$data_str.'); return false;"';
    }
    
    
	$onclick="SF.inplace.show(this)";
	if($disabled) $onclick="return false;";
	
	$span=new TagRenderer("span",true);
	$span->setAttributeIfExists("class", $class);
	if($disabled){
		$span->setAttribute("disabled", "disabled");
	}
	$label=new TagRenderer("label",true);
	$label_class="sf-inplace-lbl";
	$label_class.=" text-muted";
	$label->setAttributeIfExists("class", $label_class);
	$label->setAttribute("onclick", $onclick);
	$label->setValue(strlen(trim($value))==0 ? $emptytext : $value);
	$span->addHtml($label->render());
	
	if($type=="text") {
		$input=new TagRenderer("input");
		$input->setAttribute("type", "text");
		$input->setIdAndName($id);
		$input->setValue($value);
		$input_class="";
		$input_class="sf-inplace-fld";
		$input_class.=" form-control";
		$input->setAttributeIfExists("class", $input_class);
		$input->setAttribute("onblur", 'SF.inplace.blur(this,\''.$emptytext.'\');'.$action_str);
		$span->addHtml($input->render());
	} elseif ($type="textarea") {
		$textarea=new TagRenderer("textarea",true);
		$textarea->setIdAndName($id);
		$textarea_class="";
		$textarea_class="sf-inplace-fld";
		$textarea_class.=" form-control";
		$textarea->setAttributeIfExists("class", $textarea_class);
		$textarea->setAttribute("onblur", 'SF.inplace.blur(this,\''.$emptytext.'\');'.$action_str);
		$textarea->setValue($value);
		$span->addHtml($textarea->render());
	} 
	
	$s=$span->render();

    if($attachMessage and !$disabled) {
    	if(SmartyFaces::$validateFailed && isset(SmartyFacesMessages::$messages[$id][0])) {
    		$m_div=new TagRenderer("div",true);
    		$m_div->setAttribute("class", "invalid-feedback");
            $m_div->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$s.=$m_div->render();
    	}
    }
    return $s;
}

?>