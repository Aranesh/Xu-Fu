<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$category = $_REQUEST["cat"];
$action = $_REQUEST["action"];

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
            if ($category == "ttintrot") {
                set_settings($userid,"2",$action);
                $result = "done";
            }
            if ($category == "ttcoll") {
                set_settings($userid,"3",$action);
                $result = "done";
            }
            if ($category == "ttqf") {
                set_settings($userid,"4",$action);
                $result = "done";
            }
            if ($category == "ttsocmt") {
                set_settings($userid,"5",$action);
                $result = "done";
            }
            if ($category == "ttonstats") {
                set_settings($userid,"6",$action);
                $result = "done";
            }
            if ($result = "done") {
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Tooltip settings changed')") OR die(mysqli_error($dbcon));
                echo "OK";
            }
        }
    }
}
if ($result != "done") {
    echo "NOK";
}

mysqli_close($dbcon); 
