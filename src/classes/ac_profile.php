<?php

$titlecount = "0";
$titleext = "Name_".$language;
$titledb = mysqli_query($dbcon, "SELECT * FROM UserTitles");
while($thistitle = mysqli_fetch_object($titledb)) {
    $addtitle = "false";
    if ($user->Role == 99 ) {                                                             // Admin, all titles by default
        $addtitle = "true";
    }
    if ($thistitle->Requirement == "0") {                                                 // Standard Titles for everyone
        $addtitle = "true";
    }
    if ($user->Role >= 50 AND $user->Role <= 60 AND $thistitle->Requirement >= 50 AND $thistitle->Requirement <= 60 ) {     // Moderators
        $addtitle = "true";
    }

    if ($thistitle->id == "6") {                                                          // Early Bird title
        $enddate = strtotime("2017-12-01 00:00:01");
        $regdate = strtotime($user->regtime);
        if ($regdate < $enddate) {
            $addtitle = "true";
        }
    }

    if ($thistitle->id == "8" && $user->id == "1") {                                      // Xu-Fu account
        $addtitle = "true";
    }
    if ($addtitle == "true") {
        $titlelist[$titlecount]['id'] = $thistitle->id;
        if ($thistitle->${'titleext'} != "") {
            $titlelist[$titlecount]['name'] = htmlentities($thistitle->${'titleext'}, ENT_QUOTES, "UTF-8");
        }
        else {
            $titlelist[$titlecount]['name'] = htmlentities($thistitle->Name_en_US, ENT_QUOTES, "UTF-8");
        }
        $titlecount++;
    }
}
sortBy('name', $titlelist, 'asc');
sortBy('Name', $all_pets, 'asc');

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

<center>

    <? // =============  Sharing option ============= ?>
    <table width="200" class="profile">
        <tr class="profile">
            <td class="profile">
                <p class="commentodd">
                <b>Share your profile:</b><br>
                <a style="cursor: pointer" id="cb_share_profile" data-clipboard-text="https://wow-petguide.com?user=<? echo $user->id ?>"><img class="icon_share" src="images/icon_share.png"></a>
                <div class="remtt" style="display:none;" id="cb_share_profile_conf"><? echo _("BattletableRematchStringConf") ?></div>
                <script>
                    var btn = document.getElementById('cb_share_profile');
                    var clipboard = new Clipboard(btn);

                    clipboard.on('success', function(e) {
                        console.log(e);
                            $('#cb_share_profile_conf').delay(0).fadeIn(500);
                            $('#cb_share_profile_conf').delay(1200).fadeOut(500);
                        });

                    clipboard.on('error', function(e) {
                        console.log(e);
                    });
                </script>
                </center>
            </td>
        </tr>
    </table>
    <br>

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

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">



            <p class="blogodd"><? echo _("UP_PRInst") ?><br></p> <br>



           <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRTIntro") ?></b></p>

            <div style="max-width: 800px;">
                <table style="width:100%;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div id="user_intro" contenteditable="true" class="editable_textarea" maxlength="3000" onkeyup="count_remaining_profile(this)"><?
                                $commenttext = stripslashes($user->PrIntro);
                                $commenttext = htmlentities($commenttext, ENT_QUOTES, "UTF-8");
                                $commenttext = preg_replace("/\n/s", "<br>", $commenttext);
                                echo $commenttext; ?>
                            </div>
                        </td>
                        <td style="width:38px; text-align: right; vertical-align: bottom">
                            <img class="suc_img" width="25" height="25" src="images/blank.png" id="intro_suc">
                        </td>
                    </tr>
                    <tr>
                        <td style="max-width: 100%; text-align: right; padding-top: 5px;">
                            <span style="padding-right: 15px" class="smallodd" id="intro_remaining"></span><span style="white-space: nowrap;padding-left:15px"><p class="smallodd">[b]<b><? echo _("FormComFormatBold") ?></b>[/b] - [i]<i><? echo _("FormComFormatItalic") ?></i>[/i] - [u]<u><? echo _("FormComFormatUnderline") ?></u>[/u]</span>
                        </td>
                    </tr>
                </table>

            </div>

            <br>

            <table>
                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHTitle") ?></p>
                    </td>
                    <td style="padding-left: 10px;">

                        <select data-placeholder="" id="titleselect" class="chosen-select" onchange="pr_save_title('<? echo $user->id ?>','<? echo $user->ComSecret ?>')" required>
                            <?
                            foreach($titlelist as $key => $value) {
                                if ($user->Title == $value['id']) {
                                    echo '<option selected value="'.$value['id'].'">'.$value['name'].'</option>';
                                }
                                else {
                                    echo '<option value="'.$value['id'].'">'.$value['name'].'</option>';
                                }
                            } ?>
                        </select>

                        <script type = "text/javascript">
                            $(".chosen-select").chosen({disable_search_threshold: 10, width: 325});
                        </script>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="title_suc">
                    </td>
                </tr>


                <tr><td colspan="3"><hr class="profile"></td></tr>


                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHBtag") ?></p>
                    </td>
                    <td style="padding-left: 10px;">
                        
                        <table cellpadding="0" cellspacing="0">
                            <tr><td>
                                <select data-placeholder=" " id="btagreg_select" width="50px" style="display: inline" class="chosen-select_region" onchange="pr_save_btagregion()" required>
                                    <option value="us" <? if ($user->PrBTagRegion == "us" OR $user->PrBTagRegion == "") echo "selected" ?>>US</option>
                                    <option value="eu" <? if ($user->PrBTagRegion == "eu") echo "selected" ?>>EU</option>
                                    <option value="kr" <? if ($user->PrBTagRegion == "kr") echo "selected" ?>>KR</option>
                                    <option value="tw" <? if ($user->PrBTagRegion == "tw") echo "selected" ?>>TW</option>
                                    <option value="cn" <? if ($user->PrBTagRegion == "cn") echo "selected" ?>>CN</option>
                                </select>
                                 <script>
                                    $(".chosen-select_region").chosen({width: 63});
                                 </script>  
                            </td>
                            <td>-</td>
                            <td>
                                <div id="btag" contenteditable="true" class="editable_input" style="width:189px" maxlength="12"><?
                                    if ($user->PrBattleTag != "") {
                                        echo htmlentities($user->PrBattleTag, ENT_QUOTES, "UTF-8");
                                    }
                                ?></div>
                            </td>
                            <td><p class="blogodd" style="font-weight: bold">#</td>
                            <td>
                                <div id="btagnum" contenteditable="true" class="editable_input" style="width:45px" maxlength="4"><?
                                    if ($user->PrBTagNum != "") {
                                        echo htmlentities($user->PrBTagNum, ENT_QUOTES, "UTF-8");
                                    }
                                ?></div>
                            </td>
                            </tr>
                        </table>

                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="btag_suc"><img class="suc_img" id="btagnum_suc">
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHDiscord") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="discord" contenteditable="true" class="editable_input" maxlength="40"><?
                            if ($user->PrDiscord != "") {
                                echo htmlentities($user->PrDiscord, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="discord_suc">
                    </td>
                </tr>

                <tr><td colspan="3"><hr class="profile"></td></tr>


                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHFavpet") ?></p>
                    </td>
                    <td style="padding-left: 10px;">

                        <select data-placeholder="" id="petselect" class="chosen-select" onchange="pr_save_pet('<? echo $user->id ?>','<? echo $user->ComSecret ?>')" required>
                            <option <? if ($user->PrFavPet == "0") { echo "selected"; } ?> value="0">None</option>
                            <?
                            foreach($all_pets as $key => $value) {
                                if ($user->PrFavPet == $value['PetID']) {
                                    echo '<option selected value="'.$value['PetID'].'">'.$value['Name'].'</option>';
                                }
                                else {
                                    echo '<option value="'.$value['PetID'].'">'.$value['Name'].'</option>';
                                }
                            } ?>
                        </select>

                        <script type = "text/javascript">
                            $(".chosen-select").chosen({width: 325});
                        </script>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="pet_suc">
                    </td>
                </tr>


                <? if ($collection) { ?>

                <tr><td colspan="3"><hr class="profile"></td></tr>
                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHShowcol") ?></p>
                    </td>
                    <td style="padding-left: 15px;">
                        <div id="ttcolswitch" class="armoryswitch">
                            <input type="checkbox" class="armoryswitch-checkbox" id="pr_col" onchange="pr_colview('<? echo $user->id ?>','<? echo $user->ComSecret ?>');" <? if ($usersettings[7] == "1" OR $usersettings[7] == "") { echo "checked"; } ?>>
                            <label class="armoryswitch-label" for="pr_col">
                            <span class="armoryswitch-inner"></span>
                            <span class="armoryswitch-switch"></span>
                            </label>
                        </div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="collection_suc">
                    </td>
                </tr>
                <? } ?>


                <tr><td colspan="3"><hr class="profile"></td></tr>

                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHFacebook") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="facebook" contenteditable="true" class="editable_input" maxlength="200"><?
                            if ($user->PrSocFacebook != "") {
                                echo htmlentities($user->PrSocFacebook, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="facebook_suc">
                    </td>
                </tr>

                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHTwitter") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="twitter" contenteditable="true" class="editable_input" maxlength="200"><?
                            if ($user->PrSocTwitter != "") {
                                echo htmlentities($user->PrSocTwitter, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="twitter_suc">
                    </td>
                </tr>

                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHInstagram") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="instagram" contenteditable="true" class="editable_input" maxlength="200"><?
                            if ($user->PrSocInstagram != "") {
                                echo htmlentities($user->PrSocInstagram, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="instagram_suc">
                    </td>
                </tr>

                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHYoutube") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="youtube" contenteditable="true" class="editable_input" maxlength="200"><?
                            if ($user->PrSocYoutube != "") {
                                echo htmlentities($user->PrSocYoutube, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="youtube_suc">
                    </td>
                </tr>

                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHReddit") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="reddit" contenteditable="true" class="editable_input" maxlength="200"><?
                            if ($user->PrSocReddit != "") {
                                echo htmlentities($user->PrSocReddit, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="reddit_suc">
                    </td>
                </tr>

                <tr>
                    <td>
                        <p class="blogodd" style="font-weight: bold"><? echo _("UP_PRHTwitch") ?>:</p>
                    </td>
                    <td style="padding-left: 10px;">
                        <div id="twitch" contenteditable="true" class="editable_input" maxlength="200"><?
                            if ($user->PrSocTwitch != "") {
                                echo htmlentities($user->PrSocTwitch, ENT_QUOTES, "UTF-8");
                            }
                        ?></div>
                    </td>
                    <td style="padding-left: 5px;">
                        <img class="suc_img" id="twitch_suc">
                    </td>
                </tr>
            </table>
        <br>
        <p class="blogodd"><i><? echo _("UP_PRInstbot") ?><br>
        </td>
    </tr>

</table>

</table>

<script>

function pr_save_btagregion() {
    var e = document.getElementById("btagreg_select");
    var bt_reg = e.options[e.selectedIndex].value;
    var bt_tag = $('#btag').text();
    var bt_num = $('#btagnum').text();
    var field_content = bt_reg+'__separatorzzuugg__'+bt_tag+'__separatorzzuugg__'+bt_num;
    pr_save_fields_ajax('btag',field_content,'12','<? echo $user->id ?>','<? echo $user->ComSecret ?>');
}


$('[contenteditable]').on('paste',function(e) {
    e.preventDefault();
    var text = (e.originalEvent || e).clipboardData.getData('text/plain');
    window.document.execCommand('insertText', false, text);
});

$(document).ready(function() {
    var x_timer;
    $("div[contenteditable='true'][maxlength]").on('keydown paste', function (event) {
        clearTimeout(x_timer);
        var field = $(this).attr('class');
        var cntMaxLength = parseInt($(this).attr('maxlength'));
        var field_content = $(this).text();
        if (field_content.length > cntMaxLength) {
            var content = field_content.slice(0, cntMaxLength);
            $(this).html(content);
        }
        var ctrlDown = false,
            ctrlKey = 17,
            cmdKey = 91,
            behav = "";
        if((event.ctrlKey || event.cmdKey) && event.keyCode==67) {
          behav = 'copy';
        }
        if((event.ctrlKey || event.cmdKey) && event.keyCode==88) {
          behav = 'cut';
        }
        if (field_content.length === cntMaxLength && event.keyCode != 8 && event.keyCode != 37 && event.keyCode != 39 && event.keyCode != 46 && behav != 'paste' && behav != 'copy' && behav != 'cut') {
            event.preventDefault();
        }
        if (field == "editable_input") {
            var field_type = $(this).attr('id');
            x_timer = setTimeout(function(){
                var field_content = $('#'+field_type).text();
                if (field_type == "btag" || field_type == "btagnum") {
                    var e = document.getElementById("btagreg_select");
                    var bt_reg = e.options[e.selectedIndex].value;
                    var bt_tag = $('#btag').text();
                    var bt_num = $('#btagnum').text();
                    field_content = bt_reg+'__separatorzzuugg__'+bt_tag+'__separatorzzuugg__'+bt_num;
                }
                pr_save_fields_ajax(field_type,field_content,cntMaxLength,'<? echo $user->id ?>','<? echo $user->ComSecret ?>');
            }, 700);
        }
        else {
            x_timer = setTimeout(function(){
                var field_content = $('#user_intro').html();
                pr_save_intro_ajax(field_content,cntMaxLength,'<? echo $user->id ?>','<? echo $user->ComSecret ?>');
            }, 1500);
        }
    });
});
</script>











</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
mysqli_close($dbcon);
echo "</body>";
die;
