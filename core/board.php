<?php
$milestones = explode(',',$DEFAULT_BOARD);
if(isset($board))
{
	$var ='BOARD'.$board;
	if(isset($$var))
		$milestones = explode(',',$$var);
	else
	{
		echo $var.' Is not configured';
		exit();
	}
}
?>