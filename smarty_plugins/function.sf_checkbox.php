<?php

function smarty_function_sf_checkbox($params, $template)
{
    $tag="sf_checkbox";
    
    $attributes_list=array("id","value","required","action","immediate","attachMessage","class","title","disabled","rendered","validator");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['checkedValue']=array(
    	'required'=>false,
    	'default'=>"1",
    	'desc'=>'Value that will be submitted if checkbox is checked'		
    );
    $attributes['unCheckedValue']=array(
    	'required'=>false,
    	'default'=>"0",
    	'desc'=>'Value that will be submitted if checkbox is not checked'		
    );
    $attributes['boolean']=array(
    	'required'=>false,
    	'default'=>false,
    	'desc'=>'Store and process value of chekbox as boolean',
    	'type'=>'bool'
    );
    $attributes['label']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Label text for checkbox',
    );
    $attributes['confirm']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Prompt mesasge for user before executing action on component'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    if(!$rendered) return;
    
    if($confirm!=null){
    	$confirm='if(!confirm(\''.$confirm.'\')) return false;';
    }
    
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-input-checkbox ";
    
    $id=SmartyFacesComponent::checkNested($id,$template);
    SmartyFacesComponent::createComponent($id, $tag, $params, array("boolean","disabled","unCheckedValue"));
    
    $stateless=SmartyFacesComponent::$stateless;
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
	if(!is_null($validator)) {
		SmartyFacesContext::addValidator($id,$validator);
	}

    $invalid=false;
    SmartyFacesContext::$bindings[$id]=$value;
	if(SmartyFaces::$validateFailed and !$disabled) {
		if(isset(SmartyFacesContext::$formData[$id])) {
	    	$value = SmartyFacesContext::$formData[$id];
		} else {
			$value=$unCheckedValue;
		}
		if(SmartyFacesComponent::validationFailed($id)) {
            $invalid = true;
		}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
    }


    if(!is_null($action) && $action != "null") {
    	if($stateless) {
    		$action="'".$action."'";
    		$data=array();
    		$data['immediate']=$immediate;
    		$data_str=htmlspecialchars(json_encode($data));
    	} else {
    		$action="null";
    		$data_str="null";
    	}
    	$onchange='SF.a(this,'.$action.','.$data_str.');';
    } else {
    	$onchange="";
    }
    
	$div=new TagRenderer("div",true);
	$div_class="checkbox";
	if(!$disabled) $div_class.=" ".SmartyFacesComponent::getFormControlValidationClass($id);
	$div->setAttribute("class", $div_class);
	$div->setAttributeIfExists("title", $title);

    $c=new TagRenderer("input",false);
    $c->setAttribute("type", "checkbox");
	$class.=" form-check-input".($invalid? " is-invalid":"");
    $c->setAttribute("class", $class);
    $c->setIdAndName($id);
    $c->setValue($checkedValue);
    $c->setAttributeIfExists("onchange", $onchange);
    $c->setAttributeIfExists("onclick", $confirm);
    if($value==$checkedValue) {
    	$c->setAttribute("checked", "checked");
    }
    if($disabled) {
	    $c->setAttributeIfExists("disabled", "disabled");
    }
	$div->addHtml($c->render());

	$label_c=new TagRenderer("label", true);
	$label_c->setAttribute("class","form-check-label");
    $label_c->setAttribute("for", $id);
	$label_c->addHtml($label);
	$div->addHtml($label_c->render());
	if($attachMessage and !$disabled and $invalid) {
		$span=new TagRenderer("div",true);
		$span->setAttribute("class", "invalid-feedback");
		$span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
		$div->addHtml($span->render());
	}
	$s=$div->render();

    return $s;
}

