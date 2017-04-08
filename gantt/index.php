<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gantt View</title>
<script type="text/javascript" src="jsgantt.js"></script>
<link rel="stylesheet" type="text/css" href="jsgantt.css"/>
<style type="text/css">
<!--
body	{ font-size: 0.8em; font-family:tahoma, arial, verdana, Sans-serif; color: #656565;}
h3 { margin: 0px; font-size: 1em; }
h4 { margin: 0px; margin-left: 10px; font-size: 1em; }

.style1 {color: #0000FF}

.roundedCornerfg{
	padding: 5px 5px 5px 15px;
	border-radius: 5px;
	width:400px;
	font-size: 1.3em;
	font-weight: bold;
	font-style: italic;
	font-family: arial,helvetica;
	text-transform: uppercase;
	background: rgb(204,204,204);
	background: linear-gradient(to bottom, rgba(204,204,204,1) 0%,rgba(238,238,238,1) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cccccc', endColorstr='#eeeeee',GradientType=0 );
}

dt { float: left;
	clear: left;
	width: 60px;
	text-align: right;
	font-style: italic; }
dt:after { content: ":"; }
dd { margin: 0 0 0 70px;
	padding: 0 0 0.5em 0; }

.configlist dt {width: 170px;}
.configlist dd {margin: 0 0 0 180px;}
.dateconfiglist dt {width: 240px;}
.dateconfiglist dd {margin: 0 0 0 250px;}
.lang {width: 150px; min-width: 150px; max-width: 150px; vertical-align: top;}

.header {border-top: #bbbbbb 3px solid;
	border-bottom: #cfcfcf 3px solid;
	padding:5px 12px 5px 12px;
	font-size: 1.8em;
	font-weight: bold;
	font-style: italic;
	background: rgb(204,204,204);
	background: linear-gradient(to bottom, rgba(204,204,204,1) 0%,rgba(238,238,238,1) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cccccc', endColorstr='#eeeeee',GradientType=0 );
	}
.nav {border-bottom: #cfcfcf 1px solid;
	background-color: #ffffff;
	padding:2px 5px 2px 5px;
	font-size: 1.1em;
	font-weight: bold;
	font-style: italic;}
.nav ul { list-style-type:none;
	margin:0;
	padding:0;
	overflow:hidden; }
.nav li { float:left; }
.nav a { display:block; padding-right: 20px;color: #656565;}
a.footnote:link {text-decoration:none;color: red;}
a.footnote:visited {text-decoration:none;color: red;}
a.footnote:hover {text-decoration:underline;color: red;}
a.footnote:active {text-decoration:underline;color: red;}
-->
</style>

<style type="text/css" media="screen">

a:link, a:visited, a:active {
	color: #000;
	text-decoration: underline;
}
</style>

</head>
<body>

<div style="position:relative; " class="gantt" id="GanttChartDIV" ></div>

<script type="text/javascript">
<?php
   require_once('../core/pparse.php');
   $datafile = '"../projects/'.$project.'/gantt?V=1"';
?>
    var datafile = <?php echo  $datafile?>;
	var g = new JSGantt.GanttChart(document.getElementById('GanttChartDIV'), 'day' );

	if( g.getDivId() != null ) {
	
		g.setCaptionType('Caption');  // Set to Show Caption (None,Caption,Resource,Duration,Complete)
		g.setShowTaskInfoLink(1); //Show link in tool tip (0/1)
		g.setDayMajorDateDisplayFormat('dd mon');
		g.setShowStartDate('false');
		g.setScrollTo("today");
		//g.setShowRes('false');
		g.setDateTaskDisplayFormat('dd month yyyy HH:MI');
		// use the XML file parser
		JSGantt.parseXML(datafile,g)

		g.Draw();
	} else {
		alert("Error, unable to create Gantt Chart");
	}
</script>
<div style="margin:auto;text-align:center;width:100%;color:grey"><font size=1>© 2016-17 Ganjira Tools. <br>Mumtaz_Ahmad@mentor.com.</font></div>
</body>
</html>
