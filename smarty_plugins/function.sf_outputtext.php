<?php

function smarty_function_sf_outputtext($params, $template)
{
    $tag="sf_outputtext";
    
    $attributes_list=array("id","value","class","converter","rendered","style");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
    if(!$rendered) return;
	
	if(strlen($converter)>0) {
    	SmartyFacesContext::addConverter($id,$converter);
    }
    
    $value=  SmartyFaces::evalExpression($value);
	if(strlen($converter)>0) {
	    $value=$converter::toString($value);
	}
    
    $span=new TagRenderer("span",true);
    $span->passAttributes($attributes_values, array("class"));
    $span->setId($id);
    $span->setValue($value);
    $span->setAttribute("style", $style);
    return $span->render();
}

?>