<?php

//echo __DIR__;
//echo $project_name;
if( isset($project_name))
	require_once('core/pparse.php');
else
	require_once('../core/pparse.php');

$holidays = array();

$holidays[] = '2017-04-25';
$holidays[] = '2017-05-01';


if($cmd='list')
{
	echo '[';
	foreach($holidays as $holiday)
	{
		$d = strtotime($holiday);
		$year = date('Y',$d);
		$month = date('m',$d)-1;
		$day = date('j',$d);
		echo 'new Date('.$year.",".$month.",".$day."),";
	}
	echo ']';
}
/*
echo 'disabledDays: [
            new Date(2017,0,3),
			new Date(2017,0,3),
            new Date(currentYear,1,3),
            new Date(currentYear,1,8),
            new Date(currentYear,1,9),
            new Date(currentYear,1,10),
            new Date(currentYear,1,11),
            new Date(currentYear,1,13),
            new Date(currentYear,1,14),
            new Date(currentYear,1,15)
        ]';
		*/



?>