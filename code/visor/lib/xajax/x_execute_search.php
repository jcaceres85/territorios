<?php

header("Content-Type: text/plain; charset=utf-8");

require_once("../framework/layer.php");
require_once("../framework/search.php");


$geovisor_id = $_REQUEST["geovisor_id"];
$search_id = $_REQUEST["search_id"];
$search_value = $_REQUEST["search_value"];

if(isset($_REQUEST["limit"]))
	$limit = $_REQUEST["limit"];
else
	$limit = 25;

if(isset($_REQUEST["page"]))
	$page = $_REQUEST["page"];
else
	$page = 0;

$searches = Search::get_search_by_geovisor_search_id($geovisor_id, $search_id);

if(count($searches) > 0)
{

	$search = $searches[0];

	$search_id = $search['search_id'];
	$geoserver_layer_id = $search['geoserver_layer_id'];
	$attribute = $search['attribute'];
	$attribute_label = $search['attribute_label'];
	$type = $search['type'];


	$headers  = Search::get_query_fields($geoserver_layer_id);

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


	if($type == 'text')
	{
		$results = Search::execute_search_text($geoserver_layer_id, $attribute, $search_value, $limit, $page);

		$count = Search::execute_count_search_text($geoserver_layer_id, $attribute, $search_value);
	}
	else if($type == 'select')
	{
		$results = Search::execute_search_list($geoserver_layer_id, $attribute, $search_value, $limit, $page);

		$count = Search::execute_count_search_list($geoserver_layer_id, $attribute, $search_value);
	}
	else if($type == 'radio')
	{
		$results = Search::execute_search_radio($geoserver_layer_id, $attribute, $search_value, $limit, $page);

		$count = Search::execute_count_search_radio($geoserver_layer_id, $attribute, $search_value);
	}

	$obj = array('success'=> true, 'count'=> $count, 'headers'=>$headers, 'results'=>$results);
}
else
{
	$obj = array('success'=> false, 'count'=> 0, 'message'=> 'Error: No existe la consulta.');
}




$json = json_encode($obj); 

echo $json;

?>