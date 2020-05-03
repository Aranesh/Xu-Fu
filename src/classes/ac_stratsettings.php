<?php

if ($settingspage == ""){
    $settingspage = $_POST['settingspage'];
}

$alltagsdb = mysqli_query($dbcon, "SELECT * FROM StrategyTags ORDER BY DefaultPrio");
while ($row_tag = mysqli_fetch_object($alltagsdb)) {
    $alltags[$row_tag->id]['id'] = $row_tag->id;
    $alltags[$row_tag->id]['Name'] = _("Tag_".$row_tag->PO_Name);
    $alltags[$row_tag->id]['DefaultPrio'] = $row_tag->DefaultPrio;
}

if ($user->TagPrio == "") {
    foreach ($alltags as $key => $value) {
        $cutprio = explode("-", $value['DefaultPrio']);
        $tagprio[$cutprio[0]][$cutprio[1]] = $value['id'];
    }  
}
else {
    $test_tags = $alltags;
    $countone = "0";
    $counttwo = "0";
    $countthr = "0";    
    $cuttags = explode("-", $user->TagPrio);
    $cutone = explode(",", $cuttags[0]);
    $cuttwo = explode(",", $cuttags[1]);
    $cutthr = explode(",", $cuttags[2]);

    if ($cutone[0] != "") {
        foreach ($cutone as $key => $value) {
            $tagprio[0][$countone] = $value;
            unset($test_tags[$value]);
            $countone++;
        }
    }
    if ($cuttwo[0] != "") {
        foreach ($cuttwo as $key => $value) {
            $tagprio[1][$counttwo] = $value;
            unset($test_tags[$value]);
            $counttwo++;
        }
    }
    if ($cutthr[0] != "") {
        foreach ($cutthr as $key => $value) {
            $tagprio[2][$countthr] = $value;
            unset($test_tags[$value]);
            $countthr++;
        }        
    }
    if (count($test_tags) > "0") {
        foreach ($test_tags as $key => $value) {
            $cutprio = explode("-", $alltags[$value['id']]['DefaultPrio']);
            if ($cutprio[0] == "0") {
                $tagprio[0][$countone] = $value['id'];
                $countone++;
            }
            if ($cutprio[0] == "1") {
                $tagprio[1][$counttwo] = $value['id'];
                $counttwo++;
            }
            if ($cutprio[0] == "2") {
                $tagprio[2][$countthr] = $value['id'];
                $countthr++;
            }
        }        
    }
}

?>

<script src="https://www.wow-petguide.com/data/jquery-sortable.js"></script>
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

<td width="100%"><h class="megatitle">Strategy Settings</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>





<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('stratsettings');
    ?>
</div>




<div class="blogentryfirst">

<div class="articlebottom">
</div>

<table style="width:100%;">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>

<td>


<table width="100%" border="0">
<tr>
    <td>
    
        <table style="width: 85%;" class="profile">
            <tr class="profile">
                <th class="profile">
                    <table>
                        <tr>
                            <td>
                                <a href="?page=stratsettings" style="text-decoration: none"><button class="settings<? if ($page == "stratsettings") { echo "active"; } ?>" style="display: block">Strategy <? echo _("UM_BTSettings") ?></button></a>
                            </td>
                            <td>
                                <a href="?page=settings" style="text-decoration: none"><button class="settings<? if ($page == "settings") { echo "active"; } ?>" style="display: block"><? echo _("AS_Title"); ?></button></a>
                            </td>
                        </tr>
                    </table>
                </th>
            </tr>
        </table>
    </td>
</tr>
</table>




<? // ============= MODULE 1 - Tag priority in alternatives menu  ============= ?>


<table width="100%" border="0">
<tr>
<td>

<table width="85%" class="profile<? if ($regmailerror == "true"){ echo "hl"; } ?>">
    <tr class="profile">
        <th width="5" class="profile<? if ($regmailerror == "true"){ echo "hl"; } ?>">
            <table>
                <tr>
                    <td><img src="images/userdd_settings_grey.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><b>Priority of tags</td>
                </tr>
            </table>
        </th>
    </tr>

    <tr class="profile">
        <td class="profile">
            <p class="blogodd">
            Decide which tags should be considered more important than others when showing alternative strategies.<br>
            Tags in the green box will be moved higher up, while those in the red box will be moved down.
            <br><br>
            Drag the tags between and within brackets to set their importance.
            <br>
            <br>
            
            <div style="width: 200px; float: left; margin-left: 20px">
                <center><p class="blogodd"><b>Preferred</b></center>
                <ol class='tags_sorting' id='tags_one' style='background-color: #a3ba87'>
                   <? if ($tagprio[0][0] != "") {
                        foreach ($tagprio[0] as $key => $value) {
                            echo '<li id="'.$alltags[$value]['id'].'" class="tags_item">'.$alltags[$value]['Name'].'</li>';
                        }
                    } ?>                
               </ol>   
            </div>
            
            <div style="width: 200px; float: left; margin-left: 20px">
                 <center><p class="blogodd"><b>Neutral</b></center>    
                 <ol class='tags_sorting' id='tags_two' style='background-color: #aeaeae'>
                   <? if ($tagprio[1][0] != "") {
                        foreach ($tagprio[1] as $key => $value) {
                            echo '<li id="'.$alltags[$value]['id'].'" class="tags_item">'.$alltags[$value]['Name'].'</li>';
                        }
                   } ?>  
                </ol>   
            </div>
            
            <div style="width: 200px; float: left; margin-left: 20px">
                <center><p class="blogodd"><b>Unfavored</b></center>
                <ol class='tags_sorting' id='tags_three' style='background-color: #ba8787'>
                   <? if ($tagprio[2][0] != "") {
                        foreach ($tagprio[2] as $key => $value) {
                           echo '<li id="'.$alltags[$value]['id'].'" class="tags_item">'.$alltags[$value]['Name'].'</li>';
                        }
                   } ?>  
                </ol>   
            </div>

            <script>
            var adjustment;
            
            $("ol.tags_sorting").sortable({
              group: 'tags_sorting',
              pullPlaceholder: false,
              // animation on drop
              onDrop: function  ($item, container, _super) {
                var $clonedItem = $('<li/>').css({height: 0});
                $item.before($clonedItem);
                $clonedItem.animate({'height': $item.height()});
            
                $item.animate($clonedItem.position(), function  () {
                  $clonedItem.detach();
                  _super($item, container);
                });
              },
            
              // set $item relative to cursor position
              onDragStart: function ($item, container, _super) {
                var offset = $item.offset(),
                    pointer = container.rootGroup.pointer;
            
                adjustment = {
                  left: pointer.left - offset.left,
                  top: pointer.top - offset.top
                };
            
                _super($item, container);
              },
              onDrag: function ($item, position) {
                $item.css({
                  left: position.left - adjustment.left,
                  top: position.top - adjustment.top
                });
              }
            });
            </script>

            <div style="width: 100%; float: left; margin-left: 20px; margin-bottom: 20px">
                <button onclick="save_tag_settings(<? echo $user->id ?>,<? echo $user->ComSecret ?>,)" type="submit" tabindex="36" class="comedit"><? echo _("UP_BTSave") ?></button>
            </div>
            
        </td>
    </tr>

</table>
</table>
<br>

<? // ============= END OF MODULE 6.1 ============= ?>





</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
switch ($sendtoast) {
    case "pwadded":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("GR_PassChanged").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "namechanged":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("GR_NameChanged").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "addbnet":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GRBnetCon").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregfail":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_BnetDecl").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "bnetregsuccess":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GRBnetConY").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "bnetapierror":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_BnetFailed").'", duration: "10000", size: "large", location: "tc" });</script>';
        break;
    case "emailadded":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GREMSaved").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "mailremoved":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GREMRemoved").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "langchanged":
        echo '<script type="text/javascript">$.growl.notice({ message: "'._("AS_GRLngChanged").'", duration: "5000", size: "large", location: "tc" });</script>';
        break;
    case "genericerror":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("GR_GenError").'", duration: "7000", size: "large", location: "tc" });</script>';
        break;
    case "nowowaccess":
        echo '<script type="text/javascript">$.growl.error({ message: "'._("UP_WA_noaccess").'", duration: "7000", size: "large", location: "tc" });</script>';
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
