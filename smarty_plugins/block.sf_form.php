<?php


function smarty_block_sf_form($params, $content, $template, &$repeat)
{ 
    
    $tag="sf_form";

    $attributes_list=array("id","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['class']['default']="";
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));

    $this_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];
    //$id=SmartyFacesComponent::getParameter($tag, "id", uniqid(), $params);
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    if(SmartyFaces::$skin=="default") $class.=" sf-form";
    
    if(is_null($content)){
        SmartyFacesComponent::setCurrentStateId();
        $tr=new TagRenderer('form',true);
        $tr->setIdAndName($id);
        $tr->setAttribute("method", "post");
        $tr->setAttribute("action", "");
        $tr->setAttribute("enctype", "multipart/form-data");
        $tr->setAttribute("autocomplete", "off");
        $tr->setAttributeIfExists("class", $class);
        echo $tr->renderOpenTag();
        SmartyFacesContext::$hasPopups=false;
        SmartyFacesContext::$popupsCount=0;
        SmartyFacesContext::$default_button=array();
        $this_tag_stack['id']=$id;
        return;
    }
    
    $s=$content;
    $state_id=  SmartyFacesComponent::$current_state_id;
    $view=  SmartyFacesComponent::$current_view;
    $sf_view_id=  SmartyFacesComponent::$current_view_id;
    
    $stateless=SmartyFacesComponent::$stateless;
    
    $s.=TagRenderer::renderHidden("sf_view", $view);
    $s.=TagRenderer::renderHidden("sf_view_id", $sf_view_id);
    
    if(SmartyFaces::$logging) {
	    $s.=TagRenderer::renderHidden("sf_logging", "1");
    }
    if(SmartyFacesComponent::$current_template_id!=null) {
    	$sf_template=SmartyFacesComponent::$current_template_id;
	    $s.=TagRenderer::renderHidden("sf_template", $sf_template);
    }
    if(!$stateless) {
    	SmartyFacesContext::storeState();
    }
    if($stateless) {
    	$form_data['sf_vars']=$this_tag_stack['form_vars'];
    	$form_data['sf_validators']=SmartyFacesContext::$validators;
    	$form_data['sf_converters']=SmartyFacesContext::$converters;
    	$s.=TagRenderer::renderHidden("sf_state", urlencode(json_encode($form_data)));
    } else {
    	if(SmartyFacesContext::$storestate=="server") {
	    	$s.=TagRenderer::renderHidden("sf_state_id", $state_id);
    	} else {
		    $s.=TagRenderer::renderHidden("sf_state_id", $state_id);
    		if(SmartyFaces::$config['compress_state']) {
		    	$s.=TagRenderer::renderHidden("sf_state_data", base64_encode(gzdeflate(serialize(SmartyFacesContext::$state))));
    		} else {
		    	$s.=TagRenderer::renderHidden("sf_state_data", base64_encode(serialize(SmartyFacesContext::$state)));
    		}
    	}
    	$s.=TagRenderer::renderHidden("sf_state_store", SmartyFacesContext::$storestate);
    }
    
	SmartyFaces::$ajaxkey = SFSession::get('sf_ajax_key', uniqid());
	SFSession::set('sf_ajax_key', SmartyFaces::$ajaxkey);
    $s.=TagRenderer::renderHidden("sf_ajax_key", SmartyFaces::$ajaxkey);
    
    $tr=new TagRenderer('form',true);
    $s.=$tr->renderCloseTag();
    
    if(SmartyFacesContext::$hasPopups && SmartyFacesContext::$popupsCount==0) {
    	$script='SF.popup.removeAll();';
    	SmartyFaces::addScript($script);
    }
    
    if(count(SmartyFacesContext::$default_button)>0) {
    	$script='SF.setDeafultButton("'.@$this_tag_stack['id'].'",'.json_encode(SmartyFacesContext::$default_button).')';
    	SmartyFaces::addScript($script);
    }

    if(SmartyFaces::$ajax && SmartyFaces::$config['progressive_loading']) {
    	$s.=SmartyFaces::loadScripts();
    }
    
    SmartyFacesContext::$default_button=array();
    return $s;
    
}

