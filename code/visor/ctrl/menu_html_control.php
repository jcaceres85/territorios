<?php

require_once("../lib/framework/menu.php");

$geovisor_id = $_REQUEST["geovisor_id"];
$menu_id = $_REQUEST["menu_id"];


$menu = Menu::get_menu($geovisor_id, $menu_id);


echo '<div style="padding: 10px;">';

echo $menu['content'];

echo '</div>';

?>