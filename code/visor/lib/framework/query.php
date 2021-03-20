<?php

require_once("dbmanager.php");

class Query
{
	
    public static function get_attribute_list($layer)
    {

    }

	public static function select_by_point($layer, $x, $y)
	{

		$sql_query = "SELECT * FROM " . $layer . " l ";
		$sql_query.= "WHERE ST_Intersects(ST_Point(".$x.",".$y."),l.geom)";

        echo $sql_query;

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}
	

}

?>


    