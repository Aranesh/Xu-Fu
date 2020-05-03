<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$section = $_REQUEST["section"];

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
            switch ($section) {
                case "quickfacts":
                    $togglesection = "PrUseQuickFacts";
                    if ($user->PrUseQuickFacts == "1"){
                        $toggleto = "0";
                        $change = "hide";
                    }
                    if ($user->PrUseQuickFacts == "0"){
                        $toggleto = "1";
                        $change = "show";
                    }
                break;
                case "intro":
                    $togglesection = "PrUseIntro";
                    if ($user->PrUseIntro == "1"){
                        $toggleto = "0";
                        set_settings($user->id,"2","0");
                        $change = "hide";
                    }
                    if ($user->PrUseIntro == "0" AND $user->PrIntro != ""){
                        set_settings($user->id,"2","1");
                        $toggleto = "1";
                        $change = "show";
                    }
                    if ($user->PrUseIntro == "0" AND $user->PrIntro == ""){
                        set_settings($user->id,"2","0");
                        $toggleto = "";
                        $change = "NoIntro";
                    }
                break;
                case "socm":
                    $togglesection = "PrUseSocM";
                    if ($user->PrUseSocM == "1"){
                        $toggleto = "0";
                        $change = "hide";
                    }
                    if ($user->PrUseSocM == "0" AND ($user->PrSocFacebook != "" OR $user->PrSocTwitter != "" OR $user->PrSocInstagram != "" OR $user->PrSocYoutube != "" OR $user->PrSocReddit != "" OR $user->PrSocTwitch != "")){
                        $toggleto = "1";
                        $change = "show";
                    }
                    if ($user->PrUseSocM == "0" AND $user->PrSocFacebook == "" AND $user->PrSocTwitter == "" AND $user->PrSocInstagram == "" AND $user->PrSocYoutube == "" AND $user->PrSocReddit == "" AND $user->PrSocTwitch == ""){
                        $toggleto = "";
                        $change = "NoSocM";
                    }
                break;
                case "collection":
                    $togglesection = "PrUseCol";
                    if ($user->PrUseCol == "1"){
                        set_settings($user->id,"3","0");
                        $toggleto = "0";
                        $change = "hide";
                    }
                    if ($user->PrUseCol == "0"){
                        set_settings($user->id,"3","1");
                        $toggleto = "1";
                        $change = "show";
                    }
                break;
            }
            if ($togglesection){
                mysqli_query($dbcon, "UPDATE Users SET `$togglesection` = '$toggleto' WHERE id = '$user->id'");
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '1', 'User Profile - section $togglesection, action: $change')") OR die(mysqli_error($dbcon));
                echo $change;
                $result = "done";
            }
        }
    }
}
if ($result != "done") {
    echo "NOK";
}
mysqli_close($dbcon); 

