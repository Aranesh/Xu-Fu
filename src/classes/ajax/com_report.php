<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$language = $_REQUEST["lang"];
$category = $_REQUEST["category"];
$sortingid = $_REQUEST["sortingid"];
$type = $_REQUEST["type"];
$content = $_REQUEST["content"];
$content = mysqli_real_escape_string($dbcon, $content);

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

$settingsdb = mysqli_query($dbcon, "SELECT * FROM admin WHERE id = '1'");
$settings = mysqli_fetch_object($settingsdb);

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
if (mysqli_num_rows($userdb) > "0") {
    $user = mysqli_fetch_object($userdb);
    if ($user->ComSecret != $comsecret) {
        $reporterror = "true";
    }
}

if ($category != "1" AND $category != "0") {                                              // check that category is not tampered with. 0 = comment 1 = strategy
    $reporterror = "true";
}


if ($user AND $reporterror != "true") {                                                   // check if user already sent a report about this comment or not
    $reportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '$category' AND SortingID = '$sortingid' AND User = '$user->id'");
    if (mysqli_num_rows($reportdb) > "0") {
        $reporterror = "true";
    }
}

if ($type != "inappropriate" AND $type != "spam" AND $type != "other"){
    $reporterror = "true";
}
switch ($type) {
    case "inappropriate":
        $inputtype = "0";
        break;
    case "spam":
        $inputtype = "1";
        break;
    case "other":
        $inputtype = "2";
        break;
}

if ($reporterror != "true") {
    mysqli_query($dbcon, "INSERT INTO Reports (`User`, `IP`, `Language`, `Category`, `SortingID`, `Type`, `Content`) VALUES ('$userid', '$user_ip_adress', '$language', '$category', '$sortingid', '$inputtype', '$content')") OR die(mysqli_error($dbcon));
    echo "OK";
}
else {
    echo "NOK";
}

mysqli_close($dbcon); 