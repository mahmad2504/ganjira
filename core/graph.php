<?php
require_once('common.php');	
require_once('project_settings.php');	
require_once('board.php');
class Graph {
	private $directory = "";
	private static $start;
	private static $end;
	function __construct($project_name,$start="2011-04-26",$end="2030-01-01")
	{
		$this->directory = "projects\\".$project_name."\\archive\\";
		self::$start = $start;
		self::$end = date('Y-M-d',strtotime('this friday', strtotime( $end)));
	}
	static function ReadDirectory($directory)
	{
		$files = array();
		$dir = opendir($directory); // open the cwd..also do an err check.
		while(false != ($file = readdir($dir))) 
		{
			if(($file != ".") and ($file != "..")) 
			{
				$file_name =  basename($file, ".xml");
				
				if( (strtotime($file_name) >= strtotime(self::$start)) && (strtotime($file_name) <= strtotime(self::$end)))
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
		else if(strtolower($day) == "saturday")
		{
			$start = strtotime($start);
			$end = strtotime($end);
			$dt = Array ();
			$count  = 0;
			for($i=$start; $i<=$end;$i=$i+86400) 
			{
				if(date("w",$i) == 6) 
					$count++;
			}
			return $count;

		}
		else if(strtolower($day) == "sunday")
		{
			$start = strtotime($start);
			$end = strtotime($end);
			$dt = Array ();
			$count  = 0;
			for($i=$start; $i<=$end;$i=$i+86400) 
			{
				if(date("w",$i) == 0) 
					$count++;
			}
			return $count;

		}
	}
	function _durationdata($task,&$obj=null)
	{
		if($obj==null)
			$obj = new Obj();
		
		//$obj->x =  date("m/d", strtotime((string)$xml->task[0]->pDate));
		$obj->date =  (string)$task->pDate;
		$obj->estimate =  (string)$task->pCduration;
		$obj->oestimate = (string)$task->pEstimateO;
		//echo $task->pCaption." "; 
		$obj->timespent = intval($task->pTimeSpent);
		$obj->timespent = $obj->timespent / (60*60*8);
		//echo  $obj->timespent.EOL;
		//echo $task->pName." ".$obj->timespent.EOL;
		if(substr($obj->estimate, 0, 6 ) == "#style")
			$obj->estimate=explode(" ",$obj->estimate)[1];
		
		if(substr($task->pStartO, 0, 6 ) == "#style")
			$task->pStartO=explode(" ",$task->pStartO)[1];
		if(strlen($task->pStartO)>0)
			$task->pStart = $task->pStartO;
		if(substr($task->pStart, 0, 6 ) == "#style")
			$task->pStart=explode(" ",$task->pStart)[1];
		
		if(substr($task->pEndO, 0, 6 ) == "#style")
			$task->pEndO=explode(" ",$task->pEndO)[1];
		if(strlen($task->pEndO) == 0)
		{
			if(substr($task->pEnd, 0, 6 ) == "#style")
				$task->pEnd=explode(" ",$task->pEnd)[1];
		}
		else
			$task->pEnd = $task->pEndO;
		
		//var_dump($task);
		//echo $task->pCaption." ".$task->pName." ".$task->pStart."  ".$task->pEnd.EOL;
		//exit();
		$obj->duration =  (strtotime($task->pEnd) - strtotime($task->pStart))/(60 * 60 * 24);
		$sats = Graph::CountDays('Saturday',$task->pStart,$task->pEnd);
		$suns = Graph::CountDays('Sunday',$task->pStart,$task->pEnd);
		$hols = Graph::CountDays('Holiday',$task->pStart,$task->pEnd);
		//echo $sats." ".$suns.EOL;
		$obj->duration = $obj->duration - ($sats + $suns + $hols) + 1;
		//echo "(".$sats." ".$suns." ".$hols.")	".$obj->duration.EOL;
		
		$obj->start = (string) $task->pStart;
		$obj->end = (string) $task->pEnd;
		
		/////////////////////////////////////////////////////////////////
		$days_spent = (strtotime(date('Y-m-d')) - strtotime($task->pStart))/(60 * 60 * 24);
		$sats = Graph::CountDays('Saturday',$task->pStart,date('Y-m-d'));
		$suns = Graph::CountDays('Sunday',$task->pStart,date('Y-m-d'));
		$hols = Graph::CountDays('Holiday',$task->pStart,date('Y-m-d'));
		$days_spent = $days_spent - ($sats + $suns + $hols) + 1;
		
		$obj->current_velocity = $obj->timespent/$days_spent;
		//echo $obj->current_velocity.EOL;
		//$obj->current_velocity = $obj->estimate/$obj->duration;
		//echo $obj->current_velocity.EOL;
		//$days_remaining =  $obj->duration - $days_spent;
		
		
		$remaining_estimate = $obj->estimate - $obj->timespent;
		$remaining_duration = $obj->duration - $days_spent;
		//echo "Rest=".$remaining_estimate." Rdur=".$remaining_duration," ".EOL;
		//exit();
		if($remaining_duration == 0)
			$remaining_duration = 1;
		$obj->required_velocity = $remaining_estimate/$remaining_duration;
		//echo "CVelocity = ".$obj->current_velocity." RVelocity=".$obj->required_velocity.EOL;
		//echo $obj->timespent." ".$days_spent.EOL;
		//echo $obj->required_velocity." ".$obj->current_velocity.EOL;
		//echo $obj->required_velocity." ".$obj->current_velocity.EOL;
		//echo $remaining_estimate." ".$remaining_duration.EOL;
		//echo $obj->current_velocity.EOL;
		//echo $sats.EOL;
		//echo $days_spent." ".$obj->timespent.EOL;
		//exit();
		//$remaning days = $obj->duration - $days_spent;
		
		//echo $obj->z.EOL;
		return $obj;
	}
	function GetDurationData($milestone)
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		//echo $milestone.EOL;
		foreach($files as $file) 
		{
			//echo $file."<br>";
			$date =  basename($file, ".xml");
			$xml = simplexml_load_file($file);
			if( strtolower($milestone) == 'project')
			{
				$xml->task[0]->pDate = $date;
				$data[]=$this->_durationdata($xml->task[0]);
			}
			else
			{
				foreach($xml->task as $task)
				{
					if(strtoupper($milestone) == strtoupper($task->pCaption))
					{				
						if(count($data) == 0)
						{
							if(strtotime($milestone->start) < strtotime($date))
							{
								$obj = $this->_durationdata($task);
								//var_dump($obj);
								//$obj = new Obj();
								
								$obj->date =  (string)$date;
								$obj->estimate = $obj->oestimate;
								$data[] = $obj;
							}
						}
						$task->pDate = $date;
						$data[]=$this->_durationdata($task);
						break;
					}
				}
			}
		}
		return json_encode($data);
	}
	function _velocitydata($task,$compl)
	{
		
		$obj = new Obj();
		$dur = $this->_durationdata($task);
		//$obj->date =  (string)$task->pDate;
		$estimate =  $dur->estimate;
		$duration = $dur->duration;
		$timespent = $dur->timespent;
		$xscale  = round($duration/50);
		
		$factor  =  $estimate/$duration;
		//echo $factor.EOL;
		if(substr( $task->pComp, 0, 6 ) == "#style")
		{
			$firstpart=explode(" ",$task->pComp)[0];
			$task->pComp = str_replace($firstpart, "", (string)$task->pComp);
		}
		$comp = $task->pComp;
		$start = (string)$dur->start;	
		$end = (string)$dur->end;
		$dur->estimate = 0;//$factor;
		$i=0;
		$count = 0;
	    while( strtotime($start ) <=  strtotime($end ))
		{
			$obj = new Obj();
			$obj->date = $start;
			//	$obj->current = $i;//$dur->estimate;
			$day = date('D', strtotime( $start));
			$hols = Graph::CountDays('Holiday',$start,$start);
			/*
			if($day == 'Fri')
				$friday = $start;
			else
				$friday = date('Y-M-d',strtotime('next friday', strtotime( $start)));
			
			foreach($compl as $cobj)
			{
				if(strtotime($friday) == strtotime($cobj->date))
				{
					$comp = $cobj->comp;
					break;
				}
				$comp = 0;
			}*/
			//echo $start.EOL;
			
			if( strtotime(date('Y-m-d')) <	 strtotime($start))
				$obj->current = 0;
			else
			{
				$obj->current = $timespent;//($comp/100)*$estimate;
				
			}
			//echo $comp.EOL;
			if(($day == 'Sun')||($day == 'Sat') || ($hols>0))
			{
				//$obj->current = $obj->current + 1;
				//$i = $obj->current;
			}
			else
			{
				$dur->estimate = $dur->estimate+$factor;
				
			}
			
			$obj->required = $dur->estimate;// $dur->duration;
			$obj->current_unmodified = $obj->current;
			if($obj->current > $obj->required)
				$obj->current = $obj->required;
			$start = date('Y-m-d', strtotime($start. ' + 1 day'));
			//$i = $i + 1;
			if($count == 0)
				$data[] =  $obj;
			$count++;
			if($count >= $xscale)
				$count = 0;
			
		}
		return $data;
	}
	function GetVelocityData($milestone)
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
			$date =  basename($file, ".xml");
			$xml = simplexml_load_file($file);
			if( strtolower($milestone) == 'project')
			{
				$obj = new Obj();
				$obj->date = $date;
				$obj->comp = $xml->task[0]->pComp;
				$compl[] = $obj;
			}
			else
			{
				foreach($xml->task as $task)
				{
					if(strtoupper($milestone) == strtoupper($task->pCaption))
					{
						$obj = new Obj();
						$obj->date = $date;
						$obj->comp = $task->pComp;
						$compl[] = $obj;
						break;
					}
				}
			}
		}
		$date =  basename($file, ".xml");
		$xml = simplexml_load_file($file);
		if( strtolower($milestone) == 'project')
		{
			$xml->task[0]->pDate = $date;
			$data=$this->_velocitydata($xml->task[0],$compl);
		}
		else
		{
			foreach($xml->task as $task)
			{
				if(strtoupper($milestone) == strtoupper($task->pCaption))
				{
					$task->pDate = $date;
					$data=$this->_velocitydata($task,$compl);
					break;
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
		global $milestones;
		$tasks = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
		}
		$xml = simplexml_load_file($file);
		$i = 0;
		foreach($xml->task as $task)
		{
			$task->pShowMilestone = 0;
			if($i == 0)
			{
				$i++;
				if($milestones[0] == 'project')
				{
					$task->pShowMilestone = 1;
					$task->pCaption = 'project';
				}
			}
		
			else
			{
				foreach($milestones as $milestone)
				{
					if($milestone == $task->pCaption)
					{
						$task->pShowMilestone = 1;
						break;
					}
				}
			}
			
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
				
				$obj->start = $task->pStart;
				if(substr( $task->pStart, 0, 6 ) == "#style")
				{
					$task->pStart=explode(" ",$task->pStart)[1];
					$obj->start = $task->pStart;
				}
				//echo  "[".$task->pName.$task->pEnd." ". $task->pEndO.EOL;
				//$task->pEndO = $task->pEnd;
				$obj->starto = $task->pStartO;
				if(strlen($task->pStartO) == 0)
				{
					$obj->starto = $task->pStart;
				}
				
				$obj->status = $task->pStatus;
				$tasks[] = $obj;
				//echo $task->pCaption.EOL;
				
			}
		}
		return $tasks;
	}
	
	static function _progressdata($task)
	{
		$obj = new Obj();
		//$obj->x =  date("m/d", strtotime((string)$xml->task[0]->pDate));
		$obj->date =  (string)$task->pDate;
		$obj->progress =  (string)$task->pComp;
		if(substr($obj->progress, 0, 6 ) == "#style")
		{
			$obj->progress=explode(" ",$obj->progress)[1];
		}
		return $obj;
	}
	
	function GetProgressData($milestone)
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		foreach($files as $file) 
		{
			$date =  basename($file, ".xml");
			$xml = simplexml_load_file($file);
			
			if( strtolower($milestone) == 'project')
			{
				$xml->task[0]->pDate = $date;
				$data[]= $this->_progressdata($xml->task[0]);
				
				/*
				$obj = new Obj();
				$obj->x =  (string)$xml->task[0]->pDate;
				
				$obj->y =  (string)$xml->task[0]->pComp;
				
				if(substr($obj->y, 0, 6 ) == "#style")
				{
					$obj->y=explode(" ",$obj->y)[1];
				}*/
			}
			else
			{
				foreach($xml->task as $task)
				{
					$task->pDate = $date;
					if(strtoupper($milestone) == strtoupper($task->pCaption))
					{
						if(count($data) == 0)
						{
							if(strtotime($milestone->start) < strtotime($date))
							{
								$obj = new Obj();
								$obj->date =  (string)$date;
								$obj->progress =  0;
								$data[] = $obj;
							}
						}
						$data[] = $this->_progressdata($task);
						
						/*$obj = new Obj();
						$obj->x =  (string)$xml->task[0]->pDate;
						$obj->y =  (string)$task->pComp;
						if(substr($obj->y, 0, 6 ) == "#style")
						{
							$obj->y=explode(" ",$obj->y)[1];
						}
						$data[]=$obj;*/
						break;
					}
				}
			}
			
			
		}
		return json_encode($data);
	}
	function _enddata($task)
	{
		$obj = new Obj();
		$obj->date =  (string)$task->pDate;
		$obj->endo =  (string)$task->pEndO;
		//var_dump($task);
		$obj->end =  (string)$task->pEnd;
		
		if(substr($obj->end, 0, 6 ) == "#style")
		{
			$obj->end=explode(" ",$obj->end)[1];
		}
		if(substr($obj->endo, 0, 6 ) == "#style")
		{
			$obj->endo=explode(" ",$obj->endo)[1];
		}
		
		return $obj;
	}
	function GetEndData($milestone)
	{
		$data = array();
		$files = $this->ReadDirectory($this->directory);
		
		foreach($files as $file) 
		{
			//echo $file.EOL;
			$xml = simplexml_load_file($file);
			if( strtolower($milestone) == 'project')
			{
				$obj = $this->_enddata($xml->task[0]);
				$data[] = $obj;
				
			}
			else
			{
				foreach($xml->task as $task)
				{
					if(strtoupper($milestone) == strtoupper($task->pCaption))
					{
						$data[] = $this->_enddata($task);
						break;
					}
				}
			}
		}
		//var_dump($data);
		return json_encode($data);
	}
}
?>