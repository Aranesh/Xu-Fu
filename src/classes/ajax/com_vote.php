<?php
include("../../data/dbconnect.php");
include("../functions.php");

$pagesettingsdb = mysqli_query($dbcon, "SELECT * FROM PageSettings LIMIT 1");
$pagesettings = mysqli_fetch_object($pagesettingsdb);

$userid = $_REQUEST["userid"];
$sortingid = $_REQUEST["sortingid"];
$comsecret = $_REQUEST["delimiter"];
$type = $_REQUEST["type"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}


$commentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$sortingid'");
if (mysqli_num_rows($commentdb) > "0") {
    $comment = mysqli_fetch_object($commentdb);
    $votes = $comment->Votes;
}

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
if (mysqli_num_rows($userdb) > "0") {
    $user = mysqli_fetch_object($userdb);
    $language = $user->Language;
    require_once ("../../thirdparty/motranslator/vendor/autoload.php");
    PhpMyAdmin\MoTranslator\Loader::loadFunctions();
      _setlocale(LC_MESSAGES, $language);
      _textdomain('messages');
      _bindtextdomain('messages', __DIR__ . '/../../Locale/');
      _bind_textdomain_codeset('messages', 'UTF-8');
      set_language_vars($language);
}

if ($user && $user->ComSecret == $comsecret && $votes != "") {
    // Passed verification

    $votedb = mysqli_query($dbcon, "SELECT * FROM Votes WHERE User = '$user->id' AND SortingID = '$sortingid'");
    if (mysqli_num_rows($votedb) > "0"){
        $vote = mysqli_fetch_object($votedb);
        if ($vote->Vote == $type) {
            if ($type == 1) {
                // Already voted for up, cancelling vote
                $votes = $votes-1;
                mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '$votes' WHERE id = '$sortingid'");
                mysqli_query($dbcon, "DELETE FROM Votes WHERE id = '$vote->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Vote', '$sortingid - Cancelled Upvote')") OR die(mysqli_error($dbcon));
                echo "CancelUp";
            }
            if ($type == 0) {
                // Already voted for down, cancelling vote
                $votes = $votes+1;
                mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '$votes' WHERE id = '$sortingid'");
                mysqli_query($dbcon, "DELETE FROM Votes WHERE id = '$vote->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Vote', '$sortingid - Cancelled Downvote')") OR die(mysqli_error($dbcon));
                echo "CancelDown";
            }
        }
        else {
            if ($type == "1") {
                // Already voted, change to Upvote
                $votes = $votes+2;
                mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '$votes' WHERE id = '$sortingid'");
                mysqli_query($dbcon, "UPDATE Votes SET `Vote` = '1' WHERE id = '$vote->id'");
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Vote', '$sortingid - Change to Upvote')") OR die(mysqli_error($dbcon));
                echo "ChangeUp";
            }
            else if ($type == "0") {
                // Already voted, change to Downvote
                $votes = $votes-2;
                mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '$votes' WHERE id = '$sortingid'");
                mysqli_query($dbcon, "UPDATE Votes SET `Vote` = '0' WHERE id = '$vote->id'");
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Vote', '$sortingid - Change to Downvote')") OR die(mysqli_error($dbcon));
                echo "ChangeDown";
            }
        }
    }

    else if (mysqli_num_rows($votedb) < "1"){
        if ($type == "1") {
            // Not voted, add Upvote
            $votes = $votes+1;
            mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '$votes' WHERE id = '$sortingid'");
            mysqli_query($dbcon, "INSERT INTO Votes (`User`, `SortingID`, `Vote`) VALUES ('$userid', '$sortingid', '1')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Vote', '$sortingid - Upvote')") OR die(mysqli_error($dbcon));
            echo "Up";
        }
        else if ($type == "0") {
            // Not voted, add Downvote
            $votes = $votes-1;
            mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '$votes' WHERE id = '$sortingid'");
            mysqli_query($dbcon, "INSERT INTO Votes (`User`, `SortingID`, `Vote`) VALUES ('$userid', '$sortingid', '0')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Comment Vote', '$sortingid - Downvote')") OR die(mysqli_error($dbcon));
            echo "Down";
        }
    }

    if ($type == "1" && $comment->GoldMsgTrig == "0" && $votes >= $pagesettings->Com_Gold && $comment->User != "0") {
        $displaynumb = $pagesettings->Com_Gold+1;
        $wlcmsgsub = __("Gold Comment Reached!");
        $wlcmsgmsg = __("Congratulations!").'
'.__("One of your comments got enough upvotes from other pet battlers to reach Gold status").'
'.__("It will now be highlighted much more prominently, go check it out:").' https://wow-petguide.com/?Comment='.$sortingid.'
'.__("Thank you for your contribution!").'
'.__("Yours,").'
Xu-Fu';
        $wlcmsgsub = mysqli_real_escape_string($dbcon, $wlcmsgsub);
        $wlcmsgmsg = mysqli_real_escape_string($dbcon, $wlcmsgmsg);
        mysqli_query($dbcon, "INSERT INTO UserMessages (`Sender`, `Receiver`, `Subject`, `Content`, `Type`) VALUES ('1', '$comment->User', '$wlcmsgsub', '$wlcmsgmsg', '1')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "UPDATE Comments SET `GoldMsgTrig` = '1' WHERE id = '$sortingid'");
    }
}
mysqli_close($dbcon);