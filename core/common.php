<?php

require_once($settings_file);

$folder = "projects\\".$project_name;
define('FILTER_NAME',$folder."\\filter");
define('GAN_FILE',$folder."\\gan");
define('GANTT_DATA_FILE',$folder."\\gantt");
define('ARCHIVE_FOLDER',$folder."\\archive");

// Create Project structure from

require_once('jira.php');
require_once('structure.php');
require_once('filter.php');
require_once('project.php');
require_once('gan.php');
require_once('jsgantt.php');
require_once('graph.php');
//ERRORS
define('ERROR','error');
define('WARN','warn');
define("WEBLINK",JIRA_URL.'/browse/');

date_default_timezone_set('Asia/Karachi');

class Obj{
}

function DebugLog($log)
{
	$traces = debug_backtrace();
	foreach($traces as $trace)
	{
		if($trace['args'][count($trace['args'])-1]=="debug")
		{
			echo debug_backtrace()[1]['function'];
			if(is_array($log))
				print_r($log);
			else
				echo "::".$log."\n";
		}
	}
}

function trace($type,$log)
{
	
	if($type == ERROR)
	{
		if(isset(debug_backtrace()[1]['class']))
			echo "ERROR::".debug_backtrace()[1]['class']."::".debug_backtrace()[1]['function']."::".$log."\n";
		else
			echo "ERROR::"."::".$log."\n";
		exit(-1);

	}
	if($type == WARN)
	{
		echo "WARN::".debug_backtrace()[1]['class']."::".debug_backtrace()[1]['function']."::".$log."\n";
	}
	
}
if(!isset($date))
	$date="";
require_once('pparse.php');

?>
