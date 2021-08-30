<?php

require_once("dbmanager.php");
require_once("dbmanager-geodata.php");

class Identify
{
	
   

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

    public static function find_layer_geom_column($geoserver_layer_id)
    {
        //Get geometry column
        $sql_find_geom_column_name = "SELECT column_name FROM information_schema.columns WHERE table_name='$geoserver_layer_id' AND (column_name = 'geom' OR column_name = 'the_geom');";

        $geom_column = DBManagerGeodata::execute_scalar($sql_find_geom_column_name);

        return $geom_column;
    }


    public static function find_layer_srid($geoserver_layer_id)
    {
        $geom_column = self::find_layer_geom_column($geoserver_layer_id);
        
        $sql_find_srid = "SELECT Find_SRID('public', '$geoserver_layer_id', '$geom_column');";

        $srid = DBManagerGeodata::execute_scalar($sql_find_srid);

        return $srid;
    }

    public static function get_geometry_by_layer_gid($geoserver_layer_id, $gid, $result_type = 'wkt')
    {
        $geom_column = self::find_layer_geom_column($geoserver_layer_id);

        $sql_query = 'SELECT ';
        if($result_type == 'wkt')
        {
            $sql_query.= "ST_AsText(ST_Transform($geom_column,4326)) ";    
        }
        else if($result_type == 'json')
        {
            $sql_query.= "ST_AsGeoJSON(ST_Transform($geom_column,4326)) ";    
        }

        $sql_query.="AS geom FROM \"$geoserver_layer_id\" WHERE fid = '$gid'";
        
        $objects = DBManagerGeodata::execute_query($sql_query);

        return $objects;
    }

   
   public static function query_count_by_geom($geoserver_layer_id, $geom)
    {

        $geom_column = self::find_layer_geom_column($geoserver_layer_id);

        #$srid = self::find_layer_srid(strtolower($geoserver_layer_id));
        $srid = self::find_layer_srid($geoserver_layer_id);

        $sql_query.= "SELECT COUNT(*) FROM \"$geoserver_layer_id\" WHERE ST_Intersects(ST_SetSRID($geom_column,$srid),ST_Transform(ST_SetSRID(ST_GeomFromText('$geom'),3857),$srid)) ";

        $objects = DBManagerGeodata::execute_scalar($sql_query, PGSQL_NUM);

        return $objects;
    }



    public static function query_by_geom($geoserver_layer_id, $geom, $limit = 25, $page = 0)
    {   
        $geom_column = self::find_layer_geom_column($geoserver_layer_id);

        #$srid = self::find_layer_srid(strtolower($geoserver_layer_id));
        $srid = self::find_layer_srid($geoserver_layer_id);

        $select_fields = self::get_query_fields_string($geoserver_layer_id);

        $sql_query.= "SELECT $select_fields FROM \"$geoserver_layer_id\" WHERE ST_Intersects(ST_SetSRID($geom_column,$srid),ST_Transform(ST_SetSRID(ST_GeomFromText('$geom'),3857),$srid)) LIMIT $limit OFFSET " . ($limit * $page);

        $objects = DBManagerGeodata::execute_query($sql_query, PGSQL_NUM);

        return $objects;
    }

}

?>


    
