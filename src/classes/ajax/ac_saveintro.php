<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$cat = $_REQUEST["cat"];
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

if ($userid) {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        if ($user->ComSecret == $comsecret) {
            if ($content == ""){
                mysqli_query($dbcon, "UPDATE Users SET `PrUseIntro` = '0' WHERE id = '$user->id'");
                mysqli_query($dbcon, "UPDATE Users SET `PrIntro` = '' WHERE id = '$user->id'");
                set_settings($user->id,"2","0");
                echo "Empty";
                $result = "done";
            }
            else {
                mysqli_query($dbcon, "UPDATE Users SET `PrIntro` = '$content' WHERE id = '$user->id'");
                mysqli_query($dbcon, "UPDATE Users SET `PrUseIntro` = '1' WHERE id = '$user->id'");
                if ($cat == "tt") {
                    set_settings($user->id,"2","1");                                      // Request came from profile view editing, adding a text should then also activate the tt view
                }
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
