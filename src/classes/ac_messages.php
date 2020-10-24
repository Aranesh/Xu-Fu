<?php

// variable for messages shown at beginning
$maxmsgs = "10";
$outthreads = "0";
$inthreads = "0";

foreach($threads as $key => $value) {
    if ($threads[$key]['direction'] == "out") {
        $outthreads++;
    }
    if ($threads[$key]['direction'] == "in") {
        $inthreads++;
    }
}

if (!$redcat) {
    if ($page == "sentmsgs") {
        $redcat = "sent";
    }
    if ($page == "writemsg") {
        $redcat = "write";
    }
}

// Check GET data for given user to send message to and fill send to field accordingly

if ($page = "writemsg") {
    $sendto = $_GET["to"];
    if ($sendto) {
        $sendtodb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$sendto'");
        if (mysqli_num_rows($sendtodb) > "0") {
            $sendtouser = mysqli_fetch_object($sendtodb);
            if ($sendtouser->id != $user->id) {
                $pregivento = "true";
            }
        }
    }
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

<td width="100%"><h class="megatitle"><?php echo __("Private Messages") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('messages');
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
            <table>
                <tr>
                    <td>
                        <button id="ButtonInbox" onclick="messages_inbox()" class="settings <?php if ($redcat != "sent" AND $redcat != "write") { echo 'settingsactive'; } ?>" style="display: block"><?php echo __("Inbox") ?></button>
                    </td>
                    <td>
                        <button id="ButtonSent" onclick="messages_sent()" class="settings <?php if ($redcat == "sent") { echo 'settingsactive'; } ?>" style="display: block"><?php echo __("Sent Messages") ?></button>
                    </td>
                    <td>
                        <button id="ButtonWrite" onclick="messages_write()" class="settings <?php if ($redcat == "write") { echo 'settingsactive'; } ?>" style="display: block"><?php echo __("Write New Message") ?></button>
                    </td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">

            <div class="messagebox_outer">

                <div style="display:none" id="inmsgs"><?php echo $inthreads; ?></div>
                <div style="display:none" id="outmsgs"><?php echo $outthreads; ?></div>
                <div class="messagebox" id="inbox" <?php if ($redcat == "sent" OR $redcat == "write") { echo 'style="display:none;"'; } ?>>

                        <?
                        if ($threads) {
                           foreach($threads as $key => $value) {
                                if ($threads[$key]['direction'] == "in") {

                                if ($threads[$key]['seen'] == "0") {
                                    $threadicon = "images/icon_msgur.png";
                                }
                                else {
                                    $threadicon = "images/icon_msgrd.png";
                                }
                                $threadintro = __("Conversation with")." ".$threads[$key]['partnername'];
                                if ($threads[$key]['msgs'] > "1") {
                                    $threadmsgs = $threads[$key]['msgs']." ".__("Messages")." - ".$threads[$key]['lastmsgdate'];
                                }

                                if ($threads[$key]['type'] == "1") {
                                    $threadstyle = "msg_title_announce";
                                    if ($threads[$key]['seen'] == "0") {
                                        $threadicon = "images/icon_sysmsgur.png";
                                    }
                                    else {
                                        $threadicon = "images/icon_sysmsgrd.png";
                                    }
                                    $threadintro = $threads[$key]['subject'];
                                    $threaddate = $threads[$key]['lastmsgdate'];
                                }
                                if ($threads[$key]['type'] == "0" AND $threads[$key]['partnerrole'] < "50") {
                                    $threadstyle = "msg_title_pm";
                                }
                                if ($threads[$key]['type'] == "0" AND $threads[$key]['partnerrole'] >= "50") {
                                    $threadstyle = "msg_title_mod";
                                }
                                if ($threads[$key]['type'] == "0" AND $threads[$key]['partnerrole'] == "99") {
                                    $threadstyle = "msg_title_admin";
                                }
                                ?>

                            <div id="thread_<?php echo $threads[$key]['id']; ?>">
                                <div style="display:none" id="seenmarker_<?php echo $threads[$key]['id'] ?>"><?php echo $threads[$key]['seen']; ?></div>
                                <div class="<?php echo $threadstyle; ?>" onclick="msg_expand('<?php echo $threads[$key]['id']; ?>')<?php if ($threads[$key]['seen'] == "0") { ?>;msg_markread(<?php echo "'".$threads[$key]['id']."','".$user->id."','".$user->ComSecret."','".$threads[$key]['type']."'"; ?>)<?php } ?>">
                                    <div class="title_inner">
                                        <div style="width:40px;float:left;padding-top: 4px !important;">
                                            <img id="threadicon_<?php echo $threads[$key]['id'] ?>" src="<?php echo $threadicon ?>">
                                        </div>
                                        <div style="float:left; padding-top: 8px !important;">
                                            <span id="threadintro_<?php echo $threads[$key]['id'] ?>" style="white-space: nowrap<?php if ($threads[$key]['seen'] == "0") { echo ";font-weight: bold"; } ?>">
                                                <?php echo $threadintro ?>
                                            </span>
                                        </div>

                                        <div style="float:right; padding-top: 8px !important;">
                                            <span style="white-space: nowrap">
                                                <?php echo $threadmsgs; ?>
                                            </span>
                                        </div>



                                    </div>
                                </div>

                                <a data-remodal-target="modaldelete_<?php echo $threads[$key]['id']; ?>">
                                    <div class="title_delete_button">
                                        <center>
                                        <img style="padding-top: 7px;" src="images/icon_bin.png">
                                    </div>
                                </a>

                                <div class="remodalcomments" data-remodal-id="modaldelete_<?php echo $threads[$key]['id']; ?>">

                                    <table width="300" class="profile">
                                            <tr class="profile">
                                                    <th colspan="2" width="5" class="profile">
                                                            <table>
                                                                    <tr>
                                                                            <td><img src="images/icon_x.png"></td>
                                                                            <td><img src="images/blank.png" width="5" height="1"></td>
                                                                            <td><p class="blogodd"><b><?php echo __("Are you sure you want to delete this message?") ?></td>
                                                                    </tr>
                                                            </table>
                                                    </th>
                                            </tr>

                                            <tr class="profile">
                                                    <td class="collectionbordertwo"><center>
                                                            <table>
                                                                    <tr>
                                                                            <td style="padding-left: 12px;">
                                                                                    <input data-remodal-action="close" onclick="delete_msg('<?php echo $threads[$key]['id']; ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $threads[$key]['direction']; ?>')" type="submit" class="comdelete" value="<?php echo __("Delete") ?>">
                                                                            </td>
                                                                            <td style="padding-left: 15px;">
                                                                                    <input data-remodal-action="close" type="submit" class="comedit" value="<?php echo __("Cancel") ?>">
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
                                     $('[data-remodal-id=modaldelete_<?php echo $threads[$key]['id']; ?>]').remodal(options);
                                </script>



                                <div style="margin-bottom: 8px;position:relative;<?php if ($redthread != $threads[$key]['id']) { echo 'display:none;'; } ?>" id="<?php echo $threads[$key]['id']; ?>">

                                    <?
                                    $subscounter = "0";
                                    while ($subscounter < $threads[$key]['msgs']) {
                                        if ($subscounter == "1" && $threads[$key]['msgs'] > $maxmsgs) {     // Start DIV of hidden messages if more than 6 messages in thread
                                            $ovflowend = $threads[$key]['msgs']-$maxmsgs;
                                            ?>

                                            <div class="msg_out" style="cursor: pointer" id="msg_overflow_bt<?php echo $threads[$key]['id']; ?>" onclick="$('#msg_overflow_<?php echo $threads[$key]['id']; ?>').toggle(400);document.getElementById('msg_overflow_bt<?php echo $threads[$key]['id']; ?>').style.display = 'none';">
                                                <div class="msg_inner">   <br>
                                                    <center>======= <?php echo __("Click here to show older messages") ?> (<?php echo $ovflowend ?>) ========
                                               <br><br></div>
                                            </div>

                                            <div style="display:none;" id="msg_overflow_<?php echo $threads[$key]['id']; ?>">

                                        <?
                                        }
                                        ?>

                                        <div class="msg_<?php echo $msgs[$threads[$key]['id']][$subscounter]['direction']; ?>">
                                            <div class="msg_inner">
                                                <div class="msg_icon">
                                                    <?
                                                    echo '<a href="?user='.$msgs[$threads[$key]['id']][$subscounter]['senderid'].'" target="_blank"><img class="iconpic" style="width: 50px; height: 50px" '.$msgs[$threads[$key]['id']][$subscounter]['sendericon'].'></a>';
                                                    ?>
                                                </div>

                                                <div class="msg_header">
                                                    <?
                                                    if ($subscounter == "0") {
                                                        $combiner = " ".__("wrote on")." ";
                                                    }
                                                    else {
                                                        $combiner = " ".__("responded on")." ";
                                                    }
                                                    if ($threads[$key]['partnerdel'] == "1" && $msgs[$threads[$key]['id']][$subscounter]['senderid'] != $user->id) {
                                                        echo '<span style="font-size:14px">'.$msgs[$threads[$key]['id']][$subscounter]['sendername'].'</span>'.$combiner.$msgs[$threads[$key]['id']][$subscounter]['date'];
                                                    }
                                                    else {

                                                        echo '<span class="username" style="font-size:14px" rel="'.$msgs[$threads[$key]['id']][$subscounter]['senderid'].'" value="'.$user->id.'">'.$msgs[$threads[$key]['id']][$subscounter]['sendername'].'</span>'.$combiner.$msgs[$threads[$key]['id']][$subscounter]['date'];
                                                    }
                                                    ?>
                                                </div>

                                                <div class="msg_content">
                                                    <?php echo $msgs[$threads[$key]['id']][$subscounter]['content']; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?
                                        if ($subscounter == $ovflowend && $threads[$key]['msgs'] > $maxmsgs) {
                                            echo "</div>";
                                        }
                                        $subscounter++;
                                    }


                                    if ($threads[$key]['type'] != "1" && $threads[$key]['partnerdel'] != "1") {                    // Do not show response box when it's a system announcement
                                    ?>

                                    <div class="msg_responsebox">
                                        <div class="msg_inner" style="padding-left: 50px !important;">
                                            <div style="width:680px">
                                                <div class="msg_icon">
                                                    <?
                                                    echo '<a href="?user='.$user->id.'" target="_blank"><img class="iconpic" style="width: 50px; height: 50px" '.$usericon.'></a>';
                                                    ?>
                                                </div>

                                                    <form action="?page=sentmsgs" method="post" style="display: inline;">
                                                    <input type="hidden" name="sendto" value="<?php echo $threads[$key]['partnerid']; ?>">
                                                    <input type="hidden" name="parent" value="<?php echo $threads[$key]['id']; ?>">
                                                    <input type="hidden" name="delimiter" value="<?php echo $user->ComSecret; ?>">
                                                    <input type="hidden" name="cmd" value="sendrsp">
                                                <div style="float:left">
                                                    <textarea required name="msgcontent" placeholder="<?php echo __("Send a response") ?>" class="usermsgs" id="rsp_field_<?php echo $threads[$key]['id'] ?>" style="height: 50px; width: 600px;" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,<?php echo $threads[$key]['id'] ?>,'10000')" maxlength="10000"></textarea>
                                                </div>

                                            </div>

                                            <div style="width:664px; text-align: right;">
                                                    <span class="smallodd" style="padding-right: 10px" id="rsp_remaining_<?php echo $threads[$key]['id'] ?>">0/10000</span>
                                                    <input type="submit" class="comedit" value="<?php echo __("Send") ?>">
                                            </div>
                                                    </form>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    </div>
                                </div>
                            <?
                            }
                        }
                    }
                    ?>

                    <div class="msg_nomsgs" id="nomsgsboxin" <?php if ($inthreads != "0") { echo 'style="display:none"'; } ?>>
                        <?php echo __("There are no messages to display") ?>
                    </div>



                    </div>
                </div>

            </div>


            <div class="messagebox_outer">

                <div class="messagebox" id="sent" <?php if ($redcat != "sent") { echo 'style="display:none;"'; } ?>>
                        <?
                        if ($threads) {
                           foreach($threads as $key => $value) {
                                if ($threads[$key]['direction'] == "out") {

                                $threadicon = "images/icon_msgrd.png";
                                $threadintro = __("Conversation with")." ".$threads[$key]['partnername'];
                                if ($threads[$key]['msgs'] > "1") {
                                    $threadmsgs = $threads[$key]['msgs']." ".__("Messages")." - ".$threads[$key]['lastmsgdate'];
                                }

                                if ($threads[$key]['type'] == "1") {
                                    $threadstyle = "msg_title_announce";
                                    $threadicon = "images/icon_sysmsgrd.png";
                                    $threadintro = $threads[$key]['subject'];
                                    $threaddate = $threads[$key]['lastmsgdate'];
                                }
                                if ($threads[$key]['type'] == "0" AND $threads[$key]['partnerrole'] < "50") {
                                    $threadstyle = "msg_title_pm";
                                }
                                if ($threads[$key]['type'] == "0" AND $threads[$key]['partnerrole'] >= "50") {
                                    $threadstyle = "msg_title_mod";
                                }
                                if ($threads[$key]['type'] == "0" AND $threads[$key]['partnerrole'] == "99") {
                                    $threadstyle = "msg_title_admin";
                                }
                                ?>

                            <div id="thread_<?php echo $threads[$key]['id']; ?>">

                                <div class="<?php echo $threadstyle; ?>" onclick="msg_expand('<?php echo $threads[$key]['id']; ?>')">
                                    <div class="title_inner">
                                        <div style="width:40px;float:left;padding-top: 4px !important;">
                                            <img src="<?php echo $threadicon ?>">
                                        </div>
                                        <div style="float:left; padding-top: 8px !important;">
                                            <span style="white-space: nowrap">
                                                <?php echo $threadintro ?>
                                            </span>
                                        </div>

                                        <div style="float:right; padding-top: 8px !important;">
                                            <span style="white-space: nowrap">
                                                <?php echo $threadmsgs; ?>
                                            </span>
                                        </div>



                                    </div>
                                </div>

                                <a data-remodal-target="modaldelete_<?php echo $threads[$key]['id']; ?>">
                                    <div class="title_delete_button">
                                        <center>
                                        <img style="padding-top: 7px;" src="images/icon_bin.png">
                                    </div>
                                </a>

                                <div class="remodalcomments" data-remodal-id="modaldelete_<?php echo $threads[$key]['id']; ?>">

                                    <table width="300" class="profile">
                                            <tr class="profile">
                                                    <th colspan="2" width="5" class="profile">
                                                            <table>
                                                                    <tr>
                                                                            <td><img src="images/icon_x.png"></td>
                                                                            <td><img src="images/blank.png" width="5" height="1"></td>
                                                                            <td><p class="blogodd"><b><?php echo __("Are you sure you want to delete this message?") ?></td>
                                                                    </tr>
                                                            </table>
                                                    </th>
                                            </tr>

                                            <tr class="profile">
                                                    <td class="collectionbordertwo"><center>
                                                            <table>
                                                                    <tr>
                                                                            <td style="padding-left: 12px;">
                                                                                    <input data-remodal-action="close" onclick="delete_msg('<?php echo $threads[$key]['id']; ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $threads[$key]['direction']; ?>')" type="submit" class="comdelete" value="<?php echo __("Delete") ?>">
                                                                            </td>
                                                                            <td style="padding-left: 15px;">
                                                                                    <input data-remodal-action="close" type="submit" class="comedit" value="<?php echo __("Cancel") ?>">
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
                                     $('[data-remodal-id=modaldelete_<?php echo $threads[$key]['id']; ?>]').remodal(options);
                                </script>



                                <div style="margin-bottom: 8px;position:relative;<?php if ($redthread != $threads[$key]['id']) { echo 'display:none;'; } ?>" id="<?php echo $threads[$key]['id']; ?>">

                                    <?
                                    $subscounter = "0";
                                    while ($subscounter < $threads[$key]['msgs']) {
                                        if ($subscounter == "1" && $threads[$key]['msgs'] > $maxmsgs) {     // Start DIV of hidden messages if more than 6 messages in thread
                                            $ovflowend = $threads[$key]['msgs']-$maxmsgs;
                                            ?>

                                            <div class="msg_out" style="cursor: pointer" id="msg_overflow_bt<?php echo $threads[$key]['id']; ?>" onclick="$('#msg_overflow_<?php echo $threads[$key]['id']; ?>').toggle(400);document.getElementById('msg_overflow_bt<?php echo $threads[$key]['id']; ?>').style.display = 'none';">
                                                <div class="msg_inner">   <br>
                                                    <center>======= <?php echo __("Click here to show older messages") ?> (<?php echo $ovflowend ?>) ========
                                               <br><br></div>
                                            </div>

                                            <div style="display:none;" id="msg_overflow_<?php echo $threads[$key]['id']; ?>">

                                        <?
                                        }
                                        ?>

                                        <div class="msg_<?php echo $msgs[$threads[$key]['id']][$subscounter]['direction']; ?>">
                                            <div class="msg_inner">
                                                <div class="msg_icon">
                                                    <?
                                                    echo '<a href="?user='.$msgs[$threads[$key]['id']][$subscounter]['senderid'].'" target="_blank"><img class="iconpic" style="width: 50px; height: 50px" '.$msgs[$threads[$key]['id']][$subscounter]['sendericon'].'></a>';
                                                    ?>
                                                </div>

                                                <div class="msg_header">
                                                    <?
                                                    if ($subscounter == "0") {
                                                        $combiner = " ".__("wrote on")." ";
                                                    }
                                                    else {
                                                        $combiner = " ".__("responded on")." ";
                                                    }
                                                    if ($threads[$key]['partnerdel'] == "1" && $msgs[$threads[$key]['id']][$subscounter]['senderid'] != $user->id) {
                                                        echo '<span style="font-size:14px">'.$msgs[$threads[$key]['id']][$subscounter]['sendername'].'</span>'.$combiner.$msgs[$threads[$key]['id']][$subscounter]['date'];
                                                    }
                                                    else {
                                                        echo '<span class="username" style="font-size:14px" rel="'.$msgs[$threads[$key]['id']][$subscounter]['senderid'].'" value="'.$user->id.'">'.$msgs[$threads[$key]['id']][$subscounter]['sendername'].'</span>'.$combiner.$msgs[$threads[$key]['id']][$subscounter]['date'];
                                                    }
                                                    ?>
                                                </div>

                                                <div class="msg_content">
                                                    <?php echo $msgs[$threads[$key]['id']][$subscounter]['content']; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?
                                        if ($subscounter == $ovflowend && $threads[$key]['msgs'] > $maxmsgs) {
                                            echo "</div>";
                                        }

                                        $subscounter++;
                                    }



                                    if ($threads[$key]['type'] != "1" && $threads[$key]['partnerdel'] != "1") {                    // Do not show response box when it's a system announcement
                                    ?>

                                    <div class="msg_responsebox">
                                        <div class="msg_inner" style="padding-left: 50px !important;">
                                            <div style="width:680px">
                                                <div class="msg_icon">
                                                    <?
                                                    echo '<a href="?user='.$user->id.'" target="_blank"><img class="iconpic" style="width: 50px; height: 50px" '.$usericon.'></a>';
                                                    ?>
                                                </div>

                                                    <form action="?page=sentmsgs" method="post" style="display: inline;">
                                                    <input type="hidden" name="sendto" value="<?php echo $threads[$key]['partnerid']; ?>">
                                                    <input type="hidden" name="parent" value="<?php echo $threads[$key]['id']; ?>">
                                                    <input type="hidden" name="delimiter" value="<?php echo $user->ComSecret; ?>">
                                                    <input type="hidden" name="cmd" value="sendrsp">
                                                <div style="float:left">
                                                    <textarea required name="msgcontent" placeholder="<?php echo __("Send a response") ?>" class="usermsgs" id="rsp_field_<?php echo $threads[$key]['id'] ?>" style="height: 50px; width: 600px;" onkeyup="auto_adjust_textarea_size(this);count_remaining_msgs(this,<?php echo $threads[$key]['id'] ?>,'10000')" maxlength="10000"></textarea>
                                                </div>

                                            </div>

                                            <div style="width:664px; text-align: right;">
                                                    <span class="smallodd" style="padding-right: 10px" id="rsp_remaining_<?php echo $threads[$key]['id'] ?>">0/10000</span>
                                                    <input type="submit" class="comedit" value="<?php echo __("Send") ?>">
                                            </div>
                                                    </form>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    </div>
                                </div>
                       <?
                            }
                       }
                    } ?>

                        <div class="msg_nomsgs" id="nomsgsboxout" <?php if ($outthreads != "0") { echo 'style="display:none"'; } ?>>
                            <?php echo __("There are no messages to display") ?>
                        </div>
                    </div>





                    <div class="messagebox" id="write" <?php if ($redcat != "write") { echo 'style="display:none;"'; } ?>>

                        <div class="write_msg">
                            <form action="index.php?page=writemsg" method="post">
                                <input type="hidden" name="delimiter" value="<?php echo $user->ComSecret; ?>">
                                <input type="hidden" name="cmd" value="sendmsg">

                                <table>
                                    <tr>
                                        <td>
                                            <span style="white-space: nowrap"><p class="blogodd"><b><?php echo __("Send to") ?>:</span>
                                        </td>
                                        <td style="padding-left: 5px;">
                                            <?php if ($pregivento != "true") { ?>

                                                <select data-placeholder="<?php echo __("Name of recipient") ?>" id="username" name="recipient" class="chosen-select" required>
                                                    <option value="0"></option>
                                                </select>

                                                <script type = "text/javascript">
                                                    $("#username").chosen({width: 325});
                                                </script>


                                            <?php }
                                            else { ?>
                                                <input type="hidden" name="recipient" value="<?php echo $sendtouser->id ?>">
                                                <span class="username" style="text-decoration: none;" rel="<?php echo $sendtouser->id ?>" value="<?php echo $user->id ?>"><p class="blogodd"><b><?php echo $sendtouser->Name ?></b></p></span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td style="padding-left: 5px;">
                                            <textarea required name="msgcontent" placeholder="<?php echo __("Type your message here") ?>" class="usermsgs" id="rsp_field_write" style="height: 60px; width: 600px;" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'write','10000')" maxlength="10000"><?php echo $msgcontent; ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <div style="width:100%; text-align: right;">
                                                <span class="smallodd" style="padding-right: 10px" id="rsp_remaining_write">0/10000</span>
                                                <input type="submit" class="comedit" value="<?php echo __("Send") ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </form>

                        </div>
                    </div>

                    <?php if ($pregivento != "true") { ?>
                    <script>
                    var x_timer;
                    $('.chosen-search-input').on('input',function(e){
                        var searchterm = $('.chosen-search-input').val();
                        clearTimeout(x_timer);
                        x_timer = setTimeout(function(){
                            $(".no-results").text("<?php echo __("Searching...") ?>");
                            if (searchterm.length >= '2') {
                                clearTimeout(x_timer);
                                x_timer = setTimeout(function(){
                                    $("#username").empty();
                                    var xmlhttp = new XMLHttpRequest();
                                    xmlhttp.onreadystatechange = function() {
                                        if (this.readyState == 4 && this.status == 200) {
                                            if (this.responseText == "[]") {
                                                $(".no-results").text("<?php echo __("No user found") ?>");
                                            }
                                            else {
                                                var data = this.responseText;
                                                data = JSON.parse(data);

                                                $.each(data, function (idx, obj) {
                                                    $("#username").append('<option value="' + obj.id + '">' + obj.text + '</option>');
                                                });
                                                $("#username").trigger("chosen:updated");

                                                $("#username").chosen({width: 325});
                                                $('.chosen-search-input').val(searchterm);
                                            }
                                        }
                                    };
                                    xmlhttp.open("GET", "classes/ajax/ac_writemessage.php?q=" + encodeURIComponent(searchterm) + "&e=noxu&u=<?php echo $user->id ?>", true);
                                    xmlhttp.send();
                                }, 1000);
                            }
                            else {
                                clearTimeout(x_timer);
                                x_timer = setTimeout(function(){
                                    $("#username").empty();
                                    $(".no-results").text("<?php echo __("Please enter 2 or more characters") ?>");
                                }, 300);
                            }
                        }, 200);
                    });
                    </script>
                    <?php } ?>


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
    case "rsperror":
        echo '<script type="text/javascript">$.growl.error({ message: '.__("There was an error processing your data, I am sorry. Please try again.").', duration: "7000", size: "large", location: "tc" });</script>';
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