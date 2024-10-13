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
    		'desc'=>'Defines direction for displaying radios. Available are: horizontal, vertical'
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
    $invalid = false;
    if(SmartyFaces::$validateFailed and !$disabled) {
    	if(isset(SmartyFacesContext::$formData[$id])) {
    		$value = SmartyFacesContext::$formData[$id];
    	} else {
    		$value=null;
    	}
    	if(SmartyFacesComponent::validationFailed($id)) {
            $invalid = true;
    	}
    } else {
    	$value=  SmartyFaces::evalExpression($value);
    }
    
    $onchange="";
    if(!is_null($action) && $action!="null"){
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
    
    $cont=new TagRenderer("div",true);
    $cont->setAttributeIfExists("class", $class);
    $ix=0;
    if(is_array($values) and count($values)) {
        foreach($values as $key=>$item){
            $ix++;
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
	    	
	    	
	    	$itemId=$id."-".$ix;
	    	$radio=new TagRenderer("input");
	    	$radio->setAttribute("type", "radio");
	    	$radio->setAttribute("name",$id);
	    	$radio->setAttribute("id",$itemId);
            $radio->setAttribute("class","form-check-input ".($invalid ? " is-invalid" : ""));
	    	$radio->setValue($v);
	    	$radio->setAttributeIfExists("onchange", $onchange);
	    	if($disabled){
	    		$radio->setAttribute("disabled", "disabled");
	    	}
	    	if($v==$value) {
	    		$radio->setAttribute("checked", "checked");
	    	}

            $div=new TagRenderer("div",true);
            $div->setAttribute("class", "form-check ".($direction == "horizontal" ? " form-check-inline" : "").($invalid ? " is-invalid" : ""));
	    	
            $tag_label=new TagRenderer("label",true);
            $tag_label->setAttribute("class", "form-check-label ");
            $tag_label->setAttribute("for", $itemId);
            $tag_label->setValue($l);
            $div->addHtml($radio->render());
            $div->addHtml($tag_label->render());
            $cont->addHtml($div->render());
	    }
    }
    if($attachMessage and !$disabled and $invalid) {
        $msg_span=new TagRenderer("div",true);
        $msg_span->setAttribute("class", "invalid-feedback");
        $msg_span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
        $cont->addHtml($msg_span->render());
    }
    return $cont->render();
}

?>