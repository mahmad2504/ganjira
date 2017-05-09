<?php
require_once('core/common.php');
require_once('core/pparse.php');
require_once('core/project.php');

define('PROJECT_PROGRESS_STATUS','1');
define('PROJECT_PROGRESS_GRAPH','2');
define('PROJECT_ESTIMATE_GRAPH','3');
define('PROJECT_MILESTONE_STATUS','4');
define('PROJECT_JIRA_TASKS', '5');
define('PROJECT_CALENDAR','6');
define('PROJECT_VELOCITY_GRAPH','7');
define('PROJECT_GANTT','8');
define('PROJECT_VELOCITY_GAUGE','9');


	
$graph = new Graph($project_name);
$milestones = $graph->GetMilestones();
if(count($milestones) == 0)
{
	echo "Milestones not configured...";
	return;
}
$dur_data = $graph->GetDurationData($milestones[0]->key);
$dur_data =  json_decode($dur_data);
$dur_data = $dur_data[count($dur_data)-1];

//echo $dur_data->start." ".$dur_data->end.EOL;
//return;

$graph = new Graph($project_name,$dur_data->start,$dur_data->end);

$data = $graph->GetVelocityData($milestones[0]->key);
/*$data = json_decode($data);
var_dump($data);
return;*/
for($i=0;$i<count($milestones);$i++)
{
	$milestones[$i]->progress = GetProgressData($graph,$milestones[$i]->key);
	$milestones[$i]->finish = GetEndData($graph,$milestones[$i]->key);
}


//$dur_data = $graph->GetDurationData($milestones[0]->key);

//$dur_data =  json_decode($dur_data);
//$dur_data = $dur_data[count($dur_data)-1];
//echo $dur_data->start." ".$dur_data->end;
//return;
//$graph = new Graph($project_name,$milestones[0]->start,$milestones[0]->end);

//var_dump($milestones);


$week ="";
$twtasks = GetWeeklyReport();
//var_dump($twtasks);
//return;



/*if(count($milestones) > 0)
{
	$project_progress_data = GetProgressData($graph,$milestones[0]->key);
	$project_progress_data->name = $milestones[0]->name;
	if(strlen($milestones[0]->key) == 0)
		$project_key = "project";
	else
		$project_key = (string)$milestones[0]->key;
	$project_end_data = $graph->GetEndData($project_key);
	
	
	$project_end_data = json_decode($project_end_data);
	
	$edata = $project_end_data[count($project_end_data)-1];
	
}*/


function GetWeeklyReport()
{
	global $week;
	$date = date("Y-m-d");
	//$date = date('Y-m-d',strtotime($date."+ 2 days"));
	//$date = date('Y-m-d', strtotime($Date. ' + 1 day'));
	
	//$date = date('Y-M-d');
	
	
	$dayofweek = date('w', strtotime($date)).EOL;
	if( ($dayofweek == 0)||($dayofweek == 6))
		$date = date('Y-m-d',strtotime($date." +2 days"));
	
	//echo $date.EOL;
	$ndate = new DateTime($date);
	$week = $ndate->format("W");
	//echo $week.EOL;

	$thisfriday = date('Y-M-d',strtotime('this friday', strtotime( $date)));

	$filtername=FILTER_NAME;
	$query=QUERY;
	$users=USERS_WEEKLY_REPORT;

	$filter = new Filter($filtername,$query,1);

	if(strlen($users)>0)
		$twtasks = $filter->GetWeeklyReport($date,$users);
	else
		$twtasks = $filter->GetWeeklyReport($date);
	return $twtasks;
}

function GetEndData($graph,$key)
{
	$end_data = $graph->GetEndData($key);
	$end_data = json_decode($end_data);
	//var_dump($end_data);
	//exit();
	return $end_data;
}
function GetProgressData($graph,$milestone)
{

	$return = new Obj();
	
	
	$data = $graph->GetProgressData($milestone);
	$data = json_decode($data);
	$obj = $data[count($data)-1];
	
	$return->tw_progress = $obj->progress;

	$diff_progress = 0;
	$return->pw_progress = 0;
	
	if(count($data) > 1)
	{
		$obj = $data[count($data)-2];
		$return->pw_progress = $obj->progress;
	}
	$return->diff_progress = $return->tw_progress - $return->pw_progress;

	
	if($return->diff_progress > 0)
	{
		$return->img =  "up.png";
		$return->imgh = "up_light.png";
	}
	else
	{
		$return->img =  "down.png";
		$return->imgh = "down.png";
	}
	
	//`var_dump($return);
	return $return;
}
function PanelHeader()
{
	global $milestones;
	$project = $milestones[0];
	$data = $project->progress;
	//echo '<span style="color:red;float:right;"';
	
	
	
	//echo '<canvas  id="current_velocity_gauge" width="100" height="50"></canvas>';
	//echo '<span style="color:red;" id="current_velocity"></span>';
	
	//echo '<canvas  id="required_velocity_gauge" width="100" height="50"></canvas>';
	//echo '<span style="color:red;" id="required_velocity"></span>';
	
	//echo '</span>';
	
	echo '<a  style="float:left;" class="navbar-brand" href="../gantt/".$project_name>';
		
	echo $project->name."&nbsp&nbsp&nbsp";
	echo '</a>';
		
	echo '<a  style="color:Lime;float:left;font-size:150%" class="navbar-brand" href="#">';
	echo $data->tw_progress.'%&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';   
	echo '</a>';
	if($data->tw_progress > 0)
	{
		if($data->img == 'down.png')
		{
			echo '<img src="assets/down.png" alt="Smiley face" height="20" width="15" style="position: relative;top:+16px;float:left;">';
			echo '<a  style="color:red;float:left;" class="navbar-brand" href="#">';
		}
		else
		{
			echo '<img src="assets/up_light.png" alt="Smiley face" height="20" width="15" style="position: relative;top:+14px;float:left;">';
			echo '<a  style="color:Lime;float:left;" class="navbar-brand" href="#">';
		}
		echo "&nbsp".$data->diff_progress.'%';  
	}
	echo '</a>';
}



function PanelTitle($number)
{
	global $milestones;
	$project = $milestones[0];

	
	switch($number)
	{
		case PROJECT_PROGRESS_STATUS:
			$edata = $project->finish[count($project->finish)-1];
			if(strlen($edata->endo) > 0)
			{
				echo "Deadline ";
			        $endo = date('F jS Y', strtotime($edata->endo));
				echo '<span style="float:right;font-size:80%;">&nbsp'.$endo.'</span>';
				echo '<img style="float:right;" src="assets/deadline.png" alt="Smiley face" height="15" width="15"></img>';
		
			}
			else
			{
				echo "Expected Finish ";
				$end = date('F jS Y', strtotime($edata->end));
				echo '<span style="float:right;font-size:80%;">&nbsp'.$end.'</span>';
				//echo '<img style="float:right;" src="assets/deadline.png" alt="Smiley face" height="15" width="15"></img>';
		
			}
			break;
		case PROJECT_PROGRESS_GRAPH:
			 echo 'Progress';
			 break;
		case PROJECT_ESTIMATE_GRAPH:
			echo 'Estimates/Duration';
			break;
		case PROJECT_MILESTONE_STATUS:
			echo 'Milestones';
			break;
		case PROJECT_JIRA_TASKS:
		    global $week;
			echo 'Jira Tasks - '.date("y").'W'.$week;
			break;
		case PROJECT_CALENDAR:
			echo 'Calendar';
			break;
		case PROJECT_VELOCITY_GRAPH:
			echo 'Earned Value/Planned Value';
			break;
			
	}	
}			
function PanelBody($number)
{
	global $milestones;
	$project = $milestones[0];
	switch($number)
	{
		case PROJECT_PROGRESS_STATUS:			
			$data = $project->progress;
			echo '<div align="center">';
			echo'<table style="top:+3px;display:inline-block">';
				echo'<tr>';
					echo'<td rowspan="3">';
						echo'<div class="circleprogress" style="position: relative;width:150px;height:198px;float:right;">';
							echo'<p style="display:none;">'.$data->tw_progress."%";
						echo'</div>';
					echo '</td>';
					echo '<th></th>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>';

					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>';
						if($data->tw_progress > 0)
						{
							if($data->img == 'down.png')
							{
								echo '<img src="assets/down.png" alt="Smiley face" height="15" width="15" style="position: relative;top:+3px;float:left;">';
								echo '<a  style="color:red;position: relative;top:+0px;float:left;" href="#">';
							}
							else
							{
								echo '<img src="assets/up.png" alt="Smiley face" height="15" width="15" style="position: relative;top:+1px;float:left;">';
								echo '<a  style="color:green;position: relative;top:+0px;float:left;" href="#">';
							}
							echo "&nbsp".$data->diff_progress.'%';
						}
						echo '</a>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			echo '<div style="float:right;" id="velocity_div" ></div>';
			echo '</div>';
			
			break;
		case PROJECT_PROGRESS_GRAPH:
			 echo '<div id="project_progress_div"></div>';
			 break;
		case PROJECT_ESTIMATE_GRAPH:
			echo '<div id="project_eac_div"></div>';
			break;
		case PROJECT_MILESTONE_STATUS:
			for($i=1;$i<count($milestones);$i++)
			{
				$milestone = $milestones[$i];
				$data = $milestone->progress;
			    $key = $milestone->key;
			    $name = $milestone->name;
				$index = $i;
				//$data = GetProgressData($graph,$key);
				echo '<div class="progressbar-container">';
				echo '<div id="progressbar-'.$index.'-text2" class="progressbar-text top-left">'.$name.'&nbsp<strong>'.$data->tw_progress.'%</strong></div>';
				echo '<div id="progressbar-'.$index.'"></div>';
				
				//echo  '<span style="float:right;">';
				if($data->diff_progress != 0)
				{
					//echo  '<div id="progressbar-'.$index.'" style="color:green" class="progressbar-text bottom-right">'.$data->diff_progress.'%&nbsp&nbsp</div>';
					//echo   '<img src="assets/'.$data->img.'" alt="Smiley face" height="10" width="5" style="position: relative;top: -3px;">';
					//echo  $data->diff_progress;
					if($data->diff_progress < 0)
					{
						$color = 'red';
						$data->diff_progress = $data->diff_progress * -1;
						echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text top-right" style="color:'.$color.';"><strong>';
						echo   '<img src="assets/'.$data->img.'" alt="Smiley face" height="10" width="10" style="position: relative;top: -1px;">';
						echo  "&nbsp".$data->diff_progress."%";
						echo '</strong> </div>';
					}
					else
					{
						$color = 'green';
						echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text top-right" style="color:'.$color.';"><strong>';
						echo   '<img src="assets/'.$data->img.'" alt="Smiley face" height="10" width="7" style="position: relative;top: -1px;">';
						echo  "&nbsp".$data->diff_progress."%";
						echo '</strong> </div>';
					}
				}
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-left" style="font-size:50%;">';
				//echo "Base-Line 2017-04-04";
				echo  '</div>';
				
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-center" style="font-size:50%;">';
				//echo "Last Wekk 2017-04-04";
				echo  '</div>';
				
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-right" style="font-size:50%;">';
				//echo "This Week 2017-04-04";
				echo  '</div>';
				
				echo	'</div>';
				$index = $index + 1;
			}
			break;
		case PROJECT_JIRA_TASKS:
			
			echo '<div class="panel panel-default">';
			echo '<div class="panel-body">';
			echo '<div class="row">';
			echo '<div class="col-xs-24">';
			echo '<ul class="demo2">';
			global $twtasks;
			
			foreach($twtasks as $twtask)
			{
				echo '<li class="news-item">';
				$jiralink=JIRA_URL."/browse/".$twtask->key;
				$linktext = '<a style="font-size:90%" href="'.$jiralink.'">'.$twtask->key.'</a>';
				//echo '<div style="float:left"><div>'.$linktext;
				echo $linktext;
				//echo '<span>'."&nbsp&nbsp;&nbsp;&nbsp".$twtask->summary.'</span>';
				//echo '</div>';
				echo '<span style="font-size:90%">'."&nbsp&nbsp".$twtask->summary.'</span>';
				$done = array();
				for($i=count($twtask->worklogs)-1;$i>=0;$i--)
				{
					$worklog = $twtask->worklogs[$i];
					if($worklog->thisweek == 1)
					{
						$time = explode(":",$worklog->time);
						$date = new DateTime($worklog->started);
						$date = $date->format('d M');
						//echo '<p style="clear:both;">'.$worklog->comment.'</p>';
						if(isset($done[$worklog->author]))
						{
							
						}
						else
						{
							$userlink=JIRA_URL."/secure/ViewProfile.jspa?name=".$worklog->author;
							echo '<p align="left" style="font-size:80%"><a href="'.$userlink.'">'.$worklog->displayname.'</a></p>';
							$done[$worklog->author] = 1;
						}
					}
				}
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			break;
		case PROJECT_CALENDAR:
			echo '<div style="width:100%; max-width:220px; display:inline-block">';
			echo '<div style="aposition: absolute; left: 20%;top: 50%;" class="monthly" id="mycalendar"></div>';
			echo '</div>';
			break;
		case PROJECT_VELOCITY_GRAPH:
			echo '<div id="project_velocity_div"></div>';
			 break;
			break;
		case PROJECT_VELOCITY_GAUGE:
			//echo '<div style="float:right;" id="velocity_div" ></div>';
			break;
		case PROJECT_GANTT:
			global $project_name;
			echo '<style>
#wrap
{
    width: 320px;
    height: 238px;
    padding: 0;
    overflow: hidden;
}

#frame
{
    width: 1280px;
    height: 2000px;
    border: 0;

    -ms-transform: scale(0.25);
    -moz-transform: scale(0.25);
    -o-transform: scale(0.25);s
    -webkit-transform: scale(0.25);
    transform: scale(0.25);

    -ms-transform-origin: 0 0;
    -moz-transform-origin: 0 0;
    -o-transform-origin: 0 0;
    -webkit-transform-origin: 0 0;
    transform-origin: 0 0;
}
</style>';
			echo '<div id="wrap">
			<iframe id="frame" src="../gantt/index.php?project='.$project_name.'"></iframe>
			<a href="../gantt/'.$project_name.'" style="position:absolute; top:0; left:0; display:inline-block; width:500px; height:500px; z-index:5;"></a>
			</div>';
			
			
			break;
	}
 }
 function PanelFooter($number)
 {
	 global $milestones;
	 $project = $milestones[0];
	 $tw_end_data = $project->finish[count($project->finish)-1];
	 
	 switch($number)
	 {
		 case PROJECT_PROGRESS_STATUS:
			$enteries = count($project->finish);
			if($enteries > 1)
			{
				$pwdata = $project->finish[$enteries-2];
				if(strlen($tw_end_data->endo) > 0)
				{
					$endo = date('F jS Y', strtotime($pwdata->endo));
					echo '<span class="datefont" style="float:left;">Last Week - '.$endo.'</span>';
				}	
				else
				{
					$end = date('F jS Y', strtotime($pwdata->end));
					echo '<span class="datefont" style="float:left;">Last Week - '.$end.'</span>';
				}
			}
			if(strlen($tw_end_data->endo) == 0) // No deadline , then it is already displayed
			{
				
			}
			else
			{
				$end = date('F jS Y', strtotime($tw_end_data->end));
				echo '<span class="datefont" style="float:right;">Expected - '.$end.'</span>';
			}
			//if(strlen($tw_end_data->endo) > 0)
			//{
			//	$endo = date('F jS Y', strtotime($tw_end_data->endo));
			//	echo '<span class="datefont" style="float:right;">Baseline - '.$endo.'</span>';
			//}
			//else
			//{
				//$end = date('F jS Y', strtotime($baseline->end));
			//	echo '<span class="datefont" style="float:left;">Baseline - None</span>';
			
			//}
			echo '&nbsp';
			break;
		case PROJECT_PROGRESS_GRAPH:
			echo '&nbsp';
			break;
		case PROJECT_ESTIMATE_GRAPH:
			global $dur_data;
			echo '<span style="color:red;float:left;">Duration '.$dur_data->duration.'</span>';
			echo '<span style="color:blue;float:right;">Estimate '.$dur_data->estimate.'</span>';
			echo '&nbsp';
			break;
		case PROJECT_MILESTONE_STATUS:
			echo '&nbsp';
			break;
		case PROJECT_JIRA_TASKS:
			echo '&nbsp';
			break;
		case PROJECT_CALENDAR:
			echo '&nbsp';
			break;
		case PROJECT_VELOCITY_GRAPH:
			global $milestones;
			global $graph;
			$gdata = $graph->GetVelocityData($milestones[0]->key);
			$gdata = json_decode($gdata);
			$current = 0;
			$planned = 0;
			foreach($gdata as $data)
			{
				if($data->current > 0)
				{
					$current = round($data->current_unmodified);
					$planned =  round($data->required);
				}
			}
			echo '<span style="color:red;float:left;">Planned Value '.$planned.'</span>';
			echo '<span style="color:blue;float:right;">Earned Value '.$current.'</span>';
			
			//$gdata = $graph->GetVelocityData($project->key);
			
			
			echo '&nbsp';
			break;
		
	 }
}
function GeneratePanelHtml($number,$class='col-sm-4')
{
	echo '<div class="'.$class.'"> <div class="chart-wrapper"> <div class="chart-title">';
	PanelTitle($number);
	echo '</div><div class="chart-stage" width="10" >';
	PanelBody($number);
	echo '</div><div class="chart-notes">';
	PanelFooter($number);
	echo '</div></div></div>';
}
 
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Starter UI, by Keen IO</title>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
  <link rel="stylesheet" type="text/css" href="assets/bootstrap.css" />
  <link rel="stylesheet" type="text/css" href="assets/keen-dashboards.css" />
  <link rel="stylesheet" type="text/css" href="assets/progressbar.css" />
  <link rel="stylesheet" type="text/css" href="assets/monthly.css" />

   
	<style>

	.datefont {
	font-size:80%;
	color: transparent; 										
	background-color: #666666;
		   -webkit-background-clip: text;
		   -moz-background-clip: text;
		   background-clip: text;
		   text-shadow: rgba(255,255,255,0.5) 0px 3px 3px;
    }
	
	</style>
</head>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<body class="application">
	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		
		<div class="container-fluid">
			<div class="navbar-header">
				<?php PanelHeader();?>
			</div>
		</div>
	</div>
	<div class="container-fluid" >
		
	
	<div class="row">
		<?php 
		if(!isset($summary))
			$summary = '0';
		
		if($summary==1)
		{
			GeneratePanelHtml(PROJECT_PROGRESS_STATUS,'col-sm-4');
			GeneratePanelHtml(PROJECT_VELOCITY_GRAPH,'col-sm-8');
		}
		else if($summary==2)
		{
			GeneratePanelHtml(PROJECT_PROGRESS_STATUS,'col-sm-3');
			GeneratePanelHtml(PROJECT_MILESTONE_STATUS,'col-sm-4');
			GeneratePanelHtml(PROJECT_VELOCITY_GRAPH,'col-sm-5');
		}
		else if( $summary=='progress')
		{
		//	echo $summary.EOL;
			GeneratePanelHtml(PROJECT_PROGRESS_STATUS,'col-sm-12');
		}
		else 
		{
			GeneratePanelHtml(PROJECT_PROGRESS_STATUS,'col-sm-3');
			GeneratePanelHtml(PROJECT_PROGRESS_GRAPH,'col-sm-3');
			GeneratePanelHtml(PROJECT_ESTIMATE_GRAPH,'col-sm-3');
			GeneratePanelHtml(PROJECT_GANTT,'col-sm-3');
		}
		?>
	</div>
	<div class="row">
		<?php 
		if($summary==1)
		{
			
		}
		else if($summary==2)
		{
			
		}
		else if($summary=='progress')
		{ }
		else
		{
			GeneratePanelHtml(PROJECT_MILESTONE_STATUS);
			GeneratePanelHtml(PROJECT_VELOCITY_GRAPH,'col-sm-5');
			GeneratePanelHtml(PROJECT_CALENDAR,'col-sm-3');
		}
		?>
	</div>
	<div class="row">
		<?php 
		if($summary==1)
		{
			
		}
		else if($summary==2)
		{
			
		}
		else if($summary=='progress')
		{ }
		else
			GeneratePanelHtml(PROJECT_JIRA_TASKS,'col-sm-5');
		
		?>
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


  </div>

	<script type="text/javascript">
		
	function charts(data,mdata,type,div)
	{
		var jsonData=data;
		google.load("visualization", "1", {packages:["corechart"], callback: drawVisualization});
		google.load("visualization", "1", {packages:["gauge"], callback: DrawGauge});

		<?php
		
		
		echo 'var required_velocity = '.$dur_data->required_velocity.";";
		echo 'var current_velocity = '.$dur_data->current_velocity.";";
		
		?>
		//current_velocity = required_velocity;
		factor = required_velocity/2;
		//var section = (required_velocity+factor)/4;
		
		function DrawGauge() 
		{
			var data = google.visualization.arrayToDataTable([
			['Label', 'Value'],
			['Velocity', current_velocity]
		]);
		var maximum =  parseFloat(factor*3).toFixed(1); ;
		var options = {
			width: 70,
			height: 70,
			redFrom: 0,
			redTo: factor,
			yellowFrom: factor,
			yellowTo: factor*2,
			greenFrom: factor*2,
			greenTo: factor*3,
			minorTicks: 20,
			max: maximum,
			min: 0,
			majorTicks: ['0', maximum]
		};
		var chart = new google.visualization.Gauge(document.getElementById('velocity_div'));
		chart.draw(data, options);

		}
		function drawVisualization2()
		{
			
		}
		
		function drawVisualization() 
		{
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Data');
			
			if( type == "progress")
				data.addColumn('number', 'Progress');
			else if( type == "eac")
			{
				data.addColumn('number', 'Efforts');
				data.addColumn('number', 'Duration');
			}
			else if(type == "velocity")
			{
				data.addColumn('number', 'Current');
				data.addColumn('number', 'Required');
				//data.addColumn('number', 'Axis');
			}
			
			$.each(jsonData, function(i,jsonData)
			{
				if( type == "progress")
					var value=parseInt(jsonData.progress);
				else if( type == "eac")
				{
					var value=parseInt(jsonData.estimate);
					var value1=parseInt(jsonData.duration);
				}
				else if( type == "velocity")
				{
					var value=parseFloat(jsonData.current);
					var value1=parseFloat(jsonData.required);
				}
				//var name=new Date (jsonData.x);
				var weeknumber = moment(jsonData.date, "YYYY-MM-DD").week();
				var day = moment(jsonData.date, "YYYY-MM-DD");
				day = day.format('D');
				//var name = moment(jsonData.date);
				
				//var name=date.format('W');
				
				//console.log(jsonData.date);
				//name =  String(jsonData.date);
				
				if( type == "progress")
				{
					name = weeknumber;
					data.addRows([ [name, value] ]);
				}
				else if( type == "eac")
				{
					name = weeknumber;
					data.addRows([ [name, value, value1] ]);
				}
				else if( type == "velocity")
				{
					name = day;
					data.addRows([ [name, value, value1] ]);
				}
				
			});
			
			var options = mdata;
			var chart;
			chart = new google.visualization.ComboChart(document.getElementById(div));
			chart.draw(data, options);
		}
	}
	<?php
	// Echo google chart deata viriables and fill the data 
	function DrawGraph($project_name,$type)
	{
		global $graph;
		global $milestones;
		//$graph = new Graph($project_name);
		$project = $milestones[0];
		$options = array();
		$options['legend'] = 'none';
		$options['pointSize'] = 3;
		$hAxis = array();
		
			
		$hAxis['format'] = 'MM/dd';
		$vAxis = array();
		$vAxis['viewWindow']['min']  = -5;
		switch($type)
		{
			case 'progress':
				$hAxis['title'] =  'Week Number';
			    //echo $project_key.EOL;
				//echo 'console.log("fff");';
				//echo 'console.log('.$project_key.');';
				//$gdata = GetProgressData($graph,$project->key);
				$gdata = $graph->GetProgressData($project->key);

				//global $project_progress_data;

				//$options['title'] = 'Progress '.(string)$project_progress_data->name;
				$options['curveType'] = 'function';
				$vAxis['title'] = 'Progress';
				$vAxis['format'] = '#\'%\'';
				break;
			case 'eac':
				$hAxis['title'] =  'Week Number';
				$gdata = $graph->GetDurationData($project->key);
				//$options['title'] = 'Estimate at completion';
				$options['curveType'] = 'function';
				$vAxis['title'] = 'Man Days';
				$vAxis['format'] = '0';
				break;
			case 'velocity':
				$hAxis['title'] =  'Days';
				$gdata = $graph->GetVelocityData($project->key);
				$dur_data = json_decode($graph->GetDurationData($project->key));
				
				
				$dur_data = $dur_data[count($dur_data)-1];
				
				//$options['title'] = 'Estimate at completion';
				//$options['curveType'] = 'function';
				$vAxis['title'] = 'Earned Value';
				$vAxis['format'] = '0';
				
				$vAxis['viewWindowMode'] = 'explicit';
				
				$vAxis['viewWindow']['max']  = $dur_data->estimate;
				$vAxis['viewWindow']['min']  = 0;
				
				$options['seriesType'] = 'line';
				
				$type = array();
				$type['type'] = 'bars';
				$options['series']=array();
				$options['series'][0] = $type;    
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
	charts(data,mdata,"progress","project_progress_div");	
<?php
	DrawGraph($project_name,'eac');
?>
	charts(data,mdata,'eac',"project_eac_div");
<?php
	DrawGraph($project_name,'velocity');
?>
	charts(data,mdata,'velocity',"project_velocity_div");
	
	
	var progressBar;
	CreateProgressBar = function(id,pw,tw,diff)
	{
		if((tw == 100)&&(diff == 0))
		{
			progressBar = new ProgressBar(id, {'width':'350px', 'height':'6px'});            
			progressBarItem = {};
			progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "totalprogress";
			progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = 'lgrey';
			progressBar.createItem( progressBarItem );
			
		}
		else
		{
			progressBar = new ProgressBar(id, {'width':'350px', 'height':'6px'});            
			progressBarItem = {};
			progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "totalprogress";
			progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = ProgressBar.OPTION_VALUE.COLOR_ID.GREEN;
			progressBar.createItem( progressBarItem );
		}
		progressBarItem = {};
		progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "thisweekpositive";
		progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = 'lgreen';//ProgressBar.OPTION_VALUE.COLOR_ID.YELLOW;
		progressBar.createItem( progressBarItem );
		
		progressBarItem = {};
		progressBarItem[ProgressBar.OPTION_NAME.ITEM_ID]  = "thisweeknegative";
		progressBarItem[ProgressBar.OPTION_NAME.COLOR_ID]   = ProgressBar.OPTION_VALUE.COLOR_ID.RED;
		progressBar.createItem( progressBarItem );
		   
		//progressBar.setPercent(tw, "totalprogress");
		console.log(diff);
		if(diff > 0)
		{   
			progressBar.setPercent(pw, "totalprogress");
			progressBar.setPercent(diff, "thisweekpositive");
		}
		else
		{
			diff = diff * -1;
			if(diff + tw == 100)
				tw = tw -1;
			progressBar.setPercent(tw, "totalprogress");
			
			
			progressBar.setPercent(diff, "thisweeknegative");
		}
	}
    window.onload = function()
	{
/////////////////// Draw Circular Progress Bar /////////////////////////////
		$('.circleprogress').percentageLoader({
			valElement: 'p',
			strokeWidth: 30,
			bgColor: '#d9d9d9',
			ringColor: '#00b300',
			textColor: '#2C3E50',
			fontSize: '27px',
			fontWeight: 'bold'
		});
				
/////////////////// Draw Linear Progress Bar of each milestone /////////////////////////////
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
	   
			$data = GetProgressData($graph,$key);
			
			
			
			echo 'var pw_progress = '.$data->pw_progress.';';
			echo 'var diff = '.$data->diff_progress.';';
			echo 'var tw_progress = '.$data->tw_progress.';';
			echo 'CreateProgressBar("progressbar-'.$index.'",'.$data->pw_progress.','.$data->tw_progress.','.$data->diff_progress.');';
			$index = $index + 1;
		}
?>
/////////////////// Draw News Plugin /////////////////////////////
		$(".demo1").bootstrapNews({
		newsPerPage: 14,
		autoplay: false,
		pauseOnHover: true,
		navigation: false,
		direction: 'down',
		newsTickerInterval: 3000,
		onToDo: function () {
			//console.log(this);
		}
		});
		
		$(".demo2").bootstrapNews({
		newsPerPage: 4,
		autoplay: true,
		pauseOnHover: true,
		navigation: false,
		direction: 'down',
		newsTickerInterval: 3000,
		onToDo: function () {
			//console.log(this);
		}
		});
		$('#mycalendar').monthly({
			mode: 'event',
			//jsonUrl: 'json.phps',
			//dataType: 'json'
			xmlUrl: 'events.xml'
		});
		
		
	}
    </script>
	<script type="text/javascript" src="assets/progressbar.min.js"></script>
	<script src="assets/jquery.min.js" type="text/javascript"></script>
	<script src="assets/moment.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="assets/bootstrap.min.js"></script>
	
	<script src="assets/jQuery.circleProgressBar.js"></script>
	<script src="assets/raphael-min.js"></script>
	<script src="assets/jquery.bootstrap.newsbox.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="assets/monthly.js"></script>
</body>
</html>