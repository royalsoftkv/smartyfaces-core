<?php

function smarty_function_sf_editor($params, $template)
{
    $tag="sf_editor";
    
    $attributes_list=array("id","value","required","rendered","validator","attachMessage","class","disabled");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['config']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Congfiguration array passed to tinyMce to initialization'	
    );
    $attributes['width']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Width of editor'	
    );
    $attributes['height']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Height of editor'	
    );
    $attributes['summernoteOptions']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Options for summernote editor'	
    );
    $attributes['summernotePlugins']=array(
    	'required'=>false,
    	'default'=>array(),
    	'desc'=>'Array of plugins to be loaded with summernote'	
    );
    $attributes['editor']=array(
    	'required'=>false,
    	'default'=>'summernote',
    	'desc'=>'Type of the editor to use. Supported: summernote, CK Editor'	
    );
    $attributes['editorconfig']=array(
    	'required'=>false,
    	'default'=>array(),
    	'desc'=>'Configuration options for editor'	
    );
    $attributes['ckeditorpath']=array(
    	'required'=>false,
    	'default'=>SmartyFaces::getResourcesUrl() ."/ckeditor/ckeditor.js",
    	'desc'=>'Path to main ckeditor javascript'	
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-input-editor";
    
    if(!$rendered) return;
    
    SmartyFacesComponent::createComponent($id, $tag, $params);
    
    
    if($validator) {
    	SmartyFacesContext::addValidator($id, $validator);
    }
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    $tiny_mce_path=SmartyFaces::$config['tiny_mce_path'];
    SmartyFacesContext::$bindings[$id]=$value;
    
	if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = SmartyFacesContext::$formData[$id];
		if(SmartyFacesComponent::validationFailed($id)) {
			if(SmartyFaces::$skin=="default") $class.=" sf-vf";
		}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
    }
    
    $def_config=array('script_url'=>"$tiny_mce_path/tiny_mce_gzip.php");
    if($config==null) {
    	$config=$def_config;
    } else if (is_array($config)) {
    	$config=array_merge($def_config,$config);
    }
    if($width!=null) $config['width']=$width;
    if($height!=null) $config['height']=$height;
    if($disabled) $config['readonly']=1;
    
    $div=new TagRenderer("div",true);
    if(SmartyFaces::$skin == "bootstrap") {
	    $div->setAttribute("style", "width:".$width);
    } else {
	    $div->setAttribute("style", "width:".$width.";height:".$height);
    }
    $ta=new TagRenderer($disabled ? "div" : "textarea",true);
    $div->appendAttribute("class", "sf-editor");
    if($disabled && SmartyFaces::$skin == "bootstrap") {
    	$div->setAttribute("class", "well well-sm sf-editor-disabled");
    }
    $ta->setAttributeIfExists("class", $class);
    $ta->setIdAndName($id);
    $ta->setValue($value);
    $div->setValue($ta->render());
    $s=$div->render();
    
    if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
    $serverUrl=SmartyFaces::getServerUrl();
    
    if(SmartyFaces::$skin == "bootstrap") {
    	if(!$disabled) {
	    	static $attached_ext_editor;
	    	if($editor=="ckeditor") {
	    		if(!$attached_ext_editor) {
	    			$url = $ckeditorpath;
	    			$s.=SmartyFaces::addScript($url, true);
	    			$attached_ext_editor = true;
	    		}
	    		$s.=SmartyFaces::addScript('SF.loadCKEditor("'.$id.'",'.json_encode($editorconfig).');');
	    	} else {
		    	if(!$attached_ext_editor) {
			    	$url = SmartyFaces::getResourcesUrl() ."/summernote/summernote.min.js";
			    	$s.=SmartyFaces::addScript($url, true);
			    	if($summernotePlugins) {
			    		foreach($summernotePlugins as $summernotePlugin) {
					    	$url = SmartyFaces::getResourcesUrl() ."/summernote/summernote-$summernotePlugin.js";
					    	$s.=SmartyFaces::addScript($url, true);
			    		}
			    	}
			    	$url = SmartyFaces::getResourcesUrl() ."/font-awesome/css/font-awesome.min.css";
			    	$s.='<link type="text/css" rel="stylesheet" href="'.$url.'">';
			    	$url = SmartyFaces::getResourcesUrl() ."/summernote/summernote.css";
			    	$s.='<link type="text/css" rel="stylesheet" href="'.$url.'">';
			    	$attached_ext_editor = true;
		    	}
		    	$config2=array();
		    	$config2['height']=$height;
		    	if($summernoteOptions) {
			    	foreach ($summernoteOptions as $name=>$summernoteOption) {
			    		$config2[$name]=$summernoteOption;
			    	}
		    	}
		    	$s.=SmartyFaces::addScript('SF.loadSummernote("'.$id.'",'.json_encode($config2).');');
	    	}
    	}
    } else {
	    $s.=SmartyFaces::addScript($tiny_mce_path.'/jquery.tinymce.js',true);
    	$s.=SmartyFaces::addScript('SF.loadTinyMce(\''.$id.'\','.json_encode($config).');');
    }
    
    return $s;
}

?>