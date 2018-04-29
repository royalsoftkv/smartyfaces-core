<?php 

class SmartyFacesLogger {
	
	private static $_log=array();
	private static $time;
	
	public static function log($s) {
		if(!SmartyFaces::$logging) return;
		if(self::$time==null) {
			self::$time=microtime(true);
			$elapsed=0;
		} else {
			$elapsed=microtime(true)-self::$time;
		}
		$elapsed=round($elapsed,4);
		self::$_log[]=$elapsed . "\t".$s;
	}
	
	static function displayLog() {
		$s='<div id="sf-log" class="sf-log ui-widget-content">';
		$s.='<p class="ui-widget-header">SmartyFaces Log</p>';
		$s.='<div class="sf-log-content">';
		$s.=self::fillLog();
		$s.='</div>';
		$s.='</div>';
		$s.='<script type="text/javascript">
				$( "#sf-log" ).draggable({handle:"p"});
				$( "#sf-log" ).resizable();
			</script>';
		return $s;
	}
	
	static function fillLog() {
		$s='<pre>';
		foreach (self::$_log as $l) {
			$s.=$l;
			$s.="\r\n";
		}
		$s.='</pre>';
		return $s;
	}
	
}

?>