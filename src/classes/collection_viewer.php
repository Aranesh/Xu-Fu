<?php

require_once ('HTTP.php');
require_once ('Database.php');


$command = \HTTP\argument_POST_or_default ('command', FALSE);
if ($command == "import") {
    $name = \HTTP\argument_POST_or_default ('charname', FALSE);
    $post_realm = \HTTP\argument_POST_or_default ('realm', FALSE);
    $name = str_replace(' ', '', $name);
    $name = strtolower($name);
    $splits = explode("|", $post_realm);
    $region = $splits[0];
    $realm = $splits[1];
}
else {
    $region = \HTTP\argument_GET_or_default ('region', FALSE);
    $realm = \HTTP\argument_GET_or_default ('realm', FALSE);
    $name = \HTTP\argument_GET_or_default ('name', FALSE);
    if ($region && $realm && $name) {
        $command = "import";
        $name = str_replace(' ', '', $name);
        $name = strtolower($name);
        $region = strtolower($region);
        $realm = strtolower($realm);
    }
}


// =============  Header with Page Title  ============= ?>

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><img src="https://www.wow-petguide.com/images/main_bg02_1.png"></td>
            <td width="100%"><h class="megatitle"><? echo _("MainbarColViewer") ?></font></td>
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
// Character Selection form:
if ($command != "import") {

    echo _("ColViewerIntro")."<br><br>";

    $realmlistsdb = mysqli_query($dbcon, "SELECT * FROM Realms WHERE id = '1'") or die(mysqli_error($dbcon));
    $realmlists = mysqli_fetch_object($realmlistsdb);
    $realmsus = explode("*",$realmlists->US);
    $realmseu = explode("*",$realmlists->EU);
    $realmskr = explode("*",$realmlists->KR);
    $realmstw = explode("*",$realmlists->TW);
    ?>

    <table width="480" class="profile">
        <tr class="profile">
            <td>
                <p class="blogodd">
                <br>
                <form action="index.php?m=Collection" method="post">
                    <input type="hidden" name="command" value="import">

                <table border="0"><tr>
                <td>
                    <img src="images/blank.png" width="50" height="1">
                </td>

                <td width="230">

                    <select width="230" data-placeholder="<? echo _("FormSelectRealm") ?>" name="realm" class="chosen-select" tabindex="2" required>
                        <option value=""></option>
                        <optgroup label="<? echo _("FormSelectRealmUS") ?>">
                            <?
                            foreach($realmsus as $key => $value) {
                                $thisrealm = explode("|",$value);
                            echo '<option value="us|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                            }
                            ?>
                        </optgroup>
                        <optgroup label="<? echo _("FormSelectRealmEU") ?>">
                            <?
                            foreach($realmseu as $key => $value) {
                                $thisrealm = explode("|",$value);
                            echo '<option value="eu|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                            }
                            ?>
                        </optgroup>
                        <optgroup label="<? echo _("FormSelectRealmKR") ?>">
                            <?
                            foreach($realmskr as $key => $value) {
                                $thisrealm = explode("|",$value);
                            echo '<option value="kr|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                            }
                            ?>
                        </optgroup>
                        <optgroup label="<? echo _("FormSelectRealmTW") ?>">
                            <?
                            foreach($realmstw as $key => $value) {
                                $thisrealm = explode("|",$value);
                            echo '<option value="tw|'.$thisrealm[0].'">'.$thisrealm[1].'</option>';
                            }
                            ?>
                        </optgroup>
                    </select>
                </td>
                <td rowspan="2">
                    <button style="margin-left: 15px;" type="submit" tabindex="4" class="bnetlogin"><? echo _("FormSelectImportPets") ?></button>
                </td>
                </tr>
                <tr>
                <td></td>
                <td>
                    <input class="petselect" name="charname" tabindex="3" placeholder="<? echo _("FormSelectCharName") ?>" style="width: 230px;" width="230" required>
                </td>

                </tr>

                </table>
            </form>

                <script type = "text/javascript">
                    $(".chosen-select").chosen({width: 230});
                </script>

            </td>
        </tr>
    </table>
<? }


// Show collection:

if ($command == "import") { ?>

    <center>
    <div id="loading">
    <table width="480" class="profile">
    
        <tr class="profile">
            <td class="profile">
                <p class="blogodd">
                <center><img src="images/loading.gif"><br><br><i><? echo _("FormSelectLoadText") ?></i><br></center>
                <br><br>
            </td>
        </tr>
    
    </table>
    <br>
    </div>
    
    <?   
    // Get Pet data
    $petdata_source = blizzard_api_character_pets($region, $realm, $name);
    // Get character data for header
    $chardata_summary_source = blizzard_api_character_summary($region, $realm, $name, $language);
    // Get avatar url
    $chardata_avatar_source = blizzard_api_character_media($region, $realm, $name); 

// Fetch successful
if ($petdata_source != "error" && $chardata_summary_source != "error" && $chardata_avatar_source != "error") {
    $chardata_summary = json_decode($chardata_summary_source, TRUE);  
    $chardata_avatar = json_decode($chardata_avatar_source, TRUE);
    $userid = $user->id;
    if (!$user) {
        $userid = "0";
    }

    Database_INSERT_INTO
          ( 'PetCollections'
          , [ 'CharRegion'
            , 'IP'
            , 'User'
            , 'CharRealm'
            , 'CharRealmFull'
            , 'CharName'
            ]
          , 'ssisss'
            , $region
            , $user_ip_adress
            , $userid
            , $realm
            , $chardata_summary['realm']['name']
            , $name
          );
    ?>
    
    <div style="display:none" id="collection">
        <table style="width:1000">
            <tr><td style="width: 100%">
                <table style="width: 100%" cellspacing="0" cellpadding="0" class="profile">
            <tr class="profile">
                <td class="profile" width="30">
                    <a target="_blank" href="https://worldofwarcraft.com/en-us/character/<? echo $region ?>/<? echo $realm ?>/<? echo $name ?>">
                        <img src="<? echo $chardata_avatar['avatar_url'] ?>">
                    </a>
                </td>
                <td style="align:left; padding-left:15px; width:600px">
                    <h3 class="collection">
                    <? echo $chardata_avatar['character']['name']." - ".$chardata_summary['realm']['name']; ?><br>
                    Level <? echo $chardata_summary['level']." ".lookup_char_race($chardata_summary['race']['id'])." ".lookup_char_class($chardata_summary['character_class']['id']); ?>
                </td>
                <td align="right">
                    <?
                    $langpieces = decode_language($language);
                    ?>
                    <a href="classes/export_collection_xlsx.php?m=Collection&language=<? echo $langpieces['short']; ?>&region=<? echo $region ?>&realm=<? echo $realm ?>&name=<? echo $name ?>&language=<? echo $langpieces['short']; ?>" target="_blank">
                        <button style="margin-left: 15px; white-space:nowrap;" type="submit" tabindex="4" class="bnetlogin">Export to Excel</button>
                    </a>
                </td>
                <td align="right" style="padding-right:25px">
                    <a href="?m=Collection&region=<? echo $region ?>&realm=<? echo $realm ?>&name=<? echo $name ?>">
                        <button style="margin-left: 15px; white-space:nowrap;" type="submit" tabindex="4" class="bnetlogin"><? echo _("ColTableUpdate") ?></button>
                    </a>
                </td>
            </tr>
            </table>
        </td></tr>
        <tr><td>
    <?
    
    $petdata = prepare_collection($petdata_source); 

    if ($petdata == "empty") { ?>
        <table class="profile">
            <tr class="profile">
                <td class="collectionbordertwo" style="width: 100%">
                    <p class="blogodd">The data import worked but returned 0 pets. Either your account does not have any pets or there was an error on Blizzards side.<br>
                    Hit "Update Collection" to try again. <br>
                    <br>
                    If this message keeps coming up, check the <a class="wowhead" href="https://wow-petguide.com">page news</a> or contact Aranesh
                    either via <a class="wowhead" href="mailto:xufu@wow-petguide.com">email</a> or on <a class="wowhead" href="https://discord.gg/z4dxYUq" target="_blank">Discord</a>.</p>
                </td>
            </tr>
        </table>
        <script>
            document.getElementById('loading').style.display='none';
            document.getElementById('collection').style.display='block';
        </script>
    <? }
    else {
        print_collection($petdata,"1"); 
    }
    ?>
    </td></tr></table></div>
    <script>
        window.history.replaceState("object or string", "Title", "?m=Collection&region=<? echo $region ?>&realm=<? echo $realm ?>&name=<? echo $name ?>");
    </script>
    <?
}

else {  // Fetch not successful
    $realmlistsdb = mysqli_query($dbcon, "SELECT * FROM Realms WHERE id = '1'") or die(mysqli_error($dbcon));
    $realmlists = mysqli_fetch_object($realmlistsdb);
    $realmsus = explode("*",$realmlists->US);
    $realmseu = explode("*",$realmlists->EU);
    $realmskr = explode("*",$realmlists->KR);
    $realmstw = explode("*",$realmlists->TW);
    ?>
    
    <div id="loadingerror" style="display: none">
    <table width="480" class="profilehl">
        <tr class="profile">
            <td class="profile">
                <center><p class="blogodd"><b><br>
    
                <? echo _("ColTableErrorGen") ?>
                <br><br></b></center><p class="blogodd">
Possible reasons:<br>
<ul style="margin-left: 10px; margin-right: 10px">
    <li><p class="blogodd">Characters below level 20 are sometimes not available in the armory</li>
    <li><p class="blogodd">Inactive characters might be unavailable until logged in and out again</li>
    <li><p class="blogodd">The option "Share my game data with community developers" needs to be enabled in your Battle.net privacy settings</li>
</ul>
    
                <center><br>
    
                    <form action="index.php?m=Collection" method="post">
                        <input type="hidden" name="command" value="import">
    
                    <table border="0"><tr>
                    <td>
                        <img src="images/blank.png" width="1" height="1">
                    </td>
    
                    <td width="230">
    
                        <select width="230" data-placeholder="<? echo _("FormSelectRealm") ?>" name="realm" class="chosen-select" tabindex="2" required>
                            <option value=""></option>
                            <optgroup label="<? echo _("FormSelectRealmUS") ?>">
                                <?
                                foreach($realmsus as $key => $value) {
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
                    <td rowspan="2">
                        <button style="margin-left: 15px;" type="submit" tabindex="4" class="bnetlogin"><? echo _("FormSelectImportPets") ?></button>
                    </td>
                    </tr>
                    <tr>
                    <td></td>
                    <td>
                        <input value="<? echo htmlentities($name, ENT_QUOTES, "UTF-8"); ?>" class="petselect" name="charname" tabindex="3" placeholder="<? echo _("FormSelectCharName") ?>" style="width: 230px;" width="230" required>
                    </td>
    
                    </tr>
    
                    </table>
                </form>
            </td>
        </tr>
    
    </table>
    <br>
    </div>
    
    <script type = "text/javascript">
    window.onload = function() {
        document.getElementById('loading').style.display='none';
        document.getElementById('loadingerror').style.display='block';
        $(".chosen-select").chosen({width: 230});
    }
    </script>
<? }
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


