<?php

header("Content-Type: text/plain; charset=utf-8");

require_once("../framework/layer.php");
require_once("../framework/search.php");
require_once("../framework/identify.php");


$geovisor_id = $_REQUEST["geovisor_id"];
$geoserver_layer_id = $_REQUEST["geoserver_layer_id"];
$feature_id = $_REQUEST["feature_id"];


$results  = Identify::get_geometry_by_layer_gid($geoserver_layer_id, $feature_id);

$obj = array('success'=> true, 'results'=>$results);





$json = json_encode($obj); 

echo $json;

?>