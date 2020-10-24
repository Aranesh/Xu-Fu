<?php

// ======================================= OUTPUT COMMENT BOX ===========================================================================

function print_comments_outer($category,$sortingid,$styleset) {

// ===== Globals =====
    global $dbcon;
    global $language;
    global $tcomlanguage;
    $tlanguage = $tcomlanguage;
    if ($tlanguage != "") {
        $language = $tlanguage;
    }
    global $usericon;
    global $user;
    global $visitorid;
    global $comeditdeadline;
    global $user_ip_adress;
    global $tcomid;
    global $tcomcat;
    global $tcomsortid;
    global $tcomnatoren;
    global $tcomhighlight;
    global $jumptoblog;
    global $ads_active;

    $pagesettingsdb = mysqli_query($dbcon, "SELECT * FROM PageSettings LIMIT 1");
    $pagesettings = mysqli_fetch_object($pagesettingsdb);
    $comgolden = $pagesettings->Com_Gold;
    $numcoms = $pagesettings->Com_Displayed;
    $comhidden = $pagesettings->Com_Hidden;

    $usersettings = explode("|", $user->Settings);
    $userrights = explode("|", $user->Rights);

// ===== Settings =====

if ($user){
    $comfilter = $usersettings[0];
}
else {
    $comfilter = $_SESSION["comfilter"];
}
if (!$comfilter) {
    $comfilter = "0";
}

// ===== Definitions =====

switch ($styleset) {
    case "dark":
        $ctablehead = "com_header_dark";
        $ctablewidth = "850";
        $cheader = "comheaddark"; // Error message when not able to fetch more comments
        $ctitle = ""; // title of comments table header "XX comments"
        $ctitlelink = "loginbright"; // Link to other languages comments
        $cfilter = "comfilterdark"; // Styling of filter dropdown
        $cselects = "petselect"; // Styling of filter dropdown items
        $serrortext = "commenterror";  // Error messages within respond field
        $votecolorgrey = "vote_grey";
        $votecolorgreen = "vote_green";
        $votecolorred = "vote_red";
        $votecolorgold = "vote_gold";
     break;
    case "medium":
        $ctablehead = "com_header_dark";
        $ctablewidth = "1000";
        $cheader ="comheaddark"; // Error message when not able to fetch more comments
        $ctitle = ""; // title of comments table header "XX comments"
        $ctitlelink = "loginbright"; // Link to other languages comments
        $cfilter = "comfilterdark"; // Styling of filter dropdown
        $cselects = "petselect"; // Styling of filter dropdown items
        $serrortext = "commenterror";  // Error messages within respond field
        $votecolorgrey = "vote_grey";
        $votecolorgreen = "vote_green";
        $votecolorred = "vote_red";
        $votecolorgold = "vote_gold";
        $combg = "4D4D4D";
        $comtable = "maincomseven";
        $oddeven = "even";
     break;
    case "bright":
        $ctablehead = "com_header_dark";
        $ctablewidth = "1000";
        $cheader ="comheadbright"; // Error message when not able to fetch more comments
        $ctitle = "blogodd"; // title of comments table header "XX comments"
        $ctitlelink = "loginbright"; // Link to other languages comments
        $cfilter = "comfilterdark"; // Styling of filter dropdown
        $cselects = "petselect"; // Styling of filter dropdown items
        $serrortext = "commenterror";  // Error messages within respond field
        $votecolorgrey = "vote_grey";
        $votecolorgreen = "vote_green";
        $votecolorred = "vote_red";
        $votecolorgold = "vote_gold";
        $oddeven = "odd";
        $combg = "B0B0B0";
        $comtable = "maincomsodd";
     break;
}

if ($category == "1") {
    $ctablewidth = "70%";
}


// DB request of English comments
if ($category != "1") {
    switch ($comfilter) {
        case "0":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = 'en_US' ORDER BY Date DESC");
            break;
        case "1":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = 'en_US' ORDER BY Date");
            break;
        case "2":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = 'en_US' ORDER BY Votes DESC, Date DESC");
            break;
        case "3":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = 'en_US' ORDER BY Votes ASC, Date DESC");
            break;
    }
}

if ($category == "1") {
    switch ($comfilter) {
        case "0":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Date DESC");
            break;
        case "1":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Date");
            break;
        case "2":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Votes DESC, Date DESC");
            break;
        case "3":
                $encommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Votes ASC, Date DESC");
            break;
    }
}
$num_comments_db = mysqli_query($dbcon, "SELECT COUNT(*) AS `count` FROM `Comments` WHERE Category = '$category' AND Deleted = 0 AND SortingID = '$sortingid'");
$num_comments = mysqli_fetch_array($num_comments_db);

$encommentsnum = "0";
$dbencounter = "0";
$eninvalidcount = "0";
$encompusher = 0;

if (mysqli_num_rows($encommentsdb) > "0") {
    while ($dbencounter < mysqli_num_rows($encommentsdb)) {

        $encomment = mysqli_fetch_object($encommentsdb);
        $addencomment = "go";

        if ($userrights[0] != "1") {                                                      // Only do error checking if the logged in user is a regular user. Those with settings "can see all comments" skip error checking

            $mainuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$encomment->User' LIMIT 1");
            if (mysqli_num_rows($mainuserdb) > "0") {
                $mainuser = mysqli_fetch_object($mainuserdb);
                $mainuserrole = $mainuser->Role;
            }

            if ($encomment->Votes < $comhidden AND $mainuserrole < 50 AND $encomment->User != $user->id) {        // Only add comment if it has enough votes OR is from an admin or moderator OR is from the logged in user
                $addencomment = "error";
            }

            if ($mainuser->Role < 50) {
                $inappreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '0' AND SortingID = '$encomment->id'");
                $spamreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '1' AND SortingID = '$encomment->id'");
                $combinedthresh = mysqli_num_rows($spamreportdb)+mysqli_num_rows($inappreportdb);
                if (mysqli_num_rows($inappreportdb) >= $pagesettings->Com_ReportInappThresh OR mysqli_num_rows($spamreportdb) >= $pagesettings->Com_ReportSpamThresh OR $combinedthresh >= $pagesettings->Com_ReportInappThresh) {
                    $addencomment = "error";                                                    // Only add comment if it doesn't have too many reports against it
                }
            }

            $userreport = "";
            if ($user) {
                $userreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND User = '$user->id' AND SortingID = '$encomment->id' LIMIT 1");
                if (mysqli_num_rows($userreportdb) > "0") {
                    $userreport = mysqli_fetch_object($userreportdb);
                    if (($userreport->Type == "0" OR $userreport->Type == "1") && $mainuserrole < 50) {
                        $addencomment = "error";                                                // This user made a report for spam or inappropriate ==> do not load comment
                    }
                }
            }

        }
        // Counting EN comment, since it is OK to display to this user
        if ($addencomment == "go") {
            $encommentsnum++;
            if ($encomment->id == $tcomid) {
                $encompusher = $encommentsnum;
            }
        }
        else {
            if ($encommentsnum < $numcoms) {
                $eninvalidcount++;
            }
        }
    $dbencounter++;
    }
}

$maincommentsnum = $encommentsnum;
$maincompusher = $encompusher;
$maininvalidcount = $eninvalidcount;

// ===== DB Request Native Language, only for non-blog pages =====
if ($language != "en_US" AND $category != "1"){

    switch ($comfilter) {
        case "0":
                $maincommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Date DESC");
            break;
        case "1":
                $maincommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Date");
            break;
        case "2":
                $maincommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Votes DESC, Date DESC");
            break;
        case "3":
                $maincommentsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Votes ASC, Date DESC");
            break;
    }

    if (mysqli_num_rows($maincommentsdb) > "0") {

        $maincommentsnum = "0";
        $dbmaincounter = "0";
        $maininvalidcount = "0";

        while ($dbmaincounter < mysqli_num_rows($maincommentsdb)) {

            $maincomment = mysqli_fetch_object($maincommentsdb);
            $addmaincomment = "go";

            if ($userrights[0] != "1") {                                                      // Only do error checking if the logged in user is a regular user. Those with settings "can see all comments" skip error checking
                $mainuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$maincomment->User' LIMIT 1");
                if (mysqli_num_rows($mainuserdb) > "0") {
                    $mainuser = mysqli_fetch_object($mainuserdb);
                    $mainuserrole = $mainuser->Role;
                }

                if ($maincomment->Votes < $comhidden AND $mainuserrole < 50 AND $maincomment->User != $user->id) {        // Only add comment if it has enough votes OR is from an admin or moderator OR is from the logged in user
                    $addmaincomment = "error";
                }

                if ($mainuser->Role < 50) {
                    $inappreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '0' AND SortingID = '$maincomment->id'");
                    $spamreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '1' AND SortingID = '$maincomment->id'");
                    $combinedthresh = mysqli_num_rows($spamreportdb)+mysqli_num_rows($inappreportdb);
                    if (mysqli_num_rows($inappreportdb) >= $pagesettings->Com_ReportInappThresh OR mysqli_num_rows($spamreportdb) >= $pagesettings->Com_ReportSpamThresh OR $combinedthresh >= $pagesettings->Com_ReportInappThresh) {
                        $addmaincomment = "error";                                                    // Only add comment if it doesn't have too many reports against it
                    }
                }

                $userreport = "";
                if ($user) {
                    $userreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND User = '$user->id' AND SortingID = '$maincomment->id' LIMIT 1");
                    if (mysqli_num_rows($userreportdb) > "0") {
                        $userreport = mysqli_fetch_object($userreportdb);
                        if (($userreport->Type == "0" OR $userreport->Type == "1") && $mainuserrole < 50) {
                            $addmaincomment = "error";                                                // This user made a report for spam or inappropriate ==> do not load comment
                        }
                    }
                }
            }
            // Counting Nat-language comment, since it is OK to display to this user
            if ($addmaincomment == "go") {
                $maincommentsnum++;
                if ($maincomment->id == $tcomid) {
                    $maincompusher = $maincommentsnum;
                }
            }
            else {
                if ($maincommentsnum < $numcoms) {
                    $maininvalidcount++;
                }
            }
        $dbmaincounter++;
        }
    }
}

// ===== Output of Comment header table with filters and language options =====

switch ($category) {
    case "0":
        $linkforward = "index.php?m=".$sortingid;
        break;
    case "1":
        $linkforward = "index.php?News=".$sortingid;
        break;
    case "2":
        $linkforward = "index.php?Strategy=".$sortingid;
        break;
}

$showheadernat = "1";
$showheaderen = "1";

if (!$maincommentsnum) {
    $maincommentsnum = "0";
    $showheadernat = "2";
}
if (!$encommentsnum) {
    $encommentsnum = "0";
    $showheaderen = "2";
}

$displaylangnat = decode_language($language);
$displaylangen = decode_language("en");

// Print Table Header 
?>

    <?php if ($maincommentsnum > 5 && $user->id != 2434) { // Venatus ad placement at top of comments IF there are more than 5 comments!
        if ($ads_active == true) { ?>
            <div class="vm-placement" style="float: left; padding: 0 0 10 60" data-id="5d7905f671d1621a68eb8f22"></div>
    <?php }
    } ?>
    
    
    <div style="width:<?php echo $ctablewidth ?>" class="<?php echo $ctablehead ?>">
        <table class="anchorcom_header" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="3" width="100%">
                    <table width="100%">
                        <tr>
                            <?php if ($category != 1) { // Do not show language selector for news entries, they are global ?>
                            <td align="left" style="padding-left: 20px;">
                                <div id="coms_head_title_nat">
                                    <p class="<?php echo $ctitle ?>">
                                        <span id="com_head_numcoms_nat"><?php echo $maincommentsnum; ?></span> <?
                                        if ($maincommentsnum == "1") { echo __("Comment"); }
                                        if ($maincommentsnum > "1" OR $maincommentsnum == "0") { echo __("Comments"); }
                                        ?>
                                        (<?php echo $displaylangnat['short'] ?>)
                                    </p>
                                </div>

                                <div id="coms_head_title_en" style="display:none">
                                    <p class="<?php echo $ctitle ?>">
                                        <span id="com_head_numcoms_en"><?php echo $encommentsnum ?></span> <?
                                        if ($encommentsnum == "1") { echo __("Comment"); }
                                        if ($encommentsnum > "1" OR $encommentsnum == "0") { echo __("Comments"); }
                                        ?>
                                        (EN)
                                    </p>
                                </div>
                                <?php if ($language != "en_US") { ?>
                                    <div id="coms_en_button"><p><a class="<?php echo $ctitlelink ?>" onclick="show_en_coms()">(<?php echo __("Click to see"); ?> <span id="coms_head_counter_en"><?php echo $encommentsnum ?></span> <?php echo __("English comments"); ?>)</a></div>
                                    <div id="coms_native_button" style="display:none"><p><a class="<?php echo $ctitlelink ?>" onclick="show_native_coms()">(<?php echo __("Click to go back to your own language"); ?>)</a></div>
                                <?php } ?>
                            </td>
                            <?php } ?>
                            <td width="50%" align="right"><p class="<?php echo $ctitle ?>"><?php echo __("Show first"); ?>:</p></td>
                            <td width="1%" align="right" valign="center">
                                <form action="<?php echo $linkforward ?>" method="post">
                                    <input type="hidden" name="comcat" value="<?php echo $category ?>">
                                    <input type="hidden" name="comsortid" value="<?php echo $sortingid ?>">
                                    <input type="hidden" name="comlanguage" value="<?php echo $language ?>">
                                    <input type="hidden" id="comfilternatoren" name="comnatoren" value="nat">
                                    <input type="hidden" name="changecomfilter" value="true">

                                    <select class="<?php echo $cfilter ?>" name="comfilter" onchange="this.form.submit()">
                                        <option class="<?php echo $cselects ?>" value="newest" <?php if ($comfilter == "0") echo "selected"; ?>><?php echo __("Newest"); ?></option>
                                        <option class="<?php echo $cselects ?>" value="oldest" <?php if ($comfilter == "1") echo "selected"; ?>><?php echo __("Oldest"); ?></option>
                                        <option class="<?php echo $cselects ?>" value="votes" <?php if ($comfilter == "2") echo "selected"; ?>><?php echo __("Highest rating"); ?></option>
                                        <option class="<?php echo $cselects ?>" value="voteslow" <?php if ($comfilter == "3") echo "selected"; ?>><?php echo __("Lowest rating"); ?></option>
                                     </select>
                                </form>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <br>
<?php 


// Output of selected main language comments:
$lowband = $numcoms-3;
if (isset($maincompusher) && $maincompusher > $lowband) {
    $firstpull = $maincompusher+3;
    }
else {
    $firstpull = $numcoms;
}
if ($firstpull > $maincommentsnum) {
    $firstpull = $maincommentsnum;
}

if ($firstpull > $numcoms) {
    $startoffset = $firstpull-$numcoms+$maininvalidcount;
}
else {
    $startoffset = $maininvalidcount;
}


$remcoms = $maincommentsnum-$firstpull;
if ($remcoms <= $numcoms) {
    $addcomstext = "Load last ".$remcoms." comments";
}
else {
    $addcomstext = "Load ".$numcoms." more comments";
}
?>
<div id="coms_native">
    <div style="display:none" id="offset_<?php echo $sortingid ?>_native"><?php echo $startoffset ?></div>
    <div style="display:none" id="numcoms_<?php echo $sortingid ?>_native"><?php echo $numcoms ?></div>
    <div style="display:none" id="allcoms_<?php echo $sortingid ?>_native"><?php echo $maincommentsnum ?></div>
    <div style="display:none" id="remcoms_<?php echo $sortingid ?>_native"><?php echo $remcoms ?></div>

    <table id="com_table_<?php echo $sortingid ?>_native" width="<?php echo $ctablewidth ?>" cellpadding="0" cellspacing="0">
        <?
            print_comments($category,$sortingid,$styleset,"0",$firstpull,$language,"native",$comfilter);
        ?>
        <tr>
            <td style="padding-left:75px"></td><td colspan="2" width="100%"></td>
        </tr>
    </table>

    <table id="com_footer_<?php echo $sortingid ?>_native" width="<?php echo $ctablewidth ?>" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <center>
                    <a class="nostyle" <?php if ($maincommentsnum <= $firstpull) { echo 'style="display:none"'; } ?> id="addcomsb_<?php echo $sortingid ?>_native" onclick="load_more_coms('<?php echo $sortingid ?>','native','<?php echo $category ?>','<?php echo $sortingid ?>','<?php echo $styleset ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $visitorid ?>','<?php echo $numcoms ?>','<?php echo $language ?>','<?php echo $comeditdeadline ?>','<?php echo $comfilter ?>')">
                    <span id="addcomstext_<?php echo $sortingid ?>_native" class='button'><?php echo $addcomstext ?></span>
                    </a>
                    <div id="errorfetch_<?php echo $sortingid ?>_native" style="display:none">
                        <p class="<?php echo $cheader ?>"><?php echo __("There was an error fetching comments, please reload the page."); ?></div>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 12px"><br>
                <?
                    print_commentbox($category,$sortingid,"0",$styleset,$showheadernat,"0","false","native",$comfilter);
                ?>
            </td>
        <tr>
    </table>
</div>

<?
// Output of EN comments:

if ($language != "en_US" AND $category != "1") {

$lowband = $numcoms-3;
if ($encompusher > $lowband) {
    $firstpull = $encompusher+3;
    }
else {
    $firstpull = $numcoms;
}
if ($firstpull > $encommentsnum) {
    $firstpull = $encommentsnum;
}

if ($firstpull > $numcoms) {
    $startoffset = $firstpull-$numcoms+$eninvalidcount;
}
else {
    $startoffset = $eninvalidcount;
}



$remcoms = $encommentsnum-$firstpull;
if ($remcoms <= $numcoms) {
    $addcomstext = "Load last ".$remcoms." comments";
}
else {
    $addcomstext = "Load ".$numcoms." more comments";
}
?>
<div id="coms_en" style="display:none">
    <div style="display:none" id="offset_<?php echo $sortingid ?>_en"><?php echo $startoffset ?></div>
    <div style="display:none" id="numcoms_<?php echo $sortingid ?>_en"><?php echo $numcoms ?></div>
    <div style="display:none" id="allcoms_<?php echo $sortingid ?>_en"><?php echo $encommentsnum ?></div>
    <div style="display:none" id="remcoms_<?php echo $sortingid ?>_en"><?php echo $remcoms ?></div>

    <table id="com_table_<?php echo $sortingid ?>_en" width="<?php echo $ctablewidth ?>" cellpadding="0" cellspacing="0">
        <?
            print_comments($category,$sortingid,$styleset,"0",$firstpull,"en_US","en");
        ?>
        <tr>
            <td style="padding-left:75px"></td><td colspan="2" width="100%"></td>
        </tr>
    </table>

    <table id="com_footer_<?php echo $sortingid ?>_en" width="<?php echo $ctablewidth ?>" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <center>
                    <a class="nostyle" <?php if ($encommentsnum <= $firstpull) { echo 'style="display:none"'; } ?> id="addcomsb_<?php echo $sortingid ?>_en" onclick="load_more_coms('<?php echo $sortingid ?>','en','<?php echo $category ?>','<?php echo $sortingid ?>','<?php echo $styleset ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $visitorid ?>','<?php echo $numcoms ?>','<?php echo $language ?>','<?php echo $comeditdeadline ?>','<?php echo $comfilter ?>')">
                    <span id="addcomstext_<?php echo $sortingid ?>_en" class='button'><?php echo $addcomstext ?></span>
                    </a>
                    <div id="errorfetch_<?php echo $sortingid ?>_en" style="display:none">
                        <p class="<?php echo $cheader ?>"><?php echo __("There was an error fetching comments, please reload the page."); ?></div>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 12px"><br>
                <?
                    print_commentbox($category,$sortingid,"0",$styleset,$showheaderen,"0","false","en");
                ?>
            </td>
        <tr>
    </table>
</div>
<?
}
    if ($category != "1" && $tcomnatoren == "en" && $language != "en_US") {                                       // Switching to EN comments display
        echo '<script>';
        echo 'show_en_coms();';
        echo '</script>';
    }
}


// ======================================= OUTPUT COMMENTS ===========================================================================

function print_comments($category,$sortingid,$styleset,$offset,$numcoms,$language,$natoren,$comfilter = "") {

// ===== Globals =====
    global $dbcon;
    global $usericon;
    global $user;
    global $strat;
    global $visitorid;
    global $comeditdeadline;
    global $user_ip_adress;
    global $tcomhighlight;

    $pagesettingsdb = mysqli_query($dbcon, "SELECT * FROM PageSettings LIMIT 1");
    $pagesettings = mysqli_fetch_object($pagesettingsdb);
    $comgolden = $pagesettings->Com_Gold;
    $comgrey = $pagesettings->Com_Grey;
    $comhidden = $pagesettings->Com_Hidden;

    $usersettings = explode("|", $user->Settings);
    $userrights = explode("|", $user->Rights);

    if ($category == "2" && !$strat) {
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $sortingid")or die("None");
		$strat = mysqli_fetch_object($stratdb);             
    }
// ===== Settings =====

if (!$comfilter) {
    if ($user){
        $comfilter = $usersettings[0];
    }
    else {
        $comfilter = $_SESSION["comfilter"];
    }
    if (!$comfilter) {
        $comfilter = "0";
    }
}
// ===== Definitions =====

switch ($styleset) {
    case "dark":
        $ctableline = "commentsdark";  // Comment lines in table
        $cheader ="comheaddark"; // Header line with name and date
        $ccontent = "comcontentdark"; // Text of comment
        $cbutrespond = "comresponddark"; // Respond button
        $votecolorgrey = "vote_grey";
        $votecolorgreen = "vote_green";
        $votecolorred = "vote_red";
        $votecolorgold = "vote_gold";
        $role_0 = "com_role_0_medium";
        $role_50 = "com_role_50_medium";
        $role_99 = "com_role_99_medium";
        $role_gold = "com_role_gold_medium";
        $role_grey = "com_role_0_medium";
        $role_OP = "com_role_OP";
        $vote_opacity_grey = 'style="opacity: 0.35; filter: alpha(opacity = 35);"';
        $reporticon = "https://www.wow-petguide.com/images/icon_report_dark.png";
        $deleteicon = "https://www.wow-petguide.com/images/icon_x_dark.png";
        $editicon = "https://www.wow-petguide.com/images/icon_pen_dark.png";
        $linkicon = "https://www.wow-petguide.com/images/icon_link_dark.png";
        $iconvotedown = "https://www.wow-petguide.com/images/icon_vote_down_grey.png";
        $iconvoteup = "https://www.wow-petguide.com/images/icon_vote_up_grey.png";
        $iconvotered = "https://www.wow-petguide.com/images/icon_vote_down_red.png";
        $iconvotegreen = "https://www.wow-petguide.com/images/icon_vote_up_green.png";
        $comhighlight = "fadedark";
        $linkstyles = "bright";
     break;
    case "medium":
        $ctableline = "commentsmedium";  // Comment lines in table
        $cheader ="comheaddark"; // Header line with name and date
        $ccontent = "comcontentdark"; // Text of comment
        $cbutrespond = "comrespondmedium"; // Respond button
        $votecolorgrey = "vote_grey";
        $votecolorgreen = "vote_green";
        $votecolorred = "vote_red";
        $votecolorgold = "vote_gold";
        $role_0 = "com_role_0_medium";
        $role_50 = "com_role_50_medium";
        $role_99 = "com_role_99_medium";
        $role_gold = "com_role_gold_medium";
        $role_grey = "com_role_0_medium";
        $role_OP = "com_role_OP";
        $vote_opacity_grey = 'style="opacity: 0.4; filter: alpha(opacity = 40);"';
        $reporticon = "https://www.wow-petguide.com/images/icon_report_medium.png";
        $deleteicon = "https://www.wow-petguide.com/images/icon_x.png";
        $editicon = "https://www.wow-petguide.com/images/icon_pen.png";
        $linkicon = "https://www.wow-petguide.com/images/icon_link.png";
        $iconvotedown = "https://www.wow-petguide.com/images/icon_vote_down_grey.png";
        $iconvoteup = "https://www.wow-petguide.com/images/icon_vote_up_grey.png";
        $iconvotered = "https://www.wow-petguide.com/images/icon_vote_down_red.png";
        $iconvotegreen = "https://www.wow-petguide.com/images/icon_vote_up_green.png";
        $comhighlight = "fademedium";
        $linkstyles = "bright";
     break;
    case "bright":
        $ctableline = "commentsbright";  // Comment lines in table
        $cheader ="comheadbright"; // Header line with name and date
        $ccontent = "comcontentbright"; // Text of comment
        $cbutrespond = "comrespondbright"; // Respond button
        $votecolorgrey = "vote_grey_bright";
        $votecolorgreen = "vote_green_bright";
        $votecolorred = "vote_red_bright";
        $votecolorgold = "vote_gold_bright";
        $role_0 = "com_role_0_bright";
        $role_50 = "com_role_50_bright";
        $role_99 = "com_role_99_bright";
        $role_gold = "com_role_gold_bright";
        $role_grey = "com_role_0_bright";
        $role_OP = "com_role_OP";
        $vote_opacity_grey = 'style="opacity: 0.4; filter: alpha(opacity = 40);"';
        $reporticon = "https://www.wow-petguide.com/images/icon_report_bright.png";
        $deleteicon = "https://www.wow-petguide.com/images/icon_x_bright.png";
        $editicon = "https://www.wow-petguide.com/images/icon_pen_bright.png";
        $linkicon = "https://www.wow-petguide.com/images/icon_link_bright.png";
        $iconvotedown = "https://www.wow-petguide.com/images/icon_vote_down_bright.png";
        $iconvoteup = "https://www.wow-petguide.com/images/icon_vote_up_bright.png";
        $iconvotered = "https://www.wow-petguide.com/images/icon_vote_down_red.png";
        $iconvotegreen = "https://www.wow-petguide.com/images/icon_vote_up_green_bright.png";
        $comhighlight = "fadebright";
        $linkstyles = "dark";
     break;
}



// ===== DB Request =====

switch ($comfilter) {
    case "0":
        if ($category == "1") {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Date DESC LIMIT $offset,18446744073709551615");
        }
        else {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Date DESC LIMIT $offset,18446744073709551615");
        }
        break;
    case "1":
        if ($category == "1") {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Date LIMIT $offset,18446744073709551615");
        }
        else {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Date LIMIT $offset,18446744073709551615");
        }
        break;
    case "2":
        if ($category == "1") {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Votes DESC, Date DESC LIMIT $offset,18446744073709551615");
        }
        else {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Votes DESC, Date DESC LIMIT $offset,18446744073709551615");
        }
        break;
    case "3":
        if ($category == "1") {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' ORDER BY Votes ASC, Date DESC LIMIT $offset,18446744073709551615");
        }
        else {
            $maincomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Category = '$category' AND SortingID = '$sortingid' AND Parent = '0' AND Deleted = '0' AND language = '$language' ORDER BY Votes ASC, Date DESC LIMIT $offset,18446744073709551615");
        }
        break;
}

$countmaincomments = "0";
$countcomments = "0";
$dbmaincounter = "0";
$invalidcounter = "0";
$userreport = null;
$comments = null;

while ($dbmaincounter < mysqli_num_rows($maincomments) && $countmaincomments < $numcoms) {

    $maincomment = mysqli_fetch_object($maincomments);
    $addmaincomment = "go";

    // ===== Preparation of Main Comment Data =====

    $datum = explode(" ", $maincomment->Date);
    $datum = explode("-", $datum[0]);
    if ($language != "en_US") {
        $datum = $datum[2].".".$datum[1].".".$datum[0];
    }
    else {
        $datum = $datum[1]."/".$datum[2]."/".$datum[0];
    }

    $commenttext = stripslashes($maincomment->Comment);
    $commenttext = htmlentities($commenttext, ENT_QUOTES, "UTF-8");
    $commentedit = $commenttext;
    $commenttext = AutoLinkUrls($commenttext,'1',$linkstyles);
    $commenttext = str_replace("[u]", "<u>", $commenttext);
    $commenttext = str_replace("[/u]", "</u>", $commenttext);
    $commenttext = str_replace("[i]", "<i>", $commenttext);
    $commenttext = str_replace("[/i]", "</i>", $commenttext);
    $commenttext = str_replace("[b]", "<b>", $commenttext);
    $commenttext = str_replace("[/b]", "</b>", $commenttext);
    $commenttext = preg_replace("/\n/s", "<br>", $commenttext);

    $mainuser = "";
    $mainuserrole = "";
    $mainusername = "";

    $mainuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$maincomment->User' LIMIT 1");
    if (mysqli_num_rows($mainuserdb) > "0") {
        $mainuser = mysqli_fetch_object($mainuserdb);
        $mainusername = $mainuser->Name;
        $mainuserrole = $mainuser->Role;

        if ($mainuser->UseWowAvatar == "0"){
            $mainusericon = 'src="https://www.wow-petguide.com/images/pets/'.$mainuser->Icon.'.png"';
        }
        else if ($mainuser->UseWowAvatar == "1"){
            $mainusericon = 'src="https://www.wow-petguide.com/images/userpics/'.$mainuser->id.'.jpg"';
        }
    }
    else {
        $mainusername = $maincomment->Name;
        $mainusericon = 'src="https://www.wow-petguide.com/images/pets/'.$maincomment->Icon.'.png"';
    }

    if ($userrights[0] != "1") {                                                           // Only check for votes and reports against it if the user does not have the admin right "see all comments"

        if ($maincomment->Votes < $comhidden AND $mainuserrole < 50 AND $maincomment->User != $user->id) {        // Only add comment if it has enough votes OR is from an admin or moderator OR is from the logged in user
            $addmaincomment = "error";
        }

        if ($mainuserrole < 50) {
            $inappreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '0' AND SortingID = '$maincomment->id'");
            $spamreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '1' AND SortingID = '$maincomment->id'");
            $combinedthresh = mysqli_num_rows($spamreportdb)+mysqli_num_rows($inappreportdb);
            if (mysqli_num_rows($inappreportdb) >= $pagesettings->Com_ReportInappThresh OR mysqli_num_rows($spamreportdb) >= $pagesettings->Com_ReportSpamThresh OR $combinedthresh >= $pagesettings->Com_ReportInappThresh) {
                $addmaincomment = "error";                                                        // Only add comment if it doesn't have too many reports against it
            }
        }

        $userreport = "";
        if ($user) {
            $userreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND User = '$user->id' AND SortingID = '$maincomment->id' LIMIT 1");
            if (mysqli_num_rows($userreportdb) > "0") {
                $userreport = mysqli_fetch_object($userreportdb);
                if (($userreport->Type == "0" OR $userreport->Type == "1") && $mainuserrole < 50) {
                    $addmaincomment = "error";                                                // This user made a report for spam or inappropriate ==> do not load comment
                }
            }
        }

    }

    // Input of Main Comment data into comments array
    if ($addmaincomment == "go") {

    $comments[$countcomments]['id'] = $maincomment->id;
    $comments[$countcomments]['edited'] = $maincomment->Edited;
    $comments[$countcomments]['type'] = "main";
    $comments[$countcomments]['date'] = $datum;
    $comments[$countcomments]['user'] = $maincomment->User;
    $comments[$countcomments]['name'] = $mainusername;
    $comments[$countcomments]['icon'] = $mainusericon;
    $comments[$countcomments]['content'] = $commenttext;
    $comments[$countcomments]['contentedit'] = $commentedit;
    $comments[$countcomments]['parent'] = $maincomment->id;
    $comments[$countcomments]['rolecolor'] = $role_0;
    $comments[$countcomments]['role'] = $mainuserrole;
    $comments[$countcomments]['showrespondfield'] = false;
    $comments[$countcomments]['voteopacity'] = '';
    $comments[$countcomments]['vote'] = '';
    $comments[$countcomments]['editable'] = false;
    $comments[$countcomments]['deletable'] = false;
    $comments[$countcomments]['reportable'] = false;
    $comments[$countcomments]['opcomment'] = false;

    if ($userreport && $userreport->Type == "2") {
        $comments[$countcomments]['reportable'] = "didother";
    }
    if ($maincomment->User == $user->id OR !$user){
        $comments[$countcomments]['reportable'] = "false";
    }


    $comments[$countcomments]['votes'] = $maincomment->Votes;
    if ($maincomment->Votes == "0") {
        $comments[$countcomments]['votecolor'] = $votecolorgrey;
    }
    if ($maincomment->Votes < "0") {
        $comments[$countcomments]['votecolor'] = $votecolorred;
    }
    if ($maincomment->Votes > "0") {
        $comments[$countcomments]['votecolor'] = $votecolorgreen;
    }
    if ($maincomment->Votes > $comgolden) {
        $comments[$countcomments]['votecolor'] = $votecolorgold;
        $comments[$countcomments]['rolecolor'] = $role_gold;                                // Setting gold color for font. will be overwritten if user role is admin or moderator or strategy creator
    }
    if ($strat && $strat->User == $maincomment->User && $strat->User != "0") {
        $comments[$countcomments]['opcomment'] = "true";
        $comments[$countcomments]['rolecolor'] = $role_OP;                                // Setting OP color for comment since the comment writer is the OP of the strategy
    }    
    if ($maincomment->Votes <= $comgrey && $mainuserrole < 50) {
        $comments[$countcomments]['rolecolor'] = $role_grey;
        $comments[$countcomments]['voteopacity'] = $vote_opacity_grey;
    }

    if ($mainuserrole >= 50 && $mainuserrole <= 59) {
        $comments[$countcomments]['rolecolor'] = $role_50;
    }
    if ($mainuserrole == "99") {
    $comments[$countcomments]['rolecolor'] = $role_99;
    }

    if ($user) {
        $votedb = mysqli_query($dbcon, "SELECT Vote FROM Votes WHERE User = '$user->id' AND SortingID = '$maincomment->id' LIMIT 1");
        if (mysqli_num_rows($votedb) > "0"){
            $vote = mysqli_fetch_object($votedb);
            $comments[$countcomments]['vote'] = $vote->Vote;
        }
    }

    $timediff = strtotime(date("Y-m-d H:i:s")) - strtotime($maincomment->Date);

    if (($timediff < $comeditdeadline && $maincomment->IP == $user_ip_adress) OR ($timediff < $comeditdeadline && $maincomment->User == $user->id) OR $userrights[1] == "1"){
        $comments[$countcomments]['editable'] = "true";
    }

    $subcomments = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Parent = '$maincomment->id' AND Deleted = '0' ORDER BY Date ASC");

    if ((mysqli_num_rows($subcomments) < "1" && $timediff < $comeditdeadline && $maincomment->IP == $user_ip_adress) OR (mysqli_num_rows($subcomments) < "1" && $timediff < $comeditdeadline && $maincomment->User == $user->id) OR (mysqli_num_rows($subcomments) < "1" && $userrights[2] == "1"))  {
        $comments[$countcomments]['deletable'] = "true";
    }

    $respondfieldchecker = $countcomments;
    $hasrealsubs = "false";

    $countcomments++;


    // Gather subcomments:

    if (mysqli_num_rows($subcomments) > "0") {
        $countsubcomments = "0";
        while ($countsubcomments  < mysqli_num_rows($subcomments)) {

            $subcomment = mysqli_fetch_object($subcomments);

            $addsubcomment = "go";
            // ===== Preparation of Sub Comment Data =====

            $datum = explode(" ", $subcomment->Date);
            $datum = explode("-", $datum[0]);
            if ($language != "en_US") {
                $datum = $datum[2].".".$datum[1].".".$datum[0];
            }
            else {
                $datum = $datum[1]."/".$datum[2]."/".$datum[0];
            }

            $commenttext = stripslashes($subcomment->Comment);
            $commenttext = htmlentities($commenttext, ENT_QUOTES, "UTF-8");
            $commentedit = $commenttext;
            $commenttext = AutoLinkUrls($commenttext,'1',$linkstyles);
            $commenttext = str_replace("[u]", "<u>", $commenttext);
            $commenttext = str_replace("[/u]", "</u>", $commenttext);
            $commenttext = str_replace("[i]", "<i>", $commenttext);
            $commenttext = str_replace("[/i]", "</i>", $commenttext);
            $commenttext = str_replace("[b]", "<b>", $commenttext);
            $commenttext = str_replace("[/b]", "</b>", $commenttext);
            $commenttext = preg_replace("/\n/s", "<br>", $commenttext);

            $subuser = "";
            $subuserrole = "";
            $subusername = "";

            $subuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$subcomment->User' LIMIT 1");
            if (mysqli_num_rows($subuserdb) > "0") {
                $subuser = mysqli_fetch_object($subuserdb);
                $subusername = $subuser->Name;
                $subuserrole = $subuser->Role;

                if ($subuser->UseWowAvatar == "0"){
                    $subusericon = 'src="https://www.wow-petguide.com/images/pets/'.$subuser->Icon.'.png"';
                }
                else if ($subuser->UseWowAvatar == "1"){
                    $subusericon = 'src="https://www.wow-petguide.com/images/userpics/'.$subuser->id.'.jpg"';
                }
            }
            else {
                $subusername = $subcomment->Name;
                $subusericon = 'src="https://www.wow-petguide.com/images/pets/'.$subcomment->Icon.'.png"';
            }

            if ($userrights[0] != "1") {                                                           // Only check for votes and reports against it if the user does not have the admin right "see all comments"

                if ($subcomment->Votes < $comhidden AND $subuserrole < 50 AND $subcomment->User != $user->id) {        // Only add comment if it has enough votes OR is from an admin or moderator OR is from the logged in user
                    $addsubcomment = "error";
                }

                if ($subuserrole < 50) {
                    $inappreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '0' AND SortingID = '$subcomment->id'");
                    $spamreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND Type = '1' AND SortingID = '$subcomment->id'");
                    $combinedthresh = mysqli_num_rows($spamreportdb)+mysqli_num_rows($inappreportdb);
                    if (mysqli_num_rows($inappreportdb) >= $pagesettings->Com_ReportInappThresh OR mysqli_num_rows($spamreportdb) >= $pagesettings->Com_ReportSpamThresh OR $combinedthresh >= $pagesettings->Com_ReportInappThresh) {
                        $addsubcomment = "error";                                                        // Only add comment if it doesn't have too many reports against it
                    }

                }

                $userreport = "";
                if ($user) {
                    $userreportdb = mysqli_query($dbcon, "SELECT * FROM Reports WHERE Category = '0' AND User = '$user->id' AND SortingID = '$subcomment->id' LIMIT 1");
                    if (mysqli_num_rows($userreportdb) > "0") {
                        $userreport = mysqli_fetch_object($userreportdb);
                        if (($userreport->Type == "0" OR $userreport->Type == "1") && $subuserrole < 50) {
                            $addsubcomment = "error";                                                // This user made a report for spam or inappropriate ==> do not load comment
                        }
                    }
                }

            }

            // Input of Sub Comment data into comments array
            if ($addsubcomment == "go") {
                $hasrealsubs = "true";                                                    // Setting marker to change the corresponding main comment later on again - no respond field

                $comments[$countcomments]['id'] = $subcomment->id;
                $comments[$countcomments]['edited'] = $subcomment->Edited;
                $comments[$countcomments]['type'] = "sub";
                $comments[$countcomments]['date'] = $datum;
                $comments[$countcomments]['user'] = $subcomment->User;
                $comments[$countcomments]['name'] = $subusername;
                $comments[$countcomments]['icon'] = $subusericon;
                $comments[$countcomments]['content'] = $commenttext;
                $comments[$countcomments]['contentedit'] = $commentedit;
                $comments[$countcomments]['parent'] = $maincomment->id;
                $comments[$countcomments]['rolecolor'] = $role_0;
                $comments[$countcomments]['role'] = $subuserrole;
                $comments[$countcomments]['showrespondfield'] = false;
                $comments[$countcomments]['voteopacity'] = '';
                $comments[$countcomments]['vote'] = '';
                $comments[$countcomments]['editable'] = false;
                $comments[$countcomments]['deletable'] = false;
                $comments[$countcomments]['reportable'] = false;
                $comments[$countcomments]['opcomment'] = false;

                if ($userreport && $userreport->Type == "2") {
                    $comments[$countcomments]['reportable'] = "didother";
                }
                if ($subcomment->User == $user->id OR !$user){
                    $comments[$countcomments]['reportable'] = "false";
                }

                $comments[$countcomments]['votes'] = $subcomment->Votes;
                if ($subcomment->Votes == "0") {
                    $comments[$countcomments]['votecolor'] = $votecolorgrey;
                }
                if ($subcomment->Votes < "0") {
                    $comments[$countcomments]['votecolor'] = $votecolorred;
                }
                if ($subcomment->Votes > "0") {
                    $comments[$countcomments]['votecolor'] = $votecolorgreen;
                }
                if ($subcomment->Votes > $comgolden) {
                    $comments[$countcomments]['votecolor'] = $votecolorgold;
                    $comments[$countcomments]['rolecolor'] = $role_gold;                                // Setting gold color for font. will be overwritten if user role is admidn or moderator
                }
                if ($strat && $strat->User == $subcomment->User && $strat->User != "0") {
                    $comments[$countcomments]['opcomment'] = "true";
                    $comments[$countcomments]['rolecolor'] = $role_OP;                                // Setting OP color for comment since the comment writer is the OP of the strategy
                }   
                if ($subcomment->Votes <= $comgrey) {
                    $comments[$countcomments]['votecolor'] = $votecolorgrey;
                    $comments[$countcomments]['rolecolor'] = $role_grey;
                    $comments[$countcomments]['voteopacity'] = $vote_opacity_grey;
                }

                if ($subuserrole >= 50 && $subuserrole <= 59) {
                    $comments[$countcomments]['rolecolor'] = $role_50;
                }
                if ($subuserrole == "99") {
                $comments[$countcomments]['rolecolor'] = $role_99;
                }

                if ($user) {
                    $votedb = mysqli_query($dbcon, "SELECT * FROM Votes WHERE User = '$user->id' AND SortingID = '$subcomment->id' LIMIT 1");
                    if (mysqli_num_rows($votedb) > "0"){
                        $vote = mysqli_fetch_object($votedb);
                        $comments[$countcomments]['vote'] = $vote->Vote;
                    }
                }

                $timediff = strtotime(date("Y-m-d H:i:s")) - strtotime($subcomment->Date);

                if (($timediff < $comeditdeadline && $subcomment->IP == $user_ip_adress) OR ($timediff < $comeditdeadline && $subcomment->User == $user->id)){
                    $comments[$countcomments]['editable'] = "true";
                    $comments[$countcomments]['deletable'] = "true";
                }
                if ($userrights[1] == "1"){
                    $comments[$countcomments]['editable'] = "true";
                }
                if ($userrights[2] == "1"){
                    $comments[$countcomments]['deletable'] = "true";
                }

                $lastsubcomment = $countcomments;                                             // Set marker for last sub comment - will always be overwritten for every fetched subcomment
                $onegoodsub = "true";
                $countcomments++;
            }
            $countsubcomments++;
        }
        if ($onegoodsub == "true") {
            $comments[$lastsubcomment]['showrespondfield'] = "true";                            // Use last set marker to mark this as the last sub and therefore a response option should be shown
        }
        $onegoodsub = "";
        $lastsubcomment = "";
    }

if ($hasrealsubs == "false") {
    $comments[$respondfieldchecker]['showrespondfield'] = "true";
}

$countmaincomments++;
}
else {
    if ($countmaincomments < $numcoms) {
        $invalidcounter++;
    }
}
$dbmaincounter++;
}

    // ===== Output of Comments =====
if ($comments) {
    foreach($comments as $key => $value) {

        if ($value['type'] == "sub") {
            echo '<tr id="'.$value['id'].'" class="'.$ctableline.'"><td style="padding-left:75px"></td><td colspan="2" width="100%">';
        }
        else {
            echo '<tr id="'.$value['id'].'" class="'.$ctableline.'"><td colspan="3" width="100%">';
        }
        ?>

        <div class="anchorCM_<?php echo $value['id'] ?>" id="CM_<?php echo $value['id'] ?>" data-value="<?php echo $value['role'] ?>"> </div>
        <table <?php if ($value['id'] == $tcomhighlight) { echo 'class="'.$comhighlight.'"'; } ?> style="width:100%">
            <tr <?php echo $value['voteopacity'] ?>>
                <td rowspan="2" valign="top" align="right">
                    <table cellpadding="0" cellspacing="0">
                        <tr><td style="padding-left:30px;"></td></tr>
                        <tr>
                            <td align="right">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-top:5px"><center>
                                            <?php if ($user) {
                                            if ($value['vote'] == "1") $uppic = $iconvotegreen;
                                            else $uppic = $iconvoteup;
                                            ?>
                                            <a class="votebutton" style="display:block" onclick="com_vote('1','<?php echo $value['id'] ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $votecolorgrey ?>','<?php echo $votecolorgreen ?>','<?php echo $votecolorgold ?>','<?php echo $votecolorred ?>','<?php echo $comgolden ?>','<?php echo $styleset ?>')">
                                                <img id="upvpic_<?php echo $value['id'] ?>" src="<?php echo $uppic ?>" />
                                                <img src="<?php echo $iconvotegreen ?>" />
                                            </a>
                                            <?php } else { ?>
                                            <a class="basictooltip">
                                                <img src="<?php echo $iconvoteup ?>">
                                                <span class="custom">
                                                    <b><?php echo __("You must be logged in to vote on comments"); ?></b>
                                                </span>
                                            </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right" style="padding-top:2px" ><center>
                                            <p id="vote_display_c_<?php echo $value['id'] ?>" class="<?php echo $value['votecolor'] ?>"><span id="vote_display_<?php echo $value['id'] ?>"><?php echo $value['votes'] ?></span></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top:2px"><center>
                                            <?php if ($user) {
                                            if ($value['vote'] == "0") $downpic = $iconvotered;
                                            else $downpic = $iconvotedown;
                                            ?>
                                            <a class="votebutton" style="display:block" onclick="com_vote('0','<?php echo $value['id'] ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $votecolorgrey ?>','<?php echo $votecolorgreen ?>','<?php echo $votecolorgold ?>','<?php echo $votecolorred ?>','<?php echo $comgolden ?>','<?php echo $styleset ?>')">
                                                <img id="downvpic_<?php echo $value['id'] ?>" src="<?php echo $downpic ?>" />
                                                <img src="<?php echo $iconvotered ?>" />
                                            </a>
                                            <?php } else { ?>
                                            <a class="basictooltip">
                                                <img src="<?php echo $iconvotedown ?>">
                                                <span class="custom">
                                                    <b><?php echo __("You must be logged in to vote on comments"); ?> </b>
                                                </span>
                                            </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>

                <td rowspan="2" valign="top">
                    <?php if ($value['user'] != "0") { ?>
                        <span class="username" rel="<?php echo $value['user'] ?>" value="<?php echo $user->id ?>">
                    <?php } ?>
                    <img <?php echo $value['icon'] ?> width="50" height="50" class="commentpic">
                    <?php if ($value['user'] != "0") { ?>
                        </span>
                    <?php } ?>
                </td>

                <td width="100%" align="left" style="padding-left: 7px">
                    <?php if ($value['user'] == "0") { ?>
                        <p class="<?php echo $cheader." ".$value['rolecolor'] ?>"><?php echo $value['name']." ".__("wrote on")." ".$value['date']; ?>
                    <?php }
                    else { ?>
                        <p class="<?php echo $cheader." ".$value['rolecolor'] ?>"><?php if ($value['opcomment'] == "true") { echo "(OP) "; } ?> <span class="username" style="text-decoration: none" rel="<?php echo $value['user'] ?>" value="<?php echo $user->id ?>"><a target="_blank" href="?user=<?php echo $value['user'] ?>" class="usernamelink <?php echo $cheader." ".$value['rolecolor'] ?>"><?php echo $value['name'] ?></a></span><p class="<?php echo $cheader." ".$value['rolecolor'] ?>"><?php echo " ".__("wrote on")." ".$value['date']; ?>
                    <?php } ?>
                </td>
                
                
                <td align="right" <?php if ($value['editable'] != "true") { ?>colspan="3"<?php } ?>>
                    <a id="linkicon_<?php echo $value['id'] ?>" class="alternativessmall" style="display:block" data-clipboard-text="https://wow-petguide.com/?Comment=<?php echo $value['id'] ?>"><img height="18" src="<?php echo $linkicon ?>" /></a>
                </td>
                <div class="remtt" style="display:none; right: 0px" id="link_copied_<?php echo $value['id'] ?>">Link copied</div>
                <script>
                var btn = document.getElementById('linkicon_<?php echo $value['id'] ?>');
                var clipboard = new Clipboard(btn);
        
                clipboard.on('success', function(e) {
                    console.log(e);
                        $('#link_copied_<?php echo $value['id'] ?>').delay(0).fadeIn(500);
                        $('#link_copied_<?php echo $value['id'] ?>').delay(1200).fadeOut(500);
                    });
                clipboard.on('error', function(e) {
                    console.log(e);
                });
                </script>
                
             
                

                <td align="right">
                    
                    <?php if ($value['editable'] == "true") { ?>
                    <a class="alternativessmall" data-remodal-target="modaledit_<?php echo $value['id'] ?>" style="display:block"><img height="18" src="<?php echo $editicon ?>" /></a>

                   <div class="remodalcomments" data-remodal-id="modaledit_<?php echo $value['id'] ?>">
                        <table width="600" class="profile">
                            <tr class="profile">
                                <th colspan="2" width="5" class="profile">
                                    <table>
                                        <tr>
                                            <td><img src="images/icon_pen.png"></td>
                                            <td><img src="images/blank.png" width="5" height="1"></td>
                                            <td><p class="blogodd"><b>Edit this comment:</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>

                            <tr class="profile">
                                <td class="collectionbordertwo">
                                   <textarea class="cominputbright" id="editcommentfield_<?php echo $value['id'] ?>" style="height: 200px; width: 600px;" onkeyup="auto_adjust_textarea_size(this); count_remaining(this,'<?php echo $value['id'] ?>','<?php echo $sortingid ?>','edit','<?php echo $natoren ?>')" maxlength="3000" required><?php echo $value['contentedit'] ?></textarea>
                                </td>
                            </tr>

                            <tr class="profile">
                                <td class="collectionbordertwo"><center>
                                    <table>
                                        <tr>
                                            <td width="30%"></td>
                                            <td width="20%" style="padding-left: 12px;">
                                                <input data-remodal-action="close" onclick="edit_comment('<?php echo $value['id'] ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>')" type="submit" class="comedit" value="<?php echo __("Save changes"); ?>">
                                            </td>
                                            <td width="20%" style="padding-left: 15px;">
                                                <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                                            </td>
                                            <td width="30%" align="right">
                                                <span style="padding-right: 15px" class="<?php echo $scombbcode ?>" id="com_remaining_<?php echo $value['id']."_".$sortingid ?>_edit_<?php echo $natoren ?>"></span>
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
                     $('[data-remodal-id=modaledit_<?php echo $value['id'] ?>]').remodal(options);
                    </script>

                    <?php } ?>
                </td>

                <td align="left">
                    <?php if ($value['deletable'] == "true") { ?>
                    <a class="alternativessmall" data-remodal-target="modaldelete_<?php echo $value['id'] ?>" style="display:block"><img height="18" src="<?php echo $deleteicon ?>" /></a>

                    <div class="remodalcomments" data-remodal-id="modaldelete_<?php echo $value['id'] ?>">
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
                                                <input data-remodal-action="close" onclick="delete_comment('<?php echo $value['id'] ?>','<?php echo $value['type'] ?>','<?php echo $sortingid ?>','<?php echo $natoren ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $category ?>')" type="submit" class="comdelete" value="<?php echo __("Delete"); ?>">
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
                     $('[data-remodal-id=modaldelete_<?php echo $value['id'] ?>]').remodal(options);
                    </script>
                    <?php } ?>
                </td>
            </tr>


            <tr>
                <td width="100%" colspan="3" rowspan="2" align="left" valign="top" style="padding-left: 7px">
                    <div <?php echo $value['voteopacity'] ?> id="comcontent_<?php echo $value['id'] ?>" class="<?php echo $ccontent." ".$value['rolecolor'] ?>"><?php echo $value['content'];
                    if ($value['edited'] == 1) { echo " <i>(edited)</i>"; } ?></div>
                </td>

                <td align="left" rowspan="2" valign="top" <?php echo $value['voteopacity'] ?>>
                    
                    <?php  // If user already reported this comment, show error message:
                    if ($value['reportable'] == "didother") { ?>
                        <a id="cant_report_<?php echo $value['id'] ?>" class="basictooltip">
                            <img height="18" src="<?php echo $reporticon ?>" />
                            <span class="custom">
                                <b><?php echo __("You already reported this comment."); ?></b>
                            </span>
                        </a>
                    <?php } ?>

                    <?php // If user can report this comment, show report option:
                    if ($value['reportable'] == "") { ?>
                    <a id="report_com_<?php echo $value['id'] ?>" class="alternativessmall" data-remodal-target="modalreport_<?php echo $value['id'] ?>" style="display:block; cursor: pointer"><img height="18" src="<?php echo $reporticon ?>" /></a>

                    <div class="remodalcomments" data-remodal-id="modalreport_<?php echo $value['id'] ?>">
                        <table width="600" class="profile">
                            <tr class="profile">
                                <th colspan="2" width="5" class="profile">
                                  <img src="/images/icon_report.png" style="padding-right: 0.4em;">
                                  <p class="blogodd"><b><?php echo __("Please describe what you want to report this comment for"); ?>:</b></p>
                                </th>
                            </tr>

                            <tr class="profile">
                                <td  class="collectionbordertwo"><center>
                                    <table>
                                        <tr>
                                            <td style="width:1px">
                                                <ul class="radios">
                                                    <li>
                                                        <input class="red" type="radio" id="inappropriate_<?php echo $value['id'] ?>" value="inappropriate" name="reportcom_<?php echo $value['id'] ?>" checked>
                                                        <label for="inappropriate_<?php echo $value['id'] ?>"></label>
                                                        <div class="check"></div>
                                                    </li>
                                                </ul>
                                            </td>
                                            <td style="width:120px">
                                                <p class="blogodd"><span style="white-space: nowrap;"><?php echo __("Inappropriate"); ?></span>
                                            </td>

                                            <td style="width:1px">
                                                <ul class="radios">
                                                    <li>
                                                        <input class="red" type="radio" id="spam_<?php echo $value['id'] ?>" value="spam" name="reportcom_<?php echo $value['id'] ?>">
                                                        <label for="spam_<?php echo $value['id'] ?>"></label>
                                                        <div class="check"></div>
                                                    </li>
                                                </ul>
                                            </td>
                                            <td style="width:120px">
                                                <p class="blogodd"><span style="white-space: nowrap;"><?php echo __("Spam"); ?></span>
                                            </td>

                                            <td style="width:1px">
                                                <ul class="radios">
                                                    <li>
                                                        <input class="lightblue" type="radio" id="other_<?php echo $value['id'] ?>" value="other" name="reportcom_<?php echo $value['id'] ?>">
                                                        <label for="other_<?php echo $value['id'] ?>"></label>
                                                        <div class="check"></div>
                                                    </li>
                                                </ul>
                                            </td>
                                            <td style="width:120px">
                                                <p class="blogodd"><span style="white-space: nowrap;"><?php echo __("Other"); ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>


                            <tr class="profile">
                                <td class="collectionbordertwo">
                                   <textarea placeholder="Add short explanation (optional)" class="cominputbright" id="report_comment_field_<?php echo $value['id'] ?>" style="height: 150px; width: 600px;" onkeyup="auto_adjust_textarea_size(this)"></textarea>
                                </td>
                            </tr>

                            <tr class="profile">
                                <td class="collectionbordertwo" style="text-align:center">
                                    <input data-remodal-action="close" onclick="report_comment('<?php echo $value['id'] ?>','<?php echo $value['type'] ?>','<?php echo $sortingid ?>','<?php echo $natoren ?>','<?php echo $language ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $category ?>')" type="submit" class="comedit" value="<?php echo __("Send Report"); ?>">
                                    <input style="margin-left: 2em" data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <script>
                    var options = {
                        hashTracking: false
                    };
                     $('[data-remodal-id=modalreport_<?php echo $value['id'] ?>]').remodal(options);
                    </script>
                    <?php } ?>
                </td>
            </tr>

            <tr></tr>

            <?php if ($value['showrespondfield'] == "true") { ?>
                <tr>
                    <td colspan="2">
                    </td>
                    <td colspan="3" style="padding-left: 7px">
                        <div <?php echo $value['voteopacity'] ?> class="respondbutton_<?php echo $value['parent'] ?>"><button class="<?php echo $cbutrespond ?>" onclick="show_respond_field('<?php echo $sortingid ?>','<?php echo $value['parent'] ?>','<?php echo $language ?>','<?php echo $user->id ?>','<?php echo $category ?>','<?php echo $visitorid ?>','<?php echo $styleset ?>')">Respond</button></div>
                    </td>
                </tr>
            <?php } ?>

        </table>
        </td>
        </tr>

        <?php if ($value['showrespondfield'] == "true") { ?>
            <tr>
                <td>
                </td>
                <td colspan="3" style="padding-left: 14px">
                    <span id="respondfield_<?php echo $value['parent'] ?>" style="display: none"></span>
                </td>
            </tr>
        <?
        }
    }

    if ($invalidcounter > "0"){
        echo "<script>add_offset('".$sortingid."','".$natoren."','".$invalidcounter."');</script>";
    }

}
}




























// ======================================= OUTPUT COMMENT BOX ===========================================================================
// $category:  0 = main, 1 = blog, 2 = strat
// $sortingid: ID of entry
// $parent: ID of parent entry for subcomments. Empty for new entry
// $styleset: Color theme, dark, medium, bright
// $user: optional
// $header: switch for title. 0 = no header. 1 = "New Comment". 2 = "Be the first to write a comment:"
// $visitorid: required if the function is used from AJAX. Otherwise optional because it can be grabbed through Globals

function print_commentbox($category,$sortingid,$parent,$styleset,$header = "0",$userid = "0",$visitorid = "false",$natoren) {

// ===== Globals =====
    $dbcon = $GLOBALS['dbcon'];
    $language = $GLOBALS['language'];
    $tlanguage = $GLOBALS['tcomlanguage'];
    $ads_active = $GLOBALS['ads_active'];
    if ($tlanguage != "") {
        $language = $tlanguage;
    }
    if ($natoren == "en") {
        $language = "en_US";
    }
    $usericon = $GLOBALS['usericon'];
    $user = $GLOBALS['user'];
    if ($visitorid == "false"){
        $visitorid = $GLOBALS['visitorid'];
    }
    if (!$user AND $userid != "0") {
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid' LIMIT 1");
        if (mysqli_num_rows($userdb) > "0") {
            $user = mysqli_fetch_object($userdb);
            if ($user->UseWowAvatar == "0"){
                $usericon = 'src="https://www.wow-petguide.com/images/pets/'.$user->Icon.'.png"';
            }
            else if ($user->UseWowAvatar == "1"){
                $usericon = 'src="https://www.wow-petguide.com/images/userpics/'.$user->id.'.jpg" alt="'.$user->IconUpdate.'"';
            }
        }
    }
    
// ===== Definitions =====

switch ($styleset) {
    case "dark":
        $scomheader = "comheaddark";  // Comment Title Text
        $serrortext = "commenterror";  // Error messages
        $scominput = "cominputdark";  // Input Fields
        $scombbcode = "commbbcdark";  // Formatting Codes Text
        $scomsubinactive = "cominputdarkinac";  // Submit button deactivated
        $scomsubactive = "cominputdark";  // Submit button active
     break;
    case "medium":
        $scomheader = "comheaddark";  // Comment Title Text
        $serrortext = "commenterror";  // Error messages
        $scominput = "cominputmedium";  // Input Fields
        $scombbcode = "commbbcdark";  // Formatting Codes Text
        $scomsubinactive = "cominputmediuminac";  // Submit button deactivated
        $scomsubactive = "cominputmedium";  // Submit button active
     break;
    case "bright":
        $scomheader = "comheadbright";  // Comment Title Text
        $serrortext = "commenterror";  // Error messages
        $scominput = "cominputbright";  // Input Fields
        $scombbcode = "commbbcbright";  // Formatting Codes Text
        $scomsubinactive = "cominputmediuminac";  // Submit button deactivated
        $scomsubactive = "cominputmedium";  // Submit button active
     break;
}


// ===== JS for unregistered visitors =====
if (!$user) {
?>
<script type="text/javascript">
$(document).ready(function() {
        var x_timer;
        $("#username_<?php echo $parent.'_'.$sortingid.'_'.$natoren ?>").keyup(function (e){
                clearTimeout(x_timer);
                var user_name = $(this).val();
                x_timer = setTimeout(function(){
                        check_username_ajax(user_name,'<?php echo $natoren ?>','<?php echo $parent ?>','<?php echo $sortingid ?>','<?php echo $scomsubactive ?>','<?php echo $scomsubinactive ?>','<?php echo $serrortext ?>');
                }, 1000);
        });
});
</script>
<?
}


// ===== Table Print =====

switch ($category) {
    case "0":
        $linkforward = "index.php?m=".$sortingid;
        break;
    case "1":
        $linkforward = "index.php?News=".$sortingid;
        break;
    case "2":
        $linkforward = "index.php?Strategy=".$sortingid;
        break;
}
?>
<form action="<?php echo $linkforward ?>" method="post">
<input type="hidden" name="comcat" value="<?php echo $category ?>">
<input type="hidden" name="comsortid" value="<?php echo $sortingid ?>">
<input type="hidden" name="comparent" value="<?php echo $parent ?>">
<input type="hidden" name="visitorid" value="<?php echo $visitorid ?>">
<input type="hidden" name="comlanguage" value="<?php echo $language ?>">
<input type="hidden" name="comuserid" value="<?php echo $user->id ?>">
<input type="hidden" name="submitcomment" value="true">
<input type="hidden" name="email" value="verifymail">
<input type="hidden" name="e-mail" value="requiredfield">
<input type="hidden" name="mail" value="checkverification">

<table style="padding-left: 20px;">
    <?
    if ($header != "0") { ?>
    <tr>
        <td></td>
        <td colspan="2" style="padding-left: 7px;">
            <p class="<?php echo $scomheader ?>"><?php if ($header == "1") { echo __("New Comment:"); } else if ($header == "2") { echo __("Be the first to leave a comment:"); } ?>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <td rowspan="4" valign="top">
            <img <?php if ($user) { echo $usericon; } else { echo 'src="images/pets/level.png"'; } ?> width="50" height="50" class="commentpic">
        </td>

        <?php if (!$user) { ?>
        <td valign="top" style="padding-left: 7px;">
            <input class="<?php echo $scominput ?>" name="comname" onkeyup="input_typing(this,'<?php echo $natoren ?>','<?php echo $parent ?>','<?php echo $sortingid ?>','<?php echo $scomsubactive ?>','<?php echo $scomsubinactive ?>','<?php echo $serrortext ?>')" type="text" id="username_<?php echo $parent."_".$sortingid."_".$natoren ?>" placeholder="<?php echo __("Your Name:"); ?>" maxlength="15" required>
        </td>
        <td valign="top" style="padding-left: 15px;">
            <input class="<?php echo $scominput ?>" placeholder="<?php echo __("Email (optional, will not be published)"); ?>" type="text" maxlength="200" name="commail" size="35">
        </td> <?php } ?>
    </tr>

    <?php if (!$user) { ?>
    <tr>
        <td colspan="2" style="padding-left: 7px;">
            <div class="registerError" id="comerror_<?php echo $parent."_".$sortingid."_".$natoren ?>" style="display:none"></div>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <td colspan="2" style="padding-left: 7px;">
            <textarea class="<?php echo $scominput ?>" placeholder="<?php echo __("Write your comment here"); ?>" <?php if ($user) { echo 'style="height: 47px;"'; } else { echo 'onClick="auto_adjust_textarea_size(this)"'; } ?> name="comcontent" maxlength="3000" onkeyup="auto_adjust_textarea_size(this); count_remaining(this,'<?php echo $parent ?>','<?php echo $sortingid ?>','input','<?php echo $natoren ?>')" required></textarea>
        </td>
    </tr>

    <tr>
        <td valign="top" style="padding-left: 7px;">
            <input class="<?php echo $scomsubactive ?>" id="comsubmit_<?php echo $parent."_".$sortingid."_".$natoren ?>" type="submit" value="<?php echo __("Submit"); ?>">
        </td>

        <td valign="top" style="padding-right: 5px;" align="right">
            <span style="padding-right: 15px" class="<?php echo $scombbcode ?>" id="com_remaining_<?php echo $parent."_".$sortingid ?>_input_<?php echo $natoren ?>"></span><p class="<?php echo $scombbcode ?>">[b]<b><?php echo __("bold") ?></b>[/b] - [i]<i><?php echo __("italic") ?></i>[/i] - [u]<u><?php echo __("underline") ?></u>[/u]
        </td>
    </tr>

</table>
</form>
    <?php  // Ad placement Venatus at bottom of comments  ?>
    <br>
    <?php if ($user->id != 2434) {
        if ($ads_active == true) { ?> 
    <div class="vm-placement" data-id="5d790600da2de50943f0ef38"></div><br><br>
    <?php }
    } 
}