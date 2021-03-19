<?php

require_once("dbmanager-geodata.php");

class Identify
{
	

    public static function get_geometry_by_layer_gid($geoserver_layer_id, $gid, $result_type = 'wkt')
    {
        $sql_query = 'SELECT ';
        if($result_type == 'wkt')
        {
            $sql_query.= "ST_AsText(ST_Transform(geom,4326)) ";    
        }
        else if($result_type == 'json')
        {
            $sql_query.= "ST_AsGeoJSON(ST_Transform(geom,4326)) ";    
        }

        $sql_query.="AS geom FROM \"$geoserver_layer_id\" WHERE fid = '$gid'";
    	
    	$objects = DBManagerGeodata::execute_query($sql_query);

    	return $objects;
    }


    


}

?>


    