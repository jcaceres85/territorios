<?php

header("Content-Type: text/plain; charset=utf-8");


require_once("../framework/geovisor.php");

//header("Content-Type: text/plain; charset=UTF-8");


$headers = apache_request_headers();

$json = file_get_contents('php://input');

$data = json_decode($json);

$action = $data->action;


if($action == 'edit')
{
    $res = Geovisor::update($data);    
}
else if($action == 'new')
{
    $res = Geovisor::insert($data);
}


$obj = array('success'=> 1, 'results'=>$res);

$json = json_encode($obj); 

echo $json;

?>
