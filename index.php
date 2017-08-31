<?php
$user_id=$_GET['userid'];
if ( $user_id == "" )
{
	$user_id=$_POST['userid'];
}
if(isset($user_id))
{
try
{
$db = new PDO('sqlite:/var/www/html/kanboard/data/db.sqlite');
if(!isset($_POST["project"]) && !isset($_POST["from"]) && !isset($_POST["to"])) {
  print '<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
  	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  	<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
  	<script>
  	$( function() {
  	$( "#from" ).datepicker({dateFormat: \'yy-mm-dd\'});
  	$( "#to" ).datepicker({dateFormat: \'yy-mm-dd\'});
  	} );
  	</script>
	<a href="https://ms.gbmcloud.com/kanboard/">Back to Kanboard</a>
	<form name="report" action="'.$_SERVER["PHP_SELF"].'" method="post">
	<p>Select Type of Report</p>
	<select name="typereport" id="typereport">
		<option value="planned">Planned Tasks</option>
		<option value="completed">Completed Tasks</option>
		<option value="closed">Closed Tasks</option>
	</select>
	<p>Select Project</p>
	<select name="projectid" id="projectid">';
	$result = $db->query('select id,name from projects where projects.id IN ( select project_id from project_has_users where user_id = '.$user_id.') OR projects.id IN ( select project_id from project_has_groups where group_id IN (select group_id from group_has_users WHERE user_id = '.$user_id.'))');
	foreach($result as $row)
	{
		print '<option value="'.$row['id'].'">'.$row['name'].'</option>';
	}
	print '</select>
		<p>From:</p>
		<input type="date" name="from" id="from"/>
		<p>To:</p>
		<input type="date" name="to" id="to"/>
		<input type="hidden" name="userid" id="hiddenField" value="'.$user_id.'" />
		<input type="submit" value="Submit"/>
		</form>';
}
else {
	print '<a href="https://ms.gbmcloud.com/kanboard-reports/?userid='.$user_id.'">Get other report</a>';
        if ( $_POST["typereport"] == "completed" ) {
		$query='select tasks.id,title,date_started,date_due,date_completed,date_planned_start,date_planned_due,time_estimated,users.name,tasks.is_active,projects.name AS project from tasks,users,projects WHERE project_id="'.$_POST["projectid"].'" and tasks.owner_id = users.id AND project_id=projects.id AND date_due >= strftime(\'%s\',\''.$_POST["from"].'\') AND date_due <= strftime(\'%s\',\''.$_POST["to"].'\')'; 
		#$result = $db->query('select tasks.id,title,date_started,date_due,date_completed,date_planned_start,date_planned_due,time_estimated,users.name,tasks.is_active,projects.name AS project from tasks,users,projects WHERE project_id="'.$_POST["projectid"].'" and tasks.owner_id = users.id AND project_id=projects.id AND date_due >= strftime(\'%s\',\''.$_POST["from"].'\') AND date_due <= strftime(\'%s\',\''.$_POST["to"].'\')');
		$result = $db->query($query);
	}
	else {
		if (  $_POST["typereport"] == "closed" ) {
			$query= 'select tasks.id,title,date_started,date_due,date_completed,date_planned_start,date_planned_due,time_estimated,users.name,tasks.is_active,projects.name AS project from tasks,users,projects WHERE project_id="'.$_POST["projectid"].'" and tasks.owner_id = users.id AND project_id=projects.id AND date_completed >= strftime(\'%s\',\''.$_POST["from"].'\') AND date_completed <= strftime(\'%s\',\''.$_POST["to"].'\')';
			#$result = $db->query('select tasks.id,title,date_started,date_due,date_completed,date_planned_start,date_planned_due,time_estimated,users.name,tasks.is_active,projects.name AS project from tasks,users,projects WHERE project_id="'.$_POST["projectid"].'" and tasks.owner_id = users.id AND project_id=projects.id AND date_completed >= strftime(\'%s\',\''.$_POST["from"].'\') AND date_completed <= strftime(\'%s\',\''.$_POST["to"].'\')');
			$result = $db->query($query);
		}
		else {
			$query='select tasks.id,title,date_started,date_due,date_completed,date_planned_start,time_estimated,users.name,tasks.is_active,projects.name AS project from tasks,users,projects WHERE project_id="'.$_POST["projectid"].'" and tasks.owner_id = users.id AND project_id=projects.id AND date_planned_start >= strftime(\'%s\',\''.$_POST["from"].'\') AND date_planned_start <= strftime(\'%s\',\''.$_POST["to"].'\')';
			#$result = $db->query('select tasks.id,title,date_started,date_due,date_completed,date_planned_start,date_planned_due,time_estimated,users.name,tasks.is_active,projects.name AS project from tasks,users,projects WHERE project_id="'.$_POST["projectid"].'" and tasks.owner_id = users.id AND project_id=projects.id AND date_planned_start >= strftime(\'%s\',\''.$_POST["from"].'\') AND date_planned_start <= strftime(\'%s\',\''.$_POST["to"].'\')');
			$result = $db->query($query);
		}
	}
	$html='<head>';
	$html.='<link rel=\'stylesheet\' href=\'bootstrap/css/bootstrap.min.css\'>';
	$html.='<link rel=\'stylesheet\' href=\'bootstrap/css/bootstrap-theme.min.css\'';
	$html.='<script src=\'bootstrap/js/bootstrap.min.js\'></script>';
	$html.='</head>';
	$html.='<div class=\'jumbotron\'>';
	$html.='<div class=\'container\'>';
	$html.='<div class=\'row\'>';
	$html.='<h1 class=\'display-3\'>System Kanboard</h1>';
	$html.='<h2 class=\'display-3\'>Status Tasks Report</h2>';
	$html.='<p>From: '.date('d/M/Y',strtotime($_POST["from"])).' To: '.date('d/M/Y',strtotime($_POST["to"])).'</p>';
	$html.='</div><!-row->';
	$html.='</div><!-container->';
	$html.='</div><!-jumbotron->';
	$html.='<div>';
	$html.='<table class=\'table\'><thead>';
    	$html.='<tr><td>Task Number</td><td>Task Name</td><td>Project</td><td>Planned Start Date</td><td>Finish Date</td><td>Actual Finish Date</td><td>Planned Effort</td><td>Assigned to</td><td>Status</td></tr></thead><tbody>';
	foreach($result as $row)
	{
		$html.='<tr><td>'.$row['id'].'</td>';
		$html.='<td>'.$row['title'].'</td>';
		$html.='<td>'.$row['project'].'</td>';
		$html.='<td>'.date('d/M/Y',$row['date_planned_start']).'</td>';
		$html.='<td>'.date('d/M/Y',$row['date_due']).'</td>';
		if ( $row['date_completed'] ) {
			$html.='<td>'.date('d/M/Y',$row['date_due']).'</td>';
		}
		else {
			$html.='<td>Not yet!</td>';
		}
		$html.='<td>'.$row['time_estimated'].'</td>';
		$html.='<td>'.$row['name'].'</td>';
		if ( $row['is_active'] == 1 ) {
			$html.='<td>Open</td></tr>';
		}
		else {
			$html.='<td>Completed</td></tr>';
		}
	}
	$html.='</tbody></table>';
	$html.='</div>';
	echo $html;
	print '<form name="pdf" action="pdf.php" method="post">';
	print '<input type="hidden" name="html" value="'.$html.'">';
	print '<input type="submit" value="Save to PDF">';
	print '</form>';
	// close the database connection
	$db = NULL;
}
}
catch(PDOException $e)
	{
	print 'Exception : '.$e->getMessage();
	}
}
else
{
	print 'Forbidden access';
}
?>
