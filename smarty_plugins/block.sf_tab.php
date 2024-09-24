<?php 

function smarty_block_sf_tab($params, $content, $template, &$repeat)
{

	$tag="sf_tab";

	$attributes_list=array("onclick");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['header']=array(
		'required'=>false,
		'default'=>'',
		'desc'=>'Title in header of tab'
	);
	if($params==null and $template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));

	$parent_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-2][2];
	
	if(is_null($content)){
		return;
	}
	
	$tabs_count=(isset($parent_tag_stack['tabs']) ? count($parent_tag_stack['tabs']) : 0);
	$tabs_index=$tabs_count;
	$parent_tag_stack['tabs'][$tabs_index]['params']=$params;
	$parent_tag_stack['tabs'][$tabs_index]['content']=$content;
	
}


?>