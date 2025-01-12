<?php

function smarty_function_sf_listbox($params, $template)
{
    $tag="sf_listbox";
    
    $attributes_list=array("id","value","class","style","disabled");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['value']['required']=false;
    $attributes['values']=array(
    	'required'=>true,
    	'default'=>[],
        'desc'=>'List of items that will be displayed'
    );
    $attributes['var']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Name of the row iteration variable'
    );
    $attributes['val']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Value for item in list'
    );
    $attributes['label']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Label for value in list'
    );
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
	SmartyFacesComponent::createComponent($id, $tag, $params, array("values"));
    
	SmartyFacesContext::$bindings[$id]=$value;
	$value=  SmartyFaces::evalExpression($value);
	
	$class.=" form-control";
	
	$select=new TagRenderer("select",true);
	$select->setAttribute("multiple", "multiple");
	$select->setIdAndName($id."[]");
	$select->setAttributeIfExists("class", $class);
	$select->setAttributeIfExists("style", $style);
	$select->setDisabled($disabled);
	
    foreach($values as $key=>$item) {
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
    	$selected=(in_array($item, $value));
    	if($selected) $selected=' selected="selected"';
    	
    	$option=new TagRenderer("option",true);
    	$option->setAttribute("value", $v);
    	if($selected) {
    		$option->setAttribute("selected", "selected");
    	}
    	$option->setValue($l);
    	$select->addHtml($option->render());
    	
    }
    return $select->render();
}

?>