<?php
include("../../data/dbconnect.php");
include("../../classes/functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$strat = $_REQUEST["strat"];
$ticked = $_REQUEST["ticked"];

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
}
else {
    $failed = "1";
}
if ($failed != "1") {
    $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $strat");
    if (mysqli_num_rows($stratdb) < "1") {
        $failed = "1";        
    }
    else {
        $strat = mysqli_fetch_object($stratdb);
    }
}

if ($user && $user->ComSecret == $comsecret && $failed != "1") {
    $ticked_tags = explode(",", $ticked);
    $all_tags = get_all_tags();
    $userrights = format_userrights($user->Rights);

    // User is the creator of the strat but has no other access rights
    if ($userrights['EditStrats'] != "yes" && $userrights['EditTags'] != "yes" && $strat->User == $user->id) {
        foreach ($all_tags as $this_tag) {
            if ($this_tag['Access'] == 1) {
                if (in_array("tag_".$this_tag['ID'], $ticked_tags)) {
                    $updatetags[$this_tag['ID']] = 1;
                }
                else {
                    $updatetags[$this_tag['ID']] = 0;
                }
            }
        }
        $failed = "no";
    }
    
    // User has edit rights to all tags
    if ($userrights['EditStrats'] == "yes" OR $userrights['EditTags'] == "yes") {
        foreach ($all_tags as $this_tag) {
            if ($this_tag['Access'] > 0) {
                if (in_array("tag_".$this_tag['ID'], $ticked_tags)) {
                    $updatetags[$this_tag['ID']] = 1;
                }
                else {
                    $updatetags[$this_tag['ID']] = 0;
                }
            }
        }
        $failed = "no";
    }
    
    if ($failed == "no") {
        if ($updatetags[22] == 1) {
            $unchecked_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = $strat->id AND Tag = 21");
            if (mysqli_num_rows($unchecked_db) > 0) {
                echo "V";
                $updatetags[21] = 0;
            }
        }
        update_tags($strat->id, $updatetags);
        echo "OK";
    }
}
mysqli_close($dbcon);
