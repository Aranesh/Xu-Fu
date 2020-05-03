<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$stratid = $_REQUEST["strat"];
$comsecret = $_REQUEST["delimiter"];

$p1id = $_REQUEST["p1id"];
$p1level = $_REQUEST["p1level"];
$p1breed = $_REQUEST["p1breed"];
$p1substitute = $_REQUEST["p1substitute"];

$p2id = $_REQUEST["p2id"];
$p2level = $_REQUEST["p2level"];
$p2breed = $_REQUEST["p2breed"];
$p2substitute = $_REQUEST["p2substitute"];

$p3id = $_REQUEST["p3id"];
$p3level = $_REQUEST["p3level"];
$p3breed = $_REQUEST["p3breed"];
$p3substitute = $_REQUEST["p3substitute"];

$success = $_REQUEST["success"];
$attempts = $_REQUEST["attempts"];

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
    $pass = "ok";
}
if ($pass == "ok" && $stratid) {
    $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$stratid'");
    if (mysqli_num_rows($stratdb) > "0") {
        $strat = mysqli_fetch_object($userdb);
        $pass = "stillok";
    }    
}

if ($pass == "stillok" && $user && $user->ComSecret == $comsecret) {
    // Passed verification
    mysqli_query($dbcon, "INSERT INTO UserAttempts (`User`, `Strategy`, `Success`, `Attempts`, `Pet1`, `Substitute1`, `Breed1`, `Level1`, `Pet2`, `Substitute2`, `Breed2`, `Level2`, `Pet3`, `Substitute3`, `Breed3`, `Level3`) VALUES ('$userid', '$stratid', '$success', '$attempts', '$p1id', '$p1substitute', '$p1breed', '$p1level', '$p2id', '$p2substitute', '$p2breed', '$p2level', '$p3id', '$p3substitute', '$p3breed', '$p3level')") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Fight Recorded', '$stratid')") OR die(mysqli_error($dbcon));
    echo "OK";            
}
mysqli_close($dbcon);

