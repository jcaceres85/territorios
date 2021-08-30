<?php
require_once("config.php");
require_once("lib/framework/geovisor.php");
require_once("lib/framework/oauth.php");

$error_no = $_REQUEST["error_no"];

$access_token = $_REQUEST["access_token"];

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

$list_geovisor = Geovisor::get_all();

?>
<!DOCTYPE html>
<html>
<head>
    <meta property="og:url"                content="http://geocomunes.org/Visualizadores/ccmss" />
    <meta property="og:type"               content="article" />
    <meta property="og:title"              content="Geovisualizador de la Peninsula de Yucatan" />
    <meta property="og:description"        content="" />
    <meta property="og:image"              content="img/preview.png" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Geovisores</title>
    
    <link href="plugins/fontawesome/css/fontawesome.min.css" rel="stylesheet">
    <link href="plugins/fontawesome/css/solid.css" rel="stylesheet">

    <link href="css/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/css/ol.css" rel="stylesheet" type="text/css">
    <link href="css/ol-contextmenu.min.css" rel="stylesheet" type="text/css"/>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="css/semantic.min.css" rel="stylesheet" type="text/css"/>
    
    
    <link href="css/jspanel.css" rel="stylesheet">
    <link href="css/c3.css" rel="stylesheet">

    <style>

    body
    {
      margin-top: 100px;
      line-height: 1.4285em;
    }

    </style>

    <script type="text/javascript">
      
    var access_token =  "<?php echo $access_token; ?>";


    </script>


</head>
<body>


      <h2 class="ui center aligned header">Administrador de Geovisores</h2>

      <?php 
      
        if($error_no != null)
        {

          switch ($error_no) {
            case '101':
                $error_msj = 'No existe el geovisor especificado.';
              break;
            
            default:
                $error_msj = '';
              break;
          }


          echo '<div class="ui center aligned header error" style="color:#F00;" id="error_msj">'.$error_msj.'</div>';

        }
    
      ?>
      <div class="ui container">
        

        <div class="ui menu">
          <div class="item">
            <div id="btn-new" class="ui primary button">Nuevo</div>
          </div>
          <div class="item">
            <div id="btn-geoportal" class="ui button">Geoportal</div>
          </div>
        </div>


        <h3 class="ui center aligned header">Lista de Geovisores</h3>

        <div class="ui relaxed divided items">

          <?php

                for($i=0; $i<count($list_geovisor); $i++)
                {
                  $g = $list_geovisor[$i];
                    echo '<div class="item">';
                      echo '<div class="ui small image">';
                        echo '<img src="uploads/'.$g["logo"].'">';
                      echo '</div>';
                      echo '<div class="content">';
                        echo '<a class="header">'.$g["name"].'</a>';
                        
                        echo '<div class="description">';
                          echo $g["title"];
                        echo '</div>';
                        echo '<div class="extra">';
                          echo '<div class="btn-geovisor-delete ui right floated primary button" data-value="'.$g["id"].'">Eliminar<i class="right times icon"></i></div>';
                          echo '<div class="btn-geovisor-edit ui right floated primary button" data-value="'.$g["id"].'">Editar<i class="right edit outline icon"></i></div>';
                          echo '<div class="btn-geovisor-open ui right floated primary button" data-value="'.$g["id"].'">Ver<i class="right eye icon"></i></div>';
                        echo '</div>';
                      echo '</div>';
                    echo '</div>';
                 }
          
                

                ?>

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
    
    <!-- loading jsPanel javascript -->
    <script src="js/jspanel/jspanel.js"></script>
    <!-- optionally load jsPanel extensions -->
    <script src="js/jspanel/contextmenu/jspanel.contextmenu.js"></script>
    <script src="js/jspanel/hint/jspanel.hint.js"></script>
    <script src="js/jspanel/modal/jspanel.modal.js"></script>
    <script src="js/jspanel/tooltip/jspanel.tooltip.js"></script>
    <script src="js/jspanel/dock/jspanel.dock.js"></script>
    
    <script>
    
    $('.btn-geovisor-open').click(function() {
      var geovisor_id = $(this).attr("data-value");

      $(location).attr('href', 'index.php?geovisor_id='+ geovisor_id + '&access_token=' + access_token);

    });

    $('.btn-geovisor-edit').click(function() {
      var geovisor_id = $(this).attr("data-value");

      $(location).attr('href', 'editor.php?geovisor_id='+ geovisor_id + '&action=edit' + '&access_token=' + access_token);

    });


    $('#btn-new').click(function() {
      
      $(location).attr('href', 'editor.php?&action=new' + '&access_token=' + access_token);

    });


    $('#btn-geoportal').click(function() {
      
      $(location).attr('href', '<?php echo $MAIN_SITE_URL; ?>');

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
    <p><a href="https://mapa.redspira.org/">Ir a página principal</a></p>
  </body>
</html>

<?php
}
?>