<?php

function smarty_function_sf_image($params, $template)
{
    $tag="sf_image";
    
    $attributes_list=array("value","class","style","rendered");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['value']['required']=false;
    $attributes['value']['default']=null;
    $attributes['value']['desc']='Path to the image to be displayed';
    $attributes['data']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Base64 encoded data for image to display'		
    );
    $attributes['type']=array(
    	'required'=>false,
    	'default'=>'gif',
    	'desc'=>'Type of image to output for Base64 encoded data'		
    );
    $attributes['width']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Width of image to display. If not set default image width will be used'		
    );
    $attributes['height']=array(
    	'required'=>false,
    	'default'=>null,
    	'desc'=>'Height of image to display. If not set default image height will be used'		
    );
    $attributes['responsive']=array(
    	'required'=>false,
    	'default'=>false,
    	'type'=>'bool', 
    	'desc'=>'Set image to be nicely scaled to the parent element.'
    );
    $attributes['timestamp']=array(
    	'required'=>false,
    	'default'=>false,
    	'type'=>'bool', 
    	'desc'=>'Add timestamp to image url to prevent caching.'
    );
    if($params==null and $template==null) return $attributes;
    extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
    
    if(!$rendered) return;
    
    if(!$responsive) {
	    if($width==null and $height==null) {
	    	if($value!=null and file_exists($value)) {
	    		$info = getimagesize($value);
	    		$width=$info[0];
	    		$height=$info[1];
	    	}
	    } else if($width==null or $height==null) {
		    if($value!=null and file_exists($value)) {
		    	$info = getimagesize($value);
		    	$type=$info[2];
		    	switch ($type) {
		    		case 1:		// GIF
		    			$img = imagecreatefromgif($value);
		    			break;
		    		case 2:		//JPG
		    			$img = imagecreatefromjpeg($value);
		    			break;
		    		case 3:		//PNG
		    			$img = imagecreatefrompng($value);
		    			break;
		    	}
		    	
		    	if($width==null) {
					$width=$height*($info[0]/$info[1]); 	    		
		    	} elseif($height==null) {
		    		$height=$width/($info[0]/$info[1]);
		    	}
		    }
	    }
    }
    
    
    $serverUrl=SmartyFaces::getServerUrl();
    $index_file=SmartyFaces::$config['index_file'];
    if($data==null) {
	    $src=$serverUrl.'/'.$index_file.'?image&path='.urlencode($value);
	    if($timestamp) {
	    	$src.="&".time();
	    }
    } else {
    	$src='data:image/'.$type.';base64,'.$data;
    }
    
    if($responsive && SmartyFaces::$skin=="bootstrap") $class.=" img-responsive";
    
    $image=new TagRenderer("img");
    $image->setAttribute("src", $src);
    if(SmartyFaces::$skin=="bootstrap" && $responsive) {
    	$style="";
    	if($width) $style.="width:{$width}px;";
    	if($height) $style.="height:{$height}px;";
    	$image->setAttribute("style", $style);
    } else {
	    $image->setAttribute("width", $width);
	    $image->setAttribute("height", $height);
    }
    $image->setAttributeIfExists("class", $class);
    $image->setAttributeIfExists("style", $style);
    
    return $image->render();
}

?>