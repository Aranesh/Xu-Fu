<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$field_type = $_REQUEST["field_type"];
$field_content = $_REQUEST["field_content"];

$field_content = html_entity_decode($field_content);
$field_type = mysqli_real_escape_string($dbcon, $field_type);


if ($field_content == "<br>"){
    $field_content = "";
}

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

if ($field_type == "btag" OR $field_type == "btagnum") {
    $field_type = "PrBattleTag";
}
else if ($field_type == "discord") {
    $field_type = "PrDiscord";
}
else if ($field_type == "facebook") {
    $field_type = "PrSocFacebook";
}
else if ($field_type == "twitter") {
    $field_type = "PrSocTwitter";
}
else if ($field_type == "instagram") {
    $field_type = "PrSocInstagram";
}
else if ($field_type == "youtube") {
    $field_type = "PrSocYoutube";
}
else if ($field_type == "reddit") {
    $field_type = "PrSocReddit";
}
else if ($field_type == "twitch") {
    $field_type = "PrSocTwitch";
}
else {
    $field_type = "";
    $abortthis = "true";
}

if ($field_content != "" && $field_type != "PrDiscord" && $field_type != "PrBattleTag") {
    $field_content = preg_replace('/^(?!https?:\/\/)/', 'http://', $field_content);
}

if ($userid && $abortthis != "true") {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        if ($user->ComSecret == $comsecret) {
            if ($field_type == "PrBattleTag") {
                // echo $field_content;
                $cutbtag = explode ("__separatorzzuugg__", $field_content);
                $btag_reg = $cutbtag[0];
                $btag_name = mysqli_real_escape_string($dbcon, $cutbtag[1]);
                $btag_num = mysqli_real_escape_string($dbcon, $cutbtag[2]);
                mysqli_query($dbcon, "UPDATE Users SET PrBTagRegion = '$btag_reg' WHERE id = '$user->id'");
                mysqli_query($dbcon, "UPDATE Users SET PrBattleTag = '$btag_name' WHERE id = '$user->id'");
                mysqli_query($dbcon, "UPDATE Users SET PrBTagNum = '$btag_num' WHERE id = '$user->id'");
            }
            else {
                $field_content = mysqli_real_escape_string($dbcon, $field_content);
                mysqli_query($dbcon, "UPDATE Users SET $field_type = '$field_content' WHERE id = '$user->id'");
            }
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Profile - Edited About Section')") OR die(mysqli_error($dbcon));
            echo "OK";
            $result = "done";
        }
    }
}

if ($result != "done") {
    echo "NOK";
}

mysqli_close($dbcon);



