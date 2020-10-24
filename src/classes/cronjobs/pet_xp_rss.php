<?php
include("../../data/dbconnect.php");
include("../../classes/functions.php");

// XML-Datei automatisch erstellen
$xml = new DOMDocument('1.0', 'utf-8');
$xml->formatOutput = true;

$rss = $xml->createElement('rss');
$rss->setAttribute('version', '2.0');
$xml->appendChild($rss);

$channel = $xml->createElement('channel');
$rss->appendChild($channel);

// Head des Feeds
$head = $xml->createElement("title", "WoW Pet Level Events");
$channel->appendChild($head);

$head = $xml->createElement('description', 'Daily updates from Xu-Fu on pet leveling opportunities (https://wow-petguide.com)');
$channel->appendChild($head);

$head = $xml->createElement('language', 'en');
$channel->appendChild($head);

$head = $xml->createElement('link', 'https://www.wow-petguide.com');
$channel->appendChild($head);

// Aktuelle Zeit, falls time() in MESZ ist, muss 1 Stunde abgezogen werden
$head = $xml->createElement('lastBuildDate', date("D, j M Y H:i:s ", time() - 7200).'GMT');
$channel->appendChild($head);

// $head = $xml->createElement('pubDate', date('r', strtotime($tamers->Date)));
// $channel->appendChild($head);


    $image = $xml->createElement('image');
    $data = $xml->createElement('url', 'https://wow-petguide.com/images/xufuprofile.jpg');
    $image->appendChild($data);
    $data = $xml->createElement('title', 'WoW Pet Level Events');
    $image->appendChild($data);
    $data = $xml->createElement('link', 'https://www.wow-petguide.com');
    $image->appendChild($data);

    $channel->appendChild($image); 


// Array of all legion quests
$all_tamers_db = mysqli_query($dbcon, "SELECT * FROM Home_Tamers") OR die(mysqli_error($dbcon));
while($this_tamer = mysqli_fetch_object($all_tamers_db)) {
    $all_tamers[$this_tamer->Tamer_id] = $this_tamer->Fight_id;
}

// Feed Einträge
$legion_tamers_db = mysqli_query($dbcon, 'SELECT * FROM Home_LegionQuests ORDER BY Date DESC LIMIT 10');
while ($tamers = mysqli_fetch_object($legion_tamers_db))
{
    $content_text = "";
    $eudiff = "";
    $usdiff = "";
    $eu_petweek = "";
    $us_petweek = "";
    
    // SQUIRT:
    // EU Calculations
    $eustart = strtotime("2017-01-27 12:00:00");
    $end = strtotime($tamers->Date);
    $eudiff = ((($end-$eustart)/86400) % 15);

    if ($eudiff == 0) {
     $eu_squirt_today = TRUE;
    }
    
    // US Calculations
    $usstart = strtotime("2017-02-02 12:00:00");
    $usdiff = ((($end-$usstart)/86400) % 15);

    
    // Check against upcoming pet bonus weeks
    $petweeks_db = mysqli_query($dbcon, "SELECT * FROM Home_PetWeeks") OR die(mysqli_error($dbcon));
     while($petweek = mysqli_fetch_object($petweeks_db)) {
      $eu_petweek_diff = ((strtotime($petweek->Start)-strtotime($tamers->Date))/60/60/24)+8;
      if ($eu_petweek_diff > 0 && $eu_petweek_diff < 7) {
       $eu_petweek = TRUE;
      }
      $us_petweek_diff = ((strtotime($petweek->Start)-strtotime($tamers->Date))/60/60/24)+7;
      if ($us_petweek_diff > 0 && $us_petweek_diff < 7) {
       $us_petweek = TRUE;
      }
     }
     
    if ($us_petweek == TRUE && $eu_petweek == TRUE) {
        $content_text = $content_text.'<b>Bonus Pet Week</b> is active!<br>';
    }
    if ($us_petweek == TRUE && $eu_petweek != TRUE) {
        $content_text = $content_text.'<b>Bonus Pet Week</b> is active in the <b>US</b>!<br>';
    }
    if ($us_petweek != TRUE && $eu_petweek == TRUE) {
        $content_text = $content_text.'<b>Bonus Pet Week</b> is active in the <b>EU</b>!<br>';
    }
    
    
    if ($eudiff == 0) {
        $content_text = $content_text.'<b>EU:</b> <a class="home_legion" href="?Strategy=731">Squirt</a> is active in your Garrison today!<br>';
    }
    if ($usdiff == 0) {
        $content_text = $content_text.'<b>US:</b> <a class="home_legion" href="?Strategy=731">Squirt</a> is active in your Garrison today!<br>';
    }
    
    

    
    $date_splits = explode('-', $tamers->Date);
    $date_splits_2 = explode(' ', $date_splits[2]);
    $todays_date = $date_splits[0]."-".$date_splits[1]."-".$date_splits_2[0];
    
    $item = $xml->createElement('item');
    $channel->appendChild($item);
    
    $showtitle = htmlspecialchars('Pet XP Opportunities for '.$todays_date, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('title', $showtitle);
    $item->appendChild($data);
    
    
    
    if ($tamers->Quests == "") {
     $content_text = $content_text."No active Legion quests today.";
    }
    else {
     $content_text = $content_text."<b>Legion Quests:</b><br>";
     $show_tamers = explode('-', $tamers->Quests);
     foreach ($show_tamers as $tamer) {
      $tamer_details = decode_sortingid(3,$tamer);
      $content_text = $content_text.'- <a class="home_legion" href="?Strategy='.$all_tamers[$tamer].'">'.$tamer_details[1].'</a><br>';
     }
    }

    // ADD PET XP WEEK and SQUIRT
    
    $data = $xml->createElement('pubDate', date('r', strtotime($tamers->Date)));
    $item->appendChild($data);

    $data = $xml->createElement('description', utf8_encode($content_text));
    $item->appendChild($data);

    $bloglink = 'https://wow-petguide.com/';
    $bloglink = htmlspecialchars($bloglink, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('link', $bloglink);
    $item->appendChild($data);

    
    $data = $xml->createElement('guid', 'wow-petguide_pet-level-events'.$tamers->id);
    $data->setAttribute ('isPermaLink', 'false');
    $item->appendChild($data);
    
    
}

mysqli_close($dbcon);

// Speichere XML Datei
$xml->save('../../rss/pet_xp_feed.xml');

// Rufe die XML Datei auf
// Header('Location: https://www.wow-petguide.com/rss/pet_xp_feed.xml');
?>
