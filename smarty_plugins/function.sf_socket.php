<?php

function smarty_function_sf_socket($params, $template)
{
    $tag="sf_socket";

    $id=null;
    $type=null;
    $host=null;
    $port=null;
    $server=null;

    $attributes_list=array('id','action');
    $attributes=SmartyFacesComponent::resolveAttributtes($attributes_list);
    $attributes['type']=array(
        'required'=>true,
        'default'=>'client',
        'desc'=>'Type of socket component. If server new server socket will be started'
    );
    $attributes['host']=array(
        'required'=>false,
        'default'=>'localhost',
        'desc'=>'Socket server host'
    );
    $attributes['port']=array(
        'required'=>false,
        'default'=>'80',
        'desc'=>'Socket server port'
    );
    $attributes['server']=array(
        'required'=>false,
        'default'=>'socket',
        'desc'=>'Id of created server to connect'
    );
    $attributes['event']=array(
        'required'=>false,
        'default'=>'connect',
        'desc'=>'Event on socket to listen'
    );
    if($params==null and $template==null) return $attributes;
    $attributes_values=SmartyFacesComponent::proccessAttributes($tag, $attributes, $params);
    extract($attributes_values);

    if($type=='server') {
        SmartyFacesContext::startSocket($host, $port);
    }
}
