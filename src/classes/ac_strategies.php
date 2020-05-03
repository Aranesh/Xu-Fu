<?php

include_once ('classes/Database.php');

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
                          . "WHERE Alternatives.User = " . $user->id
                          );

$stratcounter = "0";
while ($thisstrat = mysqli_fetch_object($stratdb)) {
    $fightdetails = decode_sortingid('2',$thisstrat->id);

    $favorite_count = $thisstrat->__favourite_count;
    $comments_count = $thisstrat->__comment_count;
    $ratings_count = $thisstrat->__rating_count;
    $ratings_average = $thisstrat->__rating_average;

    $strats[$stratcounter]['id'] = $thisstrat->id;
    $strats[$stratcounter]['Pet1'] = $thisstrat->PetID1;
    $strats[$stratcounter]['Pet2'] = $thisstrat->PetID2;
    $strats[$stratcounter]['Pet3'] = $thisstrat->PetID3;

    $strats[$stratcounter]['Attempts'] = $thisstrat->__attempts_total;
    $strats[$stratcounter]['GAttempts'] = $thisstrat->__attempts_wins;
    $strats[$stratcounter]['BAttempts'] = $thisstrat->__attempts_total - $thisstrat->__attempts_wins;

    $strats[$stratcounter]['td'] = "0"; 
    if ($thisstrat->tdscript != "") {
        $strats[$stratcounter]['td'] = "1";    
    }
    
    $strats[$stratcounter]['Fightlink'] = $fightdetails[0];
    $strats[$stratcounter]['Fightname'] = $fightdetails[1];
    $strats[$stratcounter]['Favs'] = $favorite_count;
    $strats[$stratcounter]['Ratings'] = $ratings_count;
    $strats[$stratcounter]['RatingAverage'] = $ratings_average;

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
    $strats[$stratcounter]['Comments'] = $comments_count;
    $strats[$stratcounter]['NewComs'] = $thisstrat->NewComs;
    $strats[$stratcounter]['NewComsIDs'] = $thisstrat->NewComsIDs;
    $strats[$stratcounter]['Published'] = $thisstrat->Published ? "Yes" : "<b>No</b>";
    $strats[$stratcounter]['PublishedNr'] = $thisstrat->Published;
    $stratcounter++; 
}

sortBy('NewComs', $strats, 'desc');











?>

<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <? echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle">My Strategies</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('strategies');
    ?>
</div>

<div class="blogentryfirst">
<div class="articlebottom">
</div>

<table width="100%" border="0">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>
            <td>


<table style="width: 100%;">
<tr>
<td>

<table style="width: 85%;" class="profile">
    <tr class="profile">
        <th class="profile">

    <?        
    
            if (!$strats) {
                ?>
                <div class="msg_nomsgs" id="nomsgsboxout" style="min-height: 50px">
                    You have not created any strategies, yet. It's easy, give it a try if you want to share one!<br>
                    And if you are unsure, have a look at <a href="?m=StratCreationGuide" class="wowhead">Xu-Fu's Strategy Creation Guide</a><br><br>
                </div><br><br>
                <?
            }
            else {
                ?>
            <table width="100%" id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:15 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
            <thead>

                <tr>
                    <th></th>
                    <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="150"><p class="table-sortable-black" style="margin-left: 15px;">Link to fight</p></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr>
                    <th align="center" class="petlistheadersecond table-sortable:numeric" width="40"><p class="table-sortable-black">ID</th>

                    <th align="left" class="petlistheadersecond" width="150">
                            <input class="petselect" name="filter" size="25" id="catfilter" onkeyup="Table.filter(this,this)">
                    </th>

                    <th align="left" class="petlistheadersecond table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 15px;">Published</p></th>
                    <th align="left" class="petlistheadersecond"><p class="table-sortable-black" style="margin-left: 15px;">Pets</p></th>
                    <th align="center" class="petlistheadersecond table-sortable:numeric"><p class="table-sortable-black">Favourites</th>
                    <th align="center" colspan="2" class="petlistheadersecond table-sortable:numeric"><p class="table-sortable-black">Rating</th>
                    <th align="center" class="petlistheadersecond table-sortable:alphabetic"><p class="table-sortable-black">TDScript</th>
                    <th align="center" class="petlistheadersecond table-sortable:numeric"><p class="table-sortable-black">Attempts</th>
                    <th align="center" class="petlistheadersecond table-sortable:numeric"><p class="table-sortable-black">Views</th>
                    <th align="center" class="petlistheadersecond table-sortable:numeric"><p class="table-sortable-black">Comments</th>
                    <th align="center" class="petlistheadersecond table-sortable:numeric"><p class="table-sortable-black">New</th>
                </tr>
            </thead>
            <tbody>

            <?php
            foreach($strats as $key => $value) {

                if ($value['PublishedNr'] == "0") {
                    $rowclass = "mystratslistrowunpub";
                }
                if ($value['PublishedNr'] == "1") {
                    $rowclass = "mystratslistrow";
                }
                if ($value['NewComs'] == "0") {
                    $rowclassnew = $rowclass;
                } 
                if ($value['NewComs'] > "0") {
                    $rowclassnew = "mystratslistnew";
                }            
                
                
                ?>
                

                <tr class="<? echo $rowclass ?>">
                    <td class="<? echo $rowclass ?>"><center><p class="blogodd"><? echo $value['id'] ?></td>

                    <td class="<? echo $rowclass ?>" style="padding-left: 10px"><a class="comlinkdark" href="<? echo $value['Fightlink'] ?>" target="_blank"><? echo $value['Fightname'] ?></a></td>
                    
                    <td class="<? echo $rowclass ?>" style="padding-left: 10px"><center><p class="blogodd"><? echo $value['Published'] ?></td>
                    
                    <td class="<? echo $rowclass ?>" style="min-width: 100px;"><center><p class="blogodd">
                    
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
                            <img class="rating_tt" data-tooltip-content="#atooltip_<? echo $i ?>_<? echo $key ?>" style="width: 24px; height: 24px; cursor: help" src="https://www.wow-petguide.com/images/pets/resize50/level.png">
                            <div style="display:none">
                                <span id="atooltip_<? echo $i ?>_<? echo $key ?>">Level Pet</span>
                            </div> 
                        <? }
                        if ($fetchpet == "1") {  // Any Pet ?>
                            <img class="rating_tt" data-tooltip-content="#atooltip_<? echo $i ?>_<? echo $key ?>" style="width: 24px; height: 24px; cursor: help" src="https://www.wow-petguide.com/images/pets/resize50/any.png">
                            <div style="display:none">
                                <span id="atooltip_<? echo $i ?>_<? echo $key ?>">Any Pet</span>
                            </div>                             
                        <? }
                        if ($fetchpet > "10" && $fetchpet <= "20") { // Family pets
                            switch ($fetchpet) { 
                                case "11":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixHumanoid");
                                break;      
                                case "12":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixMagic");
                                break;
                                case "13":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixElemental");
                                break;      
                                case "14":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixUndead");
                                break;   
                                case "15":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixMech");
                                break;      
                                case "16":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixFlyer");
                                break;
                                case "17":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixCritter");
                                break;      
                                case "18":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixAquatic");
                                break;   
                                case "19":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixBeast");
                                break;      
                                case "20":
                                   $famname = _("PetCardPrefixAny")." "._("PetCardSuffixDragonkin");
                                break;  
                             } ?>
                            <img class="rating_tt" data-tooltip-content="#atooltip_<? echo $i ?>_<? echo $key ?>" style="width: 24px; height: 24px; cursor: help" src="https://www.wow-petguide.com/images/pets/resize50/<? echo $fetchpet ?>.png">
                            <div style="display:none">
                                <span id="atooltip_<? echo $i ?>_<? echo $key ?>"><? echo $famname ?></span>
                            </div>                               
                            <? }
                            if ($fetchpet > "20") { 
                                if (file_exists('images/pets/resize50/'.$all_pets[$fetchpet]['PetID'].'.png')) {
                                    $peticon = 'https://www.wow-petguide.com/images/pets/resize50/'.$all_pets[$fetchpet]['PetID'].'.png';
                                }
                                else {
                                    $peticon = 'https://www.wow-petguide.com/images/pets/resize50/unknown.png';
                                } ?>                    
                                <a href="http://<? echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<? echo $all_pets[$fetchpet]['PetID'] ?>" target="_blank"><img style="width: 24px; height: 24px" src="<? echo $peticon ?>"></a>

                            <? }
                        $i++;
                    }
                        ?>
                                

                    </td>
                    <td class="<? echo $rowclass ?>">
                    <div>
                        <div style="float: left; margin: 0 5 0 25"><p class="blogodd"><? echo $value['Favs'] ?></div>
                        <div style="float: left"><img src="https://www.wow-petguide.com/images/icon_strats_fav.png"></div>
                    </div>
                    </td>
                    <td style="width: 1%; text-align: left; padding-left: 20px"><p class="blogodd"><? echo $value['RatingAverage'] ?></td>
                    <td><center>
                        <div class="strat_star_<? echo $value['StratClass'] ?> rating_tt" data-tooltip-content="#atooltip_content_<? echo $key ?>" style="width:100px; height:20px; display:block; cursor: help"></div>
                        <div style="display:none">
                            <span id="atooltip_content_<? echo $key ?>"><? echo $value['Ratings'] ?> battler(s) voted on this strategy.</span>
                        </div>       
			        </td>
                    <td style="width: 1%; text-align: left; padding-left: 20px"><p class="blogodd"><?
                        if ($value['td'] == "0") {
                            echo "No";
                        }
                        else {
                            echo "Yes";
                        }
                        ?></td>
                    <td class="<? echo $rowclass ?>"><center>
                        <? if ($value['Attempts'] == "0") {
                            echo '<p class="blogodd">0</p>';
                        }
                        else { ?>
                        <div class="rating_tt" data-tooltip-content="#att_tooltip_content_<? echo $key ?>" style="cursor: help"><p class="blogodd"><? echo $value['Attempts']; ?></p></div>
                        <div style="display:none">
                            <span id="att_tooltip_content_<? echo $key ?>">
                                The below attempts on this strategy have been recorded by other battlers:<br><br>
                                
                                <table style="margin-left: 20px">
                                    <tr>
                                        <td><p class="blogeven" style="font-size: 14px">Successful:</td>
                                        <td style="padding-left: 18px"><p class="blogeven" style="font-size: 14px"><? echo $value['GAttempts']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><p class="blogeven" style="font-size: 14px">Unsuccessful:</td>
                                        <td style="padding-left: 18px"><p class="blogeven" style="font-size: 14px"><? echo $value['BAttempts']; ?></td>
                                    </tr>                                   
                                </table>
                                <br>
                                A more detailed breakdown will be available at a later time.
                            </span>
                        </div>                       
                        <? } ?>
                    </td>
                    <td class="<? echo $rowclass ?>"><center><p class="blogodd"><? echo $value['Views'] ?></td>
                    <td class="<? echo $rowclass ?>"><center><p class="blogodd">
                        <div class="rating_tt" data-tooltip-content="#coms_tooltip_content_<? echo $key ?>" style="cursor: help"><p class="blogodd"><? echo $value['Comments']; ?></p></div>
                        <div style="display:none">
                            <span id="coms_tooltip_content_<? echo $key ?>">
                                This number shows all comments, including those written for different language versions of the page. You can change the displayed language in your account settings.
                            </span>
                        </div>                    
                    </td>
                    <td class="<? echo $rowclassnew ?>"><center><?
                        if ($value['NewComs'] > "0") { ?>
                            <div class="newcoms_tt" data-tooltip-content="#newcoms_tooltip_content_<? echo $key ?>" style="cursor: help"><p class="blogodd"><b><? echo $value['NewComs']; ?></p></div>
                            <div style="display:none">
                                <span id="newcoms_tooltip_content_<? echo $key ?>">
                                Since your last visit to this strategy, other battlers left comments. You can jump to them directly using these quick links:
                                <br><br>
                                <table class="tooltip">
                                    <tr>
                                        <th class="tooltip">Date written</th>
                                        <th class="tooltip">Language</th>
                                        <th class="tooltip">Link</th>
                                    </tr>
                                <? if ($value['NewComsIDs'] != "") {
                                    $newcoms = explode(";",$value['NewComsIDs']);
                                    
                                    foreach ($newcoms as $comkey => $comvalue) {
                                        $comsplits = explode("_",$comvalue);
                                        $date = explode(" ",$comsplits[1]);
                                        $date = explode("-",$date[0]);
                                        ?>
                                        <tr>
                                            <td class="tooltip">
                                                <span name="time"><? echo $comsplits[1]; ?></span>
                                            </td>
                                            <td class="tooltip">
                                                <center><? echo strtoupper($comsplits[2]); ?> </center>
                                            </td>
                                            <td class="tooltip">
                                                <a target="_blank" href="?Comment=<? echo $comsplits[0]; ?>" class="tooltip">Open</a> 
                                            </td>                                              
                                        </tr>
                                        
                                       
                                    <? }
                                } ?>
                                </table>
                                </span>
                            </div>
                        <? }
                        else {
                            echo '<p class="blogodd">'.$value['NewComs'];
                        } ?>
                        </td>

            </tr>


            <?php
            }

            ?>

            </tbody>
            <tfoot>
                    <tr>
                            <td colspan="4" style="padding-top: 10px" align="right" class="table-page:previous" style="padding-top: 5px;"><a class="wowhead" style="cursor:pointer;text-decoration: none;">&lt; &lt; </a></td>
                            <td colspan="1" style="padding-top: 10px" align="center"><div style="white-space:nowrap;padding-top: 5px;"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                            <td colspan="6" style="padding-top: 10px" align="left" class="table-page:next" style="padding-top: 5px;"><a class="wowhead" style="cursor:pointer;text-decoration: none;"> &gt; &gt;</td>
                    </tr>
            </tfoot>
        </table>


        <? } ?>












        </th>
    </tr>

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
<br>





















</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
if ($urlchanger){
    echo '<script type="text/javascript" lang="javascript">';
    echo 'window.history.replaceState("object or string", "Title", "'.$urlchanger.'");';
    echo '</script>';
}
echo "<script>updateAllTimes('time')</script>";
mysqli_close($dbcon);
echo "</body>";
die;
