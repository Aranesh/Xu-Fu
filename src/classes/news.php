<?php
// Save changes to article:

$action = $_POST['action'];


if ($action == "save" && $userrights['EditNews'] == "yes" && $news_article) {
    $articlecontent_en_US = mysqli_real_escape_string($dbcon, $_POST['article_content_en_US']);
    $articletitle_en_US = mysqli_real_escape_string($dbcon, $_POST['article_title_en_US']);
    $article_new_cat = $_POST['news_cat_select'];
    $publish_status = $_POST['publish_status'];
    $news_type_select = $_POST['news_type_select'];
    
    mysqli_query($dbcon, "UPDATE News_Articles SET `Active` = '$publish_status', `Main` = '$news_type_select', `Category` = '$article_new_cat', `Content_en_US` = '$articlecontent_en_US', `Title_en_US` = '$articletitle_en_US' WHERE id = '$news_article->id'");

    // Update article info for output:
    $news_article_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE id = $news_article_id");  
    $news_article = mysqli_fetch_object($news_article_db);
}







// Grab and process article details for output
$publishtitle = stripslashes(htmlentities($news_article->Title_en_US, ENT_QUOTES, "UTF-8"));
$publisharticle = stripslashes(htmlentities($news_article->Content_en_US, ENT_QUOTES, "UTF-8"));
$editarticle = $publisharticle;

$publisharticle = \BBCode\process_news_full($publisharticle);

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
        <td width="100%"><center><h class="megatitle"><? echo $publishtitle; ?></h><br><a title="Pet News from Xu-Fu's Pet Guides" rel="nofollow, noindex" class="growl" style="text-decoration: none" href="http://wow-petguide.com/rss_feed.php" target="_blank"><? echo _("Blog_RSS") ?></a></td></td>
        <td><img src="https://www.wow-petguide.com/images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>





<div class="blogentryfirst">
    <div class="articlebottom"></div>
    
    <center>
        <div class="news_main_wrapper" <? if ($news_article->Active == 0) { echo 'style="background-color: #e79e9e; margin-top: 10px"'; } ?>>
       
            <div class="news_content_block" style="border-top: 9px solid #<? echo $news_categories[$news_article->Category]['Color']; ?>;">
                <div class="news_main_inner">
                    <? if ($userrights['EditNews'] == "yes") { ?>
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
                                    <input type="hidden" name="article_id" value="<? echo $news_article->id; ?>">
                            </td>
                            <td width="80px">
                                    <input id="submit_img" type="submit" value="Upload" style="display: none" class="submit_button">
                                </form> 
                            </td>
                            <td width="150px">
                                <a class="alternativessmall" href="#" id="remove_img_button" onclick="remove_image(<? echo $news_article->id; ?>)" style="display:<? if ($show_remove_button != 1) { echo "none"; } else echo "inline"; ?>">Remove Image</a>
                            </td>
                            <td width="150px">
                                <a class="alternativessmall" href="#" id="remove_img_button" onclick="delete_news_article(<? echo $news_article->id; ?>)">Delete Article</a>
                            </td>
                        </tr>
                    </table>
                    </div>
                    <? } ?>
                    <div class="news_title"><? echo $publishtitle; ?></div>
                    <div class="news_date"><span name="time"><? echo $news_article->CreatedTime; ?></span></div>
                    <div class="news_subtitle"><? echo $article_category.$article_user_text ?></div>
                    <img class="news_title" id="title_image" src="<? echo $article_image ?>">
                    <div class="news_content"><? echo $publisharticle ?></div>
                </div>
            </div>
        
            <div class="social_media_buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A//www.wow-petguide.com/?News=<? echo $news_article->id ?>" target="_blank"><img class="soc_m_share socm_fb" src="images/news_share_facebook.png"></a>
                <a href="https://twitter.com/intent/tweet?text=https%3A//www.wow-petguide.com/?News=<? echo $news_article->id ?>" target="_blank"><img class="soc_m_share" src="images/news_share_twitter.png"></a>
                <a href="https://pinterest.com/pin/create/button/?url=https%3A//www.wow-petguide.com/?News=<? echo $news_article->id ?>&media=<? echo "https://wow-petguide.com/".$article_image ?>&description=News from Xu-Fu's Pet Guides: <? echo $publishtitle; ?>" target="_blank"><img class="soc_m_share socm_pint" src="images/news_share_pinterest.png"></a>
            </div>
        
        </div>
        <div id="upload_output"></div>
        
    <br><br>
    </center>


<? if ($userrights['EditNews'] == "yes") { ?>
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
<? } ?>



<? // EDITING MODAL BELOW
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
             
             
             /*   
            ?>
                
                <td style="padding-left: 10px">
                    <p class="blogodd">Add:</p>
                    <span class="add_url_tt" data-tooltip-content="#bb_add_url" style="cursor: help;">
                        <button type="button" class="bbbutton">URL</button>
                    </span>
    
                    <div style="display: none">
                        <span id="bb_add_url">
                            <table>
                                <tr>
                                    <td style="text-align: right; padding-right: 5px"><p class="blogeven">Shown name:</p></td>
                                    <td><input class="petselect" style="width: 280px" type="text" id="bb_url_name"></td>
                                    <td rowspan="2" style="text-align: right; padding-left: 5px"><button onclick="bb_articles('url', '','news');" class="bnetlogin">Add</button></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right; padding-right: 5px"><p class="blogeven">URL:</p></td>
                                    <td><input class="petselect" style="width: 280px" type="text" id="bb_url" value=""></td>
                                </tr>
                            </table>
                        </span>
                    </div>
    
                    <script>
                        $(document).ready(function() {
                            $('.add_url_tt').tooltipster({
                                interactive: 'true',
                                animation: 'fade',
                                side: 'bottom',
                                theme: 'tooltipster-smallnote'
                            });
                        });
                    </script>
                </td>
    
    
                <td>
                    <span class="add_pet_tt" data-tooltip-content="#bb_add_pet" style="cursor: help;">
                        <button type="button" class="bbbutton">Pet</button>
                    </span>
    
                    <div style="display: none;">
                        <span id="bb_add_pet" style="height: 600px;">
                            <table>
                                <tr>
                                    <td>
                                        <select width="230" data-placeholder="" name="bb_pet" id="bb_pet_dd" class="chosen-select_pet">
                                            <option value=""></option>
                                                <?
                                                sortBy('Name', $all_pets, 'asc');
                                                foreach ($all_pets as $value) { 
                                                    echo '<option value="'.$value['PetID'].'">'.$value['Name'].'</option>';
                                                }
                                                ?>
                                        </select>
                                    </td>
                                    <td style="text-align: right; padding-left: 5px"><button onclick="bb_articles('pet', '','news');" class="bnetlogin">Add</button>
                                    </td>
                                </tr>
                            </table>
                            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                        </span>
                    </div>
    
                    <script>
                        $(document).ready(function() {
                            $('.add_pet_tt').tooltipster({
                                interactive: 'true',
                                animation: 'fade',
                                side: 'bottom',
                                height: '600',
                                theme: 'tooltipster-smallnote'
                            });
                        });
                        $(".chosen-select_pet").chosen({width: 300});
                    </script>
                </td>
    
                <td>
                    <span class="add_skill_tt" data-tooltip-content="#bb_add_skill" style="cursor: help;">
                        <button type="button" class="bbbutton">Skill</button>
                    </span>
    
                    <div style="display: none;">
                        <span id="bb_add_skill" style="height: 600px;">
                            <table>
                                <tr>
                                    <td>
                                        <select width="230" data-placeholder="" name="bb_skill" id="bb_skill_dd" class="chosen-select_skill">
                                            <option value=""></option>
                                                <?
                                                $allskillsdb = mysqli_query($dbcon, "SELECT * FROM Spells Where PetSpell = '1' ORDER BY en_US") or die(mysqli_error($dbcon));
                                                while ($thisskill = mysqli_fetch_object($allskillsdb)) {
                                                    echo '<option value="'.$thisskill->SpellID.'">'.$thisskill->en_US.'</option>';
                                                }
                                                ?>
                                        </select>
                                    </td>
                                    <td style="text-align: right; padding-left: 5px"><button onclick="bb_articles('skill', '','news');" class="bnetlogin">Add</button>
                                    </td>
                                </tr>
                            </table>
                            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                        </span>
                    </div>
    
                    <script>
                        $(document).ready(function() {
                            $('.add_skill_tt').tooltipster({
                                interactive: 'true',
                                animation: 'fade',
                                side: 'bottom',
                                height: '500',
                                width: '650',
                                theme: 'tooltipster-smallnote'
                            });
                        });
                        $(".chosen-select_skill").chosen({width: 300});
                    </script>
                </td>
    
    
    
                <td>
                    <span class="add_img_tt" data-tooltip-content="#bb_add_img" style="cursor: help;">
                        <button type="button" class="bbbutton">Image</button>
                    </span>
    
                    <div style="display: none;">
                        <span id="bb_add_img">
                            <table style="width: 575px">
    
                                <tr>
                                    <td>
                                        <select data-placeholder="" name="image_cat" id="sel_cat" class="chosen-select_image" required>
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
                                        <a href="https://www.wow-petguide.com/?page=adm_images" target="_blank"><button type="submit" class="comedit">Manage / Upload Images</button></a>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td colspan="2">
                                        <table>
                                           <tr>
                                               <td>
                                                   <p class="blogeven" style="font-size: 14px">Left:</p>
                                               </td>
                                               <td style="padding-right: 10px">
                                                   <ul class="radiossmall">
                                                       <li>
                                                           <input class="lightblue" type="radio" id="nofloatleft" value="1" name="imgfloat">
                                                           <label for="nofloatleft"></label>
                                                           <div class="check"></div>
                                                       </li>
                                                   </ul>
                                               </td>
                                               
                                               <td>
                                                   <p class="blogeven" style="font-size: 14px">Right:</p>
                                               </td>
                                               <td style="padding-right: 10px">
                                                   <ul class="radiossmall">
                                                       <li>
                                                           <input class="lightblue" type="radio" id="nofloatright" value="2" name="imgfloat">
                                                           <label for="nofloatright"></label>
                                                           <div class="check"></div>
                                                       </li>
                                                   </ul>
                                               </td>
                                               
                                               <td>
                                                   <p class="blogeven" style="font-size: 14px">Center:</p>
                                               </td>
                                               
                                               <td>
                                                   <ul class="radiossmall">
                                                       <li>
                                                           <input class="lightblue" type="radio" id="floatcenter" value="3" name="imgfloat">
                                                           <label for="floatcenter"></label>
                                                           <div class="check"></div>
                                                       </li>
                                                   </ul>
                                               </td>
                                               
                                               <td>
                                                   <p class="blogeven" style="font-size: 14px">Float-left:</p>
                                               </td>
                                               <td>
                                                   <ul class="radiossmall">
                                                       <li>
                                                           <input class="lightblue" type="radio" id="floatleft" value="4" name="imgfloat">
                                                           <label for="floatleft"></label>
                                                           <div class="check"></div>
                                                       </li>
                                                   </ul>
                                               </td>
                       
                                               <td>
                                                   <p class="blogeven" style="font-size: 14px">Float-right:</p>
                                               </td>
                                               
                                               <td>
                                                   <ul class="radiossmall">
                                                       <li>
                                                           <input class="lightblue" type="radio" id="floatright" value="5" name="imgfloat" checked>
                                                           <label for="floatright"></label>
                                                           <div class="check"></div>
                                                       </li>
                                                   </ul>
                                               </td>
                                               
                                           </tr>
                                        </table>
                                    </td>
                                </tr>
    
                                <tr>
                                    <td style="height: 400px; width: 100%" colspan="2">
                                        <div style="height: 400px; width: 100%; overflow-x: hidden; overflow-y: auto;" id="gallerycontainer">
                                        </div>
                                    </td>
    
                                </tr>
    
                             <script>
                                $(".chosen-select_image").chosen({width: 250, placeholder_text_single: 'Select a Category'});
    
                                function adm_pullgallery(i) {
                                    $('#gallerycontainer').empty();
                                    $('#gallerycontainer').load('classes/ajax/adm_pullimages.php?g='+i+'&u=<? echo $user->id ?>&del=<? echo $user->ComSecret ?>&p=e');
                                }
    
                                $(".chosen-select_image").chosen().change(function(event){
                                    var i = $('select[name=image_cat]').val();
                                    adm_pullgallery(i);
                                });
    
                                var u = $('select[name=image_cat]').val();
                                if (u != "") {
                                    adm_pullgallery(u);
                                }
                                $( document ).on( "click", ".galimg", function() {
                                    bb_articles('img', this.dataset.imgid,'news');
                                });
                            </script>
                            </table>
                        </span>
                    </div>
    
                    <script>
                        $(document).ready(function() {
                            $('.add_img_tt').tooltipster({
                                interactive: 'true',
                                animation: 'fade',
                                side: 'bottom',
                                height: '600',
                                theme: 'tooltipster-smallnote'
                            });
                        });
                        $(".chosen-select_image").chosen({width: 400});
                    </script>
                </td>
    
    
    
                <td>
                    <span class="add_user_tt" data-tooltip-content="#bb_add_user" style="cursor: help;">
                        <button type="button" class="bbbutton">Username</button>
                    </span>
    
                    <div style="display: none;">
                        <span id="bb_add_user" style="height: 600px;">
                            <table>
                                <tr>
                                    <td>
                                        <select data-placeholder="Enter Username" id="username" name="recipient" class="chosen-select_username">
                                            <option value="0"></option>
                                         </select>
    
                                        <script type = "text/javascript">
                                            $("#username").chosen({width: 325});
                                        </script>
                                    </td>
                                    <td style="text-align: right; padding-left: 5px"><button onclick="bb_articles('username', '','news');" class="bnetlogin">Add</button>
                                    </td>
                                </tr>
                            </table>
                            <br><br>If the username search is not working, briefly open the<br>
                            Pet, Skill and Image tooltips and try again. <br>
                            This is a nasty bug I can't seem to fix :/ <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                        </span>
                    </div>
    
                    <script>
                        $(document).ready(function() {
                            $('.add_user_tt').tooltipster({
                                interactive: 'true',
                                animation: 'fade',
                                side: 'bottom',
                                height: '300',
                                width: '650',
                                theme: 'tooltipster-smallnote'
                            });
                        });
                    
                        
                        var x_timer;
                        $('.chosen-search-input').on('input',function(e){
                            var searchterm = $('.chosen-search-input').val();
                            clearTimeout(x_timer);
                            x_timer = setTimeout(function(){
                                $(".no-results").text("<? echo _("PM_searching") ?>");
                                if (searchterm.length >= '2') {
                                    clearTimeout(x_timer);
                                    x_timer = setTimeout(function(){
                                        $("#username").empty();
                                        var xmlhttp = new XMLHttpRequest();
                                        xmlhttp.onreadystatechange = function() {
                                            if (this.readyState == 4 && this.status == 200) {
                                                if (this.responseText == "[]") {
                                                    $(".no-results").text("<? echo _("PM_ErrNoUser") ?>");
                                                }
                                                else {
                                                    var data = this.responseText;
                                                    data = JSON.parse(data);
    
                                                    $.each(data, function (idx, obj) {
                                                        $("#username").append('<option value="' + obj.id + '">' + obj.text + '</option>');
                                                    });
                                                    $("#username").trigger("chosen:updated");
    
                                                    $("#username").chosen({width: 325});
                                                    $('.chosen-search-input').val(searchterm);
                                                }
                                            }
                                        };
                                        xmlhttp.open("GET", "classes/ajax/ac_writemessage.php?q=" + encodeURIComponent(searchterm) + "&e=f&u=<? echo $user->id ?>", true);
                                        xmlhttp.send();
                                    }, 1000);
                                }
                                else {
                                    clearTimeout(x_timer);
                                    x_timer = setTimeout(function(){
                                        $("#username").empty();
                                        $(".no-results").text("<? echo _("PM_ErrTooShort") ?>");
                                    }, 300);
                                }
                            }, 200);
                        });
                    </script>
                </td>
    */ ?>
            </tr>
        </table>
    
        <table width="100%" class="profile">
            
        <tr class="profile">
            <td class="collectionbordertwo" <? if ($news_article->Active == 0) { echo 'style="background-color: #e79e9e"'; } ?>>
       
            <form method="post" style="display: inline">
            <input type="hidden" name="action" value="save_article">
       
                <table>
                    <tr>
                        
                        <td>
                            <p class="blogodd" style="font-size: 14px">Type:</p>
                        </td>

                        <td>
                            <select name="news_type_select" class="petselect" required>
                                <option class="petselect" value="1" <? if ($news_article->Main == 1) { echo " selected"; } ?>>Large News</option>
                                <option class="petselect" value="0" <? if ($news_article->Main == 0) { echo " selected"; } ?>>Small News</option>
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
                        
                        <td style="width: 1px; background-color: #757575">
                        </td>

                        <td style="padding: 0px 10px 0px 10px">
                            <p class="blogodd" style="font-size: 14px">Published:</p>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Yes:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="locyes" value="1" name="publish_status" <? if ($news_article->Active == "1") { echo "checked"; } ?>>
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
                                    <input class="red" type="radio" id="locno" value="0" name="publish_status" <? if ($news_article->Active == "0") { echo "checked"; } ?>>
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
                            <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_en_US" name="article_title_en_US" value="<? echo $publishtitle ?>">
                            <textarea class="edit_article" id="article_ta_en_US" name="article_content_en_US" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><? echo $editarticle ?></textarea>
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
                                <input type="submit" class="comsubmit" formaction="?News=<? echo $news_article->id ?>" value="Save" style="margin-right: 20px">
                                <input type="hidden" name="action" value="save">
                                <input data-remodal-action="close" type="submit" class="comdelete" value="<? echo _("FormButtonCancel"); ?>">
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
    
    <? }
    
// END OF EDITING MODAL
?>













<? if ($user->id > 50000) { // if (!$user) { // Deactivated ?>
<!-- Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) - 1x1 - Place in <BODY> of page where ad should appear -->
<div class="vm-placement" data-id="5d79066ce9f6e069bd0fd5da" style="display:none"></div>
<!-- / Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) -->
<? } ?>


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

<? if ($jumpto ){ ?>
    <script>
        $(window).load(function(){
        $(function(){
            $('html, body').animate({
                scrollTop: $('.anchor<? echo $jumpto ?>').offset().top-150
            }, 800);
            return false;
        });
        });
    </script>
<? }
mysqli_close($dbcon);
die;