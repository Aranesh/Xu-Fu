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
$head = $xml->createElement("title", "Featured Pet Videos");
$channel->appendChild($head);

$head = $xml->createElement('description', 'Pet Battle related videos featured on Xu-Fus Pet Guides');
$channel->appendChild($head);

$head = $xml->createElement('language', 'en');
$channel->appendChild($head);

$head = $xml->createElement('link', 'https://www.wow-petguide.com');
$channel->appendChild($head);

// Aktuelle Zeit, falls time() in MESZ ist, muss 1 Stunde abgezogen werden
$head = $xml->createElement('lastBuildDate', date("D, j M Y H:i:s ", time() - 3600).'GMT');
$channel->appendChild($head);


    $image = $xml->createElement('image');
    $data = $xml->createElement('url', 'https://wow-petguide.com/images/xufuprofile.jpg');
    $image->appendChild($data);
    $data = $xml->createElement('title', 'WoW Pet Level Events');
    $image->appendChild($data);
    $data = $xml->createElement('link', 'https://www.wow-petguide.com');
    $image->appendChild($data);

    $channel->appendChild($image); 


// Feed Einträge
$videos_db = mysqli_query($dbcon, 'SELECT * FROM Home_Videos WHERE Deleted = 0 ORDER BY Date DESC LIMIT 5');
while ($video = mysqli_fetch_object($videos_db))
{
    $item = $xml->createElement('item');
    $channel->appendChild($item);
    
    $showtitle = htmlspecialchars($video->Title, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('title', $showtitle);
    $item->appendChild($data);
    
    $description = 'Channel: '.$video->Channel.'<br><br>'.$video->Description;
    
    if (strlen($description) > "350") {
        $description = substr($description, 0, 350);
        $cutter = "349";
        while (substr($description, -1) != " ") {
            $description = substr($description, 0, "$cutter");
            $cutter = $cutter - 1;
        }
        $description = $description." [...]";
    }
    $description = replace_url_discord($description);
    
    
    
    $description = htmlspecialchars($description, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('description', utf8_encode($description));
    $item->appendChild($data);

    $bloglink = htmlspecialchars($video->Link, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('link', $bloglink);
    $item->appendChild($data);

    $data = $xml->createElement('pubDate', date('r', strtotime($video->Date)));
    $item->appendChild($data);

    $data = $xml->createElement('guid', 'wow-petguide_pet-videos'.$video->id);
    $data->setAttribute ('isPermaLink', 'false');
    $item->appendChild($data);    
}

mysqli_close($dbcon);

// Speichere XML Datei
$xml->save('../../rss/pet_videos.xml');

// Rufe die XML Datei auf
// Header('Location: https://www.wow-petguide.com/rss/pet_videos.xml');
?>
