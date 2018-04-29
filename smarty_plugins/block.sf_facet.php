<?php


function smarty_block_sf_facet($params, $content, $template, &$repeat)
{ 
    
    $tag="sf_facet";
    
    $attributes_list=array("rendered","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['name']=array(
    	'required'=>true, 'desc'=>'Name of the facet'		
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $parent_tag_stack=&$template->smarty->_tag_stack[count($template->smarty->_tag_stack)-2][2];
    if(is_null($content)){
		return;
    }
    $parent_tag_stack['facets'][$name]['content']=$content;
    $parent_tag_stack['facets'][$name]['params']=$params;
    return;
}

?>