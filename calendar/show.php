
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="./css/bootstrap-datepicker.min.css">
		<link rel="stylesheet" type="text/css" href="./css/bootstrap-theme.min.css">
		<link rel="stylesheet" type="text/css" href="./css/bootstrap-year-calendar.css">
		<link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="./css/style.css">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="Content-Type" content="text/html; charset=utf-8" />
		<meta name="title" content="Bootstrap year calendar" />
		<meta name="description" content="The fully customizable year calendar widget, for bootstrap !" />
		<meta name="keywords" content="bootstrap, jquery, javascript, widget, calendar, year, component, library, framework, html, css, api" />
		<meta name="author" content="Paul DAVID-SIVELLE" />
		<title>Program Calendar</title>
	</head>
	<body>
<div class="panel panel-default" style="margin:10px;">
	<div class="panel-heading">Program Calendar</div>
	<div class="panel-body">
		<div id="calendar"></div>
	</div>
</div>
<div class="modal modal-fade" id="event-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">
					Event
				</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="event-index">
				<form class="form-horizontal">
					<div class="form-group">
						<label for="min-date" class="col-sm-4 control-label">Name</label>
						<div class="col-sm-7">
							<input name="event-name" type="text" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="min-date" class="col-sm-4 control-label">Location</label>
						<div class="col-sm-7">
							<input name="event-location" type="text" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="min-date" class="col-sm-4 control-label">Dates</label>
						<div class="col-sm-7">
							<div class="input-group input-daterange" data-provide="datepicker">
								<input name="event-start-date" type="text" class="form-control" value="2012-04-05">
								<span class="input-group-addon">to</span>
								<input name="event-end-date" type="text" class="form-control" value="2012-04-19">
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="save-event">
					Save
				</button>
			</div>
		</div>
	</div>
</div>
<div id="context-menu">
</div>
<style>
.event-tooltip-content:not(:last-child) {
	border-bottom:1px solid #ddd;
	padding-bottom:5px;
	margin-bottom:5px;
}

.event-tooltip-content .event-title {
	font-size:18px;
}

.event-tooltip-content .event-location {
	font-size:12px;
}
</style>
		
		<script src="./js/respond.min.js"></script>
		<script src="./js/jquery-1.10.2.min.js"></script>
		<script src="./js/bootstrap.min.js"></script>
		<script src="./js/bootstrap-datepicker.min.js"></script>
		<script src="./js/bootstrap-year-calendar.js"></script>
		<script src="./js/bootstrap-popover.js"></script>
		<script src="./js/scripts.js"></script>
		
	<script type="text/javascript" class="publish">
function editEvent(event) {
	return;
	$('#event-modal input[name="event-index"]').val(event ? event.id : '');
	$('#event-modal input[name="event-name"]').val(event ? event.name : '');
	$('#event-modal input[name="event-location"]').val(event ? event.location : '');
	$('#event-modal input[name="event-start-date"]').datepicker('update', event ? event.startDate : '');
	$('#event-modal input[name="event-end-date"]').datepicker('update', event ? event.endDate : '');
	$('#event-modal').modal();
}

function deleteEvent(event) {
	var dataSource = $('#calendar').data('calendar').getDataSource();

	for(var i in dataSource) {
		if(dataSource[i].id == event.id) {
			dataSource.splice(i, 1);
			break;
		}
	}
	
	$('#calendar').data('calendar').setDataSource(dataSource);
}

function saveEvent() {
	var event = {
		id: $('#event-modal input[name="event-index"]').val(),
		name: $('#event-modal input[name="event-name"]').val(),
		location: $('#event-modal input[name="event-location"]').val(),
		startDate: $('#event-modal input[name="event-start-date"]').datepicker('getDate'),
		endDate: $('#event-modal input[name="event-end-date"]').datepicker('getDate')
	}
	
	var dataSource = $('#calendar').data('calendar').getDataSource();

	if(event.id) {
		for(var i in dataSource) {
			if(dataSource[i].id == event.id) {
				dataSource[i].name = event.name;
				dataSource[i].location = event.location;
				dataSource[i].startDate = event.startDate;
				dataSource[i].endDate = event.endDate;
			}
		}
	}
	else
	{
		var newId = 0;
		for(var i in dataSource) {
			if(dataSource[i].id > newId) {
				newId = dataSource[i].id;
			}
		}
		
		newId++;
		event.id = newId;
	
		dataSource.push(event);
	}
	
	$('#calendar').data('calendar').setDataSource(dataSource);
	$('#event-modal').modal('hide');
}

$(function() {
	var currentYear = new Date().getFullYear();

	$('#calendar').calendar({ 
	
	disabledDays: <?php $cmd = 'list'; include 'holidays.php';?>
		,
		enableContextMenu: true,
		enableRangeSelection: true,
		/*contextMenuItems:[
			{
				text: 'Update',
				click: editEvent
			},
			{
				text: 'Delete',
				click: deleteEvent
			}
		],*/
		selectRange: function(e) {
			editEvent({ startDate: e.startDate, endDate: e.endDate });
		},
		mouseOnDay: function(e) {
			if(e.events.length > 0) {
				var content = '';
				
				for(var i in e.events) {
					content += '<div class="event-tooltip-content">'
									+ '<div class="event-name" style="color:' + e.events[i].color + '">' + e.events[i].name + '</div>'
									+ '<div class="event-location">' + e.events[i].location + '</div>'
								+ '</div>';
				}
			
				$(e.element).popover({ 
					trigger: 'manual',
					container: 'body',
					html:true,
					content: content
				});
				
				$(e.element).popover('show');
			}
		},
		mouseOutDay: function(e) {
			if(e.events.length > 0) {
				$(e.element).popover('hide');
			}
		},
		dayContextMenu: function(e) {
			$(e.element).popover('hide');
		},
		dataSource: <?php include 'events.php'; ?>
	});
	
	$('#save-event').click(function() {
		//saveEvent();
	});
});
</script>
	</body>
</html>