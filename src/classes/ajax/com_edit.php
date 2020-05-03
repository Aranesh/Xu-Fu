<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$commentid = $_REQUEST["commentid"];
$comsecret = $_REQUEST["delimiter"];
$comcontent = $_REQUEST["content"];
$comcontent = mysqli_real_escape_string($dbcon, $comcontent);

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

$admindb = mysqli_query($dbcon, "SELECT * FROM admin WHERE id = '1'");
$admin = mysqli_fetch_object($admindb);
$comeditdeadline = $admin->ComEditTimer;
$comeditdeadline = $comeditdeadline+180;

$commentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$commentid'");
if (mysqli_num_rows($commentdb) > "0")  {
    $comment = mysqli_fetch_object($commentdb);

    if ($userid) {
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
        if (mysqli_num_rows($userdb) > "0") {
            $user = mysqli_fetch_object($userdb);
            $userrights = format_userrights($user->Rights);
            if ($user->ComSecret == $comsecret && $userrights[1] == "1") {
                $makeedit = "true";
                // echo "admin, secret matches";
            }
            if ($userrights[1] != "1") {
                if ($comment->User == $userid && $user->ComSecret == $comsecret && (strtotime(date("Y-m-d H:i:s")) - strtotime($comment->Date)) < $comeditdeadline){
                    $makeedit = "true";
                    // echo "logged in, comment belongs to user, time is OK, secret matches";
                }
            }
        }
    }

    if (($userrights[1] != "1" OR !$user) && $user_ip_adress == $comment->IP && (strtotime(date("Y-m-d H:i:s")) - strtotime($comment->Date)) < $comeditdeadline) {
        $makeedit = "true";
        // echo "IP matches, looks good, time is fine";
    }
}

if ($makeedit == "true"){
    $dingda = mysqli_query($dbcon, "UPDATE Comments SET `Comment` = '$comcontent' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    $dingda = mysqli_query($dbcon, "UPDATE Comments SET `Edited` = '1' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Edited', '$commentid')") OR die(mysqli_error($dbcon));
    echo "OK";
}
else {
    echo "NOK";
}

mysqli_close($dbcon); 