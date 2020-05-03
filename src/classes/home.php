<link rel="stylesheet" type="text/css" href="data/home.css?v=1<?php if ($user->Role == "99") {echo $mtime; }?>">

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td class="title_xufus"><img src="images/main_bg02_1.png"></td>
            <td width="100%">
                <center>
                    <h class="megatitle"><? echo _("UM_WelcSubject") ?></h><br><a title="Araneshs Pet Battle Blog" rel="nofollow, noindex" class="growl" style="text-decoration: none" href="http://wow-petguide.com/rss_feed.php" target="_blank"><? echo _("Blog_RSS") ?></a>
            </td>
            <td class="title_xufus"><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>

<? // Import all News Articles

    // Settings    
    
    $prj_status_compl = 10; // Percent of current project status completed, displayed in donut chart
    $num_mains = 5; // Amount of main news that will be shown
    $lb_entries = 25;  // How many leaderboard entries are shown per region
    $lb_entry_cutoff = 10;  // At which point is a cut-off of leaderboard entries with expandable option
    
    // Editing videos
    if ($userrights['Edit_Home_Videos'] == true) {
        $videos_action = $_POST['action'];
        if ($videos_action == "add_new_video") {
            $video_link = $_POST['video_link'];
            $video_data = get_youtube_infos($video_link);
            if ($video_data['pageInfo']['totalResults'] == 0) {
                echo '<script>$.growl.error({ message: "Video could not be read. Please make sure the link is correct", duration: "5000", size: "large", location: "tc"});</script>';
            }
            else {
                $vid_title = mysqli_real_escape_string($dbcon, $video_data['items'][0]['snippet']['title']);
                $vid_channel = mysqli_real_escape_string($dbcon, $video_data['items'][0]['snippet']['channelTitle']);
                mysqli_query($dbcon, "INSERT INTO Home_Videos (`Link`, `Uploader`, `Title`, `Channel`) VALUES ('$video_link', '$user->id', '$vid_title', '$vid_channel')") OR die(mysqli_error($dbcon));
                echo '<script>$.growl.notice({ message: "Video added", duration: "5000", size: "large", location: "tc"});</script>';
                $urlchanger = "index.php";
            }
        }
        if ($videos_action == "delete_video") {
            $video_id = $_POST['video'];
            mysqli_query($dbcon, "UPDATE Home_Videos SET `Deleted` = '1' WHERE id = '$video_id'") OR die(mysqli_error($dbcon));
            echo '<script>$.growl.notice({ message: "Video deleted", duration: "5000", size: "large", location: "tc"});</script>';
            $urlchanger = "index.php";
        }
    }
    
    
    
    
    require_once ('home_functions.php');
    
    $title_ext = "Title_".$language;
    $content_ext = "Content_".$language;
    
    $categories_db = mysqli_query($dbcon, "SELECT * FROM News_Categories");
    while ($this_category = mysqli_fetch_object($categories_db)) {
        $news_categories[$this_category->id]['ID'] = $this_category->id;
        $news_categories[$this_category->id]['Name'] = $this_category->Name;
        $news_categories[$this_category->id]['Color'] = $this_category->Color;
    }   
    
    if ($userrights['EditNews'] == "yes") {
        $news_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE Deleted != 1 ORDER BY CreatedTime DESC");
    }
    else {
        $news_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE Active = 1 AND Deleted != 1 ORDER BY CreatedTime DESC");
    }
    $all_count = 0;
    $main_count = 0;
    $article_authors[0] = "";
    
    while ($news_article = mysqli_fetch_object($news_db)) {
        $article_user_text = "";
        $article_used = false;
        $article_user_db = mysqli_query($dbcon, "SELECT Name, id FROM Users WHERE id = '$news_article->CreatedBy'");
        if (mysqli_num_rows($article_user_db) > "0") {
            $article_user = mysqli_fetch_object($article_user_db);
            $article_user_text = ' - BY <span style="text-decoration: none" class="username" rel="'.$article_user->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$article_user->id.'" class="creatorlink"><b>'.strtoupper($article_user->Name).'</a></span>';  
            $article_rem_user_text = '<span style="text-decoration: none" class="username" rel="'.$article_user->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$article_user->id.'" class="creatorlink"><b>'.$article_user->Name.'</a></span>';
            if (!in_array($article_user->Name, $article_authors)) {
                $article_authors[] = $article_user->Name;
            }
        }
        
        // Pull articles
        if ($article_used == false && $main_count < $num_mains) {
            $article_comments_db = mysqli_query($dbcon, "SELECT COUNT(*) AS `count` FROM `Comments` WHERE Category = 1 AND Deleted = 0 AND SortingID = $news_article->id");
            $row = mysqli_fetch_array($article_comments_db);
            $articles_main[$main_count]['Comments'] = $row['count'];
            $articles_main[$main_count]['Article'] = $news_article;
            $articles_main[$main_count]['Usertext'] = $article_user_text;

            $articles_main[$main_count]['Color'] = $news_categories[$news_article->Category]['Color'];
            if (!$articles_main[$main_count]['Color']) {
                $articles_main[$main_count]['Color'] = "5d6ae6";               
            }
            $articles_main[$main_count]['Name'] = strtoupper($news_categories[$news_article->Category]['Name']);
            if (!$articles_main[$main_count]['Name']) {
                $articles_main[$main_count]['Name'] = "PET NEWS";               
            }
            if (file_exists('images/news/'.$news_article->id.'.jpg')) {
                $articles_main[$main_count]['Image'] = 'images/news/'.$news_article->id.'.jpg';
            }
            else {
                $articles_main[$main_count]['Image'] = 'images/news/news_default.jpg';
            }
            $article_used = true;
            $main_count++;
        }
        
        if ($article_used == false) {
            $art_remaining[$all_count]['Article'] = $news_article;
            $art_remaining[$all_count]['Usertext'] = $article_rem_user_text;
            $art_remaining[$all_count]['Color'] = $news_categories[$news_article->Category]['Color'];
            if (!$art_remaining[$all_count]['Color']) {
                $art_remaining[$all_count]['Color'] = "5d6ae6";               
            }
            $art_remaining[$all_count]['Name'] = $news_categories[$news_article->Category]['Name'];
            if (!$art_remaining[$all_count]['Name']) {
                $art_remaining[$all_count]['Name'] = "Pet News";               
            }
            $all_count++;
        }
    }
    
    // Grab all Widgets
    $widgets_db = mysqli_query($dbcon, "SELECT * FROM Home_Widgets ORDER BY Prio ASC");
    $widgets = array();
    while ($widget = mysqli_fetch_object($widgets_db)) {
        $widgets[$widget->id]['Side'] = $widget->Side;
        $widgets[$widget->id]['ID'] = $widget->id;
        $widgets[$widget->id]['Prio'] = $widget->Prio;
        $widgets[$widget->id]['Name'] = $widget->Int_Name;
        $widgets[$widget->id]['First'] = 0;
    }
     
    // Apply custom order
    if ($user->Widget_Order) {
        $widgets_order = explode (",", $user->Widget_Order);
        foreach ($widgets_order as $value) {
            $order_cuts = explode ("-", $value);
            $widgets[$order_cuts[0]]['Side'] = $order_cuts[1];
            $widgets[$order_cuts[0]]['Prio'] = $order_cuts[2];
        }
    }
    sortBy('Prio', $widgets);
    
    // Process move of widgets
    $action = $_POST['action'];
    
    if ($action == "move_widget" && $user) {
        $direction = $_POST['direction'];
        $widget_id = $_POST['widget_id'];
        $urlchanger = "index.php";
        
    
        // Apply operation move widget to the right
        
        if ($direction == "right") {
            foreach ($widgets as $key => $value) {
                if ($value['ID'] == $widget_id) {
                    $widgets[$key]['Side'] = 1;
                    $widgets[$key]['Prio'] = 1;
                }
                if ($value['ID'] != $widget_id && $value['Side'] == 1) {
                    $widgets[$key]['Prio'] = $widgets[$key]['Prio']+1;
                }
            }
            $new_prio = 1;
            foreach ($widgets as $key => $value) {
                if ($value['Side'] == 0) {
                    $widgets[$key]['Prio'] = $new_prio;
                    $new_prio++;
                }
            }
        }
        
         if ($direction == "left") {
            foreach ($widgets as $key => $value) {
                if ($value['ID'] == $widget_id) {
                    $widgets[$key]['Side'] = 0;
                    $widgets[$key]['Prio'] = 1;
                }
                if ($value['ID'] != $widget_id && $value['Side'] == 0) {
                    $widgets[$key]['Prio'] = $widgets[$key]['Prio']+1;
                }
            }
            $new_prio = 1;
            foreach ($widgets as $key => $value) {
                if ($value['Side'] == 1) {
                    $widgets[$key]['Prio'] = $new_prio;
                    $new_prio++;
                }
            }
        }

         if ($direction == "up" OR $direction == "down") {
            foreach ($widgets as $key => $value) {
                if ($value['ID'] == $widget_id) {
                    $key_one = $key;
                    if ($direction == "up") {
                        $key_one_prio = $value['Prio']-1;
                    }
                    if ($direction == "down") {
                        $key_one_prio = $value['Prio']+1;
                    }
                    $key_one_side = $value['Side'];
                    $key_two_prio = $value['Prio'];
                }
            }
            foreach ($widgets as $key => $value) {
                if ($value['Prio'] == $key_one_prio && $value['Side'] == $key_one_side) {
                    $key_two = $key;
                }
            }
            $widgets[$key_one]['Prio'] = $key_one_prio;
            $widgets[$key_two]['Prio'] = $key_two_prio;
        }

        

        sortBy('Prio', $widgets);
        
        // Save new prio settings into user account
        foreach ($widgets as $value) {
            $save_prio = $save_prio.$value['ID'].'-'.$value['Side'].'-'.$value['Prio'].',';
        }
        $save_prio = substr($save_prio, 0, -1);
        mysqli_query($dbcon, "UPDATE Users SET `Widget_Order` = '$save_prio' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
    }

    // Create arrays per side
    $widgets_left = array();
    $widgets_right = array();
    
    foreach ($widgets as $widget) {
        if ($widget['Side'] == 0) {
            $widgets_left[] = $widget;
        }
        if ($widget['Side'] == 1) {
            $widgets_right[] = $widget;
        }
    }
?>


            
            
            
<div class="blogentryfirst">
<center>
<br>
<div class="home_wrapper">

<?
// Admin options to add new articles
if ($userrights['EditNews'] == "yes") { ?>
    <div style="position: absolute; top: 15px; margin-left: 20px;"> 
        <a class="alternativessmall" href="?News=add_main" style="display:inline">Add Large Article</a>
        <a class="alternativessmall" href="?News=add_small" style="display:inline">Add Small Article</a>
    </div>
<? }



// Left Widgets:
echo '<div class="home_left_wrapper">';
    foreach ($widgets_left as $widget) {
        $widget_position = 1;
        if ($widget === reset($widgets_left)) $widget_position = 0;
        if ($widget === end($widgets_left)) $widget_position = 2;
        print_widget($widget, $widget_position);
    }
    if (count($widgets_left) > 0) {
        // Ad block Venatus below left column ?>
        <div class="home_content_block">
            <script>
                if ($(window).width() >994) {
                    document.write('<div class="vm-placement" data-id="5d790cccd6864139e1ce8025"></div>');
                }
            </script>
        </div> 
    <? }  
echo '</div>';


// Center column / News articles
$home_class = "home_main_wrapper";
if (count($widgets_left) == 0 OR count($widgets_right) == 0) $home_class = "home_main_wrapper_big";
echo '<div class="'.$home_class.'">';

    foreach ($articles_main as $key => $article) {
     
         // Grab correct language title
         if (isset($article['Article']->{$title_ext}) && $article['Article']->{$title_ext} != ""){
             $publishtitle = stripslashes(htmlentities($article['Article']->{$title_ext}, ENT_QUOTES, "UTF-8"));
         }
         else {
             $publishtitle = stripslashes(htmlentities($article['Article']->Title_en_US, ENT_QUOTES, "UTF-8"));
         }
     
         // Grab correct language content and format
         if (isset($article['Article']->{$content_ext}) && $article['Article']->{$content_ext} != ""){
             $publisharticle = stripslashes(htmlentities($article['Article']->{$content_ext}, ENT_QUOTES, "UTF-8"));
         }
         else {
             $publisharticle = stripslashes(htmlentities($article['Article']->Content_en_US, ENT_QUOTES, "UTF-8"));
         }
         
         $publisharticle = \BBCode\process_news_preview($publisharticle, 1);
         ?> 
         <div class="home_content_block" style="border-top: 9px solid #<? echo $article['Color']; if ($article['Article']->Active == 0){ echo "; opacity: 0.5; filter: alpha(opacity=50);"; } ?>">
             <div class="home_main_block_inner">
                 <a href="?News=<? echo $article['Article']->id; ?>"><img src="https://www.wow-petguide.com/<? echo $article['Image']; ?>" style="min-width: 5%; width: 100%;"></a>
                 <div class="home_main_title"><a class="home_main_title" style="font-size: 22px" href="?News=<? echo $article['Article']->id; ?>"><? if ($article['Article']->Active == 0){ echo "UNPUBLISHED - "; } echo $publishtitle; ?></a></div>
                 
                 <div class="home_main_subtitle subtitle_text"><? echo $article['Name'].$article['Usertext'] ?></div>
                 <div class="home_main_date subtitle_text"><span name="time"><? echo $article['Article']->CreatedTime; ?></span></div>
                 <div class="home_main_content <? if ($key == 0) echo "home_main_content_preview_first"; else echo "home_main_content_preview"; ?>" id="article_<? echo $article['Article']->id ?>">
                     <? echo $publisharticle ?>
                 </div>
             </div>
             
             <div class="home_main_content_footer">
                 <a class="weblink" style="text-decoration: none" href="?News=<? echo $article['Article']->id ?>"><? echo $article['Comments'] ?> Comments</a>
             </div>
             
             <a href="?News=<? echo $article['Article']->id ?>"">
                 <div class="home_main_content_expand">
                     <center>Read full article
                 </div>
             </a>

         </div>
         <? }
echo '</div>';

 
// Right Widgets:
echo '<div class="home_right_wrapper">';
    foreach ($widgets_right as $widget) {
        $widget_position = 1;
        if ($widget === reset($widgets_right)) $widget_position = 0;
        if ($widget === end($widgets_right)) $widget_position = 2;
        print_widget($widget, $widget_position);
    }
    if (count($widgets_right) > 0) {
        // Ad block Venatus below right column ?>
        <div class="home_content_block ad_right">
            <script>
                if ($(window).width() >994) {
                    document.write('<div class="vm-placement" data-id="5d790cccd6864139e1ce8025"></div>');
                }
            </script>
        </div> 
    <? }
echo '</div>';


    
    
// ad block Venatus between full news and table of news, only if page width is small enough ?> 
    <div class="home_content_block"> 
        <script>
            var ad_two_id = $(window).width() <728 ? "5d7905d371d1621a68eb8f20" : "5d7905f671d1621a68eb8f22";
            document.write('<div class="vm-placement" data-id="'+ad_two_id+'"></div>');
        </script>
    </div>


    <div class="home_bottomtable_wrapper">
        
        <div class="home_content_block home_bottomtable">
            <div class="home_bottomtable_title">
                More News:
            </div>
            
            <div class="home_bottomtable_table">
                <table cellpadding="0" cellspacing="0" id="t1" style="border-collapse: collapse" class="articles_table example table-autosort table-autofilter table-autopage:10 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                    <thead>
                        <tr>
                            <th class="articles_head_first"></th>
                            <th align="left" class="articles_head_first table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 15px;">Title</th>
                            <th align="left" class="table_removable articles_head_first table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 6px">Author</th>
                            <th align="left" class="table_removable articles_head_first table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 6px">Category</th>
                        </tr>
    
                        <tr class="articles_head_second">
                            <th align="left" class="articles_head_second table-sortable:date"><p class="table-sortable-black" style="margin-left: 15px;">Date</p></th>

    
                            <th align="left" class="articles_head_second">
                                <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                            </th>
    
                            <th align="left" class="articles_head_second table_removable">                                
                                <select class="petselect" style="width:100px;" id="author_filter" onchange="Table.filter(this,this)">
                                    <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                    <? foreach ($article_authors as $author) { ?>
                                        <option class="petselect" value="<? echo $author ?>"><? echo $author ?></option>
                                    <? } ?>
                                </select>
                            </th>
    
                            <th align="left" class="articles_head_second table_removable">
                                <select class="petselect" id="category_filter" onchange="Table.filter(this,this)">
                                    <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                    <? foreach ($news_categories as $category) { ?>
                                        <option class="petselect" value="<? echo $category['Name'] ?>"><? echo $category['Name'] ?></option>
                                    <? } ?>
                                </select>
                            </th>
                        </tr>

                    </thead>
                    
                    <tbody>
                        <? foreach ($art_remaining as $key => $article) { ?>
                        <tr class="articles_table_row clickable-row" <? if ($article['Article']->Active == 0){ echo 'style="opacity: 0.5; filter: alpha(opacity=50);"'; } ?> data-href="https://wow-petguide.com/?News=<? echo $article['Article']->id ?>">
                            <td class="articles_table_cell">
                                <span name="time"><? echo $article['Article']->CreatedTime; ?></span>
                            </td>
                            <td class="articles_table_cell">
                            <? if ($article['Article']->Active == 0){ echo 'UNPUBLISHED - '; } ?>
                                <? echo stripslashes(htmlentities($article['Article']->Title_en_US, ENT_QUOTES, "UTF-8")); ?>
                            </td>
                            <td class="articles_table_cell table_removable"><? echo $article['Usertext']; ?></td>
                            <td class="articles_table_cell table_removable"><? echo $article['Name'] ?></td>
                        </tr>
                        <? } ?>
                    </tbody>
                    
                    <tfoot>
                        <tr>
                            <td align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                            <td align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                            <td align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                            <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><? echo _("ColTableReset") ?></a></div></td>
                        </tr>
                    </tfoot>
                    
                </table>
                
                <script>
                    function filter_reset() {
                        document.getElementById('namefilter').value = '';
                        Table.filter(document.getElementById('namefilter'),document.getElementById('namefilter'));
                        document.getElementById('author_filter').value = '';
                        Table.filter(document.getElementById('author_filter'),document.getElementById('author_filter'));
                        document.getElementById('category_filter').value = '';
                        Table.filter(document.getElementById('category_filter'),document.getElementById('category_filter'));
                    }
                </script>
            </div>
        </div>

    </div>
    
</div>
</center>




<br>
<div class="maincomment">
<br><br>
<table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
<tr><td width="100%" align="center">
<br><br><br>
<?
// ==== COMMENT SYSTEM 3.0 FOR MAIN ARTICLES HAPPENS HERE ====
print_comments_outer("0","11","medium");
echo "</div>";
?>
</div>

</div>

<? if ($user->id > 50000) { // if (!$user OR $user->id == 2) { // Deactivated ?>
<!-- Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) - 1x1 - Place in <BODY> of page where ad should appear -->
<div class="vm-placement" data-id="5d79066ce9f6e069bd0fd5da" style="display:none"></div>
<!-- / Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) -->
<? } ?>

<script>          
    $('.w_move_trigger').mouseover(function() {
        var lineid = this.getAttribute("data-lineid");
        document.getElementById('w_move_'+lineid).style.opacity = '1';
        // document.getElementById('w_move_trigger_'+lineid).style.opacity = '1';
    });
    $('.w_move_trigger').mouseout(function() {
        var lineid = this.getAttribute("data-lineid");
        document.getElementById('w_move_'+lineid).style.opacity = '0';
        //document.getElementById('w_move_trigger_'+lineid).style.opacity = '0.4';
    });

    jQuery(document).ready(function($) {
        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
        });
    });
    <? if ($jumpto ){ ?>
        $(window).load(function(){
        $(function(){
            $('html, body').animate({
                scrollTop: $('.anchor<? echo $jumpto ?>').offset().top-150
            }, 800);
            return false;
        });
        });
    </script>
    <? } ?>
</script>