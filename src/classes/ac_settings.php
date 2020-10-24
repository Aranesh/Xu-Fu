<?php

require_once ('Strategy.php');

if ($settingspage == ""){
$settingspage = $_POST['settingspage'];
}
$command = \HTTP\argument_GET_or_default ('command', FALSE);
$command_pieces = explode('_', $command);
$command = $command_pieces[0];
$source = \HTTP\argument_POST_or_default ('source', FALSE);
if (!$source) {
  $source = $command_pieces[1];
}


if ($user->TagPrio == "") {
    foreach ($all_tags as $key => $value) {
        $cutprio = explode("-", $value['DefaultPrio']);
        $tagprio[$cutprio[0]][$cutprio[1]] = $value['ID'];
    }  
}
else {
    $test_tags = $all_tags;
    $countone = "0";
    $counttwo = "0";
    $countthr = "0";    
    $cuttags = explode("-", $user->TagPrio);
    $cutone = explode(",", $cuttags[0]);
    $cuttwo = explode(",", $cuttags[1]);
    $cutthr = explode(",", $cuttags[2]);

    if ($cutone[0] != "") {
        foreach ($cutone as $key => $value) {
            $tagprio[0][$countone] = $value;
            unset($test_tags[$value]);
            $countone++;
        }
    }
    if ($cuttwo[0] != "") {
        foreach ($cuttwo as $key => $value) {
            $tagprio[1][$counttwo] = $value;
            unset($test_tags[$value]);
            $counttwo++;
        }
    }
    if ($cutthr[0] != "") {
        foreach ($cutthr as $key => $value) {
            $tagprio[2][$countthr] = $value;
            unset($test_tags[$value]);
            $countthr++;
        }        
    }
    if (count($test_tags) > "0") {
        foreach ($test_tags as $key => $value) {
            $cutprio = explode("-", $all_tags[$value['ID']]['DefaultPrio']);
            if ($cutprio[0] == "0") {
                $tagprio[0][$countone] = $value['ID'];
                $countone++;
            }
            if ($cutprio[0] == "1") {
                $tagprio[1][$counttwo] = $value['ID'];
                $counttwo++;
            }
            if ($cutprio[0] == "2") {
                $tagprio[2][$countthr] = $value['ID'];
                $countthr++;
            }
        }        
    }
}




// ============= MODULE 1 - Select new user name =============

if ($settingspage == "namechange") {
    $name = mysqli_real_escape_string($dbcon, $_POST['username']);
    $subname = $name;

    $regerror = "false";

    // Username validation
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name = '$subname'");
    $usernum = mysqli_num_rows($userdb);
    if ($usernum > "0") {
    $regnameerror = "true";
    $regnameprob = __("This name is already in use.");
    }

    if (mb_strlen($subname) > "15"){
    $regnameerror = "true";
    if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
    $regnameprob = $regnameprob." ".__("This username is too long. Please do not use more than 15 characters.");
    }

    if (mb_strlen($subname) < "2" && $firstclick != "true" && $directload != "true"){
    $regnameerror = "true";
    if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
    $regnameprob = $regnameprob." ".__("This name is too short. Please use at least 2 characters.");
    }

    if (preg_match('/[\'\/#\$\{\}\[\ \]\|\<\>\?\"\\\]/', $subname))
    {
    $regnameerror = "true";
    if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
    $regnameprob = $regnameprob." ".__("Please do not use empty spaces or any of these characters:")."<br># < > [ ] | { } \" ' / \ $ ?";
    }

    if (filter_var($subname, FILTER_VALIDATE_EMAIL)) {
    $regnameerror = "true";
    $regnameprob = __("Please do not use an email address as your login name.");
    }

    if ($user->NameChange != "1") {
    $regnameerror = "true";
    $regnameprob = __("Your account is not eligible for a name change. You little trickster ;-)");
    }

    // Applying Changes

    if ($regnameerror != "true") {
        $update = mysqli_query($dbcon, "UPDATE Users SET `Name` = '$name' WHERE id = '$user->id'");
        $update = mysqli_query($dbcon, "UPDATE Users SET `NameChange` = '0' WHERE id = '$user->id'");
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Namechange', 'Through User Settings')") OR die(mysqli_error($dbcon));
        ?>
        <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/index.php?page=settings&sendtoast=namechanged">
        <?
        die;
    }
}

// ============= END OF MODULE 1 =============



// ============= MODULE 2 - Set Password for Bnet Users =============

if ($settingspage == "addpw") {

    $pass = $_POST['password'];
    $passrep = $_POST['passwordrep'];

    if (mb_strlen($pass) < "6"){
        $regpasserror = "true";
        $regpassprob = __("Your password must be at least 6 characters long.");
    }
    if ($pass != $passrep){
        $regpasserror = "true";
        $regpassprob = __("The two entries did not match. Please type in your preferred password again.");
    }
    if ($pass == $user->Name ){
        $regpasserror = "true";
        $regpassprob = __("Your password cannot be identical to your username.");
    }

    // Applying Changes

    if ($regpasserror != "true") {
        $hash = hash_passwords($pass);
        $update = mysqli_query($dbcon, "UPDATE Users SET `Hash` = '$hash' WHERE id = '$user->id'");
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Password Set', 'First time after Bnet Registration')") OR die(mysqli_error($dbcon));
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
        $sendtoast = "pwadded";
    }
}

// ============= END OF MODULE 2 =============





// ============= MODULE 3.1- Adding Battle.net oAuth to existing Account =============

if ($settingspage == "addbnet" OR $command == "addbnet") {
  // $source = \HTTP\argument_POST_or_default ('source', FALSE);
  
  try
  {
    $regionselect = strtolower($_POST['regionselect']);

    $oauth = new \BattleNet\OAuth ($regionselect, 'addbnet_'.$source, '/index.php?page=settings');
  
    if (!$oauth->is_authed) {
      \HTTP\redirect_and_die ($oauth->auth_url());
    }
    
    $useraccinfo = $oauth->fetch ('account');
    $bnetid = $useraccinfo['id'];
    $battletag = $useraccinfo['battletag'];
    $wowaccess = $oauth->has_wow_access();

    // Check if this bnet ID is already in database
    if ($region == "cn"){
        $bnetuser_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE Region = 'cn' AND BnetID = '$bnetid'");
    }
    else {
        $bnetuser_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE Region != 'cn' AND BnetID = '$bnetid'");
    }
    
    // Battle.net account already known   
    if (mysqli_num_rows($bnetuser_db) > "0") {
        $addbnetuser = mysqli_fetch_object($bnetuser_db);
        $addbneterror = "alreadyregistered";
        $sendtoast = "";
    }
    else { // New Battle.net account
        mysqli_query($dbcon, "INSERT INTO UserBnet (`User`, `Region`, `BnetID`, `BattleTag`, `WoWAccess`) VALUES ('$user->id', '$region', '$bnetid', '$battletag', '$wowaccess')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Battle.net Account Added', '$bnetid')") OR die(mysqli_error($dbcon));
        delete_collection($user->id);
        $bnet_acc_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id'");
        $bnetuser = mysqli_fetch_object($bnet_acc_db);
        $sendtoast = "bnetregsuccess";
        // Redirect to Collection if the user came from there
        if ($source == 'collection') { ?>
          <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/index.php?page=collection&command=import_redirect">
          <?
          die;
        }
    }   
  }
  catch (\BattleNet\OAuthException $e) {
    print_r($e);
    die;
    // access denied by user or undefined error
    $sendtoast = 'bnetregfail';
  }
    // echo '<script>window.history.replaceState("object or string", "Title", "?page=settings");</script>';
}




// Here is the part where account 1 is selected to move Battle.net over

if ($settingspage == "addbnetone" OR $command == "addbnetone") {

  try
  {
    $regionselect = strtolower($_POST['regionselect']);
    
    $oauth = new \BattleNet\OAuth ($regionselect, 'addbnetone', '/index.php?page=settings');

    if (!$oauth->is_authed) {
      \HTTP\redirect_and_die ($oauth->auth_url());
    }

    $useraccinfo = $oauth->fetch ('account');
    $bnetid = $useraccinfo['id'];
    $battletag = $useraccinfo['battletag'];
    $wowaccess = $oauth->has_wow_access();
    
    // Check if this bnet ID is already in database
    if ($region == "cn"){
        $bnetuser_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE Region = 'cn' AND BnetID = '$bnetid'");
    }
    else {
        $bnetuser_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE Region != 'cn' AND BnetID = '$bnetid'");
    }
    
    if (mysqli_num_rows($bnetuser_db) > "0") {  
        if ($region == "cn"){
            $wowaccess = "0";
        }                
        if ($region != "cn"){
            if ($response['scope'] == "wow.profile") {  // WoW profile accessible (scope allowed by user)
                $wowaccess = "1";
            }
            if ($response['scope'] != "wow.profile") { // WoW profile not accessible (scope not allowed by user)
                $wowaccess = "0";
            }
        }
        $addbnetuser = mysqli_fetch_object($bnetuser_db);
        mysqli_query($dbcon, "UPDATE UserBnet SET `User` = '$user->id' WHERE id = '$addbnetuser->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `WoWAccess` = '$wowaccess' WHERE id = '$addbnetuser->id'");
        delete_collection($user->id); 
        delete_collection($addbnetuser->User); 
        $protocol = "From: ".$addbnetuser->User." to: ".$user->id;
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Battle.net Connection Moved', '$protocol')") OR die(mysqli_error($dbcon));
        $sendtoast = "addbnet";
        $bnet_acc_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id'");
        $bnetuser = mysqli_fetch_object($bnet_acc_db);
    }
    else { // Surprisingly, no battle.net account was found this time. Error message.
        $sendtoast = "genericerror";
    }
  }
  catch (\BattleNet\OAuthException $e) {
    // access denied by user or undefined error
    $sendtoast = 'bnetregfail';
  }
  echo '<script>window.history.replaceState("object or string", "Title", "?page=settings");</script>';
}

// ============= END OF MODULE 3.1 =============



// ============= MODULE 1 - UNLINK Battle.net account =============

if ($settingspage == "unlink_bnet") {
    delete_collection($user->id);
    mysqli_query($dbcon, "DELETE FROM UserBnet WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Users SET `UseWoWAvatar` = '0' WHERE id = '$user->id'");
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '0', 'Battle.net Connection Removed')") OR die(mysqli_error($dbcon));
    echo '<script>window.history.replaceState("object or string", "Title", "?page=settings");</script>';
    $sendtoast = 'bnet_unlinked';
    $bnetuser = '';
}


// ============= MODULE 1 - Change Battle.net region =============

if ($settingspage == "change_region") {
    $new_region = \HTTP\argument_POST_or_GET_or_default ('region', FALSE);
    if ($new_region == 'eu' OR $new_region == 'us' OR $new_region == 'kr' OR $new_region == 'tw') {
        delete_collection($user->id);
        $collection = '';
        mysqli_query($dbcon, "UPDATE Users SET `UseWoWAvatar` = '0' WHERE id = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `Region` = '$new_region' WHERE User = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `CharRealm` = '' WHERE User = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `CharName` = '' WHERE User = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `CharLevel` = '' WHERE User = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `CharClass` = '' WHERE User = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `CharRace` = '' WHERE User = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserBnet SET `CharIcon` = '' WHERE User = '$user->id'");
        $bnet_acc_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id'");
        $bnetuser = mysqli_fetch_object($bnet_acc_db);
        $user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($user_db);
        $sendtoast = 'region_changed';
    }
}





// ============= MODULE 4 - Set Language =============

if ($settingspage == "setlanguage") {

    $getlang = $_POST['setlanguage'];
    switch ($getlang) {
        case "en_US":
            $addlang = "en_US";
            break;
        case "de_DE":
            $addlang = "de_DE";
            break;
        case "es_ES":
            $addlang = "es_ES";
            break;
        case "fr_FR":
            $addlang = "fr_FR";
            break;
        case "ru_RU":
            $addlang = "ru_RU";
            break;
        case "pt_BR":
            $addlang = "pt_BR";
            break;
        case "it_IT":
            $addlang = "it_IT";
            break;
        case "pl_PL":
            $addlang = "pl_PL";
            break;
        case "ko_KR":
            $addlang = "ko_KR";
            break;
        case "zh_TW":
            $addlang = "zh_TW";
            break;
    }

    if ($addlang){
        $update = mysqli_query($dbcon, "UPDATE Users SET `Language` = '$addlang' WHERE id = '$user->id'");
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Language changed', '$addlang')") OR die(mysqli_error($dbcon));
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
        ?>
        <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/index.php?page=settings&sendtoast=langchanged">
        <?
        die;
    }
}

// ============= END OF MODULE 4 =============






// ============= MODULE 6.1 - Add email address =============

if ($settingspage == "addemail") {

    $submail = $_POST['addemail'];
    $outputemail = htmlentities($submail, ENT_QUOTES, "UTF-8");

    if (!$submail) {
        $regmailerror = "true";
        $regmailprob = __("The email address you entered is not valid.");
    }

    if ($regmailerror != "true") {
        $checkuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$submail'");
        if (mysqli_num_rows($checkuserdb) > "0") {
            $regmailerror = "true";
            $regmailprob = __("This email address is already registered to another account.");
        }
        if (!filter_var($submail, FILTER_VALIDATE_EMAIL)) {
            $regmailerror = "true";
            $regmailprob = __("The email address you entered is not valid.");
        }
    }

    // Applying Changes

    if ($regmailerror != "true") {
        $entermail = mysqli_real_escape_string($dbcon, $submail);
        $update = mysqli_query($dbcon, "UPDATE Users SET `Email` = '$entermail' WHERE id = '$user->id'");
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Email address added', '$entermail')") OR die(mysqli_error($dbcon));
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
        $sendtoast = "emailadded";
        $outputemail = "";
    }
}

// ============= END OF MODULE 6.1 =============







// ============= MODULE 6.2 - Change or remove email address =============

if ($settingspage == "changemail") {

    $submail = $_POST['addemail'];
    $outputemail = htmlentities($submail, ENT_QUOTES, "UTF-8");

    if (!$submail) {
        $regmailerror = "true";
        $regmailprob = __("The email address you entered is not valid.");
    }

    if ($regmailerror != "true") {
        $checkuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$submail'");
        if (mysqli_num_rows($checkuserdb) > "0") {
            $regmailerror = "true";
            $regmailprob = __("This email address is already registered to another account.");
        }
        if (!filter_var($submail, FILTER_VALIDATE_EMAIL)) {
            $regmailerror = "true";
            $regmailprob = __("The email address you entered is not valid.");
        }
        if ($submail == $user->Email){
            $regmailerror = "";
        }
    }


    // Applying Changes

    if ($regmailerror != "true") {
        $entermail = mysqli_real_escape_string($dbcon, $submail);
        $update = mysqli_query($dbcon, "UPDATE Users SET `Email` = '$entermail' WHERE id = '$user->id'");
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Email address changed', '$entermail')") OR die(mysqli_error($dbcon));
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
        $sendtoast = "emailadded";
        $outputemail = "";
    }
}

if ($settingspage == "removemail") {
    $update = mysqli_query($dbcon, "UPDATE Users SET `Email` = '' WHERE id = '$user->id'");
    $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '0', 'Email address removed')") OR die(mysqli_error($dbcon));
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
    $user = mysqli_fetch_object($userdb);
    $sendtoast = "mailremoved";
}

// ============= END OF MODULE 6.2 =============




// ============= MODULE 7 - Change Password =============

if ($settingspage == "changepw") {

    $pass = $_POST['password'];
    $passrep = $_POST['passwordrep'];

    if (mb_strlen($pass) < "6"){
        $changepasserror = "true";
        $changepassprob = __("Your password must be at least 6 characters long.");
    }
    if ($pass != $passrep){
        $changepasserror = "true";
        $changepassprob = __("The two entries did not match. Please type in your preferred password again.");
    }
    if ($pass == $user->Name ){
        $changepasserror = "true";
        $changepassprob = __("Your password cannot be identical to your username.");
    }

    // Applying Changes

    if ($changepasserror != "true") {
        $hash = hash_passwords($pass);
        $update = mysqli_query($dbcon, "UPDATE Users SET `Hash` = '$hash' WHERE id = '$user->id'");
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$user->id', '$user_ip_adress', '0', 'Password Changed')") OR die(mysqli_error($dbcon));
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
        $sendtoast = "pwadded";
    }
}

// ============= END OF MODULE 7 =============







// ============= MODULE 8.1 - Delete Account =============

if ($settingspage == "delaccpass") {

    if ($user->Hash != "") {
        $delpass = $_POST['delpass'];
        if (password_verify($delpass, $user->Hash)) {
            $settingspage = "delacc2";
            $delhash = substr(md5(rand()), 0, 20);
            $update = mysqli_query($dbcon, "UPDATE Users SET `DelHash` = '$delhash' WHERE id = '$user->id'");
            $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
            $user = mysqli_fetch_object($userdb);
        }
        else {
            $settingspage = "deleteacc";
            $delpasserror = "true";
            $delpassprob = __("Submitted password was not correct.");
        }
    }
    else {
        $settingspage = "delacc2";
        $delhash = substr(md5(rand()), 0, 20);
        $update = mysqli_query($dbcon, "UPDATE Users SET `DelHash` = '$delhash' WHERE id = '$user->id'");
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
    }
}

// ============= END OF MODULE 8.1 =============





// ============= MODULE 8.2 - Delete Account Last Step=============

if ($settingspage == "delaccfinal") {
    $delstring = $_POST['delstring'];

    if ($delstring != $user->DelHash) {
        $settingspage = "";
        $sendtoast = "genericerror";
    }
    else {
        $delstrats = $_POST['delstrats'];
        $delcoms = $_POST['delcoms'];

        // Step 1 - Comments
        if ($delcoms == "keep"){
            mysqli_query($dbcon, "UPDATE Comments SET `Name` = '$user->Name' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "UPDATE Comments SET `Icon` = '$user->Icon' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "UPDATE Comments SET `User` = '0' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        }

        if ($delcoms == "anon"){
            $comsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            if (mysqli_num_rows($comsdb) > "0"){
                $dirrand = "images/pets";
                $dhrand = opendir($dirrand);
                while (false !== ($filename = readdir($dhrand))) {
                    $filesplits = explode(".",$filename);
                    if ($filesplits[1] == "png" && $filesplits[0] >= "25" && preg_match("/^[1234567890]*$/is", $filesplits[0])){
                       $iconlist[] = $filesplits[0];
                    }
                }
                $countcoms = "0";
                while ($countcoms < mysqli_num_rows($comsdb)) {
                    $comment = mysqli_fetch_object($comsdb);
                    $rand_icon = array_rand($iconlist, 1);
                    $comicon = $iconlist[$rand_icon];
                    mysqli_query($dbcon, "UPDATE Comments SET `Icon` = '$comicon' WHERE id = '$comment->id'") OR die(mysqli_error($dbcon));
                    $countcoms++;
                }
            }
            mysqli_query($dbcon, "UPDATE Comments SET `Name` = 'Anonymous' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "UPDATE Comments SET `User` = '0' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        }

        if ($delcoms == "delete"){
            $comsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            if (mysqli_num_rows($comsdb) > "0"){
                $countcoms = "0";
                while ($countcoms < mysqli_num_rows($comsdb)) {
                    $comment = mysqli_fetch_object($comsdb);
                    $updatedate = date('Y-m-d H:i:s');
                    $subcomsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                    if (mysqli_num_rows($subcomsdb) > "0") {
                        mysqli_query($dbcon, "UPDATE Comments SET `Closed` = '1' WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                        mysqli_query($dbcon, "UPDATE Comments SET `CloseType` = 'Parent Comment Account Deletion' WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                        mysqli_query($dbcon, "UPDATE Comments SET `ClosedOn` = '$updatedate' WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                        mysqli_query($dbcon, "UPDATE Comments SET `Deleted` = '1' WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                        mysqli_query($dbcon, "UPDATE Comments SET `ForReview` = '0' WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                        mysqli_query($dbcon, "UPDATE Comments SET `ClosedBy` = 'Parent Comment Author' WHERE Parent = '$comment->id'") OR die(mysqli_error($dbcon));
                    }
                    mysqli_query($dbcon, "UPDATE Comments SET `Closed` = '1' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `CloseType` = 'User Account Deletion' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `ClosedOn` = '$updatedate' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `Deleted` = '1' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `ForReview` = '0' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `ClosedBy` = 'Strategy Creator' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                    $countcoms++;
                }
            }
        }


        // Step 2 - Strategies
        if ($delstrats == "keep"){
            mysqli_query($dbcon, "UPDATE Alternatives SET `CreatedBy` = '$user->Name' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "UPDATE Alternatives SET `User` = '0' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        }
        if ($delstrats == "anon"){
            mysqli_query($dbcon, "UPDATE Alternatives SET `CreatedBy` = 'Anonymous' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "UPDATE Alternatives SET `User` = '0' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        }
        if ($delstrats == "delete"){
            $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
            if (mysqli_num_rows($stratsdb) > "0"){
                while ($strat = mysqli_fetch_object($stratsdb)) {
                    \Strategy\delete ($strat->id, $user);
                }
            }
        }

        // Step 3 - User Account

        // Deleting User Icon
        $targetpath = "images/userpics/".$user->id.".jpg";
        if (file_exists($targetpath)) {
            unlink($targetpath);
        }
        // Delete User Collection
        delete_collection($user->id);
        // Delete Battle.net account
        mysqli_query($dbcon, "DELETE FROM UserBnet WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        // Delete User Protocol
        mysqli_query($dbcon, "DELETE FROM UserProtocol WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        // Delete leaderboard entry
        mysqli_query($dbcon, "DELETE FROM Leaderboard WHERE User = '$user->id'") OR die(mysqli_error($dbcon));

        // Create Last Protocol entry
        $protcom = "User ID ".$user->id." - Name ".$user->Name;
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '1', 'Account Deleted by User', '$protcom')") OR die(mysqli_error($dbcon));

        // Delete User
        mysqli_query($dbcon, "DELETE FROM Users WHERE id = '$user->id'") OR die(mysqli_error($dbcon));

        ?>
        <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/index.php?sendtoast=accdeleted">
        <?
        die;
    }
}


// ============= END OF MODULE 8.2 =============












// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================














?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <?php echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle"><?php echo __("Account Settings"); ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('settings');
    ?>
</div>




<div class="blogentryfirst">

<div class="articlebottom"></div>

<table style="width:100%;">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>
    <td>

<?php // ============= MODULE 8.1 - Account Deletion Process - Step 1 ============= ?>
<?php if ($settingspage == "deleteacc") { ?>


<table width="85%" class="profilehl">
    <tr class="profile">
        <th width="5" class="profilehl">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Account Deletion Process - Step 1"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">


            <br><p class="blogodd" style="margin-left: 30px"><b><?php echo __("Warning!"); ?></b><br><br></p>

            <p class="blogodd"><?php echo __("Following this process will permanently remove your user account.<br>In the next step you can decide what will happen to your comments or strategies, in case you submitted any."); ?>

            <?php if ($user->Hash != "") { ?>
                <br><br><?php echo __("To continue, please enter your current password:"); ?><br><br>
                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccpass">
                <table>
                    <tr>
                    <td align="right"><p class="blogodd"><b><?php if ($delpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Password"); ?>:</b></td>
                    <td><img src="images/blank.png" width="5" height="1"/></td>
                    <td><input tabindex="1" placeholder="" type="password" name="delpass" value="" required></td>

                    <td><img src="images/blank.png" width="38" height="1"/></td>
                    <td><button type="submit" tabindex="2" class="comdelete"><?php echo __("Continue Deletion Process"); ?></button></td>
                    </form>
                    <form class="form-style-login" action="index.php?page=settings" method="post">
                    <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><?php echo __("Cancel"); ?></button></td>
                    </form>
                    </tr>

                    <?
                    if ($delpasserror == "true"){
                    echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$delpassprob.'</div></td></td></tr>';
                    }
                    ?>
                </table><br>

            <?php }
            else { ?>
                <br><br><?php echo __("Please confirm that you want to continue with the deletion"); ?>:<br><br>
                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccpass">
                <table>
                    <tr>
                    <td><img src="images/blank.png" width="38" height="1"/></td>
                    <td><button type="submit" tabindex="2" class="comdelete"><?php echo __("Continue Deletion Process"); ?></button></td>
                    </form>
                    <form class="form-style-login" action="index.php?page=settings" method="post">
                    <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><?php echo __("Cancel"); ?></button></td>
                    </form>
                    </tr>

                    <?
                    if ($delpasserror == "true"){
                    echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$delpassprob.'</div></td></td></tr>';
                    }
                    ?>
                </table>
            <?php } ?>
    </form>


    </td>
    </tr>

</table>
<br><br><br><br>
</td>
</tr>

<?
mysqli_close($dbcon);
echo "</body>";
die;
}
?>
<?php // ============= END OF MODULE 8.1 ============= ?>







<?php // ============= MODULE 8.2 - Account Deletion Process - Step 2 ============= ?>
<?php if ($settingspage == "delacc2") { ?>

<table width="85%" class="profilehl">
    <tr class="profile">
        <th width="5" class="profilehl">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Account Deletion Process - Step 2"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">

            <p class="blogodd">

            <?
            $commentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND Deleted != '1'");
            $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' AND Deleted != '1'");

            if (mysqli_num_rows($commentsdb) > "0" OR mysqli_num_rows($stratsdb) > "0") { ?>

            <br><p class="blogodd"><?php echo __("Before continuing, please select what should happen to some of your data"); ?>:
            <br><br>

                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccfinal">
                    <input type="hidden" name="delstring" value="<?php echo $user->DelHash ?>">
            <table border="0" style="margin-left: 80px">

            <?php if (mysqli_num_rows($commentsdb) > "0"){ ?>
                <tr><td colspan="3">
                    <p class="blogodd"><b><?php echo __("Your comments. You wrote in total"); ?>: <?php echo mysqli_num_rows($commentsdb) ?>
                </td>
                </tr>

                <tr>
                    <td><img src="images/blank.png" width="30" height="0"></td>
                    <td>
                        <ul class="radios">
                            <li>
                                <input class="blue" type="radio" id="keep" value="keep" name="delcoms" checked>
                                <label for="keep"></label>
                                <div class="check"></div>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <p class="blogodd"><?php echo __("Keep all your comments with your name."); ?>
                    </td>
                </tr>

                <tr>
                <td><img src="images/blank.png" width="20" height="0"></td>
                    <td>
                        <ul class="radios">
                            <li>
                                <input class="blue" type="radio" id="anon" value="anon" name="delcoms">
                                <label for="anon"></label>
                                <div class="check"></div>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <p class="blogodd"><?php echo __("Keep your comments with the name <i>Anonymous</i>."); ?>
                    </td>
                </tr>

                <tr>
                <td><img src="images/blank.png" width="20" height="0"></td>
                    <td>
                        <ul class="radios">
                            <li>
                                <input class="red" type="radio" id="delete" value="delete" name="delcoms">
                                <label for="delete"></label>
                                <div class="check"></div>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <p class="blogodd"><?php echo __("Delete all comments."); ?>
                    </td>
                </tr>

                <tr><td><br></td></tr>

            <?php } ?>


            <?php if (mysqli_num_rows($stratsdb) > "0"){ ?>
                <tr><td colspan="3">
                    <p class="blogodd"><b><?php echo __("Your strategies. You created a total of"); ?>: <?php echo mysqli_num_rows($stratsdb) ?></p>
                </td>
                </tr>

                <tr>
                    <td><img src="images/blank.png" width="30" height="0"></td>
                    <td>
                        <ul class="radios">
                            <li>
                                <input class="blue" type="radio" id="keepstrats" value="keep" name="delstrats" checked>
                                <label for="keepstrats"></label>
                                <div class="check"></div>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <p class="blogodd"><?php echo __("Keep strategies with your name as the creator."); ?>
                    </td>
                </tr>

                <tr>
                <td><img src="images/blank.png" width="20" height="0"></td>
                    <td>
                        <ul class="radios">
                            <li>
                                <input class="blue" type="radio" id="anonstrats" value="anon" name="delstrats">
                                <label for="anonstrats"></label>
                                <div class="check"></div>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <p class="blogodd"><?php echo __("Keep strategies with creator name <i>Anonymous</i>."); ?>
                    </td>
                </tr>

                <tr>
                <td><img src="images/blank.png" width="20" height="0"></td>
                    <td>
                        <ul class="radios">
                            <li>
                                <input class="red" type="radio" id="deletestrats" value="delete" name="delstrats">
                                <label for="deletestrats"></label>
                                <div class="check"></div>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <p class="blogodd"><?php echo __("Delete all your strategies including all comments submitted to them."); ?>
                    </td>
                </tr>

                <tr><td><br></td></tr>

            <?php } ?>
            </table>

            <table>
                <tr>
                <td><img src="images/blank.png" width="38" height="1"/></td>
                <td><button type="submit" tabindex="2" class="comdelete"><?php echo __("Finish account deletion"); ?></button></td>
                </form>
                <form class="form-style-login" action="index.php?page=settings" method="post">
                <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><?php echo __("Cancel"); ?></button></td>
                </form>
                </tr>
            </table>

            <?php }
            if (mysqli_num_rows($commentsdb) == "0" AND mysqli_num_rows($stratsdb) == "0") { ?>

            <br><p class="blogodd"><?php echo __("This is the last step of the account deletion.<br>If you confirm below, your account will be irrevocably deleted."); ?><br><br>

                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccfinal">
                    <input type="hidden" name="delstring" value="<?php echo $user->DelHash ?>">
                <table>
                    <tr>
                    <td><img src="images/blank.png" width="38" height="1"/></td>
                    <td><button type="submit" tabindex="2" class="comdelete"><?php echo __("I'm sure, delete my account"); ?></button></td>
                    </form>
                    <form class="form-style-login" action="index.php?page=settings" method="post">
                    <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><?php echo __("Cancel"); ?></button></td>
                    </form>
                    </tr>
                </table>
            <?php } ?>
    </td>
    </tr>

</table>
<br><br><br><br>
</td>
</tr>


<?
mysqli_close($dbcon);
echo "</body>";
die;
}
?>
<?php // ============= END OF MODULE 8.2 ============= ?>



<?php // ============= MODULE 3.2 - Battle.net authorization was successfull but bnet is associated with another xufu account ============= ?>
<?

if ($addbneterror == "alreadyregistered") {
    
    $usertwodb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$addbnetuser->User'");
    $usertwo = mysqli_fetch_object($usertwodb);
    
    $commentsonedb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' && Deleted != '1'");
    $comsone = mysqli_num_rows($commentsonedb);
    
    $commentstwodb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$usertwo->id' && Deleted != '1'");
    $comstwo = mysqli_num_rows($commentstwodb);
    
    $stratsonedb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' && Published = '1' && Deleted = '0'");
    $stratsone = mysqli_num_rows($stratsonedb);
    
    $stratstwodb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$usertwo->id' && Published = '1' && Deleted = '0'");
    $stratstwo = mysqli_num_rows($stratstwodb);
    
    if ($usertwo->UseWowAvatar == "0"){
        $usertwoicon = 'src="https://www.wow-petguide.com/images/pets/'.$usertwo->Icon.'.png"';
    }
    else if ($usertwo->UseWowAvatar == "1"){
        $usertwoicon = 'src="https://www.wow-petguide.com/images/userpics/'.$usertwo->id.'.jpg?lastmod?='.$usertwo->IconUpdate.'"';
    }
    
    ?>
    <table width="85%" class="profilehl">
        <tr class="profile">
            <th width="5" class="profilehl">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Battle.net Connection"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <?php echo __("The authorization was successful but there is another Xu-Fu account already connected to your Battle.net account."); ?><br>
                <?php echo __("You can select which one to use in the future in association with your Battle.net account:"); ?><br><br>
    
                <i><b><?php echo __("Please note"); ?>:</b></b></i> <?php echo __("If you connect your current account with Battle.net, your old account could be lost. <br>To check the account, log out and select -Login with Battle.net-"); ?><br><br>
    
    <center>
    
    <table width="570" border="0">
    
    <tr>
        <td width="250" style="vertical-align: top;">
            <center>
            <form name="loginform" action="index.php?page=settings" method="post">
            <input type="hidden" name="settingspage" value="addbnetone">
            <input type="hidden" name="regionselect" value="<?php echo $region ?>">
            <h6><b><?php echo __("This account"); ?>:</b><br>
    
            <table>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>
                                    <img class="usericonsmall" <?php echo $usericon ?> heigth="30" width="30" />
                                </td>
                                <td>
                                    <span class="username" style="text-decoration: none" rel="<?php echo $user->id ?>" value="<?php echo $user->id ?>"><p class="blogodd"><b><?php echo $user->Name ?></p></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><?php echo __("Registered"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><span name="time"><?php echo $user->regtime ?></span></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><?php echo __("Comments"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><?php echo $comsone ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><?php echo __("Strategies"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><?php echo $stratsone ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td colspan="2"><img src="images/blank.png" width="220" height="1"></td>
                </tr>
    
    
                <tr>
                    <td colspan="2">
                        <center><button tabindex="20" type="submit" class="bnetlogin" name="page" value="settings"><?php echo __("Move Battle.net connection to this account"); ?></button>
                    </td>
                </tr>
    
            </table>
            </form>
        </td>
    
        <td width="70"><hr class="vertical"><hr class="vertical"><hr class="vertical"><br><img src="images/blank.png" width="70" height="1"></td>
    
        <td width="250" style="vertical-align: top;">
            <center>
    
            <h6><b><?php echo __("The old account"); ?>:</b><br>
    
            <table>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>
                                    <img class="usericonsmall" <?php echo $usertwoicon ?> heigth="30" width="30" />
                                </td>
                                <td>
                                    <span class="username" style="text-decoration: none" rel="<?php echo $usertwo->id ?>" value="<?php echo $user->id ?>"><p class="blogodd"><b><?php echo $usertwo->Name ?></p></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><?php echo __("Registered"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><span name="time"><?php echo $usertwo->regtime ?></span></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><?php echo __("Comments"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><?php echo $comstwo ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><?php echo __("Strategies"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><?php echo $stratstwo ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td colspan="2"><img src="images/blank.png" width="220" height="1"></td>
                </tr>
    
                <tr>
                    <td colspan="2">
                        <p class="blogodd"><?php echo __("Has active Battle.net connection"); ?>
                    </td>
                </tr>
    
            </table>
            </form>
        </td>
    
    </tr>
    </table>
    
            </td>
        </tr>
    
    </table>
    <br><br><br><br><br>
    <script>updateAllTimes('time')</script>
<?
die;
} ?>
<?php // ============= END OF MODULE 3.2 ============= ?>







<?
// =======================================================================================================
// ================================== ?????? SUBMENU MODULES ??????? =============================================
// =======================================================================================================
// ================================== ?????? ALL OTHER MODULS ?????? =============================================
// =======================================================================================================
?>





<?php // ============= Begin of Account Settings ============= ?>


<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    <div style="width: 100%; padding: 3 0 3 5; font-family: MuseoSans-500,Roboto; color: white">
        <?php echo __("Account Settings"); ?>
    </div>
    
    

<?php // ============= MODULE 1 - Select new user name ============= ?>
<?
if ($user->NameChange > "0") { ?>


<table width="100%" style="margin-top: 5px" class="profile<?php if ($regnameerror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th class="profile<?php if ($regnameerror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Your Account Name"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">

            <?php echo __("Since you signed up through Battle.net, your BattleTag was entered as your account name. It is now visible to others when you post comments."); ?><br>
           <?php echo __("If you do not want your BattleTag shown you can change it here."); ?>
            <br><br>
            <i>
                <?php echo __("Please note that your account name can only be changed <b>once<b>."); ?>
            </i>
            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="namechange">

            <table>
                <tr>
                <td align="right"><p class="blogodd"><b><?php if ($regnameerror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("New name"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="5" placeholder="" type="text" name="username" value="<?php echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" maxlength="15" required>
                </td>
                <td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
                    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
                    <em><?php echo __("Nickname"); ?></em>
                    <?php echo __("Your nickname is the name under which your comments and info are shown.<br> The following restrictions are in place:"); ?><br>
                    <ul>
                        <li><?php echo __("Usernames must be between <b>2</b> and <b>15</b> characters long."); ?></li>
                        <li><?php echo __("Empty spaces and the following characters are not allowed:"); ?><br># < > [ ] | { } " ' / \ $ ?</li>
                        <li><?php echo __("No offensive names :P"); ?></li>
                    </ul>
                </span></a>
                </td>
                <td><img src="images/blank.png" width="20" height="1"/></td>
                <td><button type="submit" tabindex="6" class="comedit"><?php echo __("Save New Name"); ?></button></td>
                </tr>

                <?
                if ($regnameerror == "true"){
                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regnameprob.'</div></td></td></tr>';
                }
                ?>

                <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
        </form>

        </td>
    </tr>

</table>

<?php } ?>
<?php // ============= END OF MODULE 1 ============= ?>




<?php // ============= MODULE 2 - Adding a password for Battle.net Registered Users  ============= ?>
<?
if (!$user->Hash) { ?>

<table width="100%" style="margin-top: 5px" class="profile<?php if ($regpasserror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<?php if ($regpasserror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Password Protection"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">
            <?php echo __("Your account does not have a password associated. Logging in through Battle.net is perfectly fine. Adding a password gives you the additional option to login with your username and password instead."); ?>
            <br>
            <?php echo __("You can change your password at any time."); ?>
            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="addpw">

            <table>


                <tr>
                <td align="right"><p class="blogodd"><b><?php if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Password"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="10" placeholder="" type="password" id="passwordy" name="password" required></td>
                <td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
                    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
                    <em><?php echo __("Password"); ?></em>
                    <?php echo __("Your password has to be at least 6 characters long. It is advisable to use a complex password."); ?><br><br>
                    <?php echo __("Your password will be encrypted. No one, including the site admin, can see your real password."); ?><br>
                </span></a>

                </td>
                </tr>



                <tr>
                <td align="right"><p class="blogodd"><b><?php if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Repeat password"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="11" placeholder="" type="password" name="passwordrep" id="password" required></td>
                <td></td>
                <td><img src="images/blank.png" width="20" height="1"/></td>
                <td><button type="submit" tabindex="12" class="comedit"><?php echo __("Save Password"); ?></button></td>
                </tr>


                <?
                if ($regpasserror == "true"){
                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regpassprob.'</div></td></td></tr>';
                }
                ?>


                <tr><td></td><td></td><td><div id="capsWarning" class="registerError"><p class="commenteven"><?php echo __("Warning! Caps Lock is on!"); ?></div></td></tr>

                <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
            </form>
        </td>
    </tr>

</table>

<?php } ?>
<?php // ============= END OF MODULE 2 ============= ?>









<?php // ============= MODULE 4 - Language select ============= ?>


<table width="100%" style="margin-top: 5px" class="profile">
    <tr class="profile">
        <th class="profile">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Language Selection"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">

            <?php echo __("You can set your preferred language here."); ?> <br>
            <?php echo __("If a language is flagged as <i>incomplete</i> only spell and pet names are localized. Most other page assets will default to English."); ?>

            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="setlanguage">

            <table>
                <tr>
                <td align="right"><p class="blogodd"><b><?php echo __("Select language"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><select tabindex="25" class="petselect" name="setlanguage" onchange="this.form.submit()">
                    <option class="ctselect" value="en_US" <?php if ($user->Language == "en_US") {echo "selected";} ?>>English</option>
                    <option class="ctselect" value="de_DE" <?php if ($user->Language == "de_DE") {echo "selected";} ?>>Deutsch</option>
                    <option class="ctselect" value="es_ES" <?php if ($user->Language == "es_ES") {echo "selected";} ?>>Espa&#xF1;ol</option>
                    <option class="ctselect" value="fr_FR" <?php if ($user->Language == "fr_FR") {echo "selected";} ?>>Fran&#xE7;ais</option>
                    <option class="ctselect" value="ru_RU" <?php if ($user->Language == "ru_RU") {echo "selected";} ?>>&#x420;&#x443;&#x441;&#x441;&#x43A;&#x438;&#x439;</option>
                    <option class="ctselect" value="pt_BR" <?php if ($user->Language == "pt_BR") {echo "selected";} ?>>Portugu&#xEA;s (incomplete)</option>
                    <option class="ctselect" value="it_IT" <?php if ($user->Language == "it_IT") {echo "selected";} ?>>Italiano (incomplete)</option>
                    <option class="ctselect" value="ko_KR" <?php if ($user->Language == "ko_KR") {echo "selected";} ?>>&#xD55C;&#xAD6D;&#xC5B4; (incomplete)</option>
                    <option class="ctselect" value="zh_TW" <?php if ($user->Language == "zh_TW") {echo "selected";} ?>>&#x4E2D;&#x6587; (incomplete)</option>
                </td>
                </tr>

                <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
        </form>

        </td>
    </tr>

</table>

<?php // ============= END OF MODULE 4 ============= ?>









<?php // ============= MODULE 6.1 - Add email address  ============= ?>
<?
if (!$user->Email) { ?>


<table width="100%" style="margin-top: 5px" class="profile<?php if ($regmailerror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<?php if ($regmailerror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Add Your Email Address"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">
            <?php echo __("Here you can add an email address to your account. Should you forget your password, your email address can be used to recover your account."); ?>
            <br>
            <?php echo __("You can change or remove it at any time."); ?>
            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="addemail">

            <table>

                <tr>
                <td align="right"><p class="blogodd"><b><?php if ($regmailerror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Your email address"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="150" placeholder="" type="email" name="addemail" value="<?php echo $outputemail ?>" required></td>

                <td><img src="images/blank.png" width="38" height="1"/></td>
                <td><button type="submit" tabindex="151" class="comedit"><?php echo __("Save Email"); ?></button></td>
                </tr>

                <?
                if ($regmailerror == "true"){
                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regmailprob.'</div></td></td></tr>';
                }
                ?>

              <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
            </form>
        </td>
    </tr>

</table>

<?php } ?>
<?php // ============= END OF MODULE 6.1 ============= ?>




<?php // ============= MODULE 6.2 - Change or remove email address  ============= ?>
<?
if ($user->Email) { ?>

<table width="100%" style="margin-top: 5px" class="profile<?php if ($regmailerror == "true"){ echo "hl"; } ?>">
        <tr class="profile">
                <th width="5" class="profile<?php if ($regmailerror == "true"){ echo "hl"; } ?>">
                        <table>
                                <tr>
                                        <td><img src="images/userdd_settings_grey.png"></td>
                                        <td><img src="images/blank.png" width="5" height="1"></td>
                                        <td><p class="blogodd"><b><?php echo __("Change Email Address"); ?></td>
                                </tr>
                        </table>
                </th>
        </tr>

        <tr class="profile">
                <td class="profile">
                        <p class="blogodd">

                        <?php echo __("Your currently saved email address is"); ?>: <b><?php echo $user->Email ?></b><br><br>

                        <form class="form-style-register" action="index.php?page=settings" method="post">
                                <input type="hidden" name="settingspage" value="changemail">

                        <table>

                                <tr>
                                <td align="right"><p class="blogodd"><b><?php if ($regmailerror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("New email address"); ?>:</b></td>
                                <td><img src="images/blank.png" width="5" height="1"/></td>
                                <td><input tabindex="160" placeholder="" type="email" name="addemail" value="<?php echo $outputemail ?>" required></td>

                                <td><img src="images/blank.png" width="38" height="1"/></td>
                                <td><button type="submit" tabindex="161" class="comedit"><?php echo __("Update Email"); ?></button></form></td>

                                <td><img src="images/blank.png" width="10" height="1"/></td>
                                <form class="form-style-register" action="index.php?page=settings" method="post">
                                <input type="hidden" name="settingspage" value="removemail">
                                <td><button type="submit" tabindex="162" class="comdelete"><?php echo __("Remove Email"); ?></button></td>
                                </tr>

                                <?
                                if ($regmailerror == "true"){
                                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regmailprob.'</div></td></td></tr>';
                                }
                                ?>

                            <tr>
                                        <td><img src="images/blank.png" width="150" height="1"/></td>
                                </tr>

                        </table>
                        </form>
                </td>
        </tr>

</table>

<?php } ?>
<?php // ============= END OF MODULE 6.2 ============= ?>








<?php // ============= MODULE 7 - Change password  ============= ?>
<?
if ($user->Hash) { ?>


<table width="100%" style="margin-top: 5px" class="profile<?php if ($changepasserror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<?php if ($changepasserror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><?php echo __("Change Password");  ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">
                <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="changepw">

            <table>

                <tr>
                <td align="right"><p class="blogodd"><b><?php if ($changepasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("New password"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="170" placeholder="" type="password" id="passwordy" name="password" required></td>
                <td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
                    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
                    <em><?php echo __("Password"); ?></em>
                    <?php echo __("Your password has to be at least 6 characters long. It is advisable to use a complex password."); ?><br><br>
                    <?php echo __("Your password will be encrypted. No one, including the site admin, can see your real password."); ?><br>
                </span></a>

                </td>
                </tr>


                <tr>
                <td align="right"><p class="blogodd"><b><?php if ($changepasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Repeat password"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="171" placeholder="" type="password" name="passwordrep" id="passwordz" required></td>
                <td></td>
                <td><img src="images/blank.png" width="20" height="1"/></td>
                <td><button type="submit" tabindex="172" class="comedit"><?php echo __("Save Password"); ?></button></td>
                </tr>


                <?
                if ($changepasserror == "true"){
                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$changepassprob.'</div></td></td></tr>';
                }
                ?>


                <tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><?php echo __("Warning! Caps Lock is on!"); ?></div></td></tr>

                <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
            </form>
        </td>
    </tr>

</table>

<?php } ?>
<?php // ============= END OF MODULE 7 ============= ?>


</div>
<?php // ============= End of Account Settings ============= ?>




<?php // ============= Begin of Battle.net Options ============= ?>


<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    <div style="width: 100%; padding: 3 0 3 5; font-family: MuseoSans-500,Roboto; color: white">
        <?php echo __("Battle.net Options"); ?>
    </div>

    <?php // ============= MODULE - Battle.net is authenticated, but no access to WoW Characters is given ============= ?>
    <?
    if ($bnetuser->WoWAccess == "0"){
    ?>
    <table width="100%" class="profile" style="margin-top: 5px">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Missing Battle.net WoW Access"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <?php echo __("Your Battle.net account is successfully linked with Xu-Fu, but you chose to grant no access to WoW data, which is limited to the list of your characters. If this was intentional, all is well and you don't need to do anything."); ?><br>
                <?php echo __("It does mean, however, that Xu-Fu cannot import your pet collection automatically, and you will not be able to chose a character picture as your avatar."); ?><br>
                <?php echo __("If you want to change this, follow these steps"); ?>:
            <br>
            <ol class="blogodd">
                <li><?php echo __("Visit your"); ?> <a class="wowhead" href="https://<?php echo $bnetuser->region ?>.battle.net/account/management/"></a><?php echo __("Battle.net Account Settings"); ?></a></li>
                <li><?php echo __("Go to Security -> Approved Apps"); ?></li>
                <li><?php echo __("Remove Xu-Fu from the list"); ?></li>
                <li><?php echo __("Log out of wow-petguide.com and back in with Battle.net"); ?></li>
                <li><?php echo __("When prompted to authorize Xu-Fu again, make sure the tick box is active at -Your World of Warcraft Profile-."); ?></li>
            </ol>
        </td>
        </tr>
    </table>
    
    <?php } ?>
    <?php // ============= END OF MODULE 0.2 ============= ?>

    
    
    
    <?php // ============= MODULE - Add Battle.net to account ============= ?>
    <?
    $bnetdb = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id'");
    if (mysqli_num_rows($bnetdb) == "0" && $addbneterror != "alreadyregistered") { ?>
    
    
    <table width="100%" class="profile" style="margin-top: 5px">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Battle.net Connection"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <?php echo __("Linking your Battle.net Account with your Xu-Fu Account is optional, but brings a few advantages:"); ?>
                <ul class="blogodd">
                <li><?php echo __("You can login with Battle.net, without the need of a password."); ?></li>
                <li><?php echo __("Xu-Fu can update your pet collection automaticallyn, no manual refreshing required."); ?></li>
                <li><?php echo __("You can chose a character picture as your avatar."); ?></li>
                </ul>
                <p class="blogodd"><?php echo __("Xu-Fu will not receive any of your personal information by connecting with Battle.net (such as your password, email address or any billing information)."); ?>
                <br><?php echo __("To link your account, select your account region and click on the button:"); ?>
                <br><br><center>
    
                <form name="loginform" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="addbnet">
    
        <table width="400">
            <tr>
                <td rowspan="2"><button tabindex="15" type="submit" class="bnetlogin" name="page" value="settings">Connect Battle.net Account</button></td>
    
                <td>
                    <table>
                        <tr>
                        <td><img src="images/blank.png" width="20" height="0"></td>
                            <td>
                                <ul class="radios">
                                    <li>
                                        <input class="blue" type="radio" id="standard" value="standard" name="regionselect" checked>
                                        <label for="standard"></label>
                                        <div class="check"></div>
                                    </li>
                                </ul>
                            </td>
                            <td>
                                <p class="blogodd"><?php echo __("US/EU/KR/TW"); ?>
                            </td>
                        </tr>
                    
                        <tr>
                        <td><img src="images/blank.png" width="20" height="0"></td>
                            <td>
                                <ul class="radios">
                                    <li>
                                        <input class="blue" type="radio" id="china" value="china" name="regionselect">
                                        <label for="china"></label>
                                        <div class="check"></div>
                                    </li>
                                </ul>
                            </td>
                            <td>
                                <p class="blogodd"><?php echo __("China"); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    
    
    </form>
    
    
    </td>
    </tr>
    </table>
    <?php } ?>
    <?php // ============= END OF MODULE 3.1 ============= ?>




    <?php // ============= MODULE - Change Battle.net Region ============= ?>
    <?
    if ($bnetuser && $bnetuser->Region != 'cn'){
    ?>
    <table width="100%" class="profile" style="margin-top: 5px">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Change Battle.net Region"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <?php echo __("Your region for Battle.net is set to:").' <b>'.strtoupper($bnetuser->Region).'</b>'; ?><br>
                <?php echo __("You can change your region manually below. This might be useful if you have characters on multiple regions."); ?><br>
                <br>

                <?php if ($user->UseWowAvatar == 1) {
                    echo '<b>=> '.__("Changing the region will remove your avatar icon!").'</b><br>';
                } ?>
                <?php if ($collection) {
                    echo '<b>=> '.__("Changing the region will remove your saved pet collection from Xu-Fu's!").'</b><br>';
                } ?>
                <br>
                
                <form action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="change_region">

                <table border="0">

                <?php foreach (['us', 'eu', 'kr', 'tw'] as $region)
                {
                    switch ($region) {
                        case "us":
                            $regiontitle = "United States";
                            break;
                        case "eu":
                            $regiontitle = "Europe";
                            break;
                        case "kr":
                            $regiontitle = "Korea";
                            break;
                        case "tw":
                            $regiontitle = "Taiwan";
                            break;
                    }
                    if ($region != $bnetuser->Region) { ?>
                        <tr>
                            <td style="width:30; padding-left: 20px">
                                <ul class="radios">
                                    <li>
                                        <input tabindex="11" class="blue" type="radio" id="<?php echo $region ?>" value="<?php echo $region ?>" name="region" required>
                                        <label for="<?php echo $region ?>"></label>
                                        <div class="check"></div>
                                    </li>
                                </ul>
                            </td>
                            <td>
                                <p class="blogoddnowrap"><b><?php echo __($regiontitle) ?>
                            </td>
                    </tr>
                    <?php }
                } ?>

                <tr>
                <td colspan="3" style="padding-top: 10px">
                    <button type="submit" tabindex="12" style="margin-left: 30px" class="comedit"><?php echo __("Change Region"); ?></button>
                </td>
                </tr>


                </table>
            </form>

            <script type = "text/javascript">
                $(".chosen-select").chosen({width: 230});
            </script>
        </td>
        </tr>
    </table>
    
    <?php } ?>
    <?php // ============= END OF MODULE 0.2 ============= ?>
    
    


    <?php // ============= MODULE - Unlink Battle.net Connection ============= ?>
    <?
    if ($bnetuser){
    ?>
    <table width="100%" class="profile" style="margin-top: 5px">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Unlink Battle.net Connection"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <?php if (!$user->Hash) { 
                    echo __("Your account does not have a password set. Please set one in the option above. Otherwise, unlinking Battle.net would mean you cannot login to your account anymore.");
                 }
                else { ?>
                <?php echo __("You have an active Battle.net connection to log in with Xu-Fu, use a character icon as your avatar and easily import your collection. You can remove this link by clicking the button below."); ?>
                <br><br>
                 <?php echo __("This will also remove your current pet collection from Xu-Fu and you will have to manually link a character to reimport it."); ?>

                <br>

                    <button data-remodal-target="modal_unlink" style="margin-left: 30px; margin-top: 15px;" class="comdelete"><?php echo __("Unlink Battle.net"); ?></button>
                    <br>                
                    <div class="remodal remodalstratedit remodaldelete" data-remodal-id="modal_unlink">
                        <table style="width: 400px" class="profile">
                            <tr class="profile">
                                <th colspan="2" style="width: 100%" class="profile">
                                    <table>
                                        <tr>
                                            <td><img src="images/icon_report.png" style="padding-right: 5px"></td>
                                            <td><p class="blogodd"><span style="white-space: nowrap;"><b><?php echo __("Confirm Unlinking"); ?></span></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                
                            <tr class="profile">
                                <td class="collectionbordertwo" colspan="2" style="font-family: MuseoSans-500; font-size: 14px">
                                    <div id="del_part1">
                                        <center><br><b><?php echo __("Are you sure you want to unlink your Battle.net connection?"); ?></b><br><br>
                                        <form class="form-style-register" action="index.php?page=settings" method="post">
                                            <input type="hidden" name="settingspage" value="unlink_bnet">
                                            <button style="margin-left: 30px; margin-top: 15px;" type="submit" tabindex="151" class="redlarge"><?php echo __("Unlink Battle.net"); ?></button>
                                        </form>
                                        <br>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                
                    <script>
                    var options = {
                        hashTracking: false
                    };
                    $('[data-remodal-id=modal_unlink]').remodal(options);
                    </script>
                <?php } ?>
            <br>
        </td>
        </tr>
    </table>
    
    <?php } ?>
    <?php // ============= END OF MODULE ============= ?>

    





    
    <?php // ============= END OF BATTLE.NET OPTIONS SECTION ============= ?>
</div>








<?php // ============= MODULE: Tag priority in alternatives menu ============= ?>
<script src="https://www.wow-petguide.com/data/jquery-sortable.js"></script>


<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    <div style="width: 100%; padding: 3 0 3 5; font-family: MuseoSans-500,Roboto; color: white">
        <?php echo __("Page Browsing"); ?>
    </div>

    <table width="100%" class="profile" style="margin-top: 5px">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Priority of tags"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <?php echo __("Decide which tags should be considered more important than others when showing alternative strategies."); ?><br>
                <?php echo __("Tags in the green box will be moved higher up, while those in the red box will be moved down."); ?>
                <br><br>
                <?php echo __("Drag the tags between and within brackets to set their importance."); ?>
                <br>
                <br>
                
                <div style="width: 200px; float: left; margin-left: 20px">
                    <center><p class="blogodd"><b><?php echo __("Preferred"); ?></b></center>
                    <ol class='tags_sorting' id='tags_one' style='background-color: #a3ba87'>
                       <?php if ($tagprio[0][0] != "") {
                            foreach ($tagprio[0] as $key => $value) {
                                echo '<li id="'.$all_tags[$value]['ID'].'" class="tags_item">'.$all_tags[$value]['Name'].'</li>';
                            }
                        } ?>                
                   </ol>   
                </div>
                
                <div style="width: 200px; float: left; margin-left: 20px">
                     <center><p class="blogodd"><b><?php echo __("Neutral"); ?></b></center>    
                     <ol class='tags_sorting' id='tags_two' style='background-color: #aeaeae'>
                       <?php if ($tagprio[1][0] != "") {
                            foreach ($tagprio[1] as $key => $value) {
                                echo '<li id="'.$all_tags[$value]['ID'].'" class="tags_item">'.$all_tags[$value]['Name'].'</li>';
                            }
                       } ?>  
                    </ol>   
                </div>
                
                <div style="width: 200px; float: left; margin-left: 20px">
                    <center><p class="blogodd"><b><?php echo __("Unfavored"); ?></b></center>
                    <ol class='tags_sorting' id='tags_three' style='background-color: #ba8787'>
                       <?php if ($tagprio[2][0] != "") {
                            foreach ($tagprio[2] as $key => $value) {
                               echo '<li id="'.$all_tags[$value]['ID'].'" class="tags_item">'.$all_tags[$value]['Name'].'</li>';
                            }
                       } ?>  
                    </ol>   
                </div>
    
                <script>
                var adjustment;
                
                $("ol.tags_sorting").sortable({
                  group: 'tags_sorting',
                  pullPlaceholder: false,
                  // animation on drop
                  onDrop: function  ($item, container, _super) {
                    var $clonedItem = $('<li/>').css({height: 0});
                    $item.before($clonedItem);
                    $clonedItem.animate({'height': $item.height()});
                
                    $item.animate($clonedItem.position(), function  () {
                      $clonedItem.detach();
                      _super($item, container);
                    });
                  },
                
                  // set $item relative to cursor position
                  onDragStart: function ($item, container, _super) {
                    var offset = $item.offset(),
                        pointer = container.rootGroup.pointer;
                
                    adjustment = {
                      left: pointer.left - offset.left,
                      top: pointer.top - offset.top
                    };
                
                    _super($item, container);
                  },
                  onDrag: function ($item, position) {
                    $item.css({
                      left: position.left - adjustment.left,
                      top: position.top - adjustment.top
                    });
                  }
                });
                </script>
    
                <div style="width: 100%; float: left; margin-left: 20px; margin-bottom: 20px">
                    <button onclick="save_tag_settings(<?php echo $user->id ?>,<?php echo $user->ComSecret ?>,)" type="submit" tabindex="36" class="comedit"><?php echo __("Save") ?></button>
                </div>
                
            </td>
        </tr>
    
    </table>
    
    
    
    
    
    

    <table width="100%" style="margin-top: 5px" class="profile">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Beta Features"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
                <td class="profile">
                    <p class="blogodd">
                    <?php echo __("Aranesh is regularly adding new features. If you want to test them before they are released, activate this option."); ?><br>
                    <b><?php echo __("Warning!"); ?></b> - <?php echo __("activating beta features might replace parts of the page with versions that are not fully functional!"); ?><br><br>
                    <?php echo __("To learn what is currently in development, contact"); ?> <span class="username tooltipstered" style="text-decoration: none" rel="2" value="<?php echo $user->id ?>"><a target="_blank" href="?user=2" class="usernamelink comheadbright com_role_99_bright">Aranesh</a></span> (<a class="wowhead" href="https://www.wow-petguide.com/index.php?page=writemsg&to=2" target="_blank"><?php echo __("Send message"); ?></a> / <a class="wowhead" href="https://discord.gg/z4dxYUq" target="_blank"><?php echo __("Discord"); ?></a>)<br>
                    <br>
    
                    <table>
                        <tr>
                        <td align="right"><p class="blogodd"><b><?php echo __("Beta Features"); ?>:</b></td>
                        <td><img src="images/blank.png" width="5" height="1"/></td>
                        <td>
                            <?php echo __("Off"); ?>
                        </td>
                        <td>
                            <div class="armoryswitch">
                                <input type="checkbox" class="armoryswitch-checkbox" id="setbeta" onchange="change_beta_setting('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>');" <?php if ($usersettings['BetaAccess'] == "on" ) { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="setbeta">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <?php echo __("On"); ?>
                        </td>
                        </tr>
                        <tr>
                            <td><img src="images/blank.png" width="150" height="1"/></td>
                        </tr>
                    </table>
    
                    <br>
                </td>
        </tr>
    
    </table>
    
    
    
</div>







<?php // ============= MODULE 8 - Delete Account  ============= ?>

<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    
    <table width="100%" class="profile">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><?php echo __("Delete Account"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
                <td class="profile">
                        <p class="blogodd">
    
                        <form class="form-style-register" action="index.php?page=settings" method="post">
                                <input type="hidden" name="settingspage" value="deleteacc">
                                <button style="margin-left: 30px; margin-top: 15px;" type="submit" tabindex="151" class="comdelete"><?php echo __("Start Account Deletion Process"); ?></button>
                        </form>
                </td>
        </tr>
    
    </table>

</div>


<?php // ============= END OF MODULE 8 ============= ?>






<br><br><br><br>

</div>

<?
switch ($sendtoast) {
    case "pwadded":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Your password was saved.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "namechanged":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Your Username was changed successfully.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "addbnet":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Your Battle.net Account was connected successfully!").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregfail":
        echo '<script type="text/javascript">$.growl.error({ message: "'.__("The Battle.net authorization was declined. To login using your Battle.net Account please try again and authorize Xu-Fu.<br>Note: Your personal data (password, email address) will never be shared by Battle.net to authorized apps.").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregsuccess":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Your Battle.net Account Authorization was successful. Thank you!").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "bnetapierror":
        echo '<script type="text/javascript">$.growl.error({ message: "'.__("There was a problem with the Battle.net Account service, most likely a timeout or perhaps the servers are in maintenance. Please try again later.").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "emailadded":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Your email address was saved successfully.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "mailremoved":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Email address removed.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "langchanged":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Language changed.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "genericerror":
        echo '<script type="text/javascript">$.growl.error({ message: "'.__("There was an error processing your data, I am sorry. Please try again.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "nowowaccess":
        echo '<script type="text/javascript">$.growl.error({ message: "'.__("Xu-Fu has no access to your WoW character list. Adding a character picture as your avatar is therefore not possible. <br>Please check below for instructions on how to fix this.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "bnet_unlinked":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Battle.net Connection has been removed.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "region_changed":
        echo '<script type="text/javascript">$.growl.notice({ message: "'.__("Battle.net Region has been changed.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
}



if ($urlchanger){
    echo '<script type="text/javascript" lang="javascript">';
    echo 'window.history.replaceState("object or string", "Title", "'.$urlchanger.'");';
    echo '</script>';
}
mysqli_close($dbcon);
echo "</body>";
die;