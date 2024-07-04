<?php

include("inc/globals.php");

header('Access-Control-Allow-Origin: *'); 

$DateCurrent = date('D, d M Y H:i:s', time());
$DateReference = "";

$json = file_get_contents($reminderFile);
$reminders = json_decode($json, true);

$ReminderTitle = "";
$StatusMessage = "";

include_once 'inc/lang/'.$languageCode.'.php';

if(isset($_GET['start']) || isset($_GET['restart'])) {
	
	$StatusType = "";
	
	if(isset($_GET['start'])) {
		$StatusType = "start";
		$ReminderID = $_GET['start'];
	}
	elseif(isset($_GET['restart'])) {
		$StatusType = "restart";
		$ReminderID = $_GET['restart'];		
		
	}
	
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	$PathReminderAttributes = "./" . $DirTimestamps . "/" . $ReminderID . "_attributes.txt";
	
	if(file_exists($PathReminderTimestamp)) {
		unlink($PathReminderTimestamp); // Delete Timestamp File
	}
	
	$TimestampFile = fopen($PathReminderTimestamp, 'w+'); // // Create (or clear existing) Timestamp file
	flock($TimestampFile, LOCK_EX); //Lock file to avoid other processes writing to it simlutanously

	fwrite($TimestampFile, $DateCurrent); //Insert Current Date

	flock($TimestampFile, LOCK_UN); //Unlock file for further access
	fclose($TimestampFile); //Close Timestamp File
	
	if(file_exists($PathReminderGuid)) {
		unlink($PathReminderGuid); // Delete Guid File
	}
	$guid = rand(); //Generate Random Number

	$GuidFile = fopen($PathReminderGuid, 'w+'); // // Create (or clear existing) Guid file
	flock($GuidFile, LOCK_EX); //Lock file to avoid other processes writing to it simlutanously

	fwrite($GuidFile, $guid); //Insert Guid

	flock($GuidFile, LOCK_UN); //Unlock file for further access
	fclose($GuidFile); //Close Guid File
	
	if(file_exists($PathReminderAttributes)) {
		unlink($PathReminderAttributes); // Delete Attribute File
	}
	$attributes = $AttributesDefault;

	$AttributeFile = fopen($PathReminderAttributes, 'w+'); // // Create (or clear existing) Attribute file
	flock($AttributeFile, LOCK_EX); //Lock file to avoid other processes writing to it simlutanously

	fwrite($AttributeFile, $attributes); //Insert Attribute

	flock($AttributeFile, LOCK_UN); //Unlock file for further access
	fclose($AttributeFile); //Close Attribute File	
	
	header("Location: dashboard.php?status=success-" . $StatusType . "&" . "id=" . $ReminderID);
	die();
}
elseif(isset($_GET['stop'])) {
	$StatusType = "stop";
	$ReminderID = $_GET['stop'];
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	$PathReminderAttributes = "./" . $DirTimestamps . "/" . $ReminderID . "_attributes.txt";
	
	if(file_exists($PathReminderTimestamp)) {
		unlink($PathReminderTimestamp); // Delete Timestamp File
	}
	
	if(file_exists($PathReminderGuid)) {
		unlink($PathReminderGuid); // Delete Guid File
	}
	
	if(file_exists($PathReminderAttributes)) {
		unlink($PathReminderAttributes); // Delete Attribute File
	}
	
	header("Location: dashboard.php?status=success-" . $StatusType . "&" . "id=" . $ReminderID);
	die();
}
elseif( isset($_GET['attribute']) && isset($_GET['id']) ) { //Change attributes of reminder
	$StatusType = "attribute";
	$ReminderID = $_GET['id'];
	$attributes = $_GET['attribute'];
	$PathReminderAttributes = "./" . $DirTimestamps . "/" . $ReminderID . "_attributes.txt";
	
	$AttributeFile = fopen($PathReminderAttributes, 'w+'); // // Create (or clear existing) Attribute file
	flock($AttributeFile, LOCK_EX); //Lock file to avoid other processes writing to it simlutanously

	fwrite($AttributeFile, $attributes); //Insert Attribute

	flock($AttributeFile, LOCK_UN); //Unlock file for further access
	fclose($AttributeFile); //Close Attribute File
	header("Location: dashboard.php?status=success-" . $StatusType . "&" . "id=" . $ReminderID);
	die();
}
elseif( isset($_GET['data']) && $_GET['data'] == "all" ) { //Get data as combined json
	$dataArray = array();

	$fp = fopen($reminderFile, 'r'); // Open file in read-only mode
	flock($fp, LOCK_EX); //Lock file to avoid other processes writing to it simlutanously

	$json = stream_get_contents($fp);
	$reminders = json_decode($json, true);

	flock($fp, LOCK_UN); //Unlock file for further access
	fclose($fp);
	
	for ($row = 0; $row < count($reminders); $row++) {
		$ReminderData = array();
		
		$ReminderID = $reminders[$row][0];
		$ReminderTitle = $reminders[$row][1];
		$ReminderInterval = $reminders[$row][2];
		$ReminderGroup = $reminders[$row][3];
		$ReminderShiftable = $reminders[$row][4];
		
		$timestamp = "";
		$guid = "";
		$attributes = "";

		$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
		$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
		$PathReminderAttributes = "./" . $DirTimestamps . "/" . $ReminderID . "_attributes.txt";
		
		if (file_exists($PathReminderTimestamp) && file_exists($PathReminderGuid)) { //Check if Timestamp File exists
			$timestamp = file_get_contents($PathReminderTimestamp);
			$guid = file_get_contents($PathReminderGuid);
			$attributes = file_get_contents($PathReminderAttributes);
		}
		
		$ReminderData['ReminderID'] = $ReminderID;
		$ReminderData['ReminderTitle'] = $ReminderTitle;
		$ReminderData['ReminderInterval'] = $ReminderInterval;
		$ReminderData['ReminderGroup'] = $ReminderGroup;
		$ReminderData['ReminderShiftable'] = $ReminderShiftable;
		
		$ReminderData['timestamp'] = $timestamp;
		$ReminderData['guid'] = $guid;
		$ReminderData['attributes'] = $attributes;
		
		array_push($dataArray, $ReminderData);

	}
	
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode( $dataArray );
	exit;
}

$output = "";

if(isset($_GET['status']) && isset($_GET['id'])) {
	$ReminderID = $_GET['id'];
	
	for ($row = 0; $row < count($reminders); $row++) {
		if($reminders[$row][0] == $ReminderID){
			$ReminderTitle = $reminders[$row][1];
			break;
		}
	}
	
	
	if ($_GET['status'] == "success-start"){
		$StatusMessage = "<strong>" . $ReminderTitle . "</strong>" . " " .$language['statusMessageStarted'].".";
	}
	elseif ($_GET['status'] == "success-restart"){
		$StatusMessage = "<strong>" . $ReminderTitle . "</strong>" . " " .$language['statusMessageRestarted'].".";
	}
	elseif ($_GET['status'] == "success-stop"){
		$StatusMessage = "<strong>" . $ReminderTitle . "</strong>" . " " .$language['statusMessageStopped'].".";
	}
	elseif ($_GET['status'] == "success-attribute"){
		$StatusMessage = "<strong>" . $ReminderTitle . "</strong>" . " " .$language['statusMessageAttributeAdded'].".";
	}
	
	$output .= "<div class=\"message_status\">" . $StatusMessage . "</div>";
}

$output .= "<table id=\"sortTable\">";

$output .= "<tr>";
$output .= "<th>".$language['tableTitle']."<br><i>ID (".$language['tableGroup'].")</i></th>";
$output .= "<th>Update<br><i>".$language['tableUpdateLast']."</i></th>";
$output .= "<th> </th>"; //Interval
$output .= "<th>Update<br><i>".$language['tableUpdateNext']."</i></th>";
$output .= "<th> </th>"; //Restart Button
$output .= "</tr>";


for ($row = 0; $row < count($reminders); $row++) {
	$ReminderID = $reminders[$row][0];
	$ReminderGroup = $reminders[$row][3];
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	
	$ReminderTitle = $reminders[$row][1];
	$ReminderInterval = $reminders[$row][2];
	$ReminderShiftable = $reminders[$row][4];

	$guid = ""; //Reset Guid
	
	$output .= "<tr>";

	$output .= "<td>" .$ReminderTitle. "<br><i>".$ReminderID." (".$ReminderGroup.")</i></td>"; //Titel
	
	if (file_exists($PathReminderTimestamp) && file_exists($PathReminderGuid)) { //Check if Timestamp File exists
		$DateReference = file_get_contents($PathReminderTimestamp); //Get Date from Timestamp File
		
		$dteStart = new DateTime(date('Y-m-d', strtotime($DateReference) ));
		$dteEnd = new DateTime(date('Y-m-d', strtotime($DateCurrent) ));
		$DateDifference = $dteStart->diff($dteEnd);
		$DateDifferenceDays = $DateDifference->format('%a');
		
		if ($DateDifferenceDays == 0) {
			$output .= "<td>" . date("d.m.Y", strtotime($DateReference)) . "<br><i>".$language['tableToday']."</i></td>"; //Letztes Update
		}
		elseif ($DateDifferenceDays == 1) {
			$output .= "<td>" . date("d.m.Y", strtotime($DateReference)) . "<br><i>".$language['tableYesterday']."</i></td>"; //Letztes Update
		}
		elseif ($DateDifferenceDays == 2) {
			$output .= "<td>" . date("d.m.Y", strtotime($DateReference)) . "<br><i>".$language['tableDayBeforeYesterday']."</i></td>"; //Letztes Update
		}
		else{
			$output .= "<td>" . date("d.m.Y", strtotime($DateReference)) . "<br><i>".$language['tableDateDifferenceBeforePrefix']." ".$DateDifferenceDays." ".$language['tableDifferenceDays']."</i></td>"; //Letztes Update
		}
		
		if ($ReminderShiftable) {
			$output .= "<td>&rarr;<br><i>" .$ReminderInterval. " ".$language['tableDays']."</i><br><span>".$language['tableShiftable']."</span></td>"; //Intervall
		}
		else {
			$output .= "<td>&rarr;<br><i>" .$ReminderInterval. " ".$language['tableDays']."</i><br><span>".$language['tableNonShiftable']."</span></td>"; //Intervall
		}
		
		$DateNext = new DateTime(date('Y-m-d', strtotime($DateReference) ));
		$DateIntervalString = $ReminderInterval . " days";
		date_add($DateNext, date_interval_create_from_date_string($DateIntervalString));
		
		$dteStart = new DateTime(date('Y-m-d', strtotime($DateCurrent) ));
		$DateDifference = $dteStart->diff($DateNext);
		$DateRemainingDays = $DateDifference->format('%a');
		
		if ($DateRemainingDays == 0) {
			$output .= "<td>".date_format($DateNext, 'd.m.Y')."<br><i>".$language['tableToday']."</i></td>"; //Nächstes Update
		}
		elseif ($DateRemainingDays == 1) {
			$output .= "<td>".date_format($DateNext, 'd.m.Y')."<br><i>".$language['tableTomorrow']."</i></td>"; //Nächstes Update
		}
		elseif ($DateRemainingDays == 2) {
			$output .= "<td>".date_format($DateNext, 'd.m.Y')."<br><i>".$language['tableDayAfterTomorrow']."</i></td>"; //Nächstes Update
		}
		else{
			$output .= "<td>".date_format($DateNext, 'd.m.Y')."<br><i>in ".$DateRemainingDays." ".$language['tableDifferenceDays']."</i></td>"; //Nächstes Update
		}
		
		if ($DateDifferenceDays != 0) { //If not today
			$output .= "<td><a class=\"restart\" href=\""."dashboard.php?restart=".$ReminderID."\" target=\"_self\">".$language['tableRestart']."</a>"; //Restart Link
			$output .= "<a class=\"stop\" href=\""."dashboard.php?stop=".$ReminderID."\" target=\"_self\">".$language['tableStop']."</a></td>"; //Stop Link
		}
		else{ //If today
			$output .= "<td><a class=\"stop\" href=\""."dashboard.php?stop=".$ReminderID."\" target=\"_self\">".$language['tableStop']."</a></td>"; //Stop Link
		}
		
		
		
	}
	else { //If Timestamp File does NOT exist
		$output .= "<td>-</td>"; //Letztes Update
		
		if ($ReminderShiftable) {
			$output .= "<td>&rarr;<br><i>" .$ReminderInterval. " ".$language['tableDays']."</i><br><span>".$language['tableShiftable']."</span></td>"; //Intervall
		}
		else {
			$output .= "<td>&rarr;<br><i>" .$ReminderInterval. " ".$language['tableDays']."</i><br><span>".$language['tableNonShiftable']."</span></td>"; //Intervall
		}
		
		
		$output .= "<td>-</td>"; //Nächstes Update
		$output .= "<td><a class=\"start\" href=\""."dashboard.php?start=".$ReminderID."\" target=\"_self\">".$language['tableStart']."</a></td>"; //Start Link
	}	
	$output .= "</tr>";
}


 
$output .= "</table>";

?>

<!doctype html>

<html lang="<?php echo $languageCode; ?>">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex"/>
  <meta name="robots" content="nofollow"/>

  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="viewport" content="initial-scale=1,minimum-scale=1,maximum-scale=1">
  <meta name="apple-mobile-web-app-title"content="Dashboard">

  <title>Dashboard</title>
  <meta name="description" content="Dashboard">
  <meta name="author" content="Dashboard">
  
  <link rel="stylesheet" href="inc/styles.css">

</head>

<body>

<input type="text" id="searchbar" onkeyup="filterElements()" placeholder="<?php echo $language['search']; ?>">

<?php echo $output; ?>

<a class="footer_button" href="edit.php" target="_self"><?php echo $language['editList']; ?></a>

<script>
function sortTable() {
  var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById("sortTable");
  switching = true;
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[0];
      y = rows[i + 1].getElementsByTagName("TD")[0];
      //check if the two rows should switch place:
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
        //if so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}

sortTable();
</script>

<script>
function filterElements() {
  // Declare variables
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchbar");
  filter = input.value.toUpperCase();
  table = document.getElementById("sortTable");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }
}

var startFocusInput = document.getElementById('searchbar');
startFocusInput.focus();
startFocusInput.select();
</script>

</body>
</html>