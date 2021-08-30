<?php

require_once("dbmanager.php");

class Menu
{

	public static function get_menu($geovisor_id, $menu_id)
	{
		$sql_query = "SELECT id, name, geovisor_id, type, content, parent_id FROM geovisor_menu ";
		$sql_query.= "WHERE geovisor_id='$geovisor_id' AND id='$menu_id'";

		$objects = DBManager::execute_query($sql_query);

		if(count($objects) > 0)
			return $objects[0];
		return null;
	}

	public static function get_parent_menu($geovisor_id)
	{
		$sql_query = "SELECT id, name, geovisor_id, type, content, parent_id FROM geovisor_menu ";
		$sql_query.= "WHERE parent_id is null AND geovisor_id='$geovisor_id' ORDER BY ordr";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_child_menu($geovisor_id, $parent_id)
	{
		$sql_query = "SELECT id, name, geovisor_id, type, content, parent_id FROM geovisor_menu ";
		$sql_query.= "WHERE parent_id = $parent_id AND geovisor_id='$geovisor_id'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}






}

?>


