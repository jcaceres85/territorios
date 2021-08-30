<?php
require_once("config.php");
require_once("lib/framework/geovisor.php");
require_once("lib/framework/layer.php");
require_once("lib/framework/search.php");
require_once("lib/framework/menu.php");
require_once("lib/framework/oauth.php");


$geovisor_id = $_REQUEST["geovisor_id"];
$action = $_REQUEST["action"];
$access_token = $_REQUEST["access_token"];

$legend_fonts = ['Dialog','Dialog.bold','Dialog.italic','Dialog.bolditalic','Monospaced','Monospaced.bold', 'Monospaced.italic', 'Monospaced.bolditalic','SanSerif', 'SanSerif.bold','SanSerif.italic','SanSerif.bolditalic','Serif','Serif.bold','Serif.italic','Serif.bolditalic','Ubuntu'];


if($access_token == null || $access_token == '')
{
  header("Location: " . $MAIN_SITE_URL);
  die();
}


$user_data = OAuth::get_user_data_by_accesstoken($access_token);

if(sizeof($user_data)>0)
{
	if($user_data[0]['is_superuser'] == 'f')
	{
		header("Location: " . $MAIN_SITE_URL);
	  	die();	
	}
}
else
{
	header("Location: " . $MAIN_SITE_URL);
	die();
}


$session_expired = OAuth::is_session_expired($access_token);

if($session_expired == 'f')
{

		if($action == 'edit')
		{
			$g_res = Geovisor::get($geovisor_id);

			$primary_color = '#0d5c85';
			$secondary_color = '#247aa7';
			$main_font = 'Helvetica Neue Light, HelveticaNeue-Light, Helvetica Neue, Calibri, Helvetica, Arial, sans-serif;';
			$main_font_size = 9;
			$legend_font = 'SanSerif.bold';
			$legend_font_size = '9';

			if(count($g_res)>0)
			{
				$geovisor = $g_res[0];

				$geovisor_id = $geovisor['id'];
				$name = $geovisor['name'];
				$title = $geovisor['title'];
				$coord_ini = $geovisor['coord_ini'];
				$zoom_ini = $geovisor['zoom_ini'];
				$zoom_min = $geovisor['zoom_min'];
				$zoom_max = $geovisor['zoom_max'];
				$message_ini = $geovisor['message_ini'];
				$logo = $geovisor['logo'];
				$slug = $geovisor['slug'];
				$is_public = $geovisor['is_public'];
				$base_layer = $geovisor['base_layer'];

				if($geovisor['primary_color'] != null)
					$primary_color = $geovisor['primary_color'];
				if($geovisor['secondary_color'] != null)
					$secondary_color = $geovisor['secondary_color'];
				$custom_css = $geovisor['custom_css'];
			
				if($geovisor['main_font'] != null)
					$main_font = $geovisor['main_font'];
				if($geovisor['main_font_size'] != null)
					$main_font_size = $geovisor['main_font_size'];
				if($geovisor['legend_font'] != null)
					$legend_font = $geovisor['legend_font'];
				if($geovisor['legend_font_size'] != null)
					$legend_font_size = $geovisor['legend_font_size'];

				$searches = Search::get_search_by_geovisor_id($geovisor_id);
			}
			else
			{
				header("Location: admin.php?error_no=101&access_token=".$access_token);
			  	die();
			}	
		}
		else if($action == 'new')
		{

			$base_layer = 'osm';

			$geovisor_id = 0;
			$name = '';
			$title = '';
			$coord_ini = '0,0';
			$zoom_ini = 2;
			$zoom_min = 1;
			$zoom_max = 20;
			$message_ini = '';
			$logo = '';
			$slug = '';
			$is_public = false;

			
			$custom_css = '';
			

			$searches = [];
		}


		$cat_layers = Layer::get_all();

?>
<!DOCTYPE html>
<html>
<head>
		<meta property="og:url"                content="https://mapa.redspira.org/editor" />
		<meta property="og:type"               content="article" />
		<meta property="og:title"              content="Editor Geovisor" />
		<meta property="og:description"        content="" />
		<meta property="og:image"              content="img/preview.png" />
		<meta charset="utf-8">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">

		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Editor Geovisor</title>
		
		<link href="plugins/fontawesome/css/fontawesome.min.css" rel="stylesheet">
		<link href="plugins/fontawesome/css/solid.css" rel="stylesheet">

		<link href="css/jquery-ui.css" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" href="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/css/ol.css">
		<link href="css/bootstrap.css" rel="stylesheet" type="text/css"/>
		<link href="css/semantic.min.css" rel="stylesheet" type="text/css"/>

		<link href="css/toastr.min.css" rel="stylesheet" type="text/css"/>
		
		
		<link href="css/dropzone.css" rel="stylesheet">
		<link href="css/jspanel.css" rel="stylesheet">
		<link href="css/jqtree.css" rel="stylesheet">
		<link href="css/c3.css" rel="stylesheet">
		<link href="css/edit.css" rel="stylesheet">


		<style type="text/css">
			
			.ui.modal {
				      overflow: initial;
				}
		</style>

</head>
<body>

	 
		<div id="content">


			<div id="configure-panel">
				<form class="ui form" onsubmit="return false;">
					<input type="hidden" id="geovisor_id" value="<?php echo $geovisor_id; ?>"/>
					<input type="hidden" id="action" value="<?php echo $action; ?>"/>
					<div>
						<button onclick="cancelGeovisorForm()" class="right floated ui button"><i class="angle left icon"></i> Regresar</button>
						<button id="btnSaveGeovisor" onclick="saveGeovisorForm()" class="right floated primary ui button"><i class="save icon"></i> Guardar</button>
						
					</div>

				  <h4 class="ui dividing header">Editor visor</h4>

				  <div class="ui pointing secondary menu">
					  <a class="active item" data-tab="tab-general">Datos generales</a>
					  <a class="item" data-tab="tab-layers">Capas</a>
					  <a class="item" data-tab="tab-search">Búsquedas</a>
					  <a class="item" data-tab="tab-menu">Menús</a>
					  <a class="item" data-tab="tab-style">Estilo</a>
				  </div>

				  <div class="ui bottom attached active tab segment" data-tab="tab-general">

					  <div class="field">
					    <div class="three fields">
					      <div class="field">
					      	<label>Nombre corto</label>
					        <input type="text" id="geovisor_name" name="geovisor_name" placeholder="Nombre corto" value="<?php echo $name; ?>">
					      </div>
					      <div class="field">
					      	<label>Slug (dirección URL)</label>
					        <input type="text" id="geovisor_slug" name="geovisor_slug" placeholder="Slug" value="<?php echo $slug; ?>">
					      </div>
					      <div class="field">
					      	<label>Es público</label>
					        <input type="checkbox" id="geovisor_is_public" name="geovisor_is_public" <?php if($is_public == 't') echo 'checked'; ?> />
					      </div>
					    </div>
					  </div>

					  <div class="two fields">
						  <div class="twelve wide field">
						  	<label>Título</label>
						     <input type="text" id="geovisor_title" name="geovisor_title" placeholder="Título" value="<?php echo $title; ?>">
						  </div>
						  <div class="four wide field">
						    <label>Imagen/logo:</label>
						    <input type="hidden" name="img_logo" id="img_logo_src" value="<?php echo $logo; ?>">
						    <a href="javascript:changeLogoImage()">
								  <img id="img_logo" style="max-height: 100px;" src="uploads/<?php echo $logo; ?>">
								</a>
						    
						  </div>
					  </div>

					  <div class="field">
					    <div class="four fields">
					      
					      <div class="field">
					      		<label>Coord. inicial <a href="javascript:setCurrentCoord()"><i class="plus icon"></i></a></label>
					      	  <input clsas="disabled" type="text" id="geovisor_coord" name="geovisor_coord" placeholder="Coord. ini." value="<?php echo $coord_ini; ?>">
					      </div>
					      <div class="field">
					      	<label>Zoom inicial <a href="javascript:setCurrentZoomIni()"><i class="plus icon"></i></a></label>
					        <input type="text" id="geovisor_zoom" name="geovisor_zoom" placeholder="Zoom" value="<?php echo $zoom_ini; ?>">
					      </div>
					      <div class="field">
					      	<label>Zoom mín. <a href="javascript:setCurrentZoomMin()"><i class="plus icon"></i></a></label>
					        <input type="text" id="geovisor_zoom_min" name="geovisor_zoom_min" placeholder="Zoom" value="<?php echo $zoom_min; ?>">
					      </div>
					      <div class="field">
					      	<label>Zoom máx. <a href="javascript:setCurrentZoomMax()"><i class="plus icon"></i></a></label>
					        <input type="text" id="geovisor_zoom_max" name="geovisor_zoom_max" placeholder="Zoom" value="<?php echo $zoom_max; ?>">
					      </div>
					    </div>
					  </div>
					  <div class="field">

						  <div class="sixteen wide field">
						    <label>Mensaje inicial</label>
						    <textarea id="geovisor_msj_ini" rows="3"><?php echo $message_ini; ?></textarea>
						  </div>
					   </div>

					</div> <!-- tab-general -->

					<div class="ui bottom attached tab segment" data-tab="tab-layers">

						<h4 class="ui header">Árbol de capas</h4>

						<label>Capa base default: </label>
						<div class="ui selection dropdown">

						  <input type="hidden" name="base_layer" id="base_layer" value="<?php echo $base_layer ?>">
						  <i class="dropdown icon"></i>
						  <div class="default text">Capa base</div>
						  <div class="menu">
						    <div class="item" data-value="osm">OpenSreetMap</div>
						    <div class="item" data-value="bingaeriallabels">Bing Aerial con etiquetas</div>
						    <div class="item" data-value="bingaerial">Bing Aerial</div>
						    <div class="item" data-value="bingroad">Bing Road</div>
						    <div class="item" data-value="esriworldstreetmap">Esri World Streetmap</div>
						    <div class="item" data-value="esriworldimagery">Esri World Imagery</div>
						    <div class="item" data-value="esriworldterrain">Esri World Terrain</div>
						    <div class="item" data-value="esriworldshadedrelief">Esri World Shaded Relief</div>
						    <div class="item" data-value="esriworldphysical">Esri World Physical</div>
						    <div class="item" data-value="stamen_terrain">Stamen Terrain</div>
						    <div class="item" data-value="stamen_toner">Stamen Toner</div>
						  </div>
					  	</div>

						<div style="margin-top: 10px;">
							<button onclick="newCategory()" class="mini primary ui button"><i class="folder icon"></i> Nueva categoría</button>
							<button onclick="newLayer()" class="mini primary ui button"><i class="map icon"></i> Nueva capa</button>
						</div>

						<div id="layerTree"></div>

					</div>  <!-- tab-layers -->

					<div class="ui bottom attached tab segment" data-tab="tab-search">

						<?php

							echo '<div><button onclick="newSearch();" class="ui primary button"><i class="search icon"></i> Nueva búsqueda</button></div>';
							echo '<table class="ui table" id="search-table">';							
							echo '<thead><tr><th>#</th><th>Capa</th><th>Atributo</th><th>Etiqueta</th><th>Tipo</th><th>Texto de búsqueda</th><th>@</th></tr></thead>';
							echo '<tbody>';
						    for($i=0; $i<count($searches); $i++)
						    {
						      $search = $searches[$i];

						      $search_id = $search['search_id'];
						      $geoserver_layer_id = $search['geoserver_layer_id'];
						      $layer_name = $search['layer_name'];
						      $attribute = $search['attribute'];
						      $attribute_label = $search['attribute_label'];
						      $type = $search['type'];
						      $search_text = $search['search_text'];

						      	echo '<tr><td>'.$search_id.'</td><td>'.$layer_name.' ('.$geoserver_layer_id.')</td><td>'.$attribute.'</td><td>'.$attribute_label.'</td><td>'.$type.'</td><td>'.$search_text.'</td><td><button onclick="editSearch('.$search_id.');" class="circular ui primary mini icon button"><i class="edit icon"></i></button><button onclick="removeSearch('.$search_id.');" class="circular ui red mini icon button"><i class="trash alternate icon"></i></button></td></tr>';
						      
						  	}
						  	echo '</tbody>';
						  	echo '</table>';
						?>

					</div>  <!-- tab-search -->

					<div class="ui bottom attached tab segment" data-tab="tab-menu">


						<div style="margin-top: 10px;">
							<button onclick="newMenuItem()" class="mini primary ui button"><i class="map icon"></i> Nuevo elemento (menu item)</button>
						</div>

						<div id="menuTree"></div>

						<!--
						<textarea class="text-item" style="height: 100%;">
							
						</textarea> -->

					</div>  <!-- tab-menu -->

					<div class="ui bottom attached tab segment" data-tab="tab-style">


						<div class="field">
					    <div class="four fields">
					      <div class="field">
					      	<label>Color primario (herramientas)</label>
					        <input type="text" id="geovisor_primary_color" name="geovisor_primary_color"  data-jscolor="{}" placeholder="Color primario" value="<?php echo $primary_color; ?>">
					      </div>
					      <div class="field">
					      	<label>Color secundario (ventanas)</label>
					        <input type="text" id="geovisor_secondary_color" name="geovisor_secondary_color"  data-jscolor="{}" placeholder="Color secundario" value="<?php echo $secondary_color; ?>">
					      </div>
					      <div class="field">
					      	<label>Fuente principal</label>
					        <input type="text" id="geovisor_main_font" name="geovisor_font" value="<?php echo $main_font; ?>" />

					      </div>
					      <div class="field">
					      	<label>Tamaño</label>
					        <input type="text" id="geovisor_main_font_size" name="geovisor_font_size" value="<?php echo $main_font_size; ?>" />
					      </div>
					    </div>
					  </div>

					  <div class="field">
					    <div class="three fields">
					      <div class="field">
					      	<label>Fuente leyenda/simbología (entero)</label>
					        
					        <div id="select_geovisor_font_legend" class="ui search selection dropdown" >
							  <input type="hidden" id="geovisor_legend_font" value="<?php echo $legend_font; ?>">
							  <i class="dropdown icon"></i>
							  <div class="default text">Fuente leyenda</div>
							  <div class="menu">

							    <?php
									for($f=0; $f<count($legend_fonts); $f++)
									{
										echo '<div class="item" data-value="'.$legend_fonts[$f].'">'.$legend_fonts[$f] .'</div>';
									}
								?>

							  </div>
							</div>
					      </div>
					      <div class="field">
					      	<label>Tamaño texto leyenda/simbología</label>
					        <input type="text" id="geovisor_legend_font_size" name="geovisor_legend_font_size" placeholder="Tamaño texto" value="<?php echo $legend_font_size; ?>">
					      </div>
					    </div>
					  </div>




					  	<div class="field">
					  	<label>CSS personalizado</label>
						<textarea id="custom_css" style="height: 300px;"><?php echo $custom_css; ?></textarea> 

						</div>

					</div>  <!-- tab-menu -->


				</form>

				
			</div>


			<div id="map" class="map">
				
				<div  id="modalFormLayer" class="ui coupled modal">
					<div class="header" id="title_layer_form">Capa</div>
					<div class="content">
						
						<form id="form-geovisor" class="ui form">
						<input type="hidden" id="form_layer_action">
						<input type="hidden" id="lyr_edit_id">
						<div class="field">
							<div class="two fields">
								<div class="field">
									<label>Id capa <a class="btn-search-layer button">Buscar <i class="search icon"></i></a></label>
									<input id="id_layer_form" type="text" name="id_layer_form" placeholder="Capa">
								</div>
								<div class="field">
									<label>Nombre</label>
									<input id="name_layer_form" type="text" name="name_layer_form" placeholder="Título">
								</div>
							</div>
						</div>
						<div class="field">
							<div class="fields">
								<div class="twelve wide field">
									<label>URL</label>
									<input id="geoserver_url_layer_form" type="text" name="geoserver_url_layer_form" placeholder="URL WMS">
								</div>
								<div class="four wide field">
									<label>Estilo</label>
									<input id="style_layer_form" type="text" name="style_layer_form" placeholder="Estilo">
								</div>
							</div>
						</div>


						<div class="field">
							<div class="fields">
								<div class="three wide field">
									<label>Transparencia</label>
									<input id="transparency_layer_form" type="text" name="transparency_layer_form" placeholder="Transparencia">
								</div>
								<div class="three wide field">
									<label>Índice-Z</label>
									<input id="zindex_layer_form" type="text" name="zindex_layer_form" placeholder="Índice Z">
								</div>
								<div class="three wide field">
									<label>Activa al inicio</label>
									<input id="active_layer_form" type="checkbox" name="active_layer_form">
								</div>
								<div class="three wide field">
									<label>Consultable</label>
									<input id="queryable_layer_form" type="checkbox" name="queryable_layer_form">
								</div>
								<div class="three wide field">
									<label>Mosaico</label>
									<input id="tiled_layer_form" type="checkbox" name="tiled_layer_form">
								</div>
							</div>
						</div>

						<h4 class="ui dividing header">Etiquetas</h4>

						<div class="field">
							<div class="fields">
								<div class="four wide field">
									<label>Estilo etiquetas <a class="btn-search-style button"><i class="search icon"></i></a></label>
									<input id="label_style_id_layer_form" type="text" name="transparency_layer_form" placeholder="Transparencia">
								</div>
								<div class="four wide field">
									<label>Activa al inicio</label>
									<input id="label_active_layer_form" type="checkbox" name="active_layer_form">
								</div>
								<div class="four wide field">
									<label>Zoom min:</label>
									<input id="label_zoom_min_layer_form" type="text" name="zindex_layer_form" placeholder="Zoom mínimo">
								</div>
								<div class="four wide field">
									<label>Zoom máx</label>
									<input id="label_zoom_max_layer_form" type="text" name="zindex_layer_form" placeholder="Zoom máximo">
								</div>
							</div>
						</div>

						</form>

					</div>
					<div class="actions">
						<div class="ui approve button">Guardar</div>
						<div class="ui cancel button">Cancel</div>
					</div>
				</div>

				<div  id="modalFormCategory" class="ui modal">
					<div class="header" id="title_category_form">Categoría</div>
					<div class="content">
						
						<form class="ui form">

						<input type="hidden" id="form_category_action">
						<input type="hidden" id="cat_id">

						<div class="field">
							<div class="fields">
								<div class="ten wide field">
									<label>Nombre</label>
									<input id="name_category_form" type="text" name="name_category_form" placeholder="Nombre categoría">
								</div>
								
								<div class="six wide field">
									<label>Expandida al inicio</label>
									<input id="expanded_layer_form" type="checkbox" name="expanded_layer_form">
								</div>
							</div>
						</div>

						</form>

					</div>
					<div class="actions">
						<div class="ui approve button">Guardar</div>
						<div class="ui cancel button">Cancel</div>
					</div>
				</div>


				<div  id="modalFormSearch" class="ui modal">
					<div class="header" id="title_search_form">Búsqueda</div>
					<div class="content">
						
						<form class="ui form">

						<input type="hidden" id="form_search_action">
						<input type="hidden" id="search_id_form">

						<div class="field">
							<div class="fields">

								<div class="eight wide field">
									<label>Texto de búsqueda</label>
									<input id="search_text_form" type="text" name="search_text_form">
								</div>

								<div class="eight wide field">


								<label>Capa:</label>
									<div id="search_layer_id_form" class="ui search selection dropdown">
									  <input type="hidden" id="search_layer_id">
									  <i class="dropdown icon"></i>
									  <div class="default text">Capa</div>
									  <div class="menu">

									    <?php
											echo '<div class="item" data-value=""></div>';
											for($i=0; $i<count($cat_layers); $i++)
											{
												echo '<div class="item" data-value="'.$cat_layers[$i]['id'].'">'.$cat_layers[$i]['title']. ' ('. $cat_layers[$i]['name'] .')</div>';
											}
										?>

									  </div>
									</div>

								</div>
								
								
							</div>
						</div>

						<div class="field">
							<div class="fields">

								<div class="eight wide field">
									<div class="grouped fields">
								    <label>Tipo:</label>
								    <div class="field">
								      <div id="search_type_select_form" class="ui radio checkbox">
								        <input type="radio"  name="search_type_form" value="select" checked="checked">
								        <label>Selección de opciones (lista) <i class="list icon"></i></label>
								      </div>
								    </div>
								    <div class="field">
								      <div id="search_type_text_form" class="ui radio checkbox">
								        <input type="radio"  name="search_type_form" value="text">
								        <label>Búsqueda por texto <i class="pencil alternate icon"></i></label>
								      </div>
								    </div>
								    <div id="search_type_checkbox_form" class="field">
								      <div class="ui radio checkbox">
								        <input type="radio" name="search_type_form" value="checkbox">
								        <label>Casilla de verificación <i class="check square icon"></i></label>
								      </div>
								    </div>
								  </div>

								</div>

								<div class="eight wide field">


								<label>Atributo:</label>
									<div id="search_attribute_form" class="ui search selection dropdown">
									  <input type="hidden" id="search_attribute">
									  <i class="dropdown icon"></i>
									  <div class="default text">Atributo</div>
									  <div class="menu">

									  </div>
									</div>

								</div>
								
								
							</div>
						</div>

						</form>

					</div>
					<div class="actions">
						<div class="ui approve button">Guardar</div>
						<div class="ui cancel button">Cancel</div>
					</div>
				</div>

				<div  id="modalSearchLayer" class="ui coupled modal">
					<div class="header" id="title_search_layer_form">Búsqueda capa</div>
					<div class="content">
						
						<form class="ui form">

						<div class="field">
				            <label>Buscar:</label>
				            
				            <div id="selectLayer" class="ui search selection dropdown">
				              <input type="hidden" id="selected_layer">
							  <i class="dropdown icon"></i>
							  <div class="default text">Texto a buscar</div>
							  <div class="menu">
							  	
							  </div>
				            </div>

						</div>

						</form>

					</div>
					<div class="actions">
						<div class="ui approve button">Aceptar</div>
						<div class="ui cancel button">Cancel</div>
					</div>
				</div>

				<div  id="modalSearchStyle" class="ui coupled modal">
					<div class="header" id="title_search_style_form">Búsqueda estilo</div>
					<div class="content">
						
						<form class="ui form">

						<div class="field">
				            <label>Buscar:</label>
				            
				            <div id="selectStyle" class="ui search selection dropdown">
				              <input type="hidden" id="selected_style">
							  <i class="dropdown icon"></i>
							  <div class="default text">Texto a buscar</div>
							  <div class="menu"></div>
				            </div>

						</div>

						</form>

					</div>
					<div class="actions">
						<div class="ui approve button">Aceptar</div>
						<div class="ui cancel button">Cancel</div>
					</div>
				</div>

				<div  id="modalFormMenu" class="ui modal">
					<div class="header" id="title_menu_form">Menú</div>
					<div class="content">
						
						<form class="ui form">

						<input type="hidden" id="form_menu_action">
						<input type="hidden" id="id_menu_form">

						<div class="field">
							<div class="fields">
								<div class="ten wide field">
									<label>Nombre</label>
									<input id="name_menu_form" type="text" name="name_menu_form" placeholder="Nombre menú">
								</div>
								
								<div class="six wide field">
									<label>Tipo</label>
									<select id="type_menu_form" name="type_menu_form" class="ui dropdown">
										<option value="container">Contenedor</option>
										<option value="link">Enlace</option>
										<option value="html">HTML</option>
									</select>
								</div>
							</div>

							<h4 class="ui dividing header">Contenido</h4>

							<div class="field">
								<div class="fields">
									<div id="field_menu_html" style="display: none;" class="sixteen wide field">
										<textarea class="text-item" id="content_menu_form" rows="3"></textarea>
									</div>

									<div id="field_menu_link" style="display: none;" class="ten wide field">
										<label>URL:</label>
										<input id="menu_link_form" type="text" name="menu_link_form" placeholder="URL">
									</div>
								</div>
							</div>
						</div>

						</form>

					</div>
					<div class="actions">
						<div class="ui approve button">Guardar</div>
						<div class="ui cancel button">Cancel</div>
					</div>
				</div>


				<div  id="modalImageGeovisor" class="ui modal">
					<div class="header" id="title_category_form">Imagen - logo</div>
					<div class="content">
						
						<form id="upload_image" class="dropzone">
			          	<div class="dz-message needsclick">Arrastra tus archivos o has clic aquí para subir
			                <span class="note needsclick">(Archivos .jpg, .jpeg y .png)</span>
			                </div>
			            </form>

					</div>
					<div class="actions">
						<div class="ui cancel button">Cerrar</div>
					</div>
				</div>

				<div id="panel_georef">
						 <div id="proj">EPSG:4326</div>
						 <div id="zoom">Zoom: 10</div>
						 <div id="mouse-position"><div class="custom-mouse-position"></div></div>
				 </div>

			</div>

		</div>

		

		
		<script src="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/build/ol.js"></script>
		<script src="js/jquery/jquery-1.9.1.js"></script>
		<script src="js/jquery/jquery-ui.js" type="text/javascript"></script>
		<script src="js/jquery/jquery.csv.min.js" type="text/javascript"></script>
		<script src="plugins/jquery.awesome-cursor.js" type="text/javascript"></script>
		
		<script src="js/bootstrap/bootstrap.js" type="text/javascript"></script>
		<script src="js/bootstrap/bootstrap-datepicker.js" type="text/javascript"></script>
		<script src="js/semantic.min.js" type="text/javascript"></script>
		<script src="js/moment.min.js" type="text/javascript"></script>
		<script src="js/d3/d3.min.js" type="text/javascript"></script>
		<script src="js/c3/c3.js" type="text/javascript"></script>
		<script src="js/toastr.min.js" type="text/javascript"></script>

		<script src="js/jscolor.min.js" type="text/javascript"></script>
		
		<!-- loading jsPanel javascript -->
		<script src="js/jspanel/jspanel.js"></script>
		<!-- optionally load jsPanel extensions -->
		<script src="js/jspanel/contextmenu/jspanel.contextmenu.js"></script>
		<script src="js/jspanel/hint/jspanel.hint.js"></script>
		<script src="js/jspanel/modal/jspanel.modal.js"></script>
		<script src="js/jspanel/tooltip/jspanel.tooltip.js"></script>
		<script src="js/jspanel/dock/jspanel.dock.js"></script>

		<script src="js/tree.jquery.js"></script>

		<script src="js/dropzone.js"></script>

		<script src="plugins/tinymce/tinymce.min.js" referrerpolicy="origin"></script>

		<script>
		

		var GEOSERVER_BASE_URL = '<?php echo $GEOSERVER_BASE_URL;?>';
		var baseLayer = '<?php echo $base_layer;?>';
		var coordIni = [<?php echo $coord_ini;?>];
		var zoomIni = '<?php echo $zoom_ini;?>';


		var map, map_extent, map_view;
		var lyr;
		var lyrBingMapsAerialWithLabels, lyrBingMapsAerial, lyrBingMapsRoad, lyrOSM, lyrStamenTerrain, lyrStamenToner, lyrEsriWorldStreetMap, lyrEsriWorldImagery, lyrEsriWorldTerrain, lyrEsriWorldShadedRelief, lyrEsriWorldPhysical;
		
		var select, lyrResult;

		
		var lyrsMap = {};
		var visibleLayers = [];




		<?php

		echo 'var access_token = "'.$access_token.'"; ';

		$all_layers = array();

		function layerTree($geovisor_id, $parent_category_id)
		{

				$categories = Geovisor::get_child_categories($geovisor_id, $parent_category_id);

				for($i=0; $i<count($categories); $i++)
				{
					$cat = $categories[$i];

					$category_id = $cat['id'];
					$name = $cat['name'];
					$ordr = $cat['ordr'];
					$expanded = $cat['expanded'];

					$cat_html = '<div><i class="folder icon"></i> '.$name.' <span onclick="editCategory('.$category_id.')"><i class="edit icon"></i></span><span onclick="removeCategory('.$category_id.')"><i class="remove icon"></i></span></div>';

					echo " {  id: 'cat".$category_id."', cat_id: '".$category_id."', type:'category', category_name: '".$name."', expanded: '".$expanded."', name: '".$cat_html."',  \n\t";
					echo " children: [ \n\t";
					
					layerTree($geovisor_id, $category_id);
					
					printLayer($geovisor_id, $category_id);
						

					echo "] } , \n\t";

				}
		}

		function printLayer($geovisor_id, $category_id)
		{
			$layers = Geovisor::get_layers_by_geovisor_category($geovisor_id, $category_id);

			for($j=0; $j<count($layers); $j++)
			{
				$lyr = $layers[$j];

				array_push($GLOBALS['all_layers'], $lyr);

				$lyr_id = $lyr["id"];
				$geoserver_url = $lyr["geoserver_url"];
				$geoserver_layer_id = $lyr["geoserver_layer_id"];
				$geoserver_style_id = $lyr["geoserver_style_id"];
				$lyr_name = $lyr["name"];
				$ordr = $lyr["ordr"];
				$active = $lyr["active"];
				$queryable = $lyr["queryable"];
				$tiled = $lyr["tiled"];
				$zindex = $lyr["zindex"];
				$transparency = $lyr["transparency"];
				$geoserver_label_style_id = $lyr["geoserver_label_style_id"];
				$label_active = $lyr["label_active"];
				$label_zoom_min = $lyr["label_zoom_min"];
				$label_zoom_max = $lyr["label_zoom_max"];

				$lyr_id_sel = '';
				if($geoserver_style_id == null || $geoserver_style_id == '')
					$lyr_id_sel = $geoserver_layer_id;
				else
					$lyr_id_sel = $geoserver_style_id;

				

				$lyr_html= '<div><i class="map icon"></i> '.$lyr_name.' <span onclick="activateLayer(\\\''.$lyr_id_sel.'\\\')"><i class="eye icon"></i></span><span onclick="editLayer(\\\''.$lyr_id_sel.'\\\')"><i class="edit icon"></i></span><span onclick="removeLayer(\\\''.$lyr_id_sel.'\\\')"><i class="remove icon"></i></span><div> <img src="';
				
				if(substr($geoserver_url, 0, 4 ) === "http")
					$lyr_html.= $geoserver_url;
				else
					$lyr_html.= 'http://'.$geoserver_url;
				$lyr_html.=  '/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER='.$geoserver_layer_id;
				
				if($geoserver_style_id != null AND $geoserver_style_id != '')
					$lyr_html.=  '&STYLE='.$geoserver_style_id;
				
				$lyr_html.= '&LEGEND_OPTIONS=bgColor:0xFFFFFF;fontSize:9;fontColor:0x333333;forceLabels:on&access_token='.$GLOBALS['access_token'].'"/></div><div>';
				
				echo "{ id: 'lyr".$lyr_id_sel."', lyr_id:'".$lyr_id."', type:'layer', geoserver_url: '".$geoserver_url."', geoserver_layer_id: '".$geoserver_layer_id."', geoserver_style_id: '".$geoserver_style_id."', "; 
				echo "lyr_name: '".$lyr_name."', ordr: '".$ordr."', active: '".$active."', queryable: '".$queryable."', tiled: '".$tiled."', zindex: '".$zindex."', transparency: '".$transparency."', name: '".$lyr_html."',";
				echo "geoserver_label_style_id: '".$geoserver_label_style_id."',label_active: '".$label_active."',label_zoom_min: '".$label_zoom_min."',label_zoom_max: '".$label_zoom_max."'},  \n\t";

			}
		}


		function menuTree($geovisor_id, $parent_id)
		{

				$menus = Menu::get_child_menu($geovisor_id, $parent_id);

				for($i=0; $i<count($menus); $i++)
				{
					$menu = $menus[$i];

					$menu_id = $menu['id'];
					$name = $menu['name'];
					$type = $menu['type'];
					$content = $menu['content'];

					$menu_icon = 'folder';

					switch(trim($type)) 
					{
						case 'contanier': $menu_icon = 'folder'; break;
						case 'link': $menu_icon = 'linkify'; break;
						case 'html': $menu_icon = 'edit'; break;
					}

					$menu_html = '<div><i class="'.$menu_icon.' icon"></i> '.$name.' <span onclick="editMenuItem('.$menu_id.')"><i class="edit icon"></i></span><span onclick="removeMenuItem('.$menu_id.')"><i class="remove icon"></i></span></div>';

					echo " {  id: 'menu".$menu_id."', menu_id: '".$menu_id."', type:'category', menu_name: '".$name."', type: '".$type."', content:  `".$content."`, name: '".$menu_html."',  \n\t";
					echo " children: [ \n\t";
					
					menuTree($geovisor_id, $menu_id);
						

					echo "] } , \n\t";

				}
		}

		echo "var layerData = [";

				layerTree($geovisor_id, 0);
						
		echo "]; \n\t";

		

		echo "var listSearch = [";

			for($i=0; $i<count($searches); $i++)
		    {
		      $search = $searches[$i];

		      $search_id = $search['search_id'];
		      $geoserver_layer_id = $search['geoserver_layer_id'];
		      $layer_id = $search['layer_id'];
		      $layer_name = $search['layer_name'];
		      $attribute = $search['attribute'];
		      $attribute_label = $search['attribute_label'];
		      $type = $search['type'];
		      $search_text = $search['search_text'];

		      echo "{search_id:'$search_id',layer_id:'$layer_id',  layer_name:'$layer_name ($geoserver_layer_id)', attribute:'$attribute', attribute_label:'$attribute_label', type:'$type',  search_text:'$search_text'},";

			}			

		echo "];";

		echo "var menuData = [";

				menuTree($geovisor_id, 0);
						
		echo "]; \n\t";

		?>

		</script>

		<script type="text/javascript" src="js/geo.edit.min.js"></script>

		<script type="text/javascript">

		<?php

			for($j=0; $j<count($all_layers); $j++)
			{
			  $lyr = $all_layers[$j];

			  $lyr_id = $lyr["id"];
			  $geoserver_url = $lyr["geoserver_url"];
			  $geoserver_layer_id = $lyr["geoserver_layer_id"];
			  $geoserver_style_id = $lyr["geoserver_style_id"];
			  $lyr_name = $lyr["name"];
			  $ordr = $lyr["ordr"];
			  $active = $lyr["active"];
			  $queryable = $lyr["queryable"];
			  $tiled = $lyr["tiled"];
			  $zindex = $lyr["zindex"];

			  echo "var lyr".$lyr_id." = new ol.layer.Tile({visible: ";
			 
			  if($active == 't') echo 'true'; else echo 'false';
			  echo ", source: new ol.source.TileWMS({url: '";
			  if(substr($geoserver_url, 0, 4 ) === "http")
			    echo $geoserver_url;
			  else
			    echo 'http://'.$geoserver_url;
				
			  echo "/wms', params: {'FORMAT': 'image/png', 'VERSION': '1.1.1', tiled: true, ";
			  echo "STYLES: '".$geoserver_style_id."', access_token: '".$access_token."', LAYERS: '".$geoserver_layer_id."'}, serverType: 'geoserver'}), opacity: 0.9}); \n\t";

			  echo "lyr".$lyr_id.".set('geoserver_layer_id', '".$geoserver_layer_id."');";
			  echo "lyr".$lyr_id.".set('name', '".$lyr_name."');";
			  echo "lyr".$lyr_id.".setZIndex(".$zindex.");";
			  echo "map.addLayer(lyr".$lyr_id."); \n\t";

			  if($geoserver_style_id == null || $geoserver_style_id == '')
			    echo "lyrsMap['".$geoserver_layer_id."'] = lyr".$lyr_id.";";
			  else
			    echo "lyrsMap['".$geoserver_style_id."'] = lyr".$lyr_id.";";

			}



		?>
		</script>



			
</body>
</html>

<?php

} //session_expired == false
else
{

?>
	<!DOCTYPE html>
	<html>
	<head>
	</head>
	<body>
		<p>Sesión expirada. Se requiere cerrar la sesión actual y abrir una de nuevo.</p>
		<p><a href="<?php echo $MAIN_SITE_URL;?>">Ir a página principal</a></p>
	</body>
</html>

<?php
}
?>