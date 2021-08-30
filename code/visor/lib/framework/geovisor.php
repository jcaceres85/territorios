<?php

require_once("dbmanager.php");

class Geovisor
{

	
	public static function get($id)
	{
		$sql_query = "SELECT * FROM geovisor ";
		$sql_query.= "WHERE id='$id'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_by_slug($slug)
	{
		$sql_query = "SELECT * FROM geovisor ";
		$sql_query.= "WHERE slug='$slug'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_all()
	{
		$sql_query = "SELECT * FROM geovisor ";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

	public static function get_all_public()
	{
		$sql_query = "SELECT * FROM geovisor WHERE is_public=true ";

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
		$sql_query = "SELECT id, geovisor_id, category_id, geoserver_url, geoserver_layer_id, geoserver_style_id, name, ordr, active, queryable, zindex, transparency, geoserver_label_style_id, label_active, label_zoom_min, label_zoom_max, tiled FROM geovisor_layer ";
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

		$primary_color = $data->primary_color;
		$secondary_color = $data->secondary_color;
		$custom_css = $data->custom_css;

		$main_font = $data->main_font;
		$main_font_size = $data->main_font_size;
		$legend_font = $data->legend_font;
		$legend_font_size = $data->legend_font_size;


		$tree = $data->tree;

		$treeMenu = $data->treeMenu;

		$searches = $data->searches;

		DBManager::connect();

		DBManager::execute_nonquery_ts('BEGIN TRANSACTION');

		//UPDATE geovisor
		$sql_update_geovisor = "UPDATE geovisor SET name='$geovisor_name', title='$geovisor_title', url='', coord_ini='$geovisor_coord', max_extent='', zoom_ini='$geovisor_zoom', zoom_min='$geovisor_zoom_min', zoom_max='$geovisor_zoom_max', logo='$img_logo_src', message_ini='$geovisor_msj_ini', base_layer='$base_layer', slug='$geovisor_slug', is_public='$geovisor_is_public', primary_color='$primary_color', secondary_color='$secondary_color', custom_css='$custom_css', main_font='$main_font', main_font_size='$main_font_size', legend_font='$legend_font', legend_font_size='$legend_font_size'  WHERE id='$geovisor_id';";

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

				$geoserver_label_style_id =$obj->geoserver_label_style_id;
				$label_active = $obj->label_active != null? $obj->label_active: 'f';
				$label_zoom_min = $obj->label_zoom_min!=''?$obj->label_zoom_min: 'null';
				$label_zoom_max = $obj->label_zoom_max!=''?$obj->label_zoom_max: 'null';

				$tiled = $obj->tiled != null? $obj->tiled: 'f';
				//geoserver_label_style_id

				$sql_insert_geovisor_layer = "INSERT INTO geovisor_layer(id, geovisor_id, category_id, geoserver_url, geoserver_layer_id, geoserver_style_id, name, ordr, active, queryable, zindex, transparency, geoserver_label_style_id, label_active, label_zoom_min, label_zoom_max, tiled) VALUES ('$id_count_layer', '$geovisor_id', '$category_id', '$geoserver_url', '$geoserver_layer_id', '$geoserver_style_id', '$lyr_name', '$id_count_layer', '$active', '$queryable', '$zindex', '$transparency','$geoserver_label_style_id', '$label_active', $label_zoom_min, $label_zoom_max, '$tiled');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_layer);

				$id_count_layer++;

				$count_updates++;

			}
		}



		//UPDATE geovisor_search
		$sql_delete_searches = "DELETE FROM geovisor_search WHERE geovisor_id = '$geovisor_id'";
		DBManager::execute_nonquery_ts($sql_delete_searches);

		for($i=0; $i<count($searches); $i++)
		{
			$s = $searches[$i];

			$search_id = $s->search_id;
			$layer_id = $s->layer_id;
			$attribute = $s->attribute;
			$type = $s->type;
			$search_text = $s->search_text;

			$sql_insert_geovisor_search = "INSERT INTO geovisor_search(id, geovisor_id, layer_id, attribute, type, search_text) VALUES ('$search_id', '$geovisor_id', '$layer_id', '$attribute', '$type', '$search_text');";

			DBManager::execute_nonquery_ts($sql_insert_geovisor_search);
		}


		//UPDATE geovisor_search
		$sql_delete_menus = "DELETE FROM geovisor_menu WHERE geovisor_id = '$geovisor_id'";
		DBManager::execute_nonquery_ts($sql_delete_menus);

		for($i=0; $i<count($treeMenu); $i++)
		{
			$obj = $treeMenu[$i];

			if($obj->type != null)
			{
				$menu_id = $obj->id;
				$menu_type = $obj->type;
				$menu_name = $obj->name;
				$menu_content = $obj->content;
				$menu_parent_id = $obj->parent_id;
				
				$sql_insert_geovisor_menu = "INSERT INTO geovisor_menu(id, geovisor_id, type, name, content, parent_id) VALUES ('$menu_id', '$geovisor_id', '$menu_type', '$menu_name', '$menu_content', '$menu_parent_id');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_menu);
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

		$primary_color = $data->primary_color;
		$secondary_color = $data->secondary_color;
		$custom_css = $data->custom_css;

		$main_font = $data->main_font;
		$main_font_size = $data->main_font_size;
		$legend_font = $data->legend_font;
		$legend_font_size = $data->legend_font_size;

		$tree = $data->tree;

		DBManager::connect();

		DBManager::execute_nonquery_ts('BEGIN TRANSACTION');

		$sql_query_next_val = 'SELECT max(id)+1 AS id FROM geovisor;';

		$geovisor_id = DBManager::execute_scalar_ts($sql_query_next_val);


		//UPDATE geovisor
		
		$sql_insert_geovisor = "INSERT INTO geovisor(id, name, title, url, coord_ini, max_extent, zoom_ini, zoom_min, zoom_max, logo, message_ini, dictionary, base_layer, slug, is_public, primary_color, secondary_color, custom_css, main_font, main_font_size, legend_font, legend_font_size) VALUES ('$geovisor_id', '$geovisor_name', '$geovisor_title', '', '$geovisor_coord', '$geovisor_zoom', '$geovisor_zoom_min', '$geovisor_zoom_min', '$geovisor_zoom_max', '$img_logo_src', '$geovisor_msj_ini', '', '$base_layer', '$geovisor_slug', '$geovisor_is_public', '$primary_color', '$secondary_color', '$custom_css', '$main_font', '$main_font_size', '$legend_font', '$legend_font_size');";

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

				$geoserver_label_style_id = $obj->geoserver_label_style_id;
				$label_active = $obj->label_active != null? $obj->label_active: false;
				$label_zoom_min = $obj->label_zoom_min!=''?$obj->label_zoom_min: 'null';
				$label_zoom_max = $obj->label_zoom_max!=''?$obj->label_zoom_max: 'null';
				//geoserver_label_style_id
				$tiled = $obj->tiled != null? $obj->tiled: 'f';


				$sql_insert_geovisor_layer = "INSERT INTO geovisor_layer(id, geovisor_id, category_id, geoserver_url, geoserver_layer_id, geoserver_style_id, name, ordr, active, queryable, zindex, transparency, geoserver_label_style_id, label_active, label_zoom_min, label_zoom_max, tiled) VALUES ('$id_count_layer', '$geovisor_id', '$category_id', '$geoserver_url', '$geoserver_layer_id', '$geoserver_style_id', '$lyr_name', '$id_count_layer', '$active', '$queryable', '$zindex', '$transparency','$geoserver_label_style_id', '$label_active', $label_zoom_min, $label_zoom_max, '$tiled');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_layer);

				$id_count_layer++;

				$count_updates++;

			}
		}


		for($i=0; $i<count($searches); $i++)
		{
			$s = $searches[$i];

			$search_id = $s->search_id;
			$layer_id = $s->layer_id;
			$attribute = $s->attribute;
			$type = $s->type;
			$search_text = $s->search_text;

			$sql_insert_geovisor_search = "INSERT INTO geovisor_search(id, geovisor_id, layer_id, attribute, type, search_text) VALUES ('$search_id', '$geovisor_id', '$layer_id', '$attribute', '$type', '$search_text');";

			DBManager::execute_nonquery_ts($sql_insert_geovisor_search);
		}


		for($i=0; $i<count($treeMenu); $i++)
		{
			$obj = $treeMenu[$i];

			if($obj->type != null)
			{
				$menu_id = $obj->id;
				$menu_type = $obj->type;
				$menu_name = $obj->name;
				$menu_content = $obj->content;
				$menu_parent_id = $obj->parent_id;
				
				$sql_insert_geovisor_menu = "INSERT INTO geovisor_menu(id, geovisor_id, type, name, content, parent_id) VALUES ('$menu_id', '$geovisor_id', '$menu_type', '$menu_name', '$menu_content', '$menu_parent_id');";

				DBManager::execute_nonquery_ts($sql_insert_geovisor_menu);
			}

			
		}

		
		DBManager::execute_nonquery_ts('COMMIT TRANSACTION');
		

		DBManager::close();

		return $count_updates;
	}




}

?>


