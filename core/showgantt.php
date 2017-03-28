<?php
require_once('common.php');

$filtername=FILTER_NAME;
$query=QUERY;
//echo "Updating Jira data".EOL;
//flushout();
$data = new Filter($filtername,$query);

//$KEY = 'MEH-2773';
//print_r($filter->tasks->$KEY);

if(file_exists(GAN_FILE))
{
	$layout = new Gan(GAN_FILE);
	//echo "Reading Layout from ".GAN_FILE." ".EOL;
}

$project = new Project($layout,$data);
// Save JS Gantt
$jsgantt = new JSGantt(GANTT_DATA_FILE,$project);
$jsgantt->Save();

$friday =  date( 'Y-m-d', strtotime('next friday'));
//echo $friday;
$jsgantt = new JSGantt(ARCHIVE_FOLDER."\\".$friday,$project);
$jsgantt->Save($friday);

// Save Gant file
$gan = new Gan(GAN_FILE);
$gan->Save($project);
//$project->SaveJSGanttXML("gantt\\data");
//echo "Done";
//if(isset($noshow))
//{}
//else 
//	header('Location: index.php?project='.$project_name);
?>