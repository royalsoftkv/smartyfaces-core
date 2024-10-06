<?php


function smarty_block_sf_view($params, $content, $smarty_template, &$repeat)
{ 
    

	$tag="sf_view";
	$attributes_list=array("id","class");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['template']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Name of template file in which is placed view. For system compatibility use . as directory separator when entering file path'		
	);
	$attributes['stateless']=array(
		'required'=>false,
		'default'=>null,
		'desc'=>'Defines if view will be storing its state (statefull) or stateless'		
	);
	$attributes['log']=array(
		'required'=>false,
		'default'=>false,
		'desc'=>'Used to display log information i separate window'		
	);
	$attributes['storestate']=array(
		'required'=>false,
		'default'=>'client',
		'desc'=>'Destionation for saving view state. Can be client or server.'	
	);
	if($params==null and $smarty_template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	
	SmartyFaces::$template=$smarty_template;
	SmartyFacesComponent::$current_view=SmartyFaces::getCurrentView();
    SmartyFaces::init();
    SmartyFacesContext::reset();
    SmartyFacesComponent::createComponent($id, $tag, $params);
    SmartyFaces::$logging=$log;
    if($log) SmartyFacesLogger::log("Created SmartyFaces view id=$id, stateless=".($stateless ? "true" : "false"));
    $repeat=false;
    if(is_null($content)){
        SmartyFacesComponent::$current_view_id=$id; 
        SmartyFacesComponent::$stateless=$stateless;
        SmartyFaces::$smarty->assign("SF",  SmartyFaces::createSmartyFacesVariable());
        SmartyFaces::$smarty->assign($smarty_template->getTemplateVars());
        
        $div=new TagRenderer("div",true);
        $div->setId($id);
        $class.=" skin-".SmartyFaces::$skin;
	    $div->setAttributeIfExists("class", $class);
	    
        if($template!=null) {
        	$template_dir=SmartyFaces::resolvePath(SmartyFaces::$config['view_dir']);
        	$file_name=str_replace($template_dir.DIRECTORY_SEPARATOR, "", $template);
        	SmartyFacesComponent::$current_view = $file_name;
	        SmartyFacesComponent::$current_template_id=$file_name;
        }
        
        $view=SmartyFacesComponent::$current_view;
        $sf_view_id=SmartyFacesComponent::$current_view_id;
        
        if(!in_array($storestate, SmartyFacesContext::$store_states)) $storestate="server";
        SmartyFacesContext::$storestate=$storestate;
        
        SmartyFacesContext::restoreSessionState();
        $div->setValue(SmartyFaces::getResponse($view, $sf_view_id));
        
        $s=$div->render();
        
        if($log) {
        	$s.=SmartyFacesLogger::displayLog();
        }
        
        SmartyFacesComponent::$current_view_id=null;
        SmartyFacesComponent::$current_template_id=null;
        
        echo $s;
        return;
    }

}

?>