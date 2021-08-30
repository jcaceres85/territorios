<?php

require_once("dbmanager.php");
require_once("dbmanager-geodata.php");

class Search
{
	
    public static function get_search_by_geovisor_id($geovisor_id)
    {

		$sql_query = "SELECT s.id AS search_id, s.geovisor_id, s.layer_id, s.attribute, la.attribute_label, s.type, s.search_text, l.name AS geoserver_layer_id, l.title_en AS layer_name FROM geovisor_search s "; 
		$sql_query.= "INNER JOIN layers_layer l ON (s.layer_id=l.resourcebase_ptr_id) ";
		$sql_query.= "INNER JOIN layers_attribute la ON (s.attribute=la.attribute AND l.resourcebase_ptr_id=la.layer_id) ";
		$sql_query.= "WHERE s.geovisor_id=$geovisor_id ORDER BY search_id";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
    }


    public static function get_search_by_geovisor_search_id($geovisor_id, $search_id)
    {

		$sql_query = "SELECT s.id AS search_id, s.geovisor_id, s.layer_id, s.attribute, la.attribute_label, s.type, s.search_text, l.name AS geoserver_layer_id, l.title_en AS layer_name FROM geovisor_search s "; 
		$sql_query.= "INNER JOIN layers_layer l ON (s.layer_id=l.resourcebase_ptr_id) ";
        $sql_query.= "INNER JOIN layers_attribute la ON (s.attribute=la.attribute AND l.resourcebase_ptr_id=la.layer_id) ";
		$sql_query.= "WHERE s.geovisor_id=$geovisor_id AND s.id=$search_id ORDER BY search_id";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
    }


    public static function get_layers_with_searches($geovisor_id)
    {

        $sql_query = "SELECT s.id AS search_id, s.geovisor_id, s.layer_id, s.attribute, la.attribute_label, s.type, s.search_text, l.name AS geoserver_layer_id, l.title_en AS layer_name FROM geovisor_search s "; 
        $sql_query.= "INNER JOIN layers_layer l ON (s.layer_id=l.resourcebase_ptr_id) ";
        $sql_query.= "INNER JOIN layers_attribute la ON (s.attribute=la.attribute AND l.resourcebase_ptr_id=la.layer_id) ";
        $sql_query.= "WHERE s.geovisor_id=$geovisor_id ORDER BY search_id";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }


    

    public static function get_query_fields($geoserver_layer_id)
    {
    	$sql_query = "SELECT a.attribute,a.attribute_label as label, display_order FROM layers_layer l INNER JOIN layers_attribute a ON l.resourcebase_ptr_id=a.layer_id ";
        $sql_query.= "WHERE l.name = '$geoserver_layer_id' AND attribute_label <> '' AND attribute_label IS NOT NULL ORDER BY display_order";

    	$attributes = DBManager::execute_query($sql_query);

    	return $attributes;
    }

    public static function get_query_fields_string($geoserver_layer_id)
    {
    	
    	$attributes = self::get_query_fields($geoserver_layer_id);

    	if(count($attributes) == 0)
    	{
    		return '*';
    	}

    	$select_fields = '';

    	$hasGid = false;

    	for($i=0; $i<count($attributes); $i++)
    	{
    		$attr = $attributes[$i];

    		if($attr == 'gid' || $attr == 'fid')
    			$hasGid = true;

    		$select_fields.= '"'. $attr['attribute'] . '" AS "' . $attr['label'] . '",';
    	}


    	$select_fields = ($hasGid?'':'fid AS gid,') . substr($select_fields, 0, strlen($select_fields) - 1);

    	//$select_fields.='ST_AsText(ST_Envelope(ST_Transform(geom,4326))) AS geom';

    	return $select_fields;
    }


    public static function get_layer_attribute_list_values($layer_id, $attribute)
    {

        $sql_query = "SELECT DISTINCT \"$attribute\" FROM \"$layer_id\" ORDER BY \"$attribute\"";


        $objects = DBManagerGeodata::execute_query($sql_query);

        return $objects;
    }

    public static function execute_count_search_text($geoserver_layer_id, $attribute, $value)
    {

    	$sql_query = "SELECT COUNT(*) FROM \"$geoserver_layer_id\" WHERE \"$attribute\" LIKE '%$value%'";

    	$objects = DBManagerGeodata::execute_scalar($sql_query);

    	return $objects;
    }

    public static function execute_search_text($geoserver_layer_id, $attribute, $value, $limit = 25, $page = 0)
    {

    	$select_fields = self::get_query_fields_string($geoserver_layer_id);

    	$sql_query = "SELECT $select_fields FROM \"$geoserver_layer_id\" WHERE \"$attribute\" LIKE '%$value%' LIMIT $limit OFFSET " . ($limit * $page);

    	$objects = DBManagerGeodata::execute_query($sql_query, PGSQL_NUM);

    	return $objects;
    }

    public static function execute_count_search_list($geoserver_layer_id, $attribute, $value)
    {

    	$sql_query = "SELECT COUNT(*) FROM \"$geoserver_layer_id\" WHERE \"$attribute\" = '$value'";

    	$objects = DBManagerGeodata::execute_scalar($sql_query);

    	return $objects;
    }

    public static function execute_search_list($geoserver_layer_id, $attribute, $value,  $limit = 25, $page = 0)
    {

    	$select_fields = self::get_query_fields_string($geoserver_layer_id);

    	$sql_query = "SELECT $select_fields FROM \"$geoserver_layer_id\" WHERE \"$attribute\" = '$value' LIMIT $limit OFFSET " . ($limit * $page);

    	$objects = DBManagerGeodata::execute_query($sql_query, PGSQL_NUM);

    	return $objects;
    }


    public static function execute_count_search_radio($geoserver_layer_id, $attribute, $value)
    {

    	$sql_query = "SELECT COUNT(*) FROM \"$geoserver_layer_id\" WHERE \"$attribute\" = $value";

    	$objects = DBManagerGeodata::execute_scalar($sql_query);

    	return $objects;
    }

    public static function execute_search_radio($geoserver_layer_id, $attribute, $value,  $limit = 25, $page = 0)
    {

    	$select_fields = self::get_query_fields_string($geoserver_layer_id);

    	$sql_query = "SELECT $select_fields FROM \"$geoserver_layer_id\" WHERE \"$attribute\" = $value LIMIT $limit OFFSET " . ($limit * $page);

    	$objects = DBManager::execute_query($sql_query, PGSQL_NUM);

    	return $objects;
    }


    


}

?>


    