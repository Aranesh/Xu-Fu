<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$cat = $_REQUEST["cat"];
$fab = $_REQUEST["fab"];
$twi = $_REQUEST["twi"];
$ing = $_REQUEST["ing"];
$ytu = $_REQUEST["ytu"];
$red = $_REQUEST["red"];
$twt = $_REQUEST["twt"];
if ($fab != "") {
    $fab = preg_replace('/^(?!https?:\/\/)/', 'http://', $fab);
}
if ($twi != "") {
$twi = preg_replace('/^(?!https?:\/\/)/', 'http://', $twi);
}
if ($ing != "") {
$ing = preg_replace('/^(?!https?:\/\/)/', 'http://', $ing);
}
if ($ytu != "") {
$ytu = preg_replace('/^(?!https?:\/\/)/', 'http://', $ytu);
}
if ($red != "") {
$red = preg_replace('/^(?!https?:\/\/)/', 'http://', $red);
}
if ($twt != "") {
$twt = preg_replace('/^(?!https?:\/\/)/', 'http://', $twt);
}
$fab = mysqli_real_escape_string($dbcon, $fab);
$twi = mysqli_real_escape_string($dbcon, $twi);
$ing = mysqli_real_escape_string($dbcon, $ing);
$ytu = mysqli_real_escape_string($dbcon, $ytu);
$red = mysqli_real_escape_string($dbcon, $red);
$twt = mysqli_real_escape_string($dbcon, $twt);

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
            if ($fab == "" && $twi == "" && $ing == "" && $ytu == "" && $red == "" && $twt == ""){
                mysqli_query($dbcon, "UPDATE Users SET `PrSocFacebook` = '', `PrSocTwitter` = '', `PrSocInstagram` = '', `PrSocYoutube` = '', `PrSocReddit` = '', `PrSocTwitch` = '' WHERE id = '$user->id'");
                mysqli_query($dbcon, "UPDATE Users SET `PrUseSocM` = '0' WHERE id = '$user->id'");
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Profile - Removed all Social Media Links')") OR die(mysqli_error($dbcon));
                set_settings($user->id,"5","0");
                echo "Empty";
                $result = "done";
            }
            else {
                mysqli_query($dbcon, "UPDATE Users SET `PrSocFacebook` = '$fab', `PrSocTwitter` = '$twi', `PrSocInstagram` = '$ing', `PrSocYoutube` = '$ytu', `PrSocReddit` = '$red', `PrSocTwitch` = '$twt' WHERE id = '$user->id'");
                mysqli_query($dbcon, "UPDATE Users SET `PrUseSocM` = '1' WHERE id = '$user->id'");
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Profile - Updated Social Media Links')") OR die(mysqli_error($dbcon));
                if ($cat == "tt") {
                    set_settings($user->id,"5","1");                                      // Request came from profile view editing, adding any socm should then also activate the tt view
                }
                echo "OK";
                $result = "done";
            }
        }
    }
}
if ($result != "done") {
    echo "NOK";
}

mysqli_close($dbcon); 

