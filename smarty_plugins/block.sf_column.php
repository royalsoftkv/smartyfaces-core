<?php

function smarty_block_sf_column($params, $content, $template, &$repeat)
{
	$tag="sf_column";
	
	

	$attributes_list=array("id","class","style","rendered","title");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['header']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Content that will be displayed in header of column'	
	);
	$attributes['sortby']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Expression for sorting if datamodel is attached to table'		
	);
	$attributes['width']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Width of column'		
	);
	$attributes['align']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Align of column'		
	);
	$attributes['reorder']=array(
		'required'=>false,
		'default'=>false,
		'desc'=>'Define this column as reordable handle'		
	);
	$attributes['reorderlist']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Definition of reordable list'		
	);
	if($params==null and $template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	
	
	if(strlen($sortby ??'')>0) {
		$params['action']='#['.$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-2][1]['datamodel'].'->sort()]';
		$col_id=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-2][2]['table']['attributes']['id']."-".$id;
		SmartyFacesComponent::createComponent($col_id, $tag, $params, array("sortby"));
	}
	
	if(!$rendered) {
		$repeat=false;
		return;
	}

	$tag_stack = $template->smarty->_cache['_tag_stack'];
	$parent_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-2][2];
	$this_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];
    $first_pass=$parent_tag_stack['first_pass'];
	$visibleColumns = $parent_tag_stack['params']['visibleColumns'];
    
    if(isset($parent_tag_stack['empty_data']) and $parent_tag_stack['empty_data']) {
    	$repeat=false;
    	return;
    }
	
	if (is_null($content)) {
    	if($first_pass) {
    		$column_count=(isset($parent_tag_stack['columns']) ? count($parent_tag_stack['columns']) : 0);
    		$column_index=$column_count;
	    	$parent_tag_stack['columns'][$column_index]=$params;
	    	$this_tag_stack['column_index']=$column_index;

	    	if($reorder) {
	    		$params['action']='#['.$reorderlist.'->reorder()]';
	    		SmartyFacesComponent::createComponent($id, $tag, $params);
	    		$parent_tag_stack['reorder']=true;
	    		$parent_tag_stack['order_id']=$id;
	    	}	    	
	    	
    	}
        $cell=array();
        $cell['attributes']['class']=$class;
        $cell['attributes']['style']=$style;
        $cell['attributes']['align']=$align;
        $cell['sortby']=$sortby;
		if(count($visibleColumns)==0  || in_array($id, $visibleColumns)) {
	        $parent_tag_stack['table']['rows'][count($parent_tag_stack['table']['rows'])-1]['cells'][]=$cell;
	    }
    }
    if($first_pass) {
    	$column_index=$this_tag_stack['column_index'];
    	if($reorder) {
    		$parent_tag_stack['columns'][$column_index]['header']=_createReorderHeader($parent_tag_stack['order_id']);
    	} else {
	    	if(isset($this_tag_stack['facets']['header'])) {
	    		$parent_tag_stack['columns'][$column_index]['header']=$this_tag_stack['facets']['header']['content'];
	    	}
    	}
	}
	$parent_tag_stack['col_index']++;
    if($reorder) {
    	$content=_getReorderContent($parent_tag_stack['order_id'],$parent_tag_stack['index'],$parent_tag_stack['count']);
    }
	if(count($visibleColumns)==0  || in_array($id, $visibleColumns)) {
        $parent_tag_stack['table']['rows'][count($parent_tag_stack['table']['rows'])-1]['cells'][count($parent_tag_stack['table']['rows'][count($parent_tag_stack['table']['rows'])-1]['cells'])-1]['content']=trim($content ?? "");
	}

}

function _createReorderHeader($id) {
	$a=new TagRenderer("a",true);
	$a->setAttribute("id", $id);
	$a->setAttribute("href", "#");
	$a->setAttribute("onclick", "SF.reorder.save(this);return false");
	$a->setAttribute("class", "btn btn-default btn-xs");
	$a->setValue('<span class="glyphicon glyphicon-floppy-disk text-primary"></span>');
	$s= $a->render();
	return $s;
}

function _getReorderContent($id,$index,$count) {
	$first=($index==0);
	$last=($index==$count-1);
	
	$div_group=new TagRenderer("div",true);
	$div_group->setAttribute("class", "btn-group");
	
	$btn_top=new TagRenderer("button",true);
	$btn_top->setAttribute("id", $id);
	$btn_top->setAttribute("class", "btn btn-default btn-xs");
	if($first) $btn_top->appendAttribute("class", "disabled");
	$btn_top->setAttribute("type", "button");
	$btn_top->setAttribute("onclick", "SF.reorder.move(this,'top',$index)");
	$btn_top->setValue('<span class="ui-icon ui-icon-arrowthickstop-1-n"></span>');
	
	$btn_up=new TagRenderer("button",true);
	$btn_up->setAttribute("id", $id);
	$btn_up->setAttribute("class", "btn btn-default btn-xs");
	if($first) $btn_up->appendAttribute("class", "disabled");
	$btn_up->setAttribute("type", "button");
	$btn_up->setAttribute("onclick", "SF.reorder.move(this,'up',$index)");
	$btn_up->setValue('<span class="ui-icon ui-icon-arrowthick-1-n"></span>');
	
	$btn_pos=new TagRenderer("div",true);
	$btn_pos->setAttribute("class", "btn btn-xs");
	$btn_pos->setAttribute("style", "width:40px");
	$btn_pos->setValue('<span class="badge ordinal order_handle">'.($index+1).'</span>');
	
	$btn_down=new TagRenderer("button",true);
	$btn_down->setAttribute("id", $id);
	$btn_down->setAttribute("class", "btn btn-default btn-xs");
	if($last) $btn_down->appendAttribute("class", "disabled");
	$btn_down->setAttribute("type", "button");
	$btn_down->setAttribute("onclick", "SF.reorder.move(this,'down',$index)");
	$btn_down->setValue('<span class="ui-icon ui-icon-arrowthick-1-s"></span>');	
	
	$btn_bottom=new TagRenderer("button",true);
	$btn_bottom->setAttribute("id", $id);
	$btn_bottom->setAttribute("class", "btn btn-default btn-xs");
	if($last) $btn_bottom->appendAttribute("class", "disabled");
	$btn_bottom->setAttribute("type", "button");
	$btn_bottom->setAttribute("onclick", "SF.reorder.move(this,'bottom',$index)");
	$btn_bottom->setValue('<span class="ui-icon ui-icon-arrowthickstop-1-s"></span>');	
	
	$div_group->addHtml($btn_top->render());
	$div_group->addHtml($btn_up->render());
	$div_group->addHtml($btn_pos->render());
	$div_group->addHtml($btn_down->render());
	$div_group->addHtml($btn_bottom->render());
	
	return $div_group->render();
	
}

?>
