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
        $watch_user_col = find_collection($watch_user, 2);
        if ($watch_user_col != "No Collection") {
            $watcher_collection = TRUE;
        }   
    }
}



// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("../../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
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
            $showtime = __("more than a year ago");
        }
        if ($calctime <= "29030400") {
            $showtime = __("a year ago");
        }
        if ($calctime <= "24192000") {
            $showtime = __("several months ago");
        }
        if ($calctime <= "4838400") {
            $showtime = __("a few months ago");
        }
        if ($calctime <= "4838400") {
            $showtime = __("two months ago");
        }
        if ($calctime <= "2419200") {
            $showtime = __("this month");
        }
        if ($calctime <= "604800") {
            $showtime = __("this week");
        }
        if ($calctime <= "70000") {
            $showtime = __("today");
        }
        if ($calctime <= "3600") {
            $showtime = __("an hour ago");
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
        $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' AND Deleted = '0'")or die("None");
    }
    else {
        $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' AND Deleted = '0' AND Published = '1'")or die("None");
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
        $("#Families_<?php echo $user->id ?>").CanvasJSChart( {
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
                        {  y: <?php echo $stats['Humanoid'] ?>, color: "#08adff" },
                        {  y: <?php echo $stats['Dragonkin'] ?>, color: "#59bc11" },
                        {  y: <?php echo $stats['Flying'] ?>, color: "#d4ca4f" },
                        {  y: <?php echo $stats['Undead'] ?>, color: "#9f6c73" },
                        {  y: <?php echo $stats['Critter'] ?>, color: "#7c5943" },
                        {  y: <?php echo $stats['Magic'] ?>, color: "#7341ee" },
                        {  y: <?php echo $stats['Elemental'] ?>, color: "#eb7012" },
                        {  y: <?php echo $stats['Beast'] ?>, color: "#ec2b22" },
                        {  y: <?php echo $stats['Aquatic'] ?>, color: "#08aab7" },
                        {  y: <?php echo $stats['Mechanic'] ?>, color: "#7e776d" }
                   ]
                }
                ]
            });
        });
    </script>
    <?php } ?>

    <div class="ut_container">

        <?php if ($viewcollection) { ?>
            <div class="ut_petdonut">
                <div id="Families_<?php echo $user->id ?>" style="height: 100%; width: 100%;"></div>
            </div>
        <?php } ?>

        <div class="ut_bg">
            <img style="width: 455px" src="https://www.wow-petguide.com/images/userbgs/<?php echo $bgpic ?>.jpg">
        </div>

        <div class="ut_icon" <?php if (!$viewcollection) { echo 'style="left: 25px;"'; } else { echo 'style="left: 38px;"'; }?>>
            <a target="_blank" href="index.php?user=<?php echo $user->id ?>"><img <?php echo $usericon ?> class="ut_icon" <?php if (!$viewcollection) { echo 'style="border: 1px solid #509bb9;"'; } ?>></a>
        </div>

        <div class="ut_title" <?php if (!$viewcollection) { echo 'style="left: 125px;"'; } else { echo 'style="left: 145px;"'; }?>>
            <a target="_blank"href="index.php?user=<?php echo $user->id ?>" class="ut_title"><?php echo $user->Name; ?></a>
            <p class="ut_role"><?php echo $showtitle ?></p>
        </div>

        <?php if ($useronline == "true" && ($viewusersets[6] != 0 OR $watch_user->Role > 48)) { ?>
            <div class="ut_online" <?php if (!$viewcollection) { echo 'style="left: 125px;"'; } else { echo 'style="left: 145px;"'; }?>>
                <p class="ut_online"><b>&#8226;</b> <?php echo __("Currently online") ?></p>
            </div>
        <?php } ?>


        <div class="ut_content">

            <div <?php if (!$viewcollection) { echo 'style="float: left; height: 30px; width: 335px;"'; } else { echo 'style="float: left; padding-top: 45px; width: 335px;"'; }?>> </div>

            <?php if ($viewusersets[5] == "1"){ ?>
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
            <?php } ?>

            <?php if (($viewusersets[2] == "1" OR $viewusersets[2] == "") && $user->PrIntro != ""){
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
                    $showintro = "<a class='ut_contact' target='_blank' href='index.php?user=".$user->id."'>".__("Read full introduction")."</a>";
                }
                else {
                    if ($addlink == "true") {
                        $showintro = $showintro."... <a class='ut_contact' target='_blank' href='index.php?user=".$user->id."'>".__("continue reading")."</a>";
                    }
                }
            ?>
                <div class="ut_contelement" style="margin-bottom:20px;">
                    <p class="ut_intro"><?php echo $showintro ?></p>
                </div>
            <?php } ?>


            <?php if ($viewcollection){ ?>
                <div class="ut_contelement" style="margin-bottom:15px;">
                    <a class="ut_pets" href="index.php?user=<?php echo $user->id ?>&display=Collection" target="_blank"><?php echo $stats['Unique']; ?> <?php echo __("unique pets collected") ?></a>
                    <?php if ($viewcollection && $watch_user->id != $user->id && $watcher_collection) { ?>
                    <a class="ut_contact" style="font-size: 12px; cursor: pointer; line-height: 12px;" target="_blank" href="?m=Compare&user_1=<?php echo $watch_user->id ?>&user_2=<?php echo $user->id ?>">
                        <?php echo __('Compare against your collection'); ?>
                    </a>
                    <?php } ?>
                </div>
            <?php } ?>

                        <?php if (($user->PrBattleTag && $user->PrBTagNum) OR $user->PrDiscord) { ?>
                            <div style="float:left;width:350;margin-bottom:15px;">
                                <p class="ut_qf"><?
                                    if ($user->PrBattleTag) {
                                        echo __("BattleTag:")." ";
                                        echo htmlentities($user->PrBattleTag, ENT_QUOTES, "UTF-8");
                                        echo "#";
                                        echo htmlentities($user->PrBTagNum, ENT_QUOTES, "UTF-8");
                                        echo " (";
                                        echo strtoupper($user->PrBTagRegion);
                                        echo ")";
                                        }
                                    if ($user->PrBattleTag AND $user->PrBattleTag) { echo "<br>"; }
                                    if ($user->PrDiscord) { ?> <?php echo __("Discord") ?>: <?php echo htmlentities($user->PrDiscord, ENT_QUOTES, "UTF-8"); } ?></div>
                        <?php } ?>

            <div style="float:left;width:230;margin-bottom:15px;">
                <p class="ut_qf"><?php echo __("Joined") ?>: <span name="timeuser"><?php echo $user->regtime ?></span>
                <?php if ($useronline != "true" && ($viewusersets[6] != 0 OR $watch_user->Role > 48)) { ?><br><?php echo __("Last active:") ?> <?php echo $showtime ?></p>
                <?php } ?>
            </div>

            <?php if ($numstrats > "0") { ?>
                <div style="float:left; min-width: 90px">
                    <p class="ut_qf"><?php echo __("Strategies") ?>: 
                </div>
                <div style="float:left; margin-left: 5px">
                    <p class="ut_qf"><?php echo $numstrats; ?></p>
                </div>
            <?php } ?>

            <?php if ($numcomments > "0") { ?>                         
                <div style="float:left; min-width: 90px">
                    <p class="ut_qf"><?php echo __("Comments") ?>:
                </div>
                <div style="float:left; margin-left: 5px">
                    <p class="ut_qf"><?php echo $numcomments; ?></p> 
                </div>
            <?php }

            if (!$log && $user->id != "1") { ?>
            <div class="ut_contact">
                <span class="tooltip" title="<?php echo __("You must be logged in to send messages") ?>">
                    <span style="cursor: pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"> <a class="ut_contact"><?php echo __("Send message") ?></a></span>
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
            <?php }

            if ($log == $user->id && $user->id != "1") { ?>
            <div class="ut_contact">
                <span class="tooltip" title="<?php echo __("Cannot send messages to yourself") ?>">
                    <span style="cursor: pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"> <a class="ut_contact"><?php echo __("Send message") ?></a></span>
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
            <?php }

            if ($log && $log != $user->id && $user->id != "1") { ?>
            <div class="ut_contact">
                <a target="_blank" href="index.php?page=writemsg&to=<?php echo $user->id ?>"><img src="https://www.wow-petguide.com/images/userdd_messages.png"></a> <a target="_blank" class="ut_contact" href="index.php?page=writemsg&to=<?php echo $user->id ?>"><?php echo __("Send message") ?></a>
            </div>
            <?php } ?>


        <div style="clear: both"></div>

        </div>
    </div>
    <script>updateAllTimes('timeuser')</script>
    <?
}
else {
    echo __("There was an error fetching the user data. Please refresh the page and try again");
}
mysqli_close($dbcon);