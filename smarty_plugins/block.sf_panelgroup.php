<?php


function smarty_block_sf_panelgroup($params, $content, $template, &$repeat)
{ 
    
    $tag="sf_panelgroup";
    
    $attributes_list=array("rendered");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(!$rendered) {
    	$repeat=false;
    	return;
    }

    return $content;
}

?>