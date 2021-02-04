<?php
require_once("lib/framework/geovisor.php");
require_once("lib/framework/oauth.php");


$geovisor_id = $_REQUEST["geovisor_id"];
$action = $_REQUEST["action"];
$access_token = $_REQUEST["access_token"];


if($access_token == null || $access_token == '')
{
  header("Location: https://territoriosenriesgo.unah.edu.hn/");
  die();
}


$user_data = OAuth::get_user_data_by_accesstoken($access_token);

if(sizeof($user_data)>0)
{
	if($user_data[0]['is_superuser'] == 'f')
	{
		header("Location: https://territoriosenriesgo.unah.edu.hn/");
	  	die();	
	}
}
else
{
	header("Location: https://territoriosenriesgo.unah.edu.hn/");
	die();
}


$session_expired = OAuth::is_session_expired($access_token);

if($session_expired == 'f')
{

		if($action == 'edit')
		{
			$g_res = Geovisor::get($geovisor_id);

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
			$coord_ini = '-115.4423,32.6245';
			$zoom_ini = 12;
			$zoom_min = 6;
			$zoom_max = 20;
			$message_ini = '';
			$logo = '';
			$slug = '';
			$is_public = false;
		}




?>
<!DOCTYPE html>
<html>
<head>
		<meta property="og:url"                content="https://territoriosenriesgo.unah.edu.hn/editor" />
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


</head>
<body>

	 
		<div id="content">


			<div id="configure-panel">
				<form class="ui form" onsubmit="return false;">
					<input type="hidden" id="geovisor_id" value="<?php echo $geovisor_id; ?>"/>
					<input type="hidden" id="action" value="<?php echo $action; ?>"/>
					<div>
						<button onclick="cancelGeovisorForm()" class="right floated ui button"><i class="cancel icon"></i> Cancelar/Regresar</button>
						<button id="btnSaveGeovisor" onclick="saveGeovisorForm()" class="right floated primary ui button"><i class="save icon"></i> Guardar</button>
						
					</div> 
				  <h4 class="ui dividing header">Geovisor</h4>

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


				  <div class="field">
				  	<label>Título</label>
				     <input type="text" id="geovisor_title" name="geovisor_title" placeholder="Título" value="<?php echo $title; ?>">
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
				  <div class="fields">

					  <div class="ten wide field">
					    <label>Mensaje inicial</label>
					    <textarea id="geovisor_msj_ini" rows="3"><?php echo $message_ini; ?></textarea>
					  </div>

					  <div class="si wide field">
					    <label>Imagen/logo:</label>
					    <input type="hidden" name="img_logo" id="img_logo_src" value="<?php echo $logo; ?>">
					    <a href="javascript:changeLogoImage()">
							  <img id="img_logo" style="max-height: 100px;" src="uploads/<?php echo $logo; ?>">
							</a>
					    
					  </div>

					</div>

				</form>

				<h4 class="ui header">Árbol de capas</h4>


				<div class="ui selection dropdown">
				  <input type="hidden" name="base_layer" id="base_layer" value="<?php echo $base_layer ?>">
				  <i class="dropdown icon"></i>
				  <div class="default text">Capa base</div>
				  <div class="menu">
				    <div class="item" data-value="osm">OpenSreetMap</div>
				    <div class="item" data-value="bingaeriallabels">Bing Aerial con etiquetas</div>
				    <div class="item" data-value="bingaerial">Bing Aerial</div>
				    <div class="item" data-value="bingroad">Bing Road</div>
				    <div class="item" data-value="stamen_terrain">Stamen Terrain</div>
				    <div class="item" data-value="stamen_toner">Stamen Toner</div>
				  </div>
			  </div>

				<div style="margin-top: 10px;">
					<button onclick="newCategory()" class="mini primary ui button"><i class="folder icon"></i> Nueva categoría</button>
					<button onclick="newLayer()" class="mini primary ui button"><i class="map icon"></i> Nueva capa</button>
				</div>

				<div id="layerTree"></div>
			</div>


			<div id="map" class="map">
				
				<div  id="modalFormLayer" class="ui coupled modal">
					<div class="header" id="title_layer_form">Capa</div>
					<div class="content">
						
						<form class="ui form">
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
								<div class="four wide field">
									<label>Transparencia</label>
									<input id="transparency_layer_form" type="text" name="transparency_layer_form" placeholder="Transparencia">
								</div>
								<div class="four wide field">
									<label>Índice-Z</label>
									<input id="zindex_layer_form" type="text" name="zindex_layer_form" placeholder="Índice Z">
								</div>
								<div class="four wide field">
									<label>Activa al inicio</label>
									<input id="active_layer_form" type="checkbox" name="active_layer_form">
								</div>
								<div class="four wide field">
									<label>Consultable</label>
									<input id="queryable_layer_form" type="checkbox" name="queryable_layer_form">
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
									<label>Expandida</label>
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

		<script>
		



		var map, map_extent, map_view;
		var coord_ini, zoom_ini;
		var lyr;
		var lyrBingMapsAerialWithLabels, lyrBingMapsAerial, lyrBingMapsRoad, lyrOSM, lyrStamenTerrain, lyrStamenToner;
		
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
				$zindex = $lyr["zindex"];
				$transparency = $lyr["transparency"];

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
				echo "lyr_name: '".$lyr_name."', ordr: '".$ordr."', active: '".$active."', queryable: '".$queryable."', zindex: '".$zindex."', transparency: '".$transparency."', name: '".$lyr_html."' },  \n\t";

			}
		}

		echo "var data = [";

				layerTree($geovisor_id, 0);
						
		echo "];";

		?>

		function initTree()
		{
			//openLayerTree();

			$('#layerTree').tree({
					data: data,
					dragAndDrop: true,
					autoEscape: false,
					closedIcon: $('<i class="fas fa-angle-right"></i>'),
					openedIcon: $('<i class="fas fa-angle-down"></i>'),
					onCanMoveTo: function(moved_node, target_node, position) {
							
							if (target_node.type == 'category') {
									
									return (position == 'inside' || position == 'before' || position == 'after');
							}
							else {
									return (position == 'before' || position == 'after');
							}
					}
			});
		}


		function initMap()
		{
			

			coord_ini = ol.proj.transform([<?php echo $coord_ini;?>], 'EPSG:4326', 'EPSG:3857');
			zoom_ini = <?php echo $zoom_ini;?>;

			map_view = new ol.View({
					center: coord_ini,
					zoom: zoom_ini
			});


			lyrBingMapsAerial = new ol.layer.Tile({
				visible: <?php if($base_layer == 'bingaerial') echo 'true'; else echo 'false'; ?>,
				preload: Infinity,
				queryable: false,
				source: new ol.source.BingMaps({
					key: 'AiXpl_iJ9vka91ugxW5OybC2RWcH9RJvBjbQDzh9TgZryPAP9-YdO6ig1Bj-qV85',
					imagerySet: 'Aerial'
					// use maxZoom 19 to see stretched tiles instead of the BingMaps
					// "no photos at this zoom level" tiles
					// maxZoom: 19
				})
			});
			

			lyrBingMapsAerialWithLabels = new ol.layer.Tile({
				visible: <?php if($base_layer == 'bingaeriallabels') echo 'true'; else echo 'false'; ?>,
				preload: Infinity,
				queryable: false,
				source: new ol.source.BingMaps({
					key: 'AiXpl_iJ9vka91ugxW5OybC2RWcH9RJvBjbQDzh9TgZryPAP9-YdO6ig1Bj-qV85',
					imagerySet: 'AerialWithLabels'
					// use maxZoom 19 to see stretched tiles instead of the BingMaps
					// "no photos at this zoom level" tiles
					// maxZoom: 19
				})
			});

			lyrBingMapsRoad = new ol.layer.Tile({
				visible: <?php if($base_layer == 'bingroad') echo 'true'; else echo 'false'; ?>,
				preload: Infinity,
				queryable: false,
				source: new ol.source.BingMaps({
					key: 'AiXpl_iJ9vka91ugxW5OybC2RWcH9RJvBjbQDzh9TgZryPAP9-YdO6ig1Bj-qV85',
					imagerySet: 'Road'
				})
			});
		 

			lyrOSM  = new ol.layer.Tile({
				visible: <?php if($base_layer == 'osm') echo 'true'; else echo 'false'; ?>,
				source: new ol.source.OSM()
			});


			lyrStamenTerrain = new ol.layer.Tile({
				visible: <?php if($base_layer == 'stamen_terrain') echo 'true'; else echo 'false'; ?>,
				source: new ol.source.Stamen(
				{
					layer:'terrain'
				})

			});

			lyrStamenToner = new ol.layer.Tile({
				visible: <?php if($base_layer == 'stamen_toner') echo 'true'; else echo 'false'; ?>,
				queryable: false,
				source: new ol.source.Stamen({
							layer: 'toner'
						})
			});

			


			map = new ol.Map({
				layers: [lyrBingMapsAerialWithLabels, lyrBingMapsAerial, lyrBingMapsRoad, lyrOSM, lyrStamenTerrain, lyrStamenToner],
				interactions: ol.interaction.defaults({
					altShiftDragRotate: false,
					rotate: false
				}),
				target: 'map',
				view: map_view
			});

			var mousePositionControl = new ol.control.MousePosition({
				coordinateFormat: ol.coordinate.createStringXY(4),
				projection: 'EPSG:4326',
				// comment the following two lines to have the mouse position
				// be placed within the map.
				className: 'custom-mouse-position',
				target: document.getElementById('mouse-position'),
				undefinedHTML: '&nbsp;'
			});


		 var ghostZoom = map.getView().getZoom();

		 map_zoom = ghostZoom;
		 $('#zoom').html("Zoom: " + ghostZoom);

			map.on('moveend', (function() {
					if (ghostZoom != map.getView().getZoom()) {
							ghostZoom = map.getView().getZoom();
							

							map_zoom = parseFloat(map.getView().getZoom()).toFixed(2);

							$('#zoom').html("Zoom: " + map_zoom);
					}
			}));


			map.addControl(mousePositionControl);


			var scaleLineControl = new ol.control.ScaleLine();
			map.addControl(scaleLineControl);

			var zoomControl = new ol.control.FullScreen();
			map.addControl(zoomControl);

			var overview = new ol.control.OverviewMap({layers: [lyrBingMapsAerialWithLabels, lyrBingMapsAerial, lyrBingMapsRoad, lyrOSM, lyrStamenTerrain, lyrStamenToner]});

			overview.setMap(map);

			graticule = new ol.Graticule({
				// the style to use for the lines, optional.
				strokeStyle: new ol.style.Stroke({
					color: 'rgba(0,0,0,0.9)',
					width: 2,
				}),
				showLabels: true
			});
			

			
			map.on('click', function(evt) {
				//onMapClick(evt); 
			});
			


		}

		function openLayerTree()
		{
			panelLayerTree = jsPanel.create({
					id: 'panelLayerTree',
					theme:       'primary',
					contentSize: {
							width: function() { return Math.min(400,window.innerWidth/4)},
							height: function() { return window.innerHeight-200;}
					},
					
					position:    'left-top 10 70',
					animateIn:   'jsPanelFadeIn',
					headerControls: {
	              close: 'remove'
	          },
					dragit: {
              containment: [60, 10, 10, 10]
	          },
					headerTitle: '<i class="info circle icon"></i> Configurar capas',
					content: '',
					onwindowresize: true
			});
		}



		function clickIdentify(evt)
		{
			new_click = true;
					
			lyrResult.getSource().clear();

			if(visibleLayers.length > 0)
				getInfo(0, evt.coordinate);
		}




		function mostrarResultado(f)
		{
			 
				var content = '';

				if(new_click)
				{
					new_click = false;
					mostrarPanelResultado(content);  
				}
				else
				{
					$('#panel_info_content').append(content);
				}

		}

		function getInfo(cCapas, coordinate)
		{
					var idCapa = visibleLayers[cCapas];

					var layer = getCapa(idCapa);

					var lyrName = layer.get('name');

					var geoserverLayerId = layer.get('geoserver_layer_id');
				
						var url = layer.getSource().getGetFeatureInfoUrl(
							coordinate, map.getView().getResolution(), map.getView().getProjection(),
							{'INFO_FORMAT': 'text/javascript',
								'query_layers': layer.getSource().getParams()['layers'],
							});

						if (url) {
							var parser = new ol.format.GeoJSON({
								featureProjection:"EPSG:4326"
							});

							$.ajax({
								url: url,
								dataType: 'jsonp',
								jsonpCallback: 'parseResponse'
							}).then(function(response)
							{
								
								var result = parser.readFeatures(response);
								if (result.length) {
									var info = [];
									var keys = result[0].getKeys();

									content='<span>' + lyrName + '</span>';
									content+= '<table class="tabla-info ui celled table">';

									content+= '<tbody>';
									

									for(var j=0; j<keys.length; j++)
									{
										var key = keys[j];
										if(key != 'geometry')
										{

											var encontro = false;
											var etiqueta = '';
											$.each(diccionario, function(i, item) {
												
												if(geoserverLayerId == item.capa && key == item.campo)
												{
													 etiqueta = item.etiqueta;
													 encontro = true;
												}

											});

											if(encontro)
												content+= '<tr><td>'+etiqueta+'</td>' +  '<td>'+result[0].get(key)+'</td></tr>';
											//else content+= '<tr><td>'+key+'</td>' +  '<td>'+result[0].get(key)+'</td></tr>';
										}
									}

										
									
									content+= '</tbody>';
									
									content+="</table>";

									if(new_click)
									{
										new_click = false;
										mostrarPanelResultado(content);  
									}
									else
									{
										$('#panel_info_content').append(content);
									}

									
									lyrResult.getSource().addFeatures(result);
									

								} else {

								//container.html('&nbsp;');


								}


								cCapas++;
								if(cCapas<visibleLayers.length)
									getInfo(cCapas, coordinate);


							});
			
				}
						
			
		}



		function mostrarPanelResultado(content)
		{
			if(panelInfo == null)
				{
					panelInfo = jsPanel.create({
							id: 'panelInfo',
							theme:       'primary',
							contentSize: {
									width: function() { return Math.min(400,window.innerWidth/4)},
									height: function() { return 400;}
							},
							
							position:    'right-top 10 70',
							animateIn:   'jsPanelFadeIn',
							headerTitle: '<i class="info circle icon"></i> Información',
							dragit: {
								snap: true
							},
							content: '<div id="panel_info_content">'+content+'</div>',
							onwindowresize: true
					});
				}
				else
				{
					$('#panel_info_content').html(content);
				}
		}

		function onMapClick(evt)
		{

				clickIdentify(evt);  
		}

		var file_uploaded = '';
		function initControls()
		{

			var uploadFiles = new Dropzone("#upload_image", 
	        { 
		          url: "lib/actions/upload_image.php",
		          maxFiles: 1, //Número máximo de archivos a subir
		          maxFilesize: 2, // Tamaño máximo de los archivos MB
		          addRemoveLinks: true,
		          acceptedFiles: ".jpg, .jpeg, .png",
		          accept: function(file, done) {
		            if (file.name.endsWith(".png") || file.name.endsWith(".jpg") || file.name.endsWith(".jpeg")) {
		              done();

		              file_uploaded = file.name;
		              
		            }
		            else { 
		              done("El formato de los archivos tienen que ser .jpg, .jpeg o .png"); 
		            }
		          },
		          dictRemoveFile: "Eliminar archivo",
		          dictInvalidFileType: "El formato del archivo no es válido"
		        }
		      );


      	uploadFiles.on("complete", function(file) {
        			$('#img_logo_src').val(file_uploaded);
              
					$('#img_logo').attr('src','uploads/'+file_uploaded);

          $('#modalImageGeovisor').modal('hide');
		  });

          
      //document.getElementById("upload_image").classList.add('dropzone');

			$('.dropdown').dropdown();

			$("#base_layer").on( "change", function() {
        selectBaseLayer();
      });


      $('#selectLayer')
			  .dropdown({
			    apiSettings: {
			      url: 'lib/xajax/x_search_layers_by_title.php?title={query}'
			    },
			  })
			;

      $('.ui.form')
			  .form({
			    fields: {
			      geovisor_zoom: {
			        identifier  : 'geovisor-zoom',
			        rules: [
			          {
			            type   : 'integer[1..20]',
			            prompt : 'Introduce un valor entero del 1 al 20'
			          }
			        ]
			      }
			    }
			  })
			;

		}



		function initPopups()
		{
		 
			 

			 $('#modalFormLayer')
					.modal({
						closable  : false,
						allowMultiple: true,
						onDeny    : function(){
							return true;
							},    
							onApprove : function() {
									
									var node;

									var lyr_id;

									var action = $('#form_layer_action').val();

									if(action == "add")
									{
										node = $('#layerTree').tree('getSelectedNode');

										lyr_id =  $('#id_layer_form').val();
									}
									else //edit
									{
										var lyr_edit_id = $('#lyr_edit_id').val();

										node = $('#layerTree').tree('getNodeById', 'lyr'+lyr_edit_id);

										lyr_id = node.lyr_id;
									}

									
									var action = $('#form_layer_action').val();
									var geoserver_layer_id = $('#id_layer_form').val();
									var lyr_name = $('#name_layer_form').val();
									var geoserver_url = $('#geoserver_url_layer_form').val();
									var geoserver_style_id = $('#style_layer_form').val();
									var transparency = $('#transparency_layer_form').val();
									var zindex = $('#zindex_layer_form').val();
									var active = $('#active_layer_form').prop("checked") ? 't': 'f';
									var queryable = $('#queryable_layer_form').prop("checked") ? 't': 'f';


									var lyr_id_sel = geoserver_layer_id;
									if(geoserver_style_id != null && geoserver_style_id != '')
										lyr_id_sel = geoserver_style_id;


									var lyr_html= '<div><i class="map icon"></i> ' + lyr_name + ' <span onclick="activateLayer(\''+lyr_id_sel+'\')"><i class="eye icon"></i></span><span onclick="editLayer(\''+ lyr_id_sel + '\')"><i class="edit icon"></i></span><span onclick="removeLayer(\''+ lyr_id_sel + '\')"><i class="remove icon"></i></span><div> <img src="' + geoserver_url + '/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=' + geoserver_layer_id;
									
									if(geoserver_style_id != null && geoserver_style_id != '')
										lyr_html+=  '&STYLE=' + geoserver_style_id;
									
									lyr_html+= '&LEGEND_OPTIONS=bgColor:0xFFFFFF;fontSize:9;fontColor:0x333333;forceLabels:on&access_token='+  access_token + '"/></div><div>';
									

									var newLayer = new ol.layer.Tile({visible: false, source: new ol.source.TileWMS({url: geoserver_url+'/wms', params: {'FORMAT': 'image/png', 'VERSION': '1.1.1', tiled: true, 
											STYLES: geoserver_layer_id, access_token: access_token, LAYERS: geoserver_layer_id}, serverType: 'geoserver'}), opacity: 0.9});


								  newLayer.set('geoserver_layer_id', geoserver_layer_id);
								  newLayer.set('name', lyr_name);
								  newLayer.setZIndex(zindex);
								  

									if(action == "add")
									{

										var nodeLayer = 
										{   
												id: 'lyr' + lyr_id_sel,
												name: lyr_html,
												type: 'layer',
												geoserver_layer_id: geoserver_layer_id,
												lyr_name: lyr_name,
												geoserver_url: geoserver_url,
												geoserver_style_id: geoserver_style_id,
												transparency: transparency,
												zindex: zindex,
												active: active,
												queryable: queryable
										}

										if(node)
											$('#layerTree').tree( 'addNodeAfter', nodeLayer, node);
										else 
											$('#layerTree').tree( 'appendNode', nodeLayer);

									

										

									}
									else
									if(action == "edit")
									{
										
										
										$('#layerTree').tree('updateNode', node, 
											{
												id: 'lyr' + lyr_id_sel,
												name : lyr_html,
												geoserver_layer_id : geoserver_layer_id,
												lyr_name : lyr_name,
												geoserver_url : geoserver_url,
												geoserver_style_id : geoserver_style_id,
												transparency : transparency,
												zindex : zindex,
												active : active,
												queryable : queryable}
											);

											var layerEdit = lyrsMap[lyr_edit_id];

											if(layerEdit != null)
											{	
												map.removeLayer(layerEdit);
											}

									}


									lyrsMap[lyr_id_sel] = newLayer;
									map.addLayer(newLayer);


									$('#layerTree').tree('refresh');
								}
							});


					$('#modalFormCategory')
					.modal({
						closable  : false,
						onDeny    : function(){
							
							return true;
							},    
							onApprove : function() {
									
									var node;

									var cat_id;


									var action = $('#form_category_action').val();

									var category_name = $('#name_category_form').val();
									var expanded = $('#expanded_layer_form').prop("checked") ? 't': 'f';

									if(action == "add")
									{
										var node = $('#layerTree').tree('getSelectedNode');

										cat_id = 999;
									}
									else
									{
										var category_id = $('#cat_id').val();

										var node = $('#layerTree').tree('getNodeById', 'cat'+category_id);

										cat_id = node.cat_id;

									}

									
									var cat_html = '<div><i class="folder icon"></i> '+category_name+' <span onclick="editCategory('+category_id+')"><i class="edit icon"></i></span><span onclick="removeCategory('+category_id+')"><i class="remove icon"></i></span></div>';								
									
									


									if(action == "add")
									{
										var nodeCategory = 
										{   
											id: 456,
											type: 'category',
											name: cat_html,
											category_name: category_name,
											expanded: expanded
										}


										if(node)
											$('#layerTree').tree( 'addNodeAfter', nodeCategory, node);
										else 
											$('#layerTree').tree( 'appendNode', nodeCategory);

									}
									else
									if(action == "edit")
									{
										
										
										$('#layerTree').tree('updateNode', node, 
											{
												id: 'cat' + category_id,
												name: cat_html,
												category_name: category_name,
												expanded: expanded
											}
											);

									}


									$('#layerTree').tree('refresh');
								}
							});



					$('#modalImageGeovisor')
					.modal({
						closable  : false,
						onDeny    : function(){
							
							return true;
							},    
							onApprove : function() {
									
								}
							});


					$('#modalSearchLayer')
					.modal({
						closable  : false,
						allowMultiple: true,
						onDeny    : function(){
						
						return true;
						},    
						onApprove : function() {
								

								var name_layer = $('#selectLayer').find('.text').html();

								var selected_layer = $('#selected_layer').val();

								$('#id_layer_form').val(selected_layer);
								$('#name_layer_form').val(name_layer);
						}
					});

					$("#modalSearchLayer").modal('attach events', '#modalFormLayer .btn-search-layer')

			}


		function selectBaseLayer()
		{
			 var baseLayer = $("#base_layer").val();


			 switch(baseLayer)
			 {
					case 'bingaerial': 
						lyrBingMapsAerial.setVisible(true);
						lyrBingMapsAerialWithLabels.setVisible(false);
						lyrBingMapsRoad.setVisible(false);
						lyrOSM.setVisible(false);
						lyrStamenTerrain.setVisible(false);
						lyrStamenToner.setVisible(false);
					break;
					case 'bingaeriallabels': 
						lyrBingMapsAerial.setVisible(false);
						lyrBingMapsAerialWithLabels.setVisible(true);
						lyrBingMapsRoad.setVisible(false);
						lyrOSM.setVisible(false);
						lyrStamenTerrain.setVisible(false);
						lyrStamenToner.setVisible(false);
					break;
					case 'bingroad':
						lyrBingMapsAerial.setVisible(false); 
						lyrBingMapsAerialWithLabels.setVisible(false);
						lyrBingMapsRoad.setVisible(true);
						lyrOSM.setVisible(false);
						lyrStamenTerrain.setVisible(false);
						lyrStamenToner.setVisible(false);
					break;
					case 'osm':
						lyrBingMapsAerial.setVisible(false);
						lyrOSM.setVisible(true);
						lyrBingMapsRoad.setVisible(false);
						lyrBingMapsAerialWithLabels.setVisible(false);
						lyrStamenTerrain.setVisible(false);
						lyrStamenToner.setVisible(false);
					break;
					case 'stamen_terrain':
						lyrBingMapsAerial.setVisible(false);
						lyrOSM.setVisible(false);
						lyrBingMapsRoad.setVisible(false);
						lyrBingMapsAerialWithLabels.setVisible(false);
						lyrStamenTerrain.setVisible(true);
						lyrStamenToner.setVisible(false);
					break;
					case 'stamen_toner':
						lyrBingMapsAerial.setVisible(false);
						lyrOSM.setVisible(false);
						lyrBingMapsRoad.setVisible(false);
						lyrBingMapsAerialWithLabels.setVisible(false);
						lyrStamenTerrain.setVisible(false);
						lyrStamenToner.setVisible(true);
					break;
			 }
		}

		function changeLogoImage()
		{
			$('#modalImageGeovisor').modal('show');
		}

		function openSearchLayer()
		{
			//$('#modalSearchLayer').modal('show');
		}

		function newCategory()
		{
				$('#title_category_form').html('<i class="folder icon"></i> Nueva categoría: ');

				$('#form_category_action').val('add');
				$('#name_category_form').val('');
				$('#expanded_layer_form').prop( "checked", false);

				$('#modalFormCategory').modal('show');
		}


		function editCategory(category_id)
		{
				var n = $('#layerTree').tree('getNodeById', 'cat'+category_id);

				$('#form_category_action').val('edit');
				$('#cat_id').val(category_id);

				$('#title_category_form').html('<i class="folder icon"></i> Editar categoría: '+n.category_name);
				$('#name_category_form').val(n.category_name);
				$('#expanded_layer_form').prop( "checked", n.expanded == 't' ? true : false);

				$('#modalFormCategory').modal('show');
		}

		function removeCategory(category_id)
		{
				var n = $('#layerTree').tree('getNodeById', 'cat'+category_id);


				$('#layerTree').tree('removeNode', n);
		}

		function newLayer()
		{

			$('#title_layer_form').html('<i class="map icon"></i> Nueva capa');

			$('#form_layer_action').val('add');
			$('#id_layer_form').val('');
			$('#name_layer_form').val('');
			$('#geoserver_url_layer_form').val('https://territoriosenriesgo.unah.edu.hn/geoserver');
			$('#style_layer_form').val('');
			$('#transparency_layer_form').val('100');
			$('#zindex_layer_form').val('1');
			$('#active_layer_form').prop("checked", false);
			$('#queryable_layer_form').prop("checked", true);

			$('#modalFormLayer').modal('show');
		}

		function editLayer(layer_id)
		{

			var n = $('#layerTree').tree('getNodeById', 'lyr'+layer_id);

			$('#title_layer_form').html('<i class="map icon"></i> Editar capa: '+n.lyr_name);

			$('#form_layer_action').val('edit');
			$('#lyr_edit_id').val(layer_id);

			$('#id_layer_form').val(n.geoserver_layer_id);
			$('#name_layer_form').val(n.lyr_name);
			$('#geoserver_url_layer_form').val(n.geoserver_url);
			$('#style_layer_form').val(n.geoserver_style_id);
			$('#transparency_layer_form').val(n.transparency);
			$('#zindex_layer_form').val(n.zindex);
			$('#active_layer_form').prop( "checked", n.active == 't' ? true : false);
			$('#queryable_layer_form').prop( "checked", n.queryable  == 't' ? true : false);

			$('#modalFormLayer').modal('show');
		}

		function removeLayer(layer_id)
		{
				var n = $('#layerTree').tree('getNodeById', 'lyr'+layer_id);


				$('#layerTree').tree('removeNode', n);
		}




		function getLayersList()
		{

		}

		
		function validateCategoryForm()
		{

		}

		function validateLayerForm()
		{

		}

		var cat_count_id;
		function getTreeNode(node, parent, treeNode)
		{

			if(node.parent == null || node.type == 'category')
			{
				cat_count_id++;
				
				var nodeCat = {"type":node.type,
					"category_id": cat_count_id,
					"category_name":node.category_name,
					"expanded":node.expanded,
					"parent_category_id": parent
				};

				treeNode.push(nodeCat);

				for (var i=0; i < node.children.length; i++) {
			    
			    	var child = node.children[i];

			    	getTreeNode(child, nodeCat.category_id, treeNode);

				}

			}
			else
			{
				treeNode.push({"type":node.type,
					"geoserver_layer_id":node.geoserver_layer_id,
					"lyr_name":node.lyr_name,
					"geoserver_url":node.geoserver_url,
					"geoserver_style_id":node.geoserver_style_id,
					"transparency":node.transparency,
					"zindex":node.zindex,
					"active":node.active,
					"queryable":node.queryable,
					"category_id": parent
				});
			}

		}


		function saveGeovisorForm()
		{
			var valid = true;

			var action = $('#action').val();

			if(action == null || action.trim() == '')
			{
				valid = false;
			}

			var geovisor_id = $('#geovisor_id').val();

			if(geovisor_id == null || geovisor_id.trim() == '')
			{
				valid = false;
			}

			var geovisor_name = $('#geovisor_name').val();

			if(geovisor_name == null || geovisor_name.trim() == '')
			{
				valid = false;
			}

			
			var geovisor_slug = $('#geovisor_slug').val();

			if(geovisor_slug == null || geovisor_slug.trim() == '')
			{
				valid = false;
			}

			var geovisor_is_public = $('#geovisor_is_public').prop("checked") ? 't': 'f';

			var geovisor_title = $('#geovisor_title').val();

			if(geovisor_title == null || geovisor_title.trim() == '')
			{
				valid = false;
			}

			var geovisor_coord = $('#geovisor_coord').val();

			if(geovisor_coord == null || geovisor_coord.trim() == '')
			{
				valid = false;
			}

			var geovisor_zoom = $('#geovisor_zoom').val();

			if(geovisor_zoom == null || geovisor_zoom.trim() == '')
			{
				valid = false;
			}

			var geovisor_zoom_min = $('#geovisor_zoom_min').val();

			if(geovisor_zoom_min == null || geovisor_zoom_min.trim() == '')
			{
				valid = false;
			}

			var geovisor_zoom_max = $('#geovisor_zoom_max').val();

			if(geovisor_zoom_max == null || geovisor_zoom_max.trim() == '')
			{
				valid = false;
			}

			var geovisor_msj_ini = $('#geovisor_msj_ini').val();

			if(geovisor_msj_ini == null || geovisor_msj_ini.trim() == '')
			{
				valid = false;
			}

			var img_logo_src = $('#img_logo_src').val();

			if(img_logo_src == null || img_logo_src.trim() == '')
			{
				valid = false;
			}

			var base_layer = $('#base_layer').val();

			//Validar que sean números: zoom ini, zoom min, zoom max
			//Validar que el zoom ini esté dentro del zoom_ini y zoom_max
			//Validar que el zoom max > zoom min
			//Validar coords

			if(!valid)
				return;


			var node = $('#layerTree').tree('getTree');

			var treeNode = [];

			cat_count_id = -1;

			getTreeNode(node,cat_count_id,treeNode);


			var data = {'geovisor_id':geovisor_id, 'action':action, 'geovisor_name':geovisor_name, 'geovisor_slug':geovisor_slug, 'geovisor_is_public':geovisor_is_public, 'geovisor_title':geovisor_title, 'geovisor_coord':geovisor_coord, 'geovisor_zoom':geovisor_zoom, 'geovisor_zoom_min':geovisor_zoom_min, 'geovisor_zoom_max':geovisor_zoom_max, 'geovisor_msj_ini':geovisor_msj_ini, 'img_logo_src':img_logo_src, 'base_layer':base_layer, 'tree': treeNode};


			$('#btnSaveGeovisor').addClass('disabled');
			$('#btnSaveGeovisor').addClass('loading');
			
			console.log(JSON.stringify(data));

			$.post('lib/xajax/x_set_geovisor.php', JSON.stringify(data)).done(function( response ) {
			    	
					res = JSON.parse(response);

			    	if(res.success)
			    	{
			    		toastr.info('Guardado con éxito.')
			    	}

			    	$('#btnSaveGeovisor').removeClass('disabled');
			    	$('#btnSaveGeovisor').removeClass('loading');
			  });
			
		}



		function validateGeovisorForm()
		{
			

		}	

		function cancelGeovisorForm()
		{
			$(location).attr('href', 'admin.php?&access_token=' + access_token);
		}



		function getLayer(layer)
		{
		  var lyr = null;
		  
		  var lyr = lyrsMap[layer];

		  return lyr;
		}


	  function activateLayer(layer)
	  {
	      var lyr = getLayer(layer);

	 			if(lyr != null)
	      {
	        if(!lyr.getVisible())
	        {
	            lyr.setVisible(true);


	            if(lyr instanceof ol.layer.Tile)
	            {
	              visibleLayers.push(layer);
	            }
	            
	        }
	        else
	        {
	            lyr.setVisible(false);

	            if(lyr instanceof ol.layer.Tile)
	            {
	              visibleLayers.splice(visibleLayers.indexOf(layer),1);
	            }

	        } 
	      }   
	  }




	  function setCurrentCoord()
	  {
	  	var coord = ol.proj.transform(map.getView().getCenter(), 'EPSG:3857', 'EPSG:4326');

	  	var stringifyFunc = ol.coordinate.createStringXY(4);
			var coord_txt = stringifyFunc(coord);

	  	$('#geovisor_coord').val(coord_txt);
	  }

	  function setCurrentZoomIni()
	  {
	  	$('#geovisor_zoom').val(map.getView().getZoom());
	  }

	  function setCurrentZoomMin()
	  {
	  	$('#geovisor_zoom_min').val(map.getView().getZoom());
	  }

	  function setCurrentZoomMax()
	  {
	  	$('#geovisor_zoom_max').val(map.getView().getZoom());
	  }

		
		initTree();
		initMap();
		initControls();
		initPopups();


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
		<p><a href="https://territoriosenriesgo.unah.edu.hn/">Ir a página principal</a></p>
	</body>
</html>

<?php
}
?>