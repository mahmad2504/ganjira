<?php
require_once('common.php');	
require_once('project_settings.php');

$filtername=FILTER_NAME;
$query=QUERY;
echo "Updating Jira data".EOL;
flushout();
$data = new Filter($filtername,$query);

//$KEY = 'MEH-2773';
//print_r($filter->tasks->$KEY);


if(PROJECT_LAYOUT == JIRA_STRUCTURE)
{
	echo "Reading Jira structure".EOL;
	flushout();
	$layout = new Structure(PROJECT_LAYOUT);
}
else
{
	$layout = new Gan(GAN_FILE);
	echo "Reading Layout from ".GAN_FILE." ".EOL;
	
}

$project = new Project($layout,$data);
// Save JS Gantt
$jsgantt = new JSGantt(GANTT_DATA_FILE,$project);
$jsgantt->Save();

// Update weekend data as well
$generate_weekly_data = 1;
if(isset($project_status))
{
	if($project_status == 0)
		$generate_weekly_data = 0;
}
	

if($generate_weekly_data==1)
{
	$day = date('D');
	if($day == 'Fri')
		$friday = date('Y-m-d');
	else if($day == 'Sat')
		$friday =  date('Y-m-d', strtotime('previous friday'));
	else if($day == 'Sun')
		$friday =  date('Y-m-d', strtotime('previous friday'));
	else
		$friday =  date('Y-m-d', strtotime('next friday'));

	$jsgantt = new JSGantt(ARCHIVE_FOLDER."\\".$friday.".xml",$project);
	$jsgantt->Save($friday);
}


// Save Gant file
$gan = new Gan(GAN_FILE);
$gan->Save($project);
//$project->SaveJSGanttXML("gantt\\data");
echo "Done";
?>