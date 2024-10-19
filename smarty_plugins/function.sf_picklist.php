<?php

function smarty_function_sf_picklist($params, $template)
{
    $tag="sf_picklist";
    
    $attributes_list=array("id","value","attachMessage","required","class","disabled");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['source']=array(
		'required'=>false,
    	'default'=>null,
    	'desc'=>'Array of source items to be displayed'
    );
    $attributes['value']['default']=null;
    $attributes['value']['required']=false;
    $attributes['var']=array(
		'required'=>false,
    	'default'=>null,
    	'desc'=>'Name of variable to iterate source and target elements'
    );
    $attributes['label']=array(
		'required'=>false,
    	'default'=>null,
    	'desc'=>'Label for value for select list'
    );
    $attributes['buttonclass']=array(
		'required'=>false,
    	'default'=>null,
    	'desc'=>'(deprecated) Style class that will be applied to picklist buttons'
    );
    $attributes['buttontitles']=array(
    		'required'=>false,
    		'default'=>array(),
    		'desc'=>'(deprecated) Array of title strings that will be displayed for picklist buttons'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(is_null($source)) $source=array();

    $buttontitles = _picklistButtonTitles();
	
	SmartyFacesContext::$bindings[$id]=$value;
	
	if(SmartyFaces::$validateFailed and !$disabled) {
		$data = SmartyFacesContext::$formData[$id];
		
		if(strlen($data)>0) {
			$selected=explode(",", $data);
			$value=array();
			foreach($selected as $id) {
				$value[$id]=$source[$id];
			}
		} else {
			$value=array();
		}
	} else {
		$value=  SmartyFaces::evalExpression($value);
	}
	
	if($required and !$disabled){
		SmartyFacesContext::addRequiredValidator($id);
	}

    $invalid = SmartyFacesComponent::validationFailed($id);
	
	SmartyFacesComponent::createComponent($id, $tag, $params, array("source"));
	
	//covert to array
// 	$value_arr=array();
// 	if(strlen($value)>0) {
// 		foreach (explode(",",$value) as $key) {
// 			$value_arr[$key]=$source[$key];
// 		}
// 	}
	$source_keys=array_keys($source);
	$value_keys=is_array($value) ? array_keys($value) : [];
	$left_list_keys=array_diff($source_keys, $value_keys);
	$right_list_keys=$value_keys;
	
	$hidden_val=implode(",", $value_keys);
	
	$select1=new TagRenderer("select",true);
	if($disabled) {
		$select1->setAttribute("disabled", "disabled");
	}
	$select1->setAttribute("multiple", "multiple");
	$select1->setId($id.'_l');
    $s_class = "form-select h-100 " . ($invalid ? "is-invalid" : "");
	$select1->setAttribute("class", $s_class);
    
    $values_map=array();
    
    foreach($left_list_keys as $key) {
    	$item=$source[$key];
    	$l=null;
    	$v=$key;
    	if($var!=null) {
    		$$var=$item;
    		if($label==null) {
    			$l=$item;
    		} else {
    			$l=null;eval("\$l=$label;");
    		}
    	} else {
    		$l=$item;
    	}
    	
    	$option=new TagRenderer("option",true);
    	$option->setAttribute("value", $v);
    	$option->setValue($l);
    	$select1->addHtml($option->render());
    	
    }
    
    $input1=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-chevron-right"></span>', $buttontitles['move_right'], '_bnt_mr');
    $input2=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-angles-right"></span>', $buttontitles['move_all_right'], '_bnt_mar');
    $input3=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-chevron-left"></span>', $buttontitles['move_left'], '_bnt_ml');
    $input4=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-angles-left"></span>', $buttontitles['move_all_left'], '_bnt_mal');
    
    $select2=new TagRenderer("select",true);
    if($disabled) {
    	$select2->setAttribute("disabled", "disabled");
    }
    $select2->setAttribute("multiple", "multiple");
    $select2->setId($id.'_r');
    $select2->setAttribute("class", $s_class);
    
    foreach($right_list_keys as $key) {
    	$item=$source[$key];
    	$l=null;
    	$v=$key;
    	if($var!=null) {
    		$$var=$item;
    		if($label==null) {
    			$l=$item;
    		} else {
    			$l=null;eval("\$l=$label;");
    		}
    	} else {
    		$l=$item;
    	}
    	
    	$option=new TagRenderer("option",true);
    	$option->setAttribute("value", $v);
    	$option->setValue($l);
    	$select2->addHtml($option->render());
    	
    }
    
    $input5=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-angles-up"></span>', $buttontitles['move_top'], '_bnt_tp');
    $input6=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-chevron-up"></span>', $buttontitles['move_up'], '_bnt_up');
    $input7=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-chevron-down"></span>', $buttontitles['move_down'], '_bnt_dn');
    $input8=_createButton($id,$buttonclass,$disabled,'<span class="fa fa-angles-down"></span>', $buttontitles['move_bottom'], '_bnt_bt');

	$row=new TagRenderer("div",true);
	$row->setAttribute("class", "d-flex ".$class);
    if($invalid) {
        $row->appendAttribute("class","is-invalid");
    }
	$col1=new TagRenderer("div",true);
	$col1->setAttribute("class", "flex-grow-1");
	$col1->setValue($select1->render());
	$col2=new TagRenderer("div",true);
	$col2->setAttribute("class", "pl-buttons");
	$group1=new TagRenderer("div",true);
	$group1->setAttribute("class", "btn-group-vertical");
	$group1->addHtml($input1->render());
	$group1->addHtml($input2->render());
	$group1->addHtml($input3->render());
	$group1->addHtml($input4->render());
	$col2->setValue($group1->render());
	$col3=new TagRenderer("div",true);
	$col3->setAttribute("class", "flex-grow-1");
	$col3->setValue($select2->render());
	$col4=new TagRenderer("div",true);
	$col4->setAttribute("class", "pl-buttons");
	$group2=new TagRenderer("div",true);
	$group2->setAttribute("class", "btn-group-vertical");
	$input5->appendAttribute("class", " btn btn-default btn-lg");
	$input6->appendAttribute("class", " btn btn-default");
	$input7->appendAttribute("class", " btn btn-default");
	$input8->appendAttribute("class", " btn btn-default");
	$group2->addHtml($input5->render());
	$group2->addHtml($input6->render());
	$group2->addHtml($input7->render());
	$group2->addHtml($input8->render());
	$col4->setValue($group2->render());
	$row->addHtml($col1->render());
	$row->addHtml($col2->render());
	$row->addHtml($col3->render());
	$row->addHtml($col4->render());
    $s=$row->render();
	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
		$msg_row=new TagRenderer("div",true);
		$msg_row->appendAttribute("class", "invalid-feedback");
        $msg_row->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
        $s.=$msg_row->render();
	}

    $s.=TagRenderer::renderHidden($id, $hidden_val);
    
    $script='SF.ajax.loadPickListHandler(\''.$id.'\');';
    
    $s.=SmartyFaces::addScript($script);
    
    return $s;
}


function _createButton($id,$buttonclass,$disabled,$ui_icon,$title,$id_suffix) {
	$input=new TagRenderer("button",true);
	$input->setValue($ui_icon);
	$input->setAttributeIfExists("title", $title);
	$input->setAttribute("type", "button");
	$input->setId($id.$id_suffix);
	$class='pl_mr btn btn-outline-secondary btn-sm ';
	$input->setAttribute("class", $class.$buttonclass);
	if($disabled) {
		$input->setAttribute("disabled", "disabled");
	}
	return $input;
}


function _picklistButtonTitles() {
    $list=array("move_right","move_all_right","move_left","move_all_left",
        "move_top","move_up","move_down","move_bottom");
    foreach($list as $item) {
        $list[$item]=SmartyFaces::translate("picklist_".$item);
    }
    return $list;
}