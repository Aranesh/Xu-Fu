<?php
include("../../data/dbconnect.php");
include("../functions.php");

// =================== REMOVE OLD SPAMPROTECT ENTRIES ===================

$protectdb = mysqli_query($dbcon, "SELECT * FROM Spamprotect LIMIT 150000");
$numspam = mysqli_num_rows($protectdb);
$countspams = "0";

if ($numspam > "20000"){
  while ($countspams < $numspam){
    $protectentry = mysqli_fetch_object($protectdb);

    $difference = strtotime(date('Y-m-d H:i:s'))-strtotime($protectentry->Entrytime);
    $thisspamid = $protectentry->id;

    if ($difference > "43200"){
        mysqli_query($dbcon, "DELETE FROM Spamprotect WHERE id = '$thisspamid'") OR die(mysqli_error($dbcon));
    }
  $countspams++;
  }
}

