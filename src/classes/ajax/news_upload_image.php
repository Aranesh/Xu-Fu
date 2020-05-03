<?php
include("../../data/dbconnect.php");
include("../../classes/functions.php");

    $image_id = $_POST['article_id'];

    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp'); // valid extensions
    
    if($_FILES['image']) {
        $img = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        
        if(in_array($ext, $valid_extensions)) {
            $picsizes = getimagesize($_FILES["image"]["tmp_name"]);
            if ($picsizes[0] == "") { ?>
                <script>
                    alert('There was a problem uploading the file, please try again.');
                </script>
                <? die;
            }
            if ($picsizes[0] < 600 OR $picsizes[1] < 100) { ?>
                <script>
                    alert('The image is too small. Please make sure it is at least 600 pixels in width and 100 pixels in height to avoid blurring. \nYour image is <? echo $picsizes[0]."x".$picsizes[1] ?>.');
                    $('#submit_img').hide(0);
                </script>
                <? die;
            }
            if ($picsizes[0]/$picsizes[1] < 1.6) { ?>
                <script>
                    alert('Title images need landscape format. Min width of 600px and min height of 100px is acceptable with an aspect ratio larger than 1.6. \nYour image is <? echo $picsizes[0]."x".$picsizes[1] ?> and aspect ratio <? echo $picsizes[0]/$picsizes[1] ?>. \nSorry for this, but the page would look weird with portrait images.');
                    $('#submit_img').hide(0);
                </script>
                <? die;
            }
            
            $tmp_path = "images/news/uploads/".rand(10,1000000).".".$ext;
            move_uploaded_file($tmp,"../../".$tmp_path);
            
            // Default width:
            $max_width = 840;
            $max_height = 500;
            $img_ratio = $picsizes[0] / $picsizes[1];

            // Deleting previous image if one existed
            $path = "images/news/".$image_id.".jpg";
            if (file_exists("../../".$path)) { 
                unlink("../../".$path) or die("Couldn't delete file");
            }
            
            // Create main image
            $img = resize_image("../../".$tmp_path, $ext, $max_width, $max_height);
            imagejpeg($img,"../../".$path);
            imagedestroy($img);

            
            // Deleting temporary stored file
            if (file_exists("../../".$tmp_path)) { 
                unlink("../../".$tmp_path) or die("Couldn't delete file");
            }
            
            ?>
            <script>
                $('#submit_img').hide(0);
                $('#remove_img_button').show(0);
                document.getElementById('title_image').src = "<? echo $path ?>?" + new Date().getTime();
            </script>
            <? 

        } 
        else {
            echo "<script>alert('There was a problem uploading the file, please try again.)</script>";
        }
    }
?>
