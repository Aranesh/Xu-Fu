<?php
require_once ('HTTP.php');
require_once ('Database.php');

// =============  Header with Page Title  ============= ?>

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><img src="https://www.wow-petguide.com/images/main_bg02_1.png"></td>
            <td width="100%"><h class="megatitle">List of all Battle Pets</font></td>
            <td><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>

<div class="article">
    <div class="articlebottom"></div>
<div>
    <p class="blogodd">
    <br>
    <br>

    <center>
      
    <div id="petlist">



        <table class="profile">
        
            <tr class="profile">
                <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td>
                                <table width="100%" id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:30 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                                <thead>
        
                                    <tr>
                                        <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="300"><p class="table-sortable-black" style="margin-left: 15px;"><? echo _("PetTableName") ?></p></th>
                                        <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><? echo _("ColChartLevel") ?></th>
                                        <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartQuality") ?></th>
                                        <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartBreed") ?></th>
                                        <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><? echo _("ColChartFamily") ?></th>
                                        <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColTableChartDupes") ?></th>
                                        <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartTrade") ?></th>
                                        <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartCollected") ?></th>
                                </tr>
        
                                    <tr>
                                        <th align="left" class="petlistheadersecond" width="300">
                                            <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                                        </th>
        
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:100px;" id="levelfilter" onchange="Table.filter(this,this)">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option class="petselect" value="25">25</option>
                                                <option class="petselect" value="function(val){return parseFloat(val)<25;}">< 25</option>
                                                <option class="petselect" value="function(val){return parseFloat(val)<20;}">< 20</option>
                                                <option class="petselect" value="function(val){return parseFloat(val)<15;}">< 15</option>
                                                <option class="petselect" value="function(val){return parseFloat(val)<10;}">< 10</option>
                                                <option class="petselect" value="function(val){return parseFloat(val)<5;}">< 5</option>
                                                <option class="petselect" value="1">1</option>
                                                <option class="petselect" value="function(val){return parseFloat(val)>0;}">1-25</option>
                                                <option class="petselect" value="-">-</option>
                                            </select>
                                        </th>
        
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:100px;" id="qualityfilter" onchange="Table.filter(this,this)">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option class="petselect" value="<? echo _("QualityRare") ?>"><? echo _("QualityRare") ?></option>
                                                <option class="petselect" value="<? echo _("QualityUncommon") ?>"><? echo _("QualityUncommon") ?></option>
                                                <option class="petselect" value="<? echo _("QualityCommon") ?>"><? echo _("QualityCommon") ?></option>
                                                <option class="petselect" value="<? echo _("QualityPoor") ?>"><? echo _("QualityPoor") ?></option>
                                                <option class="petselect" value="-">-</option>
                                            </select>
                                        </th>
        
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:70px;" id="breedfilter" onchange="Table.filter(this,this)">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option class="petselect" value="PP">PP</option>
                                                <option class="petselect" value="SS">SS</option>
                                                <option class="petselect" value="HH">HH</option>
                                                <option class="petselect" value="PB">PB</option>
                                                <option class="petselect" value="SB">SB</option>
                                                <option class="petselect" value="HB">HB</option>
                                                <option class="petselect" value="PS">PS</option>
                                                <option class="petselect" value="HS">HS</option>
                                                <option class="petselect" value="HP">HP</option>
                                                <option class="petselect" value="BB">BB</option>
                                                <option class="petselect" value="-">-</option>
                                            </select>
                                        </th>
        
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:150px;" id="familiesfilter" onchange="Table.filter(this,this)">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesHumanoid") ?>"><? echo _("PetFamiliesHumanoid") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesDragonkin") ?>"><? echo _("PetFamiliesDragonkin") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesFlying") ?>"><? echo _("PetFamiliesFlying") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesUndead") ?>"><? echo _("PetFamiliesUndead") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesCritter") ?>"><? echo _("PetFamiliesCritter") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesMagic") ?>"><? echo _("PetFamiliesMagic") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesElemental") ?>"><? echo _("PetFamiliesElemental") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesBeast") ?>"><? echo _("PetFamiliesBeast") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesAquatic") ?>"><? echo _("PetFamiliesAquatic") ?></option>
                                                <option class="petselect" value="<? echo _("PetFamiliesMechanical") ?>"><? echo _("PetFamiliesMechanical") ?></option>
                                            </select>
                                        </th>
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:90px;" id="uniquefilter" onchange="Table.filter(this,this)">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option class="petselect" value="function(val){return parseFloat(val)>0;}"><? echo _("FormComButtonYes"); ?></option>
                                                <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
                                                <option class="petselect" value=2>2</option>
                                                <option class="petselect" value="3">3</option>
                                            </select>
                                        </th>
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:90px;" id="tradeablefilter" onchange="Table.filter(this,this)">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option class="petselect" value="<? echo _("FormComButtonYes"); ?>"><? echo _("FormComButtonYes"); ?></option>
                                                <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
                                                <option class="petselect" value="N/A">N/A</option>
                                            </select>
                                        </th>
                                        <th align="center" class="petlistheadersecond">
                                            <select class="petselect" style="width:90px;" id="collectedfilter" onchange="Table.filter(this,this);">
                                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                                <option selected class="petselect" value="<? echo _("FormComButtonYes"); ?>"><? echo _("FormComButtonYes"); ?></option>
                                                <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
                                            </select>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
        
                                <?
                                foreach($all_pets as $pet) { ?>
                                    <tr class="petlist" <?
                                    if ($pet['Collected'] == FALSE) echo 'style="background-color: #d8a0a0" ';
                                    if ($pet['Duplicate'] == TRUE && $pet['InDB'] == TRUE) echo 'style="background-color: #ccca92" ';
                                    if ($pet['InDB'] == FALSE) echo 'style="background-color: #ff6363" ';
                                    ?>>
                                        <td class="petlist" align="left" style="padding-left: 12px;"><div style="white-space:nowrap"><a class="petlist" href="http://<? echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<? echo $pet['PetID'] ?>" target="_blank"><? echo $pet['Name'] ?></a></div></td>
                                        <td align="center" class="petlist"><p class="blogodd"><?
                                            if ($pet['Level'] == 0) echo "-";
                                            else echo $pet['Level']; ?></td>
                                        <td align="center" class="petlist"><p><?
                                            if ($pet['Quality'] == 3) { echo '<font color="#0058a5">'._("QualityRare"); }
                                            if ($pet['Quality'] == 2) { echo '<font color="#147e09">'._("QualityUncommon"); }
                                            if ($pet['Quality'] == 1) { echo '<font color="#ffffff">'._("QualityCommon"); }
                                            if ($pet['Quality'] == 0) { echo '<font color="#4d4d4d">'._("QualityPoor"); }
                                            if ($pet['Quality'] == 22) { echo '<font color="#000000">-'; } ?></td>
                                        <td align="center" class="petlist"><p class="blogodd"><? echo $pet['Breed'] ?></td>
                                        <td align="left" class="petlist" style="padding-left: 12px;"><p class="blogodd"><? echo $pet['Family'] ?></td>
                                        <td align="center" class="petlist"><p class="blogodd"><?
                                            if ($pet['Duplicate'] == TRUE) echo $pet['Dupecount'];
                                            else echo _("FormComButtonNo"); ?></td>
                                        <td align="center" class="petlist"><p class="blogodd"><?
                                            if ($pet['Cageable'] == "1") echo _("FormComButtonYes");
                                            else if ($pet['Cageable'] == "2") echo _("FormComButtonNo");
                                            else if ($pet['Cageable'] == "0") echo "N/A"; ?></td>
                                        <td align="center" class="petlist"><p class="blogodd"><?
                                            if ($pet['Collected'] == TRUE) echo _("FormComButtonYes");
                                            else echo _("FormComButtonNo"); ?></td>
                                    </tr>
                                <? } ?>
        
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                        <td colspan="2" align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                                        <td colspan="2" align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                        <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><? echo _("ColTableReset") ?></a></div></td>
                                    </tr>
                                </tfoot>
                              </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        
        </table>
        
        <table>
            <tr>
                <td style="height: 8px;"></td>
            </tr>
            <tr>
                <td style="width: 15px;"></td>
                <td style="width: 18px; background-color: #d8a0a0"> </td>
                <td><p class="blogodd"><? echo _("ColChartMissing") ?></td>
                <td style="width: 15px;"></td>
                <td style="width: 18px; background-color: #ccca92"> </td>
                <td><p class="blogodd"><? echo _("ColTableChartDupes") ?></td>
            </tr>
        </table>
    </div>

 


<br>
<div class="maincomment">
    <br>
    <table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
    <tr><td width="100%" align="center">
    <br><br>
    <?
    
    // ==== COMMENT SYSTEM 2.0 FOR MAIN ARTICLES HAPPENS HERE ====
    // print_comments_outer("0",$mainselector,"medium");
    ?>  
</div>
<? die;


