<?php

require_once("dbmanager.php");

class Layer
{

	
	public static function get_by_name($name)
	{
		$sql_query = "SELECT name, title_en FROM layers_layer ";
		$sql_query.= "WHERE name='$name'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}
	

    public static function search_by_title($title)
    {
        $sql_query = "SELECT name AS value, title_en AS name, title_en AS text FROM layers_layer ";
        $sql_query.= "WHERE upper(title_en) LIKE upper('%$title%')";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }


    public static function get_labels($layer_id)
    {
        $sql_query = "SELECT a.attribute,a.attribute_label as label,display_order FROM layers_attribute a ";
        $sql_query.= "WHERE a.layer_id='$layer_id' attribute_label <> '' AND attribute_label IS NOT NULL";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }

    public static function get_labels_by_geoserver_layer_id($geoserver_layer_id)
    {
        $sql_query = "SELECT a.attribute,a.attribute_label as label, display_order FROM layers_layer l INNER JOIN layers_attribute a ON l.resourcebase_ptr_id=a.layer_id ";
        $sql_query.= "WHERE l.name = '$geoserver_layer_id' AND attribute_label <> '' AND attribute_label IS NOT NULL ORDER BY display_order";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }

    public static function get_dictionary()
    {
        $sql_query = "SELECT l.name AS layer,a.attribute,a.attribute_label as label,display_order FROM layers_layer l INNER JOIN layers_attribute a ON l.resourcebase_ptr_id=a.layer_id ";
        $sql_query.= "WHERE attribute_label <> '' AND attribute_label IS NOT NULL";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }

}

?>


