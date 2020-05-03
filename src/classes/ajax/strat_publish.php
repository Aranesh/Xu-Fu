<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$strat = $_REQUEST["strat"];
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
if ($action != "1" && $action != "0") {
    $error = "true";
}
$stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $strat");
if (mysqli_num_rows($stratdb) < "1") {
    $error = "true";
}

if ($userid && $error != "true") {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        if ($user->ComSecret == $comsecret) {
            mysqli_query($dbcon, "UPDATE Alternatives SET `Published` = '$action' WHERE id = '$strat'");
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Strategy published or unpublished', '$action')") OR die(mysqli_error($dbcon));
            echo "OK";
            $result = "done";
        }
    }
}
if ($result != "done") {
    echo "NOK";
}

mysqli_close($dbcon);
