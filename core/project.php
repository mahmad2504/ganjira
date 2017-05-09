<?php
require_once('common.php');	
require_once('project_settings.php');

class Project {
	private $filter;
	private $structure;
	private $estimate=0;
	private $timespent=0;
	private $progress=0;
	private $start=0;
	private $status = "RESOLVED";
	private $end=0;
	private $oestimate;
	
	public function __get($name) 
  	{
		switch($name)
		{
			case 'structure':
				return $this->structure;
			case 'estimate':
				return $this->estimate;
			case 'timespent':
				return $this->timespent;
			case 'progress':
				return $this->progress;
			case 'start':
				return $this->start;
			case 'status':
				return $this->status;
			case 'end':
				return $this->end;
			case 'oestimate':
				return $this->oestimate;
			default:
				trace("error","cannot access property ".$name);
			
		}
	}
	function datesort($a, $b) 
	{
			$dateTimestamp1 = strtotime($a);
			$dateTimestamp2 = strtotime($b);
			return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
	}
	function ComputeStatus(&$task)
	{
		if( !isset($task['status_orig']) )
		  $task['status_orig'] = $task['status'];
	
		$status = array();
		if($task['isparent'] == 0)
		{
			if( (strtoupper($task['status']) == "REOPENED") || (strtoupper($task['status']) == "OPEN") || (strtoupper($task['status']) == "IN PROGRESS") || (strtoupper($task['status']) == "BLOCKED"))
				return "IN PROGRESS";
			else
				return "RESOLVED";
		}
		$task['status']="RESOLVED";
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$status = $this->ComputeStatus($ntask);
			if($status == "IN PROGRESS")
				$task['status']="IN PROGRESS";
		}
		//echo $task['status'].EOL;
		return $task['status'];
	}
	function CompudeEndDate($start,$dur)
	{
		global $holidays;
		if($dur == 0)
			return $start;
		while($dur)
		{
			$dayofweek = date('l', strtotime($start));
			$dayofweek = strtolower(substr($dayofweek,0,3));
			
			$holday = 0;
			foreach($holidays as $holiday)
			{
				if( strtotime($start) == strtotime($holiday))
				{
					$holday = 1;
					break;
				}
			}
			
			if(($dayofweek == "sat") || ($dayofweek == "sun") || ($holday==1) ) 
			{
				
			}
			else
				$dur--;
			$start = date('Y-m-d', strtotime($start. ' + 1 day'));
		}
		$end = date('Y-m-d', strtotime($start. ' - 1 day'));

		return $end;
	}
	function ComputeEnd(&$task)
	{
		$ends =  array();
		if( !isset($task['end_orig']) )
		$task['end_orig'] = $task['end'];
		if($task['isparent'] == 0)
		{
			//echo "ddd".$task['end']."\n";
			if(strlen($task['end'])>0)
				$end = $task['end'];
			else
			{
				$dur = round($task['timeoriginalestimate']/(8*60*60));
				//echo $task['key']." ".$dur."\n";
				$end = $this->CompudeEndDate($task['start'],$dur);
			}
			if(($task['status'] == "Resolved")||($task['status'] == "Closed"))
			{
				if (count($task['worklogs'])>0)
				{
					$end = $task['worklogs'][count($task['worklogs'])-1]->started;
				}
			}
			$task['end'] = $end;
			return $task['end'];
		}
		if(($task['status'] == "Resolved")||($task['status'] == "Closed"))
		{}
		else
		$ends[] = $task['end'];
		
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$ends[] = $this->ComputeEnd($ntask);
		}
		usort($ends,array( $this, 'datesort' ));
		if( strlen($task['end_orig'])  > 0)
		{
			if   ( strtotime($ends[count($ends)-1]) > strtotime($task['end_orig']))
				$task['end'] = $ends[count($ends)-1];
		}
		else
			$task['end'] = $ends[count($ends)-1];
			
	
		//echo $task['key'].' '.$task['end'].EOL;
		
		//echo $task['key']."-->";
		//foreach($starts as $start)
		//	echo $start." ";
		//echo "\n";
		//$task['start'] = $total;
		//echo $task['key']." ".$total."\n";
		//return $total;
		return $task['end'];//$ends[0];
	}
	function ComputeStart(&$task)
	{
		$starts =  array();
		if( !isset($task['start_orig']) )
		$task['start_orig'] = $task['start'];
		if($task['isparent'] == 0)
		{
			
			if (count($task['worklogs'])>0)
			{
				$task['start'] = $task['worklogs'][0]->started;
				//echo "-->".$task['key']." ".$task['start'].EOL; 
			}
			else
			{
				if( strlen($task['start']) == 0)
				{
					
					$task['start'] = date("Y-m-d");
					//echo "-->-->".$task['key']." ".$task['start'].EOL; 
				}
			}
			//echo "-->-->-->".$task['key']." ".$task['start'].EOL; 
			return $task['start'];
		}
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$st= $this->ComputeStart($ntask);
			//echo $ntask['key']." ".$ntask['start'].EOL;
			$starts[] = $st;
			
		}
		usort($starts,array( $this, 'datesort' ));
		$task['start'] = $starts[0];
		//echo $task['key']." ".$task['start'].EOL;
		//echo $task['key']."-->";
		//foreach($starts as $start)
		//	echo $start." ";
		//echo "\n";
		//$task['start'] = $total;
		//echo $task['key']." ".$total."\n";
		//return $total;
		return $task['start'];
	}
	function AdjustStartEndDatesNoWorkedTasks(&$task,$start)
	{
		if($task['isparent'] == 0)
		{
			if (count($task['worklogs']) == 0)
			{
				if(($task['status'] == "Resolved")||($task['status'] == "Closed"))
				{
					$task['start'] = $start;
					$task['end'] = $start;
				}
			}	
		}
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$this->AdjustStartEndDatesNoWorkedTasks($ntask,$task['start']);
			//$starts[] = $ntask['start'];
			//$ends[] = $ntask['end'];
		}
		
	}
	function AdjustStartEndDates(&$task,$last_end=null)
	{
		if($task['isparent'] == 0)
		{
			if (($last_end != null) && ($task['timespent']  == 0) && ($task['status'] != "Resolved") && ($task['status'] !="Closed"))
			{
				if(strtotime($last_end) > strtotime($task['start']))
				{
					//echo $task['key']." ".$task['start']." ";
					$task['start'] = $last_end;
					$dur = round($task['timeoriginalestimate']/(8*60*60));
					if($dur == 0)
						$dur = 1;
					
					$end = $this->CompudeEndDate($task['start'],$dur);
					$task['end'] =  $end;

					//echo  $last_end." ".$task['status']." ".$task['end']."<br>";
				}
			}
			if(strtotime($last_end) > strtotime($task['end']. ' + 1 day'))
				return $last_end;
			return date('Y-m-d', strtotime($task['end']. ' + 1 day'));
		}
		$last_end = null;
		$starts = array();
		$ends = array();
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$last_end  = $this->AdjustStartEndDates($ntask,$last_end);
			$starts[] = $ntask['start'];
			$ends[] = $ntask['end'];
			//echo $ntask['end'].EOL;
		}
		usort($starts,array( $this, 'datesort' ));
		usort($ends,array( $this, 'datesort' ));
		$task['start'] = $starts[0];
		if( strlen($task['end_orig'])  > 0)
		{
			if   ( strtotime($ends[count($ends)-1]) > strtotime($task['end_orig']))
				$task['end'] = $ends[count($ends)-1];
		}
		else
			$task['end'] = $ends[count($ends)-1];
		
		//$task['end'] = $ends[count($ends)-1];
		//echo $task['summary']." ".$task['end'].EOL;
		return null;
	}
	function ComputeLevel(&$task,$level)
	{
		$task['level'] = $level;
		if($task['isparent'] == 0)
			return;
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$this->ComputeLevel($ntask,$level+1);
		}
	}
	
	function ComputeEstimate(&$task)
	{
		$total = 0;
		if( !isset($task['timeoriginalestimate_orig']) )
		$task['timeoriginalestimate_orig'] = $task['timeoriginalestimate'];
		
		if($task['isparent'] == 0)
		{
			if($task['timeoriginalestimate'] > 0)
			{
				$task['noestimate'] =  0;
			}
			else 
			{
				$task['timeoriginalestimate'] = $task['timespent'];
				$task['noestimate'] =  1;
				//$task['timeoriginalestimate']= 0;
			}
			
			if(($task['status'] == "Resolved")||($task['status'] == "Closed"))
			{
				//echo "E ".$task['key']." ".$task['timespent'].EOL;
				if($task['timespent'] == 0)
				{
					// Assume estimated time is spent and engineer did not bother to update time logs 
					$task['timespent']  = $task['timeoriginalestimate'];
					//echo $task['key']." ".$task['timeoriginalestimate']/(60*60*8).EOL;
					$task['worklogs'] = array();
					$obj = new Obj();
					$resolve_date = Jira::GetResolveDate($task['key']);
					$est_in_days = $task['timeoriginalestimate']/(60*60*8)-1;
					if($est_in_days <= 0)
						$est_in_days = 1;
					$obj->started  = date('Y-m-d',strtotime($resolve_date." -".$est_in_days." days"));
					//echo "M ".$task['key']." ".$resolve_date." ".$est_in_days." ".$obj->started.EOL;
					$task['worklogs'][0] = $obj;
					
					$obj = new Obj();
					$obj->started = $resolve_date;
					//echo "M ".$task['key']." ".$obj->started.EOL;
					
					$task['worklogs'][1] = $obj;
					
					//$task['noestimate'] =  1;
				}
				$task['timeoriginalestimate'] = $task['timespent'];
			}
			//echo $task['key']."    ".$task['timeoriginalestimate']."<br>";
			return $task['timeoriginalestimate'];
		}
			
		//echo $task['key']." ".$task['isparent']."\n";
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$est = intval($this->ComputeEstimate($ntask));
			$total += $est;
			//echo $ntask['key']." ".$est/(8*60*60)."\n";
			
		}
		if($task['timeoriginalestimate'] > 0)
		{
			if($total > $task['timeoriginalestimate'])
				$task['timeoriginalestimate'] = $total;			
			if(($task['status'] == "Resolved")||($task['status'] == "Closed"))
		        $task['timeoriginalestimate'] = $total;
		}
		else
			$task['timeoriginalestimate'] = $total;		
		//echo $task['key']."    ".$task['timeoriginalestimate']."<br>";
		//echo $task['key']." ".$total."\n";
		return $task['timeoriginalestimate'];
	}
	function ComputeTimeSpent(&$task)
	{
		$total = 0;
		if( !isset($task['timespent_orig']) )
		$task['timespent_orig'] = $task['timespent'];
		
		if($task['isparent'] == 0)
		{
			if( ($task['status'] == "Resolved") || ($task['status'] == "Closed"))
				return $task['timeoriginalestimate'];
			
			return $task['timespent']>$task['timeoriginalestimate']?$task['timeoriginalestimate']:$task['timespent'];
		}
			
		//echo $task['key']." ".$task['isparent']."\n";
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$total += intval($this->ComputeTimeSpent($ntask));
		}
		
		$task['timespent'] = $total > $task['timeoriginalestimate']? $task['timeoriginalestimate']:$total;
		$task['acc_timespent'] =  $total;
		//$task['timespent'] = $total;
		return $task['timespent'];
	}

	function ComputeProgress(&$task)
	{
		if($task['isparent'] == 0)
		{
			if($task['noestimate'] ==  0)
				$task['progress'] = ($task['timespent']/$task['timeoriginalestimate'])*100;
			else
			{
				$task['timeoriginalestimate'] = $task['timespent'];
				$task['progress'] = "-100";
			}
			//echo $task['key']." p=".$task['progress']."\n";
			if( strtoupper($task['status']) == "IN PROGRESS")
				return 1;
			return 0;
		}	
		//echo $task['key']." ".$task['isparent']."\n";
		$status = 0;
		for($i=0;$i<count($task['children']);$i++)
		{
			$ntask = &$task['children'][$i];
			$status += $this->ComputeProgress($ntask);
		}
		
		if($task['timeoriginalestimate'] > 0)
		{
			$task['progress'] = ($task['timespent']/$task['timeoriginalestimate'])*100;
			//if( ($status > 0) && ($task['progress'] == 100))
			//{
			//	$task['progress'] = 99;
			//}
		}
		else
			$task['progress'] = 0;
	}
	function ComputeOriginalEstimate(&$task)
	{
		if($task['isparent'] == 0)
		{
			return $task['timeoriginalestimate'];
		}
		if(strlen($task['timeoriginalestimate'])>0)
			return $task['timeoriginalestimate'];
		$est = 0;
		for($i=0;$i<count($task['children']);$i++)
		{
			
			$ntask = &$task['children'][$i];
			$e = $this->ComputeOriginalEstimate($ntask);
			//echo $ntask['key']." ".$e.EOL;
			$est = $est + $e;
		}
		$task['oestimate'] = $est/(60*60*8);
		return $est;
	}
	function __construct($structure,$filter,$date=null)
	{
		$this->filter = $filter;
		for($i=0;$i<count($structure->tasks);$i++)
		{
			$task = &$structure->tasks[$i];
			
			if(isset($filter->tasks->$task['key']))
			{
				foreach($filter->tasks->$task['key'] as $property => $value)  
					$task[$property] = $value;
			}
			else
				trace('error',$task['key']." data not found in filter (".$this->filter->query.")");
		}
		$starts = array();
		$ends = array();
		$status = "RESOLVED";
        if($date != null)
		{
			// Remove tasks that are newer than $date
			for($i=0;$i<count($structure->tasks);$i++)
			{
				$task = &$structure->tasks[$i];
			
				if(strtotime($task['created'])>strtotime($date))
				{
					$structure->tasks[$i]['timeoriginalestimate'] = 0;
					$structure->tasks[$i]['timespent'] = 0;
					$structure->tasks[$i]['worklogs'] = array();
				}
				for($j=0;$j< count($structure->tasks[$i]['worklogs']); $j++)
				{
					if(strtotime($structure->tasks[$i]['worklogs'][$j]->started)>strtotime($date))
					{
						$structure->tasks[$i]['worklogs'][$j]->started = 0;
						$structure->tasks[$i]['timespent'] = $structure->tasks[$i]['timespent'] - $structure->tasks[$i]['worklogs'][$j]->timespent;
					}
				}
			}
		}
		$est = 0;
		for($i=0;$i<count($structure->tree);$i++)
		{
			
			$task = &$structure->tree[$i];
			$e = $this->ComputeOriginalEstimate($task);
			$task['oestimate'] = $e/(60*60*8);
			//echo $task['key']." ".$e/(60*60*8).EOL;
			$est = $est + $e;
		}
		

		
		for($i=0;$i<count($structure->tree);$i++)
		{
			
			$task = &$structure->tree[$i];
			$this->ComputeLevel($task,1);
			$this->ComputeEstimate($task);
			$this->ComputeTimeSpent($task);
			$this->ComputeProgress($task);
			$this->ComputeStart($task);
			$this->ComputeEnd($task);
			//$this->ComputeEnd($task);
			$sta = $this->ComputeStatus($task);
			if($this->ComputeStatus($task) ==  "IN PROGRESS")
				$status = "IN PROGRESS";
			
			$this->AdjustStartEndDates($task);
			$this->AdjustStartEndDatesNoWorkedTasks($task,$task['start']);
			$this->AdjustStartEndDates($task);
			
			//echo$task['key']." ".$task['start'].EOL;
			$starts[] = $task['start'];
			$ends[] = $task['end'];
			
			//echo $task['summary']."  ".$task['start']." ".$task['end'].EOL;
			//$ends[] = $this->ComputeEnd($task);

			//echo $task['acc_timespent']/(8*60*60)."<br>";
			//if($task['timeoriginalestimate'] > 0)
			//	$task['progress'] = ($task['timespent']/$task['timeoriginalestimate'])*100;
			//else
			//	$task['progress'] = 0;
			//echo $task['key']." est=".c/(8*60*60)." ts=".$task['timespent']/(8*60*60)."\n";//." p=".$task['progress']."\n";
			//echo $task['timeoriginalestimate']/(8*60*60)."<br>";
			$this->estimate += $task['timeoriginalestimate'];
			if( ($task['status'] == "Resolved") || ($task['status'] == "Closed"))
				$this->timespent += $task['timeoriginalestimate'];
			else
				$this->timespent += $task['timespent']>$task['timeoriginalestimate']?$task['timeoriginalestimate']:$task['timespent'];
			
			//echo $task['key']." st=".$task['start']." est=".$task['timeoriginalestimate']." end=".$task['end']." ts=".$task['timespent']." p=".$task['progress']."\n";
		}
		$this->status = $status;
		if($this->estimate > 0)
			$this->progress = ($this->timespent/$this->estimate)* 100;
		else
			$this->progress = 0 ;
		
		usort($starts,array( $this, 'datesort' ));
		usort($ends,array( $this, 'datesort' ));
		$this->start = $starts[0];
		//echo $this->start.EOL;
		$this->end = $ends[count($ends)-1];
		$this->oestimate = $est/(60*60*8);
		//echo $this->end.EOL;
		$this->structure = $structure;
		//echo "Start=".$this->start." est=".$this->estimate." ts=".$this->timespent." p=".$this->progress." end=".$this->end."\n";
		//echo $task['key']." ".$task['oestimate'].EOL;
	}
}
?>