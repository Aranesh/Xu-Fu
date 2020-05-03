<?php

    $image_id = $_REQUEST['id'];
        
    // Deleting previous image if one existed
    $path = "images/news/".$image_id.".jpg";
    if (file_exists("../../".$path)) { 
        unlink("../../".$path) or die("NOK");
        echo "OK";
    }
    else {
        echo "NOK";
    }
?>
