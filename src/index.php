<?php
ini_set ('display_errors', 0);

require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');
require_once ("data/dbconnect.php");
require_once ("classes/functions.php");
require_once ("classes/com_functions.php");
require_once ("classes/BBCode2.php");
require_once ('data/blizzard_api.php');
require_once ('config.php');
require_once ("thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
require_once ('BattleNet.php');
require_once ('HTML.php');
require_once ('HTTP.php');
require_once ('Localization.php');
require_once ('Strategy.php');
require_once ('TopMenu.php');
require_once ('User.php');

$ads_active = TRUE;
// =================== TRACK VISITOR IP ===================

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}


// =================== ANTI SPAM-TRAFFIC SYSTEM ===================

$antispamdb = mysqli_query($dbcon, "SELECT * FROM Blacklist WHERE IP = '$user_ip_adress'");
$antispam = mysqli_num_rows($antispamdb);

if ($antispam > "0"){
    $spamentry = mysqli_fetch_object($antispamdb);
    $spam = $spamentry->Counter+1;
    $spamtime = date('Y-m-d H:i:s');
    $update = mysqli_query($dbcon, "UPDATE Blacklist SET `Counter` = '$spam' WHERE IP = '$user_ip_adress'");
    $update = mysqli_query($dbcon, "UPDATE Blacklist SET `Lastupdate` = '$spamtime' WHERE IP = '$user_ip_adress'");
    die;
}

// =================== IMPORT SETTINGS ===================

$pagesettingsdb = mysqli_query($dbcon, "SELECT * FROM PageSettings LIMIT 1");
$pagesettings = mysqli_fetch_object($pagesettingsdb);

$admindb = mysqli_query($dbcon, "SELECT * FROM admin WHERE id= '1'");
$admin = mysqli_fetch_object($admindb);

$commentson = $admin->ComOnOff;
$comeditdeadline = $admin->ComEditTimer;
$comsubmitedit = $admin->ComSubmitTimer;
$adminIP = $admin->AdminIP;

// =================== ANTI SPAM VISITOR TRACKING ===================

$entrytime = date('Y-m-d H:i:s');
$eintragen = mysqli_query($dbcon, "INSERT INTO Spamprotect (`Entrytime`, `IP`) VALUES ('$entrytime', '$user_ip_adress')") OR die(mysqli_error($dbcon));
$visitorid = mysqli_insert_id($dbcon);

// =================== IMPORT OF VARIABLES ===================

$mainselector = $_GET['m'];

$subselector = $_GET['s'];
$page = $_POST['page'];
if ($page == ""){
    $page = $_GET['page'];
}
$viewuser = $_POST['user'];
if ($viewuser == ""){
    $viewuser = $_GET['user'];
}
$strategy = $_GET['Strategy'];
if ($strategy == ""){
    $strategy = $_GET['strategy'];
    if ($strategy == ""){
        $strategy = $_POST['strategy'];
    }
}

$searchstring = $_POST['petsearch'];
$news_article_id = $_GET['News'];
$news_article = null;

$submitcomment = $_POST['submitcomment'];
$comment = $_GET['Comment'];

$sendtoast = $_POST['sendtoast'];
if ($sendtoast == "") {
    $sendtoast = $_GET['sendtoast'];
}
$command = $_POST['command'];

// INITIALIZE SESSION

$some_name = session_name("initialize");
session_set_cookie_params(0, '/', '.wow-petguide.com');
session_start();

$defaultlang = "en_US";

// Prepare Breed data
$allbreeds = array(
    "BB" => array(
        "Health" => "0.5",
        "Speed" => "0.5",
        "Power" => "0.5"
        ),
    "PP" => array(
        "Health" => "0",
        "Speed" => "0",
        "Power" => "2"
        ),
    "SS" => array(
        "Health" => "0",
        "Speed" => "2",
        "Power" => "0"
        ),
    "HH" => array(
        "Health" => "2",
        "Speed" => "0",
        "Power" => "0"
        ),
    "HP" => array(
        "Health" => "0.9",
        "Speed" => "0",
        "Power" => "0.9"
        ),
    "PS" => array(
        "Health" => "0",
        "Speed" => "0.9",
        "Power" => "0.9"
        ),
    "HS" => array(
        "Health" => "0.9",
        "Speed" => "0.9",
        "Power" => "0"
        ),
    "PB" => array(
        "Health" => "0.4",
        "Speed" => "0.4",
        "Power" => "0.9"
        ),
    "SB" => array(
        "Health" => "0.4",
        "Speed" => "0.9",
        "Power" => "0.4"
        ),
    "HB" => array(
        "Health" => "0.9",
        "Speed" => "0.4",
        "Power" => "0.4"
        )
    );


// =================== LANGUAGE SETTINGS ===================

// === Setting from subdomain is fetched and language is activated accordingly ===
$setlang = array_shift((explode(".",$_SERVER['HTTP_HOST'])));

if (isset($setlang)) {

switch ($setlang)
    {
        case "de":
                $language = "de_DE";
                break;
        case "en":
                $language = "en_US";
                break;
        case "fr":
                $language = "fr_FR";
                break;
        case "it":
                $language = "it_IT";
                break;
        case "es":
                $language = "es_ES";
                break;
        case "pl":
                $language = "en_US";
                break;
        case "pt":
                $language = "pt_BR";
                break;
        case "ru":
                $language = "ru_RU";
                break;
        case "ko":
                $language = "ko_KR";
                break;
        case "zh":
                $language = "zh_TW";
                break;
    }
    if ($language){
        setcookie('language', $language, time() + (86400 * 60), "/", ".wow-petguide.com"); // 86400 = 1 day
        $_SESSION["lang"] = $language;
        $langreason = "Through Subdomain";
    }
}

// === If no manual setting of the language is done ===
// Check if Session language is set and if so, that's it, that one is used (or default language if rubbish is stored in session)

if (isset($_SESSION["lang"])) {
    if ($_SESSION["lang"] != "en_US" AND $_SESSION["lang"] != "de_DE" AND $_SESSION["lang"] != "fr_FR" AND $_SESSION["lang"] != "it_IT" AND $_SESSION["lang"] != "es_ES" AND $_SESSION["lang"] != "pt_BR" AND $_SESSION["lang"] != "ru_RU" AND $_SESSION["lang"] != "ko_KR" AND $_SESSION["lang"] != "zh_TW"){
        $language = $defaultlang;
        $langreason = "Switch to default because not DE, EN, RU, ES or FR and not admin";
    }
    else {
        $language = $_SESSION["lang"];
        $langreason = "Stored from Session";
    }
}



// if no session is active, check if a language is stored in a cookie
else {
    if (isset($_COOKIE["language"])) {
        $language = $_COOKIE["language"];  // Retrieving language from cookie
        $_SESSION["lang"] = $language;
        $langreason = "Stored from Cookie";
    }

// ============= no cookie language set, no session language set
    else {
        // Retrieve browser HTTP Accept Header and check for language settings

        $browserlang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        $langsplits = explode(",",strtolower($browserlang));
        $langsplitsprim = explode("-",$langsplits[0]);
        $langsplitsprim = explode(";",$langsplitsprim[0]);
        $langsplitssec = explode("-",$langsplits[1]);
        $langsplitssec = explode(";",$langsplitssec[0]);

        // Keyword addnewlang - SET UP ACTIVE LANGUAGES HERE!

        switch ($langsplitsprim[0]) {
            case "en":
                $flanguage = "en_US";
                break;
            case "de":
                $flanguage = "de_DE";
                break;
            case "ru":
                $flanguage = "ru_RU";
                break;
            case "fr":
                $flanguage = "fr_FR";
                break;
            case "es":
                $flanguage = "es_ES";
                break;
            case "it":
                $flanguage = "it_IT";
                break;
            case "pl":
                $flanguage = "en_US";
                break;
            case "pt":
                $flanguage = "pt_PT";
                break;
            case "ko":
                $flanguage = "ko_KR";
                break;
            case "kr":
                $flanguage = "ko_KR";
                break;
            case "zh":
                $flanguage = "zh_TW";
                break;
            case "ch":
                $flanguage = "zh_TW";
                break;
            case "tw":
                $flanguage = "zh_TW";
                break;
        }
        if ($flanguage) {
            $language = $flanguage;
            $langreason = "Taken from HTTP Accept Header - Primary";
        }
        else {
            switch ($langsplitssec[0]) {
                case "en":
                    $flanguage = "en_US";
                    break;
                case "de":
                    $flanguage = "de_DE";
                    break;
                case "ru":
                    $flanguage = "ru_RU";
                    break;
                case "fr":
                    $flanguage = "fr_FR";
                    break;
                case "es":
                    $flanguage = "es_ES";
                    break;
                case "it":
                    $flanguage = "it_IT";
                    break;
                case "pl":
                    $flanguage = "en_US";
                    break;
                case "pt":
                    $flanguage = "pt_PT";
                    break;
                case "ko":
                    $flanguage = "ko_KR";
                    break;
                case "kr":
                    $flanguage = "ko_KR";
                    break;
                case "zh":
                    $flanguage = "zh_TW";
                    break;
                case "ch":
                    $flanguage = "zh_TW";
                    break;
                case "tw":
                    $flanguage = "zh_TW";
                    break;
            }
        }

        if ($flanguage) {
            $language = $flanguage;
            $langreason = "Taken from HTTP Accept Header - Secondary";
        }

        else {
        // Retrieve country code from IP
        // ALTERNATIVE IP API: $iptolocation = 'http://ip-api.com/json/'.$user_ip_adress;
        // DB FOR SELF-USE???? http://lite.ip2location.com/faqs

        $iptolocation = 'http://freegeoip.net/json/'.$user_ip_adress;
        if (!$data = @file_get_contents($iptolocation)) {
            $language = "en_US";
            // echo "ip api issue";
        }
        else {

        $jsonchar = file_get_contents($iptolocation);
        $iploc = json_decode($jsonchar, TRUE);

        // Keyword addnewlang - SET UP ACTIVE LANGUAGES HERE!

        $language = $defaultlang;

        switch ($iploc['country_code']) {
                case "DE":
                        $language = "de_DE";
                        break;
                case "AT":
                        $language = "de_DE";
                        break;
                case "CH":
                        $language = "de_DE";
                        break;
                case "RU":
                        $language = "ru_RU";
                        break;
                case "BY":
                        $language = "ru_RU";
                        break;
                case "KZ":
                        $language = "ru_RU";
                        break;
                case "KG":
                        $language = "ru_RU";
                        break;
                case "FR":
                        $language = "fr_FR";
                        break;
                case "MC":
                        $language = "fr_FR";
                        break;
                case "HT":
                        $language = "fr_FR";
                        break;
                case "BJ":
                        $language = "fr_FR";
                        break;
                case "BF":
                        $language = "fr_FR";
                        break;
                case "BI":
                        $language = "fr_FR";
                        break;
                case "CF":
                        $language = "fr_FR";
                        break;
                case "TD":
                        $language = "fr_FR";
                        break;
                case "KM":
                        $language = "fr_FR";
                        break;
                case "CD":
                        $language = "fr_FR";
                        break;
                case "GA":
                        $language = "fr_FR";
                        break;
                case "DJ":
                        $language = "fr_FR";
                        break;
                case "GN":
                        $language = "fr_FR";
                        break;
                case "ML":
                        $language = "fr_FR";
                        break;
                case "NE":
                        $language = "fr_FR";
                        break;
                case "CG":
                        $language = "fr_FR";
                        break;
                case "TG":
                        $language = "fr_FR";
                        break;
                case "NC":
                        $language = "fr_FR";
                        break;
                case "MX":
                        $language = "es_ES";
                        break;
                case "CO":
                        $language = "es_ES";
                        break;
                case "ES":
                        $language = "es_ES";
                        break;
                case "AR":
                        $language = "es_ES";
                        break;
                case "PE":
                        $language = "es_ES";
                        break;
                case "VE":
                        $language = "es_ES";
                        break;
                case "CL":
                        $language = "es_ES";
                        break;
                case "EC":
                        $language = "es_ES";
                        break;
                case "GT":
                        $language = "es_ES";
                        break;
                case "CU":
                        $language = "es_ES";
                        break;
                case "BO":
                        $language = "es_ES";
                        break;
                case "DO":
                        $language = "es_ES";
                        break;
                case "HN":
                        $language = "es_ES";
                        break;
                case "PY":
                        $language = "es_ES";
                        break;
                case "SV":
                        $language = "es_ES";
                        break;
                case "NI":
                        $language = "es_ES";
                        break;
                case "CR":
                        $language = "es_ES";
                        break;
                case "PR":
                        $language = "es_ES";
                        break;
                case "PA":
                        $language = "es_ES";
                        break;
                case "UY":
                        $language = "es_ES";
                        break;
                case "GQ":
                        $language = "es_ES";
                        break;
                }
        $langreason = "Taken from GeoIP";
        }
    }
    // Set Cookie with language settings
    setcookie('language', $language, time() + (86400 * 60), "/", ".wow-petguide.com"); // 86400 = 1 day
}
}







// =================== SEARCH ENGINE ===================
// ??? TODO: If main entry is found but is ALSO found in the sub categories, check again and list entries
// ??? TODO: if no result is found, return to the current page and display a search error near the search field - OR in the main field, some kind of error message popup or so?

if ($page == "search"){
if ($searchstring != ""){
$searchstring = mysqli_real_escape_string($dbcon, $searchstring);
// Enter Search String into database

$eintragen = mysqli_query($dbcon, "INSERT INTO Searches (`IP`, `String`) VALUES ('$user_ip_adress', '$searchstring')") OR die(mysqli_error($dbcon));


$min_length = 3;
if(strlen($searchstring) >= $min_length){

$searchstring = htmlspecialchars($searchstring);

// First search: Main categories
$main_results = mysqli_query($dbcon, "SELECT * FROM Main WHERE (`Name` LIKE '%".$searchstring."%')") or die(mysqli_error($dbcon));
$subtitle_results = mysqli_query($dbcon, "SELECT * FROM Sub WHERE (`Name` LIKE '%".$searchstring."%')") or die(mysqli_error($dbcon));
$subcontent_results = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE (`Instruction` LIKE '%".$searchstring."%')") or die(mysqli_error($dbcon));

if(mysqli_num_rows($main_results) > 0 AND mysqli_num_rows($subtitle_results) == 0){
$foundentry = mysqli_fetch_object($main_results);

?>
<META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/index.php?m=<?php echo $foundentry->URLName ?>">
<?
die;
}

if(mysqli_num_rows($main_results) == 0 AND mysqli_num_rows($subtitle_results) > 0){
$foundentry = mysqli_fetch_object($subtitle_results);
?>
<META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/index.php?m=<?php echo $foundentry->Main ?>&s=<?php echo $foundentry->id ?>">
<?
die;
}
}
}
}







// ======================= USER LOGIN AND REGISTRATION PROCESS =========================

// INIIIALIZE GETTEXT AND PULL LANGUAGE FILE FIRST TIME FOR UNREGISTERED PROCESSES
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
    set_language_vars($language);

// Option 1: Battle.net Login

if ($page == "bnetlogin")
{
  list ($sendtoast, $user) = \BattleNet\login_or_register ($language);
}

// Option 2: Regular Registration

// Get Login data (relevant for registrations, those will overwrite the login with the registration name, so the login name has to be fetched first
$name = mysqli_real_escape_string($dbcon, $_POST['username']);
$pass = $_POST['password'];
$subpass = $pass;
$subpassrep = $_POST['passwordrep'];
$subname = $name;
$submail = mysqli_real_escape_string($dbcon, $_POST['email']);
$firstclick = $_POST['firstclick'];
$loginremember = $_POST['remember'];


// Processing registration data

if ($page == "register"){

$regerror = "false";

// Bypassing error detection if the registration page is loaded through direct URL
if ($subname == "" && $subpass == "" && $subpassrep == "" && $submail == "" && $firstclick == ""){
$directload = "true";
}

// Username validation

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name = '$subname'");
$usernum = mysqli_num_rows($userdb);
if ($usernum > "0") {
$regnameerror = "true";
$regerror = "true";
$regnameprob = __("This name is already in use.");
}

if (mb_strlen($subname) > "15"){
$regnameerror = "true";
$regerror = "true";
if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
$regnameprob = $regnameprob."".__("This username is too long. Please do not use more than 15 characters.");
}

if (mb_strlen($subname) < "2" && $firstclick != "true" && $directload != "true"){
$regnameerror = "true";
$regerror = "true";
if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
$regnameprob = $regnameprob."".__("This name is too short. Please use at least 2 characters.");
}

if (preg_match('/[\'\/#\$\{\}\[\ \]\|\<\>\?\"\\\]/', $subname))
{
$regnameerror = "true";
$regerror = "true";
if ($regnameprob != "" ){ $regnameprob = $regnameprob."<br>"; }
$regnameprob = $regnameprob."".__("Please do not use empty spaces or any of these characters:")."<br># < > [ ] | { } \" ' / \ $ ?";
}

if (filter_var($subname, FILTER_VALIDATE_EMAIL)) {
$regnameerror = "true";
$regerror = "true";
$regnameprob = __("Please do not use an email address as your login name.");
}




// Password validation

if (mb_strlen($subpass) < "6" && $firstclick != "true" && $directload != "true"){
$regpasserror = "true";
$regerror = "true";
$regpassprob = __("Your password must be at least 6 characters long.");
}
if ($subpass != $subpassrep){
$regpasserror = "true";
$regerror = "true";
$regpassprob = __("The two entries did not match. Please type in your preferred password again.");
}
if ($subpass == $subname && $firstclick != "true" && $directload != "true"){
$regpasserror = "true";
$regerror = "true";
$regpassprob = __("Your password cannot be identical to your username.");
}



// Email validation

if ($submail != ""){

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$submail'");
$usernum = mysqli_num_rows($userdb);
if ($usernum > "0") {
$regmailerror = "true";
$regerror = "true";
$regmailprob = __("This email address is already registered to another account.");
}


if (!filter_var($submail, FILTER_VALIDATE_EMAIL)) {
$regmailerror = "true";
$regerror = "true";
$regmailprob = __("The email address you entered is not valid.");
}

}


// Actual registration into db happens here:

if ($regerror == "false" && $firstclick != "true" && $directload != "true"){

$hash = hash_passwords($subpass);

// Select random Icon for user:

$dirrand = "images/pets";
$dhrand = opendir($dirrand);
while (false !== ($filename = readdir($dhrand))) {

$filesplits = explode(".",$filename);

if ($filesplits[1] == "png" && $filesplits[0] >= "25" && preg_match("/^[1234567890]*$/is", $filesplits[0])){
$iconlist[] = $filesplits[0];
}
}
$rand_icon = array_rand($iconlist, 1);
$randomicon = $iconlist[$rand_icon];
$comsecret = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);

mysqli_query($dbcon, "INSERT INTO Users (`Name`, `Hash`, `ComSecret`, `Email`, `regip`, `Icon`, `Language`) VALUES ('$subname', '$hash', '$comsecret', '$submail', '$user_ip_adress', '$randomicon', '$language')") OR die(mysqli_error($dbcon));
$newuserid = mysqli_insert_id($dbcon);
$wlcmsgsub = __("Welcome to Xu-Fu's Pet Guides!");
$wlcmsgmsg = __("Thank you for signing up with Xu-Fu's Pet Guides!<br><br>There are many new options available to you now, most of which you can find in your account area right here.<br>I suggest going to your Pet Collection first to make sure your pet library has been imported correctly. This will power a lot of the more advanced features here!<br><br>If you go to My Profile, you can select what other battlers see when clicking on your profile. Maybe leave them a little introduction about yourself? Or just turn off all areas if you prefer a bit more privacy.<br><br>You might also want to check out the Settings. There you can not only change your profile picture but also find options for your saved email, password or preferred language.<br><br>I hope you have a pleasant stay here and wish you all the best in your battles!<br><br>Yours,<br>Xu-Fu");
$wlcmsgmsg = preg_replace('#<br\s*/?>#i', "\n", $wlcmsgmsg);
$wlcmsgsub = mysqli_real_escape_string($dbcon, $wlcmsgsub);
$wlcmsgmsg = mysqli_real_escape_string($dbcon, $wlcmsgmsg);
mysqli_query($dbcon, "INSERT INTO UserMessages (`Sender`, `Receiver`, `Subject`, `Content`, `Type`, `Growl`) VALUES ('1', '$newuserid', '$wlcmsgsub', '$wlcmsgmsg', '1', '9')") OR die(mysqli_error($dbcon));
mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$newuserid', '$user_ip_adress', 0, 'Registration completed', 'Normal Registration')") OR die(mysqli_error($dbcon));


// Fetch data for Login page to process again
$page = "login";
$name = $_POST['username'];
$pass = $_POST['password'];
$sendtoast = "registersuccess";

}
}



// ======================= PASSWORD RESET FUNCTIONALITY HERE - =========================
// Has to be before the login so on successful pw change the user can be logged in directly

// Three possible $resetpage states:
// A) "invalid" reset link expired, not existant, or any other problem with it: invalid link, sorry!
// B) "enterpw" enter a new password (with error handling)
// C) "pwset" New password entered, logging in and redirecting to front page

// Logic: First we check if there is a user with that reset code

$pwstring = $_GET['pwstring'];

if ($page == "setpw" AND $pwstring) {
    $pwstring = mysqli_real_escape_string($dbcon, $pwstring);
    $pwstring = stripslashes(htmlentities($pwstring, ENT_QUOTES, "UTF-8"));
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE ResetCode = '$pwstring'");
    $usernum = mysqli_num_rows($userdb);

    if ($usernum < "1"){
        $resetpage = "invalid";
    }

    // Second, if there actually IS a user with that reset code, we check when this code was generated for the 24h limit:
    if ($resetpage != "invalid"){
        $checkuser = mysqli_fetch_object($userdb);
        $userprotdb = mysqli_query($dbcon, "SELECT * FROM UserProtocol WHERE Comment = '$pwstring' AND Activity = 'Password Reset Mail Requested' AND  Date >= NOW()- INTERVAL 24 HOUR");

        if (mysqli_num_rows($userprotdb) < "1") {
            $resetpage = "invalid";
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$checkuser->id', '$user_ip_adress', 0, 'PW Reset Link clicked - expired', '$pwstring')") OR die(mysqli_error($dbcon));
        }
        else {
            $resetpage = "enterpw";
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$checkuser->id', '$user_ip_adress', 0, 'PW Reset Link clicked - valid', '$pwstring')") OR die(mysqli_error($dbcon));
        }
    }

    // Third - new password was submitted and is being checked now
    $submitpw = $_POST['submitpw'];

    if ($submitpw == "true") {

        $subpass = $_POST['password'];
        $subpassrep = $_POST['passwordrep'];

        if (mb_strlen($subpass) < "6"){
            $setpasserror = "true";
            $setpassprob = __("Your password must be at least 6 characters long.");
        }
        if ($subpass != $subpassrep){
            $setpasserror = "true";
            $setpassprob = __("The two entries did not match. Please type in your preferred password again.");
        }
        if ($subpass == $checkuser->Name){
            $setpasserror = "true";
            $setpassprob = __("Your password cannot be identical to your username.");;
        }

        // Password entered is valid and user is logged in
        if ($setpasserror != "true"){
            $resetpage = "pwset";
            $hash = hash_passwords($subpass);
            $update = mysqli_query($dbcon, "UPDATE Users SET `Hash` = '$hash' WHERE id = '$checkuser->id'");
            $update = mysqli_query($dbcon, "UPDATE Users SET `ResetCode` = '' WHERE id = '$checkuser->id'");
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$checkuser->id', '$user_ip_adress', '0', 'Password changed from PW reset mail', '$pwstring')") OR die(mysqli_error($dbcon));

            // Fetch data for Login page to process again
            $page = "login";
            $name = $checkuser->Name;
            $pass = $subpass;
            $sendtoast = "pwchangesuccess";
        }
    }
}







// Processing login data

if ($page == "login") {
    $loginfail = "false";

    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name = '$name'");
    $usernum = mysqli_num_rows($userdb);

    $usermaildb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$name'");
    $usermailnum = mysqli_num_rows($usermaildb);

    if ($usernum < "1" && $usermailnum < "1") {
        $loginfail = "true";
        $loginnamefail = "true";
        $loginnamefailreason = __("No user with that name or email address found.");
    }

    // Name check was positive, there is a user with that name or email address in the DB:
    if ($loginfail == "false") {

        if ($usernum > "0") {
            $usercheck = mysqli_fetch_object($userdb);
        }
        if ($usermailnum > "0") {
            $usercheck = mysqli_fetch_object($usermaildb);
        }

        // Check if account is locked by admin
        if ($usercheck->locked > "0") {
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$usercheck->id', '$user_ip_adress', '1', 'Failed Login Attempt', 'Account Locked by Admin')") OR die(mysqli_error($dbcon));
            $loginfail = "true";
            $loginlocked = "true";
            $loginnamefail = "true";
            $loginnamefailreason = __("Your account is locked. Please contact a site admin for help.");
        }

        // Check if account is locked from further logins
        $lockdb = mysqli_query($dbcon, "SELECT * FROM UserProtocol WHERE User = '$usercheck->id' AND Activity = 'Account Locked' AND Comment = 'Too often incorrect password' AND  Date >= NOW()- INTERVAL 5 MINUTE");
        if (mysqli_num_rows($lockdb) > "0") {
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$usercheck->id', '$user_ip_adress', '1', 'Failed Login Attempt', 'Account Temp-Locked')") OR die(mysqli_error($dbcon));
            $loginfail = "true";
            $loginlocked = "true";
            $loginnamefail = "true";
            $loginnamefailreason = __("Your account is temporarily locked because of too many failed login attempts.<br> Please try again in 10 minutes.");
        }

        // User exists and entry is fetched successfully, login process (unless account is locked)
        if (password_verify($pass, $usercheck->Hash) AND $loginlocked != "true") {

            if ($sendtoast == ""){
               $sendtoast = "loginsuccess";
            }

            // Set session variables
            $_SESSION["logged_in"] = "true";
            $_SESSION["userid"] = $usercheck->id;
            $language = $usercheck->Language;
            $user = $usercheck;
            // Set cookies depending on login flag (30 day remember yes / no)
            if ($loginremember == "true") {
                if ($user->Email){
                    $snippets = explode("@", $user->Email);
                    $cookiehash = md5(mb_substr($user->Hash, 7, 5).mb_substr($user->Name, 0, 2).mb_substr($snippets[0], -7)."2d8s2f".mb_substr($snippets[1], 0, 4));
                }
                else {
                    $cookiehash = md5(mb_substr($user->Hash, 7, 5)."2d8s2f5x".mb_substr($user->Name, 0, 2));
                }

                setcookie('language_delimiter', base64_encode($user->id), time() + (86400 * 30), "/", ".wow-petguide.com"); // 86400 = 1 day
                setcookie('language_stock', $cookiehash, time() + (86400 * 30), "/", ".wow-petguide.com"); // 86400 = 1 day
                $update = mysqli_query($dbcon, "UPDATE Users SET `CHash` = '$cookiehash' WHERE id = '$user->id'");
                $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '1', 'Successful Login', 'Cookie Selected')") OR die(mysqli_error($dbcon));
            }
            else {
                $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '1', 'Successful Login', 'No Cookie Selected')") OR die(mysqli_error($dbcon));
            }
        }

        // Password was incorrect - error handling here
        else if ($loginlocked != "true"){
            // Enter unsuccessfull attempt into database
            $loginfail = "true";
            $loginpassfail = "true";
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$usercheck->id', '$user_ip_adress', '0', 'Failed Login Attempt', 'Incorrect Password')") OR die(mysqli_error($dbcon));

            // Check unsuccessful logins (wrong password) of last 5 minutes
            $loginsdb = mysqli_query($dbcon, "SELECT * FROM UserProtocol WHERE User = '$usercheck->id' AND Activity = 'Failed Login Attempt' AND Comment = 'Incorrect Password' AND  Date >= NOW()- INTERVAL 5 MINUTE");

            if (mysqli_num_rows($loginsdb) < "3") {
                $loginpassreason = __("Submitted password was not correct.");
            }
            if (mysqli_num_rows($loginsdb) >= "3" AND mysqli_num_rows($loginsdb) < "5") {
                $loginpassreason = __("Submitted password incorrect. <br>Your account will be temporarily locked if you enter a wrong password too often.");
            }
            if (mysqli_num_rows($loginsdb) >= "5") {
                $loginpassreason = __("Incorrect password. <br>Your account has been temporarily locked. <br>Please try again in 10 minutes.");
                $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$usercheck->id', '$user_ip_adress', '0', 'Account Locked', 'Too often incorrect password')") OR die(mysqli_error($dbcon));
            }
        }
    }
}




// ======================= USER SESSION AND COOKIE CHECKS =========================

$checkuserid = $_SESSION["userid"];
if (!$user AND $_SESSION["logged_in"] == "true") {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$checkuserid'");
    if (mysqli_num_rows($userdb) > "0"){
        $user = mysqli_fetch_object($userdb);
        $language = $user->Language;
        $_SESSION["lang"] = $language;
        $protcomment = "User data was saved in Session - Page: ".$page;
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '2', 'Regular User Visit', '$protcomment')") OR die(mysqli_error($dbcon));
    }
    else {
        if ($sendtoast != "accdeleted") {
            session_destroy();
        }

    }
}

if ($_SESSION["logged_in"] != "true" && $_COOKIE["language_delimiter"]){
    $cookieuserid = base64_decode($_COOKIE["language_delimiter"]);
    $cookieuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = ' $cookieuserid'");
    if (mysqli_num_rows($cookieuserdb) > "0"){
        $cookieuser = mysqli_fetch_object($cookieuserdb);

        if ($cookieuser->Email){
            $snippets = explode("@", $cookieuser->Email);
            $controlhash = md5(mb_substr($cookieuser->Hash, 7, 5).mb_substr($cookieuser->Name, 0, 2).mb_substr($snippets[0], -7)."2d8s2f".mb_substr($snippets[1], 0, 4));
        }
        else {
            $controlhash = md5(mb_substr($cookieuser->Hash, 7, 5)."2d8s2f5x".mb_substr($cookieuser->Name, 0, 2));
        }
        if ($controlhash == $_COOKIE["language_stock"]) {  // Success -the cookie and the control hash match, user is logged in again
            session_destroy(); // killing previous session
            $some_name = session_name("initialize");
            session_set_cookie_params(0, '/', '.wow-petguide.com');
            session_start();
            $_SESSION["logged_in"] = "true";
            $_SESSION["userid"] = $cookieuser->id;
            $language = $cookieuser->Language;
            $user = $cookieuser;
            $protcomment = "User data was saved in Cookie - Page: ".$page;
            $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '2', 'Regular User Visit', '$protcomment')") OR die(mysqli_error($dbcon));
        }
    }
}


// ======================= USER LOGOUT =========================

if ($page == "logout" OR $sendtoast == "accdeleted"){
    if ($_SESSION["logged_in"] == "true") {
        if (!$user) $userid = 0;
        else $userid = $user->id;
        $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`) VALUES ('$userid', '$user_ip_adress', '1', 'User Logout')") OR die(mysqli_error($dbcon));
        $_SESSION["logged_in"] = "false";
        session_destroy();
        setcookie("language_delimiter", "", time()-60*60*24*365, "/", ".wow-petguide.com");
        setcookie("language_stock", "", time()-60*60*24*365, "/", ".wow-petguide.com");
        $user = "";
    }
}

  // Hacky language selector for user Inovindil
  if ($user->id == 19733 && $page == 'make_de') {
    $update = mysqli_query($dbcon, "UPDATE Users SET `Language` = 'de_DE' WHERE id = '$user->id'");
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
    $user = mysqli_fetch_object($userdb);
  }
  if ($user->id == 19733 && $page == 'make_en') {
    $update = mysqli_query($dbcon, "UPDATE Users SET `Language` = 'en_US' WHERE id = '$user->id'");
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
    $user = mysqli_fetch_object($userdb);
  }
  // End of hack


// ======================= USER PROFILE PAGE VERIFICATION ETC =========================

$mainshow = "";
$mainentry = "";
$skipurlchange = false;

// Redirect all registration or login pages to main if user is logged in already
if (($page == "register" OR $page == "setpw" OR $page == "pwrecover" OR $page == "acretrieve") AND $_SESSION["logged_in"] == "true"){
    $mainselector = "";
    $mainshow = "Home";
    $urlchanger = "index.php";
    $sendtoast = "linkerror";
    $page = "";
}

// Add here ALL pages that need to be excluded from redirecting to main
if ($page == "404" OR $page == "adm_menu" OR $page == "adm_images" OR $page == "compare" OR $page == "petlist" OR $page == "strategies" OR $page == "settings" OR $page == "admin"  OR $page == "adm_comreports" OR $page == "adm_petimport" OR $page == "adm_peticons" OR $page == "adm_breeds" OR $page == "profile" OR $page == "collection" OR $page == "icon" OR $page == "tooltip" OR $page == "messages" OR $page == "sentmsgs" OR $page == "mycomments" OR $page == "writemsg"){
    $skipurlchange = true;
    $urlchanger = "";
}

// Add here ALL pages that need to redirect to main if no user is logged in
if (($page == "adm_menu" OR $page == "adm_images" OR $page == "strategies" OR $page == "settings" OR $page == "profile" OR $page == "collection" OR $page == "icon" OR $page == "tooltip" OR $page == "messages" OR $page == "sentmsgs" OR $page == "mycomments" OR $page == "writemsg") AND $_SESSION["logged_in"] != "true"){
    $mainselector = "";
    $mainshow = "Home";
    $urlchanger = "index.php";
    $sendtoast = "linkerror";
    $page = "";
}

// Add here all admin pages:
$userrights = format_userrights($user->Rights);
if (($page == "adm_menu" OR $page == "admin" OR $page == "adm_breeds" OR $page == "adm_peticons" OR $page == "adm_images" OR $page == "adm_comreports" OR $page == "adm_petimport") AND $userrights['AdmPanel'] != "on"){
    $mainselector = "";
    $mainshow = "Home";
    $urlchanger = "index.php";
    $sendtoast = "linkerror";
    $page = "";
}

// making sure no one is bypassing the page setting:
if ($page == "viewuser"){
    $page = "";
}

// a user is being looked at, making sure there is one and setting correct page:
if ($viewuser){
    $checkuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$viewuser' LIMIT 1");
    if (mysqli_num_rows($checkuserdb) < "1"){
        $viewuser = "";
        $mainselector = "";
        $mainshow = "Home";
        $urlchanger = "index.php";
        $sendtoast = "linkerror";
        $page = "";
    }
    else {
        $viewuser = mysqli_fetch_object($checkuserdb);
        $page = "viewuser";
        $urlchanger = "";
    }
}



// ======================= LANGUAGE OVERRIDE =========================

if ($_SESSION["logged_in"] == "true") {
    $language = $user->Language;
}
// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE AGAIN
_setlocale(LC_MESSAGES, $language);
set_language_vars($language);

// ======================= Create Array of All Pets =========================
// Can only be done here because only now the language is set
$all_pets = get_all_pets($petnext);

// ======================= Create Array of All Tags =========================
$all_tags = get_all_tags();

// ======================= Grabbing Battle.net User Data if present =========================

if ($_SESSION["logged_in"] == "true") {
    $bnetdb = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id' LIMIT 1");
    if (mysqli_num_rows($bnetdb) > "0") {
        $bnetuser = mysqli_fetch_object($bnetdb);
    }
}





// ======================= SETTING VARIABLES FOR USER =========================

if ($user) {
    if ($user->UseWowAvatar == "0"){
        $usericon = 'src="https://www.wow-petguide.com/images/pets/'.$user->Icon.'.png"';
    }
    else if ($user->UseWowAvatar == "1"){
        $usericon = 'src="https://www.wow-petguide.com/images/userpics/'.$user->id.'.jpg?lastmod?='.$user->IconUpdate.'"';
    }
    $usersettings = format_usersettings($user->Settings);
    $userrights = format_userrights($user->Rights);
}

// ======================= SETTING VARIABLE FOR COMMENT FILTERING =========================

$jumpto = null;

$changecomfilter = isset($_POST['changecomfilter']) ? $_POST['changecomfilter'] : false;
if ($changecomfilter == "true") {
    $comfilter = $_POST['comfilter'];
    if ($user){
        switch ($comfilter) {
            case "newest":
                set_settings($user,'0','0');
                break;
            case "oldest":
                set_settings($user,'0','1');
                break;
            case "votes":
                set_settings($user,'0','2');
                break;
            case "voteslow":
                set_settings($user,'0','3');
                break;
        }
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
        $user = mysqli_fetch_object($userdb);
    }
    else {
        switch ($comfilter) {
            case "newest":
                $_SESSION["comfilter"] = "0";
                break;
            case "oldest":
                $_SESSION["comfilter"] = "1";
                break;
            case "votes":
                $_SESSION["comfilter"] = "2";
                break;
            case "voteslow":
                $_SESSION["comfilter"] = "3";
                break;
        }
    }
    $jumpto = "com_header";
    $jumpanchor = "";
    $comcat = $_POST['comcat'];
    if ($comcat == "1"){
        $jumpto = $_POST['comsortid'];
    }
    if ($comcat == 0 && $_POST['comsortid'] == 11){
        $mainselector = "";
    }
    $tcomnatoren = $_POST['comnatoren'];
}

// ======================= PROCESSING COMMENT SUBMISSION =========================

if ($submitcomment == "true"){
    include 'classes/com_process.php';
}

// ======================= Processing Player collection =======================

if ($user) {  
    $findcol = find_collection($user);
    if ($findcol != "No Collection") {
        $fp = fopen($findcol['Path'], 'r');
        if ($fp !== FALSE) {
          $collection = json_decode(fread($fp, filesize($findcol['Path'])), true);
          if ($collection) {
            foreach ($collection as $key => $pet) {
                $collection[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);    
            }
          }
        }
    }
}



// =================== DELETING A STRATEGY ===============

if (\HTTP\argument_POST_or_default ('alt_edit_action', FALSE) === 'edit_delete')
{
  try
  {
    list ($mainselector, $subselector)
      = \Strategy\delete (\HTTP\argument_POST ('currentstrat'), $user);
    $sendtoast = "stratdeleted";
  }
  catch (Exception $e)
  {
    $sendtoast = "genericerror";
  }
}




// =================== SELECTION OF CORRECT PAGE ===============

// #1 Get categories from a given comment

$urlchanged = false;

if ($comment) {
    if (!preg_match("/^[1234567890]*$/is", $comment)) {                                   // Given comment ID not number format
        $comment = "";
        $sendtoast = "linkerror";
    }
    else {
        $targetcommentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$comment' AND Deleted = '0' AND Category != '3' LIMIT 1");
        if (mysqli_num_rows($targetcommentdb) > "0"){
            $targetcomment = mysqli_fetch_object($targetcommentdb);

            if ($targetcomment->User != "0") {
                $tcomuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$targetcomment->User'");
                if (mysqli_num_rows($tcomuserdb) > "0") {
                    $tcomuser = mysqli_fetch_object($tcomuserdb);
                    $tcomuserrole = $tcomuser->Role;
                }
            }

            if ($userrights[0] != "1") {                                                      // Only do error checking if the logged in user is a regular user. Those with settings "can see all comments" skip error checking
                if ($targetcomment->Votes < $pagesettings->Com_Hidden AND $tcomuserrole < 50 AND $targetcomment->User != $user->id) {        // Comment is only available if it has enough votes OR is from an admin or moderator OR is from the logged in user
                    $tcomabort = "true";
                }
                if ($tcomuserrole < 50 && $tcomabort != "true") {
                    $tcominappreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '0' AND SortingID = '$targetcomment->id'");
                    $tcomspamreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '1' AND SortingID = '$targetcomment->id'");
                    $tcombinedthresh = mysqli_num_rows($tcomspamreportdb)+mysqli_num_rows($tcominappreportdb);
                    if (mysqli_num_rows($tcominappreportdb) >= $pagesettings->Com_ReportInappThresh OR mysqli_num_rows($tcomspamreportdb) >= $pagesettings->Com_ReportSpamThresh OR $tcombinedthresh >= $pagesettings->Com_ReportInappThresh) {
                        $tcomabort = "true";                                                    // Only add comment if it doesn't have too many reports against it
                    }
                }
                if ($user) {
                    $tcomuserreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND User = '$user->id' AND SortingID = '$targetcomment->id'");
                    if (mysqli_num_rows($tcomuserreportdb) > "0") {
                        $tuserreport = mysqli_fetch_object($tcomuserreportdb);
                        if (($tuserreport->Type == "0" OR $tuserreport->Type == "1") && $tcomuserrole < 50) {
                            $tcomabort = "true";                                                // This user made a report for spam or inappropriate ==> do not load comment
                        }
                    }
                }
            }

            if ($targetcomment->Parent != "0" && $tcomabort != "true") {                                          // Check if main or subcomment
                $tcomhighlight = $comment;
                $targetcommentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$targetcomment->Parent' AND Deleted = '0' AND Category != '3' LIMIT 1");
                if (mysqli_num_rows($targetcommentdb) > "0"){
                    $targetcomment = mysqli_fetch_object($targetcommentdb);               // Check if parent comment can be viewed or not
                    $tcomuser = "";
                    $tcomuserrole = "";
                    if ($targetcomment->User != "0") {
                        $tcomuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$targetcomment->User'");
                        if (mysqli_num_rows($tcomuserdb) > "0") {
                            $tcomuser = mysqli_fetch_object($tcomuserdb);
                            $tcomuserrole = $tcomuser->Role;
                        }
                    }

                    if ($userrights[0] != "1") {                                                      // Only do error checking if the logged in user is a regular user. Those with settings "can see all comments" skip error checking
                        if ($targetcomment->Votes < $pagesettings->Com_Hidden AND $tcomuserrole < 50 AND $targetcomment->User != $user->id) {        // Comment is only available if it has enough votes OR is from an admin or moderator OR is from the logged in user
                            $tcomabort = "true";
                        }

                        if ($tcomuserrole < 50 && $tcomabort != "true") {
                            $tcominappreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '0' AND SortingID = '$targetcomment->id'");
                            $tcomspamreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '1' AND SortingID = '$targetcomment->id'");
                            $tcombinedthresh = mysqli_num_rows($tcomspamreportdb)+mysqli_num_rows($tcominappreportdb);
                            if (mysqli_num_rows($tcominappreportdb) >= $pagesettings->Com_ReportInappThresh OR mysqli_num_rows($tcomspamreportdb) >= $pagesettings->Com_ReportSpamThresh OR $tcombinedthresh >= $pagesettings->Com_ReportInappThresh) {
                                $tcomabort = "true";                                                    // Only add comment if it doesn't have too many reports against it
                            }
                        }

                        if ($user) {
                            $tcomuserreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND User = '$user->id' AND SortingID = '$targetcomment->id'");
                            if (mysqli_num_rows($tcomuserreportdb) > "0") {
                                $tuserreport = mysqli_fetch_object($tcomuserreportdb);
                                if (($tuserreport->Type == "0" OR $tuserreport->Type == "1") && $tcomuserrole < 50) {
                                    $tcomabort = "true";                                                // This user made a report for spam or inappropriate ==> do not load comment
                                }
                            }
                        }
                    }
                }
                else {
                    $tcomabort = "true";
                }
            }

            if ($tcomabort != "true") {
                switch ($targetcomment->Category) {
                case "0":
                    $mainselector = $targetcomment->SortingID;
                    break;
                case "1":
                    $mainselector = "";
                    $news_article_id = $targetcomment->SortingID;
                    $tcomsortid = $targetcomment->SortingID;
                    break;
                case "2":
                    $strategy = $targetcomment->SortingID;
                    break;
                }
                if ($mainselector == 11) {  // If the comment is on the home page, remove mainselector because it's "" for the landing page
                    $mainselector = "";
                }
                $tcomid = $targetcomment->id;
                $tcomlanguage = $targetcomment->Language;

                if (!$tcomhighlight) {
                    $tcomhighlight = $tcomid;
                }
                $jumpto = "CM_".$tcomhighlight;

                $tcomcat = $targetcomment->Category;
                $tcomlang = decode_language($targetcomment->Language);
                if ($tcomlang['short'] == "EN") {
                    $tcomnatoren = "en";
                }
                else {
                    $tcomnatoren = "nat";
                }
                $urlchanged = true;
            }
            else {
                $comment = "";
                $sendtoast = "linkerror";
            }
        }
        else {
            $comment = "";
            $sendtoast = "linkerror";
        }
    }
}


// ======================= NEWS ARTICLE VERIFICATION =========================

// Add new article
if (($news_article_id == "add_main" OR $news_article_id == "add_small")  && $userrights['EditNews'] == "yes") {
    $new_news_type = 0;
    if ($news_article_id == "add_main") {
        $new_news_type = 1;
    }
    mysqli_query($dbcon, "INSERT INTO News_Articles (`Active`, `Category`, `Main`, `CreatedBy`, `Title_en_US`, `Content_en_US`) VALUES ('0', '2', '$new_news_type', '$user->id', 'New Article', 'New Article Content')") OR die(mysqli_error($dbcon));
    $news_article_id = mysqli_insert_id($dbcon);
}

// Format for normal visit
if (!preg_match("/^[1234567890]*$/is", $news_article_id)) {                              
    $news_article_id = '';
}
if ($news_article_id) {
    $news_article_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE id = $news_article_id AND Deleted != 1");
    if (mysqli_num_rows($news_article_db) == 0) {
        $news_article_id = '';
    }
    else {
        $news_article = mysqli_fetch_object($news_article_db);
        // Get all categories into an array
        $categories_db = mysqli_query($dbcon, "SELECT * FROM News_Categories");
        while ($this_category = mysqli_fetch_object($categories_db)) {
            $news_categories[$this_category->id]['ID'] = $this_category->id;
            $news_categories[$this_category->id]['Name'] = $this_category->Name;
            $news_categories[$this_category->id]['Color'] = $this_category->Color;
        }
    }
}

$old_collection_id = $_GET['Collection'];
if ($old_collection_id) {
    $sendtoast = "old_collection";
    $mainselector = 40;
    $mainshow = "Home";
    $urlchanger = "?m=Collection";
}

// #2 Check main category and transform between name and ID
if ($mainselector != ""){
    $mainselcheck = mysqli_real_escape_string($dbcon, $mainselector);
    
    $maincheckdb = mysqli_query($dbcon, "SELECT * FROM Main WHERE URLName LIKE '$mainselcheck'");
    if (mysqli_num_rows($maincheckdb) > "0") {                                                // Mainselector has Name format and is found in the database
        $mainentry = mysqli_fetch_object($maincheckdb);
        $mainselector = $mainentry->id;
        $mainshow = $mainentry->URLName;
    }
    else {
        if (preg_match("/^[1234567890]*$/is", $mainselcheck)) {                               // No main category has been found and the submitted one is number format
            $maincheckdb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$mainselcheck'");
            if (mysqli_num_rows($maincheckdb) > "0") {                                       // Mainselector has Name format and is found in the database
                $mainentry = mysqli_fetch_object($maincheckdb);
                $mainselector = $mainentry->id;
                $mainshow = $mainentry->URLName;
                if ($urlchanged != true && $skipurlchange != true) {
                    $urlchanger = "?m=".$mainshow;
    
                    $urlrevision = $_GET['rev'];
                    if ($urlrevision) {
                        $urlchanger = $urlchanger."&rev=".$urlrevision;
                    }
                    $urllng = $_GET['lng'];
                    if ($urllng) {
                        $urlchanger = $urlchanger."&lng=".$urllng;
                    }
                }
            }
            else {
                $mainselector = "";                                                         // Defaulting to Landing Page
                $mainshow = "Home";
                $urlchanger = "index.php";
                $sendtoast = "linkerror";
            }
        }
        else {
            $mainselector = "";                                                             // Defaulting to Landing Page
            $mainshow = "Home";
            $urlchanger = "index.php";
            $sendtoast = "linkerror";
        }
    }
}

// =================== ADDING A NEW STRATEGY ===================

if ($user) {
    $addstratcmd = isset($_POST['alt_edit_action']) ? $_POST['alt_edit_action'] : null;
    if ($addstratcmd == "edit_add") {
        $addcurrentstrat = $_POST['currentstrat'];
        if ($addcurrentstrat != "") {
            $addstratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $addcurrentstrat");
            if (mysqli_num_rows($addstratdb) > "0"){
                $addstrat = mysqli_fetch_object($addstratdb);
                $addmain = $addstrat->Main;
                $addsub = $addstrat->Sub;
            }
        }
        if (!$addmain) {
            $addinputmain = $_POST['addmain'];
            $addinputsub = $_POST['addsub'];
            $addmaindb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$addinputmain'");
            $addsubdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = '$addinputsub'");
            if (mysqli_num_rows($addmaindb) > "0" && mysqli_num_rows($addsubdb) > "0") {
                $addmain = $addinputmain;
                $addsub = $addinputsub;
            }
        }
        if (!$addmain && !$addsub) {
            $sendtoast = "erroraddingstrat";
        }
        else {
            $rematch_import = $_POST['rematch_import'];
            $updatetags = "";
            if ($rematch_import != "") {
                $rematch_parts = explode(":", $rematch_import);
                for ($i = 0; $i <= 2; $i++) {
                    $o = $i+2;
                    if (strlen($rematch_parts[$o]) > 3) {
                        $pet_skills[$i][0] = $rematch_parts[$o][0];
                        $pet_skills[$i][1] = $rematch_parts[$o][1];
                        $pet_skills[$i][2] = $rematch_parts[$o][2];
                        $searchFor = base_convert(substr($rematch_parts[$o], 4), 32, 10);
                        $pet_ids[$i] = "1";
                        if ($all_pets[$searchFor]['Name']) {
                            $pet_ids[$i] = $searchFor;
                            if ($all_pets[$searchFor]['Source'] == 2) $updatetags[5] = 1;
                            if ($all_pets[$searchFor]['Source'] == 1) $updatetags[6] = 1;
                            if ($all_pets[$searchFor]['Obtainable'] == 2) $updatetags[18] = 1;
                        }
                    }
                    else {
                        switch ($rematch_parts[$o]) {
                           case "ZL":
                             $pet_ids[$i] = 0;
                             $pet_min_level[$i] = $rematch_parts[10];
                             $updatetags[7] = 1;
                             break;
                           case "ZR0":
                             $pet_ids[$i] = 1;
                             break;
                           case "ZR1":
                             $pet_ids[$i] = 11;
                             break;
                           case "ZR2":
                             $pet_ids[$i] = 20;
                             break;
                           case "ZR3":
                             $pet_ids[$i] = 16;
                             break;
                           case "ZR4":
                             $pet_ids[$i] = 14;
                             break;
                           case "ZR5":
                             $pet_ids[$i] = 17;
                             break;
                           case "ZR6":
                             $pet_ids[$i] = 12;
                             break;
                           case "ZR7":
                             $pet_ids[$i] = 13;
                             break;
                           case "ZR8":
                             $pet_ids[$i] = 19;
                             break;
                           case "ZR9":
                             $pet_ids[$i] = 18;
                             break;                
                           case "ZRA":
                             $pet_ids[$i] = 15;
                             break;
                        }
                        if ($pet_ids[$i] == "" && $pet_ids[$i] != 0) {
                            $pet_ids[$i] = 1;
                        }
                    }
                } 
                $add_pet_details = true;
            }
            if ($add_pet_details == true) {
                $pet_skills00 = $pet_skills[0][0];
                $pet_skills01 = $pet_skills[0][1];
                $pet_skills02 = $pet_skills[0][2];
                $pet_skills10 = $pet_skills[1][0];
                $pet_skills11 = $pet_skills[1][1];
                $pet_skills12 = $pet_skills[1][2];
                $pet_skills20 = $pet_skills[2][0];
                $pet_skills21 = $pet_skills[2][1];
                $pet_skills22 = $pet_skills[2][2];
                mysqli_query($dbcon, "INSERT INTO Alternatives (`User`, `Main`, `Sub`, `PetID1`, `PetLevel1`, `SkillPet11`, `SkillPet12`, `SkillPet13`, `PetID2`, `PetLevel2`, `SkillPet21`, `SkillPet22`, `SkillPet23`, `PetID3`, `PetLevel3`, `SkillPet31`, `SkillPet32`, `SkillPet33`) VALUES ('$user->id', '$addmain', '$addsub', '$pet_ids[0]', '$pet_min_level[0]', '$pet_skills00', '$pet_skills01', '$pet_skills02', '$pet_ids[1]', '$pet_min_level[1]', '$pet_skills10', '$pet_skills11', '$pet_skills12', '$pet_ids[2]', '$pet_min_level[2]', '$pet_skills20', '$pet_skills21', '$pet_skills22')") OR die(mysqli_error($dbcon));
            }
            else {
                mysqli_query($dbcon, "INSERT INTO Alternatives (`User`, `Main`, `Sub`, `PetID1`, `PetID2`, `PetID3`) VALUES ('$user->id', '$addmain', '$addsub', '1', '1', '1')") OR die(mysqli_error($dbcon));
            }
            $strategy = mysqli_insert_id($dbcon);
            $updatetags = array();
            $updatetags[21] = 1;
            update_tags($strategy, $updatetags);
            $urlchanger = "?Strategy=".$strategy;
            $mainselector = $addmain;
            $subselector = $addsub;
            $stratbyudb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id'");
            if (mysqli_num_rows($stratbyudb) == "1") {
                $sendtoast = "firstnewstrat";
            }
            else {
                $sendtoast = "addednewstrat";
            }
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 1')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 2')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 3')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 4')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 5')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 6')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 7')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$strategy', 'Turn 8')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'New Strategy Added', '$strat->id')") OR die(mysqli_error($dbcon));

        }
    }
}


// #3 A strategy ID has been passed on

$directstrat = false;

if ($strategy != ""){
    if (!preg_match("/^[1234567890]*$/is", $strategy)) {                                  // Strategy ID is not a number
        $mainselector = "";                                                             // Defaulting to Landing Page
        $urlchanger = "index.php";
        $sendtoast = "linkerror";
        $strategy = "";
    }
    else {                                                                      // Strategy ID is a number
        $stratcheckdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$strategy'");
        if (mysqli_num_rows($stratcheckdb) > "0"){                                        // Strategy is present in DB
            $stratcheck = mysqli_fetch_object($stratcheckdb);
            if ($stratcheck->Published == "1" OR $userrights['EditStrats'] == "yes" OR $stratcheck->User == $user->id) {
                if ($stratcheck->Deleted == 1 && $userrights['EditStrats'] != "yes") {
                    $mainselector = "";
                    $urlchanger = "index.php";                                                    // Defaulting to Landing Page
                    $sendtoast = "strathidden";
                    $strategy = "";  
                }
                if ($stratcheck->Deleted == 0 OR $userrights['EditStrats'] == "yes") {
                    $mainselector = $stratcheck->Main;
                    $subselector = $stratcheck->Sub;
                    if ($urlchanged != true) {
                        $urlchanger = "?Strategy=".$strategy;
                    }
                    $directstrat = true;
                    $maincheckdb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$mainselector'");
                    $mainentry = mysqli_fetch_object($maincheckdb);
                    $mainshow = $mainentry->URLName;
                }
            }
            else {
                $mainselector = "";
                $urlchanger = "index.php";                                                    // Defaulting to Landing Page
                $sendtoast = "strathidden";
                $strategy = "";
            }
        }
        else {
            $mainselector = "";
            $urlchanger = "index.php";                                                    // Defaulting to Landing Page
            $sendtoast = "stratgone";
            $strategy = "";
        }
    }
}



// #5 No strategy ID has been passed on, but IDs have been passed on using the old system
$allstratsdb = null;
if (!$directstrat && $subselector != "") {
    if (!preg_match("/^[1234567890]*$/is", $subselector)) {                           // Strategy ID is not a number
        $mainselector = "";                                                         // Defaulting to Landing Page
        $subselector = "";
        $urlchanger = "index.php";
        $sendtoast = "linkerror";
    }
    else {
        if ($userrights['EditStrats'] == "yes") {
            $allstratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Sub = $subselector AND Deleted = '0'")or die("None");
        }
        else {
            if ($user) {
                $allstratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Deleted = '0' AND ((Sub = $subselector AND Published = '1') OR (Sub = $subselector AND User = '$user->id'))") OR die(mysqli_error($dbcon));
            }
            else {
                $allstratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Sub = $subselector AND Published = '1' AND Deleted = '0'") OR die(mysqli_error($dbcon));
            }
        }
        if (mysqli_num_rows($allstratsdb) == "1") {
            $strategydb = mysqli_fetch_object($allstratsdb);
            $strategy = $strategydb->id;
            $mainselector = $strategydb->Main;
            $maincheckdb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$mainselector'");
            $mainentry = mysqli_fetch_object($maincheckdb);
            $urlchanger = "?Strategy=".$strategydb->id;
        }
        else if (mysqli_num_rows($allstratsdb) > "1") {  // Select which strategy should be the first one to be shown
            $strategydb = mysqli_fetch_object($allstratsdb);
            $allstrats = calc_strats_rating($strategydb->id, $language, $user);
            $strategy = $allstrats[0]->id;
            if ($urlchanged != true) {
                $urlchanger = "?Strategy=".$strategy;
                $directstrat = TRUE;
                $stratcheckdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$strategy'");
                $stratcheck = mysqli_fetch_object($stratcheckdb);
                $mainselector = $strategydb->Main;
                $maincheckdb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$mainselector'");
                $mainentry = mysqli_fetch_object($maincheckdb);
            }
        }
    }
}



// #5 Family Familiar Settings

if ($mainselector >= "17" AND $mainselector <= "27"){

    if ($mainselector == "17"){
        $calcselector = "24";
    }
    else {
        $calcselector = $mainselector;
    }
    if (!$subselector){
        $calcsubselector = "277";
    }
    else {
        $calcsubselector = $subselector;
    }
    $aquatarget = $calcsubselector+((18-18)-($calcselector-18))*15;
    $beastarget = $aquatarget+15;
    $crittarget = $aquatarget+30;
    $dragtarget = $aquatarget+45;
    $elemtarget = $aquatarget+60;
    $flyitarget = $aquatarget+75;
    $humatarget = $aquatarget+90;
    $magitarget = $aquatarget+105;
    $mechtarget = $aquatarget+120;
    $undetarget = $aquatarget+135;
}



// #7 Argus Family Fighter Settings
$subfamily = "";

if ($mainselector == "42"){

    if (!$subselector){
        $normtarget = "468";
    }
    else {
        $subcheckdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = '$subselector'");
        $subcheck = mysqli_fetch_object($subcheckdb);
        $subfamily = $subcheck->Family;
        if ($subcheck->Family == "0") {
            $normtarget = $subcheck->id;
        }
        else {
            $normtarget = $subcheck->Parent;
        }
    }
    $humatarget = $normtarget+21;
    $dragtarget = $normtarget+42;
    $flyitarget = $normtarget+63;
    $undetarget = $normtarget+84;
    $crittarget = $normtarget+105;
    $magitarget = $normtarget+126;
    $elemtarget = $normtarget+147;
    $beastarget = $normtarget+168;
    $aquatarget = $normtarget+189;
    $mechtarget = $normtarget+210;
}

// #8 BfA Family Battler settings

if ($mainselector == 54){

    if (!$subselector){
        $subselector = "";
        $subfamily = "1";
        $normtarget = "699";
    }
    else {
        $subcheckdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = '$subselector'");
        $subcheck = mysqli_fetch_object($subcheckdb);
        $subfamily = $subcheck->Family;
        $normtarget = $subcheck->Parent;
    }
    $humatarget = $normtarget+30;
    $dragtarget = $normtarget+60;
    $flyitarget = $normtarget+90;
    $undetarget = $normtarget+120;
    $crittarget = $normtarget+150;
    $magitarget = $normtarget+180;
    $elemtarget = $normtarget+210;
    $beastarget = $normtarget+240;
    $aquatarget = $normtarget+270;
    $mechtarget = $normtarget+300;

}






// =================== CHECK MAIN CATEGORIES THAT ONLY HAVE 1 STRATEGY AND NO ARTICLE AND SET SUBSELECTOR ACCORDINGLY ===================

// Lil Tommy Newcomer
if ($mainselector == "4"){
    $subselector = "79";
    $sendtoast = "";
}
// Crysa
if ($mainselector == "32"){
    $subselector = "343";
}
// Environeer Bert
if ($mainselector == "41"){
    $subselector = "466";
}

// Process sent messages in User message system

$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : null;

if ($cmd == "sendmsg") {
    $rsperror = "false";
    if (!$user) {
        $rsperror = "true";
    }
    if ($rsperror != "true") {
        $sendto = $_POST['recipient'];
        $delimiter = $_POST['delimiter'];
        $msgcontent = $_POST['msgcontent'];
        $msgcontent = remove_emojis($msgcontent);
        if (!$sendto OR !$delimiter OR $msgcontent == "" OR $delimiter != $user->ComSecret OR mb_strlen($msgcontent) > "10100") {
            $rsperror = "true";
        }
    }
    if ($rsperror != "true") {
        $sendtodb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$sendto'");
        if (mysqli_num_rows($sendtodb) < "1") {
            $rsperror = "true";
        }
        else {
            $sendtouser = mysqli_fetch_object($sendtodb);
        }
    }
    if ($rsperror != "true") {
        if ($sendto == $user->id) {
            $rsperror = "true";
        }
    }

    if ($rsperror != "true") {
        $msgcontent = mysqli_real_escape_string($dbcon, $msgcontent);
        mysqli_query($dbcon, "INSERT INTO UserMessages (`Sender`, `Receiver`, `Content`) VALUES ('$user->id', '$sendto', '$msgcontent')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Private Message Sent', '$sendto')") OR die(mysqli_error($dbcon));
        $msgcontent = "";
        $urlchanger = "?page=sentmsgs";
        $page = "sentmsgs";
        $redcat = "sent";
        $redthread = mysqli_insert_id($dbcon);
    }

    if ($rsperror == "true") {
        $sendtoast = "rsperror";
        $page = "writemsg";
        echo '<script type="text/javascript" lang="javascript">';
        echo 'window.history.replaceState("object or string", "Title", "index.php?page=writemsg");';
        echo '</script>';
        $msgcontent = stripslashes($msgcontent);
        $msgcontent = htmlentities($msgcontent, ENT_QUOTES, "UTF-8");
    }
}



if ($cmd == "sendrsp") {

    $rsperror = "false";
    $sendto = $_POST['sendto'];
    $delimiter = $_POST['delimiter'];
    $parent = $_POST['parent'];
    $msgcontent = $_POST['msgcontent'];

    // Check User credientials
    if ($delimiter != $user->ComSecret) {
        $rsperror = "true";
    }

    // Check Recipient
    $sendtodb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$sendto'");
    if (mysqli_num_rows($sendtodb) < "1") {
        $rsperror = "true";
    }
    else {
        $sendtouser = mysqli_fetch_object($sendtodb);
    }

    // Check Thread
    $sendthreaddb = mysqli_query($dbcon, "SELECT * FROM UserMessages WHERE id = '$parent'");
    if (mysqli_num_rows($sendthreaddb) < "1") {
        $rsperror = "true";
    }
    else {
        $sendthread = mysqli_fetch_object($sendthreaddb);
        // Check if the thread is even attached to this user
        if ($sendthread->Sender != $sendtouser->id && $sendthread->Receiver != $sendtouser->id) {
            $rsperror = "true";
        }
    }

    // Check message
    if ($msgcontent == "") {
        $rsperror = "true";
    }
    if (mb_strlen($msgcontent) > "10100"){
        $rsperror = "true";
    }

    // Defaulting type to 0 = private message. TODO: site wide announcement setting here.
    $rsptype = "0";

    // Add message to DB
    if ($rsperror == "false") {
        $msgcontent = mysqli_real_escape_string($dbcon, $msgcontent);
        mysqli_query($dbcon, "INSERT INTO UserMessages (`Sender`, `Receiver`, `Parent`, `Content`, `Type`) VALUES ('$user->id', '$sendtouser->id', '$parent', '$msgcontent', '$rsptype')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "UPDATE UserMessages SET `Seen` = '1' WHERE Parent = '$parent' AND Receiver = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserMessages SET `Seen` = '1' WHERE id = '$parent' AND Receiver = '$user->id'");
        mysqli_query($dbcon, "UPDATE UserMessages SET `Growl` = '5' WHERE id = '$parent'");
        mysqli_query($dbcon, "UPDATE UserMessages SET `DeletedSend` = '0' WHERE id = '$parent'");
        mysqli_query($dbcon, "UPDATE UserMessages SET `DeletedRec` = '0' WHERE id = '$parent'");
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '0', 'Private Message Response sent', '$sendtouser->id')") OR die(mysqli_error($dbcon));
        $redcat = "sent";
        $redthread = $parent;
        $urlchanger = "?page=sentmsgs";
        $msgcontent = "";
    }

    if ($rsperror == "true") {
        $sendtoast = "rsperror";
    }
}


// Create Table of user messages:
$threads = [];
$newmsggrowl = false;
if ($user) {
    $threadsdb = mysqli_query($dbcon, "SELECT * FROM UserMessages WHERE ((Receiver = $user->id AND DeletedRec = '0') OR (Sender = $user->id AND DeletedSend = '0')) AND Parent = '0' ORDER BY Date DESC");

    // Fill Inbox and Sent arrays
    $threadscounter = "0";
    $msgsincount = "0";
    $msgsoutcount = "0";
    if (mysqli_num_rows($threadsdb) > "0") {

        while ($threadscounter < mysqli_num_rows($threadsdb)) {
            $thread = mysqli_fetch_object($threadsdb);
            $threads[$threadscounter]['id'] = $thread->id;
            $threads[$threadscounter]['type'] = $thread->Type;
            $threads[$threadscounter]['subject'] = $thread->Subject;
            $threads[$threadscounter]['msgs'] = "1";                                                // Set default of 1 message in thread
            $threads[$threadscounter]['seen'] = "1";                                                // Set default: thread is read by user
            if ($thread->Receiver == $user->id && $thread->Seen == "0") {
                $threads[$threadscounter]['seen'] = "0";                                            // Overwriting to "unread" if the first message is already unread
                 if ($thread->Growl < "6") {
                    $newmsggrowl = true;
                    $newmsgarr[] = $thread->id;
                }
            }
            if ($thread->Sender == $user->id) {                                                   // Add initial Direction of Thread based on first message
                $threads[$threadscounter]['direction'] = "out";
                $checkpartner = $thread->Receiver;
            }
            else {
                $threads[$threadscounter]['direction'] = "in";
                $checkpartner = $thread->Sender;
            }

            $partnerdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$checkpartner'");  // Add partner name and ID
            if (mysqli_num_rows($partnerdb) > "0"){
                $partner = mysqli_fetch_object($partnerdb);
                $threads[$threadscounter]['partnername'] = htmlentities($partner->Name, ENT_QUOTES, "UTF-8");
                $threads[$threadscounter]['partnerid'] = $checkpartner;
                $threads[$threadscounter]['partnerrole'] = $partner->Role;
                if ($partner->UseWowAvatar == "0"){
                    $threads[$threadscounter]['partnericon'] = 'src="https://www.wow-petguide.com/images/pets/'.$partner->Icon.'.png"';
                }
                else if ($partner->UseWowAvatar == "1"){
                    $threads[$threadscounter]['partnericon'] = 'src="https://www.wow-petguide.com/images/userpics/'.$partner->id.'.jpg?lastmod?='.$partner->IconUpdate.'"';
                }
            }
            else {
                $threads[$threadscounter]['partnername'] = "Deleted Account";
                $threads[$threadscounter]['partnerdel'] = "1";
                $threads[$threadscounter]['partnerid'] = "0";
                $threads[$threadscounter]['partnerrole'] = "0";
                $threads[$threadscounter]['partnericon'] = 'src="https://www.wow-petguide.com/images/userpics/del_acc.jpg"';
            }

            $threads[$threadscounter]['lastmsgdate'] = format_date($thread->Date);                  // Set date of last message for output
            $threads[$threadscounter]['threaddate'] = $thread->Date;                                // Set date of last message for sorting

            // Add first message to messages array
            if ($thread->Sender == $user->id) {                                                   // add sender info
                $msgs[$thread->id][0]['sendername'] = $user->Name;
                $msgs[$thread->id][0]['senderid'] = $user->id;
                $msgs[$thread->id][0]['sendericon'] = $usericon;
                $msgs[$thread->id][0]['direction'] = "out";
            }
            else {
                $msgs[$thread->id][0]['sendername'] = $threads[$threadscounter]['partnername'];
                $msgs[$thread->id][0]['senderid'] = $threads[$threadscounter]['partnerid'];
                $msgs[$thread->id][0]['sendericon'] = $threads[$threadscounter]['partnericon'];
                $msgs[$thread->id][0]['direction'] = "in";
            }

            $msgs[$thread->id][0]['date'] = format_date($thread->Date);                             // Set date of message

            $msgs[$thread->id][0]['content'] = stripslashes($thread->Content);                      // Save content of message
            $msgs[$thread->id][0]['content'] = htmlentities($msgs[$thread->id][0]['content'], ENT_QUOTES, "UTF-8");
            $msgs[$thread->id][0]['content'] = AutoLinkUrls($msgs[$thread->id][0]['content'],'1','dark');
            $msgs[$thread->id][0]['content'] = preg_replace("/\n/s", "<br>", $msgs[$thread->id][0]['content']);

            // Grab submessages and add them to array
            $submsgsdb = mysqli_query($dbcon, "SELECT * FROM UserMessages WHERE Parent = '$thread->id' ORDER BY Date ASC");

            if (mysqli_num_rows($submsgsdb) > "0") {

                $threads[$threadscounter]['msgs'] = mysqli_num_rows($submsgsdb)+1;                  // Overwrite amount of messages with submessages + first message
                $submscgcounter = "1";

                while ($submscgcounter <= mysqli_num_rows($submsgsdb)) {
                    $submsg = mysqli_fetch_object($submsgsdb);

                    if ($submsg->Receiver == $user->id) {                                         // Fix direction and "seen" status based on all sub messages
                        $threads[$threadscounter]['direction'] = "in";
                        if ($submsg->Seen == "0") {
                            $threads[$threadscounter]['seen'] = "0";
                            if ($submsg->Growl < "6") {
                                $newmsggrowl = true;
                                $newmsgarr[] = $submsg->id;
                            }
                        }
                    }
                    else {
                        $threads[$threadscounter]['direction'] = "out";
                    }

                    $threads[$threadscounter]['lastmsgdate'] = format_date($submsg->Date);          // Update date of last message initially

                    if ($submsg->Sender == $user->id) {                                                   // add sender info
                        $msgs[$thread->id][$submscgcounter]['sendername'] = $user->Name;
                        $msgs[$thread->id][$submscgcounter]['senderid'] = $user->id;
                        $msgs[$thread->id][$submscgcounter]['sendericon'] = $usericon;
                        $msgs[$thread->id][$submscgcounter]['direction'] = "out";
                    }
                    else {
                        $msgs[$thread->id][$submscgcounter]['sendername'] = $threads[$threadscounter]['partnername'];
                        $msgs[$thread->id][$submscgcounter]['senderid'] = $threads[$threadscounter]['partnerid'];
                        $msgs[$thread->id][$submscgcounter]['sendericon'] = $threads[$threadscounter]['partnericon'];
                        $msgs[$thread->id][$submscgcounter]['direction'] = "in";
                    }

                    $msgs[$thread->id][$submscgcounter]['date'] = format_date($submsg->Date);
                    $threads[$threadscounter]['threaddate'] = $submsg->Date;                        // Set date of last message for sorting
                    // Add details of submessages to messages array
                    $msgs[$thread->id][$submscgcounter]['content'] = stripslashes($submsg->Content);
                    $msgs[$thread->id][$submscgcounter]['content'] = htmlentities($msgs[$thread->id][$submscgcounter]['content'], ENT_QUOTES, "UTF-8");
                    $msgs[$thread->id][$submscgcounter]['content'] = AutoLinkUrls($msgs[$thread->id][$submscgcounter]['content'],'1','dark');
                    $msgs[$thread->id][$submscgcounter]['content'] = preg_replace("/\n/s", "<br>", $msgs[$thread->id][$submscgcounter]['content']);
                    $submscgcounter++;
                }
            }
            $threadscounter++;
        }
    sortBy('threaddate', $threads, 'desc');
    }
}


// ================ mark all new comments on strategies as read ======================

if ($user && $command == 'markall' && $page == 'strategies') {
    mysqli_query($dbcon, "UPDATE Alternatives SET NewComs = 0 WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
}

// ================ Check if new comments have been written on strategies this user owns ======================

if ($user) {
    $stratcomsdb = mysqli_query($dbcon, "SELECT SUM(NewComs) as Total FROM Alternatives WHERE User = '$user->id' AND Deleted = '0'")or die("None");
    $stratcoms = mysqli_fetch_object($stratcomsdb);
    if ($stratcoms->Total > "0") {
        $stratcomstotal = "(".$stratcoms->Total.")";
    }
}




// ================ MAINTENANCE MODE ON / OFF ======================

/*
// Maintenance screen
if ($user->id != "2" && $user->id != "2198"){
include("maintenance.php");
die;
}

*/












// =======================================================================================================
// ================================== ↑↑↑↑↑↑ BACKEND ↑↑↑↑↑↑↑ =============================================
// =======================================================================================================
// ================================== ↓↓↓↓↓↓ FRONTEND ↓↓↓↓↓↓ =============================================
// =======================================================================================================



// ================ Link Enrichment ========================
// Default Values:
    $enr_url = "https://wow-petguide.com";
    $enr_title = "Xu-Fu's Pet Battle Strategies";
    $enr_description = "WoW Pet Battle Guides";
    $enr_image = "https://www.wow-petguide.com/images/xufuprofile.jpg";

// News Articles - Full View:
    if (!$page && $news_article) {
        $enr_url = "https://wow-petguide.com/?News=".$news_article->id;
        $enr_title = "Xu-Fu's Pet News: ".stripslashes(htmlentities($news_article->Title_en_US, ENT_QUOTES, "UTF-8"));
        $enr_description = \BBCode\bbremove_code($news_article->Content_en_US);    
        $enr_description = stripslashes(htmlentities($enr_description, ENT_QUOTES, "UTF-8"));

        if (strlen($enr_description) > "280") {
        $enr_description = substr($enr_description, 0, 280);
        $cutter = "279";
        while (substr($enr_description, -1) != " ") {
            $enr_description = substr($enr_description, 0, "$cutter");
            $cutter = $cutter - 1;
        }
            $enr_description = $enr_description."...";
        }
        if (file_exists('images/news/'.$news_article->id.'.jpg')) {
            $enr_image = 'https://www.wow-petguide.com/images/news/'.$news_article->id.'.jpg';
        }
        else {
            $enr_image = 'https://www.wow-petguide.com/images/news/news_default.jpg';
        }
    }

    

// A direct strategy has been selected 
if ($directstrat == TRUE) {
    $enr_url = "https://wow-petguide.com/?Strategy=".$stratcheck->id;
    if (file_exists('images/fights/m'.$mainselector.'_s'.$subselector.'.jpg')) {
        $enr_image = 'https://www.wow-petguide.com/images/fights/m'.$mainselector.'_s'.$subselector.'.jpg';
    }
    $subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $subselector LIMIT 1");
    $thissub = mysqli_fetch_object($subnamedb);
    if ($thissub->Parent != "0") {
        $subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $thissub->Parent");
        $thissub = mysqli_fetch_object($subnamedb);
    }
    $subnname = stripslashes(htmlentities($thissub->Name, ENT_QUOTES, "UTF-8"));
    $enr_title = "Xu-Fu Strategy vs. ".$subnname;



    if ($stratcheck->User != "0") {
        $stratuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$stratcheck->User'");
        if (mysqli_num_rows($stratuserdb) > "0") {
            $stratuser = mysqli_fetch_object($stratuserdb);
            $stratuser_name = $stratuser->Name;
        }
    }
    if ($stratcheck->User == "0" && $stratcheck->CreatedBy != "") {
        $stratuser_name = $stratuser->CreatedBy;
    }
    $pets = array();
	while ($i < "4") {
		switch ($i) {
			case "1":
				$fetchpet = $stratcheck->PetID1;
				if ($fetchpet <= 20 || $stratcheck->SkillPet11 == "0") { $skill1 = "*"; $skillwildcards++; $skill1nb = "*";}
				else if ($stratcheck->SkillPet11 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1']; $skill1nb = "1"; }
				else if ($stratcheck->SkillPet11 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4']; $skill1nb = "2"; }
				if ($fetchpet <= 20 || $stratcheck->SkillPet12 == "0") { $skill2 = "*"; $skillwildcards++; $skill2nb = "*";}
				else if ($stratcheck->SkillPet12 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; $skill2nb = "1"; }
				else if ($stratcheck->SkillPet12 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; $skill2nb = "2"; }
				if ($fetchpet <= 20 || $stratcheck->SkillPet13 == "0") { $skill3 = "*"; $skillwildcards++; $skill3nb = "*";}
				else if ($stratcheck->SkillPet13 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; $skill3nb = "1"; }
				else if ($stratcheck->SkillPet13 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; $skill3nb = "2"; }
                $reqlevel = $stratcheck->PetLevel1;
				break;
			case "2":
				$fetchpet = $stratcheck->PetID2;
				if ($fetchpet <= 20 || $stratcheck->SkillPet21 == "0") { $skill1 = "*"; $skillwildcards++; $skill1nb = "*";}
				else if ($stratcheck->SkillPet21 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1']; $skill1nb = "1"; }
				else if ($stratcheck->SkillPet21 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4']; $skill1nb = "2"; }
				if ($fetchpet <= 20 || $stratcheck->SkillPet22 == "0") { $skill2 = "*"; $skillwildcards++; $skill2nb = "*";}
				else if ($stratcheck->SkillPet22 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; $skill2nb = "1"; }
				else if ($stratcheck->SkillPet22 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; $skill2nb = "2"; }
				if ($fetchpet <= 20 || $stratcheck->SkillPet23 == "0") { $skill3 = "*"; $skillwildcards++; $skill3nb = "*";}
				else if ($stratcheck->SkillPet23 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; $skill3nb = "1"; }
				else if ($stratcheck->SkillPet23 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; $skill3nb = "2"; }
                $reqlevel = $stratcheck->PetLevel2;
				break;
			case "3":
				$fetchpet = $stratcheck->PetID3;
				if ($fetchpet <= 20 || $stratcheck->SkillPet31 == "0") { $skill1 = "*"; $skillwildcards++; $skill1nb = "*";}
				else if ($stratcheck->SkillPet31 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1']; $skill1nb = "1"; }
				else if ($stratcheck->SkillPet31 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4']; $skill1nb = "2"; }
				if ($fetchpet <= 20 || $stratcheck->SkillPet32 == "0") { $skill2 = "*"; $skillwildcards++; $skill2nb = "*";}
				else if ($stratcheck->SkillPet32 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; $skill2nb = "1"; }
				else if ($stratcheck->SkillPet32 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; $skill2nb = "2"; }
				if ($fetchpet <= 20 || $stratcheck->SkillPet33 == "0") { $skill3 = "*"; $skillwildcards++; $skill3nb = "*";}
				else if ($stratcheck->SkillPet33 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; $skill3nb = "1"; }
				else if ($stratcheck->SkillPet33 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; $skill3nb = "2"; }
                $reqlevel = $stratcheck->PetLevel3;
				break;
		}
        if ($fetchpet == 0) { // Level  pet
            if ($reqlevel == ""){
               $reqlevel = "1+";
            }
            $reqlevelpieces = explode("+", $reqlevel);
            $displayreqlvl = $reqlevelpieces[0]."+";
            $pets[$i] = __("Any Level")." ".$displayreqlvl." ".__("Pet");
        }
        if ($fetchpet == 1) { // Any pet
            $reqlevelpieces = explode("+", $reqlevel);
            $displayreqlvl = $reqlevelpieces[0]."+";
            if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
                $pets[$i] = __("Any Pet");
            }
            else {
                 $pets[$i] = __("Any Level")." ".$displayreqlvl." ".__("Pet");
            }
        }

        if ($fetchpet > "10" && $fetchpet <= "20") { // Family pet
            switch ($fetchpet) {
               case "11":
                  $famname = __("Humanoid");
               break;
               case "12":
                  $famname = __("Magic");
               break;
               case "13":
                  $famname = __("Elemental");
               break;
               case "14":
                  $famname = __("Undead");
               break;
               case "15":
                  $famname = __("Mechanical");
               break;
               case "16":
                  $famname = __("Flying");
               break;
               case "17":
                  $famname = __("Critter");
               break;
               case "18":
                  $famname = __("Aquatic");
               break;
               case "19":
                  $famname = __("Beast");
               break;
               case "20":
                  $famname = __("Dragonkin");
               break;
            }
            $reqlevelpieces = explode("+", $reqlevel);
            $displayreqlvl = $reqlevelpieces[0]."+";
            if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
                $pets[$i] = __("Any")." ".$famsuffix;
            }
            else {
                 $pets[$i] = __("Any Level")." ".$displayreqlvl." ".$famsuffix;
            }
        }
        if ($fetchpet > "20" ) { // regular pet
            $pets[$i] = $all_pets[$fetchpet]['Name'].' ('.$skill1nb.$skill2nb.$skill3nb.')';
        }
        $i++;
    }

    if ($stratuser_name) {
        $enr_description = "Strategy by ".$stratuser_name;
    }
    else {
        $enr_description = "Strategy";
    }
    $enr_description = $enr_description." vs. ".$subnname." using: ".$pets[1].", ".$pets[2]." and ".$pets[3].".";
}


    
    
    
$mtime = microtime(true);

?>
<!DOCTYPE html5>
<html>
<head>
    <?php if ($user->id != 2434) { ?> 
    <script src="https://hb.vntsm.com/v3/live/ad-manager.min.js" type="text/javascript" data-site-id="5d78f6ac71d1621a68eb8f02" data-mode="scan" async></script>
    <script data-ad-client="ca-pub-1844645922810044" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <?php } ?>
<?php  /*/* Deactivated google analytics ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-62324188-1"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'UA-62324188-1');
        </script>
 <?php */ ?>
    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $enr_title ?></title>
    <meta name="Description" content="<?php echo __("World of Warcraft Pet Battle guides - your one-stop place for strategies to beat all WoW pet battle quests, achievements and opponents!") ?>" />
    <meta name="Keywords" content="<?php echo __("warcraft pets, warcraftpets, wow vanity pets, wow battle pets, wow companions") ?>" />
    <link rel="alternate" type="application/rss+xml" title="Xu-Fu's Pet Battles" href="/rss_feed.php">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <link rel="alternate" href="https://www.wow-petguide.com" hreflang="x-default" />
        <link rel="alternate" href="https://www.wow-petguide.com" hreflang="en" />
        <link rel="alternate" href="https://de.wow-petguide.com" hreflang="de" />
        <link rel="alternate" hreflang="fr" href="https://fr.wow-petguide.com/">
        <link rel="alternate" hreflang="es" href="https://es.wow-petguide.com/">
        <link rel="alternate" hreflang="ru" href="https://ru.wow-petguide.com/">
        <link rel="alternate" hreflang="pt" href="https://pt.wow-petguide.com/">
        <link rel="alternate" hreflang="it" href="https://it.wow-petguide.com/">
        <link rel="alternate" hreflang="zh" href="https://zh.wow-petguide.com/">
        <link rel="alternate" hreflang="ko" href="https://ko.wow-petguide.com/">
        <meta property="fb:app_id" content="1146631942116322"/>
        <meta property="og:type"          content="Website" />
        <meta property="og:url"           content="<?php echo $enr_url ?>" />
        <meta property="og:title"         content="<?php echo $enr_title ?>" />
        <meta property="og:description"   content="<?php echo $enr_description ?>" />
        <meta property="og:image"         content="<?php echo $enr_image ?>" />
    <link rel="stylesheet" type="text/css" href="data/style.css?v=2.81<?php if ($user->Role == "99") {echo $mtime; }?>">
    <link rel="stylesheet" type="text/css" href="data/news_article.css?v=2<?php if ($user->Role == "99") {echo $mtime; }?>">
    <link rel="stylesheet" type="text/css" href="data/battletable.css?v=2.11<?php if ($user->Role == "99") {echo $mtime; }?>">
    <link rel="stylesheet" href="data/remodal.css">
    <link rel="stylesheet" href="data/remodal-default-theme.css">
    <link rel="stylesheet" href="data/jquery.growl.css">
    <link rel="stylesheet" href="data/chosen.css">
    <link rel="stylesheet" type="text/css" href="data/tooltipster/tooltipster.bundle.min.css" />
    <link rel="stylesheet" type="text/css" href="data/tooltipster/themes.css" />
    <script src="//wow.zamimg.com/widgets/power.js">
    var wowhead_tooltips = { "colorlinks": false, "iconizelinks": true, "renamelinks": false }</script>
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="data/jquery.capslockstate.js"></script>
    <script src="data/jquery.waypoints.min.js"></script>
    <script src="data/functions.js?v=2.6<?php if ($user->Role == "99") {echo $mtime; }?>"></script>
    <script src="data/jquery.growl.js"></script>
    <script src="data/tooltipster.bundle.min.js"></script>
    <script src="data/table.js"></script>
    <script src="data/clipboard.min.js"></script>
    <script src="data/remodal.min-2.js"></script>
    <script src="data/jquery.canvasjs.min.js"></script>
    <script src="data/chosen.jquery.min.js"></script>
    <?php if ($userrights['Edit_Home_Leveling'] == true) { ?>
        <script src="data/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="data/jquery-ui.min.css">
    <?php } ?>
    <?php /* Cookie consent thingy - removed at the moment
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js"></script>
    <script>
    window.addEventListener("load", function(){
    window.cookieconsent.initialise({
      "palette": {
        "popup": {
          "background": "#1d8a8a"
        },
        "button": {
          "background": "#62ffaa"
        }
      },
      "theme": "edgeless",
      "content": {
        "message": "Xu-Fu uses cookies to ensure you get the best experience.",
        "href": "https://www.wow-petguide.com/index.php?m=DataPrivacy"
      }
    })});
    </script>
    */ ?>
    <script>var current_language = "<?php echo urlencode($language); ?>";</script>
    
</head>




<body> 




<?php

if ($newmsggrowl && $page != "messages" && $page != "sentmsgs" && $page != "writemsg") {
    echo '<script>$.growl.notice({ message: "'.__("You have unread messages. Click <a href='index.php?page=messages' class='growl'>here</a> to read them.").'", duration: "5000", size: "large" });</script>';
    foreach ($newmsgarr as $key => $value) {
        mysqli_query($dbcon, "UPDATE UserMessages SET Growl = Growl + 1 WHERE id = '$value'") OR die(mysqli_error($dbcon));
    }
} ?>
<a href="#" class="to-top-btn">
    <img src="https://www.wow-petguide.com/images/go-top.png" alt="^">
</a>

<?php

function build_menu ($user)
{
  $userrights = format_userrights($user->Rights);
  $menus = TopMenu_MenuBuilder::begin();

  $menu = $menus->top (Localization_string ('Info'), 'blog');
  $menu->sub (Localization_category (46, 'Development Notes'), 'DevLog');
  $menu->sub (Localization_category (47, 'Data Protection'), 'DataPrivacy');
  $menu->sub (Localization_category (63, 'Rules'), 'Rules');
  
  $menu = $menus->top (Localization_string ('Tools'), 'tools', 'Tools');
  $menu->sub (Localization_string ("Xu-Fu's Pet Collection Viewer"), "Collection");
  $menu->sub (Localization_string ('Collection Comparator'), 'Compare');
  $menu->sub ('Magpie', 'https://magpie.wow-petguide.com');
  if ($user && $userrights['EditStrats'] == "yes")
  {
    $menu->sub (Localization_category (65, 'Competitions'), 'Competitions');
  }
  if ($user->id == 2)
  {
    $menu->sub (Localization_category (71, 'Most Used Pets'), 'TopPets');
  }
  
  $menu = $menus->top (Localization_string ('Guides'), 'guides');
  /*
  if ($user && ($userrights[EditBeginnerGuide] == "yes" OR $userrights[EditGuides] == "yes"))
  {
    $menu->sub (Localization_category (64, 'Beginners Guide'), 'BeginnersGuide');
  }
  */
  $menu->sub (Localization_category (45, 'Getting Started'), 'GettingStarted');
  $menu->sub (Localization_category (44, 'Powerleveling Pets'), 'Powerleveling');
  $menu->sub (Localization_category (58, 'Most Used Pets'), 'MostUsedPets');
  $menu->sub (Localization_category (69, 'Recommended Addons'), 'RecommendedAddons');
  $menu->sub (Localization_category (59, 'Using TD Scripts'), 'UsingTDScripts');
  $menu->sub (Localization_category (43, 'TD Scripts'), 'TDScripts');
  $menu->sub (Localization_category (50, 'Strategy Creation Guide'), 'StratCreationGuide');
  if ($user && $userrights['EditTestpage'] == "yes")
  {
    $menu->sub (Localization_category (68, 'Test Page'), 'Test');
  }
  
    $menu = $menus->top (Localization_string ('Shadowlands'), 'SL');
    $menu->sub (Localization_category (70, 'World Quests'), 'ShadowlandsWQs');
    if ($user->id == 2) {
    $menu->sub (Localization_category (73, 'Family Exorcist'), 'FamilyExorcist');
    }
    $menu->sub (Localization_category (72, 'Abhorrent Adversaries'), 'AbhorrentAdversaries');
  
  $menu = $menus->top (Localization_string ('BfA'), 'bfa');
  $menu->sub (Localization_category (51, 'World Quests'), 'BattleforAzeroth');
  $menu->sub (Localization_category (54, 'Family Battler'), 'FamilyBattler');
  $menu->sub (Localization_category (61, 'Mechagon'), 'Mechagon');
  $menu->sub (Localization_category (62, 'Nazjatar'), 'Nazjatar');
  $menu->sub (Localization_category (52, 'Baal'), 'Baal');

  $menu = $menus->top (Localization_string ('Legion'), 'legion');
  $menu->sub (Localization_category (16, 'Legion World Quests'), 'LegionWQ');
  $menu->sub (Localization_category (17, 'Family Familiar'), 'FamilyFamiliar');
  $menu->sub (Localization_category (35, 'Falcosaur Team Rumble'), 'Falcosaur');
  $menu->sub (Localization_category (28, 'Sternfathoms Pet Journal'), 'Sternfathom');
  // $menu->sub (Localization_string ('Legion Ready-Checker'), 'LegionChecker');
  $menu->sub (Localization_category (42, 'Anomalous Animals of Argus'), 'Argus');

  $menu = $menus->top (Localization_string ('Draenor'), 'draenor');
  $menu->sub (Localization_category (2, 'The Pet Menagerie'), 'Menagerie');
  $menu->sub (Localization_category (5, 'Draenor Master Tamers'), 'Draenor');
  $menu->sub (Localization_category (9, 'Tanaan Jungle'), 'Tanaan');

  $menu = $menus->top (Localization_string ('Pandaria'), 'pandaria');
  $menu->sub (Localization_category (3, 'Pandarias Master Tamers'), 'Pandaria');
  $menu->sub (Localization_category (6, 'Pandaren Spirit Tamers'), 'PandaSpirits');
  $menu->sub (Localization_category (10, 'The Beasts of Fable'), 'Fable');
  $menu->sub (Localization_fight (4, 'Little Tommy Newcomer'), 'TommyNewcomer&s=79');

  $menu = $menus->top (Localization_string ('Dungeons'), 'dungeons');
  $menu->sub (Localization_category (66, 'Blackrock Depths'), 'BlackrockDepths');
  $menu->sub (Localization_category (60, 'Stratholme'), 'Stratholme');
  $menu->sub (Localization_category (53, 'Gnomeregan'), 'Gnomeregan');
  $menu->sub (Localization_category (36, 'The Deadmines'), 'Deadmines');
  $menu->sub (Localization_category (33, 'Wailing Caverns'), 'WailingCritters');
  $menu->sub (Localization_string ('The Celestial Tournament'), 'CelestialTournament');

  $menu = $menus->top (Localization_string ('Misc'), 'misc');
  $menu->sub (Localization_category (7, 'The Darkmoon Faire'), 'Darkmoon');
  $menu->sub (Localization_category (12, 'The Elekk Plushie'), 'Elekk');
  $menu->sub (Localization_category (31, 'Algalon the Observer'), 'Algalon');
  $menu->sub (Localization_fight (32, 'Crysa'), 'Crysa&s=343');
  $menu->sub (Localization_fight (41, 'Environeer Bert'), 'Bert&s=466');

  $menu = $menus->top (Localization_string ('PvP'), 'pvp');
  $menu->sub (Localization_category (48, 'Introduction'), 'PvPIntro');
  if ($user && ($userrights['EditPvP'] == "yes" OR $userrights['LocArticles'] == "yes"))
  {
    // $menu->sub (Localization_category (55, 'PvP Teams'), 'PvPTeams');
  }
  $menu->sub (Localization_category (56, 'Family Brawler'), 'FamilyBrawler');
  $menu->sub (Localization_category (49, 'PvP Tier List'), 'PvPTierList');
  $menu->sub (Localization_category (57, 'PvP Families'), 'PvPFamilies');
  return $menus->finalize();
}

echo HTML_to_string ( TopMenu_create_html ( build_menu ($user)
                                          , $user
                                            //! \todo Use $_SERVER['REQUEST_URI'] instead?
                                          , 'm=' . $mainshow . '&s=' . $subselector
                                          , $mainentry ? $mainentry->MenuHighlight : ""
                                          , $language
                                          )
                    );

$unreadcounter = $user ? User_unread_private_message_count ($user) : 0;
$mynewcoms = $user ? User_unread_comment_count ($user) : 0;

if ($page == "login" && $loginfail == "true"){
include 'classes/ac_login.php';
}
if ($page == "register"){
include 'classes/ac_register.php';
}
if ($page == "pwrecover") {
include 'classes/ac_pwreset.php';
}
if ($page == "setpw") {
include 'classes/ac_setpw.php';
}
if ($page == "acretrieve") {
include 'classes/ac_retrieve.php';
}
if ($page == "profile") {
include 'classes/ac_profile.php';
}
if ($page == "404") {
include 'errordocs/404.php';
die;
}
if ($page == "settings") {
include 'classes/ac_settings.php';
}
if ($page == "strategies") {
include 'classes/ac_strategies.php';
}
if ($page == "messages" OR $page == "sentmsgs" OR $page == "writemsg") {
include 'classes/ac_messages.php';
}
if ($page == "mycomments") {
include 'classes/ac_mycomments.php';
}
if ($page == "collection") {
    include 'classes/ac_collection.php';
}
if ($page == "icon") {
include 'classes/ac_icon.php';
}
if ($page == "tooltip") {
include 'classes/ac_tooltip.php';
}
if ($page == "viewuser") {
include 'classes/ac_viewuser.php';
}
if ($page == "admin") {
include 'classes/adm_main.php';
}
if ($page == "adm_menu") {
include 'classes/adm_menu.php';
}
if ($page == "adm_breeds") {
include 'classes/adm_breeds.php';
}
if ($page == "adm_strategies") {
include 'classes/adm_strategies.php';
}
if ($page == "loc") {
include 'classes/loc_mains.php';
}
if ($page == "adm_peticons") {
include 'classes/adm_peticons.php';
}
if ($page == "adm_petimport") {
include 'classes/adm_petimport.php';
}
if ($page == "adm_comreports") {
include 'classes/adm_comreports.php';
}
if ($page == "adm_images") {
include 'classes/adm_images.php';
}
if ($page == "loc_fights") {
include 'classes/loc_fights.php';
}
if (!$page && $news_article_id) {
include 'classes/news.php';
}
if ($page == "petlist") {
include 'classes/petlist.php';
}




// ==================== DONATION RECEIVED PAGE =========================

$donate = isset($_GET['donate']) ? $_GET['donate'] : null;
if ($donate == "success"){

?>
<div class="blogtitle">

<table width="100%" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="https://www.wow-petguide.com/images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><?php echo __("Donation Received"); ?></h></td>
<td><img src="https://www.wow-petguide.com/images/main_bg02_2.png"></td>
</tr>
</table>

</div>

<div class="blogentryfirst">

<div class="articlebottom">
</div>
<center>

<table width="75%">
<tr><td width="100%">
<center>
<img src="https://www.wow-petguide.com//images/xufu_small.png">
<br><br>
<p class="blogodd"><b><?php echo __("Thank You!<br><br>Your contribution is much appreciated and will go towards the server costs."); ?>
<br><br><br><br>

<br><br><center>

</form>


</td></tr>
</table>

</div>


<?
die;
}







// ==================== SPECIAL PAGES SELECTION =========================

// Home
if ($mainselector == "" OR $mainselector == 11) {
    include 'classes/home.php';
}

// Tools: Pet Collection Viewer
if ($mainselector == "40"){
    include 'classes/collection_viewer.php';
}

// Tools: Pet Collection Viewer
if ($mainselector == "67"){
    include 'classes/collection_comparisor.php';
}

// Tools: Pet Collection Viewer
if ($mainselector == "71"){
    include 'classes/most_used_pets.php';
}






// ==================== REGULAR FIGHT MENU STARTING HERE =========================

if ($mainentry->Type != "1" && $mainselector != ""){           // Show fight menu only for articles that have strategies


    // Calculation of Garrison Pet Menagerie Fight
    $end = strtotime(date('Y-m-d H:i:s'));
    
    $eustart = strtotime("2017-01-28 08:00:00"); // Starting with Fight 1 - Deebs Tyri Puzzle
    $eudiff = ((($end-$eustart)/86400) % 15)+1;

    $usstart = strtotime("2017-02-03 16:00:00");  
    $usdiff = (($end-$usstart)/86400) % 15+1;

    $twstart = strtotime("2020-09-25 01:00:00"); 
    $twdiff = (($end-$twstart)/86400) % 15+1;
    
    // ===== REGULAR SUB MENU ======

    if (!preg_match("/^[1234567890]*$/is", $subselector)) {                                   // Tampering protection
        $subselector = "1";
    }

    $dbmainselector = $mainselector;

    if ($subselector == ""){

        // Create main article button for strategy pages
        if ($mainselector == "17"){                                                           // Family Familiar temp set to Humanoid (24)
            $dbmainselector = "24";
            $ffresetback = "true";
        }

        echo "<div class=\"remodal-bg leftmenu\"><ul class=\"vertical-list\">";
        echo "<li><a class=\"articleactivebutton\" href=\"index.php?m=".$mainshow."\">".__("Main Article")."</a></li>";
        echo "<br>";
    }

else if ($subselector != ""){

    if ($mainselector != "4" AND $mainselector != "32" AND $mainselector != "41"){

        if ($mainselector >= "18" AND $mainselector <= "27"){
            $mainshowsave = $mainshow;
            $mainshow = "FamilyFamiliar";
        }

        echo "<div class=\"remodal-bg leftmenu\"><ul class=\"vertical-list\">";
        echo "<li><a class=\"articlebutton\" href=\"index.php?m=".$mainshow."\" >".__("Main Article")."</a></li>";
        echo "<br>";

        if ($mainselector >= "18" AND $mainselector <= "27"){
            $mainshow = $mainshowsave;
        }
    }
}






// Generate submenu for Argus Family Fighter achievement

if ($mainselector == "42"){ ?>

    <br>
    <?php if ($subfamily == "0") { ?>
    <li><a class="articleactivebutton" href="index.php?s=<?php echo $subselector ?>"><?php echo __("Regular strategies") ?></a></li>
    <?php }
    else { ?>
    <li><a class="articlebutton" href="?m=Argus&s=<?php echo $normtarget ?>"><?php echo __("Regular strategies") ?></a></li>
    <?php } ?>
    <br>

    <link rel="stylesheet" type="text/css" href="data/fflegion.css">
    <table><tr>
    <td>
    <?php if ($subfamily == "1")
    {
    echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffhumanoida.png"><span class="custom"><b>'.__("Humanoid Havoc").'</b></span></a>';
    } else { echo '<a class="ffhumanoid fffamtt" href="?m=Argus&s='.$humatarget.'"><span class="custom"><b>'.__("Humanoid Havoc").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "2")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffdragonkina.png"><span class="custom"><b>'.__("Draconic Destruction").'</b></span></a>';
    } else { echo '<a class="ffdragonkin fffamtt" href="?m=Argus&s='.$dragtarget.'"><span class="custom"><b>'.__("Draconic Destruction").'</b></span></a>'; } ?>
    </td>


    <td>
    <?php if ($subfamily == "3")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffflyinga.png"><span class="custom"><b>'.__("Fierce Fliers").'</b></span></a>';
    } else { echo '<a class="ffflying fffamtt" href="?m=Argus&s='.$flyitarget.'"><span class="custom"><b>'.__("Fierce Fliers").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "4")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffundeada.png"><span class="custom"><b>'.__("Unstoppable Undead").'</b></span></a>';
    } else { echo '<a class="ffundead fffamtt" href="?m=Argus&s='.$undetarget.'"><span class="custom"><b>'.__("Unstoppable Undead").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "5")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffcrittera.png"><span class="custom"><b>'.__("Critical Critters").'</b></span></a>';
    } else { echo '<a class="ffcritter fffamtt" href="?m=Argus&s='.$crittarget.'"><span class="custom"><b>'.__("Critical Critters").'</b></span></a>'; } ?>
    </td>

    </tr>
    <tr>

    <td>
    <?php if ($subfamily == "6")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffmagica.png"><span class="custom"><b>'.__("Magical Mayhem").'</b></span></a>';
    } else { echo '<a class="ffmagic fffamtt" href="?m=Argus&s='.$magitarget.'"><span class="custom"><b>'.__("Magical Mayhem").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "7")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffelementala.png"><span class="custom"><b>'.__("Elemental Escalation").'</b></span></a>';
    } else { echo '<a class="ffelemental fffamtt" href="?m=Argus&s='.$elemtarget.'"><span class="custom"><b>'.__("Elemental Escalation").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "8")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffbeasta.png"><span class="custom"><b>'.__("Beast Blitz").'</b></span></a>';
    } else { echo '<a class="ffbeast fffamtt" href="?m=Argus&s='.$beastarget.'"><span class="custom"><b>'.__("Beast Blitz").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "9")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffaquatica.png"><span class="custom"><b>'.__("Aquatic Assault").'</b></span></a>';
    } else { echo '<a class="ffaquatic fffamtt" href="?m=Argus&s='.$aquatarget.'"><span class="custom"><b>'.__("Aquatic Assault").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "10")
    { echo '<a class="fffamtt" href="?m=Argus&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffmechanica.png"><span class="custom"><b>'.__("Mechanical Melee").'</b></span></a>';
    } else { echo '<a class="ffmechanical fffamtt" href="?m=Argus&s='.$mechtarget.'"><span class="custom"><b>'.__("Mechanical Melee").'</b></span></a>'; } ?>
    </td>


    </tr>
    </tr></table>
<?
}


// Generate submenu for Family Familiar Legion achievement

if ($mainselector >= "17" AND $mainselector <= "27"){

?>

<link rel="stylesheet" type="text/css" href="data/fflegion.css">
<table><tr>
<td>
<?php if ($mainselector == "24")
{ echo '<a class="fffamtt" href="index.php?m=LHumanoid&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffhumanoida.png"><span class="custom"><b>'.__("Murlocs, Harpies, and Wolvar, Oh My!").'</b></span></a>';
} else { echo '<a class="ffhumanoid fffamtt" href="index.php?m=LHumanoid&s='.$humatarget.'"><span class="custom"><b>'.__("Murlocs, Harpies, and Wolvar, Oh My!").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "21")
{ echo '<a class="fffamtt" href="index.php?m=LDragonkin&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffdragonkina.png"><span class="custom"><b>'.__("Dragons!").'</b></span></a>';
} else { echo '<a class="ffdragonkin fffamtt" href="index.php?m=LDragonkin&s='.$dragtarget.'"><span class="custom"><b>'.__("Dragons!").'</b></span></a>'; } ?>
</td>


<td>
<?php if ($mainselector == "23")
{ echo '<a class="fffamtt" href="index.php?m=LFlying&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffflyinga.png"><span class="custom"><b>'.__("Flock Together").'</b></span></a>';
} else { echo '<a class="ffflying fffamtt" href="index.php?m=LFlying&s='.$flyitarget.'"><span class="custom"><b>'.__("Flock Together").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "27")
{ echo '<a class="fffamtt" href="index.php?m=LUndead&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffundeada.png"><span class="custom"><b>'.__("The Lil' Necromancer").'</b></span></a>';
} else { echo '<a class="ffundead fffamtt" href="index.php?m=LUndead&s='.$undetarget.'"><span class="custom"><b>'.__("The Lil' Necromancer").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "20")
{ echo '<a class="fffamtt" href="index.php?m=LCritter&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffcrittera.png"><span class="custom"><b>'.__("Mousing Around").'</b></span></a>';
} else { echo '<a class="ffcritter fffamtt" href="index.php?m=LCritter&s='.$crittarget.'"><span class="custom"><b>'.__("Mousing Around").'</b></span></a>'; } ?>
</td>

</tr>
<tr>

<td>
<?php if ($mainselector == "25")
{ echo '<a class="fffamtt" href="index.php?m=LMagic&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffmagica.png"><span class="custom"><b>'.__("Master of Magic").'</b></span></a>';
} else { echo '<a class="ffmagic fffamtt" href="index.php?m=LMagic&s='.$magitarget.'"><span class="custom"><b>'.__("Master of Magic").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "22")
{ echo '<a class="fffamtt" href="index.php?m=LElemental&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffelementala.png"><span class="custom"><b>'.__("Ragnaros, Watch and Learn").'</b></span></a>';
} else { echo '<a class="ffelemental fffamtt" href="index.php?m=LElemental&s='.$elemtarget.'"><span class="custom"><b>'.__("Ragnaros, Watch and Learn").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "19")
{ echo '<a class="fffamtt" href="index.php?m=LBeast&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffbeasta.png"><span class="custom"><b>'.__("Best of Beasts").'</b></span></a>';
} else { echo '<a class="ffbeast fffamtt" href="index.php?m=LBeast&s='.$beastarget.'"><span class="custom"><b>'.__("Best of Beasts").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "18")
{ echo '<a class="fffamtt" href="index.php?m=LAquatic&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffaquatica.png"><span class="custom"><b>'.__("Aquatic Acquiescence").'</b></span></a>';
} else { echo '<a class="ffaquatic fffamtt" href="index.php?m=LAquatic&s='.$aquatarget.'"><span class="custom"><b>'.__("Aquatic Acquiescence").'</b></span></a>'; } ?>
</td>

<td>
<?php if ($mainselector == "26")
{ echo '<a class="fffamtt" href="index.php?m=LMechanical&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffmechanica.png"><span class="custom"><b>'.__("Roboteer").'</b></span></a>';
} else { echo '<a class="ffmechanical fffamtt" href="index.php?m=LMechanical&s='.$mechtarget.'"><span class="custom"><b>'.__("Roboteer").'</b></span></a>'; } ?>
</td>


</tr>
</tr></table><br>

<?
}

// End of Family Familiar Legion achievement



// Generate submenu for BfA Family Battler achievement

if ($mainselector == 54){ ?>

    <link rel="stylesheet" type="text/css" href="data/fflegion.css">
    <table><tr>
    <td>
    <?php if ($subfamily == "1")
    {
    echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffhumanoida.png"><span class="custom"><b>'.__("Human Resources").'</b></span></a>';
    } else { echo '<a class="ffhumanoid fffamtt" href="?m=FamilyBattler&s='.$humatarget.'"><span class="custom"><b>'.__("Human Resources").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "2")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffdragonkina.png"><span class="custom"><b>'.__("Dragons Make Everything Better").'</b></span></a>';
    } else { echo '<a class="ffdragonkin fffamtt" href="?m=FamilyBattler&s='.$dragtarget.'"><span class="custom"><b>'.__("Dragons Make Everything Better").'</b></span></a>'; } ?>
    </td>


    <td>
    <?php if ($subfamily == "3")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffflyinga.png"><span class="custom"><b>'.__("Fun With Flying").'</b></span></a>';
    } else { echo '<a class="ffflying fffamtt" href="?m=FamilyBattler&s='.$flyitarget.'"><span class="custom"><b>'.__("Fun With Flying").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "4")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffundeada.png"><span class="custom"><b>'.__("Not Quite Dead Yet").'</b></span></a>';
    } else { echo '<a class="ffundead fffamtt" href="?m=FamilyBattler&s='.$undetarget.'"><span class="custom"><b>'.__("Not Quite Dead Yet").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "5")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffcrittera.png"><span class="custom"><b>'.__("Critters With Huge Teeth").'</b></span></a>';
    } else { echo '<a class="ffcritter fffamtt" href="?m=FamilyBattler&s='.$crittarget.'"><span class="custom"><b>'.__("Critters With Huge Teeth").'</b></span></a>'; } ?>
    </td>

    </tr>
    <tr>

    <td>
    <?php if ($subfamily == "6")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffmagica.png"><span class="custom"><b>'.__("Magicians Secret").'</b></span></a>';
    } else { echo '<a class="ffmagic fffamtt" href="?m=FamilyBattler&s='.$magitarget.'"><span class="custom"><b>'.__("Magicians Secret").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "7")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffelementala.png"><span class="custom"><b>'.__("Element of Success").'</b></span></a>';
    } else { echo '<a class="ffelemental fffamtt" href="?m=FamilyBattler&s='.$elemtarget.'"><span class="custom"><b>'.__("Element of Success").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "8")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffbeasta.png"><span class="custom"><b>'.__("Beast Mode").'</b></span></a>';
    } else { echo '<a class="ffbeast fffamtt" href="?m=FamilyBattler&s='.$beastarget.'"><span class="custom"><b>'.__("Beast Mode").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "9")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffaquatica.png"><span class="custom"><b>'.__("Hobbyist Aquarist").'</b></span></a>';
    } else { echo '<a class="ffaquatic fffamtt" href="?m=FamilyBattler&s='.$aquatarget.'"><span class="custom"><b>'.__("Hobbyist Aquarist").'</b></span></a>'; } ?>
    </td>

    <td>
    <?php if ($subfamily == "10")
    { echo '<a class="fffamtt" href="?m=FamilyBattler&s='.$subselector.'"><img src="https://www.wow-petguide.com/images/ffmechanica.png"><span class="custom"><b>'.__("Machine Learning").'</b></span></a>';
    } else { echo '<a class="ffmechanical fffamtt" href="?m=FamilyBattler&s='.$mechtarget.'"><span class="custom"><b>'.__("Machine Learning").'</b></span></a>'; } ?>
    </td>


    </tr>
    </tr></table>
<?
}

// End of BfA Family Battler achievement





// Generate sub categories menu
if ($subfamily OR $subfamily == "0" OR $mainselector == "42") {
    if (!$subfamily) { $subfamily = "0"; }
    $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE (Main = $dbmainselector OR Sec_Main = $dbmainselector) AND Family = $subfamily ORDER BY Prio");
}
else {
    $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = $dbmainselector OR Sec_Main = $dbmainselector ORDER BY Prio");
}

    while ($subentry = mysqli_fetch_object($subdb)) {
 
        if ($subentry->Parent != "0") {
            $subparentdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = '$subentry->Parent'");
            $showsubentry = mysqli_fetch_object($subparentdb);
        }
        else {
            $showsubentry = $subentry;
        }
        
        
        
        // Hacky exclusion of BfA fights that are NOT family battler relevant
        // First part is hacky exclusion of broken Shadowlands fights
        if ($mainselector != 51 && ($subentry->Parent == 716 OR $showsubentry->Name == "What Do You Mean, Mind Controlling Plants?" OR $showsubentry->Name == "Crawg in the Bog" OR $showsubentry->Name == "Unbreakable" OR $showsubentry->Name == "This Little Piggy Has Sharp Tusks")) {
         // do nothing
        }
        else {
    
            if (!isset($showsubentry->{$subnameext}) || $showsubentry->{$subnameext} == ""){
            $thisentryname = $showsubentry->Name;
            $thisentryname = stripslashes(htmlentities($thisentryname, ENT_QUOTES, "UTF-8"));
            }
            else {
            $thisentryname = $showsubentry->{$subnameext};
            $thisentryname = stripslashes(htmlentities($thisentryname, ENT_QUOTES, "UTF-8"));
            }
            $thisfam_battler = stripslashes(htmlentities($showsubentry->Family_Battler_Name, ENT_QUOTES, "UTF-8"));
    
            // Check if fight is Family Familiar
            $itsff = "false";
    
            switch ($subentry->id) {
                case "145":
                    $itsff = "true";
                    break;
                case "146":
                    $itsff = "true";
                    break;
                case "148":
                    $itsff = "true";
                    break;
                case "151":
                    $itsff = "true";
                    break;
                case "156":
                    $itsff = "true";
                    break;
                case "160":
                    $itsff = "true";
                    break;
                case "165":
                    $itsff = "true";
                    break;
                case "167":
                    $itsff = "true";
                    break;
                case "168":
                    $itsff = "true";
                    break;
                case "171":
                    $itsff = "true";
                    break;
                case "175":
                    $itsff = "true";
                    break;
                case "177":
                    $itsff = "true";
                    break;
                case "181":
                    $itsff = "true";
                    break;
                case "183":
                    $itsff = "true";
                    break;
                case "184":
                    $itsff = "true";
                    break;
            }
    
    
    
            if ($showsubentry->Comment == "Placeholder" && $thisentryname == ""){
            echo "<br>";
            }
            if ($showsubentry->Comment == "Placeholder" && $thisentryname != "" && $subentry->id != "179" && $subentry->id != "426"){
            echo "<br><li class=\"placeholder\">".$thisentryname."</li>";
            }
            if ($showsubentry->Comment == "Placeholder" && $subentry->id == "179"){
            echo "<a style=\"text-decoration: none;\" href=\"?m=LegionWQ&s=337\"><li class=\"beastbutton\">".$thisentryname.":</li></a>";
            }
            if ($showsubentry->Comment == "Placeholder" && $subentry->id == "426"){
            echo "<a style=\"text-decoration: none;\" href=\"?m=LegionWQ&s=427\"><li class=\"beastbutton\">".$thisentryname.":</li></a>";
            }
    
    
            if ($showsubentry->Comment != "Placeholder" && $mainselector != "4" && $mainselector != "32" && $mainselector != "41")
            {
    
            echo "<li><a ";
    
    
            if ($subentry->id == $subselector) {
            echo "class=\"activebutton";
            $thisentrylink = $subentry->Link;
            $thisentryrematchid = $subentry->RematchID;
            }
            else {
                if ($subentry->id == $eudiff OR $subentry->id == $usdiff OR $subentry->id == $twdiff){
                    $buttonstyle = "current";
                }
                else {
                    $buttonstyle = "";
                }
            echo "class=\"button".$buttonstyle;
            }
            if ($subentry->FFAlternative != "" OR $mainentry->id == 54){
                if ($ffresetback == "true"){
                    $mainalt = $mainshow;
                    $mainshow = "LHumanoid";
                }
                if ($mainentry->id != 54) {
                    echo " fftooltip\" href=\"index.php?m=".$mainshow."&s=".$subentry->id."\" >".$thisentryname."<span class=\"custom\"><b>Quest:</b> ".$subentry->FFAlternative;
                }
                if ($mainentry->id == 54 AND $language == "en_US") {
                    echo " fftooltip\" href=\"index.php?m=".$mainshow."&s=".$subentry->id."\" >".$thisfam_battler."<span class=\"custom\"><b>Quest:</b> ".$thisentryname;
                }
                if ($mainentry->id == 54 AND $language != "en_US") {
                    echo " \" href=\"index.php?m=".$mainshow."&s=".$subentry->id."\" >".$thisentryname."<span style=\"display: none\"><b>Quest:</b> ".$thisentryname;
                }
                if ($subentry->Skull == "1"){
                    echo "<br>".__("<b>Attention:</b>This is a tough fight with the chosen family.");
                }
                echo "</span>";
                if ($ffresetback == "true") $mainshow = $mainalt;
            }
            else {
                if ($itsff == "true"){
                    echo " fftooltip\" href=\"index.php?m=".$mainshow."&s=".$subentry->id."\" >".$thisentryname."<span class=\"custom\"><b>".__("Family Familiar")."</b></span>";
                }
                else {
                    echo "\" href=\"index.php?m=".$mainshow."&s=".$subentry->id."\" >".$thisentryname;
                }
            }
    
            // Skull for Legion Family Familiar
            if ($subentry->Skull == "1"){
            echo "<div class=\"ffskull\"><img src=\"https://www.wow-petguide.com/images/skull_s.png\"></div>";
            }
            // Paw for Family Familiar view
            if ($itsff == "true"){
            echo '<div class="ffskull"><img src="https://www.wow-petguide.com/images/petbattleicon.png" style="padding-left: 7px;" height="16" width="16"></div>';
            }
            // EU flag for Garrison
            if ($subentry->id == $eudiff){
            echo '<div class="ffskull"><img src="https://www.wow-petguide.com/images/eu.png" style="padding-left: 7px;" height="16" width="16"></div>';
            }
            // US flag for garrison
            if ($subentry->id == $usdiff){
            echo '<div class="ffskull"><img src="https://www.wow-petguide.com/images/us.png" style="padding-left: 5px;" height="16" width="18"></div>';
            }
            // TW flag for garrison
            if ($subentry->id == $twdiff){
            echo '<div class="ffskull"><img src="https://www.wow-petguide.com/images/tw.png" style="padding-left: 5px;" height="16" width="18"></div>';
            }
            echo "</a></li>";
    
            }
        
        }
    }
    
    // Ad placement to left of battletable Venatus
    
    echo "</ul>";
    if ((!$user OR $user->id == 2) && mysqli_num_rows($subdb) > 4) { // For fights and pages that have a menu on the left
        if ($ads_active == true) { ?>
            <div class="vm-placement" data-id="5d790cccd6864139e1ce8025"></div>
        <?php }
    }
    if ((!$user OR $user->id == 2) && mysqli_num_rows($subdb) < 2) { // For fights that have no menu on the left, eg crysa
        if ($ads_active == true) { ?>
            <div class="vm-placement" style="position: absolute; left: -665px; top: 90px" data-id="5d790cccd6864139e1ce8025"></div>
        <?php }
    }
    echo "</div>";
}




// ====== PAGE: Article about main categories =======
if ($subselector == "" AND $mainentry->Type != "2" AND $mainselector != ""){
    $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainentry->id'") or die(mysqli_error($dbcon));
    // This is only around for Celestial Tournament which cannot move to the new articles format:
    if (mysqli_num_rows($articledb) > 0) {
        include 'classes/articles.php';
    }
    else {
        // TOP2 - add here a functionality to add a new, empty article if there is none, yet. This is for newly created menu items
    }
}

// ====== PAGE: Battletable and Alternatives =======
if ($subselector && $subselector != 0) {

    echo '<div class="remodal-bg battletable">';

    if ($strategy)
    {
      // If there are alternatives in the database, select the chosen one:
      $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $strategy");
      if ($stratdb) {
          $strat = mysqli_fetch_object($stratdb);
      }
    }
    else
    {
      $strategy = '';
    }

    // Ad placement to right of battletable Venatus
    if (!$user OR $user->id == 2) {
        if ($ads_active == true) { ?>
        <script>
        if ($(window).width() >4750) {
            document.write('<div style="position: absolute; left: 960px; top: 150px"><div class="vm-placement" data-id="5d7905e26f74dc4f153093d7"></div></div>');
        }
        </script>
    <?php }
    }

    $skipalts = false;

    include 'classes/strat_edit_process.php';
    include 'classes/battletable2.php';
    include 'classes/strat_edit.php';
    
    // ======================== Alternatives 2.0 ==========================
    if ($skipalts != "true"){

        if (!$allstratsdb) {
            if ($userrights['EditStrats'] == "yes") {
                $allstratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Sub = $subselector AND Deleted = '0'")or die("None");
            }
            else {
                if ($user) {
                    $allstratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Deleted = '0' AND ((Sub = $subselector AND Published = '1') OR (Sub = $subselector AND User = '$user->id'))") OR die(mysqli_error($dbcon));
                }
                else {
                    $allstratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Sub = $subselector AND Published = '1' AND Deleted = '0'") OR die(mysqli_error($dbcon));
                }
            }
        }
        if (mysqli_num_rows($allstratsdb) > "1") {
            ?>
                <div class="alt_tt_linkbox alternatives_tt" rel="<?php echo $user->id ?>" value="<?php echo $strategy ?>"></div>
                <div class="alt_tt_box_s"></div>
                <div class="alt_tt_box"></div>
                <div class="alt_tt_boxtext">
                    <?php echo mysqli_num_rows($allstratsdb); ?> Alternatives
                </div>
            <?
        }
    }
    
    // Internal Comments Box
    if ($userrights['EditStrats'] == 'yes') {
        // Add new internal comment
        $int_com_action = isset($_POST['int_com_action']) ? $_POST['int_com_action'] : null;
        $int_com_user = isset($_POST['int_com_user']) ? $_POST['int_com_user'] : null;
        $int_com_strategy = isset($_POST['int_com_strategy']) ? $_POST['int_com_strategy'] : null;
        if ($int_com_strategy == $strat->id && $int_com_user == $user->id && $int_com_action == 'internal_comment' && $userrights['EditStrats'] == 'yes') {
            $int_com_content = mysqli_real_escape_string($dbcon, $_POST['int_com_content']);
            mysqli_query($dbcon, "INSERT INTO Comments (`User`, `Category`, `SortingID`, `Language`, `Comment`, `IP`) VALUES ('$int_com_user', '3', '$int_com_strategy', 'en_US', '$int_com_content', '$user_ip_adress')") OR die(mysqli_error($dbcon));
            $addedid = mysqli_insert_id($dbcon);
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$int_com_user', '$user_ip_adress', '1', 'Internal Note written by Curator', '$addedid')") OR die(mysqli_error($dbcon));
            $updatetags = array();
            $updatetags[29] = 1;
            update_tags($int_com_strategy, $updatetags);
        }
        ?>
       
        <div class="internal_comment">
         Internal notes:
        </div>
        

        <?
        $int_coms_db = mysqli_query($dbcon, "SELECT * FROM Comments WHERE SortingID = $strat->id AND Category = '3' AND Deleted != '1'") OR die(mysqli_error($dbcon));
        if (mysqli_num_rows($int_coms_db) > "0") { ?>
            <div class="internal_comment">
            <table>
            <?php 
            while($int_com = mysqli_fetch_object($int_coms_db)) {
                $output_intcom = stripslashes(htmlentities($int_com->Comment, ENT_QUOTES, "UTF-8"));
                $output_intcom = str_replace(PHP_EOL, "<br>", $output_intcom);
                $intcomdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$int_com->User'");
                if (mysqli_num_rows($intcomdb) > "0") {
                    $intcom_user = mysqli_fetch_object($intcomdb);
                }
                ?>
                
                <tr id="int_comment_box_<?php echo $int_com->id ?>">
                    <td class="internal_comment" style="background-color: #f0e6c5; margin-right: 5px;" valign="top"><span name="time"><?php echo $int_com->Date; ?></span></td>
                    <td class="internal_comment" style="background-color: #f0e6c5" valign="top">
                         <span class="username" rel="<?php echo $int_com->User ?>" value="<?php echo $user->id ?>"><a class="creatorlink" style="color: #31445e; font-size: 14px" target="_blank" href="?user=<?php echo $int_com->User ?>"><?php echo $intcom_user->Name ?>:</a></span>
                    <td class="internal_comment" valign="top" style="width: 100%"><?php echo $output_intcom; ?></td>
                    <td class="internal_comment" valign="top">
                        <a style="cursor: pointer" data-remodal-target="modaldelete_<?php echo $int_com->id ?>"><img height="12" src="https://www.wow-petguide.com/images/icon_x_bright.png" /></a>
                    
                        <div class="remodalcomments" data-remodal-id="modaldelete_<?php echo $int_com->id ?>">
                            <table width="300" class="profile">
                                <tr class="profile">
                                    <th colspan="2" width="5" class="profile">
                                        <table>
                                            <tr>
                                                <td><img src="images/icon_x.png"></td>
                                                <td><img src="images/blank.png" width="5" height="1"></td>
                                                <td><p class="blogodd"><b><?php echo __("Are you sure you want to delete this comment?"); ?></td>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>
    
                                <tr class="profile">
                                    <td class="collectionbordertwo"><center>
                                        <table>
                                            <tr>
                                                <td style="padding-left: 12px;">
                                                    <input data-remodal-action="close" onclick="delete_int_comment('<?php echo $int_com->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>')" type="submit" class="comdelete" value="<?php echo __("Delete"); ?>">
                                                </td>
                                                <td style="padding-left: 15px;">
                                                    <input data-remodal-action="close" type="submit" class="comedit" value="<?php echo __("Cancel"); ?>">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    
                        <script>
                        var options = {
                            hashTracking: false
                        };
                         $('[data-remodal-id=modaldelete_<?php echo $int_com->id ?>]').remodal(options);
                        </script>
                    
                    
                    </td>
                </tr>
            
                
            <?php } ?>
        </table></div>
        <?php } ?>
        
        <div class="internal_comment">
            <form action="index.php?Strategy=<?php echo $strat->id ?>" method="post">
            <input type="hidden" name="int_com_user" value="<?php echo $user->id ?>">
            <input type="hidden" name="int_com_strategy" value="<?php echo $strat->id ?>">
            <input type="hidden" name="int_com_action" value="internal_comment">
                <table>
                    <tr>
                        <td style="margin-right: 5px;" valign="top">
                            <textarea name="int_com_content" class="cominputbright" style="height: 30px; width: 720px" onkeyup="auto_adjust_textarea_size(this);" placeholder="Type new internal note here"></textarea>
                        </td>
                        <td valign="top">
                            <input type="submit" class="comsubmit" value="Submit">
                        </td>
                    </tr>
                </table>
            
        </div>
     <?php }
         
    
    // ======================== Comment System 2.0 ==========================
    // Setting the correct category for the comments to be crawled:
    $commaincat = $mainselector;
    $comstrat = $subselector;
    echo "<br>";
    // including the comments system:
    if ($strategy) {
      print_comments_outer("2",$strategy,"dark");
    }

    echo '</div>';
    
    // Ad placement - Video Slider. Only on battletable. Only on specific cases and randomized for now. Venatus
    if (!$user OR $user->id == 2) {
        if ($ads_active == true) { ?>
            <script>
                if ($(window).width() >1550) {
                    document.write('<div class="vm-placement" data-id="5d79066ce9f6e069bd0fd5da" style="display:none"></div>');
                }
            </script>
            <?php 
        }
    }
}

// End of Regular fight menu!


switch ($sendtoast) {
    case "registersuccess":
        echo '<script>$.growl.notice({ message: "'.__("Thanks for signing up!<br>Click on your name to access account settings.<br>I also sent you a <a href='?page=messages' class='growl'>welcome message</a> with more info.").'", duration: "7000", size: "large" });</script>';
        break;
    case "loginsuccess":
        echo '<script>$.growl.notice({ message: "'.__("You are now logged in.").'", duration: "5000", size: "medium" });</script>';
        break;
    case "pwchangesuccess":
        echo '<script>$.growl.notice({ message: "'.__("Your password has been changed. Welcome back!").'", duration: "5000", size: "medium" });</script>';
        break;
    case "linkerror":
        echo '<script>$.growl.error({ message: "'.__("The link you followed could not be read. <br>You were redirected to the front page. Sorry for the inconvenience.").'", duration: "9000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregfail":
        echo '<script>$.growl.error({ message: "'.__("The Battle.net authorization was declined. To login using your Battle.net Account please try again and authorize Xu-Fu.<br>Note: Your personal data (password, email address) will never be shared by Battle.net to authorized apps.").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "bnetapierror":
        echo '<script>$.growl.error({ message: "'.__("There was a problem with the Battle.net Account service, most likely a timeout or perhaps the servers are in maintenance. Please try again later.").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregister":
        ?>
            <script>$.growl.notice({ message: "<?php echo __("Thank you for signing up! You are now logged in. <br>Note: You can change your username by clicking on your name in the top right corner.") ?>", duration: "7000", size: "large", location: "tc" });</script>
            <script>
                var userid = '<?php echo $user->id ?>';
                $.post('classes/ajax/ac_update_col_on_reg.php', {'userid':userid}, function(data) {
                });
            </script>
        <?
        break;
    case "namechanged":
        echo '<script>$.growl.notice({ message: "'.__("Your Username was changed successfully.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "pwadded":
        echo '<script>$.growl.notice({ message: "'.__("Your password was saved.").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "tokeninactive":
        echo '<script>$.growl.error({ message: "'.__("The connection to your Battle.net Account is disrupted.<br>Please visit the <a class='growl' href='index.php?page=settings'>Account settings</a> to fix this problem.").'", fixed: "true", duration: "99999999", size: "large", location: "tc" });</script>';
        break;
    case "accdeleted":
        echo '<script>$.growl.notice({ message: "'.__("Your account has been deleted. <br>Thank you for your time with Xu-Fu!").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "comsubmiterror":
        echo '<script>$.growl.error({ message: "'.__("There was a problem with processing your comment. I am sorry, please try again. If this error persists, please contact <a href='mailto:xufu@wow-petguide.com?subject=Problem with comment system' class='growl'>Aranesh</a>").'", duration: "15000", size: "large", location: "tc" });</script>';
        break;
    case "erroraddingstrat":
        echo '<script>$.growl.error({ message: "There was a problem adding your strategy. Please try again. If this error persists, please contact Aranesh.", duration: "15000", size: "large", location: "tc" });</script>';
        break;
    case "addednewstrat":
        echo '<script>$.growl.notice({ message: "New strategy created! Now you can fill it with life :-)", duration: "5000", size: "large", location: "bl" });</script>';
        break;
    case "firstnewstrat":
        echo '<script>$.growl.notice({ message: "It seems this is your first new strategy. If you need some help, check out the <a href=\"?m=StratCreationGuide\" class=\"growl\" target=\"_blank\">Strategy Creation Guide</a>", duration: "15000", size: "large", location: "bl" });</script>';
        break;
    case "strathidden":
        echo '<script>$.growl.error({ message: "The strategy you are trying to access has been unpublished by its creator. Xu-Fu apologizes for the inconvenience.", duration: "15000", size: "large", location: "tc" });</script>';
        break;
    case "stratgone":
        echo '<script>$.growl.error({ message: "The strategy you are trying to access does not exist. If this link worked in the past, the strategy might have been deleted in the meanwhile.", duration: "15000", size: "large", location: "tc" });</script>';
        break;
    case "genericerror":
        echo '<script>$.growl.error({ message: "There was a problem processing your request. Please try again. If this error persists, please contact Aranesh.", duration: "15000", size: "large", location: "tr" });</script>';
        break;
    case "stratdeleted":
        echo '<script>$.growl.error({ message: "Your strategy has been deleted. Xu-Fu is a little bit sad, but he understands, not all things are meant to last.", duration: "8000", size: "large", location: "tr" });</script>';
        break;
    case "old_collection":
        echo '<script>$.growl.error({ message: "Apologies, the link you used is outdated and no longer working. Please enter your character details below to view your collection.", duration: "8000", size: "large", location: "tc" });</script>';
        break;
}

if ($jumpto ){ ?>
    <script>
        $(window).load(function(){
        $(function(){
            $('html, body').animate({
                scrollTop: $('.anchor<?php echo $jumpto ?>').offset().top-150
            }, 800);
            return false;
        });
        });
    </script>
    <?
}

if ($urlchanger && $skipurlchange != true){
    if ($customsubs_tempz) {
        $urlchanger = $urlchanger."&Substitutes=".$customsubs_tempz;
    }
    echo '<script>window.history.replaceState("object or string", "Title", "'.$urlchanger.'");</script>';
}

echo "<script>updateAllTimes('time')</script>";
mysqli_close($dbcon);
?>
</body>