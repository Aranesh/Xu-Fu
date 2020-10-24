<?php
include("../../data/dbconnect.php");
include("../functions.php");

$main = $_GET['main'];
$userid = $_GET['user'];
$language = $_GET['lng'];
$type = $_GET['type'];
$options = $_GET['options'];

if (!$language) {
    $language = "en_US";
}

    $allbreeds = [ 'BB' => ['Health' => 0.5, 'Speed' => 0.5, 'Power' => 0.5 ]
             , 'PP' => ['Health' => 0.0, 'Speed' => 0.0, 'Power' => 2.0 ]
             , 'SS' => ['Health' => 0.0, 'Speed' => 2.0, 'Power' => 0.0 ]
             , 'HH' => ['Health' => 2.0, 'Speed' => 0.0, 'Power' => 0.0 ]
             , 'HP' => ['Health' => 0.9, 'Speed' => 0.0, 'Power' => 0.9 ]
             , 'PS' => ['Health' => 0.0, 'Speed' => 0.9, 'Power' => 0.9 ]
             , 'HS' => ['Health' => 0.9, 'Speed' => 0.9, 'Power' => 0.0 ]
             , 'PB' => ['Health' => 0.4, 'Speed' => 0.4, 'Power' => 0.9 ]
             , 'SB' => ['Health' => 0.4, 'Speed' => 0.9, 'Power' => 0.4 ]
             , 'HB' => ['Health' => 0.9, 'Speed' => 0.4, 'Power' => 0.4 ]
             ];
    
    $col_master = [];
    $allpets = [];
    $taglow = [];
    $taghigh = [];

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("../../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
set_language_vars($language);
$petnext = "Name_".$language;

if ($language == "en_US") {
    $petnext = "Name";
}

$all_pets = get_all_pets($petnext); 

$maincheckdb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$main'") OR die(mysqli_error($dbcon));
if (mysqli_num_rows($maincheckdb) < 1) {
    $error = TRUE;
    $error_message = "Please try again.";
}

if ($error != TRUE) { // Grabbing sub categories
    $main_entry = mysqli_fetch_object($maincheckdb);
    
    $family_fight = FALSE;
    
    if ($main_entry->id == 17) { // Family Familiar
        $family_fight = TRUE;
        switch ($options) {
            case "0":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main >= 18 && Main <= 27 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "humanoid":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 24 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "dragonkin":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 21 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "flying":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 23 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "undead":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 27 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "critter":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 20 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "magic":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 25 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "elemental":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 22 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "beast":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 19 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "aquatic":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 18 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "mechanical":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 26 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
        }
    }
    if ($main_entry->id == 42) { // Beasts of Argus
        $family_fight = TRUE;
        switch ($options) {
            case "0":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "regular":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 0 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "humanoid":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 1 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "dragonkin":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 2 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "flying":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 3 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "undead":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 4 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "critter":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 5 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "magic":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 6 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "elemental":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 7 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "beast":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 8 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "aquatic":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 9 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "mechanical":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 42 && Family = 10 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
        }    
    }
    if ($main_entry->id == 54) { // Family Battler
        $family_fight = TRUE;
        switch ($options) {
            case "0":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "humanoid":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 1 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "dragonkin":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 2 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "flying":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 3 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "undead":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 4 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "critter":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 5 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "magic":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 6 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "elemental":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 7 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "beast":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 8 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "aquatic":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 9 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
            case "mechanical":
                $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = 54 && Family = 10 && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
                break;
        }    
    }
    if ($family_fight == FALSE) { // All other categories
        $allsubs_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = '$main' && Comment != 'Placeholder' ORDER BY Prio DESC") OR die(mysqli_error($dbcon));
    }
    if (mysqli_num_rows($allsubs_db) < 1) {
        $error = TRUE;
        $error_message = "No fights found in this category.";
    }
}

if ($error != TRUE) { // Grab user collection and go through strategies
    if ($userid) {
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
        if (mysqli_num_rows($userdb) > "0") {
            $user = mysqli_fetch_object($userdb);
            $findcol = find_collection($user, 2);
            if ($findcol != "No Collection") {
                $fp = fopen($findcol['Path'], 'r');
                if ($fp !== FALSE) {
                    $collection = json_decode(fread($fp, filesize($findcol['Path'])), true);
                }
            }
        }
    }
    
    $used_collection = $collection;
    $missing_warning = 2;
 
    $familylist = array();
    while ($sub = mysqli_fetch_object($allsubs_db)) {
        
        // Celestial Tournament - skipping fights depending on the week selection
        if ($options == 'ct1' && $sub->id > 40 && $sub->id < 48) {
            continue;
        }
        if ($options == 'ct2' && ($sub->id < 41 OR $sub->id == 45 OR $sub->id == 46 OR $sub->id == 47)) {
            continue;
        }
        if ($options == 'ct3' && $sub->id < 44) {
            continue;
        }
        
        
        // Create Array of all Subs with their localized names
        $all_tamers[$sub->id]['Family'] = $sub->Family;
        if ($sub->Parent != "0") {
            $subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $sub->Parent");
            $fetch_sub = mysqli_fetch_object($subnamedb);
        }
        else { $fetch_sub = $sub; }
        
        
        // Hacky Family Battler modification to show the NPC name instead of the quest name:
        if ($main_entry->id != 54) {
            if ($fetch_sub->{$petnext} == "") {
                $all_tamers[$sub->id]['Name'] = $fetch_sub->Name;
            }
            else {
                $all_tamers[$sub->id]['Name'] = $fetch_sub->{$petnext};
            }  
        }
        if ($main_entry->id == 54 AND $language == "en_US") {
            $all_tamers[$sub->id]['Name'] = stripslashes(htmlentities($fetch_sub->Family_Battler_Name, ENT_QUOTES, "UTF-8"));
        }
        if ($main_entry->id == 54 AND $language != "en_US") {
            if ($fetch_sub->{$petnext} == "") {
                $all_tamers[$sub->id]['Name'] = $fetch_sub->Name;
            }
            else {
                $all_tamers[$sub->id]['Name'] = $fetch_sub->{$petnext};
            }
        }
        
        
        if ($sub->Family != 0) {
            switch ($sub->Family) {
            case "1":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Humanoid").")";
                break;
            case "2":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Dragonkin").")";
                break;
            case "3":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Flying").")";
                break;
            case "4":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Undead").")";
                break;
            case "5":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Critter").")";
                break;
            case "6":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Magic").")";
                break;
            case "7":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Elemental").")";
                break;
            case "8":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Beast").")";
                break;
            case "9":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Aquatic").")";
                break;
            case "10":
                $all_tamers[$sub->id]['Name'] = $all_tamers[$sub->id]['Name']." (".__("Mechanical").")";
                break;
            }
        }
        

        // Create array of all required pets
        $one_fight_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Sub = '$sub->id' LIMIT 1") OR die(mysqli_error($dbcon));
        if (mysqli_num_rows($one_fight_db) > 0) {
            $one_fight = mysqli_fetch_object($one_fight_db);
            
            if ($type == "heroic") { // Adding collection without already used pets to the calculation for heroic modes only
                $custom_collection = $used_collection;
            }
            else {
                $used_collection = $collection; // resetting collection back to all pets for normal tables
            }
            $strats = calc_strats_rating($one_fight->id, $language, $user, 1, $custom_collection);
            
            $final_strategies[] = $strats[0]->id;

            $all_tamers[$sub->id]['Strategy'] = $strats[0]->id;
            $all_tamers[$sub->id]['Prio'] = $sub->Prio;
            
            // Go through all pets individually
            for ($p = 0; $p < 3; $p++) {
                $reqbreeds = "";
                $reqhp = "";
                $reqsp = "";
                $reqpw = "";
                $reqlevel = "";
                $species = "";
                $doublepet = FALSE;
                $triplepet = FALSE;
                $just_added = FALSE;
                switch ($p)
                {
                case 0:
                    $species = $strats[0]->PetID1;
                    $reqbreeds = $strats[0]->Breeds1;
                    $reqhp = $strats[0]->Health1;
                    $reqsp = $strats[0]->Speed1;
                    $reqpw = $strats[0]->Power1;
                    break;
                case 1:
                    $species = $strats[0]->PetID2;
                    $reqbreeds = $strats[0]->Breeds2;
                    $reqhp = $strats[0]->Health2;
                    $reqsp = $strats[0]->Speed2;
                    $reqpw = $strats[0]->Power2;
                    if ($species > 20 && $species == $strats[0]->PetID1) {
                        $doublepet = TRUE;
                    }
                    break;
                case 2:
                    $species = $strats[0]->PetID3;
                    $reqbreeds = $strats[0]->Breeds3;
                    $reqhp = $strats[0]->Health3;
                    $reqsp = $strats[0]->Speed3;
                    $reqpw = $strats[0]->Power3;
                    if ($species > 20 && ($species == $strats[0]->PetID1 OR $species == $strats[0]->PetID2)) {
                        $doublepet = TRUE;
                    }
                    if ($species > 20 && $species == $strats[0]->PetID1 && $species == $strats[0]->PetID2) {
                        $triplepet = TRUE;
                        $doublepet = FALSE;
                    }
                    break;
                }
                                
                // Skip loop if the pet is a special pet (any family, level pet, any pet)
                if ($species > 20) {
                                   
                    $breedarray = [];
    
                    $reqhpnumber = substr ($reqhp, 1);
                    $reqpwnumber = substr ($reqpw, 1);
                    $reqspnumber = substr ($reqsp, 1);
                    $cutbreeds = explode (",", $reqbreeds);
    
                    foreach ($allbreeds as $breed => $breedstats) {
                        if ($all_pets[$species][$breed] == 1) {
                            $thishp = round ( 100 + ( ( $all_pets[$species]['Health']
                                                      + $breedstats['Health']
                                                      )
                                                    * 162.5
                                                    )
                                            , 0
                                            , PHP_ROUND_HALF_DOWN
                                            );
                            $thispw = round ( ( $all_pets[$species]['Power']
                                              + $breedstats['Power']
                                              )
                                            * 32.5
                                            , 0
                                            , PHP_ROUND_HALF_DOWN
                                            );
                            $thissp = round ( ( $all_pets[$species]['Speed']
                                              + $breedstats['Speed']
                                              )
                                            * 32.5
                                            , 0
                                            , PHP_ROUND_HALF_DOWN
                                            );
    
                            $breedok = true;
    
                            if ($reqhp)
                            {
                              $breedok = $breedok
                                      && Util_evaluate_comparison ($reqhp[0], $thishp, $reqhpnumber);
                            }
                            if ($reqsp)
                            {
                              $breedok = $breedok
                                      && Util_evaluate_comparison ($reqsp[0], $thissp, $reqspnumber);
                            }
                            if ($reqpw)
                            {
                              $breedok = $breedok
                                      && Util_evaluate_comparison ($reqpw[0], $thispw, $reqpwnumber);
                            }
    
                            if ($reqbreeds)
                            {
                                $breedok = $breedok && in_array ($breed, $cutbreeds);
                            }
    
                            $breedarray[] = ['Breed' => $breed, 'BreedOK' => $breedok];
                        }
                    }
                    $add_pet['Breeds'] = $breedarray;

                    // Check against collection
                    if ($used_collection) {
                        $colpets_array = array_filter($used_collection, function($element) use($species){ return isset($element['Species']) && $element['Species'] == $species;});
                        $usedpet = FALSE;
                        $ownedstatus = 0;
                        $add_pet['Owned']['Level'] = '-';
                        $add_pet['Owned']['Quality'] = '-';
                        $add_pet['Owned']['Breed'] = '-';
                        $add_pet['Owned']['Status'] = 0;
                        $add_pet['Status'] = 0;
                        foreach ($colpets_array as $mypetkey => $mypetvalue)
                        {
                            $ownedstatus = 1;
                            $usedpet = $mypetkey;
                            $add_pet['Owned']['Level'] = $mypetvalue['Level'];
                            $add_pet['Owned']['Quality'] = $mypetvalue['Quality'];
                            $add_pet['Owned']['Breed'] = $mypetvalue['Breed'];
                            $add_pet['Owned']['Status'] = 1;
                            $add_pet['Status'] = 1;
                            
                            if ($mypetvalue['Level'] == "25" && $mypetvalue['Quality'] == "3")
                            {
                                foreach ($breedarray as $_ => $info)
                                {
                                    if ($info['BreedOK'] && $info['Breed'] == $mypetvalue['Breed'])
                                    {
                                        $ownedstatus = 2;
                                        $usedpet = $mypetkey;
                                        $add_pet['Owned']['Status'] = 2;
                                        $add_pet['Status'] = 2;
                                    }
                                }
                            }
        
                            if ($ownedstatus === 2)
                            {
                                break;
                            }
                        }
        
                        if ($usedpet)
                        {
                            unset($used_collection[$usedpet]);
                            $used_collection = array_values($used_collection);
                            $used_collection['USEDPETS'][] = $species;
                            $rematch_breeds[$strats[0]->id][$p] = $mypetvalue['Breed'];
                        }
                        
                        if ($ownedstatus == 1 && $missing_warning > 1) {
                            $missing_warning = 1;
                        }
                        if ($ownedstatus == 0 && $missing_warning > 0) {
                            $missing_warning = 0;
                        }
                    }
                                       
                    // $add_pet['Breeds'] = $reqbreeds;
                    $add_pet['Health'] = $reqhp;
                    $add_pet['Power'] = $reqpw;
                    $add_pet['Speed'] = $reqsp;
                    $add_pet['Strategy'] = $strats[0]->id;
                    $add_pet['Sub'] = $sub->id;
                    
                    if ($doublepet == FALSE && $triplepet == FALSE) {
                        $petlist[$species]['Species'] = $species;
                        $petlist[$species]['PetID'] = $all_pets[$species]['PetID'];
                        $petlist[$species]['Family'] = $all_pets[$species]['Family'];
                        $petlist[$species]['Name'] = $all_pets[$species]['Name'];
                        $petlist[$species]['Strategies'][] = $strats[0]->id;
                        $petlist[$species]['Occurrences'][] = $add_pet;
                    }
                    if ($doublepet == TRUE) {
                        $petlist[$species]['AddOne'] == TRUE;
                        $petlist[$species]['AddOne']['Species'] = $species;
                        $petlist[$species]['AddOne']['PetID'] = $all_pets[$species]['PetID'];
                        $petlist[$species]['AddOne']['Family'] = $all_pets[$species]['Family'];
                        $petlist[$species]['AddOne']['Name'] = $all_pets[$species]['Name'];
                        $petlist[$species]['AddOne']['Strategies'][] = $strats[0]->id;
                        $petlist[$species]['AddOne']['Occurrences'][] = $add_pet;
                    }
                    if ($triplepet == TRUE) {
                        $petlist[$species]['AddTwo'] == TRUE;
                        $petlist[$species]['AddTwo']['Species'] = $species;
                        $petlist[$species]['AddTwo']['PetID'] = $all_pets[$species]['PetID'];
                        $petlist[$species]['AddTwo']['Family'] = $all_pets[$species]['Family'];
                        $petlist[$species]['AddTwo']['Name'] = $all_pets[$species]['Name'];
                        $petlist[$species]['AddTwo']['Strategies'][] = $strats[0]->id;
                        $petlist[$species]['AddTwo']['Occurrences'][] = $add_pet;
                    }
                    if (!in_array($all_pets[$species]['Family'], $familylist)) {
                        $familylist[] = $all_pets[$species]['Family'];
                    }
                }
            }
        }
    }
    sortBy('Prio', $all_tamers, 'asc');

    $usage_tracker = 0;
    
    if (!$petlist) {
        $no_fights = TRUE;
    }
    
    if ($no_fights != TRUE) {
        // Bring all pets back onto the main level of the array 
        foreach($petlist as $pet) {
            $new_petlist[] = $pet;
            $this_usage = count($pet['Occurrences']);
            if ($pet['AddOne'] == TRUE) {
                $new_petlist[] = $pet['AddOne'];
                $this_usage = count($pet['AddOne']['Occurrences']);
            }
            if ($pet['AddTwo'] == TRUE) {
                $new_petlist[] = $pet['AddTwo'];
                $this_usage = count($pet['AddTwo']['Occurrences']);
            }
            if ($this_usage > $usage_tracker) {
                $usage_tracker = $this_usage;
            }
        }
        
        // Get Status of ownership to first level of array
        if ($collection) {
            foreach($new_petlist as $key => $pet) {
                $new_petlist[$key]['Status'] = 2;
                foreach ($pet['Occurrences'] as $tamer) {
                    if ($new_petlist[$key]['Status'] > $tamer['Owned']['Status']) {
                        $new_petlist[$key]['Status'] = $tamer['Owned']['Status'];
                    }
                }
            }
            foreach($new_petlist as $pet) {
                if ($pet['Status'] == 2) {
                    $petlist_owned[] = $pet;
                }
                else {
                    $petlist_missing[] = $pet;
                }
            }
            if ($petlist_owned && $petlist_missing) {
                sortBy('Name', $petlist_owned, 'asc');
                sortBy('Name', $petlist_missing, 'asc');
                $new_petlist = array_merge($petlist_missing, $petlist_owned);
            }
            if (!$petlist_owned && $petlist_missing) {
                sortBy('Name', $petlist_missing, 'asc');
                $new_petlist = $petlist_missing;
            }
            if ($petlist_owned && !$petlist_missing) {
                sortBy('Name', $petlist_owned, 'asc');
                $new_petlist = $petlist_owned;
            }
        }
        else {
            sortBy('Name', $new_petlist, 'asc');
        }
        
        foreach ($final_strategies as $key => $strategy) {
            if ($key == 0) {
                $rematch_string = create_rematch_string($strategy, $language, $rematch_breeds[$strategy]);
            }
            else {
                $rematch_string = $rematch_string.nl2br("\n").create_rematch_string($strategy, $language, $rematch_breeds[$strategy]);
            }
        }
        $rematch_string = str_replace("<br />", "", $rematch_string);
    }
    // echo "<pre>";
    // print_r($rematch_breeds);
    $petlist = $new_petlist;
    
    ?>
    <div style="display: grid">
      
    <?php // No fights found in this section
    if ($no_fights == TRUE) { ?>
        <div style="border-radius: 10px; background-color: #b4b4b4; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_black.png" style="width: 25px; margin-right: 10px; float: left">
            There are currently no strategies in this section.<br>
        </div> 
    <?
    die;
    } ?>
        
    <div style="position: absolute; top: 0px; left: 1000px; margin: 0 0 10 5; width: 300px">
        <div style="margin-top: 10px;" id="rematch_string_pettable" data-clipboard-text="<?php echo $rematch_string ?>">
			<button class="bnetlogin">Mass Rematch Export</button>
		</div>

		<div class="remtt" style="display:none;" id="rematchconfirm_pettable"><?php echo __("Copied to clipboard!") ?></div>

		<script>
		var btn = document.getElementById('rematch_string_pettable');
		var clipboard = new Clipboard(btn);

		clipboard.on('success', function(e) {
			console.log(e);
				$('#rematchconfirm_pettable').delay(0).fadeIn(500);
				$('#rematchconfirm_pettable').delay(1200).fadeOut(500);
			});
		clipboard.on('error', function(e) {
			console.log(e);
		});
		</script>
    </div>
    
    
    <?
    // Warnings and Disclaimers
    if (!$collection && $type != 'heroic') { // NO collection regular categories ?>
        <div style="border-radius: 10px; background-color: #b4b4b4; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_black.png" style="width: 25px; margin-right: 10px; float: left">
            This feature works best if you create an account and import your collection.<br>
            Only strategies that are best suited for the pets you own will then be selected.
        </div>
    <?php }
    
    // Warnings and Disclaimers
    if (!$collection && $type == 'heroic') { // NO collection and heroic categories ?>
        <div style="border-radius: 10px; background-color: #e6b3b3; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_red.png" style="width: 25px; margin-right: 10px; float: left">
            This feature only works properly if you create an account and import your collection (top right corner of the page).<br>
            Without a collection to compare against, the table will be inaccurate and likely not suitable for an attempt without healing.
        </div>
    <?php }
    
    if ($main == 66 && (!$collection OR ($collection && $missing_warning != 2))) { // Blackrock Depths ?>
        <div style="border-radius: 10px; background-color: #dddcb0; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_orange.png" style="width: 25px; margin-right: 10px; float: left">
            This dungeon has random tamers in Stage 3 and 5. The table below does not reflect that and assumes you will have to fight all tamers in the list.<br>
            Your run will likely not require all of the pets shown.
        </div>
    <?php }
    
    if ($main == 60 && (!$collection OR ($collection && $missing_warning != 2))) { // Stratholme ?>
        <div style="border-radius: 10px; background-color: #dddcb0; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_orange.png" style="width: 25px; margin-right: 10px; float: left">
            This dungeon has the optional tamers Fras Siabi and Postmaster Malowne. The table below does not reflect that and includes them by default.<br>
            You will not require all of the pets shown to just complete the dungeon.
        </div>
    <?php }
    
    if ($collection && $usage_tracker > 3 && $type == 'heroic') { // Heroic with collection and a pet is being used more than 3 times ?>
        <div style="border-radius: 10px; background-color: #dddcb0; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_orange.png" style="width: 25px; margin-right: 10px; float: left">
            The table has picked a pet to be used in more than 3 fights, even though there is no healing possible in this encounter.<br>
            This is likely because you have picked multiple favourite strategies (the green heart) using this pet. It can be perfectly fine in some situations but please make sure to double-check before starting the encounter. 
        </div>
    <?php }
    
    if ($main == 35 && (!$collection OR ($collection && $missing_warning != 2))) { // Falcosaurs ?>
        <div style="border-radius: 10px; background-color: #dddcb0; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_warning_orange.png" style="width: 25px; margin-right: 10px; float: left">
            To complete the quests, you only need to complete 3 fights per Falcosaur pet. The tables here to not reflect that and list ALL fights.<br>
            You will definitely not need all pets shown below.
        </div>
    <?php }
    
    if ($collection && $missing_warning == 2) { // Collection and all required pets available ?>
        <div style="border-radius: 10px; background-color: #abe2a4; margin: 0 0 10 5; padding: 10px; width: 700px">
            <img src="images/icon_petlist_check.png" style="width: 18px; margin-right: 10px; float: left">
            Smooth sailing! You have all pets required for this section. Enjoy the fights!
        </div>
    <?php }
    
    
    if ($missing_warning < 2) { // Collection and NOT all pets available
        if ($missing_warning == 1) {
            $warning_icon = 'orange';
            $warning_color = '#dddcb0';
        }
        if ($missing_warning == 0) {
            $warning_icon = 'red';
            $warning_color = '#e6b3b3';
        }
        ?>
        <div style="border-radius: 10px; background-color: <?php echo $warning_color ?>; margin: 0 0 10 5; padding: 10px; ; width: 700px; grid-column: 1">
            <img src="images/icon_warning_<?php echo $warning_icon ?>.png" style="width: 25px; margin-right: 10px; float: left">
            At least one required pet is not in your collection or not level 25 rare.<br>
            You might be able to use a substitute which the table does not check for automatically.<br>
            Take a look at the affected strategies directly to see if this is possible for you.
        </div>
    <?php }
    
    if ($type == 'heroic') { // Showing the table of fights for HC Dungeons ?>
        <div style="border-radius: 10px; width: 700px; background-color: #b4b4b4; margin: 0 0 10 5; padding: 10px">
            <div>
                <img src="images/icon_petlist_guide.png" style="width: 20px; margin-right: 10px; float: left">
                The following strategies have been selected based on your preferences.<br>
                Follow these links in order for the best experience:<br>
                <table style="padding: 5 0 0 25">
                    <?
                    $count_subs = 1;
                    foreach ($all_tamers as $tamer) { ?>
                        <tr>
                            <td><?php echo $count_subs ?>.</td>
                            <td><a class="weblink" href="?Strategy=<?php echo $tamer['Strategy']; ?>" target="_blank"><?php echo $tamer['Name']; ?></a></td>
                        </tr>
                    <?
                    $count_subs++;
                    } ?>
                </table>
            </div>
        </div>
    <?php } ?>   
    
    <div style="display: block">
    <table id="t1" style="margin-left: 5px; border-collapse: collapse; border: 5px solid #a2a2a2;" class="petlist article_petlist example table-autosort table-autofilter table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
        <thead>
            <tr>
                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 15px;"><?php echo __("Name") ?></p></th>
                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><?php echo __("Family") ?></th>
                <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black" style="margin-left: 0px;"><?php echo __("Fights") ?></th>
                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 0px;"><?php echo __("Tamer") ?></th>
                <th align="center" class="petlistheaderfirst" colspan="4"><p class="blogodd"><?php echo __("Requirements") ?></th>
                <?php if ($collection) { ?>
                    <th colspan="3" align="center" class="petlistheaderyourcol"><p class="blogodd"><?php echo __("Your Pet Collection") ?></th>
                <?php } ?>
            </tr>

            <tr>
                <th align="left" class="petlistheadersecond" style="width: 150px">
                    <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                </th>

                <th align="center" class="petlistheadersecond">
                        <select class="petselect" style="width:80px;" id="familiesfilter" onchange="Table.filter(this,this)">
                                <option class="petselect" value=""><?php echo __("All") ?></option>
                                <?
                                    if (in_array("Humanoid", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Humanoid").'">'.__("Humanoid").'</option>';
                                    }
                                    if (in_array("Dragonkin", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Dragonkin").'">'.__("Dragonkin").'</option>';
                                    }
                                    if (in_array("Flying", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Flying").'">'.__("Flying").'</option>';
                                    }
                                    if (in_array("Undead", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Undead").'">'.__("Undead").'</option>';
                                    }
                                    if (in_array("Critter", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Critter").'">'.__("Critter").'</option>';
                                    }
                                    if (in_array("Magic", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Magic").'">'.__("Magic").'</option>';
                                    }
                                    if (in_array("Elemental", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Elemental").'">'.__("Elemental").'</option>';
                                    }
                                    if (in_array("Beast", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Beast").'">'.__("Beast").'</option>';
                                    }
                                    if (in_array("Aquatic", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Aquatic").'">'.__("Aquatic").'</option>';
                                    }
                                    if (in_array("Mechanical", $familylist)) {
                                        echo '<option class="petselect" value="'.__("Mechanical").'">'.__("Mechanical").'</option>';
                                    }
                                ?>
                        </select>
                </th>
                
                <th align="left" class="petlistheadersecond">
                    <select class="petselect" style="width:60px;" id="occfilter" onchange="Table.filter(this,this)">
                        <option class="petselect" value=""><?php echo __("All") ?></option>
                        <option class="petselect" value="1">1</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>1;}">> 1</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>5;}">> 5</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>10;}">> 10</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>20;}">> 20</option>
                    </select>
                </th>
                
                <th align="center" class="petlistheadersecond" style="width: 200px">
                    <input class="petselect" name="filter" size="5" id="tamerfilter" onkeyup="Table.filter(this,this)">
                </th>
                
                <th align="center" class="petlistheadersecond" style="width: 60px"><p class="blogodd"><img src="images/bt_icon_health.png"></th>
                <th align="center" class="petlistheadersecond" style="width: 50px"><p class="blogodd"><img src="images/bt_icon_speed.png"></th>
                <th align="center" class="petlistheadersecond" style="width: 50px"><p class="blogodd"><img src="images/bt_icon_power.png"></th>
                <th align="left" class="petlistheadersecond" style="width: 100px"><p class="blogodd">Breeds</th>

                <?php if ($collection) { ?>
                    <th align="center" class="petlistheaderyourcol" style="width: 75px"><p class="blogodd"><?php echo __("Level") ?></th>
                    <th align="center" class="petlistheaderyourcol" style="width: 75px"><p class="blogodd"><?php echo __("Quality") ?></th>
                    <th align="center" class="petlistheaderyourcol" style="width: 75px"><p class="blogodd"><?php echo __("Breed") ?></th>
                <?php } ?>

            </tr>
        </thead>

        <tbody>

    <?
    foreach($petlist as $pet) {
        print_table_row($pet, 1);
    }
} ?>
</table>
</div>
    
<script src="data/table.js"></script>

<script>
    function expand_row(id) {
     $('.'+id).fadeIn('slow');
     $('.'+id+'_toggle').hide();
    }
</script>


</div>

<?
function print_table_row($pet, $counter) {
    global $collection, $all_tamers;
    
    $row_class = 'article_petlist'; // default cell background
    if ($collection) { // Colour table cells based on collection ownership
        switch ($pet['Status']) {
        case "0":
            $row_class = 'article_petlist_red';
            break;
        case "1":
            $row_class = 'article_petlist_yellow';
            break;
        }
    }
    ?>

        <tr class="<?php echo $row_class ?>">
            <td class="petlist" align="left" style="padding-left: 4px;"><a class="petlist" style="font-family: MuseoSans-500,Roboto" href="http://www.wowhead.com/npc=<?php echo $pet['PetID'] ?>" target="_blank"><?php echo $pet['Name'] ?></a></td>
            <td align="left" class="petlist" style="padding-left: 4px;"><center><p class="blogodd"><?php echo $pet['Family'] ?></center></td>
            <td class="petlist"><center><p class="blogodd"><?php echo count($pet['Occurrences']); ?></p></span></center></td>
            
            <?
            $colspan = 5;
            if ($collection) {
               $colspan = 8; 
            } ?>
            
            <td class="petlist" colspan="<?php echo $colspan ?>" style="margin: 0px; padding: 0px; border-left: 1px dotted #434343">
                <table class="petlist_subtable" style="width: 100%;" cellspacing="0" cellpadding="0">
                    <?
                    $tamerlist = $pet['Occurrences'];
                    sortBy('Status', $tamerlist, 'asc');
                    $visible_tamers = 0;
                    foreach ($tamerlist as $key => $tamer) {
                        // Create Breeds info.
                        $breedsoutput = '';
                        $breedreqs = FALSE;
                        foreach ($tamer['Breeds'] as $breed) {
                            if ($breed['BreedOK'] == TRUE) {
                                $breedsoutput = $breedsoutput.', '.$breed['Breed'];
                            }
                            else {
                                $breedreqs = TRUE;
                            }
                        }
                        $breedsoutput = substr($breedsoutput, 2);
                        if ($breedreqs == FALSE) {
                            $breedsoutput = '';
                        }
                        if ($breedreqs == TRUE && $breedsoutput == '') {
                            $breedsoutput = 'Unclear';
                        }
                        
                        // Define bg color
                        $row_class = 'article_petlist'; // default background
                        if ($collection) { // Colour table cells based on collection ownership
                            $ownership = 2;
                            if ($ownership > $tamer['Owned']['Status']) {
                                $ownership = $tamer['Owned']['Status'];
                            }
                            switch ($ownership) {
                            case "0":
                                $row_class = 'article_petlist_red';
                                break;
                            case "1":
                                $row_class = 'article_petlist_yellow';
                                break;
                            }
                        }
                        
                        // Define collapsing table rows
                        $rowcollapse_id = '';
                        $rowcollapse = '';
                        $rowcollapse_add = FALSE;
                        
                        // For no collection:
                        if (!$collection) {
                            if ($key > 0 && count($pet['Occurrences']) > 2) {
                                $rowcollapse_id = 'pet_row_'.$pet['Species'].'_'.$counter;
                                $rowcollapse = 'style="display: none"';
                                $rowcollapse_add = TRUE;
                            }   
                        }
                        // For collection
                        if ($collection) {
                            if ($tamer['Status'] == 2 && count($pet['Occurrences']) > 2 && $visible_tamers > 0) {
                                $rowcollapse_id = 'pet_row_'.$pet['Species'].'_'.$counter;
                                $rowcollapse = 'style="display: none"';
                                $rowcollapse_add = TRUE;
                            }
                            else {
                                $visible_tamers++;
                            }
                        }

                        
                        ?>
                        <tr class="<?php echo $row_class.' '.$rowcollapse_id ?>" <?php echo $rowcollapse ?>>
                            <td class="petlist" align="left" style="width: 200px"><a class="petlist" href="?Strategy=<?php echo $tamer['Strategy']; ?>" target="_blank"><?php echo $all_tamers[$tamer['Sub']]['Name']; ?></a></td>
                            <td class="petlist" align="center" style="width: 60px"><p class="blogodd"><?php echo $tamer['Health'] ?></td>
                            <td class="petlist" align="center" style="width: 50px"><p class="blogodd"><?php echo $tamer['Speed'] ?></td>
                            <td class="petlist" align="center" style="width: 50px"><p class="blogodd"><?php echo $tamer['Power'] ?></td>
                            <td class="petlist" align="left" style="width: 100px"><p class="blogodd"><?php echo $breedsoutput ?></td>
                            
                            <?php if ($collection) { ?>
                                <td class="petlist petlist_yours" align="center" style="width: 75px; border-left: 1px dotted #434343"><p class="blogodd"><?php echo $tamer['Owned']['Level']; ?></td>
                                <td class="petlist" align="center" style="width: 75px"><p class="blogodd"><?
                                    if ($tamer['Owned']['Quality'] == 3) { echo '<font color="#0058a5">'.__("Rare"); }
                                    if ($tamer['Owned']['Quality'] == 2) { echo '<font color="#147e09">'.__("Uncommon"); }
                                    if ($tamer['Owned']['Quality'] == 1) { echo '<font color="#ffffff">'.__("Common"); }
                                    if ($tamer['Owned']['Quality'] === 0) { echo '<font color="#4d4d4d">'.__("Poor"); }
                                    if ($tamer['Owned']['Quality'] === '-') { echo '<font color="#000000">-'; } ?></td>
                                <td class="petlist" align="center" style="width: 75px"><p class="blogodd"><?php echo $tamer['Owned']['Breed']; ?></td>
                            <?php } ?>  
                         </tr>
                    <?php }
                    if ($rowcollapse_add == TRUE) { ?>
                        <tr class="article_petlist <?php echo $rowcollapse_id.'_toggle'; ?>">
                            <td style="cursor: pointer; text-align: center" onclick="expand_row('<?php echo $rowcollapse_id ?>')" colspan="<?php echo $colspan ?>"><font color="#6a6a6a">▼</font> <i>Show all Tamers</i> <font color="#6a6a6a">▼</font></td>
                        </tr>
                    <?php } ?>
                </table>
            </td>
        </tr> 
<?php }



if ($error == TRUE) { ?>
    <center>
    <div id="petlist_error" style="display: none">
    <img src="images/xufu_sad.png">
    <br>
    <b>There was a problem loading the pet list. <?php echo $error_message ?><br>
    If this error keeps appearing, please inform <a class="wowhead" href="mailto:XuFu@WoW-Petguide.com">Aranesh</a>.</b>
    <br>
    <br>
    </div
    </center>
   <script>
    $('#loading_field').hide('fade');
    $('#petlist_error').show('fade');
   </script> 
<?php }
?>