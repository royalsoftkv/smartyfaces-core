<?php

class SmartyFacesVue
{

	static $app;
	static $assetsConfig;
	static $dev = false;
	static $allowedClasses = [];

	static function process($app, $dev=false) {
		self::$app = $app;
		self::$dev = $dev;
		$assetsFile = ROOT . "/apps/" .$app . "/webpack-assets.json";
		if(file_exists($assetsFile)) {
			$content = file_get_contents($assetsFile);
			self::$assetsConfig = json_decode($content, true);
		}
	}

	static function renderHead() {
		if(self::$dev) {
			$devServer = self::getDevServer();
			$chunk_vendors_js = $devServer . 'js/chunk-vendors.js';
			$app_js = $devServer . 'js/app.js';
			return '  
	            <link href="'.$app_js.'" rel="preload" as="script">
	            <link href="'.$chunk_vendors_js.'" rel="preload" as="script">';
		} else {
			$chunk_vendors_js = '/apps/'.self::$app.'/dist/' . self::$assetsConfig['chunk-vendors']['js'];
			$app_js = '/apps/'.self::$app.'/dist/' . self::$assetsConfig['app']['js'];
			$chunk_vendors_css = '/apps/'.self::$app.'/dist/' . self::$assetsConfig['chunk-vendors']['css'];
			$app_css = '/apps/'.self::$app.'/dist/' . self::$assetsConfig['app']['css'];
			return '  
	            <link href="'.$app_js.'" rel="preload" as="script">
	            <link href="'.$chunk_vendors_js.'" rel="preload" as="script">
	            <link href="'.$app_css.'" rel="preload" as="style">
			    <link href="'.$chunk_vendors_css.'" rel="preload" as="style">
			    <link href="'.$app_js.'" rel="preload" as="script">
			    <link href="'.$chunk_vendors_js.'" rel="preload" as="script">
			    <link href="'.$chunk_vendors_css.'" rel="stylesheet">
			    <link href="'.$app_css.'" rel="stylesheet">';
		}
	}

	static function getDevServer() {
		return 'https://localhost:8080/';
	}

	static function renderScripts() {
		if(self::$dev) {
			$devServer = self::getDevServer();
			$chunk_vendors_js = $devServer . 'js/chunk-vendors.js';
			$app_js = $devServer . 'js/app.js';
		} else {
			$chunk_vendors_js = '/apps/'.self::$app.'/dist/' . self::$assetsConfig['chunk-vendors']['js'];
			$app_js = '/apps/'.self::$app.'/dist/' . self::$assetsConfig['app']['js'];
		}
		return '<script src="'.$chunk_vendors_js.'"></script>
				<script src="'.$app_js.'"></script>';
	}

	static function processVueRequest() {
		$sf_action = $_POST['sf_action'];
		$sf_action_data = $_POST['sf_action_data'];

		ob_end_clean();
		ob_start();

		if(!$sf_action) {
			SmartyFacesVue::sendResponseCode(400, "Undefined remote action");
		}
		$arr = explode("::", $sf_action);
		$class = $arr[0];
		$method = $arr[1];
		if(!class_exists($class)) {
			SmartyFacesVue::sendResponseCode(400, "Unknown action class");
		}
		if(!self::checkClassAccess($class, $method)) {
			SmartyFacesVue::sendResponseCode(400, "Not allowed action class");
		}
		if(!method_exists($class, $method)) {
			SmartyFacesVue::sendResponseCode(400, "Unknown class method");
		}
		$actionData = [];
		if($sf_action_data) {
			parse_str($sf_action_data, $actionData);
		}

		try {
			$result = call_user_func([$class, $method], $actionData);
			$data['success']=true;
			$data['result']=$result;
		} catch (Exception $e) {
			$data['success']=false;
			if($e instanceof \ActiveRecord\DatabaseException) {
				$data['error']='DatabaseException';
			} else {
				$data['error']=$e->getMessage();
			}
			if(Configuration::isDev()) {
				$data['trace']=$e->getTrace();
			}
		}
		echo json_encode($data);
		exit;
	}

	private static function checkClassAccess($class, $method) {
		foreach(self::$allowedClasses as $row) {
			if(!is_array($row)) {
				//allowed whole class
				if($class == $row) {
					return true;
				}
			} else {
				//[class,[methods]]
				$check_class = $row[0];
				$check_methods = $row[1];
				if($check_methods && !is_array($check_methods)) {
					$check_methods = [$check_methods];
				}
				if($check_class == $class && in_array($method, $check_methods)) {
					return true;
				}
			}
		}
		return false;
	}

	static function sendResponseCode($code, $msg = null) {
		http_response_code($code);
		if($msg) {
			echo $msg;
		}
		exit;
	}

	static function allow($class) {
		self::$allowedClasses[]=$class;
	}

}
