<?php 

class TagRenderer {
	
	public $tag;
	public $block;
	public $html="";
	//PHP-7-FIX
	public $attributes = [];
	public $custom;
	
	public function __construct($tag,$block=false) {
		$this->tag=$tag;
		$this->block=$block;
	}
	
	function setAttribute($name,$value) {
		$this->attributes[$name]=$value;
	}
	
	function setAttributeIfExists($name,$value) {
		if($value!==null && strlen(trim($value))>0) $this->setAttribute($name, $value);
	}
	
	function setIdAndName($id, $multiple=false) {
		$this->setId($id);
		$this->setAttribute("name", $id. ($multiple?"[]":""));
	}
	
	function setId($id) {
		$this->setAttribute("id", $id);
	}
	
	function setValue($value) {
		if($this->block) {
			$this->html=$value;
		} else {
			$this->setAttribute("value", $value);
		}
	}
	
	function render() {
		$s=$this->renderOpenTag();
		if($this->block) {
			$s.=$this->html;
			$s.=$this->renderCloseTag();
		}
		return $s;
	}
	
	function renderOpenTag() {
		$s='';
		$s.='<'.$this->tag;
		$s.=$this->renderAttributes();
		$s.=" ".$this->custom;
		if(!$this->block) {
			$s.='/>'.PHP_EOL;
			return $s;
		}
		$s.='>';
		return $s;
	}
	
	function renderCloseTag() {
		return '</'.$this->tag.'>'.PHP_EOL;
	}
	
	function renderAttributes() {
		if(count($this->attributes)==0) return "";
		$arr=array();
		foreach($this->attributes as $name=>$value) {
			$arr[]=$name.'="'.$value.'"';
		}
		return " ".implode(" ", $arr)." ";
	}
	
	static function renderHidden($name,$value) {
		$tr=new TagRenderer("input");
		$tr->setAttribute("type", "hidden");
		$tr->setIdAndName($name);
		$tr->setValue($value);
		return $tr->render();
	}
	
	function setCustom($custom) {
		$this->custom=$this->custom." ".$custom;
	}
	
	function addHtml($value) {
		$this->html.=$value.PHP_EOL;
	}
	
	function passAttributes($attributes, $attr) {
		foreach($attr as $a){
			if(isset($attributes[$a])){
				$this->setAttribute($a, $attributes[$a]);
			}
		}
	}
	
	function setDisabled($disabled) {
		if($disabled) {
			$this->setAttribute("disabled", "disabled");
		}
	}
	
	function setSelected($selected) {
		if($selected) {
			$this->setAttribute("selected", "selected");
		}
	}
	
	function appendAttribute($attr,$val) {
		if(!isset($this->attributes[$attr])) {
			$this->attributes[$attr]="";
		}
		$this->attributes[$attr].=" ".$val;
	}
}

