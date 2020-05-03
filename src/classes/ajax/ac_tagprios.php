<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$usersecret = $_REQUEST["delimiter"];
$stratprio = $_REQUEST["stratprio"];

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

if ($usercheck == "OK" && $stratprio){
    mysqli_query($dbcon, "UPDATE Users SET `TagPrio` = '$stratprio' WHERE id = '$userid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Tag Priorities updated', '$stratprio')") OR die(mysqli_error($dbcon));
    echo "OK";
}

mysqli_close($dbcon); 