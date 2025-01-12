<?php


function smarty_function_sf_region($params, $template)
{
    $tag="sf_region";
    
    $attributes_list=array("id","value","rendered");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['assign']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'name or array of variable names which will be assigned from parent template to region'		
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
	SmartyFacesContext::$regions[$params['id']]=$params;
	
	if(!$rendered) return;
	
	if($assign!=null) {
		if(is_array($assign)) {
			$vars=$assign;
		} else {
			$vars[]=$assign;
		}
		foreach($vars as $var) {
			$val=$template->getTemplateVars($var);
			$template->smarty->assign($var,$val);
		}

	}
	
	$s="";
	$s.='<span id="'.$id.'">';
	$s.=$template->smarty->fetch("string:".$value,null,$id);
	$s.='</span>';
	return $s;

}

