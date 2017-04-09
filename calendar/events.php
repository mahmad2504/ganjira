<?php

$colors = array(
"#87CEFA",
"#6A5ACD",
"#EE82EE",
"#87CEEB",
"#B0E0E6",
"#FFC0CB",
);
function GetProjectNames()
{
	$directory = 'projects';
	$dir = opendir($directory);
	while(false != ($file = readdir($dir)))
    {
		if(($file != ".") and ($file != "..") and ($file != 'all')) 
		{
			$projects[] = $file; // put in array.
		}  
	}
	return $projects;
	
}
function EchoTaskData($title,$id,$task)
{
	global $colors;
	echo '{';
	echo  'id: '.$id.',';
	echo  "name: '".$title."',";

	//if(substr( $task->pName, 0, 6 ) == "#style")
	//{
	//	$task->pName=explode(" ",$task->pName)[1];
	//}
	echo  "location: '".$task->name."',";
	//if(substr( $task->pEnd, 0, 6 ) == "#style")
	//{
	//	$task->pEnd=explode(" ",$task->pEnd)[1];
	//}	
	
	if((strtoupper($task->status) == "RESOLVED")||(strtoupper($task->status) == "CLOSED"))
	{
		$datepieces = explode("-",$task->end);
		echo 'color : "#DCDCDC",';
	}
	else
	{
		$datepieces = explode("-",$task->endo);
		echo 'color : "'.$colors[$id].'",';
	}
	echo 'startDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
	echo 'endDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
	echo '},';
}

if($project_name != 'all')
{
	require_once('core/common.php');
	require_once('core/pparse.php');
	
	$graph = new Graph($project_name);
	$mstasks = $graph->GetMilestones();
	
	$id = 0;

	$title = (string)$mstasks[0]->name;
	$mstasks[0]->name = "Project";
	echo "[";
	foreach($mstasks as $task)
	{
		EchoTaskData($title,$id,$task);
	}
	echo "]";
}
else
{
	$projects = GetProjectNames();
	echo "[";
	$id=0;
	foreach($projects as $project_name)
	{
		$settings_file = 'projects\\'.$project_name.'\\settings.php';
		require_once('core/common.php');
		require_once('core/pparse.php');
		
		$graph = new Graph($project_name);
		$mstasks = $graph->GetMilestones();

		$title = (string)$mstasks[0]->name;
		$mstasks[0]->name = "Project";
		
		foreach($mstasks as $task)
		{
			EchoTaskData($title,$id,$task);
		}
		$id = $id+1;
	}
	echo "]";
}
?>