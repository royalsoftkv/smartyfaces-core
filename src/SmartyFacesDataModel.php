<?php

abstract class SmartyFacesDataModel {
	
	protected $name='global';
	protected $session_save_options=array();  //page,rpp,sort,filter

    var $pages;
    var $rows_per_page=10;
    var $rows_per_page_list=array(5,10,50,100,0);
    var $start;
    var $stop;
    var $page=1;
    var $count;
    
    var $column;
    var $asc;

    var $default_sort;
    
    var $_list;
    var $selected = array();
    
    var $filter=array();
    var $params=array();
    
    var $light=false;
    
    function __construct($light=false){
    	$this->light=$light;
    	$this->resetFilter();
    	$this->restoreOptions();
    }
    
    function __get($name){
    	if($name=='list') {
    		if($this->light) {
    			$sql=$this->query(false);
    			return $this->getList($sql);
    		} else {
    			return $this->_list;
    		}
    		
    	}
    }
    
    function __set($name,$val) {
    	if($name=='list') {
    		if($this->light) return;
    		$this->_list=$val;
    	}
    }

	/**
	 * @param bool $all
	 * @return \ActiveRecord\Model[]
	 */
	function load($all=false) {
		$sql=$this->query(false);
		
		$column=$this->column;
		$order=($this->asc ? "asc" : "desc");
		if($column) {
			if(!is_array($column)) {
				$sql.= " order by $column $order ";
			} else {
				$sql.= " order by ".implode(",", $column);
			}
		} else if (!empty($this->default_sort)) {
			$sql.= " order by  " .$this->default_sort;
		}
		
		if(!$all) {
			$this->calculate();
			if($this->rows_per_page>0) {
				$offset=$this->getOffset();
				$limit=$this->getLimit();
				$sql.=" limit $offset, $limit";
			}
		}
		$list= $this->getList($sql);
		$this->storeOptions();
		if(!$this->light) {
			$this->_list=$list;
		}
		return $list;
	}
	
	abstract function getList($sql);
	abstract function getCount($sql);
	abstract function query($count);
	abstract function getRowKey($row);
	
	protected function calculate(){
		$this->count=$this->count();
		if($this->rows_per_page==0) {
			$this->pages=1;
			$this->start=1;
			$this->stop=$this->count;
		} else {
			$this->pages=ceil($this->count/$this->rows_per_page);
			$this->start=($this->page-1)*$this->rows_per_page+1;
			$this->stop=$this->start+$this->rows_per_page-1;
			if($this->stop > $this->count) $this->stop=$this->count;
		}
	}
	
	public function count() {
		$sql=$this->query(true);
		return $this->getCount($sql);
	}
	
	public function getOffset(){
		return ($this->page-1)*$this->rows_per_page;
	}
	public function getLimit(){
		return $this->rows_per_page;
	}
	public function first(){
		if($this->page>1){
			$this->page=1;
		}
		$this->storeOptions();
	}
	 
	public function previous(){
		if($this->page>1){
			$this->page--;
		}
		$this->storeOptions();
	}
	 
	public function next(){
		if($this->page<$this->pages){
			$this->page++;
		}
		$this->storeOptions();
		 
	}
	 
	public function last(){
		if($this->page<$this->pages){
			$this->page=$this->pages;
		}
		$this->storeOptions();
	}
	
	public function changeRowsPerPage($rows) {
		$this->rows_per_page=$rows;
		$this->first();
	}
	
	public function go($page) {
		if($page<1 || $page>$this->pages){
			$this->page=1;
		} else {
			$this->page=$page;
		}
		$this->storeOptions();		
	}
	
	public function paginate() {
		$action_data=$_POST['sf_action_data']['action'];
		switch ($action_data) {
			case "first":
				$this->first();
				return;
			case "prev":
				$this->previous();
				return;
			case "next":
				$this->next();
				return;
			case "last":
				$this->last();
				return;
			case "rpp":
				$this->changeRowsPerPage($_POST['sf_action_data']['param']);
				return;
			case "page":
				$this->go($_POST['sf_action_data']['param']);
				return;
		}
	}
	
	public function sort() {
		$action_data=$_POST['sf_action_data']['action'];
		if($action_data=="sort") {
			$column=SmartyFacesComponent::$action_component['params']['sortby'];
			$this->sortColumn($column);
		}
	}
	
	public function sortColumn($column) {
		if($this->column==$column) {
			$this->asc=!$this->asc;
		} else {
			$this->column=$column;
			$this->asc=true;
		}
		$this->storeOptions();
	}
	
	public function icon($column) {
		if($this->column==$column) {
			return ($this->asc ?
					'<span class="fa fa-arrow-up"></span>'
					:
					'<span class="fa fa-arrow-down"></span>');
		} else {
			return '<span class="fa fa-arrows-v text-muted"></span>';
		}
	}
	
	function paginatorStrings() {
		return array(
			"displayed"=>SmartyFaces::translate("displayed"),
			"total"=>SmartyFaces::translate("total"),
			"of"=>SmartyFaces::translate("of"),
			"page"=>SmartyFaces::translate("page"),
			"go_to_first_page"=>SmartyFaces::translate("go_to_first_page"),
			"go_to_previous_page"=>SmartyFaces::translate("go_to_previous_page"),
			"go_to_next_page"=>SmartyFaces::translate("go_to_next_page"),
			"go_to_last_page"=>SmartyFaces::translate("go_to_last_page"),
			"rows_per_page"=>SmartyFaces::translate("rows_per_page"),
			"all"=>SmartyFaces::translate("all")
		);
	}
	
	public function paginatorTemplate($id) {
		$strings=$this->paginatorStrings();
		$first=$this->page==1;
		$last=$this->page==$this->pages;
		if($this->count==0) return "";
		
		$status_line="";
		$status_line.=$strings['displayed'].' <strong>'.$this->start.' - '.$this->stop.'</strong> '.$strings['of'].' '.$strings['total'] . ' ';
		$status_line.='<strong>'.$this->count.'</strong>';
		$status_line.=' | '.$strings['page'].' <strong>'.$this->page.'</strong> '.$strings['of'].' <strong>'.$this->pages. '</strong>';
		
		$s="";
		$s.='<div class="paginator row align-items-center">';
		$s.='<div class="col-md-4 text-left status col-4">';
		$s.=$status_line;
		$s.='</div>';
		$s.='<div class="col-md-4 text-center pages col-4 d-flex justify-content-center">';

		$s.='<ul class="pagination">';

		$s.='<li class="page-item '.($first ? 'disabled' : '').'">';
		if($first) {
			$s.='<span class="page-link">';
		} else {
			$s.='<a class="page-link" id="'.$id.'" href="#" onclick="'.(!$first ? 'SF.dm.paginate(this,\'first\'); ' : '').'return false;" title="'.$strings['go_to_first_page'].'">';
		}
		$s.='<span class="fa fa-angle-double-left"></span>';
		if($first) {
			$s.='</span>';
		} else {
			$s.='</a>';
		}
		$s.='</li>';

		$s.='<li class="page-item '.($first ? 'disabled' : '').'">';
		if($first) {
			$s.='<span class="page-link" >';
		} else {
			$s.='<a  class="page-link" id="'.$id.'" href="#" onclick="'.(!$first ? 'SF.dm.paginate(this,\'prev\'); ' : '').'return false;" title="'.$strings['go_to_previous_page'].'">';
		}
		$s.='<span class="fa fa-angle-left"></span>';
		if($first) {
			$s.='</span>';
		} else {
			$s.='</a>';
		}
		$s.='</li>';

		$max_pages=3;
		$start_page=1;
		$end_page=$this->pages;
		if($this->pages > $max_pages) {
			$currentPagePositionFromStart = intval($max_pages/2);
			$start_page =	$this->page - $currentPagePositionFromStart;
			if($start_page <= 0){
				$start_page = 1;
			}
			$end_page = $start_page + $max_pages - 1;
			if($end_page > $this->pages){
				$end_page = $this->pages;
				$start_page = $end_page - $max_pages + 1;
			}
		}
		for($i=$start_page;$i<=$end_page;$i++) {
			$s.='<li class="page-item '.($this->page==$i ? 'active' : '').'">';
			$s.='<a  class="page-link" id="'.$id.'" href="#" onclick="SF.dm.paginate(this,\'page\','.$i.'); return false;">';
			$s.=$i;
			$s.='</a>';
			$s.='</li>';
		}

		$s.='</li>';

		$s.='<li class="page-item '.($last ? 'disabled' : '').'">';
		if($last) {
			$s.='<span class="page-link" >';
		} else {
			$s.='<a  class="page-link" id="'.$id.'" href="#" onclick="'.(!$last ? 'SF.dm.paginate(this,\'next\'); ' : '').'return false;" title="'.$strings['go_to_next_page'].'">';
		}
		$s.='<span class="fa fa-angle-right"></span>';
		if($last) {
			$s.='</span>';
		} else {
			$s.='</a>';
		}
		$s.='</li>';

		$s.='<li class="page-item '.($last ? 'disabled' : '').'">';
		if($last) {
			$s.='<span class="page-link" >';
		} else {
			$s.='<a  class="page-link" id="'.$id.'" href="#" onclick="'.(!$last ? 'SF.dm.paginate(this,\'last\'); ' : '').'return false;" title="'.$strings['go_to_last_page'].'">';
		}
		$s.='<span class="fa fa-angle-double-right"></span>';
		if($last) {
			$s.='</span>';
		} else {
			$s.='</a>';
		}
		$s.='</li>';

		$s.='</ul>';


		$s.='</div>';
		$s.='<div class="col-md-4 text-right rpp col-4 d-flex justify-content-end align-items-center gap-3">';

		$s.=$strings['rows_per_page'].': ';
		$list=$this->rows_per_page_list;
		$s.='<select id="'.$id.'" onchange="SF.dm.paginate(this,\'rpp\',this.value); return false;" class="form-select w-auto">';
		foreach($list as $val) {
			$s.='<option value="'.$val.'"'.($val==$this->rows_per_page ? ' selected="selected"' : '').'>'.($val==0 ? $strings['all'] : $val).'</option>';
		}
		$s.='</select>';

		$s.='</div>';
		$s.='</div>';
		return $s;
	}
	
	public function getSelected() {
		$list=$this->load(true);
		foreach($list as $item) {
			if(in_array($this->getRowKey($item),$this->selected)) {
				$list[]=$item;
			}
		}
		return $list;
	}
	
	public function clearSelected() {
		$this->selected=array();
	}
	
	public function search() {
		$this->page=1;
		$this->load();
	}
	
	public function getFilter($name) {
		if(!isset($this->filter[$name])) return false;
		if(strlen(trim($this->filter[$name]))==0) return false;
		return $this->filter[$name];
	}
	
	public function resetSearch() {
		$this->resetFilter();
		$this->resetSort();
		$this->load();
	}
	
	public function resetFilter() {
		$this->filter=array();
	}
	
	public function resetSort() {
		$this->column=null;
		$this->asc=true;
	}

	
	public function selectAllPages() {
		$list = $this->load(true);
		$this->selected=array();
		foreach($list as $item) {
			$this->selected[]=$this->getRowKey($item);
		}
	}
	
	public function isSelected($row) {
		if(count($this->selected)==0) return false;
		return in_array($this->getRowKey($row), $this->selected);
	}
	
	public function toggleSelect($key) {
		if(in_array($key, $this->selected)) {
			$this->selected=array_diff($this->selected, array($key));
		} else {
			$this->selected[]=$key;
		}
	}
	
	public function selectAll() {
		foreach($this->list as $row) {
			$key=$this->getRowKey($row);
			if(!in_array($key, $this->selected)) {
				$this->selected[]=$key;
			}
		}
	}
	
	public function selectNone() {
		foreach($this->list as $row) {
			$key=$this->getRowKey($row);
			$remove=array();
			if(in_array($key, $this->selected)) {
				$remove[]=$key;
			}
			$this->selected=array_diff($this->selected, $remove);
		}
	}
	
	public function selectInvert() {
		foreach($this->list as $row) {
			$this->toggleSelect($this->getRowKey($row));
		}
	}
	
	public function selectedCount() {
		return count($this->selected);
	}
	
	
	public function canSelectAll() {
		$count=$this->count;
		$selected=count($this->selected);
		if($selected>0 and $selected!=$count) {
			return true;
		} else {
			return false;
		}
	}
	
	function storeOptions(){
		$session_save_options=$this->session_save_options;
		if(in_array("page", $session_save_options)) {
			SFSession::set(["datamodel_".$this->name, 'page'], $this->page);
		}
		if(in_array("rpp", $session_save_options)) {
			SFSession::set(["datamodel_".$this->name, 'rpp'], $this->rows_per_page);
		}
		if(in_array("sort", $session_save_options)) {
			SFSession::set(["datamodel_".$this->name, 'sort'], array($this->column,$this->asc));
		}
		if(in_array("filter", $session_save_options)) {
			SFSession::set(["datamodel_".$this->name, 'filter'], $this->filter);
		}
	}
	
	function restoreOptions() {
		if(SFSession::exists(["datamodel_".$this->name, 'page'])) {
			$this->page = SFSession::get(["datamodel_".$this->name, 'page'],1);
		}
		if(SFSession::exists(["datamodel_".$this->name, 'rpp'])) {
			$this->rows_per_page = SFSession::get(["datamodel_".$this->name, 'rpp'],10);
		}
		if(SFSession::exists(["datamodel_".$this->name, 'sort'])) {
			$arr = SFSession::get(["datamodel_".$this->name, 'sort']);
			$this->column=$arr[0];
			$this->asc=$arr[1];
		}
		if(SFSession::exists(["datamodel_".$this->name, 'filter'])) {
			$this->filter = SFSession::get(["datamodel_".$this->name, 'filter']);
		}


	}
	
	function resetOptions() {
		SFSession::delete("datamodel_".$this->name);
	}
	
	function getFilterValue($name) {
		$val=$this->filter[$name];
		$val=trim($val);
		$val=str_replace("*", "%", $val);
		return $val;
	}
	
	function isFilter($name) {
		if(!isset($this->filter[$name])) return false;
		$val=$this->filter[$name];
		$val=trim($val);
		return strlen($val)>0;
	}
	
	function isFiltered() {
		return (count($this->filter)>0);
	}

	function filterSql($name, $column, $operator="=") {
		if($this->isFilter($name)) {
			$this->params[]=$this->getFilterValue($name);
			return " and $column $operator ? ";
		}
	}

	function customFilterSql($name, $sql="1=1") {
		if($this->isFilter($name)) {
			$cnt = substr_count($sql, "?");
			for($i=1; $i<=$cnt; $i++) {
				$this->params[]=$this->getFilterValue($name);
			}
			return " and $sql ";
		}
	}

	function isEmptyFilter() {
		if (count($this->filter)==0)return true;
		foreach($this->filter as $val) {
			if(!empty($val) && !is_null($val) && strlen($val)>0) {
				return false;
			}
		}
		return true;
	}

	function isSorted() {
		return !empty($this->column);
	}

	function getItemByKey($key) {
		foreach ($this->_list as $item) {
			if($this->getRowKey($item) == $key) {
				return $item;
			}
		}
	}
}

?>
