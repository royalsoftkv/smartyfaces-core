<?php 

function smarty_block_sf_tooltip($params, $content, $template, &$repeat) {

	$tag="sf_tooltip";
	
	$attributes_list=array("id","rendered","class");
	$attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
	$attributes['for']=array(
		'required'=>true,
		'desc'=>'Id of component to which will be attached tooltip'		
	);
	$attributes['placement']=array(
		'required'=>false,
		'deafult'=>'auto',
		'desc'=>'Placement of tooltip. can be top | bottom | left | right | auto'		
	);
	if($params==null and $template==null) return $attributes;
	extract(SmartyFacesComponent::proccessAttributes($tag, $attributes, $params));
	
	if(!$rendered) return;
	
	if (is_null($content)) {
        return;
    } 
    
    $for=$params['for'];
    
    $div=new TagRenderer("div",true);
    $div->setAttribute("class", "ui-helper-hidden");
    $div->setId("$for-content");
    $div->setValue($content);
    $s=$div->render();
	
    if(SmartyFaces::$skin=="default") {
		$script='SF.attachTooltip("'.$for.'","'.$class. '");';
    } else {
		$script='SF.bs_attachTooltip("'.$for.'","'.$placement. '");';
    }
	$s.=SmartyFaces::addScript($script);
	
	echo $s;
}

?>