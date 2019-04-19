<?php require_once('teleinfo_func_v3.php'); ?>







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
                             backgroundColor: '#FF0000',
							 titleTextStyle: {color: '#000',fontSize : '14'},
                             colors : ['#424242'],
                             curveType : 'function',
                             focusTarget : 'category',
                             lineWidth : '3',
                             legend : {position: 'none'},
                             vAxis : {textStyle : {color : '#FFFFFF', fontSize : '16'}, gridlines : {color: '#FFFFFF', count: 'auto'}, baselineColor : '#AAA', minValue : 0},
                             hAxis : {textStyle : {color : '#FFFFFF'}, gridlines : {color: '#DDD'}}
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
                   backgroundColor: '#FF0000',
				   titleTextStyle: {color: '#000',fontSize : '14'},
                   colors : ['#424242', '#424242'],
                   focusTarget : 'category',
                   isStacked: true,
                   legend : {position: 'none'},
                   vAxis : {textStyle : {color : '#FFFFFF', fontSize : '16'}, gridlines : {color: '#FFFFFF', count: 'auto'}, baselineColor : '#AAA', minValue : 0},
                   hAxis : {textStyle : {color : '#FFFFFF'}, gridlines : {color: '#DDD'}}
              };




    var dailyChart = new google.visualization.ColumnChart(document.getElementById("daily_div"));
    dailyChart.draw(data, dailyChartOptions);

  }

</script>
