<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SmartyFacesValidator
 *
 * @author marko
 */
class SmartyFacesValidator {
    
    
    public static function validateRequired($formData,$id){
    	if(!defined("VALUE_IS_REQUIRED_MESSAGE")) {
	    	define("VALUE_IS_REQUIRED_MESSAGE",SmartyFaces::translate('value_is_required'));
    	}
        if(!isset ($formData[$id])) {
            SmartyFacesMessages::addError($id, VALUE_IS_REQUIRED_MESSAGE);
            return;
        }
        if(is_null($formData[$id])){
            SmartyFacesMessages::addError($id, VALUE_IS_REQUIRED_MESSAGE);
            return;
        }
        if(is_string($formData[$id]) and $formData[$id]=="null"){
            SmartyFacesMessages::addError($id, VALUE_IS_REQUIRED_MESSAGE);
            return;
        }
        if(is_string($formData[$id]) and strlen($formData[$id])==0){
            SmartyFacesMessages::addError($id, VALUE_IS_REQUIRED_MESSAGE);
            return;
        }
    }
    
    
    public static function passed(){
        if(count(SmartyFacesMessages::$messages)==0){
            return true;
        } else {
            return false;
        }
    }
    
}

?>
