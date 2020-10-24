<?php
include("../../data/dbconnect.php");
include("../functions.php");

$seluser = $_GET['q'];
$thisuser = $_GET['u'];
$exclude_own = $_GET['e'];

if ($seluser){
$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name LIKE '%$seluser%'");

$json = [];
while($row = $userdb->fetch_assoc()){
    if ($exclude_own == "f") {
        $json[] = ['id'=>$row['id'], 'text'=>$row['Name']];
    }
    else if ($exclude_own == "noxu") {
        if ($row['id'] != 1 && $row['id'] != $thisuser) {
            $json[] = ['id'=>$row['id'], 'text'=>$row['Name']];
        }
    }
    else {
        if ($row['id'] != $thisuser) {
            $json[] = ['id'=>$row['id'], 'text'=>$row['Name']];
        }
    }
}

echo json_encode($json);
}

mysqli_close($dbcon);