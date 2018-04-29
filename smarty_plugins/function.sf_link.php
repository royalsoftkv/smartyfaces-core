<?php

/*
 * sf_link [value|view|disabled|action]
 * 
 */

/**
 * This tag renderds simple link to another view
 * @param type $params
 * @param type $template 
 */

function smarty_function_sf_link($params, $template){
    
    $tag="sf_link";
    $attributes_list=array("value","disabled","action","rendered","class","title");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['value']['required']=false;
    $attributes['value']['default']="";
    $attributes['view']=array(
    	"required"=>false,
    	"default"=>null,
    	"desc"=>"Defines view to which will be linked link. Default is current view"		
    );
    $attributes['viewvar']=array(
    	"required"=>false,
    	"default"=>SmartyFaces::$config['view_var_name'],
    	"desc"=>"URL variable for linking view"		
    );
    $attributes['confirm']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Prompt mesasge for user before executing action on button'
    );
    $attributes['actionparams']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Array of parameters which will be passed to ajax function'
    );
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
	if(!$rendered) return;
	
    if($view==null) $view=SmartyFacesComponent::$current_view;
    
    if($confirm!=null){
    	$confirm='if(!confirm(\''.$confirm.'\')) return false;';
    }
    
    $index_file=SmartyFaces::$config['index_file'];
    $server_url=SmartyFaces::$config['server_url'];
    
    $href="$server_url/$index_file?$viewvar=$view";
    $onclick="";
    if($action!=null){
    	$action=str_replace("'", "\'", $action);
    	if($actionparams!=null) {
    		$actionparams=json_encode($actionparams,JSON_FORCE_OBJECT);
    		$actionparams=str_replace('"', "'", $actionparams);
    		$actionparams=",".$actionparams;
    	}
        $onclick=$confirm.'SF.l(\''.$action.'\''.$actionparams.'); return false;';
    }
    
    if($disabled){
        $s=$value;
    } else {
    	$link=new TagRenderer("a",true);
    	$link->setAttribute("href", $href);
    	$link->setAttributeIfExists("onclick", $onclick);
    	$link->passAttributes($attributes_values, array("class","title"));
    	$link->setValue($value);
    	$s=$link->render();
    }
    
    return $s;
    
}


?>
