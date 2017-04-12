<?php
require_once('core/common.php');
require_once('core/pparse.php');
require_once('core/project.php');

$graph = new Graph($project_name);
$milestones = $graph->GetMilestones();
if(count($milestones) > 0)
{
	$project_progress_data = GetProgressData($project_name,$milestones[0]->key);
	$project_progress_data->name = $milestones[0]->name;
}
$week ="";
$twtasks = GetWeeklyReport();

function GetWeeklyReport()
{
	global $week;
	$date = date('Y-M-d');
	$ndate = new DateTime($date);
	$week = $ndate->format("W");

	$thisfriday = date('Y-M-d',strtotime('this friday', strtotime( $date)));

	$filtername=FILTER_NAME;
	$query=QUERY;
	$users=USERS_WEEKLY_REPORT;


	$filter = new Filter($filtername,$query);

	if(strlen($users)>0)
		$twtasks = $filter->GetWeeklyReport($date,$users);
	else
		$twtasks = $filter->GetWeeklyReport($date);
	return $twtasks;
}

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


function EchoProjectStatus($data)
{
	
 
}
function PanelHeader()
{
	global $project_progress_data;
	$data = $project_progress_data;
	echo '<a  style="float:left;" class="navbar-brand" href="../gantt/".$project_name>';
		
	echo $data->name."&nbsp&nbsp&nbsp";
	echo '</a>';
		
	echo '<a  style="color:green;float:left;font-size:150%" class="navbar-brand" href="#">';
	echo $data->tw_progress.'%&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';   
	echo '</a>';
		
	echo '<img src="assets/'.$data->img.'" alt="Smiley face" height="20" width="15" style="position: relative;top:+16px;float:left;">';
		
	if($data->img == 'down.png')
		$color = 'red';
	else
		$color = 'green';
 
	echo '<a  style="color:'.$color.';float:left;" class="navbar-brand" href="#">';
	echo "&nbsp".$data->diff_progress.'%';  
	echo '</a>';
}
function PanelTitle($number)
{
	switch($number)
	{
		case '1':
			echo "Project Status";
			break;
		case '2':
			 echo 'Project Progress Trend';
			 break;
		case '3':
			echo 'Project Estimates/Duration Trend';
			break;
		case '4':
			echo 'Milestones';
			break;
		case '5':
		    global $week;
			echo 'Jira Tasks - '.date("y").'W'.$week;
			break;
			
	}	
}

			
function PanelBody($number)
{
	switch($number)
	{
		case '1':
			global $project_progress_data;
			$data = $project_progress_data;
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
						echo '<p style="font-size:70%;">Due 20-04-2017</p>';
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>';
						echo '<img src="assets/'.$data->img.'" alt="Smiley face" height="15" width="15" style="position: relative;top:+3px;float:left;">';
						if($data->img == 'down.png')
							$color = 'red';
						else
							$color = 'green';
						echo '<a  style="color:'.$color.';position: relative;top:+0px;float:left;" href="#">';
						echo "&nbsp".$data->diff_progress.'%'; 
						echo '</a>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			echo '</div>';
			break;
		case '2':
			 echo '<div id="project_progress_div"></div>';
			 break;
		case '3':
			echo '<div id="project_eac_div"></div>';
			break;
		case '4':
			$index = 0;
			global $milestones;
			global $project_name;
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
						echo   '<img src="assets/'.$data->img.'" alt="Smiley face" height="10" width="5" style="position: relative;top: -3px;">';
						echo  "&nbsp".$data->diff_progress."%";
						echo '</strong> </div>';
					}
				}
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-left" style="font-size:50%;">';
				echo "Base-Line 2017-04-04";
				echo  '</div>';
				
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-center" style="font-size:50%;">';
				echo "Last Wekk 2017-04-04";
				echo  '</div>';
				
				echo  '<div id="progressbar-'.$index.'-text3" class="progressbar-text bottom-right" style="font-size:50%;">';
				echo "This Week 2017-04-04";
				echo  '</div>';
				
				echo	'</div>';
				$index = $index + 1;
			}
			break;
		case '5':
			
			echo '<div class="panel panel-default">';
			echo '<div class="panel-body">';
			echo '<div class="row">';
			echo '<div class="col-xs-12">';
			echo '<ul class="demo2">';
			global $twtasks;
			$done = array();
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
	}
}
 function PanelFooter($number)
 {
	 switch($number)
	 {
		 case '1':
			echo '<span class="datefont" style="float:left;">Last Week - 20-04-2017</span>';
			echo '<span style="float:right;">Base Line - 20-04-2017</span>';
			echo '&nbsp';
			break;
		case '2':
			echo '&nbsp';
			break;
		case '3':
			echo '<span style="color:red;float:left;">Duration</span>';
			echo '<span style="color:blue;float:right;">Estimates</span>';
			echo '&nbsp';
			break;
		case '4':
			echo '&nbsp';
			break;
		case '5':
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
  <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="assets/keen-dashboards.css" />
  <link rel="stylesheet" type="text/css" href="assets/progressbar.css" />
   
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
		GeneratePanelHtml(1,'col-sm-3');
		GeneratePanelHtml(2,'col-sm-3');
		GeneratePanelHtml(3,'col-sm-3');
		GeneratePanelHtml(6,'col-sm-3');
		?>
	</div>
	<div class="row">
		<?php 
		GeneratePanelHtml(4);
		GeneratePanelHtml(5,'col-sm-5');
		GeneratePanelHtml(7,'col-sm-3');
		?>
	</div>

	
    <div class="row">
      <div class="col-sm-3">
        <div class="chart-wrapper">
         dsdsd
        </div>
      </div>
      <div class="col-sm-3">
        <div class="chart-wrapper">
          
		  dsadsd
        </div>
      </div>
      <div class="col-sm-3">
        <div class="chart-wrapper">
          dsadsd
        </div>
      </div>
      <div class="col-sm-3">
        <div class="chart-wrapper">
          dsdsad
        </div>
      </div>
	       
    </div>


  </div>

	<script type="text/javascript">
		
	function charts(data,mdata,type,div)
	{
		var jsonData=data;
		google.load("visualization", "1", {packages:["corechart"], callback: drawVisualization});
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
			$.each(jsonData, function(i,jsonData)
			{
				var value=parseInt(jsonData.y);
				var value1=parseInt(jsonData.z);
		
				//var name=new Date (jsonData.x);
				var date = moment(jsonData.x);
				var name=date.format('W');
				//var name=jsonData.x;
				if( type == "progress")
					data.addRows([ [name, value] ]);
				else if( type == "eac")
					data.addRows([ [name, value, value1] ]);
				
			});
			
			var options = mdata;
			var chart;
			chart = new google.visualization.LineChart(document.getElementById(div));
			chart.draw(data, options);
		}
	}
	<?php
	// Echo google chart deata viriables and fill the data 
	function DrawGraph($project_name,$type)
	{
		$graph = new Graph($project_name);
		$options = array();
		$options['legend'] = 'none';
		$options['pointSize'] = 3;
		$hAxis = array();
		$hAxis['title'] =  'Week Number';
			
		$hAxis['format'] = 'MM/dd';
		$vAxis = array();
		
		switch($type)
		{
			case 'progress':
				$gdata = $graph->GetProgressData('project');

				global $project_progress_data;

				//$options['title'] = 'Progress '.(string)$project_progress_data->name;
				$options['curveType'] = 'function';
				$vAxis['title'] = 'Progress';
				$vAxis['format'] = '#\'%\'';
				break;
			case 'eac':
				$gdata = $graph->GetDurationData('project');
				//$options['title'] = 'Estimate at completion';
				$options['curveType'] = 'function';
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
	charts(data,mdata,"progress","project_progress_div");	
<?php
	DrawGraph($project_name,'eac');
?>
	charts(data,mdata,'eac',"project_eac_div");
	var progressBar;
	CreateProgressBar = function(id,pw,tw,diff)
	{
		
		progressBar = new ProgressBar(id, {'width':'350px', 'height':'6px'});            
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
		   
		//progressBar.setPercent(tw, "totalprogress");
		console.log(diff);
		if(diff > 0)
		{
			progressBar.setPercent(pw, "totalprogress");
			progressBar.setPercent(diff, "thisweekpositive");
		}
		else
		{
			progressBar.setPercent(tw, "totalprogress");
			diff = diff * -1;
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
			ringColor: '#00ff00',
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
	   
			$data = GetProgressData($project_name,$key);
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
		newsTickerInterval: 5000,
		onToDo: function () {
			//console.log(this);
		}
		});
		
		$(".demo2").bootstrapNews({
		newsPerPage: 4,
		autoplay: true,
		pauseOnHover: true,
		navigation: true,
		direction: 'down',
		newsTickerInterval: 5000,
		onToDo: function () {
			//console.log(this);
		}
		});
	}
    </script>
	<script type="text/javascript" src="assets/progressbar.min.js"></script>
	<script src="assets/jquery.min.js" type="text/javascript"></script>
	<script src="assets/moment.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="assets/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/keen.min.js"></script>
	<script type="text/javascript" src="assets/keen.dashboard.js"></script>
	<script src="assets/jQuery.circleProgressBar.js"></script>
	<script src="assets/raphael-min.js"></script>
	<script src="assets/jquery.bootstrap.newsbox.min.js" type="text/javascript"></script>
</body>
</html>