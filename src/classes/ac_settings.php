<?php

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


$alltagsdb = mysqli_query($dbcon, "SELECT * FROM StrategyTags ORDER BY DefaultPrio");
while ($row_tag = mysqli_fetch_object($alltagsdb)) {
    $alltags[$row_tag->id]['id'] = $row_tag->id;
    $alltags[$row_tag->id]['Name'] = _("Tag_".$row_tag->PO_Name);
    $alltags[$row_tag->id]['DefaultPrio'] = $row_tag->DefaultPrio;
}

if ($user->TagPrio == "") {
    foreach ($alltags as $key => $value) {
        $cutprio = explode("-", $value['DefaultPrio']);
        $tagprio[$cutprio[0]][$cutprio[1]] = $value['id'];
    }  
}
else {
    $test_tags = $alltags;
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
            $cutprio = explode("-", $alltags[$value['id']]['DefaultPrio']);
            if ($cutprio[0] == "0") {
                $tagprio[0][$countone] = $value['id'];
                $countone++;
            }
            if ($cutprio[0] == "1") {
                $tagprio[1][$counttwo] = $value['id'];
                $counttwo++;
            }
            if ($cutprio[0] == "2") {
                $tagprio[2][$countthr] = $value['id'];
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
    $regnameprob = _("UR_ErrNameDupe");
    }

    if (mb_strlen($subname) > "15"){
    $regnameerror = "true";
    if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
    $regnameprob = $regnameprob." "._("UR_ErrNameLength");
    }

    if (mb_strlen($subname) < "2" && $firstclick != "true" && $directload != "true"){
    $regnameerror = "true";
    if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
    $regnameprob = $regnameprob." "._("UR_ErrNameShort");
    }

    if (preg_match('/[\'\/#\$\{\}\[\ \]\|\<\>\?\"\\\]/', $subname))
    {
    $regnameerror = "true";
    if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
    $regnameprob = $regnameprob." "._("UR_ErrNameChars")."<br># < > [ ] | { } \" ' / \ $ ?";
    }

    if (filter_var($subname, FILTER_VALIDATE_EMAIL)) {
    $regnameerror = "true";
    $regnameprob = _("UR_ErrNameIsMail");
    }

    if ($user->NameChange != "1") {
    $regnameerror = "true";
    $regnameprob = _("AS_ErrNameCh");
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
        $regpassprob = _("UR_ErrPassShort");
    }
    if ($pass != $passrep){
        $regpasserror = "true";
        $regpassprob = _("UR_ErrPassNomatch");
    }
    if ($pass == $user->Name ){
        $regpasserror = "true";
        $regpassprob = _("UR_ErrPassIsName");
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
    // SET REGION
    $regionselect = strtolower($_POST['regionselect']);
    if ($regionselect == "standard") {
        $region = "us";
    }
    else if ($regionselect == "china") {
        $region = "cn";
    }
    if (!$region){
        $region = $user->Region;
    }
    
    $oauth = new \BattleNet\OAuth ($region, 'addbnet_'.$source, '/index.php?page=settings');

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
    // access denied by user or undefined error
    $sendtoast = 'bnetregfail';
  }
    // echo '<script>window.history.replaceState("object or string", "Title", "?page=settings");</script>';
}




// Here is the part where account 1 is selected to move Battle.net over

if ($settingspage == "addbnetone" OR $command == "addbnetone") {

  try
  {
    $region = strtolower($_POST['regionselect']);
    
    $oauth = new \BattleNet\OAuth ($region, 'addbnetone', '/index.php?page=settings');

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
        $regmailprob = _("UR_ErrMailInvalid");
    }

    if ($regmailerror != "true") {
        $checkuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$submail'");
        if (mysqli_num_rows($checkuserdb) > "0") {
            $regmailerror = "true";
            $regmailprob = _("UR_ErrMailDupe");
        }
        if (!filter_var($submail, FILTER_VALIDATE_EMAIL)) {
            $regmailerror = "true";
            $regmailprob = _("UR_ErrMailInvalid");
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
        $regmailprob = _("UR_ErrMailInvalid");
    }

    if ($regmailerror != "true") {
        $checkuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$submail'");
        if (mysqli_num_rows($checkuserdb) > "0") {
            $regmailerror = "true";
            $regmailprob = _("UR_ErrMailDupe");
        }
        if (!filter_var($submail, FILTER_VALIDATE_EMAIL)) {
            $regmailerror = "true";
            $regmailprob = _("UR_ErrMailInvalid");
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
        $changepassprob = _("UR_ErrPassShort");
    }
    if ($pass != $passrep){
        $changepasserror = "true";
        $changepassprob = _("UR_ErrPassNomatch");
    }
    if ($pass == $user->Name ){
        $changepasserror = "true";
        $changepassprob = _("UR_ErrPassIsName");
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
            $delpassprob = _("AC_PWWrong1");
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
                $countstrats = "0";
                while ($countstrats < mysqli_num_rows($stratsdb)) {
                    $strat = mysqli_fetch_object($stratsdb);

                    $updatedate = date('Y-m-d H:i:s');
                    mysqli_query($dbcon, "UPDATE Comments SET `Closed` = '1' WHERE Category = '2' AND SortingID = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `CloseType` = 'Strategy Deleted by Creator' WHERE Category = '2' AND SortingID = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `ClosedOn` = '$updatedate' WHERE Category = '2' AND SortingID = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `Deleted` = '1' WHERE Category = '2' AND SortingID = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `ForReview` = '0' WHERE Category = '2' AND SortingID = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "UPDATE Comments SET `ClosedBy` = 'Strategy Creator' WHERE Category = '2' AND SortingID = '$strat->id'") OR die(mysqli_error($dbcon));

                    mysqli_query($dbcon, "DELETE FROM Strategy WHERE SortingID = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "DELETE FROM UserAttempts WHERE Strategy = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "DELETE FROM UserFavStrats WHERE Strategy = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "DELETE FROM UserStratRating WHERE Strategy = '$strat->id'") OR die(mysqli_error($dbcon));
                    mysqli_query($dbcon, "DELETE FROM Alternatives WHERE id = '$strat->id'") OR die(mysqli_error($dbcon));
                    $countstrats++;
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
    <img class="ut_icon" width="84" height="84" <? echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle"><? echo _("AS_Title"); ?></h></td>
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

<? // ============= MODULE 8.1 - Account Deletion Process - Step 1 ============= ?>
<? if ($settingspage == "deleteacc") { ?>


<table width="85%" class="profilehl">
    <tr class="profile">
        <th width="5" class="profilehl">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_AccDelSt1"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">


            <br><p class="blogodd" style="margin-left: 30px"><b><? echo _("AS_AccDelWarn"); ?></b><br><br></p>

            <p class="blogodd"><? echo _("AS_AccDelTx1"); ?>

            <? if ($user->Hash != "") { ?>
                <br><br><? echo _("AS_AccDelTx2"); ?><br><br>
                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccpass">
                <table>
                    <tr>
                    <td align="right"><p class="blogodd"><b><? if ($delpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("UL_LogPass"); ?>:</b></td>
                    <td><img src="images/blank.png" width="5" height="1"/></td>
                    <td><input tabindex="1" placeholder="" type="password" name="delpass" value="" required></td>

                    <td><img src="images/blank.png" width="38" height="1"/></td>
                    <td><button type="submit" tabindex="2" class="comdelete"><? echo _("AS_AccDelBTC"); ?></button></td>
                    </form>
                    <form class="form-style-login" action="index.php?page=settings" method="post">
                    <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><? echo _("FormButtonCancel"); ?></button></td>
                    </form>
                    </tr>

                    <?
                    if ($delpasserror == "true"){
                    echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$delpassprob.'</div></td></td></tr>';
                    }
                    ?>
                </table><br>

            <? }
            else { ?>
                <br><br><? echo _("AS_AccDelInst1"); ?>:<br><br>
                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccpass">
                <table>
                    <tr>
                    <td><img src="images/blank.png" width="38" height="1"/></td>
                    <td><button type="submit" tabindex="2" class="comdelete"><? echo _("AS_AccDelBTC"); ?></button></td>
                    </form>
                    <form class="form-style-login" action="index.php?page=settings" method="post">
                    <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><? echo _("FormButtonCancel"); ?></button></td>
                    </form>
                    </tr>

                    <?
                    if ($delpasserror == "true"){
                    echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$delpassprob.'</div></td></td></tr>';
                    }
                    ?>
                </table>
            <? } ?>
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
<? // ============= END OF MODULE 8.1 ============= ?>







<? // ============= MODULE 8.2 - Account Deletion Process - Step 2 ============= ?>
<? if ($settingspage == "delacc2") { ?>

<table width="85%" class="profilehl">
    <tr class="profile">
        <th width="5" class="profilehl">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_AccDelSt2"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">

            <p class="blogodd">

            <?
            $commentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND Deleted != '1'");
            $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id'");

            if (mysqli_num_rows($commentsdb) > "0" OR mysqli_num_rows($stratsdb) > "0") { ?>

            <br><p class="blogodd"><? echo _("AS_AccDelInst2"); ?>:
            <br><br>

                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccfinal">
                    <input type="hidden" name="delstring" value="<? echo $user->DelHash ?>">
            <table border="0" style="margin-left: 80px">

            <? if (mysqli_num_rows($commentsdb) > "0"){ ?>
                <tr><td colspan="3">
                    <p class="blogodd"><b><? echo _("AS_AccDelInfCom"); ?>: <? echo mysqli_num_rows($commentsdb) ?>
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
                        <p class="blogodd"><? echo _("AS_AccDelComOpt1"); ?>
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
                        <p class="blogodd"><? echo _("AS_AccDelComOpt2"); ?>
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
                        <p class="blogodd"><? echo _("AS_AccDelComOpt3"); ?>
                    </td>
                </tr>

                <tr><td><br></td></tr>

            <? } ?>


            <? if (mysqli_num_rows($stratsdb) > "0"){ ?>
                <tr><td colspan="3">
                    <p class="blogodd"><b><? echo _("AS_AccDelInfStrat"); ?>: <? echo mysqli_num_rows($stratsdb) ?></p>
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
                        <p class="blogodd"><? echo _("AS_AccDelStratOpt1"); ?>
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
                        <p class="blogodd"><? echo _("AS_AccDelStratOpt2"); ?>
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
                        <p class="blogodd"><? echo _("AS_AccDelStratOpt3"); ?>
                    </td>
                </tr>

                <tr><td><br></td></tr>

            <? } ?>
            </table>

            <table>
                <tr>
                <td><img src="images/blank.png" width="38" height="1"/></td>
                <td><button type="submit" tabindex="2" class="comdelete"><? echo _("AS_AccDelBTFin"); ?></button></td>
                </form>
                <form class="form-style-login" action="index.php?page=settings" method="post">
                <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><? echo _("FormButtonCancel"); ?></button></td>
                </form>
                </tr>
            </table>

            <? }
            if (mysqli_num_rows($commentsdb) == "0" AND mysqli_num_rows($stratsdb) == "0") { ?>

            <br><p class="blogodd"><? echo _("AS_AccDelInst3"); ?><br><br>

                <form class="form-style-register" action="index.php?page=settings" method="post">
                    <input type="hidden" name="settingspage" value="delaccfinal">
                    <input type="hidden" name="delstring" value="<? echo $user->DelHash ?>">
                <table>
                    <tr>
                    <td><img src="images/blank.png" width="38" height="1"/></td>
                    <td><button type="submit" tabindex="2" class="comdelete"><? echo _("AS_AccDelBTEnd"); ?></button></td>
                    </form>
                    <form class="form-style-login" action="index.php?page=settings" method="post">
                    <td><button style="margin-left: 15px;" type="submit" tabindex="3" class="comsubmit"><? echo _("FormButtonCancel"); ?></button></td>
                    </form>
                    </tr>
                </table>
            <? } ?>
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
<? // ============= END OF MODULE 8.2 ============= ?>



<? // ============= MODULE 3.2 - Battle.net authorization was successfull but bnet is associated with another xufu account ============= ?>
<?

if ($addbneterror == "alreadyregistered") {
    
    $usertwodb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$addbnetuser->User'");
    $usertwo = mysqli_fetch_object($usertwodb);
    
    $commentsonedb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' && Deleted != '1'");
    $comsone = mysqli_num_rows($commentsonedb);
    
    $commentstwodb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$usertwo->id' && Deleted != '1'");
    $comstwo = mysqli_num_rows($commentstwodb);
    
    $stratsonedb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' && Published = '1'");
    $stratsone = mysqli_num_rows($stratsonedb);
    
    $stratstwodb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$usertwo->id' && Published = '1'");
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
                        <td><p class="blogodd"><b><? echo _("AS_BNTitle"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <? echo _("AS_BNDupCon1"); ?><br>
                <? echo _("AS_BNDupCon2"); ?><br><br>
    
                <i><b><? echo _("AS_PleaseNote"); ?>:</b></b></i> <? echo _("AS_BNDupCon3"); ?><br><br>
    
    <center>
    
    <table width="570" border="0">
    
    <tr>
        <td width="250" style="vertical-align: top;">
            <center>
            <form name="loginform" action="index.php?page=settings" method="post">
            <input type="hidden" name="settingspage" value="addbnetone">
            <input type="hidden" name="regionselect" value="<? echo $region ?>">
            <h6><b><? echo _("AS_BNDupT1"); ?>:</b><br>
    
            <table>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>
                                    <img class="usericonsmall" <? echo $usericon ?> heigth="30" width="30" />
                                </td>
                                <td>
                                    <span class="username" style="text-decoration: none" rel="<? echo $user->id ?>" value="<? echo $user->id ?>"><p class="blogodd"><b><? echo $user->Name ?></p></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><? echo _("UP_ABRegDate"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><span name="time"><? echo $user->regtime ?></span></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><? echo _("FormComBlogPromptComments"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><? echo $comsone ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><? echo _("UP_TabStrategies"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><? echo $stratsone ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td colspan="2"><img src="images/blank.png" width="220" height="1"></td>
                </tr>
    
    
                <tr>
                    <td colspan="2">
                        <center><button tabindex="20" type="submit" class="bnetlogin" name="page" value="settings"><? echo _("Move Battle.net connection to this account"); ?></button>
                    </td>
                </tr>
    
            </table>
            </form>
        </td>
    
        <td width="70"><hr class="vertical"><hr class="vertical"><hr class="vertical"><br><img src="images/blank.png" width="70" height="1"></td>
    
        <td width="250" style="vertical-align: top;">
            <center>
    
            <h6><b><? echo _("AS_BNDupT2"); ?>:</b><br>
    
            <table>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>
                                    <img class="usericonsmall" <? echo $usertwoicon ?> heigth="30" width="30" />
                                </td>
                                <td>
                                    <span class="username" style="text-decoration: none" rel="<? echo $usertwo->id ?>" value="<? echo $user->id ?>"><p class="blogodd"><b><? echo $usertwo->Name ?></p></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><? echo _("UP_ABRegDate"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><span name="time"><? echo $usertwo->regtime ?></span></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><? echo _("FormComBlogPromptComments"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><? echo $comstwo ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td>
                        <p class="blogodd"><b><? echo _("UP_TabStrategies"); ?>:</b>
                    </td>
                    <td style="text-align: right">
                        <p class="blogodd"><? echo $stratstwo ?></p>
                    </td>
                </tr>
    
                <tr>
                    <td colspan="2"><img src="images/blank.png" width="220" height="1"></td>
                </tr>
    
                <tr>
                    <td colspan="2">
                        <p class="blogodd"><? echo _("AS_BNDupT3"); ?>
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
<? // ============= END OF MODULE 3.2 ============= ?>







<?
// =======================================================================================================
// ================================== ?????? SUBMENU MODULES ??????? =============================================
// =======================================================================================================
// ================================== ?????? ALL OTHER MODULS ?????? =============================================
// =======================================================================================================
?>





<? // ============= Begin of Account Settings ============= ?>


<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    <div style="width: 100%; padding: 3 0 3 5; font-family: MuseoSans-500,Roboto; color: white">
        Account Settings
    </div>
    
    

<? // ============= MODULE 1 - Select new user name ============= ?>
<?
if ($user->NameChange > "0") { ?>


<table width="100%" style="margin-top: 5px" class="profile<? if ($regnameerror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th class="profile<? if ($regnameerror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_UNTitle"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">

            <? echo _("AS_UNChInst1"); ?><br>
           <? echo _("AS_UNChInst2"); ?>
            <br><br>
            <i>
                <? echo _("AS_UNChInst3"); ?>
            </i>
            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="namechange">

            <table>
                <tr>
                <td align="right"><p class="blogodd"><b><? if ($regnameerror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("AS_UNHead1"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="5" placeholder="" type="text" name="username" value="<? echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" maxlength="15" required>
                </td>
                <td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
                    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
                    <em><? echo _("RG_HNick"); ?></em>
                    <? echo _("RG_NickDesc"); ?><br>
                    <ul>
                        <li><? echo _("RG_NickRest1"); ?></li>
                        <li><? echo _("RG_NickRest2"); ?><br># < > [ ] | { } " ' / \ $ ?</li>
                        <li><? echo _("RG_NickRest3"); ?></li>
                    </ul>
                </span></a>
                </td>
                <td><img src="images/blank.png" width="20" height="1"/></td>
                <td><button type="submit" tabindex="6" class="comedit"><? echo _("AS_UNBTSave"); ?></button></td>
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

<? } ?>
<? // ============= END OF MODULE 1 ============= ?>




<? // ============= MODULE 2 - Adding a password for Battle.net Registered Users  ============= ?>
<?
if (!$user->Hash) { ?>

<table width="100%" style="margin-top: 5px" class="profile<? if ($regpasserror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<? if ($regpasserror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_PWTitle"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">
            <? echo _("AS_PWInst1"); ?>
            <br>
            <? echo _("AS_PWInst2"); ?>
            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="addpw">

            <table>


                <tr>
                <td align="right"><p class="blogodd"><b><? if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("UL_LogPass"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="10" placeholder="" type="password" id="passwordy" name="password" required></td>
                <td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
                    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
                    <em><? echo _("UL_LogPass"); ?></em>
                    <? echo _("RG_PassRest1"); ?><br><br>
                    <? echo _("RG_PassRest2"); ?><br>
                </span></a>

                </td>
                </tr>



                <tr>
                <td align="right"><p class="blogodd"><b><? if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RG_RepPass"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="11" placeholder="" type="password" name="passwordrep" id="password" required></td>
                <td></td>
                <td><img src="images/blank.png" width="20" height="1"/></td>
                <td><button type="submit" tabindex="12" class="comedit"><? echo _("RP_Save"); ?></button></td>
                </tr>


                <?
                if ($regpasserror == "true"){
                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regpassprob.'</div></td></td></tr>';
                }
                ?>


                <tr><td></td><td></td><td><div id="capsWarning" class="registerError"><p class="commenteven"><? echo _("UL_CapsOn"); ?></div></td></tr>

                <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
            </form>
        </td>
    </tr>

</table>

<? } ?>
<? // ============= END OF MODULE 2 ============= ?>









<? // ============= MODULE 4 - Language select ============= ?>


<table width="100%" style="margin-top: 5px" class="profile">
    <tr class="profile">
        <th class="profile">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_LNTitle"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">

            <? echo _("AS_LNInst1"); ?> <br>
            <? echo _("AS_LNInst2"); ?>

            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="setlanguage">

            <table>
                <tr>
                <td align="right"><p class="blogodd"><b><? echo _("AS_LNHead"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><select tabindex="25" class="petselect" name="setlanguage" onchange="this.form.submit()">
                    <option class="ctselect" value="en_US" <? if ($user->Language == "en_US") {echo "selected";} ?>>English</option>
                    <option class="ctselect" value="de_DE" <? if ($user->Language == "de_DE") {echo "selected";} ?>>Deutsch</option>
                    <option class="ctselect" value="es_ES" <? if ($user->Language == "es_ES") {echo "selected";} ?>>Espa&#xF1;ol</option>
                    <option class="ctselect" value="fr_FR" <? if ($user->Language == "fr_FR") {echo "selected";} ?>>Fran&#xE7;ais</option>
                    <option class="ctselect" value="ru_RU" <? if ($user->Language == "ru_RU") {echo "selected";} ?>>&#x420;&#x443;&#x441;&#x441;&#x43A;&#x438;&#x439;</option>
                    <option class="ctselect" value="pt_BR" <? if ($user->Language == "pt_BR") {echo "selected";} ?>>Portugu&#xEA;s (incomplete)</option>
                    <option class="ctselect" value="it_IT" <? if ($user->Language == "it_IT") {echo "selected";} ?>>Italiano (incomplete)</option>
                    <option class="ctselect" value="ko_KR" <? if ($user->Language == "ko_KR") {echo "selected";} ?>>&#xD55C;&#xAD6D;&#xC5B4; (incomplete)</option>
                    <option class="ctselect" value="zh_TW" <? if ($user->Language == "zh_TW") {echo "selected";} ?>>&#x4E2D;&#x6587; (incomplete)</option>
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

<? // ============= END OF MODULE 4 ============= ?>









<? // ============= MODULE 6.1 - Add email address  ============= ?>
<?
if (!$user->Email) { ?>


<table width="100%" style="margin-top: 5px" class="profile<? if ($regmailerror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<? if ($regmailerror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_EMAddTitle"); ?></td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">
            <? echo _("AS_EMAddInst1"); ?>
            <br>
            <? echo _("AS_EMAddInst2"); ?>
            <br><br>

            <form class="form-style-register" action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="addemail">

            <table>

                <tr>
                <td align="right"><p class="blogodd"><b><? if ($regmailerror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("AS_EMAddH"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="150" placeholder="" type="email" name="addemail" value="<? echo $outputemail ?>" required></td>

                <td><img src="images/blank.png" width="38" height="1"/></td>
                <td><button type="submit" tabindex="151" class="comedit"><? echo _("AS_EMBTSave"); ?></button></td>
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

<? } ?>
<? // ============= END OF MODULE 6.1 ============= ?>




<? // ============= MODULE 6.2 - Change or remove email address  ============= ?>
<?
if ($user->Email) { ?>

<table width="100%" style="margin-top: 5px" class="profile<? if ($regmailerror == "true"){ echo "hl"; } ?>">
        <tr class="profile">
                <th width="5" class="profile<? if ($regmailerror == "true"){ echo "hl"; } ?>">
                        <table>
                                <tr>
                                        <td><img src="images/userdd_settings_grey.png"></td>
                                        <td><img src="images/blank.png" width="5" height="1"></td>
                                        <td><p class="blogodd"><b><? echo _("AS_EMCTitle"); ?></td>
                                </tr>
                        </table>
                </th>
        </tr>

        <tr class="profile">
                <td class="profile">
                        <p class="blogodd">

                        <? echo _("AS_EMCurrent"); ?>: <b><? echo $user->Email ?></b><br><br>

                        <form class="form-style-register" action="index.php?page=settings" method="post">
                                <input type="hidden" name="settingspage" value="changemail">

                        <table>

                                <tr>
                                <td align="right"><p class="blogodd"><b><? if ($regmailerror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("AS_EMCHeader"); ?>:</b></td>
                                <td><img src="images/blank.png" width="5" height="1"/></td>
                                <td><input tabindex="160" placeholder="" type="email" name="addemail" value="<? echo $outputemail ?>" required></td>

                                <td><img src="images/blank.png" width="38" height="1"/></td>
                                <td><button type="submit" tabindex="161" class="comedit"><? echo _("AS_EMCBTUpdate"); ?></button></form></td>

                                <td><img src="images/blank.png" width="10" height="1"/></td>
                                <form class="form-style-register" action="index.php?page=settings" method="post">
                                <input type="hidden" name="settingspage" value="removemail">
                                <td><button type="submit" tabindex="162" class="comdelete"><? echo _("AS_EMCBTRemove"); ?></button></td>
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

<? } ?>
<? // ============= END OF MODULE 6.2 ============= ?>








<? // ============= MODULE 7 - Change password  ============= ?>
<?
if ($user->Hash) { ?>


<table width="100%" style="margin-top: 5px" class="profile<? if ($changepasserror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<? if ($changepasserror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b><? echo _("AS_PWCTitle");  ?></td>
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
                <td align="right"><p class="blogodd"><b><? if ($changepasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RP_NPField"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="170" placeholder="" type="password" id="passwordy" name="password" required></td>
                <td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
                    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
                    <em><? echo _("UL_LogPass"); ?></em>
                    <? echo _("RG_PassRest1"); ?><br><br>
                    <? echo _("RG_PassRest2"); ?><br>
                </span></a>

                </td>
                </tr>


                <tr>
                <td align="right"><p class="blogodd"><b><? if ($changepasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RG_RepPass"); ?>:</b></td>
                <td><img src="images/blank.png" width="5" height="1"/></td>
                <td><input tabindex="171" placeholder="" type="password" name="passwordrep" id="passwordz" required></td>
                <td></td>
                <td><img src="images/blank.png" width="20" height="1"/></td>
                <td><button type="submit" tabindex="172" class="comedit"><? echo _("RP_Save"); ?></button></td>
                </tr>


                <?
                if ($changepasserror == "true"){
                echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$changepassprob.'</div></td></td></tr>';
                }
                ?>


                <tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><? echo _("UL_CapsOn"); ?></div></td></tr>

                <tr>
                    <td><img src="images/blank.png" width="150" height="1"/></td>
                </tr>

            </table>
            </form>
        </td>
    </tr>

</table>

<? } ?>
<? // ============= END OF MODULE 7 ============= ?>


</div>
<? // ============= End of Account Settings ============= ?>




<? // ============= Begin of Battle.net Options ============= ?>


<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    <div style="width: 100%; padding: 3 0 3 5; font-family: MuseoSans-500,Roboto; color: white">
        Battle.net Options
    </div>

    <? // ============= MODULE - Battle.net is authenticated, but no access to WoW Characters is given ============= ?>
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
                        <td><p class="blogodd"><b><? echo _("AS_NoWoWTitle"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <? echo _("AS_NoWoWInst1"); ?><br>
                <? echo _("AS_NoWoWInst2"); ?><br>
                <? echo _("AS_NoWoWInst3"); ?>:
            <br>
            <ol class="blogodd">
                <li><? echo _("AS_NoWoWInst4"); ?> <a class="wowhead" href="https://<? echo $bnetuser->region ?>.battle.net/account/management/"></a><? echo _("AS_NoWoWInst5"); ?></a></li>
                <li><? echo _("AS_NoWoWInst6"); ?></li>
                <li><? echo _("AS_NoWoWInst7"); ?></li>
                <li><? echo _("AS_NoWoWInst8"); ?></li>
                <li><? echo _("AS_NoWoWInst9"); ?></li>
            </ol>
        </td>
        </tr>
    </table>
    
    <? } ?>
    <? // ============= END OF MODULE 0.2 ============= ?>

    
    
    
    <? // ============= MODULE - Add Battle.net to account ============= ?>
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
                        <td><p class="blogodd"><b><? echo _("AS_BNTitle"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <? echo _("AS_BNInst1"); ?>
                <ul class="blogodd">
                <li><? echo _("AS_BNInst2"); ?></li>
                <li><? echo _("AS_BNInst3"); ?></li>
                <li><? echo _("AS_BNInst4"); ?></li>
                </ul>
                <p class="blogodd"><? echo _("AS_BNInst5"); ?>
                <br><? echo _("AS_BNInst6"); ?>
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
                                <p class="blogodd"><? echo _("UL_RegionsW"); ?>
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
                                <p class="blogodd"><? echo _("UL_RegionsC"); ?>
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
    <? } ?>
    <? // ============= END OF MODULE 3.1 ============= ?>




    <? // ============= MODULE - Change Battle.net Region ============= ?>
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
                        <td><p class="blogodd"><b><? echo _("Change Battle.net Region"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <? echo _("Your region for Battle.net is set to:<b>").' '.strtoupper($bnetuser->Region).'</b>'; ?><br>
                <? echo _("You can change your region manually below. This might be useful if you have characters on multiple regions."); ?><br>
                <br>

                <? if ($user->UseWowAvatar == 1) {
                    echo '<b>=> '._("Changing the region will remove your avatar icon!").'</b><br>';
                } ?>
                <? if ($collection) {
                    echo '<b>=> '._("Changing the region will remove your saved pet collection from Xu-Fu's!").'</b><br>';
                } ?>
                <br>
                
                <form action="index.php?page=settings" method="post">
                <input type="hidden" name="settingspage" value="change_region">

                <table border="0">

                <? foreach (['us', 'eu', 'kr', 'tw'] as $region)
                {
                    switch ($region) {
                        case "us":
                            $regiontitle = "FormSelectRealmUS";
                            break;
                        case "eu":
                            $regiontitle = "FormSelectRealmEU";
                            break;
                        case "kr":
                            $regiontitle = "FormSelectRealmKR";
                            break;
                        case "tw":
                            $regiontitle = "FormSelectRealmTW";
                            break;
                    }
                    if ($region != $bnetuser->Region) { ?>
                        <tr>
                            <td style="width:30; padding-left: 20px">
                                <ul class="radios">
                                    <li>
                                        <input tabindex="11" class="blue" type="radio" id="<? echo $region ?>" value="<? echo $region ?>" name="region" required>
                                        <label for="<? echo $region ?>"></label>
                                        <div class="check"></div>
                                    </li>
                                </ul>
                            </td>
                            <td>
                                <p class="blogoddnowrap"><b><? echo _($regiontitle) ?>
                            </td>
                    </tr>
                    <? }
                } ?>

                <tr>
                <td colspan="3" style="padding-top: 10px">
                    <button type="submit" tabindex="12" style="margin-left: 30px" class="comedit"><? echo _("Change Region"); ?></button>
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
    
    <? } ?>
    <? // ============= END OF MODULE 0.2 ============= ?>
    
    


    <? // ============= MODULE - Unlink Battle.net Connection ============= ?>
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
                        <td><p class="blogodd"><b><? echo _("Unlink Battle.net Connection"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <? if (!$user->Hash) { 
                    echo _("Your account does not have a password set. Please set one in the option above. Otherwise, unlinking Battle.net would mean you cannot login to your account anymore.");
                 }
                else { ?>
                <? echo _("You have an active Battle.net connection to log in with Xu-Fu, use a character icon as your avatar and easily import your collection. You can remove this link by clicking the button below."); ?>
                <br><br>
                 <? echo _("This will also remove your current pet collection from Xu-Fu and you will have to manually link a character to reimport it."); ?>

                <br>

                    <button data-remodal-target="modal_unlink" style="margin-left: 30px; margin-top: 15px;" class="comdelete"><? echo _("Unlink Battle.net"); ?></button>
                    <br>                
                    <div class="remodal remodalstratedit remodaldelete" data-remodal-id="modal_unlink">
                        <table style="width: 400px" class="profile">
                            <tr class="profile">
                                <th colspan="2" style="width: 100%" class="profile">
                                    <table>
                                        <tr>
                                            <td><img src="images/icon_report.png" style="padding-right: 5px"></td>
                                            <td><p class="blogodd"><span style="white-space: nowrap;"><b><? echo _("Confirm Unlinking"); ?></span></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                
                            <tr class="profile">
                                <td class="collectionbordertwo" colspan="2" style="font-family: MuseoSans-500; font-size: 14px">
                                    <div id="del_part1">
                                        <center><br><b><? echo _("Are you sure you want to unlink your Battle.net connection?"); ?></b><br><br>
                                        <form class="form-style-register" action="index.php?page=settings" method="post">
                                            <input type="hidden" name="settingspage" value="unlink_bnet">
                                            <button style="margin-left: 30px; margin-top: 15px;" type="submit" tabindex="151" class="redlarge"><? echo _("Unlink Battle.net"); ?></button>
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
                <? } ?>
            <br>
        </td>
        </tr>
    </table>
    
    <? } ?>
    <? // ============= END OF MODULE ============= ?>

    





    
    <? // ============= END OF BATTLE.NET OPTIONS SECTION ============= ?>
</div>








<? // ============= MODULE: Tag priority in alternatives menu ============= ?>
<script src="https://www.wow-petguide.com/data/jquery-sortable.js"></script>


<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    <div style="width: 100%; padding: 3 0 3 5; font-family: MuseoSans-500,Roboto; color: white">
        Page Browsing
    </div>

    <table width="100%" class="profile" style="margin-top: 5px">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b>Priority of tags</td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                Decide which tags should be considered more important than others when showing alternative strategies.<br>
                Tags in the green box will be moved higher up, while those in the red box will be moved down.
                <br><br>
                Drag the tags between and within brackets to set their importance.
                <br>
                <br>
                
                <div style="width: 200px; float: left; margin-left: 20px">
                    <center><p class="blogodd"><b>Preferred</b></center>
                    <ol class='tags_sorting' id='tags_one' style='background-color: #a3ba87'>
                       <? if ($tagprio[0][0] != "") {
                            foreach ($tagprio[0] as $key => $value) {
                                echo '<li id="'.$alltags[$value]['id'].'" class="tags_item">'.$alltags[$value]['Name'].'</li>';
                            }
                        } ?>                
                   </ol>   
                </div>
                
                <div style="width: 200px; float: left; margin-left: 20px">
                     <center><p class="blogodd"><b>Neutral</b></center>    
                     <ol class='tags_sorting' id='tags_two' style='background-color: #aeaeae'>
                       <? if ($tagprio[1][0] != "") {
                            foreach ($tagprio[1] as $key => $value) {
                                echo '<li id="'.$alltags[$value]['id'].'" class="tags_item">'.$alltags[$value]['Name'].'</li>';
                            }
                       } ?>  
                    </ol>   
                </div>
                
                <div style="width: 200px; float: left; margin-left: 20px">
                    <center><p class="blogodd"><b>Unfavored</b></center>
                    <ol class='tags_sorting' id='tags_three' style='background-color: #ba8787'>
                       <? if ($tagprio[2][0] != "") {
                            foreach ($tagprio[2] as $key => $value) {
                               echo '<li id="'.$alltags[$value]['id'].'" class="tags_item">'.$alltags[$value]['Name'].'</li>';
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
                    <button onclick="save_tag_settings(<? echo $user->id ?>,<? echo $user->ComSecret ?>,)" type="submit" tabindex="36" class="comedit"><? echo _("UP_BTSave") ?></button>
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
                        <td><p class="blogodd"><b><? echo _("AS_BTTitle"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
                <td class="profile">
                    <p class="blogodd">
                    <? echo _("AS_BTInst1"); ?><br>
                    <b><? echo _("AS_AccDelWarn"); ?></b> - <? echo _("AS_BTInst2"); ?><br><br>
                    <? echo _("AS_BTInst3"); ?> <span class="username tooltipstered" style="text-decoration: none" rel="2" value="<? echo $user->id ?>"><a target="_blank" href="?user=2" class="usernamelink comheadbright com_role_99_bright">Aranesh</a></span> (<a class="wowhead" href="https://www.wow-petguide.com/index.php?page=writemsg&to=2" target="_blank"><? echo _("UP_TTSendMsg"); ?></a> / <a class="wowhead" href="https://discord.gg/z4dxYUq" target="_blank"><? echo _("UP_PRHDiscord"); ?></a>)<br>
                    <br>
    
                    <table>
                        <tr>
                        <td align="right"><p class="blogodd"><b><? echo _("AS_BTTitle"); ?>:</b></td>
                        <td><img src="images/blank.png" width="5" height="1"/></td>
                        <td>
                            <? echo _("Button_Off"); ?>
                        </td>
                        <td>
                            <div class="armoryswitch">
                                <input type="checkbox" class="armoryswitch-checkbox" id="setbeta" onchange="change_beta_setting('<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($usersettings['BetaAccess'] == "on" ) { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="setbeta">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <? echo _("Button_On"); ?>
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







<? // ============= MODULE 8 - Delete Account  ============= ?>

<div style="width: 85%; background-color: #4b4b4b; padding: 5px; margin-bottom: 15px; border-radius: 5px;">
    
    <table width="100%" class="profile">
        <tr class="profile">
            <th width="5" class="profile">
                <table>
                    <tr>
                        <td><img src="images/userdd_settings_grey.png"></td>
                        <td><img src="images/blank.png" width="5" height="1"></td>
                        <td><p class="blogodd"><b><? echo _("AS_DLTitle"); ?></td>
                    </tr>
                </table>
            </th>
        </tr>
    
        <tr class="profile">
                <td class="profile">
                        <p class="blogodd">
    
                        <form class="form-style-register" action="index.php?page=settings" method="post">
                                <input type="hidden" name="settingspage" value="deleteacc">
                                <button style="margin-left: 30px; margin-top: 15px;" type="submit" tabindex="151" class="comdelete"><? echo _("AS_DLBTStart"); ?></button>
                        </form>
                </td>
        </tr>
    
    </table>

</div>


<? // ============= END OF MODULE 8 ============= ?>






<br><br><br><br>

</div>

<?
switch ($sendtoast) {
    case "pwadded":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("GR_PassChanged").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "namechanged":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("GR_NameChanged").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "addbnet":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GRBnetCon").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregfail":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_BnetDecl").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregsuccess":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GRBnetConY").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "bnetapierror":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_BnetFailed").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "emailadded":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GREMSaved").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "mailremoved":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GREMRemoved").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "langchanged":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GRLngChanged").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "genericerror":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_GenError").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "nowowaccess":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("UP_WA_noaccess").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "bnet_unlinked":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("Battle.net Connection has been removed.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "region_changed":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("Battle.net Region has been changed.").'", duration: "7000", size: "large", location: "tc" });</script>';
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
