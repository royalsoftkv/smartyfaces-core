<?php

use ActiveRecord\Table;
abstract class SmartyFacesObjectDataModel extends SmartyFacesDataModel {
	
	protected $name='global';
	public $model="Model";
	public $options = array();
	
    function getList($sql){}
    function getCount($sql){}
    function query($count){}
    
    function __get($name){
    	if($name=='list') {
    		if($this->light) {
    			return $this->load();
    		} else {
    			return $this->_list;
    		}
    		
    	}
    }
	
	function load($all=false) {
		
		$table=Table::load($this->model);
		$options=$this->options;
		
		$column=$this->column;
		$order=($this->asc ? "asc" : "desc");
		if($column) {
			$options['order']="$column $order";
		}
		
		if(!$all) {
			$this->calculate();
			if($this->rows_per_page>0) {
				$offset=$this->getOffset();
				$limit=$this->getLimit();
				$options['limit']=$limit;
				$options['offset']=$offset;
			}
		}
		$list= $table->find($options);
		$this->storeOptions();
		if(!$this->light) {
			$this->_list=$list;
		}
		return $list;
	}
	
	function getRowKey($row) {
		$table=Table::load($this->model);
		$pk_key = $table::$primary_key;
		return $row->$pk_key;
	}
	
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
		$table=Table::load($this->model);
		$options=$this->options;
		$options['select']='count(*) as cnt';
		$list= $table->find($options);
		$item=$list[0];
		return $item->cnt;
	}
}

?>