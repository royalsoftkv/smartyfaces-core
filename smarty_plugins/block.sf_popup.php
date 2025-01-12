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
    	'desc'=>'(deprecated) If set to true, the dialog will have modal behavior; other items on the page will be disabled, 
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
    	'default'=>false,
    	'type'=>'bool',
    	'desc'=>'(deprecated) Define if modal popup can be dragged by its header'
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
	

    $modal_fade=new TagRenderer("div",true);
    $modal_fade->setAttribute("class", "modal".($fade ? " fade" : ""));
    $modal_fade->setAttribute("data-bs-backdrop","static");
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
    $h5=new TagRenderer("h5",true);
    $h5->setAttribute("class", "modal-title");
    $h5->setValue($header);
    $modal_hdr->addHtml($h5->render());
    $modal_hdr->addHtml('<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>');
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

//    $script='$("body").removeClass("modal-open");
//            $(".modal-backdrop").remove();
//             $("#'.$id.'").modal({backdrop:'.($modal ? '"static"' : 'false').'});
//             $("#'.$id.'").on("hidden.bs.modal", function (e) {
//                '.SmartyFacesComponent::buildJsAction($params).'
//            });';

    $script='SF.popup.open("'.$id.'", function() { '.SmartyFacesComponent::buildJsAction($params).' } )';

    $s.=SmartyFaces::addScript($script);

    return $s;
}

