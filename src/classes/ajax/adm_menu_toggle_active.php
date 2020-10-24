<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$usersecret = $_REQUEST["delimiter"];
$lineid = $_REQUEST["lineid"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
if (mysqli_num_rows($userdb) > "0") {
    $user = mysqli_fetch_object($userdb);
    if ($user->ComSecret == $usersecret) {
        $usercheck = "OK";
    }
}

$itemid_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE id = '$lineid'");
if (mysqli_num_rows($itemid_db) > "0") {
    $menu_item = mysqli_fetch_object($itemid_db);
    $itemcheck = "OK";
}

if ($itemcheck == "OK" && $usercheck == "OK"){
    if ($menu_item->Active == 0) {
        mysqli_query($dbcon, "UPDATE Menu_Primary SET `Active` = '1' WHERE id = '$lineid'") OR die(mysqli_error($dbcon));
        echo "ON";
    }
    if ($menu_item->Active == 1) {
        mysqli_query($dbcon, "UPDATE Menu_Primary SET `Active` = '0' WHERE id = '$lineid'") OR die(mysqli_error($dbcon));
        echo "OFF";
    }
}

mysqli_close($dbcon); 