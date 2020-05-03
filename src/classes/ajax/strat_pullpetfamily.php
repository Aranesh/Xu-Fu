<?
include("../../data/dbconnect.php");
include("../functions.php");

$pet = $_GET['pet'];
$slot = $_GET['slot'];

if ($pet == "0") {
    $petimage = "https://www.wow-petguide.com/images/bt_edit_Any.png";    
}
if ($pet == "1") {
    $petimage = "https://www.wow-petguide.com/images/bt_edit_Level.png";
}
if ($pet > "1") {
    $petdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID = '$pet'");
    
    if (mysqli_num_rows($petdb) < 1){
        $petimage = "https://www.wow-petguide.com/images/bt_edit_Any.png";
    }
    else {
        $pet = mysqli_fetch_object($petdb);
        $imagesrc = "https://www.wow-petguide.com/images/bt_edit_".$pet->Family.".png";
    
        if (false === @file_get_contents($imagesrc,0,null,0,1)) {
            $petimage = "https://www.wow-petguide.com/images/bt_edit_Any.png";
        }
        else {
            $petimage = $imagesrc;
        }
    }
}

?>
<img style="vertical-align:middle;" src="<? echo $petimage ?>">