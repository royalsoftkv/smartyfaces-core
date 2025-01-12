<?php
function smarty_block_sf_mail($params, $content, $template, &$repeat){
	
	$tag="sf_mail";
	
	$attributes_list=array();
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['to']=array(
		'required'=>true,'desc'=>'Email address(es) to which will be sent email'		
	);
	$attributes['subject']=array(
		'required'=>true,'desc'=>'Subject of email message'		
	);
	$attributes['from']=array(
		'required'=>true,'desc'=>'Email address of sender'		
	);
	$attributes['type']=array(
		'required'=>false,'default'=>'text','desc'=>'Type of email message. Can be: text, email or alternative'		
	);
	$attributes['debug']=array(
		'required'=>false,'default'=>false,'desc'=>'Used to only render email message, not to sent it'		
	);
	$attributes['cc']=array(
		'required'=>false,'default'=>null,'desc'=>'CC header field of message'
	);
	$attributes['bcc']=array(
		'required'=>false,'default'=>null,'desc'=>'BCC header field of message'
	);
	if($params==null and $template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));

	$this_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];
	
	if (is_null($content)) {
        return;
    }
	
	
    if(!$repeat){
        if (isset($content)) {
        	$headers_arr=array();
        	$headers_arr[]="From: ".$from;
        	$headers_arr[]="Return-Path: <".$from.">";
        	$headers_arr[]="MIME-Version: 1.0";
	        if($type=="text") $type="text/plain";
	        if($type=="html") $type="text/html";
	        if($type=="alternative") $type="multipart/alternative";
        	
        	$charset="charset=utf-8";
        	if($type=="text/plain" or $type=="text/html"){
	        	$headers_arr[]="Content-Type: $type; $charset";
        	} else {
        		$boundary=uniqid("b_");
        		$headers_arr[]="Content-Type: $type;\n boundary=\"$boundary\"";
        	}
        	if($bcc!=null) {
        		$headers_arr[]="Bcc:$bcc";
        	}
        	if($cc!=null) {
        		$headers_arr[]="Cc:$cc";
        	}
        	$headers=implode("\n", $headers_arr);
        	$text=SmartyFacesComponent::getFacet($template, "text");
        	$html=SmartyFacesComponent::getFacet($template, "html");
        	if($type=="text/plain" or $type=="text/html"){
        		if($type=="text/plain") {
        			if($text!=null) {
        				$message=$text;
        			} else {
        				$message=$content;
        			}
        		} else {
        			if($html!=null) {
        				$message=$html;
        			} else {
        				$message=$content;
        			}
        		}
        	} else {
        		$text_message=$text;
        		$text_html=$html;
        		$message="\n\n\n";
        		$message.="--$boundary";
        		$message.="\n";
        		$message.="Content-Type: text/plain; charset=\"UTF-8\"";
        		$message.="\n";
        		$message.="Content-Transfer-Encoding: base64";
				$message.="\n";
        		$message.="MIME-Version: 1.0";
        		$message.="\n\n";
        		$message.=chunk_split(base64_encode($text_message));
        		$message.="\n\n\n";
        		$message.="--$boundary";
        		$message.="\n";
        		$message.="Content-Type: text/html; charset=\"UTF-8\"";
        		$message.="\n";
        		$message.="Content-Transfer-Encoding: base64";
        		$message.="\n";
        		$message.="MIME-Version: 1.0";
        		$message.="\n\n";
        		$message.=chunk_split(base64_encode($text_html));
        		$message.="\n\n\n\n";
        		$message.="--$boundary--";
        		$message.="\n";
        	}
        	
        	$subject="=?UTF-8?B?".base64_encode($subject)."?=";

			if($debug) {
				return $message;
			}

        	if(SmartyFaces::$config['mail_enabled']) {
				$ret=@mail($to,$subject,$message,$headers);
				return $ret;
        	} else {
        		return false;
        	}
        }
    }
}

