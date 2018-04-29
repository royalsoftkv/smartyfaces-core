<?php

function smarty_function_sf_status($params, $template)
{
    $tag="sf_status";
    
    $attributes_list=array("id","value");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['id']['required']=false;
    $attributes['id']['default']="sf-status";
    $attributes['value']['required']=false;
    $attributes['value']['default']="Loading...";
    $attributes['value']['desc']="Text or html which will be displayed inside component during ajax request";
    
    if($params==null and $template==null) return $attributes;
    
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $s="<div id=\"$id\">$value</div>";
    return $s;

}

?>