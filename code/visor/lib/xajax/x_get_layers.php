<?php

require_once("../framework/ticket_manager.php");

header("Content-Type: text/plain; charset=utf-8");


$depto = $_REQUEST["depto"];
$estatus = $_REQUEST["estatus"];
$fecha_ini = $_REQUEST["fecha_ini"];
$fecha_fin = $_REQUEST["fecha_fin"];

$tickets = TicketManager::get_tickets($depto, $estatus, $fecha_ini, $fecha_fin);

if (!function_exists('json_encode')) {
    echo 'PHP not compiled with json support', PHP_EOL;
}

//$json_tickets = json_encode(utf8ize($tickets)); //Producción
$json_tickets = json_encode($tickets); //Desarrollo

echo $json_tickets;

?>