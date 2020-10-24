<?php
$comcat = $_POST['comcat'];
$comsortid = $_POST['comsortid'];
$comparent = $_POST['comparent'];
$comvisitorid = $_POST['visitorid'];
$comlanguage = $_POST['comlanguage'];
$comuserid = $_POST['comuserid'];

$comcontent = $_POST['comcontent'];
$comcontent = remove_emojis($comcontent);
$antispam1 = $_POST['email'];  // prefill: verifymail
$antispam2 = $_POST['e-mail']; // prefill: requiredfield
$antispam3 = $_POST['mail'];   // prefill: checkverification

$comname = $_POST['comname'];
$commail = $_POST['commail'];


// ===== Error processing =====
$comerror = "false";

if ($comcat != "0" AND $comcat != "1" AND $comcat != "2"){                                // Anti tampering
    $comerror = "true";
    $comerrordesc = "Incorrect category number (tampering)";
}


if (!$comcontent){
    $comerror = "true";
    $comerrordesc = "Comment empty";
}

if (mb_strlen($comcontent) > "3020") {
    $comerror = "true";
    $comerrordesc = "Comment too long (tampering)";
}

if (!preg_match("/^[1234567890]*$/is", $comparent) OR !preg_match("/^[1234567890]*$/is", $comsortid) OR !preg_match("/^[1234567890]*$/is", $comuserid)) {      // Anti tampering
    $comerror = "true";
    $comerrordesc = "Invalid IDs (tampering)";
}

if (!$comuserid) {
    if ($antispam1 != "verifymail" OR $antispam2 != "requiredfield" OR $antispam3 != "checkverification") {         // Anti Spambots via hidden fields
        $comerror = "true";
        $comerrordesc = "Hidden antispam fields incorrect (spambot)";
    }

    $antispamdb = mysqli_query($dbcon, "SELECT * FROM Blacklist WHERE Keyword != ''");        // Anti Spambots via keywords
    $antispamcount = "0";
    while ($antispamcount < mysqli_num_rows($antispamdb)) {
        $spamentry = mysqli_fetch_object($antispamdb);
        $spamwords[$antispamcount] = $spamentry->Keyword;
        $antispamcount++;
    }
    foreach ($spamwords as $key => $value)
    {
        if (strpos($comcontent, $value) !== false OR strpos($commail, $value) !== false OR strpos($comname, $value) !== false) {
            $comerror = "true";
            $comerrordesc = "Antispam hit through keyword: ".$value;
        }
    }

    $comuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name = '$comname'");     // Username validation
    if (mysqli_num_rows($comuserdb) > "0") {
        $comerror = "true";
        $comerrordesc = "Name already exists as registered user";
    }

    if (mb_strlen($comname) > "25" OR mb_strlen($comname) < "2"){
        $comerror = "true";
        $comerrordesc = "Name too short or too long";
    }

    if (preg_match('/[\'\/#\$\{\}\[\ \]\|\<\>\?\"\\\]/', $comname))
    {
        $comerror = "true";
        $comerrordesc = "Name contains characters not allowed";
    }

    if (filter_var($comname, FILTER_VALIDATE_EMAIL)) {
        $comerror = "true";
        $comerrordesc = "Name is an email address";
    }
}

if (!$comuserid AND $comvisitorid == "0"){                                         // Anti Spambots via visitor ID, only if not logged in
    $comerror = "true";
    $comerrordesc = "Bot detected - visitor ID empty";
}

$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
if (!$comuserid AND preg_match($reg_exUrl, $comcontent, $url)) {
    $comerror = "true";
    $comerrordesc = "Unregistered and URL in comment - declined";
}

$comdate = date("Y-m-d H:i:s");

if (!$comuserid AND $comvisitorid != "0") {
    $spamprotectdb = mysqli_query($dbcon, "SELECT * FROM Spamprotect WHERE id = '$comvisitorid'") or die(mysqli_error($dbcon));
    if(mysqli_num_rows($spamprotectdb) > "0"){
        $spamprot = mysqli_fetch_object($spamprotectdb);
        $thisentrytime = $spamprot->Entrytime;
        $checkdiff = strtotime($comdate)-strtotime($spamprot->Entrytime);
        if ($checkdiff <= "8" AND strlen($comcontent) > "300"){
            $comerror = "true";
            $comerrordesc = "Spam - Time taken to enter: ".$checkdiff." seconds. Content: ".strlen($comcontent)." characters.";
        }
        if ($checkdiff <= "4" AND strlen($comcontent) > "100"){
            $comerror = "true";
            $comerrordesc = "Spam - Time taken to enter: ".$checkdiff." seconds. Content: ".strlen($comcontent)." characters.";
        }
    }
    else {
        $comerror = "true";
        $comerrordesc = "No such visitor ID in DB - perhaps tampering";
    }
}

// Anti VPN and bad proxy protection
if (!$comuserid AND $comerror != "true") {  // ACtivate this
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 
    $user_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $strictness = 1;
    $allow_public_access_points = 'true';
    $lighter_penalties = 'true';
    
    $parameters = array(
        'user_agent' => $user_agent,
        'user_language' => $user_language,
        'strictness' => $strictness,
        'allow_public_access_points' => $allow_public_access_points,
        'lighter_penalties' => $lighter_penalties
    );
    
    $formatted_parameters = http_build_query($parameters);

    $url = sprintf(
        'https://www.ipqualityscore.com/api/json/ip/%s/%s?%s', 
        api_lookup_key,
        $user_ip_adress, 
        $formatted_parameters
    );
    
    $timeout = 5;
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    
    $json = curl_exec($curl);
    curl_close($curl);
    
    // Decode the result into an array.
    $result = json_decode($json, true);
    
    // Check to see if our query was successful.
    if(isset($result['success']) && $result['success'] === true){
        if($result['fraud_score'] >= 80 && $result['is_crawler'] === false){
            $comerror = "true";
            //entering data for tracking only
            $comdeleted = "1";
            $comclosed = "1";
            $comclosetype = "Spam";
            $comclosedby = "Bad IP - fraud score of ".$result['fraud_score'];
            $sendtoast = "";
            if (!$comicon) $comicon = 0;
            mysqli_query($dbcon, "INSERT INTO Comments (`User`, `Category`, `SortingID`, `Language`, `Date`, `Name`, `Mail`, `Comment`, `Parent`, `Icon`, `IP`, `Deleted`, `Closed`, `CloseType`, `ClosedBy`) VALUES ('$comuserid', '$comcat', '$comsortid', '$comlanguage', '$comdate', '$comname', '$commail', '$comcontent', '$comparent', '$comicon', '$user_ip_adress', '$comdeleted', '$comclosed', '$comclosetype', '$comclosedby')") OR die(mysqli_error($dbcon));
        }
    }
}


if ($comuserid && ($comuserid != $user->id)) {
    $comerror = "true";
    $comerrordesc = "Submitting user is not logged in - could be tampering";
}

// Preparing data for input into DB

$comname = mysqli_real_escape_string($dbcon, $comname);
$commail = mysqli_real_escape_string($dbcon, $commail);
$comcontent = mysqli_real_escape_string($dbcon, $comcontent);

if ($comerror == "true"){
    $comdeleted = "1";
    $comclosed = "1";
    $comclosetype = "Spam";
    $comclosedby = "Antispam";
    $sendtoast = "comsubmiterror";
}
else {
    $comdeleted = "0";
    $comclosed = "0";
    $comclosetype = "";
    $comclosedby = "";

    if ($comuserid == "") {
        $dirrand = "images/pets";                                                             // Generate random Icon from existing pet icons
        $dhrand = opendir($dirrand);
        while (false !== ($filename = readdir($dhrand))) {
            $filesplits = explode(".",$filename);
            if ($filesplits[1] == "png" && $filesplits[0] >= "25" && preg_match("/^[1234567890]*$/is", $filesplits[0])){
                $iconlist[] = $filesplits[0];
            }
        }
        $rand_icon = array_rand($iconlist, 1);
        $comicon = $iconlist[$rand_icon];
    }
}
if (!$comuserid) {
    $comuserid = "0";
}

if ($comerror != "true"){
    if (!$comicon) $comicon = 0;
    mysqli_query($dbcon, "INSERT INTO Comments (`User`, `Category`, `SortingID`, `Language`, `Date`, `Name`, `Mail`, `Comment`, `Parent`, `Icon`, `IP`, `Deleted`, `Closed`, `CloseType`, `ClosedBy`) VALUES ('$comuserid', '$comcat', '$comsortid', '$comlanguage', '$comdate', '$comname', '$commail', '$comcontent', '$comparent', '$comicon', '$user_ip_adress', '$comdeleted', '$comclosed', '$comclosetype', '$comclosedby')") OR die(mysqli_error($dbcon));
    $addedid = mysqli_insert_id($dbcon);
    if ($comuserid) {
        mysqli_query($dbcon, "INSERT INTO Votes (`User`, `SortingID`, `Vote`) VALUES ('$comuserid', '$addedid', '1')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "UPDATE Comments SET `Votes` = '1' WHERE id = '$addedid'");
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$comuserid', '$user_ip_adress', '0', 'Comment written', '$addedid')") OR die(mysqli_error($dbcon));
    }
    
    // For comments to strategies, create entry to notify the strategy owner   
    if ($comcat == "2") {
        // Grab strategy
        $stratcomdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$comsortid'");
        if (mysqli_num_rows($stratcomdb) > "0"){
            $stratcomthis = mysqli_fetch_object($stratcomdb);
            if ($user->id != $stratcomthis->User && $stratcomthis->User != "0") {
                if ($stratcomthis->NewComsIDs == "") {
                    $updatestratcomids = $addedid."_".$comdate."_".$comlanguage;
                }
                else {
                    $updatestratcomids = $stratcomthis->NewComsIDs.";".$addedid."_".$comdate."_".$comlanguage;
                }
                mysqli_query($dbcon, "UPDATE Alternatives SET NewComs = NewComs + 1 WHERE id  = '$comsortid'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE Alternatives SET NewComsIDs = '$updatestratcomids' WHERE id  = '$comsortid'") OR die(mysqli_error($dbcon));
            }
        }
    }
    
    $updatedusers[] = "--";

    // Mark previous comments as "new" for their specific user
    if ($comparent != "0") {
        $cparentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$comparent'");
        // Only check if the newly submitted comment is a subcomment
        if (mysqli_num_rows($cparentdb) > "0"){
            $cparent = mysqli_fetch_object($cparentdb);
            // Check Parent comment first
            if ($cparent->User != $user->id && $cparent->User != "0") {
                mysqli_query($dbcon, "UPDATE Comments SET `NewActivity` = '1' WHERE id = '$cparent->id'");
                $updatedusers[] = "$cparent->User";
            }
            // Go through all subcomments next
            $csubsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Parent = '$comparent' AND id != '$addedid'");
            while($csubs = mysqli_fetch_object($csubsdb)) {
                if ($csubs->User != $user->id && $csubs->User != "0" && !in_array($csubs->User, $updatedusers)) {
                    mysqli_query($dbcon, "UPDATE Comments SET `NewActivity` = '1' WHERE id = '$csubs->id'");
                    $updatedusers[] = "$csubs->User";
                }
            }
        }
    }

    $tcomlang = decode_language($comlanguage);
    if ($tcomlang['short'] == "EN") {
        $tcomnatoren = "en";
    }
    else {
        $tcomnatoren = "nat";
    }
    $comment = $addedid;

    $jumpanchor = "";
    $jumpto = "CM_".$addedid;
    $urlchanger = "?Comment=".$addedid;
}