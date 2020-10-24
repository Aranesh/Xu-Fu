<?

function print_mover($widget, $position) {
 ?>
    <div class="home_widget_move">
        <div id="w_move_trigger_<?php echo $widget['Name'] ?>" class="w_move_trigger" data-lineid="<?php echo $widget['Name'] ?>">
            <img src="images/widget_move_trigger.png">
        </div>
        <div id="w_move_<?php echo $widget['Name'] ?>" style="float: right; opacity: 0" class="w_move_trigger" data-lineid="<?php echo $widget['Name'] ?>">
            <form action="index.php" method="post" style="display: inline">
            <input type="hidden" name="action" value="move_widget">
            <input type="hidden" name="widget_id" value="<?php echo $widget['ID'] ?>">
            <button class="move_widget" name="direction" value="hide"><img src="images/icon_hide_widget.png"></button>
            <?php if ($widget['Side'] == 1) { ?>
                <button class="move_widget" name="direction" value="left"><img src="images/widget_move_left.png"></button>
            <?php }
            if ($position != 0) { ?>
                <button class="move_widget" name="direction" value="up"><img src="images/widget_move_up.png"></button>
            <?php }
            if ($position != 2) { ?>
                <button class="move_widget" name="direction" value="down"><img src="images/widget_move_down.png"></button>
            <?php }
            if ($widget['Side'] == 0) { ?>
                <button class="move_widget" name="direction" value="right"><img src="images/widget_move_right.png"></button>
            <?php } ?>
            </form>
        </div>
    </div>
<?
}

function print_unhide_widget() {
 global $hidden_widgets;
 ?>
 <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
     <div class="home_menu_block_inner">
         <div class="home_main_title" style="margin: 0px"><?php echo __('Unhide Widgets'); ?></div>

         <div class="home_main_content" style="margin: 0; padding-left: 0px">
          <form action="index.php" method="post">
          <input type="hidden" name="action" value="unhide_widgets">
          <button class="button_home_team"><?php echo __('Unhide all widgets'); ?></button>
          </form>
         </div>
     </div>
 </div>
<?php }


function print_widget($widget, $position) {

    global $userrights, $dbcon, $language, $user, $bnetuser, $collection, $usersettings, $prj_status_compl, $lb_entries, $lb_entry_cutoff, $unreadcounter, $mynewcoms;
    
    switch($widget['Name']) {
        
    case "leveling":
     ?>
        
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #11aa9a">
                
                <div class="home_widget_move" style="width: 20px; z-index: 3">
                  <img id="rss_pet_xp" data-clipboard-text="https://www.wow-petguide.com/rss/pet_xp_feed.xml" class="icon_share" src="images/icon_rss.png" style="margin: 0px; width: 16px; cursor: pointer">
                    <div class="remtt" style="display:none" id="rss_pet_xp_confirm"><?php echo __("RSS Link copied to clipboard!") ?></div>
                    <script>
                    var btn = document.getElementById('rss_pet_xp');
                    var clipboard = new Clipboard(btn);
            
                    clipboard.on('success', function(e) {
                        console.log(e);
                            $('#rss_pet_xp_confirm').delay(0).fadeIn(500);
                            $('#rss_pet_xp_confirm').delay(1200).fadeOut(500);
                        });
                    clipboard.on('error', function(e) {
                        console.log(e);
                    });
                    </script>
                </div>
                
                
                <?php if ($userrights['Edit_Home_Leveling'] == true) { ?>
                    <div class="home_widget_move" style="width: 20px; z-index: 2">
                        <a data-remodal-target="modal_edit_tamers"><img src="images/icon_pen_dark.png" style="margin-left: 24px; width: 16px; cursor: pointer"></a>
                        <div class="remodal modalteam" data-remodal-id="modal_edit_tamers" style="text-align: left">
                            <button data-remodal-action="close" class="remodal-close-team"></button>
                            <div style="background-color: #c9c9c9; display: table; width: 100%;">
                                <div style="display: table; width: 100%; padding: 15; font-size: 14px; font-family: MuseoSans-300">
                                 
                                 <button id="add_day" onclick="switch_tamers_menu('add_day');" class="tamers_menu settings settingsactive" style="height: 32px; width: 130px; margin-bottom: 10px">Add Dailies</button>
                                 <button id="edit_days" onclick="switch_tamers_menu('edit_days');" class="tamers_menu settings" style="height: 32px; width: 130px; margin-bottom: 10px">Past Days</button>
                                 <button id="edit_tamers" onclick="switch_tamers_menu('edit_tamers');" class="tamers_menu settings" style="height: 32px; width: 130px; margin-bottom: 10px">Edit Tamers</button>
                                 <button id="pet_weeks" onclick="switch_tamers_menu('pet_weeks');" class="tamers_menu settings" style="height: 32px; width: 130px; margin-bottom: 10px">Pet Weeks</button>

                                    <div class="tamers_content" id="add_day_content">
                                     Add the Legion dailies of today (or select a different date).<br>
                                     The widget only shows the currently active quests.<br>
                                     If you enter multiple entries in a day, only the newest one will be shown.<br>
                                     <form action="index.php" method="post">
                                         <input type="hidden" name="action" value="add_dailies">
                                         <input type="text" value="<?php echo date('Y-m-d'); ?>" name="new_dailies" class="cominputbright" style="width: 200px" id="datepicker-13" required>
                                         
                                         <br><br>
                                         <?
                                          $tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_Tamers ORDER BY Tamer_id ASC") OR die(mysqli_error($dbcon));
                                          while($tamer = mysqli_fetch_object($tamers_db)) {
                                           $tamer_details = decode_sortingid(3,$tamer->Tamer_id); ?>
                                           <input type="checkbox" name="<?php echo $tamer->id ?>" value="<?php echo $tamer->Tamer_id ?>">
                                           <label for="<?php echo $tamer->id ?>"> <?php echo $tamer_details[1]; ?></label><br>
                                           <?php } ?>
                                           <br>
                                         <input type="checkbox" name="none" value="none">
                                         <label for="none"> No quests today</label><br>
                                         <br>

                                         <input type="submit" class="comedit" value="Add Quests">
                                         <script>
                                            $(function() {
                                               $( "#datepicker-13" ).datepicker({
                                                 dateFormat: "yy-mm-dd",
                                                 firstDay: 1
                                               });
                                            });
                                         </script>
                                     </form>
                                    </div>


                                    <div class="tamers_content" id="edit_days_content" style="display: none">
                                    You can remove entries here. To edit one, just delete it and submit a new one for the same day.<br>
                                    Only the latest 30 days are shown.<br>
                                    <br>
                                    <table id="edit_days_table" style="min-width: 400px; border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:30 table-page-number:editdayspage table-page-count:editdayspages table-filtered-rowcount:editdaysfiltercount table-rowcount:editdaysallcount">
                                         <thead>
                                             <tr>
                                                 <th class="petlistheaderfirst table-sortable:date" style="cursor: pointer">Date</th>
                                                 <th class="petlistheaderfirst table-sortable:date">Quests</th>
                                                 <th class="petlistheaderfirst"></th>
                                             </tr>
                                         </thead>
                                         
                                         <tbody>
                                             <?
                                             $days_db = mysqli_query($dbcon, "SELECT * FROM Home_LegionQuests ORDER BY Date DESC LIMIT 30") OR die(mysqli_error($dbcon));
                                             while($day = mysqli_fetch_object($days_db)) {
                                             $day_quests = explode('-', $day->Quests);
                                             
                                             ?>
                                                 <tr class="admin">
                                                     <td><span name="time"><?php echo $day->Date ?></span></td>
                                                     <td style="padding-left: 8px"><?
                                                     if ($day->Quests != "") {
                                                      foreach ($day_quests as $value) {
                                                       $tamer_details = decode_sortingid(3,$value);
                                                       echo $tamer_details[1].", ";
                                                      }
                                                     } ?></td>
                                                     <td style="padding-left: 8px">
                                                         <form action="index.php" method="post" style="display: inline">
                                                             <input type="hidden" name="action" value="delete_day">
                                                             <input type="hidden" name="legion_day" value="<?php echo $day->id ?>">
                                                             <input type="submit" class="comdelete" value="Delete">
                                                         </form>
                                                     </td>
                                                 </tr>
                                              <?php } ?>
                                         </tbody>
                                     </table>
                                    </div>
                                    
                                    
                                    
                                    <div class="tamers_content" id="edit_tamers_content" style="display: none">
                                    Change which tamers can show up in the widget and which strategy is linked.<br>
                                    Use the corresponding IDs for each of them. (To edit, just delete a tamer and re-add it).
                                    <br><br>
                                     <form action="index.php" method="post">
                                         <input type="hidden" name="action" value="add_new_tamer">
                                         <b>Fight ID: </b><input type="text" name="new_tamer_id" class="cominputbright" style="width: 100px" required>
                                         <br><b>Strategy ID: </b><input type="text" name="new_tamer_strategy" class="cominputbright" style="width: 100px" required>
                                         <br><input type="submit" class="comedit" value="Add Tamer">
                                     </form>
                                     <br><br>

                                     <table id="edit_tamers_table" style="min-width: 400px; border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:20 table-page-number:edittamerspage table-page-count:edittamerspages table-filtered-rowcount:edittamersfiltercount table-rowcount:edittamersallcount">
                                         <thead>
                                             <tr>
                                                 <th class="petlistheaderfirst table-sortable:numeric" style="cursor: pointer">#</th>
                                                 <th class="petlistheaderfirst table-sortable:date">Name</th>
                                                 <th class="petlistheaderfirst table-sortable:date">Linked Strategy</th>
                                                 <th class="petlistheaderfirst"></th>
                                             </tr>
                                         </thead>
                                         
                                         <tbody>
                                             <?
                                             $counttamers = 1;
                                             $tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_Tamers ORDER BY Tamer_id ASC") OR die(mysqli_error($dbcon));
                                             while($tamer = mysqli_fetch_object($tamers_db)) {
                                              $tamer_details = decode_sortingid(3,$tamer->Tamer_id);
                                              
                                              ?>
                                                 <tr class="admin">
                                                     <td><center><?php echo $counttamers ?></center></td>
                                                     <td style="padding-left: 8px"><a target="_blank" class="weblink" href="<?php echo $tamer_details[0]; ?>"><?php echo $tamer_details[1]; ?></a></td>
                                                     <td style="padding-left: 8px"><center><a target="_blank" class="weblink" href="?strategy=<?php echo $tamer->Fight_id; ?>"><?php echo $tamer->Fight_id; ?></a></td>
                                                     <td style="padding-left: 8px">
                                                         <form action="index.php" method="post" style="display: inline">
                                                             <input type="hidden" name="action" value="delete_tamer">
                                                             <input type="hidden" name="tamer" value="<?php echo $tamer->id ?>">
                                                             <input type="submit" class="comdelete" value="Delete">
                                                         </form>
                                                     </td>
                                                 </tr>
                                                 <?
                                                 $counttamers++;
                                             } ?>
                                         </tbody>
                                         
                                         <tfoot>
                                             <tr>
                                                 <td align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                                 <td align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="edittamerspage"></span> / <span id="edittamerspages"></span></div></td>
                                                 <td align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                             </tr>
                                         </tfoot>
                                     </table>
                                    </div>
                                    

                                    <div class="tamers_content" id="pet_weeks_content" style="display: none">
                                     Start date is always set to Tuesday (US). <br>
                                     EU times will be calculated automatically.
                                     <br><br>
                                     <form action="index.php" method="post">
                                         <input type="hidden" name="action" value="add_new_petweek">
                                         <input type="text" name="new_petweek" class="cominputbright" style="width: 200px" id="datepicker-12" required>
                                         <input type="submit" class="comedit" value="Add Week">
                                         <script>
                                            $(function() {
                                               $( "#datepicker-12" ).datepicker({
                                                 dateFormat: "yy-mm-dd",
                                                 firstDay: 1
                                               });
                                            });
                                         </script>
                                     </form>
                                     <br>

                                     <table id="petweeks" style="min-width: 400px; border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:20 table-page-number:petweekspage table-page-count:petweekspages table-filtered-rowcount:petweeksfiltercount table-rowcount:petweeksallcount">
                                         <thead>
                                             <tr>
                                                 <th class="petlistheaderfirst table-sortable:numeric" style="cursor: pointer">#</th>
                                                 <th class="petlistheaderfirst table-sortable:date">Start</th>
                                                 <th class="petlistheaderfirst table-sortable:date">End</th>
                                                 <th class="petlistheaderfirst"></th>
                                             </tr>
                                         </thead>
                                         
                                         <tbody>
                                             <?
                                             $countweeks = 1;
                                             $petweeks_db = mysqli_query($dbcon, "SELECT * FROM Home_PetWeeks WHERE Start >= (CURRENT_DATE - INTERVAL 14 DAY) ORDER BY Start ASC") OR die(mysqli_error($dbcon));
                                             while($petweek = mysqli_fetch_object($petweeks_db)) { ?>
                                                 <tr class="admin">
                                                     <td><center><?php echo $countweeks ?></center></td>
                                                     <td style="padding-left: 8px"><center><span name="time"><?php echo $petweek->Start; ?></span></td>
                                                     <td style="padding-left: 8px"><center><span name="time"><?php echo date('Y-m-d', strtotime($petweek->Start. ' + 7 days')); ?></span></td>
                                                     <td style="padding-left: 8px">
                                                         <form action="index.php" method="post" style="display: inline">
                                                             <input type="hidden" name="action" value="delete_petweek">
                                                             <input type="hidden" name="petweek" value="<?php echo $petweek->id ?>">
                                                             <input type="submit" class="comdelete" value="Delete">
                                                         </form>
                                                     </td>
                                                 </tr>
                                                 <?
                                                 $countweeks++;
                                             } ?>
                                         </tbody>
                                         
                                         <tfoot>
                                             <tr>
                                                 <td align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                                 <td align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="petweekspage"></span> / <span id="petweekspages"></span></div></td>
                                                 <td align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                             </tr>
                                         </tfoot>
                                     </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <?php if ($user) print_mover($widget, $position);
                
                // EU Calculations
                $eustart = strtotime("2017-01-28 08:00:00");
                $end = strtotime(date('Y-m-d H:i:s'));
                $eudiff = ((($end-$eustart)/86400) % 15);

                if ($eudiff == 0) {
                 $eu_squirt_today = TRUE;
                 $eu_squirt_date = date('Y-m-d H:i:s');
                }
                else {
                 $subtract_days = 15;
                 if (date('H') < 9) {
                  $subtract_days = 14;
                 }
                 $eu_squirt_togo = $subtract_days-$eudiff;               
                 $eu_squirt_date = date("Y-m-d H:i:s", strtotime("+".$eu_squirt_togo." day"));
                 $eu_squirt_today = FALSE;
                }
                
                // US Calculations
                // $end = strtotime('2020-06-18 18:00:00');
                $usstart = strtotime("2017-02-03 16:00:00");
                $usdiff = ((($end-$usstart)/86400) % 15);

                if ($usdiff == 0) {
                 $us_squirt_today = TRUE;
                 $us_squirt_date = date('Y-m-d H:i:s');
                }
                else {
                 $subtract_days = 15;
                 if (date('H') < 16) {
                  $subtract_days = 14;
                 }
                 $us_squirt_togo = $subtract_days-$usdiff;             
                 $us_squirt_date = date("Y-m-d H:i:s", strtotime("+".$us_squirt_togo." day"));
                 $us_squirt_today = FALSE;
                }
                
                // TW Calculations
                $twstart = strtotime("2020-09-25 01:00:00");
                $twdiff = ((($end-$twstart)/86400) % 15);

                if ($twdiff == 0) {
                 $tw_squirt_today = TRUE;
                 $tw_squirt_date = date('Y-m-d H:i:s');
                }
                else {
                 $subtract_days = 15;
                 if (date('H') < 1) {
                  $subtract_days = 14;
                 }
                 $tw_squirt_togo = $subtract_days-$twdiff;             
                 $tw_squirt_date = date("Y-m-d H:i:s", strtotime("+".$tw_squirt_togo." day"));
                 $tw_squirt_today = FALSE;
                }
                
                // Check against upcoming pet bonus weeks
                $petweeks_db = mysqli_query($dbcon, "SELECT * FROM Home_PetWeeks WHERE Start >= (CURRENT_DATE - INTERVAL 10 DAY) ORDER BY Start ASC LIMIT 3") OR die(mysqli_error($dbcon));
                 while($petweek = mysqli_fetch_object($petweeks_db)) {
                  $eu_petweek_diff = ((strtotime($petweek->Start)-strtotime($eu_squirt_date))/60/60/24)+8;
                  if ($eu_petweek_diff > 0 && $eu_petweek_diff < 7) {
                   $eu_super_squirt = TRUE;
                  }
                  $us_petweek_diff = ((strtotime($petweek->Start)-strtotime($us_squirt_date))/60/60/24)+7;
                  if ($us_petweek_diff > 0 && $us_petweek_diff < 7) {
                   $us_super_squirt = TRUE;
                  }
                  $tw_petweek_diff = ((strtotime($petweek->Start)-strtotime($tw_squirt_date))/60/60/24)+9;
                  if ($tw_petweek_diff > 0 && $tw_petweek_diff < 7) {
                   $tw_super_squirt = TRUE;
                  }
                 }
                 
                 
                 if ($collection && $user) $level_region = $user->CharRegion;
                 if ($collection && $bnetuser) $level_region = $bnetuser->Region;
                 if ($level_region != "us" && $level_region != "eu" && $level_region != "tw") $level_region = "us";
                 
                // Array of all legion quests
                $all_tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_Tamers") OR die(mysqli_error($dbcon));
                 while($this_tamer = mysqli_fetch_object($all_tamers_db)) {
                  $all_tamers[$this_tamer->Tamer_id] = $this_tamer->Fight_id;
                 }
                ?>
                
         
         
         
         
         
         <div class="home_main_title" style="margin: 0px; padding: 22px 12px 2px 22px; background-color; display: block"><?php echo __('Level Opportunities'); ?></div>
         <div class="home_menu_block_inner" style="padding: 10px">
     
              <center>
               <button id="xp_switch_us" class="lb_region_switch <?php if ($level_region == "us") echo "lb_region_switch_active"; ?>" onclick="xp_change_region('us')"><?php echo __('US'); ?></button>
               <button id="xp_switch_eu" class="lb_region_switch <?php if ($level_region == "eu") echo "lb_region_switch_active"; ?>" onclick="xp_change_region('eu')"><?php echo __('EU'); ?></button>
               <button id="xp_switch_tw" class="lb_region_switch <?php if ($level_region == "tw") echo "lb_region_switch_active"; ?>" onclick="xp_change_region('tw')"><?php echo __('TW'); ?></button>
              </center>
                    
                    
               <div id="eu_level_widget" <?php if ($level_region != "eu") echo 'style="display: none"'; ?>>     
                    <div class="home_main_content" style="line-height: 20px; color: #ffffff; margin: 5 0 0 0; padding: 0px; background-color: #1c1c24; border-radius: 7px">
                        <div style="margin: 7px; position: relative;">
                         <div style="position: absolute; top: 0px; right: 0px"><a href="?Strategy=731"><img onmouseover="squirt_onHover('eu');" onmouseout="squirt_offHover('eu');" id="squirt_image_eu" src="/images/home_squirt.png" style="width: 60px"></a></div>
                         <?php if ($eu_squirt_today != True) { ?><?php echo __('Next'); ?> <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('Day'); ?>:<br><?
                         }
                         
                         if ($eu_squirt_today == True) {
                          if ($eu_super_squirt == TRUE) { ?>
                           <b><div style="padding: 6px 0px 0px 5px"><font color="#fff000">Today</font> is <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('day'); ?> <br> <?php echo __('with'); ?> <font color="#fff000"><?php echo __('Super XP!'); ?></font><br><br></div>
                          <?php }
                          else { ?>
                          <b><div style="padding: 15px 0px 0px 5px"><?php echo __('Today is'); ?> <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('day!'); ?><br><br></div>
                         <?php }
                         }
                         else {
                          ?><b><span name="time"><?php echo $eu_squirt_date ?></span><?php if ($eu_super_squirt == TRUE) { echo ' <font color="#fff000">Super XP!</font>'; } ?></b><br><?
                         } ?>
                         <?php if ($eu_squirt_today!= True) {
                          $eu_squirt_date_splits = explode('-',$eu_squirt_date);
                          // print_r($eu_squirt_date_splits);
                          $end_date = $eu_squirt_date_splits[2]+1;
                          $glink = 'https://calendar.google.com/calendar/r/eventedit?text=WoW+Pet+Battles+Squirt+Day&dates=';
                          $glink = $glink.$eu_squirt_date_splits[0].$eu_squirt_date_splits[1].$eu_squirt_date_splits[2].'/'.$eu_squirt_date_splits[0].$eu_squirt_date_splits[1].$end_date;
                          $glink = $glink.'&details=Great+opportunity+to+level+your+pets+in+WoW+with+Squirt:+https://www.wow-petguide.com/index.php?Strategy=731';
                          ?> <a class="home" target="_blank" style="font-size: 12px" href="<?php echo $glink ?>"><?php echo __('Add to calendar'); ?></a><?
                         } ?>
                        </div>
                    </div>
                    
                    
                    <div class="home_main_content" style="line-height: 20px; color: #ffffff; margin: 10 0 0 0; padding: 0px; background-color: #020a4a; border-radius: 7px">
                        <div style="margin: 7px">
                         <?php // Calculations
                         $time_today = strtotime(date('Y-m-d H:i:s'));
                         $petweeks_db = mysqli_query($dbcon, "SELECT * FROM Home_PetWeeks WHERE Start >= (CURRENT_DATE - INTERVAL 10 DAY) ORDER BY Start ASC LIMIT 2") OR die(mysqli_error($dbcon));
                         $petweek = mysqli_fetch_object($petweeks_db);
                         
                         $petweek_diff = ((strtotime($petweek->Start)-$time_today)/60/60/24)+8;
                         if ($petweek_diff > 0 && $petweek_diff < 7) {
                          $eu_petweek = TRUE;
                         }
                         
                         if ($eu_petweek != TRUE) { // ATTENTION: Reduce + X day by one for US!
                          if ($petweek_diff < 0) { // The petweek has just passed, skipping to the next one
                           $petweek = mysqli_fetch_object($petweeks_db);
                          }
                          echo __('Next Pet Bonus Week').':<br>';
                          echo '<b><span name="time">'.date("Y-m-d H:i:s", strtotime($petweek->Start." + 1 day")).'</span> - <span name="time">'.date("Y-m-d H:i:s", strtotime($petweek->Start." + 8 day")).'</span></b>';
                         }
                         else { ?>
                          <?php echo __('Pet Bonus Week active'); ?>!
                          <table cellpadding="0" cellspacing="0" style="width: 100%; border-radius: 20px; margin-top: 5px; background-color: #1d2d7d">
                           <tr>
                            <?
                           for($i=1;$i<8;$i++) { // ATTENTION: For US let i start at 0 and go to 7
                            $this_date = date("Y-m-d H:i:s", strtotime($petweek->Start." + ".$i." day"));
                            $this_date = explode("-", $this_date);
                            $this_date = explode(" ", $this_date[2]);
                            $bgcolor = "";
                            if ($this_date[0] == date("d")) $bgcolor = "fd2472";
                            echo '<td style="text-align: center; padding: 8 4 8 4; border-radius: 50px; background-color: '.$bgcolor.'"><p style="font-size: 12px;" class="ut_role">'.$this_date[0].'</p></td>';
                            } ?>
                            
                           </tr>
                          </table>
                         <?php }
                         ?>
                        </div>
                    </div>
                    
                    <div class="home_main_content" style="background-image: url('images/home_legion_bg.png'); line-height: 20px; color: #ffffff; margin: 10 0 0 0; padding: 0px; background-color: #022f00; border-radius: 7px">
                        <div style="margin: 7px">
                         <?php // EU
                         $time_today = date('Y-m-d H:i:s');
                         $hours_today = date('Hi');
                         if ($hours_today > 900) { 
                          $target_date = date("Y-m-d");
                         }
                         else {
                          $target_date = date("Y-m-d", strtotime("- 1 day"));
                         }
                         $legion_tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_LegionQuests WHERE Date >= '$target_date' AND Date < '$target_date' + INTERVAL 1 DAY ORDER BY id DESC LIMIT 1") OR die(mysqli_error($dbcon));
                         if (mysqli_num_rows($legion_tamers_db) < 1) {
                          echo __('Active Legion Quests').":<br>";
                          echo '<div style="margin-top: 6px">';
                          echo __('Will be added shortly.');
                          echo "</div>";
                         }
                         else {
                          $eu_tamers = mysqli_fetch_object($legion_tamers_db);
                          if ($eu_tamers->Quests == "") {
                           echo __('Legion Quests').":<br>";
                           echo '<div style="margin-top: 6px">';
                           echo __('No quests for leveling active today.');
                           echo "</div>";
                          }
                          else {
                           $eu_tamers = explode('-', $eu_tamers->Quests);
  
                           echo __('Active Legion Quests').":<br>";
                           echo '<div style="margin-top: 6px; margin-bottom: 10px"><ul class="home_recruiting" style="margin-left: 5px">';
                           foreach ($eu_tamers as $tamer) {
                            $tamer_details = decode_sortingid(3,$tamer);
                            echo '<li><a class="home_legion" href="?Strategy='.$all_tamers[$tamer].'">'.$tamer_details[1].'</a></li>';
                           }
                           echo "</ul></div>";
                          }
                         }
                          ?>
                        </div>
                    </div>
                </div>
                        
                        

               <div id="us_level_widget" <?php if ($level_region != "us") echo 'style="display: none"'; ?>>     
                    <div class="home_main_content" style="line-height: 20px; color: #ffffff; margin: 5 0 0 0; padding: 0px; background-color: #1c1c24; border-radius: 7px">
                        <div style="margin: 7px; position: relative;">
                         <div style="position: absolute; top: 0px; right: 0px"><a href="?Strategy=731"><img onmouseover="squirt_onHover('us');" onmouseout="squirt_offHover('us');" id="squirt_image_us" src="/images/home_squirt.png" style="width: 60px"></a></div>
                         <?php if ($us_squirt_today != True) { ?><?php echo __('Next'); ?> <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('Day'); ?>:<br><?
                         }
                         
                         if ($us_squirt_today == True) {
                          if ($us_super_squirt == TRUE) { ?>
                           <b><div style="padding: 6px 0px 0px 5px"><font color="#fff000">Today</font> is <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('day'); ?> <br> <?php echo __('with'); ?> <font color="#fff000"><?php echo __('Super XP!'); ?></font><br><br></div>
                          <?php }
                          else { ?>
                          <b><div style="padding: 15px 0px 0px 5px"><?php echo __('Today is'); ?> <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('day!'); ?><br><br></div>
                         <?php }
                         }
                         else {
                          ?><b><span name="time"><?php echo $us_squirt_date ?></span><?php if ($us_super_squirt == TRUE) { echo ' <font color="#fff000">Super XP!</font>'; } ?></b><br><?
                         } ?>
                         <?php if ($us_squirt_today!= True) {
                          $us_squirt_date_splits = explode('-',$us_squirt_date);
                          $end_date = $us_squirt_date_splits[2]+1;
                          $glink = 'https://calendar.google.com/calendar/r/eventedit?text=WoW+Pet+Battles+Squirt+Day&dates=';
                          $glink = $glink.$us_squirt_date_splits[0].$us_squirt_date_splits[1].$us_squirt_date_splits[2].'/'.$us_squirt_date_splits[0].$us_squirt_date_splits[1].$end_date;
                          $glink = $glink.'&details=Great+opportunity+to+level+your+pets+in+WoW+with+Squirt:+https://www.wow-petguide.com/index.php?Strategy=731';
                          ?> <a class="home" target="_blank" style="font-size: 12px" href="<?php echo $glink ?>"><?php echo __('Add to calendar'); ?></a><?
                         } ?>
                        </div>
                    </div>
                    
                    <div class="home_main_content" style="line-height: 20px; color: #ffffff; margin: 10 0 0 0; padding: 0px; background-color: #020a4a; border-radius: 7px">
                        <div style="margin: 7px">
                         <?php // Calculations
                         $time_today = strtotime(date('Y-m-d H:i:s'));
                         
                         $petweek_diff = ((strtotime($petweek->Start)-$time_today)/60/60/24)+7.3; // adjusted for US quest start at 3PM EU time
                         if ($petweek_diff > 0 && $petweek_diff < 7) {
                          $us_petweek = TRUE;
                         }
                         
                         if ($us_petweek != TRUE) {
                          echo __('Next Pet Bonus Week').':<br>';
                          echo '<b><span name="time">'.date("Y-m-d H:i:s", strtotime($petweek->Start)).'</span> - <span name="time">'.date("Y-m-d H:i:s", strtotime($petweek->Start." + 7 day")).'</span></b>';
                         }
                         else { ?>
                          <?php echo __('Pet Bonus Week active'); ?>!
                          <table cellpadding="0" cellspacing="0" style="width: 100%; border-radius: 20px; margin-top: 5px; background-color: #1d2d7d">
                           <tr>
                            <?
                           for($i=0;$i<7;$i++) {
                            $this_date = date("Y-m-d H:i:s", strtotime($petweek->Start." + ".$i." day"));
                            $this_date = explode("-", $this_date);
                            $this_date = explode(" ", $this_date[2]);
                            $bgcolor = "";
                            if ($this_date[0] == date("d")) $bgcolor = "fd2472";
                            echo '<td style="text-align: center; padding: 8 4 8 4; border-radius: 50px; background-color: '.$bgcolor.'"><p style="font-size: 12px;" class="ut_role">'.$this_date[0].'</p></td>';
                            } ?>
                            
                           </tr>
                          </table>
                         <?php }
                         ?>
                        </div>
                    </div>
                    
                    <div class="home_main_content" style="background-image: url('images/home_legion_bg.png'); line-height: 20px; color: #ffffff; margin: 10 0 0 0; padding: 0px; background-color: #022f00; border-radius: 7px">
                        <div style="margin: 7px">
                         <?
                         $time_today = date('Y-m-d H:i:s');
                         $hours_today = date('Hi');
                         if ($hours_today > 1700) {
                          $target_date = date("Y-m-d");
                         }
                         else {
                          $target_date = date("Y-m-d", strtotime("- 1 day"));
                         }
                         $legion_tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_LegionQuests WHERE Date >= '$target_date' AND Date < '$target_date' + INTERVAL 1 DAY ORDER BY id DESC LIMIT 1") OR die(mysqli_error($dbcon));
                         if (mysqli_num_rows($legion_tamers_db) < 1) {
                          echo __('Active Legion Quests').":<br>";
                          echo '<div style="margin-top: 6px">';
                          echo __('Will be added shortly.');
                          echo "</div>";
                         }
                         else {
                          $this_tamers = mysqli_fetch_object($legion_tamers_db);
                          if ($this_tamers->Quests == "") {
                           echo __('Legion Quests').":<br>";
                           echo '<div style="margin-top: 6px">';
                           echo __('No quests for leveling active today.');
                           echo "</div>";
                          }
                          else {
                           $this_tamers = explode('-', $this_tamers->Quests);
  
                           echo __('Active Legion Quests').":<br>";
                           echo '<div style="margin-top: 6px; margin-bottom: 10px"><ul class="home_recruiting" style="margin-left: 5px">';
                           foreach ($this_tamers as $tamer) {
                            $tamer_details = decode_sortingid(3,$tamer);
                            echo '<li><a class="home_legion" href="?Strategy='.$all_tamers[$tamer].'">'.$tamer_details[1].'</a></li>';
                           }
                           echo "</div>";
                          }
                         }
                          ?>
                        </div>
                    </div>
                </div>


               <div id="tw_level_widget" <?php if ($level_region != "tw") echo 'style="display: none"'; ?>>     
                    <div class="home_main_content" style="line-height: 20px; color: #ffffff; margin: 5 0 0 0; padding: 0px; background-color: #1c1c24; border-radius: 7px">
                        <div style="margin: 7px; position: relative;">
                         <div style="position: absolute; top: 0px; right: 0px"><a href="?Strategy=731"><img onmouseover="squirt_onHover('tw');" onmouseout="squirt_offHover('tw');" id="squirt_image_tw" src="/images/home_squirt.png" style="width: 60px"></a></div>
                         <?php if ($tw_squirt_today != True) { ?><?php echo __('Next'); ?> <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('Day'); ?>:<br><?
                         }
                         
                         if ($tw_squirt_today == True) {
                          if ($tw_super_squirt == TRUE) { ?>
                           <b><div style="padding: 6px 0px 0px 5px"><font color="#fff000">Today</font> is <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('day'); ?> <br> <?php echo __('with'); ?> <font color="#fff000"><?php echo __('Super XP!'); ?></font><br><br></div>
                          <?php }
                          else { ?>
                          <b><div style="padding: 15px 0px 0px 5px"><?php echo __('Today is'); ?> <a href="?Strategy=731" class="home"><?php echo __('Squirt'); ?></a> <?php echo __('day!'); ?><br><br></div>
                         <?php }
                         }
                         else {
                          ?><b><span name="time"><?php echo $tw_squirt_date ?></span><?php if ($tw_super_squirt == TRUE) { echo ' <font color="#fff000">Super XP!</font>'; } ?></b><br><?
                         } ?>
                         <?php if ($tw_squirt_today!= True) {
                          $tw_squirt_date_splits = explode('-',$tw_squirt_date);
                          $end_date = $tw_squirt_date_splits[2]+1;
                          $glink = 'https://calendar.google.com/calendar/r/eventedit?text=WoW+Pet+Battles+Squirt+Day&dates=';
                          $glink = $glink.$tw_squirt_date_splits[0].$tw_squirt_date_splits[1].$tw_squirt_date_splits[2].'/'.$tw_squirt_date_splits[0].$tw_squirt_date_splits[1].$end_date;
                          $glink = $glink.'&details=Great+opportunity+to+level+your+pets+in+WoW+with+Squirt:+https://www.wow-petguide.com/index.php?Strategy=731';
                          ?> <a class="home" target="_blank" style="font-size: 12px" href="<?php echo $glink ?>"><?php echo __('Add to calendar'); ?></a><?
                         } ?>
                        </div>
                    </div>
                    
                    
                    <div class="home_main_content" style="line-height: 20px; color: #ffffff; margin: 10 0 0 0; padding: 0px; background-color: #020a4a; border-radius: 7px">
                        <div style="margin: 7px">
                         <?php // Calculations
                         $time_today = strtotime(date('Y-m-d H:i:s'));
                          
                         $petweek_diff = ((strtotime($petweek->Start)-$time_today)/60/60/24)+8.7; // adjusted for TW quest start at 1AM EU time
                         if ($petweek_diff > 0 && $petweek_diff < 7) {
                          $tw_petweek = TRUE;
                         }
                         
                         if ($tw_petweek != TRUE) {
                          echo __('Next Pet Bonus Week').':<br>';
                          echo '<b><span name="time">'.date("Y-m-d H:i:s", strtotime($petweek->Start." + 2 day")).'</span> - <span name="time">'.date("Y-m-d H:i:s", strtotime($petweek->Start." + 9 day")).'</span></b>';
                         }
                         else { ?>
                          <?php echo __('Pet Bonus Week active'); ?>!
                          <table cellpadding="0" cellspacing="0" style="width: 100%; border-radius: 20px; margin-top: 5px; background-color: #1d2d7d">
                           <tr>
                            <?
                           for($i=2;$i<9;$i++) {
                            $this_date = date("Y-m-d H:i:s", strtotime($petweek->Start." + ".$i." day"));
                            $this_date = explode("-", $this_date);
                            $this_date = explode(" ", $this_date[2]);
                            $bgcolor = "";
                            if ($this_date[0] == date("d")) $bgcolor = "fd2472";
                            echo '<td style="text-align: center; padding: 8 4 8 4; border-radius: 50px; background-color: '.$bgcolor.'"><p style="font-size: 12px;" class="ut_role">'.$this_date[0].'</p></td>';
                            } ?>
                            
                           </tr>
                          </table>
                         <?php }
                         ?>
                        </div>
                    </div>
                    
                    <div class="home_main_content" style="background-image: url('images/home_legion_bg.png'); line-height: 20px; color: #ffffff; margin: 10 0 0 0; padding: 0px; background-color: #022f00; border-radius: 7px">
                        <div style="margin: 7px">
                         <?
                         $time_today = date('Y-m-d H:i:s');
                         $hours_today = date('Hi');
                         if ($hours_today > 100) {
                          $target_date = date("Y-m-d");
                         }
                         else {
                          $target_date = date("Y-m-d", strtotime("- 1 day"));
                         }
                         $legion_tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_LegionQuests WHERE Date >= '$target_date' AND Date < '$target_date' + INTERVAL 1 DAY ORDER BY id DESC LIMIT 1") OR die(mysqli_error($dbcon));
                         if (mysqli_num_rows($legion_tamers_db) < 1) {
                          echo __('Active Legion Quests').":<br>";
                          echo '<div style="margin-top: 6px">';
                          echo __('Will be added shortly.');
                          echo "</div>";
                         }
                         else {
                          $this_tamers = mysqli_fetch_object($legion_tamers_db);
                          if ($this_tamers->Quests == "") {
                           echo __('Legion Quests').":<br>";
                           echo '<div style="margin-top: 6px">';
                           echo __('No quests for leveling active today.');
                           echo "</div>";
                          }
                          else {
                           $this_tamers = explode('-', $this_tamers->Quests);
  
                           echo __('Active Legion Quests').":<br>";
                           echo '<div style="margin-top: 6px; margin-bottom: 10px"><ul class="home_recruiting" style="margin-left: 5px">';
                           foreach ($this_tamers as $tamer) {
                            $tamer_details = decode_sortingid(3,$tamer);
                            echo '<li><a class="home_legion" href="?Strategy='.$all_tamers[$tamer].'">'.$tamer_details[1].'</a></li>';
                           }
                           echo "</div>";
                          }
                         }
                          ?>
                        </div>
                    </div>
                </div>
               
               
               

                    <div style="padding-top: 14px; display: table">
                      <a href="?m=Powerleveling"><img src="images/home_level_guide.png" style="width: 25px; display: inline-block"></a>
                     <div style="margin-top: 5px; display: inline-block; vertical-align: top;">
                      <a href="?m=Powerleveling" class="wowhead" style="font-size: 14px; white-space: nowrap"><?php echo __('Powerleveling Guide'); ?></a>
                     </div>
                    </div>
                    

                    

                </div>
            </div>
            
            
            <script>
             function squirt_onHover(c) {
               $("#squirt_image_"+c).attr('src', 'images/home_squirt_hover.png');
             }
             
             function squirt_offHover(c) {
               $("#squirt_image_"+c).attr('src', 'images/home_squirt.png');
             }
             
             function xp_change_region(region) {
              if (region == "eu") {
               $('#us_level_widget').hide(500);
               $('#tw_level_widget').hide(500);
               $('#eu_level_widget').show(500);
               $('#xp_switch_us').removeClass('lb_region_switch_active');
               $('#xp_switch_tw').removeClass('lb_region_switch_active');
               $('#xp_switch_eu').addClass('lb_region_switch_active');
              }
              if (region == "us") {
               $('#us_level_widget').show(500);
               $('#tw_level_widget').hide(500);
               $('#eu_level_widget').hide(500);
               $('#xp_switch_eu').removeClass('lb_region_switch_active');
               $('#xp_switch_tw').removeClass('lb_region_switch_active');
               $('#xp_switch_us').addClass('lb_region_switch_active');
              }
              if (region == "tw") {
               $('#tw_level_widget').show(500);
               $('#eu_level_widget').hide(500);
               $('#us_level_widget').hide(500);
               $('#xp_switch_eu').removeClass('lb_region_switch_active');
               $('#xp_switch_us').removeClass('lb_region_switch_active');
               $('#xp_switch_tw').addClass('lb_region_switch_active');
              }
             }
            </script>
       <?
     break;


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
                            
                            <iframe width="220" height="130" src="<?php echo $video_link ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            
 
 
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
            <iframe width="280" height="130" src="<?php echo $newlink ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
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
                
                <div class="home_widget_move" style="width: 20px; z-index: 3">
                  <img id="rss_videos" data-clipboard-text="https://www.wow-petguide.com/rss/pet_videos.xml" class="icon_share" src="images/icon_rss.png" style="margin: 0px; width: 16px; cursor: pointer">
                    <div class="remtt" style="display:none" id="rss_videos_confirm"><?php echo __("RSS Link copied to clipboard!") ?></div>
                    <script>
                    var btn = document.getElementById('rss_videos');
                    var clipboard = new Clipboard(btn);
            
                    clipboard.on('success', function(e) {
                        console.log(e);
                            $('#rss_videos_confirm').delay(0).fadeIn(500);
                            $('#rss_videos_confirm').delay(1200).fadeOut(500);
                        });
                    clipboard.on('error', function(e) {
                        console.log(e);
                    });
                    </script>
                </div>
                
                
                <?php if ($userrights['Edit_Home_Videos'] == true) { ?>
                    <div class="home_widget_move" style="width: 20px; z-index: 2">
                        <a data-remodal-target="modal_edit_vids"><img src="images/icon_pen_dark.png" style="margin-left: 24px; width: 16px; cursor: pointer"></a>
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
                                                    <td><?php echo $countvids ?></td>
                                                    <td style="padding-left: 8px"><a class="weblink" href="<?php echo $vid->Link ?>" target="_blank">Open</a></td>
                                                    <td style="padding-left: 8px"><?php echo htmlspecialchars($vid->Channel); ?></td>
                                                    <td style="padding-left: 8px"><?php echo htmlspecialchars($vid->Title); ?></td>
                                                    
                                                    <td style="padding-left: 8px"><span name="time"><?php echo $vid->Date ?></span></td>
                                                    <td style="padding-left: 8px">
                                                        <form action="index.php" method="post" style="display: inline">
                                                            <input type="hidden" name="action" value="delete_video">
                                                            <input type="hidden" name="video" value="<?php echo $vid->id ?>">
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
                <?php } ?>
                
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner" style="padding: 10px">
                    <div class="home_main_title" style="margin: 0px; padding: 12px"><?php echo __('Featured Videos'); ?></div>
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
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __('Quicklinks'); ?></div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        <ul class="home_recruiting">
                         <li><a class="weblink" style="font-size: 14px" href="https://wow-petguide.com/?News=257">Shadowland Preparation</a></li>
                            <li><a class="weblink" style="font-size: 14px" href="https://wow-petguide.com/?News=248">Shadowland Pet News</a></li>
                            <li><a class="weblink" style="font-size: 14px" href="https://wow-petguide.com/?News=249">Changes with Shadowlands</a></li>
                        </ul>
                    </div>
                </div>
            </div>            
        <?php break;
        

        case "strategies": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __('Newest Strategies'); ?>
                        <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                         <table class="table_leaderboard">
                            <?php $strats_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Published = 1 AND Deleted = 0 ORDER BY Id Desc LIMIT 15");
                             $count_new_strats = 0;
                                while ($this_new_strat = mysqli_fetch_object($strats_db)) {

                                    $strat_title = decode_sortingid(2,$this_new_strat->id);
                                    if ($strat_title['Family'] != "0" && $strat_title['Family'] != "") {
                                        switch ($strat_title['Family']) {
                                        case "1":
                                            $categoryname = $strat_title[1]." (".__("Humanoid").")";
                                            break;
                                        case "2":
                                            $categoryname = $strat_title[1]." (".__("Dragonkin").")";
                                            break;
                                        case "3":
                                            $categoryname = $strat_title[1]." (".__("Flying").")";
                                            break;
                                        case "4":
                                            $categoryname = $strat_title[1]." (".__("Undead").")";
                                            break;
                                        case "5":
                                            $categoryname = $strat_title[1]." (".__("Critter").")";
                                            break;
                                        case "6":
                                            $categoryname = $strat_title[1]." (".__("Magic").")";
                                            break;
                                        case "7":
                                            $categoryname = $strat_title[1]." (".__("Elemental").")";
                                            break;
                                        case "8":
                                            $categoryname = $strat_title[1]." (".__("Beast").")";
                                            break;
                                        case "9":
                                            $categoryname = $strat_title[1]." (".__("Aquatic").")";
                                            break;
                                        case "10":
                                            $categoryname = $strat_title[1]." (".__("Mechanical").")";
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
        <?php break;
        
        
        case "leaderboard": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner" style="padding: 10px">
                    <div class="home_main_title" style="margin: 0px; padding: 12px"><?php echo __('Top Collections'); ?></div>
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
                            <button id="lb_switch_global" class="lb_region_switch <?php if ($col_region == "global") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('global')"><?php echo __('Global'); ?></button>
                            <button id="lb_switch_us" class="lb_region_switch <?php if ($col_region == "us") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('us')">US</button>
                            <button id="lb_switch_eu" class="lb_region_switch <?php if ($col_region == "eu") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('eu')">EU</button>
                            <button id="lb_switch_kr" class="lb_region_switch <?php if ($col_region == "kr") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('kr')">KR</button>
                            <button id="lb_switch_tw" class="lb_region_switch <?php if ($col_region == "tw") echo "lb_region_switch_active"; ?>" onclick="lb_change_region('tw')">TW</button>
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
                                echo '<th style="text-align: left">Name</th><th style="text-align: right">'.__('Pets').'</th></tr>';
                                while($lb_entry = mysqli_fetch_object($lb_db)) {
                                    
                                    $lb_user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$lb_entry->User'");
                                    $lb_user = mysqli_fetch_object($lb_user_db);
                                    // Find out which flag to use
                                    
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
                                    if ($lb_region == "global") echo '<td style="padding: 0px"><img style="width: 20px" src="images/lb_flag_'.$lb_entry->Region.'.jpg"></td>';
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
        <?php break;
        
       
        case "comments": ?>
        <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
            <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __('Newest Comments'); ?>
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
                            echo mysqli_num_rows($comprdb)." ".__('new in the last 24h')."<br>";
                            
                            
                            ?>
                        </div>
                        <a data-remodal-target="modal_comments"><button class="button_home_team"><?php echo __('See latest'); ?> <?php echo $number_of_comments_to_show ?></button></a>
                        <div class="remodal modalteam" data-remodal-id="modal_comments" style="text-align: left">
                            <button data-remodal-action="close" class="remodal-close-team"></button>
                            
    
                            <div style="background-color: #c9c9c9; display: table; width: 100%;">
                                <div style="display: table; width: 100%; padding: 15 10 0 18; font-size: 14px; font-family: MuseoSans-300">
                                    <?php echo __('Amount of comments to show:'); ?>
                                    <form method="post" action="#modal_comments" style="display: inline">
                                        <select onchange="this.form.submit()" name="select_coms_toshow" class="petselect" required>
                                            <option class="petselect" value="10" <?php if ($usersettings['RecentComments'] == 10 OR $usersettings['RecentComments'] == "") { echo " selected"; } ?>>10</option>
                                            <option class="petselect" value="20" <?php if ($usersettings['RecentComments'] == 20) { echo " selected"; } ?>>20</option>
                                            <option class="petselect" value="50" <?php if ($usersettings['RecentComments'] == 50) { echo " selected"; } ?>>50</option>
                                            <option class="petselect" value="100" <?php if ($usersettings['RecentComments'] == 100) { echo " selected"; } ?>>100</option>
                                            <option class="petselect" value="200" <?php if ($usersettings['RecentComments'] == 200) { echo " selected"; } ?>>200</option>
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
                                                            $categoryname = $categorycheck[1]." (".__("Humanoid").")";
                                                            break;
                                                        case "2":
                                                            $categoryname = $categorycheck[1]." (".__("Dragonkin").")";
                                                            break;
                                                        case "3":
                                                            $categoryname = $categorycheck[1]." (".__("Flying").")";
                                                            break;
                                                        case "4":
                                                            $categoryname = $categorycheck[1]." (".__("Undead").")";
                                                            break;
                                                        case "5":
                                                            $categoryname = $categorycheck[1]." (".__("Critter").")";
                                                            break;
                                                        case "6":
                                                            $categoryname = $categorycheck[1]." (".__("Magic").")";
                                                            break;
                                                        case "7":
                                                            $categoryname = $categorycheck[1]." (".__("Elemental").")";
                                                            break;
                                                        case "8":
                                                            $categoryname = $categorycheck[1]." (".__("Beast").")";
                                                            break;
                                                        case "9":
                                                            $categoryname = $categorycheck[1]." (".__("Aquatic").")";
                                                            break;
                                                        case "10":
                                                            $categoryname = $categorycheck[1]." (".__("Mechanical").")";
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
                                                            $showintro = $showintro."... <a class='home_rightlink' target='_blank' href='?Comment=".$thiscom->id."'>[".__("continue reading")."]</a>";
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
                                                            <?php echo $showintro ?>
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
        <?php break;


        case "recruiting": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __('Looking for:'); ?></div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        <ul class="home_recruiting">
                            <?php // <li><a class="weblink tt_recruiting" data-tooltip-content="#tt_news_writer">News Writer</a></li> ?>
                            <li><a class="weblink tt_recruiting" data-tooltip-content="#tt_translator"><?php echo __('Translator'); ?></a>
                                <ul class="home_recruiting">
                                    <li>Italian</li>
                                    <li>Spanish</li>
                                </ul>
                            </li>
                        </ul>
                        <?php /* News Writer not required right now 
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
                            <span id="tt_translator"><?php echo __("Xu-Fu is set up to be fully localized into the languages WoW is translated as well. All it needs is volunteers to do so!<br>If you speak English plus one of the listed languages and would like to help out, send an email or ping Aranesh on Discord!<br><br>Contact:"); ?> 
                             <a class="weblink" href="mailto:xufu@wow-petguide.com">E-Mail</a> - <a class="weblink" href="https://discord.gg/z4dxYUq" target="_blank">Discord</a>
                            </span>
                        </div>
                        
                        <a data-remodal-target="modalteam"><button class="button_home_team"><?php echo __('See the current team'); ?></button></a>

                        <div class="remodal modalteam" data-remodal-id="modalteam" style="text-align: left">
                            <button data-remodal-action="close" class="remodal-close-team"></button>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><?php echo __("Site creator and admin") ?>:</p>
                                </div>
                
                                <div style="display: table; width: 100%">
                                    <div class="teamplayer">
                                        <div style="float: left; margin-right: 10px">
                                            <span class="username" style="text-decoration: none" rel="2" value="<?php echo $user->id ?>">
                                                <img width="50" height="50" src="https://www.wow-petguide.com/images/userpics/2.jpg" style="border-radius: 10px;">
                                            </span>
                                        </div>
                                        <div style="padding-top: 13px; text-align: left">
                                            <span class="username" style="text-decoration: none" rel="2" value="<?php echo $user->id ?>">
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
                                    <p class="blogodd"><?php echo __("News Writer:") ?></p>
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
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <img width="50" height="50" <?php echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><?php echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                
                
                
                            <?
                            $modsdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '3' ORDER BY Name");
                            if (mysqli_num_rows($modsdb) > "0") {
                            ?>
                
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><?php echo __("Content Creator:") ?></p>
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
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <img width="50" height="50" <?php echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><?php echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                
                
                
                            <?
                            $locadb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '4' ORDER BY Name");
                            if (mysqli_num_rows($locadb) > "0") {
                            ?>
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><?php echo __("Strategy Curator:") ?></p>
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
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <img width="50" height="50" <?php echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><?php echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                
                
                            <?
                            $locadb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '5' ORDER BY Name");
                            if (mysqli_num_rows($locadb) > "0") {
                            ?>
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><?php echo __("Localization") ?>:</p>
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
                                                <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                    <img width="50" height="50" <?php echo $thismodicon ?> style="border-radius: 10px;">
                                                </span>
                                            </div>
                                            <div style="padding-top: 13px; text-align: left">
                                                <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                    <h5 style="color: black; font-size: 18px;"><?php echo $thismod->Name ?></h5>
                                                </span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                
                
                
                
                            <?
                            $locadb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Frontpage = '9' ORDER BY Name");
                            if (mysqli_num_rows($locadb) > "0") {
                            ?>
                            <br>
                            <div style="background-color: #c9c9c9; display: table; width: 100%">
                                <div style="padding: 7px; margin-bottom: 5px; background: #c9c9c9;">
                                    <p class="blogodd"><?php echo __("Former Contributors") ?>:</p>
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
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <img width="50" height="50" <?php echo $thismodicon ?> style="border-radius: 10px;">
                                                    </span>
                                                </div>
                                                <div style="padding-top: 13px; text-align: left">
                                                    <span class="username" style="text-decoration: none" rel="<?php echo $thismod->id ?>" value="<?php echo $user->id ?>">
                                                        <h5 style="color: black; font-size: 18px;"><?php echo $thismod->Name ?></h5>
                                                    </span>
                                                </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
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
        <?php break;

        case "notifications": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php if (!$user) { echo __("Your Account"); } else { echo __("Notifications"); } ?></div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        <?php if (!$user) { ?>
                            <a href="#modallogin" onclick="hideloadingbnetlogin()"><button class="home_team" style="font-size: 14px; padding: 7 10 6 10;"><?php echo __("Login / Register") ?></button></a>
                        <?php }
                        if ($user) {
                            if ($unreadcounter > "0") {
                                $homeunreadmsg = '<a href="?page=messages" class="weblink">'.$unreadcounter.' '.__("new messages").'</a>';
                            }
                            else {
                                $homeunreadmsg = $unreadcounter.' '.__("new messages");
                            }
                            if ($mynewcoms > "0") {
                                $homeunreadcoms = '<a href="?page=mycomments" class="weblink">'.$mynewcoms.' '.__("comment responses").'</a>';
                            }
                            else {
                                $homeunreadcoms = $mynewcoms.' '.__("comment responses");
                            }
                            $new_strategy_comments = User_unread_strategy_comment_count ($user);
                            if ($new_strategy_comments > "0") {
                                $home_unread_strats = '<a href="?page=strategies" class="weblink">'.$new_strategy_comments.' '.__("new strategy comments").'</a>';
                            }
                            else {
                                $home_unread_strats = '0 '.__("new strategy comments");
                            }
                        echo $homeunreadmsg."<br>";
                        echo $homeunreadcoms."<br>";
                        echo $home_unread_strats;
                        } ?>
                        <br>
                    </div>
                </div>
            </div>
        <?php break;

        case "donations": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __("Support Xu-Fu") ?></div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                     <script type='text/javascript' src='https://ko-fi.com/widgets/widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Buy Aranesh a Ko-fi', '#2486d1', 'F1F61IQGC');kofiwidget2.draw();</script> 
                    </div>
                </div>
            </div>
        <?php break;

        case "contact": ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __("Get in Touch") ?></div>

                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">

                        <div style="padding-bottom: 4px"><a href="https://discord.gg/z4dxYUq" target="_blank"><img src="images/home_discord.png"></a></div>
                        <div style="float: left;padding-right: 4px"><a href="https://www.facebook.com/pages/XuFus-Pet-Guides/647394748729692" target="_blank"><img src="images/home_facebook.png"></a></div>
                        <div style="float: left"><a href="https://twitter.com/XuFusPetguide" target="_blank"><img src="images/home_twitter.png"></a></div>

                    </div>
                </div>
            </div>
        <?php break;

        case "devupdate":
            $prj_status_miss = 100-$prj_status_compl; ?>
            <div class="home_content_block home_menu_item" style="border-top: 9px solid #444444">
                <?php if ($user) print_mover($widget, $position); ?>
                <div class="home_menu_block_inner">
                    <div class="home_main_title" style="margin: 0px"><?php echo __("Development Update") ?></div>
                    <div class="home_main_content" style="margin: 10 0 0 0; padding-left: 10px">
                        A new "top pet list" with better filters, checks, bells and whistles.<br><br>
                        <b>Status:</b>
                        
                        <div style="height: 170; width: 170;">
                            <div style="margin-top: 75px; height: 50px; width: 170px; z-index: 20; position: absolute; font-size:16px"><center><b><?php echo $prj_status_compl ?>%</b></center></div>
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
                                    { y: <?php echo $prj_status_compl ?>, color: "#4186cb", },
                                    { y: <?php echo $prj_status_miss ?>, color: "#d3d3d3", },
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
        <?php break;  
    } 
}