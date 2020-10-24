<link rel="stylesheet" type="text/css" href="data/home.css?v=3<?php if ($user->Role == "99") {echo $mtime; }?>">

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td class="title_xufus"><img src="images/main_bg02_1.png"></td>
            <td width="100%">
                <center>
                    <h class="megatitle"><?php echo __("Welcome to Xu-Fu's Pet Guides!") ?></h><br>
                    
                    <a class="growl" style="cursor: pointer; text-decoration: none" id="rss_news" data-clipboard-text="https://www.wow-petguide.com/rss/rss_feed.xml"><?php echo __("Subscribe via RSS") ?></a>
                    
                    <div class="remtt" style="display:none" id="rss_news_confirm"><?php echo __("RSS Link copied to clipboard!") ?></div>
                    <script>
                    var btn = document.getElementById('rss_news');
                    var clipboard = new Clipboard(btn);
            
                    clipboard.on('success', function(e) {
                        console.log(e);
                            $('#rss_news_confirm').delay(0).fadeIn(500);
                            $('#rss_news_confirm').delay(1200).fadeOut(500);
                        });
                    clipboard.on('error', function(e) {
                        console.log(e);
                    });
                    </script>
            </td>
            <td class="title_xufus"><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>

<?php // Import all News Articles

    // Settings    
    
    $prj_status_compl = 5; // Percent of current project status completed, displayed in donut chart
    $num_mains = 5; // Amount of main news that will be shown
    $lb_entries = 25;  // How many leaderboard entries are shown per region
    $lb_entry_cutoff = 10;  // At which point is a cut-off of leaderboard entries with expandable option
    
    // Editing videos
    if ($userrights['Edit_Home_Videos'] == true) {
        $videos_action = $_POST['action'];
        if ($videos_action == "add_new_video") {
            $video_link = $_POST['video_link'];
            $video_data = get_youtube_infos($video_link);
            // echo "<pre><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
            // print_r($video_data);
            // die;
            if ($video_data['pageInfo']['totalResults'] == 0) {
                echo '<script>$.growl.error({ message: "Video could not be read. Please make sure the link is correct", duration: "5000", size: "large", location: "tc"});</script>';
            }
            else {
                $vid_description = mysqli_real_escape_string($dbcon, $video_data['items'][0]['snippet']['description']);
                $vid_title = mysqli_real_escape_string($dbcon, $video_data['items'][0]['snippet']['title']);
                $vid_channel = mysqli_real_escape_string($dbcon, $video_data['items'][0]['snippet']['channelTitle']);
                mysqli_query($dbcon, "INSERT INTO Home_Videos (`Link`, `Uploader`, `Title`, `Channel`, `Description`) VALUES ('$video_link', '$user->id', '$vid_title', '$vid_channel', '$vid_description')") OR die(mysqli_error($dbcon));
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
    
    // Editing Leveling Opportunities Widget
    if ($userrights['Edit_Home_Leveling'] == true) {
        $this_action = $_POST['action'];
        // Editing Pet Weeks
        if ($this_action == "add_new_petweek") {
            $new_petweek = $_POST['new_petweek'];
            $weekday = date('w', strtotime($new_petweek));
            if ($weekday != 2 && $weekday != 3) {
                echo '<script>$.growl.error({ message: "Unclear date, please select a Tuesday or Wednesday.", duration: "10000", size: "large", location: "tc"});</script>';
            }
            else {
                if ($weekday == 3) {
                    $new_petweek = date('Y/m/d', strtotime($new_petweek. ' - 1 days'));
                }
                $new_petweek_input = $new_petweek.' 08:00:00';
                mysqli_query($dbcon, "INSERT INTO Home_PetWeeks (`Start`) VALUES ('$new_petweek_input')") OR die(mysqli_error($dbcon));
                echo '<script>$.growl.notice({ message: "New Pet XP Week starting on '.$new_petweek.' added.", duration: "10000", size: "large", location: "tc"});</script>';
            }
        }
        if ($this_action == "delete_petweek") {
            $week_id = $_POST['petweek'];
            mysqli_query($dbcon, "DELETE FROM Home_PetWeeks WHERE id = '$week_id'") OR die(mysqli_error($dbcon));
            echo '<script>$.growl.notice({ message: "Pet XP Week removed from calendar", duration: "5000", size: "large", location: "tc"});</script>';
        }
        // Editing Pet Tamers
        if ($this_action == "add_new_tamer") {
            $new_tamer_id = $_POST['new_tamer_id'];
            $new_tamer_strategy = $_POST['new_tamer_strategy'];
            $tamer_id_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $new_tamer_id");
            if (mysqli_num_rows($tamer_id_db) < 1) {
                echo '<script>$.growl.error({ message: "Could not find tamer ID. Please make sure it exists.", duration: "10000", size: "large", location: "tc"});</script>';
            }
            $strategy_id_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $new_tamer_strategy");
            if (mysqli_num_rows($strategy_id_db) < 1) {
                echo '<script>$.growl.error({ message: "Could not find Strategy. Please make sure it exists.", duration: "10000", size: "large", location: "tc"});</script>';
            }
            if (mysqli_num_rows($strategy_id_db) > 0 && mysqli_num_rows($tamer_id_db) > 0) {
                mysqli_query($dbcon, "INSERT INTO Home_Tamers (`Tamer_id`, `Fight_id`) VALUES ('$new_tamer_id','$new_tamer_strategy')") OR die(mysqli_error($dbcon));
                echo '<script>$.growl.notice({ message: "New tamer added.", duration: "10000", size: "large", location: "tc"});</script>';
            }

        }
        if ($this_action == "delete_tamer") {
            $tamer_id = $_POST['tamer'];
            mysqli_query($dbcon, "DELETE FROM Home_Tamers WHERE id = '$tamer_id'") OR die(mysqli_error($dbcon));
            echo '<script>$.growl.notice({ message: "Tamer removed", duration: "5000", size: "large", location: "tc"});</script>';
        }
        
        // Add Quests for a specific day
        if ($this_action == "add_dailies") {
            $tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_Tamers ORDER BY Tamer_id ASC") OR die(mysqli_error($dbcon));
            while($tamer = mysqli_fetch_object($tamers_db)) {
                $this_result = $_POST[$tamer->id];
                if ($this_result) {
                    $all_quests = $all_quests."-".$this_result;
                }
            }
            $no_quest = $_POST['none']; 
            if ($no_quest == "none") {
                $all_quests = "none";
            }
            if (!$all_quests) {
                echo '<script>$.growl.error({ message: "No quests selected, please select at least 1.", duration: "10000", size: "large", location: "tc"});</script>';
            }
            else {
                $all_quests = substr($all_quests, 1); 
                $quests_date = $_POST['new_dailies'];
                $legion_tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_LegionQuests WHERE Date >= '$quests_date' AND Date < '$quests_date' + INTERVAL 1 DAY ORDER BY id DESC LIMIT 1") OR die(mysqli_error($dbcon));
                if (mysqli_num_rows($legion_tamers_db) > 0) {
                    echo '<script>$.growl.error({ message: "An entry for that day exists already. Please delete the old one first or pick a different day.", duration: "10000", size: "large", location: "tc"});</script>';
                }
                else {
                    $quests_date = $quests_date.' 12:00:00';
                    if ($no_quest == "none") {
                        $all_quests = "";
                    }
                    mysqli_query($dbcon, "INSERT INTO Home_LegionQuests (`Date`, `Quests`) VALUES ('$quests_date','$all_quests')") OR die(mysqli_error($dbcon));
                    echo '<script>$.growl.notice({ message: "Dailies added!", duration: "5000", size: "large", location: "tc"});</script>'; 
                }
            }
        }
        
        if ($this_action == "delete_day") {
            $day_id = $_POST['legion_day'];
            mysqli_query($dbcon, "DELETE FROM Home_LegionQuests WHERE id = '$day_id'") OR die(mysqli_error($dbcon));
            echo '<script>$.growl.notice({ message: "Quests for that day removed", duration: "5000", size: "large", location: "tc"});</script>';
        }
        
        
        $urlchanger = "index.php";
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
        $news_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE Deleted != 1 AND Sticky != 1 ORDER BY CreatedTime DESC");
    }
    else {
        $news_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE Active = 1 AND Deleted != 1 AND Sticky != 1 ORDER BY CreatedTime DESC");
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
    
    if ($action == "unhide_widgets" && $user) {
        mysqli_query($dbcon, "DELETE FROM Home_Hidden_Widgets WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
        echo '<script>$.growl.notice({ message: "All widgets are shown again. Magic!", duration: "5000", size: "large", location: "tc"});</script>';
    }
    
    if ($action == "move_widget" && $user) {
        $direction = $_POST['direction'];
        $widget_id = $_POST['widget_id'];
        $urlchanger = "index.php";
        

        if ($direction == "hide") {
            mysqli_query($dbcon, "INSERT INTO Home_Hidden_Widgets (`User`, `Widget`) VALUES ('$user->id', '$widget_id')") OR die(mysqli_error($dbcon));
            echo '<script>$.growl.notice({ message: "Widget hidden. You can unhide it through the menu on the bottom of the right or left column.", duration: "5000", size: "large", location: "tc"});</script>';
        }

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
    
    // Check for hidden widgets
    if ($user) {
        $hidden_widgets_db = mysqli_query($dbcon, "SELECT * FROM Home_Hidden_Widgets WHERE User = $user->id") OR die(mysqli_error($dbcon));
            while($hidden_widget = mysqli_fetch_object($hidden_widgets_db)) {
            $hidden_widgets[$hidden_widget->Widget] = TRUE;
            $add_unhide_widget = TRUE;
        }
    }
    
    foreach ($widgets as $widget) {
        if ($widget['Side'] == 0 && $hidden_widgets[$widget['ID']] != TRUE) {
            $widgets_left[] = $widget;
        }
        if ($widget['Side'] == 1 && $hidden_widgets[$widget['ID']] != TRUE) {
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
<?php }



 
    $sticky_db = mysqli_query($dbcon, "SELECT * FROM News_Articles WHERE Deleted != 1 AND Sticky = 1 ORDER BY CreatedTime DESC");
    
    if (mysqli_num_rows($sticky_db) > 0) { ?>
        <div class="home_sticky_block">
            <img class="icon_lightning" src="images/icon_home_lightning.png">
            <?
            $count_stickies = 0;
            while ($sticky_article = mysqli_fetch_object($sticky_db)) { ?>
                <a class="sticky_element" <?php if ($count_stickies > 0) echo 'style="border-top: 1px solid #989898"'; ?> href="?News=<?php echo $sticky_article->id; ?>">
                    <b>Important News:</b> <?php echo stripslashes(htmlentities($sticky_article->Title_en_US, ENT_QUOTES, "UTF-8")); ?>
                </a> 
            <?
            $count_stickies++;
            } ?>
        </div>
    <?php }


// Left Widgets:
echo '<div class="home_left_wrapper">';
    foreach ($widgets_left as $widget) {
        $widget_position = 1;
        if ($widget === reset($widgets_left)) $widget_position = 0;
        if ($widget === end($widgets_left)) $widget_position = 2;
        print_widget($widget, $widget_position);
    }
    
    if ($add_unhide_widget == TRUE && count($widgets_right) < 1 && count($widgets_left) > 0) { // Show unhide widgets part if no place on right
        print_unhide_widget();
    }
    
    if (count($widgets_left) > 0) {
        // Ad block Venatus below left column
        if ($ads_active == true) { ?>
        <div class="home_content_block">
            <script>
                if ($(window).width() >994) {
                    document.write('<div class="vm-placement" data-id="5d790cccd6864139e1ce8025"></div>');
                }
            </script>
        </div> 
    <?php }
    }  
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
         <div class="home_content_block" style="border-top: 9px solid #<?php echo $article['Color']; if ($article['Article']->Active == 0){ echo "; opacity: 0.5; filter: alpha(opacity=50);"; } ?>">
             <div class="home_main_block_inner">
                 <a href="?News=<?php echo $article['Article']->id; ?>"><img src="https://www.wow-petguide.com/<?php echo $article['Image']; ?>" style="min-width: 5%; width: 100%;"></a>
                 <div class="home_main_title"><a class="home_main_title" style="font-size: 22px" href="?News=<?php echo $article['Article']->id; ?>"><?php if ($article['Article']->Active == 0){ echo "UNPUBLISHED - "; } echo $publishtitle; ?></a></div>
                 
                 <div class="home_main_subtitle subtitle_text"><?php echo $article['Name'].$article['Usertext'] ?></div>
                 <div class="home_main_date subtitle_text"><span name="time"><?php echo $article['Article']->CreatedTime; ?></span></div>
                 <div class="home_main_content <?php if ($key == 0) echo "home_main_content_preview_first"; else echo "home_main_content_preview"; ?>" id="article_<?php echo $article['Article']->id ?>">
                     <?php echo $publisharticle ?>
                 </div>
             </div>
             
             <div class="home_main_content_footer">
                 <a class="weblink" style="text-decoration: none" href="?News=<?php echo $article['Article']->id ?>"><?php echo $article['Comments'] ?> Comments</a>
             </div>
             
             <a href="?News=<?php echo $article['Article']->id ?>"">
                 <div class="home_main_content_expand">
                     <center>Read full article
                 </div>
             </a>

         </div>
         <?php }
echo '</div>';

 
// Right Widgets:
echo '<div class="home_right_wrapper">';
    foreach ($widgets_right as $widget) {
        $widget_position = 1;
        if ($widget === reset($widgets_right)) $widget_position = 0;
        if ($widget === end($widgets_right)) $widget_position = 2;
        print_widget($widget, $widget_position);
    }
    if ($add_unhide_widget == TRUE && (count($widgets_right) > 0 OR (count($widgets_right) < 1 && count($widgets_left) < 1))) {
        print_unhide_widget();
    }
    if (count($widgets_right) > 0) {
        // Ad block Venatus below right column
        if ($ads_active == true) { ?>
        <div class="home_content_block ad_right">
            <script>
                if ($(window).width() >994) {
                    document.write('<div class="vm-placement" data-id="5d790cccd6864139e1ce8025"></div>');
                }
            </script>
        </div> 
    <?php }
    }
echo '</div>';


    
    
// ad block Venatus between full news and table of news
    if ($ads_active == true) { ?> 
    <div class="home_content_block"> 
        <script>
            var ad_two_id = $(window).width() <728 ? "5d7905d371d1621a68eb8f20" : "5d7905f671d1621a68eb8f22";
            document.write('<div class="vm-placement" data-id="'+ad_two_id+'"></div>');
        </script>
    </div>
    <?php } ?>

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
                                    <option class="petselect" value=""><?php echo __("All") ?></option>
                                    <?php foreach ($article_authors as $author) { ?>
                                        <option class="petselect" value="<?php echo $author ?>"><?php echo $author ?></option>
                                    <?php } ?>
                                </select>
                            </th>
    
                            <th align="left" class="articles_head_second table_removable">
                                <select class="petselect" id="category_filter" onchange="Table.filter(this,this)">
                                    <option class="petselect" value=""><?php echo __("All") ?></option>
                                    <?php foreach ($news_categories as $category) { ?>
                                        <option class="petselect" value="<?php echo $category['Name'] ?>"><?php echo $category['Name'] ?></option>
                                    <?php } ?>
                                </select>
                            </th>
                        </tr>

                    </thead>
                    
                    <tbody>
                        <?php foreach ($art_remaining as $key => $article) { ?>
                        <tr class="articles_table_row clickable-row" <?php if ($article['Article']->Active == 0){ echo 'style="opacity: 0.5; filter: alpha(opacity=50);"'; } ?> data-href="https://wow-petguide.com/?News=<?php echo $article['Article']->id ?>">
                            <td class="articles_table_cell">
                                <span name="time"><?php echo $article['Article']->CreatedTime; ?></span>
                            </td>
                            <td class="articles_table_cell">
                            <?php if ($article['Article']->Active == 0){ echo 'UNPUBLISHED - '; } ?>
                                <?php echo stripslashes(htmlentities($article['Article']->Title_en_US, ENT_QUOTES, "UTF-8")); ?>
                            </td>
                            <td class="articles_table_cell table_removable"><?php echo $article['Usertext']; ?></td>
                            <td class="articles_table_cell table_removable"><?php echo $article['Name'] ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    
                    <tfoot>
                        <tr>
                            <td align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                            <td align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                            <td align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                            <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><?php echo __("Reset Filters") ?></a></div></td>
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

<?php if ($user->id > 50000) { // if (!$user OR $user->id == 2) { // Deactivated ?>
<!-- Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) - 1x1 - Place in <BODY> of page where ad should appear -->
<div class="vm-placement" data-id="5d79066ce9f6e069bd0fd5da" style="display:none"></div>
<!-- / Wow Pet Guides - Rich Media (5d79066ce9f6e069bd0fd5da) -->
<?php } ?>

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
    <?php if ($jumpto ){ ?>
        $(window).load(function(){
        $(function(){
            $('html, body').animate({
                scrollTop: $('.anchor<?php echo $jumpto ?>').offset().top-150
            }, 800);
            return false;
        });
        });
    </script>
    <?php } ?>
</script>