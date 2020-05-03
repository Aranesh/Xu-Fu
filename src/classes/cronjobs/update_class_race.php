<?php
include("../../data/dbconnect.php");
include("../functions.php");
require_once ('../../data/blizzard_api.php');
// =================== FUNCTION REFRESHES USERS PET COLLECTION AUTOMATICALLY ===================


// Import Races

$racesdb = mysqli_query($dbcon, "SELECT * FROM Character_Races") or die(mysqli_error($dbcon));
while($race = mysqli_fetch_object($racesdb)) {
    $all_races[$race->id]['ID'] = $race->id;
}

$races = blizzard_api_races('us', '../../');

$races = json_decode($races, TRUE);
foreach ($races['races'] as $race) {
    if (!$all_races[$race['id']]['ID']) {
        mysqli_query($dbcon, "INSERT INTO Character_Races (`id`) VALUES ('$race[id]')") OR die(mysqli_error($dbcon));
    }
    foreach (['en_US', 'de_DE', 'fr_FR', 'it_IT', 'es_ES', 'pl_PL', 'pt_PT', 'ru_RU', 'es_MX', 'pt_BR', 'ko_KR', 'zh_TW'] as $locale) {
        $entryname = mysqli_real_escape_string($dbcon, $race['name'][$locale]);
        mysqli_query($dbcon, "UPDATE Character_Races SET `Name_$locale` = '$entryname' WHERE id = '$race[id]'") or die(mysqli_error($dbcon));
    }
}
echo "Races imported<br>";




// Import Classes

$classesdb = mysqli_query($dbcon, "SELECT * FROM Character_Classes") or die(mysqli_error($dbcon));
while($class = mysqli_fetch_object($classesdb)) {
    $all_classes[$class->id]['ID'] = $class->id;
}

$classes = blizzard_api_classes('us', '../../');
$classes = json_decode($classes, TRUE);

foreach ($classes['classes'] as $class) {
    if (!$all_classes[$class['id']]['ID']) {
        mysqli_query($dbcon, "INSERT INTO Character_Classes (`id`) VALUES ('$class[id]')") OR die(mysqli_error($dbcon));
    }
    foreach (['en_US', 'de_DE', 'fr_FR', 'it_IT', 'es_ES', 'pl_PL', 'pt_PT', 'ru_RU', 'es_MX', 'pt_BR', 'ko_KR', 'zh_TW'] as $locale) {
        $entryname = mysqli_real_escape_string($dbcon, $class['name'][$locale]);
        mysqli_query($dbcon, "UPDATE Character_Classes SET `Name_$locale` = '$entryname' WHERE id = '$class[id]'") or die(mysqli_error($dbcon));
    }
}
echo "Classes imported<br>";