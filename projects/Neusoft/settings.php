<?php 

define('JIRA_URL', 'http://jira.alm.mentorg.com:8080');
define('JIRA_UPDATE_ALLOWED','false');
define('QUERY','(project=MEH or project=HL) and labels in (intel_automotive_hyp_gptask, intel_automotive_hyp)');
define('USERS_TIMESHEET','');
define('USERS_WEEKLY_REPORT','');
define('JIRA_STRUCTURE',607);
define('PROJECT_LAYOUT',JIRA_STRUCTURE);
//define('PROJECT_LAYOUT',GAN_FILE);


define('TITLE','Neusoft');

$BOARD1 ='project,MEH-2773,MEH-2772,MEH-2996,MEH-2959,MEH-2944';
$DEFAULT_BOARD = $BOARD1;

?>
