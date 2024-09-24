<?php


function smarty_block_sf_repeat($params, $content, $template, &$repeat)
{ 
    
    $tag="sf_repeat";
    
    $attributes_list=array("value");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['var']=array(
    	'required'=>true,
    	'desc'=>'Name of the row iteration variable'		
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    
    if((is_array($value) and count($value)==0) or (!is_array($value) and $value==0)) return;
    if(is_null($content)){
    	if(is_array($value)) {
    		$count=count($value);
    	} else {
    		$count=$value;
    	}
	    $index=0;
	    $template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2]['count']=$count;
	    $template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2]['index']=$index;
	    $template->assign($var,$value[$index]);
	    $repeat=true;
        return;
    }
    $count=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2]['count'];
    $index=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2]['index'];
    //echo "[$index/$count]";
    $index++;
    $template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2]['index']=$index;
   
    $repeat=($index<$count);
    if($repeat==false){
    	$template->clearAssign($var);
    } else {
	$template->assign($var,$value[$index]);
    }
    $s=$content;
    return $s;
}

?>