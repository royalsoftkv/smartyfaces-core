<?php

function smarty_function_sf_commandlink($params, $template)
{

	$tag="sf_commandlink";
	
	$attributes_list=array("id","action","value","immediate","rendered","disabled","style","class","title","custom");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['confirm']=array(
			'required'=>false,
			'default'=>null,
			'desc'=>'Prompt mesasge for user before executing action on button'
	);
	$attributes['onclick']=array(
			'required'=>false,
			'default'=>'',
			'desc'=>'Javascript action that will be invoked before button action'
	);
	$attributes['update']=array(
			'required'=>false,
			'default'=>'',
			'desc'=>'[Experimental] Id of region to update'
	);
	if($params==null and $template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	
    $id=SmartyFacesComponent::checkInRegion($id,$template);
    $id=SmartyFacesComponent::checkNested($id,$template);
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    $stateless=SmartyFacesComponent::$stateless;
    
    if(!$rendered) return;
	if($confirm!=null){
    	$confirm='if(!confirm(\''.$confirm.'\')) return false;';
    }
    
    if($disabled) {
    	$action_str='';
    } else {
    	if($stateless) {
    		$action="'".$action."'";
    		$data=array();
    		$data['immediate']=$immediate;
    		$data_str=htmlspecialchars(json_encode($data));
    	} else {
    		$action="null";
    		$data_str="null";
    	}
    	$action_str='SF.a(this,'.$action.','.$data_str.');';
    }
    
    if(strlen($onclick)>0 and substr($onclick, -1, 1)!=";") $onclick.=";";
    $disabled_act=""; if($disabled) $disabled_act="return false;";
    
    $tr=new TagRenderer('a',true);
    $tr->setAttribute("href", "#");
    $tr->setAttributeIfExists("style", $style);
    $tr->setAttributeIfExists("title", $title);
    $tr->setAttributeIfExists("class", $class);
    if($disabled) $tr->setAttribute("disabled", "disabled");
    $tr->setId($id);
    $tr->setAttribute("onclick", $disabled_act.$confirm.$onclick.$action_str.' return false;');
    $tr->setCustom($custom);
    $tr->setValue($value);
    
    return $tr->render();
    
}

?>