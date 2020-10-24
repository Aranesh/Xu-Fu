<?
include("../../data/dbconnect.php");
include("../functions.php");

$petget = $_GET['pet'];
$slot = $_GET['slot'];
$language = $_GET['lng'];
$strategy = $_GET['strat'];
$def = $_GET['def'];
$defpet = $_GET['defpet'];

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("../../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
set_language_vars($language);
$stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$strategy'");

if (mysqli_num_rows($stratdb) < 1 OR !preg_match("/^[1234567890]*$/is", $petget) OR !preg_match("/^[1234567890]*$/is", $strategy) OR !preg_match("/^[1234567890]*$/is", $slot) OR !preg_match("/^[1234567890]*$/is", $def)){
    echo '<p class="blogodd"><b>Error loading pet, please refresh the page.</b>';
}
else {
    $strat = mysqli_fetch_object($stratdb);
    if ($petget > "1") {
        $petdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID = '$petget'");
        $pet = mysqli_fetch_object($petdb);
        if ($def == "1" && $defpet == $pet->PetID) {
            $def = "0";
        }
        // Get Spells
        $spellsdb = mysqli_query($dbcon, "SELECT * FROM Pet_Abilities WHERE id = '$pet->Skill1' OR id = '$pet->Skill2' OR id = '$pet->Skill3' OR id = '$pet->Skill4' OR id = '$pet->Skill5' OR id = '$pet->Skill6'");
        while ($thisspell = mysqli_fetch_object($spellsdb)) {
            if ($thisspell->id == $pet->Skill1) {
                $i = "1";
            }
            if ($thisspell->id == $pet->Skill2) {
                $i = "2";
            }
            if ($thisspell->id == $pet->Skill3) {
                $i = "3";
            }
            if ($thisspell->id == $pet->Skill4) {
                $i = "4";
            }
            if ($thisspell->id == $pet->Skill5) {
                $i = "5";
            }
            if ($thisspell->id == $pet->Skill6) {
                $i = "6";
            }
            $spells[$i]['SpellID'] = $thisspell->id;
            $spells[$i]['Icon'] = "images/pet_abilities/".$thisspell->id.".png";
            $spells[$i]['Family'] = $thisspell->Family;
            $spells[$i]['Name'] = $thisspell->${'language'};
        }
    
        // Check breed availability
        $avbreeds = "0";
        if ($pet->BB == "1") {
            $avbreeds++;
        }
        if ($pet->PP == "1") {
            $avbreeds++;
        }
        if ($pet->SS == "1") {
            $avbreeds++;
        }
        if ($pet->HH == "1") {
            $avbreeds++;
        }
        if ($pet->HP == "1") {
            $avbreeds++;
        }
        if ($pet->PS == "1") {
            $avbreeds++;
        }
        if ($pet->HS == "1") {
            $avbreeds++;
        }
        if ($pet->PB == "1") {
            $avbreeds++;
        }
        if ($pet->SB == "1") {
            $avbreeds++;
        }
        if ($pet->HB == "1") {
            $avbreeds++;
        }
        if ($avbreeds == "1") {
            $hidedetails = "; display: none";
        }
       
        if ($def == "1") {
            $quickskill1 = "*";
            $quickskill2 = "*";
            $quickskill3 = "*";
        }
        else {
            if ($slot == "1") {
                if ($strat->SkillPet11 == "0") {
                    $quickskill1 = "*";
                }
                else {
                    $quickskill1 = $strat->SkillPet11;
                }
                if ($strat->SkillPet12 == "0") {
                    $quickskill2 = "*";
                }
                else {
                    $quickskill2 = $strat->SkillPet12;
                }
                if ($strat->SkillPet13 == "0") {
                    $quickskill3 = "*";
                }
                else {
                    $quickskill3 = $strat->SkillPet13;
                }
            }
            if ($slot == "2") {
                if ($strat->SkillPet21 == "0") {
                    $quickskill1 = "*";
                }
                else {
                    $quickskill1 = $strat->SkillPet21;
                }
                if ($strat->SkillPet22 == "0") {
                    $quickskill2 = "*";
                }
                else {
                    $quickskill2 = $strat->SkillPet22;
                }
                if ($strat->SkillPet23 == "0") {
                    $quickskill3 = "*";
                }
                else {
                    $quickskill3 = $strat->SkillPet23;
                }
            }
            if ($slot == "3") {
                if ($strat->SkillPet31 == "0") {
                    $quickskill1 = "*";
                }
                else {
                    $quickskill1 = $strat->SkillPet31;
                }
                if ($strat->SkillPet32 == "0") {
                    $quickskill2 = "*";
                }
                else {
                    $quickskill2 = $strat->SkillPet32;
                }
                if ($strat->SkillPet33 == "0") {
                    $quickskill3 = "*";
                }
                else {
                    $quickskill3 = $strat->SkillPet33;
                }
            }  
        }
    }
    
    if ($petget > "20") { ?>
        <div style="width: 225; font-family: MuseoSans-500; font-size: 16px; padding: 6 5 12 5; margin: 5 0 5 0; background-color: #b6b6b6; float: left">
            <b>Skills: <span id="skillshort_<?php echo $slot ?>_1"><?php echo $quickskill1 ?></span>
            <span id="skillshort_<?php echo $slot ?>_2"><?php echo $quickskill2 ?></span>
            <span id="skillshort_<?php echo $slot ?>_3"><?php echo $quickskill3 ?></span></b> <br>
            
            
            <?
            $skillcount = "1";
            while ($skillcount <= "3") {
                $chosenspell = "";
                if ($skillcount == "1") {
                    $spell1c = "1";
                    $spell2c = "4";
                    if ($slot == "1") {
                        $chosenspell = $strat->SkillPet11;
                    }
                    if ($slot == "2") {
                        $chosenspell = $strat->SkillPet21;
                    }
                    if ($slot == "3") {
                        $chosenspell = $strat->SkillPet31;
                    }     
                }
                if ($skillcount == "2") {
                    $spell1c = "2";
                    $spell2c = "5";
                    if ($slot == "1") {
                        $chosenspell = $strat->SkillPet12;
                    }
                    if ($slot == "2") {
                        $chosenspell = $strat->SkillPet22;
                    }
                    if ($slot == "3") {
                        $chosenspell = $strat->SkillPet32;
                    }                  
                }
                if ($skillcount == "3") {
                    $spell1c = "3";
                    $spell2c = "6";
                    if ($slot == "1") {
                        $chosenspell = $strat->SkillPet13;
                    }
                    if ($slot == "2") {
                        $chosenspell = $strat->SkillPet23;
                    }
                    if ($slot == "3") {
                        $chosenspell = $strat->SkillPet33;
                    }  
                }
                if ($def == "1") {
                    $chosenspell = "0";
                }
                
                ?> 
                <div style="width: 35px; margin: 10 0 0 25; float: left;">
                    <div style="display: none">
                        <select name="petskill<?php echo $slot ?>_<?php echo $skillcount ?>" id="petskill<?php echo $slot ?>_<?php echo $skillcount ?>" required>
                            <option value="<?php echo $spells[$spell1c]['SpellID']; ?>_<?php echo $slot ?>_1" <?php if ($chosenspell == "1") { echo "selected"; } ?>><?php echo $spells[$spell1c]['Name'] ?></option>
                            <option value="slot<?php echo $slot ?>_<?php echo $skillcount ?>x" <?php if ($chosenspell == "0" OR $chosenspell == "") { echo "selected"; } ?>>slot<?php echo $slot ?>_<?php echo $skillcount ?>x</option>
                            <option value="<?php echo $spells[$spell2c]['SpellID']; ?>_<?php echo $slot ?>_2" <?php if ($chosenspell == "2") { echo "selected"; } ?>><?php echo $spells[$spell2c]['Name'] ?></option>
                        </select>
                    </div>
                        <div style="width: 35px";>
                            <div id="icon_<?php echo $spells[$spell1c]['SpellID'] ?>_<?php echo $slot ?>_1" class="icon_noborder_<?php echo $skillcount.$slot ?> spell_tt" data-tooltip-content="#pet<?php echo $slot ?>_set<?php echo $skillcount ?>_spell1_tt" style="float: left; margin-bottom: 3px; border:3px solid 
                            <?php if ($chosenspell == "1") { echo "#ffffff"; } else { echo "#888888"; } ?>;" onclick="select_icon_<?php echo $slot ?>_<?php echo $skillcount ?>('<?php echo $spells[$spell1c]['SpellID'] ?>_<?php echo $slot ?>_1')">
                                <img src="<?php echo $spells[$spell1c]['Icon'] ?>" style="width: 36px; height: 36px;">
                            </div>
                            <div style="display: none"><span id="pet<?php echo $slot ?>_set<?php echo $skillcount ?>_spell1_tt"><?php echo $spells[$spell1c]['Name'] ?></span></div>
                            
                            <div id="icon_slot<?php echo $slot ?>_<?php echo $skillcount ?>x" class="icon_noborder_<?php echo $skillcount.$slot ?> spell_tt" data-tooltip-content="#pet<?php echo $slot ?>_set<?php echo $skillcount ?>_spellx_tt" style="float: left; margin-bottom: 3px; border:3px solid
                            <?php if ($chosenspell == "0") { echo "#ffffff"; } else { echo "#888888"; } ?> ;" onclick="select_icon_<?php echo $slot ?>_<?php echo $skillcount ?>('slot<?php echo $slot ?>_<?php echo $skillcount ?>x')">
                                <img src="https://www.wow-petguide.com/images/bt_edit_wildcard.png" style="width: 36px; height: 36px;">
                            </div>
                            <div style="display: none"><span id="pet<?php echo $slot ?>_set<?php echo $skillcount ?>_spellx_tt">Wildcard - select if skill is not used in battle.</span></div>
                            
                            <div id="icon_<?php echo $spells[$spell2c]['SpellID'] ?>_<?php echo $slot ?>_2" class="icon_noborder_<?php echo $skillcount.$slot ?> spell_tt" data-tooltip-content="#pet<?php echo $slot ?>_set<?php echo $skillcount ?>_spell2_tt" style="float: left; margin-bottom: 3px; border:3px solid
                            <?php if ($chosenspell == "2") { echo "#ffffff"; } else { echo "#888888"; } ?>;" onclick="select_icon_<?php echo $slot ?>_<?php echo $skillcount ?>('<?php echo $spells[$spell2c]['SpellID'] ?>_<?php echo $slot ?>_2')">
                                <img src="<?php echo $spells[$spell2c]['Icon'] ?>" style="width: 36px; height: 36px;">
                            </div>
                            <div style="display: none"><span id="pet<?php echo $slot ?>_set<?php echo $skillcount ?>_spell2_tt"><?php echo $spells[$spell2c]['Name'] ?></span></div>
                        </div>
                    <script>
                        $("#petskill<?php echo $slot ?>_<?php echo $skillcount ?>").chosen();
        
                        $("#petskill<?php echo $slot ?>_<?php echo $skillcount ?>").chosen().change(function(event){
                            i = $('select[name=petskill<?php echo $slot ?>_<?php echo $skillcount ?>]').val();
                            $('.icon_noborder_<?php echo $skillcount.$slot ?>').css("borderColor","#888888");
                            $('#icon_'+i).css("borderColor","#ffffff");
                            var quicks = i.split("_");
                            var q = "";
                            if (quicks[2] != "1" && quicks[2] != "2") {
                                q = "*";
                            }
                            else {
                                q = quicks[2];
                            }
                            document.getElementById('skillshort_<?php echo $slot ?>_<?php echo $skillcount ?>').innerHTML = q;
                        });
                        function select_icon_<?php echo $slot ?>_<?php echo $skillcount ?>(i){
                            $("#petskill<?php echo $slot ?>_<?php echo $skillcount ?>").val(i).change();
                            $('#petskill<?php echo $slot ?>_<?php echo $skillcount ?>').trigger("chosen:updated");
                        }
                        $(document).ready(function() {
                            $('.spell_tt').tooltipster({
                                maxWidth: '250',
                                theme: 'tooltipster-smallnote'
                            });
                        });  
                    </script>
                </div>
            <?
            $skillcount++;
            } ?>      
        </div>
    
    <?php }
    
    if ($petget <= "20") {
        if ($slot == "1") {
            $stdlevel = $strat->PetLevel1;
            $stdpet = $strat->PetID1;
        }
        if ($slot == "2") {
            $stdlevel = $strat->PetLevel2;
            $stdpet = $strat->PetID2;
        }
        if ($slot == "3") {
            $stdlevel = $strat->PetLevel3;
            $stdpet = $strat->PetID3;
        }
        if ($petget == $stdpet) {
            $reqlevel = $stdlevel;
        }
        if ($reqlevel != "") {
            $levelpieces = explode("+", $reqlevel);
            $reqlevel = $levelpieces[0];
        }
        else {
            $reqlevel = "1";
        }

    ?>
        <div style="width: 225; font-family: MuseoSans-500; font-size: 16px; padding: 6 5 12 5; margin: 5 0 5 0; background-color: #b6b6b6; float: left<?php echo $hidedetails ?>">
            <b>Required Level:</b><br>
            
            <div style="width: 200px; margin: 10 0 0 10; float: left;">
                <div style="width: 20px; float: left; padding-top: 3px;"><span id="edit_level_numb_<?php echo $slot ?>"><?php echo $reqlevel ?></span></div>
                <div style="width: 175px; float: left"><input type="range" min="1" max="25" name="min_level_<?php echo $slot ?>" value="<?php echo $reqlevel ?>" id="edit_level_slider_<?php echo $slot ?>" style="width: 175px" class="alt_edit_slider"></div>
            </div>
            <script>
                var slider<?php echo $slot ?> = document.getElementById("edit_level_slider_<?php echo $slot ?>");
                var output<?php echo $slot ?> = document.getElementById("edit_level_numb_<?php echo $slot ?>");
                slider<?php echo $slot ?>.oninput = function() {
                     output<?php echo $slot ?>.innerHTML = this.value;
                }								
            </script>            
            
            
        </div>
    <?php } ?>
    
    <div style="width: 225; font-family: MuseoSans-500; font-size: 16px; padding: 6 5 12 5; margin: 5 0 5 0; background-color: #b6b6b6; float: left">
        <b>Required stats:</b><br>
        
        
        <?
       if ($slot == "1") {
            $reqhp = $strat->Health1;
            $reqsp = $strat->Speed1;
            $reqpw = $strat->Power1;
            $reqbreeds = $strat->Breeds1;
        }
        if ($slot == "2") {
            $reqhp = $strat->Health2;
            $reqsp = $strat->Speed2;
            $reqpw = $strat->Power2;
            $reqbreeds = $strat->Breeds2;
        }
        if ($slot == "3") {
            $reqhp = $strat->Health3;
            $reqsp = $strat->Speed3;
            $reqpw = $strat->Power3;
            $reqbreeds = $strat->Breeds3;
        }
        ?>
        
        <div style="width: 200px; margin: 10 0 0 10; float: left;">
            <div style="float: left; margin-right: 5px; padding-top: 2px; width: 18px">
                <img src="https://www.wow-petguide.com/images/bt_icon_health.png" style="vertical-align: middle;">
            </div>
            <div style="float: left; margin-right: 5px; padding-top: 3px; width: 65px">
                Health:
            </div>
            <div style="float: left">
                <?
                $cond = "";
                $numb = "";
                if ($reqhp != "") {
                    $cond = $reqhp[0];
                    $numb = substr($reqhp, 1);
                }
                ?>
                <select class="petselect" name="reqhp_cond_<?php echo $slot ?>" size="1">
                    <option value=">" <?php if ($cond == ">") { echo "selected"; } ?>>></option>
                    <option value="<" <?php if ($cond == "<") { echo "selected"; } ?>><</option>
                    <option value="=" <?php if ($cond == "=") { echo "selected"; } ?>>=</option>
                </select>
            </div>
            <div style="float: left">
                <input type="text" maxlength="4" class="petselect" style="width: 60px" name="reqhp_numb_<?php echo $slot ?>" value="<?php echo $numb ?>">
            </div>
        </div>
        
        <div style="width: 200px; margin: 0 0 0 10; float: left;">
            <div style="float: left; margin-right: 5px; padding-top: 2px; width: 18px">
                <img src="https://www.wow-petguide.com/images/bt_icon_speed.png" style="vertical-align: middle;">
            </div>
            <div style="float: left; margin-right: 5px; padding-top: 3px; width: 65px">
                Speed:
            </div>
            <div style="float: left">
                <?
                $cond = "";
                $numb = "";
                if ($reqsp != "") {
                    $cond = $reqsp[0];
                    $numb = substr($reqsp, 1);
                }
                ?>
                <select class="petselect" name="reqsp_cond_<?php echo $slot ?>" size="1">
                    <option value=">" <?php if ($cond == ">") { echo "selected"; } ?>>></option>
                    <option value="<" <?php if ($cond == "<") { echo "selected"; } ?>><</option>
                    <option value="=" <?php if ($cond == "=") { echo "selected"; } ?>>=</option>
                </select>
            </div>
            <div style="float: left">
                <input type="text" maxlength="3" class="petselect" style="width: 60px" name="reqsp_numb_<?php echo $slot ?>" value="<?php echo $numb ?>">
            </div>
        </div>       

        <div style="width: 200px; margin: 0 0 0 10; float: left;">
            <div style="float: left; margin-right: 5px; padding-top: 2px; width: 18px">
                <img src="https://www.wow-petguide.com/images/bt_icon_power.png" style="vertical-align: middle;">
            </div>
            <div style="float: left; margin-right: 5px; padding-top: 3px; width: 65px">
                Power:
            </div>
            <div style="float: left">
                <?
                $cond = "";
                $numb = "";
                if ($reqpw != "") {
                    $cond = $reqpw[0];
                    $numb = substr($reqpw, 1);
                }
                ?>
                <select class="petselect" name="reqpw_cond_<?php echo $slot ?>" size="1">
                    <option value=">" <?php if ($cond == ">") { echo "selected"; } ?>>></option>
                    <option value="<" <?php if ($cond == "<") { echo "selected"; } ?>><</option>
                    <option value="=" <?php if ($cond == "=") { echo "selected"; } ?>>=</option>
                </select>
            </div>
            <div style="float: left">
                <input type="text" maxlength="3" class="petselect" style="width: 60px" name="reqpw_numb_<?php echo $slot ?>" value="<?php echo $numb ?>">
            </div>
        </div>          
    </div>

    <?php if ($petget > "20") { ?>
        <div style="width: 225; font-family: MuseoSans-500; font-size: 16px; padding: 6 5 12 5; margin: 5 0 5 0; background-color: #b6b6b6; float: left<?php echo $hidedetails ?>">
            <b>Breeds:</b><br>
    
            <?
            $thistbreeds = explode(",", $reqbreeds);
            $thisbreeds = array();
            foreach ($thistbreeds as $key => $value) {
                switch ($value) {
                    case "BB":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "PP":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "SS":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "HH":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "HP":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "PS":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "HS":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "PB":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "SB":
                        $thisbreeds[$value] = " checked";
                        break;
                    case "HB":
                        $thisbreeds[$value] = " checked";
                        break;
                }
            }
            if ($reqbreeds == "") {
                foreach (['BB', 'PP', 'SS', 'HH', 'HP', 'PS', 'HS', 'PB', 'SB', 'HB'] as $breed_short) {
                    $thisbreeds[$breed_short] = " checked";
                }
            }
            echo '<div style="width: 220px; margin: 10 0 0 20">';
    
            if ($pet->BB == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>BB:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_BB" id="Pet<?php echo $slot ?>_BB" <?php echo $thisbreeds['BB']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_BB">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->PP == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>PP:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_PP" id="Pet<?php echo $slot ?>_PP" <?php echo $thisbreeds['PP']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_PP">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->SS == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>SS:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_SS" id="Pet<?php echo $slot ?>_SS" <?php echo $thisbreeds['SS']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_SS">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->HH == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>HH:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_HH" id="Pet<?php echo $slot ?>_HH" <?php echo $thisbreeds['HH']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_HH">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->HP == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>HP:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_HP" id="Pet<?php echo $slot ?>_HP" <?php echo $thisbreeds['HP']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_HP">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->PS == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>PS:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_PS" id="Pet<?php echo $slot ?>_PS" <?php echo $thisbreeds['PS']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_PS">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->HS == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>HS:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_HS" id="Pet<?php echo $slot ?>_HS" <?php echo $thisbreeds['HS']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_HS">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->PB == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>PB:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_PB" id="Pet<?php echo $slot ?>_PB" <?php echo $thisbreeds['PB']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_PB">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->SB == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>SB:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_SB" id="Pet<?php echo $slot ?>_SB" <?php echo $thisbreeds['SB']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_SB">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }
            if ($pet->HB == "1") { ?>
            <div style="float: left; width: 70px; margin: 0 8 5 0">
                <div  style="float: left; width: 30px"><b>HB:</b></div>
                <div class="publishswitch" style="float: left">
                    <input type="checkbox" class="publishswitch-checkbox" name="Pet<?php echo $slot ?>_HB" id="Pet<?php echo $slot ?>_HB" <?php echo $thisbreeds['HB']; ?>>
                    <label class="publishswitch-label" for="Pet<?php echo $slot ?>_HB">
                    <span class="publishswitch-inner"></span>
                    <span class="publishswitch-switch"></span>
                    </label>
                </div>
            </div>
            <?php }  ?>
        </div>
    <?php } ?>
    </div>
<?php }



