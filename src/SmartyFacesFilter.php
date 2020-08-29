<?php

class SmartyFacesFilter {

    public static function filter($tpl_source, $smarty) {
    	
    	// skip if there is no sf_view tags in it
    	if(strstr($tpl_source, "{{sf_view")===false && strstr($tpl_source, "{sf_view")===false) {
    		return $tpl_source;
    	}

        $single_delim = !(strstr($tpl_source, "{{sf_view")!==false);

        if($single_delim) {
            $s = $tpl_source;
            $pattern="/\{sf_view(.*)\}/";
            $replacement="<sf_view$1>";
            $s = preg_replace($pattern, $replacement, $s);
            $s = str_replace("{/sf_view}","</sf_view>",$s);
        } else {
            $s = $tpl_source;
            $s = str_replace("{{", "<", $s);
            $s = str_replace("}}", ">", $s);
        }

        if(property_exists($smarty, "source")) {
	        $current_file = $smarty->source->filepath;
	        $current_file = realpath($current_file);
        } else {
            $current_file = $smarty->compiler_object->template->resource_name;
        }
        $template_dir=SmartyFaces::resolvePath(SmartyFaces::$config['view_dir']);
        $file_name=str_replace($template_dir.DIRECTORY_SEPARATOR, "", $current_file);

        require_once(dirname(__FILE__)."/FileUtils.php");
        $subview_dir=SmartyFaces::resolvePath(SmartyFaces::$config['tmp_dir'])."/subview";
        if(!file_exists($subview_dir)) @mkdir($subview_dir,0777,true);
        $file = $subview_dir.DIRECTORY_SEPARATOR.$file_name;
        $file_dir = dirname($file);
        
       	$files=FileUtils::getFilesFromDir($file_dir);
       	foreach($files as $file){
       		if(substr($file, -5)==".view"){
       			@unlink($file);
       		}
       	}
       	// clear sessions
       	$state=SmartyFacesContext::getState();
       	unset($state[$file_name]);
       	SmartyFacesContext::setState($state);
        
        $create_file = str_replace($template_dir, "", $current_file);
        $create_file = $subview_dir.$create_file.".source";
        $dir=dirname($create_file);
        if(!file_exists($dir)) {
	        @mkdir($dir,0777,true);
        }
        @file_put_contents($create_file, $s);
        
        return $tpl_source;
    }

}
