<?php

header("Content-Type: text/plain; charset=utf-8");

require_once("../framework/layer.php");
require_once("../framework/search.php");
require_once("../framework/identify.php");


$geovisor_id = $_REQUEST["geovisor_id"];
$search_id = $_REQUEST["search_id"];
$feature_id = $_REQUEST["feature_id"];


$searches = Search::get_search_by_geovisor_search_id($geovisor_id, $search_id);

if(count($searches) > 0)
{

	$search = $searches[0];

	$geoserver_layer_id = $search['geoserver_layer_id'];

	$results  = Identify::get_geometry_by_layer_gid($geoserver_layer_id, $feature_id);


	$obj = array('success'=> true, 'results'=>$results);
}
else
{
	$obj = array('success'=> false, 'count'=> 0, 'message'=> 'Error: No existe la consulta.');
}




$json = json_encode($obj); 

echo $json;

?>