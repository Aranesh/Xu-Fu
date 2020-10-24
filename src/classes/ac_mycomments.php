<?php

function get_comments($activenor) {
    $dbcon = $GLOBALS['dbcon'];
    $user = $GLOBALS['user'];
    $pagesettings = $GLOBALS['pagesettings'];
    $exccutoff = "100";

    if ($activenor == "0") {
        $allcomsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND Deleted = '0' AND Category != '3' ORDER BY Date");
    }
    else {
        $allcomsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND Deleted = '0' AND NewActivity = '1' AND Category != '3' ORDER BY Date");
    }
    if (mysqli_num_rows($allcomsdb) > "0"){
        $threads[] = "-";

        $countcoms = "0";
        $realcoms = "0";
        while ($countcoms < mysqli_num_rows($allcomsdb)) {
            $thiscomment = mysqli_fetch_object($allcomsdb);

            if ($thiscomment->Parent == "0") {
                $threadid = $thiscomment->id;
            }
            else {
                $threadid = $thiscomment->Parent;
            }

            if ($activenor == "0" AND $thiscomment->NewActivity == "1") {
                $threads[] = $threadid;
            }

            if (!in_array($threadid, $threads)) {
                $parentdb = mysqli_query($dbcon, "SELECT COUNT(*) FROM Comments WHERE Parent = '$threadid' AND Deleted = '0'");
                $parentdb_count = mysqli_fetch_row ($parentdb)[0];
                $threads[] = $threadid;

                $coms[$realcoms]['id'] = $thiscomment->id;
                $coms[$realcoms]['newactive'] = $thiscomment->NewActivity;
                $coms[$realcoms]['responses'] = $parentdb_count;

                $categorycheck = decode_sortingid($thiscomment->Category,$thiscomment->SortingID);
                $coms[$realcoms]['category'] = $categorycheck[1];

                if (mb_strlen($thiscomment->Comment) > $exccutoff){
                    $shortcomment = preg_replace( "/\r|\n/", " ", $thiscomment->Comment);
                    $shortcomment = wordwrap($shortcomment, $exccutoff, "<br />\n" );
                    $tempshortcom = explode("<br />\n", $shortcomment);
                    $shortcomment = $tempshortcom[0];
                    $shortcomment = stripslashes($shortcomment);
                    $shortcomment = htmlentities($shortcomment, ENT_QUOTES, "UTF-8");
                    $shortcomment = $shortcomment."</b></u> ...";
                    $shortcomment = AutoLinkUrls($shortcomment,'1','dark');
                    $shortcomment = str_replace("[u]", "<u>", $shortcomment);
                    $shortcomment = str_replace("[/u]", "</u>", $shortcomment);
                    $shortcomment = str_replace("[i]", "<i>", $shortcomment);
                    $shortcomment = str_replace("[/i]", "</i>", $shortcomment);
                    $shortcomment = str_replace("[b]", "<b>", $shortcomment);
                    $coms[$realcoms]['excerpt'] = str_replace("[/b]", "</b>", $shortcomment);
                    $coms[$realcoms]['long'] = "true";

                    $commenttext = stripslashes($thiscomment->Comment);
                    $commenttext = htmlentities($commenttext, ENT_QUOTES, "UTF-8");
                    $commenttext = str_replace("[u]", "<u>", $commenttext);
                    $commenttext = str_replace("[/u]", "</u>", $commenttext);
                    $commenttext = str_replace("[i]", "<i>", $commenttext);
                    $commenttext = str_replace("[/i]", "</i>", $commenttext);
                    $commenttext = str_replace("[b]", "<b>", $commenttext);
                    $commenttext = str_replace("[/b]", "</b>", $commenttext);
                    $coms[$realcoms]['fulltext'] = preg_replace("/\n/s", "<br>", $commenttext);
                }
                else {
                    $shortcomment = preg_replace( "/\r|\n/", "", $thiscomment->Comment);
                    $shortcomment = stripslashes($shortcomment);
                    $shortcomment = htmlentities($shortcomment, ENT_QUOTES, "UTF-8");
                    $shortcomment = AutoLinkUrls($shortcomment,'1','dark');
                    $shortcomment = str_replace("[u]", "<u>", $shortcomment);
                    $shortcomment = str_replace("[/u]", "</u>", $shortcomment);
                    $shortcomment = str_replace("[i]", "<i>", $shortcomment);
                    $shortcomment = str_replace("[/i]", "</i>", $shortcomment);
                    $shortcomment = str_replace("[b]", "<b>", $shortcomment);
                    $coms[$realcoms]['excerpt'] = str_replace("[/b]", "</b>", $shortcomment);

                    $coms[$realcoms]['long'] = "false";
                }
                $coms[$realcoms]['votes'] = $thiscomment->Votes;

                if ($thiscomment->Votes >= $pagesettings->Com_Gold) {
                    $coms[$realcoms]['color'] = "com_role_gold_bright";
                }
                if ($thiscomment->Votes <= $pagesettings->Com_Grey) {
                    $coms[$realcoms]['color'] = "com_role_grey";
                }

                $datesplits = explode(" ", $thiscomment->Date);
                $datesplits = explode("-", $datesplits[0]);
                $coms[$realcoms]['year'] = $datesplits[0];
                $coms[$realcoms]['month'] = $datesplits[1];
                $coms[$realcoms]['day'] = $datesplits[2];
                $realcoms++;
            }
            $countcoms++;
        }
    }
    if ($coms) {
        sortBy('date',$coms,'asc');
    }
    return $coms;
}

$comsnew = get_comments('1');
$comsold = get_comments('0');

if ($comsnew && $comsold) {
    $coms = array_merge($comsnew, $comsold);
}
else {
    if ($comsnew) {
        $coms = $comsnew;
    }
    if ($comsold) {
        $coms = $comsold;
    }
}

foreach($coms as $key => $value) {
    if (!$arr_years){
        $arr_years[] = $value['year'];
    }
    if (!$arr_months){
        $arr_months[] = $value['month'];
    }
    if (!$arr_days){
        $arr_days[] = $value['day'];
    }
    if (!in_array($value['year'], $arr_years)) {
        $arr_years[] = $value['year'];
    }
    if (!in_array($value['month'], $arr_months)) {
        $arr_months[] = $value['month'];
    }
    if (!in_array($value['day'], $arr_days)) {
        $arr_days[] = $value['day'];
    }
}

sort($arr_years);
sort($arr_months);
sort($arr_days);

// Mark New comments as read AFTER the coms array has been created

if ($comsnew) {
    mysqli_query($dbcon, "UPDATE Comments SET `NewActivity` = '0' WHERE User = '$user->id'");
    echo '<script>$.growl.notice({ message: "'.__("Some of your comments got replies from other pet battlers!<br>They are highlighted in green. A click on the link brings you to the respective thread.").'", duration: "7000", size: "large", location: "tc" });</script>';
}






// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================





?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <?php echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle"><?php echo __("My Comments") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('mycomments');
    ?>
</div>

<div class="blogentryfirst">
<div class="articlebottom">
</div>

<table width="100%" border="0">
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

        <?
            if (!$coms) {
                ?>
                <div class="msg_nomsgs" id="nomsgsboxout">
                    <?php echo __("You have not written any comments so far. Go forth and tell your tale with pride! :-)") ?>
                </div><br><br>
                <?
            }
            else {
                ?>
            <table width="100%" id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:30 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
            <thead>

                <tr>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric" width="40"></th>
                    <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="150"><p class="table-sortable-black" style="margin-left: 15px;"><?php echo __("Link to thread") ?></p></th>
                    <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="100%"><p class="table-sortable-black" style="margin-left: 15px;"><?php echo __("You wrote") ?></p></th>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><?php echo __("Responses") ?></th>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><?php echo __("Votes") ?></th>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><?php echo __("Year") ?></th>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><?php echo __("Month") ?></th>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><?php echo __("Day") ?></th>
                </tr>

                <tr>
                    <th align="center" class="petlistheadersecond table-sortable:numeric" width="40"><p class="table-sortable-black">#</th>

                    <th align="left" class="petlistheadersecond" width="150">
                            <input class="petselect" name="filter" size="25" id="catfilter" onkeyup="Table.filter(this,this)">
                    </th>

                    <th align="left" class="petlistheadersecond" width="300">
                            <input class="petselect" name="filter" size="25" id="contentfilter" onkeyup="Table.filter(this,this)">
                    </th>

                    <th align="center" class="petlistheadersecond">
                            <select class="petselect" style="width:100px;" id="respfilter" onchange="Table.filter(this,this)">
                                <option class="petselect" value=""><?php echo __("All") ?></option>
                                <option class="petselect" value="0">0</option>
                                <option class="petselect" value="function(val){return parseFloat(val)>0;}">> 0</option>
                                <option class="petselect" value="function(val){return parseFloat(val)>10;}">> 10</option>
                            </select>
                    </th>

                    <th align="center" class="petlistheadersecond">
                            <select class="petselect" id="votefilter" onchange="Table.filter(this,this)">
                                <option class="petselect" value=""><?php echo __("All") ?></option>
                                <option class="petselect" value="1">1</option>
                                <option class="petselect" value="function(val){return parseFloat(val)>1;}">> 1</option>
                                <option class="petselect" value="function(val){return parseFloat(val)>50;}">> 50</option>
                                <option class="petselect" value="function(val){return parseFloat(val)<0;}">< 0</option>
                            </select>
                    </th>

                    <th align="center" class="petlistheadersecond">
                            <select class="petselect" id="yearfilter" onchange="Table.filter(this,this)">
                                    <option class="petselect" value=""><?php echo __("All") ?></option>
                                    <?
                                    foreach ($arr_years as $yearvalue) {
                                        echo '<option class="petselect" value="'.$yearvalue.'">'.$yearvalue.'</option>';
                                    }
                                    ?>
                            </select>
                    </th>

                    <th align="center" class="petlistheadersecond">
                            <select class="petselect" id="monthfilter" onchange="Table.filter(this,this)">
                                    <option class="petselect" value=""><?php echo __("All") ?></option>
                                    <?
                                    foreach ($arr_months as $monthvalue) {
                                        echo '<option class="petselect" value="'.$monthvalue.'">'.$monthvalue.'</option>';
                                    }
                                    ?>
                            </select>
                    </th>
                    <th align="center" class="petlistheadersecond">
                            <select class="petselect" id="dayfilter" onchange="Table.filter(this,this)">
                                    <option class="petselect" value=""><?php echo __("All") ?></option>
                                    <?
                                    foreach ($arr_days as $dayvalue) {
                                        echo '<option class="petselect" value="'.$dayvalue.'">'.$dayvalue.'</option>';
                                    }
                                    ?>
                            </select>
                    </th>

                </tr>
            </thead>
            <tbody>

            <?php
                foreach($coms as $key => $value) {
            ?>

            <tr class="petlist" <?
            if ($coms[$key]['votes'] <= $pagesettings->Com_Hidden) {
                    echo 'style="background-color: #d8a0a0" ';
            }
            if ($coms[$key]['newactive'] == "1") {
                    echo 'style="background-color: #9ae0af" ';
            }
            ?>>
                    <td class="petlist"><center><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $key+1 ?>.</td>

                    <td class="petlist"><a class="comlinkdark" href="?Comment=<?php echo $coms[$key]['id'] ?>" target="_blank"><?php echo $coms[$key]['category'] ?></a></td>

                    <td class="petlist"><?php if ($coms[$key]['long'] == "true") {
                        ?><span class="tt_<?php echo $coms[$key]['id'] ?>" data-tooltip-content="#ttc_<?php echo $coms[$key]['id'] ?>"><p class="blogodd <?php echo $coms[$key]['color'] ?>"><?php echo $coms[$key]['excerpt'] ?></span>
                        <div style="display:none">
                            <span id="ttc_<?php echo $coms[$key]['id'] ?>">
                                <?php echo $coms[$key]['fulltext'] ?>
                            </span>
                        </div>
                        <?
                    }
                    else { ?><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $coms[$key]['excerpt']; } ?></td>

                    <td align="center" class="petlist"><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $coms[$key]['responses'] ?></td>

                    <td align="center" class="petlist" style="padding-left: 12px;"><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $coms[$key]['votes'] ?></td>

                    <td align="center" class="petlist"><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $coms[$key]['year'] ?></td>

                    <td align="center" class="petlist"><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $coms[$key]['month'] ?></td>

                    <td align="center" class="petlist"><p class="<?php echo $coms[$key]['color'] ?> blogodd"><?php echo $coms[$key]['day'] ?></td>
            </tr>

            <?php if ($coms[$key]['long'] == "true") { ?>
            <script>
                $('.tt_<?php echo $coms[$key]['id'] ?>').tooltipster({
                    contentCloning: true,
                    theme: 'tooltipster-smallnote',
                    updateAnimation: 'null',
                    animationDuration: 350,
                    maxWidth: 650
                 });
            </script>
            <?php } ?>


            <?php
            }

            ?>

            </tbody>
            <tfoot>
                    <tr>
                            <td colspan="2" align="right" class="table-page:previous" style="cursor:pointer;padding-top: 5px;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                            <td colspan="1" align="center"><div style="white-space:nowrap;padding-top: 5px;"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                            <td colspan="2" align="left" class="table-page:next" style="cursor:pointer;padding-top: 5px;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                            <td colspan="3" align="right"><div style="white-space:nowrap; margin-right:10px;padding-top: 5px;"><a class="wowhead" style="text-decoration: none; cursor:pointer" onclick="filter_reset()"><?php echo __("Reset Filters") ?></a></div></td>
                    </tr>
            </tfoot>
        </table>

        <script>
            function filter_reset() {
                document.getElementById('catfilter').value = '';
                Table.filter(document.getElementById('catfilter'),document.getElementById('catfilter'));
                document.getElementById('contentfilter').value = '';
                Table.filter(document.getElementById('contentfilter'),document.getElementById('contentfilter'));
                document.getElementById('respfilter').value = '';
                Table.filter(document.getElementById('respfilter'),document.getElementById('respfilter'));
                document.getElementById('votefilter').value = '';
                Table.filter(document.getElementById('votefilter'),document.getElementById('votefilter'));
                document.getElementById('yearfilter').value = '';
                Table.filter(document.getElementById('yearfilter'),document.getElementById('yearfilter'));
                document.getElementById('monthfilter').value = '';
                Table.filter(document.getElementById('monthfilter'),document.getElementById('monthfilter'));
                document.getElementById('dayfilter').value = '';
                Table.filter(document.getElementById('dayfilter'),document.getElementById('dayfilter'));
            }
        </script>

        <?php } ?>












        </th>
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
if ($urlchanger){
    echo '<script type="text/javascript" lang="javascript">';
    echo 'window.history.replaceState("object or string", "Title", "'.$urlchanger.'");';
    echo '</script>';
}
mysqli_close($dbcon);
echo "</body>";
die;