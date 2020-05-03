<?php
include("../../data/dbconnect.php");
include("../functions.php");

$username = $_REQUEST["q"];

$username = mysqli_real_escape_string($dbcon, $username);
$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name = '$username'");
if (mysqli_num_rows($userdb)!= "0") {
   echo 'NOK';
}
else {
   echo 'OK';
}


mysqli_close($dbcon); 





