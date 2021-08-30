<?php

require_once("dbmanager.php");

class Style
{

	
	public static function get_by_name($name)
	{
		$sql_query = "SELECT name, sld_title FROM layers_style ";
		$sql_query.= "WHERE name='$name'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}
	

    public static function search_by_title($title)
    {
        $sql_query = "SELECT name AS value, sld_title ||' (' || name || ')' AS name,  sld_title ||' (' || name || ')' AS text FROM layers_style ";
        $sql_query.= "WHERE upper(sld_title) LIKE upper('%$title%') OR upper(name) LIKE upper('%$title%')";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }



}

?>


