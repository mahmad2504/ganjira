<?php

$colors = array(
"#87CEFA",
"#6A5ACD",
"#EE82EE",
"#87CEEB",
"#B0E0E6",
"#FFC0CB",
);

if($project_name != 'all')
{
	require_once('core/common.php');
	require_once('core/pparse.php');
	
	$dir = opendir('projects');
	$id = 0;
	while(false != ($file = readdir($dir)))
    {
		if(($file != ".") and ($file != "..") and ($file != 'all')) 
		{
			if( strtoupper($project_name) == strtoupper($file))
				break;
			else
				$id = $id + 1;
		} 
		
	}
	
	
	$jsgantt = new JSGantt(GANTT_DATA_FILE);
	$mstasks = $jsgantt->GetMilestoneTasks();
	//$id=0;
	
	//if(substr( $mstasks[0]->pName, 0, 6 ) == "#style")
	//{
	//	$firstpart=explode(" ",$mstasks[0]->pName)[0];
	//	$mstasks[0]->pName = str_replace($firstpart, "", (string)$mstasks[0]->pName);
	//}
	$title = (string)$mstasks[0]->pName;
	$mstasks[0]->pName = "Project";
	echo "[";
	foreach($mstasks as $task)
	{

		echo '{';
		echo  'id: '.$id.',';
		echo  "name: '".$title."',";
		
		//if(substr( $task->pName, 0, 6 ) == "#style")
		//{
		//	$task->pName=explode(" ",$task->pName)[1];
		//}
		echo  "location: '".$task->pName."',";
		//if(substr( $task->pEnd, 0, 6 ) == "#style")
		//{
		//	$task->pEnd=explode(" ",$task->pEnd)[1];
		//}
		$datepieces = explode("-",$task->pEnd);
		//echo $task->pStatus.EOL;
		if((strtoupper($task->pStatus) == "RESOLVED")||(strtoupper($task->pStatus) == "CLOSED"))
		{
			$datepieces = explode("-",$task->pEnd);
			echo 'color : "#DCDCDC",';
		}
		else
		{
			$datepieces = explode("-",$task->pEndO);
			echo 'color : "'.$colors[$id].'",';
		}
		echo 'startDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
		echo 'endDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
		echo '},';
	}
	echo "]";
}
else
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
	echo "[";
	$id=0;
	foreach($projects as $project_name)
	{
		$settings_file = 'projects\\'.$project_name.'\\settings.php';
		$gantt_file = 'projects\\'.$project_name.'\\gantt';
		require_once('core/common.php');
		require_once('core/pparse.php');
		
		$jsgantt = new JSGantt($gantt_file);
		$mstasks = $jsgantt->GetMilestoneTasks();
	
	
		//if(substr( $mstasks[0]->pName, 0, 6 ) == "#style")
		//{
		//	$firstpart=explode(" ",$mstasks[0]->pName)[0];
		//	$mstasks[0]->pName = str_replace($firstpart, "", (string)$mstasks[0]->pName);
		//}
		$title = (string)$mstasks[0]->pName;
		$mstasks[0]->pName = "Project";
		
		foreach($mstasks as $task)
		{

			echo '{';
			echo  'id: '.$id.',';
			echo  "name: '".$title."',";
		
			//if(substr( $task->pName, 0, 6 ) == "#style")
			//{
			//	$task->pName=explode(" ",$task->pName)[1];
			//}
			echo  "location: '".$task->pName."',";
			//if(substr( $task->pEnd, 0, 6 ) == "#style")
			//{
			//	$task->pEnd=explode(" ",$task->pEnd)[1];
			//}	
			
			if((strtoupper($task->pStatus) == "RESOLVED")||(strtoupper($task->pStatus) == "CLOSED"))
			{
				$datepieces = explode("-",$task->pEnd);
				echo 'color : "#DCDCDC",';
			}
			else
			{
				$datepieces = explode("-",$task->pEndO);
				echo 'color : "'.$colors[$id].'",';
			}
			echo 'startDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
			echo 'endDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
			echo '},';
		}
		$id = $id+1;
	}
	echo "]";
}
/*echo 
			"[{
				id: 0,
				name: 'Google I/O',
				location: 'San Francisco, CA',
				startDate: new Date(currentYear, 4, 28),
				endDate: new Date(currentYear, 4, 29),
			},
			{
				id: 1,
				name: 'Microsoft Convergence',
				location: 'New Orleans, LA',
				startDate: new Date(currentYear, 2, 16),
				endDate: new Date(currentYear, 2, 19)
			},
			{
				id: 2,
				name: 'Microsoft Build Developer Conference',
				location: 'San Francisco, CA',
				startDate: new Date(currentYear, 3, 29),
				endDate: new Date(currentYear, 4, 1)
			},
			{
				id: 3,
				name: 'Apple Special Event',
				location: 'San Francisco, CA',
				startDate: new Date(currentYear, 8, 1),
				endDate: new Date(currentYear, 8, 1)
			},
			{
				id: 4,
				name: 'Apple Keynote',
				location: 'San Francisco, CA',
				startDate: new Date(currentYear, 8, 9),
				endDate: new Date(currentYear, 8, 9)
			},
			{
				id: 5,
				name: 'Chrome Developer Summit',
				location: 'Mountain View, CA',
				startDate: new Date(currentYear, 10, 17),
				endDate: new Date(currentYear, 10, 18)
			},
			{
				id: 6,
				name: 'F8 2015',
				location: 'San Francisco, CA',
				startDate: new Date(currentYear, 2, 25),
				endDate: new Date(currentYear, 2, 26)
			},
			{
				id: 7,
				name: 'Yahoo Mobile Developer Conference',
				location: 'New York',
				startDate: new Date(currentYear, 7, 25),
				endDate: new Date(currentYear, 7, 26),
				color: 'blue'
			},
			{
				id: 8,
				name: 'Android Developer Conference',
				location: 'Santa Clara, CA',
				startDate: new Date(currentYear, 10, 17),
				endDate: new Date(currentYear, 10, 17)
			},
			{
				id: 9,
				name: 'Unified BSP',
				location: 'Serial Bsp driver',
				startDate: new Date(currentYear, 10, 17),
				endDate: new Date(currentYear, 10, 17)
			},]";
*/



?>