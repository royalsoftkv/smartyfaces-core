<?php

function smarty_function_sf_editor($params, $template)
{
	$tag="sf_editor";

	$attributes_list=array("id","value","required","rendered","validator","attachMessage","class","disabled");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
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

	if(!$rendered) return;

	SmartyFacesComponent::createComponent($id, $tag, $params);


	if($validator) {
		SmartyFacesContext::addValidator($id, $validator);
	}

	if($required and !$disabled){
		SmartyFacesContext::addRequiredValidator($id);
	}
	SmartyFacesContext::$bindings[$id]=$value;

    $invalid = SmartyFaces::$validateFailed;
	if($invalid and !$disabled) {
		$value = SmartyFacesContext::$formData[$id];
	} else {
		$value=  SmartyFaces::evalExpression($value);
	}

	$div=new TagRenderer("div",true);
    if(isset($_POST['sf_editor_height'][$id])) {
        $heights = $_POST['sf_editor_height'][$id];
        $heights_arr = explode(";", $heights);
        $div_height = $heights_arr[0].'px';
        $height =   $heights_arr[1];
    } else {
        $div_height = 'auto';
    }
    $div->setAttribute("style", "width:".$width.";height:".$div_height);
	$ta=new TagRenderer($disabled ? "div" : "textarea",true);
    $c_class = $class . " sf-editor " . ($invalid ? "is-invalid" : "");
	if($disabled) {
        $c_class.=" well well-sm sf-editor-disabled bg-light border p-2";
	}
    $div->setAttribute("class",$c_class);
	$ta->setIdAndName($id);
	$ta->setValue($value);
	$div->setValue($ta->render());
	$s=$div->render();

	if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
	$serverUrl=SmartyFaces::getServerUrl();

	if(!$disabled) {
		static $attached_ext_editor;
		if($editor=="ckeditor") {
			if(!$attached_ext_editor) {
				$url = $ckeditorpath;
				$s.=SmartyFaces::addScript($url, true);
				$attached_ext_editor = true;
			}
			$editorconfig['language']=get_editorlanguage();
			if(!empty($height)) {
				$editorconfig['height']=$height;
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

	return $s;
}

function get_editorlanguage() {
	$lng = SmartyFaces::getLanguage();
	if($lng == 'sr') $lng = 'sr-latn';
	return $lng;
}

?>