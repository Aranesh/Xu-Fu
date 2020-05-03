<?php
include("../../data/dbconnect.php");
include("../functions.php");
require_once ('../../data/blizzard_api.php'); 

// US Import of Realms
$realms = blizzard_api_realm_status('us', 'en_US', '../../');
if ($realms == "error") {
    echo "US Armory API could not be read.<br>";
}
else {
    $realmscombo = array();
    $realms = json_decode($realms, TRUE);
    foreach($realms['realms'] as $key => $value) {
        $realmscombo[]['comboname'] = $value['slug']."|".$value['name'];
    }
    sortBy('comboname', $realmscombo, 'asc');
    foreach($realmscombo as $key => $value) {
        $realmlist = $realmlist."*".$value['comboname'];
    }
    $realmlist = substr($realmlist, 1);
    $realmlist = mysqli_real_escape_string($dbcon, $realmlist);
    mysqli_query($dbcon, "UPDATE Realms SET `US` = '$realmlist' WHERE id = '1'") OR die(mysqli_error($dbcon));
    $realmlist = "";
    $realms = "";
    $realmscombo = "";
    echo "US realms updated successfully.<br>";
}



// EU Import of Realms
$realms = blizzard_api_realm_status('eu', 'en_US', '../../');
if ($realms == "error") {
    echo "EU Armory API could not be read.<br>";
}
else {
    $realms = json_decode($realms, TRUE);
    $realmscombo = array();
    foreach($realms['realms'] as $key => $value) {
        $realmscombo[]['comboname'] = $value['slug']."|".$value['name'];
    }
    sortBy('comboname', $realmscombo, 'asc');
    foreach($realmscombo as $key => $value) {
        $realmlist = $realmlist."*".$value['comboname'];
    }
    $realmlist = substr($realmlist, 1);
    $realmlist = mysqli_real_escape_string($dbcon, $realmlist);
    mysqli_query($dbcon, "UPDATE Realms SET `EU` = '$realmlist' WHERE id = '1'") OR die(mysqli_error($dbcon));
    $realmlist = "";
    $realms = "";
    $realmscombo = "";
    echo "EU realms updated successfully.<br>";
}





// TW Import of Realms
$realms = blizzard_api_realm_status('tw', 'zh_TW', '../../');
if ($realms == "error") {
    echo "TW Armory API could not be read.<br>";
}
else {
    $realms = json_decode($realms, TRUE);
    $realmscombo = array();
    foreach($realms['realms'] as $key => $value) {
        $realmscombo[]['comboname'] = $value['slug']."|".$value['name'];
    }
    sortBy('comboname', $realmscombo, 'asc');
    foreach($realmscombo as $key => $value) {
        $realmlist = $realmlist."*".$value['comboname'];
    }
    $realmlist = substr($realmlist, 1);
    $realmlist = mysqli_real_escape_string($dbcon, $realmlist);
    mysqli_query($dbcon, "UPDATE Realms SET `TW` = '$realmlist' WHERE id = '1'") OR die(mysqli_error($dbcon));
    $realmlist = "";
    $realms = "";
    $realmscombo = "";
    echo "TW realms updated successfully.<br>";
}



// KR Import of Realms
$realms = blizzard_api_realm_status('kr', 'ko_KR', '../../');
if ($realms == "error") {
    echo "KR Armory API could not be read.<br>";
}
else {
    $realms = json_decode($realms, TRUE);
    $realmscombo = array();
    foreach($realms['realms'] as $key => $value) {
        $realmscombo[]['comboname'] = $value['slug']."|".$value['name'];
    }
    sortBy('comboname', $realmscombo, 'asc');
    foreach($realmscombo as $key => $value) {
        $realmlist = $realmlist."*".$value['comboname'];
    }
    $realmlist = substr($realmlist, 1);
    $realmlist = mysqli_real_escape_string($dbcon, $realmlist);
    mysqli_query($dbcon, "UPDATE Realms SET `KR` = '$realmlist' WHERE id = '1'") OR die(mysqli_error($dbcon));
    $realmlist = "";
    $realms = "";
    $realmscombo = "";
    echo "KR realms updated successfully.<br>";
}

$updatetime = date('Y-m-d H:i:s');
mysqli_query($dbcon, "UPDATE Realms SET `Date` = '$updatetime' WHERE id = '1'") OR die(mysqli_error($dbcon));







