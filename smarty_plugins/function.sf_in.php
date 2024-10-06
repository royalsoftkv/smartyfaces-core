<?php

function smarty_function_sf_in($params, $template)
{
    $tag="sf_in";
    
    $attributes_list=array();
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['name']=array(
    	'required'=>true,'desc'=>'Name of the bean instance used in view'		
    );
    $attributes['scope']=array(
    	'required'=>false,'default'=>'event','desc'=>'Scope of component. Can be: event, session, application (TODO)'
    );
    $attributes['args']=array(
    	'required'=>false,'default'=>null,'desc'=>'Array of arguments that will be passed to bean constructor'		
    );
    $attributes['class']=array(
    	'required'=>false,'default'=>null,'desc'=>'Name of the class whitch will be instantiated. By default it is camelized name of bean'		
    );
    
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $obj=SmartyFacesComponent::getInstance($name, $scope, $args, $class);
    SmartyFaces::$GLOBALS[$name]=$obj;
    $template->assignByRef($name,$obj);
}
