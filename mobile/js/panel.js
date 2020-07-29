/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

function initBestwayPanel(_object_id) {
  jeedom.object.all({
    onlyHasEqLogic : 'bestway',
    searchOnchild : '0',
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (objects) {
      var li = ' <ul data-role="listview">';
      for (var i in objects) {
        if (objects[i].isVisible != 1) {
          continue;
        }
        var icon = '';
        if (isset(objects[i].display) && isset(objects[i].display.icon)) {
          icon = objects[i].display.icon;
        }
        li += '<li></span><a href="#" class="link" data-page="panel" data-plugin="bestway" data-title="' + icon.replace(/\"/g, "\'") + ' ' + objects[i].name + '" data-option="' + objects[i].id + '"><span>' + icon + '</span> ' + objects[i].name + '</a></li>';
      }
      li += '</ul>';
      panel(li);
    }
  });
  displayBestway(_object_id);
  
  $(window).on("resize", function (event) {
    setTileSize('.eqLogic');
    $('.div_eqLogicBestway').packery({gutter : 0});
  });
}

function displayBestway(_object_id,_period){
  setBackgroundImage('plugins/bestway/core/img/panel.jpg');
  $.ajax({
    type: 'POST',
    url: 'plugins/bestway/core/ajax/bestway.ajax.php',
    data: {
      action: 'getPanel',
      period : _period || '',
      object_id : _object_id,
      version : 'mobile'
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      var html =data.result.period;
      var graphs = data.result.graphData
      for(var i in data.result.bestways){
        html += '<legend>'+data.result.bestways[i].eqLogic.name+'</legend>';
        html += '<div class="div_eqLogicBestway">';
        html += data.result.bestways[i].html;
        html += '</div><br/>';
        html += '<div id="div_chartBestway'+data.result.bestways[i].eqLogic.id+'"></div>';
        graphs[data.result.bestways[i].eqLogic.id] = data.result.bestways[i].graph;
      }
      $('#div_displayEquipementBestway').empty().html(html).trigger('create');
      $('.eqLogic-widget').addClass('col2');
      setTileSize('.eqLogic');
      $('.div_eqLogicBestway').packery({gutter : 0});
      initGraph(graphs);
      $('.bt_changePeriod').off('click').on('click',function(){
        displayBestway(_object_id,$(this).attr('data-period'))
      });
    }
  });
}

function initGraph(bestway_graphs){
  jeedom.history.chart = [];
  for(var i in bestway_graphs){
    let bestway = bestway_graphs[i];
    if (isNaN(i)) {
      continue;
    }
    let graphOption = {
      option : {
        graphColor : '#2ecc71',
        graphScale : 1,
        displayAlert:false
      },
      height : 500,
      el : 'div_chartBestway'+i,
      showNavigator : false,
      showLegend : false,
      showScrollbar : false,
      displayAlert : false,
      cmd_id : bestway.filter_power,
      dateRange : 'all',
      dateStart : bestway_graphs.day.start,
      dateEnd : bestway_graphs.day.end
    };
    var options = JSON.parse(JSON.stringify(graphOption));
    options.success = function(){
      graphOption.option.graphColor = '#3498db';
      graphOption.option.graphScale = 0;
      graphOption.cmd_id = bestway.temp_now;
      jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
      
      graphOption.option.graphColor = '#c0392b';
      graphOption.option.graphScale = 1;
      graphOption.cmd_id = bestway.heat_power;
      jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
      
      graphOption.option.graphColor = '#bdc3c7';
      graphOption.option.graphScale = 1;
      graphOption.cmd_id = bestway.wave_power;
      jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
      
      graphOption.option.graphColor = '#e74c3c';
      graphOption.option.graphScale = 0;
      graphOption.cmd_id = bestway.temp_set;
      jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
    }
    jeedom.history.drawChart(options);
  }
}
