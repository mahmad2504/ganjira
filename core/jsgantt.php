<?php
require_once('common.php');	

class JSGantt {
	private $filename;
	private $project;
	
	function __construct($filename,$project=null)
	{
		$this->filename = $filename;
		if($project == null)
		{
			if (file_exists($filename)) 
			{
				
			}
			else
				echo $filename." Does not exist\n";
		}
		else
			$this->project = $project;
	}
	function GetRav()
	{
		
		$xml = simplexml_load_file($this->filename);
		if($xml->task[0]->pRav)
			return eval("return ".$xml->task[0]->pRav.";");
		else
			return 0;
		
	}
	function GetMilestoneTasks()
	{
		$ms = array();
		$xml = simplexml_load_file($this->filename);
		//$xml->task[0]->pShowMilestone=1;
		foreach($xml->task as $task)
		{
			if($task->pShowMilestone==1)
			{
				if(substr( $task->pName, 0, 6 ) == "#style")
				{
					$firstpart=explode(" ",$task->pName)[0];
					$task->pName = str_replace($firstpart, "", (string)$task->pName);
				}
				if(substr( $task->pEnd, 0, 6 ) == "#style")
				{
					$task->pEnd=explode(" ",$task->pEnd)[1];
				}
				//echo  "[".$task->pName.$task->pEnd." ". $task->pEndO.EOL;
				//$task->pEndO = $task->pEnd;
				if(strlen($task->pEndO) == 0)
					$task->pEndO = $task->pEnd;
				
				$ms [] = $task;
			}
		}return $ms;
	}
	function  GetColor($status)
	{
		if( strtoupper($status) == "IN PROGRESS")
		{
			return "green";
		}
		else if((strtoupper($status) == "CLOSED") || (strtoupper($status) == "RESOLVED"))
			return "lightgrey";
		else 
			return "";
	}
	function IsLabelExist($task,$str)
	{
		foreach($task['labels'] as $label)
		{
			if(strtolower($label) == $str)
				return true;
		}
		return false;
	}
	function TaskJSGanttXML($xml,$task,$id,$pid)
	{
		global $milestones;
		$pShowMilestone=0;
		$pID=$id;
	
		foreach($milestones as $milestone)
		{
			if($task['key'] == $milestone)
			{
				$pShowMilestone=1;
				break;
			}
		}
		//// Summary  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$summarystyle = "";
		$length = 65-($task['level']*3);
		$pName = substr($task['summary'],0,$length);

		if($task['isparent'] == 1)
		{
			if((strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
			{
				if((strtoupper($task['status_orig']) == "CLOSED") || (strtoupper($task['status_orig']) == "RESOLVED"))
					$summarystyle = "#style=color:lightgrey ";
				else 
					$summarystyle = "";
			}
		}
		else
		{
			
			if(strtoupper($task['status_orig']) == "OPEN")
			{
				if(($task['progress'] > 0)||($task['timespent']>0))
					$summarystyle = "#style=color:green ";
				else
					$summarystyle = "";
			}
			else if((strtoupper($task['status_orig']) == "CLOSED") || (strtoupper($task['status_orig']) == "RESOLVED"))
				$summarystyle = "#style=color:lightgrey ";
			else 
				$summarystyle = "#style=color:green ";
		}
		
		if($task['issuetype'] == "Requirement")
		{
			if((strtoupper($task['status_orig']) == "CLOSED") || (strtoupper($task['status_orig']) == "RESOLVED"))
			{
				
			}
			else
			{
				$today = strtotime(date('Y-M-d'));
				$end = strtotime($task['end']);
				if( $today > $end ) // LaTE
					$summarystyle = "#style=color:red ";
				else
					$summarystyle = "#style=color:green ";
			}
			
		}
		
		$pName = $summarystyle.$pName;
		//// Start ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		$pStart = $task['start'];
		
		//// End   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pEnd = $task['end'];
		$style = "";
		if( (strtoupper($task['status']) != "CLOSED") && (strtoupper($task['status']) != "RESOLVED"))
		{
			if(strtotime($task['end_orig']) > 0)
				$style = "#style=color:green ";
			
			
			$today = strtotime(date('Y-M-d'));
			$end = strtotime($task['end']);
			if( $today > $end )
				$style = "#style=color:red ";
			
			if(strtotime($task['end_orig']) > 0)
			{
				if   ( strtotime($task['end']) > strtotime($task['end_orig']))
					$style = "#style=color:orange ";
					
			}
		}
		else
			$style = "#style=color:lightgrey ";
		$pEnd = $style.$pEnd;
		///////////////////
		$pMile = 0;
		
		
		// Progress ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pComp = round($task['progress']);
		if($task['issuetype'] == "Requirement")
		{
			if((strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
				$pComp = "Complete";
			else
				$pComp = "Waiting";
		}
		$pComp = $summarystyle.$pComp;
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pGroup = $task['isparent'];
		$pParent = $pid;
		$pNotes = WEBLINK.$task['key'];
		
		//// pOpen ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pOpen = 1;
		if( (strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
			$pOpen = 0;
		if($this->IsLabelExist($task,"gantt_show_closed"))
			$pOpen = 0;
		
		
		//// Class ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pClass = "";
		if($task['isparent'] == 0)// Non group task
		{
			$pClass = "gtaskgreen";
			if( (strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
				$pClass = "gtaskcomplete";
			else if( strtoupper($task['status']) == "IN PROGRESS")
				$pClass = "gtaskgreen";
			else
			{
				if(($task['progress'] > 0)||($task['timespent']>0))
					$pClass = "gtaskyellow";
				else
					$pClass = "gtaskopen";
			}
			
			if($task['issuetype'] == "Requirement")
			{
				if((strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
					$pClass = "gtaskcomplete";
				else
				{
					$today = strtotime(date('Y-M-d'));
					$end = strtotime($task['end']);
					if( $today > $end ) // LaTE
						$pClass = "gtaskred";
					else
						$pClass = "gtaskblue";
				}
			}
		}
		
		//// Resource ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pRes = "";
		$style = $summarystyle;
		if($task['isparent'] == 0)// Non group task
		{
			$pRes = $task['assignee'];
			if($task['issuetype'] == "Requirement")
			{
				if((strtoupper($task['status']) != "CLOSED") && (strtoupper($task['status']) != "RESOLVED"))
				{
					$today = strtotime(date('Y-M-d'));
					$end = strtotime($task['end']);
					if( $today > $end ) 
					{
						$style = "#style=color:red ";
					}
					else
						$style = "#style=color:green ";
				}
				$pRes = "Requirement";
			}
		}
		$pRes = $style.$pRes;
	
		/// Caption ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pCaption = $task['key'];
		
		// Cduration ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$pCduration = round($task['timeoriginalestimate']/(8*60*60),1);
		if($task['timeoriginalestimate_orig'] > 0)
		{
			if($task['timeoriginalestimate'] > $task['timeoriginalestimate_orig'])
				$pCduration = "(".$task['timeoriginalestimate_orig']/(8*60*60).") ".round($task['timeoriginalestimate']/(8*60*60),1);
			else if($task['timeoriginalestimate'] < $task['timeoriginalestimate_orig'])
				$pCduration = round($task['timeoriginalestimate']/(8*60*60),1)." (".$task['timeoriginalestimate_orig']/(8*60*60).")";
			else
				$pCduration = round($task['timeoriginalestimate']/(8*60*60),1);
		}
		
		$pCduration = $summarystyle.$pCduration;
		
		$pStatus = $task['status'];
		/////////////////////////////////////////////////////////////////////
		// Original Values //
		
		$pStatusO = $task['status_orig'];
		$pEndO = $task['end_orig'];
		$pStartO = $task['start_orig'];
		$pEstimateO = $task['timeoriginalestimate_orig'];
		$pTimeSpentO = $task['timespent_orig'];
		
		
		$node = $xml->addChild("task");
		$node->addChild("pShowMilestone",$pShowMilestone);
		$node->addChild("pID",$pID);
		$node->addChild("pName",$pName);
		$node->addChild("pStart",$pStart);
		$node->addChild("pEnd",$pEnd);
		$node->addChild("pMile",$pMile);
		$node->addChild("pCduration",$pCduration);
		$node->addChild("pComp",$pComp);
		
		$node->addChild("pGroup",$pGroup);
		$node->addChild("pParent",$pParent);
		
		$node->addChild("pDepend","");
		$node->addChild("pNotes",$pNotes);
		$node->addChild("pOpen",$pOpen);
		$node->addChild("pClass",$pClass);
		$node->addChild("pRes",$pRes);
		$node->addChild("pCaption",$pCaption);
		$node->addChild("pStatus",$pStatus);
		$node->addChild("pStatusO",$pStatusO);
		$node->addChild("pEndO",$pEndO);
		$node->addChild("pStartO",$pStartO);
		$node->addChild("pEstimateO",$pEstimateO);
		$node->addChild("pTimeSpentO",$pTimeSpentO);
		
		$ntid = $id+1;
		foreach($task['children'] as $ntask)
		{
			$ntid=$this->TaskJSGanttXML($xml,$ntask,$ntid,$id);
		}
		return $ntid;
	}
	function TaskJSGanttXML2($xml,$task,$id,$pid)
	{
		//for($i=0;$i<$task['level'];$i++)
		//	echo "   ";
		$goodjob = -1;
		$node = $xml->addChild("task");
		$node->addChild("pID",$id);
		$length = 65-($task['level']*3);
		//echo $task['summary']." ".$task['level']." ".$length."\n";
		//echo substr($task['summary'],0,$length);
		//if(strlen($task['summary'])> $length)
		//	$node->addChild("pName","#style=color:red ".substr($task['summary'],0,$length)."...");
		//else
		//	$node->addChild("pName",$task['summary']);
		$node->addChild("pStart",$task['start']);
		
		//echo $task['summary']." ".$task['status'];
		if( (strtoupper($task['status']) != "CLOSED") && (strtoupper($task['status']) != "RESOLVED"))
		{
			
			$today = strtotime(date('Y-M-d'));
			$end = strtotime($task['end']);
			if( $today > $end )
			{
				//echo " red".EOL;
				$node->addChild("pEnd","#style=color:red ".$task['end']);
			}
			else
				$node->addChild("pEnd",$task['end']);
				
		}
		else
			$node->addChild("pEnd",$task['end']);
		//echo EOL;
		
		$node->addChild("pEnd",$task['end']);
		$node->addChild("pMile",0);
		//if(($task['status'] == "Resolved")||($task['status'] == "Closed"))
		//	$node->addChild("pComp",100);
		//else
		
		if($task['issuetype'] == "Requirement")
		{
			//$node->addChild("pCduration"," ");
			$node->addChild("pCduration",round($task['timeoriginalestimate']/(8*60*60),1));
			if((strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
				$node->addChild("pComp","Received");
			else
				$node->addChild("pComp","#style=color:red Awaiting");
		}
		else
		{
			$node->addChild("pComp",round($task['progress']));
			$dur = round($task['timeoriginalestimate']/(8*60*60),1);
			if($dur == 0)
				$node->addChild("pCduration"," ");
			else
			{
				if($task['timeoriginalestimate_orig'] > 0)
				{
					if($task['timeoriginalestimate'] > $task['timeoriginalestimate_orig'])
					{
						$node->addChild("pCduration","(".$task['timeoriginalestimate_orig']/(8*60*60).") ".round($task['timeoriginalestimate']/(8*60*60),1));
						$goodjob = 0;
					}
					else if($task['timeoriginalestimate'] < $task['timeoriginalestimate_orig'])
					{
						$goodjob = 1;
						$node->addChild("pCduration",round($task['timeoriginalestimate']/(8*60*60),1)." (".$task['timeoriginalestimate_orig']/(8*60*60).")"  );
					}
					else
						$node->addChild("pCduration",round($task['timeoriginalestimate']/(8*60*60),1));
				}
				else
					$node->addChild("pCduration",round($task['timeoriginalestimate']/(8*60*60),1));
				
			}
		}
		
		//$node->addChild("pCduration",round($task['timeoriginalestimate']/(8*60*60),1));
		//$node->addChild("pCduration"," ");
			$node->addChild("pGroup",$task['isparent']);
		$node->addChild("pParent",$pid);
		
		$node->addChild("pDepend","");
		$node->addChild("pNotes",WEBLINK.$task['key']);
		
		$color="";
		if($task['isparent'] == 0)// Non group task
		{
			if( strtoupper($task['status']) == "IN PROGRESS")
			{
				if($task['issuetype'] == "Requirement")
				{
					//$node->addChild("pImage","..\\image\\req.png");
					$today = strtotime(date('Y-M-d'));
					$end = strtotime($task['end']);
					if( $today > $end ) // LaTE
					{
						$node->addChild("pClass","gtaskred");
					}
					else
						$node->addChild("pClass","gtaskblue");
				}
				else
					$node->addChild("pClass","gtaskgreen");
			}
			if((strtoupper($task['status']) == "CLOSED") || (strtoupper($task['status']) == "RESOLVED"))
			{
					if($goodjob == 1)
					{
						//$node->addChild("pImage","..\\image\\thumbup_grey.png");
					}
				//if($task['issuetype'] == "Requirement")
					//$node->addChild("pImage","..\\image\\req-met.png");
					$node->addChild("pClass","gtaskcomplete");
			}
			else
			{
				//if($task['issuetype'] == "Requirement")
				//	$node->addChild("pImage","..\\image\\req.png");
				
				if($task['progress'] > 0)
				{
					$node->addChild("pClass","gtaskyellow");
				}
				else
				{
					$node->addChild("pClass","gtaskopen");
				}
			}
		}
		else
		{
			$label_found = 0;
			if($task['level'] == 3)
			{
				$node->addChild("pOpen",0);
				$label_found = 1;
			}
			else
			{
				foreach($task['labels'] as $label)
				{
					if(strtolower($label) == "gantt_show_closed")
					{
						$node->addChild("pOpen",0);
						$label_found = 1;
						break;
					}
				}
			}
			if($label_found)
			{
				
			}
			else
			{
				if($task['status'] == "RESOLVED")
					$node->addChild("pOpen",0);
				else 
					$node->addChild("pOpen",1);
			}
		}
	
		
		$color = $this->GetColor($task['status']);
		
			
		if(strlen($task['summary'])> $length)
			$node->addChild("pName","#style=color:".$color." ".substr($task['summary'],0,$length)."...");
		else
			$node->addChild("pName","#style=color:".$color." ".$task['summary']);
		
		$node->addChild("pLink",WEBLINK.$task['key']);
		
		if($task['isparent'])
			$node->addChild("pRes","");
		else
		{
			if($task['issuetype'] == "Requirement")
				$node->addChild("pRes","Requirement");
			else
				$node->addChild("pRes",$task['assignee']);
		}
		$node->addChild("pCaption",$task['key']);
		$node->addChild("pStatus",$task['status_orig']);
		$node->addChild("pEnd",$task['end_orig']);
		$node->addChild("pStart",$task['start_orig']);
		$node->addChild("pEstimate",$task['timeoriginalestimate_orig']);
		$node->addChild("pTimeSpent", $task['timespent_orig']);
		//echo $id." ".$task['key']." ".$pid."\n";
		$ntid = $id+1;
		foreach($task['children'] as $ntask)
		{
			
			$ntid=$this->TaskJSGanttXML($xml,$ntask,$ntid,$id);
		}
		return $ntid;
	}
	function Save($date=null)
	{
		global $milestones;
		if($date == null)
			$date = date('Y-m-d');
		
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><project></project>', null, false);
		$xml['xmlns:xsi'] = "http://www.w3.org/2001/XMLSchema-instance";
		
		$id = 1;
		$pid = 0;
		$pShowMilestone=0;
		
		foreach($milestones as $milestone)
		{
			if(strtolower($milestone)=='project')
			{
				$pShowMilestone=1;
				break;
			}
		}
		
		$node = $xml->addChild("task");
		$node->addChild("pID",$id);
		$color = $this->GetColor($this->project->status);
		$node->addChild("pName","#style=color:".$color." ".TITLE);
		$node->addChild("pDate",$date);
		$node->addChild("pStart",$this->project->start);
		
		///////////////////////////////////////////////////////////////////////////////////////
		if( $this->project->status != "RESOLVED")
		{
			$today = strtotime(date('Y-M-d'));
			$end = strtotime($this->project->end);
			if( $today > $end )
				$node->addChild("pEnd","#style=color:red ".$this->project->end);
			else
				$node->addChild("pEnd",$this->project->end);
		}
		else
			$node->addChild("pEnd",$this->project->end);
		//////////////////////////////////////////////////////////////////////////////////////
		//$node->addChild("pEnd",$this->end);
		$node->addChild("pMile",0);
		//echo $this->progress."\n";
		$node->addChild("pComp",round($this->project->progress));
		$node->addChild("pGroup",1);
		$node->addChild("pParent",$pid);
		$node->addChild("pOpen",1);	
		$node->addChild("pDepend","");
		$node->addChild("pNotes","");
		$node->addChild("pShowMilestone",$pShowMilestone);
		$node->addChild("pCduration",round($this->project->estimate/(8*60*60)));
		
		
		
		$ntid = $id+1;
		//echo $id." "."None"." ".$pid."\n";
		foreach($this->project->structure->tree as $task)
		{
			$ntid = $this->TaskJSGanttXML($xml,$task,$ntid,$id);
		}
		$data = $xml->asXML();
		file_put_contents($this->filename, $data);
	}
}
?>