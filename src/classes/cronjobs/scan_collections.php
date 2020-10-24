<?php
include("../../data/dbconnect.php");
include("../functions.php");
ini_set ('display_errors', 0);

$all_pets = get_all_pets('Name');

// =================== This cronjob scans user collections for breeds of their pets and updates the pet database accordingly ===================

$users_db = mysqli_query($dbcon, "SELECT * FROM Users ORDER BY id") or die(mysqli_error($dbcon));
while($user = mysqli_fetch_object($users_db)) {
    $findcol = find_collection($user, 2);
    if ($findcol != "No Collection") {
        $fp = fopen($findcol['Path'], 'r');
        $collection = json_decode(fread($fp, filesize($findcol['Path'])), true);
            foreach ($collection as $key => $pet) {
                if ($all_pets[$pet['Species']][$pet['Breed']] != 1 && $all_pets[$pet['Species']]['Name'] != "") {
                    echo 'New breed found for '.$all_pets[$pet['Species']]['Name'].': '.$pet['Breed'].'<br>';
                    $update_breed = $pet['Breed'];
                    $species = $pet['Species'];
                    mysqli_query($dbcon, "UPDATE PetsUser SET `$update_breed` = '1' WHERE RematchID = '$species'") or die(mysqli_error($dbcon));
                }
            }
    }
}

echo "all done";