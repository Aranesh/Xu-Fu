<?php
require_once ('HTTP.php');
require_once ('Database.php');

// Get type of import:
$col1_active = \HTTP\argument_POST_or_default ('col1_type_switch', FALSE);
$col2_active = \HTTP\argument_POST_or_default ('col2_type_switch', FALSE);
// Set default view to 1: user 2: manual
if ($col1_active == '') {
    if ($collection) {
    $col1_active = "own";
    }
    else $col1_active = "manual";
}
if ($col2_active == '') {
    $col2_active = "manual";
}

$command = \HTTP\argument_POST_or_default ('command', FALSE);

// Import details for Collection 1
$region_1 = \HTTP\argument_GET_or_default ('region_1', FALSE);
$realm_1 = \HTTP\argument_GET_or_default ('realm_1', FALSE);
$name_1 = \HTTP\argument_GET_or_default ('name_1', FALSE);
if ($region_1 && $realm_1 && $name_1) { 
    $splits_1[0] = $region_1;
    $splits_1[1] = $realm_1;
    $command = "test_import";
    $source_1 = 'url';
    $col1_active = "manual";
}

$user_1 = \HTTP\argument_GET_or_default ('user_1', FALSE);
if (preg_match("/^[1234567890]*$/is", $user_1) && $user_1) { 
    $source_1 = 'url';
    $command = "test_import";
    if ($user_1 == $user->id) {
        $col1_active = "own";
    }
    else {
        $col1_active = "user";
    }
}

// Import details for Collection 2
$region_2 = \HTTP\argument_GET_or_default ('region_2', FALSE);
$realm_2 = \HTTP\argument_GET_or_default ('realm_2', FALSE);
$name_2 = \HTTP\argument_GET_or_default ('name_2', FALSE);

if ($region_2 && $realm_2 && $name_2) {
    $splits_2[0] = $region_2;
    $splits_2[1] = $realm_2;
    $command = "test_import";
    $source_2 = 'url';
    $col2_active = "manual";
}
$user_2 = \HTTP\argument_GET_or_default ('user_2', FALSE);
if (preg_match("/^[1234567890]*$/is", $user_2) && $user_2) { 
    $source_2 = 'url';
    $command = "test_import";
    if ($user_2 == $user->id) {
        $col2_active = "own";
    }
    else {
        $col2_active = "user";
    }
}

// =============  Header with Page Title  ============= ?>

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><img src="https://www.wow-petguide.com/images/main_bg02_1.png"></td>
            <td width="100%"><h class="megatitle"><? echo _("Collection Comparator") ?></font></td>
            <td><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>

<div class="article">
    <div class="articlebottom"></div>
<div>
    <center>
    <p class="blogodd"><br>
    <br>

<?php

// Intro sentence on blank character selection
if (!$command) {
    //echo _("With this tool you can compare two pet collections against each other.")."<br><br>";
    // echo _("This tool is under development! It might not work properly at times.")."<br><br>";
}

// Loading screen
if ($command) { ?>
    <div id="loading">
        <table style="width:480px; margin-bottom: 40px" class="profile">
            <tr class="profile">
                <td class="profile">
                    <p class="blogodd">
                    <center><img src="images/loading.gif"><br><br><i><? echo _("FormSelectLoadText") ?></i><br></center>
                    <br><br>
                </td>
            </tr>
        </table>
    </div>
<? }

// Char details submitted
if ($command == "test_import") {

    // Collection 1 testing
    if ($col1_active == "own") {
        $collection = '';
        $getcol = update_collection($user->id,"1");
        if ($getcol[0] == "error")
        {
            $char1_error = true;
            $char1_errmsg = _("Your collection could not be loaded. Please reimport it through your collection tab");
        }
        else if ($getcol[0] == "success")
        {
            $findcol = find_collection($user);
            $fp = fopen($findcol['Path'], 'r');
            $collection = json_decode(fread($fp, filesize($findcol['Path'])), true);
            foreach ($collection as $key => $pet) {
                $collection[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);    
            }
            $petdata_1 = $collection;
            $col1_user = $user;
        }
    }
    if ($col1_active == "user") {
        if ($user_1) {
            $col1_user = $user_1;
        }
        else {
            $col1_user = \HTTP\argument_POST_or_default ('col1_user', FALSE);
        }
        if (!preg_match("/^[1234567890]*$/is", $col1_user) OR !$col1_user) {
            $char1_error = true;
            $char1_errmsg = _("User collection could not be loaded, please try again.");
            $col1_user = '';
        }
        else {
            $col1_user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$col1_user' LIMIT 1");
            if (mysqli_num_rows($col1_user_db) < "1") {
                $char1_error = true;
                $char1_errmsg = _("User collection could not be loaded, please try again.");
                $col1_user = '';
            }
            else {
                $col1_user = mysqli_fetch_object($col1_user_db);
                
                $getcol = update_collection($col1_user->id,"1");
                if ($getcol[0] == "error")
                {
                    $char1_error = true;
                    $char1_errmsg = _("User collection could not be loaded, please try again.");
                }
                else if ($getcol[0] == "success")
                {
                    $findcol = find_collection($col1_user);
                    $fp = fopen($findcol['Path'], 'r');
                    $petdata_1 = json_decode(fread($fp, filesize($findcol['Path'])), true);
                    foreach ($petdata_1 as $key => $pet) {
                        $petdata_1[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);    
                    }
                }
            }
        }
    }
    if ($col1_active == "manual") {
        if ($source_1 != 'url') { // Import from form unless source is URL
            $name_1 = \HTTP\argument_POST_or_default ('charname_1', FALSE);
            $post_realm_1 = \HTTP\argument_POST_or_default ('realm_1', FALSE);
            $name_1 = str_replace(' ', '', $name_1);
            $name_1 = strtolower($name_1);
            $splits_1 = explode("|", $post_realm_1);
            $region_1 = $splits_1[0];
            $realm_1 = $splits_1[1];
        }
        
        // Get data for Char 1
        $petdata_source_1 = blizzard_api_character_pets($region_1, $realm_1, $name_1);
        $chardata_summary_source_1 = blizzard_api_character_summary($region_1, $realm_1, $name_1, $language);
        $chardata_avatar_source_1 = blizzard_api_character_media($region_1, $realm_1, $name_1); 

        // Error handling
        if ($petdata_source_1 == "error" OR $chardata_summary_source_1 == "error" OR $chardata_avatar_source_1 == "error") {
            $char1_error = true;
            $char1_errmsg = _("Character details could not be imported.");
        }
        if ($char1_error != true) {
            $petdata_1 = prepare_collection($petdata_source_1);
            if ($petdata_1 == "empty") {
                $char1_error = true;
                $char1_errmsg = _("The data import worked but returned 0 pets. Either the account does not have any pets or there was an error on Blizzards side.");
            }
        }
    }
    
 
 
 
 
    // Collection 2 testing
    if ($col2_active == "own") {
        $collection = '';
        $getcol = update_collection($user->id,"1");
        if ($getcol[0] == "error")
        {
            $char2_error = true;
            $char2_errmsg = _("Your collection could not be loaded. Please reimport it through your collection tab");
        }
        else if ($getcol[0] == "success")
        {
            $findcol = find_collection($user);
            $fp = fopen($findcol['Path'], 'r');
            $collection = json_decode(fread($fp, filesize($findcol['Path'])), true);
            foreach ($collection as $key => $pet) {
                $collection[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);    
            }
            $petdata_2 = $collection;
            $col2_user = $user;
        } 
    }
    if ($col2_active == "user") {
        if ($user_2) {
            $col2_user = $user_2;
        }
        else {
            $col2_user = \HTTP\argument_POST_or_default ('col2_user', FALSE);
        }
        if (!preg_match("/^[1234567890]*$/is", $col2_user) OR !$col2_user) {
            $char2_error = true;
            $char2_errmsg = _("User collection could not be loaded, please try again.");
            $col2_user = '';
        }
        else {
            $col2_user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$col2_user' LIMIT 1");
            if (mysqli_num_rows($col2_user_db) < "1") {
                $char2_error = true;
                $char2_errmsg = _("User collection could not be loaded, please try again.");
                $col2_user = '';
            }
            else {
                $col2_user = mysqli_fetch_object($col2_user_db);
                
                $getcol = update_collection($col2_user->id,"1");
                if ($getcol[0] == "error")
                {
                    $char2_error = true;
                    $char2_errmsg = _("User collection could not be loaded, please try again.");
                }
                else if ($getcol[0] == "success")
                {
                    $findcol = find_collection($col2_user);
                    $fp = fopen($findcol['Path'], 'r');
                    $petdata_2 = json_decode(fread($fp, filesize($findcol['Path'])), true);
                    foreach ($petdata_2 as $key => $pet) {
                        $petdata_2[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);    
                    }
                }
            }
        }
    }
    if ($col2_active == "manual") {
        if ($source_2 != 'url') { // Import from form unless source is URL
            $name_2 = \HTTP\argument_POST_or_default ('charname_2', FALSE);
            $post_realm_2 = \HTTP\argument_POST_or_default ('realm_2', FALSE);
            $name_2 = str_replace(' ', '', $name_2);
            $name_2 = strtolower($name_2);
            $splits_2 = explode("|", $post_realm_2);
            $region_2 = $splits_2[0];
            $realm_2 = $splits_2[1];
        }
        // Get data for Char 2
        $petdata_source_2 = blizzard_api_character_pets($region_2, $realm_2, $name_2);
        $chardata_summary_source_2 = blizzard_api_character_summary($region_2, $realm_2, $name_2, $language);
        $chardata_avatar_source_2 = blizzard_api_character_media($region_2, $realm_2, $name_2); 
        
        if ($petdata_source_2 == "error" OR $chardata_summary_source_2 == "error" OR $chardata_avatar_source_2 == "error") {
            $char2_error = true;
            $char2_errmsg = _("Character details could not be imported.");
        }

        if ($char2_error != true) {
            $petdata_2 = prepare_collection($petdata_source_2);
            if ($petdata_2 == "empty") {
                $char2_error = true;
                $char2_errmsg = _("The data import worked but returned 0 pets. Either the account does not have any pets or there was an error on Blizzards side.");
            }
        }   
    } 

    if ($char1_error != true AND $char2_error != true) {
        $command = "output_comparison";
    }
}

    // If only one character is submitted via URL, skip testing of the others
    if (!$source_1 && $source_2 == 'url') {
        $char1_error = false;
        $command = '';
    }
    if ($source_1 == 'url' && !$source_2) {
        $char2_error = false;
        $command = '';
    }


// Standard form or problem with import
if ($char1_error == true OR $char2_error == true OR !$command) {
    
    $realmlistsdb = mysqli_query($dbcon, "SELECT * FROM Realms WHERE id = '1'") or die(mysqli_error($dbcon));
    $realmlists = mysqli_fetch_object($realmlistsdb);
    $realmsus = explode("*",$realmlists->US);
    $realmseu = explode("*",$realmlists->EU);
    $realmskr = explode("*",$realmlists->KR);
    $realmstw = explode("*",$realmlists->TW);
    ?>

    <form action="index.php?m=Compare" method="post">
    <input type="hidden" name="command" value="test_import">
    <input type="hidden" id="col1_type_switch" name="col1_type_switch" value="<? echo $col1_active; ?>">
    <input type="hidden" id="col2_type_switch" name="col2_type_switch" value="<? echo $col2_active; ?>">
    
    <div id="charform" style="margin: 0 auto; max-width: 600px; display: table">
        <div class="form_block" style="min-width: 238px; float: left<?
        if ($char1_error == true) echo '; border: 5px solid #d67676';
        if ($char1_error != true && $char2_error == true) echo '; border: 5px solid #4aa838';
        ?>">
            <div style="width: 100%; padding: 0 0 5 5">
                <? if ($collection) { ?>
                <button id="col1_own" type="button" tabindex="1" class="lb_region_switch <? if ($col1_active == "own") echo "lb_region_switch_active"; ?>" onclick="event.preventDefault(); col_switch('1', 'own')">Own</button>
                <? } ?>
                <button id="col1_user" type="button" tabindex="2" class="lb_region_switch <? if ($col1_active == "user") echo "lb_region_switch_active"; ?>" onclick="event.preventDefault(); col_switch('1', 'user')">User</button>
                <button id="col1_manual" type="button" tabindex="4" class="lb_region_switch <? if ($col1_active == "manual") echo "lb_region_switch_active"; ?>" onclick="event.preventDefault(); col_switch('1', 'manual')">Manual Character</button>
            </div>
            
            <? if ($collection) { ?>
            <div id="input_col1_own" <? if ($col1_active != "own") echo 'style="display:none"'; ?>>
                <? if (!$bnetuser OR $user->UseWoWForCol == 1) {
                    $charstring = $user->CharName.' '.$user->CharRealmFull.'-'.strtoupper($user->CharRegion);
                }
                if ($bnetuser AND $user->UseWoWForCol != 1) {
                    $show_realm = $bnetuser->CharRealmFull;
                    if (!$bnetuser->CharRealmFull) {
                        $show_realm = ucfirst($bnetuser->CharRealm);
                    }
                    $charstring = $bnetuser->CharName.' '.$show_realm.'-'.strtoupper($bnetuser->Region);
                } ?>
                <div style="margin-left: 5px; font-size: 16px; line-height: 24px">
                    <? echo $charstring; ?><br>
                    <b>Pets</b>: <? echo count($collection); ?>
                </div>

                <? if ($col1_active == "own" && $char1_error == TRUE) { ?>
                    <div style="max-width: 230; margin: 5 0 0 5; font-size: 14px; color: #831010; font-weight: bold">
                        <? echo $char1_errmsg; ?>
                    </div>
                <? } ?>
            </div>
            <? } ?>

            <div id="input_col1_user" tabindex="3" <? if ($col1_active != "user") echo 'style="display:none"'; ?>>
                <table>
                    <tr>
                        <td>
                            <select data-placeholder="Enter Username" id="username_1" name="col1_user" class="chosen-select_username_one test" <? if ($col1_active == "user") echo 'required'; ?>>
                                <option value=""></option>
                                <? if ($col1_user) { ?>
                                    <option value="<? echo $col1_user->id ?>" selected><? echo $col1_user->Name ?></option>
                                <? } ?>
                             </select>

                            <script type = "text/javascript">
                                $("#username_1").chosen({width: 230, allow_single_deselect: true, search_contains: true});
                            </script>
                        </td>
                    </tr>
                    
                <? if ($col1_active == "user" && $char1_error == TRUE) { ?>
                    <tr>
                        <td style="max-width: 230; font-size: 14px; color: #831010; font-weight: bold">
                            <? echo $char1_errmsg; ?>
                        </td>
                    </tr>
                <? } ?>
                    
                </table>
                
                <script>
                    var x_timer;
                    $('#username_1_chosen').find('.chosen-search-input').on('input',function(e){
                        var searchterm = $('#username_1_chosen').find('.chosen-search-input').val();
                        clearTimeout(x_timer);
                        x_timer = setTimeout(function(){
                            $(".no-results").text("<? echo _("PM_searching") ?>");
                            if (searchterm.length >= '2') {
                                clearTimeout(x_timer);
                                x_timer = setTimeout(function(){
                                    $("#username_1").empty();
                                    var xmlhttp = new XMLHttpRequest();
                                    xmlhttp.onreadystatechange = function() {
                                        if (this.readyState == 4 && this.status == 200) {
                                            if (this.responseText == "[]") {
                                                $(".no-results").text("<? echo _("No user with a collection found") ?>");
                                            }
                                            else {
                                                var data = this.responseText;
                                                data = JSON.parse(data);
                                                $.each(data, function (idx, obj) {
                                                    $("#username_1").append('<option value="' + obj.id + '">' + obj.text + '</option>');
                                                });
                                                $("#username_1").trigger("chosen:updated");
                                                // $('[class=chosen-search-input]:eq(0)').val(searchterm);
                                            }
                                        }
                                    };
                                    xmlhttp.open("GET", "classes/ajax/select_user_no_col.php?q=" + encodeURIComponent(searchterm), true);
                                    xmlhttp.send();
                                }, 1000);
                            }
                            else {
                                clearTimeout(x_timer);
                                x_timer = setTimeout(function(){
                                    $("#username_1").empty();
                                    $(".no-results").text("<? echo _("PM_ErrTooShort") ?>");
                                }, 300);
                            }
                        }, 200);
                    });
                </script>
            </div>

            <div id="input_col1_manual" <? if ($col1_active != "manual") echo 'style="display:none"'; ?>>
                <table>
                    <tr>
                        <td>
                            <select tabindex="6" width="230" data-placeholder="<? echo _("Realm") ?>" name="realm_1" id="realm_select_1" class="chosen-select" <? if ($col1_active == "manual") echo 'required'; ?>>
                                <option value=""></option>
                                    <optgroup label="<? echo _("FormSelectRealmUS") ?>">
                                        <? foreach($realmsus as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_1[0]."|".$splits_1[1] == "us|".$thisrealm[0]) {
                                                echo '<option selected value="us|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="us|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup label="<? echo _("FormSelectRealmEU") ?>">
                                        <?
                                        foreach($realmseu as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_1[0]."|".$splits_1[1] == "eu|".$thisrealm[0]) {
                                                echo '<option selected value="eu|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="eu|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup label="<? echo _("FormSelectRealmKR") ?>">
                                        <?
                                        foreach($realmskr as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_1[0]."|".$splits_1[1] == "kr|".$thisrealm[0]) {
                                                echo '<option selected value="kr|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="kr|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup label="<? echo _("FormSelectRealmTW") ?>">
                                        <?
                                        foreach($realmstw as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_1[0]."|".$splits_1[1] == "tw|".$thisrealm[0]) {
                                                echo '<option selected value="tw|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="tw|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input value="<? echo ucfirst(htmlentities($name_1, ENT_QUOTES, "UTF-8")); ?>" class="petselect" name="charname_1" id="char_input_1"  tabindex="7" placeholder="<? echo _("FormSelectCharName") ?>" style="width: 230px;" width="230" <? if ($col1_active == "manual") echo 'required'; ?>>
                        </td>
                    </tr>
                    <? if ($col1_active == "manual" && $char1_error == TRUE) { ?>
                    <tr>
                        <td style="max-width: 230; font-size: 14px; color: #831010; font-weight: bold">
                            <? echo $char1_errmsg; ?>
                        </td>
                    </tr>
                    <? } ?>
                </table>
            </div>
        </div>

        
        
        
        
        <div style="float: left; margin: 40 20 0 20"><p class="blogodd"><b>VS</b></p></div>






        <div class="form_block" style="min-width: 238px; float: left<?
        if ($char2_error == true) echo '; border: 5px solid #d67676';
        if ($char2_error != true && $char1_error == true) echo '; border: 5px solid #4aa838';
        ?>">
            <div style="width: 100%; padding: 0 0 5 5">
                <? if ($collection) { ?>
                <button id="col2_own" type="button" tabindex="10" class="lb_region_switch <? if ($col2_active == "own") echo "lb_region_switch_active"; ?>" onclick="event.preventDefault(); col_switch('2', 'own')">Own</button>
                <? } ?>
                <button id="col2_user" type="button" tabindex="11" class="lb_region_switch <? if ($col2_active == "user") echo "lb_region_switch_active"; ?>" onclick="event.preventDefault(); col_switch('2', 'user')">User</button>
                <button id="col2_manual" type="button" tabindex="12" class="lb_region_switch <? if ($col2_active == "manual") echo "lb_region_switch_active"; ?>" onclick="event.preventDefault(); col_switch('2', 'manual')">Manual Character</button>
            </div>
            
            <? if ($collection) { ?>
            <div id="input_col2_own" <? if ($col2_active != "own") echo 'style="display:none"'; ?>>
                <? if (!$bnetuser OR $user->UseWoWForCol == 1) {
                    $charstring = $user->CharName.' '.$user->CharRealmFull.'-'.strtoupper($user->CharRegion);
                }
                if ($bnetuser AND $user->UseWoWForCol != 1) {
                    $show_realm = $bnetuser->CharRealmFull;
                    if (!$bnetuser->CharRealmFull) {
                        $show_realm = ucfirst($bnetuser->CharRealm);
                    }
                    $charstring = $bnetuser->CharName.' '.$show_realm.'-'.strtoupper($bnetuser->Region);
                } ?>
                <div style="margin-left: 5px; font-size: 16px; line-height: 24px">
                    <? echo $charstring; ?><br>
                    <b>Pets</b>: <? echo count($collection); ?>
                </div>

                <? if ($col2_active == "own" && $char2_error == TRUE) { ?>
                    <div style="max-width: 230; margin: 5 0 0 5; font-size: 14px; color: #831010; font-weight: bold">
                        <? echo $char2_errmsg; ?>
                    </div>
                <? } ?>
            </div>
            <? } ?>

            <div id="input_col2_user" <? if ($col2_active != "user") echo 'style="display:none"'; ?>>
                <table>
                    <tr>
                        <td>
                            <select data-placeholder="Enter Username" id="username_2" tabindex="13" name="col2_user" class="chosen-select_username_one" <? if ($col2_active == "user") echo 'required'; ?>>
                                <option value=""></option>
                                <? if ($col2_user) { ?>
                                    <option value="<? echo $col2_user->id ?>" selected><? echo $col2_user->Name ?></option>
                                <? } ?>
                             </select>

                            <script type = "text/javascript">
                                $("#username_2").chosen({width: 230, allow_single_deselect: true, search_contains: true});
                            </script>
                        </td>
                    </tr>
                    
                <? if ($col2_active == "user" && $char2_error == TRUE) { ?>
                    <tr>
                        <td style="max-width: 230; font-size: 14px; color: #831010; font-weight: bold">
                            <? echo $char2_errmsg; ?>
                        </td>
                    </tr>
                <? } ?>
                    
                </table>
                
                <script>
                    var x_timer;
                    $('#username_2_chosen').find('.chosen-search-input').on('input',function(e){
                        var searchterm = $('#username_2_chosen').find('.chosen-search-input').val();
                        clearTimeout(x_timer);
                        x_timer = setTimeout(function(){
                            $(".no-results").text("<? echo _("PM_searching") ?>");
                            if (searchterm.length >= '2') {
                                clearTimeout(x_timer);
                                x_timer = setTimeout(function(){
                                    $("#username_2").empty();
                                    var xmlhttp = new XMLHttpRequest();
                                    xmlhttp.onreadystatechange = function() {
                                        if (this.readyState == 4 && this.status == 200) {
                                            if (this.responseText == "[]") {
                                                $(".no-results").text("<? echo _("No user with a collection found") ?>");
                                            }
                                            else {
                                                var data = this.responseText;
                                                data = JSON.parse(data);
                                                $.each(data, function (idx, obj) {
                                                    $("#username_2").append('<option value="' + obj.id + '">' + obj.text + '</option>');
                                                });
                                                $("#username_2").trigger("chosen:updated");
                                                // $('[class=chosen-search-input]:eq(1)').val(searchterm);
                                            }
                                        }
                                    };
                                    xmlhttp.open("GET", "classes/ajax/select_user_no_col.php?q=" + encodeURIComponent(searchterm), true);
                                    xmlhttp.send();
                                }, 1000);
                            }
                            else {
                                clearTimeout(x_timer);
                                x_timer = setTimeout(function(){
                                    $("#username_2").empty();
                                    $(".no-results").text("<? echo _("PM_ErrTooShort") ?>");
                                }, 300);
                            }
                        }, 200);
                    });
                </script>
            </div>

            <div id="input_col2_manual" <? if ($col2_active != "manual") echo 'style="display:none"'; ?>>
                <table>
                    <tr>
                        <td>
                            <select width="230" data-placeholder="<? echo _("Realm") ?>" name="realm_2" id="realm_select_2" class="chosen-select" tabindex="8" <? if ($col2_active == "manual") echo 'required'; ?>>
                                <option value=""></option>
                                    <optgroup label="<? echo _("FormSelectRealmUS") ?>">
                                        <? foreach($realmsus as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_2[0]."|".$splits_2[1] == "us|".$thisrealm[0]) {
                                                echo '<option selected value="us|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="us|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup label="<? echo _("FormSelectRealmEU") ?>">
                                        <?
                                        foreach($realmseu as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_2[0]."|".$splits_2[1] == "eu|".$thisrealm[0]) {
                                                echo '<option selected value="eu|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="eu|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup label="<? echo _("FormSelectRealmKR") ?>">
                                        <?
                                        foreach($realmskr as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_2[0]."|".$splits_2[1] == "kr|".$thisrealm[0]) {
                                                echo '<option selected value="kr|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="kr|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup label="<? echo _("FormSelectRealmTW") ?>">
                                        <?
                                        foreach($realmstw as $key => $value) {
                                            $thisrealm = explode("|",$value);
                                            if ($splits_2[0]."|".$splits_2[1] == "tw|".$thisrealm[0]) {
                                                echo '<option selected value="tw|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                            else {
                                                echo '<option value="tw|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                                            }
                                        }
                                        ?>
                                    </optgroup>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input value="<? echo ucfirst(htmlentities($name_2, ENT_QUOTES, "UTF-8")); ?>" class="petselect" name="charname_2" id="char_input_2"  tabindex="9" placeholder="<? echo _("FormSelectCharName") ?>" style="width: 230px;" width="230" <? if ($col2_active == "manual") echo 'required'; ?>>
                        </td>
                    </tr>
                    <? if ($col2_active == "manual" && $char2_error == TRUE) { ?>
                    <tr>
                        <td style="max-width: 230; font-size: 14px; color: #831010; font-weight: bold">
                            <? echo $char2_errmsg; ?>
                        </td>
                    </tr>
                    <? } ?>
                </table>
            </div>
        </div>





 
        <div style="display: table; width: 100%">
            <center><button style="margin-top: 20px" type="submit" tabindex="14" class="bnetlogin"><? echo _("Compare Collections") ?></button></center>
        </div>
    
        </form>
    </div>
    
    <script>
        $(".chosen-select").chosen({width: 230, allow_single_deselect: true});
        function col_switch(col, area) {
            $('#input_col'+col+'_own').hide(500);
            $('#input_col'+col+'_user').hide(500);
            $('#input_col'+col+'_manual').hide(500);
            $('#input_col'+col+'_'+area).show(500);
            $('#col'+col+'_own').removeClass('lb_region_switch_active');
            $('#col'+col+'_user').removeClass('lb_region_switch_active');
            $('#col'+col+'_manual').removeClass('lb_region_switch_active');
            $('#col'+col+'_'+area).addClass('lb_region_switch_active');
            $('#col'+col+'_type_switch').val(area);
            if (area == 'own') {
                document.getElementById('username_'+col).removeAttribute("required");
                document.getElementById('realm_select_'+col).removeAttribute("required");
                document.getElementById('char_input_'+col).removeAttribute("required");
            }
            if (area == 'user') {
                $('#username_'+col).prop('required',true);
                document.getElementById('realm_select_'+col).removeAttribute("required");
                document.getElementById('char_input_'+col).removeAttribute("required");
            }
            if (area == 'manual') {
                document.getElementById('username_'+col).removeAttribute("required");
                $('#realm_select_'+col).prop('required',true);
                $('#char_input_'+col).prop('required',true);
            }
        }
        document.getElementById('loading').style.display='none';
        document.getElementById('charform').style.display='table';
    </script>
<? }



// Output of Collection Comparison
if ($char1_error != true AND $char2_error != true AND $command == "output_comparison") {
    if ($col1_active == 'own') {
        $col1_avatar = $usericon;
    }
    if ($col1_active == 'user') {
        if ($col1_user->UseWowAvatar == "0"){
            $col1_avatar = 'src="https://www.wow-petguide.com/images/pets/'.$col1_user->Icon.'.png"';
        }
        else if ($col1_user->UseWowAvatar == "1"){
            $col1_avatar = 'src="https://www.wow-petguide.com/images/userpics/'.$col1_user->id.'.jpg?lastmod?='.$col1_user->IconUpdate.'"';
        }
    }
    if ($col1_active == 'manual') {
        $chardata_summary_1 = json_decode($chardata_summary_source_1, TRUE);  
        $chardata_avatar_1 = json_decode($chardata_avatar_source_1, TRUE);
    }
    if ($col2_active == 'own') {
        $col2_avatar = $usericon;
    }
    if ($col2_active == 'user') {
        if ($col2_user->UseWowAvatar == "0"){
            $col2_avatar = 'src="https://www.wow-petguide.com/images/pets/'.$col2_user->Icon.'.png"';
        }
        else if ($col2_user->UseWowAvatar == "1"){
            $col2_avatar = 'src="https://www.wow-petguide.com/images/userpics/'.$col2_user->id.'.jpg?lastmod?='.$col2_user->IconUpdate.'"';
        }
    }
    if ($col2_active == 'manual') {
        $chardata_summary_2 = json_decode($chardata_summary_source_2, TRUE);  
        $chardata_avatar_2 = json_decode($chardata_avatar_source_2, TRUE);
    }
    
    
    // Get region to display for usernames
    
    if ($col1_active == 'own' OR $col1_active == 'user') {
        if ($col1_user->UseWoWForCol == 1) {
            $col1_region = strtoupper($col1_user->CharRegion);
        }
        else {
            $bnetuser_1_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$col1_user->id'");
            if (mysqli_num_rows($bnetuser_1_db) > "0") {
                $bnetuser_1 = mysqli_fetch_object($bnetuser_1_db);
                $col1_region = strtoupper($bnetuser_1->Region);
            }
            else {
                $col1_region = strtoupper($col1_user->CharRegion);
            }
        }
    }
    
    if ($col2_active == 'own' OR $col2_active == 'user') {
        if ($col2_user->UseWoWForCol == 1) {
            $col2_region = strtoupper($col2_user->CharRegion);
        }
        else {
            $bnetuser_2_db = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$col2_user->id'");
            if (mysqli_num_rows($bnetuser_2_db) > "0") {
                $bnetuser_2 = mysqli_fetch_object($bnetuser_2_db);
                $col2_region = strtoupper($bnetuser_2->Region);
            }
            else {
                $col2_region = strtoupper($col2_user->CharRegion);
            }
        }
    }
    ?>
    
    <div style="display:none" id="collection">
        <table style="width:1000">
            <tr>
                <td style="width:100%">
                    <table style="width: 100%" cellspacing="0" cellpadding="0" class="profile">
                        <tr class="profile">
                            <td class="profile" width="30" style="background-color: #aabad3; padding: 4 0 2 5">
                                <? if ($col1_active == 'own' OR $col1_active == 'user') { ?>
                                    <span class="username" style="text-decoration: none" rel="<? echo $col1_user->id ?>" value="<? echo $user->id ?>">
                                        <img width="84" height="84" <? echo $col1_avatar ?>>
                                    </span>
                                <? }
                                if ($col1_active == 'manual') { ?>
                                    <a target="_blank" href="https://worldofwarcraft.com/en-us/character/<? echo $region_1 ?>/<? echo $realm_1 ?>/<? echo $name_1 ?>">
                                        <img width="84" height="84" src="<? echo $chardata_avatar_1['avatar_url'] ?>">
                                    </a>
                                <? } ?>
                            </td>
                            <td style="align:left; padding-left:15px; width:450px; background-color: #aabad3">
                                <? if ($col1_active == 'own' OR $col1_active == 'user') { ?>
                                    <span class="username" style="text-decoration: none" rel="<? echo $col1_user->id ?>" value="<? echo $user->id ?>">
                                        <a href="?user=<? echo $col1_user->id ?>" style="font-size: 20px; font-family: MuseoSans-500,Roboto;" class="creatorlink"><? echo $col1_user->Name ?> - <? echo $col1_region ?></a>
                                    </span>
                                <? }
                                if ($col1_active == 'manual') {
                                    echo '<h3 class="collection">'.$chardata_avatar_1['character']['name'].' - '.$chardata_summary_1['realm']['name'].'-'.strtoupper($region_1).'<br>';
                                    echo 'Level '.$chardata_summary_1['level'].' '.lookup_char_race($chardata_summary_1['race']['id']).' '.lookup_char_class($chardata_summary_1['character_class']['id']).'</h3>';
                                } ?>
                            </td>
                    
                            <td style="width: 150px" class="comparison_vs">
                                <center><h3 class="collection">VS</h3></center>
                            </td>

                            <td style="text-align:right; padding-right:15px; width:450px; background-color: #aec3a7">
                                <? if ($col2_active == 'own' OR $col2_active == 'user') { ?>
                                    <span class="username" style="text-decoration: none" rel="<? echo $col2_user->id ?>" value="<? echo $user->id ?>">
                                        <a href="?user=<? echo $col2_user->id ?>" style="font-size: 20px; font-family: MuseoSans-500,Roboto;" class="creatorlink"><? echo $col2_user->Name ?> - <? echo $col2_region ?></a>
                                    </span>
                                <? }
                                if ($col2_active == 'manual') {
                                    echo '<h3 class="collection">'.$chardata_avatar_2['character']['name'].' - '.$chardata_summary_2['realm']['name'].'-'.strtoupper($region_2).'<br>';
                                    echo 'Level '.$chardata_summary_2['level'].' '.lookup_char_race($chardata_summary_2['race']['id']).' '.lookup_char_class($chardata_summary_2['character_class']['id']).'</h3>';
                                } ?>
                            </td>
                            
                            <td class="profile" width="30" style="background-color: #aec3a7; padding: 4 5 2 0">
                                <? if ($col2_active == 'own' OR $col2_active == 'user') { ?>
                                    <span class="username" style="text-decoration: none" rel="<? echo $col2_user->id ?>" value="<? echo $user->id ?>">
                                        <img width="84" height="84" <? echo $col2_avatar ?>>
                                    </span>
                                <? }
                                if ($col2_active == 'manual') { ?>
                                    <a target="_blank" href="https://worldofwarcraft.com/en-us/character/<? echo $region_2 ?>/<? echo $realm_2 ?>/<? echo $name_2 ?>">
                                        <img width="84" height="84" src="<? echo $chardata_avatar_2['avatar_url'] ?>">
                                    </a>
                                <? } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr><td>
                <?
                if ($col1_active == 'user') $chardata_avatar_1['character']['name'] = $col1_user->Name;
                if ($col1_active == 'own') $chardata_avatar_1['character']['name'] = $user->Name;
                if ($col2_active == 'user') $chardata_avatar_2['character']['name'] = $col2_user->Name;
                if ($col2_active == 'own') $chardata_avatar_2['character']['name'] = $user->Name;
                
                if (!$col1_user) $user_1_id = 0;
                else $user_1_id = $col1_user->id;
                if (!$col2_user) $user_2_id = 0;
                else $user_2_id = $col2_user->id;
                if (!$user) $userid = 0;
                else $userid = $user->id;
                
                Database_INSERT_INTO
                      ( 'Pet_Comparisons'
                      , [ 'User'
                        , 'IP'
                        , 'User_1'
                        , 'User_2'
                        , 'Region_1'
                        , 'Realm_1'
                        , 'Name_1'
                        , 'Region_2'
                        , 'Realm_2'
                        , 'Name_2'
                        ]
                      , 'isiissssss'
                        , $userid
                        , $user_ip_adress
                        , $user_1_id
                        , $user_2_id
                        , $region_1
                        , $realm_1
                        , $name_1
                        , $region_2
                        , $realm_2
                        , $name_2
                      );
                print_collection_comparison($petdata_1,$petdata_2,$chardata_avatar_1,$chardata_avatar_2);  ?>
            </td>
            </tr>

        </table>
        <script>
            document.getElementById('loading').style.display='none';
            document.getElementById('collection').style.display='block';
            <? // URL changer
            if ($col1_active == 'user' OR $col1_active == 'own') {
                $url_part1 = '&user_1='.$col1_user->id;
            }
            if ($col1_active == 'manual') {
                $url_part1 = '&region_1='.$region_1.'&realm_1='.$realm_1.'&name_1='.$name_1;
            }
            if ($col2_active == 'user' OR $col2_active == 'own') {
                $url_part2 = '&user_2='.$col2_user->id;
            }
            if ($col2_active == 'manual') {
                $url_part2 = '&region_2='.$region_2.'&realm_2='.$realm_2.'&name_2='.$name_2;
            } ?>
           window.history.replaceState("object or string", "Title", "?m=Compare<? echo $url_part1.$url_part2 ?>");
        </script>
    <?
    }
    ?>
<br><br>
</div>


<br>
<div class="maincomment">
    <br>
    <table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
    <tr><td width="100%" align="center">
    <br><br>
    <?
    
    // ==== COMMENT SYSTEM 2.0 FOR MAIN ARTICLES HAPPENS HERE ====
    print_comments_outer("0",$mainselector,"medium");
echo "</div>";




