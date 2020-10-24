<?
include("../../data/dbconnect.php");
$searchstring = $_GET['g'];
$userid = $_GET['u'];
$usersecret = $_GET['del'];
$page = $_GET['p'];

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
if (mysqli_num_rows($userdb) > "0") {
    $user = mysqli_fetch_object($userdb);
    if ($user->ComSecret == $usersecret) {
        $usercheck = "OK";
    }
}

if ($usercheck == "OK"){
    $imagesdb = mysqli_query($dbcon, "SELECT * FROM Images WHERE Category = '$searchstring'");

    if (mysqli_num_rows($imagesdb) < 1){
        echo '<p class="blogodd">No images found in this category.<br><br>';
    }
    else {

        if ($page == "a") {

            while ($thisimg = mysqli_fetch_object($imagesdb)) { 

                $imguserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$thisimg->User'");
                $imguser = mysqli_fetch_object($imguserdb);
                $outputuser = '<span class="username" rel="'.$imguser->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$imguser->id.'" class="bt_infobox">'.$imguser->Name.'</a></span>';

                ?>
                <div class="galleryitem">
                    <div style="width: 228px; height: 154px;">
                        <img class="tooltip_<?php echo $thisimg->id ?>" data-tooltip-content="#tooltip_content_<?php echo $thisimg->id ?>" src="https://www.wow-petguide.com/images/articles/<?php echo $thisimg->Filename ?>" style="max-width: 228; max-height: 145; cursor: pointer">
                    </div>

                    <div style="display: none">
                        <span id="tooltip_content_<?php echo $thisimg->id ?>">
                            <center>
                                <p class="smalleven"><i>(<?php echo $thisimg->Width ?>x<?php echo $thisimg->Height ?>)</i><br>
                                Uploaded by: <?php echo $outputuser ?><br>
                                <img src="https://www.wow-petguide.com/images/articles/<?php echo $thisimg->Filename ?>">
                            </center>
                        </span>
                    </div>

                    <script>
                        $(document).ready(function() {
                            $('.tooltip_<?php echo $thisimg->id ?>').tooltipster({
                                interactive: 'true'
                            });
                        });
                    </script>
                    
                    <div style="width: 228px; height: 45px;">

                        <?php if ($thisimg->Title != "") {
                           echo '<p class="smallodd"><i>'.$thisimg->Title.'</i><br>';    
                        }
                    
                        if ($user->id == $thisimg->User OR $user->Role == "99") { ?>

                        <a class="alternativessmall" style="color: black; font-weight: normal; text-decoration: underline" data-remodal-target="modaldelimg_<?php echo $thisimg->id ?>">Delete Image</a>

                        <div class="remodalcomments" data-remodal-id="modaldelimg_<?php echo $thisimg->id ?>">
                            <table width="300" class="profile">
                                <tr class="profile">
                                    <th colspan="2" width="5" class="profile">
                                        <table>
                                            <tr>
                                                <td><img src="https://www.wow-petguide.com/images/icon_x.png"></td>
                                                <td><img src="https://www.wow-petguide.com/images/blank.png" width="5" height="1"></td>
                                                <td><p class="blogodd"><b>Delete this image?</td>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>

                                <tr class="profile">
                                    <td class="collectionbordertwo"><center>
                                        <form enctype="multipart/form-data" action="?page=adm_images" method="POST">
                                        <input type="hidden" name="action" value="adm_delimage">
                                        <input type="hidden" name="subuser" value="<?php echo $user->id; ?>">
                                        <input type="hidden" name="delimiter" value="<?php echo $user->ComSecret ?>">
                                        <input type="hidden" name="selimage" value="<?php echo $thisimg->id ?>">
                                        <table>
                                            <tr>
                                                <td colspan="2">
                                                    <img src="https://www.wow-petguide.com/images/articles/<?php echo $thisimg->Filename ?>" style="max-width: 400; max-height: 300">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: right">

                                                        <input type="submit" class="comdelete" value="Delete">
                                                    </form>
                                                </td>
                                                <td style="padding-left: 15px;">
                                                    <input data-remodal-action="close" type="submit" class="comedit" value="Cancel">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <script>
                        var options = {
                            hashTracking: false
                        };
                        $('[data-remodal-id=modaldelimg_<?php echo $thisimg->id ?>]').remodal(options);
                        </script>

                    <?php } ?>
                    </div>
                </div>
                <style>
                    div.galleryitem {
                        width: 230;
                        height: 200;
                        padding: 2px;
                        margin: 5px;
                        float: left;
                        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ebe9f9+0,d8d0ef+50,cec7ec+51,c1bfea+100;Purple+3D+%231 */
                        background: #ebe9f9; /* Old browsers */
                        background: -moz-linear-gradient(top, #ebe9f9 0%, #d8d0ef 50%, #cec7ec 51%, #c1bfea 100%); /* FF3.6-15 */
                        background: -webkit-linear-gradient(top, #ebe9f9 0%,#d8d0ef 50%,#cec7ec 51%,#c1bfea 100%); /* Chrome10-25,Safari5.1-6 */
                        background: linear-gradient(to bottom, #ebe9f9 0%,#d8d0ef 50%,#cec7ec 51%,#c1bfea 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ebe9f9', endColorstr='#c1bfea',GradientType=0 ); /* IE6-9 */
                    }
                </style> <?
            }
        }
        if ($page == "e") {
            while ($thisimg = mysqli_fetch_object($imagesdb)) { 
                $imguserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$thisimg->User'");
                $imguser = mysqli_fetch_object($imguserdb);
                $outputuser = '<span class="username" rel="'.$imguser->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$imguser->id.'" class="bt_infobox">'.$imguser->Name.'</a></span>';
                ?>

                <div class="galleryitem">
                    <div style="width: 168px; height: 104px;">
                        <center><img data-imgid="<?php echo $thisimg->id ?>" class="galimg tooltip_<?php echo $thisimg->id ?>" data-tooltip-content="#tooltip_content_<?php echo $thisimg->id ?>" src="https://www.wow-petguide.com/images/articles/<?php echo $thisimg->Filename ?>" style="max-width: 168; max-height: 100; cursor: pointer">
                    </div>

                    <div style="display: none">
                        <span id="tooltip_content_<?php echo $thisimg->id ?>">
                            <center>
                                <p class="smalleven"><i>(<?php echo $thisimg->Width ?>x<?php echo $thisimg->Height ?>)</i><br>
                                Uploaded by: <?php echo $outputuser ?><br>
                                <img style="max-width: 750; max-height: 500" src="https://www.wow-petguide.com/images/articles/<?php echo $thisimg->Filename ?>">
                            </center>
                        </span>
                    </div>

                    <script>
                        $(document).ready(function() {
                            $('.tooltip_<?php echo $thisimg->id ?>').tooltipster({
                                interactive: 'false',
                            });
                        });
                    </script>
                    <center>
                    <?php if ($thisimg->Title != "") {
                        echo '<p class="smallodd"><i>'.$thisimg->Title.'</i><br>';    
                    } ?>
                </div>
                <style>
                    div.galleryitem {
                        width: 170;
                        height: 125;
                        padding: 2px;
                        margin: 5px;
                        float: left;
                        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ebe9f9+0,d8d0ef+50,cec7ec+51,c1bfea+100;Purple+3D+%231 */
                        background: #ebe9f9; /* Old browsers */
                        background: -moz-linear-gradient(top, #ebe9f9 0%, #d8d0ef 50%, #cec7ec 51%, #c1bfea 100%); /* FF3.6-15 */
                        background: -webkit-linear-gradient(top, #ebe9f9 0%,#d8d0ef 50%,#cec7ec 51%,#c1bfea 100%); /* Chrome10-25,Safari5.1-6 */
                        background: linear-gradient(to bottom, #ebe9f9 0%,#d8d0ef 50%,#cec7ec 51%,#c1bfea 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ebe9f9', endColorstr='#c1bfea',GradientType=0 ); /* IE6-9 */
                    }
                </style> <?
            }
        }
    }
}



