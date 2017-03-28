<?php

if( isset($argv))
{
	define("EOL","\r\n"); 
	foreach ( $argv as $value)
	{
		$env="cmd";
		$params=explode("=",$value);
		if(count($params)==2)
			${$params[0]} = $params[1];
	}
}
else
{
	$env="web";
	define("EOL","<br>");
	$url = parse_url($_SERVER["REQUEST_URI"]);
	//var_dump($url);
	//$cmd = basename($url['path']);
	
	$path =  explode("/",$url['path']);	
	//echo $url['query'];
	if(isset($url['query']))
	{
		$params_list=explode("&",$url['query']);
		//int_r($params);
		foreach($params_list as $p)
		{
			//print_r($p)."<br>";
			$params=explode("=",$p);
			for($i=0;$i<count($params);$i=$i+2)
			{
				//echo $key." ".$value."<br>";
				$$params[$i]=$params[$i+1];
			}
		}
	}
	
}
function flushout()
{
	global $env;
	if($env=="web")
     {
      	flush();
      	ob_flush();
     }
	
}
?>