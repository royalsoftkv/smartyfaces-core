<?php

function smarty_function_sf_picklist($params, $template)
{
    $tag="sf_picklist";
    
    $attributes_list=array("id","value","attachMessage","required","class","disabled");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['source']=array(
		'required'=>false,
    	'default'=>null,
    	'description'=>'Array of source items to be displayed'    		
    );
    $attributes['value']['default']=null;
    $attributes['value']['required']=false;
    $attributes['var']=array(
		'required'=>false,
    	'default'=>null,
    	'description'=>'Name of variable to iterate source and target elements'    		
    );
    $attributes['label']=array(
		'required'=>false,
    	'default'=>null,
    	'description'=>'Label for value for select list'    		
    );
    $attributes['buttonclass']=array(
		'required'=>false,
    	'default'=>null,
    	'description'=>'Style class that will be applied to picklist buttons'    		
    );
    $attributes['buttontitles']=array(
    		'required'=>false,
    		'default'=>array(),
    		'description'=>'Array of title strings that will be displayed for picklist buttons'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-picklist";
    if(is_null($source)) $source=array();
    
    
	
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
		
		
		if(SmartyFacesComponent::validationFailed($id)) {
			if(SmartyFaces::$skin=="default") $class.=" sf-vf";
		}
	} else {
		$value=  SmartyFaces::evalExpression($value);
	}
	
	if($required and !$disabled){
		SmartyFacesContext::addRequiredValidator($id);
	}
	
	SmartyFacesComponent::createComponent($id, $tag, $params, array("source"));
	
	//covert to array
// 	$value_arr=array();
// 	if(strlen($value)>0) {
// 		foreach (explode(",",$value) as $key) {
// 			$value_arr[$key]=$source[$key];
// 		}
// 	}
	$source_keys=array_keys($source);
	$value_keys=array_keys($value);
	$left_list_keys=array_diff($source_keys, $value_keys);
	$right_list_keys=$value_keys;
	
	$hidden_val=implode(",", $value_keys);
	
	$select1=new TagRenderer("select",true);
	if($disabled) {
		$select1->setAttribute("disabled", "disabled");
	}
	$select1->setAttribute("multiple", "multiple");
	$select1->setId($id.'_l');
	$select1->setAttribute("style", "min-width:100px");
	if(SmartyFaces::$skin=="bootstrap") $select1->setAttribute("class", "form-control");
    
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
    
    $input1=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthick-1-e', 'move_right', '_bnt_mr');
    $input2=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthickstop-1-e', 'move_all_right', '_bnt_mar');
    $input3=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthick-1-w', 'move_left', '_bnt_ml');
    $input4=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthickstop-1-w', 'move_all_left', '_bnt_mal');
    
    $select2=new TagRenderer("select",true);
    if($disabled) {
    	$select2->setAttribute("disabled", "disabled");
    }
    $select2->setAttribute("multiple", "multiple");
    $select2->setId($id.'_r');
    $select2->setAttribute("style", "min-width:100px");    
    if(SmartyFaces::$skin=="bootstrap") $select2->setAttribute("class", "form-control");
    
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
    
    $input5=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthickstop-1-n', 'move_top', '_bnt_tp');
    $input6=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthick-1-n', 'move_up', '_bnt_up');
    $input7=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthick-1-s', 'move_down', '_bnt_dn');
    $input8=_createButton($id,$buttonclass,$disabled,'ui-icon-arrowthickstop-1-s', 'move_bottom', '_bnt_bt');
    
    if(SmartyFaces::$skin=="bootstrap") {
		$row=new TagRenderer("div",true);
		$row->setAttribute("class", "row");
		$col1=new TagRenderer("div",true);
		$col1->setAttribute("class", "col-md-5 pl-select");
		$col1->setValue($select1->render());
		$col2=new TagRenderer("div",true);
		$col2->setAttribute("class", "col-md-1 pl-buttons");
		$group1=new TagRenderer("div",true);
		$group1->setAttribute("class", "btn-group-vertical");
		$group1->addHtml($input1->render());
		$group1->addHtml($input2->render());
		$group1->addHtml($input3->render());
		$group1->addHtml($input4->render());
		$col2->setValue($group1->render());
		$col3=new TagRenderer("div",true);
		$col3->setAttribute("class", "col-md-5 pl-select");
		$col3->setValue($select2->render());
		$col4=new TagRenderer("div",true);
		$col4->setAttribute("class", "col-md-1 pl-buttons");
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
    	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
    		$msg_row=new TagRenderer("div",true);
    		$msg_row->appendAttribute("class", SmartyFacesComponent::getFormControlValidationClass($id));
    		$row->appendAttribute("class", SmartyFacesComponent::getFormControlValidationClass($id));
    		$span=new TagRenderer("div",true);
    		$span->setAttribute("class", "help-block");
    		$span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$msg_row->addHtml($span->render());
    		$s=$row->render();
	    	$s.=$msg_row->render();
    	} else {
    		$s=$row->render();
    	}
    } else {
	    $table=new TagRenderer("table",true);
	    $table->setAttributeIfExists("class", $class);
	    $tr=new TagRenderer("tr",true);
	    $td1=new TagRenderer("td",true);
	    $td1->addHtml($select1->render());
	    $tr->addHtml($td1->render());
	    $td2=new TagRenderer("td",true);
	    $td2->addHtml($input1->render());
	    $td2->addHtml($input2->render());
	    $td2->addHtml($input3->render());
	    $td2->addHtml($input4->render());
	    $tr->addHtml($td2->render());
	    $td3=new TagRenderer("td",true);
	    $td3->addHtml($select2->render());
	    $tr->addHtml($td3->render());
	    $td4=new TagRenderer("td",true);
	    $td4->addHtml($input5->render());   
	    $td4->addHtml($input6->render());    
	    $td4->addHtml($input7->render());    
	    $td4->addHtml($input8->render());
	    $tr->addHtml($td4->render());
	    $table->addHtml($tr->render());
	    $s=$table->render();
	    if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
    } 

    
    $s.=TagRenderer::renderHidden($id, $hidden_val);
    
    $script='SF.ajax.loadPickListHandler(\''.$id.'\');';
    
    $s.=SmartyFaces::addScript($script);
    
    return $s;
}


function _createButton($id,$buttonclass,$disabled,$ui_icon,$title,$id_suffix) {
	if(SmartyFaces::$skin=="default") $input=new TagRenderer("input");
	if(SmartyFaces::$skin=="bootstrap") $input=new TagRenderer("button",true);
	if(SmartyFaces::$skin=="bootstrap") $input->setValue('<span class="ui-icon '.$ui_icon.'"></span>');
	$input->setAttribute("title", isset($buttontitles[$title]) ? $buttontitles[$title] : '');
	$input->setAttribute("type", "button");
	$input->setId($id.$id_suffix);
	$class="";
	if(SmartyFaces::$skin=="default") $class='pl_mr ui-icon '.$ui_icon.' ';
	if(SmartyFaces::$skin=="bootstrap") $class='pl_mr btn btn-default btn-xs ';
	$input->setAttribute("class", $class.$buttonclass);
	if($disabled) {
		$input->setAttribute("disabled", "disabled");
	}
	return $input;
}

?>