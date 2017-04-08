<?php

require_once('core/pparse.php');

if(isset($path))
{
	if(count($path) != 4)
	{
		echo "URL Errors";
		return;
	}
	$cmd = $path[2];
	$project_name = $path[3];

}
//$url = parse_url($_SERVER["REQUEST_URI"]);

//$params =  explode("/",$url['path']);

//$cmd = $params[2];
//$project_name$project_name = $params[3];
if( !is_dir('projects\\'.$project_name))
{
	echo $project_name." does not exist";
	return;
}
$settings_file = 'projects\\'.$project_name.'\\settings.php';

switch($cmd)
{
	case 'dashboard':
		include 'dashboard\\show.php';
		break;
	case 'calendar':
		include 'calendar\\show.php';
		break;
	case 'build':
		include 'core\\buildgantt.php';
		break;
	case 'gantt':
		include 'core\\showgantt.php';
		break;
	case 'graph':
		include 'core\\showgraph.php';
		break;
	case 'rebuild':
		echo "Rebuilding.....".EOL;
		flushout();
		$filter = 'projects\\'.$project_name.'\\filter';
		if(file_exists($filter))
			unlink($filter);
		include 'core\\buildgantt.php';
		break;
	case 'timesheet':
		include 'core\\timesheet.php';
		break;
	case 'weeklyreport':
		include 'core\\weeklyreport.php';
		break;
	default:
		echo "invalid command".EOL;
		break;
	
	
}



?>