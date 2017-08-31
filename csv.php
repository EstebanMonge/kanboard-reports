<?php
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php.log");
# Set user_id via GET
$user_id=$_GET['userid'];
# If not set user_id via GET
if ( $user_id == "" ) {
	$user_id=$_POST['userid'];
}

# Determine if userid is configured
if(isset($user_id)) {
try {
# Set database path
$db1 = new PDO('sqlite:/var/www/html/kanboard/data/db.sqlite');

#Build user tags
$usertags='[';
$result = $db1->query('select id,name from users where is_active = 1');
foreach($result as $row) {
	$usertags.='{id: '.$row['id'].', name: \''.$row['name'].'\'},';
}
	$usertags=substr($usertags, 0, -1);
	$usertags.=']';

#Build project tags
$projecttags='[';
$result = $db1->query('select id,name from projects where is_active = 1');
foreach($result as $row) {
	$projecttags.='{id: '.$row['id'].', name: \''.$row['name'].'\'},';
}
	$projecttags=substr($projecttags, 0, -1);
	$projecttags.=']';

#Build tasks columns tags
$taskstags='[';
$result= $db1->query('PRAGMA table_info(tasks)');
foreach($result as $row) {
        $taskstags.='{id: '.$row['cid'].', name: \''.$row['name'].'\'},';
}
        $taskstags=substr($taskstags, 0, -1);
        $taskstags.=']';

#Build client tags
$clienttags='[';
$result= $db1->query('SELECT DISTINCT value FROM "project_has_metadata" WHERE name = "Cliente"');
foreach($result as $row) {
        $clienttags.='{id: \''.$row['value'].'\', name: \''.$row['value'].'\'},';
}
        $clienttags=substr($clienttags, 0, -1);
        $clienttags.=']';

#Build tags tags
$tagstags='[';
$result= $db1->query('SELECT DISTINCT name FROM "tags"');
foreach($result as $row) {
        $tagstags.='{id: \''.$row['id'].'\', name: \''.$row['name'].'\'},';
}
        $tagstags=substr($tagstags, 0, -1);
        $tagstags.=']';

#Determine if taskstate is set, if not print form
if(!isset($_POST["taskstate"])) {
  print '<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
	<link href="magicsuggest/magicsuggest-min.css" rel="stylesheet">
  	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  	<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
  	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="magicsuggest/magicsuggest-min.js"></script>
  	<script>
  	$( function() {
  	$( "#from" ).datepicker({dateFormat: \'yy-mm-dd\'});
  	$( "#to" ).datepicker({dateFormat: \'yy-mm-dd\'});
  	} );
  	</script>
	<script>
$(function() {
    $(\'#usertags\').magicSuggest({
        placeholder: \'Type some names\',
	allowFreeEntries: false,
	id: \'usertags\',
	name: \'usertags\',
	displayField: \'name\',
        data: '.$usertags.' 
    });
    $(\'#projecttags\').magicSuggest({
        placeholder: \'Type some project names\',
	allowFreeEntries: false,
	id: \'projecttags\',
	name: \'projecttags\',
	displayField: \'name\',
        data: '.$projecttags.' 
    });
    $(\'#taskstags\').magicSuggest({
        placeholder: \'Type some column names\',
	allowFreeEntries: false,
	id: \'taskstags\',
	name: \'taskstags\',
	displayField: \'name\',
	maxSelection: 100,
	valueField: \'name\',
        data: '.$taskstags.' 
    });
    $(\'#tagstags\').magicSuggest({
        placeholder: \'Type some tags\',
	allowFreeEntries: false,
	id: \'tagstags\',
	name: \'tagstags\',
	displayField: \'name\',
	maxSelection: 100,
	valueField: \'id\',
        data: '.$tagstags.' 
    });
    $(\'#clienttags\').magicSuggest({
        placeholder: \'Type some client names\',
        allowFreeEntries: false,
        id: \'clienttags\',
        name: \'clienttags\',
        displayField: \'name\',
        maxSelection: 100,
        valueField: \'name\',
        data: '.$clienttags.'
    });
});
	</script>
	<a href="https://ms.gbmcloud.com/kanboard/">Back to Kanboard</a>
	<form name="report" action="'.$_SERVER["PHP_SELF"].'" method="post">
	<div class="form-group">
	<label for="typereport">Type of Report: </label>
        <select class="form-control" name="typereport" id="typereport">
		<option value="tasks">All tasks</option>
		<option value="subtasks">All Subtasks</option>
                <option value="activities">Activities of projects</option>
        </select>
	<label for="clienttags">Name of client (empty for all): </label>
	<div id="clienttags"></div>
	<label for="tagstags">Tags (empty for none): </label>
	<div id="tagstags"></div>
        <label for="timerange">Date due range</label>
        <select class="form-control" name="timerange" id="timerange">
                <option value="all">All time</option>
                <option value="lweek">Last week</option>
                <option value="nweek">Next week</option>
                <option value="ltweek">Last two week</option>
                <option value="ntweek">Next two week</option>
                <option value="lmonth">Last month</option>
                <option value="nmonth">Next month</option>
        </select>
	<label for="usertags">Name of the users (empty for all): </label>
	<div id="usertags"></div>
	<label for="projecttags">Name of the projects (empty for all): </label>
	<div id="projecttags"></div>
	<label for="taskstags">Select columns: </label>
	<div id="taskstags"></div>
	<label for="taskstate">Task State</label>
	<select class="form-control" name="taskstate" id="taskstate">
		<option value="openclosed">Closed and Open tasks</option>
		<option value="closed">Closed tasks</option>
		<option value="open">Open tasks</option>
	</select>
	<input type="hidden" name="userid" id="hiddenField" value="'.$user_id.'" />
	<input type="submit" value="Submit"/>
	</div>
	</form>';
}

#If taskstate is set, print report 
else {
	$task_tags=$_POST["taskstags"];
	$client_tags=$_POST["clienttags"];
	$time_range=$_POST["timerange"];
	switch ($_POST["typereport"] ) {
        //if ( $_POST["typereport"] == "tasks" ) {
		case "tasks": 
#Build the select
		print '<a href="https://ms.gbmcloud.com/kanboard-reports/csv.php?userid='.$user_id.'">Get other report</a>';
		$select='select ';
		$from=' FROM tasks';
		$where=' WHERE 1 ';
		if(isset($_POST["taskstags"]))
                {
	                foreach($_POST["taskstags"] as $value) {
				switch ($value) {
					case "owner_id":
						$select.="USR.name AS owner_id,";
						$left.=' LEFT JOIN users USR ON tasks.owner_id = USR.id ';
						break;
					case "creator_id":
						$select.="CRT.name AS creator_id,";
						$left.=' LEFT JOIN users CRT ON tasks.creator_id = CRT.id ';
						break;
					case "project_id":
						array_push($task_tags,"contract_id");
						$select.="projects.name AS project_id, META.value as contract_id, ";
						$where.="AND tasks.project_id=projects.id AND projects.is_active = 1 ";
						$from.=',projects';
						$left.=' LEFT JOIN project_has_metadata META ON projects.id = META.project_id AND META.name = "Contrato" ';
						break;
					case "column_id":
						$select.="columns.title AS column_id,";
						$where.="AND tasks.column_id=columns.id ";
						$from.=',columns';
						break;
					case "category_id":
						$select.="project_has_categories.name AS category_id,";
						$left.=' LEFT JOIN project_has_categories ON tasks.category_id=project_has_categories.id';
						break;
					case "swimlane_id":
						$select.="swimlanes.name AS swimlane_id,";
						$left.=' LEFT JOIN swimlanes ON tasks.swimlane_id=swimlanes.id ';
						break;
					default:
						$select.='tasks.'.$value.',';
				}
			}
		}
		$select=substr($select, 0, -1);
                switch ($time_range) {
                        case "lweek":
                                $where.=" AND datetime(date_due,'unixepoch') BETWEEN datetime('now', '-6 days') AND datetime('now', 'localtime') ";
                                break;
                        case "nweek":
                                $where.=" AND datetime(date_due,'unixepoch') BETWEEN datetime('now', 'localtime') AND datetime('now', '+6 days') ";
                                break;
                        case "ltweek":
                                $where.=" AND datetime(date_due,'unixepoch') BETWEEN datetime('now', '-13 days') AND datetime('now', 'localtime') ";
                                break;
                        case "ntweek":
                                $where.=" AND datetime(date_due,'unixepoch') BETWEEN datetime('now', 'localtime') AND datetime('now', '+13 days') ";
                                break;
                        case "lmonth":
                                $where.=" AND datetime(date_due,'unixepoch') BETWEEN datetime('now', '-30 days') AND datetime('now', 'localtime') ";
                                break;
                        case "nmonth":
                                $where.=" AND datetime(date_due,'unixepoch') BETWEEN datetime('now', 'localtime') AND datetime('now', '+30 days') ";
                                break;
                        default:
                                $error="Oops";
                }
		$sql1=$select.$from.$left.$where;
		$sql1_projects="";
		switch ($_POST["taskstate"]) {
			case "closed":
				$sql1.=" AND tasks.is_active = 0";
				break;
			case "open":
				$sql1.=" AND tasks.is_active = 1";
				break;
		}
		if(isset($_POST["projecttags"])) {
			$sql1 .=" AND tasks.project_id IN ( ";
			foreach($_POST["projecttags"] as $value) {
			   $sql1.= $value.", ";
			   $sql1_projects.= $value.", ";
			}
			$sql1=substr($sql1, 0, -2);
			$sql1 .=" )";
			$sql1_projects=substr($sql1_projects, 0, -2);
		}	
		if(isset($_POST["usertags"])) {
			$sql1 .=" AND tasks.owner_id IN ( ";
			foreach($_POST["usertags"] as $value) {
				$sql1.= $value.", ";
			}
			$sql1=substr($sql1, 0, -2);
			$sql1 .=" )";
		}
		$project_metadata = "SELECT project_id,name FROM project_has_metadata WHERE project_id IN (".$sql1_projects.") AND name = 'Contrato'";
		$result = $db1->query($sql1);
#Build the report
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
	$html.='</div><!-row->';
	$html.='</div><!-container->';
	$html.='</div><!-jumbotron->';
	$html.='<div>';
	$html.='<table class=\'table\'><thead><tr>';
        foreach($task_tags as $value) {
		if ( $value == "is_active") {
			$html.='<td>State</td>';
		}
		else {
			$html.='<td>'.ucfirst(str_replace(" id","",str_replace("_"," ",$value))).'</td>';
		}
	}
	$html.='</tr></thead><tbody>';
	foreach($result as $row) {
		$html.='<tr>';
	        foreach($task_tags as $value) {
			if (strpos($value, 'date') !== false) {
				$html.='<td>'.date('d/M/Y',$row["$value"]).'</td>';
			}
			else {
				if (strpos($value, 'is_active') !== false) {
					if ( $row["$value"] == 0 ) {
        	                        	$html.='<td>Closed</td>';
					}
					else {
        	                        	$html.='<td>Open</td>';
					}
	                        }
				else {
       	        			$html.='<td>'.$row["$value"].'</td>';
				}
			}
        	}	
		$html.='</tr>';
	}
	$html.='</tbody></table>';
	$html.='</div>';
	echo $html;
	break;
	case "activities":
		header("Location: excel.php?clients=".urlencode(serialize($client_tags)));
		die();
	break;
}
#Close the database connection
	$db1 = NULL;
	}
}
	catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
	}
}
else {
	print 'Forbidden access';
}
?>
