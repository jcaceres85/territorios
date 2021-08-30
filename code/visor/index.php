<?php
require_once("config.php");
require_once("lib/framework/geovisor.php");
require_once("lib/framework/menu.php");
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
    //header("Location: " . $MAIN_SITE_URL);
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
    //header("Location: " . $MAIN_SITE_URL);
    die();
  }
}
else
{
  die();
}



//Consultar los geovisores del mismo tipo de acceso (público o privado)

$is_public = $geovisor["is_public"];



$access_token = $_REQUEST["access_token"];

if($is_public == 'f')
{

  if($access_token == null || $access_token == '')
  {
    //header("Location: " . $MAIN_SITE_URL);
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
    <meta property="og:url"                content="<?php echo $MAIN_SITE_URL;?>/geovisor/" />
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
    <link href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.5.0/css/ol.css" rel="stylesheet" type="text/css">
    <link href="css/ol-ext.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/ol-contextmenu@latest/dist/ol-contextmenu.min.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="css/semantic.min.css" rel="stylesheet" type="text/css"/>
    
    <link href="css/toastr.min.css" rel="stylesheet">
    
    <link href="css/jspanel.css" rel="stylesheet">
    <link href="css/c3.css" rel="stylesheet">
    <style type="text/css">
      

      <?php



        echo ':root {';
        if($geovisor['primary_color'] != null)
          echo ' --primary-color:'.$geovisor['primary_color'].';';
        else 
          echo ' --primary-color:#0d5c85;';

        if($geovisor['secondary_color'] != null)
          echo ' --secondary-color:'.$geovisor['secondary_color'].';';
        else 
          echo ' --secondary-color:#0d5c85;';

        if($geovisor['main_font'] != null)
          echo '--main-font:'.$geovisor['main_font'].';';
        else
          echo '--main-font:"Helvetica Neue Light", "HelveticaNeue-Light", "Helvetica Neue", Calibri, Helvetica, Arial, sans-serif;';

        if($geovisor['main_font_size'] != null)
          echo '--main-font-size:'.$geovisor['main_font_size'].'pt;';
        else
          echo '--main-font-size:9pt';

        echo '}';



      ?>



    </style>

    <link href="css/index.css" rel="stylesheet">

    <?php

      echo "\n";
      if($geovisor['custom_css'] != null)
      {
        echo '<style type="text/css">';
        echo "\n";
        echo $geovisor['custom_css'];
        echo "\n";
        echo '</style>';
      }

    ?>

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
                <div class="item" data-value="esriworldstreetmap">Esri World Streetmap</div>
                <div class="item" data-value="esriworldimagery">Esri World Imagery</div>
                <div class="item" data-value="esriworldterrain">Esri World Terrain</div>
                <div class="item" data-value="esriworldshadedrelief">Esri World Shaded Relief</div>
                <div class="item" data-value="esriworldphysical">Esri World Physical</div>
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
                  
          <button id="toolDragPan" class="ui tiny toggle icon button ctrl-geovisor active" data-content="Mover">
            <i class="mouse pointer icon"></i>
          </button>
           <!-- 
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

        <div id="panel_menu" class="ui menu">

          <div class="ui dropdown icon item">
              <i class="sidebar icon"></i>
              <div class="menu">
                
               
                <?php

                    function menuTree($geovisor_id, $parent_id)
                    {


                        $menus = Menu::get_child_menu($geovisor_id, $parent_id);


                        for($i=0; $i<count($menus); $i++)
                        {
                          $m = $menus[$i];

                          $menu_id = $m['id'];
                          $menu_name = $m['name'];
                          $menu_type = trim($m['type']);
                          $menu_content = $m['content'];

                          switch ($menu_type) {
                            case 'container':
                              echo '<div class="item">';
                              echo '<i class="dropdown icon"></i> <span class="text">'.$menu_name.'</span><div class="menu" tabindex="-1">'; 

                                menuTree($geovisor_id, $menu_id);

                              echo '</div></div>';
                              break;
                            case 'link':
                              echo '<a id="menu'.$menu_id.'" href="'.$menu_content.'" target="_blank" class="ui item button">'.$menu_name.' </a>';
                            break;
                            case 'html':
                              echo '<button id="menu'.$menu_id.'" onclick="openHtmlMenu('.$geovisor_id.','.$menu_id.',\''.$menu_name.'\')" class="ui item button">'.$menu_name.' </button>';
                            break;
                            default:
                              echo '';
                              break;
                          }

  
                        } 

                        

                    }

                    menuTree($geovisor_id, 0);

                ?>
                </div>
            </div>
        </div>

        <div id="context_menu" class="ol-popup-context-menu">
            <a href="#" id="context_menu_closer" class="ol-popup-context-menu-closer"></a>
            <div id="context_menu_content"></div>
        </div>

        <div id="panel_georef">
             <div id="proj">EPSG:4326</div>
             <div id="zoom">Zoom: 10</div>
             <div id="mouse-position"><div class="custom-mouse-position"></div></div>
         </div>

        <img id="logo2" style="height:50px;" src="<?php if($geovisor["logo"] != null || $geovisor["logo"] != '') echo 'uploads/'.$geovisor["logo"]; else $LOGO_IMG_DEFAULT ?>"/>

     	  

      </div>

    </div>

    

    <div id="modal_ini" class="ui basic modal">
      <i class="close icon"></i>
      <div class="ui header center aligned">
       Presentación - <?php echo $geovisor["name"]; ?>
      </div>
      <div class="scrolling content">
        
        
          
          <!--<img class="ui centered large image" style="max-width: 300px;" src="uploads/<?php echo $geovisor["logo"]; ?>"/>-->
       
          <p><?php echo $geovisor["message_ini"]; ?></p>
      </div>
      <div class="actions">
        
        <div class="ui ctrl-geovisor positive right labeled icon button">
          Ok
          <i class="checkmark icon"></i>
        </div>
      </div>
    </div>



    
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.5.0/build/ol.js"></script>
    <script src="js/ol/ol-ext.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ol-contextmenu"></script>

    <script src="js/jsts.min.js" type="text/javascript"></script>

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
   
      var coord_ini = ol.proj.transform([<?php echo $geovisor["coord_ini"];?>], 'EPSG:4326', 'EPSG:3857');
      var zoom_ini = <?php echo $geovisor["zoom_ini"];?>;
      var zoom_min = <?php echo $geovisor["zoom_min"];?>;
      var zoom_max = <?php echo $geovisor["zoom_max"];?>;
      var base_layer = '<?php echo $geovisor["base_layer"];?>';
      var access_token = '<?php echo $access_token; ?>';
      var geovisor_id = '<?php echo $geovisor_id; ?>';
   
    </script>

    <script type="text/javascript" src="js/geo.map.min.js"></script>

      
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
