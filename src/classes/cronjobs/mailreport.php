<?php
include("../../data/dbconnect.php");
include("../functions.php");

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

$allcomsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW()") OR die(mysqli_error($dbcon));
$allcoms = mysqli_num_rows($allcomsdb);

$allspamdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '1' AND CloseType = 'Spam'") OR die(mysqli_error($dbcon));
$allspam = mysqli_num_rows($allspamdb);

$allantispamdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '1' AND CloseType = 'Spam' AND ClosedBy = 'Antispam'") OR die(mysqli_error($dbcon));
$allantispam = mysqli_num_rows($allantispamdb);

$endb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'en_US'") OR die(mysqli_error($dbcon));
$encom = mysqli_num_rows($endb);

$frdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'fr_FR'") OR die(mysqli_error($dbcon));
$frcom = mysqli_num_rows($frdb);

$dedb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'de_DE'") OR die(mysqli_error($dbcon));
$decom = mysqli_num_rows($dedb);

$itdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'it_IT'") OR die(mysqli_error($dbcon));
$itcom = mysqli_num_rows($itdb);

$esdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'es_ES'") OR die(mysqli_error($dbcon));
$escom = mysqli_num_rows($esdb);

$pldb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'pl_PL'") OR die(mysqli_error($dbcon));
$plcom = mysqli_num_rows($pldb);

$ptdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'pt_PT'") OR die(mysqli_error($dbcon));
$ptcom = mysqli_num_rows($ptdb);

$rudb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'ru_RU'") OR die(mysqli_error($dbcon));
$rucom = mysqli_num_rows($rudb);

$kodb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'ko_KR'") OR die(mysqli_error($dbcon));
$kocom = mysqli_num_rows($kodb);

$zhdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Date > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND Date <= NOW() AND Deleted = '0' AND Language = 'zh_TW'") OR die(mysqli_error($dbcon));
$zhcom = mysqli_num_rows($zhdb);




$recipient = "xufu@wow-petguide.com";
$recname = "Aranesh";
$subject = "Daily Comment Report";

$content = "<br><br>Here is your daily comment report: <br><br>Number of comments in last 24h: <b>".$allcoms."</b><br>Spam comments filtered: <b>".$allantispam."</b><br><br>";

$content = $content."Comments split by language:<br>";
if ($encom > "0"){
     $content = $content."<b>EN:</b> ".$encom."<br>";
}
if ($decom > "0"){
     $content = $content."<b>DE:</b> ".$decom."<br>";
}
if ($frcom > "0"){
     $content = $content."<b>FR:</b> ".$frcom."<br>";
}
if ($itcom > "0"){
     $content = $content."<b>IT:</b> ".$itcom."<br>";
}
if ($escom > "0"){
     $content = $content."<b>ES:</b> ".$escom."<br>";
}
if ($plcom > "0"){
     $content = $content."<b>PL:</b> ".$plcom."<br>";
}
if ($ptcom > "0"){
     $content = $content."<b>PT:</b> ".$ptcom."<br>";
}
if ($rucom > "0"){
     $content = $content."<b>RU:</b> ".$rucom."<br>";
}
if ($kocom > "0"){
     $content = $content."<b>KO:</b> ".$kocom."<br>";
}
if ($zhcom > "0"){
     $content = $content."<b>ZH:</b> ".$zhcom."<br>";
}


// xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody);
