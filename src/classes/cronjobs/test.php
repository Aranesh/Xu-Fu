<?php
include("../../data/dbconnect.php");
include("../functions.php");

$count = "1";
$offset = "24";
while ($count < "60") {

$allcomsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL $offset HOUR) AND Date <= NOW()") OR die(mysqli_error($dbcon));
echo mysqli_num_rows($allcomsdb)."<br>";
$offset = $offset+24;
$count++;
}


