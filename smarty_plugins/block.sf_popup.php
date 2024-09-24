<?php


function smarty_block_sf_popup($params, $content, $template, &$repeat)
{ 
    
	
    $tag="sf_popup";
    
    $attributes_list=array("id","rendered","immediate","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['header']=array(
    	'required'=>false,
    	'default'=>"",
    	'desc'=>'Text in header of popup'		
    );
    $attributes['action']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Action to execute on popup close'		
    );
    $attributes['modal']=array(
    	'required'=>false,
    	'default'=>true,
    	'desc'=>'If set to true, the dialog will have modal behavior; other items on the page will be disabled, 
    		i.e., cannot be interacted with. Modal dialogs create an overlay below the dialog but above other page elements.'		
    );
    $attributes['width']=array(
    	'required'=>false,
    	'default'=>"'auto'",
    	'desc'=>'Width of popup. Can be auto, in px or %. If it is not numeric it must be enclosed with single qoutes (\')'
    );
    $attributes['fade']=array(
    	'required'=>false,
    	'default'=>true,
    	'type'=>'bool',
    	'desc'=>'Define if modal popup will be opened with fade effect'
    );
    $attributes['draggable']=array(
    	'required'=>false,
    	'default'=>true,
    	'type'=>'bool',
    	'desc'=>'Define if modal popup can be dragged by its header'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	$this_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];
    
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    SmartyFacesContext::$hasPopups=true;
    
    if(!$rendered) {
    	$repeat=false;
    	return;
    }
   
    if(is_null($content)){
		$this_tag_stack['id']=$id;
		SmartyFacesContext::$popupsCount++;
		return;
    }

	$id=$this_tag_stack['id'];
	
	if(isset($this_tag_stack['facets']['header'])) {
		$header=$this_tag_stack['facets']['header']['content'];
	}
	$footer="";
	if(isset($this_tag_stack['facets']['footer'])) {
		$footer=$this_tag_stack['facets']['footer']['content'];
	}
	
	if(SmartyFaces::$skin=="default") {
	
		$div=new TagRenderer("div",true);
		$div->setId($id);
		$div->setAttribute("title", $header);
		if(SmartyFaces::$skin=="default") {
			$div->setAttribute("class", "ui-helper-hidden");
		}
		$div->setValue($content);
		$s=$div->render();
		
		$modal=$modal ? 'true' : 'false';
		$config['appendTo']='$("#'.$id.'").closest("form")';
		$config['modal']="$modal";
		$config['beforeClose']="function(event, ui){".SmartyFacesComponent::buildJsAction($params)."}";
		if($fade){
			$config['show']="'fade'";
			$config['hide']="'fade'";
		}
		if($width) {
			$config['width']="$width";
		}
		$config['draggable']=$draggable ? "true" : "false";
		$arr=array();
		foreach($config as $key=>$val) {
			$arr[]="$key:$val";
		}
		$config_str="{".implode(",",$arr)."}";
		
		$script='$("#'.$id.'").dialog('.$config_str.');';
		
		$s.=SmartyFaces::addScript($script);
	} else if (SmartyFaces::$skin=="bootstrap") {
		
		$modal_fade=new TagRenderer("div",true);
		$modal_fade->setAttribute("class", ($modal ? "modal" : "non-modal").($fade ? " fade" : ""));
		$modal_fade->setId($id);
		$modal_dlg=new TagRenderer("div",true);
		$modal_dlg->setAttribute("class", "modal-dialog $class");
		if($width) {
			$modal_dlg->setAttribute("style", "width:$width");
		}
		$modal_cnt=new TagRenderer("div",true);
		$modal_cnt->setAttribute("class", "modal-content");
		$modal_hdr=new TagRenderer("div",true);
		$modal_hdr->setAttribute("class", "modal-header");
		$modal_hdr->addHtml('<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>');
		$h4=new TagRenderer("h4",true);
		$h4->setAttribute("class", "modal-title");
		$h4->setValue($header);
		$modal_hdr->addHtml($h4->render());
		$modal_cnt->addHtml($modal_hdr->render());
		
		$modal_body=new TagRenderer("div",true);
		$modal_body->setAttribute("class", "modal-body");
		$modal_body->setValue($content);
		$modal_cnt->addHtml($modal_body->render());
		
		if($footer) {
			$modal_footer=new TagRenderer("div",true);
			$modal_footer->setAttribute("class", "modal-footer");
			$modal_footer->setValue($footer);
			$modal_cnt->addHtml($modal_footer->render());
		}
		
		$modal_dlg->setValue($modal_cnt->render());
		$modal_fade->setValue($modal_dlg->render());
		
		$s=$modal_fade->render();
		
		$script='$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
				 $("#'.$id.'").modal({backdrop:'.($modal ? '"static"' : 'false').'});
				 $("#'.$id.'").on("hidden.bs.modal", function (e) {
					'.SmartyFacesComponent::buildJsAction($params).'
				});';
		if($draggable) {
			$script.='$("#'.$id.' .modal-dialog").parent().draggable({handle:".modal-header",cursor: "pointer"});';
		}
		
		$s.=SmartyFaces::addScript($script);
	}
    
    return $s;
}

?>