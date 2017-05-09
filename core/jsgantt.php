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
		$pID=$id;
	
	
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
			//echo $task['key']." ".$task['end']." ".$task['end_orig'].EOL;
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
		
		if(isset($task['oestimate']))
			$pEstimateO = $task['oestimate'];
		else
			$pEstimateO =0;
		
		//echo $task['key']." ".$pEstimateO.EOL;
		
		$pTimeSpentO = $task['timespent_orig'];
		
		$pEstimate = $task['timeoriginalestimate'];
		$pTimeSpent = $task['timespent'];
		$isparent = $task['isparent'];
		$pLevel = $task['level'];
			
		$node = $xml->addChild("task");
		//$node->addChild("pShowMilestone",$pShowMilestone);
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
		$node->addChild("pEstimate",$pEstimate);
		$node->addChild("pTimeSpent",$pTimeSpent);
		
		
		$node->addChild("pStatusO",$pStatusO);
		$node->addChild("pEndO",$pEndO);
		//echo $pEnd." ".$pEndO.EOL;
		$node->addChild("pStartO",$pStartO);
		$node->addChild("pEstimateO",$pEstimateO);
		$node->addChild("pTimeSpentO",$pTimeSpentO);
		$node->addChild("isparent",$isparent);
		$node->addChild("pLevel",$pLevel);
		
		$ntid = $id+1;
		foreach($task['children'] as $ntask)
		{
			$ntid=$this->TaskJSGanttXML($xml,$ntask,$ntid,$id);
		}
		return $ntid;
	}
	
	function Save($date=null)
	{
		if($date == null)
			$date = date('Y-m-d');
		
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><project></project>', null, false);
		$xml['xmlns:xsi'] = "http://www.w3.org/2001/XMLSchema-instance";
		
		$id = 1;
		$pid = 0;
	
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
		//$node->addChild("pShowMilestone",$pShowMilestone);
		$node->addChild("pCduration",round($this->project->estimate/(8*60*60)));
		
		$node->addChild("pEstimate",$this->project->estimate);
		$node->addChild("pTimeSpent",$this->project->timespent);
		$node->addChild("pEstimateO",$this->project->oestimate);
		$node->addChild("pLevel",0);
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