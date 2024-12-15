<?php

function smarty_function_sf_datepicker($params, $template)
{
    $tag="sf_datepicker";
    $attributes_list=array("id","value","required","attachMessage","class","disabled","validator","converter",
    		"action","size","title","onclick","onchange","style","rendered");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['dateFormat']=array(
    			'required'=>false,
   				'default'=>"dd.mm.yy",
   				'desc'=>'Date format used to display date in datepicker (Note: bootstrap theme uses moment.js date formats)');
    $attributes['buttonImage']=array(
   				'required'=>false,
   				'default'=>null,
   				'desc'=>'Custom path to calendar image (not used for bootstrap skin)');
    $attributes['bootstrapIcon']=array(
    		'required'=>false,
    		'default'=>"calendar",
    		'desc'=>'Icon to display for control button');
    $attributes['datepickerOptions']=array(
   				'required'=>false,
   				'default'=>array(),
   				'desc'=>'Additional date picker options');
    $attributes['action']['desc']='Ajax action invoked on change event';
    $attributes['size']['desc']='Defines size of input text field for datepicker';

    if($params==null and $template==null) return $attributes;
    
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    $id=SmartyFacesComponent::checkNested($id,$template);
    
    if(SmartyFaces::$skin=="default") $class.=" sf-input sf-input-datepicker";
    
    if($required and !$disabled){
        SmartyFacesContext::addRequiredValidator($id);
    }
    
    if(!is_null($validator)) {
    	SmartyFacesContext::addValidator($id,$validator);
    }
    if(strlen($converter ?? "")>0 and !$disabled) {
    	SmartyFacesContext::addConverter($id,$converter);
    }
    SmartyFacesComponent::createComponent($id, $tag, $params);
    if(!$rendered) return;
    
    
    SmartyFacesContext::$bindings[$id]=$value;
	if(SmartyFaces::$validateFailed and !$disabled) {
    	$value = SmartyFacesContext::$formData[$id];
		if(SmartyFacesComponent::validationFailed($id)) {
			if(SmartyFaces::$skin=="default") $class.=" sf-vf";
		}
    } else {
	    $value=  SmartyFaces::evalExpression($value);
	    if(strlen($converter ?? "")>0) {
	    	$value=$converter::toString($value);
	    }
    }


    if(strlen($onchange ?? "")>0 and substr($onchange, -1, 1)!=";") $onchange.=";";
    if($action) {
	    $stateless=SmartyFacesComponent::$stateless;
	    if($stateless) {
	    	$action=$onchange.'SF.a(this,\''.$action.'\',null) ';
	    } else {
	    	$action=$onchange.'SF.a(this,null,null) ';
	    }
    } else {
    	$action=$onchange;
    }
    
    $i=new TagRenderer("input",false);
    $i->setAttribute("type", "text");
    $i->setAttributeIfExists("style", $style);
    if(Smartyfaces::$skin=="bootstrap") $class.=" form-control";
    $i->setAttributeIfExists("class", $class);
    $i->setAttributeIfExists("title", $title);
    $i->setAttributeIfExists("onclick", $onclick);
    $i->setAttributeIfExists("size", $size);
    if($disabled) {
    	$i->setAttribute("disabled", "true");
    }
    $i->setIdAndName($id);
    $i->setValue($value);
    $i->setAttribute("onchange", $action);
    
    if(SmartyFaces::$skin=="bootstrap") {
    	$div=new TagRenderer("div",true);
    	$div->setId($id."_dtp");
    	$div_class="input-group datepicker-group date";
    	if(!$disabled) $div_class.=" ".SmartyFacesComponent::getFormControlValidationClass($id);
    	$div->setAttribute("class", $div_class);
    	$div->addHtml($i->render());
    	$span=new TagRenderer("span",true);
    	$span->setAttribute("class", "input-group-addon");
    	$span->setAttribute("for", $id);
    	$span_icon=new TagRenderer("span",true);
    	$span_icon->setAttribute("class", "glyphicon glyphicon-$bootstrapIcon");
    	$span->setValue($span_icon->render());
    	$div->addHtml($span->render());
    	$s=$div->render();
    	if($attachMessage and !$disabled and isset(SmartyFacesMessages::$messages[$id][0])) {
    		$m_div=new TagRenderer("div",true);
    		$m_div->setAttribute("class", SmartyFacesComponent::getFormControlValidationClass($id));
    		$span=new TagRenderer("span",true);
    		$span->setAttribute("class", "help-block");
    		$span->setValue(SmartyFacesMessages::$messages[$id][0]['message']);
    		$m_div->setValue($span->render());
    		$s.=$m_div->render();
    	}
    } else {
	    $s=$i->render();
	    if($attachMessage and !$disabled) $s.=SmartyFacesComponent::renderMessage($id);
    }
    
    
	
    
	if(SmartyFaces::$skin=="default") {
	    if($disabled) $options["disabled"]=true;
	    if($buttonImage==null or strlen($buttonImage)==0) $buttonImage="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAA3NCSVQICAjb4U/gAAAAMFBMVEX///+gmpr///8YUZjj4+OsrKzDw8NUh8Y3crsAAADV1dW8vLy2trb5+fmqSZ0AAAAF/nxFAAAAEHRSTlMA//////////8i//////8RTfYmuQAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAABlSURBVAiZYzCGAgbzcjAoZrDoAINmBriUkAsYKDIIpYEBkJHi5uLm4g1k7N69e9eqM0DGFm8XtzRXIOPM0dB7764CGaeWroIwopau8r0LkgoNDQ0UDAUylMBAkUEQChj+zwSD/wCE6S3ZKEtU2QAAAABJRU5ErkJggg==";
	    $options["dateFormat"]=$dateFormat;
	    $options["buttonImage"]=$buttonImage;
	    $options["buttonImageOnly"]=true;
	    $options["showOn"]="button";
		$options_json=json_encode($options,JSON_FORCE_OBJECT);
		 
		$script='
				SF.loadDatepicker("'.$id.'",'.$options_json.');
				';
		$s.=SmartyFaces::addScript($script);
	    
	} else if (SmartyFaces::$skin=="bootstrap") {
		$options["format"]=$dateFormat;
		$options["locale"]=SmartyFaces::getLanguage();
//		$options['debug']=true;
		foreach ($datepickerOptions as $name=>$val) {
			$options[$name]=$val;
		}
		static $attached_ext_dtp;
		if(!$attached_ext_dtp) {
			$url = SmartyFaces::getResourcesUrl() ."/moment/moment.min.js";
			$s.=SmartyFaces::addScript($url, true);
			$url = SmartyFaces::getResourcesUrl() ."/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js";
			$s.=SmartyFaces::addScript($url, true);
			if(SmartyFaces::getLanguage()!="en") {
				$url = SmartyFaces::getResourcesUrl() ."/moment/locale/".SmartyFaces::getLanguage().".js";
				$s.=SmartyFaces::addScript($url, true);
			}
			$url = SmartyFaces::getResourcesUrl() ."/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css";
			$s.='<link type="text/css" rel="stylesheet" href="'.$url.'">';
			$attached_ext_dtp = true;
		}
		$options_json=json_encode($options,JSON_FORCE_OBJECT);
		$s.=SmartyFaces::addScript('$("#'.$id.'_dtp").datetimepicker('.$options_json.');');
		if(!empty($action)) {
			$s.=SmartyFaces::addScript('$("#'.$id.'_dtp").datetimepicker().on("dp.change", function(e){
				if(e.oldDate !=null) {
					$(this).find("input").change();
				}
			});');
		}
	}
    
	
    return $s;
}

?>
