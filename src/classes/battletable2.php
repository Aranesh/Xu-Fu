<?php


// ======================================================================
// =================== BEGIN OF BATTLE TABLE 2.0 ========================
// ======================================================================

// Get name + link of the fight
if (!$thissub) { // could be already filled from link enrichment, no second db request
	$subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $subselector");
	$thissub = mysqli_fetch_object($subnamedb);
	if ($thissub->Parent != "0") {
		$subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $thissub->Parent");
		$thissub = mysqli_fetch_object($subnamedb);
	}
}

$map_image = "images/maps/m".$thissub->Main."_s".$thissub->id.".jpg";
$fight_image = "images/fights/m".$thissub->Main."_s".$thissub->id.".jpg";


if (!isset($thissub->{$subnameext}) || $thissub->{$subnameext} == ""){ $subnname = $thissub->Name; }
else { $subnname = $thissub->{$subnameext}; }

$subnname = stripslashes(htmlentities($subnname, ENT_QUOTES, "UTF-8"));
$subnlink = $thissub->Link;

if ($wowhdomain != "en"){
	$replacer = $wowhdomain.".wowhead.com";
	$subnlink = str_replace("www.wowhead.com",$replacer,$subnlink);
}





// =================== BEGIN OF TABLE FOR NO STRATEGIES AVAILABLE ========================
if (!$strat) {
	
	// ============================================= First Row ============================================= ?>
	<div style="width: 801px; height: 90px; background-image: url(https://www.wow-petguide.com/images/battle_01.png);">
		<div style="margin-left: 90px; padding-top: 28px; width: 550px; float: left">
			<a class="headertooltip" target="_blank" href="<?php echo $subnlink ?>">vs. <?php echo $subnname ?><?php if (file_exists($fight_image)) { echo '<span class="fight"><img src="https://www.wow-petguide.com/'.$fight_image.'" class="fight" alt=""></span>'; } ?></a>
		</div>
	</div>

	<?php if (file_exists($map_image)) { ?>
	<div class="mapicon"><span class="mapicon"><img class="mapicon" src="https://www.wow-petguide.com/images/mapicon.png"></span>
		<img class="map" src="https://www.wow-petguide.com/<?php echo $map_image ?>"/>
	</div>
	<?php }
	
	// ============================================= Second Row ============================================= ?>
	<div style="width: 801px; background-color: #185d93">
		<div style="Padding: 30px; color: #fff; font-size: 16px; font-family: MuseoSans-500">
			<center>
				<font style="font-size: 22px"><b>Oops, there is nothing here!</b></font><br>
				<br>
				It seems there is no strategy available for this fight, yet!<br>
				That's a shame, really, but <b>you</b> can help to remedy this dire situation!<br><br>
				<?php if (!$user) { ?>
					If you know a cool strategy to beat this fight, you can login or create an account and add it directly here. It's free, easy and both Xu-Fu and your fellow pet battlers will thank you for sharing your ideas!
					<br><br>
					<div class="home_rightelco">
						 <a href="#modallogin" onclick="hideloadingbnetlogin()"><button class="home_team" style="font-size: 14px; padding: 7 10 6 10;"><?php echo __("Login / Register") ?></button></a>
					</div>
				<?php }
				if ($user) { ?>
					If you know a cool strategy to beat this fight, you can add it directly here. It's quick and easy, and if you're stuck, the <a href="?m=StratCreationGuide" class="growl" style="font-size: 16px" target="_blank">Strategy Creation Guide</a> can surely help out.
					<br><br>
					<form enctype="multipart/form-data" action="index.php" method="POST">
						<input type="hidden" name="alt_edit_action" value="edit_add">
						<input type="hidden" name="addmain" value="<?php echo $mainselector ?>">
						<input type="hidden" name="addsub" value="<?php echo $subselector ?>">
						<center><button type="submit" class="bnetlogin">Add Your Strategy</button>
					</form>
				<?php } ?>
			</center>
		</div>
	</div>

	<div style="height: 90px; width: 801px; background-image: url(https://www.wow-petguide.com/images/battle_05.png)">
		<?
		if ($thissub->{$subcomext} == ""){ $subcomment = $thissub->Comment; }
		else { $subcomment = $thissub->{$subcomext}; }
		?>
		<div style="padding-left: 110px; float: left; padding-top: 14px; height: 76px">
			<img src="https://www.wow-petguide.com/images/xufu_small.png">
		</div>
		<div style="float: left; padding-left: 15px; width: 525px; position: relative; top: 50%; -webkit-transform: translateY(-50%); -ms-transform: translateY(-50%); transform: translateY(-50%);">
			<p class="comment"><i><?php echo __("Thanks for sharing!") ?></i></p>
		</div>
	</div>
	<?
	die;
}
// =================== END OF TABLE FOR NO STRATEGIES ========================









// ======================== Prepare Data ========================

// Strategy creator Icon and Name
$stratusererror = false;
$headermargin = "16";
if ($strat->User != "0") {
	$stratuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$strat->User'");
    
    if (mysqli_num_rows($stratuserdb) > "0") {
        $stratuser = mysqli_fetch_object($stratuserdb);
        $headertext = __('Strategy added by').' <span class="username" rel="'.$stratuser->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$stratuser->id.'" class="usernamelink" style="color: #ededed; font-weight: normal"><b>'.$stratuser->Name.'</a></span>';
        if ($stratuser->UseWowAvatar == "0"){
            $stratusericon = '<span class="username" rel="'.$stratuser->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$stratuser->id.'"><img src="https://www.wow-petguide.com/images/pets/'.$stratuser->Icon.'.png" style="width:50px; height: 50px; float: left" class="commentpic"></a></span>';
        }
        else if ($stratuser->UseWowAvatar == "1"){
            $stratusericon = '<span class="username" rel="'.$stratuser->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$stratuser->id.'"><img src="https://www.wow-petguide.com/images/userpics/'.$stratuser->id.'.jpg" style="width:50px; height: 50px; float: left" class="commentpic"></a></span>';
        }
    }
    else {
        $stratusererror = true;
    }
}

if ($strat->User == "0" OR $stratusererror) {
    $stratusericon = '<img src="https://www.wow-petguide.com/images/userpics/del_acc.jpg" style="width:50px; height: 50px; float: left" class="commentpic">';
    if ($strat->CreatedBy == "") {
        $headertext = "";
        $headermargin = "28";
    }
    else {
        $headertext = __('Strategy added by').' '.$strat->CreatedBy;
    }
}

// Remove unseen comments from strategy if the creator himself visits
if ($user->id == $strat->User && ($strat->NewComs != "0" OR $strat->NewComsIDs != "")) {
	echo '<script>$.growl.notice({ message: "'.$strat->NewComs.' new comment(s) have been posted here since your last visit. <br>They have now been marked as read.", duration: "15000", size: "large", location: "tr" });</script>';
    mysqli_query($dbcon, "UPDATE Alternatives SET NewComs = '0' WHERE id  = '$strat->id'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Alternatives SET NewComsIDs = '' WHERE id  = '$strat->id'") OR die(mysqli_error($dbcon));
}
?>
	
<div>
	<?php // ============================================= First Row ============================================= ?>
	<div style="width: 801px; height: 90px; background-image: url(https://www.wow-petguide.com/images/battle_01.png);">
		<div style="display: table; margin-left: 90px; padding-top: 0px; height: 90px; width: 550px; float: left;">
		<span style="display: table-cell; vertical-align: middle;">
			<a class="headertooltip" target="_blank" href="<?php echo $subnlink ?>">vs. <?php echo $subnname ?><?php if (file_exists($fight_image)) { echo '<span class="fight"><img src="https://www.wow-petguide.com/'.$fight_image.'" class="fight" alt=""></span>'; } ?></a>
            <?php if ($headertext) { ?>
			<br><p class="comheaddark" style="color: #ededed"><?php echo $headertext ?></p>
            <?php }
			if ($userrights[4] == "20") { // Deactivated - change to "1" to see it with admin account
			$sugdb = mysqli_query($dbcon, "SELECT * FROM Suggestions WHERE Main = '$mainselector' AND Sub = '$subselector' AND Reviewed = '0' ORDER BY Date DESC");
			if (mysqli_num_rows($sugdb) > "0") {
				echo '<br><a href="suggestions.php?m='.$mainselector.'&s='.$subselector.'" target="_blank" class="wowhead" style="color: #fff; font-size: 13px; text-decoration: none; font-weight: normal">Unreviewed Suggestions: '.mysqli_num_rows($sugdb).'</a>';
			}
		} ?>
		</span>
	</div>

		<?php // Favorite Icon
			if (!$user) $favtttext = "You must be logged in to set a favorite strategy";
			else $favtttext = "For every battle you can set one strategy as your favourite";
		?>
		<div style="height: 90px; z-index: 2; float: left;">
			<div style="padding-top: 12px; padding-left: 26px;">
				<?php $stratfavsdb = mysqli_query($dbcon, "SELECT * FROM UserFavStrats WHERE Sub = '$subselector' && Strategy = '$strat->id'");
				if (mysqli_num_rows($stratfavsdb) > "0") { $stratfavnum = mysqli_num_rows($stratfavsdb); }
				else { $stratfavnum = ""; } ?>

				<div class="tt_fav" title="<?php echo $favtttext ?>" style="width: 40; z-index: 2; position: absolute; <?php if ($user) echo "cursor: pointer"; ?>" <?php if ($user) { ?> onclick="toggle_favstrat('<?php echo $subselector ?>','<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>') <?php } ?>">
					<div style="width: 40px; height: 15px"></div>
					<div style="width: 47px">
						<center><p class="blogeven" style="font-size: 11px"><span id="favcounter"><?php echo $stratfavnum ?></span></p>
					</div>
				</div>

				<div style="width: 40; position: absolute; z-index: 1" class="tt_fav" title="<?php echo $favtttext ?>">

					<?php if (!$user) { ?>
						<img src="https://www.wow-petguide.com/images/icon_unfavedstrat.gif">
					<?php }
					else {
						$favstratdb = mysqli_query($dbcon, "SELECT * FROM UserFavStrats WHERE User = '$user->id' AND Sub = '$subselector'");
						if (mysqli_num_rows($favstratdb) < "1"){
							$stratfaved = "false";
							$favstraticon = "icon_unfavedstrat.gif";
						}
						else {
							$faventry = mysqli_fetch_object($favstratdb);
							if ($faventry->Strategy == $strat->id) {
								$stratfaved = "true";
								$favstraticon = "icon_favedstrat.gif";
							}
							else {
								$stratfaved = "false";
								$favstraticon = "icon_unfavedstrat.gif";
							}
						} ?>

						<img id="favstraticon" onclick="toggle_favstrat('<?php echo $subselector ?>','<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>')" style="cursor: pointer" src="https://www.wow-petguide.com/images/<?php echo $favstraticon ?>">

					<?php } ?>
					<script>
						$(document).ready(function() {
							$('.tt_fav').tooltipster({
								maxWidth: '200',
								delay: 1000,
								theme: 'tooltipster-smallnote'
							});
						});
					</script>
				</div>
			</div>

			<div style="height: 40">
			</div>

			<?php // Star Ratings ?>
			<?
				$stratratingdb = mysqli_query($dbcon, "SELECT * FROM UserStratRating WHERE User = '$user->id' AND Strategy = '$strat->id'");
				if (mysqli_num_rows($stratratingdb) > "0"){
					$ownrating = mysqli_fetch_object($stratratingdb);
					$showown = "block";
				}
				else {
					$showown = "none";
				}

				$allratingdb = mysqli_query($dbcon, "SELECT * FROM UserStratRating WHERE Strategy = '$strat->id'");

				$avgratingdb = mysqli_query($dbcon, "SELECT AVG(Rating) AS rating_avg FROM UserStratRating WHERE Strategy = '$strat->id'");
				$avgrating = $avgratingdb->fetch_assoc();

				if (!$avgrating['rating_avg']) { // No ratings
					$stratclass = "0";
				}
				else {
					if ($avgrating['rating_avg'] <= "1.25") { $stratclass = "1"; }
					if ($avgrating['rating_avg'] > "1.25") { $stratclass = "1_5"; }
					if ($avgrating['rating_avg'] > "1.75") { $stratclass = "2"; }
					if ($avgrating['rating_avg'] > "2.25") { $stratclass = "2_5"; }
					if ($avgrating['rating_avg'] > "2.75") { $stratclass = "3"; }
					if ($avgrating['rating_avg'] > "3.25") { $stratclass = "3_5"; }
					if ($avgrating['rating_avg'] > "3.75") { $stratclass = "4"; }
					if ($avgrating['rating_avg'] > "4.25") { $stratclass = "4_5"; }
					if ($avgrating['rating_avg'] > "4.75") { $stratclass = "5"; }
				}
			?>
			<div style="z-index: 2;" class="rating_tooltip" data-tooltip-content="#rating_tooltip_content">
				<div class="strat_stars_container">
					<div <?php if ($user && $user->id != $strat->User) { ?> id="star1_control" onclick="rate_strategy('<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','1','<?php echo $showown ?>','<?php echo $avgrating['rating_avg'] ?>')" <?php } else echo 'style="cursor: default"'; ?> class="strat_star"></div>
					<div <?php if ($user && $user->id != $strat->User) { ?> id="star2_control" onclick="rate_strategy('<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','2','<?php echo $showown ?>','<?php echo $avgrating['rating_avg'] ?>')" <?php } else echo 'style="cursor: default"'; ?> class="strat_star"></div>
					<div <?php if ($user && $user->id != $strat->User) { ?> id="star3_control" onclick="rate_strategy('<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','3','<?php echo $showown ?>','<?php echo $avgrating['rating_avg'] ?>')" <?php } else echo 'style="cursor: default"'; ?> class="strat_star"></div>
					<div <?php if ($user && $user->id != $strat->User) { ?> id="star4_control" onclick="rate_strategy('<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','4','<?php echo $showown ?>','<?php echo $avgrating['rating_avg'] ?>')" <?php } else echo 'style="cursor: default"'; ?> class="strat_star"></div>
					<div <?php if ($user && $user->id != $strat->User) { ?> id="star5_control" onclick="rate_strategy('<?php echo $strat->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','5','<?php echo $showown ?>','<?php echo $avgrating['rating_avg'] ?>')" <?php } else echo 'style="cursor: default"'; ?> class="strat_star"></div>
					<div id="strat_stars" class="strat_star_<?php echo $stratclass ?>" style="width:100px; height:20px; display:block;"></div>
				</div>
			</div>

			<div style="margin-left: 5px">
				<img id="rating_suc" src="images/bt_star_success.gif" style="display: none">
			</div>
			
			<?php if ($userrights['EditStrats'] == "yes") { ?>
			<div style="margin-left: 36px">
				<a data-remodal-target="modal_reset_stats" class="usernamelinkwith " style="font-size: 11px; font-weight: normal; cursor: pointer">Reset</a>
			</div>
			
			<div class="remodal remodalstratedit" data-remodal-id="modal_reset_stats">
					<table style="width: 400px" class="profile">
						<tr class="profile">
							<th colspan="2" style="width: 100%" class="profile">
								<table>
									<tr>
										<td><img src="images/icon_report.png" style="padding-right: 5px"></td>
										<td><p class="blogodd"><span style="white-space: nowrap;"><b>Reset Ratings</span></td>
									</tr>
								</table>
							</th>
						</tr>
			
						<tr class="profile">
							<td class="collectionbordertwo" colspan="2" style="font-family: MuseoSans-500; font-size: 14px">
								<div>
									<center><br><b>This option removes all favourites and ratings! </b><br><br>
									It can be useful if a strategy was changed massively. <br>Be aware that it cannot be undone! <br>Are you sure?
									<br><br>
									<form enctype="multipart/form-data" action="index.php?Strategy=<?php echo $strat->id ?>" method="POST">
										<input type="hidden" name="alt_edit_action" value="edit_reset">
										<input type="hidden" name="currentstrat" value="<?php echo $strat->id ?>">
										<button type="submit" class="redlarge">Yes, reset ratings</button>
									</form>
									<br>
								</div>                       
							</td>
						</tr>
					</table>
				</div>
			
				<script>
					var options = {
						hashTracking: false
					};
					$('[data-remodal-id=modal_reset_stats]').remodal(options);
				</script>
			<?php } ?>
			

			<div style="display: none">
				<span id="rating_tooltip_content">
					<table style="width: 100%">
						<tr>
							<td>
								<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto; ">Average rating:
							</td>
							<td style="text-align: right">
								<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto; "><span id="rating_average"><?php echo round($avgrating['rating_avg'],1); ?></span> <img style="vertical-align:middle; margin-bottom: 3px; height: 14px; width: 14px" src="https://www.wow-petguide.com/images/icon_star_rating.png">
							</td>
						</tr>
						<tr>
							<td>
								<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto; ">Ratings:
							</td>
							<td style="text-align: right">
								<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto; "><span id="rating_total"><?php echo mysqli_num_rows($allratingdb) ?></span>
							</td>
						</tr>
						<tr>
							<td>
								<div id="rating_own_intro" style="margin-top: 10px; display: <?php echo $showown ?>">
									<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto; ">Your vote:
								</div>
							</td>
							<td style="text-align: right">
								<div id="rating_own" style="margin-top: 10px; display: <?php echo $showown ?>">
									<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto; "><span id="rating_own"><span id="rating_own_number"><?php echo $ownrating->Rating ?></span> <img style="vertical-align:middle; margin-bottom: 3px; height: 14px; width: 14px" src="https://www.wow-petguide.com/images/icon_star_rating.png">
								</div>
							</td>
						</tr>
					</table>
				</span>
			</div>

			<script>
				$(document).ready(function() {
					$('.rating_tooltip').tooltipster({
						minWidth: '200',
						animation: 'fade',
						theme: 'tooltipster-smallnote'
					});
				});
			</script>
		</div>
	</div>


	<?php 
		
	// Interim Row - Shadowlands Disclaimer
	
	// Do not show in Shadowlands category
	if ($mainselector != 70 && $mainselector != 56 && $mainselector != 48 && $mainselector != 72 && $mainselector != 73) {
		
		$sl_caution_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = '$strat->id' AND Tag = 30");
		$sl_problem_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = '$strat->id' AND Tag = 28");
		$sl_color = "green";
        if (mysqli_num_rows($sl_caution_db) > "0") {
            $sl_color = "yellow";
        }
        if (mysqli_num_rows($sl_problem_db) > "0") {
            $sl_color = "red";
        }
		
		switch ($sl_color) {
			case "yellow":
				$disc_green = "sl_disclaimer_green_i";
				$disc_red = "sl_disclaimer_red_i";
				$disc_yellow = "sl_disclaimer_yellow_a";
				break;
			case "green":
				$disc_green = "sl_disclaimer_green_a";
				$disc_red = "sl_disclaimer_red_i";
				$disc_yellow = "sl_disclaimer_yellow_i";
				break;
			case "red":
				$disc_green = "sl_disclaimer_green_i";
				$disc_red = "sl_disclaimer_red_a";
				$disc_yellow = "sl_disclaimer_yellow_i";
				break;
		}
		?>
		
		<div style="width: 801px; height: 40px; background-image: url(https://www.wow-petguide.com/images/bt_shadowlands_bg.png)">
			<div style="width: 801px; height: 40px; padding: 5px">
				<div class="sl_disclaimer_bg">
					<div style="float: left; padding-top: 1px; padding-right: 15px;"><a class="sl_disclaimer" href="?News=257"><?php echo __('Strategy Readiness'); ?>:</a></div>
					<div class="sl_ampel" data-tooltip-content="#sl_ampel_tt">
						<div class="sl_disclaimer <?php echo $disc_green ?>"></div>
						<div class="sl_disclaimer <?php echo $disc_yellow ?>"></div>
						<div class="sl_disclaimer <?php echo $disc_red ?>"></div>
					</div>
					
					<div style="display: none">
						<span id="sl_ampel_tt">
						   <?php if ($sl_color == "green") { ?>
								This strategy has been tested for Shadowlands and will continue to work after the Shadowlands Pre-Patch.
								<br><br>
								You can read more info about the upcoming pet battle changes <a class="comlink" href="?News=249" target="_blank">here.</a>
						   <?php }
							if ($sl_color == "yellow") { ?>
								This strategy has not been tested for the upcoming pet battle changes with Shadowlands, yet.<br><br>
								To learn more about Xu-Fu's preparation process, please read the <a class="comlink" href="?News=257" target="_blank">news announcement.</a>
						   <?php }
							if ($sl_color == "red") { ?>
								This strategy will no longer work after the Shadowlands Pre-Patch goes live.<br>
								It will need substantial changes or might have to be unpublished.<br><br>
								To learn more about Xu-Fu's preparation process, have a look at the <a class="comlink" href="?News=257" target="_blank">news announcement.</a>
						   <?php } ?>
						</span>
					</div>

					<script>
							$(document).ready(function() {
								$('.sl_ampel').tooltipster({
									maxWidth: '400',
									minWidth: '200',
									side: ['bottom'],
									interactive: 'true',
									theme: 'tooltipster-smallnote'
								});
							});
					</script>
					
				</div>
				<a href="?News=248" target="_blank"><img src="images/sl_disclaimer_logo.png" style="height: 30px; float: right; margin-right: 10px"></a>
			</div>
		</div>
		
	<?php }
	
	
	
	
	// Second Row - Pets ?>
<div style="width: 801px; height: 170px">
	<div class="bt_2_buttons" style="float: left; width: 90px; height: 170px">

		<!-- Button Column #1: Rematch -->
		<div style="margin-left: 10px; margin-top: 20px;" id="rematch_string_<?php echo $strat->id ?>" data-clipboard-text="placeholder">
			<button class="clip_button" onclick="create_rematch('<?php echo $strat->id ?>','<?php echo $language ?>')"><?php echo __("Rematch String") ?></button>
		</div>

		<div class="remtt" style="display:none;" id="rematchconfirm_<?php echo $strat->id ?>"><?php echo __("Copied to clipboard!") ?></div>

		<script>
		var btn = document.getElementById('rematch_string_<?php echo $strat->id ?>');
		var clipboard = new Clipboard(btn);

		clipboard.on('success', function(e) {
			console.log(e);
				$('#rematchconfirm_<?php echo $strat->id ?>').delay(0).fadeIn(500);
				$('#rematchconfirm_<?php echo $strat->id ?>').delay(1200).fadeOut(500);
			});
		clipboard.on('error', function(e) {
			console.log(e);
		});
		</script>

		<?php // Rematch steps selector
		if ($usersettings) {
			if ($usersettings['RematchSteps'] == "on") {
				$rmstepsswitch = "1";
			}
			else {
				$rmstepsswitch = "0";
			}
		}
		else {
			$rmstepsswitch = "1";
		}
		?>

		<div style="margin-left: 10px; margin-top: 8px;">
			<p class="commenteven"><b><?php echo __('Incl. steps:'); ?></b>
			<br>
			<center>
				<div id="ttcolswitch" class="publishswitch" style="margin-top: 5px; margin-bottom: 12px">
					<input type="checkbox" class="publishswitch-checkbox" id="us_rematchsteps" onchange="us_rematchsteps('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>');" <?php if ($rmstepsswitch == "1") { echo "checked"; } ?>>
					<label class="publishswitch-label" for="us_rematchsteps">
					<span class="publishswitch-inner"></span>
					<span class="publishswitch-switch"></span>
					</label>
				</div>
			</center>
			<span id="rm_steps_switch" style="display: none"><?php echo $rmstepsswitch ?></span>
		</div>




	<?php // -- TD Script Button -->

	if ($strat->tdscript) {
		 $outputscript = htmlspecialchars($strat->tdscript, ENT_QUOTES);
		 $previewscript = htmlentities($strat->tdscript, ENT_QUOTES, "UTF-8");
		 $previewscript = nl2br($previewscript);
		 ?>
		<div style="margin-left: 10px; margin-top: 8px;" id="tdbtn<?php echo $subselector ?>" data-clipboard-text="<?php echo $outputscript ?>">
			<button class="clip_button td_tooltip" data-tooltip-content="#td_tooltip"><?php echo __('TD Script'); ?></button>
		</div>

		<div style="display: none">
			<span id="td_tooltip">
				<?php echo __('Click the button to copy the script into your clipboard:'); ?><br><br>
			   <?php echo $previewscript ?>
			</span>
		</div>

		<div class="remtt" style="display:none;" id="scriptconfirm<?php echo $subselector ?>"><?php echo __("Copied to clipboard!") ?></div>

		<script>
			$(document).ready(function() {
				$('.td_tooltip').tooltipster({
					theme: 'tooltipster-smallnote'
				});
			});
								
			 var btn = document.getElementById('tdbtn<?php echo $subselector ?>');
			 var clipboard = new Clipboard(btn);
	
			 clipboard.on('success', function(e) {
				console.log(e);
				$('#scriptconfirm<?php echo $subselector ?>').delay(0).fadeIn(500);
				$('#scriptconfirm<?php echo $subselector ?>').delay(1200).fadeOut(500);
			 });
	
			 clipboard.on('error', function(e) {
				  console.log(e);
			 });
		</script>
	<?php } ?>


	</div>


	<?php // Pet Cards ?>
    <div style="display:none">
        <span id="rm_name_<?php echo $strat->id ?>"><?php echo $subnname ?></span>
        <span id="rm_fight_<?php echo $strat->id ?>"><?php echo $thissub->RematchID ?></span>
    </div>

   <?
	$updatetags['1'] = 0; // tagsinfo 
	
   // Import Substitutes from URL if present
	$customsubs = array();
   $customsubs_tempz = isset($_GET['Substitutes']) ? $_GET['Substitutes'] : null;
   if (!$customsubs_tempz) {
      $customsubs_tempz = isset($_GET['substitutes']) ? $_GET['substitutes'] : null;
   }
   if ($customsubs_tempz) {
	   $customsubs_temp = explode("-", $customsubs_tempz);
	   foreach ($customsubs_temp as $key => $value) {
			if ($value[0] == "1" OR $value[0] == "2" OR $value[0] == "3") {
			  $cuspieces = explode(":", $value);
			  
			  // Converting NPC IDs into Species from Substitutes - to make old format work as well
			  if ($cuspieces[1] > 2670 && !$all_pets[$cuspieces[1]]['Name']) {
				  $search_pet = searchForId($cuspieces[1], $all_pets, array());
				  $cuspieces[1] = $search_pet[0];
			  }
			  
				if ($all_pets[$cuspieces[1]]['Name'] != "") {
					$customsubs[$value[0]] = $cuspieces[1];
				}
			}
		}
	}

	$i = "1";
    $temp_collection = $collection;

    $lowestlevel = 25;

	while ($i < "4") {
		$skillwildcards = "0";
		switch ($i) {
			case "1":
				$petcountimg = "images/bt_2_firstpet.png";
				$fetchpet = $strat->PetID1;
				if ($fetchpet <= 20 || $strat->SkillPet11 == "0") { $skill1 = "*"; $skillwildcards++; $skill1nb = "*";}
				else if ($strat->SkillPet11 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1']; $skill1nb = "1"; }
				else if ($strat->SkillPet11 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4']; $skill1nb = "2"; }
				if ($fetchpet <= 20 || $strat->SkillPet12 == "0") { $skill2 = "*"; $skillwildcards++; $skill2nb = "*";}
				else if ($strat->SkillPet12 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; $skill2nb = "1"; }
				else if ($strat->SkillPet12 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; $skill2nb = "2"; }
				if ($fetchpet <= 20 || $strat->SkillPet13 == "0") { $skill3 = "*"; $skillwildcards++; $skill3nb = "*";}
				else if ($strat->SkillPet13 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; $skill3nb = "1"; }
				else if ($strat->SkillPet13 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; $skill3nb = "2"; }
                $reqlevel = $strat->PetLevel1;
				$reqbreeds = $strat->Breeds1;
				$reqhp = $strat->Health1;
				$reqsp = $strat->Speed1;
				$reqpw = $strat->Power1;
				break;
			case "2":
				$petcountimg = "images/bt_2_scndpet.png";
				$fetchpet = $strat->PetID2;
				if ($fetchpet <= 20 || $strat->SkillPet21 == "0") { $skill1 = "*"; $skillwildcards++; $skill1nb = "*";}
				else if ($strat->SkillPet21 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1']; $skill1nb = "1"; }
				else if ($strat->SkillPet21 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4']; $skill1nb = "2"; }
				if ($fetchpet <= 20 || $strat->SkillPet22 == "0") { $skill2 = "*"; $skillwildcards++; $skill2nb = "*";}
				else if ($strat->SkillPet22 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; $skill2nb = "1"; }
				else if ($strat->SkillPet22 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; $skill2nb = "2"; }
				if ($fetchpet <= 20 || $strat->SkillPet23 == "0") { $skill3 = "*"; $skillwildcards++; $skill3nb = "*";}
				else if ($strat->SkillPet23 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; $skill3nb = "1"; }
				else if ($strat->SkillPet23 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; $skill3nb = "2"; }
                $reqlevel = $strat->PetLevel2;
				$reqbreeds = $strat->Breeds2;
				$reqhp = $strat->Health2;
				$reqsp = $strat->Speed2;
				$reqpw = $strat->Power2;
				break;
			case "3":
				$petcountimg = "images/bt_2_thirdpet.png";
				$fetchpet = $strat->PetID3;
				if ($fetchpet <= 20 || $strat->SkillPet31 == "0") { $skill1 = "*"; $skillwildcards++; $skill1nb = "*";}
				else if ($strat->SkillPet31 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1']; $skill1nb = "1"; }
				else if ($strat->SkillPet31 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4']; $skill1nb = "2"; }
				if ($fetchpet <= 20 || $strat->SkillPet32 == "0") { $skill2 = "*"; $skillwildcards++; $skill2nb = "*";}
				else if ($strat->SkillPet32 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; $skill2nb = "1"; }
				else if ($strat->SkillPet32 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; $skill2nb = "2"; }
				if ($fetchpet <= 20 || $strat->SkillPet33 == "0") { $skill3 = "*"; $skillwildcards++; $skill3nb = "*";}
				else if ($strat->SkillPet33 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; $skill3nb = "1"; }
				else if ($strat->SkillPet33 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; $skill3nb = "2"; }
                $reqlevel = $strat->PetLevel3;
				$reqbreeds = $strat->Breeds3;
				$reqhp = $strat->Health3;
				$reqsp = $strat->Speed3;
				$reqpw = $strat->Power3;
				break;
		}

		$petlist_feedback[$i]['id'] = $fetchpet;
		$petslotinfo[$i]['DirectSub'] = null;
		$petslotinfo[$i]['Subscount'] = 0;
		
		if ($fetchpet > "20" ) {  // Create pet card for a regular pet 
		$petarray = array();
		$petarray[0]['PetID'] = $fetchpet;   // Add strategy pet as first
		$petarray[0]['Primary'] = "true";    // Make strategy pet the primarily shown one
        $petslotinfo[$i]['PetID'] = $fetchpet;
		$petarray[0]['Skill1NB'] = $skill1nb;
		$petarray[0]['Skill1ID'] = $skill1;
		$petarray[0]['Skill2NB'] = $skill2nb;
		$petarray[0]['Skill2ID'] = $skill2;
		$petarray[0]['Skill3NB'] = $skill3nb;
		$petarray[0]['Skill3ID'] = $skill3;
		$breedsgathered = "";
		$breedarray = array();
		$cutbreeds = explode(",", $reqbreeds);

		// Breed info - Case 0 - no requirements
		$petarray[0]['ReqBreed'] = "Any";

		// Specific requirements Case 1 - Only breed is limited
		if ($reqbreeds != "" && $reqhp == "" && $reqsp == "" && $reqpw == "") {
			$petarray[0]['ReqBreed'] = str_replace(",", ", ", $reqbreeds);
				$updatetags['1'] = 1; // tagsinfo  
		}

		// Specific requirements Case 2 and Case 3 - stats are limited and possibly breeds as well
		if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {
			$numbreeds = 0;
			$posbreeds = "0";
			$listbreeds = "";

			foreach ($allbreeds as $breed => $breedstats) {
				if ($all_pets[$fetchpet][$breed] == "1") {      // The current breed is possible for this pet, now check if stats are OK
					$thishp = round(100+(($all_pets[$fetchpet]['Health']+$breedstats['Health'])*162.5), 0, PHP_ROUND_HALF_DOWN);
					$thissp = round(($all_pets[$fetchpet]['Speed']+$breedstats['Speed'])*32.5, 0, PHP_ROUND_HALF_DOWN);
					$thispw = round(($all_pets[$fetchpet]['Power']+$breedstats['Power'])*32.5, 0, PHP_ROUND_HALF_DOWN);
					$reqhpnumber = substr($reqhp, 1);
					$reqspnumber = substr($reqsp, 1);
					$reqpwnumber = substr($reqpw, 1);

					$breedok = "true";

					if ($reqhp != "") {
						switch ($reqhp[0]) {
							case "<":
								if ($thishp >= $reqhpnumber) {
									$breedok = "false";
								}
								break;
							case ">":
								if ($thishp <= $reqhpnumber) {
									$breedok = "false";
								}
								break;
							case "=":
								if ($thishp != $reqhpnumber) {
									$breedok = "false";
								}
								break;
						}
					}

					if ($reqsp != "") {
						switch ($reqsp[0]) {
							case "<":
								if ($thissp >= $reqspnumber) {
									$breedok = "false";
								}
								break;
							case ">":
								if ($thissp <= $reqspnumber) {
									$breedok = "false";
								}
								break;
							case "=":
								if ($thissp != $reqspnumber) {
									$breedok = "false";
								}
								break;
						}
					}

					if ($reqpw != "") {
						switch ($reqpw[0]) {
							case "<":
								if ($thispw >= $reqpwnumber) {
									$breedok = "false";
								}
								break;
							case ">":
								if ($thispw <= $reqpwnumber) {
									$breedok = "false";
								}
								break;
							case "=":
								if ($thispw != $reqpwnumber) {
									$breedok = "false";
								}
								break;
						}
					}

					// Check if the breed is included in the creator selected breeds
					if ($reqbreeds != "" && $breedok == "true" && !in_array($breed, $cutbreeds)) {
						$breedok = "false";
					}

					// List all breeds together that passed all tests btbr01
					if ($breedok == "true") {
						$listbreeds = $listbreeds.$breed.", ";
						$posbreeds++;
					}

					// Create Breed Array for all breeds of this pet:
					 $breedarray[$numbreeds]['Breed'] = $breed;
					 $breedarray[$numbreeds]['BreedOK'] = $breedok;
					 $breedarray[$numbreeds]['PW'] = $thispw;
					 $breedarray[$numbreeds]['SP'] = $thissp;
					 $breedarray[$numbreeds]['HP'] = $thishp;
					 $breedok = "";
					 $numbreeds++;
				}
				$breedsgathered = "true";
			}

			// All breeds are fine:
			if ($posbreeds > "0" && $numbreeds == $posbreeds) {
				$petarray[0]['ReqBreed'] = "Any";
			}
			// Only some breeds are fine:
			if ($posbreeds > "0" && $numbreeds != $posbreeds) {
				$petarray[0]['ReqBreed'] = substr($listbreeds, 0, -2);
				$updatetags['1'] = 1; // tagsinfo 
			}
			// no breed is fine:
			if ($posbreeds == "0") {
				$petarray[0]['ReqBreed'] = "Uncertain";
				$petarray[0]['BreedError'] = "statsexcluded";
				$updatetags['1'] = 1; // tagsinfo 
			}
		}

		// Create breed array for this pet
		 $numbreeds = 0;
		 if ($breedsgathered != "true") {
			foreach ($allbreeds as $breed => $breedstats) {
			   if ($all_pets[$fetchpet][$breed] == "1") {
					 if ($reqbreeds != "" && !in_array($breed, $cutbreeds)) {
						$breedok = "false";
					 }
					 else {
						$breedok = "true";
					 }
				  $breedarray[$numbreeds]['Breed'] = $breed;
				  $breedarray[$numbreeds]['BreedOK'] = $breedok;
				  $breedarray[$numbreeds]['PW'] = round(($all_pets[$fetchpet]['Power']+$breedstats['Power'])*32.5, 0, PHP_ROUND_HALF_DOWN);
				  $breedarray[$numbreeds]['SP'] = round(($all_pets[$fetchpet]['Speed']+$breedstats['Speed'])*32.5, 0, PHP_ROUND_HALF_DOWN);
				  $breedarray[$numbreeds]['HP'] = round(100+(($all_pets[$fetchpet]['Health']+$breedstats['Health'])*162.5), 0, PHP_ROUND_HALF_DOWN);
				  $numbreeds++;
				}
			}
		 }
		 
		 $petarray[0]['BreedInfo'] = $breedarray;
		 $petarray[0]['OwnedValue'] = "3"; // Set prio of the primary pet to highest value 3 always
		 $fbpet[$i][0]['BreedInfo'] = $breedarray;

		 // Check against user collection
		 if ($temp_collection) {
			$searchFor = $petarray[0]['PetID'];
			$petarray[0]['Owned'] = "0";
			$petarray[0]['AllOwned'] = array_filter($temp_collection, function($element) use($searchFor){ return isset($element['Species']) && $element['Species'] == $searchFor;});
			$usedpet = "";
			foreach ($petarray[0]['AllOwned'] as $mypetkey => $mypetvalue) {
			   if (($mypetvalue['Level'] != "25" OR $mypetvalue['Quality'] != "3") && $petarray[0]['Owned'] != "2") { // Pet is not rare level 25
				  $petarray[0]['Owned'] = "1";
				  $usedpet = $mypetkey;
			   }
			   else {
				$breedokay = "false";
                if ($petarray[0]['Owned'] != "2") {
                    $petarray[0]['Owned'] = "1";
                }
				if (!$breedarray) {  // This happens only if there is no breed info available for the pet. Any breed is accepted in that case
					$petarray[0]['Owned'] = "2";
                    $petarray[0]['RematchBreed'] = $mypetvalue['Breed'];
					$petarray[0]['SelectedBreed'] = $mypetvalue['Breed'];
					$usedpet = $mypetkey;
				}
				  foreach ($breedarray as $breedkey => $breedvalues) {
					 if ($breedvalues['BreedOK'] == "true" && $breedvalues['Breed'] == $mypetvalue['Breed']) {
						$petarray[0]['Owned'] = "2";
                        $petarray[0]['RematchBreed'] = $mypetvalue['Breed'];
						$petarray[0]['SelectedBreed'] = $mypetvalue['Breed'];
						$usedpet = $mypetkey;
					 }
				  }
			   }
			}
			if ($usedpet) {
				unset($temp_collection[$usedpet]);
				$temp_collection = array_values($temp_collection);
			}
			$searchFor = "";
			$petarray[0]['OwnedCount'] = count($petarray[0]['AllOwned']);
		 }

		// Go through all pets to identify possible substitutes

		$countsubstitutes = "1";
		foreach ($all_pets as $key => $value) {  // Go through all possible pets
			if ($value['Species'] != $fetchpet && $value['Family'] == $all_pets[$fetchpet]['Family']) {  // exclude the strategy pet and all other families
			
				// First case, all 3 skills are required:
				if ($skillwildcards != "3") {
					$skillstring = "-*-*-".$value['Skill1']
                                 ."-".$value['Skill2']
                                 ."-".$value['Skill3']
                                 ."-".$value['Skill4']
                                 ."-".$value['Skill5']
                                 ."-".$value['Skill6']
                                 ."-";

					if (strpos($skillstring, '-' . $skill1 . '-') !== false &&
                        strpos($skillstring, '-' . $skill2 . '-') !== false &&
                        strpos($skillstring, '-' . $skill3 . '-') !== false
                    )
                    {   // Check only pets that have all skills required

						$petarray[$countsubstitutes]['PetID'] = $value['Species'];
						$petarray[$countsubstitutes]['Name'] = $all_pets[$value['Species']]['Name'];
						$petarray[$countsubstitutes]['Skill1NB'] = "*";
						$petarray[$countsubstitutes]['Skill2NB'] = "*";
						$petarray[$countsubstitutes]['Skill3NB'] = "*";
						$petarray[$countsubstitutes]['OwnedValue'] = "2"; // Set sorting value of substitutes to default 2, lower than primary pet. this will be overridden with other values if a collection is saved
						$s1used = "";
						$s2used = "";
						$s3used = "";
						$ignorepet = "";

						switch ($skill1) {   // set skill for slot 1
							case $value['Skill1']:
								$petarray[$countsubstitutes]['Skill1NB'] = "1";
								$petarray[$countsubstitutes]['Skill1ID'] = $skill1;
								$s1used = "true";
								break;
							case $value['Skill2']:
								$petarray[$countsubstitutes]['Skill2NB'] = "1";
								$petarray[$countsubstitutes]['Skill2ID'] = $skill1;
								$s2used = "true";
								break;
							case $value['Skill3']:
								$petarray[$countsubstitutes]['Skill3NB'] = "1";
								$petarray[$countsubstitutes]['Skill3ID'] = $skill1;
								$s3used = "true";
								break;
							case $value['Skill4']:
								$petarray[$countsubstitutes]['Skill1NB'] = "2";
								$petarray[$countsubstitutes]['Skill1ID'] = $skill1;
								$s1used = "true";
								break;
							case $value['Skill5']:
								$petarray[$countsubstitutes]['Skill2NB'] = "2";
								$petarray[$countsubstitutes]['Skill2ID'] = $skill1;
								$s2used = "true";
								break;
							case $value['Skill6']:
								$petarray[$countsubstitutes]['Skill3NB'] = "2";
								$petarray[$countsubstitutes]['Skill3ID'] = $skill1;
								$s3used = "true";
								break;
						}
						switch ($skill2) {   // set skill for slot 2
							case $value['Skill1']:
								$petarray[$countsubstitutes]['Skill1NB'] = "1";
								$petarray[$countsubstitutes]['Skill1ID'] = $skill2;
								if ($s1used == "true") { $ignorepet = "true"; }
								else { $s1used = "true"; }
								break;
							case $value['Skill2']:
								$petarray[$countsubstitutes]['Skill2NB'] = "1";
								$petarray[$countsubstitutes]['Skill2ID'] = $skill2;
								if ($s2used == "true") { $ignorepet = "true"; }
								else { $s2used = "true"; }
								break;
							case $value['Skill3']:
								$petarray[$countsubstitutes]['Skill3NB'] = "1";
								$petarray[$countsubstitutes]['Skill3ID'] = $skill2;
								if ($s3used == "true") { $ignorepet = "true"; }
								else { $s3used = "true"; }
								break;
							case $value['Skill4']:
								$petarray[$countsubstitutes]['Skill1NB'] = "2";
								$petarray[$countsubstitutes]['Skill1ID'] = $skill2;
								if ($s1used == "true") { $ignorepet = "true"; }
								else { $s1used = "true"; }
								break;
							case $value['Skill5']:
								$petarray[$countsubstitutes]['Skill2NB'] = "2";
								$petarray[$countsubstitutes]['Skill2ID'] = $skill2;
								if ($s2used == "true") { $ignorepet = "true"; }
								else { $s2used = "true"; }
								break;
							case $value['Skill6']:
								$petarray[$countsubstitutes]['Skill3NB'] = "2";
								$petarray[$countsubstitutes]['Skill3ID'] = $skill2;
								if ($s3used == "true") { $ignorepet = "true"; }
								else { $s3used = "true"; }
								break;
						}
						switch ($skill3) {   // set skill for slot 3
							case $value['Skill1']:
								$petarray[$countsubstitutes]['Skill1NB'] = "1";
								$petarray[$countsubstitutes]['Skill1ID'] = $skill3;
								if ($s1used == "true") { $ignorepet = "true"; }
								break;
							case $value['Skill2']:
								$petarray[$countsubstitutes]['Skill2NB'] = "1";
								$petarray[$countsubstitutes]['Skill2ID'] = $skill3;
								if ($s2used == "true") { $ignorepet = "true"; }
								break;
							case $value['Skill3']:
								$petarray[$countsubstitutes]['Skill3NB'] = "1";
								$petarray[$countsubstitutes]['Skill3ID'] = $skill3;
								if ($s3used == "true") { $ignorepet = "true"; }
								break;
							case $value['Skill4']:
								$petarray[$countsubstitutes]['Skill1NB'] = "2";
								$petarray[$countsubstitutes]['Skill1ID'] = $skill3;
								if ($s1used == "true") { $ignorepet = "true"; }
								break;
							case $value['Skill5']:
								$petarray[$countsubstitutes]['Skill2NB'] = "2";
								$petarray[$countsubstitutes]['Skill2ID'] = $skill3;
								if ($s2used == "true") { $ignorepet = "true"; }
								break;
							case $value['Skill6']:
								$petarray[$countsubstitutes]['Skill3NB'] = "2";
								$petarray[$countsubstitutes]['Skill3ID'] = $skill3;
								if ($s3used == "true") { $ignorepet = "true"; }
								break;
						}
						
						$breedsgathered = "";
						$breedarray = array();
						$cutbreeds = explode(",", $reqbreeds);

						// Breed info - Case 0 - no requirements
						$petarray[$countsubstitutes]['ReqBreed'] = "Any";

						// Specific requirements Case 2 stats are limited
						if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {

							$numbreeds = 0;
							$posbreeds = "0";
							$listbreeds = "";

							foreach ($allbreeds as $breed => $breedstats) {
								if ($all_pets[$value['Species']][$breed] == "1") {      // The current breed is possible for this pet, now check if stats are OK
									$thishp = round(100+(($all_pets[$value['Species']]['Health']+$breedstats['Health'])*162.5), 0, PHP_ROUND_HALF_DOWN);
									$thissp = round(($all_pets[$value['Species']]['Speed']+$breedstats['Speed'])*32.5, 0, PHP_ROUND_HALF_DOWN);
									$thispw = round(($all_pets[$value['Species']]['Power']+$breedstats['Power'])*32.5, 0, PHP_ROUND_HALF_DOWN);
									$reqhpnumber = substr($reqhp, 1);
									$reqspnumber = substr($reqsp, 1);
									$reqpwnumber = substr($reqpw, 1);
									$breedok = "true";

									if ($reqhp != "") {
										switch ($reqhp[0]) {
											case "<":
												if ($thishp >= $reqhpnumber) {
													$breedok = "false";
												}
												break;
											case ">":
												if ($thishp <= $reqhpnumber) {
													$breedok = "false";
												}
												break;
											case "=":
												if ($thishp != $reqhpnumber) {
													$breedok = "false";
												}
												break;
										}
									}

									if ($reqsp != "") {
										switch ($reqsp[0]) {
											case "<":
												if ($thissp >= $reqspnumber) {
													$breedok = "false";
												}
												break;
											case ">":
												if ($thissp <= $reqspnumber) {
													$breedok = "false";
												}
												break;
											case "=":
												if ($thissp != $reqspnumber) {
													$breedok = "false";
												}
												break;
										}
									}

									if ($reqpw != "") {
										switch ($reqpw[0]) {
											case "<":
												if ($thispw >= $reqpwnumber) {
													$breedok = "false";
												}
												break;
											case ">":
												if ($thispw <= $reqpwnumber) {
													$breedok = "false";
												}
												break;
											case "=":
												if ($thispw != $reqpwnumber) {
													$breedok = "false";
												}
												break;
										}
									}


									// List all breeds together that passed all tests
									if ($breedok == "true") {
										$listbreeds = $listbreeds.$breed.", ";
										$posbreeds++;
									}

									// Create Breed Array for all breeds of this pet:
									 $breedarray[$numbreeds]['Breed'] = $breed;
									 $breedarray[$numbreeds]['BreedOK'] = $breedok;
									 $breedarray[$numbreeds]['PW'] = $thispw;
									 $breedarray[$numbreeds]['SP'] = $thissp;
									 $breedarray[$numbreeds]['HP'] = $thishp;
									 $breedok = "";
									$numbreeds++;
								}
								$breedsgathered = "true";
							}

							// All breeds are fine:
							if ($posbreeds > "0" && $numbreeds == $posbreeds) {
								$petarray[$countsubstitutes]['ReqBreed'] = "Any";
							}
							// Only some breeds are fine:
							if ($posbreeds > "0" && $numbreeds != $posbreeds) {
								$petarray[$countsubstitutes]['ReqBreed'] = substr($listbreeds, 0, -2);
							}
							// no breed is fine:
							if ($posbreeds == "0") {
								$ignorepet = "true";
								$petarray[$countsubstitutes]['BreedError'] = "statsexcluded";
							}

							// MISSING TODO - Add breeds from crowd intelligence (user reviews), in brackets

						}

						// Specific requirements Case 2 - breed is limited. Which cannot be used for substitutes, so in this case show warning
						if ($reqbreeds != "") {
							$petarray[$countsubstitutes]['BreedError'] = "subbreedexcluded";
							$petarray[$countsubstitutes]['ReqBreed'] = "Uncertain";
						}


						// If 2 required spells are found in the same slot, or the required stats are not met, exclude pet from substitutes:
						if ($ignorepet == "true") {
							unset($petarray[$countsubstitutes]);
						}
						else { // Only add more info to array and check against collection if the pet is supposed to be added as substitute

						   // Create breed array for this pet
							$numbreeds = 0;
							if ($breedsgathered != "true") {
							   foreach ($allbreeds as $breed => $breedstats) {
								  if ($all_pets[$value['Species']][$breed] == "1") {
									 $breedarray[$numbreeds]['Breed'] = $breed;
									 $breedarray[$numbreeds]['BreedOK'] = "true";
									 $breedarray[$numbreeds]['PW'] = round(($all_pets[$value['Species']]['Power']+$breedstats['Power'])*32.5, 0, PHP_ROUND_HALF_DOWN);
									 $breedarray[$numbreeds]['SP'] = round(($all_pets[$value['Species']]['Speed']+$breedstats['Speed'])*32.5, 0, PHP_ROUND_HALF_DOWN);
									 $breedarray[$numbreeds]['HP'] = round(100+(($all_pets[$value['Species']]['Health']+$breedstats['Health'])*162.5), 0, PHP_ROUND_HALF_DOWN);
									 $numbreeds++;
								   }
							   }
							}
							$petarray[$countsubstitutes]['BreedInfo'] = $breedarray;
							$fbpet[$i][$countsubstitutes]['id'] = $value['PetID'];
							$fbpet[$i][$countsubstitutes]['BreedInfo'] = $breedarray;

							// Check against user collection
							if ($temp_collection) {
							   $searchFor = $petarray[$countsubstitutes]['PetID'];
							   $petarray[$countsubstitutes]['Owned'] = "0";
							   $petarray[$countsubstitutes]['OwnedValue'] = "0";
							   $petarray[$countsubstitutes]['AllOwned'] = array_filter($temp_collection, function($element) use($searchFor){ return isset($element['Species']) && $element['Species'] == $searchFor;});
							   foreach ($petarray[$countsubstitutes]['AllOwned'] as $mypetkey => $mypetvalue) {
								  $petarray[$countsubstitutes]['SelectedBreed'] = $mypetvalue['Breed'];
								  if (($mypetvalue['Level'] != "25" OR $mypetvalue['Quality'] != "3") && $petarray[$countsubstitutes]['Owned'] != "2") {
									 $petarray[$countsubstitutes]['Owned'] = "1";
									 $petarray[$countsubstitutes]['OwnedValue'] = "1";
								  }
								  else {
									if ($reqhp != "" OR $reqsp != "" OR $reqpw != "" && $petarray[$countsubstitutes]['Owned'] != "2") {
										$breedokay = "false";
										$petarray[$countsubstitutes]['Owned'] = "1";
										foreach ($breedarray as $breedkey => $breedvalues) {
										  if ($breedvalues['BreedOK'] == "true" && $breedvalues['Breed'] == $mypetvalue['Breed']) {
											 $petarray[$countsubstitutes]['Owned'] = "2";
											 $petarray[$countsubstitutes]['OwnedValue'] = "2";
											 $petarray[$countsubstitutes]['RematchBreed'] = $mypetvalue['Breed'];
										  }
										}
									}
									else {
										$petarray[$countsubstitutes]['Owned'] = "2";
									}
								  }
							   }
							   $searchFor = "";
							   $petarray[$countsubstitutes]['OwnedCount'] = count($petarray[$countsubstitutes]['AllOwned']);
							}
						$countsubstitutes++;
						}
					}
				}
			}
		}
		
		// Sort by importance
		sortBy('OwnedValue', $petarray, 'desc');

		foreach ($petarray as $key => $value) {  // Check if this substitute was directly selected through URL
			if ($customsubs[$i] == $value['PetID']) {
				$petarray[0]['Primary'] = "";
				$petarray[$key]['Primary'] = "true";
				$petslotinfo[$i]['DirectSub'] = $key+1;
				$petslotinfo[$i]['PetID'] = $value['PetID'];
			}
		}
	
		// Add additional info to array controlling the substitutes
		$petslotinfo[$i]['Subscount'] = $countsubstitutes;
		$petslotinfo[$i]['Family'] = $all_pets[$fetchpet]['Family'];
		$petslotinfo[$i]['ShowSubWarning'] = "block";
		if ($petslotinfo[$i]['DirectSub'] == null) {
			$petslotinfo[$i]['ShowSubWarning'] = "none";
			$petslotinfo[$i]['DirectSub'] = "1";
		}
		
		// Add additional info to array controlling user attempt field
		$petlist_feedback[$i]['Substitutes'] = count($petarray);
		$petlist_feedback[$i]['Sub_Details'] = $petarray;
	  
	  ?>

		<div style="float: left; width: 237px; height: 170px; overflow: hidden" class="petcard_container">
			<?			
			foreach ($petarray as $key => $value) {
			if ($value['Primary'] == "true") {
				$displaycard = "block";
			}
			else {
				$displaycard = "none";
			}
			if (file_exists('images/pets/resize50/'.$all_pets[$value['PetID']]['PetID'].'.png')) {
				$petimage = 'images/pets/resize50/'.$all_pets[$value['PetID']]['PetID'].'.png';
			}
			else {
				$petimage = 'images/pets/resize50/unknown.png';
			}
         if ($value['PetID'] <= "20" && $value['PetID'] >= "11") {
            $petimage = 'images/pets/resize50/unknown.png';
         }

			if ($key == 0) { ?>
				<div class="bt_2_<?php echo $all_pets[$value['PetID']]['Family'] ?>_d" style="width: 24px; height: 170px; float: left">
					<div style="float: left"><img src="https://www.wow-petguide.com/<?php echo $petcountimg ?>"></div>
					<div style="float: left; padding-top: 33px"><img src="https://www.wow-petguide.com/images/bt_2_ic_<?php echo $all_pets[$value['PetID']]['Family'] ?>.png"></div>
				</div>
			<?
			} ?>

			<div id="petcard_<?php echo $i.'_'.$key; ?>" style="display: <?php echo $displaycard ?>">

            <?php // Details for Rematch
            switch ($value['RematchBreed']) {
               case "PP":
               $outputbreed = "4";
               break;
               case "SS":
               $outputbreed = "5";
               break;
               case "HH":
               $outputbreed = "6";
               break;
               case "HP":
               $outputbreed = "7";
               break;
               case "PS":
               $outputbreed = "8";
               break;
               case "HS":
               $outputbreed = "9";
               break;
               case "PB":
               $outputbreed = "A";
               break;
               case "SB":
               $outputbreed = "B";
               break;
               case "HB":
               $outputbreed = "C";
               break;
               case "BB":
               $outputbreed = "3";
               break;
            } ?>
            <div style="display: none">
               <?
                if ($value['Skill1NB'] == "*"){ $rm_sk1 = "0"; }
                else { $rm_sk1 = $value['Skill1NB']; }
                if ($value['Skill2NB'] == "*"){ $rm_sk2 = "0"; }
                else { $rm_sk2 = $value['Skill2NB']; }
                if ($value['Skill3NB'] == "*"){ $rm_sk3 = "0"; }
                else { $rm_sk3 = $value['Skill3NB']; }

                if ($key == "0") {
                    echo 'Special or not:<span id ="rm_p'.$i.'_s'.$strat->id.'_special">0</span>';
                    echo 'Special or not:<span id ="rm_p'.$i.'_s'.$strat->id.'_subscount">'.$petslotinfo[$i]['Subscount'].'</span>';
                }
                echo '<span id ="rm_p'.$i.'_s'.$strat->id.'_sub'.$key.'_skills">'.$rm_sk1.$rm_sk2.$rm_sk3.'</span>';
                echo '<span id ="rm_p'.$i.'_s'.$strat->id.'_sub'.$key.'_breed">'.$outputbreed.'</span>';
                echo '<span id ="rm_p'.$i.'_s'.$strat->id.'_sub'.$key.'_species">'.$value['PetID'].'</span>';
                echo '<span id ="rm_p'.$i.'_s'.$strat->id.'_sub'.$key.'_petid">'.$all_pets[$value['PetID']]['PetID'].'</span>';
				echo '<span id ="rm_p'.$i.'_s'.$strat->id.'_petid">'.$value['PetID'].'</span>';
				echo '<span id ="rm_p'.$i.'_s'.$strat->id.'_petreqhp">0</span>';
                 ?>
            </div>


					<div class="bt_2_<?php echo $all_pets[$value['PetID']]['Family'] ?>" style="width: 213px; height: 170px; float: left">

						<div class="bt_petimage">
							<a href="http://<?php echo $wowhdomain ?>.wowhead.com/npc=<?php echo $all_pets[$value['PetID']]['PetID'] ?>" target="_blank">
								<img src="<?php echo $petimage ?>" class="bt_petimage">
							</a>
						</div>

						<div class="bt_petname">
							<a href="http://<?php echo $wowhdomain ?>.wowhead.com/npc=<?php echo $all_pets[$value['PetID']]['PetID'] ?>" target="_blank" class="bt_petdetails">
								<?php echo $all_pets[$value['PetID']]['Name'] ?>
							</a>
						</div>

						<div class="bt_petdetails">
							<p class="bt_petdetails">Skills:
							<?
							if ($value['Skill1NB'] == "*") { echo '* '; }
							else {
								echo '<a href="http://'.$wowhdomain.'.wowhead.com/petability='.$value['Skill1ID'].'" target="_blank" class="bt_petdetails">'.$value['Skill1NB'].' </a>';
							}
							if ($value['Skill2NB'] == "*") { echo '* '; }
							else {
								echo '<a href="http://'.$wowhdomain.'.wowhead.com/petability='.$value['Skill2ID'].'" target="_blank" class="bt_petdetails">'.$value['Skill2NB'].' </a>';
							}
							if ($value['Skill3NB'] == "*") { echo '* '; }
							else {
								echo '<a href="http://'.$wowhdomain.'.wowhead.com/petability='.$value['Skill3ID'].'" target="_blank" class="bt_petdetails">'.$value['Skill3NB'].' </a>';
							}
							?>
							<br>
					<?
					$breedlinebreak = "";
					$reqbrsplits = explode(",", $value['ReqBreed']);
					if (count($reqbrsplits) <= "4") {
						$reqbroutput = $value['ReqBreed'];
					}
					else {
						foreach($reqbrsplits as $reqbrkey => $onereqbr) {
							if ($reqbrkey == "0") {
								$reqbroutput = $onereqbr;
							}
							if ($reqbrkey < "4" && $reqbrkey != "0") {
								$reqbroutput = $reqbroutput.", ".$onereqbr;
							}
							if ($reqbrkey == "4") {
								$breedlinebreak = "1";
								$reqbroutput = $reqbroutput."<br>".$onereqbr;
							}
							if ($reqbrkey > "4") {
								$reqbroutput = $reqbroutput.", ".$onereqbr;
							}
						}
					}
					?>
					Breed: <span class="breed_tooltip_<?php echo $i.$key ?>" data-tooltip-content="#breed_tooltip_div_<?php echo $i.$key ?>" style="cursor: pointer;"><?php echo $reqbroutput; ?></span>

					<div style="display: none">
						<span id="breed_tooltip_div_<?php echo $i.$key ?>">
						   <b><?php echo $all_pets[$value['PetID']]['Name'] ?></b><br>
						   This pet can have the following breeds:<br><br>

						   <table class="tooltip"><tr>
							  <th class="tooltip">Breed</th>
							  <th class="tooltip">Health</th>
							  <th class="tooltip">Power</th>
							  <th class="tooltip">Speed</th>
						   </tr>
						   <?
						   $brwarning = "";
						   $posbreeds = $value['BreedInfo'];
						   sortBy('BreedOK', $posbreeds, 'desc');
						   foreach ($posbreeds as $brnumber => $brvalue) {
							  if ($brvalue['BreedOK'] == "false") {
								 $makered = "xpwaste";
								 $brwarning = "true";
							  }
							  else {
								 $makered = "";
							  }
							  ?>
							 <tr class="tooltip">
								<tr class="tooltip xp_row">
								<td class="tooltip <?php echo $makered ?>" style="text-align: center"><?php echo $brvalue['Breed'] ?></td>
								<td class="tooltip <?php echo $makered ?>" style="text-align: center"><?php echo $brvalue['HP'] ?></td>
								<td class="tooltip <?php echo $makered ?>" style="text-align: center"><?php echo $brvalue['PW'] ?></td>
								<td class="tooltip <?php echo $makered ?>" style="text-align: center"><?php echo $brvalue['SP'] ?></td>
								</tr>
						   <?php } ?>


						   </table>
						   <br>
						   <?
						   if ($brwarning == "true") { echo "Breeds marked in red are not suited for this strategy.<br>"; }
						   if ($value['BreedError'] == "statsexcluded") { echo "<b>Warning</b> - the creator of this strategy set limitations to the stats that exclude all possible breeds. Use at your own risk."; }
						   if ($value['BreedError'] == "subbreedexcluded") { echo "<b>Warning</b> - the creator of this strategy set limitations which breeds work for the original pet choice. This cannot be applied in the same way to the selected substitute. Use this pet at your own risk."; }


						   ?>

						</span>
					</div>

					<script>
						$(document).ready(function() {
							$('.breed_tooltip_<?php echo $i.$key ?>').tooltipster({
								maxWidth: '400',
								minWidth: '200',
								theme: 'tooltipster-smallnote'
							});
						});
					</script>
				  <br>


				  <?php // Print stat requirements if present

				  if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {
					   echo  '<table style="width: 100%; margin-top: 4px" cellpadding="0" cellspacing="0"><tr>';
					   if ($reqhp != "") {
						   echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_health.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqhp.'</td>';
					   }
					   if ($reqsp != "") {
						   echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_speed.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqsp.'</td>';
					   }
					   if ($reqpw != "") {
						   echo '<td><img src="https://www.wow-petguide.com/images/bt_icon_power.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqpw.'</td>';
					   }
					   echo "</tr></table>";
						if ($breedlinebreak == "1" && ($reqhp != "" OR $reqsp != "" OR $reqpw != "")) {
							$coltopmargin = "0";
						}
						else {
							$coltopmargin = "8";
						}
				   }
				   else {
						if ($breedlinebreak == "1") {
							$coltopmargin = "13";
						}
						else {
							$coltopmargin = "30";
						}
				   }



				  // Print own pet collection if there is a collection
				  if ($collection) {
					 if ($value['Owned'] == "0") {
						$coltabbg = 'colpetdisplay_red';
						$outputowned = "Missing";
					 }
					 if ($value['Owned'] == "1") {
						$coltabbg = 'colpetdisplay_orange';
					 }
					 if ($value['Owned'] == "2") {
						$coltabbg = 'colpetdisplay_green';
					 }

					 if ($value['OwnedCount'] == "1" && $value['Owned'] == "2") {
						foreach ($value['AllOwned'] as $ownedpkey => $ownedpetv) {
						   $outputownedbreed = $ownedpetv['Breed'];
						}
						$outputowned = '<font color="#1f96ff">25 '.$outputownedbreed.'</font>';
					 }
					 if ($value['OwnedCount'] == "1" && $value['Owned'] == "1") {
						foreach ($value['AllOwned'] as $ownedpkey => $ownedpetv) {
						   $outputownedlevel = $ownedpetv['Level'];
						   $outputownedquali = $ownedpetv['Quality'];
                           $outputownedbreed = $ownedpetv['Breed'];
						}
						if ($outputownedquali == "3") { $outputownedlevelquali = "#177cd6"; }
						if ($outputownedquali == "2") { $outputownedlevelquali = "#44dd34"; }
						if ($outputownedquali == "1") { $outputownedlevelquali = "#ffffff"; }
						if ($outputownedquali == "0") { $outputownedlevelquali = "#d2d2d2"; }
						$outputowned = '<font style="text-shadow: 0" color="'.$outputownedlevelquali.'">'.$outputownedlevel.' '.$outputownedbreed.'</font>';
					 }
					 if ($value['OwnedCount'] > "1") {
						$outputowned = $value['OwnedCount']."*";
					 }

					 ?>
					 <div class="colpetdisplay <?php echo $coltabbg ?>" style="margin-top: <?php echo $coltopmargin ?>px;">
						<p class="bt_petdetails">Your pet: <span class="col_tooltip_<?php echo $i.$key ?>" <?php if ($value['OwnedCount'] > "1" OR $value['Owned'] == "1") { ?>data-tooltip-content="#col_tooltip_div_<?php echo $i.$key ?>" style="cursor: pointer"<?php } ?> ><?php echo $outputowned; ?></span>
					 </div>

					<?php if ($value['OwnedCount'] > "1") { // Tooltip in case the user has duplicates of this pet?>
						<div style="display: none">
							<span id="col_tooltip_div_<?php echo $i.$key ?>">
							   In your collection:<br><br>

							   <table class="tooltip"><tr>
								 <th class="tooltip"></th>
								  <th class="tooltip">Quality</th>
								  <th class="tooltip">Level</th>
								  <th class="tooltip">Breed</th>
							   </tr>
							   <?
							   $ownedpets = $value['AllOwned'];
							   sortBy('level', $ownedpets, 'desc');
							   foreach ($ownedpets as $brnumber => $brvalue) {

								 if ($brvalue['Quality'] == "3") { $outputownedquali = "#1f96ff"; $outputownedqualin = "Rare"; }
								 if ($brvalue['Quality'] == "2") { $outputownedquali = "#44dd34"; $outputownedqualin = "Uncommon"; }
								 if ($brvalue['Quality'] == "1") { $outputownedquali = "#ffffff"; $outputownedqualin = "Common"; }
								 if ($brvalue['Quality'] == "0") { $outputownedquali = "#d2d2d2"; $outputownedqualin = "Poor"; }
								  ?>
								 <tr class="tooltip">
									<tr class="tooltip xp_row">
									   <td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $all_pets[$value['PetID']]['Name'] ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets" style="color: <?php echo $outputownedquali ?>"><?php echo $outputownedqualin ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Level'] ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Breed'] ?></td>
									</tr>
							   <?php } ?>
							   </table>
							   <br>

							  <?php if ($value['Owned'] == "1") {
								 echo "This slot requires a level 25, rare ".$all_pets[$value['PetID']]['Name'];
							   } ?>
							</span>
						</div>

						<script>
							$(document).ready(function() {
								$('.col_tooltip_<?php echo $i.$key ?>').tooltipster({
									maxWidth: '400',
									minWidth: '200',
									theme: 'tooltipster-smallnote'
								});
							});
						</script>
					 <?php } ?>

					<?php if ($value['Owned'] == "1" && $value['OwnedCount'] < "2") { // Tooltip in case stats or breed are not met ?>
						<div style="display: none">
							<span id="col_tooltip_div_<?php echo $i.$key ?>">
							   Your pet does not meet all requirements.<br>
							   If not specified otherwise, it needs to be level 25 and rare.<br>
							   In some cases a specific breed or stats are required.
							   <br><br>The pet card shows all requirements.<br>
							</span>
						</div>

						<script>
							$(document).ready(function() {
								$('.col_tooltip_<?php echo $i.$key ?>').tooltipster({
									maxWidth: '400',
									minWidth: '200',
									side: 'bottom',
									theme: 'tooltipster-smallnote'
								});
							});
						</script>
					 <?php } ?>
				  <?php } ?>

						</div>
					</div>
				</div>
			<?php } ?>
		</div>

	<?
   }

   // Create pet card for a pet of any particular family
   if ($fetchpet > "10" && $fetchpet <= "20") {

   switch ($fetchpet) {
      case "11":
         $famname = __("Humanoid");
         $famsuffix = __("Humanoid");
         $famid = "0";
         $rmfamid = "1";
         $petslotinfo[$i]['Family'] = "Humanoid";
      break;
      case "12":
         $famname = __("Magic");
         $famsuffix = __("Magic");
         $famid = "5";
         $rmfamid = "6";
         $petslotinfo[$i]['Family'] = "Magic";
      break;
      case "13":
         $famname = __("Elemental");
         $famsuffix = __("Elemental");
         $famid = "6";
         $rmfamid = "7";
         $petslotinfo[$i]['Family'] = "Elemental";
      break;
      case "14":
         $famname = __("Undead");
         $famsuffix = __("Undead");
         $famid = "3";
         $rmfamid = "4";
         $petslotinfo[$i]['Family'] = "Undead";
      break;
      case "15":
         $famname = __("Mechanical");
         $famsuffix = __("Mech");
         $famid = "9";
         $rmfamid = "A";
         $petslotinfo[$i]['Family'] = "Mechanical";
      break;
      case "16":
         $famname = __("Flying");
         $famsuffix = __("Flyer");
         $famid = "2";
         $rmfamid = "3";
         $petslotinfo[$i]['Family'] = "Flying";
      break;
      case "17":
         $famname = __("Critter");
         $famsuffix = __("Critter");
         $famid = "4";
         $rmfamid = "5";
         $petslotinfo[$i]['Family'] = "Critter";
      break;
      case "18":
         $famname = __("Aquatic");
         $famsuffix = __("Aquatic");
         $famid = "8";
         $rmfamid = "9";
         $petslotinfo[$i]['Family'] = "Aquatic";
      break;
      case "19":
         $famname = __("Beast");
         $famsuffix = __("Beast");
         $famid = "7";
         $rmfamid = "8";
         $petslotinfo[$i]['Family'] = "Beast";
      break;
      case "20":
         $famname = __("Dragonkin");
         $famsuffix = __("Dragon");
         $famid = "1";
         $rmfamid = "2";
         $petslotinfo[$i]['Family'] = "Dragonkin";
      break;
   }

	$reqlevelpieces = explode("+", $reqlevel);
	$displayreqlvl = $reqlevelpieces[0]."+";
	if ($reqlevelpieces[0] == "") {
		$petlist_feedback[$i]['reqlevel'] = "1";
	}
	else {
		$petlist_feedback[$i]['reqlevel'] = $reqlevelpieces[0];
	}

	if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
      $petcardtitle = __("Any")." ".$famsuffix;
   }
   else {
      if ($_SESSION["lang"] =="es_ES"){
         $petcardtitle = __("Any")." ".$famsuffix." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
      }
      else if ($_SESSION["lang"] =="fr_FR"){
         $petcardtitle = __("Any Pet")." de type ".$famsuffix." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
      }
      else {
         $petcardtitle = __("Any Level")." ".$displayreqlvl." ".$famsuffix;
      }
   }
   $qfpettitle[$i] = $petcardtitle;
   ?>

		<div style="float: left; width: 237px; height: 170px; overflow: hidden" class="petcard_container">

            <?php // Details for Rematch ?>
            <div style="display: none">
            <span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_special">1</span>
            <span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_qc">ZR<?php echo $rmfamid ?></span>
				<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_petid"><?php echo $fetchpet ?></span>
            </div>

			<?
			if (file_exists('images/pets/resize50/'.$fetchpet.'.png')) {
				$petimage = 'images/pets/resize50/'.$fetchpet.'.png';
			}
			else {
				$petimage = 'images/pets/resize50/unknown.png';
			} ?>

				<div class="bt_2_<?php echo $all_pets[$fetchpet]['Family'] ?>_d" style="width: 24px; height: 170px; float: left">
					<div style="float: left"><img src="https://www.wow-petguide.com/<?php echo $petcountimg ?>"></div>
					<div style="float: left; padding-top: 33px"><img src="https://www.wow-petguide.com/images/bt_2_ic_<?php echo $all_pets[$fetchpet]['Family'] ?>.png"></div>
				</div>

				<div id="petcard_<?php echo $i ?>">

					<div class="bt_2_<?php echo $all_pets[$fetchpet]['Family'] ?>" style="width: 213px; height: 170px; float: left">

						<div class="bt_petimage">
							<img style="margin-bottom: 3px" src="<?php echo $petimage ?>" class="bt_petimage">
						</div>

						<div class="bt_petname">
							<p class="bt_petdetails">
								<?php echo $petcardtitle; ?>
							</p>
						</div>

						<div class="bt_petdetails">
							<p class="bt_petdetails">
                     Skills: Any<br>
                     Breed: Any<br>

				  <?php // Print stat requirements if present

				  if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {
					   echo  '<table style="width: 100%; margin-top: 4px" cellpadding="0" cellspacing="0"><tr>';
					   if ($reqhp != "") {
						   echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_health.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqhp.'</td>';
					   }
					   if ($reqsp != "") {
						   echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_speed.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqsp.'</td>';
					   }
					   if ($reqpw != "") {
						   echo '<td><img src="https://www.wow-petguide.com/images/bt_icon_power.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqpw.'</td>';
					   }
					   echo "</tr></table>";
					   $coltopmargin = "8";
				   }
				   else {
						$coltopmargin = "30";
				   }

				  // Print own pet collection if there is a collection
				  if ($temp_collection) {

                  if ($reqlevel == "") {
                     $reqlevel = "1";
                  }
                  
					$searchFor = $famid;
					$temp_petarray = array_filter($temp_collection, function($element) use($searchFor){ return isset($element['Family']) && $element['Family'] == $searchFor;});
					$petarray = array();
					$petunqualarray = array();
					$countfampets = "0";
					$countunqualpets = "0";
 					$reqhpnumber = substr($reqhp, 1);
					$reqspnumber = substr($reqsp, 1);
					$reqpwnumber = substr($reqpw, 1);

					foreach ($temp_petarray as $mypetkey => $mypetvalue) {

                     switch ($mypetvalue['Quality']) {
                        case "0":
                           $qualcalc = "1";
                        break;
                        case "1":
                           $qualcalc = "1.1";
                        break;
                        case "2":
                           $qualcalc = "1.2";
                        break;
                        case "3":
                           $qualcalc = "1.3";
                        break;
                     }

							$thishp = round(100+(($all_pets[$mypetvalue['Species']]['Health']+$allbreeds[$mypetvalue['Breed']]['Health'])*$mypetvalue['Level']*$qualcalc*5), 0, PHP_ROUND_HALF_DOWN);
							$thissp = round(($all_pets[$mypetvalue['Species']]['Speed']+$allbreeds[$mypetvalue['Breed']]['Speed'])*$mypetvalue['Level']*$qualcalc, 0, PHP_ROUND_HALF_DOWN);
							$thispw = round(($all_pets[$mypetvalue['Species']]['Power']+$allbreeds[$mypetvalue['Breed']]['Power'])*$mypetvalue['Level']*$qualcalc, 0, PHP_ROUND_HALF_DOWN);

							if ($mypetvalue['Level'] >= $reqlevel) {
                        $breedok = "";
                        if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {
									if ($reqhp != "") {
										switch ($reqhp[0]) {
											case "<":
												if ($thishp >= $reqhpnumber) {
													$breedok = "false";
												}
												break;
											case ">":
												if ($thishp <= $reqhpnumber) {
													$breedok = "false";
												}
												break;
											case "=":
												if ($thishp != $reqhpnumber) {
													$breedok = "false";
												}
												break;
										}
									}

									if ($reqsp != "") {
										switch ($reqsp[0]) {
											case "<":
												if ($thissp >= $reqspnumber) {
													$breedok = "false";
												}
												break;
											case ">":
												if ($thissp <= $reqspnumber) {
													$breedok = "false";
												}
												break;
											case "=":
												if ($thissp != $reqspnumber) {
													$breedok = "false";
												}
												break;
										}
									}

									if ($reqpw != "") {
										switch ($reqpw[0]) {
											case "<":
												if ($thispw >= $reqpwnumber) {
													$breedok = "false";
												}
												break;
											case ">":
												if ($thispw <= $reqpwnumber) {
													$breedok = "false";
												}
												break;
											case "=":
												if ($thispw != $reqpwnumber) {
													$breedok = "false";
												}
												break;
										}
									}
                        }
                        if ($breedok == "false") {
                           $petunqualarray[$countunqualpets] = $mypetvalue;
                           $petunqualarray[$countunqualpets]['Health'] = $thishp;
                           $petunqualarray[$countunqualpets]['Power'] = $thispw;
                           $petunqualarray[$countunqualpets]['Speed'] = $thissp;
                           $countunqualpets++;
                        }
                        else {
                           $petarray[$countfampets] = $mypetvalue;
                           $petarray[$countfampets]['Health'] = $thishp;
                           $petarray[$countfampets]['Power'] = $thispw;
                           $petarray[$countfampets]['Speed'] = $thissp;
                           $countfampets++;
                        }
							}
                     else {
                        $petunqualarray[$countunqualpets] = $mypetvalue;
                        $petunqualarray[$countunqualpets]['Health'] = $thishp;
                        $petunqualarray[$countunqualpets]['Power'] = $thispw;
                        $petunqualarray[$countunqualpets]['Speed'] = $thissp;
                        $countunqualpets++;
                     }
						}
						if ($petarray) {
							$allqualfam = count($petarray);
						}
						else {
							$allqualfam = "0";
						}

                     $allfam = count($temp_petarray);

					 if ($allfam == "0") {
                        $showtooltip = "0";
						$coltabbg = 'colpetdisplay_red';
						$outputowned = "none";
					 }
					 if ($allfam > "0" && $allqualfam == "0") {
                        $showtooltip = "1";
						$coltabbg = 'colpetdisplay_orange';
                        $outputowned = $allfam." unqualified*";
					 }
					 if ($allfam > "0" && $allqualfam > "0") {
                        $showtooltip = "1";
						$coltabbg = 'colpetdisplay_green';
                        $outputowned = $allqualfam."*";
					 }

					 ?>
					 <div class="colpetdisplay <?php echo $coltabbg ?>" style="margin-top: <?php echo $coltopmargin ?>">
						<p class="bt_petdetails">You have <span class="col_tooltip_<?php echo $i.$key ?>" <?php if ($showtooltip == "1") { ?>data-tooltip-content="#col_tooltip_div_<?php echo $i.$key ?>" style="cursor: pointer"<?php } ?> ><?php echo $outputowned; ?></span>
					 </div>

					<?php if ($showtooltip == "1") { ?>
						<div style="display: none">
							<span id="col_tooltip_div_<?php echo $i.$key ?>">
                        <?php if ($coltabbg == "colpetdisplay_green") {
                           echo "This is a list of all pets you own that qualify for this strategy slot:<br><br>";
                           $outputarray = $petarray;
                        }
                        else {
                           echo "You have no pets that meet all requirements for this slot. <br>Below list are all your pets of this family:<br><br>";
                           $outputarray = $petunqualarray;
                        } ?>

                       <table width="100%" id="t<?php echo $i ?>" style="border-collapse: collapse;" class="tooltip table-autosort table-autofilter table-autopage:18 table-page-number:t<?php echo $i ?>page table-page-count:t<?php echo $i ?>pages table-rowcount:t<?php echo $i ?>allcount">

                           <thead>
                        <tr>
								  <th class="tooltip table-sortable:alphabetic" style="cursor: pointer">Name</th>
								  <th class="tooltip table-sortable:alphabetic" style="cursor: pointer">Quality</th>
								  <th class="tooltip table-sortable:numeric" style="cursor: pointer">Level</th>
								  <th class="tooltip table-sortable:alphabetic" style="cursor: pointer">Breed</th>
								  <th class="tooltip table-sortable:numeric" style="cursor: pointer">Health</th>
								  <th class="tooltip table-sortable:numeric" style="cursor: pointer">Power</th>
								  <th class="tooltip table-sortable:numeric" style="cursor: pointer">Speed</th>
							   </tr>
                           </thead>
								<?
								foreach ($outputarray as $brnumber => $brvalue) {
									if ($brvalue['Quality'] == "3") { $outputownedquali = "#1f96ff"; $outputownedqualin = "Rare"; }
									if ($brvalue['Quality'] == "2") { $outputownedquali = "#44dd34"; $outputownedqualin = "Uncommon"; }
									if ($brvalue['Quality'] == "1") { $outputownedquali = "#ffffff"; $outputownedqualin = "Common"; }
									if ($brvalue['Quality'] == "0") { $outputownedquali = "#d2d2d2"; $outputownedqualin = "Poor"; }
								?>

								<tr class="tooltip xp_row">
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $all_pets[$brvalue['Species']]['Name'] ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets" style="color: <?php echo $outputownedquali ?>"><?php echo $outputownedqualin ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Level'] ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Breed'] ?></td>
 									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Health'] ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Power'] ?></td>
									<td class="tooltip" style="text-align: center"><p class="tooltippets"><?php echo $brvalue['Speed'] ?></td>
								</tr>
							   <?php } ?>

                        <tfoot>
                            <tr class="tooltip xp_row">
                                <td colspan="2" align="right" class="table-page:previous" style="cursor:pointer;"><a class="tooltippets" style="text-decoration: none;">&lt; &lt; </a></td>
                                <td colspan="2" align="center"><div style="white-space:nowrap"><p class="tooltippets"><span id="t<?php echo $i ?>page"></span> / <span id="t<?php echo $i ?>pages"></span></div></td>
                                <td colspan="3" align="left" class="table-page:next" style="cursor:pointer;"><a class="tooltippets" style="text-decoration: none;"> &gt; &gt;</td>
                            </tr>
                        </tfoot>

							   </table>
							   <br>
							</span>
						</div>

						<script>
							$(document).ready(function() {
								$('.col_tooltip_<?php echo $i.$key ?>').tooltipster({
									maxWidth: '650',
									minWidth: '500',
                                    interactive: true,
									theme: 'tooltipster-smallnote'
								});
							});
						</script>
					 <?php } ?>

				  <?php } ?>

						</div>
					</div>
				</div>
		</div>


   <?php }


 // Create pet card for a level pet
   if ($fetchpet == 0) { 
      if ($reqlevel == ""){
         $reqlevel = "1+";
      }
	  // Save lowest level pet info for usage in tags:

	  if (intval($reqlevel) < $lowestlevel OR !$lowestlevel) {
		$lowestlevel = intval($reqlevel);
	  }
		$reqlevelpieces = explode("+", $reqlevel);
		$displayreqlvl = $reqlevelpieces[0]."+";
		$petlist_feedback[$i]['reqlevel'] = $reqlevelpieces[0];

		if ($_SESSION["lang"] =="fr_FR"){
         $petcardtitle = __("Any Pet")." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
      }
      else {
         $petcardtitle = __("Any Level")." ".$displayreqlvl." ".__("Pet");
      }
	  $qfpettitle[$i] = $petcardtitle;
      $petslotinfo[$i]['Family'] = "Level";
	  $displayhp = substr($reqhp, 1);
   ?>

		<div style="float: left; width: 237px; height: 170px; overflow: hidden" class="petcard_container">

            <?php // Details for Rematch ?>
            <div style="display: none">
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_special">2</span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_qc">ZL</span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_level"><?php echo $reqlevelpieces[0] ?></span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_petid"><?php echo $fetchpet ?></span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_petreqhp"><?php echo $displayhp ?></span>
            </div>


				<div class="bt_2_Level_d" style="width: 24px; height: 170px; float: left">
					<div style="float: left"><img src="https://www.wow-petguide.com/<?php echo $petcountimg ?>"></div>
					<div style="float: left; padding-top: 33px"><img src="https://www.wow-petguide.com/images/bt_2_ic_Level.png"></div>
				</div>

				<div id="petcard_<?php echo $i ?>">

					<div class="bt_2_Level" style="width: 213px; height: 170px; float: left">

						<div class="bt_petimage">
							<img src="https://www.wow-petguide.com/images/pets/resize50/level.png" class="bt_petimage">
						</div>

						<div class="bt_petname">
							<p class="bt_petdetails">
								<?php echo $petcardtitle; ?>
							</p>
						</div>

						<div class="bt_petdetails">
							<p class="bt_petdetails">

                  <?php // Print stat requirements if present

                  if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {
                      echo  '<table style="width: 100%; margin-top: 40px" cellpadding="0" cellspacing="0"><tr>';
                      if ($reqhp != "") {
                         echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_health.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqhp.'</td>';
                      }
                      if ($reqsp != "") {
                         echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_speed.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqsp.'</td>';
                      }
                      if ($reqpw != "") {
                         echo '<td><img src="https://www.wow-petguide.com/images/bt_icon_power.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqpw.'</td>';
                      }
                      echo "</tr></table>";
                   } ?>
						</div>
					</div>
				</div>
		</div>


   <?php }

 // Create pet card for Any Pet
   if ($fetchpet == "1") {
		$reqlevelpieces = explode("+", $reqlevel);
		$displayreqlvl = $reqlevelpieces[0]."+";
		$petlist_feedback[$i]['reqlevel'] = $reqlevelpieces[0];

		if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
         $petcardtitle = __("Any Pet");
      }
      else {
         if ($_SESSION["lang"] =="fr_FR"){
            $petcardtitle = __("Any Pet")." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
         }
         else {
            $petcardtitle = __("Any Level")." ".$displayreqlvl." ".__("Pet");
         }
      }
	  $qfpettitle[$i] = $petcardtitle;
      $petslotinfo[$i]['Family'] = "Any";
   ?>

		<div style="float: left; width: 237px; height: 170px; overflow: hidden" class="petcard_container">

            <?php // Details for Rematch ?>
            <div style="display: none">
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_special">1</span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_qc">ZR0</span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_petid"><?php echo $fetchpet ?></span>
					<span id ="rm_p<?php echo $i ?>_s<?php echo $strat->id ?>_petreqhp">0</span>
            </div>

				<div class="bt_2_any_d" style="width: 24px; height: 170px; float: left">
					<div style="float: left"><img src="https://www.wow-petguide.com/<?php echo $petcountimg ?>"></div>
					<div style="float: left; padding-top: 33px"><img src="https://www.wow-petguide.com/images/bt_2_ic_Any.png"></div>
				</div>

				<div id="petcard_<?php echo $i ?>">

					<div class="bt_2_any" style="width: 213px; height: 170px; float: left">

						<div class="bt_petimage">
							<img src="https://www.wow-petguide.com/images/pets/resize50/any.png" class="bt_petimage">
						</div>

						<div class="bt_petname">
							<p class="bt_petdetails">
								<?php echo $petcardtitle; ?>
							</p>
						</div>

						<div class="bt_petdetails">
							<p class="bt_petdetails">

                  <?php // Print stat requirements if present

                  if ($reqhp != "" OR $reqsp != "" OR $reqpw != "") {
                      echo  '<table style="width: 100%; margin-top: 40px" cellpadding="0" cellspacing="0"><tr>';
                      if ($reqhp != "") {
                         echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_health.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqhp.'</td>';
                      }
                      if ($reqsp != "") {
                         echo '<td style="padding-right: 5px"><img src="https://www.wow-petguide.com/images/bt_icon_speed.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqsp.'</td>';
                      }
                      if ($reqpw != "") {
                         echo '<td><img src="https://www.wow-petguide.com/images/bt_icon_power.png" style="vertical-align: middle;"><p class="bt_petdetails_small"> '.$reqpw.'</td>';
                      }
                      echo "</tr></table>";
                   } ?>
						</div>
					</div>
				</div>
		</div>


   <?php }

	$i++;
	} ?>

</div>



	<?php // Third Row - Substitutes ?>
	<?

	if ($petslotinfo[1]['Subscount'] > "1" OR $petslotinfo[2]['Subscount'] > "1" OR $petslotinfo[3]['Subscount'] > "1") { ?>

	<div style="width: 801px">
		<div class="bt_substitutes">
			<div style="text-align: center; margin-top: 4px;">
				<p class="commenteven">Substitutes:
			</div>
		</div>
		<?
      $i = "1";

      while ($i < "4") {

      if ($petslotinfo[$i]['Subscount'] > "1") { ?>
		<div class="bt_subpanel bt_subpanel_<?php echo $petslotinfo[$i]['Family'] ?>">
			<div class="bt_subpanel_d bt_subpanel_<?php echo $petslotinfo[$i]['Family'] ?>_d">
			   <span style="display: <?php echo $petslotinfo[$i]['ShowSubWarning']; ?>" id="bt_subwarning_<?php echo $i ?>" class="bt_subpanel_tt_<?php echo $i ?>" data-tooltip-content="#bt_subpanel_tt_<?php echo $i ?>"><img style="cursor: pointer" src="https://www.wow-petguide.com/images/icon_exclamation.png"></span>
						<div style="display: none">
							<span id="bt_subpanel_tt_<?php echo $i ?>">
							   <b>Note:</b> You have selected a substitute pet.<br>
							   The strategy might fail if you use it with this pet instead of the one the strategy creator intended.<br><br>

							   The substitute finder looks for these criteria in relation to the original pet:<br>
							   <ul>
								 <li>Same family</li>
								 <li>Has the required skills</li>
								 <li>Has the required stats (if specified)</li>
								 <li>Ignores specific breed requirements for the main pet, as breeds can be very different for substitutes</li>
							   </ul>
							</span>
						</div>

					<script>
							$(document).ready(function() {
								$('.bt_subpanel_tt_<?php echo $i ?>').tooltipster({
									maxWidth: '500',
									minWidth: '200',
									side: 'bottom',
									theme: 'tooltipster-smallnote'
								});
							});
					</script>
         </div>
			<div style="margin-top: 4px;">
				<p class="commenteven">
				<span style="padding: 0 8 0 12; cursor: pointer" onclick="bt_petcardswap('<?php echo $i ?>','<?php echo $petslotinfo[$i]['Subscount'] ?>','down','<?php echo $strat->id ?>')">
					<span class="bt_subarrowleft">
					</span>
				</span>
				<span id="bt_petcounter_<?php echo $i ?>"><?php echo $petslotinfo[$i]['DirectSub']; ?></span> / <?php echo $petslotinfo[$i]['Subscount'] ?>
				<span style="padding: 0 12 0 8; cursor: pointer" onclick="bt_petcardswap('<?php echo $i ?>','<?php echo $petslotinfo[$i]['Subscount'] ?>','up','<?php echo $strat->id ?>')">
					<span class="bt_subarrowright">
					</span>
				</span>
			</div>
		</div>
		<?php } else {
			echo '<div class="bt_subpanel bt_subpanel_'.$petslotinfo[$i]['Family'].'"><div class="bt_subpanel_d bt_subpanel_'.$petslotinfo[$i]['Family'].'_d"></div></div>';
			echo '<div style="display: none"><span id="bt_petcounter_'.$i.'">1</span></div>';
		}
      $i++;
      }
	echo "</div>";
	} ?>



    <?php // ============================================= Fourth Row ============================================= ?>
	<?php // Prepare comment from creator
    if (!isset($strat->{$altcommentext}) || $strat->{$altcommentext} == ""){
        $stratcomment = $strat->Comment;
    }
    else {
        $stratcomment = $strat->{$altcommentext};
    }
    if ($stratcomment){
        $stratcomment = stripslashes(htmlentities($stratcomment, ENT_QUOTES, "UTF-8"));
		$stratcomment = \BBCode\process_creator_comment($stratcomment);
    }

	// Add + 1 to Viewcounter
	if ($user->id != "2") {
		mysqli_query($dbcon, "UPDATE Alternatives SET Views = Views + 1 WHERE id  = '$strat->id'") OR die(mysqli_error($dbcon));
	}

	/* Below calculates reliability based on attempts and can potentially add the reliable tag - curators don't like it, booooh. 
	if ($user->id == 2) {
		$attempts_success_db = mysqli_query($dbcon, "SELECT * FROM UserAttempts WHERE Strategy = '$strat->id' AND Success = '1'") OR die(mysqli_error($dbcon));
		$attempts_all_db = mysqli_query($dbcon, "SELECT * FROM UserAttempts WHERE Strategy = '$strat->id'") OR die(mysqli_error($dbcon));
		
		$attempts_success = mysqli_num_rows($attempts_success_db);
		$attempts_all = mysqli_num_rows($attempts_all_db);
		
		if ($attempts_success/$attempts_all*100 >= 90) {
			// Add the reliable tag
		}
	} */
	
	
	// Update Tags in strat and update strat id tagsupdate
		update_tags($strat->id, $updatetags);

	?>
	<div style="width: 801px">
		<div class="bt_4_1"></div>
	</div>
	
	
    <div style="width: 801px; background-color: #12496F">

            <?php // ================== Comment from Creator
            if ($stratcomment) {
					$tagswidth = "230";
					?>
                <div style="width: 75px; float: left; margin: 4 0 0 8">
                    <?php echo $stratusericon ?>
                </div>
                <div style="max-width: 460px; float: left; margin: 5 8 5 8">
                    <p class="speech"></b><?php echo $stratcomment ?></p>
                </div>
            <?php }
			else {
				$tagswidth = "750";
			} ?>


            <?php // ================== Tags
			
			$used_tags = $all_tags;
			
			$show_tags = false;
		
			$active_tags_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = '$strat->id'");
			while ($this_tag = $active_tags_db->fetch_object())
			{
			$show_tags = true;
			  if ($all_tags[$this_tag->Tag]['ID']) {
				$used_tags[$this_tag->Tag]['Active'] = 1;
			  }	  
			}
						
			$used_tags[7]['Name'] = $used_tags[7]['Name']." ".$lowestlevel."+";
			
			// XP Tag title calculation
			
			if ($thissub->Experience > 0 && $thissub->Experience <= 5) {
				$xp_tag_title = __("Low XP");;
			}
			if ($thissub->Experience >= 5 && $thissub->Experience < 12.5) {
				$xp_tag_title = __("Medium XP");;
			}
			if ($thissub->Experience >= 12.5) {
				$xp_tag_title = __("High XP");;
			}

			// Output of Tags
			
            if ($show_tags == true OR $thissub->Experience > 0 OR $user->id == $strat->User OR $userrights['EditStrats'] == "yes") { ?>
                <div style="float: right; margin: 4 8 10 0; max-width: <?php echo $tagswidth ?>px">
                    <?php if ($thissub->Experience > 0) { ?>
                        <div class="tag tag_xp xp_tooltip" data-tooltip-content="#tag_xp_tt"><?php echo $xp_tag_title ?></div>
                    <?php }

					// Output of all regular tags
					foreach ($used_tags as $tag_id) {
						if (($userrights['EditStrats'] == "yes" OR $userrights['EditTags'] == "yes") OR $tag_id['Visible'] == 1)  { ?>
							<div id="tag_<?php echo $tag_id['ID'] ?>" class="tag tag_tt" style="background-color: #<?php echo $tag_id['Color']; if ($tag_id['Active'] != 1) { echo '; display: none'; } ?>" data-tooltip-content="#tag_<?php echo $tag_id['Slug'] ?>_tt"><?php echo $tag_id['Name'] ?></div>
							<div style="display: none">
								<span id="tag_<?php echo $tag_id['Slug'] ?>_tt"><?php echo $tag_id['Description'] ?></span>
							</div>	
						<?php }
					}
											
                    // Tootlip for Experience Gain
                    if ($thissub->Experience > 0) { ?>
					<div style="display: none">
						<span id="tag_xp_tt">
							 <?php echo __("This table shows the level increase for completing the fight with level 25 pets and one carry pet."); ?><br>
							 <?php echo __("Partial level experience is not included in the calculation."); ?><br><br>
							 <table class="tooltip" style="float: left; margin-right: 25px"><tr>
								 <th class="tooltip"><?php echo __("Before"); ?></th>
								 <th class="tooltip"></th>
								 <th class="tooltip"><?php echo __("After"); ?></th>
								 <th class="tooltip"><?php echo __("Experience Gain"); ?></th>
							 </tr>
							 <tr class="tooltip">
							 <?
							 $showgreenxp = "none";
							 $showredxp = "none";
							 $levelsteps = array(1 => "0", 2 =>  "50", 3 => "160", 4 => "280", 5 => "475", 6 => "755", 7 => "1205", 8 => "1765", 9 => "2360", 10 => "3080", 11 => "3840", 12 => "4740", 13 => "5685", 14 => "6675", 15 => "7825", 16 => "9025", 17 => "10275", 18 => "11705", 19 => "13190",20  => "14730", 21 => "16325", 22 => "18125", 23 => "19985", 24 => "21905", 25 => "23885");
							 $lvl = "1";
							 $firsttrigger = false;
							 $xpwaste = null;
							 while ($lvl < 25) {
								if ($firsttrigger == "true") {
									$xpwaste = "xpwaste";
									$showredxp = "block";
								}
								$newlevel = "";
								$rawxp = round($thissub->Experience*1.1*($lvl + 9) * (25 - $lvl + 5));
								$xpgaint = ($levelsteps[$lvl]+$rawxp)-$levelsteps[25];
								if ($xpgaint < "0") {
									$xpgain = $rawxp;
								}
								else {
									$xpgain = $rawxp-$xpgaint;
									$firsttrigger = "true";
									$showgreenxp = "block";
								}
								if ($firsttrigger == "true" && $xpwaste != "xpwaste") {
									$xpwaste = "xpmax";
								}

								$calctarget = $levelsteps[$lvl]+$rawxp;
								foreach ($levelsteps as $key => $value) {
									if ($calctarget >= $value) {
										$newlevel = $key;
									}
								}
								echo '<tr class="tooltip">';
								echo '<td class="lvlrow_'.$lvl.' tooltip '.$xpwaste.'" style="text-align: center">'.$lvl.'</td>';
								echo '<td class="lvlrow_'.$lvl.' tooltip '.$xpwaste.'" style="text-align: center; padding: 0 5 0 5">➜</td>';
								echo '<td class="lvlrow_'.$lvl.' tooltip '.$xpwaste.'" style="text-align: center"><span id="xp_target_'.$lvl.'">'.$newlevel.'</span></td>';
								echo '<td class="lvlrow_'.$lvl.' tooltip '.$xpwaste.'" style="text-align: center"><span id="xp_level_'.$lvl.'">'.$xpgain.'</span></td>';
								echo '</tr>';
								$lvl++;
							 }
							 ?>
							 </table>

							 <table>
								 <tr><td style="height: 20px"></td></tr>
								 <tr>
									 <td>
										 <input type="checkbox" id="xp_hat" onchange="bt_recalc_xp('<?php echo $thissub->Experience ?>')" value="true" checked>
									 </td>
									 <td>
										 <p class="commenteven" style="color: #fff; cursor: default" onClick="document.getElementById('xp_hat').checked = !document.getElementById('xp_hat').checked; bt_recalc_xp('<?php echo $thissub->Experience ?>')"><?php echo __("Safari Hat"); ?>
									 </td>
								 </tr>
								 <tr>
									 <td>
										 <input type="checkbox" id="xp_ltreat" onchange="bt_recalc_xp('<?php echo $thissub->Experience ?>')" value="true">
									 </td>
									 <td>
										 <p class="commenteven" style="color: #fff; cursor: default" onClick="document.getElementById('xp_ltreat').checked = !document.getElementById('xp_ltreat').checked; bt_recalc_xp('<?php echo $thissub->Experience ?>')"><?php echo __("Lesser Pet Treat"); ?>
									 </td>
								 </tr>
								 <tr>
									 <td>
										 <input type="checkbox" id="xp_btreat" onchange="bt_recalc_xp('<?php echo $thissub->Experience ?>')" value="true">
									 </td>
									 <td>
										 <p class="commenteven" style="color: #fff; cursor: default" onClick="document.getElementById('xp_btreat').checked = !document.getElementById('xp_btreat').checked; bt_recalc_xp('<?php echo $thissub->Experience ?>')"><?php echo __("Pet Treat"); ?>
									 </td>
								 </tr>
								 <tr>
									 <td>
										 <input type="checkbox" id="xp_dmhat" onchange="bt_recalc_xp('<?php echo $thissub->Experience ?>')" value="true">
									 </td>
									 <td>
										 <p class="commenteven" style="color: #fff; cursor: default" onClick="document.getElementById('xp_dmhat').checked = !document.getElementById('xp_dmhat').checked; bt_recalc_xp('<?php echo $thissub->Experience ?>')"><?php echo __("Darkmoon Hat"); ?>
									 </td>
								 </tr>
								 <tr>
									 <td>
										 <input type="checkbox" id="xp_petweek" onchange="bt_recalc_xp('<?php echo $thissub->Experience ?>')" value="true">
									 </td>
									 <td>
										 <p class="commenteven" style="color: #fff; cursor: default" onClick="document.getElementById('xp_petweek').checked = !document.getElementById('xp_petweek').checked; bt_recalc_xp('<?php echo $thissub->Experience ?>')"><?php echo __("Pet Battle Week"); ?>
									 </td>
								 </tr>
							 </table>

							<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

							<div id="greenxp" style="display: <?php echo $showgreenxp ?>;">
							<table>
								<tr>
									<td class="tooltip xpmax" style="padding-left: 10px; padding-top: 10px"></td>
									<td>
										<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto">=
									</td>
									<td rowspan="2">
										<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto"> <?php echo __("Pet levels to 25 with minimal XP lost"); ?>
									</td>
								</tr>
								<tr>
									<td style="padding-top: 15px;"></td>
									<td></td>
								</tr>
							</table>
							</div>

							<div id="redxp" style="display: <?php echo $showredxp ?>">
							<table>
								<tr>
									<td class="tooltip xpwaste" style="padding-left: 10px; padding-top: 10px"></td>
									<td>
										<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto">=
									</td>
									<td rowspan="2">
										<p style="color: #fff; font-size: 14px; font-family: MuseoSans-300,Roboto"> <?php echo __("Additional XP above level 25 is lost"); ?>
									</td>
								</tr>
								<tr>
									<td style="padding-top: 15px;"></td>
									<td></td>
								</tr>
							</table>
							</div>

						</span>
					</div>
    					<script>
    						$(document).ready(function() {
    							$('.xp_tooltip').tooltipster({
    								maxWidth: '450',
    								interactive: 'true',
    								minWidth: '400',
    								animation: 'fade',
    								side: 'left',
    								updateAnimation: 'scale',
    								theme: 'tooltipster-smallnote'
    							});
    						});
    					</script>
                    <?php } ?>
					<script>
						$(document).ready(function() {
							$('.tag_tt').tooltipster({
								maxWidth: '250',
								theme: 'tooltipster-smallnote'
							});
						});
					</script>
                </div>
			<?php } 		
		?>


		<table style="width: 100%; height: 1px"></table>
		<div style="width: 801px; background-color: #12496f">

			<div class="bt_infopanel bt_infopanel_b1">
				<b><?php echo __('Enemy Pets:'); ?></b><br>
					<?php if ($thissub->Pet1 != "") {
						$npcpetdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE PetID = $thissub->Pet1");
						$npcpet = mysqli_fetch_object($npcpetdb);
						echo '<a href="http://'.$wowhdomain.'.wowhead.com/npc='.$thissub->Pet1.'" target="_blank" class="bt_infobox">'.(isset($npcpet->{$petnext}) ? $npcpet->{$petnext} : "").'</a>';
						if ($thissub->Pet2 != "0") {
							$npcpetdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE PetID = $thissub->Pet2");
							$npcpet = mysqli_fetch_object($npcpetdb);
							echo ', <a href="http://'.$wowhdomain.'.wowhead.com/npc='.$thissub->Pet2.'" target="_blank" class="bt_infobox">'.(isset($npcpet->{$petnext}) ? $npcpet->{$petnext} : "").'</a>';
						}
						if ($thissub->Pet3 != "0") {
							$npcpetdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE PetID = $thissub->Pet3");
							$npcpet = mysqli_fetch_object($npcpetdb);
							echo ', <a href="http://'.$wowhdomain.'.wowhead.com/npc='.$thissub->Pet3.'" target="_blank" class="bt_infobox">'.(isset($npcpet->{$petnext}) ? $npcpet->{$petnext} : "").'</a>';
						}
					} ?>
			</div>
			
			<div class="bt_infopanel bt_infopanel_b2">
				<b><?php echo __('Strategy last updated:'); ?></b><br>
				<span name="time"><?php echo $strat->Updated; ?></span>
				<?php if ($strat->Updated != $strat->Created) { ?>
				<br><b><?php echo __('Strategy created:'); ?></b><br>
				<span name="time"><?php echo $strat->Created; ?></span>
				<?php } ?>
			</div>
			

			<div class="bt_infopanel bt_infopanel_b3">
				<?
				if (file_exists($map_image) OR $thissub->Coords != "") {
					if (file_exists($map_image)) { ?>
						<div class="tt_mapimage" style="display: inline" data-tooltip-content="#tt_mapimage"><img src="https://www.wow-petguide.com/images/bt_4_mag.png" style="vertical-align:middle; margin-bottom: 1px"></div>
						<div style="display: none">
							<span id="tt_mapimage"><img style="border-radius: 5px" src="<?php echo $map_image ?>"></span>
						</div>
						<script>
							$(document).ready(function() {
								$('.tt_mapimage').tooltipster({
									theme: 'tooltipster-smallnote',
									functionPosition: function(instance, helper, position){
										 position.coord.top += -450;
										 position.coord.left += -200;
										 return position;
									}
								});
							});
						</script>
					<?php }
					else { ?>
						<img src="https://www.wow-petguide.com/images/bt_4_mag.png" style="vertical-align:middle; margin-bottom: 1px">
					<?php }
					if ($thissub->Coords != "") {
						if ($thissub->Zone != "") {
							$coordsoutput = "/way ".$thissub->Zone." ".$thissub->Coords." ".$subnname;
						}
						else {
							$coordsoutput = "/way ".$thissub->Coords." ".$subnname;
						}

						echo $thissub->Coords.' (<a class="bt_infobox" style="cursor: pointer; text-decoration: underline" id="cb_coords" data-clipboard-text="'.$coordsoutput.'">TomTom</a>)';
						echo '<div class="remtt" style="display:none;" id="cb_coords_conf">'.__("Copied to clipboard!").'</div>';
						?>
						<script>
							var btn = document.getElementById('cb_coords');
							var clipboard = new Clipboard(btn);

							clipboard.on('success', function(e) {
								console.log(e);
									$('#cb_coords_conf').delay(0).fadeIn(500);
									$('#cb_coords_conf').delay(1200).fadeOut(500);
								});

							clipboard.on('error', function(e) {
								console.log(e);
							});
						</script>
					<?php }
				}
				?>


				<div class="tt_pageviews" data-tooltip-content="#tt_pageviews"><img src="https://www.wow-petguide.com/images/bt_4_paw.png" style="vertical-align:middle; margin-bottom: 3px"> <?php echo $strat->Views ?></div>
					<div style="display: none">
						<span id="tt_pageviews"><?php echo __('Pageviews. This number indicates how often this strategy has been accessed by visitors.'); ?></span>
					</div>
					<script>
						$(document).ready(function() {
							$('.tt_pageviews').tooltipster({
								maxWidth: '250',
								theme: 'tooltipster-smallnote'
							});
						});
					</script>
			</div>

		</div>
		
		
		<table style="width: 100%; height: 1px"></table>
		
			

		<?
		// ====== Record Battle Results ======= 
		if ($user) { ?>
		<div style="display: none; width: 801px; background-color: #12496f" id="attempt_panel">
			<div class="bt_fb_outer">
			<div id="bt_fb_form" class="bt_fb_container">
				<?php echo __('Help making this strategy better by recording your attempt:'); ?><br><br>

				<div class="bt_fb_form">
					<?
					$i = "1";
				// echo "<pre>";
				// print_r($petlist_feedback);
			
					while ($i < "4") {	
						// Line for a standard pet
						if ($petlist_feedback[$i]['id'] > "20") {
							foreach ($petlist_feedback[$i]['Sub_Details'] as $this_sub_key => $this_sub) {			
								if (file_exists('images/pets/resize50/'.$all_pets[$this_sub['PetID']]['PetID'].'.png')) {
									$petimage = 'images/pets/resize50/'.$all_pets[$this_sub['PetID']]['PetID'].'.png';
								}
								else {
									$petimage = 'images/pets/resize50/unknown.png';
								}
							 switch ($all_pets[$this_sub['PetID']]['Family']) {
								 case "Humanoid":
									 $bt_fb_bg = "0e6189";
								 break;
								 case "Magic":
									 $bt_fb_bg = "592d87";
								 break;
								 case "Elemental":
									 $bt_fb_bg = "7d471d";
								 break;
								 case "Undead":
									 $bt_fb_bg = "62464d";
								 break;
								 case "Mechanical":
									 $bt_fb_bg = "494849";
								 break;
								 case "Flying":
									 $bt_fb_bg = "787229";
								 break;
								 case "Critter":
									 $bt_fb_bg = "604538";
								 break;
								 case "Aquatic":
									 $bt_fb_bg = "00747e";
								 break;
								 case "Beast":
									 $bt_fb_bg = "811f22";
								 break;
								 case "Dragonkin":
									 $bt_fb_bg = "00783f";
								 break;
							 }
							if ($this_sub['Primary'] == "true") {
								$displaycard = "display: block;";
							}
							else {
								$displaycard = "display: none;";
							}
								?>
								<div style="display: none"><span id="bt_fb_npcid_<?php echo $i ?>_<?php echo $this_sub_key ?>"><?php echo $this_sub['PetID'] ?></span></div>
								<div id="fb_card_<?php echo $i.'_'.$this_sub_key; ?>" style="height: 25px; margin-bottom: 7px; <?php echo $displaycard ?>">
									<div class="bt_fb_panel bt_fb_front" style="width: 250px; background-color: #<?php echo $bt_fb_bg ?>; margin-right: 2px;"><img src="https://www.wow-petguide.com/<?php echo $petimage ?>" class="bt_fb_peticon"><?php echo $all_pets[$this_sub['PetID']]['Name'] ?></div>
									<div class="bt_fb_panel" style="width: 100px; padding-left: 5px;"><?php echo __('Breed used:'); ?> </div>
									<div class="bt_fb_panel bt_fb_end" style="width: 380px; margin-left: 2px; padding-left: 5px;">
									<?
									foreach ($this_sub['BreedInfo'] as $breedkey => $breedvalue) {
										$highbreed = "";
										if ($this_sub['SelectedBreed'] == "" && $breedkey == "0") {
											$highbreed = ' checked="checked"';
										}
										if ($this_sub['SelectedBreed'] == $breedvalue['Breed']) {
											$highbreed = ' checked="checked"';
										} ?>
										<input class="hidden radio-label yes-button" value="<?php echo $breedvalue['Breed'] ?>" type="radio" name="bt_fb_br_<?php echo $i.'_'.$this_sub_key ?>" id="brsel_<?php echo $i.'_'.$this_sub_key."_".$breedvalue['Breed'] ?>" <?php echo $highbreed ?>>
										<label class="button-label" for="brsel_<?php echo $i.'_'.$this_sub_key."_".$breedvalue['Breed'] ?>">
										  <h1><?php echo $breedvalue['Breed'] ?></h1>
										</label>
									<?php } ?>

									</div>
								</div>
							<?
							}
						}

						// Any Family Pet
						if ($petlist_feedback[$i]['id'] > "10" && $petlist_feedback[$i]['id'] <= "20") {

						 switch ($petlist_feedback[$i]['id']) {
							 case "11":
								 $bt_fb_name = __("Any")." ".__("Humanoid");
								 $bt_fb_bg = "0e6189";
							 break;
							 case "12":
								 $bt_fb_name = __("Any")." ".__("Magic");
								 $bt_fb_bg = "592d87";
							 break;
							 case "13":
								 $bt_fb_name = __("Any")." ".__("Elemental");
								 $bt_fb_bg = "7d471d";
							 break;
							 case "14":
								 $bt_fb_name = __("Any")." ".__("Undead");
								 $bt_fb_bg = "62464d";
							 break;
							 case "15":
								 $bt_fb_name = __("Any")." ".__("Mech");
								 $bt_fb_bg = "494849";
							 break;
							 case "16":
								 $bt_fb_name = __("Any")." ".__("Flyer");
								 $bt_fb_bg = "787229";
							 break;
							 case "17":
								 $bt_fb_name = __("Any")." ".__("Critter");
								 $bt_fb_bg = "604538";
							 break;
							 case "18":
								 $bt_fb_name = __("Any")." ".__("Aquatic");
								 $bt_fb_bg = "00747e";
							 break;
							 case "19":
								 $bt_fb_name = __("Any")." ".__("Beast");
								 $bt_fb_bg = "811f22";
							 break;
							 case "20":
								 $bt_fb_name = __("Any")." ".__("Dragon");
								 $bt_fb_bg = "00783f";
							 break;
						 }
							?>
							<div id="fb_card_<?php echo $i; ?>" style="height: 25px; margin-bottom: 7px;">
								<div class="bt_fb_panel bt_fb_front" style="width: 250px; background-color: #<?php echo $bt_fb_bg ?>; margin-right: 2px;"><img src="https://www.wow-petguide.com/images/pets/resize50/<?php echo $petlist_feedback[$i]['id'] ?>.png" class="bt_fb_peticon"><?php echo $bt_fb_name ?></div>
								<div class="bt_fb_panel" style="width: 100px; padding-left: 5px;"><?php echo __('Level:'); ?>: <span id="bt_fb_counter_<?php echo $i ?>"><?php echo $petlist_feedback[$i]['reqlevel'] ?></span></div>
								<div class="bt_fb_panel bt_fb_end" style="width: 385px; margin-left: 2px;"><input type="range" min="1" max="25" value="<?php echo $petlist_feedback[$i]['reqlevel'] ?>" id="bt_fb_range_<?php echo $i ?>" style="width: 370px" class="bt_fb_slider"></div>
							</div>
							
							<script>
								document.getElementById("bt_fb_range_<?php echo $i ?>").oninput = function() {
									 document.getElementById("bt_fb_counter_<?php echo $i ?>").innerHTML = this.value;
								}
							</script>
							<?
						}

						
						// Level Pet or Any Pet
						if ($petlist_feedback[$i]['id'] == "0" OR $petlist_feedback[$i]['id'] == "1") {
							switch($petlist_feedback[$i]['id']) {
								case "0":
									$bt_fb_bg = "763a7a";
									$bt_fb_img = "images/pets/resize50/level.png";
									$bt_fb_name = "Level Pet";
									break;
								case "1":
									$bt_fb_bg = "8e8e8e";
									$bt_fb_img = "images/pets/resize50/any.png";
									$bt_fb_name = "Any Pet";
									break;
							}
							?>
							<div id="fb_card_<?php echo $i; ?>" style="height: 25px; margin-bottom: 7px;">
								<div class="bt_fb_panel bt_fb_front" style="width: 250px; background-color: #<?php echo $bt_fb_bg ?>; margin-right: 2px;"><img src="https://www.wow-petguide.com/<?php echo $bt_fb_img ?>" class="bt_fb_peticon"><?php echo $bt_fb_name ?></div>
								<div class="bt_fb_panel" style="width: 100px; padding-left: 5px;"><?php echo __('Level:'); ?>: <span id="bt_fb_counter_<?php echo $i ?>"><?php echo $petlist_feedback[$i]['reqlevel'] ?></span></div>
								<div class="bt_fb_panel bt_fb_end" style="width: 385px; margin-left: 2px;"><input type="range" min="1" max="25" value="<?php echo $fbpet[$i]['reqlevel'] ?>" id="bt_fb_range_<?php echo $i ?>" style="width: 370px" class="bt_fb_slider"></div>
							</div>
							<script>
								document.getElementById("bt_fb_range_<?php echo $i ?>").oninput = function() {
									 document.getElementById("bt_fb_counter_<?php echo $i ?>").innerHTML = this.value;
								}
							</script>
						<?php }
						$i++;
					} ?>


					<br>
					<div id="fb_card_4" style="height: 25px; margin-bottom: 7px;">
						<div class="bt_fb_panel bt_fb_front" style="width: 200px; margin-right: 2px; padding-left: 5px"><?php echo __('Attempts required:'); ?></div>
						<div class="bt_fb_panel" style="width: 30px; text-align: center"><span id="bt_fb_counter_4">1</span></div>
						<div class="bt_fb_panel bt_fb_end" style="width: 320px; margin-left: 2px;"><input type="range" min="1" max="100" value="1" id="bt_fb_range_4" style="width: 310px" class="bt_fb_slider"></div>
					</div>
					<script>
						var slider4 = document.getElementById("bt_fb_range_4");
						var output4 = document.getElementById("bt_fb_counter_4");
						slider4.oninput = function() {
							 output4.innerHTML = this.value;
						}
					</script>

					<div id="fb_card_5">
						<div class="bt_fb_panel bt_fb_front" style="width: 200px; margin-right: 2px; padding-left: 5px"><?php echo __('Successful:'); ?></div>
						<div class="bt_fb_panel bt_fb_end" style="width: 100px; text-align: center">

							<input class="hidden radio-label yes-button" value="1" type="radio" name="bt_fb_success" id="success" checked="checked"/>
							<label class="button-label" for="success">
							  <h1><?php echo __('Yes'); ?></h1>
							</label>
							<input class="hidden radio-label no-button" value="0" type="radio" name="bt_fb_success" id="nosuccess"/>
							<label class="button-label" for="nosuccess">
							  <h1><?php echo __('No'); ?></h1>
							</label>

						</div>
						<div class="bt_fb_send">
							<input class="hidden radio-label send-button" type="radio" name="sendbutton" id="sendbutton" onclick="bt_record_fight('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $strat->id ?>')" checked="checked"/>
							<label class="button-label" for="sendbutton">
							  <h1><?php echo __('Send'); ?></h1>
							</label>
						</div>
					</div>
				</div>
				<br><br>
			</div>
			<div id="bt_fb_saved" class="bt_fb_container" style="display: none">
				<center><?php echo __('Your attempt has been recorded. Thank you very much!'); ?></center>
			</div>
		
		</div>
	</div>
		
		
			<center><div id="bt_3_arr" style="padding-bottom: 8px"><img style="cursor: pointer" src="https://www.wow-petguide.com/images/bt_3_arrow.png" onclick="$('#attempt_panel').show(600); $('#bt_3_arr').hide(); $('#bt_3_arrup').show();"></div></center>
			<center><div id="bt_3_arrup" style="display:none; padding-bottom: 8px"><img style="cursor: pointer" src="https://www.wow-petguide.com/images/bt_3_arrowup.png" onclick="$('#attempt_panel').hide(600); $('#bt_3_arr').show(); $('#bt_3_arrup').hide();"></div></center>
	
	
	
		
	
	<?php } ?>
	
	</div>

	<div style="width: 801px">
		<div class="bt_4_3"></div>
	</div>

	<?php // Strategy Steps 2.0
		echo '</b><div class="bt_steps" id="bt_steps"><div style="display: none" id="step_firstline"></div>';
			$stepsdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = $strat->id ORDER BY id");
			while ($step = mysqli_fetch_object($stepsdb)) {
				bt_stredit_printline($step, $strat, $language); 
			}
		echo "</div>";
	// END OF NEW steps table
	?>

	<div style="height: 90px; width: 801px; background-image: url(https://www.wow-petguide.com/images/battle_05.png)">
		<?
		if (!isset($thissub->{$subcomext}) || $thissub->{$subcomext} == ""){ $subcomment = $thissub->Comment; }
		else { $subcomment = $thissub->{$subcomext}; }
		?>
		<div style="padding-left: 110px; float: left; padding-top: 14px; height: 76px">
			<img src="https://www.wow-petguide.com/images/xufu_small.png">
		</div>
		<div style="float: left; padding-left: 15px; width: 525px; position: relative; top: 50%; -webkit-transform: translateY(-50%); -ms-transform: translateY(-50%); transform: translateY(-50%);">
			<p class="comment"><i><?php echo $subcomment ?></i></p>
		</div>
	</div>


<br><br>