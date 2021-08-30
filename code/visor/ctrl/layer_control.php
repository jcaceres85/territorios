<?php
require_once("../lib/framework/geovisor.php");
require_once("../lib/framework/layer.php");
require_once("../lib/framework/search.php");

$geovisor_id = $_REQUEST["geovisor_id"];
$access_token = $_REQUEST["access_token"];

$g_res = Geovisor::get($geovisor_id);


$legend_font = 'SanSerif.bold';

if(count($g_res)>0)
{
  $geovisor = $g_res[0];

  if($geovisor['legend_font'] != null || $geovisor['legend_font'] != '')
    $GLOBALS['legend_font'] = $geovisor['legend_font'];

  if($geovisor['legend_font_size'] != null || $geovisor['legend_font_size'] != '')
    $GLOBALS['legend_font_size'] = round($geovisor['legend_font_size']);
}
else
{
  $GLOBALS['legend_font'] = $legend_font;  
}


$all_layers = array();

$searches = Search::get_search_by_geovisor_id($geovisor_id);


function layerTreeCtrl($geovisor_id, $parent_category_id)
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

      layerTreeCtrl($geovisor_id, $category_id);
      
      printLayerCtrl($geovisor_id, $category_id);
        

      echo '</div>';
      echo '</div>'; //category-item

    }
}

function printLayerCtrl($geovisor_id, $category_id)
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
    $transparency = $lyr["transparency"];
    $zindex = $lyr["zindex"];
    $geoserver_label_style_id = $lyr["geoserver_label_style_id"];
    $label_active = $lyr["label_active"];

    $lyr_id_sel = '';
    if($geoserver_style_id == null || $geoserver_style_id == '')
      $lyr_id_sel = $geoserver_layer_id;
    else
      $lyr_id_sel = $geoserver_style_id;

    echo '<div class="layer-item" data-id="'.$lyr_name.'" style="display: block;">';
    echo '<div class="checkbox">';
    echo '<span onclick="activarCapa(\''.$lyr_id_sel.'\')" id="chk_'.$lyr_id_sel.'" class="check"><i class="checkitem fas fa-'.($active=='t'?'check-':'').'square" aria-hidden="true"></i> '.$lyr_name. '</span>';
    echo '<span id="activate_legend_lyr_'.$lyr_id_sel.'" class="toggle-legend-lyr" data-toggle="collapse" data-target="#grp_legend_lyr_'.$lyr_id_sel.'"><i class="angle '.($active=='t'?'up':'down').' icon"></i></span>';

    

    echo '<span class="toggle-options-lyr" data-toggle="collapse" data-target="#grp_options_lyr_'.$lyr_id_sel.'"><i class="cog icon"></i></span>';
    echo '</div>';

    echo '<div>';

    echo '<div class="options-lyr collapse" id="grp_options_lyr_'.$lyr_id_sel.'">';

      if($geoserver_label_style_id != null && $geoserver_label_style_id != '')
      { 
        echo '<span>Etiquetas</span> <span onclick="activarCapa(\''.$geoserver_label_style_id.'\');$(this).children().toggleClass(\'on\');$(this).children().toggleClass(\'off\');"><i class="activate-layer-label toggle '.($active == 't' && $label_active == 't'?'on':'off').' icon"></i></span><i class="fas fa-ad"></i>';
      }

      echo '<div><span><i class="eye icon"></i> Transparencia:</span> <span class="transparency-value">'.($transparency).'%</span></div>';
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
      
      echo '&LEGEND_OPTIONS=fontName:'.$GLOBALS['legend_font'].';bgColor:0xFFFFFF;fontSize:'.$GLOBALS['legend_font_size'].';fontColor:0x333333;forceLabels:on;dpi:100&access_token='.$GLOBALS['access_token'].'"/>';
    
    echo '</div>'; //grp-legend-lyr

    echo '</div>';

    echo '</div>'; //layer-item
  }
}



function searchForms($searches)
{

    

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


      echo '<div class="search-item">';
      echo '<label class="search-label">'.$search_text.'</label>';

      if($type == 'text')
      {

        echo '<div class="ui form">';


        echo '<div class="inline fields">';
        echo '<label class="search-attribute-label">'.$attribute_label.':</label>';
        
        echo '<div class="field">';
        

        echo '<div  class="ui input"><input type="text" autocomplete="off" id="search_'.$search_id.'" placeholder="Buscar..."></div>';

        echo '</div>'; //field

        echo '<div class="field">';

          echo '<div><button id="btn_search_'.$search_id.'" class="ui mini icon primary button" onclick="executeSearch('.$search_id.')"><i class="search icon"></i>';

          //echo '</button> <button class="ui mini icon primary button"><i class="filter icon"></i></button>';

          echo '</div>';

        echo '</div>';
      }
      else if($type == 'select')
      {
        $values = Search::get_layer_attribute_list_values($geoserver_layer_id, $attribute);

        echo '<div class="ui form">';


        echo '<div class="inline fields">';
        echo '<label class="search-attribute-label">'.$attribute_label.':</label>';
        
        echo '<div class="field">';
        
        echo '<div class="ui search selection dropdown"><input type="hidden" autocomplete="off" id="search_'.$search_id.'"> <i class="dropdown icon"></i><div class="default text">Seleccionar</div><div class="menu">';
        for($j=0; $j<count($values); $j++)
        {
          echo ' <div class="item" data-value="'.$values[$j][$attribute].'">'.$values[$j][$attribute].'</div>';
        }
        echo '</div></div>'; //dropdown

        echo '</div>'; //field

        echo '<div class="field">';

          echo '<div><button id="btn_search_'.$search_id.'" class="ui mini icon primary button" onclick="executeSearch('.$search_id.')"><i class="search icon"></i>';

          //echo '</button> <button class="ui mini icon primary button"><i class="filter icon"></i></button>';

          echo '</div>';

        echo '</div>';

        echo '</div>'; //form
      }
      else if($type == 'radio')
      {
        echo '<div class="ui form">';


        echo '<div class="inline fields">';
        echo '<label class="search-attribute-label">'.$attribute_label.':</label>';
        
        echo '<div class="field">';
        

        echo '<div  class="ui input"><input type="radio" id="search_'.$search_id.'"></div>';

        echo '</div>'; //field

        echo '<div class="field">';

          echo '<div><button id="btn_search_'.$search_id.'" class="ui mini icon primary button" onclick="executeSearch('.$search_id.')"><i class="search icon"></i>';

          //echo '</button> <button class="ui mini icon primary button"><i class="filter icon"></i></button>';

          echo '</div>';

        echo '</div>';
      }


      echo '</div>'; //category-item

    }
}

?>

<div class="ui pointing secondary menu">
  <div class="item active" data-tab="tab-layers"><i class="fas fa-layer-group"></i>&nbsp;Capas</div>
  <div class="item" data-tab="tab-legend"><i class="fas fa-list-alt"></i>&nbsp;Leyenda</div>
  
  <?php 
    if(count($searches)>0)
    {
      echo '<div class="item" data-tab="tab-search"><i class="fas fa-search"></i>&nbsp;Búsquedas</div>';
    }
  ?>

</div>



<div class="ui bottom attached active tab segment" data-tab="tab-layers">

  <div id="layerTools">
    <div class="ui mini icon input">
      <input id="filterLayerInput" type="text" placeholder="Filtrar...">
      <i class="search icon"></i>
    </div>
  </div>

  <div id="arbolCapas<?php echo $geovisor_id;?>" style="margin-top: 10px;" class="ui list">

    <?php
        layerTreeCtrl($geovisor_id, 0);
    ?>
    
  </div>
</div>

<div class="ui bottom attached tab segment" data-tab="tab-legend">
  <div id="legend-items"></div>
</div>

<?php 

  if(count($searches)>0)
  {
    echo '<div class="ui bottom attached tab segment" data-tab="tab-search">';
      echo '<div id="search-items">';      
        searchForms($searches);
      echo '</div>';
    echo '</div>';
  }

?>

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

  $('.ui.dropdown').dropdown({});


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


         $(this).prev().find('.transparency-value').html(''+(opacity*100)+'%')
         
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


            if(lyr instanceof ol.layer.Tile || lyr instanceof ol.layer.Image)
            {
              visibleLayers.push(capa);
            }

            $('#grp_legend_lyr_' + capa).collapse('show');
            $('#activate_legend_lyr_' + capa).find('i').removeClass('angle down').addClass('angle up');
            
        }
        else
        {
            lyr.setVisible(false);

            if(lyr instanceof ol.layer.Tile || lyr instanceof ol.layer.Image)
            {
              visibleLayers.splice(visibleLayers.indexOf(capa),1);
            }

        } 
      }

      refreshLehend();
      refreshActiveLayersBufferDialog();
  }

  function getLayer(capa)
  {
      var lyr = null;
      
      var lyr = lyrsMap[capa];

      return lyr;
  }



  function refreshLehend()
  {
    
    var legendDiv = '';
    for(var i=0; i<visibleLayers.length; i++)
    {
      var visLyr = getLayer(visibleLayers[i]);

      if(visLyr.get('legend_url') != null)
        legendDiv+='<div style="margin:5px; display: block; float:left;"><div class="legend-item-head"><span>'+visLyr.get('name')+'</span></div><div><img class="legend-item-img" src="'+visLyr.get('legend_url')+'"/></div></div>';
    }

    $('#legend-items').html(legendDiv);

  }


  function executeFilter(text)
  {

  }

  function executeSearch(search_id, page=0, limit = 20)
  {

     var search_value = $('#search_' + search_id).val();

     if(search_value == null || search_value.trim() == '')
     {
      toastr.error('Campo de búsqueda vacío.');

      return;
     }


     $('#btn_search_' + search_id).addClass('loading');

     $.ajax({
            url: 'lib/xajax/x_execute_search.php?geovisor_id=<?php echo $geovisor_id;?>&search_id=' + search_id + '&search_value=' + search_value + '&page=' + page + '&limit=' + limit,
            dataType: 'json',
            success: function(response) {

                  if(response.success)
                  {
                    
                    var headers = response.headers;
                    var results = response.results;

                    var htmlResult = $('<div class="search-result"></div>');

                    var pages = parseInt(response.count/limit);

                    if(response.count%limit>0) pages++;

                    htmlResult.append('<div class="search-result-header"><span>Se encontraron '+ response.count +' resultados</span> / <span>Página '+(page + 1)+' de '+pages+'</span> </div>');

                    var tableResult = $('<table class="tabla-info ui celled table"></table>');

                    var tableResultHeader = '<thead><tr>';

                    for(var i=0; i<headers.length; i++)
                    {
                      var h = headers[i];

                      tableResultHeader+= '<th>' + h.label + '</th>';
                    }

                    tableResultHeader+= '<th><i>Acciones</i></th>';

                    tableResultHeader+= '</tr></thead>';

                    tableResult.append(tableResultHeader);


                    var tableResultBody = $('<tbody></tbody>');

                    for(var i=0; i<results.length; i++)
                    {
                      var r = results[i];

                      var tableResultRow = $('<tr></tr>');
                      for(var j=0; j<r.length; j++)
                      {
                        tableResultRow.append('<td>' + r[j] + '</td>');
                      }

                      var buttonShowFeature = $('<button class="ui tiny icon button"><i class="search icon"></i></button>');

                      //var wktGeometry = r[j];
                      //$('<td><a href="javascript:showFeatureGeometry(\''+wktGeometry+'\')"><i class="search icon"></i></a></td>').appendTo(tableResultRow);


                      $('<td><a href="javascript:showFeature(\'search\','+search_id+',\''+r[0]+'\')"><i class="search icon"></i></a></td>').appendTo(tableResultRow);

                      tableResultBody.append(tableResultRow);
                      
                    }

                    tableResult.append(tableResultBody);

                    htmlResult.append(tableResult);

                    var pagination =  '<div class="ui right floated">';

                    if(page > 0) pagination+= '<a href="javascript:executeSearch('+search_id+','+(page-1)+','+limit+')">Anterior <i class="angle left icon"></i></a>';

                    pagination+=  ' [Página ' + (page+1) + '] ';

                    if(page < (pages-1)) pagination+= '<a href="javascript:executeSearch('+search_id+','+(page+1)+','+limit+')"><i class="angle right icon"></i> Siguiente</a>';

                    pagination+='</div>';

                    htmlResult.append(pagination);

                    showPanelSearchResult(htmlResult);

                  }
                  else
                  {
                    toastr.error(toastr.message);
                  }

                  $('#btn_search_' + search_id).removeClass('loading');
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(textStatus + " - " + errorThrown);

                toastr.error(textStatus + " - " + errorThrown);

                $('#btn_search_' + search_id).removeClass('loading');
            }
      });
  }

  function showFeature(type, source_id, FeatureId, zoomTo = true)
  {
    //https://territoriosenriesgo.unah.edu.hn/geoserver/wfs?service=wfs&version=2.0.0&request=GetFeature&typeNames=geonode:termicas&featureID=2&outputFormat=application/json
    //gid
    var urlShowFeature = '';

    if(type == 'search')
      urlShowFeature = 'lib/xajax/x_get_geometry_search.php?geovisor_id=<?php echo $geovisor_id;?>&search_id=' + source_id + '&feature_id=' + FeatureId;
    else if(type == 'buffer')
      urlShowFeature = 'lib/xajax/x_get_geometry_by_geoserver_layer_id.php?geovisor_id=<?php echo $geovisor_id;?>&geoserver_layer_id=' + source_id + '&feature_id=' + FeatureId;

    $.ajax({
            url: urlShowFeature,
            dataType: 'json',
            success: function(response) {



                if(response.success)
                {
                  if(response.results.length>0)
                  {
                    var geometry = response.results[0]['geom'];

                    showFeatureGeometry(geometry, zoomTo);
                  }
                }
                else
                {
                  toastr.error(toastr.message);
                }

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(textStatus + " - " + errorThrown);

                toastr.error(textStatus + " - " + errorThrown);
            }
      });
  }


  function showFeatureGeometry(featureGeometry, zoomTo)
  {

    var parser = new ol.format.WKT();

    var feature = parser.readFeature(featureGeometry, {
      dataProjection: 'EPSG:4326',
      featureProjection: 'EPSG:3857',
    });

    lyrResult.getSource().clear();

    lyrResult.getSource().addFeatures([feature]);

    if(zoomTo)
      map.getView().fit(feature.getGeometry())

  }

  var panelSearchResult = null;

  function showPanelSearchResult(htmlResult)
    {
      if(panelSearchResult == null)
        {
          panelSearchResult = jsPanel.create({
              id: 'panelSearchResult',
              theme:       'primary',
              contentSize: {
                  width: function() { return Math.min(600,window.innerWidth/2)},
                  height: function() { return 400;}
              },
              
              position:    'right-bottom 10 25',
              animateIn:   'jsPanelFadeIn',
              headerTitle: '<i class="info circle icon"></i> Resultado',
              dragit: {
                snap: true
              },
              content: '<div id="panel_search_result"></div>',
              onwindowresize: true
          });

          $('#panel_search_result').append(htmlResult);
        }
        else
        {
          $('#panel_search_result').html('');
          $('#panel_search_result').append(htmlResult);
        }
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
  $transparency = $lyr["transparency"];
  $queryable = $lyr["queryable"];
  $zindex = $lyr["zindex"];
  $geoserver_label_style_id = $lyr["geoserver_label_style_id"];
  $label_active = $lyr["label_active"];
  $label_zoom_min = $lyr["label_zoom_min"];
  $label_zoom_max = $lyr["label_zoom_max"];

  $tiled = $lyr["tiled"];

  if($transparency == null)
  {
    $transparency = 100;
  }

  $transparency = $transparency / 100;

  if($tiled == 't')
  { 
    echo "var lyr".$lyr_id." = new ol.layer.Tile({visible: ";

    if($active == 't') echo 'true'; else echo 'false';
    echo ", source: new ol.source.TileWMS({url: '";
    if(substr($geoserver_url, 0, 4 ) === "http")
      echo $geoserver_url;
    else
      echo 'http://'.$geoserver_url;
    
    echo "/wms', params: {'FORMAT': 'image/png', 'VERSION': '1.1.1', tiled: true, ";
    echo "STYLES: '".$geoserver_style_id."', access_token: '".$access_token."', LAYERS: '".$geoserver_layer_id."'}, serverType: 'geoserver'}), opacity: ".$transparency."}); \n\t";
  }
  else
  {
      echo "var lyr".$lyr_id." = new ol.layer.Image({visible: ";

      if($active == 't') echo 'true'; else echo 'false';
      echo ", source: new ol.source.ImageWMS({url: '";
      if(substr($geoserver_url, 0, 4 ) === "http")
        echo $geoserver_url;
      else
        echo 'http://'.$geoserver_url;
      
      echo "/wms', params: {'FORMAT': 'image/png', 'VERSION': '1.1.1', ";

      echo "STYLES: '".$geoserver_style_id."', access_token: '".$access_token."', LAYERS: '".$geoserver_layer_id."'}, serverType: 'geoserver'}), opacity: ".$transparency."}); \n\t";
  }

  
  //params: propertyName=the_geom,

  echo "lyr".$lyr_id.".set('geoserver_layer_id', '".$geoserver_layer_id."');";
  echo "lyr".$lyr_id.".set('name', '".$lyr_name."');";
  echo "lyr".$lyr_id.".set('queryable', '".$queryable."');";

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
      
  $legend_url.='&LEGEND_OPTIONS=fontName:'.$GLOBALS['legend_font'].';bgColor:0xFFFFFF;fontSize:9;fontColor:0x333333;forceLabels:on;dpi:100&access_token='.$GLOBALS['access_token'];


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

  if($geoserver_label_style_id != null && $geoserver_label_style_id != '')
  {
    echo "var lyr".$lyr_id."_lbl = new ol.layer.Tile({visible: ";
    if($active == 't' && $label_active == 't') echo 'true, '; else echo 'false, ';
        
    if($label_zoom_min != null && $label_zoom_min != '') echo 'minZoom:' . $label_zoom_min . ', ';
    if($label_zoom_max != null && $label_zoom_max != '') echo 'maxZoom:' . $label_zoom_max . ', ';

    echo "source: new ol.source.TileWMS({url: '";
    if(substr($geoserver_url, 0, 4 ) === "http")
      echo $geoserver_url;
    else
      echo 'http://'.$geoserver_url;
    
    echo "/wms', params: {'FORMAT': 'image/png', 'VERSION': '1.1.1', tiled: true, ";
    echo "STYLES: '".$geoserver_label_style_id."', access_token: '".$access_token."', LAYERS: '".$geoserver_layer_id."'}, serverType: 'geoserver'}), opacity: 1}); \n\t";


     echo "lyr".$lyr_id."_lbl.setZIndex(".($zindex+1).");";

    echo "map.addLayer(lyr".$lyr_id."_lbl); \n\t";

    echo "lyr".$lyr_id."_lbl.set('legend_url', null);";
    echo "lyr".$lyr_id."_lbl.set('queryable', false);";


    echo "lyrsMap['".$geoserver_label_style_id."'] = lyr".$lyr_id."_lbl;";

  }

  if($geoserver_style_id == null || $geoserver_style_id == '')
    echo "lyrsMap['".$geoserver_layer_id."'] = lyr".$lyr_id.";";
  else
    echo "lyrsMap['".$geoserver_style_id."'] = lyr".$lyr_id.";";

}

?>

refreshLehend();

</script>