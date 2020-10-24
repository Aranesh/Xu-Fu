<?php
include("../../data/dbconnect.php");
include("../../classes/functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$strat = $_REQUEST["strat"];
$tag = $_REQUEST["tag"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
    $user_ip_adress = $_SERVER['REMOTE_ADDR'];
}
$failed = FALSE;

// User exists
$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
if (mysqli_num_rows($userdb) > "0") {
    $user = mysqli_fetch_object($userdb);
}
else {
    $failed = TRUE;
}

// Strategy exists
if ($failed != TRUE) {
    $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $strat");
    if (mysqli_num_rows($stratdb) < "1") {
        $failed = TRUE;        
    }
    else {
        $strat = mysqli_fetch_object($stratdb);
    }
}

// Tag exists
if ($user && $user->ComSecret == $comsecret && $failed != TRUE) { 
    $this_tag_db = mysqli_query($dbcon, "SELECT * FROM StrategyTags WHERE id = $tag");
    if (mysqli_num_rows($this_tag_db) < 1) {
        $failed = TRUE;
    }
    else {
      $this_tag = mysqli_fetch_object($this_tag_db);
    }
}

// User has access rights to edit this tag
if ($failed == FALSE) {
    $userrights = format_userrights($user->Rights);
    
    if ($userrights['EditStrats'] != "yes" && $userrights['EditTags'] != "yes" && $strat->User == $user->id) { // User is only the strategy creator
        if ($this_tag->Access != 1) {
            $failed = TRUE;
        }
    }

    // User can edit tags of all strategies
    if ($userrights['EditStrats'] == "yes" OR $userrights['EditTags'] == "yes") {
        if ($this_tag->Access == 0) { // This tag cannot be modified by anyone (system tag)
            $failed = TRUE;
        }
    }
}

// Everything is passed, modify tag now
if ($failed == FALSE) {
    $updatetags = array();
    $all_tags_db = mysqli_query($dbcon, "SELECT * FROM StrategyTags");
    while ($this_tag = $all_tags_db->fetch_object())
    {
      $all_tags[$this_tag->id]['ID'] = $this_tag->id;
      $all_tags[$this_tag->id]['Slug'] = $this_tag->Slug;
      $all_tags[$this_tag->id]['Access'] = $this_tag->Access;
      $all_tags[$this_tag->id]['Visible'] = $this_tag->Visible;
      $all_tags[$this_tag->id]['Color'] = $this_tag->Color;
      $all_tags[$this_tag->id]['DefaultPrio'] = $this_tag->DefaultPrio;
      $all_tags[$this_tag->id]['Active'] = 0;
    }
    // Check status of tag on this strategy
    $tag_x_strat_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = $strat->id AND Tag = $tag");
    if (mysqli_num_rows($tag_x_strat_db) > 0) {
        $updatetags[$tag] = 0;
        echo "0";
    }
    else {
        if ($tag == 22) $updatetags[21] = 0;
        $updatetags[$tag] = 1;
        echo "1";
    }
    ini_set ('display_errors', 1);
    update_tags($strat->id, $updatetags);
}

mysqli_close($dbcon);
