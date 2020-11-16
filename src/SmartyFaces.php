<?php
/*
 * SmartyFaces
* NOTE: Modified library file ar marked with ##SmartyFaces-modified##
*/

class SmartyFaces {

	public static $signature="0.4.1 12.03.2019";
	public static $versions="Smarty 3.1.18 - jQuery 1.11.1 - jQuery UI 1.10.4 - PHP ActiveRecord 1.0 - Bootstrap 3.3.4";

	const DEFAULT_VIEW_NAME="home";

	public static $SF_ROOT;
	private static $_initilaized;

	/**
	 * @var Smarty
	 */
	public static $smarty;
	public static $template;
	private static $gcms;
	public static $ajax=false;
	public static $lng;

	public static $SF_ACTION;

	public static $scripts = "";

	public static $logging;

	public static $validateFailed=false;
	public static $GLOBALS = array();
	public static $config=array(
			'tmp_dir'=>'tmp',
			'session_id'=>null,
			'server_url'=>'/smartyfaces',
			'lng_dir'=>'lng',
			'lng_def'=>'en',
			'lng_var'=>'l',
			'lng_session_var'=>'sf_lng',
			'load_classes'=>true,
			'resources_exclude'=>array(),
			'index_file'=>'index.php',
			'orm_path'=>'lib/php-activerecord/ActiveRecord.php',
			'force_compile'=>false,
			'view_dir'=>'view',
			'ajax_url_param'=>'?ajax',
			'resource_url_param'=>'?resource',
			'view_var_name'=>'page',
			'default_view'=>self::DEFAULT_VIEW_NAME,
			'auto_load_smarty'=>true,
			'smarty_path'=>'lib/smarty/Smarty.class.php',
			'states_limit'=>0,
			'load_css'=>true,
			'progressive_loading'=>false,
			'remove_unused_params'=>true,
			'skin'=>'default',
			'compress_state'=>false,
			'image_dir'=>array('images'),
			'mail_enabled'=>true,
			'resources_url'=>'auto',
			'eval_with_file'=>true,
			'secure_actions'=>[]
	);

	public static $skins = array("default","none","bootstrap");
	public static $skin;
	public static $afterSessionStart;
	public static $ajaxkey;

	const CALLBACK_LANGUAGE_LOADING_FUNCTION = 'CALLBACK_LANGUAGE_LOADING_FUNCTION';
	const CALLBACK_LANGUAGE_NOT_FOUND_FUNCTION = 'CALLBACK_LANGUAGE_NOT_FOUND_FUNCTION';
	const CALLBACK_LANGUAGE_PROCESS_TRANSLATION_FUNCTION = 'CALLBACK_LANGUAGE_PROCESS_TRANSLATION_FUNCTION';
	public static $callbackFunctions = [];
	public static $globalAssign = [];

	public static function configure($config=null) {
		self::$config['root_path']=$_SERVER['DOCUMENT_ROOT'];
		if($config==null) return;
		self::$config=array_merge(self::$config,$config);
		self::$SF_ROOT=self::resolvePath(self::$config['root_path']);
		if(!in_array(self::$config['skin'], self::$skins)) self::$config['skin']="default";
		self::$skin=self::$config['skin'];
	}

	public static function display($view=null) {
		if($view==null) {
			$view=self::$config['default_view'];
			$view_var_name=self::$config['view_var_name'];
			if(isset($_GET[$view_var_name])) $view=$_GET[$view_var_name];
		}
		if(substr($view, -4,4)==".tpl") $view=substr($view,0, -4);
		self::$smarty->display("$view.tpl");
	}

	public static function loadAndConfigureSmarty() {
		if(self::$config['auto_load_smarty']) {
			$smarty=self::loadSmarty();
			self::configureSmarty($smarty);
		}
	}

	public static function getCurrentView() {
		 
		$obj=self::$template;
		while(!property_exists($obj, "source")) {
			$obj=$obj->parent;
		}
		$current_file = $obj->source->filepath;
		$current_file = realpath($current_file);
		$template_dir=SmartyFaces::resolvePath(SmartyFaces::$config['view_dir']);
		$file_name=$current_file;
		$file_name=str_replace($template_dir.DIRECTORY_SEPARATOR, "", $current_file);
		return $file_name;
	}

	public static function getResponse($view, $sf_view_id) {
		$sf_view_file=self::resolvepath(self::$config['tmp_dir'])."/subview/".$view."-".$sf_view_id.".view";
		SmartyFacesLogger::log("Get response for view $view, view_id=$sf_view_id: file=$sf_view_file");
		$recompile=false;
		if(!file_exists($sf_view_file) or filesize($sf_view_file)==0) $recompile=true;
		if($recompile) {
			$source_file=SmartyFaces::resolvePath(SmartyFaces::$config['tmp_dir'])."/subview/".$view.".source";
			SmartyFacesLogger::log("File ned to be recompiled");
			if(file_exists($source_file)) {
				$source=file_get_contents($source_file);
			} else {
				if(self::$ajax) {
					SmartyFaces::reload();
				} else {
					return "Unable to load source file: $source_file. Check template attribute in sf_view!";
				}
			}

			$pos=strpos($source, '<sf_view id="'.$sf_view_id);
			$source = substr($source, $pos);
			$pos2=strpos($source, ">");
			$source = substr($source, $pos2+1);
			$pos3=strpos($source,"</sf_view>");
			$source=substr($source,0,$pos3);
			file_put_contents($sf_view_file, $source);
		}
		$s= self::$smarty->fetch($sf_view_file);
		$s=str_replace("\t","",$s);
		//clean wrong encoded characters
		$s=@iconv('UTF-8', 'UTF-8//IGNORE', $s);
		SmartyFacesLogger::log("Fetched response");
		return $s;
	}

	public static function loadSmarty() {
		$smarty=new Smarty();
		$smarty->compile_dir=SmartyFaces::resolvePath(SmartyFaces::$config['tmp_dir'])."/compile";
		return $smarty;
	}


	public static function configureSmarty(Smarty $smarty){
		$smarty->addTemplateDir(self::resolvePath(self::$config['view_dir']));
		$smarty->addPluginsDir(dirname(dirname(__FILE__))."/smarty_plugins");
		require_once dirname(__FILE__)."/SmartyFacesFilter.php";
		$smarty->registerFilter("pre",array("SmartyFacesFilter","filter"));
		$smarty->force_compile=self::$config['force_compile'];
		$smarty->use_sub_dirs = true;
		$smarty->setCompileDir(SmartyFaces::resolvePath(self::$config['tmp_dir'])."/compile");
		if(property_exists($smarty, 'inheritance_merge_compiled_includes')) {
			$smarty->inheritance_merge_compiled_includes = false;
		}
		self::$smarty=$smarty;
		self::$smarty->assign(self::$globalAssign);
	}

	public static function init(){
		if(self::$_initilaized) return;
		$tmp_dir=SmartyFaces::resolvePath(SmartyFaces::$config['tmp_dir']);
		if(!file_exists($tmp_dir)) @mkdir($tmp_dir);
		$compile_dir=$tmp_dir."/compile";
		if(!file_exists($compile_dir)) @mkdir($compile_dir);
		$subview_dir=$tmp_dir."/subview";
		if(!file_exists($subview_dir)) @mkdir($subview_dir);
		if(self::$config['eval_with_file']) {
			$eval_dir=$tmp_dir."/eval";
			if(!file_exists($eval_dir)) @mkdir($eval_dir);
		}

		SmartyFacesComponent::$current_view=self::getCurrentView();
		// load classes
		self::loadClasses();
		// load js
		if(!self::$config['progressive_loading']) {
			self::loadJs();
			// load css
			self::loadCss();
		}
		self::$_initilaized=true;
	}

	public static function initAjax(){
		self::loadClasses();
	}

	public static function loadClasses(){
		$jq = new jQuery();
		if(!self::$config['load_classes']) return;
		SmartyFacesLogger::log("Loading classes");
		require_once dirname(__FILE__)."/FileUtils.php";
		$files = FileUtils::getFilesFromDir(self::$SF_ROOT."/src");
		foreach ($files as $file) {
			require_once $file;
		}
	}

	public static function loadJs(){
		$serverUrl=self::getServerUrl();
		if(empty(self::$config['resources_overrride_js'])) {
			$resources=array(
					["jquery/jquery.min.js",1],
					["jquery-php/jquery.php.js",1],
					["smartyfaces/js/smartyfaces.js",1]);
			if(self::$skin=="default") {
				$resources[]=["jquery-ui/jquery-ui-custom.min.js",1];
			} else if (self::$skin=="bootstrap") {
				$resources[]=["jquery-ui/jquery-ui-notooltip.custom.js",1];
				$resources[]=["bootstrap/js/bootstrap.min.js",1];
			}
		} else {
			$resources = self::$config['resources_overrride_js'];
		}
		self::loadResources("js", $resources);
		$index_file=self::$config['index_file'];
		echo '  <script type="text/javascript">
                    SF.ajax.url="'.$serverUrl.'/'.$index_file.self::$config['ajax_url_param'].'";
                    SF.ajax.key=\''.self::$ajaxkey.'\';
                </script>';

		if(self::$config['progressive_loading'] && !self::$ajax) {
			// load first all linked scripts
			echo self::loadScripts();
		}
	}

	public static function loadScripts() {
		return self::$scripts;
	}

	private static function loadResources($type, $resources) {
		$serverUrl=self::getServerUrl();
		foreach($resources as $resource) {
			if(is_array($resource)) {
				$resource_name = $resource[0];
				$resource_ext = $resource[1]==1;
			} else {
				$resource_name = $resource;
				$resource_ext = false;
			}
			if(in_array($resource_name, self::$config['resources_exclude'])) continue;
			if($resource_ext) {
				$resource_url = self::getResourcesUrl() . "/". $resource_name;
			} else {
				$resource_name=urlencode($resource_name);
				$index_file=self::$config['index_file'];
				$resource_url = $serverUrl.'/'.$index_file.self::$config['resource_url_param'].'&name='.$resource.'&type='.$type;
			}
			if($type=="js") {
				echo '<script type="text/javascript" src="'.$resource_url.'"></script>';
			} else if ($type=="css") {
				echo '<link type="text/css" rel="stylesheet" href="'.$resource_url.'">';
			}
		}
	}

	public static function getResourcesUrl() {
		$resource_url = self::$config['resources_url'];
		if($resource_url=="auto") {
			$serverUrl=self::getServerUrl();
			$resource_dir = __DIR__ ."/resources";
			$resource_url = $serverUrl . str_replace(realpath(self::$config['root_path']), "", $resource_dir);
			return $resource_url;
		} else {
			return $resource_url;
		}
	}

	public static function loadCss(){
		$serverUrl=self::getServerUrl();
		$resources = array();
		if(self::$skin=="bootstrap") {
			//must done this way because of fonts linking
			$resources[]=array("bootstrap/css/bootstrap.min.css",1);
			$resources[]=array("bootstrap/css/bootstrap-theme.min.css",1);
		}
		$resources[]=["smartyfaces/css/smartyfaces.css",1];
		if(self::$config['load_css']) {
			self::loadResources("css", $resources);
		}
		if(isset(self::$config['jquery_ui'])) {
			echo '<link type="text/css" rel="stylesheet" href="'.self::$config['jquery_ui'].'">';
		}
	}

	public static function getServerUrl(){
		if(self::isCMS()) {
			$config = self::$gcms->GetConfig();
			return $config['root_url'];
		} else {
			// FIXED because of Alias Directive
			return self::$config['server_url'];
		}
	}

	public static function processLinkAction(){
		$sf_action=$_POST['sf_action'];

		if(isset(SmartyFaces::$config['secure_actions']) &&
			(in_array("link", SmartyFaces::$config['secure_actions']) || in_array("all", SmartyFaces::$config['secure_actions']) )) {
			SmartyFacesSecurity::checkAction($sf_action);
		}


		//$sf_form_data=$_POST['sf_form_data'];
		//parse_str($sf_form_data, $formData);
		//SmartyFacesContext::$formData = $formData;
		SmartyFaces::invokeDirectAction($sf_action);
		jQuery::getResponse();
		exit();
	}

	public static function processAjax(){
		 
		self::$ajax=true;
		self::initAjax();

		if(isset($_POST['sf_event'])) {
			self::processEvent();
			return;
		}
		 
		// first check if is link action
		if(isset($_POST['sf_link_action']) and $_POST['sf_link_action']==true){
			self::processLinkAction();
			return;
		}
		 
		SmartyFacesTrigger::trigger(SmartyFacesTrigger::CUSTOM_REQUEST_PROCESS);

		$formData=array();
		$upload=false;
		// restore view
		if(isset($_GET['file_upload'])){
			$sf_source=$_GET['sf_source'];
			$sf_view=$_POST['sf_view'];
			$sf_view_id=$_POST['sf_view_id'];
			if(isset($_POST['sf_state_id'])) $sf_state_id=$_POST['sf_state_id'];
			$storestate=$_POST['sf_state_store'];
			$sf_form_data=$_POST['sf_upload_data'];
			parse_str($sf_form_data, $formData);
			$upload=true;
		} else {
			$sf_source=$_POST['sf_source'];
			$sf_form_data=$_POST['sf_form_data'];
			parse_str($sf_form_data, $formData);
			if(isset($formData['sf_view'])) $sf_view=$formData['sf_view'];
			if(isset($formData['sf_view_id'])) $sf_view_id=$formData['sf_view_id'];
			if(isset($formData['sf_state_id'])) $sf_state_id=$formData['sf_state_id'];
			if(isset($formData['sf_state_store'])) $storestate=$formData['sf_state_store'];
		}
		 
		if(isset($formData['sf_logging'])) {
			SmartyFaces::$logging=true;
		}
		SmartyFacesLogger::log("AJAX:Start processing");
		SmartyFacesLogger::log("AJAX:Data: sf_source=$sf_source, form_data=".print_r($formData,true));

		SmartyFaces::checkAjax($formData);
		 
		if (!isset($_POST['sf_state_id']) and !isset($formData['sf_state_id'])) {
			self::processStatelessAjax();
			return;
		}

		// apply request values
		SmartyFacesComponent::$current_view=$sf_view;
		SmartyFacesComponent::$current_view_id=$sf_view_id;
		SmartyFacesContext::$storestate=$storestate;
		if(SmartyFacesContext::$storestate=="server") {
			if(!empty($sf_state_id)) SmartyFacesComponent::$current_state_id=$sf_state_id;
		}
		SmartyFacesContext::$formData = $formData;
		if(isset($formData['sf_template'])) {
			SmartyFacesComponent::$current_template_id=$formData['sf_template'];
		}
		SmartyFacesContext::restoreState();

		if($sf_source and isset(SmartyFacesContext::$components["event"]["component.$sf_source"])) {
			$sf_action_component=SmartyFacesContext::$components["event"]["component.$sf_source"];
			SmartyFacesComponent::$action_component=$sf_action_component;
			$sf_action=$sf_action_component['params']['action'];
			$immediate=(isset($sf_action_component['params']['immediate']) and $sf_action_component['params']['immediate']);
		} else {
			//remote command
			$sf_action=$_POST['sf_action'];
			$immediate=true;
		}

		if(isset(SmartyFaces::$config['secure_actions']) &&
			(in_array("action", SmartyFaces::$config['secure_actions']) || in_array("all", SmartyFaces::$config['secure_actions']) )) {
			SmartyFacesSecurity::checkAction($sf_action);
		}

		$convertersList=  SmartyFacesContext::$converters;
		if($convertersList!=null){
			foreach($convertersList as $id=>$converters){
				foreach($converters as $converter){
					self::processConverter($converter,$formData,$id);
				}
			}
		}

		// process validations
		if(!$immediate){
			SmartyFacesMessages::clear();
			$validatorsList=  SmartyFacesContext::$validators;
			if($validatorsList!=null){
				foreach($validatorsList as $id=>$validators){
					foreach($validators as $validator){
						self::processValidator($validator,$formData,$id);
					}
				}
			}
		}



		self::$validateFailed = !SmartyFacesValidator::passed();
		if($immediate){
			// update model values
			self::updateModelValues($formData);
			// invoke application
			SmartyFaces::invokeAction($sf_action);
		} else {
			// check validations
				
			if(self::$validateFailed){
				// rerender same view
				// update model values
				// self::updateModelValues($formData);
				if($upload) {
					self::processUpload();
				}
			} else {

				// update model values
				self::updateModelValues($formData);

				// invoke application
				SmartyFaces::invokeAction($sf_action);
				 
			}
			 
		}

		// render response
		SmartyFacesContext::$bindings=array();
		SmartyFacesContext::$validators=array();
		SmartyFacesContext::$converters=array();

		$update_id="";
		if(isset($sf_action_component['params']['update'])) $update_id=$sf_action_component['params']['update'];
		if($update_id=="") {
			// update whole view
			if(isset($formData['sf_template'])) {
				$sf_view=$formData['sf_template'];
			}
			$output=self::getResponse($sf_view,$sf_view_id);
			//$output.=$s;
			jQuery("div#$sf_view_id")->html($output);
		} else {
			$region_content=SmartyFacesContext::$regions[$update_id];
			$output=self::$smarty->fetch("string:".$region_content,null, $update_id);
			jQuery("div#$sf_view_id #$update_id")->html($output);
		}
		// handle oncomplete
		if(SmartyFacesValidator::passed() and isset($sf_action_component['params']['oncomplete'])) {
			jQuery::evalScript($sf_action_component['params']['oncomplete']);
		}
		if(self::$validateFailed){
			jQuery::evalScript("SF.scrollToElement('.has-error:first');");
		}
		if(SmartyFaces::$logging) {
			jQuery("#sf-log")->html(SmartyFacesLogger::fillLog());
		}
		jQuery::getResponse();
		exit();

	}

	static function processEvent() {
		$sf_source=$_POST['sf_source'];//required
		$sf_event=$_POST['sf_event'];//required
		$sf_action_data=null;
		if(isset($_POST['sf_action_data'])) {
			$sf_action_data=$_POST['sf_action_data'];
		}
		if(isset($_POST['sf_form_data'])) {
			$sf_form_data=$_POST['sf_form_data'];
			parse_str($sf_form_data, $formData);
			$sf_view=$formData['sf_view'];
			$sf_view_id=$formData['sf_view_id'];
			$sf_state_id=$formData['sf_state_id'];
			$sf_state_store=$formData['sf_state_store'];
				
			SmartyFacesComponent::$current_view=$sf_view;
			SmartyFacesComponent::$current_view_id=$sf_view_id;
			SmartyFacesContext::$storestate=$sf_state_store;
			if(SmartyFacesContext::$storestate=="server") {
				if(!empty($sf_state_id)) SmartyFacesComponent::$current_state_id=$sf_state_id;
			}
			SmartyFacesContext::$formData = $formData;
			SmartyFacesContext::$actionData = $sf_action_data;
			if(isset($formData['sf_template'])) {
				SmartyFacesComponent::$current_template_id=$formData['sf_template'];
			}
			SmartyFacesContext::restoreState();

			if(isset(SmartyFacesContext::$components["event"]["component.$sf_source"])) {
				$sf_action_component=SmartyFacesContext::$components["event"]["component.$sf_source"];
				SmartyFacesComponent::$action_component=$sf_action_component;
				$events=$sf_action_component['params']['events'];
				$event=$events[$sf_event];
				$immediate=isset($event['immediate']) ? $event['immediate'] : false;
				$sf_action=$event['action'];

				if(isset(SmartyFaces::$config['secure_actions']) &&
					(in_array("event", SmartyFaces::$config['secure_actions']) || in_array("all", SmartyFaces::$config['secure_actions']) )) {
					SmartyFacesSecurity::checkAction($sf_action);
				}
				 
				$convertersList=  SmartyFacesContext::$converters;
				if($convertersList!=null){
					foreach($convertersList as $id=>$converters){
						foreach($converters as $converter){
							self::processConverter($converter,$formData,$id);
						}
					}
				}
				// process validations
				if(!$immediate){
					SmartyFacesMessages::clear();
					$validatorsList=  SmartyFacesContext::$validators;
					if($validatorsList!=null){
						foreach($validatorsList as $id=>$validators){
							foreach($validators as $validator){
								self::processValidator($validator,$formData,$id);
							}
						}
					}
				}
				self::$validateFailed = !SmartyFacesValidator::passed();
				if($immediate or !self::$validateFailed){
					self::updateModelValues($formData);
					SmartyFaces::invokeAction($sf_action);
				}
				 
				// render response
				SmartyFacesContext::$bindings=array();
				SmartyFacesContext::$validators=array();
				SmartyFacesContext::$converters=array();
				 
				$update_id="";
				if(isset($event['update'])) $update_id=$event['update'];
				if($update_id) {
					$region_content=SmartyFacesContext::$regions[$update_id];
					$output=self::$smarty->fetch("string:".$region_content,null, $update_id);
					jQuery("div#$sf_view_id #$update_id")->html($output);
				} else {
					// update whole view
					if(isset($formData['sf_template'])) {
						$sf_view=$formData['sf_template'];
					}
					$output=self::getResponse($sf_view,$sf_view_id);
					jQuery("div#$sf_view_id")->html($output);
				}
				// handle oncomplete
				//if(SmartyFacesValidator::passed() and $event['oncomplete']) {
				//	jQuery::evalScript($event['oncomplete']);
					//}
					if(self::$validateFailed){
						jQuery::evalScript("SF.scrollToElement('.sf-msg-error:first');");
					}
					if(SmartyFaces::$logging) {
						jQuery("#sf-log")->html(SmartyFacesLogger::fillLog());
					}
					jQuery::getResponse();
					exit();
			}
		}
		 
		 
		exit();
	}

	static function processStatelessAjax() {
		SmartyFacesLogger::log("Start processing stateless ajax");
		$sf_form_data=$_POST['sf_form_data'];
		parse_str($sf_form_data, $formData);
		SmartyFacesContext::$formData=&$formData;
		if(isset($formData['sf_view'])) $sf_view=$formData['sf_view'];
		if(isset($formData['sf_view_id'])) $sf_view_id=$formData['sf_view_id'];
		if(isset($formData['sf_state'])) $sf_state=json_decode(urldecode($formData['sf_state']), true);
		if(isset($sf_state['sf_vars'])) {
			$sf_vars=$sf_state['sf_vars'];
			SmartyFacesContext::$formVars=$sf_vars;
		}
		 
		 
		SmartyFacesLogger::log("Form vars=".print_r(SmartyFacesContext::$formVars, true));
		$sf_action=$_POST['sf_action'];

		if(isset(SmartyFaces::$config['secure_actions']) &&
			(in_array("stateless", SmartyFaces::$config['secure_actions']) || in_array("all", SmartyFaces::$config['secure_actions']) )) {
			SmartyFacesSecurity::checkAction($sf_action);
		}


		if(isset($formData['sf_view'])) {
			SmartyFacesComponent::$current_view=$sf_view;
			SmartyFacesComponent::$current_view_id=$sf_view_id;
			SmartyFacesComponent::$stateless=true;
		}
		 
		$immediate=false;
		if(isset($_POST['sf_action_data']['immediate'])) {
			$immediate=$_POST['sf_action_data']['immediate'];
			if($immediate==="true") $immediate=true;
			if($immediate==="false") $immediate=false;
		}
		 
		if(isset($sf_state['sf_converters'])) {
			foreach($sf_state['sf_converters'] as $id=>$converters){
				foreach($converters as $converter){
					self::processConverter($converter,$formData,$id);
				}
			}
		}

		if(!$immediate){
			 
			if(isset($sf_state['sf_validators'])) {
				SmartyFacesMessages::clear();
				foreach($sf_state['sf_validators'] as $id=>$validators){
					foreach($validators as $validator){
						SmartyFacesLogger::log("Processing validator: $validator");
						self::processValidator($validator,$formData,$id);
					}
				}
			}
			 
		}
		if($immediate or (!$immediate and SmartyFacesValidator::passed())){
			SmartyFacesLogger::log("Executong action: " . $sf_action);

			if(self::$config['eval_with_file']) {
				$tmp_dir=SmartyFaces::resolvePath(SmartyFaces::$config['tmp_dir']."/eval");
				$tmpfile = tempnam($tmp_dir, "eval");
				$s='<?php '.$sf_action.'; ?>';
				file_put_contents($tmpfile, $s);
				chmod($tmpfile,0755);
				require $tmpfile;
			} else {
				eval("$sf_action;");
			}

		}
		if(isset($formData['sf_var'])) {
			SmartyFacesLogger::log("Assigning form vars: ". print_r(SmartyFacesContext::$formVars, true));
			self::$smarty->assign(SmartyFacesContext::$formVars);
		}
		if(isset($formData['sf_template'])) {
			$sf_view=$formData['sf_template'];
		}
		$output=self::getResponse($sf_view,$sf_view_id);
		jQuery("div#$sf_view_id")->html($output);
		if(SmartyFaces::$logging) {
			jQuery("#sf-log")->html(SmartyFacesLogger::fillLog());
		}
		jQuery::getResponse();
		exit();
	}

	private static function processValidator($validator,$formData, $id) {
		if(strlen($validator)>0  and substr($validator, 0,2)=="#[" and substr($validator, -1,1)=="]") {
			$validator=substr($validator, 2,-1);
			eval($validator.";");
		} else {
			$str=$validator."(\$formData,\$id);";
			eval($str);
		}
	}

	private static function processConverter($converter,&$formData, $id) {
		$converter::toObject($formData,$id);
	}

	private static function updateModelValues($formData) {
		$bindings=SmartyFacesContext::$bindings;
		if($bindings!=null){
			foreach($bindings as $id=>$binding){
				if(isset($formData[$id])) {
					if(isset(SmartyFacesContext::$components["event"]["component.$id"])) {
						$component=SmartyFacesContext::$components["event"]["component.$id"];
						if($component['tag']=="sf_picklist") {
							$source=$component['params']['source'];
							$selected_ids=$formData[$id];
							if(strlen($selected_ids)>0) {
								$selected=explode(",", $selected_ids);
								$value=array();
								foreach($selected as $id) {
									$value[$id]=$source[$id];
								}
								$formData[$id]=$value;
							} else {
								$formData[$id]=array();
							}
						} else if ($component['tag']=="sf_listbox") {
							$source=$component['params']['values'];
							if(count($formData[$id])>0) {
								foreach($formData[$id] as $id) {
									$value[$id]=$source[$id];
								}
								$formData[$id]=$value;
							}
						}
					}
					if($component['tag']=="sf_checkbox" and $component['params']['boolean']) {
						SmartyFaces::updateModelValue($binding,true);
					} else {
						SmartyFaces::updateModelValue($binding,$formData[$id]);
					}
				} else {
					//checkbox fix
					$component=SmartyFacesContext::$components["event"]["component.$id"];
					if($component['tag']=="sf_checkbox" and !$component['params']['disabled']) {
						if($component['params']['boolean']) {
							SmartyFaces::updateModelValue($binding,false);
						} else {
							SmartyFaces::updateModelValue($binding,$component['params']['unCheckedValue']);
						}
					} else if($component['tag']=="sf_radiogroup" and !$component['params']['disabled']) {
						SmartyFaces::updateModelValue($binding,null);
					} else if ($component['tag']=="sf_listbox") {
						SmartyFaces::updateModelValue($binding,array());
					}
				}
			}
		}
	}


	public static function evalExpression($el){
		foreach(SmartyFaces::$GLOBALS as $name=>$obj){
			$$name=$obj;
		}
		//#[EL]
		if(substr($el, 0,2)=="#[" and substr($el, -1,1)=="]") {
			// EL
			$ret=null;
			$el=substr($el, 2,-1);
			@eval("\$ret=$el;");
			return $ret;
		}
		//[bean.property]
		if(substr($el, 0,1)=="[" and substr($el, -1,1)=="]"){
			$el=substr($el, 1, -1);
			$arr=explode(".", $el);
			if(count($arr)==2){ //[bean.property]]
				$bean=$arr[0];
				$property=$arr[1];
				$obj=SmartyFacesComponent::getInstance($bean);
				if($obj==null){
					return null;
				}
				return $obj->$property;

			} else {
				//[bean.prop1.prop2]
				$bean=$arr[0];
				$prop1=$arr[1];
				$prop2=$arr[2];
				$obj=SmartyFacesComponent::getInstance($bean);
				if($obj==null){
					return null;
				}
				return $obj->$prop1->$prop2;
			}
		} else {
			return $el;
		}
	}

	public static function updateModelValue($el,$value){
		if($value==="null") $value=null;
		foreach(SmartyFaces::$GLOBALS as $name=>$obj){
			$$name=$obj;
		}
		if(substr($el, 0,2)=="#[" and substr($el, -1,1)=="]") {
			// EL
			$el=substr($el, 2,-1);
			self::$SF_ACTION = "updateModelValue: "."$el=\$value;";
			eval("$el=\$value;");
			return;
		}
		//[bean.property]
		if(substr($el, 0,1)=="[" and substr($el, -1,1)=="]"){
			$el=substr($el, 1, -1);
			$arr=explode(".", $el);
			if(count($arr)==2){
				$bean=$arr[0];
				$property=$arr[1];
				$obj=SmartyFacesComponent::getInstance($bean);
				if($obj==null){
					return;
				}
				$obj->$property=$value;

			} else {
				$bean=$arr[0];
				$prop1=$arr[1];
				$prop2=$arr[2];
				$obj=SmartyFacesComponent::getInstance($bean);
				if($obj==null){
					return;
				}
				$obj->$prop1->$prop2=$value;
			}
		}
	}

	public static function invokeAction($action){
		if(trim($action)=="") return;
		if(isset($_GET['file_upload'])) {
			SmartyFaces::processUpload();
			return;
		}
		foreach(SmartyFaces::$GLOBALS as $name=>$obj){
			$$name=$obj;
		}
		if(substr($action, 0,2)=="#[" and substr($action, -1,1)=="]") {
			// EL
			$action=substr($action, 2,-1);
			self::$SF_ACTION = "invokeAction: ".$action;

			if(self::$config['eval_with_file']) {
				$tmp_dir=SmartyFaces::resolvePath(SmartyFaces::$config['tmp_dir']."/eval");
				$tmpfile = tempnam($tmp_dir, "eval");
				$s='<?php '.$action.'; ?>';
				file_put_contents($tmpfile, $s);
				chmod($tmpfile,0755);
				require $tmpfile;
			} else {
				eval("$action;");
			}
			return;
		}
		$arr=explode(".", $action);
		$bean=$arr[0];
		$method=$arr[1];
		$obj=SmartyFacesComponent::getInstance($bean);
		if($obj==null){
			return;
		}
		eval("\$obj->$method;");
	}

	public static function invokeDirectAction($action){
		eval("$action;");
	}

	public static function createSmartyFacesVariable(){
		$sf = array();
		$sf['view']=SmartyFacesComponent::$current_view;
		return $sf;
	}

	public static function checkAcceptTypes($ext,$acceptTypes){
		if($acceptTypes==null) return true;
		$arr=explode(" ", $acceptTypes);
		foreach($arr as $type) {
			if(strtolower($type)==strtolower($ext)) return true;
		}
		return false;
	}

	public static function processUpload(){
		$sf_source=$_GET['sf_source'];
		if(self::$validateFailed) {
			echo '<script language="javascript" type="text/javascript">
	    		window.top.window.SF.upload.stop("'.$_GET['form_id'].'","'.$sf_source.'",'.json_encode(array()).');
	    	</script> ';
			exit();
		}
		$sf_files_id=substr($sf_source, 0, -2);
		$sf_action_component=SmartyFacesContext::$components["event"]["component.$sf_source"];
		$multiple = $sf_action_component['params']['multiple'];
		$filesToCheck=[];
		if($multiple) {
			foreach($_FILES[$sf_files_id] as $prop=>$values) {
				foreach($values as $index=>$val) {
					$filesToCheck[$index][$prop]=$val;
				}
			}
		} else {
			$filesToCheck[]=$_FILES[$sf_files_id];
		}

		$upload_info=null;
		$wrong_file_type = false;
		$upload_info['success']=true;
		$success_cnt = 0;
		foreach($filesToCheck as &$fileToCheck) {
			$error=$fileToCheck['error'];
			$size=$fileToCheck['size'];
			$name=$fileToCheck['name'];
			$error_msg = [];
		if($error>0){
			switch ($error){
				case 1:
				case 2:
						$error_msg[] = self::translate("file_size_exceed_allowed_limit");
					break;
				case 3:
						$error_msg[] = self::translate("file_is_not_completely_uploeded");
					break;
				case 4:
						$error_msg[] = self::translate("you_did_not_select_file_for_upload");
					break;
				default:
						$error_msg[] = self::translate("general_upload_error")." [$error]";
			}
		}
			$ext = pathinfo($name, PATHINFO_EXTENSION);
		$acceptTypes=$sf_action_component['params']['acceptTypes'];
		$canAccept=self::checkAcceptTypes($ext,$acceptTypes);
		if(!$canAccept){
				$error_msg[] = self::translate("wrong_file_type")." [$error]";
		}
		$maxSize=$sf_action_component['params']['maxSize'];
		if($maxSize!=null and $size>$maxSize) {
				$error_msg[] = self::translate("you_must_upload_file_of_maximum_size") .' ' .$maxSize.' B';
		}
			$ret = move_uploaded_file($fileToCheck['tmp_name'], $fileToCheck['tmp_name'].".upload");
		if(!$ret){
				$error_msg[] = self::translate("general_upload_error");
			}
			$fileToCheck['tmp_name']=$fileToCheck['tmp_name'].".upload";

			if(!empty($error_msg)) {
				$upload_info['success']=false;
			} else {
				$success_cnt++;
			}

			$fileToCheck['result']=empty($error_msg)?'success':'danger';
			$fileToCheck['error']=implode(", ", $error_msg);

		}
		$id=$sf_action_component['params']['id'];
		if(count($error_msg)>0 && !$multiple) {
			echo '<script language="javascript" type="text/javascript">
	    		window.top.window.SF.upload.abort("'.$_GET['form_id'].'","'.$id.'","'.$error_msg[0].'");
	    	</script> ';
			exit();
		}
		$files=json_encode($filesToCheck);
		echo '<script language="javascript" type="text/javascript">
    		window.top.window.SF.upload.stop("'.$_GET['form_id'].'","'.$sf_source.'",'.$files.');
    	</script> ';
		exit();
	}

	public static function getUplaodFiles($single=true){
		$files = $_POST['sf_files'];
		if($single) {
			return $files[0];
		} else {
			return $files;
		}
	}

	public static function initCMS($gcms, $params) {
		self::$gcms=$gcms;
		$config = self::$gcms->GetConfig();
		self::$SF_ROOT=$config['root_path'];
		 
		$dbtype=$config['dbms'];
		$dbhost=$config['db_hostname'];
		$dbuser=$config['db_username'];
		$dbpass=$config['db_password'];
		$dbname=$config['db_name'];
		//     	$connString="$dbtype://$dbuser:$dbpass@$dbhost/$dbname;charset=utf8";
		$connString="mysql://root:@localhost/isportdb;charset=utf8";
		 
		self::loadORM($connString);
		if(isset($params['view'])) {
			$view=$params['view'];
			$smarty = self::$gcms->GetSmarty();
			self::configureSmarty($smarty);
			$smarty->addTemplateDir(self::$SF_ROOT."/view");
			$smarty->display($view);
		}
	}

	public static function loadORM($connString) {
		// load ORM
		require_once(self::resolvePath(self::$config['orm_path']));
		$cfg = ActiveRecord\Config::instance();
		$cfg->set_model_directory(SmartyFaces::$SF_ROOT."/src/model");
		$cfg->set_connections(array('development' => $connString));
	}

	private static function isCMS() {
		return self::$gcms != null;
	}

	public static function startSession() {
		SmartyFacesLogger::log("Starting session");
		if(self::isCMS()) {
			$dirname = self::$SF_ROOT;
			$session_key = substr(md5($dirname), 0, 8);
			@session_name('CMSSESSID' . $session_key);
			@ini_set('url_rewriter.tags', '');
			@ini_set('session.use_trans_sid', 0);
			if(!@session_id()) session_start();
		} else {
			if(self::$config['session_id']!=null) {
				$sessionId=self::$config['session_id'];
				session_id($sessionId);
				@session_start();
			} else {
				if(session_id()==''){
					SFSession::instance()->start();
				}
			}
		}
		if(self::$afterSessionStart!=null) {
			call_user_func(self::$afterSessionStart);
		}
		SmartyFacesLogger::log("Started session id=".session_id());
	}
		
	public static function redirect($url) {
		jQuery::evalScript('location.href="'.$url.'";');
		jQuery::getResponse();
		exit();
	}
		
	public static function reload() {
		jQuery::evalScript('location.href=location.href;');
		jQuery::getResponse();
		exit();
	}

	static function fetchResource() {
		$name=$_GET['name'];
		$type=$_GET['type'];
		$resource_file=dirname(__FILE__)."/resources/$type/$name";
		if(!file_exists($resource_file)) {
			$resource_file=dirname(__FILE__)."/resources/$name";
		}
		if($type=="js") {
			header("Content-type: text/javascript");
		} else if ($type=="css") {
			header("Content-type: text/css");
		}
		readfile($resource_file);
		exit();
	}

	static function fetchImage() {
		$path=$_GET['path'];
		$folder=dirname($path);
		// chech if path is in allowed dirs
		$allowed=false;
		foreach(SmartyFaces::$config['image_dir'] as $image_dir) {
			$image_dir_real=SmartyFaces::resolvePath($image_dir,false);
			if(FileUtils::folderIsOnPath($image_dir_real,$folder)) {
				$allowed=true;
				break;
			}
		}
		if(!$allowed) return;
		if(!file_exists($path)) return;
		header('Content-type: '.mime_content_type($path));
		ob_end_clean();
		readfile($path);
		exit();
	}

	static function processCustomRequest() {
		$request=$_GET['request'];
		$requestProcessorClass=ucfirst($request)."RequestProcessor";
		if(class_exists($requestProcessorClass)) {
			$res = call_user_func(array($requestProcessorClass,'process'));
			if(!$res) {
				exit;
			}
		}
	}


	static function loadLanguages($lng=null) {
		$lng_dir=SmartyFaces::resolvePath(self::$config['lng_dir']);
		$lng_var=self::$config['lng_var'];
		if(empty($lng)) {
		$lng_sel=self::getLanguage();
		} else {
			$lng_sel=$lng;
		}
		$lng_file=$lng_dir."/".$lng_sel.".lng";
		if(file_exists($lng_file)) {
			$data = FileUtils::parseLngFile($lng_file);
		}

		$callbackFunction = @self::$callbackFunctions[self::CALLBACK_LANGUAGE_LOADING_FUNCTION];
		if(!empty($callbackFunction) && is_callable($callbackFunction)) {
			$callbackFunction($data);
		}

		if(!empty($data)) {
			self::$lng=new LanguageArray($data);
		}
		if(self::$smarty) {
			self::$smarty->assign($lng_var,self::$lng);
		}
	}

	static function translate($key){
		return self::$lng[$key];
	}

	static function getLanguage() {
		$lng_def=self::$config['lng_def'];
		$lng_sel=SFSession::get(['SF_SESSION',self::$config['lng_session_var']], $lng_def);
		return $lng_sel;
	}

	static function changeLanguage($lng, $reload=true) {
		SFSession::set(['SF_SESSION', self::$config['lng_session_var']], $lng);
		if($reload) {
			self::reload();
		}
	}

	static function resolvePath($path,$error=true) {
		$root_path=self::$config['root_path'];
		if(substr($path,0, 1)!=DIRECTORY_SEPARATOR && substr($path,1, 1)!=":") {
			$rpath=realpath($root_path."/".$path);
			if($rpath===false && $error){
				throw new Exception("Unable to resolve path for: $root_path and $path");
				exit();
			}
			return $rpath;
		} else {
			return $path;
		}

	}

	static function processRequest($view=null){

		if(isset($_GET['resource'])) {
			if(!isset($_GET['name'])) die("Not defined name of the resource");
			if(!isset($_GET['type'])) die("Not defined type of the resource");
			SmartyFaces::fetchResource();
		} else if (isset($_GET['image'])) {
			if(!isset($_GET['path'])) die("Not defined path to image");
			SmartyFaces::fetchImage();
		} else if (isset($_GET['ajax'])) {
			SmartyFaces::loadAndConfigureSmarty();
			SmartyFaces::loadLanguages();
			SmartyFaces::processAjax();
		} else if (isset($_GET['request'])) {
			SmartyFaces::processCustomRequest();
		} else {
			SmartyFaces::loadAndConfigureSmarty();
			SmartyFaces::loadLanguages();
			SmartyFaces::display($view);
		}
	}

	static function isResourceOrImage() {
		return isset($_GET['resource']) || isset($_GET['image']);
	}

	static function isRequest() {
		return isset($_GET['request']);
	}

	static function getSmartyVar($name) {
		if(self::$ajax) return null;
		return self::$smarty->getTemplateVars($name);
	}

	static function addScript($s,$external=false) {
		if($external) {
			$script='<script type="text/javascript" src="'.$s.'"></script>';
		} else {
			$script =  '<script type="text/javascript">';
			$script.=$s;
			$script.= '</script>';
		}
		if(self::$config['progressive_loading']) {
			self::$scripts.=$script;
		} else {
			return $script;
		}
	}

	static function setSkin($skin) {
		self::$skin=$skin;
	}

	static function clearSession() {
		SFSession::delete('SF_SESSION');
		self::reload();
	}

	static function checkAjax($formData) {

		$check = true;
		if(!isset($formData['sf_ajax_key'])) {
			$check=false;
		}

		$sf_ajax_key = $formData['sf_ajax_key'];
		if($sf_ajax_key != SFSession::get('sf_ajax_key')) {
			$check = false;
		}

		$headers = getallheaders();
		$found_header= false;
		foreach ($headers as $key => $val) {
			if (strtolower($key) == "sf_ajax_key") {
				if ($val == $sf_ajax_key) {
					$found_header = true;
					break;
				}
			}
		}

		if(!$found_header) {
			$check = false;
		}

		if(!$check) {
			SmartyFacesTrigger::trigger(SmartyFacesTrigger::NOT_AUTHORIZED_AJAX, $formData);
		}
	}

	static function globalAssign($key, $value) {
		self::$globalAssign[$key]=$value;
	}

}


class LanguageArray implements ArrayAccess {
	private $array;
	public function __construct(array $array){$this->array   = $array;}
	public function offsetExists($offset){return isset($this->array[$offset]);}
	public function offsetGet($offset){
		if(isset($this->array[$offset])) {
			$val=$this->array[$offset];
		} else {
			$val=$offset;
			$callbackFunction = @SmartyFaces::$callbackFunctions[SmartyFaces::CALLBACK_LANGUAGE_NOT_FOUND_FUNCTION];
			if(!empty($callbackFunction) && is_callable($callbackFunction)) {
				$callbackFunction($val);
			}
		}

		$callbackFunction = @SmartyFaces::$callbackFunctions[SmartyFaces::CALLBACK_LANGUAGE_PROCESS_TRANSLATION_FUNCTION];
		if(!empty($callbackFunction) && is_callable($callbackFunction)) {
			$callbackFunction($offset, $val);
		}
		return $val;
	}
	public function offsetSet($offset, $value){$this->array[$offset] = $value;}
	public function offsetUnset($offset){unset($this->array[$offset]);}
}


?>
