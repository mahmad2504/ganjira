<?php
require_once('common.php');	
require_once('project_settings.php');	

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
	/** 
	* Counts the number occurrences of a certain day of the week between a start and end date
	* The $start and $end variables must be in UTC format or you will get the wrong number 
	* of days  when crossing daylight savings time
	* @param - $day - the day of the week such as "Monday", "Tuesday"...
	* @param - $start - a UTC timestamp representing the start date
	* @param - $end - a UTC timestamp representing the end date
	* @return Number of occurences of $day between $start and $end
	*/
	static function CountDays($day, $start, $end)
	{        
		global $holidays;
		if(strtolower($day) == "holiday")
		{
			$count = 0;
			foreach($holidays as $holiday)
			{
				// Ignore this holiday if falling on sat or sunday
				$dayofweek = date('l', strtotime($holiday));
				if(($dayofweek == 'sat')||($dayofweek == 'sun'))
					continue;
				
				// Determine if holiday is falling between start and end day , inclusive 
				$holiday = strtotime($holiday);
				$start = strtotime($start);
				$end = strtotime($end);
				
				if(($holiday <= $end) && ($holiday >= $start))
					$count++;
			}
			return $count;
		}
		$start = strtotime($start);
		$end = strtotime($end);
		//get the day of the week for start and end dates (0-6)
		$w = array(date('w', $start), date('w', $end));

		//get partial week day count
		if ($w[0] < $w[1])
		{            
			$partialWeekCount = ($day >= $w[0] && $day <= $w[1]);
		}else if ($w[0] == $w[1])
		{
			$partialWeekCount = $w[0] == $day;
		}else
		{
			$partialWeekCount = ($day >= $w[0] || $day <= $w[1]);
		}

		//first count the number of complete weeks, then add 1 if $day falls in a partial week.
		return floor( ( $end-$start )/60/60/24/7) + $partialWeekCount;
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
				
				//echo $xml->task[0]->pStart."  ".$xml->task[0]->pEnd.EOL;
				$obj->z =  (strtotime($xml->task[0]->pEnd) - strtotime($xml->task[0]->pStart))/(60 * 60 * 24);
				$sats = Graph::CountDays('Saturday',$xml->task[0]->pStart,$xml->task[0]->pEnd);
				$suns = Graph::CountDays('Sunday',$xml->task[0]->pStart,$xml->task[0]->pEnd);
				$hols = Graph::CountDays('Holiday',$xml->task[0]->pStart,$xml->task[0]->pEnd);
				//echo $sats." ".$suns.EOL;
				$obj->z = $obj->z - ($sats + $suns + $hols);
				//echo $obj->z.EOL;
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
						$obj->z =  (strtotime($xml->task[0]->pEnd) - strtotime($xml->task[0]->pStart))/(60 * 60 * 24);
				
						$data[]=$obj;
						break;
					}
				}
			}
		}
		return json_encode($data);
	}
	/*
	function GetResourceUtilization()
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
			//echo $file."<br>";
			$xml = simplexml_load_file($file);
			foreach($xml->task as $task)
			{
				if(strtoupper($milestone) == strtoupper($task->pCaption))
				{
					$obj = new Obj();
					//$obj->x =  date("m/d", strtotime((string)$task->pDate));
					$obj->x =  (string)$xml->task[0]->pDate;
					$obj->y =  (string)$task->pCduration;
					$obj->z =  (strtotime($xml->task[0]->pEnd) - strtotime($xml->task[0]->pStart))/(60 * 60 * 24);
			
					$data[]=$obj;
					break;
				}
			}
		}
		return json_encode($data);
	}*/
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
				$obj->end = $task->pEnd;
				if(substr( $task->pEnd, 0, 6 ) == "#style")
				{
					$task->pEnd=explode(" ",$task->pEnd)[1];
					$obj->end = $task->pEnd;
				}
				//echo  "[".$task->pName.$task->pEnd." ". $task->pEndO.EOL;
				//$task->pEndO = $task->pEnd;
				$obj->endo = $task->pEndO;
				if(strlen($task->pEndO) == 0)
				{
					$obj->endo = $task->pEnd;
				}
				$obj->status = $task->pStatus;
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