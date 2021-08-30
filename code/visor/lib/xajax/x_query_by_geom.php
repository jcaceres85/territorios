<?php

header("Content-Type: text/plain; charset=utf-8");

require_once("../framework/layer.php");
require_once("../framework/identify.php");


$geoserver_layer_id = $_REQUEST["geoserver_layer_id"];

$geom = $_REQUEST["geom"];

if(isset($_REQUEST["limit"]))
	$limit = $_REQUEST["limit"];
else
	$limit = 25;

if(isset($_REQUEST["page"]))
	$page = $_REQUEST["page"];
else
	$page = 0;


$count = Identify::query_count_by_geom($geoserver_layer_id, $geom);

$results = Identify::query_by_geom($geoserver_layer_id, $geom, $limit, $page);

$headers  = Identify::get_query_fields($geoserver_layer_id);

$hasGid = false;

for($i=0; $i<count($headers); $i++)
{
	if($headers[$i]['attribute'] == 'gid')
	{
		$hasGid = true;
		break;
	}
}

if(!$hasGid)
{
	$headers = array_merge( [["attribute" => "gid", "label"=> "ID", "display_order"=>"0"]], $headers);
}


$obj = array('success'=> true, 'count'=> $count, 'headers'=>$headers, 'results'=>$results);

$json = json_encode($obj); 

echo $json;

?>