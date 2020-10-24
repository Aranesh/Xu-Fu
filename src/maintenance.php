<?php

$language = "en_US";

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
putenv("LANG=".$language.".UTF-8");
setlocale(LC_ALL, $language.".UTF-8");

$domain = "messages";
bindtextdomain($domain, "Locale");
textdomain($domain);

set_language_vars($language);

// =================== ANTI SPAM-TRAFFIC SYSTEM ===================

$ipnorm = $_SERVER['REMOTE_ADDR'];
$antispamdb = mysqli_query($dbcon, "SELECT * FROM Blacklist WHERE IP = '$ipnorm'");
$antispam = mysqli_num_rows($antispamdb);

if ($antispam > "0"){
$spamentry = mysqli_fetch_object($antispamdb);
$spam = $spamentry->Counter+1;
$spamtime = date('Y-m-d H:i:s');
$update = mysqli_query($dbcon, "UPDATE Blacklist SET `Counter` = '$spam' WHERE IP = '$ipnorm'");
$update = mysqli_query($dbcon, "UPDATE Blacklist SET `Lastupdate` = '$spamtime' WHERE IP = '$ipnorm'");
die;
}


// ======================= BEGIN OF ACTUAL PAGE =========================

?>
<!DOCTYPE html5>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Xu-Fu's Pet Battle Strategies</title>
<meta name="Description" content="<?php echo _("World of Warcraft Pet Battle guides - your one-stop place for strategies to beat all WoW pet battle quests, achievements and opponents!") ?>" />
<meta name="Keywords" content="<?php echo _("warcraft pets, warcraftpets, wow vanity pets, wow battle pets, wow companions") ?>" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <link rel="alternate" hreflang="en" href="https://www.wow-petguide.com/">
    <link rel="alternate" hreflang="fr" href="http://fr.wow-petguide.com/">
    <link rel="alternate" hreflang="de" href="http://de.wow-petguide.com/">
    <link rel="alternate" hreflang="es" href="http://es.wow-petguide.com/">
    <link rel="alternate" hreflang="ru" href="http://ru.wow-petguide.com/">
    <link rel="alternate" hreflang="pt" href="http://pt.wow-petguide.com/">
    <link rel="alternate" hreflang="it" href="http://it.wow-petguide.com/">
    <link rel="alternate" hreflang="zh" href="http://zh.wow-petguide.com/">
    <link rel="alternate" hreflang="ko" href="http://ko.wow-petguide.com/">
    <meta property="fb:app_id" content="1146631942116322"/>
    <meta property="og:url"           content="https://wow-petguide.com" />
    <meta property="og:type"          content="Website" />
    <meta property="og:title"         content="Xu-Fu's Pet Battle Strategies" />
    <meta property="og:description"   content="WoW Pet Battle Guides" />
    <meta property="og:image"         content="https://www.wow-petguide.com/images/xufuprofile.jpg" />
<link rel="stylesheet" type="text/css" href="data/style.css?v=2.0<?php echo $mtime;?>">
<link rel="stylesheet" href="data/remodal.css">
<link rel="stylesheet" href="data/remodal-default-theme.css">
<link rel="stylesheet" href="data/jquery.growl.css">
<link rel="stylesheet" href="data/image-picker.css">
<link rel="stylesheet" href="data/select2.css">
<link rel="stylesheet" href="data/chosen.css">
<link rel="stylesheet" type="text/css" href="data/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" type="text/css" href="data/tooltipster/themes.css" />
<script src="//wow.zamimg.com/widgets/power.js">
var wowhead_tooltips = { "colorlinks": false, "iconizelinks": true, "renamelinks": false }</script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="data/jquery.capslockstate.js"></script>
<script src="data/jquery.waypoints.min.js"></script>
<script src="data/functions.js?v=2.0<?php // echo $mtime;?>"></script>
<script src="data/jquery.growl.js"></script>
<script src="data/tooltipster.bundle.min.js"></script>
<script src="data/table.js"></script>
<script src="data/clipboard.min.js"></script>
<script src="data/remodal.min-2.js"></script>
<script src="data/jquery.canvasjs.min.js"></script>
<script src="data/image-picker.min.js"></script>
<script src="data/select2.min.js"></script>
<script src="data/chosen.jquery.min.js"></script>
</head>



<body>

<div class="remodal-bg wrapper">
<div class="spacer">


</div>
<div class="remodal-bg container">


<nav>
    <ul>



</nav>


</div>
<div class="searcher">




</div>
</div>


<?php
// ==================== Mod Access Page =========================
?>

<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h2>Maintenance Mode</h2><br>
<p class="blogeven">Wartungsmodus - Mode Maintenance - Modalità Manutenzione - режим обслуживания - Modo de mantenimiento<br>
Modo de Manutenção - 유지 관리 모드 - 维护模式计算机</p></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="blogentryfirst">

<div class="articlebottom">
</div>

<center>

<table width="75%" border="0">
<tr><td width="100%">

<br><center>
<img src="images/XuFu_Maintenance.jpg" alt="" />
<br><br>

<div style="max-width:800px;text-align: left">

<p class="blogodd">Xu-Fu is currently being updated. <br>
Update: things broke, fixing it, takes a bit longer sorry<br><br>
Downtime started at <a class="wowhead" href="https://www.google.com/search?client=firefox-b-d&q=11%3A15+GMT" target="_blank">15:48 GMT</a> and is expected to last 60 minutes.<br><br>
<b>What's in this update:</b><br><br>

Fixing an issue that caused localization to not work. While updating, the page will display without any text, better to have maintenance mode on :D<br>
To all English speaking visitors: sorry, nothing new for you this time!<br>
<br>
<? // You can follow the progress in <a class="wowhead" href="https://trello.com/c/NXbAIXz2/19-localization-restructure" target="_blank">this Trello ticket</a>. ?>

<br>
<hr class="home"><br>
<p class="blogodd">Want to support Xu-Fu? Consider sending a Ko-Fi :-)<br>
But don't worry, we'll keep the site updated and alive either way.<br>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
<script type='text/javascript' src='https://ko-fi.com/widgets/widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Buy Aranesh a Ko-fi', '#2486d1', 'F1F61IQGC');kofiwidget2.draw();</script> 
                    </div>
                    
</p>
</div>
<br>

</td></tr>
</table>


<br>
<div class="maincomment">
<br>
<table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
<tr><td style="width:100%;padding-left: 240px">

<br><br>
<?
// ==== COMMENT SYSTEM 3.0 FOR MAIN ARTICLES HAPPENS HERE ====
print_comments_outer("0","38","medium");
?>


</div>



<?




die;






