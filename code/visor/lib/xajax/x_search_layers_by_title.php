<?php

header("Content-Type: text/plain; charset=utf-8");

require_once("../framework/layer.php");


$title = $_REQUEST["title"];

$layers = Layer::search_by_title($title);

$obj = array('success'=> true, 'results'=>$layers);

$json = json_encode($obj); 

echo $json;

?>