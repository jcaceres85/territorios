<?php
require_once("../lib/framework/geovisor.php");
require_once("../lib/framework/layer.php");

$geovisor_id = $_REQUEST["geovisor_id"];
$access_token = $_REQUEST["access_token"];



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

      echo '<div class="category-item">';

      echo '<div class="grupo_capas_head" data-toggle="collapse" data-target="#grp_cat_'.$geovisor_id.'_'.$category_id.'"><span>  ';
      if($expanded == 'f')
        echo '<i class="fas fa-plus-square"';
      
      else
        echo '<i class="fas fa-minus-square"';
      echo ' aria-hidden="true"></i> '.$name.'</span></div>'; 
      echo '<div class="grupo_capas_items ';
      if($expanded == 'f')
        echo 'collapse';
      else
        echo 'collapse in';
      echo '" id="grp_cat_'.$geovisor_id.'_'.$category_id.'">';

      layerTree($geovisor_id, $category_id);
      
      printLayer($geovisor_id, $category_id);
        

      echo '</div>';
      echo '</div>'; //category-item

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

    $lyr_id_sel = '';
    if($geoserver_style_id == null || $geoserver_style_id == '')
      $lyr_id_sel = $geoserver_layer_id;
    else
      $lyr_id_sel = $geoserver_style_id;

    echo '<div class="layer-item" data-id="'.$lyr_name.'" style="display: block;">';
    echo '<div class="checkbox">';
    echo '<span onclick="activarCapa(\''.$lyr_id_sel.'\')" id="chk_'.$lyr_id_sel.'" class="check"><i class="checkitem fas fa-'.($active=='t'?'check-':'').'square" aria-hidden="true"></i> '.$lyr_name. '</span>';
    echo '<span id="activate_legend_lyr_'.$lyr_id_sel.'" class="toggle-legend-lyr" data-toggle="collapse" data-target="#grp_legend_lyr_'.$lyr_id_sel.'"><i class="angle '.($active=='t'?'up':'down').' icon"></i></span><span class="toggle-options-lyr" data-toggle="collapse" data-target="#grp_options_lyr_'.$lyr_id_sel.'"><i class="cog icon"></i></span>';
    echo '</div>';

    echo '<div>';

    echo '<div class="options-lyr collapse" id="grp_options_lyr_'.$lyr_id_sel.'">';
      echo '<div><span><i class="eye icon"></i> Transparencia</span></div>';
      echo '<div id="slider_'.$lyr_id_sel.'" class="slider_transparency"></div>';
      echo '<div><span><i class="sort amount up icon"></i> Índice-Z: </span> <span id="spn_lyr_zindex_'.$lyr_id_sel.'">'.$zindex.'</span>  <span class="spin-zindex" onclick="zIndexUp(\''.$lyr_id_sel.'\')"><i class="arrow up icon"></i></span><span class="spin-zindex" onclick="zIndexDown(\''.$lyr_id_sel.'\')"><i class="arrow down icon"></i></span></div>';
    echo '</div>';

    echo '<div class="options-lyr collapse '.($active=='t'?'in':'').'" id="grp_legend_lyr_'.$lyr_id_sel.'">';
      echo '<img src="';
      
      if(substr($geoserver_url, 0, 4 ) === "http")
        echo $geoserver_url;
      else
        echo 'http://'.$geoserver_url;
      echo '/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER='.$geoserver_layer_id;
      
      if($geoserver_style_id != null AND $geoserver_style_id != '')
        echo '&STYLE='.$geoserver_style_id;
      
      echo '&LEGEND_OPTIONS=bgColor:0xFFFFFF;fontSize:9;fontColor:0x333333;forceLabels:on&access_token='.$GLOBALS['access_token'].'"/>';
    
    echo '</div>'; //grp-legend-lyr

    echo '</div>';

    echo '</div>'; //layer-item
  }
}

?>

<div class="ui pointing secondary menu">
  <div class="item active" data-tab="tab-capas">Capas</div>
  <div class="item" data-tab="tab-leyenda">Leyenda</div>
</div>



<div class="ui bottom attached active tab segment" data-tab="tab-capas">

  <div id="layerTools">
    <div class="ui mini icon input">
      <input id="filterLayerInput" type="text" placeholder="Filtrar...">
      <i class="search icon"></i>
    </div>
  </div>

  <div id="arbolCapas<?php echo $geovisor_id;?>" style="margin-top: 10px;" class="ui list">

    <?php
        layerTree($geovisor_id, 0);
    ?>
    
  </div>
</div>

<div class="ui bottom attached tab segment" data-tab="tab-leyenda">
  <div id="legend-items"></div>
</div>


<style type="text/css">
  

      .grupo_capas_head
      {
        padding-left: 5px;
        margin-bottom: 4px;
        border-bottom: 1px solid #333;
        color: #333;
        font-size: 8pt;
        padding-top: 5px;
        cursor: pointer;
      }

      .grupo_capas_head span
      {
        margin-left: 5px;
        cursor: pointer;
      }

      .grupo_capas_items
      {
         margin-top: 5px;
         margin-left: 15px;
         width: 100%;
         color: #333;
         font-size: 8pt;
      }

      .grupo_capas_items label
      {
        color: #333;
      }

      .options-lyr
      {
        margin-left: 10px;
      }

      .toggle-options-lyr
      {
        margin-left: 3px;
        color: #999;
      }

      .toggle-options-lyr:hover
      {
        margin-left: 3px;
        color: #333;
      }

      .toggle-legend-lyr {
          margin-left: 2px;
          color: #999;
      }

      .toggle-legend-lyr:hover {
          margin-left: 2px;
          color: #333;
      }

      .slider_transparency
      {
        width: 150px;
      }
      
      .spin-zindex
      {
        cursor: pointer;
      }

      .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default
      {
          border: 1px solid #d3d3d3;
          font-weight: normal;
          color: #555555;
          background: none;
          background-color: #555555 !important;
      }

      .ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus
      {
          border: 1px solid #d3d3d3;
          font-weight: normal;
          color: #555555;
          background: none;
          background-color: #333333 !important;
      }

      .radio, .checkbox
      {
        margin-bottom: 0px !important;
      }

      .ui.segment
      {
        padding: 0; 
      }

      .ui.menu .item
      {
        font-size: 8pt;
      }

      .ui.attached.segment
      {
        border: none;
      }



</style>

<script type="text/javascript">
  
  var tipoClasifTamaño = 'estandar';

  $('#arbolCapas<?php echo $geovisor_id;?> .grupo_capas_head').click(function(){
        $(this).find('i').toggleClass('fas fa-plus-square').toggleClass('fas fa-minus-square');
  });

  $('#arbolCapas<?php echo $geovisor_id;?> .check').click(function(){
    $(this).find('.checkitem').toggleClass('fas fa-square').toggleClass('fas fa-check-square');
  });

  $('#arbolCapas<?php echo $geovisor_id;?> .lyr_options').click(function(){
    $(this).find('i').toggleClass('fas fa-angle-down').toggleClass('fas fa-angle-up');
  });


  $('#arbolCapas<?php echo $geovisor_id;?> .radio').click(function(){
    $(this).find('i').toggleClass('fas fa-circle').toggleClass('fas fa-dot-circle-o');
  });


  $('#arbolCapas<?php echo $geovisor_id;?> .toggle-legend-lyr').click(function(){
    $(this).find('i').toggleClass('angle down').toggleClass('angle up');
  });

  $('.menu .item').tab();


  $('#filterLayerInput').on('input', function(e){
      var filterText = $(this).val();

      $('.layer-item').each(function(){
        
        if($(this).data("id").toLowerCase().includes(filterText.toLowerCase()))
        {
          $(this).show();
        }
        else
        {
          $(this).hide();
        }

      });

      $('.category-item').each(function(){
          
          if($(this).children('.grupo_capas_items').children("[style*='display: block']").length>0)
          {
            $(this).show();
            $(this).children('.grupo_capas_items').collapse("show");
          }
          else
          {
            $(this).hide();
          }

      });

  });

  $(".slider_transparency").slider({
      value: 1,
      min: 0,
      max: 1,
      step: 0.1,
      slide: function( event, ui ) {
         var opacity = ui.value;
         
         var slider_id = ui.handle.parentElement.id;

         var lyr_id = slider_id.replace('slider_','');


         lyr = getLayer(lyr_id);

         if(lyr != null)
          lyr.setOpacity(opacity);
         
      }
    });

  function zIndexUp(lyr_id)
  {
    lyr = getLayer(lyr_id);

    if(lyr != null)
    {
      var zindex = lyr.getZIndex();
      zindex++;
      lyr.setZIndex(zindex);
      $('#spn_lyr_zindex_' + lyr_id).html(''+zindex);
    }   
  }

  function zIndexDown(lyr_id)
  {
    lyr = getLayer(lyr_id);

    if(lyr != null)
    {
      var zindex = lyr.getZIndex();
      if(zindex > 0)
        zindex--;
      lyr.setZIndex(zindex);
      $('#spn_lyr_zindex_' + lyr_id).html(''+zindex);
    }   
  }


  function activarCapa(capa)
  {

      var lyr = getLayer(capa);
 
      if(lyr != null)
      {
        if(!lyr.getVisible())
        {
            lyr.setVisible(true);


            if(lyr instanceof ol.layer.Tile)
            {
              visibleLayers.push(capa);
            }

            $('#grp_legend_lyr_' + capa).collapse('show');
            $('#activate_legend_lyr_' + capa).find('i').removeClass('angle down').addClass('angle up');
            
        }
        else
        {
            lyr.setVisible(false);

            if(lyr instanceof ol.layer.Tile)
            {
              visibleLayers.splice(visibleLayers.indexOf(capa),1);
            }

        } 
      }

      refreshLehend();  
  }

  function getLayer(capa)
  {
      var lyr = null;
      
      var lyr = lyrsMap[capa];

      return lyr;
  }



  function filterLayer(text)
  {

  }

  function refreshLehend()
  {
    
    var legendDiv = '';
    for(var i=0; i<visibleLayers.length; i++)
    {
      var visLyr = getLayer(visibleLayers[i]);

      legendDiv+='<div style="margin:5px; display: block; float:left;"><span class="legend-item-head">'+visLyr.get('name')+'</span><br><img class="legend-item-img" src="'+visLyr.get('legend_url')+'"/></div>';
    }

    $('#legend-items').html(legendDiv);

  }


  

//Add LAYERS
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
  //params: propertyName=the_geom,

  echo "lyr".$lyr_id.".set('geoserver_layer_id', '".$geoserver_layer_id."');";
  echo "lyr".$lyr_id.".set('name', '".$lyr_name."');";

  //properties

  echo "lyr".$lyr_id.".setZIndex(".$zindex.");";


  $legend_url = '';
  if(substr($geoserver_url, 0, 4 ) === "http")
    $legend_url.=$geoserver_url;
  else
    $legend_url.='http://'.$geoserver_url;
  $legend_url.='/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER='.$geoserver_layer_id;
  
  if($geoserver_style_id != null AND $geoserver_style_id != '')
    $legend_url.='&STYLE='.$geoserver_style_id;
      
  $legend_url.='&LEGEND_OPTIONS=bgColor:0xFFFFFF;fontSize:9;fontColor:0x333333;forceLabels:on&access_token='.$GLOBALS['access_token'];


  echo "lyr".$lyr_id.".set('legend_url', '".$legend_url."');";


  //Labels
  $labels = Layer::get_labels_by_geoserver_layer_id($geoserver_layer_id);

  $labels_query = '[';

  for($l=0; $l<count($labels); $l++)
  {
    $lbl = $labels[$l];

    $labels_query.= "['".$lbl['attribute']."','".$lbl['label']."'],";    
  }

  $labels_query.= ']';

  echo "lyr".$lyr_id.".set('labels', ".$labels_query.");";

  //-Labels

  echo "map.addLayer(lyr".$lyr_id."); \n\t";


  $lyr_id_sel = '';
  if($geoserver_style_id == null || $geoserver_style_id == '')
    $lyr_id_sel = $geoserver_layer_id;
  else
    $lyr_id_sel = $geoserver_style_id;
  if($active == 't')
  {
    echo "visibleLayers.push('".$lyr_id_sel."');";
  }

  if($geoserver_style_id == null || $geoserver_style_id == '')
    echo "lyrsMap['".$geoserver_layer_id."'] = lyr".$lyr_id.";";
  else
    echo "lyrsMap['".$geoserver_style_id."'] = lyr".$lyr_id.";";

}

?>

refreshLehend();

</script>