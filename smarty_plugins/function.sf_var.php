<?php

function smarty_function_sf_var($params, $template)
{
    $tag="sf_var";
    
    $attributes_list=array();
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['name']=array(
    	'required'=>true,
    	'desc'=>'Name of the variable'		
    );
    $attributes['default']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Default value of variable'		
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $parent_tag=&$template->smarty->_tag_stack[count($template->smarty->_tag_stack)-1];
    $parent_tag_name=$parent_tag[0];
   	if($parent_tag_name!="sf_form") {
   		throw new Exception("sf_var tag must be within sf_form tag!");
   	}
   	
   	if(!isset(SmartyFacesContext::$formVars[$name])) {
   		if($default===null) {
   			if(isset($template->tpl_vars[$name])) {
   				$value=$template->tpl_vars[$name]->value;
   			} else {
   				$value=null;
   			}
   		} else {
	   		$value=$default;
   		}
   		SmartyFacesContext::setFormVar($name,$value);
   	} else {
   		$value=SmartyFacesContext::getFormVar($name);
   	}
   	$template->assign($name,$value);
   	
   	$parent_tag[2]['form_vars'][$name]=$value;
    
}

?>