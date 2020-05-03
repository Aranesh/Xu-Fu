<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$threadid = $_REQUEST["threadid"];
$usersecret = $_REQUEST["delimiter"];

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

if ($usercheck == "OK"){
    $threaddb = mysqli_query($dbcon, "SELECT * FROM UserMessages WHERE id = '$threadid'");
    if (mysqli_num_rows($threaddb) > "0")  {
        mysqli_query($dbcon, "UPDATE UserMessages SET `Seen` = '1' WHERE id = '$threadid'") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "UPDATE UserMessages SET `Seen` = '1' WHERE Parent = '$threadid'") OR die(mysqli_error($dbcon));
        echo "OK";
    }
}

mysqli_close($dbcon); 