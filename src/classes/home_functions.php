<?

function print_mover($widget, $position) {
 ?>
    <div class="home_widget_move">
        <div id="w_move_trigger_<? echo $widget['Name'] ?>" class="w_move_trigger" data-lineid="<? echo $widget['Name'] ?>">
            <img src="images/widget_move_trigger.png">
        </div>
        <div id="w_move_<? echo $widget['Name'] ?>" style="float: right; opacity: 0" class="w_move_trigger" data-lineid="<? echo $widget['Name'] ?>">
            <form action="index.php" method="post" style="display: inline">
            <input type="hidden" name="action" value="move_widget">
            <input type="hidden" name="widget_id" value="<? echo $widget['ID'] ?>">
            <? if ($widget['Side'] == 1) { ?>
                <button class="move_widget" name="direction" value="left"><img src="images/widget_move_left.png"></button>
            <? }
            if ($position != 0) { ?>
                <button class="move_widget" name="direction" value="up"><img src="images/widget_move_up.png"></button>
            <? }
            if ($position != 2) { ?>
                <button class="move_widget" name="direction" value="down"><img src="images/widget_move_down.png"></button>
            <? }
            if ($widget['Side'] == 0) { ?>
                <button class="move_widget" name="direction" value="right"><img src="images/widget_move_right.png"></button>
            <? } ?>
            </form>
        </div>
    </div>
<?
}

function print_widget($widget, $position) {

    global $userrights, $dbcon, $language, $user, $bnetuser, $collection, $usersettings, $prj_status_compl, $lb_entries, $lb_entry_cutoff, $unreadcounter, $mynewcoms;
    
    switch($widget['Name']) {
        
        
   
    case "videos":
        
        
/* This is for channels crawling:
 *
 *
  This crawls through channels alternatively
                            $url = "https://www.youtube.com/feeds/videos.xml?channel_id=UCYNF03Z2-wUOWEFxHvpqVVA";
                            $video_feed = simplexml_load_file($url);
                            foreach($video_feed->entry as $item) {
                                $vid_title = $item->title;
                                $vid_link = str_replace('watch?v=', 'embed/', $item->link->attributes()->href);
                                break;
                            }
                            
                            <iframe width="220" height="130" src="<? echo $video_link ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            
 
 
function getFreshContent() {
    $html = "";
    $newsSource = array(
        array(
            "title" => "Channel",
            "url" => "https://www.youtube.com/feeds/videos.xml?channel_id=UCBcRF18a7Qf58cCRy5xuWwQ"
        ) /*,
        array(
            "title" => "CNN",
            "url" => "http://rss.cnn.com/rss/cnn_latest.rss"
        ),
        array(
            "title" => "Fox News",
            "url" => "http://feeds.foxnews.com/foxnews/latest"
        ) */  /*
    );
    function getFeed($url){
        $rss = simplexml_load_file($url);
        // echo "<pre>";
        // print_r($rss);
        $count = 0;
        $html .= '<ul>';
        foreach($rss->entry as $item) {
            $count++;
            if($count > 1){
                break;
            }
            $newlink = str_replace('watch?v=', 'embed/', $item->link->attributes()->href);
            echo $newlink;
            ?>1
            <iframe width="240" height="115" src="https://www.youtube.com/embed/eawyJ0M6FHM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            <iframe width="280" height="130" src="<? echo $newlink ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
           <?
            // print_r($item->link);
            // $html .= '<li><a href="'.htmlspecialchars($item->link).'">'.htmlspecialchars($item->title).'</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
    foreach($newsSource as $source) {
        $html .= '<h2>'.$source["title"].'</h2>';
        $html .= getFeed($source["url"]);
    }
    return $html;
}
        
      */  
        
        ?>
        
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #11aa9a">
                
                
                <? if ($userrights['Edit_Home_Videos'] == true) { ?>
                    <div class="home_widget_move" style="width: 20px; z-index: 999999">
                        <a data-remodal-target="modal_edit_vids"><img src="images/icon_pen_dark.png" style="width: 16px; cursor: pointer"></a>
                        <div class="remodal modalteam" data-remodal-id="modal_edit_vids" style="text-align: left">
                            <button data-remodal-action="close" class="remodal-close-team"></button>
                            <div style="background-color: #c9c9c9; display: table; width: 100%;">
                                <div style="display: table; width: 100%; padding: 15; font-size: 14px; font-family: MuseoSans-300">
                                    <b>Edit videos:</b><br><br>
                                    
                                    <form action="index.php" method="post">
                                        <input type="hidden" name="action" value="add_new_video">
                                        <input type="text" class="cominputbright" name="video_link">
                                        <input type="submit" class="comedit" value="Add video">
                                    </form>
                                    <br><br>
                                    
                                    <table id="tvideos" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:20 table-page-number:tvideospage table-page-count:tvideospages table-filtered-rowcount:tvideosfiltercount table-rowcount:tvideosallcount">
                                        <thead>
                                            <tr>
                                                <th class="petlistheaderfirst table-sortable:numeric" style="cursor: pointer">#</th>
                                                <th class="petlistheaderfirst"></th>
                                                <th class="petlistheaderfirst style="text-align: left"><input class="petselect" name="filter" size="25" id="channelfilter" onkeyup="Table.filter(this,this)"></th>
                                                <th class="petlistheaderfirst style="text-align: left"><input class="petselect" name="filter" size="25" id="titlefilter" onkeyup="Table.filter(this,this)"></th>
                                                <th class="petlistheaderfirst table-sortable:numeric" style="cursor: pointer">Date</th>
                                                <th class="petlistheaderfirst"></th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?
                                            $countvids = 1;
                                            $vids_db = mysqli_query($dbcon, "SELECT * FROM Home_Videos WHERE Deleted = 0 ORDER BY Date DESC") OR die(mysqli_error($dbcon));
                                            while($vid = mysqli_fetch_object($vids_db)) { ?>
                                                <tr class="admin">
                                                    <td><? echo $countvids ?></td>
                                                    <td style="padding-left: 8px"><a class="weblink" href="<? echo $vid->Link ?>" target="_blank">Open</a></td>
                                                    <td style="padding-left: 8px"><? echo htmlspecialchars($vid->Channel); ?></td>
                                                    <td style="padding-left: 8px"><? echo htmlspecialchars($vid->Title); ?></td>
                                                    
                                                    <td style="padding-left: 8px"><span name="time"><? echo $vid->Date ?></span></td>
                                                    <td style="padding-left: 8px">
                                                        <form action="index.php" method="post" style="display: inline">
                                                            <input type="hidden" name="action" value="delete_video">
                                                            <input type="hidden" name="video" value="<? echo $vid->id ?>">
                                                            <input type="submit" class="comdelete" value="Delete">
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?
                                                $countvids++;
                                            } ?>
                                        </tbody>
                                        
                                        <tfoot>
                                            <tr>
                                                <td align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                                <td align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="tvideospage"></span> / <span id="tvideospages"></span></div></td>
                                                <td align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <? } ?>
                
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner" style="padding: 10px">
                    <div class="home_main_title" style="margin: 0px; padding: 12px">Featured Videos</div>
                    <div class="home_main_content" style="margin: 5 0 0 0; padding-left: 0px;">
                        <table class="table_leaderboard">
                            <?
                            $count_videos = 0;
                            $new_vids_db = mysqli_query($dbcon, "SELECT * FROM Home_Videos WHERE Deleted = 0 ORDER BY Date DESC LIMIT 5");
                            while($new_vid = mysqli_fetch_object($new_vids_db)) {
                                $video_id = getYoutubeIdFromUrl($new_vid->Link);
                                
                                echo '<tr ';
                                if ($count_videos > 1) echo 'class="videos_row" style="display: none"';
                                echo '>';
                                echo '<td style="padding-left: 0px;"><b>'.htmlspecialchars($new_vid->Channel).'</td></tr>';
                                echo '<tr ';
                                if ($count_videos > 1) echo 'class="videos_row" style="display: none"';
                                echo '>';
                                echo '<td style="padding-left: 0px;">'.htmlspecialchars($new_vid->Title).'</td></tr>';
                                echo '<tr ';
                                if ($count_videos > 1) echo 'class="videos_row" style="display: none"';
                                echo '>';
                                echo '<td style="padding-bottom: 12px; padding-left: 0px"><iframe width="220" height="124" src="https://www.youtube.com/embed/'.$video_id.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></td>';
                                echo '</tr>';
                                $count_videos++;
                            }
                            echo '<tr class="videos_expander"><td onclick="table_expand(\'videos\')"><center><img style="width: 30px" src="https://www.wow-petguide.com/images/icon_home_down_g.png"></td></tr>';
                            echo '<tr class="videos_collapser" style="display: none"><td onclick="table_collapse(\'videos\')"><center><img style="width: 30px" src="https://www.wow-petguide.com/images/icon_home_up_g.png"></td></tr>';
                            ?>
                        </table>
                        <script>
                            function table_expand(rowname) {
                                $('.'+rowname+'_expander').hide();
                                $('.'+rowname+'_row').show();
                                $('.'+rowname+'_collapser').show();
                            }
                            function table_collapse(rowname) {
                                $('.'+rowname+'_expander').show();
                                $('.'+rowname+'_row').hide();
                                $('.'+rowname+'_collapser').hide();
                            }
                        </script>
                    </div>
                </div>
            </div>    
       <?
       break;













        
        
        
        case "quicklinks": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #11aa9a; background-color: #e3fffc">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Patch 8.3 Quicklinks</div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        <ul class="home_recruiting">
                            <li><a class="weblink" style="font-size: 14px" href="https://wow-petguide.com/?News=194">All 8.3 pet info</a></li>
                            <li><a class="weblink" style="font-size: 14px" href="https://www.wow-petguide.com/index.php?m=BlackrockDepths">Blackrock Depths</a></li>
                            <li><a class="weblink" style="font-size: 14px" href="https://www.wow-petguide.com/index.php?m=BattleforAzeroth">New World Quests</a></li>
                        </ul>
                    </div>
                </div>
            </div>            
        <? break;
        

        case "strategies": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Newest strategies
                        <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                         <table class="table_leaderboard">
                            <? $strats_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Published = 1 ORDER BY Id Desc LIMIT 15");
                             $count_new_strats = 0;
                                while ($this_new_strat = mysqli_fetch_object($strats_db)) {

                                    $strat_title = decode_sortingid(2,$this_new_strat->id);
                                    if ($strat_title['Family'] != "0" && $strat_title['Family'] != "") {
                                        switch ($strat_title['Family']) {
                                        case "1":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesHumanoid").")";
                                            break;
                                        case "2":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesDragonkin").")";
                                            break;
                                        case "3":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesFlying").")";
                                            break;
                                        case "4":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesUndead").")";
                                            break;
                                        case "5":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesCritter").")";
                                            break;
                                        case "6":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesMagic").")";
                                            break;
                                        case "7":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesElemental").")";
                                            break;
                                        case "8":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesBeast").")";
                                            break;
                                        case "9":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesAquatic").")";
                                            break;
                                        case "10":
                                            $categoryname = $strat_title[1]." ("._("PetFamiliesMechanical").")";
                                            break;
                                        }
                                    }
                                    else {
                                        $categoryname = $strat_title[1];
                                    }             


                               
                                    echo '<tr ';
                                    if ($count_new_strats > 5) echo 'class="newstrats_row" style="display: none"';
                                    echo '>';
                                    echo '<td><a class="weblink" target="_blank" style="font-size: 14px" href="?Strategy='.$this_new_strat->id.'">'.$categoryname.'</a><br></td>';
                                    echo '</tr>';
                                    $count_new_strats++;
                                }
                                echo '<tr class="newstrats_expander"><td onclick="newstrats_expand()"><img style="width: 30px; margin-left: 55px" src="https://www.wow-petguide.com/images/icon_home_down_g.png"></td></tr>';
                                echo '<tr class="newstrats_collapser" style="display: none"><td onclick="newstrats_collapse()"><img style="width: 30px; margin-left: 55px" src="https://www.wow-petguide.com/images/icon_home_up_g.png"></td></tr>';
                            ?>
                         </table>
                    <script>
                        function newstrats_expand() {
                            $('.newstrats_expander').hide();
                            $('.newstrats_row').show();
                            $('.newstrats_collapser').show();
                        }
                        function newstrats_collapse() {
                            $('.newstrats_expander').show();
                            $('.newstrats_row').hide();
                            $('.newstrats_collapser').hide();
                        }
                    </script>
                        </div>
                    </div>
                </div>
            </div>
        <? break;
        
        
        case "leaderboard": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner" style="padding: 10px">
                    <div class="home_main_title" style="margin: 0px; padding: 12px">Top Collections</div>
                        <div class="home_main_content" style="margin: 0px">
                            
                            <?
                            $lb_global_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard ORDER BY Unique_Pets DESC LIMIT $lb_entries");
                            $lb_us_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard WHERE Region = 'us' ORDER BY Unique_Pets DESC LIMIT $lb_entries");
                            $lb_eu_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard WHERE Region = 'eu' ORDER BY Unique_Pets DESC LIMIT $lb_entries");
                            $lb_kr_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard WHERE Region = 'kr' ORDER BY Unique_Pets DESC LIMIT $lb_entries");
                            $lb_tw_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard WHERE Region = 'tw' ORDER BY Unique_Pets DESC LIMIT $lb_entries");
                            
                            $col_region = "global";
                            if ($collection && $user) $col_region = $user->CharRegion;
                            if ($collection && $bnetuser) $col_region = $bnetuser->Region;

                            // Header buttons ?>
                            
                            <center>
                            <button id="lb_switch_global" class="lb_region_switch <? if ($col_region == "global") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('global')">Global</button>
                            <button id="lb_switch_us" class="lb_region_switch <? if ($col_region == "us") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('us')">US</button>
                            <button id="lb_switch_eu" class="lb_region_switch <? if ($col_region == "eu") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('eu')">EU</button>
                            <button id="lb_switch_kr" class="lb_region_switch <? if ($col_region == "kr") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('kr')">KR</button>
                            <button id="lb_switch_tw" class="lb_region_switch <? if ($col_region == "tw") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('tw')">TW</button>
                            </center>
                            <br>
                            
                            <?
                            // Rank Tables
                            foreach (['global', 'us', 'eu', 'kr', 'tw'] as $lb_region) {
                                switch ($lb_region) {
                                    case "global":
                                        $lb_db = $lb_global_db;
                                    break;
                                    case "us":
                                        $lb_db = $lb_us_db;
                                    break;
                                    case "eu":
                                        $lb_db = $lb_eu_db;
                                    break;
                                    case "kr":
                                        $lb_db = $lb_kr_db;
                                    break;
                                    case "tw":
                                        $lb_db = $lb_tw_db;
                                    break;
                                }
                                $lb_counter = 1;
                                $user_found = false;
                                echo '<div class="region_leaderboard" id="lb_'.$lb_region.'" ';
                                if ($lb_region != $col_region) echo 'style="display: none"';
                                echo '>';
                                echo '<table class="table_leaderboard"><tr><th style="padding-left: 0px; text-align: left"></th>';
                                if ($lb_region == "global") echo '<th></th>';
                                echo '<th style="text-align: left">Name</th><th style="text-align: right">Pets</th></tr>';
                                while($lb_entry = mysqli_fetch_object($lb_db)) {
                                    
                                    $lb_user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$lb_entry->User'");
                                    $lb_user = mysqli_fetch_object($lb_user_db);
                                    $tr_class = ' class="lb_regular_row lb_row';
                                    if ($collection && $lb_entry->User == $user->id) {
                                        $tr_class = ' class="lb_own_row lb_row';
                                        $user_found = true;
                                    }
                                    echo '<tr'.$tr_class;
                                    if ($lb_counter > $lb_entry_cutoff) echo ' lb_row_extra" style="display: none"';
                                    else echo '"';
                                    echo '>';
                                    echo '<td><b>'.$lb_counter.'.</td>';
                                    if ($lb_region == "global") echo '<td style="padding: 0px"><img style="width: 20px" src="images/lb_flag_'.$lb_user->Region.'.jpg"></td>';
                                    echo '<td><span style="text-decoration: none" class="username" rel="'.$lb_user->id.'" value="'.$user->id.'">';
                                    echo '<a target="_blank" href="?user='.$lb_user->id.'" class="creatorlink">'.$lb_user->Name.'</a></span></td>';
                                    echo '<td style="text-align: right">'.$lb_entry->Unique_Pets.'</td>';
                                    echo '</tr>';
                                    $lb_counter++;
                                }
                                if ($lb_counter > $lb_entry_cutoff+1) {
                                 echo '<tr class="lb_expander"><td colspan="5" style="text-align: center" onclick="lb_expand()"><img style="width: 30px" src="https://www.wow-petguide.com/images/icon_home_down_g.png"></td></tr>';
                                 echo '<tr class="lb_collapser" style="display: none"><td colspan="5" style="text-align: center" onclick="lb_collapse()"><img style="width: 30px" src="https://www.wow-petguide.com/images/icon_home_up_g.png"></td></tr>';
                                }
                                if ($collection && $user_found == false && ($lb_region == $col_region OR $lb_region == "global")) {     
                                    $user_rank = get_collection_rank($user->id,$lb_region);
                                    $lb_thisuser_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard WHERE User = '$user->id'");
                                    $lb_thisuser = mysqli_fetch_object($lb_thisuser_db);
                                    echo '<tr><td>...</td></tr>';
                                    echo '<tr class="lb_regular_row">';
                                    echo '<td><b>'.$user_rank.'.</td>';
                                    if ($lb_region == "global") echo '<td></td>';
                                    echo '<td><span style="text-decoration: none" class="username" rel="'.$user->id.'" value="'.$user->id.'">';
                                    echo '<a target="_blank" href="?user='.$user->id.'" class="creatorlink">'.$user->Name.'</a></span></td>';
                                    echo '<td style="text-align: right">'.$lb_thisuser->Unique_Pets.'</td>';
                                    echo '</tr>';
                                }
                                echo "</table>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    <script>
                        function lb_expand() {
                            $('.lb_expander').hide();
                            $('.lb_collapser').show();
                            $('.lb_row_extra').show();
                        }
                        function lb_collapse() {
                            $('.lb_expander').show();
                            $('.lb_collapser').hide();
                            $('.lb_row_extra').hide();
                        }
                        function lb_change_region(region) {
                            $('div.region_leaderboard').hide(500);
                            $('#lb_switch_global').removeClass('lb_region_switch_active');
                            $('#lb_switch_us').removeClass('lb_region_switch_active');
                            $('#lb_switch_eu').removeClass('lb_region_switch_active');
                            $('#lb_switch_kr').removeClass('lb_region_switch_active');
                            $('#lb_switch_tw').removeClass('lb_region_switch_active');
                            $('#lb_'+region).show(500);
                            $('#lb_switch_'+region).addClass('lb_region_switch_active');
                        }
                    </script>
                </div>
            </div>
        <? break;
        
       
        case "comments": ?>
        <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
            <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Newest comments
                        <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                            <?

                            $number_of_comments_to_show = 10;
                            $newcoms_toshow = $_POST['select_coms_toshow'];
                                  
                            if ($newcoms_toshow == 10 OR $newcoms_toshow == 20 OR $newcoms_toshow == 50 OR $newcoms_toshow == 100 OR $newcoms_toshow == 200) {
                                $number_of_comments_to_show = $newcoms_toshow;
                                if ($user) {
                                    set_settings($user,10,$newcoms_toshow);
                                    $usersettings['RecentComments'] = $newcoms_toshow;
                                }
                            }
                            if ($user && $usersettings['RecentComments']) {
                                $number_of_comments_to_show = $usersettings['RecentComments'];
                            }
                            if ($user && !$usersettings['RecentComments']) {
                                $number_of_comments_to_show = 10;
                            }
                            
                            $comprdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Deleted = 0 AND Category != '3' AND (Language = 'en_US' OR Language = '$language') AND Date > DATE_SUB(NOW(), INTERVAL 1 DAY)") OR die(mysqli_error($dbcon));
                            echo mysqli_num_rows($comprdb)." new in the last 24h<br>";
                            
                            
                            ?>
                        </div>
                        <a data-remodal-target="modal_comments"><button class="button_home_team">See latest <? echo $number_of_comments_to_show ?></button></a>
                        <div class="remodal modalteam" data-remodal-id="modal_comments" style="text-align: left">
                            <button data-remodal-action="close" class="remodal-close-team"></button>
                            
    
                            <div style="background-color: #c9c9c9; display: table; width: 100%;">
                                <div style="display: table; width: 100%; padding: 15 10 0 18; font-size: 14px; font-family: MuseoSans-300">
                                    Amount of comments to show:
                                    <form method="post" action="#modal_comments" style="display: inline">
                                        <select onchange="this.form.submit()" name="select_coms_toshow" class="petselect" required>
                                            <option class="petselect" value="10" <? if ($usersettings['RecentComments'] == 10 OR $usersettings['RecentComments'] == "") { echo " selected"; } ?>>10</option>
                                            <option class="petselect" value="20" <? if ($usersettings['RecentComments'] == 20) { echo " selected"; } ?>>20</option>
                                            <option class="petselect" value="50" <? if ($usersettings['RecentComments'] == 50) { echo " selected"; } ?>>50</option>
                                            <option class="petselect" value="100" <? if ($usersettings['RecentComments'] == 100) { echo " selected"; } ?>>100</option>
                                            <option class="petselect" value="200" <? if ($usersettings['RecentComments'] == 200) { echo " selected"; } ?>>200</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                        
                                <div style="display: table; width: 100%">
                                        <?
                                        $comprdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE Deleted = '0' AND Category != '3' AND (Language = 'en_US' OR Language = '$language') ORDER BY id DESC LIMIT $number_of_comments_to_show ");
                                        while($thiscom = mysqli_fetch_object($comprdb)) {
                                            if ($thiscom->SortingID == "38" AND $thiscom->Category == "0") { }    // Exclude maintenance page posts
                                            else {
                                                $stopcom = "false";
                                                if ($thiscom->Parent != "0") {
                                                    $parentdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE id = '$thiscom->Parent'");
                                                    $thisparent = mysqli_fetch_object($parentdb);
                                                    if ($thisparent->Language != "en_US" AND $thisparent->Language != $language) {
                                                        $stopcom = "true";
                                                    }
                                                }
                                                if ($stopcom != "true") {
                                                    $showdatebr = explode(" ", $thiscom->Date);
                                                    $showdatebr = explode("-", $showdatebr[0]);
                                                    if ($language != "en_US") {
                                                        $showdate = $showdatebr[2].".".$showdatebr[1].".".$showdatebr[0];
                                                    }
                                                    else {
                                                        $showdate = $showdatebr[1]."/".$showdatebr[2]."/".$showdatebr[0];
                                                    }
                            
                                                    $curr_date=strtotime(date("Y-m-d H:i:s"));
                                                    $the_date=strtotime($thiscom->Date);
                                                    $diff=floor(($curr_date-$the_date)/(60*60*24));
                                                    switch($diff)
                                                    {
                                                        case 0:
                                                            $showdate = "today";
                                                            break;
                                                        case 1:
                                                            $showdate = "yesterday";
                                                            break;
                                                        default:
                                                            $showdate = $diff." days ago";
                                                    }
                            
                                                    $categorycheck = decode_sortingid($thiscom->Category,$thiscom->SortingID);
                                                    if ($categorycheck['Family'] != "0" && $categorycheck['Family'] != "") {
                                                        switch ($categorycheck['Family']) {
                                                        case "1":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesHumanoid").")";
                                                            break;
                                                        case "2":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesDragonkin").")";
                                                            break;
                                                        case "3":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesFlying").")";
                                                            break;
                                                        case "4":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesUndead").")";
                                                            break;
                                                        case "5":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesCritter").")";
                                                            break;
                                                        case "6":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesMagic").")";
                                                            break;
                                                        case "7":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesElemental").")";
                                                            break;
                                                        case "8":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesBeast").")";
                                                            break;
                                                        case "9":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesAquatic").")";
                                                            break;
                                                        case "10":
                                                            $categoryname = $categorycheck[1]." ("._("PetFamiliesMechanical").")";
                                                            break;
                                                        }
                                                    }
                                                    else {
                                                        $categoryname = $categorycheck[1];
                                                    }
                            
                                                    $introoutput = stripslashes($thiscom->Comment);
                                                    $introoutput = htmlentities($introoutput, ENT_QUOTES, "UTF-8");
                                                    $introoutput = AutoLinkUrls($introoutput,'1','dark');
                                                    $introoutput = preg_replace("/\n/s", " - ", $introoutput);
                                                    if (strlen($introoutput) > "500") {
                                                        $showintro = substr($introoutput, 0, 500);
                                                        $cutter = "499";
                                                        while (substr($showintro, -1) != " ") {
                                                            $showintro = substr($introoutput, 0, "$cutter");
                                                            $cutter = $cutter - 1;
                                                        }
                                                            $showintro = $showintro."... <a class='home_rightlink' target='_blank' href='?Comment=".$thiscom->id."'>["._("UP_TTintrolong2")."]</a>";
                                                        }
                                                        else {
                                                            $showintro = $introoutput;
                                                        }
                                                     ?>
                            
                                                    <div style="max-width: 800px; margin: 10 20 0 20;">
                                                        <div style="padding-top: 9px; padding-right: 15px; float: right; font-size: 14px; opacity: 0.85;">
                            
                                                        </div>
                                                        <div style="padding-top: 2px;">
                            
                                                        <?
                                                        if ($thiscom->User != "0") {
                                                            $comuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$thiscom->User'");
                                                            $thisyuser = mysqli_fetch_object($comuserdb);
                                                            if ($thisyuser->UseWowAvatar == "0"){
                                                                $thisyusericon = 'src="https://www.wow-petguide.com/images/pets/'.$thisyuser->Icon.'.png"';
                                                            }
                                                            else if ($thisyuser->UseWowAvatar == "1"){
                                                                $thisyusericon = 'src="https://www.wow-petguide.com/images/userpics/'.$thisyuser->id.'.jpg"';
                                                            }
                            
                                                        echo '<span class="username tooltipstered" rel="'.$thisyuser->id.'" value="'.$user->id.'">';
                                                        echo '<img style="border-radius: 3px;" '.$thisyusericon.' width="25" height="25"></span>';
                                                        echo ' <span style="vertical-align: 7px" class="username tooltipstered" rel="'.$thisyuser->id.'" value="'.$user->id.'">';
                                                        echo '<a target="_blank" href="?user='.$thisyuser->id.'" class="comlinkdark" style="color: black">'.$thisyuser->Name.'</a></span>';
                                                        echo '<span style="vertical-align: 7px; font-size: 14px; font-family: MuseoSans-500"> wrote '.$showdate;
                                                        echo ' on: <a class="weblink" href="?Comment='.$thiscom->id.'">'.$categoryname.'</a></span>';
                                                        }
                                                        else {
                                                            $thisyusericon = 'src="https://www.wow-petguide.com/images/pets/'.$thiscom->Icon.'.png"';
                            
                                                        echo '<img style="border-radius: 3px;" '.$thisyusericon.' width="25" height="25">';
                                                        echo ' <span style="vertical-align: 7px;">';
                                                        echo '<p style="font-family: MuseoSans-500; font-size: 14px; font-weight: bold; color: black">'.$thiscom->Name.'</p></span>';
                                                        echo '<span style="vertical-align: 7px; font-size: 14px; font-family: MuseoSans-500"> wrote '.$showdate;
                                                        echo ' on: <a class="weblink" href="?Comment='.$thiscom->id.'">'.$categoryname.'</a></span>';
                                                        }
                                                        ?>
                                                        </div>
                                                        <div style="font-family: MuseoSans-300; font-size: 14px">
                                                            <? echo $showintro ?>
                                                        </div>
                                                    </div>
                                                <?
                                                }
                                            }
                                        } ?>
                                        <br><br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <? break;


        case "recruiting": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Looking for:</div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        <ul class="home_recruiting">
                            <? // <li><a class="weblink tt_recruiting" data-tooltip-content="#tt_news_writer">News Writer</a></li> ?>
                            <li><a class="weblink tt_recruiting" data-tooltip-content="#tt_translator">Translator</a> for
                                <ul class="home_recruiting">
                                    <li>Italian</li>
                                    <li>French</li>
                                    <li>Korean</li>
                                    <li>Chinese</li>
                                </ul>
                            </li>
                        </ul>
                        <? /* News Writer not required right now 
                        <div style="display: none">
                            <span id="tt_news_writer">Are you always up to date with everything going on in the pet world? <br>
                            Do you like to express yourself in a written form?
                            Would you like to post on one of the most visited pet battling pages in the world?<br>
                            If so, get in touch! I would love to hand over the News to someone who is eager to share updates on the pet battling world!<br>
                            You’ll have access to this very news section here and it would be your decision what and when to post.<br>
                            <br>
                            Contact: <a class="weblink" href="mailto:xufu@wow-petguide.com">E-Mail</a> - <a class="weblink" href="https://discord.gg/z4dxYUq" target="_blank">Discord</a>
                            </span>
                        </div>
                        */ ?>
                        
                        <div style="display: none">
                            <span id="tt_translator">Xu-Fu is set up to be fully localized into the languages WoW is translated as well. All it needs is volunteers to do so!<br>
                            If you speak English plus one of the listed languages and would like to help out, send an email or ping Aranesh on Discord!<br>
                            <br>
                            Contact: <a class="weblink" href="mailto:xufu@wow-petguide.com">E-Mail</a> - <a class="weblink" href="https://discord.gg/z4dxYUq" target="_blank">Discord</a>
                            </span>
                        </div>
                        
                        <a data-remodal-target="modalteam"><button class="button_home_team">See the current team</button></a>

                        <div class="remodal modalteam" data-remodal-id="modalteam" style="text-align: left">
                            <button data-remodal-action="close" class="remodal-close-team"></button>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><? echo _("LP_TeamAdmin") ?>:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                                    <div class="teamplayer">
                                        <div style="float: left; margin-right: 10px">
                                            <span class="username" style="text-decoration: none" rel="2" value="<? echo $user->id ?>">
                                                <img width="50" height="50" src="https://www.wow-petguide.com/images/userpics/2.jpg" style="border-radius: 10px;">
                                            </span>
                                        </div>
                                        <div style="padding-top: 13px; text-align: left">
                                            <span class="username" style="text-decoration: none" rel="2" value="<? echo $user->id ?>">
                                                <h5 style="color: black; font-size: 18px;">Aranesh</h5>
                                            </span>
                                        </div>
                                    </div>
                                        <div style="float: right">
                                            <img style="padding-right: 10px;" src="https://www.wow-petguide.com/images/xufu_team.png">
                                        </div>
                                </div>
                            </div>
                
                
                
                            <?
                            $modsdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '2' ORDER BY Name");
                            if (mysqli_num_rows($modsdb) > "0") {
                            ?>
                
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd">News Writer:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                
                                    <?
                                    while($thismod = mysqli_fetch_object($modsdb)) {
                                        if ($thismod->UseWowAvatar == "0"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/pets/'.$thismod->Icon.'.png"';
                                        }
                                        else if ($thismod->UseWowAvatar == "1"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/userpics/'.$thismod->id.'.jpg?lastmod?='.$thismod->IconUpdate.'"';
                                        }
                                        ?>
                
                                        <div class="teamplayer">
                                                <div style="float: left; margin-right: 10px">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <img width="50" height="50" <? echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><? echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                            <? } ?>
                
                
                
                            <?
                            $modsdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '3' ORDER BY Name");
                            if (mysqli_num_rows($modsdb) > "0") {
                            ?>
                
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd">Content Creator:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                
                                    <?
                                    while($thismod = mysqli_fetch_object($modsdb)) {
                                        if ($thismod->UseWowAvatar == "0"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/pets/'.$thismod->Icon.'.png"';
                                        }
                                        else if ($thismod->UseWowAvatar == "1"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/userpics/'.$thismod->id.'.jpg?lastmod?='.$thismod->IconUpdate.'"';
                                        }
                                        ?>
                
                                        <div class="teamplayer">
                                                <div style="float: left; margin-right: 10px">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <img width="50" height="50" <? echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><? echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                            <? } ?>
                
                
                
                            <?
                            $locadb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '4' ORDER BY Name");
                            if (mysqli_num_rows($locadb) > "0") {
                            ?>
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd">Strategy Curator:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                
                                    <?
                                    while($thismod = mysqli_fetch_object($locadb)) {
                                        if ($thismod->UseWowAvatar == "0"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/pets/'.$thismod->Icon.'.png"';
                                        }
                                        else if ($thismod->UseWowAvatar == "1"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/userpics/'.$thismod->id.'.jpg?lastmod?='.$thismod->IconUpdate.'"';
                                        }
                                        ?>
                
                                        <div class="teamplayer">
                                                <div style="float: left; margin-right: 10px">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <img width="50" height="50" <? echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><? echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                            <? } ?>
                
                
                            <?
                            $locadb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '5' ORDER BY Name");
                            if (mysqli_num_rows($locadb) > "0") {
                            ?>
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><? echo _("LP_TeamLoca") ?>:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                
                                    <?
                                    while($thismod = mysqli_fetch_object($locadb)) {
                                        if ($thismod->UseWowAvatar == "0"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/pets/'.$thismod->Icon.'.png"';
                                        }
                                        else if ($thismod->UseWowAvatar == "1"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/userpics/'.$thismod->id.'.jpg?lastmod?='.$thismod->IconUpdate.'"';
                                        }
                                        ?>
                
                                        <div class="teamplayer">
                                            <div style="float: left; margin-right: 10px">
                                                <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                    <img width="50" height="50" <? echo $thismodicon ?> style="border-radius: 10px;">
                                                </span>
                                            </div>
                                            <div style="padding-top: 13px; text-align: left">
                                                <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                    <h5 style="color: black; font-size: 18px;"><? echo $thismod->Name ?></h5>
                                                </span>
                                            </div>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                            <? } ?>
                
                
                
                
                            <?
                            $locadb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '9' ORDER BY Name");
                            if (mysqli_num_rows($locadb) > "0") {
                            ?>
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><? echo _("LP_TeamFormer") ?>:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                
                                    <?
                                    while($thismod = mysqli_fetch_object($locadb)) {
                                        if ($thismod->UseWowAvatar == "0"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/pets/'.$thismod->Icon.'.png"';
                                        }
                                        else if ($thismod->UseWowAvatar == "1"){
                                            $thismodicon = 'src="https://www.wow-petguide.com/images/userpics/'.$thismod->id.'.jpg?lastmod?='.$thismod->IconUpdate.'"';
                                        }
                                        ?>
                
                                        <div class="teamplayer">
                                                <div style="float: left; margin-right: 10px">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <img width="50" height="50" <? echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<? echo $thismod->id ?>" value="<? echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><? echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                            <? } ?>
                        </div>
            
            
                        <script>
                            $(document).ready(function() {
                                $('.tt_recruiting').tooltipster({
                                    maxWidth: '400',
                                    interactive: 'true',
                                    theme: 'tooltipster-smallnote'
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        <? break;

        case "notifications": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><? if (!$user) { echo _("LP_SB_Account"); } else { echo _("LP_SB_Notes"); } ?></div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        <? if (!$user) { ?>
                            <a href="#modallogin" onclick="hideloadingbnetlogin()"><button class="home_team" style="font-size: 14px; padding: 7 10 6 10;"><? echo _("LP_SB_BTLog") ?></button></a>
                        <? }
                        if ($user) {
                            if ($unreadcounter > "0") {
                                $homeunreadmsg = '<a href="?page=messages" class="weblink">'.$unreadcounter.' '._("LP_NF_NewMsgs").'</a>';
                            }
                            else {
                                $homeunreadmsg = $unreadcounter.' '._("LP_NF_NewMsgs");
                            }
                            if ($mynewcoms > "0") {
                                $homeunreadcoms = '<a href="?page=mycomments" class="weblink">'.$mynewcoms.' '._("LP_NF_NewCms").'</a>';
                            }
                            else {
                                $homeunreadcoms = $mynewcoms.' '._("LP_NF_NewCms");
                            }
                            $new_strategy_comments = User_unread_strategy_comment_count ($user);
                            if ($new_strategy_comments > "0") {
                                $home_unread_strats = '<a href="?page=strategies" class="weblink">'.$new_strategy_comments.' new strategy comments</a>';
                            }
                            else {
                                $home_unread_strats = "0 new strategy comments";
                            }
                        echo $homeunreadmsg."<br>";
                        echo $homeunreadcoms."<br>";
                        echo $home_unread_strats;
                        } ?>
                        <br>
                    </div>
                </div>
            </div>
        <? break;

        case "donations": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Support Xu-Fu</div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
<script type='text/javascript' src='https://ko-fi.com/widgets/widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Buy Aranesh a Ko-fi', '#2486d1', 'F1F61IQGC');kofiwidget2.draw();</script> 
                    </div>
                </div>
            </div>
        <? break;

        case "contact": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Get in Touch</div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">

                        <div style="padding-bottom: 4px"><a href="https://discord.gg/z4dxYUq" target="_blank"><img src="images/home_discord.png"></a></div>
                        <div style="float: left;padding-right: 4px"><a href="https://www.facebook.com/pages/XuFus-Pet-Guides/647394748729692" target="_blank"><img src="images/home_facebook.png"></a></div>
                        <div style="float: left"><a href="https://twitter.com/XuFusPetguide" target="_blank"><img src="images/home_twitter.png"></a></div>

                    </div>
                </div>
            </div>
        <? break;

        case "devupdate":
            $prj_status_miss = 100-$prj_status_compl; ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <? if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px">Development Update</div>
                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        Better localization tools.<br><br>
                        <b>Status:</b>
                        
                        <div style="height: 170; width: 170;">
                            <div style="margin-top: 75px; height: 50px; width: 170px; z-index: 20; position: absolute; font-size:16px"><center><b><? echo $prj_status_compl ?>%</b></center></div>
                            <div id="devUpdate" style="height: 170; width: 170; z-index: 5"></div>
                        </div>

                        <script>
                            
                        window.onload = function () {
                        
                        var chart = new CanvasJS.Chart("devUpdate", {
                            animationEnabled: true,
                            animationDuration: 800,
                            backgroundColor: null,
                            interactivityEnabled: true,
                            width: 170,
                            height: 170,
                            data: [{
                                type: "doughnut",
                                startAngle: 270,
                                innerRadius: "50%",
                                showInLegend: false,
                                toolTipContent: "<b>{label}:</b> {y} (#percent%)",
                                dataPoints: [
                                    { y: <? echo $prj_status_compl ?>, color: "#4186cb", },
                                    { y: <? echo $prj_status_miss ?>, color: "#d3d3d3", },
                                ]
                            }]
                            });
                            chart.render();
                        }
                        </script>
                        More details in the <a href="?m=DevLog" class="weblink" style="font-size: 14px">Devlog</a><br>
                    </div>
                </div>
            </div>
        <? break;  
    } 
}
