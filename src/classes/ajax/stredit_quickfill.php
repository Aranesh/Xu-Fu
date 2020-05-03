<?
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$lineid = $_REQUEST["lineid"];
$type = $_REQUEST["type"];
$customid = $_REQUEST["customid"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

if ($userid) {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        $userrights = format_userrights($user->Rights);
        if ($user->ComSecret == $comsecret) {
            $veri1 = "true";
        }
    }
}
if ($veri1 == "true") {
    $stepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE id = '$lineid'");
    if (mysqli_num_rows($stepdb) > "0") {
        $step = mysqli_fetch_object($stepdb);
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$step->SortingID'");
        if (mysqli_num_rows($stratdb) > "0") {
            $strat = mysqli_fetch_object($stratdb);
            if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {
                $veri2 = "true";   
            }
        }
    }
}

if ($veri2 == "true") {
    // Type 0 - Pet Skill via Quick-Fill
    if ($type == "0") {
        $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$customid'");
        $spell = mysqli_fetch_object($spelldb);
        $spellinput = mysqli_real_escape_string($dbcon, $spell->en_US);
        $spellinput = mysqli_real_escape_string($dbcon, $spellinput);
        $newinstruction = "[spell=".$spellinput."]";
        $entersuc = "true";
    }

    // Type 1 - Quick Text: Pass
    if ($type == "1") {
        $newinstruction = "Pass";
        $entersuc = "true";            
    }
    
    // Type 2 - Quick Text: Bring in regular pet
    if ($type == "2") {
        $petdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID = '$customid'");
        if (mysqli_num_rows($petdb) > "0") {
            $pet = mysqli_fetch_object($petdb);
            $petinput = mysqli_real_escape_string($dbcon, $pet->Name);
            $petinput = mysqli_real_escape_string($dbcon, $petinput);
            $newinstruction = "[i]Bring in your [pet=".$petinput."][/i]";
            $entersuc = "true";
            $clearturn = "true";
        } 
    }
    
    // Type 3 - Quick Text: Swap to regular pet
    if ($type == "3") {
        $petdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID = '$customid'");
        if (mysqli_num_rows($petdb) > "0") {
            $pet = mysqli_fetch_object($petdb);
            $petinput = mysqli_real_escape_string($dbcon, $pet->Name);
            $petinput = mysqli_real_escape_string($dbcon, $petinput);
            $newinstruction = "Swap to your [pet=".$petinput."]";
            $entersuc = "true";
        } 
    }

    // Type 4 - Bring in level pet
    if ($type == "4") {
        $newinstruction = "[i]Bring in your Level Pet[/i]";
        $entersuc = "true";
        $clearturn = "true";
    }

    // Type 5 - Swap to your level pet
    if ($type == "5") {
        $newinstruction = "Swap to your Level Pet";
        $entersuc = "true";
    }
    
    // Type 6 - Bring in family pet
    if ($type == "6") {
        if ($customid == "Humanoid" OR $customid == "Magic" OR $customid == "Elemental" OR $customid == "Undead" OR $customid == "Mechanical" OR $customid == "Flying" OR $customid == "Critter" OR $customid == "Aquatic" OR $customid == "Beast" OR $customid == "Dragonkin") {
            $newinstruction = "[i]Bring in your ".$customid." pet[/i]";
            $entersuc = "true";
            $clearturn = "true";
        }
    }
    
    // Type 7 - Bring in family pet
    if ($type == "7") {
        if ($customid == "Humanoid" OR $customid == "Magic" OR $customid == "Elemental" OR $customid == "Undead" OR $customid == "Mechanical" OR $customid == "Flying" OR $customid == "Critter" OR $customid == "Aquatic" OR $customid == "Beast" OR $customid == "Dragonkin") {
            $newinstruction = "Swap to your ".$customid." pet";
            $entersuc = "true";
        }
    }
    
    // Type 8 - Quick Text: enemy pet comes in
    if ($type == "8") {
        $petdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE PetID = '$customid'");
        if (mysqli_num_rows($petdb) > "0") {
            $pet = mysqli_fetch_object($petdb);
            $inputpetname = mysqli_real_escape_string($dbcon, $pet->Name);
            $inputpetname = mysqli_real_escape_string($dbcon, $inputpetname);
            $newinstruction = "[i][enemy=".$inputpetname."] comes in[/i]";
            $entersuc = "true";
            $clearturn = "true";
        } 
    }
    
    // Type 9 - Quick Text
    if ($type == "9") {
        $newinstruction = "[i]An enemy pet comes in[/i]";
        $entersuc = "true";
        $clearturn = "true";
    }

    // Type 10 - Quick Text
    if ($type == "10") {
        $newinstruction = "Any standard attack will finish the fight";
        $entersuc = "true";
        // $clearturn = "true";
    }
}


if ($entersuc == "true") {
    mysqli_query($dbcon, "UPDATE Strategy SET `Instruction` = '$newinstruction' WHERE id = '$lineid'");
    mysqli_query($dbcon, "UPDATE Strategy SET `Instruction_de_DE` = '', `Instruction_fr_FR` = '', `Instruction_it_IT` = '', `Instruction_es_ES` = '', `Instruction_pl_PL` = '', `Instruction_pt_PT` = '', `Instruction_ru_RU` = '', `Instruction_es_MX` = '', `Instruction_pt_BR` = '', `Instruction_ko_KR` = '', `Instruction_zh_TW` = '' WHERE id = '$lineid'");
    mysqli_query($dbcon, "UPDATE Strategy SET `Step_de_DE` = '', `Step_fr_FR` = '', `Step_it_IT` = '', `Step_es_ES` = '', `Step_pl_PL` = '', `Step_pt_PT` = '', `Step_ru_RU` = '', `Step_es_MX` = '', `Step_pt_BR` = '', `Step_ko_KR` = '', `Step_zh_TW` = '' WHERE id = '$lineid'");
    
    $stepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = '$strat->id' AND id < '$lineid' ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($stepdb) > "0") {
       $prevstep = mysqli_fetch_object($stepdb);
       echo $prevstep->id;
       $prevstepcont = $prevstep->Step;
    }
    else {
       echo "firstline";
       $prevstepcont = "";
    }
    
    // Remove Turn info if necessary
    if ($clearturn == "true") {
        mysqli_query($dbcon, "UPDATE Strategy SET `Step` = '' WHERE id = '$lineid'"); 
    }
    else { // Auto-Fill Turn numbers and stuff
        $steppieces = explode(" ", $prevstepcont);
        if ($steppieces[0] == "Turn" AND preg_match("/^[1234567890]*$/is", $steppieces[1])) {
            $newstep = $steppieces[1]+1;
            $inputstep = "Turn ".$newstep;
            mysqli_query($dbcon, "UPDATE Strategy SET `Step` = '$inputstep' WHERE id = '$lineid'");
        }
        if ($steppieces[0] == "Turns" OR $steppieces[0] == "Turn") {
            $piecetwo = explode("-", $steppieces[1]);
            $piecethree = explode("+", $steppieces[1]);
            if ($piecetwo[1] != "" AND preg_match("/^[1234567890]*$/is", $piecetwo[1])) {
                $newnumber = $piecetwo[1];
            }
            if ($piecethree[1] != "" AND preg_match("/^[1234567890]*$/is", $piecethree[1])) {
                $newnumber = $piecethree[1];
            }
            if ($newnumber) {
                $newstep = $newnumber+1;
                $inputstep = "Turn ".$newstep;
                mysqli_query($dbcon, "UPDATE Strategy SET `Step` = '$inputstep' WHERE id = '$lineid'");                    
            }
        }
        if ($prevstepcont == "") {
            mysqli_query($dbcon, "UPDATE Strategy SET `Step` = 'Turn 1' WHERE id = '$lineid'");
        }
    }

    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Strategy Step Edited - Quickfill', 'Strategy $strat->id - Step $lineid')") OR die(mysqli_error($dbcon));
}
else {
    echo "NOK";
}
