<?php
require_once('core/common.php');
require_once('core/pparse.php');

$graph = new Graph($project_name);

function GetProgressData($project_name,$milestone)
{
	global $graph;
	$return = new Obj();
	
	
	$data = $graph->GetProgressData($milestone);
	$data = json_decode($data);
	$obj = $data[count($data)-1];
	
	$return->tw_progress = $obj->y;
	$diff_progress = 0;
	$return->pw_progress = 0;
	if(count($data) > 1)
	{
		$obj = $data[count($data)-2];
		$return->pw_progress = $obj->y;
	}
	$return->diff_progress = $return->tw_progress - $return->pw_progress;
	if($return->diff_progress > 0)
		$return->img =  "up.png";
	else
		$return->img =  "down.png";
	
	//`var_dump($return);
	return $return;
}

$milestones = $graph->GetMilestones();
if(count($milestones) > 0)
{
	$data = GetProgressData($project_name,$milestones[0]->key);
	$data->name = $milestones[0]->name;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Starter UI, by Keen IO</title>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
  <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="assets/keen-dashboards.css" />
  <link rel="stylesheet" href="assets/progressbar.css" />
    <link rel="stylesheet" href="assets/circle-progressbar.css" />
</head>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<body class="application">
  <div class="container-fluid">
    <div class="row">

      <div class="col-sm-4">
        <div class="chart-wrapper">
          <div class="chart-title">
		   
            <?php echo $data->name."   -   " ?> <?php echo $data->tw_progress; ?>% <span style="float:right;">&nbsp&nbsp<?php echo $data->diff_progress;  ?>% 
			
			<img src="assets/<?php echo $data->img; ?>" alt="Smiley face" height="15" width="10" style="position: relative;top: -3px;">
			
			</span>
          </div>
          <div class="chart-stage">
       
	   <?php  
		    $index = 0;
			foreach($milestones as $milestone)
	       {
			   if($index == 0) // Ignore first milestone as it is shown in heading
			   {
				   $index = $index + 1;
				   continue;
			   }
			   $key = $milestone->key;
			   $name = $milestone->name;
			   
				$data = GetProgressData($project_name,$key);
				echo '<div class="progressbar-container">';
				echo '<div id="progressbar-'.$index.'-text2" class="progressbar-text top-left">'.$name.'</div>';
				echo '<div id="progressbar-'.$index.'"></div>';
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-left"><strong> Current Progress '.$data->tw_progress.'% </strong> </div>';
				echo  '<span style="float:right;">';
				if($data->diff_progress != 0)
				{
					echo  '<div id="progressbar-'.$index.'" style="color:green float:left" class="progressbar-text bottom-right">'.$data->diff_progress.'%&nbsp&nbsp</div>';
					echo   '<img src="assets/up.png" alt="Smiley face" height="10" width="5" style="position: relative;top: -3px;">';
				}
				echo   '</span>';
				echo	'</div>';
				$index = $index + 1;
		   }
		   ?>
          </div>
          <div class="chart-notes">
            This is a sample text region to describe this chart.
          </div>
        </div>
      </div>

      <div class="col-sm-4">
        <div class="chart-wrapper">
          <div class="chart-title">
            Pageviews by browser (past 5 days)
          </div>
          <div class="chart-stage">
            <div id="project_progress_div"></div>
          </div>
          <div class="chart-notes">
            Notes go down here
          </div>
        </div>
      </div>
	  
	  <div class="col-sm-4">
        <div class="chart-wrapper">
          <div class="chart-title">
            Pageviews by browser (past 5 days)
          </div>
          <div class="chart-stage">
            <div id="project_eac_div"></div>
          </div>
          <div class="chart-notes">
            Notes go down here
          </div>
        </div>
      </div>

    </div>


    <div class="row">

      <div class="col-sm-4">
        <div class="chart-wrapper">
          <div class="chart-title">
            Impressions by advertiser
          </div>
          <div class="chart-stage">
            	<div class="percent" style="width:100px;height:100px;">
					<p style="display:none;">40%</p>
				</div>
				<div class="percent1" style="width:100px;height:100px;">
					<p style="display:none;">40%</p>
				</div>
          </div>
          <div class="chart-notes">
            Notes go down here
          </div>
        </div>
      </div>

      <div class="col-sm-4">
        <div class="chart-wrapper">
          <div class="chart-title">
			<div class="panel-heading"> <span class="glyphicon glyphicon-list-alt"></span><b>&nbsp&nbsp Jira Tasks</b></div>
          </div>
          <div class="chart-stage">


<div class="panel panel-default">

<div class="panel-body">
<div class="row">
<div class="col-xs-12">
<ul class="demo2">
<li class="news-item">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in venenatis enim... <a href="#">Read more...</a></li>
<li class="news-item">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in venenatis enim... <a href="#">Read more...</a></li>
<li class="news-item">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in venenatis enim... <a href="#">Read more...</a></li>
<li class="news-item">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in venenatis enim... <a href="#">Read more...</a></li>
<li class="news-item">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in venenatis enim... <a href="#">Read more...</a></li>
<li class="news-item">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in venenatis enim... <a href="#">Read more...</a></li>
</ul>
</div>
</div>
</div>
</div>


</div>
</div>
</div>
      <div class="col-sm-4">
        <div class="chart-wrapper">
          <div class="chart-title">
            Impressions by country
          </div>
          <div class="chart-stage">
            
          </div>
          <div class="chart-notes">
            Notes go down here
          </div>
        </div>
      </div>

    </div>

    <div class="row">
      <div class="col-sm-3">
        <div class="chart-wrapper">
         
        </div>
      </div>
      <div class="col-sm-3">
        <div class="chart-wrapper">
          
		  
        </div>
      </div>
      <div class="col-sm-3">
        <div class="chart-wrapper">
          
        </div>
      </div>
      <div class="col-sm-3">
        <div class="chart-wrapper">
          
        </div>
      </div>
    </div>

    <hr>

  </div>

   <script type="text/javascript">
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
				//if(c=="ProjectProgressChart")
					chart = new google.visualization.LineChart(document.getElementById(ChartType));
				/*if(c=="ColumnChart")
					chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
				else if(c=="PieChart")
					chart = new google.visualization.PieChart(document.getElementById('piechart_div'));
				else if(c=="BarChart")
					chart = new google.visualization.BarChart(document.getElementById('bar_div'));
				else if(c=="GeoChart")
				chart = new google.visualization.GeoChart(document.getElementById('regions_div'));*/

				chart.draw(data, options);
			}
		}

		<?php
		function DrawGraph($project_name,$type)
		{
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
					$gdata = $graph->GetProgressData('project');
					$options['title'] = 'My Project Progress';
					$options['curveType'] = 'function';
					
					//$hAxis['title'] =  'Weeks';
					
					$vAxis['title'] = 'Progress';
					$vAxis['format'] = '#\'%\'';

					
					break;
				case 'eac':
					$gdata = $graph->GetDurationData('project');
					
					$options['title'] = 'Estimate at completion';
					$options['curveType'] = 'function';
				
					//$hAxis['title'] =  'Weeks';

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
		}
		DrawGraph($project_name,'progress');
?>
		charts(data,mdata,"project_progress_div");	
<?php
		DrawGraph($project_name,'eac');
?>
				charts(data,mdata,"project_eac_div");
			var progressBar;
			CreateProgressBar = function(id,pw,tw,diff)
			{
				
				progressBar = new ProgressBar(id, {'width':'300px', 'height':'6px'});            
                progressBarItem = {};
                progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "totalprogress";
                progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = ProgressBar.OPTION_VALUE.COLOR_ID.GREEN;
                progressBar.createItem( progressBarItem );
  
                progressBarItem = {};
                progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "thisweekpositive";
                progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = 'lgreen';//ProgressBar.OPTION_VALUE.COLOR_ID.YELLOW;
                progressBar.createItem( progressBarItem );
                
                progressBarItem = {};
                progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "thisweeknegative";
                progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = ProgressBar.OPTION_VALUE.COLOR_ID.RED;
                progressBar.createItem( progressBarItem );
                   
				progressBar.setPercent(pw, "totalprogress");
				console.log(diff);
				if(diff > 0)
					progressBar.setPercent(diff, "thisweekpositive");
				else
					progressBar.setPercent(diff, "thisweeknegative");
			}
            window.onload = function(){
<?php
				$index = 0;
                foreach($milestones as $milestone)
				{
					if($index == 0)
					{
						$index = $index + 1;
						continue;
					}
					$key = $milestone->key;
					$name = $milestone->name;
			   
					$data = GetProgressData($project_name,$key);
					echo 'var pw_progress = '.$data->pw_progress.';';
					echo 'var diff = '.$data->diff_progress.';';
					echo 'var tw_progress = '.$data->tw_progress.';';

					//$data = GetProgressData($project_name,$milestone);
					echo 'CreateProgressBar("progressbar-'.$index.'",'.$data->pw_progress.','.$data->tw_progress.','.$data->diff_progress.');';
					//echo 'CreateProgressBar("progressbar-'.$index.'");';
					//echo 'CreateProgressBar("progressbar-'.$index.'");';
					//echo 'CreateProgressBar("progressbar-'.$index.'");';
					$index = $index + 1;
				}
?>
			 $('.percent').percentageLoader({
				valElement: 'p',
				strokeWidth: 10,
				bgColor: '#d9d9d9',
				ringColor: '#00ff00',
				textColor: '#2C3E50',
				fontSize: '14px',
				fontWeight: 'normal'
			});
			
			$('.percent1').percentageLoader({
				valElement: 'p',
				strokeWidth: 30,
				bgColor: '#d9d9d9',
				ringColor: '#ff0000',
				textColor: '#2C3E50',
				fontSize: '14px',
				fontWeight: 'normal'
			});
			$(".demo2").bootstrapNews({
            newsPerPage: 4,
            autoplay: true,
			pauseOnHover: true,
			navigation: true,
            direction: 'down',
            newsTickerInterval: 2500,
            onToDo: function () {
                //console.log(this);
            }
        });



            }
        </script>
  
  
  <script type="text/javascript" src="assets/progressbar.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>
  <script src="https://momentjs.com/downloads/moment.min.js" type="text/javascript"></script>
  <script type="text/javascript" src="assets/bootstrap.min.js"></script>

  
  <script type="text/javascript" src="assets/keen.min.js"></script>


  <script type="text/javascript" src="assets/keen.dashboard.js"></script>
  <script src="assets/jQuery.circleProgressBar.js"></script>
  <script src="assets/raphael-min.js"></script>
  <script src="assets/jquery.bootstrap.newsbox.min.js" type="text/javascript"></script>
  

</body>
</html>