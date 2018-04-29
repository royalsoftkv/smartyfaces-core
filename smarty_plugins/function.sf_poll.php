<?php

function smarty_function_sf_poll($params, $template)
{
    $tag="sf_poll";
    $attributes_list=array("id","action","update");
    $registered_events=array("poll");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['interval']=array(
    	'required'=>false,
    	'default'=>1000,
    	'desc'=>'Interval of time in miliseconds to execute poll action'		
    );
    $attributes['enabled']=array(
    	'required'=>false,
    	'default'=>true,
    	'desc'=>'Enables or disabled poll'		
    );
    $attributes['actionData']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Data which will be passed to the action'
    );
    $attributes['oncomplete']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Javascript function that will be executed when response is finished.
    		It accept one argument data which holds processed data from ajax call'
    );
    $attributes['onstart']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Javascript function that will be executed before poll is called'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(!$enabled) {
    	$s='<script type="text/javascript">
    		clearTimeout(window.sf_poll_'.$id.');
    	</script>';
    	return $s;
    }
    
    //add poll event
    $ajaxEvent=array();
    $ajaxEvent['action']=$action;
    $ajaxEvent['actionData']=$actionData;
    $ajaxEvent['immediate']=true;
    $ajaxEvent['update']=$update;
    $ajaxEvent['oncomplete']=$oncomplete;
    $params['events']['poll']=$ajaxEvent;
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    $data=array();
    if(isset($ajaxEvent['actionData'])) $data[]="actionData:".$ajaxEvent['actionData'];
    if(isset($ajaxEvent['oncomplete'])) $data[]="oncomplete:function(){".$ajaxEvent['oncomplete']."}";
    if(count($data)>0) {
    	$data="{".implode(",", $data)."}";
    	$event_str="SF.p(\"$id\",$data)";
    } else {
    	$event_str="SF.p(\"$id\")";
    }
    $s='
	
    		
    <script type="text/javascript" id="'.$id.'">
		clearTimeout(window.sf_poll_'.$id.');
    	reload=function() {
    		'.$onstart.';'.$event_str.';
    	}
    	window.sf_poll_'.$id.'=setTimeout(reload, '.$interval.');
    </script>';
    

    return $s;
}

?>