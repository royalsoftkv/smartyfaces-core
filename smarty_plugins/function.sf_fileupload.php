<?php

function smarty_function_sf_fileupload($params, $template)
{
    $tag="sf_fileupload";
    
    $attributes_list=array("id","value","rendered","action","immediate","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['value']['required']=false;
    $attributes['value']['default']="Send";
    $attributes['value']['desc']="Used for title of the button";
    $attributes['acceptTypes']=array(
    	'required'=>false,
    	'default'>null,
    	'desc'=>'Extensions of accepted file types space separated. Default is all'
    );
    $attributes['resetCtrl']=array(
    	'required'=>false,
    	'default'=>"Reset",
    	'desc'=>'(deprecated) Label for reset control'
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
    $attributes['multiple']=array(
    	'required'=>false,
    	'default'=>false,
    	'desc'=>'Allow upload of multiple files'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(!$rendered) return;
    
    SmartyFacesComponent::createComponent($id."_f", $tag, $params,array("acceptTypes","maxSize","multiple"));
    
    $div=new TagRenderer("div",true);
    $div->setId($id."_div");
    
    $span=new TagRenderer("div",true);
    $span->setId("sf_upload_form");
    $class.=" input-group upload-group";
    $span->setAttributeIfExists("class", $class);
    
    $file=new TagRenderer("input");
    $file->setAttribute("type", "file");
    $file->setIdAndName($id, $multiple);
    if(SmartyFaces::$skin=="bootstrap") $fileClass.=" form-control";
    $file->setAttributeIfExists("class", $fileClass);
    if($multiple) {
    	$file->setAttribute("multiple", "multiple");
    }
    $span->addHtml($file->render());
    
    $submit=new TagRenderer("button",true);
    $submit->setIdAndName($id."_f");
    $submit->setAttribute("class", $buttonClass." btn btn-primary");
    $submit->setAttribute("type", "submit");
    $submit->setValue('<span class="fa fa-upload"/>');
    $submit->setAttribute("title", $value);
    $submit->setAttribute("onclick", 'SF.upload.submit(this,\''.$id.'\'); return false;');
    $span->addHtml($submit->render());

    
    $div_content=$span->render();
    
    $div_content.='<div id="sf_upload_process" style="display:none;">Sending...</div>';
    
    $sf_upload_error=new TagRenderer("div",true);
    $sf_upload_error->setId("sf_upload_error");
    $sf_upload_error->setAttribute("style", "display:none");
    $sf_upload_error->setAttribute("class", "invalid-feedback p-2");
    
    $msg_wrapper=new TagRenderer("div",true);
    $msg_wrapper->setAttribute("class", "alert alert-danger");

    $sf_upload_error_msg=new TagRenderer("span",true);
    $sf_upload_error_msg->setId("sf_upload_error_msg");
    
    $sf_upload_error->addHtml($sf_upload_error_msg->render());
    $a=new TagRenderer("a",true);
    $a->setAttribute("href", "#");
    $a->setAttribute("onclick", 'SF.upload.reset(\''.$id.'\'); return false;');
    $a->setValue('<span class="fa fa-times"/>');
    
    $msg_wrapper->addHtml($sf_upload_error_msg->render());
    $a->setAttribute("class", "text-danger pull-right");
    $sf_upload_error->addHtml($a->render());

    $div_content.=$sf_upload_error->render();
    
    
    
    $div_content.='<iframe id="sf_iframe" name="sf_iframe" style="border: 0px solid rgb(255, 255, 255); height: 0pt; width: 0pt;"></iframe>';
    $div_content.=TagRenderer::renderHidden("sf_upload_data", "");
    $div->setValue($div_content);
    
    return $div->render();
}

?>