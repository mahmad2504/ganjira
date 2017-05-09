<?php 


define('JIRA_URL', 'http://jira.alm.mentorg.com:8080');
define('JIRA_UPDATE_ALLOWED','false');
define('QUERY','(project=BSP or project=OS) and labels in (DMR,UNI_BSP)');
define('USERS_TIMESHEET','');
define('USERS_WEEKLY_REPORT','');
define('JIRA_STRUCTURE',620);

define('PROJECT_LAYOUT',JIRA_STRUCTURE);
//define('PROJECT_LAYOUT',GAN_FILE);

define('TITLE','Unified BSP');

$BOARD1 ='BSP-8406,BSP-8345,BSP-8299,BSP-8422';
$BOARD2 = 'BSP-8405,OS-7755,BSP-8447,BSP-8448';

$DEFAULT_BOARD = $BOARD2;







	
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
