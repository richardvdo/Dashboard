<?php require_once('teleinfo_func_v2.php'); ?>







<script type="text/javascript">
  google.charts.load('current', {packages: ['corechart'], 'language': 'fr'});
  google.charts.setOnLoadCallback(drawDashboard);

  function drawDashboard() {

    var data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        data.addColumn({type:'string', role:'style'});
        data.addColumn('number', 'W');
        data.addRows([<?php echo getInstantConsumptionLight (1); ?>]);

     var dashboard = new google.visualization.LineChart(document.getElementById('puissance'));
			
	 var options = {
                             title: 'Consommation instantanée (en W)',
							 width: 400,
							 height : 200,
                             backgroundColor: '#000',
							 titleTextStyle: {color: '#6495ED',fontSize : '12'},
                             colors : ['#6495ed'],
                             curveType : 'function',
                             focusTarget : 'category',
                             lineWidth : '1',
                             legend : {position: 'none'},
                             vAxis : {textStyle : {color : '#777777', fontSize : '16'}, gridlines : {color: '#777777', count: 'auto'}, baselineColor : '#AAA', minValue : 0},
                             hAxis : {textStyle : {color : '#777777'}, gridlines : {color: '#DDD'}}
        };

    dashboard.draw(data,options);

  }
  
  google.charts.setOnLoadCallback(drawChart);
  
  function drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn('date', 'Date');
    data.addColumn('number', 'Consommation');
    data.addRows([<?php echo getDailyData (5); ?>]);
    var dailyChartOptions = {
                   title: 'Consommation journalière (en kW)',
				   width: 300,
				   height : 200,
                   backgroundColor: '#000',
				   titleTextStyle: {color: '#6495ED',fontSize : '12'},
                   colors : ['#6495ed', '#6495ed'],
                   focusTarget : 'category',
                   isStacked: true,
                   legend : {position: 'none'},
                   vAxis : {textStyle : {color : '#777777', fontSize : '16'}, gridlines : {color: '#777777', count: 'auto'}, baselineColor : '#AAA', minValue : 0},
                   hAxis : {textStyle : {color : '#777777'}, gridlines : {color: '#DDD'}}
              };




    var dailyChart = new google.visualization.ColumnChart(document.getElementById("daily_div"));
    dailyChart.draw(data, dailyChartOptions);

  }

</script>
