<?php

function smarty_function_sf_paginator($params, $template)
{
    $tag="sf_paginator";
    
    $attributes_list=array("id","value","immediate");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['datamodel']=array(
    	'required'=>false,
    	'default'=>null,
    	'description'=>'Class of instance SmartyFacesDataModel representing data'		
    );
    $attributes['value']['required']=false;
    $attributes['value']['default']=null;


    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    

    if($value==null) {
    	$value=$template->smarty->_tag_stack[count($template->smarty->_tag_stack)-2][1]['value'];
    }
    if($datamodel==null) {
    	$datamodel=$template->smarty->_tag_stack[count($template->smarty->_tag_stack)-2][1]['datamodel'];
    }
    
    $params['action']='#['.$datamodel.'->paginate()]';
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    return $value->paginatorTemplate($id);
    
}

?>