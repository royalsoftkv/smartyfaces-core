<?php

class FileUtils {

    public static function getFilesFromDir($dir, $recursive = true) {

        $files = array();
        if(!is_dir($dir)) {
        	return $files;
        }
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".."  && $file!=".svn" && $file!=".htaccess") {
                    if (is_dir($dir . '/' . $file)) {
                        $dir2 = $dir . '/' . $file;
                        if($recursive) {
	                        $files[] = self::getFilesFromDir($dir2);
                        }
                    } else {
                        $files[] = $dir . '/' . $file;
                    }
                }
            }
            closedir($handle);
        }

        return self::array_flat($files);
    }

    private static function array_flat($array) {
        $tmp=array();
        foreach ($array as $a) {    
            if (is_array($a)) {
                $tmp = array_merge($tmp, self::array_flat($a));
            } else {
                $tmp[] = $a;
            }
        }

        return $tmp;
    }
    
    public static function parseLngFile($file) {
    	$lines=file($file, FILE_IGNORE_NEW_LINES);
    	$data=array();
    	if(is_array($lines)) {
	        foreach($lines as $line) {
	            if(trim($line)=="") continue;
	            $p=strpos($line, "=");
	            $key=substr($line, 0, $p);
	            $val=substr($line,$p+1);
	            $data[$key]=$val;
	        }
	    }
    	return $data;
    }

    static function delete_directory($dirname) {
    	$dir_handle=false;
    	if (is_dir($dirname)) {
    		$dir_handle = opendir($dirname);
    	}
    	if (!$dir_handle) {
    		return false;
    	}
    	while($file = readdir($dir_handle)) {
    		if ($file != "." && $file != "..") {
    			if (!is_dir($dirname."/".$file))
    				unlink($dirname."/".$file);
    			else
    				self::delete_directory($dirname.'/'.$file);
    		}
    	}
    	closedir($dir_handle);
    	rmdir($dirname);
    	return true;
    }
    
    static function folderIsOnPath($path,$folder) {
    	return strpos(realpath($folder), realpath($path))===0;
    }

}

?>
