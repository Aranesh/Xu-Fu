<?php
include("../../data/dbconnect.php");
include("../functions.php");
include("../com_functions.php");

$category = $_REQUEST["category"];
$sortingid = $_REQUEST["sortingid"];
$styleset = $_REQUEST["styleset"];
$offset = $_REQUEST["offset"];
$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$visitorid = $_REQUEST["visitorid"];
$comeditdeadline = $_REQUEST["editd"];
$numcoms = $_REQUEST["numcoms"];
$language = $_REQUEST["lang"];
$natoren = $_REQUEST["natoren"];
$comfilter = $_REQUEST["comfilter"];

if ($natoren == "en") {
    $language = "en_US";
}

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("../../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
set_language_vars($language);
if ($userid) {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid' && ComSecret = '$comsecret'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
    }
}

print_comments($category,$sortingid,$styleset,$offset,$numcoms,$language,$natoren,$comfilter);

mysqli_close($dbcon); 