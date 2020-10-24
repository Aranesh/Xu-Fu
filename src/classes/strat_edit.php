<?
$all_special_pets_db = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID < 21 ORDER BY Name") or die(mysqli_error($dbcon));
while ($thispet = mysqli_fetch_object($all_special_pets_db)) {
    $all_special_pets[$thispet->PetID]['PetID'] = $thispet->PetID;
    $all_special_pets[$thispet->PetID]['Name'] = $thispet->Name;
    if (isset($thispet->{$petnext}) && $thispet->{$petnext} != "") {
        $all_special_pets[$thispet->PetID]['Name'] = $thispet->{$petnext};
    }
}

$all_normal_pets = $all_pets;
foreach ($all_normal_pets as $thispet) {
    if ($thispet['Species'] < 21 OR $thispet['Special'] == 1) {
        unset($all_normal_pets[$thispet['Species']]);
    }
}
sortBy('Name', $all_normal_pets, 'asc');

// ===================== Publish or Unpublish =====================

if (($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) && $strat->Deleted == 0) { ?>
    <div class="edit_top_box">
        <div style="float: left">
            <?php if ($user->id == $strat->User) {
                echo __('You are the creator of this strategy');
            }
            else {
                 echo __('You can modify this strategy');
            }
            ?>
        </div>
        <div style="float: right">
            <div class="publishswitch">
                <input type="checkbox" class="publishswitch-checkbox" id="setbeta" onchange="stratedit_publish('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $strat->id ?>');" <?php if ($strat->Published == "1" ) { echo "checked"; } ?>>
                <label class="publishswitch-label" for="setbeta">
                <span class="publishswitch-inner"></span>
                <span class="publishswitch-switch"></span>
                </label>
            </div>
        </div>
        <div id="stratedit_publ" style="float: right; margin-right: 10px; <?php if ($strat->Published == "0") echo 'display: none'; ?>">
            <?php echo __('Strategy is published'); ?>
        </div>
        <div id="stratedit_unpubl" style="float: right; padding: 0 6 0 6; margin-right: 10px; background-color: #c71717; <?php if ($strat->Published == "1") echo 'display: none'; ?>">
            <?php echo __('This strategy is not published!'); ?>
        </div>
    </div>
<?php }

if ($userrights['EditStrats'] == "yes" && $strat->Deleted == 1) { ?>
    <div class="edit_top_box_deleted">
        <div style="float: left">
            <?php echo __('This strategy is deleted! To undelete, get in touch with Aranesh'); ?>
        </div>
    </div>
<?php }


// ===================== Add new Strategy =====================

if ($user) {
    // Define position depending on substitutes or none
    if ($petslotinfo[1]['Subscount'] > "1" OR $petslotinfo[2]['Subscount'] > "1" OR $petslotinfo[3]['Subscount'] > "1") {
        $tabheight = "342";
    }
    else {
        $tabheight = "320";
    }
    ?>

    <div class="edit_add_linkbox" data-remodal-target="modal_edit_add" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_add_box_s edit_add_box_s" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_add_box edit_add_box" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_add_boxtext" style="top: <?php echo $tabheight-5; ?>">
        +
    </div>

    <div class="remodal remodalstratedit" data-remodal-id="modal_edit_add">
        <form enctype="multipart/form-data" action="index.php" method="POST">
            <input type="hidden" name="alt_edit_action" value="edit_add">
            <input type="hidden" name="currentstrat" value="<?php echo $strat->id ?>">
            <input type="hidden" name="addmain" value="<?php echo $mainselector ?>">
            <input type="hidden" name="addsub" value="<?php echo $subselector ?>">
            <table style="width: 400px" class="profile">
                <tr class="profile">
                    <th colspan="2" style="width: 100%" class="profile">
                        <table>
                            <tr>
                                <td><img src="images/icon_art_author.png" style="padding-right: 5px"></td>
                                <td><p class="blogodd"><span style="white-space: nowrap;"><b>Add a new strategy</span></td>
                            </tr>
                        </table>
                    </th>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo" colspan="2" style="font-family: MuseoSans-500; font-size: 14px">
                        <img style="float: left; margin-right: 10px" src="https://www.wow-petguide.com/images/xufu_mail.png">
                        <br>
                        Please make sure you have read the <a href="?m=Rules" class="wowhead" style="font-size: 14px" target="_blank">Strategy Creation Rules</a>.<br><br>
                        Thanks for sharing your strategy!
                        <br><br><br><center>
                            <table><tr>
                                <td style="font-family: MuseoSans-500; font-size: 14px">Import via Rematch:</td>
                                <td>
                                    <input type="text" maxlength="300" class="stredit_editline" style="width: 100%;" name="rematch_import" placeholder="(optional)">
                                </td>
                            </tr></table>
                        <button type="submit" class="bnetlogin">Begin Strategy Creation</button>
                        <br><br>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
    var options = {
        hashTracking: false
    };
    $('[data-remodal-id=modal_edit_add]').remodal(options);
    </script>
<?php }


// ===================== Delete Strategy =====================

if (($userrights['DeleteStrats'] == "yes" OR $strat->User == $user->id) && $strat->Deleted != 1) {
    // Define position depending on substitutes or none
    if ($petslotinfo[1]['Subscount'] > "1" OR $petslotinfo[2]['Subscount'] > "1" OR $petslotinfo[3]['Subscount'] > "1") {
        $tabheight = "384";
    }
    else {
        $tabheight = "362";
    }
    ?>

    <div class="edit_add_linkbox" data-remodal-target="modal_edit_delete" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_add_box_s edit_delete_box_s" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_add_box edit_delete_box" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_add_boxtext" style="top: <?php echo $tabheight-3; ?>">
        -
    </div>

    <div class="remodal remodalstratedit remodaldelete" data-remodal-id="modal_edit_delete">
        <table style="width: 400px" class="profile">
            <tr class="profile">
                <th colspan="2" style="width: 100%" class="profile">
                    <table>
                        <tr>
                            <td><img src="images/icon_report.png" style="padding-right: 5px"></td>
                            <td><p class="blogodd"><span style="white-space: nowrap;"><b><?php echo __('Delete Strategy'); ?></span></td>
                        </tr>
                    </table>
                </th>
            </tr>

            <tr class="profile">
                <td class="collectionbordertwo" colspan="2" style="font-family: MuseoSans-500; font-size: 14px">
                    <div id="del_part1">
                        <center><br><b>You are about to delete this strategy. </b><br><br>
                        This process is not reversible - the strategy and all comments written on it will be permanently deleted.
                        <br><br>
                        <button type="submit" class="redlarge" onclick="$('#del_part1').hide(); $('#del_part2').show()">Understood</button>
                        <br><br>
                    </div>
                    <div id="del_part2" style="display: none">
                        <center><br><b>Are you absolutely sure?</b><br><br>There is no going back!
                        <br><br>
                            <form enctype="multipart/form-data" action="index.php" method="POST">
                                <input type="hidden" name="alt_edit_action" value="edit_delete">
                                <input type="hidden" name="currentstrat" value="<?php echo $strat->id ?>">
                                <button type="submit" class="redlarge">Yes, delete this strategy</button>
                            </form>
                        <br>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <script>
    var options = {
        hashTracking: false
    };
    $('[data-remodal-id=modal_edit_delete]').remodal(options);
    $(document).on('closing', '.remodaldelete', function () {
        $('#del_part1').show();
        $('#del_part2').hide()
    });
    </script>
<?php }



// ===================== Edit Pets =====================

if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) { ?>

    <div class="edit_pets_linkbox" data-remodal-target="modal_edit_pets"></div>
    <div class="edit_pets_box_s edit_box_s"></div>
    <div class="edit_pets_box edit_box"></div>
    <div class="edit_pets_boxtext">
        Pets
    </div>

    <div class="remodal remodalstratedit" data-remodal-id="modal_edit_pets">
        <form enctype="multipart/form-data" action="index.php?Strategy=<?php echo $strat->id ?>" method="POST">
            <input type="hidden" name="alt_edit_action" value="edit_save_pets">
            <table style="width:780" class="profile">
                <tr class="profile">
                    <th colspan="2" style="width: 100%" class="profile">
                        <table style="width: 100%;">
                            <tr>
                                <td><img src="images/userdd_settings_grey.png"></td>
                                <td><img src="images/blank.png" width="5" height="1"></td>
                                <td><p class="blogodd"><span style="white-space: nowrap;"><b>Modify Pets</span></td>
                                <td style="width: 100%; text-align: right"><p class="blogodd edit_pets_help" data-tooltip-content="#edit_pets_helptt" style="cursor: pointer">?</td>
                                <div style="display: none"><span id="edit_pets_helptt">
                                    Here you can modify the pets of your strategy. Check out the full <a href="?m=StratCreationGuide" class="growl" style="font-size: 14px" target="_blank">strategy creation guide</a> for more details.
                                    <ul>
                                        <li>Use the drop down menus to add pets to your strategy.</li>
                                        <li>Special pets like the level slot or a random pet from a family are shown at the top of each list</li>
                                        <li>For many pets you can select which breeds are shown as working and which are not</li>
                                        <li>If there is no breed selection available, the chosen pet only has one possible breed</li>
                                        <li>Adding stat requirements is usually better than selecting specific breeds</li>
                                    </ul>
                                </span></div>
                                <script>
                                $(document).ready(function() {
                                    $('.edit_pets_help').tooltipster({
                                        maxWidth: '400',
                                        interactive: true,
                                        side: ['left'],
                                        theme: 'tooltipster-smallnote'
                                    });
                                });
                                </script>
                            </tr>
                        </table>
                    </th>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo" colspan="2">
                        <div style="float: left; width: 235px; padding: 4px; background-color: #cacaca; border: 2px solid #a4a4a4; margin-right: 5px">
                            <div style="width: 210; font-family: MuseoSans-500; font-size: 16px; float: left; margin: 0 6 6 0;">First</div>
                            <div id="petfamilyicon_1" style="height: 19px; float: left; margin-top: 1px"></div>
                            <div id="peticon_1" style="width: 25px; float: left"></div>
                            <div style="width: 210px; float: right">
                                <select data-placeholder="" name="edit_pet1_select" id="edit_pet1_select" class="chosen-select">
                                    <option value="1" <?php if ($strat->PetID1 == "1") echo "selected"; ?>>Any Pet</option>
                                    <option value="0" <?php if ($strat->PetID1 == "0") echo "selected"; ?>>Level Pet</option>
                                    <?php foreach ($all_special_pets as $thispet) { ?>
                                        <option value="<?php echo $thispet['PetID'] ?>" <?php if ($strat->PetID1 == $thispet['PetID']) echo "selected"; ?>><?php echo $thispet['Name'] ?></option>
                                    <?php }
                                    foreach ($all_normal_pets as $thispet) { ?>
                                        <option value="<?php echo $thispet['Species'] ?>" <?php if ($strat->PetID1 == $thispet['Species']) echo "selected"; ?>><?php echo $thispet['Name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div id="petdetails_1"></div>
                        </div>

                        <div style="float: left; width: 235px; padding: 4px; background-color: #cacaca; border: 2px solid #a4a4a4; margin-right: 5px">
                            <div style="width: 210; font-family: MuseoSans-500; font-size: 16px; float: left; margin: 0 6 6 0;">Second</div>
                            <div id="petfamilyicon_2" style="height: 19px; float: left; margin-top: 1px"></div>
                            <div id="peticon_2" style="width: 25px; float: left"></div>
                            <div style="width: 210px; float: right">
                                <select data-placeholder="" name="edit_pet2_select" id="edit_pet2_select" class="chosen-select">
                                    <option value="1" <?php if ($strat->PetID2 == "1") echo "selected"; ?>>Any Pet</option>
                                    <option value="0" <?php if ($strat->PetID2 == "0") echo "selected"; ?>>Level Pet</option>
                                    <?php foreach ($all_special_pets as $thispet) { ?>
                                        <option value="<?php echo $thispet['PetID'] ?>" <?php if ($strat->PetID2 == $thispet['PetID']) echo "selected"; ?>><?php echo $thispet['Name'] ?></option>
                                    <?php }
                                    foreach ($all_normal_pets as $thispet) { ?>
                                        <option value="<?php echo $thispet['Species'] ?>" <?php if ($strat->PetID2 == $thispet['Species']) echo "selected"; ?>><?php echo $thispet['Name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div id="petdetails_2"></div>
                        </div>

                        <div style="float: left; width: 235px; padding: 4px; background-color: #cacaca; border: 2px solid #a4a4a4;">
                            <div style="width: 210; font-family: MuseoSans-500; font-size: 16px; float: left; margin: 0 6 6 0;">Third</div>
                            <div id="petfamilyicon_3" style="height: 19px; float: left; margin-top: 1px"></div>
                            <div id="peticon_3" style="width: 25px; float: left"></div>
                            <div style="width: 210px; float: right">
                                <select data-placeholder="" name="edit_pet3_select" id="edit_pet3_select" class="chosen-select">
                                    <option value="1" <?php if ($strat->PetID3 == "1") echo "selected"; ?>>Any Pet</option>
                                    <option value="0" <?php if ($strat->PetID3 == "0") echo "selected"; ?>>Level Pet</option>
                                    <?php foreach ($all_special_pets as $thispet) { ?>
                                        <option value="<?php echo $thispet['PetID'] ?>" <?php if ($strat->PetID3 == $thispet['PetID']) echo "selected"; ?>><?php echo $thispet['Name'] ?></option>
                                    <?php }
                                    foreach ($all_normal_pets as $thispet) { ?>
                                        <option value="<?php echo $thispet['Species'] ?>" <?php if ($strat->PetID3 == $thispet['Species']) echo "selected"; ?>><?php echo $thispet['Name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div id="petdetails_3"></div>
                        </div>

                        <script>
                            $("#edit_pet1_select").chosen({width: 210, placeholder_text_single: 'Select a pet'});
                            $("#edit_pet2_select").chosen({width: 205, placeholder_text_single: 'Select a pet'});
                            $("#edit_pet3_select").chosen({width: 205, placeholder_text_single: 'Select a pet'});

                            function pull_petdetails(pet, slot, def, defpet) {
                                $('#petdetails_'+slot).empty();
                                $('#petdetails_'+slot).load('classes/ajax/strat_pullpet.php?pet='+pet+'&defpet='+defpet+'&def='+def+'&slot='+slot+'&lng=<?php echo $language ?>&strat=<?php echo $strat->id ?>');
                                $('#peticon_'+slot).empty();
                                $('#peticon_'+slot).load('classes/ajax/strat_pullpeticon.php?pet='+pet+'&slot='+slot+'&lng=<?php echo $language ?>');
                                $('#petfamilyicon_'+slot).empty();
                                $('#petfamilyicon_'+slot).load('classes/ajax/strat_pullpetfamily.php?pet='+pet+'&slot='+slot+'&lng=<?php echo $language ?>');
                            }

                            $("#edit_pet1_select").chosen().change(function(){
                                var pet = $('select[name=edit_pet1_select]').val();
                                pull_petdetails(pet, '1', '1', '<?php echo $strat->PetID1 ?>');
                            });

                            $("#edit_pet2_select").chosen().change(function(){
                                var pet = $('select[name=edit_pet2_select]').val();
                                pull_petdetails(pet, '2', '1', '<?php echo $strat->PetID2 ?>');
                            });

                            $("#edit_pet3_select").chosen().change(function(){
                                var pet = $('select[name=edit_pet3_select]').val();
                                pull_petdetails(pet, '3', '1', '<?php echo $strat->PetID3 ?>');
                            });

                            var u = $('select[name=edit_pet1_select]').val();
                            if (u != "") {
                                pull_petdetails(u, '1', '0', '-');
                            }
                            var p = $('select[name=edit_pet2_select]').val();
                            if (p != "") {
                                pull_petdetails(p, '2', '0', '-');
                            }
                            var t = $('select[name=edit_pet3_select]').val();
                            if (t != "") {
                                pull_petdetails(t, '3', '0', '-');
                            }
                        </script>
                    </td>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo">
                        <table>
                            <tr>
                                <td style="padding-left: 12px;">
                                    <input type="submit" class="comedit" value="<?php echo __("Save changes"); ?>">
                                </td>
                                <td style="padding-left: 15px;">
                                    <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
    var options = {
        hashTracking: false
    };
    $('[data-remodal-id=modal_edit_pets]').remodal();
    </script>

<?php }






// ===================== Edit Creator Comment =====================

if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {

    // Define position depending on substitutes or none
    if ($petslotinfo[1]['Subscount'] > "1" OR $petslotinfo[2]['Subscount'] > "1" OR $petslotinfo[3]['Subscount'] > "1") {
        $tabheight = "284";
    }
    else {
        $tabheight = "262";
    }
    ?>

    <div class="edit_info_linkbox" data-remodal-target="modal_edit_info" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_info_box_s edit_box_s" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_info_box edit_box" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_info_boxtext" style="top: <?php echo $tabheight+20; ?>">
        Info
    </div>

    <div class="remodal remodalstratedit" data-remodal-id="modal_edit_info">


    <table style="position: sticky; top:1px; z-index: 453599" class="profile">
        <tr class="profile">
            <td style="padding-left: 10px">
                <p class="blogodd">Format text:</p>
            </td>
            <?
            \BBCode\bboptions_simple('');
            echo '<td><p class="blogodd">Add elements:</p></td>';
            \BBCode\bboptions_url('');
            \BBCode\bboptions_pet('');
            \BBCode\bboptions_ability('');
            ?>

        </tr>
    </table>


    <table style="width: 670px" class="profile">
         <tr class="profile">
            <td class="collectionbordertwo">
                <div id="article_en_US" class="language_input">
                    <span id="article_lng" style="display: none">en_US</span>
                    <form method="post" style="display: inline">
                    <input type="hidden" name="alt_edit_action" value="edit_save_comment">
                    <textarea class="edit_article" id="article_ta_en_US" name="article_content_en_US" style="height: 300; width: 640; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','1000')" maxlength="1000"><?php echo stripslashes(htmlentities($strat->Comment, ENT_QUOTES, "UTF-8")); ?></textarea>
                </div>
            </td>
        </tr>
    </table>

    <table style="position: sticky;bottom:5px;z-index: 453599; width: 670px" class="profile">
        <tr class="profile">
            <td class="collectionbordertwo"><center>
                <table style="width: 100%">
                    <tr>
                        <td style="padding-right: 12px; padding-left: 12px; width: 30%; text-align: left">
                            <input type="submit" class="comsubmit" formaction="index.php?Strategy=<?php echo $strategy ?>" value="Save creator comment">
                        </td>
                        <td width: 40%; text-align: left">
                            <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                    </form>
                        </td>
                        <td style="width: 30%; padding-right: 12px; text-align: right">
                            <p class="smallodd" style="white-space: nowrap"><span style="padding-right: 15px" class="commbbcbright" id="rsp_remaining_rmg"></span>
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
    $('[data-remodal-id=modal_edit_info]').remodal(options);
</script>


<?
}


// ===================== Edit TD Scripts =====================

if ($userrights['EditStrats'] == "yes" OR $userrights['EditTDScripts'] == "yes" OR $strat->User == $user->id) { ?>

    <div class="edit_td_linkbox" data-remodal-target="modal_edit_td"></div>
    <div class="edit_td_box_s edit_box_s"></div>
    <div class="edit_td_box edit_box"></div>
    <div class="edit_td_boxtext">
        TD
    </div>

    <div class="remodal remodalstratedit" data-remodal-id="modal_edit_td">
        <form enctype="multipart/form-data" action="index.php?Strategy=<?php echo $strat->id ?>" method="POST">
            <input type="hidden" name="save_td" value="true">
            <table width="450" class="profile">
                <tr class="profile">
                    <th colspan="2" width="5" class="profile">
                        <table border="0">
                            <tr>
                                <td><img src="images/userdd_settings_grey.png"></td>
                                <td><img src="images/blank.png" width="5" height="1"></td>
                                <td><p class="blogodd"><span style="white-space: nowrap;"><b>Edit TD Script</span></td>
                            </tr>
                        </table>
                    </th>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo" colspan="2">
                        <textarea name="tdscript" class="cominputbright" onkeyup="auto_adjust_textarea_size(this);" style="height: 250px; max-height: 600px; width: 700px; overflow: auto"><?php echo htmlentities($strat->tdscript, ENT_QUOTES, "UTF-8") ?></textarea>
                    </td>
                </tr>

                <tr class="profile">
                    <td class="collectionbordertwo">
                        <table>
                            <tr>
                                <td style="padding-left: 12px;">
                                    <input type="submit" class="comedit" value="<?php echo __("Save changes"); ?>">
                                </td>
                                <td style="padding-left: 15px;">
                                    <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </form>
    </div>

	<script>
	var options = {
		hashTracking: false
	};
	$('[data-remodal-id=modal_edit_td]').remodal(options);
	</script>

<?php }



// ===================== Edit Tags =====================

if ($userrights['EditStrats'] == "yes" OR $userrights['EditTags'] == "yes" OR $strat->User == $user->id) {
    // Define position depending on substitutes or none
    if ($petslotinfo[1]['Subscount'] > "1" OR $petslotinfo[2]['Subscount'] > "1" OR $petslotinfo[3]['Subscount'] > "1") {
        $tabheight = "284";
    }
    else {
        $tabheight = "262";
    }
    ?>

    <div class="edit_tags_linkbox tagsedit_tooltip" data-tooltip-content="#edit_tags_tt" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_tags_box_s edit_box_s" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_tags_box edit_box" style="top: <?php echo $tabheight; ?>"></div>
    <div class="edit_tags_boxtext" style="top: <?php echo $tabheight-6; ?>">
        Tags
    </div>
        
       
    <div style="display: none;">
        <div id="edit_tags_tt" style="max-width: 350px">
            Click on a tag to activate or deactivate:<br><br>
                <?php 
                $listof_tags = $used_tags;
                sortBy('ID', $listof_tags, 'desc');
                foreach ($used_tags as $tag_id) {
                    if ($tag_id['Access'] == 1 OR ($tag_id['Access'] == 2 && ($userrights['EditStrats'] == "yes" OR $userrights['EditTags'] == "yes"))) { ?>
                        <div id="tag_editing_button_<?php echo $tag_id['ID']; ?>" onclick="change_tag(<?php echo $tag_id['ID']; ?>)" class="tag tag_tt <?php if ($tag_id['Active'] == 0) echo ' inactive_tag'; ?>" style="float: left; background-color: #<?php echo $tag_id['Color']; ?>" data-tooltip-content="#tag_edit_<?php echo $tag_id['Slug'] ?>_tt"><?php echo $tag_id['Name'] ?></div>
                        <div style="display: none">
                            <span id="tag_edit_<?php echo $tag_id['Slug'] ?>_tt"><?php echo $tag_id['Description'] ?></span>
                        </div>
                    <? }
                }
                ?>
                
                <script type = "text/javascript">
                    function change_tag(tag){
                        var xmlhttp = new XMLHttpRequest();
                        xmlhttp.onreadystatechange = function() {
                            if (this.readyState == 4 && this.status == 200) {
                                if (this.responseText == '1') {
                                    $('#tag_'+tag).show();
                                    $('#tag_editing_button_'+tag).removeClass('inactive_tag');
                                    if (tag == 22) {
                                        $('#tag_editing_button_21').addClass('inactive_tag');
                                        $('#tag_21').hide();
                                    }
                                }
                                if (this.responseText == '0') {
                                    $('#tag_'+tag).hide();
                                    $('#tag_editing_button_'+tag).addClass('inactive_tag');
                                }
                            }
                        };
                        xmlhttp.open("GET", "classes/ajax/bt_edit_tags.php?strat=" + encodeURIComponent('<?php echo $strat->id ?>')
                        + "&userid=" + encodeURIComponent('<?php echo $user->id ?>')
                        + "&tag=" + encodeURIComponent(tag)
                        + "&delimiter=" + encodeURIComponent('<?php echo $user->ComSecret ?>'), true);
                        xmlhttp.send();
                    }
                </script>
                
                
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.tagsedit_tooltip').tooltipster({
                 interactive: 'true',
                 animation: 'fade',
                 side: 'left',
                 width: '350',
                 trigger: 'click',
                 theme: 'tooltipster-smallnote'
            });
        });
    </script>
<?php }