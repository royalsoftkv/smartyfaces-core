<?php

function smarty_function_sf_fileupload($params, $template)
{
    $tag="sf_fileupload";
    
    $attributes_list=array("id","value","rendered","action","immediate","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['value']['required']=false;
    $attributes['value']['default']="Send";
    $attributes['acceptTypes']=array(
    	'required'=>false,
    	'default'>null,
    	'desc'=>'Extensions of accepted file types space separated. Default is all'
    );
    $attributes['resetCtrl']=array(
    	'required'=>false,
    	'default'=>"Reset",
    	'desc'=>'Label for reset control'
    );
    $attributes['maxSize']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Limited file size for upload. Default is unlimited'
    );
    $attributes['buttonClass']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Style class of button'
    );
    $attributes['fileClass']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Style class of input file'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(!$rendered) return;
    
    SmartyFacesComponent::createComponent($id."_f", $tag, $params,array("acceptTypes","maxSize"));
    
    $div=new TagRenderer("div",true);
    $div->setId($id."_div");
    
    $span=new TagRenderer("div",true);
    $span->setId("sf_upload_form");
    if(SmartyFaces::$skin=="bootstrap") $class.=" input-group upload-group";
    $span->setAttributeIfExists("class", $class);
    
    $file=new TagRenderer("input");
    $file->setAttribute("type", "file");
    $file->setIdAndName($id);
    if(SmartyFaces::$skin=="bootstrap") $fileClass.=" form-control";
    $file->setAttributeIfExists("class", $fileClass);
    $span->addHtml($file->render());
    
    if(SmartyFaces::$skin=="bootstrap") {
    	$group_span=new TagRenderer("span",true);
    	$group_span->setAttribute("class", "input-group-btn");
    	$submit=new TagRenderer("button",true);
    	$submit->setIdAndName($id."_f");
    	$submit->setAttribute("class", $buttonClass." btn btn-primary");
    	$submit->setAttribute("type", "submit");
    	$submit->setValue('<span class="glyphicon glyphicon-upload"/>');
    	$submit->setAttribute("title", $value);
    	$submit->setAttribute("onclick", 'SF.upload.submit(this,\''.$id.'\');');
    	$group_span->setValue($submit->render());
    	$span->addHtml($group_span->render());
    } else {
	    $submit=new TagRenderer("input");
	    $submit->setAttribute("type", "submit");
	    $submit->setAttributeIfExists("class", $buttonClass);
	    $submit->setIdAndName($id."_f");
	    $submit->setValue($value);
	    $submit->setAttribute("onclick", 'SF.upload.submit(this,\''.$id.'\');');
	    $span->addHtml($submit->render());
    }
    
    
    $div_content=$span->render();
    
    $div_content.='<div id="sf_upload_process" style="display:none;">Sending...</div>';
    
    $sf_upload_error=new TagRenderer("span",true);
    $sf_upload_error->setId("sf_upload_error");
    $sf_upload_error->setAttribute("style", "display:none");
    
    if(SmartyFaces::$skin=="bootstrap") {
    	$msg_wrapper=new TagRenderer("div",true);
    	$msg_wrapper->setAttribute("class", "alert alert-danger");
    	
    }
    
    $sf_upload_error_msg=new TagRenderer("span",true);
    $sf_upload_error_msg->setId("sf_upload_error_msg");
    
    $sf_upload_error->addHtml($sf_upload_error_msg->render());
    $sf_upload_error->addHtml('&nbsp;');
    $a=new TagRenderer("a",true);
    $a->setAttribute("href", "#");
    $a->setAttribute("onclick", 'SF.upload.reset(\''.$id.'\'); return false;');
    $a->setValue($resetCtrl);
    
    if(SmartyFaces::$skin=="bootstrap") {
    	$msg_wrapper->addHtml($sf_upload_error_msg->render());
    	$a->setAttribute("class", "alert-link pull-right");
    	$msg_wrapper->addHtml($a->render());
    	$sf_upload_error->setValue($msg_wrapper->render());
    } else {
	    $sf_upload_error->addHtml($a->render());
    }
    
    $div_content.=$sf_upload_error->render();
    
    
    
    $div_content.='<iframe id="sf_iframe" name="sf_iframe" style="border: 0px solid rgb(255, 255, 255); height: 0pt; width: 0pt;"></iframe>';
    $div_content.=TagRenderer::renderHidden("sf_upload_data", "");
    $div->setValue($div_content);
    
    return $div->render();
}

?>