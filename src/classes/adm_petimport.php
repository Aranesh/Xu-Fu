<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('BattleNet.php');
require_once ('Database.php');
require_once ('Growl.php');
require_once ('HTML.php');
require_once ('HTTP.php');
require_once ('Util.php');
require_once ('Admin.php');

$command = \HTTP\argument_POST_or_GET_or_default ('command', FALSE);
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
            <td width="100%"><h class="megatitle">Administration - Pet Import</h></td>
            <td><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>



<div class="remodal-bg leftmenu">
    <? print_profile_menu('admin'); ?>
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

    <? print_admin_menu('adm_petimport'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile"><br> <?


            // ======================= Main Menu =======================

            if (!$command) { ?>
                <p class="blogodd">Pet and spell data is mainly available through the Blizzard API. On this page you can update and edit the lists, based on their three different databases:
                <br>

                <ol>
                    <li><p class="blogodd"><b>User Pets.</b><br>
                        Contains all pets that players can obtain (including non-battle pets).<br>
                        <a class="wowhead" href="?page=adm_petimport&command=checkuserpets">Check full list</a> - Check the entire pet list adjust where necessary.<br><br>
                    </li>
                    <li><p class="blogodd"><b>NPC Pets.</b><br>
                        Contains pets that NPCs are using.<br>
                        <a class="wowhead" href="?page=adm_petimport&command=checknpcpets">Check for new NPC Pets</a> - This will go through ALL species IDs one by one and import them as NPC pets, if they are not on the User Pets list.<br><br>
                    </li>
                    <li><p class="blogodd"><b>Pet Spells</b><br>
                        Contains all pet abilities in the game.<br>
                        <a class="wowhead" href="?page=adm_petimport&command=checkspells">Check for new spells</a> - Automatically check if new spells need to be imported. These are all spells used by NPC and user pets. Best use when the other two lists are up to date.<br>
                    </li>
                </ol>
                <? }


            // ======================= User Pet Import =======================
            // Importing or updating a user pet

            if ($command == "importpet") {
                $importspecies = \HTTP\argument_POST ('species', FALSE);
                 \ADMIN\import_single_pet($importspecies);
                $command = "checkuserpets";
            }


            /* Import of ALL pets anew
            $import_all_pets = $all_pets;
            sortBy('Species', $import_all_pets);

            foreach ($import_all_pets as $key => $value) {
                if ($value['Species'] > 2712) {
                    \ADMIN\import_single_pet($value['Species']);
                }
            }
            */

            // Listing all user pets into table

            if ($command == "checkuserpets")  { ?>
            <p class="blogodd">
            <?

                $pet_masterlist = blizzard_api_pets_masterlist("us", "en_US");
                $pet_masterlist = json_decode($pet_masterlist, TRUE);

                foreach ($pet_masterlist['pets'] as $key => $value) {
                    $mergepets[$value['id']]['API'] = TRUE;
                    $mergepets[$value['id']]['Name_en_US'] = $value['name'];
                    $mergepets[$value['id']]['Species'] = $value['id'];
                }

                $dbpetsdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID > 20 ORDER BY RematchID");
                while ($thispet = mysqli_fetch_object($dbpetsdb)) {
                    $mergepets[$thispet->RematchID]['CanBattle'] = $thispet->Special;
                    $mergepets[$thispet->RematchID]['DB'] = TRUE;
                    $mergepets[$thispet->RematchID]['Name_en_US'] = $thispet->Name;
                    $mergepets[$thispet->RematchID]['Name_de_DE'] = $thispet->Name_de_DE;
                    $mergepets[$thispet->RematchID]['Name_fr_FR'] = $thispet->Name_fr_FR;
                    $mergepets[$thispet->RematchID]['Name_it_IT'] = $thispet->Name_it_IT;
                    $mergepets[$thispet->RematchID]['Name_es_ES'] = $thispet->Name_es_ES;
                    $mergepets[$thispet->RematchID]['Name_pl_PL'] = $thispet->Name_pl_PL;
                    $mergepets[$thispet->RematchID]['Name_pt_PT'] = $thispet->Name_pt_PT;
                    $mergepets[$thispet->RematchID]['Name_ru_RU'] = $thispet->Name_ru_RU;
                    $mergepets[$thispet->RematchID]['Name_es_MX'] = $thispet->Name_es_MX;
                    $mergepets[$thispet->RematchID]['Name_pt_BR'] = $thispet->Name_pt_BR;
                    $mergepets[$thispet->RematchID]['Name_ko_KR'] = $thispet->Name_ko_KR;
                    $mergepets[$thispet->RematchID]['Name_zh_TW'] = $thispet->Name_zh_TW;
                    $mergepets[$thispet->RematchID]['Description_en_US'] = $thispet->Description_en_US;
                    $mergepets[$thispet->RematchID]['Description_de_DE'] = $thispet->Description_de_DE;
                    $mergepets[$thispet->RematchID]['Description_fr_FR'] = $thispet->Description_fr_FR;
                    $mergepets[$thispet->RematchID]['Description_it_IT'] = $thispet->Description_it_IT;
                    $mergepets[$thispet->RematchID]['Description_es_ES'] = $thispet->Description_es_ES;
                    $mergepets[$thispet->RematchID]['Description_pl_PL'] = $thispet->Description_pl_PL;
                    $mergepets[$thispet->RematchID]['Description_pt_PT'] = $thispet->Description_pt_PT;
                    $mergepets[$thispet->RematchID]['Description_ru_RU'] = $thispet->Description_ru_RU;
                    $mergepets[$thispet->RematchID]['Description_es_MX'] = $thispet->Description_es_MX;
                    $mergepets[$thispet->RematchID]['Description_pt_BR'] = $thispet->Description_pt_BR;
                    $mergepets[$thispet->RematchID]['Description_ko_KR'] = $thispet->Description_ko_KR;
                    $mergepets[$thispet->RematchID]['Description_zh_TW'] = $thispet->Description_zh_TW;
                    $mergepets[$thispet->RematchID]['Source_en_US'] = $thispet->Source_en_US;
                    $mergepets[$thispet->RematchID]['Source_de_DE'] = $thispet->Source_de_DE;
                    $mergepets[$thispet->RematchID]['Source_fr_FR'] = $thispet->Source_fr_FR;
                    $mergepets[$thispet->RematchID]['Source_it_IT'] = $thispet->Source_it_IT;
                    $mergepets[$thispet->RematchID]['Source_es_ES'] = $thispet->Source_es_ES;
                    $mergepets[$thispet->RematchID]['Source_pl_PL'] = $thispet->Source_pl_PL;
                    $mergepets[$thispet->RematchID]['Source_pt_PT'] = $thispet->Source_pt_PT;
                    $mergepets[$thispet->RematchID]['Source_ru_RU'] = $thispet->Source_ru_RU;
                    $mergepets[$thispet->RematchID]['Source_es_MX'] = $thispet->Source_es_MX;
                    $mergepets[$thispet->RematchID]['Source_pt_BR'] = $thispet->Source_pt_BR;
                    $mergepets[$thispet->RematchID]['Source_ko_KR'] = $thispet->Source_ko_KR;
                    $mergepets[$thispet->RematchID]['Source_zh_TW'] = $thispet->Source_zh_TW;
                    $mergepets[$thispet->RematchID]['PetID'] = $thispet->PetID;
                    $mergepets[$thispet->RematchID]['Species'] = $thispet->RematchID;
                    $mergepets[$thispet->RematchID]['Family'] = $thispet->Family;
                    $mergepets[$thispet->RematchID]['Skill1'] = $thispet->Skill1;
                    $mergepets[$thispet->RematchID]['Skill2'] = $thispet->Skill2;
                    $mergepets[$thispet->RematchID]['Skill3'] = $thispet->Skill3;
                    $mergepets[$thispet->RematchID]['Skill4'] = $thispet->Skill4;
                    $mergepets[$thispet->RematchID]['Skill5'] = $thispet->Skill5;
                    $mergepets[$thispet->RematchID]['Skill6'] = $thispet->Skill6;
                    $mergepets[$thispet->RematchID]['Health'] = $thispet->Health;
                    $mergepets[$thispet->RematchID]['Power'] = $thispet->Power;
                    $mergepets[$thispet->RematchID]['Speed'] = $thispet->Speed;
                    $mergepets[$thispet->RematchID]['Breeds'] = "";
                        if ($thispet->BB == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = "BB,";
                        }
                        if ($thispet->PP == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."PP,";
                        }
                        if ($thispet->SS == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."SS,";
                        }
                        if ($thispet->HH == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."HH,";
                        }
                        if ($thispet->HP == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."HP,";
                        }
                        if ($thispet->PS == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."PS,";
                        }
                        if ($thispet->HS == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."HS,";
                        }
                        if ($thispet->PB == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."PB,";
                        }
                        if ($thispet->SB == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."SB,";
                        }
                        if ($thispet->HB == "1") {
                            $mergepets[$thispet->RematchID]['Breeds'] = $mergepets[$thispet->RematchID]['Breeds']."HB,";
                        }
                        $mergepets[$thispet->RematchID]['Breeds'] = substr($mergepets[$thispet->RematchID]['Breeds'], 0, -1);
                        $mergepets[$thispet->RematchID]['Cageable'] = $thispet->Cageable;
                        $mergepets[$thispet->RematchID]['Capturable'] = $thispet->Capturable;
                        $mergepets[$thispet->RematchID]['Horde_Only'] = $thispet->Horde_Only;
                        $mergepets[$thispet->RematchID]['Alliance_Only'] = $thispet->Alliance_Only;
                        $mergepets[$thispet->RematchID]['Source'] = $thispet->Source;
                        $mergepets[$thispet->RematchID]['Obtainable'] = $thispet->Obtainable;
                        $mergepets[$thispet->RematchID]['Difficulty'] = $thispet->Difficulty;
                        $mergepets[$thispet->RematchID]['Unique'] = $thispet->Unique;
                        $mergepets[$thispet->RematchID]['DefRarity'] = $thispet->DefRarity;
                }

                // Calculations and evaluations
                /* Priorities:
                 * 1 = In Xufu DB but not in API
                 * 2 = In API but not in Xufu DB
                 * 3 = Base stats missing
                 * 3 = Breed data missing
                 * 6 = Some Additional Meta data missing
                 * 99 = All good!
                */

                foreach ($mergepets as $key => $value) {
                    $mergepets[$value['Species']]['Prio'] = "99";

                    if ($value['Cageable'] == "0" OR $value['Obtainable'] == "0" OR $value['Difficulty'] == "0" OR $value['Unique'] == "0" OR $value['DefRarity'] == "0") {
                        $mergepets[$value['Species']]['Prio'] = "6";
                    }

                    if ($value['Breeds'] == "") {
                        $mergepets[$value['Species']]['Prio'] = "4";
                    }
                    if ($value['Health'] == "0" OR $value['Power'] == "0" OR $value['Speed'] == "0") {
                        $mergepets[$value['Species']]['Prio'] = "3";
                    }

                    if ($value['API'] == TRUE && $value['DB'] != TRUE) {
                        $mergepets[$value['Species']]['MissingDB'] = "Yes";
                        $mergepets[$value['Species']]['Prio'] = "2";
                        $mergepets[$value['Species']]['Hideall'] = TRUE;
                    }

                    if ($value['API'] != TRUE && $value['DB'] == TRUE) {
                        $mergepets[$value['Species']]['MissingAPI'] = "Yes";
                        $mergepets[$value['Species']]['Prio'] = "1";
                    }
                }

                sortBy('Species', $mergepets, 'desc');
                sortBy('Prio', $mergepets, 'asc');

                // echo "<pre>";
                // print_r($mergepets);

                ?>

                The list below shows all user pets. Use the drop downs to make adjustments or buttons to update/import data.
                If a pet is in a section with a red headline, there is data missing. The further down a pet is, the more information is available.<br>
                Ideally, all pets should be filled with all data. They are in the green section at the bottom then.
                <table class="admin" style="width: 100%">
                    <tr>
                        <th class="admin">Species</th>
                        <th class="admin">NPC-ID</th>
                        <th class="admin">Name</th>
                        <th class="admin">Family</th>
                        <th class="admin">Skills</th>
                        <th class="admin">Breeds</th>
                        <th class="admin">Details</th>
                        <th class="admin">
                            <div class="tt_loc" data-tooltip-content="#tt_source"><p class="smallodd">Source</div>
                                <div style="display: none">
                                    <span id="tt_source">
                                        Source is currently limited to TCG or Shop pets. It might be extended in the future.
                                    </span>
                                </div>
                        </th>
                        <th class="admin">
                            <div class="tt_loc" data-tooltip-content="#tt_obt"><p class="smallodd">Obtainable</div>
                                <div style="display: none">
                                    <span id="tt_obt">
                                        Obtainable means that it's still possible to get this pet in-game. Non-obtainable are for example the bat from Karazhans during a previous addon-launch.<br>
                                        Extremely difficult to obtain pets like past Blizzcon pets can be considered non-obtainable
                                    </span>
                                </div>
                        </th>
                        <th class="admin">
                            <div class="tt_loc" data-tooltip-content="#tt_diff"><p class="smallodd">Difficulty</div>
                                <div style="display: none">
                                    <span id="tt_diff">
                                        Difficulty means how hard it is to acquire a pet. 1 is the easiest, 6 the hardest. Some examples:<br>
                                        1: Any wild pet. Basic vendor pets. Very cheap pets.<br>
                                        2: Pets behind easy achievements or quests but non-tradeable.<br>
                                        3: Pets from rare mobs that need some farming (or are expensive in the AH)<br>
                                        4: Pets behind difficult achievements like current raid tiers or very difficult pet battle achievements<br>
                                        5: Store and TCG pets<br>
                                        6: Extremely rare or expensive pets, like old Blizzcon pets.
                                    </span>
                                </div>
                        </th>
                        <th class="admin">
                            <div class="tt_loc" data-tooltip-content="#tt_unique"><p class="smallodd">Max Copies</div>
                                <div style="display: none">
                                    <span id="tt_unique">
                                        Most pets can be owned multiple times up to 3 but some you can only acquire once, making them unique in your collection.
                                    </span>
                                </div>
                        </th>
                        <th class="admin">
                            <div class="tt_loc" data-tooltip-content="#tt_defrar"><p class="smallodd">Default Rarity</div>
                                <div style="display: none">
                                    <span id="tt_defrar">
                                        This is the default rarity a pet has when it's being learned. Please note that this can be different from the item quality (for example, some blue pet items become green pets after being learned)
                                    </span>
                                </div>
                        </th>
                        <th class="admin">Options</th>
                    </tr>

                <?
                $countpets = "0";
                foreach ($mergepets as $key => $value) {

                    if ($countpets == "30") {
                        $countpets = "0"; ?>
                        <tr>
                            <th class="admin">Species</th>
                            <th class="admin">NPC-ID</th>
                            <th class="admin">Name</th>
                            <th class="admin">Family</th>
                            <th class="admin">Skills</th>
                            <th class="admin">Breeds</th>
                            <th class="admin">Details</th>
                            <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_source"><p class="smallodd">Source</div></th>
                            <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_obt"><p class="smallodd">Obtainable</div></th>
                            <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_diff"><p class="smallodd">Difficulty</div></th>
                            <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_unique"><p class="smallodd">Max Copies</div></th>
                            <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_defrar"><p class="smallodd">Default Rarity</div></th>
                            <th class="admin">Options</th>
                        </tr>
                    <?
                    }

                    if ($value['Prio'] == "1" && $header1 != TRUE) {
                        echo '<tr class="adminpetred"><td colspan="13"><br><p class="blogodd"><b><center>Below pets are in Xu-Fus database, but they do not seem to be in Blizzards API. Consider deleting them.<br><br></td></tr>';
                        $header1 = TRUE;
                    }
                    if ($value['Prio'] == "2" && $header2 != TRUE) {
                        echo '<tr class="adminpetred"><td colspan="13"><br><p class="blogodd"><b><center>Below pets were found in WoWs API, but are not in Xu-Fus database. You should import them.<br><br></td></tr>';
                        $header2 = TRUE;
                    }
                    if (($value['Prio'] == "3" OR $value['Prio'] == "4") && $header3 != TRUE) {
                        echo '<tr class="adminred"><td colspan="13"><br><p class="blogodd"><b><center>Base stats and/or Breeds are missing for the below pets. Use the Stat/Breed importer to update them.<br><br></td></tr>';
                        $header3 = TRUE;
                    }
                    if ($value['Prio'] == "5" && $header5 != TRUE) {
                        echo '<tr class="adminred"><td colspan="13"><br><p class="blogodd"><b><center>For the following pets, some localization data is missing<br><br></td></tr>';
                        $header5 = TRUE;
                    }
                    if ($value['Prio'] == "6" && $header6 != TRUE) {
                        echo '<tr class="adminpetred"><td colspan="13"><br><p class="blogodd"><b><center>The pets below this line are missing at least one of the additional fields Cageable, Source, Obtainable, Difficulty, Unique or Default Rarity.<br><br></td></tr>';
                        $header6 = TRUE;
                    }
                    if ($value['Prio'] == "99" && $header99 != TRUE) {
                        echo '<tr class="admingreen"><td colspan="13"><br><p class="blogodd"><b><center>Below pets passed all checks with flying colors. Yay them!<br><br></td></tr>';
                        $header99 = TRUE;
                    }
                    ?>
                    <tr class="admin">
                    <td class="admin"><? echo $value['Species'] ?></td>

                    <? /*  OLD - this contains a function that allows editing the NPC-ID - not needed anymore, info is via API
                    <td class="admin" style="white-space: nowrap"><? if ($value['Hideall'] != TRUE) { ?><input class="petselect" value="<? echo $value['PetID'] ?>" style="width: 80px" id="new_npcid_<? echo $value['Species'] ?>">
                    <input class="comedit" type="submit" onclick="adm_update_petstat('npcid', '<? echo $value['Species'] ?>')" value="✓"><? } ?></td>
                    */ // New one below:?>
                    <td class="admin"><? echo $value['PetID'] ?></td>


                    <td class="admin"><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/npc=<? echo $value['PetID'] ?>'><? echo $value['Name_en_US'] ?></a></td>

                    <td class="admin"><center><? if ($value['Hideall'] == TRUE) { echo '<center><p class="smallodd">-'; }
                        else { echo $value['Family']; } ?>
                    </td>

                    <td class="admin">
                        <?
                        if ($value['Hideall'] != TRUE) { ?>
                        <div id="skills_<? echo $key ?>"><a class="pr_contact" style="cursor: pointer" onclick="$('#skillsf_<? echo $key ?>').show();$('#skills_<? echo $key ?>').hide()"><center>Show</a></div>
                        <div style="display: none" id="skillsf_<? echo $key ?>">
                            <b>Skill1:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['Skill1'] ?>'><? echo $value['Skill1'] ?></a><br>
                            <b>Skill2:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['Skill2'] ?>'><? echo $value['Skill2'] ?></a><br>
                            <b>Skill3:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['Skill3'] ?>'><? echo $value['Skill3'] ?></a><br>
                            <b>Skill4:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['Skill4'] ?>'><? echo $value['Skill4'] ?></a><br>
                            <b>Skill5:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['Skill5'] ?>'><? echo $value['Skill5'] ?></a><br>
                            <b>Skill6:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['Skill6'] ?>'><? echo $value['Skill6'] ?></a>
                        </div>
                        <? }
                        else { echo '<center><p class="smallodd">-'; } ?>
                    </td>

                    <td class="admin">
                        <?
                        if ($value['Hideall'] != TRUE && $value['Breeds'] != "") { echo $value['Breeds']; }
                        else {
                            if ($value['Breeds'] == "") echo '<center><p class="smallodd"><b>Missing';
                            else echo '<center><p class="smallodd">-'; }?>
                    </td>

                    <td class="admin">
                        <?
                        if ($value['Hideall'] != TRUE) { ?>
                        <div id="bstats_<? echo $key ?>"><a class="pr_contact" style="cursor: pointer" onclick="$('#bstatsf_<? echo $key ?>').show();$('#bstats_<? echo $key ?>').hide()"><center>Show</a></div>
                        <div style="display: none; white-space: nowrap" id="bstatsf_<? echo $key ?>">
                            <b>Health:</b> <? echo $value['Health'] ?></a><br>
                            <b>Power:</b> <? echo $value['Power'] ?></a><br>
                            <b>Speed:</b> <? echo $value['Speed'] ?></a><br>
                                <?
                                if ($value['Hideall'] != TRUE) {
                                    if ($value['Cageable'] == "0") { echo "<b>Cageable:</b> N/A<br>"; }
                                    if ($value['Cageable'] == "1") { echo "<b>Cageable:</b> Yes<br>"; }
                                    if ($value['Cageable'] == "2") { echo "<b>Cageable:</b> No<br>"; }

                                    if ($value['Capturable'] == "1") { echo "<b>Capturable:</b> Yes<br>"; }
                                    if ($value['Capturable'] == "0") { echo "<b>Capturable:</b> No<br>"; }

                                    if ($value['Horde_Only'] == "1") { echo "<b>Faction:</b> Horde Only<br>"; }
                                    if ($value['Alliance_Only'] == "1") { echo "<b>Faction:</b> Alliance Only<br>"; }
                                    if ($value['Alliance_Only'] == "0" && $value['Horde_Only'] == "0") { echo "<b>Faction:</b> None<br>"; }
                                } ?>
                        </div>
                        <? }
                        else {
                            if ($value['Prio'] == "3") echo '<center><p class="smallodd"><b>Missing';
                            else echo '<center><p class="smallodd">-'; }?>
                    </td>

                    <td class="admin"><? if ($value['Hideall'] == TRUE) { echo '<center><p class="smallodd">-'; }
                        else { ?>
                            <select class="petselect" id="adm_source_<? echo $value['Species'] ?>" onchange="adm_update_petstat('source', '<? echo $value['Species'] ?>')">
                                <option class="petselect" value="0" <? if ($value['Source'] == "0") {echo "selected";} ?>>N/A</option>
                                <option class="petselect" value="3" <? if ($value['Source'] == "3") {echo "selected";} ?>>Normal</option>
                                <option class="petselect" value="1" <? if ($value['Source'] == "1") {echo "selected";} ?>>Shop</option>
                                <option class="petselect" value="2" <? if ($value['Source'] == "2") {echo "selected";} ?>>TCG</option>
                            </select>
                        <? } ?>
                    </td>

                    <td class="admin"><? if ($value['Hideall'] == TRUE) { echo '<center><p class="smallodd">-'; }
                        else { ?>
                            <select class="petselect" id="adm_obtainable_<? echo $value['Species'] ?>" onchange="adm_update_petstat('obtainable', '<? echo $value['Species'] ?>')">
                                <option class="petselect" value="0" <? if ($value['Obtainable'] == "0") {echo "selected";} ?>>N/A</option>
                                <option class="petselect" value="1" <? if ($value['Obtainable'] == "1") {echo "selected";} ?>>Yes</option>
                                <option class="petselect" value="2" <? if ($value['Obtainable'] == "2") {echo "selected";} ?>>No</option>
                            </select>
                        <? } ?>
                    </td>

                    <td class="admin"><? if ($value['Hideall'] == TRUE) { echo '<center><p class="smallodd">-'; }
                        else { ?>
                            <select class="petselect" id="adm_difficulty_<? echo $value['Species'] ?>" onchange="adm_update_petstat('difficulty', '<? echo $value['Species'] ?>')">
                                <option class="petselect" value="0" <? if ($value['Difficulty'] == "0") {echo "selected";} ?>>N/A</option>
                                <option class="petselect" value="1" <? if ($value['Difficulty'] == "1") {echo "selected";} ?>>1</option>
                                <option class="petselect" value="2" <? if ($value['Difficulty'] == "2") {echo "selected";} ?>>2</option>
                                <option class="petselect" value="3" <? if ($value['Difficulty'] == "3") {echo "selected";} ?>>3</option>
                                <option class="petselect" value="4" <? if ($value['Difficulty'] == "4") {echo "selected";} ?>>4</option>
                                <option class="petselect" value="5" <? if ($value['Difficulty'] == "5") {echo "selected";} ?>>5</option>
                                <option class="petselect" value="6" <? if ($value['Difficulty'] == "6") {echo "selected";} ?>>6</option>
                            </select>
                        <? } ?>
                    </td>

                    <td class="admin"><? if ($value['Hideall'] == TRUE) { echo '<center><p class="smallodd">-'; }
                        else { ?>
                            <select class="petselect" id="adm_unique_<? echo $value['Species'] ?>" onchange="adm_update_petstat('unique', '<? echo $value['Species'] ?>')">
                                <option class="petselect" value="0" <? if ($value['Unique'] == "0") {echo "selected";} ?>>N/A</option>
                                <option class="petselect" value="1" <? if ($value['Unique'] == "1") {echo "selected";} ?>>1</option>
                                <option class="petselect" value="3" <? if ($value['Unique'] == "3") {echo "selected";} ?>>3</option>
                            </select>
                        <? } ?>
                    </td>

                    <td class="admin"><? if ($value['Hideall'] == TRUE) { echo '<center><p class="smallodd">-'; }
                        else { ?>
                            <select class="petselect" id="adm_defrarity_<? echo $value['Species'] ?>" onchange="adm_update_petstat('defrarity', '<? echo $value['Species'] ?>')">
                                <option class="petselect" value="0" <? if ($value['DefRarity'] == "0") {echo "selected";} ?>>N/A</option>
                                <option class="petselect" value="1" <? if ($value['DefRarity'] == "1") {echo "selected";} ?>>Grey</option>
                                <option class="petselect" value="2" <? if ($value['DefRarity'] == "2") {echo "selected";} ?>>White</option>
                                <option class="petselect" value="3" <? if ($value['DefRarity'] == "3") {echo "selected";} ?>>Green</option>
                                <option class="petselect" value="4" <? if ($value['DefRarity'] == "4") {echo "selected";} ?>>Blue</option>
                            </select>
                        <? } ?>
                    </td>

                    <td class="admin" style="white-space: nowrap">
                        <form action="index.php?page=adm_petimport&command=importpet" style="display: inline" method="POST">
                            <input type="hidden" name="species" value="<? echo $value['Species'] ?>">
                            <input class="cominputmedium" type="submit" value="<? if ($value['Prio'] == "2") { echo "Import"; } else { echo "Update"; } ?>">
                        </form>

                     <? /* DELETE Option - Reactivate only if required if ($value['Hideal'l] != TRUE) { ?>
                    <button class="comdelete" data-remodal-target="modal_del_<? echo $value['Species'] ?>">Delete</button>

                    <div class="remodal remodalstratedit" data-remodal-id="modal_del_<? echo $value['Species'] ?>">
                        <form enctype="multipart/form-data" action="index.php?Strategy=<? echo $strat->id ?>" method="POST">
                            <input type="hidden" name="save_td" value="true">
                            <table width="350" class="profile">
                                <tr class="profile">
                                    <td class="collectionbordertwo">
                                        Are you sure you want to delete this pet? This cannot be undone! You will need to reimport the pet to add it again.
                                        <input type="submit" class="comdelete" value="Delete Pet">
                                        <input data-remodal-action="close" class="comedit" value="<? echo _("FormButtonCancel"); ?>">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>

                    <script>
                    var options = {
                        hashTracking: false
                    };
                    $('[data-remodal-id=modal_del_<? echo $value['Species'] ?>]').remodal(options);
                    </script>

                 <? } */ ?>

                    </td>

                    </tr>
                <?
                $countpets++;
                }
            }


            // ======= NPC Pets




    if ($command == "checknpcpets")  { ?>
        <p class="blogodd">This process will go through all Species IDs one by one and check if there is a pet available through the API. If yes and it's not a user pet, it will be imported as an NPC pet. <br>
        This will likely include a lot of unused entries, test pets and weird things that are not available in the game. It's OK to just ignore those.<br><br>

        <button class="comsubmit" onclick="$('.hiddenrow').show();">Show All Rows</button>
        <button class="comsubmit" onclick="$('.hiddenrow').hide();">Hide non-NPCs</button>
        <table class="admin" style="width: 100%">
            <tr>
                <th class="admin">Species</th>
                <th class="admin">NPC-ID</th>
                <th class="admin">Name</th>
                <th class="admin">Localization</th>
                <th class="admin">Family</th>
                <th class="admin">Skills</th>
                <th class="admin">Icon</th>
                <th class="admin">Result</th>
                <th class="admin">Options</th>
            </tr>

        <?
        $token = blizzard_api_cache_token('eu', battlenet_api_client_id, battlenet_api_client_secret, 'data/blizzard_api_token.json');
        $query_path = '/wow/pet/';
        $apipets = blizzard_api_query('eu', $query_path, $token);
        $apipets = json_decode($apipets, TRUE);
        foreach ($apipets['pets'] as $key => $value) {
            $userpets[$value['stats']['speciesId']]['API'] = TRUE;
            $userpets[$value['stats']['speciesId']]['Name'] = $value['name'];
            $userpets[$value['stats']['speciesId']]['PetID'] = $value['creatureId'];
            $userpets[$value['stats']['speciesId']]['Species'] = $value['stats']['speciesId'];
            $userpets[$value['stats']['speciesId']]['Family'] = ucwords($value['family']);
            $userpets[$value['stats']['speciesId']]['Skill1'] = $allpets[$value['creatureId']]['Skill1'];
            $userpets[$value['stats']['speciesId']]['Skill2'] = $allpets[$value['creatureId']]['Skill2'];
            $userpets[$value['stats']['speciesId']]['Skill3'] = $allpets[$value['creatureId']]['Skill3'];
            $userpets[$value['stats']['speciesId']]['Skill4'] = $allpets[$value['creatureId']]['Skill4'];
            $userpets[$value['stats']['speciesId']]['Skill5'] = $allpets[$value['creatureId']]['Skill5'];
            $userpets[$value['stats']['speciesId']]['Skill6'] = $allpets[$value['creatureId']]['Skill6'];
        }

        $dbpetsdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE Species > 0");
        while ($thispet = mysqli_fetch_object($dbpetsdb)) {
            $npcpets[$thispet->Species]['Name'] = $thispet->Name;
            $npcpets[$thispet->Species]['PetID'] = $thispet->PetID;
            $npcpets[$thispet->Species]['Species'] = $thispet->Species;
            $npcpets[$thispet->Species]['Skill1'] = $thispet->Skill1;
            $npcpets[$thispet->Species]['Skill2'] = $thispet->Skill2;
            $npcpets[$thispet->Species]['Skill3'] = $thispet->Skill3;
            $npcpets[$thispet->Species]['Skill4'] = $thispet->Skill4;
            $npcpets[$thispet->Species]['Skill5'] = $thispet->Skill5;
            $npcpets[$thispet->Species]['Skill6'] = $thispet->Skill6;
            $npcpets[$thispet->Species]['Family'] = $thispet->Family;
            $npcpets[$thispet->Species]['Icon'] = $thispet->Icon;
            $npcpets[$thispet->Species]['Name_de_DE'] = $thispet->Name_de_DE;
            $npcpets[$thispet->Species]['Name_fr_FR'] = $thispet->Name_fr_FR;
            $npcpets[$thispet->Species]['Name_it_IT'] = $thispet->Name_it_IT;
            $npcpets[$thispet->Species]['Name_es_ES'] = $thispet->Name_es_ES;
            $npcpets[$thispet->Species]['Name_pl_PL'] = $thispet->Name_pl_PL;
            $npcpets[$thispet->Species]['Name_pt_PT'] = $thispet->Name_pt_PT;
            $npcpets[$thispet->Species]['Name_ru_RU'] = $thispet->Name_ru_RU;
            $npcpets[$thispet->Species]['Name_es_MX'] = $thispet->Name_es_MX;
            $npcpets[$thispet->Species]['Name_pt_BR'] = $thispet->Name_pt_BR;
            $npcpets[$thispet->Species]['Name_ko_KR'] = $thispet->Name_ko_KR;
            $npcpets[$thispet->Species]['Name_zh_TW'] = $thispet->Name_zh_TW;
        }

        $startfrom = $_POST['startfrom'];
        if ($startfrom) {
            $checkspecies = $startfrom;
        }
        else {
            $checkspecies = "1";
        }

        $numimports = "100";
        $i = "0";
        $countpets = "0";
        while ($i < $numimports) {
            $thispet = "";
            if ($userpets[$checkspecies]['API'] == TRUE) {
                if ($npcpets[$checkspecies]['Species'] != "") {
                    mysqli_query($dbcon, "DELETE FROM PetsNPC WHERE Species = '$checkspecies'") OR die(mysqli_error($dbcon));
                }
                $thispet = $userpets[$checkspecies];
                $thispet['Result'] = "User Pet";
                $thispet['hide'] = FALSE;
                $thispet['hideloc'] = TRUE;
                $thispet['hideicon'] = TRUE;
            }
            else {
                if ($npcpets[$checkspecies]['Name'] != "" && $npcpets[$checkspecies]['PetID'] != "0" && $npcpets[$checkspecies]['Family'] != "" && $npcpets[$checkspecies]['Icon'] != "" && $npcpets[$checkspecies]['Name_de_DE'] != "" &&
                    $npcpets[$checkspecies]['Name_fr_FR'] != "" && $npcpets[$checkspecies]['Name_it_IT'] != "" && $npcpets[$checkspecies]['Name_es_ES'] != "" &&
                    $npcpets[$checkspecies]['Name_pl_PL'] != "" && $npcpets[$checkspecies]['Name_pt_PT'] != "" && $npcpets[$checkspecies]['Name_ru_RU'] != "" &&
                    $npcpets[$checkspecies]['Name_ru_RU'] != "" && $npcpets[$checkspecies]['Name_es_MX'] != "" && $npcpets[$checkspecies]['Name_pt_BR'] != "" &&
                    $npcpets[$checkspecies]['Name_ko_KR'] != "" && $npcpets[$checkspecies]['Name_zh_TW'] != "") {
                    $thispet = $npcpets[$checkspecies];
                    $thispet['Result'] = "Already Imported";
                    $thispet['hide'] = FALSE;
                    $thispet['npc'] = TRUE;
                }
                else {
                    $updatenpcpet = \ADMIN\import_npc_pet($checkspecies);
                    if ($updatenpcpet == "no_pet") {
                        $thispet['Result'] = "No entry";
                        $thispet['hide'] = TRUE;
                    }
                    else if ($updatenpcpet == "error") {
                        $thispet['Result'] = "API Error";
                        $thispet['hide'] = TRUE;
                        $thispet['npc'] = TRUE;
                    }
                    else {
                        $thispet = $updatenpcpet;
                        $thispet['Result'] = "Updated!";
                        $thispet['hide'] = FALSE;
                        $thispet['npc'] = TRUE;
                    }
                }
            }

            // Output
            if ($countpets == "30") {
                $countpets = "0"; ?>
                <tr>
                    <th class="admin">Species</th>
                    <th class="admin">NPC-ID</th>
                    <th class="admin">Name</th>
                    <th class="admin">Localization</th>
                    <th class="admin">Family</th>
                    <th class="admin">Skills</th>
                    <th class="admin">Icon</th>
                    <th class="admin">Result</th>
                    <th class="admin">Options</th>
                </tr>
            <? } ?>

            <tr <? if ($thispet['npc'] != TRUE) { echo 'style="display: none" class="admin hiddenrow"'; } else { echo 'class="admin"'; $countpets++; } ?>>
            <td class="admin"><center><? echo $checkspecies ?></td>
            <td class="admin"><center><? if ($thispet['hide'] != TRUE) echo $thispet['PetID']; else echo "-"; ?></td>
            <td class="admin"><? if ($thispet['hide'] != TRUE) { ?>
                <a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/npc=<? echo $thispet['PetID'] ?>'><? echo $thispet['Name'] ?></a> <? }
                else echo "-"; ?>
            </td>

            <td class="admin"><center>
                <? if ($thispet['hide'] != TRUE && $thispet['hideloc'] != TRUE) { ?>
                <div id="loc_<? echo $checkspecies ?>"><a class="pr_contact" style="cursor: pointer" onclick="$('#locf_<? echo $checkspecies ?>').show();$('#loc_<? echo $checkspecies ?>').hide()"><center>Show</a></div>
                <div style="display: none" id="locf_<? echo $checkspecies ?>">
                    <table cellpadding="0" cellspacing="0">
                        <?
                        foreach (['Name_de_DE', 'Name_fr_FR', 'Name_it_IT', 'Name_es_ES', 'Name_pl_PL', 'Name_pt_PT', 'Name_ru_RU', 'Name_es_MX', 'Name_pt_BR', 'Name_ko_KR', 'Name_zh_TW'] as $locale) { ?>
                        <tr>
                            <td><p class="smallodd"><b><? echo $locale ?>:</b></td>
                            <td><? echo $thispet[$locale] ?></td>
                        </tr>
                        <? } ?>
                    </table>
                </div>
                <? }
                else echo "-"; ?>
            </td>

            <td class="admin npcpets"><center><? if ($thispet['hide'] != TRUE) echo $thispet['Family']; else echo "-"; ?></td>

            <td class="admin"><center>
                <? if ($thispet['hide'] != TRUE) { ?>
                <div id="skills_<? echo $checkspecies ?>"><a class="pr_contact" style="cursor: pointer" onclick="$('#skillsf_<? echo $checkspecies ?>').show();$('#skills_<? echo $checkspecies ?>').hide()"><center>Show</a></div>
                <div style="display: none" id="skillsf_<? echo $checkspecies ?>">
                    <? if ($thispet['Skill1'] != "0" && $thispet['Skill1']) { ?><b>Skill1:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $thispet['Skill1'] ?>'><? echo $thispet['Skill1'] ?></a><br><? } ?>
                    <? if ($thispet['Skill2'] != "0" && $thispet['Skill2']) { ?><b>Skill2:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $thispet['Skill2'] ?>'><? echo $thispet['Skill2'] ?></a><br><? } ?>
                    <? if ($thispet['Skill3'] != "0" && $thispet['Skill3']) { ?><b>Skill3:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $thispet['Skill3'] ?>'><? echo $thispet['Skill3'] ?></a><br><? } ?>
                    <? if ($thispet['Skill4'] != "0" && $thispet['Skill4']) { ?><b>Skill4:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $thispet['Skill4'] ?>'><? echo $thispet['Skill4'] ?></a><br><? } ?>
                    <? if ($thispet['Skill5'] != "0" && $thispet['Skill5']) { ?><b>Skill5:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $thispet['Skill5'] ?>'><? echo $thispet['Skill5'] ?></a><br><? } ?>
                    <? if ($thispet['Skill6'] != "0" && $thispet['Skill6']) { ?><b>Skill6:</b><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $thispet['Skill6'] ?>'><? echo $thispet['Skill6'] ?></a><? } ?>
                </div>
                <? }
                else echo "-"; ?>
            </td>

            <td class="admin"><center><? if ($thispet['hide'] != TRUE && $thispet['hideicon'] != TRUE) echo $thispet['Icon']; else echo "-"; ?></td>

            <td class="admin"><? echo $thispet['Result'] ?></td>

            <td class="admin" style="white-space: nowrap">
                <form action="index.php?page=adm_petimport&command=importnpcpet" style="display: inline" method="POST">
                    <input type="hidden" name="species" value="<? echo $thispet['Species'] ?>">
                    <input class="cominputmedium" type="submit" value="Update">
                </form>

            </td>

            </tr>     <?

            $checkspecies++;
            $i++;
        } ?>

        </table>
        <br>
        <form action="index.php?page=adm_petimport&command=checknpcpets" style="display: inline" method="POST">
            <input type="hidden" name="startfrom" value="<? echo $checkspecies ?>">
            <input class="cominputmedium" type="submit" value="Check the next <? echo $numimports ?>">
        </form>
        <br>
        <form action="index.php?page=adm_petimport&command=checknpcpets" style="display: inline" method="POST">
            <p class="blogodd">Start checking from species: <input type="text" name="startfrom"> <input class="cominputmedium" type="submit" value="Go">
        </form>
        <br><br>


     <? }

            ?>

        </td>
    </tr>

</table>

</table>

<script>
    $(document).ready(function() {
        $('.tt_loc').tooltipster({
            maxWidth: '600',
            theme: 'tooltipster-smallnote'
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


      /*

        //
        if ($command == "updatespell")  { ?>
        <p class="blogodd"> <?
            $importspell = \HTTP\argument_POST ('spellid', FALSE);
            $spellimported = \ADMIN\import_single_spell($importspell);
            if ($spellimported == "error") {
                echo "<b>There was an error importing the spell. Please try again.</b><br><br><br>";
            }
            else {
                echo "Spell ID ".$spellimported['id']." imported:";
                echo "<br><br>";
                echo "Name EN: ".$spellimported['name']."<br>";
                echo "Name DE: ".$spellimported['de_DE']['name']."<br>";
                echo "Name FR: ".$spellimported['fr_FR']['name']."<br>";
                echo "Name IT: ".$spellimported['it_IT']['name']."<br>";
                echo "Name ES: ".$spellimported['es_ES']['name']."<br>";
                echo "Name PL: ".$spellimported['pl_PL']['name']."<br>";
                echo "Name PT: ".$spellimported['pt_PT']['name']."<br>";
                echo "Name RU: ".$spellimported['ru_RU']['name']."<br>";
                echo "Name MX: ".$spellimported['es_MX']['name']."<br>";
                echo "Name BR: ".$spellimported['pt_BR']['name']."<br>";
                echo "Name KR: ".$spellimported['ko_KR']['name']."<br>";
                echo "Name ZH: ".$spellimported['zh_TW']['name']."<br>";
                echo "<br>";
                echo "Family: ".convert_family($spellimported['petTypeId'])."<br>";
                echo "Icon: ".$spellimported['icon']."<br>";
                echo "Cooldown: ".$spellimported['cooldown']."<br>";
                echo "Rounds: ".$spellimported['rounds']."<br>";
                echo "Passive: ".$spellimported['isPassive']."<br>";
                echo "HideHints: ".$spellimported['hideHints']."<br>";
                echo "<br><br>";

            }
            $command = "checkspells";
        }

        if ($command == "checkspells")  { ?>
            <p class="blogodd">

            <? foreach ($allpets as $value) {
                $spells[$value['Skill1']]['SpellID'] = $value['Skill1'];
                $spells[$value['Skill2']]['SpellID'] = $value['Skill2'];
                $spells[$value['Skill3']]['SpellID'] = $value['Skill3'];
                $spells[$value['Skill4']]['SpellID'] = $value['Skill4'];
                $spells[$value['Skill5']]['SpellID'] = $value['Skill5'];
                $spells[$value['Skill6']]['SpellID'] = $value['Skill6'];
                $spells[$value['Skill1']]['Used'] = TRUE;
                $spells[$value['Skill2']]['Used'] = TRUE;
                $spells[$value['Skill3']]['Used'] = TRUE;
                $spells[$value['Skill4']]['Used'] = TRUE;
                $spells[$value['Skill5']]['Used'] = TRUE;
                $spells[$value['Skill6']]['Used'] = TRUE;
            }
            unset($spells[0]);

            $npcpetsdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC");
            while ($thisnpcpet = mysqli_fetch_object($npcpetsdb)) {
                if ($thisnpcpet->Skill1 != "0") {
                    $spells[$thisnpcpet->Skill1]['SpellID'] = $thisnpcpet->Skill1;
                    $spells[$value['Skill1']]['Used'] = TRUE;
                }
                if ($thisnpcpet->Skill2 != "0") {
                    $spells[$thisnpcpet->Skill2]['SpellID'] = $thisnpcpet->Skill2;
                    $spells[$value['Skill2']]['Used'] = TRUE;
                }
                if ($thisnpcpet->Skill3 != "0") {
                    $spells[$thisnpcpet->Skill3]['SpellID'] = $thisnpcpet->Skill3;
                    $spells[$value['Skill3']]['Used'] = TRUE;
                }
                if ($thisnpcpet->Skill4 != "0") {
                    $spells[$thisnpcpet->Skill4]['SpellID'] = $thisnpcpet->Skill4;
                    $spells[$value['Skill4']]['Used'] = TRUE;
                }
                if ($thisnpcpet->Skill5 != "0") {
                    $spells[$thisnpcpet->Skill5]['SpellID'] = $thisnpcpet->Skill5;
                    $spells[$value['Skill5']]['Used'] = TRUE;
                }
                if ($thisnpcpet->Skill6 != "0") {
                    $spells[$thisnpcpet->Skill6]['SpellID'] = $thisnpcpet->Skill6;
                    $spells[$value['Skill6']]['Used'] = TRUE;
                }
            }

            $allspellsdb = mysqli_query($dbcon, "SELECT SpellID, Icon, Family, Cooldown, Rounds, Passive, HideHints, PetSpell, en_US, de_DE, fr_FR, it_IT, es_ES, pl_PL, pt_PT, ru_RU, es_MX, pt_BR, ko_KR, zh_TW FROM Spells");
            $allspells = array();

            while ($row_allspells = mysqli_fetch_object($allspellsdb)) {
                $spells[$row_allspells->SpellID]['SpellID'] = $row_allspells->SpellID;
                $spells[$row_allspells->SpellID]['DB'] = TRUE;
                $spells[$row_allspells->SpellID]['Icon'] = $row_allspells->Icon;
                $spells[$row_allspells->SpellID]['Family'] = $row_allspells->Family;
                $spells[$row_allspells->SpellID]['Cooldown'] = $row_allspells->Cooldown;
                $spells[$row_allspells->SpellID]['Rounds'] = $row_allspells->Rounds;
                $spells[$row_allspells->SpellID]['Passive'] = $row_allspells->Passive;
                $spells[$row_allspells->SpellID]['HideHints'] = $row_allspells->HideHints;
                $spells[$row_allspells->SpellID]['PetSpell'] = $row_allspells->PetSpell;
                $spells[$row_allspells->SpellID]['en_US'] = $row_allspells->en_US;
                $spells[$row_allspells->SpellID]['de_DE'] = $row_allspells->de_DE;
                $spells[$row_allspells->SpellID]['fr_FR'] = $row_allspells->fr_FR;
                $spells[$row_allspells->SpellID]['it_IT'] = $row_allspells->it_IT;
                $spells[$row_allspells->SpellID]['es_ES'] = $row_allspells->es_ES;
                $spells[$row_allspells->SpellID]['pl_PL'] = $row_allspells->pl_PL;
                $spells[$row_allspells->SpellID]['pt_PT'] = $row_allspells->pt_PT;
                $spells[$row_allspells->SpellID]['ru_RU'] = $row_allspells->ru_RU;
                $spells[$row_allspells->SpellID]['es_MX'] = $row_allspells->es_MX;
                $spells[$row_allspells->SpellID]['pt_BR'] = $row_allspells->pt_BR;
                $spells[$row_allspells->SpellID]['ko_KR'] = $row_allspells->ko_KR;
                $spells[$row_allspells->SpellID]['zh_TW'] = $row_allspells->zh_TW;
            }

            foreach ($spells as $key => $value) {
                $spells[$value['SpellID']]['Command'] = "SetUsed";
                if ($value['Used'] != TRUE && $value['DB'] == TRUE) {
                    $spells[$value['SpellID']]['Command'] = "SetUnused";
                }
                if ($value['HideHints'] == "9" OR $value['Passive'] == "9" OR $value['Rounds'] == "99" OR $value['Cooldown'] == "99" OR $value['Icon'] == "" OR $value['Family'] == "" OR $value['en_US'] == "" OR $value['de_DE'] == "" OR $value['fr_FR'] == "" OR $value['it_IT'] == "" OR $value['es_ES'] == "" OR $value['pl_PL'] == "" OR $value['pt_PT'] == "" OR $value['ru_RU'] == "" OR $value['es_MX'] == "" OR $value['pt_BR'] == "" OR $value['ko_KR'] == "" OR $value['zh_TW'] == "") {
                    $spells[$value['SpellID']]['Command'] = "Update";
                }
                if ($value['Used'] == TRUE && $value['DB'] != TRUE) {
                    $spells[$value['SpellID']]['Command'] = "ImportNew";
                }


            }
            sortBy('SpellID', $spells, 'desc');

            ?>
            The spell database will be automatically checked and imported when you open this table. It takes information from the pet database to know which spells need to be imported.<br>
            To have the latest spell data, make sure that the pet database is updated first.<br>
            Use the manual update button if a spell has been changed by Blizzard (for example renamed).

            <table class="admin" style="width: 100%">
                <tr>
                    <th class="admin">Spell-ID</th>
                    <th class="admin">Name</th>
                    <th class="admin">Localization</th>
                    <th class="admin">Family</th>
                    <th class="admin">Cooldown</th>
                    <th class="admin">Rounds</th>
                    <th class="admin">Passive</th>
                    <th class="admin">
                        <div class="tt_loc" data-tooltip-content="#tt_defrar"><p class="smallodd">Hide Hints</div>
                            <div style="display: none">
                                <span id="tt_defrar">
                                    "Hint" is the tooltip in-game that shows what this spell is strong and weak against. This is only relevant for damaging spells and is thus hidden for passive spells or healing abilities.<br>
                                    The flag here controls this information
                                </span>
                            </div>
                    </th>
                    <th class="admin">Icon</th>
                    <th class="admin">
                        <div class="tt_loc" data-tooltip-content="#tt_active"><p class="smallodd">Active</div>
                            <div style="display: none">
                                <span id="tt_active">
                                    Active means, the spell is used by at least one pet. Inactive means, the spell is in the Blizzard database, but there is no player pet using this ability.<br>
                                    Most of the latter are test spells or spells only used by NPC pets.
                                </span>
                            </div>
                    </th>
                    <th class="admin">Result</th>
                    <th class="admin">Options</th>
                </tr>

             <?
            $countspells = "0";
            foreach ($spells as $key => $value) {
                // Header
                if ($countspells == "30") {
                    $countspells = "0"; ?>
                    <tr>
                        <th class="admin">Spell-ID</th>
                        <th class="admin">Name</th>
                        <th class="admin">Localization</th>
                        <th class="admin">Family</th>
                        <th class="admin">Cooldown</th>
                        <th class="admin">Rounds</th>
                        <th class="admin">Passive</th>
                        <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_defrar"><p class="smallodd">Hide Hints</div></th>
                        <th class="admin">Icon</th>
                        <th class="admin"><div class="tt_loc" data-tooltip-content="#tt_active"><p class="smallodd">Active</div></th>
                        <th class="admin">Result</th>
                        <th class="admin">Options</th>
                    </tr>
                <? }

                if ($value['Command'] == "Update" OR $value['Command'] == "ImportNew") {
                    $updatespell = \ADMIN\import_single_spell($value['SpellID']);
                    if ($updatespell == "error") {
                        $value['Result'] = "Error Importing!";
                    }
                    else {
                        $value['Icon'] = $updatespell['icon'];
                        $value['Family'] = convert_family($updatespell['petTypeId']);
                        $value['Cooldown'] = $updatespell['cooldown'];
                        $value['Rounds'] = $updatespell['rounds'];
                        $value['Passive'] = $updatespell['isPassive'];
                        $value['HideHints'] = $updatespell['hideHints'];
                        $value['en_US'] = $updatespell['name'];
                        $value['de_DE'] = $updatespell['de_DE']['name'];
                        $value['fr_FR'] = $updatespell['fr_FR']['name'];
                        $value['it_IT'] = $updatespell['it_IT']['name'];
                        $value['es_ES'] = $updatespell['es_ES']['name'];
                        $value['pl_PL'] = $updatespell['pl_PL']['name'];
                        $value['pt_PT'] = $updatespell['pt_PT']['name'];
                        $value['ru_RU'] = $updatespell['ru_RU']['name'];
                        $value['es_MX'] = $updatespell['es_MX']['name'];
                        $value['pt_BR'] = $updatespell['pt_BR']['name'];
                        $value['ko_KR'] = $updatespell['ko_KR']['name'];
                        $value['zh_TW'] = $updatespell['zh_TW']['name'];

                        if ($value['Command'] == "Update") {
                            $value['Result'] = "Updated";
                        }
                        if ($value['Command'] == "ImportNew") {
                            $value['Result'] = "Imported";
                            $value['PetSpell'] = "1";
                        }
                    }
                }

                if ($value['Command'] == "SetUnused" && $value['PetSpell'] != "0") {
                    mysqli_query($dbcon, "UPDATE Spells SET `PetSpell` = '0' WHERE SpellID = '$value['SpellID']'") OR die(mysqli_error($dbcon));
                    $value['Result'] = "Updated";
                }
                if ($value['Command'] == "SetUnused" && $value['PetSpell'] == "0") {
                    $value['Result'] = "No update needed";
                }

                if ($value['Command'] == "SetUsed" && $value['PetSpell'] != "1") {
                    mysqli_query($dbcon, "UPDATE Spells SET `PetSpell` = '1' WHERE SpellID = '$value['SpellID']'") OR die(mysqli_error($dbcon));
                    $value['Result'] = "Updated";
                }
                if ($value['Command'] == "SetUsed" && $value['PetSpell'] == "1") {
                    $value['Result'] = "No update needed";
                } ?>

                <tr class="admin">
                <td class="admin"><? echo $value['SpellID'] ?></td>
                <td class="admin"><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/petability=<? echo $value['SpellID'] ?>'><? echo $value['en_US'] ?></a></td>
                <td class="admin">
                    <div id="loc_<? echo $key ?>"><a class="pr_contact" style="cursor: pointer" onclick="$('#locf_<? echo $key ?>').show();$('#loc_<? echo $key ?>').hide()"><center>Show</a></div>
                    <div style="display: none" id="locf_<? echo $key ?>">
                        <table cellpadding="0" cellspacing="0">
                            <?
                            foreach (['de_DE', 'fr_FR', 'it_IT', 'es_ES', 'pl_PL', 'pt_PT', 'ru_RU', 'es_MX', 'pt_BR', 'ko_KR', 'zh_TW'] as $locale) { ?>
                            <tr>
                                <td><p class="smallodd"><b><? echo $locale ?>:</b></td>
                                <td><p class="smallodd"><? echo $value[$locale] ?></td>
                            </tr>
                            <? } ?>

                        </table>
                    </div>
                </td>

                <td class="admin"><? echo $value['Family'] ?></td>
                <td class="admin"><? echo $value['Cooldown'] ?></td>
                <td class="admin"><? echo $value['Rounds'] ?></td>
                <td class="admin"><? echo $value['Passive'] ?></td>
                <td class="admin"><? echo $value['HideHints'] ?></td>
                <td class="admin"><? echo $value['Icon'] ?></td>
                <td class="admin"><? echo $value['PetSpell'] ?></td>
                <td class="admin"><? echo $value['Result'] ?></td>


                <td class="admin" style="white-space: nowrap">
                    <form action="index.php?page=adm_petimport&command=updatespell" style="display: inline" method="POST">
                        <input type="hidden" name="spellid" value="<? echo $value['SpellID'] ?>">
                        <input class="cominputmedium" type="submit" value="Update">
                    </form>
                </td>

                </tr>
            <?
            $countspells++;
            }
        }

        if ($command == "importnpcpet")  { ?>
        <p class="blogodd"> <?
            $importspecies = \HTTP\argument_POST ('species', FALSE);
            $npcimport = \ADMIN\import_npc_pet($importspecies);
            if ($npcimport == "error" OR $npcimport == "no_pet") {
                echo "<b>There was an error importing the pet. Please try again.</b><br><br><br>";
            }
            else {
                echo "Species ".$npcimport['id']." imported:";
                echo "<br><br>";
                echo "Name EN: ".$npcimport['name']."<br>";
                echo "Name DE: ".$npcimport['de_DE']['name']."<br>";
                echo "Name FR: ".$npcimport['fr_FR']['name']."<br>";
                echo "Name IT: ".$npcimport['it_IT']['name']."<br>";
                echo "Name ES: ".$npcimport['es_ES']['name']."<br>";
                echo "Name PL: ".$npcimport['pl_PL']['name']."<br>";
                echo "Name PT: ".$npcimport['pt_PT']['name']."<br>";
                echo "Name RU: ".$npcimport['ru_RU']['name']."<br>";
                echo "Name MX: ".$npcimport['es_MX']['name']."<br>";
                echo "Name BR: ".$npcimport['pt_BR']['name']."<br>";
                echo "Name KR: ".$npcimport['ko_KR']['name']."<br>";
                echo "Name ZH: ".$npcimport['zh_TW']['name']."<br>";
                echo "<br>";
                echo "Family: ".convert_family($npcimport['petTypeId'])."<br>";
                echo "Icon: ".$npcimport['icon']."<br>";
                echo "<br><br>";

            }
            $command = "checknpcpets";
        }






        */
