<?php
require_once("lib/framework/geovisor.php");
require_once("lib/framework/oauth.php");

$geovisor_id = $_REQUEST["geovisor_id"];

$slug = $_REQUEST["url"];


if($geovisor_id != null && trim($geovisor_id) != '')
{
  $g_res = Geovisor::get($geovisor_id);

  if(count($g_res)>0)
  {
    $geovisor = $g_res[0];
  }
  else
  {
    echo 'Geovisor no encontrado. Redireccionando';
    //header("Location: https://territoriosenriesgo.unah.edu.hn/");
    die();
  }

}
else if($slug != null && trim($slug) != '')
{
  $g_res = Geovisor::get_by_slug($slug);

  if(count($g_res)>0)
  {
    $geovisor = $g_res[0];

    $geovisor_id = $geovisor["id"];
  }
  else
  {
    //header("Location: https://territoriosenriesgo.unah.edu.hn/");
    die();
  }
}



//Consultar los geovisores del mismo tipo de acceso (público o privado)

$is_public = $geovisor["is_public"];



$access_token = $_REQUEST["access_token"];

if($is_public == 'f')
{

  if($access_token == null || $access_token == '')
  {
    //header("Location: https://territoriosenriesgo.unah.edu.hn/");
    die();
  }

  $session_expired = OAuth::is_session_expired($access_token);
}




if($is_public == 't' || ($is_public == 'f' && $session_expired == 'f'))
{

?>
<!DOCTYPE html>
<html>
<head>
    <meta property="og:url"                content="https://www.mexicali.gob.mx/sitioimip/geovisor/" />
    <meta property="og:type"               content="article" />
    <meta property="og:title"              content="Geovisor" />
    <meta property="og:description"        content="" />
    <meta property="og:image"              content="img/preview.png" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $geovisor["title"]; ?></title>
    
    <link href="plugins/fontawesome/css/fontawesome.min.css" rel="stylesheet">
    <link href="plugins/fontawesome/css/solid.css" rel="stylesheet">

    <link href="css/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/css/ol.css" rel="stylesheet" type="text/css">
    <link href="css/ol-contextmenu.min.css" rel="stylesheet" type="text/css"/>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="css/semantic.min.css" rel="stylesheet" type="text/css"/>
    
    <link href="css/toastr.min.css" rel="stylesheet">
    
    <link href="css/jspanel.css" rel="stylesheet">
    <link href="css/c3.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-182942260-2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-182942260-2');
    </script>


</head>
<body>

   
    <div id="content">
      
      <div id="map" class="map">
      
        <div id="panel_tools" class="ui buttons">
           
          <div class="ui icon top left pointing dropdown button ctrl-geovisor" data-content="Cambiar capa base">
            <input type="hidden" id="select_capa_base" value="<?php echo $geovisor['base_layer'] ?>">
            <i class="globe icon"></i>
            <div class="menu">
                <div class="header">Capa base</div>
                <div class="item" data-value="osm">OpenStreetMap</div>
                <div class="item" data-value="bingaeriallabels">Bing Aerial con etiquetas</div>
                <div class="item" data-value="bingaerial">Bing Aerial</div>
                <div class="item" data-value="bingroad">Bing Road</div>
                <div class="item" data-value="stamen_terrain">Stamen Terrain</div>
                <div class="item" data-value="stamen_toner">Stamen Toner</div>
              </div>
          </div>

    
          <button id="toolGraticule" class="ui tiny toggle icon button ctrl-geovisor" data-content="Mostrar gradícula">
            <i class="fa fa-border-all"></i>
          </button>
          <button id="toolHome" class="ui tiny toggle icon button ctrl-geovisor" data-content="Ir a vista inicial">
            <i class="home icon"></i>
          </button>
          <!--          
          <button id="toolDragPan" class="ui tiny toggle icon button ctrl-geovisor active" data-content="Mover">
            <i class="hand pointer icon"></i>
          </button>
          <button id="toolIdentify" class="ui tiny toggle icon button ctrl-geovisor" data-content="Identificar">
            <i class="info icon"></i>
          </button>

          <button id="toolZoomIn" class="ui tiny toggle icon button ctrl-geovisor" data-content="Acercar">
            <i class="zoom-in icon"></i>
          </button>
          <button id="toolZoomOut" class="ui tiny toggle icon button ctrl-geovisor" data-content="Alejar">
            <i class="zoom-out icon"></i>
          </button>
          -->
          <button id="toolZoomBack" class="ui tiny toggle icon button ctrl-geovisor" data-content="Atrás">
            <i class="arrow left icon"></i>
          </button>
          <button id="toolZoomForward" class="ui tiny toggle icon button disabled ctrl-geovisor" data-content="Adelante">
            <i class="arrow right icon"></i>
          </button>
          <button id="toolMeasure" class="ui tiny toggle icon button ctrl-geovisor" data-content="Medición">
            <i class="fa fa-ruler"></i>
          </button>
          <button id="toolExport" class="ui tiny toggle icon button ctrl-geovisor" data-content="Descargar">
            <i class="fa fa-download"></i>
          </button>
          
        </div>

        <select id="typeMeasure" style="display:none;">
          <option value="length">Distancia</option>
          <option value="area">Área</option>
        </select>

        <div id="context_menu" class="ol-popup-context-menu">
            <a href="#" id="context_menu_closer" class="ol-popup-context-menu-closer"></a>
            <div id="context_menu_content"></div>
        </div>

        <div id="panel_georef">
             <div id="proj">EPSG:4326</div>
             <div id="zoom">Zoom: 10</div>
             <div id="mouse-position"><div class="custom-mouse-position"></div></div>
         </div>

        <img id="logo2" style="height:50px;" src="img/logos/logos.png"/>

     	  

      </div>

    </div>

    

    <div id="modal_ini" class="ui basic modal">
      <i class="close icon"></i>
      <div class="header">
       Presentación - <?php echo $geovisor["name"]; ?>
      </div>
      <div class="scrolling content">
        
        
          
          <img class="ui centered large image" style="max-width: 300px;" src="uploads/<?php echo $geovisor["logo"]; ?>"/>
          <br>
          <p><?php echo $geovisor["message_ini"]; ?></p>
      </div>
      <div class="actions">
        
        <div class="ui positive right labeled icon button">
          Ok
          <i class="checkmark icon"></i>
        </div>
      </div>
    </div>



    
    <script src="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/build/ol.js"></script>
    <script src="js/ol/ol-contextmenu.js"></script>
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
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>

    <!-- loading jsPanel javascript -->
    <script src="js/jspanel/jspanel.js"></script>
    <!-- optionally load jsPanel extensions -->
    <script src="js/jspanel/contextmenu/jspanel.contextmenu.js"></script>
    <script src="js/jspanel/hint/jspanel.hint.js"></script>
    <script src="js/jspanel/modal/jspanel.modal.js"></script>
    <script src="js/jspanel/tooltip/jspanel.tooltip.js"></script>
    <script src="js/jspanel/dock/jspanel.dock.js"></script>
    <script>
    



    var map, map_extent, map_view;
    var coord_ini, zoom_ini;
    var graticule, overview;
    var activeTool;
    var lyr;
    var lyrBingMapsAerialWithLabels, lyrBingMapsAerial, lyrBingMapsRoad, lyrOSM, lyrStamenTerrain, lyrStamenToner;
    
    var select, lyrResult;
    var panelInfo, panelMetadatos;

    var new_click = false;

    var lyrsMap = {};
    var visibleLayers = [];

    var measureLayer;
    var sourceMeasure;
    var drawMeasure;
    var pointerMoveHandler;
    var sketch;
    var helpTooltipElement;
    var helpTooltip;
    var measureTooltipElement;
    var measureTooltip;

    var navigationHistory = [];
    var navigationHistoryFwd = [];
    var shouldUpdate = true;
    var firstNav = true;


    function initMap()
    {
      
      var imgResult = new ol.style.Circle({
        radius: 7,
        fill: null,
        stroke: new ol.style.Stroke({color: 'yellow', width: 2})
      });

      var stylesResult = {
        'Point': new ol.style.Style({
          image: imgResult
        }),
        'LineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            width: 2
          })
        }),
        'MultiLineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            width: 2
          })
        }),
        'MultiPoint': new ol.style.Style({
          image: imgResult
        }),
        'MultiPolygon': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            width: 2
          }),
          fill: new ol.style.Fill({
            color: 'rgba(255, 255, 0, 0.1)'
          })
        }),
        'Polygon': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            lineDash: [4],
            width: 3
          }),
          fill: new ol.style.Fill({
            color: 'rgba(255, 255, 0, 0.1)'
          })
        }),
        'GeometryCollection': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            width: 2
          }),
          fill: new ol.style.Fill({
            color: 'yellow'
          }),
          image: new ol.style.Circle({
            radius: 10,
            fill: null,
            stroke: new ol.style.Stroke({
              color: 'yellow'
            })
          })
        }),
        'Circle': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            width: 2
          }),
          fill: new ol.style.Fill({
            color: 'rgba(255, 255, 0, 0.1)'
          })
        })
      };

      var styleResultFunction = function(feature) {
        return stylesResult[feature.getGeometry().getType()];
      };


      coord_ini = ol.proj.transform([<?php echo $geovisor["coord_ini"];?>], 'EPSG:4326', 'EPSG:3857');
      zoom_ini = <?php echo $geovisor["zoom_ini"];?>;
      zoom_min = <?php echo $geovisor["zoom_min"];?>;
      zoom_max = <?php echo $geovisor["zoom_max"];?>;

      map_view = new ol.View({
          center: coord_ini,
          zoom: zoom_ini,
          minZoom: zoom_min,
          maxZoom: zoom_max
      });


      lyrBingMapsAerial = new ol.layer.Tile({
        visible: <?php if($geovisor['base_layer'] == 'bingaerial') echo 'true'; else echo 'false'; ?>,
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
        visible: <?php if($geovisor['base_layer'] == 'bingaeriallabels') echo 'true'; else echo 'false'; ?>,
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
        visible: <?php if($geovisor['base_layer'] == 'bingroad') echo 'true'; else echo 'false'; ?>,
        preload: Infinity,
        queryable: false,
        source: new ol.source.BingMaps({
          key: 'AiXpl_iJ9vka91ugxW5OybC2RWcH9RJvBjbQDzh9TgZryPAP9-YdO6ig1Bj-qV85',
          imagerySet: 'Road'
        })
      });
     

      lyrOSM  = new ol.layer.Tile({
        visible: <?php if($geovisor['base_layer'] == 'osm') echo 'true'; else echo 'false'; ?>,
        source: new ol.source.OSM()
      });


      lyrStamenTerrain = new ol.layer.Tile({
        visible: <?php if($geovisor['base_layer'] == 'stamen_terrain') echo 'true'; else echo 'false'; ?>,
        source: new ol.source.Stamen(
        {
          layer:'terrain'
        })

      });

      lyrStamenToner = new ol.layer.Tile({
        visible: <?php if($geovisor['base_layer'] == 'stamen_toner') echo 'true'; else echo 'false'; ?>,
        queryable: false,
        source: new ol.source.Stamen({
              layer: 'toner'
            })
      });


      lyrResult = new ol.layer.Vector({source: new ol.source.Vector({}), style: styleResultFunction});
      lyrResult.setZIndex(99);


      var lyrWaterMark = new ol.layer.Tile({visible: false, source: new ol.source.TileWMS({url: 'img/watermark/watermark.png', 
      	params: {'FORMAT': 'image/png', 'VERSION': '1.1.1', tiled: true, STYLES: '',  LAYERS: 'parque_zona_ind'}}), opacity: 0.1, crossOrigin: null}); 
      lyrWaterMark.setZIndex(99);


      map = new ol.Map({
        layers: [lyrBingMapsAerialWithLabels, lyrBingMapsAerial, lyrBingMapsRoad, lyrOSM, lyrStamenTerrain, lyrStamenToner, lyrResult, lyrWaterMark],
        interactions: ol.interaction.defaults({
          altShiftDragRotate: true,
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


      function centerMap(obj) {
		  map.getView().animate({
		    duration: 500,
		    center: obj.coordinate
		  });
	  }


      var contextmenu = new ContextMenu({
		  width: 170,
		  defaultItems: true, // defaultItems are (for now) Zoom In/Zoom Out
		  items: [
		    {
		      text: 'Centrar mapa aquí',
		      icon: 'img/icons/center.png',
		      callback: centerMap // `center` is your callback function
		    },
		    {
		      text: 'Identificar',
		      icon: 'img/icons/info.png',
		      callback: clickIdentify // `center` is your callback function
		    }
		  ]
		});

	  map.addControl(contextmenu);

      graticule = new ol.Graticule({
        // the style to use for the lines, optional.
        strokeStyle: new ol.style.Stroke({
          color: 'rgba(0,0,0,0.9)',
          width: 2,
        }),
        showLabels: true
      });
      

      if (window.location.hash !== '') {
        
        var hash = window.location.hash.replace('#map=', '');
        var parts = hash.split('/');
        if (parts.length === 4) {
          zoom = parseInt(parts[0], 10);
          center = [
            parseFloat(parts[1]),
            parseFloat(parts[2])
          ];
          rotation = parseFloat(parts[3]);
        }
      }

      
      var view = map.getView();

      var updatePermalink = function() {
        
        
        if (shouldUpdate == false) {
         
          shouldUpdate = true;


          if(navigationHistory.length == 0)
          {
            var state = {
              zoom: map.getView().getZoom(),
              center: map.getView().getCenter(),
              rotation: map.getView().getRotation()
            };

            navigationHistory.push(state);
          }

          return;
        }

        var center = view.getCenter();
        var hash = '#map=' +
            view.getZoom() + '/' +
            Math.round(center[0] * 100) / 100 + '/' +
            Math.round(center[1] * 100) / 100 + '/' +
            view.getRotation();
        
        var state = {
          zoom: view.getZoom(),
          center: view.getCenter(),
          rotation: view.getRotation()
        };


        navigationHistoryFwd = [];

        navigationHistory.push(state);

        firstNav = true;


        //$('#toolZoomForward').addClass('disabled');

        //window.history.pushState(state, 'map', hash);
      };

      map.on('moveend', updatePermalink);


      /*
      window.addEventListener('popstate', function(){
      	
        if (event.state === null) {
          return;
        }

        map.getView().setCenter(event.state.center);
        map.getView().setZoom(event.state.zoom);
        map.getView().setRotation(event.state.rotation);
        shouldUpdate = false;

      });*/
      

      
      map.on('click', function(evt) {
        onMapClick(evt); 
      });
      
      activeTool = 'panzoom';

      initDictionary();
      initMeasure();
    }

    

    function initMeasure()
    {
      sourceMeasure = new ol.source.Vector();

      measureLayer = new ol.layer.Vector({
        source: sourceMeasure,
        style: new ol.style.Style({
          fill: new ol.style.Fill({
            color: 'rgba(255, 255, 255, 0.3)'
          }),
          stroke: new ol.style.Stroke({
            color: '#c6970a',
            width: 2
          }),
          image: new ol.style.Circle({
            radius: 7,
            fill: new ol.style.Fill({
              color: '#c6970a'
            })
          })
        })
      });

      measureLayer.setZIndex(99);

      map.addLayer(measureLayer);
    }


    function addInteractionMeasure() {

      var continuePolygonMsg = 'Clic para continuar dibujando el polígono';

      var continueLineMsg = 'Clic para continuar dibujando la línea';


      pointerMoveHandler = function(evt) {
        if (evt.dragging) {
          return;
        }
        /** @type {string} */
        var helpMsg = 'Clic para empezar a dibujar';

        if (sketch) {
          var geom = (sketch.getGeometry());
          if (geom instanceof ol.geom.Polygon) {
            helpMsg = continuePolygonMsg;
          } else if (geom instanceof ol.geom.LineString) {
            helpMsg = continueLineMsg;
          }
        }

        helpTooltipElement.innerHTML = helpMsg;
        helpTooltip.setPosition(evt.coordinate);

        helpTooltipElement.classList.remove('hidden');
      };

      map.on('pointermove', pointerMoveHandler);

      map.getViewport().addEventListener('mouseout', function() {
        helpTooltipElement.classList.add('hidden');
      });

      var typeSelect = $('#typeMeasure');

      typeSelect.on('change', function() {
        map.removeInteraction(drawMeasure);
        addInteractionMeasure();
      });

      
        var type = (typeSelect.val() == 'area' ? 'Polygon' : 'LineString');
        
        drawMeasure = new ol.interaction.Draw({
          source: sourceMeasure,
          type: type,
          style: new ol.style.Style({
            fill: new ol.style.Fill({
              color: 'rgba(255, 255, 255, 0.2)'
            }),
            stroke: new ol.style.Stroke({
              color: 'rgba(0, 0, 0, 0.5)',
              lineDash: [10, 10],
              width: 2
            }),
            image: new ol.style.Circle({
              radius: 5,
              stroke: new ol.style.Stroke({
                color: 'rgba(0, 0, 0, 0.7)'
              }),
              fill: new ol.style.Fill({
                color: 'rgba(255, 255, 255, 0.2)'
              })
            })
          })
        });

        map.addInteraction(drawMeasure);

        createMeasureTooltip();
        createHelpTooltip();

        var listener;
        drawMeasure.on('drawstart',
          function(evt) {
            // set sketch
            sketch = evt.feature;

            /** @type {module:ol/coordinate~Coordinate|undefined} */
            var tooltipCoord = evt.coordinate;

            listener = sketch.getGeometry().on('change', function(evt) {
              
              var geom = evt.target;
              
              var output;
              if (geom instanceof ol.geom.Polygon) {
                
                output = formatArea(geom);
                tooltipCoord = geom.getInteriorPoint().getCoordinates();
              } else if (geom instanceof ol.geom.LineString) {
                
                output = formatLength(geom);
                tooltipCoord = geom.getLastCoordinate();
              }
              measureTooltipElement.innerHTML = output;
              measureTooltip.setPosition(tooltipCoord);
            });
          }, this);

        drawMeasure.on('drawend',
          function() {
            measureTooltipElement.className = 'tooltip tooltip-static';
            measureTooltip.setOffset([0, -7]);
            // unset sketch
            sketch = null;
            // unset tooltip so that a new one can be created
            measureTooltipElement = null;
            createMeasureTooltip();
            ol.Observable.unByKey(listener);
          }, this);
    }

    function formatLength(line) {

      var length = ol.sphere.getLength(line);
      var output;
      if (length > 100) {
        output = (Math.round(length / 1000 * 100) / 100) +
            ' ' + 'km';
      } else {
        output = (Math.round(length * 100) / 100) +
            ' ' + 'm';
      }
      return output;
    };

    function formatArea(polygon) {
      var area = ol.sphere.getArea(polygon);
      var output;
      if (area > 10000) {
        output = (Math.round(area / 1000000 * 100) / 100) +
            ' ' + 'km<sup>2</sup>';
      } else {
        output = (Math.round(area * 100) / 100) +
            ' ' + 'm<sup>2</sup>';
      }
      return output;
    };

    function createHelpTooltip() {
        if (helpTooltipElement) {
          helpTooltipElement.parentNode.removeChild(helpTooltipElement);
        }
        helpTooltipElement = document.createElement('div');
        helpTooltipElement.className = 'tooltip hidden';
        helpTooltip = new ol.Overlay({
          element: helpTooltipElement,
          offset: [15, 0],
          positioning: 'center-left'
        });
        map.addOverlay(helpTooltip);
      }


      function createMeasureTooltip() {
        if (measureTooltipElement) {
          measureTooltipElement.parentNode.removeChild(measureTooltipElement);
        }
        measureTooltipElement = document.createElement('div');
        measureTooltipElement.className = 'tooltip tooltip-measure';
        measureTooltip = new ol.Overlay({
          element: measureTooltipElement,
          offset: [0, -15],
          positioning: 'bottom-center'
        });
        map.addOverlay(measureTooltip);
      }

    function clickPanZoom(evt)
    {

    }

    function clickIdentify(evt)
    {
      identify(evt.coordinate);
    }

    function identify(coordinate)
    {
      new_click = true;
          
      lyrResult.getSource().clear();

      if(visibleLayers.length > 0)
        getInfo(0, coordinate);
    }

    function clickZoomIn(evt)
    {
      map.getView().setZoom(map.getView().getZoom()+1);
      map.getView().setCenter(evt.coordinate);
    }

    function clickZoomOut(evt)
    {
      map.getView().setZoom(map.getView().getZoom()-1);
      map.getView().setCenter(evt.coordinate);
    }


    function exportMapImage(event)
    {
		map.once('rendercomplete', function(event) {
	      var canvas = event.context.canvas;
	      if (navigator.msSaveBlob) {
	        navigator.msSaveBlob(canvas.msToBlob(), 'map.png');
	      } else {
	        canvas.toBlob(function(blob) {
	          saveAs(blob, 'map.png');
	        });
	      }
	    });
	    map.renderSync();
	  	
    }

    function doPanZoom(location, zoom_level) {

        map_view.animate({center: location, zoom: zoom_level, duration: 500});
    }

    function doPanZoomRotate(location, zoom_level, rotation) {

        map_view.animate({center: location, zoom: zoom_level, rotation: rotation, duration: 200});
    }

    var diccionario = [];
    function initDictionary()
    {
      
         $.ajax({
            url: 'lib/xajax/x_get_dictionary.php',
            dataType: 'json',
            success: function(response) {

                dictionary = response.results;
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(textStatus + " - " + errorThrown);
            }
         });

    }

    function showResult(f)
    {
       
        var content = '';

        if(new_click)
        {
          new_click = false;
          showPanelResult(content);  
        }
        else
        {
          $('#panel_info_content').append(content);
        }

    }

    function getInfo(cCapas, coordinate)
    {
          var idCapa = visibleLayers[cCapas];

          var layer = getLayer(idCapa);

          var lyrName = layer.get('name');

          var geoserverLayerId = layer.get('geoserver_layer_id');
        
          var url = layer.getSource().getGetFeatureInfoUrl(
            coordinate, map.getView().getResolution(), map.getView().getProjection(),
            {'INFO_FORMAT': 'application/json',
              'query_layers': layer.getSource().getParams()['layers'],
          });

            if (url) {
              var parser = new ol.format.GeoJSON({
                featureProjection:"EPSG:4326"
              });

              $.getJSON(url, function( data ) {
                
                var result = parser.readFeatures(data);
                if (result.length) {
                  var info = [];
                  var keys = result[0].getKeys();

                  content='<span>' + lyrName + ' (' + result.length + ')' + '</span>';
                  content+= '<table class="tabla-info ui celled table">';

                  content+= '<tbody>';
                  

                  var rowLabels = []; 

                  for(var j=0; j<keys.length; j++)
                  {
                    var key = keys[j];
                    if(key != 'geometry')
                    {

                      var found = false;
                      var order = 0;
                      var label = '';
                      $.each(dictionary, function(i, item) {
                        
                        if(geoserverLayerId == item.layer && key == item.attribute)
                        {
                           label = item.label;
                           display_order = item.display_order;
                           found = true;
                        }

                      });

                      if(found)
                      {
                        rowLabels.push({label:label,value:result[0].get(key),display_order:display_order});
                      }
                        
                      //else content+= '<tr><td>'+key+'</td>' +  '<td>'+result[0].get(key)+'</td></tr>';
                    }
                  }

                  for(var j=0; j<rowLabels.length-1; j++)
                  {
                    for(var i=j; i<rowLabels.length; i++)
                    {
                        if(parseInt(rowLabels[j].display_order)>parseInt(rowLabels[i].display_order))
                        {
                            var ban = rowLabels[j];
                            rowLabels[j] = rowLabels[i];
                            rowLabels[i] = ban;
                        }
                    }
                  }

                  for(var j=0; j<rowLabels.length; j++)
                  {
                    content+= '<tr><td>'+rowLabels[j].label+'</td>' +  '<td>'+rowLabels[j].value+'</td></tr>';
                  }
                  
                  
                  content+= '</tbody>';
                  
                  content+="</table>";

                  if(new_click)
                  {
                    new_click = false;
                    showPanelResult(content);
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



    function showPanelResult(content)
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

      if(activeTool == 'panzoom')
      {
        clickPanZoom(evt);
      }
      else if(activeTool == 'identify')
      {
        clickIdentify(evt);  
      }
      else if(activeTool == 'zoomin')
      {
        clickZoomIn(evt);
      }
      else if(activeTool == 'zoomout')
      {
        clickZoomOut(evt); 
      }
      else if(activeTool == 'measure')
      {
        
      }
      
    }

    function initControls()
    {

      activateTool('identify');

      $("#select_capa_base").on( "change", function() {
        selectBaseLayer();
      });

      $("#select_geovisor").on( "change", function() {
        selectGeovisor();
      });

      $('.dropdown').dropdown();

      $('#toolHome').click(function() {
        doPanZoom(coord_ini, zoom_ini);
      });

      $('#toolExport').click(function(event) {
        exportMapImage(event);
      });


      $('.ctrl-geovisor')
        .popup();
      

      $('#toolDragPan').click(function() {
          
        deactivateTool();
        if(!$(this).hasClass('active'))
        {
          //$('#map').awesomeCursor('pointer');
          document.getElementById("map").style.cursor = "default";
          activateTool('panzoom');
        }

      });

      $('#toolIdentify').click(function() {
        deactivateTool();
        if(!$(this).hasClass('active'))
        {
          //$('#map').awesomeCursor('pointer');
          //document.getElementById("map").style.cursor = "help";
          activateTool('identify');
        }

      });

      $('#toolZoomIn').click(function() {
        deactivateTool();
        if(!$(this).hasClass('active'))
        {
          //$('#map').awesomeCursor('search-plus');
          document.getElementById("map").style.cursor = "zoom-in";
          
          activateTool('zoomin');
        }
        
      });

      $('#toolZoomOut').click(function() {
        deactivateTool();
        if(!$(this).hasClass('active'))
        {
          //$('#map').awesomeCursor('search-plus');
          document.getElementById("map").style.cursor = "zoom-out";
          activateTool('zoomout');
        }
      });

      $('#toolZoomBack').click(function() {
         
         var state = navigationHistory.pop();


         if(firstNav)
         {
            navigationHistoryFwd.push(state);

            state = navigationHistory.pop();
            firstNav = false; 
         }

         if(state != null && state != undefined)
         {
            
            navigationHistoryFwd.push(state);


            doPanZoomRotate(state.center, state.zoom, state.rotation);
            

            shouldUpdate = false;

            $('#toolZoomForward').removeClass('disabled');
         }


      });

      $('#toolZoomForward').click(function() {
         
         var state = navigationHistoryFwd.pop();
         
         if(state != null && state != undefined)
         {
            navigationHistory.push(state);

            doPanZoomRotate(state.center, state.zoom, state.rotation);

            shouldUpdate = false;
         }
         else
         {
            $('#toolZoomForward').addClass('disabled');
         }

      });

      $('#toolMeasure').click(function() {
        
        deactivateTool();
        if(!$(this).hasClass('active'))
        {
          activateTool('measure');
        }

      });
      
      $('#toolGraticule').click(function() {
        if($(this).hasClass('active'))
        {
          graticule.setMap(null);
          $(this).removeClass('active');
        }
        else
        {
          graticule.setMap(map);
          $(this).addClass('active');
        }
          
      });


    }


    function activateTool(tool)
    { 
      switch(tool)
      {
        case 'panzoom':
          $('#toolDragPan').addClass('active');
        break;
        case 'identify':
          $('#toolIdentify').addClass('active');
        break;
        case 'zoomin':
          $('#toolZoomIn').addClass('active');
        break;
        case 'zoomout':
          $('#toolZoomOut').addClass('active');
        break;
        case 'measure': 
          $('#typeMeasure').show();
          addInteractionMeasure();
          $('#toolMeasure').addClass('active');
        break;
      }


      activeTool = tool;
    }

    function deactivateTool()
    {
      switch(activeTool)
      {
        case 'panzoom':
          $('#toolDragPan').removeClass('active');
        break;
        case 'identify':
          $('#toolIdentify').removeClass('active');
        break;
        case 'zoomin':
          $('#toolZoomIn').removeClass('active');
        break;
        case 'zoomout':
          $('#toolZoomOut').removeClass('active');
        break;
        case 'measure':
          map.removeInteraction(drawMeasure);
          sourceMeasure.clear();
          $('#typeMeasure').hide(); 
          map.getOverlays().getArray().slice(0).forEach(function(overlay) {
            map.removeOverlay(overlay);
          });
          $('#toolMeasure').removeClass('active');
          ol.Observable.unByKey(pointerMoveHandler);
          
        break;
      }
    }


    function initPopups()
    {
     
        var contextMenu =  document.getElementById('context_menu');
        var closerContextMenu = document.getElementById('context_menu_closer');

        lyrContextMenu = new ol.Overlay({
          element: contextMenu,
          autoPan: true,
          autoPanAnimation: {
            duration: 250
          }
        });


        closerContextMenu.onclick = function() {
          lyrContextMenu.setPosition(undefined);
          closerContextMenu.blur();


          bufferedLayer.getSource().clear();

          return false;
        };

        map.addOverlay(lyrContextMenu);

    }


    function selectBaseLayer()
    {
       var baseLayer = $("#select_capa_base").val();


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

    function selectGeovisor()
    {
    	var geovisor_extra_id = $("#select_geovisor").val();
    	var geovisor_extra_text = $("#select_geovisor").text();

    	jsPanel.create({
            id: 'panelCapas' + geovisor_extra_id,
	          theme: 'primary',
	          contentSize: {
	              width: function() { return Math.min(400, window.innerWidth*0.5);},
	              height: function() {  return window.innerHeight-300;}
	          },
	          position:    'left-top 30 40',
	          animateIn:   'jsPanelFadeIn',
	          headerTitle: '<i class="map marker icon"></i> Capas de información ' + geovisor_extra_text,
	          headerControls: {
	              
	          },
	          dragit: {
              containment: [60, 10, 10, 10]
	          },
	          content:     function (panel) {
	              $(this.content).load('ctrl/layer_control.php?geovisor_id=' + geovisor_extra_id + '&access_token=<?php echo $access_token; ?>', function () {
	              });
	          },
	          onwindowresize: true
	       });

    }

    </script>

    <script>
        initMap();
        initControls();
        initPopups();

        $('#modal_ini').modal('show');

        jsPanel.create({
            id: 'panelCapas',
	          theme: 'primary',
	          contentSize: {
	              width: function() { return Math.min(400, window.innerWidth*0.5);},
	              height: function() {  return window.innerHeight-200;}
	          },
	          position:    'left-top 10 70',
	          animateIn:   'jsPanelFadeIn',
	          headerTitle: '<i class="map marker icon"></i> <b>Panel de información geográfica</b> ',
	          headerControls: {
	              close: 'remove',
                maximize: 'remove',
	          },
	          dragit: {
              containment: [60, 10, 10, 10]
	          },
	          content:     function (panel) {
	              $(this.content).load('ctrl/layer_control.php?geovisor_id=<?php echo $geovisor_id . "&access_token=". $access_token; ?>', function () {

	              });
	          },
	          onwindowresize: true
	       });


	       document.addEventListener('jspanelclosed', function (event) {
            
            switch(event.detail) 
            {
              case 'panelMetadatos': panelMetadatos = null; break;
              case 'panelInfo': panelInfo = null; lyrResult.getSource().clear(); break;
              case 'panelSearchResult': panelSearchResult = null;  lyrResult.getSource().clear(); break;
            }
        });




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
    <p><a href="https://www.mexicali.gob.mx/sitioimip/geovisor/">Ir a página principal</a></p>
  </body>
</html>

<?php
}
?>