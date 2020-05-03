<?php
include("../../data/dbconnect.php");
include("../functions.php");

// =================== FUNCTION REFRESHES USERS WOW AVATARS ONCE EVERY 7 DAYS ===================

$wowusersdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE UseWowAvatar = '1'") OR die(mysqli_error($dbcon));
if (mysqli_num_rows($wowusersdb) > "0"){
    $countusers = "0";
    while ($countusers < mysqli_num_rows($wowusersdb)){
        $thisuser = mysqli_fetch_object($wowusersdb);
        $bnetuserdb = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$thisuser->id' AND (IconUpdate = '0000-00-00 00:00:00' OR IconUpdate < DATE_SUB(NOW(), INTERVAL 7 DAY))") OR die(mysqli_error($dbcon));
            if (mysqli_num_rows($bnetuserdb) > "0"){
                $thisbnet = mysqli_fetch_object($bnetuserdb);
                $iconpath = "http://render-".$thisbnet->Region.".worldofwarcraft.com/character/".$thisbnet->CharIcon;
                $targetpath = "../../images/userpics/".$thisuser->id.".jpg";

                if (checkExternalFile($iconpath) == "200" && $thisbnet->CharIcon != '') {
                    if (file_exists($targetpath)) {
                        unlink($targetpath);
                    }
                    copy($iconpath, $targetpath);
                    $icontime = date('Y-m-d H:i:s');
                    mysqli_query($dbcon, "UPDATE UserBnet SET `IconUpdate` = '$icontime' WHERE id = '$thisbnet->id'") OR die(mysqli_error($dbcon));
                    echo "Updated User Icon for: ".$thisuser->Name."<br>";
                }
                else {
                    echo "No valid character - reset icon for: ".$thisuser->Name."<br>";
                    $icontime = date('Y-m-d H:i:s');
                    mysqli_query($dbcon, "UPDATE UserBnet SET `IconUpdate` = '$icontime' WHERE id = '$thisbnet->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Users SET `UseWowAvatar` = '0' WHERE id = '$thisuser->id'") OR die(mysqli_error($dbcon));
                }
            }
        $countusers++;
    }
}