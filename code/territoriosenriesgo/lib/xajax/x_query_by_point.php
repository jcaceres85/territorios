<?php

require_once("../framework/query.php");

header("Content-Type: text/plain; charset=utf-8");


$layer = $_REQUEST["layer"];
$x = $_REQUEST["x"];
$y = $_REQUEST["y"];
$srid = $_REQUEST["srid"];

$objects = Query::select_by_point($layer, $x, $y);

if (!function_exists('json_encode')) {
    echo 'PHP not compiled with json support', PHP_EOL;
}

//$json_tickets = json_encode(utf8ize($tickets)); //Producción
$json_objects = json_encode($objects); //Desarrollo

echo $json_objects;

?>