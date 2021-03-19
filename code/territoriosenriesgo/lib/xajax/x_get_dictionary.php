<?php

require_once("../framework/layer.php");

header("Content-Type: text/plain; charset=utf-8");

$dictionary = Layer::get_dictionary();

$obj = array('success'=> true, 'results'=>$dictionary);

$json = json_encode($obj); 

echo $json;

?>