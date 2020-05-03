<?php

if ($settingspage == ""){
    $settingspage = $_POST['settingspage'];
}
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

<td width="100%"><h class="megatitle"><? echo _("UP_Title") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('profile');
    ?>
</div>

<div class="blogentryfirst">
<div class="articlebottom">
</div>

<table style="width: 100%;">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>
            <td>




<table style="width: 100%;">
<tr>
<td>

<table style="width: 85%;" class="profile">
    <tr class="profile">
        <th class="profile">
            <table>
                <tr>
                    <td>
                        <a href="?page=profile" style="text-decoration: none"><button class="settings<? if ($page == "profile") { echo "active"; } ?>" style="display: block"><? echo _("UP_BTProfile") ?></button></a>
                    </td>
                    <td>
                        <a href="?page=icon" style="text-decoration: none"><button class="settings<? if ($page == "icon") { echo "active"; } ?>" style="display: block"><? echo _("UP_BTIcon") ?></button></a>
                    </td>
                    <td>
                        <a href="?page=tooltip" style="text-decoration: none"><button class="settings<? if ($page == "tooltip") { echo "active"; } ?>" style="display: block"><? echo _("UP_BTTooltip") ?></button></a>
                    </td>
                </tr>
            </table>
        </th>
    </tr>






    <tr class="profile">
        <td class="profile">
            <table><tr>
                <td>
            <p class="blogodd"><? echo _("UP_TTInst1") ?> <span class="username" style="text-decoration: none" rel="1" value="<? echo $user->id ?>"><a target="_blank" href="?user=1" class="usernamelink com_role_99_bright">Xu-Fu</a></span>
            <br><? echo _("UP_TTInst2") ?><br>

            </td></tr></table>
            <br>

            <div style="float: left; margin-left: 20px; width:439px; ">
                <p class="blogodd" style="line-height: 30px;"><? echo _("UP_TTHPreview") ?><br>
                <div style="position: relative; border: 1px solid #4e6f95;">

                    <?
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

                        if ($usersettings[1] == "") {
                            $bgpic = "1";
                        }
                        else {
                            $bgpic = $usersettings[1];
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


                        $regdate = explode(" ", $user->regtime);
                        $regdate = explode("-", $regdate[0]);
                        if ($language != "en_US") {
                            $regdate = $regdate[2].".".$regdate[1].".".$regdate[0];
                        }
                        else {
                            $regdate = $regdate[1]."/".$regdate[2]."/".$regdate[0];
                        }
                        $commentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND Deleted != '1'");
                        if (mysqli_num_rows($commentsdb) > "0"){
                            $numcomments = mysqli_num_rows($commentsdb);
                        }
                        else {
                            $numcomments = "0";
                        }
                        $stratsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = '$user->id' AND Published = '1'")or die("None");
                        if (mysqli_num_rows($stratsdb) > "0"){
                            $numstrats = mysqli_num_rows($stratsdb);
                        }
                        else {
                            $numstrats = "0";
                        }


                        if ($collection) {
                            $stats = get_collection_stats($collection); ?> 
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

                        <div id="col2" class="ut_petdonut" <? if (!$collection OR $usersettings[3] == "0") { echo 'style="display: none;"'; } ?>>
                            <div id="Families_<? echo $user->id ?>" style="height: 100%; width: 100%;"></div>
                        </div>

                    <div class="ut_bg">
                        <img id="ttbgpic" src="https://www.wow-petguide.com/images/userbgs/<? echo $bgpic ?>.jpg">
                    </div>

                    <div id="col3" class="ut_icon" <? if (!$collection OR $usersettings[3] == "0") { echo 'style="left: 30px;"'; } else { echo 'style="left: 43px;"'; }?>>
                        <a target="_blank" href="index.php?user=<? echo $user->id ?>"><img id="col4" <? echo $usericon ?> class="ut_icon" <? if (!$collection OR $usersettings[3] == "0") { echo 'style="border: 1px solid #509bb9;"'; } ?>></a>
                    </div>

                    <div id="col5" class="ut_title" <? if (!$collection OR $usersettings[3] == "0") { echo 'style="left: 130px;"'; } else { echo 'style="left: 150px;"'; }?>>
                        <a target="_blank"href="index.php?user=<? echo $user->id ?>" class="ut_title"><? echo $user->Name; ?></a>
                        <p class="ut_role"><? echo $showtitle; ?></p>
                    </div>

                    <div id="ttonstat" class="ut_online" <?
                        if (!$collection OR $usersettings[3] == "0") {
                            echo 'style="left: 130px';
                            if ($usersettings[6] == "0") { echo ';display:none" '; }
                            echo '"';
                        } else
                        {
                            echo 'style="left: 150px';
                            if ($usersettings[6] == "0") { echo ';display:none" '; }
                            echo '"';
                        }?>>
                        <p class="ut_online"><b>&#8226;</b> <? echo _("UP_TTLLco") ?></p>
                    </div>


                    <div class="ut_content">
                        <div id="col7" <? if (!$collection OR $usersettings[3] == "0") { echo 'style="float: left; height: 30px; width: 335px;"'; } else { echo 'style="float: left; height: 45px; width: 335px;"'; }?>> </div>

                        <?
                        if ($user->PrSocFacebook == "" && $user->PrSocTwitter == "" && $user->PrSocInstagram == "" && $user->PrSocYoutube == "" && $user->PrSocReddit == "" && $user->PrSocTwitch == "") {
                            $socmempty = "true";
                        } ?>

                        <div id="socmcont" class="ut_socm" <? if ($socmempty == "true" OR $usersettings[5] == "0"){ echo 'style="display:none;"'; } ?>>
                            <a id="socm_facebook" class="<? if ($user->PrSocFacebook != "") { echo "ut_sm_facebook"; } ?>" href="<? echo $user->PrSocFacebook ?>" target="_blank"></a>
                            <a id="socm_twitter" class="<? if ($user->PrSocTwitter != "") { echo "ut_sm_twitter"; } ?>" href="<? echo $user->PrSocTwitter ?>" target="_blank"></a>
                            <a id="socm_isntagram" class="<? if ($user->PrSocInstagram != "") { echo "ut_sm_instagram"; } ?>" href="<? echo $user->PrSocInstagram ?>" target="_blank"></a>
                            <a id="socm_youtube" class="<? if ($user->PrSocYoutube != "") { echo "ut_sm_youtube"; } ?>" href="<? echo $user->PrSocYoutube ?>" target="_blank"></a>
                            <a id="socm_reddit" class="<? if ($user->PrSocReddit != "") { echo "ut_sm_reddit"; } ?>" href="<? echo $user->PrSocReddit ?>" target="_blank"></a>
                            <a id="socm_twitch" class="<? if ($user->PrSocTwitch != "") { echo "ut_sm_twitch"; } ?>" href="<? echo $user->PrSocTwitch ?>" target="_blank"></a>
                        </div>

                        <?
                            $introoutput = stripslashes($user->PrIntro);
                            $introoutput = htmlentities($introoutput, ENT_QUOTES, "UTF-8");
                            $editoutput = $introoutput;
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
                            <div id="ttintro" class="ut_contelement ut_intro" style="margin-bottom:20px<? if ($usersettings[2] == "0") { echo ';display:none" '; } ?>">
                                <p class="ut_intro"><? echo $showintro ?></p>
                            </div>



                        <? if ($collection) { ?>
                            <div id="col1" class="ut_contelement" style="margin-bottom:15px;<? if ($usersettings[3] == "0") { echo 'display:none;'; } ?>" >
                                <a class="ut_pets" href="index.php?user=<? echo $user->id ?>&display=Collection" target="_blank"><? echo $stats['Unique']." "._("UP_TTunipets") ?></a>
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

                        <div id="ttqf1" style="float:left;width:230;margin-bottom:15px<? if ($usersettings[4] == "0") { echo ';display:none" '; } ?>" >
                            <p class="ut_qf"><? echo _("UP_TTTjoined") ?>: <? echo $regdate ?>
                            <? if ($useronline != "true") { ?><br><? echo _("UP_TTLL") ?> <? echo $showtime ?></p>
                            <? } ?>
                        </div>
                        
                        <? if ($numstrats > "0") { ?>
                            <div id="ttqf2" style="float:left; min-width: 80px">
                                <p class="ut_qf">Strategies: 
                            </div>
                            <div id="ttqf3" style="float:left; margin-left: 5px">
                                <p class="ut_qf"><? echo $numstrats; ?></p>
                            </div>
                        <? } ?>

                        <? if ($numcomments > "0") { ?>                        
                            <div id="ttqf2" style="float:left; min-width: 80px">
                                <p class="ut_qf"><? echo _("FormComBlogPromptComments") ?>:
                            </div>
                            <div id="ttqf3" style="float:left; margin-left: 5px">
                                <p class="ut_qf"><? echo $numcomments; ?></p> 
                            </div>
                        <? } ?>
                        
                          <div class="ut_contact">
                            <a href="#"><img src="https://www.wow-petguide.com/images/userdd_messages.png"></a> <a class="ut_contact" href="#"><? echo _("UP_TTSendMsg") ?></a>
                        </div>

                    <div style="clear: both"></div>

                    </div>
                </div>
            </div>
        </div>




        <div class="ttbgs">
            <p class="blogodd" style="line-height: 30px;"><? echo _("UP_TTAvBGs") ?>:<br>
            <div style="padding-top: 10px; height:500px; width:100%;overflow: auto;background: #363636;">
            <?
                $dirrand = "images/userbgs";
                $dhrand = opendir($dirrand);

                $countthem = "0";

                while (false !== ($filename = readdir($dhrand))) {
                    $filesplits = explode(".",$filename);

                    if ($filesplits[1] == "jpg" && preg_match("/^[1234567890]*$/is", $filesplits[0])){
                        $iconlist[$countthem]['filename'] = $filename;
                        $iconlist[$countthem]['id'] = $filesplits[0];
                        $countthem++;
                      }
                }
                sort($iconlist);

                $mycountthis = "0";
                while ($mycountthis < $countthem){
                    echo '<div onClick="change_bg(\''.$iconlist[$mycountthis]['id'].'\',\''.$user->id.'\',\''.$user->ComSecret.'\');" class="ttbgsind" style="background-image: url(\'https://www.wow-petguide.com/images/userbgs/'.$iconlist[$mycountthis]['filename'].'\');"></div>';
                    $mycountthis++;
                }
                ?>
            </div>
        </div>

        <div class="ttoptions">
            <p class="blogodd" style="line-height: 30px;"><? echo _("UP_TTTOptions") ?>:</p><br>

                <?
                if ($usersettings[2] == "") { $ttintro = "1"; } else { $ttintro = $usersettings[2]; }
                if ($usersettings[2] == "" && $user->PrIntro == ""){ $ttintro = "0"; }
                if ($usersettings[3] == "") { $ttcol = "1"; } else { $ttcol = $usersettings[3]; }
                if ($usersettings[4] == "") { $ttqf = "1"; } else { $ttqf = $usersettings[4]; }
                if ($usersettings[5] == "") { $ttsocm = "1"; } else { $ttsocm = $usersettings[5]; }
                if ($usersettings[5] == "" && $user->PrSocFacebook == "" && $user->PrSocTwitter == "" && $user->PrSocInstagram == "" && $user->PrSocYoutube == "" && $user->PrSocReddit == "" && $user->PrSocTwitch == "") { $ttsocm = "0"; }
                if ($usersettings[6] == "") { $ttonstat = "1"; } else { $ttonstat = $usersettings[6]; }
                ?>
                <table>
                    <tr >
                        <td id="ttintrotr" <? if ($user->PrIntro == "") { echo 'style="opacity: 0.4; filter: alpha(opacity = 40);"'; } ?>>
                            <p class="blogodd" style="white-space:nowrap"><? echo _("UP_TTTIntro") ?>:</p>
                        </td>
                        <td id="ttintrotr2" <? if ($user->PrIntro == "") { echo 'style="opacity: 0.4; filter: alpha(opacity = 40);"'; } ?>>
                            <div id="ttintroswitch" class="armoryswitch ttwarningintro">
                                <input type="checkbox" <? if ($user->PrIntro == "") { echo 'disabled="disabled"'; } ?> class="armoryswitch-checkbox" id="ttintrot" onchange="change_tt_settings('ttintrot','<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($ttintro == "1") { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="ttintrot">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>

                            <? if ($user->PrIntro == "") { ?>
                            <script>
                                initialize_ttintrowarning();
                            </script>
                            <? } ?>
                         </td>

                        <td>
                            <a class="alternativessmall" href="#modalabout" style="display:block"><img height="18" src="images/icon_pen.png" /></a>
                        </td>
                    </tr>

                    <tr >
                        <td id="ttsocmtr" <? if ($socmempty == "true") { echo 'style="opacity: 0.4; filter: alpha(opacity = 40);"'; } ?>>
                            <p class="blogodd" style="white-space:nowrap"><? echo _("UP_TTTSS") ?>:</p>
                        </td>
                        <td id="ttsocmtr2" <? if ($socmempty == "true") { echo 'style="opacity: 0.4; filter: alpha(opacity = 40);"'; } ?>>
                            <div id="ttsocmswitch" class="armoryswitch ttwarningsocm">
                                <input type="checkbox" <? if ($socmempty == "true") { echo 'disabled="disabled"'; } ?> class="armoryswitch-checkbox" id="ttsocmt" onchange="change_tt_settings('ttsocmt','<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($ttsocm == "1") { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="ttsocmt">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>

                            <? if ($socmempty == "true") { ?>
                            <script>
                                initialize_ttsocmwarning();
                            </script>
                            <? } ?>
                         </td>

                        <td>
                            <a class="alternativessmall" href="#modalsocm" style="display:block"><img height="18" src="images/icon_pen.png" /></a>
                        </td>
                    </tr>

                    <tr>
                        <td id="ttcoltr" <? if (!$collection) { echo 'style="opacity: 0.4; filter: alpha(opacity = 40);"'; } ?>>
                            <p class="blogodd" style="white-space:nowrap"><? echo _("UM_PetCollection") ?>:</p>
                        </td>
                        <td id="ttcoltr2" <? if (!$collection) { echo 'style="opacity: 0.4; filter: alpha(opacity = 40);"'; } ?>>
                            <div id="ttcolswitch" class="armoryswitch ttwarningcol">
                                <input type="checkbox" <? if (!$collection) { echo 'disabled="disabled"'; } ?> class="armoryswitch-checkbox" id="ttcoll" onchange="change_tt_settings('ttcoll','<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($ttcol == "1" && $collection) { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="ttcoll">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>

                        <? if (!$collection) { ?>
                        <td>
                            <a style="white-space:nowrap" href="?page=collection" class="wowhead"><? echo _("FormSelectImportPets") ?></a>
                        </td>
                        <script>
                            $('.ttwarningcol').tooltipster({
                                content: 'Import your pet collection to activate this option',
                                theme: 'tooltipster-smallnote',
                                updateAnimation: 'null',
                                animationDuration: 350,
                            });
                        </script>
                        <? } ?>
                    </tr>

                <? // Deactivating the whole Quick Facts display or no display - this is not used anymore, saving the code for later
                /*
                    <tr>
                        <td><p class="blogodd">Quick facts:</p></td>
                        <td>
                            <div class="armoryswitch">
                                <input type="checkbox" class="armoryswitch-checkbox" id="ttqf" onchange="change_tt_settings('ttqf','<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($ttqf == "1") { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="ttqf">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>
                    </tr>
                */
                ?>

                    <tr>
                        <td><p class="blogodd" style="white-space:nowrap"><? echo _("UP_TTTonlinestat") ?>:</p></td>
                        <td>
                            <div class="armoryswitch">
                                <input type="checkbox" class="armoryswitch-checkbox" id="ttonstats" onchange="change_tt_settings('ttonstats','<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($ttonstat == "1") { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="ttonstats">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>
                    </tr>

                </table>

        </div>

        <div class="remodal remodalsuggest" data-remodal-id="modalabout">
            <table width="800" class="profile">
                <tr class="profile">
                    <th colspan="2" width="5" class="profile">
                        <table border="0">
                            <tr>
                                <td><img src="images/headericon_profile_blue.png"></td>
                                <td><img src="images/blank.png" width="5" height="1"></td>
                                <td><p class="blogodd"><span style="white-space: nowrap;"><b><? echo _("UP_TTInsteditin") ?></span></td>
                            </tr>
                        </table>
                    </th>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo" colspan="2">
                       <textarea placeholder="Add your introduction here" class="cominputbright" id="intro_field" style="height: 200px; width: 800px;" name="p_aboutcontent" onkeyup="auto_adjust_textarea_size(this); count_remaining_profile(this)" maxlength="5000"><? echo $editoutput ?></textarea>
                        <p class="blogodd">
                    </td>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo">
                        <table>
                            <tr>
                                <td style="padding-left: 12px;">
                                    <input data-remodal-action="close" onclick="save_intro('tt','<? echo $user->id ?>','<? echo $user->ComSecret ?>')" type="submit" class="comedit" value="<? echo _("FormComButtonSavechange"); ?>">
                                </td>
                                <td style="padding-left: 15px;">
                                    <input data-remodal-action="close" type="submit" class="comdelete" value="<? echo _("FormButtonCancel"); ?>">
                                </td>
                                <td align="right" width="100%">
                                    <span style="padding-right: 15px" class="smallodd" id="intro_remaining"></span><span style="white-space: nowrap;"><p class="smallodd">[b]<b><? echo _("FormComFormatBold") ?></b>[/b] - [i]<i><? echo _("FormComFormatItalic") ?></i>[/i] - [u]<u><? echo _("FormComFormatUnderline") ?></u>[/u]</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>



        <div class="remodal remodalsuggest" data-remodal-id="modalsocm">
            <table width="450" class="profile">
                <tr class="profile">
                    <th colspan="2" width="5" class="profile">
                        <table border="0">
                            <tr>
                                <td><img src="images/headericon_profile_blue.png"></td>
                                <td><img src="images/blank.png" width="5" height="1"></td>
                                <td><p class="blogodd"><span style="white-space: nowrap;"><b><? echo _("UP_TTTInsteditsm") ?></span></td>
                            </tr>
                        </table>
                    </th>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo" colspan="2">
                        <p class="blogodd">
                            <? echo _("UP_TTInsteditsm") ?><br><br>

                            <table border="0">
                                <tr>
                                    <td>
                                        <p class="blogodd"><b><? echo _("UP_PRHFacebook") ?>:</b>
                                    </td>
                                    <td style="padding-left: 5px;">
                                        <input class="cominputbright" style="width: 450px; font-size: 14px;" placeholder="" type="text" maxlength="200" id="soc_facebook" size="35" value="<? echo $user->PrSocFacebook ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p class="blogodd"><b><? echo _("UP_PRHTwitter") ?>:</b>
                                    </td>
                                    <td style="padding-left: 5px;">
                                        <input class="cominputbright" style="width: 450px; font-size: 14px;" placeholder="" type="text" maxlength="200" id="soc_twitter" size="35" value="<? echo $user->PrSocTwitter ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p class="blogodd"><b><? echo _("UP_PRHInstagram") ?>:</b>
                                    </td>
                                    <td style="padding-left: 5px;">
                                        <input class="cominputbright" style="width: 450px; font-size: 14px;" placeholder="" type="text" maxlength="200" id="soc_instagram" size="35" value="<? echo $user->PrSocInstagram ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p class="blogodd"><b><? echo _("UP_PRHYoutube") ?>:</b>
                                    </td>
                                    <td style="padding-left: 5px;">
                                        <input class="cominputbright" style="width: 450px; font-size: 14px;" placeholder="" type="text" maxlength="200" id="soc_youtube" size="35" value="<? echo $user->PrSocYoutube ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p class="blogodd"><b><? echo _("UP_PRHReddit") ?>:</b>
                                    </td>
                                    <td style="padding-left: 5px;">
                                        <input class="cominputbright" style="width: 450px; font-size: 14px;" placeholder="" type="text" maxlength="200" id="soc_reddit" size="35" value="<? echo $user->PrSocReddit ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p class="blogodd"><b><? echo _("UP_PRHTwitch") ?>:</b>
                                    </td>
                                    <td style="padding-left: 5px;">
                                        <input class="cominputbright" style="width: 450px; font-size: 14px;" placeholder="" type="text" maxlength="200" id="soc_twitch" size="35" value="<? echo $user->PrSocTwitch ?>">
                                    </td>
                                </tr>
                            </table>


                    </td>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo">
                        <table>
                            <tr>
                                <td style="padding-left: 12px;">
                                    <input data-remodal-action="close" onclick="save_socm('tt','<? echo $user->id ?>','<? echo $user->ComSecret ?>')" type="submit" class="comedit" value="<? echo _("FormComButtonSavechange"); ?>">
                                </td>
                                <td style="padding-left: 15px;">
                                    <input data-remodal-action="close" type="submit" class="comdelete" value="<? echo _("FormButtonCancel"); ?>">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>



        </td>
    </tr>

</table>
</table>
<br>






</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
switch ($sendtoast) {
    case "genericerror":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_GenError").'", duration: "7000", size: "large", location: "tc" });</script>';
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





