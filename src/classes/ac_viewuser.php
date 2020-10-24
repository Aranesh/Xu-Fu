<?php

include_once ('classes/Database.php');



// ======================= SETTING VARIABLES FOR USER TO VIEW =========================

// Icon path

if ($viewuser->UseWowAvatar == "0"){
    $viewusericon = 'src="https://www.wow-petguide.com/images/pets/'.$viewuser->Icon.'.png"';
}
else if ($viewuser->UseWowAvatar == "1"){
    $viewusericon = 'src="https://www.wow-petguide.com/images/userpics/'.$viewuser->id.'.jpg" alt="'.$viewuser->IconUpdate.'"';
}

    $viewusersettings = format_usersettings($viewuser->Settings);
    if ($viewusersettings[7] != 0 OR $user->Role > 49) { 
        $viewuser_findcol = find_collection($viewuser);
        if ($viewuser_findcol != "No Collection") {
            $fp = fopen($viewuser_findcol['Path'], 'r');
            $viewuser_collection = json_decode(fread($fp, filesize($viewuser_findcol['Path'])), true);
        }
    }

    $comments_count = Database_query_single ( "SELECT COUNT(*) "
                                            . "FROM Comments "
                                            . "WHERE User = '$viewuser->id' "
                                            . "AND Deleted != '1'"
                                            );
    $strategies_count = Database_query_single ( "SELECT COUNT(*) "
                                              . "FROM Alternatives "
                                              . "WHERE User = '$viewuser->id'"
                                              );
    $mydatetime = Database_query_maybe_single ( "SELECT Date "
                                              . "FROM UserProtocol "
                                              . "WHERE User = '$viewuser->id' "
                                              . "ORDER BY Date DESC "
                                              . "LIMIT 1"
                                              );
    if ($mydatetime !== FALSE) {
        $datetimenow = strtotime(date("Y-m-d H:i:s"));
        $calctime = $datetimenow - strtotime($mydatetime);
        if ($calctime > 29030400) {
            $showtime = __("more than a year ago");
        }
        if ($calctime <= 29030400) {
            $showtime = __("a year ago");
        }
        if ($calctime <= 24192000) {
            $showtime = __("several months ago");
        }
        if ($calctime <= 4838400) {
            $showtime = __("a few months ago");
        }
        if ($calctime <= 4838400) {
            $showtime = __("two months ago");
        }
        if ($calctime <= 2419200) {
            $showtime = __("this month");
        }
        if ($calctime <= 604800) {
            $showtime = __("this week");
        }
        if ($calctime <= 70000) {
            $showtime = __("today");
        }
        if ($calctime <= 3600) {
            $showtime = __("an hour ago");
        }
        if ($calctime <= 260) {
            $useronline = "true";
        }
    }

    $display = $_GET['display'];
    if ($display == "Collection" && !$viewuser_collection) {
        $display = "";
        echo '<script>window.history.replaceState("object or string", "Title", "index.php?user='.$viewuser->id.'");</script>';
    }
    if ($display != "" && $display != "Collection" && $display != "Strategies") {
        $display = "";
        echo '<script>window.history.replaceState("object or string", "Title", "index.php?user='.$viewuser->id.'");</script>';
    }

    $thistitle = Database_query_maybe_object ( "SELECT * "
                                         . "FROM UserTitles "
                                         . "WHERE id = '$viewuser->Title' "
                                         . "LIMIT 1"
                                         );
    if ($thistitle !== FALSE) {
        $titleext = "Name_".$language;
        if ($thistitle->${'titleext'} != "") {
            $showtitle = htmlentities($thistitle->${'titleext'}, ENT_QUOTES, "UTF-8");
        }
        else {
            $showtitle = htmlentities($thistitle->Name_en_US, ENT_QUOTES, "UTF-8");
        }
    }
    else {
        $showtitle = "Pet Battler";
    }

    
    
    if ($userrights['EditStrats'] == "yes") {
        // Grab all Strategies from User
        //! \todo copied from ac_strategies.php, factorize out! note there are small modifications though!
        // Only different betweeen this and the one below is that this one ignores published or unpublished
        /// vvvvvv
        $stratdb = Database_query ( "SELECT Alternatives.*"
                                    . ", COALESCE (favourites.count, 0) as __favourite_count"
                                    . ", COALESCE (comments.count, 0) as __comment_count"
                                    . ", COALESCE (rating.count, 0) as __rating_count"
                                    . ", COALESCE (rating.average, 0) as __rating_average "
                                    . ", COALESCE (attempts.total, 0) AS __attempts_total "
                                    . ", COALESCE (attempts.wins, 0) AS __attempts_wins "
                                  . "FROM `Alternatives` "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT Strategy, Sub, COUNT(*) AS count "
                                    . "FROM UserFavStrats "
                                    . "GROUP BY Strategy"
                                  . ") favourites ON favourites.Strategy = Alternatives.id "
                                    . "AND favourites.Sub = Alternatives.Sub "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT Strategy, COUNT(*) AS count, ROUND (AVG (Rating), 1) as average "
                                    . "FROM UserStratRating "
                                    . "GROUP BY Strategy"
                                  . ") rating ON rating.Strategy = Alternatives.id "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT SortingID, COUNT(*) AS count "
                                    . "FROM Comments "
                                    . "WHERE Deleted = 0 "
                                    . "GROUP BY SortingID"
                                  . ") comments ON comments.SortingID = Alternatives.id "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT Strategy, COUNT(*) AS total, SUM(Success = 1) as wins "
                                    . "FROM UserAttempts "
                                    . "GROUP BY Strategy"
                                  . ") attempts ON attempts.Strategy = Alternatives.id "
        /// ^^^^^^
                                  . "WHERE Alternatives.User = " . $viewuser->id . " "
        /// vvvvvv
                                  );
    }
    else {
        // Grab all Strategies from User
        //! \todo copied from ac_strategies.php, factorize out! note there are small modifications though!
        /// vvvvvv
        $stratdb = Database_query ( "SELECT Alternatives.*"
                                    . ", COALESCE (favourites.count, 0) as __favourite_count"
                                    . ", COALESCE (comments.count, 0) as __comment_count"
                                    . ", COALESCE (rating.count, 0) as __rating_count"
                                    . ", COALESCE (rating.average, 0) as __rating_average "
                                    . ", COALESCE (attempts.total, 0) AS __attempts_total "
                                    . ", COALESCE (attempts.wins, 0) AS __attempts_wins "
                                  . "FROM `Alternatives` "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT Strategy, Sub, COUNT(*) AS count "
                                    . "FROM UserFavStrats "
                                    . "GROUP BY Strategy"
                                  . ") favourites ON favourites.Strategy = Alternatives.id "
                                    . "AND favourites.Sub = Alternatives.Sub "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT Strategy, COUNT(*) AS count, ROUND (AVG (Rating), 1) as average "
                                    . "FROM UserStratRating "
                                    . "GROUP BY Strategy"
                                  . ") rating ON rating.Strategy = Alternatives.id "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT SortingID, COUNT(*) AS count "
                                    . "FROM Comments "
                                    . "WHERE Deleted = 0 "
                                    . "GROUP BY SortingID"
                                  . ") comments ON comments.SortingID = Alternatives.id "
                                  . "LEFT OUTER JOIN ("
                                    . "SELECT Strategy, COUNT(*) AS total, SUM(Success = 1) as wins "
                                    . "FROM UserAttempts "
                                    . "GROUP BY Strategy"
                                  . ") attempts ON attempts.Strategy = Alternatives.id "
        /// ^^^^^^
                                  . "WHERE Alternatives.User = " . $viewuser->id . " "
                                  . "AND Published = '1'"
        /// vvvvvv
                                  );
    }


    $strats = [];
    $stratcounter = 0;
    while ($thisstrat = mysqli_fetch_object($stratdb)) {
        $fightdetails = decode_sortingid('2',$thisstrat->id);
    
        $strats[$stratcounter]['id'] = $thisstrat->id;
        $strats[$stratcounter]['Pet1'] = $thisstrat->PetID1;
        $strats[$stratcounter]['Pet2'] = $thisstrat->PetID2;
        $strats[$stratcounter]['Pet3'] = $thisstrat->PetID3;
        
        $strats[$stratcounter]['Deleted'] = $thisstrat->Deleted;
        
        $strats[$stratcounter]['Published'] = $thisstrat->Published;
        
        $strats[$stratcounter]['Attempts'] = $thisstrat->__attempts_total;
        $strats[$stratcounter]['GAttempts'] = $thisstrat->__attempts_wins;
        $strats[$stratcounter]['BAttempts'] = $thisstrat->__attempts_total - $thisstrat->__attempts_wins;
    
        $strats[$stratcounter]['td'] = "0";
        if ($thisstrat->tdscript != "") {
            $strats[$stratcounter]['td'] = "1";
        }
    
        $strats[$stratcounter]['Fightlink'] = $fightdetails[0];
        $strats[$stratcounter]['Fightname'] = $fightdetails[1];
        $strats[$stratcounter]['Favs'] = $thisstrat->__favourite_count;
        $strats[$stratcounter]['Ratings'] = $thisstrat->__rating_count;
        $strats[$stratcounter]['RatingAverage'] = $thisstrat->__rating_average;
    
        //! \todo This is a floor(x*2)/2 or alike. Plus the . -> _ though.
        if ($strats[$stratcounter]['RatingAverage'] == "0") {
            $strats[$stratcounter]['RatingAverage'] = "-";
            $strats[$stratcounter]['StratClass'] = "0";
        }
            if ($strats[$stratcounter]['RatingAverage'] <= "1.25" && $strats[$stratcounter]['StratClass'] != "0" ) { $strats[$stratcounter]['StratClass'] = "1"; }
            if ($strats[$stratcounter]['RatingAverage'] > "1.25") { $strats[$stratcounter]['StratClass'] = "1_5"; }
            if ($strats[$stratcounter]['RatingAverage'] > "1.75") { $strats[$stratcounter]['StratClass'] = "2"; }
            if ($strats[$stratcounter]['RatingAverage'] > "2.25") { $strats[$stratcounter]['StratClass'] = "2_5"; }
            if ($strats[$stratcounter]['RatingAverage'] > "2.75") { $strats[$stratcounter]['StratClass'] = "3"; }
            if ($strats[$stratcounter]['RatingAverage'] > "3.25") { $strats[$stratcounter]['StratClass'] = "3_5"; }
            if ($strats[$stratcounter]['RatingAverage'] > "3.75") { $strats[$stratcounter]['StratClass'] = "4"; }
            if ($strats[$stratcounter]['RatingAverage'] > "4.25") { $strats[$stratcounter]['StratClass'] = "4_5"; }
            if ($strats[$stratcounter]['RatingAverage'] > "4.75") { $strats[$stratcounter]['StratClass'] = "5"; }
            
        $strats[$stratcounter]['Views'] = $thisstrat->Views;
        $strats[$stratcounter]['Comments'] = $thisstrat->__comment_count;
    /// ^^^^^^
    /// vvvvvv
        $stratcounter++;
    }

    sortBy('NewComs', $strats, 'desc');
    /// ^^^^^^

    $viewstrategies = $stratcounter !== 0;

    // =======================================================================================================
    // ================================== ?????? BACKEND ??????? =============================================
    // =======================================================================================================
    // ================================== ?????? FRONTEND ?????? =============================================
    // =======================================================================================================


    if ($viewuser_collection) {
        $stats = get_collection_stats($viewuser_collection); 
    ?>
    <script>
    $(function () {
        $("#families_header_<?php echo $user->id ?>").CanvasJSChart( {
            title:{
                fontFamily: "MuseoSans-500",
                fontWeight: "normal",
                fontColor: "black"
            },
            interactivityEnabled: false,
            animationEnabled: false,
            backgroundColor: null,
            width: 130,
            height: 130,
            data: [
                {
                    type: "doughnut",
                    startAngle:270,
                    innerRadius: "60%",
                    showInLegend: false,
                    toolTipContent: "",
                    indexLabel: "",
                    dataPoints: [
                        {  y: <?php echo $stats['Humanoid'] ?>, color: "#08adff" },
                        {  y: <?php echo $stats['Dragonkin'] ?>, color: "#59bc11" },
                        {  y: <?php echo $stats['Flying'] ?>, color: "#d4ca4f" },
                        {  y: <?php echo $stats['Undead'] ?>, color: "#9f6c73" },
                        {  y: <?php echo $stats['Critter'] ?>, color: "#7c5943" },
                        {  y: <?php echo $stats['Magic'] ?>, color: "#7341ee" },
                        {  y: <?php echo $stats['Elemental'] ?>, color: "#eb7012" },
                        {  y: <?php echo $stats['Beast'] ?>, color: "#ec2b22" },
                        {  y: <?php echo $stats['Aquatic'] ?>, color: "#08aab7" },
                        {  y: <?php echo $stats['Mechanic'] ?>, color: "#7e776d" }
                   ]
                }
                ]
            });
        });
    </script>
    <?php } ?>


    <div class="blogtitle">
        <div style="position: absolute; right:0px;">
            <img src="images/main_bg02_2.png">
        </div>
    
        <?php if ($viewuser_collection) { ?>
            <div class="ut_petdonut" style="left: 29px;">
                <div id="families_header_<?php echo $user->id ?>" style="height: 100%; width: 100%;"></div>
            </div>
        <?php } ?>
    
        <div class="ut_icon" <?php if (!$viewuser_collection) { echo 'style="left: 50px;"'; } else { echo 'style="left: 53px;"'; }?>>
            <a href="index.php?user=<?php echo $viewuser->id ?>"><img <?php echo $viewusericon ?> class="ut_icon" <?php if (!$viewuser_collection) { echo 'style="border: 1px dotted #5678ad;"'; } ?>></a>
        </div>
    
        <div style="position: relative; padding-left: 170px;top: 48%;transform: translateY(-50%);">
            <h class="megatitle" style="line-height: 25px;"><?php echo $viewuser->Name ?></h>
            <p class="pr_role"><?php echo $showtitle ?></p>
        </div>
    
        <?php if ($viewuser_collection OR $viewstrategies == "true") { ?>
        <div style="position: absolute;left: 310px;top: 148px;">
            <button id="ButtonAbout" onclick="profile_about('<?php echo $viewuser->id ?>')" class="profile <?php if ($display != "Collection" AND $display != "Strategies") { echo 'profileactive'; } ?>" style="display: block"><?php echo __("About") ?></button>
            <?php if ($viewuser_collection) { ?><button id="ButtonCollection" onclick="profile_collection('<?php echo $viewuser->id ?>')" class="profile <?php if ($display == "Collection") { echo 'profileactive'; } ?>" style="display: block"><?php echo __("Collection") ?></button><?php } ?>
            <?php if ($viewstrategies == "true") { ?><button id="ButtonStrategies" onclick="profile_strategies('<?php echo $viewuser->id ?>')" class="profile <?php if ($display == "Strategies") { echo 'profileactive'; } ?>" style="display: block"><?php echo __("Strategies") ?></button><?php } ?>
        </div>
        <?php } ?>
    </div>



<div class="remodal-bg maincontent">

    <div style="position: relative; float:left; min-height: 600px; padding-left: 300px;">

        <div id="collection" style="padding: 32 10 10 25; <?php if ($display != "Collection") { echo 'display: none'; } ?>" >
             <?php if ($viewuser_collection) {
                print_collection($viewuser_collection,'0',$viewuser->Name, $viewuser);
            } ?>
        <br><br>
        </div>

        <div id="about" style="padding: 32 10 10 15; <?php if ($display != "About" && $display != "") { echo 'display: none'; } ?>">
            <?php if ($viewuser->PrIntro != "") {
                $introoutput = stripslashes($viewuser->PrIntro);
                $introoutput = htmlentities($introoutput, ENT_QUOTES, "UTF-8");
                $introoutput = AutoLinkUrls($introoutput,'1','dark');
                $introoutput = str_replace("[u]", "<u>", $introoutput);
                $introoutput = str_replace("[/u]", "</u>", $introoutput);
                $introoutput = str_replace("[i]", "<i>", $introoutput);
                $introoutput = str_replace("[/i]", "</i>", $introoutput);
                $introoutput = str_replace("[b]", "<b>", $introoutput);
                $introoutput = str_replace("[/b]", "</b>", $introoutput);
                $introoutput = preg_replace("/\n/s", "<br>", $introoutput);
                }
                else if ($viewuser->PrIntro == ""){
                    $introoutput = '<span class="username" style="text-decoration: none;font-weight: bold" rel="'.$viewuser->id.'" value="'.$user->id.'">'.$viewuser->Name.'</span> '.__("has not added any personal information.");
                }
            ?>
            <p class="blogodd"><?php  echo $introoutput ?><br>
        </div>


<?php if ($viewstrategies == "true") { ?>
    <div id="strategies" style="padding: 32 10 10 25; <?php if ($display != "Strategies") { echo 'display: none'; } ?>" >

        <table style="min-width: 800px" class="profile">
            <tr class="profile">
                <th class="profile">        
                    <table id="t2" style="border-collapse: collapse" class="example table-autosort table-autofilter table-autopage:15 table-page-number:t2page table-page-count:t2pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                     
                        <thead>
            
                            <tr>
                                <th align="left" class="petlistheadersecond" width="150">
                                        <input class="petselect" name="filter" size="25" id="catfilter" onkeyup="Table.filter(this,this)">
                                </th>
            
                                <th align="left" class="petlistheadersecond"><center><p class="table-sortable-black">Pets</p></th>
                                <th align="center" class="petlistheadersecond table-sortable:numeric"><center><p class="table-sortable-black">Favourites</th>
                                <th align="center" colspan="2" class="petlistheadersecond table-sortable:numeric"><center><p class="table-sortable-black">Rating</th>
                                <th align="center" class="petlistheadersecond table-sortable:numeric"><center><p class="table-sortable-black">Views</th>
                                <th align="center" class="petlistheadersecond table-sortable:numeric"><center><p class="table-sortable-black">Comments</th>
                            </tr>
                        </thead>
                        <tbody>
            
                        <?php
                        foreach($strats as $key => $value) {
                            $row_class = "mystratslistrow";
                            if ($value['Published'] == 0) {
                                $row_class = "mystratslistrowunpub";
                            }
                            if ($value['Deleted'] == 1) {
                                $row_class = "mystratslistrowdeleted";
                            }
                            
                            
                            
                            
                        if ($value['Deleted'] == 0 OR $userrights['EditStrats'] == "yes") {
                            ?>
                            <tr class="mystratslistrow">
                                <td class="<?php echo $row_class ?>" style="padding-left: 10px"><a class="comlinkdark" href="<?php echo $value['Fightlink'] ?>" target="_blank"><?php echo $value['Fightname'] ?></a></td>
                          
                                <td class="<?php echo $row_class ?>"><center>
                                
                                <?
                                $i = "1";
                                while ($i < "4") {
                                    switch ($i) {		
                                        case "1":
                                            $fetchpet = $value['Pet1'];
                                            break;
                                        case "2":
                                            $fetchpet = $value['Pet2'];
                                            break;                             
                                        case "3":
                                            $fetchpet = $value['Pet3'];
                                            break;
                                    }
            
                                    if ($fetchpet == "0") {  // Level Pet ?>
                                        <img class="rating_tt" data-tooltip-content="#atooltip_<?php echo $i ?>_<?php echo $key ?>" style="width: 24px; height: 24px; cursor: help" src="https://www.wow-petguide.com/images/pets/resize50/level.png">
                                        <div style="display:none">
                                            <span id="atooltip_<?php echo $i ?>_<?php echo $key ?>">Level Pet</span>
                                        </div> 
                                    <?php }
                                    if ($fetchpet == "1") {  // Any Pet ?>
                                        <img class="rating_tt" data-tooltip-content="#atooltip_<?php echo $i ?>_<?php echo $key ?>" style="width: 24px; height: 24px; cursor: help" src="https://www.wow-petguide.com/images/pets/resize50/any.png">
                                        <div style="display:none">
                                            <span id="atooltip_<?php echo $i ?>_<?php echo $key ?>">Any Pet</span>
                                        </div>                             
                                    <?php }
                                    if ($fetchpet > "10" && $fetchpet <= "20") { // Family pets
                                        switch ($fetchpet) { 
                                            case "11":
                                               $famname = __("Any")." ".__("Humanoid");
                                            break;      
                                            case "12":
                                               $famname = __("Any")." ".__("Magic");
                                            break;
                                            case "13":
                                               $famname = __("Any")." ".__("Elemental");
                                            break;      
                                            case "14":
                                               $famname = __("Any")." ".__("Undead");
                                            break;   
                                            case "15":
                                               $famname = __("Any")." ".__("Mech");
                                            break;      
                                            case "16":
                                               $famname = __("Any")." ".__("Flyer");
                                            break;
                                            case "17":
                                               $famname = __("Any")." ".__("Critter");
                                            break;      
                                            case "18":
                                               $famname = __("Any")." ".__("Aquatic");
                                            break;   
                                            case "19":
                                               $famname = __("Any")." ".__("Beast");
                                            break;      
                                            case "20":
                                               $famname = __("Any")." ".__("Dragon");
                                            break;  
                                         } ?>
                                        <img class="rating_tt" data-tooltip-content="#atooltip_<?php echo $i ?>_<?php echo $key ?>" style="width: 24px; height: 24px; cursor: help" src="https://www.wow-petguide.com/images/pets/resize50/<?php echo $fetchpet ?>.png">
                                        <div style="display:none">
                                            <span id="atooltip_<?php echo $i ?>_<?php echo $key ?>"><?php echo $famname ?></span>
                                        </div>                               
                                        <?php }
                                        if ($fetchpet > "20") { 
                                            if (file_exists('images/pets/resize50/'.$all_pets[$fetchpet]['PetID'].'.png')) {
                                                $peticon = 'https://www.wow-petguide.com/images/pets/resize50/'.$all_pets[$fetchpet]['PetID'].'.png';
                                            }
                                            else {
                                                $peticon = 'https://www.wow-petguide.com/images/pets/resize50/unknown.png';
                                            } ?>                    
                                            <a href="http://<?php echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<?php echo $all_pets[$fetchpet]['PetID'] ?>" target="_blank"><img style="width: 24px; height: 24px" src="<?php echo $peticon ?>"></a>
            
                                        <?php }
                                    $i++;
                                }
                                    ?>
                                            
            
                                </td>
                                <td class="<?php echo $row_class ?>">
                                <div>
                                    <div style="float: left; margin: 0 5 0 25"><p class="blogodd"><?php echo $value['Favs'] ?></div>
                                    <div style="float: left"><img src="https://www.wow-petguide.com/images/icon_strats_fav.png"></div>
                                </div>
                                </td>
                                <td class="<?php echo $row_class ?>" style="width: 1%; text-align: left; padding-left: 20px"><p class="blogodd"><?php echo $value['RatingAverage'] ?></td>
                                <td><center>
                                    <div class="strat_star_<?php echo $value['StratClass'] ?> rating_tt" data-tooltip-content="#atooltip_content_<?php echo $key ?>" style="width:100px; height:20px; display:block; cursor: help"></div>
                                    <div style="display:none">
                                        <span id="atooltip_content_<?php echo $key ?>"><?php echo $value['Ratings'] ?> battler(s) voted on this strategy.</span>
                                    </div>       
                                </td>

                                <td class="<?php echo $row_class ?>"><center><p class="blogodd"><?php echo $value['Views'] ?></td>
                                <td class="<?php echo $row_class ?>"><center><p class="blogodd">
                                    <div class="rating_tt" data-tooltip-content="#coms_tooltip_content_<?php echo $key ?>" style="cursor: help"><p class="blogodd"><?php echo $value['Comments']; ?></p></div>
                                    <div style="display:none">
                                        <span id="coms_tooltip_content_<?php echo $key ?>">
                                            This number shows all comments, including those written in different languages. You can change the displayed language in your account settings.
                                        </span>
                                    </div>                    
                                </td>
                        </tr>
            
            
                        <?php
                        }
                    }
                        ?>
            
                        </tbody>
                        
                        <tfoot>
                            <tr>
                                <td colspan="3" style="padding-top: 10px" align="right" class="table-page:previous" style="padding-top: 5px;"><a class="wowhead" style="cursor:pointer;text-decoration: none;">&lt; &lt; </a></td>
                                <td colspan="1" style="padding-top: 10px" align="center"><div style="white-space:nowrap;padding-top: 5px;"><p class="blogodd"><span id="t2page"></span> / <span id="t2pages"></span></div></td>
                                <td colspan="3" style="padding-top: 10px" align="left" class="table-page:next" style="cursor:pointer;padding-top: 5px;"><a class="wowhead" style="cursor:pointer;text-decoration: none;"> &gt; &gt;</td>
                            </tr>
                        </tfoot>
                </table>
        </table>
        
        <script>
    $(document).ready(function() {
        $('.rating_tt').tooltipster({
            maxWidth: '250',
            theme: 'tooltipster-smallnote'
        });
    });
    $(document).ready(function() {
        $('.newcoms_tt').tooltipster({
            maxWidth: '300',
            theme: 'tooltipster-smallnote',
            interactive: 'true'
        });
    });
</script>
    </div>
<?php } ?>    
    
    
</div>      




    <div class="profileleft">
        <div style="padding: 30 30 10 10;text-align: right">
            <?
            if ($viewuser->PrSocFacebook != "" OR $viewuser->PrSocTwitter != "" OR $viewuser->PrSocInstagram != "" OR $viewuser->PrSocYoutube != "" OR $viewuser->PrSocReddit != "" OR $viewuser->PrSocTwitch != "") {
                if ($viewuser->PrSocFacebook != "") {
                    $smout = str_replace('"', '\"', $viewuser->PrSocFacebook);
                    $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                    echo '<a class="profile_sm_facebook" href="'.$smout.'" target="_blank"></a>';
                }
                if ($viewuser->PrSocTwitter != "") {
                    $smout = str_replace('"', '\"', $viewuser->PrSocTwitter);
                    $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                    echo '<a class="profile_sm_twitter" href="'.$smout.'" target="_blank"></a>';
                }
                if ($viewuser->PrSocInstagram != "") {
                    $smout = str_replace('"', '\"', $viewuser->PrSocInstagram);
                    $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                    echo '<a class="profile_sm_instagram" href="'.$smout.'" target="_blank"></a>';
                }
                if ($viewuser->PrSocYoutube != "") {
                    $smout = str_replace('"', '\"', $viewuser->PrSocYoutube);
                    $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                    echo '<a class="profile_sm_youtube" href="'.$smout.'" target="_blank"></a>';
                }
                if ($viewuser->PrSocFacebook != "" && $viewuser->PrSocTwitter != "" && $viewuser->PrSocInstagram != "" && $viewuser->PrSocYoutube != "" && $viewuser->PrSocReddit != "" && $viewuser->PrSocTwitch != "") {
                    echo "<br>";
                }
                if ($viewuser->PrSocReddit != "") {
                    $smout = str_replace('"', '\"', $viewuser->PrSocReddit);
                    $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                    echo '<a class="profile_sm_reddit" href="'.$smout.'" target="_blank"></a>';
                }
                if ($viewuser->PrSocTwitch != "") {
                    $smout = str_replace('"', '\"', $viewuser->PrSocTwitch);
                    $smout = htmlentities($smout, ENT_QUOTES, "UTF-8");
                    echo '<a class="profile_sm_twitch" href="'.$smout.'" target="_blank"></a>';
                }
                ?>
                <br><br>
            <?php } ?>


            <table class="profileleft" style="width: auto; margin-right: 0px; margin-left: auto; cellpadding: 0; cellspacing: 0">
                <?php if ($viewuser->PrBattleTag OR $viewuser->PrDiscord) {
                    if ($viewuser->PrBattleTag) { ?>
                        <tr>
                            <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("BattleTag:") ?></td>
                            <td><p class="blogodd" style="font-size: 15px;"><b><?
                                        echo htmlentities($viewuser->PrBattleTag, ENT_QUOTES, "UTF-8");
                                        echo "#";
                                        echo htmlentities($viewuser->PrBTagNum, ENT_QUOTES, "UTF-8");
                                        echo " (";
                                        echo strtoupper($viewuser->PrBTagRegion);
                                        echo ")";
                        ?>
                            </b></td>
                        </tr>
                    <?php }
                    if ($viewuser->PrDiscord) { ?>
                        <tr>
                            <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("Discord") ?>:</td>
                            <td><p class="blogodd" style="font-size: 15px;"><b><?php echo htmlentities($viewuser->PrDiscord, ENT_QUOTES, "UTF-8"); ?></b></td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="2">
                            <hr class="quickfacts">
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($viewuser_collection) { ?>

                    <tr>
                        <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("Pets Collected") ?>:</td>
                        <td><p class="blogodd" style="font-size: 15px"><b><?php echo $stats['Maxed']+$stats['NotMaxed']; ?></b></td>
                    </tr>

                    <tr>
                        <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("Unique pets") ?>:</td>
                        <td><p class="blogodd" style="font-size: 15px"><b><?php echo $stats['Unique']; ?></b></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <hr class="quickfacts">
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($viewuser->PrFavPet) {
                $favpet = Database_query_object ( "SELECT * "
                                                . "FROM PetsUser "
                                                . "WHERE PetID = $viewuser->PrFavPet"
                                                );
                  ?>
                    <tr>
                        <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap"><?php echo __("Favourite Pet") ?>:</td>
                        <td><p class="blogodd" style="font-size: 15px;"><b>
                            <a class="wowhead" style="text-decoration: none" href="http://<?php echo $wowhdomain ?>.wowhead.com/npc=<?php echo $favpet->PetID ?>" target="_blank"><?php echo $favpet->${'petnext'}; ?></a>
                        </b></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <hr class="quickfacts">
                        </td>
                    </tr>
                <?php } ?>
                
                <?php if ($stratcounter > "0") {
                  ?>
                    <tr>
                        <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap">Strategies:</td>
                        <td><p class="blogodd" style="font-size: 15px"><b><?php echo $stratcounter ?></b></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <hr class="quickfacts">
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($comments_count > 0) { ?>
                    <tr>
                        <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("Comments") ?>:</td>
                        <td><p class="blogodd" style="font-size: 15px"><b><?php echo $comments_count; ?></b></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <hr class="quickfacts">
                        </td>
                    </tr>
                 <?php } ?>
                 
                <tr>
                    <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("Registered") ?>:</td>
                    <td><p class="blogodd" style="font-size: 15px"><b><span name="time"><?php echo $viewuser->regtime ?></span></b></td>
                </tr>
                
                
                <?php if ($viewusersettings[6] != 0 OR $user->Role > 49) { ?>
                <tr>
                    <td style="text-align:right; vertical-align: top"><p class="blogodd" style="font-size: 15px; white-space:nowrap;"><?php echo __("Last active:") ?></td>
                    <td><p class="blogodd" style="font-size: 15px"><b><?php echo $showtime ?></b></td>
                </tr>
                <?php } ?>

            </table>

            <br>
            <?php if (!$user && $viewuser->id != "1") { ?>
                <span class="tooltip" title="<?php echo __("You must be logged in to send messages") ?>">
                    <span style="cursor: pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"> <a class="pr_contact"><?php echo __("Send message") ?></a></span>
                </span>
                <script>
                    $(document).ready(function() {
                        $('.tooltip').tooltipster({
                            maxWidth: '150',
                            theme: 'tooltipster-smallnote'
                        });
                    });
                </script>
            <?php }

            if ($viewuser->id == $user->id && $viewuser->id != "1") { ?>
                <span class="tooltip" title="<?php echo __("Cannot send messages to yourself") ?>">
                    <span style="cursor: pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"> <a class="pr_contact"><?php echo __("Send message") ?></a></span>
                </span>
                <script>
                    $(document).ready(function() {
                        $('.tooltip').tooltipster({
                            maxWidth: '150',
                            theme: 'tooltipster-smallnote'
                        });
                    });
                </script>
            <?php }

            if ($user && $viewuser->id != $user->id && $viewuser->id != "1") { ?>
                <a data-remodal-target="modalsendmsg" style="cursor:pointer"><img src="https://www.wow-petguide.com/images/userdd_messages.png"></a> <a data-remodal-target="modalsendmsg" class="pr_contact"><?php echo __("Send message") ?></a>

                <div class="remodal remodalsuggest" data-remodal-id="modalsendmsg" data-remodal-options="hashTracking: false">
                    <table width="600" class="profile">
                        <tr class="profile">
                            <th colspan="2" width="5" class="profile">
                                <table border="0">
                                    <tr>
                                        <td><img src="images/headericon_profile_blue.png"></td>
                                        <td><img src="images/blank.png" width="5" height="1"></td>
                                        <td><p class="blogodd"><span style="white-space: nowrap;"><b><?php echo __("Send private message to") ?> <span class="username" style="text-decoration: none;" rel="<?php echo $viewuser->id ?>" value="<?php echo $user->id ?>"><?php echo $viewuser->Name ?></span></span></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <form action="index.php?page=writemsg" method="post">
                        <tr class="profile">
                            <td class="collectionbordertwo" colspan="2">

                               <textarea required placeholder="<?php echo __("Type your message here") ?>" class="cominputbright" id="rsp_field_write" style="height: 60px; width: 600px;" name="msgcontent" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'write','2000')" maxlength="2000"><?php echo $editoutput ?></textarea>
                                <p class="blogodd">
                            </td>
                        </tr>

                        <tr class="profile">
                            <td class="collectionbordertwo">
                                <table>
                                    <tr>
                                        <td style="padding-left: 12px;">
                                                <input type="hidden" name="delimiter" value="<?php echo $user->ComSecret; ?>">
                                                <input type="hidden" name="cmd" value="sendmsg">
                                                <input type="hidden" name="recipient" value="<?php echo $viewuser->id ?>">
                                                <input type="submit" class="comedit" value="<?php echo __("Send") ?>">
                                        </td>
                                        </form>
                                        <td style="padding-left: 15px;">
                                            <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                                        </td>
                                        <td align="right" width="100%">
                                            <span class="smallodd" style="padding-right: 10px" id="rsp_remaining_write">0/2000</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php } ?>
             <br><br><br><br>
        </div>
    </div>




    <div class="articlebottom"></div>

</div>
<script>updateAllTimes('time')</script>
<?
mysqli_close($dbcon);
echo "</body>";
die;