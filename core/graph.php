<?php
require_once('common.php');	

class Graph {
	private $directory = "";
	function __construct($project_name)
	{
		$this->directory = "projects\\".$project_name."\\archive\\";
	}
	static function ReadDirectory($directory)
	{
		$files = array();
		$dir = opendir($directory); // open the cwd..also do an err check.
		while(false != ($file = readdir($dir))) 
		{
			if(($file != ".") and ($file != "..")) 
			{
				$files[] = $directory.$file; // put in array.
			}  
		}
		natsort($files); // sort.
		return $files;
	}
	function GetDurationData($milestone)
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
			//echo $file."<br>";
			$xml = simplexml_load_file($file);
			if( strtolower($milestone) == 'project')
			{
				$obj = new Obj();
				//$obj->x =  date("m/d", strtotime((string)$xml->task[0]->pDate));
				$obj->x =  (string)$xml->task[0]->pDate;
				$obj->y =  (string)$xml->task[0]->pCduration;
				$data[]=$obj;
			}
			else
			{
				foreach($xml->task as $task)
				{
					if(strtoupper($milestone) == strtoupper($task->pCaption))
					{
						$obj = new Obj();
						//$obj->x =  date("m/d", strtotime((string)$task->pDate));
						$obj->x =  (string)$xml->task[0]->pDate;
						$obj->y =  (string)$task->pCduration;
						$data[]=$obj;
						break;
					}
				}
			}
		}
		return json_encode($data);
	}

	function GetMilestones()
	{
		$tasks = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
		}
		$xml = simplexml_load_file($file);
		foreach($xml->task as $task)
		{
			if($task->pShowMilestone==1)
			{
				$obj = new Obj();
				$obj->key = $task->pCaption;
				$obj->name = $task->pName;
				if(substr( $obj->name, 0, 6 ) == "#style")
				{
					$firstpart=explode(" ",$obj->name)[0];
					$obj->name = str_replace($firstpart, "", (string)$obj->name);
				}
				$tasks[] = $obj;
				//echo $task->pCaption.EOL;
				
			}
		}
		return $tasks;
	}
	function GetProgressData($milestone)
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
			//echo $file."<br>";
			$xml = simplexml_load_file($file);
			
			if( strtolower($milestone) == 'project')
			{
				$obj = new Obj();
				//$obj->x =  date("m/d", strtotime((string)$xml->task[0]->pDate));
				$obj->x =  (string)$xml->task[0]->pDate;
				
				$obj->y =  (string)$xml->task[0]->pComp;
				
				if(substr($obj->y, 0, 6 ) == "#style")
				{
					$obj->y=explode(" ",$obj->y)[1];
				}
				//echo $obj->x ." ".$obj->y.EOL;
				$data[]=$obj;
			}
			else
			{
				foreach($xml->task as $task)
				{
					if(strtoupper($milestone) == strtoupper($task->pCaption))
					{
						$obj = new Obj();
						//$obj->x =  date("m/d", strtotime((string)$task->pDate));

						$obj->x =  (string)$xml->task[0]->pDate;
						//echo $task->pDate."\n";
						$obj->y =  (string)$task->pComp;
						if(substr($obj->y, 0, 6 ) == "#style")
						{
							$obj->y=explode(" ",$obj->y)[1];
						}
						$data[]=$obj;
						break;
					}
				}
			}
			
			
		}
		return json_encode($data);
	}
}
?>