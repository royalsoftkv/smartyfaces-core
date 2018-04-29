<?php

function smarty_function_sf_ajax($params, $template)
{
    $tag="sf_ajax";
    $attributes_list=array("rendered","action","immediate","update");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['for']=array(
    	'required'=>true,
    	'desc'=>'Id of target component to which will be attached ajax event'
    );
    $attributes['event']=array(
    	'required'=>true,
    	'desc'=>'Name of event for which will be executed ajax action'
    );
    $attributes['actionData']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Data which will be passed to the action'
    );
    $attributes['oncomplete']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Javascript function that will be executed when response is finished. 
    		It accept one argument data which holds processed data from ajax call'
    );
    if($params==null and $template==null) return $attributes;
    
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	if(!$rendered) return;
	
	$ajaxEvent=array();
	$ajaxEvent['action']=$action;
	$ajaxEvent['actionData']=$actionData;
	$ajaxEvent['immediate']=$immediate;
	$ajaxEvent['update']=$update;
	$ajaxEvent['oncomplete']=$oncomplete;
	
	SmartyFacesContext::$ajaxEvents[$for][$event]=$ajaxEvent;

}

?>