<?php

function smarty_block_sf_datatable($params, $content, $template, &$repeat)
{
    $tag="sf_datatable";
    
    $attributes_list=array("id","value","style","class");
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['value']['desc']="Array of data that will be displayed in table";
    $attributes['var']=array(
    	'required'=>true,
    	'desc'=>'Name of the row iteration variable'		
    );
    $attributes['index']=array(
    	'required'=>false,'default'=>null,'desc'=>'Index of row iteration'
    );
    $attributes['emptyRowsMessage']=array(
    	'required'=>false,'default'=>SmartyFaces::translate('there_are_no_results'),'desc'=>'text message that will be displayed if there is no rows in table'
    );
    $attributes['rowKeyVar']=array(
    	'required'=>false,'default'=>null,'desc'=>'Name of row variable which holds row iteration data: index, iteration, first, last'		
    );
    $attributes['rowSelection']=array(
    	'required'=>false,'default'=>null,'desc'=>'Index of row which will be rendered as selected'		
    );
    $attributes['header']=array(
    	'required'=>false,'default'=>null,'desc'=>'Header of the table'		
    );
    $attributes['datamodel']=array(
    	'required'=>false,'default'=>null,'desc'=>'Data model of table'		
    );
    $attributes['rowclass']=array(
    	'required'=>false,'default'=>'','desc'=>'Function that calculates style class for each row'		
    );
    $attributes['styled']=array(
    	'required'=>false,'default'=>true,'type'=>'bool', 'desc'=>'If set to true table will be styled with some css classes (deprecated)'
    );
    $attributes['responsive']=array(
    	'required'=>false,'default'=>true,'type'=>'bool', 'desc'=>'Display table to be responsive for smalled devices'
    );
    $attributes['visibleColumns']=array(
    	'required'=>false,'default'=>[],'type'=>'array', 'desc'=>'List if clumns to display. If empty display all'
    );
    if($params==null and $template==null) return $attributes;
    $params=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($params);

	$this_tag_stack=&$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];

	$this_tag_stack['params']=$params;
	
	if (is_null($content)) {
		$this_tag_stack['table']['attributes']['id']=$id;
		if($value instanceof SmartyFacesDataModel) {
			$dataModel = $value;
			$value=$dataModel->load();
		}
		//PHP-7-FIX
		if(!is_array($value)) $value=[];
		$this_tag_stack['count']=count($value);
		$data=$value;
		if(count($data)==0) $this_tag_stack['empty_data']=true;
		if(!isset($this_tag_stack['index'])) $this_tag_stack['index']=0;
		
		if(!isset($this_tag_stack['table'])) $this_tag_stack['table']=array();
		$table_class=$class;
		$table_class.=" table table-hover table-striped table-condensed";

		$this_tag_stack['table']['attributes']['class']=$table_class;
		$this_tag_stack['table']['attributes']['style']=$style;

		if(is_null($data)) $data=[];
        $row=@array_shift($data);
        
        $tr=array();
        $tr['attributes']=array();
        $rc="";
        if($rowclass!="") {
        	eval("\$rc=$rowclass;");
        	$rc=" $rc";
        }
        $tr['attributes']['class']=$rc;
        $this_tag_stack['table']['rows'][]=$tr;
        $this_tag_stack['data']=$data;
        $this_tag_stack['row']=$row;
        $this_tag_stack['first_pass']=true;
        $template->assign($var,$row);
        $template->assign($index,$this_tag_stack['index']);
        if($rowKeyVar!=null){
        	$rowKeyVarArr['index']=$this_tag_stack['index'];
        	$rowKeyVarArr['iteration']=$this_tag_stack['index']+1;
        	$rowKeyVarArr['first']=$this_tag_stack['index']==0;
        	$rowKeyVarArr['last']=($this_tag_stack['index']==count($value)-1);
        	$template->assign($rowKeyVar,$rowKeyVarArr);
        }
        return "";
    }
    
    $first_pass=@$this_tag_stack['first_pass'];
    $columns=array();
    if(isset($this_tag_stack['columns'])) $columns=$this_tag_stack['columns'];
	$data=@$this_tag_stack['data'];
    if($first_pass) {
    	if(isset($this_tag_stack['facets']['header'])) $header=$this_tag_stack['facets']['header']['content'];
    	if($header!=null) {
    		$this_tag_stack['table']['caption']=$header;
    	}
    	
    	if(_columnsHasHeaders($columns)) {
			$visibleColumns = $this_tag_stack['params']['visibleColumns'];
    		$col_index=-1;
		    $cells = [];
    		foreach($columns as $column){
    			$col_index++;
				if(count($visibleColumns)>0 && isset($column['id']) && !in_array($column['id'], $visibleColumns)) {
					continue;
				}
    			$cell['content']=$column['header'];
    			$cell['attributes']['class']=$column['class'];
    			$cell['attributes']['width']=$column['width'];
    			$cell['attributes']['title']=$column['title'];
    			$cell['attributes']['align']=$column['align'];
    			$cell['sortby']=$column['sortby'];
    			$cell['id']=$column['id'];
			    $cells[]=$cell;
    		}
		    if($value instanceof SmartyFacesDataModel) {
			    $dataModel = $value;
				$availableColumns = [];
			    foreach($columns as $col) {
					$header=$col['header'] ? trim(strip_tags($col['header'])) : "";
					$header=str_replace("&nbsp;", "", $header);
					if(empty($header)) {
						$header="[".$col['id']."]";
					}
				    $availableColumns[$col['id']]=$header;
			    }
			    $dataModel->availableColumns=$availableColumns;
		    }
		    if(count($visibleColumns)>0) {
				$sorted_cells = [];
				$cells_map = [];
				$sorted_index = [];
				foreach($cells as $ix => &$cell) {
					$cell['index']=$ix;
					$cells_map[$cell['id']]=$cell;
				}
				foreach($visibleColumns as $col_id) {
					if(isset($cells_map[$col_id])) {
						$sorted_cells[]=$cells_map[$col_id];
						$sorted_index[]=$cells_map[$col_id]['index'];
						unset($cells_map[$col_id]);
    		}
				}
			    $sorted_cells = array_merge($sorted_cells, array_values($cells_map));
			    $cells = $sorted_cells;
			    $this_tag_stack['table']['sorted_cells_index']=$sorted_index;
		    }
		    $this_tag_stack['table']['head']['cells']=$cells;

    	}
    }
    $this_tag_stack['first_pass']=false;
	if(!is_array($data)) {
		$data=[];
	}
    $count=count($data);
    
    if($count>0) {
    	$repeat=true;
	    $row=array_shift($data);
	    $this_tag_stack['data']=$data;
	    $template->assign($var,$row);
	    $this_tag_stack['index']++;
	    $this_tag_stack['col_index']=0;
	    $template->assign($index,$this_tag_stack['index']);
	    if($rowKeyVar!=null){
	    	$rowKeyVarArr['index']=$this_tag_stack['index'];
	    	$rowKeyVarArr['iteration']=$this_tag_stack['index']+1;
	    	$rowKeyVarArr['first']=$this_tag_stack['index']==0;
		    //PHP-7-FIX
	    	$rowKeyVarArr['last']=($this_tag_stack['index']==count(is_array($value)?$value:[])-1);
	    	$template->assign($rowKeyVar,$rowKeyVarArr);
	    }
	    $even=($this_tag_stack['index'] % 2 == 0) ? "" : "even-row";
		$rowClass="";
		if($rowSelection!==null and $rowSelection==$this_tag_stack['index']) {
			$rowClass=" selected";
		}
		$rc="";
		if($rowclass!="") {
			eval("\$rc=$rowclass;");
			$rc=" $rc";
		}
	    $tr=array();
	    $tr['attributes']['class']=$even.$rowClass.$rc;
	    $this_tag_stack['table']['rows'][]=$tr;
    } else {
    	$footer=null;
    	if(isset($this_tag_stack['facets']['footer'])) {
    		$footer=$this_tag_stack['facets']['footer']['content'];
    		if($this_tag_stack['facets']['footer']['params']['rendered']===false) $footer = null;
    	}
    	if($footer!=null) {
    		$this_tag_stack['table']['footer']=$footer;
    	}
    	
		$template->clearAssign(array($var,$index));
		if($rowKeyVar!=null){
			$template->clearAssign($rowKeyVar);
		}
		
		$s= _displayTable($this_tag_stack,$template);
		if(isset($this_tag_stack['reorder']) && $this_tag_stack['reorder'])	{
			$script='SF.reorder.init("'.$this_tag_stack['table']['attributes']['id'].'");';
			$s.=SmartyFaces::addScript($script);
		}
		return $s;
    }
    
}

function _getAttributes($attributes) {
	$attr=array();
	if(is_array($attributes)) {
		foreach($attributes as $name=>$value) {
			if(strlen($value ?? '')>0) {
				$attr[]="$name=\"$value\"";
			}
		}
	}
	if(count($attr)>0) {
		$attributes_str=" ".implode(" ", $attr);
	} else {
		$attributes_str="";
	}
	return $attributes_str;
}

function _displayTable($this_tag_stack, $template) {
	//echo '<pre>'.print_r($this_tag_stack['table'],true).'</pre>';
	$attributes=@$this_tag_stack['table']['attributes'];
	$s="";
	$responsive=$this_tag_stack['params']['responsive'];
	if($responsive){
		$s.='<div class="table-responsive">';
	}
	$s.="<table"._getAttributes($attributes).">";
	$hasdata = (isset($this_tag_stack['table']['rows']) && count($this_tag_stack['table']['rows'])>0 and !isset($this_tag_stack['empty_data']));
	if($hasdata) {
		if(isset($this_tag_stack['table']['rows'][0]['cells'])) {
			$colspan=count($this_tag_stack['table']['rows'][0]['cells']);
		} else {
			$colspan="1";
		}
	} else {
		if(isset($this_tag_stack['table']['head']['cells'])) {
			$colspan=count($this_tag_stack['table']['head']['cells']);
		} else {
			$colspan="1";
		}
	}
	$thead_open=false;
	if(isset($this_tag_stack['table']['caption'])) {
		$s.="<thead>";
		$thead_open=true;
		$s.="<tr>";
		$class="sf-caption";
		if(isset($this_tag_stack['facets']['header']['params']['class'])) {
			$class.=" ".$this_tag_stack['facets']['header']['params']['class'];
		}
		$s.="<td class=\"".$class."\" colspan=\"$colspan\">";
		$s.=trim($this_tag_stack['table']['caption']);
		$s.="</td>";
		$s.="</tr>";
	}
	if(isset($this_tag_stack['table']['head']) and count($this_tag_stack['table']['head'])>0) {
		if(!$thead_open) {
			$s.="<thead>";
		}
		if(count($this_tag_stack['table']['head']['cells'])>0) {
			$s.="<tr>";
			foreach($this_tag_stack['table']['head']['cells'] as $cell) {
				$sortby=isset($cell['sortby']) && strlen($cell['sortby'])>0;
				if(is_object($this_tag_stack['params']['value'])) {
					if($sortby && $this_tag_stack['params']['value']->column==$cell['sortby']) {
						if(isset($cell['attributes']['class'])) {
							$cell['attributes']['class'].=" sorted";
						} else {
							$cell['attributes']['class']="sorted";
						}
					}
				}
				if($sortby) {
					$cell['attributes']['class'].=" sortable";
				}
				$s.="<th"._getAttributes($cell['attributes']).">";
				$head_id = $this_tag_stack['table']['attributes']['id']."-".$cell['id'];
				if($sortby) $s.='<div class="d-flex align-items-center justify-content-between"><a id="'.$head_id.'" href="" onclick="SF.dm.sort(this);return false">';
				$content="";
				if(isset($cell['content'])) $content=trim($cell['content']);
				$s.=$content;
				if($sortby) $s.='</a>';
				if($sortby and is_object($this_tag_stack['params']['value'])) {
					$s.=$this_tag_stack['params']['value']->icon($cell['sortby']);
                    $s.='</div>';
				}
				$s.="</th>";
			}
			$s.="</tr>";
		}
	}
	if($thead_open) {
		$s.="</thead>";
	}
	$s.="<tbody>";
	if($hasdata) {
		foreach($this_tag_stack['table']['rows'] as $index=>$row) {
			if(isset($this_tag_stack['params']['rowSelection']) and $this_tag_stack['params']['rowSelection']==$index) {
				$rowClass=" selected ";
				if(isset($row['attributes']['class'])) {
					$row['attributes']['class'].=" $rowClass";
				} else {
					$row['attributes']['class']=$rowClass;
				}
			}
		    $s.='<tr'._getAttributes($row['attributes']).'>';
			$cells=$row['cells'];
			if(!empty($cells)) {
				if(isset($this_tag_stack['table']['sorted_cells_index'])) {
					$sorted_cells_index = $this_tag_stack['table']['sorted_cells_index'];
					$sorted_cells = [];
					foreach ($sorted_cells_index as $ix) {
						$sorted_cells[]=$cells[$ix];
					}
					$cells = $sorted_cells;
				}
				foreach($cells as $cell) {
					$sortby=isset($cell['sortby']);
					if(is_object($this_tag_stack['params']['value'])) {
						if($sortby && $this_tag_stack['params']['value']->column==$cell['sortby']) {
							if(isset($cell['attributes']['class'])) {
								$cell['attributes']['class'].=" sorted";
							} else {
								$cell['attributes']['class']="sorted";
							}
						}
					}
					$s.="<td"._getAttributes($cell['attributes']).">";
					$s.=trim($cell['content']);
					$s.="</td>";
				}
			}
			$s.= "</tr>";
		}
	} else {
		$s.='<td colspan="'.$colspan.'" class="sf-emptydata">';
		$s.=trim($this_tag_stack['params']['emptyRowsMessage']);
		$s.='</td>';
	}
	if(isset($this_tag_stack['table']['footer'])) {
		$s.="<tfoot>";
		$s.="<tr>";
		$s.="<td class=\"\" colspan=\"$colspan\">";
		$s.=trim($this_tag_stack['table']['footer']);
		$s.="</td>";
		$s.="</tr>";
		$s.="</tfoot>";
	}
	$s.="</tbody>";
	$s.="</table>";
	if($responsive) {
		$s.='</div>';
	}
	return $s;
}

function _columnsHasHeaders($columns) {
	foreach($columns as $column) {
		if(isset($column['header'])) return true;
	}
	return false;
}

