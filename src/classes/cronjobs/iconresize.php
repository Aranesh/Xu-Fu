<?php
include("../../data/dbconnect.php");
include("../functions.php");

// =================== RESIZE ICONS TO 50x50 ===================

$picdesheight="50";
$picdeswidth="50";

$dirrand = "../../images/pets";
$dhrand = opendir($dirrand);

while (false !== ($filename = readdir($dhrand))) {
    $filesplits = explode(".",$filename);
    if ($filesplits[1] == "png"){

        $iconlink = "../../images/pets/".$filesplits[0].".png";
        $iconsmallink = "../../images/pets/resize50/".$filesplits[0].".png";

        if(!file_exists($iconsmallink)){

            $src_img=imagecreatefrompng($iconlink);

            $realw=imageSX($src_img);
            $realh=imageSY($src_img);

            $dst_img=ImageCreateTrueColor($picdeswidth,$picdesheight);
            imagecopyresampled($dst_img,$src_img,0,0,0,0,$picdeswidth,$picdesheight,$realw,$realh);

            $target = "../../images/pets/resize50/".$filesplits[0].".png";
            imagepng($dst_img,$target);
            imagedestroy($dst_img);
            imagedestroy($src_img);

            echo "Icon ".$filesplits[0]." resized and saved. <img src=\"".$iconsmallink."\"><br>";
        }
    }
}

echo "<br><br>All Icons checked and resized if required.";


