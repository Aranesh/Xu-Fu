<?php
include("../../data/dbconnect.php");
include("../functions.php");

$species = $_REQUEST["species"];
$command = $_REQUEST["command"];
$value = $_REQUEST["value"];
$newid = $_REQUEST["newid"];

$all_pets = get_all_pets("Name");
$all_tags = get_all_tags();

if ($command != "npcid" && $command != "cageable" && $command != "source" && $command != "obtainable" && $command != "difficulty" && $command != "unique" && $command != "defrarity") {
    $upderror = TRUE;
}

if ($upderror != TRUE) {
    $speciesdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID = '$species' LIMIT 1");
    if (mysqli_num_rows($speciesdb) > "0") {
        switch($command) {
            case "npcid":
                if (!preg_match("/^[1234567890]*$/is", $newid)) {                                  
                    $upderror = TRUE;
                }
                else {
                    $speciesdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID = '$newid' LIMIT 1");
                    if (mysqli_num_rows($speciesdb) > "0" && $newid != "") {
                        echo "DUP";
                        die;
                    }
                    else {
                        mysqli_query($dbcon, "UPDATE PetsUser SET `PetID` = '$newid' WHERE RematchID = '$species'");
                    }
                }
                break;
            case "cageable":
                if ($value != "0" && $value != "1" && $value != "2") {
                    $upderror = TRUE;
                }
                else {
                    mysqli_query($dbcon, "UPDATE PetsUser SET `Cageable` = '$value' WHERE RematchID = '$species'");
                }
                break;
            case "source":
                if ($value != "0" && $value != "1" && $value != "2" && $value != "3") {
                    $upderror = TRUE;
                }
                else {
                    // 0 = undecided, 1 = Shop, 2 = TCG, 3 = normal
                    // Remove Shop tag unless another pet is from shop
                    if ($all_pets[$species]['Source'] == 1 && $value != 1) { 
                        $alts_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE PetID1 = '$species' OR PetID2 = '$species' OR PetID3 = '$species'");
                        while($this_alt = mysqli_fetch_object($alts_db)) {
                            $updatetags = "";
                            if (($all_pets[$this_alt->PetID1]['Source'] == 1 && $this_alt->PetID1 != $species) OR ($all_pets[$this_alt->PetID2]['Source'] == 1 && $this_alt->PetID2 != $species) OR ($all_pets[$this_alt->PetID3]['Source'] == 1 && $this_alt->PetID3 != $species)) {
                            }
                            else {
                                $updatetags[6] = 0;
                                update_tags($this_alt->id, $updatetags);
                                $updatetags = "";
                            }
                        }
                    }
 
                    // Remove TCG tag unless another pet is from shop
                    if ($all_pets[$species]['Source'] == 2 && $value != 2) { 
                        $alts_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE PetID1 = '$species' OR PetID2 = '$species' OR PetID3 = '$species'");
                        while($this_alt = mysqli_fetch_object($alts_db)) {
                            $updatetags = "";
                            if (($all_pets[$this_alt->PetID1]['Source'] == 2 && $this_alt->PetID1 != $species) OR ($all_pets[$this_alt->PetID2]['Source'] == 2 && $this_alt->PetID2 != $species) OR ($all_pets[$this_alt->PetID3]['Source'] == 2 && $this_alt->PetID3 != $species)) {
                            }
                            else {
                                $updatetags[5] = 0;
                                update_tags($this_alt->id, $updatetags);
                                $updatetags = "";
                            }
                        }
                    }
                    
                    if ($value == 1) { // Add Shop Tag
                        $alts_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE PetID1 = '$species' OR PetID2 = '$species' OR PetID3 = '$species'");
                        $updatetags[6] = 1;
                        while($this_alt = mysqli_fetch_object($alts_db)) {
                            update_tags($this_alt->id, $updatetags);
                        }
                        $updatetags = "";
                    }
                    if ($value == 2) { // Add TCG Tag
                        $alts_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE PetID1 = '$species' OR PetID2 = '$species' OR PetID3 = '$species'");
                        $updatetags[5] = 1;
                        while($this_alt = mysqli_fetch_object($alts_db)) {
                            update_tags($this_alt->id, $updatetags);
                        }
                        $updatetags = "";
                    }
                    mysqli_query($dbcon, "UPDATE PetsUser SET `Source` = '$value' WHERE RematchID = '$species'");
                }
                break;
            case "obtainable":
                if ($value != "0" && $value != "1" && $value != "2") {
                    $upderror = TRUE;
                }
                else { // 0 = undecided, 1 = Yes, 2 = No
                    // Remove Unobtainable tag unless another pet is from shop
                    if ($all_pets[$species]['Obtainable'] == 2 && $value != 2) { 
                        $alts_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE PetID1 = '$species' OR PetID2 = '$species' OR PetID3 = '$species'");
                        while($this_alt = mysqli_fetch_object($alts_db)) {
                            $updatetags = "";
                            if (($all_pets[$this_alt->PetID1]['Obtainable'] == 2 && $this_alt->PetID1 != $species) OR ($all_pets[$this_alt->PetID2]['Obtainable'] == 2 && $this_alt->PetID2 != $species) OR ($all_pets[$this_alt->PetID3]['Obtainable'] == 2 && $this_alt->PetID3 != $species)) {
                            }
                            else {
                                $updatetags[18] = 0;
                                update_tags($this_alt->id, $updatetags);
                                $updatetags = "";
                            }
                        }
                    }
                    
                    if ($value == 2) { // Add Unobtainable Tag
                        $alts_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE PetID1 = '$species' OR PetID2 = '$species' OR PetID3 = '$species'");
                        $updatetags[18] = 1;
                        while($this_alt = mysqli_fetch_object($alts_db)) {
                            update_tags($this_alt->id, $updatetags);
                        }
                        $updatetags = "";
                    }
                    mysqli_query($dbcon, "UPDATE PetsUser SET `Obtainable` = '$value' WHERE RematchID = '$species'");
                }
                break;
            case "difficulty":
                if ($value != "0" && $value != "1" && $value != "2" && $value != "3" && $value != "4" && $value != "5" && $value != "6") {
                    $upderror = TRUE;
                }
                else {
                    mysqli_query($dbcon, "UPDATE PetsUser SET `Difficulty` = '$value' WHERE RematchID = '$species'");
                }
                break;
            case "unique":
                if ($value != "0" && $value != "1" && $value != "3") {
                    $upderror = TRUE;
                }
                else {
                    mysqli_query($dbcon, "UPDATE PetsUser SET `Unique` = '$value' WHERE RematchID = '$species'");
                }
                break;
            case "defrarity":
                if ($value != "0" && $value != "1" && $value != "2" && $value != "3" && $value != "4") {
                    $upderror = TRUE;
                }
                else {
                    mysqli_query($dbcon, "UPDATE PetsUser SET `DefRarity` = '$value' WHERE RematchID = '$species'");
                }
                break;
        }
    }
    else {
        $upderror = TRUE;   
    }
}
if ($upderror == TRUE) {
    echo "NOK";
}
else {
    echo "OK";
}

mysqli_close($dbcon); 

