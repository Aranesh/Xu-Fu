<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('BattleNet.php');
require_once ('Database.php');
require_once ('Growl.php');
require_once ('HTML.php');
require_once ('HTTP.php');
require_once ('Util.php');

$command = \HTTP\argument_POST_or_GET_or_default ('command', FALSE);

if ($command) {
    $collection = '';
}

$realmlistsdb = mysqli_query($dbcon, "SELECT * FROM Realms WHERE id = '1'") or die(mysqli_error($dbcon));
$realmlists = mysqli_fetch_object($realmlistsdb);
$realmsus = explode("*",$realmlists->US);
$realmseu = explode("*",$realmlists->EU);
$realmskr = explode("*",$realmlists->KR);
$realmstw = explode("*",$realmlists->TW);

// ==================== UPDATE COLLECTION ====================
if ($command == "update") {
    $getcol = update_collection($user->id,"1");

    if ($getcol[0] == "error")
    {
        $command = "";
        if ($getcol[1] == 'bnetcharincorrect' OR $getcol[1] == 'generic_import_error')
        {
            echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_ColUpdateError").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
        if ($getcol[1] == 'no_pets_in_import')
        {
            echo '<script type="text/javascript">$.growl.error({ message: "'._("The data import worked but returned 0 pets. Either your account does not have any pets or there was an error on Blizzards side. Please try a different character or change the region of your account in the settings.").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
    }
    else if ($getcol[0] == "success")
    {
        $findcol = find_collection($user);
        if ($findcol != "No Collection") {
            $fp = fopen($findcol['Path'], 'r');
            $collection = json_decode(fread($fp, filesize($findcol['Path'])), true);
            echo '<script type="text/javascript">$.growl.notice({ message: "'._("GR_ColUpdate").'", duration: "5000", size: "large", location: "tc" });</script>';
        }
        else {
            echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_ColUpdateError").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
    }
}

// ==================== DELETE COLLECTION DATA ====================
if ($command == "delete_collection") {
    delete_collection($user->id);
    $collection = '';
    $command = '';
}





// =============  Header with Page Title  ============= ?>
<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><img src="images/blank.png" width="50" height="1" alt="" /></td>
            <td><img class="ut_icon" width="84" height="84" <? echo $usericon ?>></td>
            <td><img src="images/blank.png" width="50" height="1" alt="" /></td>
            <td width="100%"><h class="megatitle"><? echo _("UM_PetCollection") ?></h></td>
            <td><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>




<? // ============= Submenu with Collection selected  ============= ?>
<div class="remodal-bg leftmenu">
    <? print_profile_menu('col'); ?>

    <? // ============= Page 1 - Pet Collection is found  ============= ?>
    <? if ($collection) { ?>
    <center>

    <? // ============= Sub Menu Option: Show Last Update and Option to Refresh =============  ?>
    <div class="profile">
        <b><? echo _("PC_LastUpdate") ?>:</b>
        <span name="time"><? echo $findcol['Date']; ?></span><br>
        <form class="form-style-even" style="display: inline" action="index.php?page=collection" method="post">
            <input type="hidden" name="command" value="update">
            <input type="submit" style="margin: 5 0 5 10;" value="Update Now">
        </form>
    </div>

    <? // ============= Connected Character =============  ?>
    <? if (!$bnetuser OR $user->UseWoWForCol == 1) {
        $charstring = $user->CharName.' '.$user->CharRealmFull.'-'.strtoupper($user->CharRegion);
        $update_command = 'change_wow_char';
    }
    if ($bnetuser AND $user->UseWoWForCol != 1) {
        $update_command = 'show_bnet_chars';
        $show_realm = $bnetuser->CharRealmFull;
        if (!$bnetuser->CharRealmFull) {
            $show_realm = ucfirst($bnetuser->CharRealm);
        }
        $charstring = $bnetuser->CharName.' '.$show_realm.'-'.strtoupper($bnetuser->Region);
    } ?>
    <div class="profile">
        <b><? echo _("Connected Character") ?>:</b><br>
        <? echo $charstring ?>
        <form class="form-style-even" style="display: inline" action="index.php?page=collection" method="post">
            <input type="hidden" name="command" value="<? echo $update_command ?>">
            <input type="submit" style="margin: 5 0 5 10;" value="Change character">
        </form>
    </div>

    <? // =============  Sharing option ============= ?>
    <div class="profile">
        <b>Share your collection:</b><br>
        <a style="cursor: pointer" id="cb_share_profile" data-clipboard-text="https://wow-petguide.com?user=<? echo $user->id ?>&display=Collection"><img class="icon_share" src="images/icon_share.png"></a>
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
    </div>

    <div class="profile">
        <form class="form-style-even" style="display: inline" action="index.php?page=collection" method="post">
            <input type="hidden" name="command" value="delete_collection">
            <button type="submit" style="margin: 5 0 5 10;" class="comdelete"><? echo _("Remove collection"); ?></button>
        </form>
    </div>
    
</div>

    <? // =============  Display Collection ============= ?>

   
<? }
echo '</div>';
// END OF LEFT MENU







// =============  Main Page - Menu structure with no collection saved   =============
if (!$collection) {  ?>
    <div class="blogentryfirst">
    <div class="articlebottom"></div>
    
    
    <? // Loading screen
    if ($command) { ?>
        <div id="loading" style="margin-left: 260px">
            <table style="width:480px; margin: 0 auto" class="profile">
                <tr class="profile">
                    <td class="profile">
                        <p class="blogodd">
                        <center><img src="images/loading.gif"><br><br><i><? echo _("Loading...") ?></i><br></center>
                        <br><br>
                    </td>
                </tr>
            </table>
            <br>
            <br>
            <br>
        </div>
    <? }

    if ($command == 'retry_character' OR $command == 'set_character') {
        if ($command == 'set_character') {
            $new_charname = \HTTP\argument_POST_or_default ('charname', FALSE);
            $new_charslug = \HTTP\argument_POST_or_default ('charslug', FALSE);
            $new_charrealm = \HTTP\argument_POST_or_default ('charrealm', FALSE);
            $new_charlevel = \HTTP\argument_POST_or_default ('charlevel', FALSE);
            $new_charrace = \HTTP\argument_POST_or_default ('charrace', FALSE);
            $new_charclass = \HTTP\argument_POST_or_default ('charclass', FALSE);
            Database_UPDATE
              ( 'UserBnet'
              , [ 'CharRealm'
                , 'CharRealmFull'
                , 'CharName'
                , 'CharLevel'
                , 'CharClass'
                , 'CharRace'
                ]
              , 'WHERE User = ?'
              , 'sssiiii'
                , $new_charslug
                , $new_charrealm
                , $new_charname
                , $new_charlevel
                , $new_charclass
                , $new_charrace
              , $user->id
              );
              
              Database_UPDATE
              ( 'Users'
              , [ 'UseWoWForCol' ]
              , 'WHERE id = ?'
              , 'ii'
                , '0'
              , $user->id
              );
            $bnetdb = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id' LIMIT 1");
            $bnetuser = mysqli_fetch_object($bnetdb);
            $charstring = $new_charname.' '.$new_charrealm.'-'.strtoupper($bnetuser->Region);
            echo '<script type="text/javascript">$.growl.notice({ message: "'._("Character set to: ").$charstring.'", duration: "7000", size: "large", location: "tc" });</script>'; 
        }
        
        $getcol = update_collection($user->id,"1");

        if ($getcol[0] == "error")
        {
            $command = 'loadchars';
            $error_msg = 'generic_error';
            if ($getcol[1] == 'no_pets_in_import')
            {   
                $error_msg = 'zero_pets';
            }
        }
        else if ($getcol[0] == "success")
        {
            mysqli_close($dbcon);
            ?>
            <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=import_success">
            <?
            die;
        }
    }



    // Import characters from Battle.net to display only, do not set any character (was done above)
    if ($command == 'show_bnet_chars') {
        $charlist = [];
        
        try
        {
            $oauth = new \BattleNet\OAuth ($bnetuser->Region, 'show_bnet_chars', '/index.php?page=collection');

            if (!$oauth->is_authed)
            {
              \HTTP\redirect_and_die ($oauth->auth_url());
            }
            
            if ($oauth->fetch ('account')['id'] != $bnetuser->BnetID)
            {
                mysqli_close($dbcon);
                ?>
                <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=wrong_bnet">
                <?
                die;
            }
            
            // Import 
            $wowcharsinfo = $oauth->fetch ('wowprofile');
        
            // Go through all characters and add to an array
            $all_chars = array();
            $count_chars = 0;
            foreach ($wowcharsinfo['wow_accounts'] as $account) {
                foreach ($account['characters'] as $char) {
                  $all_chars[$count_chars]['Name'] = $char['name'];
                  $all_chars[$count_chars]['Realm'] = $char['realm']['name'];
                  $all_chars[$count_chars]['Slug'] = $char['realm']['slug'];
                  $all_chars[$count_chars]['Class'] = $char['playable_class']['id'];
                  $all_chars[$count_chars]['Race'] = $char['playable_race']['id'];
                  $all_chars[$count_chars]['Level'] = $char['level'];
                  $count_chars++;
                  $show_races[$char['playable_race']['id']] = lookup_char_race($char['playable_race']['id']);
                  $show_classes[$char['playable_class']['id']] = lookup_char_class($char['playable_class']['id']);
                }
              }
              sortBy ('Level', $all_chars, 'desc', 'reindex');
            $command = 'select_bnetchar';
        }
        catch (\BattleNet\OAuthException $e) {
            $command = '';
            echo '<script>$.growl.error({ message: "'._("There was a problem connecting to the Battle.net service. Please check your account is set to the correct region (-> Settings on the left) or try again later.").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
        ?><script>document.getElementById('loading').style.display='none';</script><?
    }
    


    
    
    // Import characters from Battle.net to display only, do not set any character (was done above)
    if ($command == 'loadchars') {
        $charlist = [];
        
        try
        {
            $oauth = new \BattleNet\OAuth ($bnetuser->Region, 'loadchars', '/index.php?page=collection');

            if (!$oauth->is_authed)
            {
              \HTTP\redirect_and_die ($oauth->auth_url());
            }
            
            if ($oauth->fetch ('account')['id'] != $bnetuser->BnetID)
            {
                mysqli_close($dbcon);
                ?>
                <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=wrong_bnet">
                <?
                die;
            }
            
            // Import 
            $wowcharsinfo = $oauth->fetch ('wowprofile');
        
            // Go through all characters and add to an array
            $all_chars = array();
            $count_chars = 0;
            foreach ($wowcharsinfo['wow_accounts'] as $account) {
                foreach ($account['characters'] as $char) {
                  $all_chars[$count_chars]['Name'] = $char['name'];
                  $all_chars[$count_chars]['Realm'] = $char['realm']['name'];
                  $all_chars[$count_chars]['Slug'] = $char['realm']['slug'];
                  $all_chars[$count_chars]['Class'] = $char['playable_class']['id'];
                  $all_chars[$count_chars]['Race'] = $char['playable_race']['id'];
                  $all_chars[$count_chars]['Level'] = $char['level'];
                  $count_chars++;
                  $show_races[$char['playable_race']['id']] = lookup_char_race($char['playable_race']['id']);
                  $show_classes[$char['playable_class']['id']] = lookup_char_class($char['playable_class']['id']);
                }
              }
              sortBy ('Level', $all_chars, 'desc', 'reindex');

            $getcol = update_collection($user->id,"1");
    
            if ($getcol[0] == "error")
            {
                $command = 'select_bnetchar';
                $error_msg = 'generic_error';
                if ($getcol[1] == 'no_pets_in_import')
                {   
                    $error_msg = 'zero_pets';
                }
            }
            else if ($getcol[0] == "success")
            {
                mysqli_close($dbcon);
                ?>
                <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=import_success">
                <?
                die;
            }
        }
        catch (\BattleNet\OAuthException $e) {
            $command = '';
            echo '<script>$.growl.error({ message: "'._("There was a problem connecting to the Battle.net service. Please check your account is set to the correct region (-> Settings on the left) or try again later.").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
        ?><script>document.getElementById('loading').style.display='none';</script><?
    }
    
    
    
    
    // Import characters from Battle.net and set highest level one automatically
    if ($command == 'import_from_bnet' OR $command == 'import_redirect') {
        $charlist = [];
        
        try
        {
            $oauth = new \BattleNet\OAuth ($bnetuser->Region, 'import_from_bnet', '/index.php?page=collection');
            
            if (!$oauth->is_authed)
            {
              \HTTP\redirect_and_die ($oauth->auth_url());
            }
            
            if ($oauth->fetch ('account')['id'] != $bnetuser->BnetID)
            {
                mysqli_close($dbcon);
                ?>
                <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=wrong_bnet">
                <?
                die;
            }
            
            // Import 
            $wowcharsinfo = $oauth->fetch ('wowprofile');
        
            // Go through all characters and add to an array
            $all_chars = array();
            $count_chars = 0;
            foreach ($wowcharsinfo['wow_accounts'] as $account) {
                foreach ($account['characters'] as $char) {
                  $all_chars[$count_chars]['Name'] = $char['name'];
                  $all_chars[$count_chars]['Realm'] = $char['realm']['name'];
                  $all_chars[$count_chars]['Slug'] = $char['realm']['slug'];
                  $all_chars[$count_chars]['Class'] = $char['playable_class']['id'];
                  $all_chars[$count_chars]['Race'] = $char['playable_race']['id'];
                  $all_chars[$count_chars]['Level'] = $char['level'];
                  $count_chars++;
                  $show_races[$char['playable_race']['id']] = lookup_char_race($char['playable_race']['id']);
                  $show_classes[$char['playable_class']['id']] = lookup_char_class($char['playable_class']['id']);
                }
              }
              sortBy ('Level', $all_chars, 'desc', 'reindex');

                Database_UPDATE
                  ( 'UserBnet'
                  , [ 'CharRealm'
                    , 'CharRealmFull'
                    , 'CharName'
                    , 'CharLevel'
                    , 'CharClass'
                    , 'CharRace'
                    ]
                  , 'WHERE User = ?'
                  , 'sssiiii'
                    , $all_chars[0]['Slug']
                    , $all_chars[0]['Realm']
                    , $all_chars[0]['Name']
                    , $all_chars[0]['Level']
                    , $all_chars[0]['Class']
                    , $all_chars[0]['Race']
                  , $user->id
                  );
                Database_UPDATE
                ( 'Users'
                , [ 'UseWoWForCol' ]
                , 'WHERE id = ?'
                , 'ii'
                  , '0'
                , $user->id
                );
                $bnetdb = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id' LIMIT 1");
                $bnetuser = mysqli_fetch_object($bnetdb);

            $charstring = $all_chars[0]['Name'].' '.$all_chars[0]['Realm'].'-'.strtoupper($bnetuser->Region);
            
            $getcol = update_collection($user->id,"1");

            if ($getcol[0] == "error")
            {
                echo '<script type="text/javascript">$.growl.notice({ message: "'._("Character set to: ").$charstring.'", duration: "7000", size: "large", location: "tc" });</script>'; 
                $command = 'select_bnetchar';
                $error_msg = 'generic_error';
                if ($getcol[1] == 'no_pets_in_import')
                {   
                    $error_msg = 'zero_pets';
                }
            }
            else if ($getcol[0] == "success")
            {
                mysqli_close($dbcon);
                ?>
                <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=import_success">
                <?
                die;
            }
        }
        catch (\BattleNet\OAuthException $e) {
            $command = '';
            echo '<script>$.growl.error({ message: "'._("There was a problem connecting to the Battle.net service. Please check your account is set to the correct region (-> Settings on the left) or try again later.").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
        ?><script>document.getElementById('loading').style.display='none';</script><?
    }
    
    

    
    


    // Show list of available characters to pick from
    if ($command == 'select_bnetchar') {
        
        $charstring = ucfirst($bnetuser->CharName).' '.ucfirst($bnetuser->CharRealm).'-'.strtoupper($bnetuser->Region);
        
        ?>
        
        <table width="100%" border="0">
            <tr>
                <td width="1%">
                    <img src="images/blank.png" width="250" height="1">
                </td>
                <td>
                    <table width="100%" border="0">
                        <tr>
                            <td>
                                <table width="85%" class="profile">
                                    <tr class="profile">
                                        <th width="5" class="profile">
                                            <table>
                                                <tr>
                                                    <td><img src="images/userdd_settings_grey.png"></td>
                                                    <td><img src="images/blank.png" width="5" height="1"></td>
                                                    <td><p class="blogodd"><b><? echo _("Connect a character") ?></td>
                                                </tr>
                                            </table>
                                        </th>
                                    </tr>
                                    <tr class="profile">
                                        <td class="profile">
                                            <p class="blogodd">
                                                <?
                                                if ($error_msg) {
                                                    switch ($error_msg) {
                                                        case "zero_pets":
                                                            echo '<br><b><font color="#c70200">=> '._("The import with your connected character worked fine but the collection contained zero pets.").'</font></b>';
                                                            echo '<br><br>'._("Here are a few things you can try:");
                                                            echo '<br><br>- '._("Go back and try again later, it could be a temporary issue.");
                                                            echo '<br>- '._("Check in the settings on the left that your account is set to the correct region.");
                                                            echo '<br>- '._("Pick a different character below to connect and try the import again.").'<br><br>';
                                                            break;
                                                        case "generic_error":
                                                            echo '<br><b><font color="#c70200">=> '._("Importing your collection unfortunately did not work. The reason was not given.").'</font></b>';
                                                            echo '<br><br>'._("Please try again later or select a different character from the list below.").'<br><br>';
                                                            break;
                                                    }  

                                                }
                                                else {
                                                    echo _("Importing your collection works through the armory and needs a specific character to be set.");
                                                    echo '<br>'._("The currently connected character is:").'<b> '.$charstring.'</b><br>';
                                                }
                                      
                                                ?>
                                                <br>
                                                      
                                                <center>
                                                <table id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:0 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                                                <thead>
                                                    <tr>
                                                        <th class="petlistheaderfirst table-sortable:alphabetic"><p class="blogodd">Name</th>
                                                        <th class="petlistheaderfirst table-sortable:alphabetic"><p class="blogodd">Realm</th>
                                                        <th class="petlistheaderfirst table-sortable:numeric"><p class="blogodd">Level</th>
                                                        <th class="petlistheaderfirst table-sortable:alphabetic"><p class="blogodd">Race</th>
                                                        <th class="petlistheaderfirst table-sortable:alphabetic"><p class="blogodd">Class</th>
                                                        <th class="petlistheaderfirst"></th>
                                                    </tr>

                                                    <tr>
                                                        <th class="petlistheadersecond">
                                                            <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                                                        </th>
                                                        <th class="petlistheadersecond">
                                                            <input class="petselect" name="filter" size="25" id="realmfilter" onkeyup="Table.filter(this,this)">
                                                        </th>
                                                        <th class="petlistheadersecond">
                                                            <select class="petselect" style="width:100px;" onchange="Table.filter(this,this)">
                                                                <option class="petselect" value=""><? echo _("All") ?></option>
                                                                <option class="petselect" value="120">120</option>
                                                                <option class="petselect" value="function(val){return parseFloat(val)>=110;}">>= 110</option>
                                                                <option class="petselect" value="function(val){return parseFloat(val)>=100;}">>= 100</option>
                                                                <option class="petselect" value="function(val){return parseFloat(val)<100;}">< 100</option>
                                                            </select>
                                                        </th>
                                                        <th class="petlistheadersecond">
                                                            <select class="petselect" style="width:150px;" onchange="Table.filter(this,this)">
                                                                <option class="petselect" value=""><? echo _("All") ?></option>
                                                                <?
                                                                asort($show_races);
                                                                foreach ($show_races as $race) { ?>
                                                                    <option class="petselect" value="<? echo $race; ?>"><? echo $race; ?></option>
                                                                <? } ?>
                                                            </select>
                                                        </th>
                                                        <th class="petlistheadersecond">
                                                            <select class="petselect" style="width:150px;" onchange="Table.filter(this,this)">
                                                                <option class="petselect" value=""><? echo _("All") ?></option>
                                                                <?
                                                                asort($show_classes);
                                                                foreach ($show_classes as $class) { ?>
                                                                    <option class="petselect" value="<? echo $class; ?>"><? echo $class; ?></option>
                                                                <? } ?>
                                                            </select>
                                                        </th>
                                                        <th class="petlistheadersecond"></th>
                                                    </tr>
                                                    </thead>
                                                    
                                                    <tbody>
                                                    <? foreach ($all_chars as $char) {
                                                        $connected_char = false;
                                                        if ($char['Name'] == $bnetuser->CharName && $char['Realm'] == $bnetuser->CharRealmFull && $char['Level'] == $bnetuser->CharLevel) {
                                                            $connected_char = true;
                                                        }
                                                        echo '<tr class="petlist" ';
                                                        if ($connected_char == true) {
                                                            echo 'style="background-color: #a2cd92"';
                                                        }
                                                        echo '>';
                                                        echo '<td class="petlist">';
                                                        echo '<form style="display: inline" action="index.php?page=collection" method="post">';
                                                        echo '<input type="hidden" name="charname" value="'.mysqli_real_escape_string($dbcon, $char['Name']).'">';
                                                        echo '<input type="hidden" name="charslug" value="'.mysqli_real_escape_string($dbcon, $char['Slug']).'">';
                                                        echo '<input type="hidden" name="charrealm" value="'.mysqli_real_escape_string($dbcon, $char['Realm']).'">';
                                                        echo '<input type="hidden" name="charlevel" value="'.mysqli_real_escape_string($dbcon, $char['Level']).'">';
                                                        echo '<input type="hidden" name="charrace" value="'.mysqli_real_escape_string($dbcon, $char['Race']).'">';
                                                        echo '<input type="hidden" name="charclass" value="'.mysqli_real_escape_string($dbcon, $char['Class']).'">';
                                                        echo '<p class="blogodd">'.$char['Name'].'</td>';
                                                        echo '<td class="petlist"><p class="blogodd">'.$char['Realm'].'</td>';
                                                        echo '<td class="petlist"><p class="blogodd">'.$char['Level'].'</td>';
                                                        echo '<td class="petlist"><p class="blogodd">'.lookup_char_race($char['Race']).'</td>';
                                                        echo '<td class="petlist"><p class="blogodd">'.lookup_char_class($char['Class']).'</td>';
                                                        echo '<td class="petlist">';
                                                        if ($connected_char == false) {
                                                            echo '<button type="submit" style="margin: 0" class="comsubmit">'._("Set and import").'</button>';
                                                            echo '<input type="hidden" name="command" value="set_character">';
                                                        }
                                                        else {
                                                            echo '<input type="hidden" name="command" value="retry_character">';
                                                            echo '<button type="submit" style="margin: 0" class="comedit">'._("Retry").'</button>';
                                                        }
                                                        echo '</form></td></tr>';
                                                    } ?>
                                                    </tbody>
                                                </table>
                                                </center>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br><br><br><br>
    <? }

    
    
    // Import from character details
    if ($command == 'import_from_char' OR $command == 'change_char') {
        $postchar = \HTTP\argument_POST_or_default ('charname', FALSE);
        $post_realm = \HTTP\argument_POST_or_default ('realm', FALSE);
        
        $name = str_replace(' ', '', $postchar);
        $splits = explode("|", $post_realm);
        $realm = $splits[1];
        $region = $splits[0];

        $chardata_summary_source = blizzard_api_character_summary($region, $realm, $name, $language);

        if ($chardata_summary_source == "error") {
            $import_from_char_error = TRUE;
            if ($command == 'import_from_char') $command = '';
            if ($command == 'change_char') $command = 'change_wow_char';
            echo '<script type="text/javascript">$.growl.error({ message: "'._("There was an error importing the pets through your character. Please check if you selected the correct realm (and region) and try again. You can use any character from your account to import your pets.").'", duration: "10000", size: "large", location: "tc" });</script>';
        }
        else {
            $chardata_summary = json_decode($chardata_summary_source, TRUE);
            $char_avatar = explode("character/", $chardata_avatar['avatar_url']);

            // Adding Character to User Data as their set character
            Database_UPDATE
              ( 'Users'
              , [ 'UseWoWForCol'
                , 'CharRegion'
                , 'CharRealm'
                , 'CharRealmFull'
                , 'CharName'
                , 'CharLevel'
                , 'CharClass'
                , 'CharRace'
                , 'CharIcon'
                ]
              , 'WHERE id = ?'
              , 'sssssiiisi'
                , '1'
                , $region
                , $realm
                , $chardata_summary['realm']['name']
                , $name
                , $chardata_summary['level']
                , $chardata_summary['character_class']['id']
                , $chardata_summary['race']['id']
                , $char_avatar[1]
              , $user->id
              );
            $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user->id'");
            $user = mysqli_fetch_object($userdb);
            
            $getcol = update_collection($user->id,"1");
        
            if ($getcol[0] == "error")
            {
                if ($command == 'import_from_char') $command = '';
                if ($command == 'change_char') $command = 'change_wow_char';
                $import_from_char_error = TRUE;
                if ($getcol[1] == 'bnetcharincorrect' OR $getcol[1] == 'generic_import_error')
                {
                    echo '<script type="text/javascript">$.growl.error({ message: "'._("There was an error importing the pets through your character. Please check if you selected the correct realm (and region) and try again. You can use any character from your account to import your pets.").'", duration: "10000", size: "large", location: "tc" });</script>';
                }
                if ($getcol[1] == 'no_pets_in_import')
                {
                    echo '<script type="text/javascript">$.growl.error({ message: "'._("The data import worked but returned 0 pets. Either your account does not have any pets or there was an error on Blizzards side. Please try a different character or change the region of your account in the settings.").'", duration: "10000", size: "large", location: "tc" });</script>';
                }
            }
            else if ($getcol[0] == "success")
            {
                mysqli_close($dbcon);
                ?>
                <META HTTP-EQUIV="refresh" CONTENT="0; URL=https://www.wow-petguide.com/?page=collection&sendtoast=import_success">
                <?
                die;
            }  
        }
        ?><script>document.getElementById('loading').style.display='none';</script><?
    }
    
    
    

    if ($command == 'change_wow_char') { ?>
        
        <script>document.getElementById('loading').style.display='none';</script>
        <table width="100%" border="0">
            <tr>
                <td width="1%">
                    <img src="images/blank.png" width="250" height="1">
                </td>
                <td>
                    <table width="100%" border="0">
                        <tr>
                            <td>
                                <table width="85%" class="profile">
                                    <tr class="profile">
                                        <th width="5" class="profile">
                                            <table>
                                                <tr>
                                                    <td><img src="images/userdd_settings_grey.png"></td>
                                                    <td><img src="images/blank.png" width="5" height="1"></td>
                                                    <td><p class="blogodd"><b><? echo _("Change connected character") ?></td>
                                                </tr>
                                            </table>
                                        </th>
                                    </tr>
                                    <tr class="profile">
                                        <td class="profile">
                                            <p class="blogodd">

                                                <br>
                                                    <p class="blogodd">
                                                    <? echo _("Enter the details of a valid character below to change to it and import your pets anew."); ?>
                                                    <br>
                                                    <form action="index.php?page=collection" method="post" style="display: inline">
                                                    <input type="hidden" name="command" value="change_char">
                                                    
                                                    <table style="margin: 10 0 0 20">
                                                        <tr>
                                                            <td>
                                                                <select width="330" data-placeholder="<? echo _("Realm") ?>" name="realm" class="chosen-select" tabindex="1" required>
                                                                    <option value=""></option>
                                                                        <optgroup label="<? echo _("FormSelectRealmUS") ?>">
                                                                            <? foreach($realmsus as $key => $value) {
                                                                                $thisrealm = explode("|",$value);
                                                                                if ($splits[0]."|".$splits[1] == "us|".$thisrealm[0]) {
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
                                                                                if ($splits[0]."|".$splits[1] == "eu|".$thisrealm[0]) {
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
                                                                                if ($splits[0]."|".$splits[1] == "kr|".$thisrealm[0]) {
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
                                                                                if ($splits[0]."|".$splits[1] == "tw|".$thisrealm[0]) {
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
                                                                <input value="<? echo ucfirst(htmlentities($name, ENT_QUOTES, "UTF-8")); ?>" class="petselect" name="charname" tabindex="2" placeholder="<? echo _("FormSelectCharName") ?>" style="width: 330px;" required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="max-width: 230; font-size: 14px; color: #831010; font-weight: bold">
                                                                <? echo $char1_errmsg; ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <button tabindex="15" style="margin: 5 0 30 25" type="submit" class="bnetlogin"><? echo _("Import collection"); ?></button>
                                                    </form>
                                                    
                                                    <script type = "text/javascript">
                                                        $(".chosen-select").chosen({width: 330});
                                                    </script>
 
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br><br><br><br>
                                 
                                                
                                                
        
    <? }
    
    
    
    
    

    
    
    
    
    
    
    
    // Standard page for no collection with instructions
    
    if (!$command) { ?>
        
        
        <table width="100%" border="0">
            <tr>
                <td width="1%">
                    <img src="images/blank.png" width="250" height="1">
                </td>
                <td>
                    <table width="100%" border="0">
                        <tr>
                            <td>
                                <table width="85%" class="profile">
                                    <tr class="profile">
                                        <th width="5" class="profile">
                                            <table>
                                                <tr>
                                                    <td><img src="images/userdd_settings_grey.png"></td>
                                                    <td><img src="images/blank.png" width="5" height="1"></td>
                                                    <td><p class="blogodd"><b><? echo _("Your Pet Collection") ?></td>
                                                </tr>
                                            </table>
                                        </th>
                                    </tr>
                                    <tr class="profile">
                                        <td class="profile">
                                            <p class="blogodd">
                                                <? echo _("Importing your pet collection is very easy and unlocks the true power of Xu-Fu's. Strategies will then be sorted
                                                          based on your available pets and those you can follow directly are shown first, alongside some other features only available with
                                                          a saved collection."); ?>
                                                <br>
                                                <? echo _("And on top, Xu-Fu will keep your collection updated."); ?>                                        
                                                <br><br>
                                                <? echo _("There are two options how to import your collection:"); ?>
                                                <br>

                                                <div style="margin: 10 0 10 20; padding: 10px; width: 450px; border: 5px solid grey; float: left">
                                                    <p class="blogodd">
                                                    <b><? echo _("Option 1: Import through Battle.net"); ?></b><br>
                                                    
                                                    <? if (!$bnetuser) { ?>
                                                        <br>
                                                        <? echo _("Connect your Battle.net account. With this option you can then also log into your Xu-Fu account."); ?>
                                                        <br>
                                                        <? echo _("You will be redirected to Battle.net and then back here. The page will refresh several times."); ?>
                                                        <form name="loginform" action="index.php?page=settings" method="post" style="margin: 10 0 0 0">
                                                        <input type="hidden" name="settingspage" value="addbnet">
                                                        <input type="hidden" name="source" value="collection">
                                                        <button tabindex="15" style="margin: 5 0 0 25" type="submit" class="bnetlogin" name="page" value="settings"><? echo _("Connect Battle.net Account"); ?></button>
                                                        </form>
                                                    <? } ?>

                                                    <? if ($bnetuser) { ?>
                                                        <form name="loginform" action="index.php?page=collection" method="post" style="margin: 10 0 0 10">
                                                        <input type="hidden" name="command" value="import_from_bnet">
                                                        <button tabindex="15" style="margin: 5 0 0 25" type="submit" class="bnetlogin"><? echo _("Import collection automatically"); ?></button>
                                                        </form>
                                                    <? } ?>
                                                </div>



                                                <div style="margin: 10 20 10 20; padding: 10px; width: 450px; <?
                                                if ($import_from_char_error == true) echo 'border: 5px solid #c45858; ';
                                                else echo 'border: 5px solid grey; '; ?> float: left">
                                                    <p class="blogodd">
                                                    <b><? echo _("Option 2: Import via character"); ?></b><br>
                                                    <br>
                                                    <? echo _("Enter the details of a valid character below to import your collection from the armory."); ?>
                                                    <br>
                                                    <form action="index.php?page=collection" method="post" style="display: inline">
                                                    <input type="hidden" name="command" value="import_from_char">
                                                    
                                                    <table style="margin: 10 0 0 20">
                                                        <tr>
                                                            <td>
                                                                <select width="330" data-placeholder="<? echo _("Realm") ?>" name="realm" class="chosen-select" tabindex="1" required>
                                                                    <option value=""></option>
                                                                        <optgroup label="<? echo _("FormSelectRealmUS") ?>">
                                                                            <? foreach($realmsus as $key => $value) {
                                                                                $thisrealm = explode("|",$value);
                                                                                if ($splits[0]."|".$splits[1] == "us|".$thisrealm[0]) {
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
                                                                                if ($splits[0]."|".$splits[1] == "eu|".$thisrealm[0]) {
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
                                                                                if ($splits[0]."|".$splits[1] == "kr|".$thisrealm[0]) {
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
                                                                                if ($splits[0]."|".$splits[1] == "tw|".$thisrealm[0]) {
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
                                                                <input value="<? echo ucfirst(htmlentities($name, ENT_QUOTES, "UTF-8")); ?>" class="petselect" name="charname" tabindex="2" placeholder="<? echo _("FormSelectCharName") ?>" style="width: 330px;" required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="max-width: 230; font-size: 14px; color: #831010; font-weight: bold">
                                                                <? echo $char1_errmsg; ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <button tabindex="15" style="margin: 5 0 0 25" type="submit" class="bnetlogin"><? echo _("Import collection"); ?></button>
                                                    </form>
                                                    
                                                    <script type = "text/javascript">
                                                        $(".chosen-select").chosen({width: 330});
                                                    </script>
                                                </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br><br><br><br>
        
        
    <? }
    
    
    
    
}







// Collection is present, regular output

if ($collection) { ?>

 <div class="blogentryfirst">
        <div class="articlebottom"></div>
        <table width="100%" border="0">
            <tr>
                <td width="1%">
                    <img src="images/blank.png" width="250" height="1">
                </td>
                <td>
                    <table style="width:1000">
                        <tr>
                            <td style="width: 100%">
                                <table style="width: 100%" cellspacing="0" cellpadding="0" class="profile">
                                    <tr class="profile">
                                        <td class="profile" style="width: 1;">
                                            <img style="height: 40px; width: 40px; border-radius: 10px;" <? echo $usericon ?>>
                                        </td>
                                        <td style="align:left;padding-left:10px;width:600px">
                                            <h3 class="collection"><? echo _("ColTableTitleNName"); ?>
                                        </td>
                                        <td align="right" style="padding: 0 25 7 0">
                                            <?
                                            $langpieces = decode_language($language);
                                            ?>
                                            <a href="classes/export_collection_xlsx.php?user=<? echo $user->id ?>&language=<? echo $langpieces['short']; ?>" target="_blank">
                                                <button style="margin-left: 15px; white-space:nowrap;" type="submit" tabindex="4" class="bnetlogin">Export to Excel</button>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <? print_collection($collection, '1'); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br><br><br><br><br><br>
    </div>


<? }

// Toasts:
$sendtoast = \HTTP\argument_GET_or_default ('sendtoast', FALSE);

switch ($sendtoast) {
    case "import_success":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("The import of your pet collection was successful.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "wrong_bnet":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("You seem to be logged into a different Battle.net account on Blizzards web pages than the one you used to log into Xu-Fu. Please head over to Battle.net and make sure you log into the correct Battle.net account before trying again.").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
}
// echo '<script>window.history.replaceState("object or string", "Title", "?page=collection");</script>';

mysqli_close($dbcon);
echo "<script>updateAllTimes('time')</script>";
echo "</body>";
die;
