<?php 


define('JIRA_URL', 'http://jira.alm.mentorg.com:8080');
define('JIRA_UPDATE_ALLOWED','false');
define('QUERY','(project=NUMW) and labels in (ADM)');
define('USERS_TIMESHEET','');
define('USERS_WEEKLY_REPORT','');
define('JIRA_STRUCTURE',640);

define('PROJECT_LAYOUT',JIRA_STRUCTURE);
//define('PROJECT_LAYOUT',GAN_FILE);

define('TITLE','Azure Device Management');

$BOARD1 ='NUMW-5451';


$DEFAULT_BOARD = $BOARD1;







	
/*var_dump($a);
 
 if(isset($board))
{}
else
	$board = 1;
 
 
if($board == 1)
{
	$milestones[] = 'BSP-8406';
	$milestones[] = 'BSP-8345';
	$milestones[] = 'BSP-8299';
	$milestones[] = 'BSP-8422';	
}
else
{
	$milestones[] = 'BSP-8405';
}*/

?>
