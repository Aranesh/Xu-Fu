<?php
include("../../data/dbconnect.php");
include("../functions.php");
require_once ('../Strategy.php');

// =================== FUNCTION Removes empty, unpublished strategies older than 14 days ===================

$strats_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Comment = '' AND PetID1 = 1 AND PetID2 = 1 AND PetID3 = 1 AND Created < DATE_SUB(NOW(), INTERVAL 14 DAY) ORDER BY id") OR die(mysqli_error($dbcon));
$user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = 2") OR die(mysqli_error($dbcon));
$user = mysqli_fetch_object($user_db);

while ($strat = mysqli_fetch_object($strats_db)){
    $strat_id = $strat->id;
    $steps_db = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = $strat_id") OR die(mysqli_error($dbcon));
        $steps_present = FALSE;
        while ($step = mysqli_fetch_object($steps_db)){
            if ($step->Instruction != "") {
                $steps_present = TRUE;
            }
        }
    if ($steps_present == FALSE) {
        echo $strat_id." deleted.<br>";
        \Strategy\delete ($strat_id, $user);
    }
}