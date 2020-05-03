<?php
include("../../data/dbconnect.php");
include("../../classes/functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$strat = $_REQUEST["stratid"];

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
    $stratdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $strat");
    if (mysqli_num_rows($stratdb) < "1") {
        $failed = "1";        
    }
}

if ($user && $user->ComSecret == $comsecret && $failed != "1") {
    $userrights = format_userrights($user->Rights);

    // User has localization rights
    if ($userrights['LocArticles'] == "yes") {
        
        $locales = array(
            0 => array(
                'locale' => 'de_DE',
                'Name' => 'German'
            ),
            1 => array(
                'locale' => 'fr_FR',
                'Name' => 'French'
            ),
            2 => array(
                'locale' => 'it_IT',
                'Name' => 'Italian'
            ),
            3 => array(
                'locale' => 'es_ES',
                'Name' => 'Spanish'
            ),
            4 => array(
                'locale' => 'pl_PL',
                'Name' => 'Polish'
            ),
            5 => array(
                'locale' => 'pt_BR',
                'Name' => 'Portuguese'
            ),
            6 => array(
                'locale' => 'ru_RU',
                'Name' => 'Russian'
            ),
            7 => array(
                'locale' => 'ko_KR',
                'Name' => 'Korean'
            ),
            8 => array(
                'locale' => 'zh_TW',
                'Name' => 'Chinese'
            ),
        );
        
        foreach ($locales as $locale) {
            $ext_title = "title_".$locale['locale'];
            $ext_title_import = "Name_".$locale['locale'];
            $inputtitle = $_REQUEST[$ext_title];
            $inputtitle = mysqli_real_escape_string($dbcon, $inputtitle);
            mysqli_query($dbcon, "UPDATE Sub SET $ext_title_import = '$inputtitle' WHERE id = '$strat'") OR die("NOK");
    
            $ext_comment = "comment_".$locale['locale'];
            $ext_comment_import = "Comment_".$locale['locale'];
            $inputcomment = $_REQUEST[$ext_comment];
            $inputcomment = mysqli_real_escape_string($dbcon, $inputcomment);
            mysqli_query($dbcon, "UPDATE Sub SET $ext_comment_import = '$inputcomment' WHERE id = '$strat'") OR die("NOK");
        }
        echo "OK";
    }
}

if ($failed == "1") {
    echo "NOK";
}
mysqli_close($dbcon);

