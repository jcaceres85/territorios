<?php

require_once("dbmanager.php");

class Geovisor
{

	
	public static function get($id)
	{
		$sql_query = "SELECT id, name, title, url, coord_ini, max_extent, zoom_ini, zoom_min, zoom_max, logo, message_ini, dictionary, base_layer, slug, is_public FROM geovisor ";
		$sql_query.= "WHERE id='$id'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_by_slug($slug)
	{
		$sql_query = "SELECT id, name, title, url, coord_ini, max_extent, zoom_ini, zoom_min, zoom_max, logo, message_ini, dictionary, base_layer, slug, is_public FROM geovisor ";
		$sql_query.= "WHERE slug='$slug'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_all()
	{
		$sql_query = "SELECT id, name, title, url, coord_ini, max_extent, zoom_ini, zoom_min, zoom_max, logo, message_ini, dictionary, base_layer, slug, is_public FROM geovisor ";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_all_public()
	{
		$sql_query = "SELECT id, name, title, url, coord_ini, max_extent, zoom_ini, zoom_min, zoom_max, logo, message_ini, dictionary, base_layer, slug, is_public FROM geovisor WHERE is_public=true ";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_categories($geovisor_id)
	{
		$sql_query = "SELECT id, geovisor_id, name, ordr, parent_id, expanded FROM geovisor_category ";
		$sql_query.= "WHERE geovisor_id='$geovisor_id' ORDER BY ordr";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}


	public static function get_parent_categories($geovisor_id)
	{
		$sql_query = "SELECT id, geovisor_id, name, ordr, expanded FROM geovisor_category ";
		$sql_query.= "WHERE parent_id is null AND geovisor_id='$geovisor_id' ORDER BY ordr";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_child_categories($geovisor_id, $parent_category_id)
	{
		$sql_query = "SELECT id, geovisor_id, name, ordr, expanded FROM geovisor_category ";
		$sql_query.= "WHERE parent_id = $parent_category_id AND geovisor_id='$geovisor_id' ORDER BY ordr";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}


	public static function get_layers_by_geovisor_category($geovisor_id, $category_id)
	{
		$sql_query = "SELECT id, geovisor_id, category_id, geoserver_url, geoserver_layer_id, geoserver_style_id, name, ordr, active, queryable, zindex, transparency FROM geovisor_layer ";
		$sql_query.= "WHERE geovisor_id='$geovisor_id' AND category_id='$category_id' ORDER BY ordr";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	


	public static function update($data)
	{

		$geovisor_id = $data->geovisor_id;
		$geovisor_name = $data->geovisor_name;
		$geovisor_title = $data->geovisor_title;
		$geovisor_slug = $data->geovisor_slug;
		$geovisor_is_public = $data->geovisor_is_public;
		$geovisor_coord = $data->geovisor_coord;
		$geovisor_zoom = $data->geovisor_zoom;
		$geovisor_zoom_min = $data->geovisor_zoom_min;
		$geovisor_zoom_max = $data->geovisor_zoom_max;
		$geovisor_msj_ini = $data->geovisor_msj_ini;
		$img_logo_src = $data->img_logo_src;
		$base_layer = $data->base_layer;


		$tree = $data->tree;

		DBManager::connect();

		DBManager::execute_nonquery_ts('BEGIN TRANSACTION');

		//UPDATE geovisor
		$sql_update_geovisor = "UPDATE geovisor SET name='$geovisor_name', title='$geovisor_title', url='', coord_ini='$geovisor_coord', max_extent='', zoom_ini='$geovisor_zoom', zoom_min='$geovisor_zoom_min', zoom_max='$geovisor_zoom_max', logo='$img_logo_src', message_ini='$geovisor_msj_ini', base_layer='$base_layer', slug='$geovisor_slug', is_public='$geovisor_is_public' WHERE id='$geovisor_id';";

		DBManager::execute_nonquery_ts($sql_update_geovisor);

		//UPDATE geovisor_category
		$sql_delete_categories = "DELETE FROM geovisor_category WHERE geovisor_id = '$geovisor_id'";
		DBManager::execute_nonquery_ts($sql_delete_categories);

		//UPDATE geovisor_layer
		$sql_delete_layers = "DELETE FROM geovisor_layer WHERE geovisor_id = '$geovisor_id'";
		DBManager::execute_nonquery_ts($sql_delete_layers);

		$id_count_category = 1;
		$id_count_layer = 1;


		$count_updates = 1;

		for($i=1; $i<count($tree); $i++)
		{
			$obj = $tree[$i];

			if($obj->type == "category")
			{
				$category_name = $obj->category_name;
				$expanded = $obj->expanded;
				$parent_category_id = $obj->parent_category_id;

				$sql_insert_geovisor_category = "INSERT INTO geovisor_category(id, geovisor_id, name, ordr, parent_id, expanded) VALUES ('$id_count_category', '$geovisor_id', '$category_name', '$id_count_category', '$parent_category_id', '$expanded');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_category);

				$id_count_category++;

				$count_updates++;

			}
			else if($obj->type == "layer")
			{
				$geoserver_layer_id = $obj->geoserver_layer_id;
				$lyr_name = $obj->lyr_name;
				$geoserver_url = $obj->geoserver_url;
				$geoserver_style_id = $obj->geoserver_style_id;
				$transparency = $obj->transparency;
				$zindex = $obj->zindex;
				$active = $obj->active;
				$queryable = $obj->queryable;
				$category_id = $obj->category_id;


				$sql_insert_geovisor_layer = "INSERT INTO geovisor_layer(id, geovisor_id, category_id, geoserver_url, geoserver_layer_id, geoserver_style_id, name, ordr, active, queryable, zindex, transparency) VALUES ('$id_count_layer', '$geovisor_id', '$category_id', '$geoserver_url', '$geoserver_layer_id', '$geoserver_style_id', '$lyr_name', '$id_count_layer', '$active', '$queryable', '$zindex', '$transparency');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_layer);

				$id_count_layer++;

				$count_updates++;

			}
		}


		
		DBManager::execute_nonquery_ts('COMMIT TRANSACTION');
		

		DBManager::close();

		return $count_updates;
	}


	public static function insert($data)
	{

		$geovisor_name = $data->geovisor_name;
		$geovisor_title = $data->geovisor_title;
		$geovisor_slug = $data->geovisor_slug;
		$geovisor_is_public = $data->geovisor_is_public;
		$geovisor_coord = $data->geovisor_coord;
		$geovisor_zoom = $data->geovisor_zoom;
		$geovisor_zoom_min = $data->geovisor_zoom_min;
		$geovisor_zoom_max = $data->geovisor_zoom_max;
		$geovisor_msj_ini = $data->geovisor_msj_ini;
		$img_logo_src = $data->img_logo_src;
		$base_layer = $data->base_layer;


		$tree = $data->tree;

		DBManager::connect();

		DBManager::execute_nonquery_ts('BEGIN TRANSACTION');

		$sql_query_next_val = 'SELECT max(id)+1 AS id FROM geovisor;';

		$geovisor_id = DBManager::execute_scalar_ts($sql_query_next_val);


		//UPDATE geovisor
		
		$sql_insert_geovisor = "INSERT INTO geovisor(id, name, title, url, coord_ini, max_extent, zoom_ini, zoom_min, zoom_max, logo, message_ini, dictionary, base_layer, slug, is_public) VALUES ('$geovisor_id', '$geovisor_name', '$geovisor_title', '', '$geovisor_coord', '$geovisor_zoom', '$geovisor_zoom_min', '$geovisor_zoom_min', '$geovisor_zoom_max', '$img_logo_src', '$geovisor_msj_ini', '', '$base_layer', '$geovisor_slug', '$geovisor_is_public');";

		DBManager::execute_nonquery_ts($sql_insert_geovisor);

		//UPDATE geovisor_category
		$sql_delete_categories = "DELETE FROM geovisor_category WHERE geovisor_id = '$geovisor_id'";
		DBManager::execute_nonquery_ts($sql_delete_categories);

		//UPDATE geovisor_layer
		$sql_delete_layers = "DELETE FROM geovisor_layer WHERE geovisor_id = '$geovisor_id'";
		DBManager::execute_nonquery_ts($sql_delete_layers);

		$id_count_category = 1;
		$id_count_layer = 1;


		$count_updates = 1;

		for($i=1; $i<count($tree); $i++)
		{
			$obj = $tree[$i];

			if($obj->type == "category")
			{
				$category_name = $obj->category_name;
				$expanded = $obj->expanded;
				$parent_category_id = $obj->parent_category_id;

				$sql_insert_geovisor_category = "INSERT INTO geovisor_category(id, geovisor_id, name, ordr, parent_id, expanded) VALUES ('$id_count_category', '$geovisor_id', '$category_name', '$id_count_category', '$parent_category_id', '$expanded');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_category);

				$id_count_category++;

				$count_updates++;

			}
			else if($obj->type == "layer")
			{
				$geoserver_layer_id = $obj->geoserver_layer_id;
				$lyr_name = $obj->lyr_name;
				$geoserver_url = $obj->geoserver_url;
				$geoserver_style_id = $obj->geoserver_style_id;
				$transparency = $obj->transparency;
				$zindex = $obj->zindex;
				$active = $obj->active;
				$queryable = $obj->queryable;
				$category_id = $obj->category_id;


				$sql_insert_geovisor_layer = "INSERT INTO geovisor_layer(id, geovisor_id, category_id, geoserver_url, geoserver_layer_id, geoserver_style_id, name, ordr, active, queryable, zindex, transparency) VALUES ('$id_count_layer', '$geovisor_id', '$category_id', '$geoserver_url', '$geoserver_layer_id', '$geoserver_style_id', '$lyr_name', '$id_count_layer', '$active', '$queryable', '$zindex', '$transparency');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_layer);

				$id_count_layer++;

				$count_updates++;

			}
		}


		
		DBManager::execute_nonquery_ts('COMMIT TRANSACTION');
		

		DBManager::close();

		return $count_updates;
	}




}

?>


