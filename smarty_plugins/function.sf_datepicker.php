<?php

function smarty_function_sf_datepicker($params, $template)
{
    $tag="sf_datepicker";
    $attributes_list=array("id","value","required","attachMessage","class","disabled","validator","converter",
    		"action","size","title","onclick","onchange","style","rendered");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['dateFormat']=array(
    			'required'=>false,
   				'default'=>"dd.mm.yy",
   				'desc'=>'(deprecated) Date format used to display date in datepicker');
    $attributes['buttonImage']=array(
   				'required'=>false,
   				'default'=>null,
   				'desc'=>'(deprecated) Custom path to calendar image');
    $attributes['bootstrapIcon']=array(
    		'required'=>false,
    		'default'=>"calendar",
    		'desc'=>'(deprecated) Icon to display for control button');
    $attributes['datepickerOptions']=array(
   				'required'=>false,
   				'default'=>array(),
   				'desc'=>'(deprecated) Additional date picker options');
    $attributes['action']['desc']='Ajax action invoked on change event';
    $attributes['size']['desc']='Defines size of input text field for datepicker (deprecated)';
    $attributes['onclick']['desc']='(deprecated)';
	$attributes['time']=array(
		'required'=>false,
		'default'=>false,
		'desc'=>'Allows selection of time also');
	$attributes['block']=array(
		'required'=>false,
		'default'=>false,
		'desc'=>'Display with 100% width');

    if($params==null and $template==null) return $attributes;
    
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $id=SmartyFacesComponent::checkNested($id,$template);
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    
    if(!is_null($validator)) {
    	SmartyFacesContext::addValidator($id,$validator);
    }
    if(strlen($converter ?? "")>0 and !$disabled) {
    	SmartyFacesContext::addConverter($id,$converter);
    }
    SmartyFacesComponent::createComponent($id, $tag, $params);
    if(!$rendered) return;
    
    
    SmartyFacesContext::$bindings[$id]=$value;
    $invalid = SmartyFacesComponent::validationFailed($id);
	if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = SmartyFacesContext::$formData[$id];
		if($invalid) {
			$class.=" sf-vf is-invalid";
		}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
	    if(strlen($converter ?? "")>0) {
	    	$value=$converter::toString($value);
	    }
    }


    if(strlen($onchange ?? "")>0 and substr($onchange, -1, 1)!=";") $onchange.=";";
    if($action) {
	    $stateless=SmartyFacesComponent::$stateless;
	    if($stateless) {
	    	$action=$onchange.'SF.a(this,\''.$action.'\',null) ';
	    } else {
	    	$action=$onchange.'SF.a(this,null,null) ';
	    }
    } else {
    	$action=$onchange;
    }
    
    $i=new TagRenderer("input",false);
    $i->setAttribute("type", $time ? "datetime-local" : "date");
    $i->setAttributeIfExists("style", $style);
    $class.=" form-control";
	if(!$block) {
		$class.=" width-auto";
	}
    $i->setAttributeIfExists("class", $class);
    $i->setAttributeIfExists("title", $title);
    if($disabled) {
    	$i->setAttribute("disabled", "true");
    }
    $i->setIdAndName($id);
	if(!empty($value)) {
		$value = date($time ? "Y-m-d H:i:s" : "Y-m-d", strtotime($value));
	}
	$i->setValue($value);
    $i->setAttribute("onchange", $action);
    
    $s=$i->render();
    if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
        $m_div=new TagRenderer("div",true);
        $m_div->setAttribute("class", "invalid-feedback");
        $m_div->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
        $s.=$m_div->render();
    }

    return $s;
}

