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

positionEqLogic();

$('.div_eqLogicBestway').each(function(){
  var container = $(this).packery({
    itemSelector: ".eqLogic-widget",
    gutter : 0,
  });
});
$('.div_eqLogicBestway .eqLogic-widget').trigger('resize');

$('.div_eqLogicBestway').off('click','.eqLogic-widget .history').on('click','.eqLogic-widget .history', function (event) {
  event.stopImmediatePropagation()
  event.stopPropagation()
  if (event.ctrlKey) {
    var cmdIds = []
    $(this).closest('.eqLogic.eqLogic-widget').find('.history[data-cmd_id]').each(function () {
      cmdIds.push($(this).data('cmd_id'))
    })
    cmdIds = cmdIds.join('-')
  } else {
    var cmdIds = $(this).closest('.history[data-cmd_id]').data('cmd_id')
  }
  $('#md_modal2').dialog({title: "{{Historique}}"}).load('index.php?v=d&modal=cmd.history&id=' + cmdIds).dialog('open')
})

$('.bt_changePeriod').on('click',function(){
  var url = document.URL
  var newAdditionalURL = '';
  var tempArray = url.split("?");
  var baseURL = tempArray[0];
  var aditionalURL = tempArray[1];
  var temp = '';
  if(aditionalURL)  {
    var tempArray = aditionalURL.split('&');
    for ( var i in tempArray ){
      if(tempArray[i].indexOf('period') == -1){
        newAdditionalURL += temp+tempArray[i];
        temp = "&";
      }
    }
  }
  jeedom.history.chart = [];
  var url = baseURL+'?'+newAdditionalURL+temp+'period='+$(this).attr('data-period')
  loadPage(url.replace('#', ''));
});

function initGraph(){
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

initGraph();
