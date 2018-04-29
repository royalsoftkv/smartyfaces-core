<?php

function smarty_function_sf_messages($params, $template)
{
    $tag="sf_messages";
    
    
    $attributes_list=array("class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['for']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'ID of compoenent to which is attached message'		
    );
    $attributes['global']=array(
    	'required'=>false,
    	'default'=>false,
    	'desc'=>'If set, only global messages will be shown, i.e. messages not attached to any components'		
    );
    $attributes['style']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Style for message'		
    );
    $attributes['styled']=array(
    	'required'=>false,
    	'default'=>true,
    	'desc'=>'Indicates if messages will be styled by default'		,
    	'type'=>'bool'
    );
    $attributes['customClasses']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Array which defines custom classes for different message types'
    );
    $attributes['flash']=array(
    	'required'=>false,
    	'default'=>true,
    	'desc'=>'Show also flash messages from session'
    );
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);
    
    if(SmartyFacesValidator::passed() && !$flash) return;
    
    if($flash) {
    	//TODO:SESSION-WRITE
    	if(isset($_SESSION[SmartyFacesMessages::FLASH_MESSAGE_SESSION_KEY])) {
	    	if(count($_SESSION[SmartyFacesMessages::FLASH_MESSAGE_SESSION_KEY])>0) {
	    		foreach($_SESSION[SmartyFacesMessages::FLASH_MESSAGE_SESSION_KEY] as $message) {
	    			SmartyFacesMessages::$messages[null][]=$message;
	    		}
	    		unset($_SESSION[SmartyFacesMessages::FLASH_MESSAGE_SESSION_KEY]);
	    	}
    	}
    	
    }    
    if($for==null){
    	if(SmartyFaces::$skin=="default") {
		    $cont=new TagRenderer("ul",true);
    	} else {
    		$cont=new TagRenderer("div",true);
    	}
	    $cont->setAttribute("class", $styled ? (SmartyFaces::$skin=="default" ? 'sf-msg-box' : '') : '');
	    $cont->appendAttribute("class", $class);
	    $cont->passAttributes($attributes_values, array("style"));
	    
	   
	    
        foreach(SmartyFacesMessages::$messages as $id=>$messageList) {
        	if($id!=null and $global) continue;
        	if(SmartyFaces::$skin=="default") {
	        	$li=new TagRenderer("li",true);
        	}
            foreach($messageList as $message){
                $type=$message['type'];
                $m=$message['message'];
                $msg_class="";
                if($styled && SmartyFaces::$skin=="default") $msg_class="sf-msg-".$type;
                if($styled && SmartyFaces::$skin=="bootstrap") $msg_class="alert alert-dismissable alert-".SmartyFacesMessages::convertToBootstrapStyles($type);
                if($customClasses!=null && isset($customClasses[$type])) {
                	$msg_class=$customClasses[$type];
                }
                $div=new TagRenderer("div",true);
                $div->setAttribute("onclick", "$(this).fadeOut();");
                $div->setAttribute("class", ($styled ? (SmartyFaces::$skin=="default" ? 'sf-msg' : '') : '').' '.$class."-msg ".$msg_class." ".$msg_class.'-global');
                $div->setValue($m);
                if(SmartyFaces::$skin=="default") {
	            	$li->addHtml($div->render());
                } else {
                	$cont->addHtml($div->render());
                }
            }
            if(SmartyFaces::$skin=="default") {
            	$cont->addHtml($li->render());
            }
        }
        return $cont->render();
    } else {
        if(isset(SmartyFacesMessages::$messages[$for])){
            $errors=array();
            foreach(SmartyFacesMessages::$messages[$for] as $message){
                $type=$message['type'];
                $m=$message['message'];
                $msg_class="";
                if($styled && SmartyFaces::$skin=="default") $msg_class="sf-msg-".$type;
                if($styled && SmartyFaces::$skin=="bootstrap") $msg_class=" help-block";
                if($customClasses!=null && isset($customClasses[$type])) {
                	$msg_class=$customClasses[$type];
                }
                $span=new TagRenderer("span",true);
                $span->setAttribute("class", $class." ".$msg_class);
                $span->passAttributes($attributes_values, array("style"));
                $span->setValue($m);
                
                if(SmartyFaces::$skin=="bootstrap") {
                	$msg_cont=new TagRenderer("div",true);
                	$class="";
                	switch($type){
			    		case SmartyFacesMessages::INFO: $class="has-success";break;
			    		case SmartyFacesMessages::WARNING: $class="has-warning";break;
			    		case SmartyFacesMessages::ERROR: $class="has-error";break;
			    		case SmartyFacesMessages::SUCCESS: $class="has-success";break;
                	}
                	$msg_cont->setAttributeIfExists("class", $class);
                	$msg_cont->setValue($span->render());
                }
                
                if(SmartyFaces::$skin=="bootstrap") {
                	$errors[]=$msg_cont->render();
                } else {
	                $errors[]=$span->render();
                }
            }
            
            return implode("", $errors);
        }
    }
}

?>