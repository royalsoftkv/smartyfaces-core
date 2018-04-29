<?php

function smarty_function_sf_radiogroup($params, $template)
{
    $tag="sf_radiogroup";
    
    $attributes_list=array("id","value","required","action","immediate","rendered","attachMessage","disabled","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['values']=array(
    		'required'=>true,
    		'default'=>array(),
    		'desc'=>'Array of values to display'
    );
    $attributes['var']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Name of the row iteration variable'
    );
    $attributes['val']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Value for data'
    );
    $attributes['label']=array(
    		'required'=>false,
    		'default'=>null,
    		'desc'=>'Label for display data'
    );
    $attributes['direction']=array(
    		'required'=>false,
    		'default'=>'horizontal',
    		'desc'=>'Defines direction for displaying radios. Available are: hirozontal, verical'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(!$rendered) return;
    
    SmartyFacesComponent::createComponent($id, $tag, $params, array("disabled"));
    
    $stateless=SmartyFacesComponent::$stateless;
    
    if($required and !$disabled){
    	SmartyFacesContext::addRequiredValidator($id);
    }
    
    SmartyFacesContext::$bindings[$id]=$value;
    if(SmartyFaces::$validateFailed and !$disabled) {
    	if(isset(SmartyFacesContext::$formData[$id])) {
    		$value = SmartyFacesContext::$formData[$id];
    	} else {
    		$value=null;
    	}
    	if(SmartyFacesComponent::validationFailed($id)) {
    		if(SmartyFaces::$skin=="default") $class.=" sf-vf";
    	}
    } else {
    	$value=  SmartyFaces::evalExpression($value);
    }
    
    $onchange="";
    if(!is_null($action)){
    	if($stateless) {
    		$action="'".$action."'";
    		$data=array();
    		$data['immediate']=$immediate;
    		$data_str=htmlspecialchars(json_encode($data));
    	} else {
    		$action="null";
    		$data_str="null";
    	}
    	$onchange='SF.a(this,'.$action.','.$data_str.'); return false;';
    }
    
    if(SmartyFaces::$skin=="default") $class.=" sf-radio-group";
    
    $span=new TagRenderer("span",true);
    
    if(is_array($values) and count($values)) {
	    foreach($values as $key=>$item){
	    	$v=null;
	    	$l=null;
	    	if($var!=null) {
	    		$$var=$item;
	    		if($val==null) {
	    			$v=$item;
	    		} else {
	    			$v=null;eval("\$v=$val;");
	    		}
	    		if($label==null) {
	    			$l=$item;
	    		} else {
	    			$l=null;eval("\$l=$label;");
	    		}
	    	} else {
	    		$v=$key;
	    		$l=$item;
	    	}
	    	
	    	
	    	
	    	$radio=new TagRenderer("input");
	    	$radio->setAttribute("type", "radio");
	    	$radio->setIdAndName($id);
	    	$radio->setValue($v);
	    	$radio->setAttributeIfExists("onchange", $onchange);
	    	if($disabled){
	    		$radio->setAttribute("disabled", "disabled");
	    	}
	    	if($v==$value) {
	    		$radio->setAttribute("checked", "checked");
	    	}
	    	
	    	if(SmartyFaces::$skin=="default") {
		    	$span->addHtml($radio->render());
		    	$span->addHtml($l);
			    if($direction=="vertical"){
			    	$span->addHtml('<br/>');
			    }
	    	} else {
	    		$tag_label=new TagRenderer("label",true);
	    		$tag_label->setAttribute("class", $direction=="horizontal" ? "radio-inline" : "");
	    		$tag_label->addHtml($radio->render());
	    		$tag_label->addHtml($l);
	    		if($direction=="vertical") {
	    			$div=new TagRenderer("div",true);
	    			$div->setAttribute("class", "radio");
	    			$div->setValue($tag_label->render());
		    		$span->addHtml($div->render());
	    		} else {
		    		$span->addHtml($tag_label->render());
	    		}
	    	}
	    	
	    }
    }
    if(SmartyFaces::$skin=="default") {
	    $s=$span->render();
	    if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
    } else {
    	$span->appendAttribute("class", SmartyFacesComponent::getFormControlValidationClass($id));
    	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
    		$msg_span=new TagRenderer("span",true);
    		$msg_span->setAttribute("class", "help-block");
    		$msg_span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$span->addHtml($msg_span->render());
    	}
    	$s=$span->render();
    }
    return $s;
}

?>