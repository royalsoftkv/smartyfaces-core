<?php

function smarty_function_sf_commandbutton($params, $template)
{
    $tag="sf-commandbutton";
    
    $attributes_list=array("id","action","value","rendered","immediate","class","style","title","disabled","update","custom");
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
    $attributes['btnclass']=array(
    	'required'=>false,
    	'default'=>'default',
    	'desc'=>'Additional boostrap context class. Can be default, primary, success, info, warning and danger'		
    );
    $attributes['button']=array(
    	'required'=>false,
    	'default'=>false,
    	'desc'=>'Render button as button tag'		
    );
    $attributes['default']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Make button default on form with defined scope'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    $id=SmartyFacesComponent::checkNested($id,$template);
    SmartyFacesComponent::createComponent($id, $tag, $params);
    if(!$rendered) return;
    
    if($default) {
    	SmartyFacesContext::$default_button[$id] = $default;
    }
    
    $stateless=SmartyFacesComponent::$stateless;
    if($stateless) {
    	$action="'".$action."'";
    	$data=array();
    	$data['immediate']=$immediate;
    	$data_str=htmlspecialchars(json_encode($data));
    } else {
    	$action="null";
    	$data_str="null";
    }
    
    if($confirm!=null){
    	$confirm='if(!confirm(\''.$confirm.'\')) return false;';
    }
    
    if(strlen($onclick)>0 and substr($onclick, -1, 1)!=";") $onclick.=";";
    
    if($button) {
    	$c=new TagRenderer("button",true);
    } else {
	    $c=new TagRenderer("input",false);
    }
    $c->setCustom($custom);
    if(SmartyFaces::$skin=="default") $class.=" sf-button";
    if(SmartyFaces::$skin=="bootstrap") $class.=" btn btn-".$btnclass;
    $c->setAttributeIfExists("class", $class);
    $c->setAttributeIfExists("style", $style);
    $c->setAttributeIfExists("title", $title);
    if($disabled) {
    	$c->setAttribute("disabled", $disabled);
    }
    $c->setId($id);
    $c->setAttribute("type", "submit");
    $c->setValue($value);
    $c->setAttribute("onclick", $confirm.$onclick.'SF.a(this,'.$action.','.$data_str.'); return false;');
    return $c->render();
}

?>