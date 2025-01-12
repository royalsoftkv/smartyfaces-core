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
	    $flashMessages = SFSession::get(SmartyFacesMessages::FLASH_MESSAGE_SESSION_KEY, []);
	    foreach($flashMessages as $message) {
		    SmartyFacesMessages::$messages[null][]=$message;
	    }
	    SFSession::delete(SmartyFacesMessages::FLASH_MESSAGE_SESSION_KEY);
    	
    }    
    if($for==null){
        $cont=new TagRenderer("div",true);
	    $cont->setAttribute("class", '');
	    $cont->appendAttribute("class", $class);
	    $cont->passAttributes($attributes_values, array("style"));
	    
	   
	    
        foreach(SmartyFacesMessages::$messages as $id=>$messageList) {
        	if($id!=null and $global) continue;
            foreach($messageList as $message){
                $type=$message['type'];
                $m=$message['message'];
                $msg_class="";
                if($styled) $msg_class="alert alert-dismissable alert-".SmartyFacesMessages::convertToBootstrapStyles($type);
                if($customClasses!=null && isset($customClasses[$type])) {
                	$msg_class=$customClasses[$type];
                }
                $div=new TagRenderer("div",true);
                $div->setAttribute("onclick", "$(this).fadeOut();");
                $div->setAttribute("class", ' '.$class."-msg ".$msg_class." ".$msg_class.'-global');
                $div->setValue($m);
                $cont->addHtml($div->render());
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
                if($styled) $msg_class=" is-invalid";
                if($customClasses!=null && isset($customClasses[$type])) {
                	$msg_class=$customClasses[$type];
                }
                $span=new TagRenderer("span",true);
                $span->setAttribute("class", $class." ".$msg_class);
                $span->passAttributes($attributes_values, array("style"));
                $span->setValue($m);
                
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

                $errors[]=$msg_cont->render();
            }
            
            return implode("", $errors);
        }
    }
}

?>