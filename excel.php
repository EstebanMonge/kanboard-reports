<?php
ini_set("log_errors", 1);
ini_set("error_liog", "php.log");
#print_r($_POST['tagstags']);
# Set user_id via GET
try {
# Set database path
$client_tags = unserialize(urldecode($_GET['clients']));
$client_value = "";
foreach($client_tags as $tags) {
	$client_value.="'".$tags."',";
}
$client_value=substr($client_value, 0, -1);
$task_tags=['project_id','title','owner_id','column_id'];
$db1 = new PDO('sqlite:/var/www/html/kanboard/data/db.sqlite');
		$sql1="SELECT project_id FROM project_has_metadata WHERE value in (".$client_value.")";
		$result = $db1->query($sql1);
		$project_ids="";
		foreach($result as $row) {
			$project_ids.=$row["project_id"].",";
		}
		$project_ids=substr($project_ids, 0, -1);
		$sql1='select tasks.id,projects.name AS project_id, META.value as contract_id, tasks.title,USR.name AS owner_id,columns.title AS column_id FROM tasks,projects,columns LEFT JOIN project_has_metadata META ON projects.id = META.project_id AND META.name = "Contrato" LEFT JOIN users USR ON tasks.owner_id = USR.id WHERE 1 AND tasks.project_id=projects.id AND projects.is_active = 1 AND tasks.column_id=columns.id AND tasks.is_active = 1 AND tasks.project_id IN ( '.$project_ids.' )';
		$result = $db1->query($sql1);
		$sql1 = 'select count(*) AS count,projects.name,tasks.project_id FROM tasks,projects,columns LEFT JOIN project_has_metadata META ON projects.id = META.project_id AND META.name = "Contrato" LEFT JOIN users USR ON tasks.owner_id = USR.id WHERE 1 AND tasks.project_id=projects.id AND projects.is_active = 1 AND tasks.column_id=columns.id AND tasks.is_active = 1 AND tasks.project_id IN ( '.$project_ids.' ) and columns.title != "Backlog" group by tasks.project_id';
		$sql3 = 'select count(*) AS count,projects.name,tasks.project_id FROM tasks,projects,columns LEFT JOIN project_has_metadata META ON projects.id = META.project_id AND META.name = "Contrato" LEFT JOIN users USR ON tasks.owner_id = USR.id WHERE 1 AND tasks.project_id=projects.id AND projects.is_active = 1 AND tasks.column_id=columns.id AND tasks.is_active = 1 AND tasks.project_id IN ( '.$project_ids.' ) AND columns.title != "Backlog" AND columns.title != "Done" GROUP BY tasks.project_id';
		$total_tasks = $db1->query($sql1);
#Build the report
	require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Esteban Monge")
                                                         ->setLastModifiedBy("Esteban Monge")
                                                         ->setTitle("Office 2007 XLSX Test Document")
                                                         ->setSubject("Office 2007 XLSX Test Document")
                                                         ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                                                         ->setKeywords("office 2007 openxml php")
                                                         ->setCategory("Test result file");
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
	$objPHPExcel->getActiveSheet()->setTitle('Project General Information');
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B1', 'Project General Information')
            ->setCellValue('B3', 'Project')
            ->setCellValue('C3', 'Progress')
            ->setCellValue('D3', 'Semaphore')
            ->setCellValue('E3', 'Comments');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Century Goth');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(30);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
	$objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('E3')->getFont()->setBold(true);
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(1);
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
	$objPHPExcel->setActiveSheetIndex(1)
           ->setCellValue('B1', 'Tasks Details')
           ->setCellValue('G3', 'Tags')
           ->setCellValue('F3', 'Last Comment');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Century Goth');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(30);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
	$objPHPExcel->getActiveSheet()->setTitle('Tasks Details');
	$objPHPExcel->getActiveSheet()->getStyle('G3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('F3')->getFont()->setBold(true);
	$header=array("");
	$rows=array("");
	$rownumber=4;
        foreach($task_tags as $value) {
		if ( $value == "is_active") {
			array_push($header,"State");
		}
		else {
			array_push($header,ucfirst(str_replace(" id","",str_replace("_"," ",$value))));
		}
	}
	$objPHPExcel->getActiveSheet()->fromArray($header, null, 'A3');
	foreach($result as $row) {
	        foreach($task_tags as $value) {
			if (strpos($value, 'date') !== false) {
				array_push($rows,date('d/M/Y',$row["$value"]));
			}
			else {
				if (strpos($value, 'is_active') !== false) {
					if ( $row["$value"] == 0 ) {
						array_push($rows,"Closed");
					}
					else {
						array_push($rows,"Open");
					}
	                        }
				else {
					array_push($rows,$row["$value"]);
				}
			}
        	}
		$sql1='SELECT comment FROM "comments" WHERE task_id = '.$row["id"].' ORDER BY ID DESC LIMIT 1';
		$comment = $db1->query($sql1);
		if ($comment === FALSE ) {
			array_push($rows," ");
		}
		$are_comments=0;
		foreach($comment as $commentary) {
			 array_push($rows,$commentary["comment"]);
			$are_comments=1;
		}
		if ( $are_comments == 0 ) {
			 array_push($rows," ");
		}
		$sql2='SELECT name FROM tags WHERE id IN (select tag_id from task_has_tags where task_id = '.$row["id"].')';
		$all_tasks_tags = $db1->query($sql2);
		$are_tasks_tags=0;
		foreach($all_tasks_tags as $tasks_tags) {
			 array_push($rows,$tasks_tags["name"]);
			 $are_tasks_tags=1;
		}
		if ( $are_tasks_tags == 0 ) {
			 array_push($rows," ");
		}
		$objPHPExcel->getActiveSheet()->fromArray($rows, null, 'A'.$rownumber);	
		$rows=array("");
		$rownumber++;
	}
	$objPHPExcel->setActiveSheetIndex(0);
	$rows=array("");
	$rownumber=4;
	foreach($total_tasks as $row){
		array_push($rows,$row["name"]);
		$objPHPExcel->getActiveSheet()->fromArray($rows, null, 'A'.$rownumber);	
		$pending_tasks = $db1->query($sql3);
		foreach($pending_tasks as $fila){
			if ($row["project_id"] == $fila["project_id"]){
				$percentaje=($fila["count"]*100)/$row["count"];
				$objPHPExcel->getActiveSheet()
	        		    ->setCellValue('C'.$rownumber, $percentaje);
				switch (true) {
					case ($percentaje < 25):
					$objPHPExcel->getActiveSheet()->getStyle('D'.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F93939');
					break;
					case ($percentaje < 50):
					$objPHPExcel->getActiveSheet()->getStyle('D'.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF99');
					break;
					case ($percentaje < 75):
					$objPHPExcel->getActiveSheet()->getStyle('D'.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FF9933');
					break;
					default:
					$objPHPExcel->getActiveSheet()->getStyle('D'.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00CC66');
				}
			}
		}
		$rows=array("");
		$rownumber++;
	}
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="01simple.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');

#Close the database connection
	$db1 = NULL;
}
	catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
	}
?>
