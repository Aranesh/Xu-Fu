<?php
include("../../data/dbconnect.php");
include("../functions.php");
require_once('../../data/blizzard_api.php');

// =================== FUNCTION REFRESHES USERS PET COLLECTION AUTOMATICALLY ===================

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
    <table><tr><td>ID</td><td>User Name</td><td>Update Status</td><td>Description</td></tr>

<?
$usersdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE LastColUpdate = '0000-00-00 00:00:00' OR LastColUpdate < DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY id") OR die(mysqli_error($dbcon));
// $usersdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = 2") OR die(mysqli_error($dbcon)); // Activate for testing only with Aranesh Account
echo "Collections found to check: ".mysqli_num_rows($usersdb);

// $getcol = update_collection('2', '0', '../../', 'cronjob');
// echo "<tr><td>".$user->id."</td><td>".$user->Name."</td><td>".$getcol[0]."</td><td>".$getcol[1]."</td></tr>";
// die;


if (mysqli_num_rows($usersdb) > "0"){
    $countusers = "0";
    while ($user = mysqli_fetch_object($usersdb)) {
        if ($countusers > 500) {
            die;
        }
        $getcol = update_collection($user->id, '0', '../../', 'cronjob');
        echo "<tr><td>".$user->id."</td><td>".$user->Name."</td><td>".$getcol[0]."</td><td>".$getcol[1]."</td></tr>";
        $updatetime = date('Y-m-d H:i:s');
        mysqli_query($dbcon, "UPDATE Users SET `LastColUpdate` = '$updatetime' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
        $countusers++;
    }
}

echo "</table>";