<?
include("../../data/dbconnect.php");
include("../functions.php");

$pet = $_GET['pet'];
$slot = $_GET['slot'];
$language = $_GET['lng'];

if ($language == "de_DE") {
 $wowhdomain = "de";
}
else if ($language == "it_IT") {
 $wowhdomain = "it";
}
else if ($language == "es_ES") {
 $wowhdomain = "es";
}
else if ($language == "fr_FR") {
 $wowhdomain = "fr";
}
else if ($language == "pt_BR") {
 $wowhdomain = "pt";
}
else if ($language == "ru_RU") {
 $wowhdomain = "ru";
}
else if ($language == "pl_PL") {
 $wowhdomain = "en";
}
else if ($language == "ko_KR") {
 $wowhdomain = "ko";
}
else if ($language == "zh_TW") {
 $wowhdomain = "cn";
}
else if ($language == "en_US") {
 $wowhdomain = "en";
}

if ($pet == "0") {
 echo '<img style="vertical-align:middle; width: 25px; height: 25px" src="https://www.wow-petguide.com/images/pets/resize50/level.png">';  
}
if ($pet == "1") {
 echo '<img style="vertical-align:middle; width: 25px; height: 25px" src="https://www.wow-petguide.com/images/pets/resize50/any.png">';
}
if ($pet > "1") {
 $petdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID = '$pet'");
 
 if (mysqli_num_rows($petdb) < 1){
     $petimage = "https://www.wow-petguide.com/images/pets/resize50/unknown.png";
 }
 else {
     $pet = mysqli_fetch_object($petdb);
     $imagesrc = "https://www.wow-petguide.com/images/pets/resize50/".$pet->PetID.".png";
 
     if (false === @file_get_contents($imagesrc,0,null,0,1)) {
         $petimage = "https://www.wow-petguide.com/images/pets/resize50/unknown.png";
     }
     else {
         $petimage = $imagesrc;
     }
 }
 
 ?>
 <a href="http://<?php echo $wowhdomain ?>.wowhead.com/npc=<?php echo $pet->PetID ?>" target="_blank">
     <img style="vertical-align:middle; width: 25px; height: 25px" src="<?php echo $petimage ?>">
 </a>
 <?
}