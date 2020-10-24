<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$field_content = $_REQUEST["field_content"];
$field_content = remove_emojis($field_content);
$field_content = mysqli_real_escape_string($dbcon, $field_content);
$field_content = trim($field_content);
$field_content = preg_replace('#<br\s*/?>#i', "\n", $field_content);
$field_content = str_replace('<div>', '\n', $field_content);
$field_content = str_replace('</div>', '', $field_content);
$field_content = html_entity_decode($field_content);

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

if ($userid && $abortthis != "true") {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        if ($user->ComSecret == $comsecret) {

            if ($field_content != "") {
                mysqli_query($dbcon, "UPDATE Users SET `PrUseIntro` = '1' WHERE id = '$user->id'");
            }
            else {
                mysqli_query($dbcon, "UPDATE Users SET `PrUseIntro` = '0' WHERE id = '$user->id'");
            }
            mysqli_query($dbcon, "UPDATE Users SET PrIntro = '$field_content' WHERE id = '$user->id'");
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Profile - Edited Intro')") OR die(mysqli_error($dbcon));
            echo "OK";
            $result = "done";
        }
    }
}

if ($result != "done") {
    echo "NOK";
}

mysqli_close($dbcon);


