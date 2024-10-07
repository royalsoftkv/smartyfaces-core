<?php

function smarty_function_sf_inputtextarea($params, $template)
{
    $tag="sf_inputtextarea";
    
    $attributes_list=array("id","value","required","disabled","class","rendered","validator",
    		"attachMessage","title","style","custom","converter");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['rows']=array(
    	"required"=>false,
    	"default"=>null,
    	"desc"=>"Defines number of rows in textarea"		
    );
    $attributes['cols']=array(
    	"required"=>false,
    	"default"=>null,
    	"desc"=>"Defines number of columns in textarea"		
    );
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
    $id=SmartyFacesComponent::checkNested($id,$template);
    
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    if(!$rendered) return;
    
    if($validator!=null) {
    	SmartyFacesContext::addValidator($id, $validator);
    }
    if(strlen($converter)>0) {
    	SmartyFacesContext::addConverter($id,$converter);
    }
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    
    SmartyFacesContext::$bindings[$id]=$value;
    
    $class.=" form-control width-auto";
    
	if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = SmartyFacesContext::$formData[$id];
		if(SmartyFacesComponent::validationFailed($id)) {
    		$class.=" sf-vf is-invalid";
    	}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
	    if(strlen($converter)>0) {
	    	$value=$converter::toString($value);
	    }
    }


    if(SmartyFaces::$skin=="bootstrap") {
    	$span=new TagRenderer("span",true);
    	$span->setAttributeIfExists("class", SmartyFacesComponent::getFormControlValidationClass($id));
    }
    
    $ta=new TagRenderer("textarea",true);
    $ta->setCustom($custom);
    $ta->setAttributeIfExists("class", $class);
    $ta->setIdAndName($id);
    $ta->passAttributes($attributes_values,array("rows","cols","title"));
    $ta->setAttributeIfExists("style", $style);
    if($disabled) {
    	$ta->setAttribute("disabled", "disabled");
    }
    $ta->setValue($value);
    
    $span->addHtml($ta->render());

    if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
        $msg_span=new TagRenderer("span",true);
        $msg_span->setAttribute("class", "invalid-feedback");
        $msg_span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
        $span->addHtml($msg_span->render());
    }

    $s=$span->render();

    return $s;
}
