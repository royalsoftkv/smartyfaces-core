<?php

function smarty_function_sf_media($params, $template)
{
    $tag="sf_media";
    $attributes_list=array("id");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['type']=array(
    	'required'=>true,
    	'desc'=>'TODO'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    $s="";
    return $s;
}

?>