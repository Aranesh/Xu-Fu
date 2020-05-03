<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$commentid = $_REQUEST["commentid"];
$comsecret = $_REQUEST["delimiter"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}


$commentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$commentid'");
if (mysqli_num_rows($commentdb) > "0")  {
    $comment = mysqli_fetch_object($commentdb);
    if ($userid) {
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
        if (mysqli_num_rows($userdb) > "0") {
            $user = mysqli_fetch_object($userdb);
            $userrights = format_userrights($user->Rights);
            if ($user->ComSecret == $comsecret && $userrights['EditStrats'] == "yes") {
                // Delete comment
                $deletedate = date("Y-m-d H:i:s");
                mysqli_query($dbcon, "UPDATE Comments SET `Deleted` = '1' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE Comments SET `NewActivity` = '0' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Internal Comment Deleted by Curator', '$commentid')") OR die(mysqli_error($dbcon));
                echo "OK";
                mysqli_close($dbcon);
                die;
            }
        }
    }
}

echo "NOK";
mysqli_close($dbcon);

 