<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SmartyFacesMessages
 *
 */
class SmartyFacesMessages {
    
    public static $messages=array();
    const FLASH_MESSAGE_SESSION_KEY = "FLASH_MESSAGE";
    
    const INFO = "info";
    const WARNING = "warn";
    const ERROR = "error";
    const SUCCESS = "success";
    
    public static function addError($id,$m) {
        $message["type"]=self::ERROR;
        $message["message"]=$m;
        self::$messages[$id][]=$message;
    }
    
    public static function addMessage($type,$id,$m) {
        $message["type"]=$type;
        $message["message"]=$m;
        self::$messages[$id][]=$message;
    }
    
    public static function errorMessageExists($id){
        if(!isset(self::$messages[$id])) return false;
        $messageList = self::$messages[$id];
        foreach($messageList as $message){
            if($message["type"]==self::ERROR){
                return true;
            }
        }
        return false;
    }
    
    public static function clear(){
        self::$messages=array();
    }
    
    public static function addGlobalMessage($type,$m){
        $message["type"]=$type;
        $message["message"]=$m;
        self::$messages[null][]=$message;
    }
    
    public static function convertToBootstrapStyles($type) {
    	switch ($type) {
    		case self::INFO: return "info";
    		case self::WARNING: return "warning";
    		case self::ERROR: return "danger";
    		case self::SUCCESS: return "success";
    	}
    }
    
    
    static function addGlobalInfo($msg, $traslated=false) {
    	if(!$traslated) $msg=SmartyFaces::translate($msg);
    	self::addGlobalMessage(self::INFO, $msg);
    }
    static function addGlobalWarning($msg, $traslated=false) {
    	if(!$traslated) $msg=SmartyFaces::translate($msg);
    	self::addGlobalMessage(self::WARNING, $msg);
    }
    static function addGlobalError($msg, $traslated=false) {
    	if(!$traslated) $msg=SmartyFaces::translate($msg);
    	self::addGlobalMessage(self::ERROR, $msg);
    }
    static function addGlobalSuccess($msg, $traslated=false) {
    	if(!$traslated) $msg=SmartyFaces::translate($msg);
    	self::addGlobalMessage(self::SUCCESS, $msg);
    }
    
    static function addFlashMessage($type, $msg) {
    	//TODO:SESSION-WRITE
    	$_SESSION[self::FLASH_MESSAGE_SESSION_KEY][]=array('type'=>$type,'message'=>$msg);
    }
    
}

?>
