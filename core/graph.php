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