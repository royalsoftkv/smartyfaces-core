<?php 

function smarty_block_sf_tabs($params, $content, $template, &$repeat)
{

	$tag="sf_tabs";

	$attributes_list=array("id","action","value");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['value']=array(
			'required'=>false,
			'default'=>0,
			'desc'=>'Index of selected tab'
	);
	$attributes['load']=array(
			'required'=>false,
			'default'=>false,
			'type'=>'bool',
			'desc'=>'Set false to prevent load with javascript'
	);
	if($params==null and $template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	
	SmartyFacesComponent::createComponent($id, $tag, $params);

	@$this_tag_stack=$template->smarty->_cache['_tag_stack'][count($template->smarty->_cache['_tag_stack'])-1][2];
	
	if(is_null($content)){
		return;
	}
	
	SmartyFacesContext::$bindings[$id]=$value;
	$value=  SmartyFaces::evalExpression($value);
	if($value=="null") $value=0;
	
	$div=new TagRenderer("div",true);
	$div->setId("$id-tabs");
	
	$ul=new TagRenderer("ul",true);
    $ul->setAttribute("class", "nav nav-tabs");
    if(isset($this_tag_stack['tabs'])) {
        foreach ($this_tag_stack['tabs'] as $index => $tab) {
            $li = new TagRenderer("li", true);
            $li->setAttribute("role","presentation");
            $li->setAttribute("class","nav-item");
            $a = new TagRenderer("a", true);
            $a->setAttribute("href", "#$id-tabs-$index");
            $a->setAttribute("class", $value == $index ? "nav-link active" : "nav-link");
            if ($action === null) {
                $a->setAttribute("data-bs-toggle", "tab");
                $a->setAttribute("data-bs-target", "#$id-tabs-$index");
            } else {
                $a->setAttribute("onclick", 'SF.tabs.bs_action(\'' . $id . '\',' . $index . '); return false;');
            }
            $onclick = $tab['params']['onclick'];
            if(strlen($onclick ?? '')>0) {
                $a->setAttribute("onclick", $onclick);
            }
            $a->setValue($tab['params']['header']);
            $li->setValue($a->render());

            $ul->addHtml($li->render());
        }
    }
	$div->addHtml($ul->render());
	
    $tab_content=new TagRenderer("div",true);
    $tab_content->setAttribute("class", "tab-content");

    if(isset($this_tag_stack['tabs'])) {
        foreach ($this_tag_stack['tabs'] as $index => $tab) {
            $tab_div = new TagRenderer("div", true);
            $tab_div->setId("$id-tabs-$index");
            $tab_div->setAttribute("class", "tab-pane");
            if ($value == $index) {
                $tab_div->appendAttribute("class", "active");
            }
            if (($action !== null and $value == $index) or $action === null) {
                $tab_div->setValue($tab['content']);
            }
            $tab_content->addHtml($tab_div->render());
        }
    }
	
    $div->addHtml($tab_content->render());

	$s=$div->render();
	$s.=TagRenderer::renderHidden($id, $value);

	return $s;
}

