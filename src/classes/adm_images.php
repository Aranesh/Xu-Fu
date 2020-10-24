<?php

$showupload = "1";
$action = $_POST['action'];
if ($action == "start_upload") {

    $image_cat = $_POST['image_cat'];
    $new_cat = $_POST['new_cat'];
    $uploadOk = 1;

    $imageFileType = strtolower(pathinfo(basename($_FILES["fileToUpload"]["name"]),PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image

    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        // File is an image
    } else {
        $uperror = '<li><p class="blogodd">File is not an image.</li>';
        $uploadOk = 0;
    }

    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $uperror = $uperror.'<li><p class="blogodd">File is not a JPG, JPEG or PNG image.</li>';
        $uploadOk = 0;
    }

    if ($_FILES["fileToUpload"]["size"] > 2000000) {
        $uperror = $uperror.'<li><p class="blogodd">Uploaded file is too large (limit is 2 MB).</li>';
        $uploadOk = 0;
    }

    if ($image_cat == "" && $new_cat == "") {
        $uperror = $uperror.'<li><p class="blogodd">No category was selected.</li>';
        $uploadOk = 0;
    }

    if ($image_cat) {
        $catdb = mysqli_query($dbcon, "SELECT * FROM ImageCats Where id = '$image_cat'");
        if (mysqli_num_rows($catdb) < "1") {
            $uperror = $uperror.'<li><p class="blogodd">There was an error with the category you selected. Please try again.</li>';
            $uploadOk = 0;
        }
        else {
            $selcat = mysqli_fetch_object($catdb);
            $showselcat = $selcat->Name;
            $createnewcat = "false";
        }
    }

    if ($new_cat) {
        $catdb = mysqli_query($dbcon, "SELECT * FROM ImageCats Where Name = '$new_cat'");
        if (mysqli_num_rows($catdb) > "0") {
            $selcat = mysqli_fetch_object($catdb);
            $showselcat = $selcat->Name;
            $createnewcat = "false";
        }
        else {
            if (preg_match('/[\'\"\\\]/', $new_cat)) {
                $uperror = $uperror.'<li><p class="blogodd">Please don\'t use apostrophes in the category name.</li>';
                $uploadOk = 0;
            }
            else {
              $showselcat = stripslashes(htmlentities($new_cat, ENT_QUOTES, "UTF-8"));
              $createnewcat = "true";
            }
        }
    }



    if ($uploadOk == "1") {
        $newimg_title = htmlentities($_POST['img_name'], ENT_QUOTES, "UTF-8");
        $tempnb = mt_rand(100000000, 999999999);
        $fn = $_FILES['fileToUpload']['tmp_name'];
        $size = getimagesize( $fn );

        // Processing of JPG Images
        if($imageFileType == "jpg" OR $imageFileType == "jpeg") {      // 0 = breite
            $src = imagecreatefromstring(file_get_contents($fn));
            $dst = imagecreatetruecolor( $size[0], $size[1] );
            imagecopy($dst, $src, 0, 0, 0, 0, $size[0], $size[1] );
            $temp_save = "images/uploads/".$user->id."_".$tempnb.".jpg";
            if (imagejpeg($dst, $temp_save, 90)) {
                $showupload = "0";
            } else {
                $uploadOk = 0;
                $uperror = $uperror.'<li><p class="blogodd">Sorry, there was an error uploading your file.</li>';
            }
            imagedestroy($src);
            imagedestroy($dst);
        }

        // Create image from PNG
        if($imageFileType == "png") {
            $image = imagecreatefrompng($fn);
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, TRUE);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            $temp_save = "images/uploads/".$user->id."_".$tempnb.".jpg";
            if (imagejpeg($bg, $temp_save, 90)) {
                $showupload = "0";
            } else {
                $uploadOk = 0;
                $uperror = $uperror.'<li><p class="blogodd">Sorry, there was an error uploading your file.</li>';
            }
            imagedestroy($bg);
            imagedestroy(imagecopy);
        }
    }
}



if ($action == "resizereview") {
    $newimg_title = htmlentities($_POST['img_name'], ENT_QUOTES, "UTF-8");
    $target_height = $_POST['image_height'];
    $target_width = $_POST['image_width'];
    $selcat = $_POST['selcat'];
    $fn = $_POST['file'];

    if (!preg_match("/^[1234567890]*$/is", $target_height)) {
            $target_height = "";
        }
        if (!preg_match("/^[1234567890]*$/is", $target_width)) {
            $target_width = "";
    }

    $catdb = mysqli_query($dbcon, "SELECT * FROM ImageCats WHERE Name = '$selcat'");
    if (mysqli_num_rows($catdb) < "1") {
        $showselcat = htmlentities($selcat, ENT_QUOTES, "UTF-8");
        $insertcat = mysqli_real_escape_string($dbcon, $selcat);
        $createnewcat = "true";
    }
    else {
        $selcat = mysqli_fetch_object($catdb);
        $showselcat = $selcat->Name;
    }

    // Resize required - calculate new width/height, resize, then move file
    if ($target_height != "" OR $target_width != "") {
        $src = "images/uploads/".$fn.".jpg";
        $size = getimagesize( $src );
        if ($target_width > $size[0] OR $target_height > $size[1]) {
            $action = "start_upload";
            $uploadOk = "1";
            $noenlarge = "1";
            $temp_save = $src;
            $temp_output = $fn;
            $showupload = "0";
            $resizeerror = "1";
        }
        else {
            $ratio = $size[0]/$size[1]; // width/height

            if (!$target_width) {
                $target_width = $size[0];
            }
            if (!$target_height) {
                $target_height = $size[1];
            }

            if ($target_width && $target_height) {
                if ($size[0] > $size[1]) {
                    if($size[0] < $target_width)
                        $new_width = $size[0];
                    else
                    $new_width = $target_width;

                    $divisor = $size[0] / $new_width;
                    $new_height = floor( $size[1] / $divisor);
                }
                else {
                     if($size[1] < $target_height)
                         $new_height = $size[1];
                     else
                         $new_height =  $target_height;

                    $divisor = $size[1] / $new_height;
                    $new_width = floor( $size[0] / $divisor );
                }
            }

            // Resample
            $image_p = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($src);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $size[0], $size[1]);

            // Output
            unlink($src);
            imagejpeg($image_p, $src, 90);

            $action = "start_upload";
            $uploadOk = "1";
            $temp_save = $src;
            $temp_output = $fn;
            $showupload = "0";
            $resizeerror = "1";
            $size = getimagesize( $src );
        }
    }


    if ($resizeerror != "1") {

        // Create new category if necessary
        if ($createnewcat == "true") {
            mysqli_query($dbcon, "INSERT INTO ImageCats (`Name`, `CreatedBy`) VALUES ('$insertcat', '$user->id')");
            $newcatid = mysqli_insert_id($dbcon);
            $catdb = mysqli_query($dbcon, "SELECT * FROM ImageCats WHERE id = '$newcatid'");
            $selcat = mysqli_fetch_object($catdb);
        }

        // Rename and move picture
        if ($target_height == "" && $target_width == "") {
            $tempnb = mt_rand(100000000, 999999999);
            $src = "images/uploads/".$fn.".jpg";
            $tempdst = $selcat->id."_".$user->id."_".$tempnb.".jpg";
            $dst = "images/articles/".$tempdst;
            rename($src, $dst);
        }

        // Add new image to database
        $size = getimagesize( $dst );
        $newimg_title = $_POST['img_name'];
        if ($newimg_title) {
            $newimg_title = stripslashes($newimg_title);
            $newimg_title = htmlentities($newimg_title, ENT_QUOTES, "UTF-8");
        }

        mysqli_query($dbcon, "INSERT INTO Images (`Category`, `Title`, `User`, `Filename`, `Width`, `Height`) VALUES ('$selcat->id', '$newimg_title', '$user->id', '$tempdst', '$size[0]', '$size[1]')");
        $newimgid = mysqli_insert_id($dbcon);

        // Add user protocol entry:
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Image Uploaded', '$newimgid')") OR die(mysqli_error($dbcon));
        $finalized = "1";
        $showcat = $selcat->id;
    }
}

if ($action == "adm_delimage") {
    $subuser = $_POST['subuser'];
    $usersecret = $_POST['delimiter'];
    if ($subuser == $user->id && $usersecret == $user->ComSecret) {
        $delimage = $_POST['selimage'];
        $imagesdb = mysqli_query($dbcon, "SELECT * FROM Images WHERE id = '$delimage'");

        if (mysqli_num_rows($imagesdb) < 1){
            $delerror = "1";
        }
        else {
            $delthisimg = mysqli_fetch_object($imagesdb);
            if ($delthisimg->User == $user->id OR $user->Role == "99") {
                $showcat = $delthisimg->Category;
                $fpath = "images/articles/".$delthisimg->Filename;
                unlink($fpath);
                mysqli_query($dbcon, "DELETE FROM Images WHERE id = '$delimage'");
                mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Image deleted', '$delimage')") OR die(mysqli_error($dbcon));
                $imgdeleted = "1";
            }
        }
    }
}






// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================




?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <?php echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle">Administration - Article Images</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('admin');
    ?>
</div>




<div class="blogentryfirst">
<div class="articlebottom">
</div>

<table style="width: 100%;">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>
            <td>




<table style="width: 100%;">
<tr>
<td>
<table style="width: 85%;" class="profile">

    <?php print_admin_menu('adm_images'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">
            <br>
            <?php // error handling
            if ($finalized == "1") {
                echo '<p class="blogodd"><b>File uploaded successfully!</b><br>';
                echo '<br><hr class="home"><br>';
            }

            if ($action == "start_upload" && $uploadOk == "0") {
                echo '<p class="blogodd">There was a problem with your upload. The file could not be processed. Problem(s) detected:<br><br>';
                echo $uperror;
                echo '<br><hr class="home"><br>';
                $showupload = "1";
            }

            if ($delerror == "1") {
                echo '<p class="blogodd">Image could not be deleted due to some problem. Please try again or contact Aranesh if it persists.<br>';
                $showupload = "1";
                echo '<br><hr class="home"><br>';
            }

            if ($imgdeleted == "1") {
                echo '<p class="blogodd">Image deleted.<br>';
                $showupload = "1";
                echo '<br><hr class="home"><br>';
            }


            // Second step of upload process, show image and chosen details to double check, warnings etc.

            if ($action == "start_upload" && $uploadOk == "1") {
            ?>
            <p class="blogodd"><b>Step 2: Review your submission</b><br>
            <?php if ($noenlarge == "1") {
                echo "<font color='red'>Error: Images cannot be made larger than the original file. This would result in bad image quality.<br>";
                echo "If you want to resize the image, please enter values lower than the original ones</font>";
            } ?>
            <br>

            <br>
            <center>
                <?php if ($size[0] >= "600") {
                    $imgclass = "blogcenter";
                }
                else {
                    $imgclass = "blogeven";
                } ?>
            <img class="<?php echo $imgclass ?>" src="<?php echo $temp_save ?>" alt="" />

            <br>

            <form enctype="multipart/form-data" action="?page=adm_images" method="POST">
            <table>
                <tr>
                    <td style="text-align: right; padding-right: 8px; white-space: nowrap;">
                         <p class="smallodd"><b>Current:</p>
                    </td>
                    <td>
                        <p class="smallodd">Width:</b> <?php echo $size[0]; ?></p>
                    </td>
                    <td>
                        <p class="smallodd">Height:</b> <?php echo $size[1]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; white-space: nowrap;">
                        <p class="smallodd"><b>Resize to:</p>
                    </td>
                    <td>
                        <input class="petselect" style="width: 100; font-weight: normal" type="field" name="image_width" value="">
                    </td>
                    <td>
                        <input class="petselect" style="width: 100; font-weight: normal" type="field" name="image_height" value="">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2">
                        <p class="smallodd">Enter one or both values to resize. <br>Leave blank to not resize.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 10px; text-align: right; padding-right: 8px; white-space: nowrap;">
                        <p class="smallodd"><b>Title:
                    </td>
                    <td colspan="2" style="padding-top: 10px">
                        <input class="petselect" style="width: 250; font-weight: normal" type="field" name="img_name" value="<?php echo $newimg_title ?>">
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 10px; text-align: right; padding-right: 8px; white-space: nowrap;">
                        <p class="smallodd"><b>Category:
                    </td>
                    <td colspan="2" style="padding-top: 10px">
                        <p class="smallodd"><?php echo $showselcat; ?> <?php if ($createnewcat == "true") { echo "(will be created new)"; } ?>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td style="padding-top: 20px">
                        <?
                        if (!$temp_output) {
                            $temp_output = $user->id."_".$tempnb;
                        } ?>
                        <input type="hidden" name="action" value="resizereview">
                        <input type="hidden" name="file" value="<?php echo $temp_output; ?>">
                        <input type="hidden" name="selcat" value="<?php echo $showselcat ?>">
                        <center>
                        <input class="comedit" type="submit" name="change" value="Next Step">
                        </form>
                    </td>
                    <form enctype="multipart/form-data" action="?page=adm_images" method="POST">
                    <td style="padding-top: 20px">
                        <center>
                        <input class="comdelete" type="submit" name="change" value="Cancel">
                        </form>
                    </td>
                </tr>

            </table>

            <?
            }







            if ($showupload == "1") { // Normal submission page
            ?>

            <center>
            <table style="width: 80%">
                <tr>
                    <td><p class="blogodd">Select a category:</p>
                        <select width="230" data-placeholder="" name="image_cat" id="sel_cat" class="chosen-select" required>
                            <option value=""></option>
                                <?
                                $allcatsdb = mysqli_query($dbcon, "SELECT * FROM ImageCats ORDER BY Name") or die(mysqli_error($dbcon));
                                $catcounter = "0";
                                while ($thiscat = mysqli_fetch_object($allcatsdb)) {
                                    echo '<option value="'.$thiscat->id.'"';
                                    if ($showcat == $thiscat->id OR ($showcat == "" && $catcounter == "0")) {
                                        echo " selected";
                                    }
                                    echo '>'.$thiscat->Name.'</option>';
                                    $catcounter++;
                                }
                                ?>
                        </select>
                    </td>

                    <td>
                        <a class="alternativessmall" style="color: black" data-remodal-target="upload_pic" style="display:block"><button class="bnetlogin">Upload new picture</button></a>
                    </td>
                </tr>
            </table>



            <br>

            <div id="gallerycontainer"></div>


            <script>
                $("#sel_cat").chosen({width: 250, placeholder_text_single: 'Select a Category'});

                function adm_pullgallery(i) {
                    $('#gallerycontainer').empty();
                    $('#gallerycontainer').load('classes/ajax/adm_pullimages.php?g='+i+'&u=<?php echo $user->id ?>&del=<?php echo $user->ComSecret ?>&p=a');
                }

                $(".chosen-select").chosen().change(function(event){
                    var i = $('select[name=image_cat]').val();
                    adm_pullgallery(i);
                });

                var u = $('select[name=image_cat]').val();
                if (u != "") {
                    adm_pullgallery(u);
                }
            </script>


            <div class="remodalcomments" data-remodal-id="upload_pic">
                <table width="500" class="profile">

                    <tr class="profile">
                        <td class="collectionbordertwo">
                            <p class="blogodd">Accepted file formats: JPG, JPEG and PNG. <br>
                            PNG images will be converted to JPG (white background instead of transparency).<br>
                            Maximum file size: 2 MB.<br>
                            <br>
                                <form enctype="multipart/form-data" action="?page=adm_images" method="POST">
                                <input type="hidden" name="action" value="start_upload">
                                <center>
                                <table>
                                    <tr>
                                        <td colspan="2">
                                            <input style="border: 1px solid #ccc; display: inline-block;  padding: 6px 12px; cursor: pointer" name="fileToUpload" type="file" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 10px">
                                            <p class="smallodd">Title (optional):</p>
                                        </td>
                                        <td style="padding-top: 10px">
                                            <input class="petselect" style="width: 250; font-weight: normal" type="field" name="img_name">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 10px">
                                            <p class="smallodd">Add to category:</p>
                                        </td>
                                        <td>
                                            <select width="230" data-placeholder="" name="image_cat" id="image_cat" class="chosen-select" required>
                                                <option value=""></option>
                                                    <?
                                                    $allcatsdb = mysqli_query($dbcon, "SELECT * FROM ImageCats ORDER BY Name") or die(mysqli_error($dbcon));
                                                    while ($thiscat = mysqli_fetch_object($allcatsdb)) {
                                                        echo '<option value="'.$thiscat->id.'">'.$thiscat->Name.'</option>';
                                                    }
                                                    ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="smallodd">Or create new category:</p>
                                        </td>
                                        <td>
                                            <input onchange="addCat()" class="petselect" style="width: 250; font-weight: normal" type="field" name="new_cat" id="add_new_cat" value="" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="padding-top: 40px">
                                            <center>
                                             <button class="bnetlogin" type="submit" name="change">Upload & Review Image</button>
                                        </td>
                                    </tr>

                                </table>
                                </center>

                                <script>
                                    $(".chosen-select").chosen({width: 250, allow_single_deselect: true, placeholder_text_single: 'Select a Category' });
                                    function addCat() {
                                        if (document.getElementById("add_new_cat").value != "") {
                                            $('#image_cat').find($('option')).attr('selected',false)
                                            $('.chosen-select').trigger('chosen:updated');
                                            document.getElementById("image_cat").required = false;
                                            document.getElementById("add_new_cat").required = true;
                                        }
                                        else {
                                            document.getElementById("image_cat").required = true;
                                            document.getElementById("add_new_cat").required = false;
                                        }
                                    }
                                    $(".chosen-select").chosen().change(function(event){
                                        var e = document.getElementById("image_cat");
                                        var selCat = e.options[e.selectedIndex].value;
                                        if(selCat != "") {
                                            $('#add_new_cat').val('');
                                            document.getElementById("image_cat").required = true;
                                            document.getElementById("add_new_cat").required = false;
                                        }
                                        else {
                                            document.getElementById("image_cat").required = false;
                                            document.getElementById("add_new_cat").required = true;
                                        }
                                    });
                                </script>

                                <br>
                        </td>
                    </tr>
                </table>
            </div>

            <script>
            var options = {
                hashTracking: false
            };
            $('[data-remodal-id=upload_pic]').remodal(options);
            </script>



            <?php } ?>
        </td>
    </tr>

</table>

</table>







</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
mysqli_close($dbcon);
echo "</body>";
die;