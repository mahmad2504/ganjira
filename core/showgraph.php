<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Ganjira Charts</title>
<meta content='width=device-width, initial-scale=1' name='viewport'/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>
<script src="https://momentjs.com/downloads/moment.min.js" type="text/javascript"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script>
function charts(data,mdata,ChartType)
{
	var c=ChartType;
	var jsonData=data;
	google.load("visualization", "1", {packages:["corechart"], callback: drawVisualization});
	function drawVisualization() 
	{
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Data');
		data.addColumn('number', 'Progress');
		$.each(jsonData, function(i,jsonData)
		{
			var value=parseInt(jsonData.y);
			//var name=new Date (jsonData.x);
			var date = moment(jsonData.x);
			var name=date.format('W');
			//var name=jsonData.x;
			data.addRows([ [name, value] ]);
		});
		
		var options = mdata;
		
		
	/*	var options = {
			title : "Word Population Density",
			colorAxis: {colors: ['#54C492', '#cc0000']},
			"colorAxis":{"colors":["#54C492","#cc0000"]}
			datalessRegionColor: '#dedede',
			defaultColor: '#dedede'
		};*/

		var chart;
		if(c=="LineChart")
			chart = new google.visualization.LineChart(document.getElementById('line_div'));
		if(c=="ColumnChart")
			chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
		else if(c=="PieChart")
			chart = new google.visualization.PieChart(document.getElementById('piechart_div'));
		else if(c=="BarChart")
			chart = new google.visualization.BarChart(document.getElementById('bar_div'));
		else if(c=="GeoChart")
		chart = new google.visualization.GeoChart(document.getElementById('regions_div'));

		chart.draw(data, options);
	}
}

$(document).ready(function () 
{
<?php
	require_once('common.php');
	$graph = new Graph($project_name);
	
	$options = array();
	$options['legend'] = 'none';
	$options['pointSize'] = 3;
	$options['width'] = 400;
	$options['height'] = 200;
	$hAxis = array();
	$hAxis['title'] =  'Week Number';
	$hAxis['format'] = 'MM/dd';
	$vAxis = array();
			
	switch($type)
	{
		case 'progress':
			$gdata = $graph->GetProgressData($milestone);
			$options['title'] = 'My Project Progress';
			$options['curveType'] = 'function';
			
			$hAxis['title'] =  'Weeks';
			
			$vAxis['title'] = 'Progress';
			$vAxis['format'] = '#\'%\'';

			
			break;
		case 'eac':
			$gdata = $graph->GetDurationData($milestone);
			
			$options['title'] = 'Estimate at completion';
			$options['curveType'] = 'function';
		
			$hAxis['title'] =  'Weeks';

			$vAxis['title'] = 'Man Days';
			$vAxis['format'] = '0';
			break;
	}
	$options['hAxis'] = $hAxis;
	$options['vAxis'] = $vAxis;
	$gmdeta = json_encode($options);
	echo 'var data=';
	echo $gdata;
	echo ';';
	
	echo 'var mdata=';
	echo $gmdeta;
	echo ';';

?>	

	//url='../generategraph.php?type=project_progress';
	//ajax_data('GET',url, function(data)
	//{//
		charts(data,mdata,"LineChart");	
		//charts(data,"PieChart");	
		//charts(data,"BarChart");
		//charts(data,"GeoChart");
	//});
});
</script>
<style>
body{font-family:arial}
</style>
</head>
<body style="text-align:center">
<h1></h1>

<div id="line_div"></div>
<div id="chart_div"></div>
<div id="regions_div" style="width: 900px; height: 500px;"></div>
<div id="piechart_div" style="width: 900px; height: 500px;"></div>
<div id="bar_div" style="width: 900px; height: 500px;"></div>


</body>
</html>