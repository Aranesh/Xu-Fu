<?

include_once ('classes/Growl.php');
include_once ('classes/Util.php');
include_once ('classes/Database.php');

// User.php

function User_can_edit_strat ($strat)
{
    global $userrights, $user;
    return $userrights['EditStrats'] == "yes"
        || $strat->User == $user->id;
}
function User_can_save_td_script ($strat)
{
    global $userrights, $user;
    return User_can_edit_strat ($strat)
        || $userrights['EditTDScripts'] == "yes";
}

function StratEditProcess_update_time ($strat)
{
    Database_query ( "UPDATE Alternatives "
        . "SET `Updated` = CURRENT_TIMESTAMP"
        . " WHERE id = '$strat->id'"
    );
}

// strat_edit_process.php

function StratEditProcess_save_td_script ($strat, $raw_script)
{
    global $user;
    $tdscripty = Database_escape_string ($raw_script);
    $tag_script = $tdscripty != "";

    Database_query ( "UPDATE Alternatives "
                   . "SET `tdscript` = '$tdscripty'"
                   . "WHERE id = '$strat->id'"
                   );
    
    $updatetags[17] = intval($tag_script); // TDScript Tag
    
    update_tags($strat->id, $updatetags);
    
    StratEditProcess_update_time($strat);
    
    Database_protocol_user_activity ($user, 4, "TD Script Updated", $strat->id);

    Growl_show_notice ("TD Script saved.");
}

function StratEditProcess_save_comment ($strat, $raw_comment)
{
    global $user;
    $savecom = Database_escape_string ($raw_comment);

    Database_query ( "UPDATE Alternatives "
                   . "SET `Comment` = '$savecom' "
                   . "WHERE id = '$strat->id'"
                   );

    StratEditProcess_update_time($strat);               
                   
    Database_protocol_user_activity ($user, 4, "Creator Comment Updated", $strat->id);

    Growl_show_notice ("Strategy info saved.");
}

function StratEditProcess_reset_ratings ($strat)
{
    Database_DELETE ('UserFavStrats', 'WHERE Strategy = ?', 'i', $strat->id);
    Database_DELETE ('UserStratRating', 'WHERE Strategy = ?', 'i', $strat->id);

    Growl_show_notice ("Ratings and Favourites removed.");
}

function StratEditProcess_update_alternative_stats ($strat, $field, $shortfield, $index)
{
    global $_POST;
    $req_cond = $_POST['req' . $shortfield . '_cond_' . $index];
    $req_value = $_POST['req' . $shortfield . '_numb_' . $index];

    $db_field_name = $field . $index;

    $new_value = $req_value == "" ? "" : $req_cond . $req_value;
    $old_value = $strat->$db_field_name;

    if ($new_value != $old_value)
    {
        Database_query ( "UPDATE Alternatives "
                       . "SET `$db_field_name` = '$new_value' "
                       . "WHERE id = '$strat->id'"
                       );
        StratEditProcess_update_time($strat);
    }
}

function StratEditProcess_update_hard_breed_requirements ($strat, $index, $possible_breeds, $pet_id, $inputskills)
{
    global $_POST;
    $required_level = $_POST['min_level_' . $index];

    if (Util_is_actual_pet_id ($pet_id))
    {
        $required_breeds = [];
        foreach ($possible_breeds as $breed)
        {
            if ($_POST['Pet' . $index . '_' . $breed] == "on")
            {
                $required_breeds[] = $breed;
            }
        }

        $breedstring = sizeof ($required_breeds) == sizeof ($possible_breeds)
                     ? ''
                     : implode ($required_breeds, ',');

        Database_query ( "UPDATE Alternatives "
                       . "SET `PetID${index}` = '$pet_id'"
                         . ", `PetName${index}` = ''"
                         . ", `PetLevel${index}` = ''"
                         . ", `SkillPet${index}1` = '" . $inputskills[1] . "'"
                         . ", `SkillPet${index}2` = '" . $inputskills[2] . "'"
                         . ", `SkillPet${index}3` = '" . $inputskills[3] . "'"
                         . ", `Breeds${index}` = '$breedstring'"
                       . " WHERE id = '$strat->id'"
                       );
    }
    else
    {
        Database_query ( "UPDATE Alternatives "
                       . "SET `PetID${index}` = '$pet_id'"
                         . ", `PetName${index}` = ''"
                         . ", `PetLevel${index}` = '${required_level}'"
                         . ", `SkillPet${index}1` = '1'"
                         . ", `SkillPet${index}2` = '1'"
                         . ", `SkillPet${index}3` = '1'"
                       . " WHERE id = '$strat->id'"
                       );
    }
    StratEditProcess_update_time($strat);
}

function StratEditProcess_parse_skill_slots ($input, $pet, $slot, $checkpet)
{
    if ($input == "slot" . $pet . "_" . $slot . "x")
    {
        return 0;
    }

    $skillpieces = explode ("_", $input);
    $skill_id = $skillpieces[0];
    $pet_slot = $skillpieces[1];
    $skill_slot = $skillpieces[2];

    if ($pet_slot != $pet)
    {
        throw new Exception ("internal inconsistency: $pet_slot != $pet");
    }

    $skill1_varname = "Skill" . $slot;
    $skill2_varname = "Skill" . ($slot + 3);

    if ($skill_slot == 1 && $checkpet->$skill1_varname == $skill_id)
    {
        return 1;
    }
    else if ($skill_slot == 2 && $checkpet->$skill2_varname == $skill_id)
    {
        return 2;
    }

    throw new Exception ("inconsistency between skill slot and actual pet skills");
}


function StratEditProcess_breeds_for_pet ($checkpet)
{
    $breed_strings = ["BB", "PP", "SS", "HH", "HP", "PS", "HS", "PB", "SB", "HB"];
    $possible_breeds = [];
    foreach ($breed_strings as $breed)
    {
        if ($checkpet->$breed == "1")
        {
            $possible_breeds[] = $breed;
        }
    }
    return $possible_breeds;
}

function StratEditProcess_save_pets ($strat)
{
    global $_POST, $user, $all_pets;

    $pet_ids = [ 0 /* one based lol */
               , $_POST['edit_pet1_select']
               , $_POST['edit_pet2_select']
               , $_POST['edit_pet3_select']
               ];
    
    $pet_levels = [ 0
               , $_POST['min_level_1']
               , $_POST['min_level_2']
               , $_POST['min_level_3']
               ];

    try
    {
        Util_require_integer_lt ($_POST['reqhp_numb_1'], 10000);
        Util_require_integer_lt ($_POST['reqhp_numb_2'], 10000);
        Util_require_integer_lt ($_POST['reqhp_numb_3'], 10000);
        Util_require_integer_lt ($_POST['reqsp_numb_1'], 1000);
        Util_require_integer_lt ($_POST['reqsp_numb_2'], 1000);
        Util_require_integer_lt ($_POST['reqsp_numb_3'], 1000);
        Util_require_integer_lt ($_POST['reqpw_numb_1'], 1000);
        Util_require_integer_lt ($_POST['reqpw_numb_2'], 1000);
        Util_require_integer_lt ($_POST['reqpw_numb_3'], 1000);
        Util_require_comparison_operator ($_POST['reqhp_cond_1']);
        Util_require_comparison_operator ($_POST['reqhp_cond_2']);
        Util_require_comparison_operator ($_POST['reqhp_cond_3']);
        Util_require_comparison_operator ($_POST['reqsp_cond_1']);
        Util_require_comparison_operator ($_POST['reqsp_cond_2']);
        Util_require_comparison_operator ($_POST['reqsp_cond_3']);
        Util_require_comparison_operator ($_POST['reqpw_cond_1']);
        Util_require_comparison_operator ($_POST['reqpw_cond_2']);
        Util_require_comparison_operator ($_POST['reqpw_cond_3']);

        Util_require_integer ($pet_ids[1]);
        Util_require_integer ($pet_ids[2]);
        Util_require_integer ($pet_ids[3]);

        $checkpets = [];
        $inputskills = [];

        for ($index = 1; $index <= 3; $index += 1)
        {
            if (!Util_is_actual_pet_id ($pet_ids[$index]))
            {
                Util_require_level ($_POST['min_level_' . $index]);
            }
            else
            {
                $checkpets[$index] = Database_query_object ( "SELECT * FROM PetsUser "
                                                           . "WHERE RematchID = '" . $pet_ids[$index] . "' "
                                                           . "LIMIT 1"
                                                           );
                $inputskills[$index][1] = StratEditProcess_parse_skill_slots ($_POST['petskill' . $index . '_1'], $index, 1, $checkpets[$index]);
                $inputskills[$index][2] = StratEditProcess_parse_skill_slots ($_POST['petskill' . $index . '_2'], $index, 2, $checkpets[$index]);
                $inputskills[$index][3] = StratEditProcess_parse_skill_slots ($_POST['petskill' . $index . '_3'], $index, 3, $checkpets[$index]);
            }
        }

        // Actual updates start here.

        $updatetags[5] = 0; // TCG Tag
        $updatetags[6] = 0; // Shop Tag
        $updatetags[18] = 0; // Unobtainable Tag
        $updatetags[7] = 0; // Level 1 Tag
        
        for ($i = 1; $i <= 3; $i += 1)
        {  
            StratEditProcess_update_alternative_stats ($strat, "Health", "hp", $i);
            StratEditProcess_update_alternative_stats ($strat, "Speed", "sp", $i);
            StratEditProcess_update_alternative_stats ($strat, "Power", "pw", $i);

            $possible_breeds = StratEditProcess_breeds_for_pet ($checkpets[$i]);

            StratEditProcess_update_hard_breed_requirements ($strat, $i, $possible_breeds, $pet_ids[$i], $inputskills[$i]);

            if ($pet_ids[$i] == 0)
            {
                $updatetags[7] = 1;
            }
            if ($all_pets[$pet_ids[$i]]['Source'] == 2)
            {
                $updatetags[5] = 1;
            }
            if ($all_pets[$pet_ids[$i]]['Source'] == 1)
            {
                $updatetags[6] = 1;
            }
            if ($all_pets[$pet_ids[$i]]['Obtainable'] == 2)
            {
                $updatetags[18] = 1;
            }
        }

        update_tags($strat->id, $updatetags);
        
        StratEditProcess_update_time($strat);
        
        // Sanity checking details entered - TODO

        Database_protocol_user_activity ($user, 4, "Strategy Pets Changed", $strat->id);

        Growl_show_notice ("New pet details saved.");
    }
    catch (Exception $e)
    {
        Growl_show_error ("There was an error saving your details. Please refresh the page and try again. " . $e->getMessage());
    }
}


if ($strat)
{
  $alt_edit_action = isset($_POST['alt_edit_action']) ? $_POST['alt_edit_action'] : null;

  if (isset($_POST['save_td']) && $_POST['save_td'] == "true" && User_can_save_td_script ($strat))
  {
      StratEditProcess_save_td_script ($strat, $_POST['tdscript']);
  }

  if ($alt_edit_action == "edit_save_comment" && User_can_edit_strat ($strat))
  {
      StratEditProcess_save_comment ($strat, $_POST['article_content_en_US']);
  }
  
  if ($alt_edit_action == "edit_reset" && User_can_edit_strat ($strat))
  {
      StratEditProcess_reset_ratings ($strat);
  }

  if ($alt_edit_action == "edit_save_pets" && User_can_edit_strat ($strat))
  {
      StratEditProcess_save_pets ($strat);
  }

  //! \note Reload modified entry since we will continue with outputting the site.
  //! \todo Only reload if we actually changed something.
  $strat = Database_query_object ("SELECT * FROM Alternatives WHERE id = $strat->id");
}
