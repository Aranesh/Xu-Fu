<?php
include("data/dbconnect.php");

// XML-Datei automatisch erstellen
$xml = new DOMDocument('1.0', 'utf-8');
$xml->formatOutput = true;

$rss = $xml->createElement('rss');
$rss->setAttribute('version', '2.0');
$xml->appendChild($rss);

$channel = $xml->createElement('channel');
$rss->appendChild($channel);

// Head des Feeds
$head = $xml->createElement("title", "Xufu Pet Battle Blog");
$channel->appendChild($head);

$head = $xml->createElement('description', 'Read the latest news and updates from wow-petguide.com');
$channel->appendChild($head);

$head = $xml->createElement('language', 'en');
$channel->appendChild($head);

$head = $xml->createElement('link', 'https://www.wow-petguide.com');
$channel->appendChild($head);

// Aktuelle Zeit, falls time() in MESZ ist, muss 1 Stunde abgezogen werden
$head = $xml->createElement('lastBuildDate', date("D, j M Y H:i:s ", time() - 3600).' GMT');
$channel->appendChild($head);

// Feed Einträge
$result = mysqli_query($dbcon, 'SELECT id, CreatedTime, Category, CreatedBy, Title_en_US, Content_en_US FROM News_Articles WHERE Active = "1" ORDER BY CreatedTime DESC');
while ($rssdata = mysqli_fetch_array($result))
{
    $item = $xml->createElement('item');
    $channel->appendChild($item);

    $showtitle = htmlspecialchars($rssdata["Title_en_US"], ENT_XML1, 'UTF-8');
    $data = $xml->createElement('title', $showtitle);
    $item->appendChild($data);

    if (strlen($rssdata["Content_en_US"]) > "500") {
        $showtext = substr($rssdata["Content_en_US"], 0, 500);
        $cutter = "499";
        while (substr($showtext, -1) != " ") {
            $showtext = substr($rssdata["Content_en_US"], 0, "$cutter");
            $cutter = $cutter - 1;
        }
        $showtext = $showtext." [...]";
    }
    else {
        $showtext = $rssdata["Content_en_US"];
    }

    $showtext = htmlspecialchars($showtext, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('description', utf8_encode($showtext));
    $item->appendChild($data);

    $bloglink = 'https://wow-petguide.com/?News='.$rssdata["id"];;
    $bloglink = htmlspecialchars($bloglink, ENT_XML1, 'UTF-8');
    $data = $xml->createElement('link', $bloglink);
    $item->appendChild($data);

    /*
    $image = $xml->createElement('image');
    $data = $xml->createElement('url', 'https://www.w3schools.com/images/logo.gif');
    $image->appendChild($data);
    $data = $xml->createElement('title', 'W3Schools.com');
    $image->appendChild($data);
    $data = $xml->createElement('link', 'https://www.w3schools.com</link>');
    $image->appendChild($data);

    $item->appendChild($image);
    */

    $data = $xml->createElement('pubDate', date('r', strtotime($rssdata["CreatedTime"])));
    $item->appendChild($data);

    $data = $xml->createElement('guid', $bloglink);
    $item->appendChild($data);
}

mysqli_close($dbcon);

// Speichere XML Datei
$xml->save('rss/rss_feed.xml');

// Rufe die XML Datei auf
Header('Location: https://www.wow-petguide.com/rss/rss_feed.xml');
?>
