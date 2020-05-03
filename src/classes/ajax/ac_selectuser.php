<?php
include("../../data/dbconnect.php");
include("../functions.php");

$seluser = $_GET['q'];
$thisuser = $_GET['delim'];


if ($seluser){
$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name LIKE '%$seluser%'");

$json = [];
while($row = $userdb->fetch_assoc()){
    if ($row['id'] != $thisuser) {
        $json[] = ['id'=>$row['id'], 'text'=>$row['Name']];
    }
}

echo json_encode($json);
}

mysqli_close($dbcon); 