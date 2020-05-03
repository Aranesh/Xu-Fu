<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$log = $_REQUEST["log"];
$language = $_REQUEST["l"];

if ($userid) {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
    }
}

if ($log) {
    $watch_userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$log'");
    if (mysqli_num_rows($watch_userdb) > "0") {
        $watch_user = mysqli_fetch_object($watch_userdb);
        $watch_userrights = format_userrights($watch_user->Rights);
    }
}

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
putenv("LANG=".$language.".UTF-8");
setlocale(LC_ALL, $language.".UTF-8");

$domain = "messages";
bindtextdomain($domain, "../../Locale");
textdomain($domain);

set_language_vars($language);

if ($user) {
    $viewusersets = explode("|", $user->Settings);
    if ($viewusersets[1] == "") {
        $bgpic = "1";
    }
    else {
        $bgpic = $viewusersets[1];
    }

    $titledb = mysqli_query($dbcon, "SELECT * FROM UserTitles WHERE id = '$user->Title'");
    if (mysqli_num_rows($titledb) > "0") {
        $thistitle = mysqli_fetch_object($titledb);
        $titleext = "Name_".$language;
        if ($thistitle->${'titleext'} != "") {
            $showtitle = htmlentities($thistitle->${'titleext'}, ENT_QUOTES, "UTF-8");
        }
        else {
            $showtitle = htmlentities($thistitle->Name_en_US, ENT_QUOTES, "UTF-8");
        }
    }
    else {
        $showtitle = "Pet Battler";
    }

    if ($user->UseWowAvatar == "0"){
        $usericon = 'src="https://www.wow-petguide.com/images/pets/'.$user->Icon.'.png"';
    }
    else if ($user->UseWowAvatar == "1"){
        $usericon = 'src="https://www.wow-petguide.com/images/userpics/'.$user->id.'.jpg?lastmod?='.$user->IconUpdate.'"';
    }

    $activitydb = mysqli_query($dbcon, "SELECT * FROM UserProtocol WHERE User = '$user->id' ORDER BY Date DESC LIMIT 1");
    if (mysqli_num_rows($activitydb) > "0") {
        $lastactivity = mysqli_fetch_object($activitydb);
        $datetimenow = strtotime(date("Y-m-d H:i:s"));
        $mydatetime = strtotime($lastactivity->Date);
        $calctime = $datetimenow - $mydatetime;
        if ($calctime > "29030400") {
            $showtime = _("UP_TTLL1");
        }
        if ($calctime <= "29030400") {
            $showtime = _("UP_TTLL2");
        }
        if ($calctime <= "24192000") {
            $showtime = _("UP_TTLL3");
        }
        if ($calctime <= "4838400") {
            $showtime = _("UP_TTLL4");
        }
        if ($calctime <= "4838400") {
            $showtime = _("UP_TTLL5");
        }
        if ($calctime <= "2419200") {
            $showtime = _("UP_TTLL6");
        }
        if ($calctime <= "604800") {
            $showtime = _("UP_TTLL7");
        }
        if ($calctime <= "70000") {
            $showtime = _("UP_TTLL8");
        }
        if ($calctime <= "3600") {
            $showtime = _("UP_TTLL9");
        }
        if ($calctime <= "260") {
            $useronline = "true";
        }
    }

    $commentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND Deleted != '1'");
                        if (mysqli_num_rows($commentsdb) > "0"){
                            $numcomments = mysqli_num_rows($commentsdb);
                        }
                        else {
                            $numcomments = "0";
                        }
    if ($watch_userrights['EditStrats'] == "yes") {
        $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id'")or die("None");
    }
    else {
        $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' AND Published = '1'")or die("None");
    }
                        if (mysqli_num_rows($stratsdb) > "0"){
                            $numstrats = mysqli_num_rows($stratsdb);
                        }
                        else {
                            $numstrats = "0";
                        }
    if ($viewusersets[3] == "1" OR $viewusersets[3] == ""){
        $viewuser_findcol = find_collection($user, 2);
        if ($viewuser_findcol != "No Collection") {
            $fp = fopen($viewuser_findcol['Path'], 'r');
            $viewcollection = json_decode(fread($fp, filesize($viewuser_findcol['Path'])), true);
        }        
        if ($viewcollection) {
            $petnext = "Name_".$language;
            if ($language == "en_US") {
                $petnext = "Name";
            }
            $stats = get_collection_stats($viewcollection, $petnext);  
        }
    }

    if ($viewcollection) {
    ?>
    <script>
    $(function () {
        $("#Families_<? echo $user->id ?>").CanvasJSChart( {
            title:{
                fontFamily: "MuseoSans-500",
                fontWeight: "normal",
                fontColor: "black"
            },
            interactivityEnabled: false,
            animationEnabled: false,
            backgroundColor: null,
            width: 130,
            height: 130,
            data: [
                {
                    type: "doughnut",
                    startAngle:270,
                    innerRadius: "60%",
                    showInLegend: false,
                    toolTipContent: "",
                    indexLabel: "",
                    dataPoints: [
                        {  y: <? echo $stats['Humanoid'] ?>, color: "#08adff" },
                        {  y: <? echo $stats['Dragonkin'] ?>, color: "#59bc11" },
                        {  y: <? echo $stats['Flying'] ?>, color: "#d4ca4f" },
                        {  y: <? echo $stats['Undead'] ?>, color: "#9f6c73" },
                        {  y: <? echo $stats['Critter'] ?>, color: "#7c5943" },
                        {  y: <? echo $stats['Magic'] ?>, color: "#7341ee" },
                        {  y: <? echo $stats['Elemental'] ?>, color: "#eb7012" },
                        {  y: <? echo $stats['Beast'] ?>, color: "#ec2b22" },
                        {  y: <? echo $stats['Aquatic'] ?>, color: "#08aab7" },
                        {  y: <? echo $stats['Mechanic'] ?>, color: "#7e776d" }
                   ]
                }
                ]
            });
        });
    </script>
    <? } ?>

    <div class="ut_container">

        <? if ($viewcollection) { ?>
            <div class="ut_petdonut">
                <div id="Families_<? echo $user->id ?>" style="height: 100%; width: 100%;"></div>
            </div>
        <? } ?>

        <div class="ut_bg">
            <img src="https://www.wow-petguide.com/images/userbgs/<? echo $bgpic ?>.jpg">
        </div>

        <div class="ut_icon" <? if (!$viewcollection) { echo 'style="left: 30px;"'; } else { echo 'style="left: 43px;"'; }?>>
            <a target="_blank" href="index.php?user=<? echo $user->id ?>"><img <? echo $usericon ?> class="ut_icon" <? if (!$viewcollection) { echo 'style="border: 1px solid #509bb9;"'; } ?>></a>
        </div>

        <div class="ut_title" <? if (!$viewcollection) { echo 'style="left: 130px;"'; } else { echo 'style="left: 150px;"'; }?>>
            <a target="_blank"href="index.php?user=<? echo $user->id ?>" class="ut_title"><? echo $user->Name; ?></a>
            <p class="ut_role"><? echo $showtitle ?></p>
        </div>

        <? if ($useronline == "true" && ($viewusersets[6] == "1" OR $viewusersets[6] == "")) { ?>
            <div class="ut_online" <? if (!$viewcollection) { echo 'style="left: 130px;"'; } else { echo 'style="left: 150px;"'; }?>>
                <p class="ut_online"><b>&#8226;</b> <? echo _("UP_TTLLco") ?></p>
            </div>
        <? } ?>


        <div class="ut_content">

            <div <? if (!$viewcollection) { echo 'style="float: left; height: 30px; width: 335px;"'; } else { echo 'style="float: left; padding-top: 45px; width: 335px;"'; }?>> </div>

            <? if ($viewusersets[5] == "1"){ ?>
                <div class="ut_socm">
                    <?
                        if ($user->PrSocFacebook != "") {
                            $smout = str_replace('"', '\"', $user->PrSocFacebook);
                            $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                            echo '<a class="ut_sm_facebook" href="'.$smout.'" target="_blank"></a>';
                        }
                        if ($user->PrSocTwitter != "") {
                            $smout = str_replace('"', '\"', $user->PrSocTwitter);
                            $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                            echo '<a class="ut_sm_twitter" href="'.$smout.'" target="_blank"></a>';
                        }
                        if ($user->PrSocInstagram != "") {
                            $smout = str_replace('"', '\"', $user->PrSocInstagram);
                            $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                            echo '<a class="ut_sm_instagram" href="'.$smout.'" target="_blank"></a>';
                            }
                        if ($user->PrSocYoutube != "") {
                            $smout = str_replace('"', '\"', $user->PrSocYoutube);
                            $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                            echo '<a class="ut_sm_youtube" href="'.$smout.'" target="_blank"></a>';
                        }
                        if ($user->PrSocReddit != "") {
                            $smout = str_replace('"', '\"', $user->PrSocReddit);
                            $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                            echo '<a class="ut_sm_reddit" href="'.$smout.'" target="_blank"></a>';
                        }
                        if ($user->PrSocTwitch != "") {
                            $smout = str_replace('"', '\"', $user->PrSocTwitch);
                            $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                            echo '<a class="ut_sm_twitch" href="'.$smout.'" target="_blank"></a>';
                        }
                    ?>
                </div>
            <? } ?>

            <? if (($viewusersets[2] == "1" OR $viewusersets[2] == "") && $user->PrIntro != ""){
                $introoutput = stripslashes($user->PrIntro);
                $introoutput = htmlentities($introoutput, ENT_QUOTES, "UTF-8");
                $introoutput = str_replace("[u]", "<u>", $introoutput);
                $introoutput = str_replace("[/u]", "</u>", $introoutput);
                $introoutput = str_replace("[i]", "<i>", $introoutput);
                $introoutput = str_replace("[/i]", "</i>", $introoutput);
                $introoutput = str_replace("[b]", "<b>", $introoutput);
                $introoutput = str_replace("[/b]", "</b>", $introoutput);
                $introoutput = replace_url_dark($introoutput);
                $introoutput = preg_replace("/\n/s", " ", $introoutput);
                if (strlen($introoutput) > "130") {
                    $showintro = substr($introoutput, 0, 130);
                    $cutter = "129";
                    while (substr($showintro, -1) != " ") {
                        $showintro = substr($introoutput, 0, "$cutter");
                        $cutter = $cutter - 1;
                    }
                        $addlink = "true";
                    }
                    else {
                        $showintro = $introoutput;
                    }

                $words = str_word_count($showintro, 1);
                function cmp($a, $b) {
                    return strlen($b) - strlen($a);
                }
                usort($words, 'cmp');

                if (strlen($words[0]) > "35") {
                    $showintro = "<a class='ut_contact' target='_blank' href='index.php?user=".$user->id."'>"._("UP_TTintrolong1")."</a>";
                }
                else {
                    if ($addlink == "true") {
                        $showintro = $showintro."... <a class='ut_contact' target='_blank' href='index.php?user=".$user->id."'>"._("UP_TTintrolong2")."</a>";
                    }
                }
            ?>
                <div class="ut_contelement" style="margin-bottom:20px;">
                    <p class="ut_intro"><? echo $showintro ?></p>
                </div>
            <? } ?>


            <? if ($viewcollection){ ?>
                <div class="ut_contelement" style="margin-bottom:15px;">
                    <a class="ut_pets" href="index.php?user=<? echo $user->id ?>&display=Collection" target="_blank"><? echo $stats['Unique']; ?> <? echo _("UP_TTunipets") ?></a>
                </div>
            <? } ?>

                        <? if (($user->PrBattleTag && $user->PrBTagNum) OR $user->PrDiscord) { ?>
                            <div style="float:left;width:350;margin-bottom:15px;">
                                <p class="ut_qf"><?
                                    if ($user->PrBattleTag) {
                                        echo _("UP_PRHBtag")." ";
                                        echo htmlentities($user->PrBattleTag, ENT_QUOTES, "UTF-8");
                                        echo "#";
                                        echo htmlentities($user->PrBTagNum, ENT_QUOTES, "UTF-8");
                                        echo " (";
                                        echo strtoupper($user->PrBTagRegion);
                                        echo ")";
                                        }
                                    if ($user->PrBattleTag AND $user->PrBattleTag) { echo "<br>"; }
                                    if ($user->PrDiscord) { ?> <? echo _("UP_PRHDiscord") ?>: <? echo htmlentities($user->PrDiscord, ENT_QUOTES, "UTF-8"); } ?></div>
                        <? } ?>

            <div style="float:left;width:230;margin-bottom:15px;">
                <p class="ut_qf"><? echo _("UP_TTTjoined") ?>: <span name="timeuser"><? echo $user->regtime ?></span>
                <? if ($useronline != "true" && ($viewusersets[6] == "1" OR $viewusersets[6] == "")) { ?><br><? echo _("UP_TTLL") ?> <? echo $showtime ?></p>
                <? } ?>
            </div>

            <? if ($numstrats > "0") { ?>
                <div style="float:left; min-width: 80px">
                    <p class="ut_qf">Strategies: 
                </div>
                <div style="float:left; margin-left: 5px">
                    <p class="ut_qf"><? echo $numstrats; ?></p>
                </div>
            <? } ?>

            <? if ($numcomments > "0") { ?>                         
                <div style="float:left; min-width: 80px">
                    <p class="ut_qf"><? echo _("FormComBlogPromptComments") ?>:
                </div>
                <div style="float:left; margin-left: 5px">
                    <p class="ut_qf"><? echo $numcomments; ?></p> 
                </div>
            <? }

            if (!$log && $user->id != "1") { ?>
            <div class="ut_contact">
                <span class="tooltip" title="<? echo _("UP_TTErrNoAcc") ?>">
                    <span style="cursor: pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"> <a class="ut_contact"><? echo _("UP_TTSendMsg") ?></a></span>
                </span>
                <script>
                    $(document).ready(function() {
                        $('.tooltip').tooltipster({
                            maxWidth: '150',
                            theme: 'tooltipster-smallnote'
                        });
                    });
                </script>
            </div>
            <? }

            if ($log == $user->id && $user->id != "1") { ?>
            <div class="ut_contact">
                <span class="tooltip" title="<? echo _("UP_TTNoMsgToS") ?>">
                    <span style="cursor: pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"> <a class="ut_contact"><? echo _("UP_TTSendMsg") ?></a></span>
                </span>
                <script>
                    $(document).ready(function() {
                        $('.tooltip').tooltipster({
                            maxWidth: '150',
                            theme: 'tooltipster-smallnote'
                        });
                    });
                </script>
            </div>
            <? }

            if ($log && $log != $user->id && $user->id != "1") { ?>
            <div class="ut_contact">
                <a target="_blank" href="index.php?page=writemsg&to=<? echo $user->id ?>"><img src="https://www.wow-petguide.com/images/userdd_messages.png"></a> <a target="_blank" class="ut_contact" href="index.php?page=writemsg&to=<? echo $user->id ?>"><? echo _("UP_TTSendMsg") ?></a>
            </div>
            <? } ?>


        <div style="clear: both"></div>

        </div>
    </div>
    <script>updateAllTimes('timeuser')</script>
    <?
}
else {
    echo _("UP_TTDBerror");
}
mysqli_close($dbcon);
