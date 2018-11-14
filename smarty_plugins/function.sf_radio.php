<?php

function smarty_function_sf_radio($params, $template)
{
    $tag="sf_radio";
    
    $attributes_list=array("id","value","required","action","immediate","attachMessage","class","disabled");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['partial']=array(
    	'required'=>false,
    	'default'=>true,
    	'desc'=>'DEPRECATED'		
    );
    $attributes['checkedValue']=array(
    		'required'=>false,
    		'default'=>"1",
    		'desc'=>'Value that will be submitted if radio is checked'
    );
    $attributes['unCheckedValue']=array(
    		'required'=>false,
    		'default'=>"0",
    		'desc'=>'Value that will be submitted if radio is not checked'
    );
    $attributes['confirm']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Prompt mesasge for user before executing action on component'
    );
    $attributes['label']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Label text for radio',
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-radio";
    
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    
    SmartyFacesContext::$bindings[$id]=$value;

	if(SmartyFaces::$validateFailed and !$disabled) {
		if(isset(SmartyFacesContext::$formData[$id])) {
	    	$value = SmartyFacesContext::$formData[$id];
		} else {
			$value=$unCheckedValue;
		}
		if(SmartyFacesComponent::validationFailed($id)) {
			if(SmartyFaces::$skin=="default") $class.=" sf-vf";
		}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
    }
    
    if($confirm!=null){
    	$confirm='if(!confirm(\''.$confirm.'\')) return false;';
    }
    
    if($action!=null) {
    	$onclick=$confirm.'SF.a(this,null);';
    } else {
    	if($confirm) {
	    	$onclick=$confirm;
    	} else {
	    	$onclick="";
    	}
    }
    
    if($disabled) $disabled=' disabled="disabled" ';
    
    $radio=new TagRenderer("input");
    $radio->setAttribute("type", "radio");
    $radio->setAttributeIfExists("class", $class);
    if($disabled) {
    	$radio->setAttribute("disabled", "disabled");
    }
    $radio->setIdAndName($id);
    $radio->setValue($checkedValue);
    $radio->setAttributeIfExists("onclick", $onclick);
    if($value==$checkedValue) {
    	$radio->setAttribute("checked", "checked");
    }
    
    if(SmartyFaces::$skin=="default") {
	    $s=$radio->render();
	    $s.=$label;
	    if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
    } else {
    	$div=new TagRenderer("div",true);
    	$div->setAttribute("class", "radio");
    	$div->appendAttribute("class", SmartyFacesComponent::getFormControlValidationClass($id));
    	$div_label=new TagRenderer("label",true);
    	$div_label->addHtml($radio->render());
    	$div_label->addHtml($label);
    	$div->addHtml($div_label->render());
    	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
    		$span=new TagRenderer("span",true);
    		$span->setAttribute("class", "help-block");
    		$span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$div->addHtml($span->render());
    	}
    	$s=$div->render();
    }
    
    return $s;
}

?>