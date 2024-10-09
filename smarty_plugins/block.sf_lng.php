<?php


function smarty_block_sf_lng($params, $content, $template, &$repeat)
{ 
    
    $tag="sf_lng";
    
    $attributes_list=array("rendered");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['lng']=array(
    	'required'=>false,'default'=>null,'desc'=>'Language code for this translation block'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $lng_def=SmartyFaces::$config['lng_def'];
	if(!$lng) $lng=$lng_def;
    if(!$rendered) {
    	$repeat=false;
    	return;
    }

	$lng_sel = SmartyFaces::getLanguage();

	if($lng_sel==$lng){
		return $content;
	}
    
}

