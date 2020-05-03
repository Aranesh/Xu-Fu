<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$commentid = $_REQUEST["commentid"];
$comsecret = $_REQUEST["delimiter"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

$admindb = mysqli_query($dbcon, "SELECT * FROM admin WHERE id = '1'");
$admin = mysqli_fetch_object($admindb);
$comeditdeadline = $admin->ComEditTimer;
$comeditdeadline = $comeditdeadline+180;

$commentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$commentid'");
if (mysqli_num_rows($commentdb) > "0")  {
    $comment = mysqli_fetch_object($commentdb);

    if ($userid) {
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
        if (mysqli_num_rows($userdb) > "0") {
            $user = mysqli_fetch_object($userdb);
            $userrights = format_userrights($user->Rights);
            if ($user->ComSecret == $comsecret && $userrights[2] == "1") {
                $deleteable = "true";
                $closetype = "Admin-Deletion";
                $closedby = $user->id;
                // echo "admin, secret matches";
            }
            if ($user->Role < "99") {
                if ($comment->User == $userid && $user->ComSecret == $comsecret && (strtotime(date("Y-m-d H:i:s")) - strtotime($comment->Date)) < $comeditdeadline){
                    $deleteable = "true";
                    $closetype = "User deleted";
                    $closedby = $user->id;
                    // echo "logged in, comment belongs to user, time is OK, secret matches";
                }
            }
        }
    }

    if (($userrights[2] != "1" OR !$user) && $user_ip_adress == $comment->IP && (strtotime(date("Y-m-d H:i:s")) - strtotime($comment->Date)) < $comeditdeadline) {
        $deleteable = "true";
        $closetype = "User deleted";
        $closedby = $comment->Name;
        // echo "IP matches, looks good, time is fine";
    }
}

if ($deleteable == "true"){
    // Remove comment from list of comments in an alternative if it was a new one for the creator
    if ($comment->Category == "2") {
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$comment->SortingID'");
        if (mysqli_num_rows($stratdb) > "0")  {
            $strat = mysqli_fetch_object($stratdb);
            if ($strat->NewComs > "0") {
                $newcoms = explode(";",$strat->NewComsIDs);
                
                $counti = "0";
                foreach ($newcoms as $comkey => $comvalue) {
                    $comsplits = explode("_",$comvalue);               
                    if ($comsplits[0] == $comment->id) {
                        $updatenewcoms = "true";
                    }
                    else {
                        if ($counti == "0") {
                            $insertnewcoms = $comvalue;
                        }
                        else {
                            $insertnewcoms = $insertnewcoms.";".$comvalue;
                        }
                        $counti++;
                    }
                }
                if ($updatenewcoms == "true") {
                    mysqli_query($dbcon, "UPDATE Alternatives SET NewComs = NewComs - 1 WHERE id  = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Alternatives SET NewComsIDs = '$insertnewcoms' WHERE id  = '$strat->id'") OR die(mysqli_error($dbcon));
                }
            }
        }    
    }
    
    
    
    // Delete comment
    $deletedate = date("Y-m-d H:i:s");
    mysqli_query($dbcon, "UPDATE Comments SET `Closed` = '1' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Comments SET `CloseType` = '$closetype' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Comments SET `ClosedBy` = '$closedby' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Comments SET `ForReview` = '0' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Comments SET `ClosedOn` = '$deletedate' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Comments SET `Deleted` = '1' WHERE id = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Reports SET `Reviewed` = '1' WHERE Category = '0' AND SortingID = '$commentid'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Deleted by User', '$commentid')") OR die(mysqli_error($dbcon));
    echo "OK";
}
else {
    echo "NOK";
}

mysqli_close($dbcon); 