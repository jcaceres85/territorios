<?php

header("Content-Type: text/plain; charset=utf-8");


require_once("../framework/layer.php");

//header("Content-Type: text/plain; charset=utf-8");

$id = $_REQUEST["id"];

$labels = Layer::get_labels($id);

$obj = array('success'=> true, 'results'=>$labels);

$json = json_encode($obj); 

echo $json;

?>
