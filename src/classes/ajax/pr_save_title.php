<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$new_title = $_REQUEST["new_title"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}


if ($userid) {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        if ($user->ComSecret == $comsecret) {
            mysqli_query($dbcon, "UPDATE Users SET Title = '$new_title' WHERE id = '$user->id'");
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Profile - Edited User Title')") OR die(mysqli_error($dbcon));
            echo "OK";
            $result = "done";
        }
    }
}

if ($result != "done") {
    echo "NOK";
}

mysqli_close($dbcon);