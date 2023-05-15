<?php

include("inc/globals.php");

$ReminderID = strtolower($_POST['ReminderID']);
$ReminderTitle = $_POST['ReminderTitle'];
$ReminderInterval = $_POST['ReminderInterval'];
$ReminderGroup = $_POST['ReminderGroup'];
$ReminderShiftable = $_POST['ReminderShiftable'];

$IDexists = false;

if (empty($ReminderID) || empty($ReminderTitle) || empty($ReminderInterval) ) {
	$headercontent = "location:edit.php?status=InputEmpty";
	header($headercontent);
	die();
}
else{
	if ( preg_match("/^[A-Za-z0-9-]+$/",$ReminderID) && is_numeric($ReminderInterval) ){ //If ID only contains letters/numbers/hyphens + interval is a number
		$ReminderInterval = $ReminderInterval * 1; //Avoid numbers with leading zeros
		$ReminderShiftableBool = false;
		
		if ($ReminderShiftable == "true") {
			$ReminderShiftableBool = true;
		}
		
		$entry = [$ReminderID,$ReminderTitle,$ReminderInterval,$ReminderGroup,$ReminderShiftableBool];

		$json = file_get_contents($reminderFile);
		$reminders = json_decode($json);
		
		foreach($reminders as &$reminder) {
			if($reminder[0] == $ReminderID) {
				$IDexists = true;
				break; //if there will be only one then break out of loop
			}
		}
		
		if($IDexists == false){ //ID does not already exist
			array_push($reminders, $entry);

			$fp = fopen($reminderFile, 'w');
			fwrite($fp, json_encode($reminders));
			fclose($fp);
			
			$headercontent = "location:edit.php?status=success";
			header($headercontent);
			die();			
		}
		else{ //ID already in use
			$headercontent = "location:edit.php?status=IDexists";
			header($headercontent);
			die();
		}

		
	}
	else {
		$headercontent = "location:edit.php?status=InputWrongFormat";
		header($headercontent);
		die();
	}
}




?>