<?php
// Save changes to article:

$action = $_POST['action'];

if ($action == "save" && $userrights['EditNews'] == "yes" && $news_article) {
    $articlecontent_en_US = mysqli_real_escape_string($dbcon, $_POST['article_content_en_US']);
    $articletitle_en_US = mysqli_real_escape_string($dbcon, $_POST['article_title_en_US']);
    $article_new_cat = $_POST['news_cat_select'];
    $publish_status = $_POST['publish_status'];
    $news_type_select = $_POST['news_type_select'];
    $toc_select = $_POST['toc'];
    $sticky_select = $_POST['sticky'];
    
    mysqli_query($dbcon, "UPDATE News_Articles SET `Active` = '$publish_status', `TOC` = '$toc_select', `Sticky` = '$sticky_select', `Main` = '$news_type_select', `Category` = '$article_new_cat', `Content_en_US` = '$articlecontent_en_US', `Title_en_US` = '$articletitle_en_US' WHERE id = '$news_article->id'");

    // Update article info for output:
    $news_article_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE id = $news_article_id");  
    $news_article = mysqli_fetch_object($news_article_db);
}



// Grab and process article details for output
$publishtitle = stripslashes(htmlentities($news_article->Title_en_US, ENT_QUOTES, "UTF-8"));
$publisharticle = stripslashes(htmlentities($news_article->Content_en_US, ENT_QUOTES, "UTF-8"));
$editarticle = $publisharticle;

$toc = \BBCode\process_news_full($publisharticle);
$publisharticle = $toc['article'];
unset($toc['article']);

$article_user_text = "";
$article_user_db = mysqli_query($dbcon, "SELECT Name, id FROM Users WHERE id = '$news_article->CreatedBy'");
if (mysqli_num_rows($article_user_db) > "0") {
    $article_user = mysqli_fetch_object($article_user_db);
    $article_user_text = ' - BY <span style="text-decoration: none" class="username" rel="'.$article_user->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$article_user->id.'" class="creatorlink"><b>'.strtoupper($article_user->Name).'</a></span>';  
}
$article_category = strtoupper($news_categories[$news_article->Category]['Name']);

if (!$article_category) {
    $article_category = "PET NEWS";               
}
$article_category_id = $news_article->Category;
if ($article_category_id == 0) {
    $article_category_id = 2;
}
if (file_exists('images/news/'.$news_article->id.'.jpg')) {
    $article_image = 'images/news/'.$news_article->id.'.jpg';
    $show_remove_button = 1;
}
else {
    $article_image = 'images/news/news_default.jpg';
}


?>

<div class="blogtitle" id="pagestart">
    <table width="100%" margin="0" cellpadding="0" cellspacing="0">
        <tr>
        <td><img src="https://www.wow-petguide.com/images/main_bg02_1.png"></td>
        <td width="100%"><center><h class="megatitle"><?php echo $publishtitle; ?></h><br><a title="Pet News from Xu-Fu's Pet Guides" rel="nofollow, noindex" class="growl" style="text-decoration: none" href="http://wow-petguide.com/rss_feed.php" target="_blank"><?php echo __("Subscribe via RSS") ?></a></td></td>
        <td><img src="https://www.wow-petguide.com/images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>



<div class="blogentryfirst">
    <div class="articlebottom"></div>

    <?
    // Displaying the side menu for specific articles
    if ($news_article->TOC == 1 && $toc) { ?>

    <div class="remodal-bg art_qi_right" style="max-width:400px">
        <center>
        <table class="profile" style="width: 100%">
            <tr class="profile">
                <th class="profile" style="padding-top: 2px; padding-bottom: 2px">
                    <p class="smallodd"><b>Quick Navigation:
                </th>
            </tr>
            <tr class="profile">
                <td class="profile">
                    <table>
                        <tr>
                            <td style="vertical-align:top; padding-top: 16px">
                                <img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_art_toc.png" alt="" />
                            </td>
                            <td style="vertical-align:top; padding-top: 16px">
                                <?
                                    echo '<p class="smallodd"><b>Table of Contents:</b><br><br>';
                                    foreach ($toc as $key => $value) {
                                        switch ($value['type']) {
                                            case "1":
                                                echo '<a class="articles_toc_major" onclick="scrollto(\''.$value['anchor'].'\')">'.$value['title'].'</a><br>';
                                                break;
                                            case "2":
                                                echo '<a class="articles_toc_minor" onclick="scrollto(\''.$value['anchor'].'\')">'.$value['title'].'</a><br>';
                                                break;
                                            case "3":
                                                echo '<a class="articles_toc_smallest h3_bullet" onclick="scrollto(\''.$value['anchor'].'\')">'.$value['title'].'</a><br>';
                                                break;
                                        }
                                    }
                                ?>
                                <br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
         </table>
    </div>
<?php } ?>

    <center>
        <div class="news_main_wrapper" <?php if ($news_article->Active == 0) { echo 'style="background-color: #e79e9e; margin-top: 10px"'; } ?>>
       
            <div class="news_content_block" style="border-top: 9px solid #<?php echo $news_categories[$news_article->Category]['Color']; ?>;">
                <div class="news_main_inner">
                    <?php if ($userrights['EditNews'] == "yes") { ?>
                    <div class="news_edit">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="100px">
                                <a class="alternativessmall" data-remodal-target="edit_article" style="display:inline">Edit Article</a>
                            </td>
                            <td width="125px" >
                                <form style="display:inline" class="upload_form" id="edit_image" action="ajaxupload.php" method="post" enctype="multipart/form-data">
                                    <input type="file" id="upload_img" accept="image/*" name="image" class="file_upload">
                                    <label for="upload_img"></label>
                                    <input type="hidden" name="article_id" value="<?php echo $news_article->id; ?>">
                            </td>
                            <td width="80px">
                                    <input id="submit_img" type="submit" value="Upload" style="display: none" class="submit_button">
                                </form> 
                            </td>
                            <td width="150px">
                                <a class="alternativessmall" href="#" id="remove_img_button" onclick="remove_image(<?php echo $news_article->id; ?>)" style="display:<?php if ($show_remove_button != 1) { echo "none"; } else echo "inline"; ?>">Remove Image</a>
                            </td>
                            <td width="150px">
                                <a class="alternativessmall" href="#" id="remove_img_button" onclick="delete_news_article(<?php echo $news_article->id; ?>)">Delete Article</a>
                            </td>
                        </tr>
                    </table>
                    </div>
                    <?php } ?>
                    <div class="news_title"><?php echo $publishtitle; ?></div>
                    <div class="news_date"><span name="time"><?php echo $news_article->CreatedTime; ?></span></div>
                    <div class="news_subtitle"><?php echo $article_category.$article_user_text ?></div>
                    <img class="news_title" id="title_image" src="<?php echo $article_image ?>">
                    <div class="news_content"><?php echo $publisharticle ?></div>
                </div>
            </div>
        
            <div class="social_media_buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A//www.wow-petguide.com/?News=<?php echo $news_article->id ?>" target="_blank"><img class="soc_m_share socm_fb" src="images/news_share_facebook.png"></a>
                <a href="https://twitter.com/intent/tweet?text=https%3A//www.wow-petguide.com/?News=<?php echo $news_article->id ?>" target="_blank"><img class="soc_m_share" src="images/news_share_twitter.png"></a>
                <a href="https://pinterest.com/pin/create/button/?url=https%3A//www.wow-petguide.com/?News=<?php echo $news_article->id ?>&media=<?php echo "https://wow-petguide.com/".$article_image ?>&description=News from Xu-Fu's Pet Guides: <?php echo $publishtitle; ?>" target="_blank"><img class="soc_m_share socm_pint" src="images/news_share_pinterest.png"></a>
            </div>
        </div>
        <div id="upload_output"></div>
        
    <br><br>
    </center>







<?php if ($userrights['EditNews'] == "yes") { ?>
<script>
    $(document).ready(function (e) {
     $(".upload_form").on('submit',(function(e) {
      e.preventDefault();
        var r = confirm("Are you sure you want to replace the current picture?");
        if (r == true) {
        $.ajax({
        url: "classes/ajax/news_upload_image.php",
       type: "POST",
       data:  new FormData(this),
       contentType: false,
             cache: false,
       processData:false,
       beforeSend : function()
       {
        // alert('sending now');
       },
       success: function(data) {
        // alert(data);
         $("#upload_output").html(data).fadeIn();
         $("#edit_image")[0].reset(); 
        },
         error: function(e) 
          {
            alert('There was a problem with the upload');
          }          
        });
       }
     }));
    });
    
    $('.upload_form').on('click touchstart' , function(){
        $(this).val('');
    });
    
    $(".upload_form").change(function() {
       $('#submit_img').show(100);
    });

</script>
<?php } ?>



<?php // EDITING MODAL BELOW
if ($userrights['EditNews'] == "yes") { ?>
    
    <div class="remodal_articles" data-remodal-id="edit_article">
    
        <table style="position: sticky; top:1px; z-index: 453599" class="profile">
            <tr class="profile"> <?
                
            \BBCode\bboptions_simple('news');
            \BBCode\bboptions_spacer();
            \BBCode\bboptions_advanced('news');
            \BBCode\bboptions_spacer();
            \BBCode\bboptions_tables('news');
            \BBCode\bboptions_spacer();
            echo '<td><p class="blogodd">Add:</p></td>';
            \BBCode\bboptions_url('news');
            \BBCode\bboptions_pet('news');
            \BBCode\bboptions_ability('news');
            \BBCode\bboptions_image('news');
            \BBCode\bboptions_user('news');
           ?>
            </tr>
        </table>
    
        <table width="100%" class="profile">
            
        <tr class="profile">
            <td class="collectionbordertwo" <?php if ($news_article->Active == 0) { echo 'style="background-color: #e79e9e"'; } ?>>
       
            <form method="post" style="display: inline">
            <input type="hidden" name="action" value="save_article">
       
                <table>
                    <tr>
                        
                        <td>
                            <p class="blogodd" style="font-size: 14px">Type:</p>
                        </td>

                        <td>
                            <select name="news_type_select" class="petselect" required>
                                <option class="petselect" value="1" <?php if ($news_article->Main == 1) { echo " selected"; } ?>>Large News</option>
                                <option class="petselect" value="0" <?php if ($news_article->Main == 0) { echo " selected"; } ?>>Small News</option>
                            </select>
                        </td>
                        
                        <td style="width: 1px; background-color: #757575">
                        </td> 
                        
                        <td>
                            <p class="blogodd" style="font-size: 14px">Category:</p>
                        </td>

                        <td>
                            <select name="news_cat_select" class="petselect" required>
                                <?
                                foreach ($news_categories as $thiscat) {
                                    echo '<option class="petselect" value="'.$thiscat['ID'].'"';
                                    if ($article_category_id == $thiscat['ID']) {
                                        echo " selected";
                                    }
                                    echo '>'.$thiscat['Name'].'</option>';
                                }
                                ?>
                            </select>
                        </td>
                        
                        <td style="width: 1px; background-color: #757575"></td>


                        <td>
                            <p class="blogodd" style="font-size: 14px">TOC:</p>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Yes:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="tocyes" value="1" name="toc" <?php if ($news_article->TOC == 1) { echo "checked"; } ?>>
                                    <label for="tocyes"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">No:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="tocno" value="0" name="toc" <?php if ($news_article->TOC == 0) { echo "checked"; } ?>>
                                    <label for="tocno"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>
                        
                        
                        <td style="width: 1px; background-color: #757575"></td>


                        <td>
                            <p class="blogodd" style="font-size: 14px">Sticky:</p>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Yes:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="stickyyes" value="1" name="sticky" <?php if ($news_article->Sticky == 1) { echo "checked"; } ?>>
                                    <label for="stickyyes"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">No:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="stickyno" value="0" name="sticky" <?php if ($news_article->Sticky == 0) { echo "checked"; } ?>>
                                    <label for="stickyno"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>
                        
                        
                        <td style="width: 1px; background-color: #757575"></td>




                        <td style="padding: 0px 10px 0px 10px">
                            <p class="blogodd" style="font-size: 14px">Published:</p>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Yes:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="locyes" value="1" name="publish_status" <?php if ($news_article->Active == "1") { echo "checked"; } ?>>
                                    <label for="locyes"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">No:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="red" type="radio" id="locno" value="0" name="publish_status" <?php if ($news_article->Active == "0") { echo "checked"; } ?>>
                                    <label for="locno"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>


    
            <tr class="profile">
                <td class="collectionbordertwo">
                    <div id="article_en_US" class="language_input">
                            <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_en_US" name="article_title_en_US" value="<?php echo $publishtitle ?>">
                            <textarea class="edit_article" id="article_ta_en_US" name="article_content_en_US" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
        
        <table style="position: sticky;bottom:5px;z-index: 453599; width: 100%" class="profile">
            <tr class="profile">
                <td class="collectionbordertwo"><center>
                    <table style="width: 100%">
                        <tr>
                            
                            <td style="width:100px"></td>

                            <td style="padding-left: 12px; width: 80%; text-align: center">
                                <input type="submit" class="comsubmit" formaction="?News=<?php echo $news_article->id ?>" value="Save" style="margin-right: 20px">
                                <input type="hidden" name="action" value="save">
                                <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                                </form>
                            </td>

                            <td style="padding-left: 15px;">
                                <p class="smallodd" style="white-space: nowrap"><span style="padding-right: 15px" class="commbbcbright" id="rsp_remaining_rmg"></span>
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
        $('[data-remodal-id=edit_article]').remodal(options);
        
        $(document).on('opened', '.remodal', function () {
            adjust_editfield();
        });
        
        function adjust_editfield() {
            var el = document.getElementById('article_ta_en_US');
            var h = el.scrollHeight;
            el.style.height = h+"px";
        }
    </script>
    
    <?php }
    
// END OF EDITING MODAL
?>













<?php if ($user->id == 0) { // if (!$user) { // Deactivated ?>
<!-- Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) - 1x1 - Place in <BODY> of page where ad should appear -->
<div class="vm-placement" data-id="5d79066ce9f6e069bd0fd5da" style="display:none"></div>
<!-- / Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) -->
<?php } ?>


<br>
<div class="maincomment">
   <br>
   <table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
   <tr><td style="width:100%;padding-left: 240px">
   <br><br>
   <?
   // ==== COMMENT SYSTEM 3.0 FOR MAIN ARTICLES HAPPENS HERE ====
   print_comments_outer("1",$news_article->id,"medium"); ?>
    </div>
    </div>
    <script>updateAllTimes('time')</script>
</body>

<?php if ($jumpto ){ ?>
    <script>
        $(window).load(function(){
        $(function(){
            $('html, body').animate({
                scrollTop: $('.anchor<?php echo $jumpto ?>').offset().top-150
            }, 800);
            return false;
        });
        });
    </script>
<?php }
mysqli_close($dbcon);
die;