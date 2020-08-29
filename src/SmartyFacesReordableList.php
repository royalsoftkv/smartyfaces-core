<?php 

class SmartyFacesReordableList {
	
	public $list;
	public $autosave;
	public $position_column;
	private $old_positions;
	
	function __construct($list,$autosave=false,$position_column="position") {
		$this->list=$list;
		$this->autosave=$autosave;
		$this->position_column=$position_column;
	}
	
	function moveTop($row) {
		$this->storePositions();
		$this->list=self::array_top($this->list, $row);
		$this->reloadPositions();
		if($this->autosave) $this->save();
	}
	function moveUp($row) {
		$this->storePositions();
		$this->list=self::array_up($this->list, $row);
		$this->reloadPositions();
		if($this->autosave) $this->save();
	}
	function moveDown($row) {
		$this->storePositions();
		$this->list=self::array_down($this->list, $row);
		$this->reloadPositions();
		if($this->autosave) $this->save();
	}
	function moveBottom($row) {
		$this->storePositions();
		$this->list=self::array_bottom($this->list, $row);
		$this->reloadPositions();
		if($this->autosave) $this->save();
	}
	function reloadPositions() {
		$p=0;
		$position_column = $this->position_column;
		foreach($this->list as $item) {
			$p++;
			if(is_array($item)) {
				$item[$position_column]=$p;
			} else {
				$item->$position_column=$p;
			}
		}
	}
	function storePositions() {
		$p=0;
		$item=$this->list[0];
		$class=get_class($item);
		$table=$class::$table_name;
		$primary=$class::$primary_key;
		$position_column = $this->position_column;
		$this->old_positions=array();
		foreach($this->list as $item) {
			$p++;
			if(is_array($item)) {
				$this->old_positions[$item[$primary]]=$item[$position_column];
			} else {
				$this->old_positions[$item->$primary]=$item->$position_column;
			}
		}
	}
	function save() {
		$p=0;
		$item=$this->list[0];
		$class=get_class($item);
		$table=$class::$table_name;
		$primary=$class::$primary_key;
		$position=$this->position_column;
		$conn=$class::connection();
		$sql="update $table set $position = case ";
		foreach($this->list as $item) {
			$p++;
			$id=$item->id;
			$old_pos=$this->old_positions[$id];
			$sql.=" when $primary='$id' then $p ";
		}
		$sql.=" end ";
		$conn->query($sql);
	}
	
	function saveReorder($pos_list){
		$p=0;
		$item=$this->list[0];
		$class=get_class($item);
		$table=$class::$table_name;
		$primary=$class::$primary_key;
		$position=$this->position_column;
		$conn=$class::connection();
		$sql="update $table set $position = case ";
		$new_list=array();
		foreach($pos_list as $index=>$p) {
			$pos=$index+1;
			$id=$this->list[$p]->id;
			$old_pos=$this->list[$p]->$position;
			$new_list[]=$this->list[$p];
			if($old_pos!=$pos) {
				$sql.=" when $primary='$id' then $pos ";
			}
		}
		$this->list=$new_list;
		$this->reloadPositions();
		$sql.=" end ";
		$conn->query($sql);
	}
	
	function reorder() {
		$action_data=$_POST['sf_action_data']['action'];
		switch ($action_data) {
			case "save":
				$order=$_POST['sf_action_data']['param']['order'];
				$this->saveReorder(explode(",",$order));
				break;
			case "move":
				$direction=$_POST['sf_action_data']['param']['direction'];
				$row=$_POST['sf_action_data']['param']['row'];
				switch ($direction) {
					case "top":
						$this->moveTop($row);
						break;
					case "up":
						$this->moveUp($row);
						break;
					case "down":
						$this->moveDown($row);
						break;
					case "bottom":
						$this->moveBottom($row);
						break;
				}
		}
	}
	
	static function array_down($a,$x) {
		if( count($a)-1 > $x ) {
			$b = array_slice($a,0,$x,true);
			$b[] = $a[$x+1];
			$b[] = $a[$x];
			$b += array_slice($a,$x+2,count($a),true);
			return($b);
		} else { return $a;
		}
	}
	
	static function array_up($a,$x) {
		if( $x > 0 and $x < count($a) ) {
			$b = array_slice($a,0,($x-1),true);
			$b[] = $a[$x];
			$b[] = $a[$x-1];
			$b += array_slice($a,($x+1),count($a),true);
			return($b);
		} else { return $a;
		}
	}
	
	static function array_top($a,$x) {
		$b=$a[$x];
		unset($a[$x]);
		return array_merge(array($b),array_values($a));
	}
	
	static function array_bottom($a,$x) {
		$b=$a[$x];
		unset($a[$x]);
		return array_merge(array_values($a),array($b));
	}
	
}



?>