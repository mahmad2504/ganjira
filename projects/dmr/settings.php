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


//$milestones[] = 'project';
$milestones[] = 'BSP-8406';
$milestones[] = 'BSP-8345';
$milestones[] = 'BSP-8357';
$milestones[] = 'BSP-8221';
$milestones[] = 'BSP-8306';
$milestones[] = 'BSP-8299';


?>
