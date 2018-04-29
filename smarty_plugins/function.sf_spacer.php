<?php

function smarty_function_sf_spacer($params, $template)
{
    $tag="sf_spacer";
    
    $attributes_list=array("style");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['width']=array(
    	'required'=>false,
    	'default'=>1,
    	'desc'=>'Width of spacer image'
    );
    $attributes['height']=array(
    	'required'=>false,
    	'default'=>1,
    	'desc'=>'Height of spacer image'
    );
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
    
    $style=SmartyFacesComponent::getParameter($tag, "style","", $params);
    
    $img=new TagRenderer("img");
    $img->setAttributeIfExists("style", $style);
    $img->setAttribute("src", "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7");
    $img->passAttributes($attributes_values, array("width","height"));
    $img->setAttribute("border", "0");
    
   	return $img->render();
}

?>