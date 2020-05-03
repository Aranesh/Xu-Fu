<?php
namespace BBCode;

// ======================================= CONTROLLING ARTICLE PARSING ===========================================================================

function process_news_full($article) {
    $article = \BBCode\bbparse_urls($article, 16, 'home');                        // URLs
    $toc = \BBCode\bbparse_toc($article);                                         // Headlines - only formatting
    $article = $toc['article']; 
    $article = \BBCode\bbparse_pets($article, 16);                                // Replace Pets & spells
    $article = \BBCode\bbparse_users($article, 16);                               // Replace Usernames
    $article = \BBCode\bbparse_images($article, 'news');                          // Replace Images
    $article = \BBCode\bbparse_simple($article);                                  // Standard text formats bold, italic, underscore, strikethrough
    $article = \BBCode\bbparse_tables($article);                                  // Tables
    $article = \BBCode\bbparse_advanced($article);                                // Advanced text formats, quote, bluepost, bullets, aligning, font-size, code, hr
    $article = \BBCode\bbparse_cleanup($article);                                 // Clean-up of previous steps - always include at end!
    return $article;
}

function process_news_preview($article, $img_size) {
    $article = \BBCode\bbparse_urls($article, 14, 'home');                        // URLs
    $toc = \BBCode\bbparse_toc($article);                                         // Headlines - only formatting
    $article = $toc['article']; 
    $article = \BBCode\bbparse_pets($article, 14);                                // Replace Pets & spells
    $article = \BBCode\bbparse_users($article, 14);                               // Replace Usernames
    $article = \BBCode\bbparse_images($article, 'news', $img_size);               // Replace Images
    $article = \BBCode\bbparse_simple($article);                                  // Standard text formats bold, italic, underscore, strikethrough
    $article = \BBCode\bbparse_tables($article);                                  // Tables
    $article = \BBCode\bbparse_advanced($article);                                // Advanced text formats, quote, bluepost, bullets, aligning, font-size, code, hr
    $article = \BBCode\bbparse_cleanup($article);                                 // Clean-up of previous steps - always include at end!
    return $article;
}

function process_main_article($article) {
    $article = \BBCode\bbparse_urls($article, 16, 'home');                        // URLs
    $toc = \BBCode\bbparse_toc($article);                                         // Headlines - only formatting
    $article = $toc['article']; 
    $article = \BBCode\bbparse_pets($article, 16);                                // Replace Pets & spells
    $article = \BBCode\bbparse_users($article, 16);                               // Replace Usernames
    $article = \BBCode\bbparse_images($article, 'article');                       // Replace Images
    $article = \BBCode\bbparse_simple($article);                                  // Standard text formats bold, italic, underscore, strikethrough
    $article = \BBCode\bbparse_tables($article);                                  // Tables
    $article = \BBCode\bbparse_advanced($article);                                // Advanced text formats, quote, bluepost, bullets, aligning, font-size, code, hr
    $article = \BBCode\bbparse_cleanup($article);                                 // Clean-up of previous steps - always include at end!
    $toc['article'] = $article;
    return $toc;
}

function process_creator_comment($article) {
    $article = \BBCode\bbparse_urls($article, 14, 'strategy');                    // URLs
    $article = \BBCode\bbparse_pets($article, 14, 'strategy');                    // Replace Pets & spells
    $article = \BBCode\bbparse_simple($article);                                  // Standard text formats bold, italic, underscore, strikethrough
    $article = \BBCode\bbparse_cleanup($article);                                 // Clean-up of previous steps - always include at end!
    return $article;
}





// ======================================= FUNCTIONS FOR PARSING ===========================================================================


function bbparse_tables($article) {
    $article = str_replace('[table]', '<table class="articles">', $article);
    $article = str_replace('[/table]', '</table>', $article);
    $article = str_replace('[tr]', '<tr>', $article);
    $article = str_replace('[/tr]', '</tr>', $article);
    $article = str_replace('[td]', '<td>', $article);
    $article = str_replace('[/td]', '</td>', $article);
    return $article;
}

function bbparse_urls($article, $link_size, $area = 'default') {
    $link_class = "weblink";
    if ($area == "strategy") { $link_class = "comlink"; }
    if (strpos($article, '[url=') !== false && strpos($article, '[/url]') !== false) {
        $cutarticle = explode("[url=", $article);
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snippets1 = explode("[/url]", $value);
                $snippets2 = explode("]", $snippets1[0]);
                $replacestring = '[url='.$snippets2[0].']'.$snippets2[1].'[/url]';
                $maskurl = preg_replace('/^(?!https?:\/\/)/', 'http://', $snippets2[0]);
                $maskurl = str_replace('http','WLPz37f2',$maskurl);
                $maskurl = str_replace('www','MjwMhR9z',$maskurl);
                $replacewith = '<a href="'.$maskurl.'" target="_blank" style="font-size: '.$link_size.'px" class="'.$link_class.'">'.$snippets2[1].'</a>';
                $article = str_replace($replacestring,$replacewith,$article);
            }
        }
    }
    // Format normally added links:
    $article = AutoLinkUrls($article,'1',$area,$link_size);
    return $article;
}

function bbparse_toc($article) { 
    if ((strpos($article, '[h1]') !== false && strpos($article, '[/h1]') !== false) OR (strpos($article, '[h2]') !== false && strpos($article, '[/h2]') !== false) OR (strpos($article, '[h3]') !== false && strpos($article, '[/h3]') !== false)) {
        $cutarticle = preg_split("/\[h[123]\]/", $article);
        $majorcount = 1;
        $minorcount = 1;
        $smallestcount = 1;
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value){
            if ($key != "0") {
                $headerpieces = explode("[/h", $value);
                $headertype = explode("]", $headerpieces[1]);
                switch ($headertype[0]) {
                    case "1":
                        $toc[$key]['type'] = "1";
                        $toc[$key]['count'] = $majorcount;
                        $beginningfollow = explode("[/h1]", $value);
                        $toc[$key]['anchor'] = 'anchor_'.$majorcount;
                        $toc[$key]['title'] = $majorcount.'. '.$headerpieces[0];
                        $beginning = $beginning.'<h1 class="news" id="anchor_'.$majorcount.'">'.$majorcount.'. '.$headerpieces[0].'</h1>'.$beginningfollow[1];
                        $majorcount++;
                        $minorcount = "1";
                        break;
                    case "2":
                        $toc[$key]['type'] = "2";
                        $toc[$key]['count'] = $minorcount;
                        $beginningfollow = explode("[/h2]", $value);
                        $displaymajor = $majorcount-1;
                        $toc[$key]['anchor'] = 'anchor_'.$displaymajor.$minorcount;
                        $toc[$key]['title'] = $displaymajor.'.'.$minorcount.' '.$headerpieces[0];
                        $beginning = $beginning.'<h2 class="news" id="anchor_'.$displaymajor.$minorcount.'">'.$displaymajor.'.'.$minorcount.' '.$headerpieces[0].'</h2>'.$beginningfollow[1];
                        $minorcount++;
                        break;
                    case "3":
                        $toc[$key]['type'] = "3";
                        $toc[$key]['count'] = $smallestcount;
                        $beginningfollow = explode("[/h3]", $value);
                        $toc[$key]['anchor'] = 'anchor_'.$displaymajor.$minorcount.$smallestcount;
                        $toc[$key]['title'] = $headerpieces[0];
                        $beginning = $beginning.'<h3 class="news" id="anchor_'.$displaymajor.$minorcount.$smallestcount.'">'.$headerpieces[0].'</h2>'.$beginningfollow[1];
                        $smallestcount++;
                        break;
                }
            }
        }
        $article = $beginning;
    }
    $toc['article'] = $article;
    return $toc;
}

function bbparse_pets($article, $link_size, $area = 'default') {
    global $all_pets, $language, $dbcon, $wowhdomain;
    
    $link_class = "weblink";
    if ($area == "strategy") $link_class = "comlink";
    
    // Replace Pets
    if (strpos($article, '[pet=') !== false) {
        $cutarticle = explode("[pet=", $article);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snips2 = array();
                $snips = explode("]", $value, 2);
                if (strpos($snips[0], ':') !== false) {
                    $snips2 = explode(":", $snips[0]);
                    $snips[0] = $snips2[0];
                }
                $search_pet = searchForId($snips[0], $all_pets, array());
                if (!$search_pet && $snips2[1]) {
                    $beginning = $beginning.'<font style="font-size: '.$link_size.'px"><b><i>'.$snips2[1].'</i></b></font>'.$snips[1];
                }
                else if (!$search_pet && !$snips2[1]) {
                    $beginning = $beginning.'<font style="font-size: '.$link_size.'px"><b><i>Unknown Pet ('.$snips[0].')</i></b></font>'.$snips[1];
                }
                else {
                    $beginning = $beginning.'<a class="'.$link_class.'" style="font-size: '.$link_size.'px" href="http://'.$wowhdomain.'.wowhead.com/npc='.$snips[0].'" target="_blank">'.$all_pets[$search_pet[0]]['Name'].'</a>'.$snips[1];
                }
            }
        }
        $article = $beginning;
    }
    
    // Replace Spells:
    if (strpos($article, '[skill=') !== false) {
        $cutarticle = explode("[skill=", $article);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snips2 = array();
                $snips = explode("]", $value, 2);
                if (strpos($snips[0], ':') !== false) {
                    $snips2 = explode(":", $snips[0]);
                    $snips[0] = $snips2[0];
                }
                $ability_db = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$snips[0]'") or die(mysqli_error($dbcon));
                if (mysqli_num_rows($ability_db) == 0  && $snips2[1]) {
                    $beginning = $beginning.'<font style="font-size: '.$link_size.'px"><b><i>'.$snips2[1].'</i></b></font>'.$snips[1];
                }
                else if (mysqli_num_rows($ability_db) == 0 && !$snips2[1]) {
                    $beginning = $beginning.'<font style="font-size: '.$link_size.'px"><b><i>Unknown Ability ('.$snips[0].')</i></b></font>'.$snips[1];
                }
                else {
                    $ability = mysqli_fetch_object($ability_db);
                    $beginning = $beginning.'<a class="'.$link_class.'" style="font-size: '.$link_size.'px" href="http://'.$wowhdomain.'.wowhead.com/pet-ability='.$ability->SpellID.'" target="_blank">'.$ability->{$language}.'</a>'.$snips[1];
                }
            }
        }
        $article = $beginning;
    }
    return $article;
}


function bbparse_users($article, $link_size) { 
    if (strpos($article, '[user=') !== false) {
      global $user, $dbcon;
        $cutarticle = explode("[user=", $article);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snips2 = array();
                $snips = explode("]", $value, 2);
                if (strpos($snips[0], ':') !== false) {
                    $snips2 = explode(":", $snips[0]);
                    $snips[0] = $snips2[0];
                }
                $user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$snips[0]'") or die(mysqli_error($dbcon));
                if (mysqli_num_rows($user_db) == 0  && $snips2[1]) {
                    $beginning = $beginning.'<font style="font-size: '.$link_size.'px"><b><i>'.$snips2[1].'</i></b></font>'.$snips[1];
                }
                else if (mysqli_num_rows($user_db) == 0 && !$snips2[1]) {
                    $beginning = $beginning.'<font style="font-size: '.$link_size.'px"><b><i>Unknown User ('.$snips[0].')</i></b></font>'.$snips[1];
                }
                else {
                    $thisuser = mysqli_fetch_object($user_db);
                    $beginning = $beginning.'<span style="text-decoration: none" class="username" rel="'.$snips[0].'" value="'.$user->id.'"><a class="creatorlink" style="font-size: '.$link_size.'px" href="?user='.$thisuser->id.'" target="_blank">'.$thisuser->Name.'</a></span>'.$snips[1];
                }
            }
        }
        $article = $beginning;
    }
    return $article;
}


function bbparse_images($article, $area, $tooltips = 0) {
    global $news_article, $dbcon; // TODO - image parsing with tooltips currently always links to news articles (ugh) - not used anywhere else so it's fine-ish but for future if needed elsewhere, recode
    
    // Tooltips 0 => all images shown with max-width 810px
    // Tooltips 1 => images are thumbnails and get a tooltip when larger than 500px width
    // Tooltips 2 => images are thumbnails and get a tooltip when larger than 180px width
  
    // Replace Images:
    if (strpos($article, '[img=') !== false) {
        $cutarticle = explode("[img=", $article);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snippets1 = explode("]", $value, 2);
                $imgclass = $area."_floatright";
                if (strpos($snippets1[0], '-') !== false) {
                    $snips = explode("-", $snippets1[0]);
                    $imageid = $snips[0];
                    switch ($snips[1]) {
                        case "left":
                            $imgclass = $area."_left";
                            break;
                        case "right":
                            $imgclass = $area."_right";
                            break;
                        case "center":
                            $imgclass = $area."_center";
                            break;
                        case "floatleft":
                            $imgclass = $area."_floatleft";
                            break;
                        case "floatright":
                            $imgclass = $area."_floatright";
                            break;
                    }
                }
                else {
                    $imageid = $snippets1[0];
                }
                $allpetsdb = mysqli_query($dbcon, "SELECT * FROM Images WHERE id = '$imageid'") or die(mysqli_error($dbcon));
                $thisimage = mysqli_fetch_object($allpetsdb);
                if ($tooltips > 0) {
                  if ($tooltips == 2) {
                    if ($thisimage->Height > 200 OR $thisimage->Width > 180) {
                      $beginning = $beginning.'<div class="'.$imgclass.'">';
                      $beginning = $beginning.'<a href="?News='.$news_article->id.'">';
                      $beginning = $beginning.'<img class="news_image_small img_prev_tt '.$imgclass.'" data-tooltip-content="#image_tt_'.$imageid.'_'.$key.'" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'"></div>';
                      $beginning = $beginning.'</a>';
                      $beginning = $beginning.'<div style="display: none"><span id="image_tt_'.$imageid.'_'.$key.'">';
                      $beginning = $beginning.'<img style="border-radius: 0px; max-width: 900px" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'">';
                      $beginning = $beginning.'</span></div>'.$snippets1[1];
                      $add_prev_tt = true;
                    }
                    else {
                      $beginning = $beginning.'<div class="'.$imgclass.'"><img class="news_image '.$imgclass.'" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'"></div>'.$snippets1[1];
                    }
                  }
                  if ($tooltips == 1) {
                    if ($thisimage->Height > 200 OR $thisimage->Width > 500) {
                      $beginning = $beginning.'<div class="'.$imgclass.'">';
                      $beginning = $beginning.'<a href="?News='.$news_article->id.'">';
                      $beginning = $beginning.'<img style="max-width: 500px" class="news_image img_prev_tt '.$imgclass.'" data-tooltip-content="#image_tt_'.$imageid.'_'.$key.'" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'"></div>';
                      $beginning = $beginning.'</a>';
                      $beginning = $beginning.'<div style="display: none"><span id="image_tt_'.$imageid.'_'.$key.'">';
                      $beginning = $beginning.'<img style="border-radius: 0px; max-width: 900px" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'">';
                      $beginning = $beginning.'</span></div>'.$snippets1[1];
                      $add_prev_tt = true;
                    }
                    else {
                      $beginning = $beginning.'<div class="'.$imgclass.'"><img class="news_image '.$imgclass.'" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'"></div>'.$snippets1[1];
                    }
                  }
                }
                else {
                  $beginning = $beginning.'<div class="'.$imgclass.'"><img style="border-radius: 2px; max-width: 810px;" src="https://www.wow-petguide.com/images/articles/'.$thisimage->Filename.'"></div>'.$snippets1[1];
                }
            }
        }
        $article = $beginning;
    }
    return $article;
}

function bbparse_simple($article) { 
    $article = str_replace("[u]", "<u>", $article);
    $article = str_replace("[/u]", "</u>", $article);
    $article = str_replace("[i]", "<i>", $article);
    $article = str_replace("[/i]", "</i>", $article);
    $article = str_replace("[b]", "<b>", $article);
    $article = str_replace("[/b]", "</b>", $article);
    $article = str_replace("[s]", "<del>", $article);
    $article = str_replace("[/s]", "</del>", $article);
    return $article;
}

function bbparse_advanced($article) { 
    $article = str_replace("[qt]", "<blockquote class='standard'>", $article);
    $article = str_replace("[/qt]", "</blockquote>", $article);
    $article = str_replace("[bluepost]", "<blockquote class='bluepost'>", $article);
    $article = str_replace("[/bluepost]", "</blockquote>", $article);
    $article = str_replace("[/bl]", "</li></ul>", $article);
    $article = str_replace("[center]", "<center>", $article);
    $article = str_replace("[/center]", "</center>", $article);
    $article = str_replace("[right]", "<div style='text-align: right;'><p class='blogodd'>", $article);
    $article = str_replace("[/right]", "</p></div>", $article);
    $article = str_replace("[small]", "<p class='blogodd' style='font-size: 14'>", $article);
    $article = str_replace("[/small]", "</p>", $article);
    $article = str_replace("[large]", "<p class='blogodd' style='font-size: 18'>", $article);
    $article = str_replace("[/large]", "</p>", $article);
    $article = str_replace("[code]", "<blockquote class='code'>", $article);
    $article = str_replace("[/code]", "</blockquote>", $article);
    $article = str_replace("[hr]", "<hr class='home'>", $article);
    return $article;
}

function bbparse_cleanup($article) { 
    $article = str_replace(PHP_EOL, "<br>", $article);
    $article = str_replace('WLPz37f2','http',$article);
    $article = str_replace('MjwMhR9z','www',$article);
    
    // Remove line break before bullet points:
    if (strpos($article, '[bl]') !== false) {
        $cutarticle = explode("[bl]", $article);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                if (substr($value, -4) == "<br>") {
                    $beginning = $beginning."<ul style='margin: 0px;'><li>".substr($value, 0, -4);
                }
                else {
                  $beginning = $beginning."<ul style='margin: 0px;'><li>".$value;
                }
            }
        }
        $article = $beginning;
    }
    
    // Remove line break BEFORE certain strings:
    foreach (['<tr>', '<td>', '</tr>', '</td>', '</table>', '<blockquote class=\'code\'>', '<blockquote class=\'standard\'>', '<blockquote class=\'bluepost\'>'] as $cleanup_string) {
        if (strpos($article, $cleanup_string) !== false) {
            $article_pieces = explode($cleanup_string, $article);

            foreach ($article_pieces as $key => $value) {
                    if ($key == 0) {
                        if (substr($value, -4) == '<br>') {
                            $article = substr($value, 0, -4).$cleanup_string;
                        }
                        else {
                            $article = $value.$cleanup_string;
                        }
                    }
                    if ($key != array_key_last($article_pieces) && $key != 0){
                        if (substr($value, -4) == '<br>') {
                            $article = $article.substr($value, 0, -4).$cleanup_string;
                        }
                        else {
                            $article = $article.$value.$cleanup_string;
                        }
                    }
                    if ($key === array_key_last($article_pieces)){
                        $article = $article.$value;
                    }
            }
        }
    }
    
    // Remove line breaks AFTER certain strings:
    foreach (['<td>', '</td>', '</table>', '</blockquote>'] as $cleanup_string) {
        if (strpos($article, $cleanup_string) !== false) {
            $article_pieces = explode($cleanup_string, $article);
    
            foreach ($article_pieces as $key => $value) {
                if ($key == 0) {
                    $article = $value;
                }
                if ($key > 0) {
                    if (substr($value, 0, 4) == '<br>') {
                        $article = $article.$cleanup_string.substr($value, 4);
                    }
                    else if (substr($value, 1, 4) == '<br>') {
                        $article = $article.$cleanup_string.substr($value, 5);
                    }
                    else {
                        $article = $article.$cleanup_string.$value;
                    }
                }
            }
        }
    }

    // Check for unclosed tables and close them
    while (substr_count($article, '<table') > substr_count($article, '</table>')) {
        $article = $article.'</table>';
    }

    $article = $article.'<script>$(document).ready(function() {$(\'.img_prev_tt\').tooltipster({ maxWidth: \'800\', theme: \'tooltipster\'}); });</script>';
    return $article;
}



// ======================================= FUNCTIONS FOR ADDING MENU OPTIONS TO MODAL WINDOWS ===========================================================================

function bboptions_tables($area) { ?>
    <td><button type="button" class="bbbutton" onclick="bb_articles('table', '','<? echo $area ?>')"><img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_bb_table.png" alt="" /></button></td>
<? }

function bboptions_spacer() { ?>
    <td style="width: 10px"></td>
<? }

function bboptions_simple($area) { ?>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','b','<? echo $area ?>')"><b>[b]</b></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','i','<? echo $area ?>')"><i>[i]</i></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','u','<? echo $area ?>')"><u>[u]</u></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','s','<? echo $area ?>')"><del>[s]</del></button></td> 
<? }

function bboptions_advanced($area) { ?>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','large','<? echo $area ?>')"><img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_bb_fontlarge.png" alt="" /></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','small','<? echo $area ?>')"><img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_bb_fontsmall.png" alt="" /></button></td>

    <td style="padding-left: 10px"><button type="button" data-tooltip-content="#bb_mjh_tt" class="bbbutton bb_mjh_tt" onclick="bb_articles('simple','h1','<? echo $area ?>')">h1</button>
        <div style="display: none"><span id="bb_mjh_tt">Formats the selection as a Major headline</span></div></td>
    <td><button type="button" data-tooltip-content="#bb_mnh_tt" class="bbbutton bb_mnh_tt" onclick="bb_articles('simple','h2','<? echo $area ?>')">h2</button>
        <div style="display: none"><span id="bb_mnh_tt">Formats the selection as a Minor headline</span></div></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','h3','<? echo $area ?>')">h3</button></td>
    <td>
        <script>
            $(document).ready(function() {
                $('.bb_mnh_tt').tooltipster({
                    theme: 'tooltipster-smallnote'
                });
            });
            $(document).ready(function() {
                $('.bb_mjh_tt').tooltipster({
                    theme: 'tooltipster-smallnote'
                });
            });
        </script>
    </td>

    <td style="padding-left: 10px"><button type="button" class="bbbutton" onclick="bb_articles('simple','bl','<? echo $area ?>')"><img style="padding: 2px" src="https://www.wow-petguide.com/images/icon_bb_bullet.png" alt="" /></button></td>

    <td style="padding-left: 10px"><button type="button" class="bbbutton" onclick="bb_articles('simple','center','<? echo $area ?>')"><img style="padding: 2px" src="https://www.wow-petguide.com/images/icon_bb_center.png" alt="" /></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','right','<? echo $area ?>')"><img style="padding: 2px" src="https://www.wow-petguide.com/images/icon_bb_right.png" alt="" /></button></td>

    <td style="padding-left: 10px"><button type="button" class="bbbutton" onclick="bb_articles('simple','qt','<? echo $area ?>')"><img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_bb_quote.png" alt="" /></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','bluepost','<? echo $area ?>')" style="background-color: #92d2fa"><img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_bb_quote.png" alt="" /></button></td>
    <td><button type="button" class="bbbutton" onclick="bb_articles('simple','code','<? echo $area ?>')">code</button></td>

    <td style="padding-left: 10px"><button type="button" class="bbbutton" onclick="bb_articles('single','hr','<? echo $area ?>')"><img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_bb_hr.png" alt="" /></button></td>
<? }

function bboptions_url($area) { ?>
    <td>
        <span class="add_url_tt" data-tooltip-content="#bb_add_url" style="cursor: help;">
            <button type="button" class="bbbutton">URL</button>
        </span>

        <div style="display: none">
            <span id="bb_add_url">
                <table>
                    <tr>
                        <td style="text-align: right; padding-right: 5px"><p class="blogeven">Shown name:</p></td>
                        <td><input class="petselect" style="width: 280px" type="text" id="bb_url_name"></td>
                        <td rowspan="2" style="text-align: right; padding-left: 5px"><button onclick="bb_articles('url', '','<? echo $area ?>');" class="bnetlogin">Add</button></td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding-right: 5px"><p class="blogeven">URL:</p></td>
                        <td><input class="petselect" style="width: 280px" type="text" id="bb_url" value=""></td>
                    </tr>
                </table>
            </span>
        </div>

        <script>
            $(document).ready(function() {
                $('.add_url_tt').tooltipster({
                    interactive: 'true',
                    animation: 'fade',
                    side: 'bottom',
                    theme: 'tooltipster-smallnote'
                });
            });
        </script>
    </td>
<? }

function bboptions_pet($area) {
    global $dbcon; ?>
    <td>
        <span class="add_pet_tt" data-tooltip-content="#bb_add_pet" style="cursor: help;">
            <button type="button" class="bbbutton">Pet</button>
        </span>

        <div style="display: none;">
            <span id="bb_add_pet" style="height: 600px;">
                <table>
                    <tr>
                        <td>
                            <select width="230" data-placeholder="" name="bb_pet" id="bb_pet_dd" class="chosen-select_pet">
                                <option value=""></option>
                                    <?
                                    $allpetsdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID  > 20 ORDER BY Name") or die(mysqli_error($dbcon));
                                    while ($thispet = mysqli_fetch_object($allpetsdb)) {
                                        echo '<option value="'.$thispet->PetID.':'.$thispet->Name.'">'.$thispet->Name.'</option>';
                                    }
                                    ?>
                            </select>
                        </td>
                        <td style="text-align: right; padding-left: 5px"><button onclick="bb_articles('pet', '','<? echo $area ?>');" class="bnetlogin">Add</button>
                        </td>
                    </tr>
                </table>
                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            </span>
        </div>

        <script>
            $(document).ready(function() {
                $('.add_pet_tt').tooltipster({
                    interactive: 'true',
                    animation: 'fade',
                    side: 'bottom',
                    height: '600',
                    theme: 'tooltipster-smallnote'
                });
            });
            $(".chosen-select_pet").chosen({width: 300});
        </script>
    </td>
<? }

function bboptions_ability($area) {
    global $dbcon; ?>
    <td>
        <span class="add_skill_tt" data-tooltip-content="#bb_add_skill" style="cursor: help;">
            <button type="button" class="bbbutton">Skill</button>
        </span>

        <div style="display: none;">
            <span id="bb_add_skill" style="height: 600px;">
                <table>
                    <tr>
                        <td>
                            <select width="230" data-placeholder="" name="bb_skill" id="bb_skill_dd" class="chosen-select_skill">
                                <option value=""></option>
                                    <?
                                    $allskillsdb = mysqli_query($dbcon, "SELECT * FROM Spells Where PetSpell = '1' ORDER BY en_US") or die(mysqli_error($dbcon));
                                    while ($thisskill = mysqli_fetch_object($allskillsdb)) {
                                        echo '<option value="'.$thisskill->SpellID.':'.$thisskill->en_US.'">'.$thisskill->en_US.'</option>';
                                    }
                                    ?>
                            </select>
                        </td>
                        <td style="text-align: right; padding-left: 5px"><button onclick="bb_articles('skill', '','<? echo $area ?>');" class="bnetlogin">Add</button>
                        </td>
                    </tr>
                </table>
                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            </span>
        </div>

        <script>
            $(document).ready(function() {
                $('.add_skill_tt').tooltipster({
                    interactive: 'true',
                    animation: 'fade',
                    side: 'bottom',
                    height: '500',
                    width: '650',
                    theme: 'tooltipster-smallnote'
                });
            });
            $(".chosen-select_skill").chosen({width: 300});
        </script>
    </td>
<? }

function bboptions_image($area) {
    global $dbcon, $user ?>
    <td>
        <span class="add_img_tt" data-tooltip-content="#bb_add_img" style="cursor: help;">
            <button type="button" class="bbbutton">Image</button>
        </span>

        <div style="display: none;">
            <span id="bb_add_img">
                <table style="width: 575px">

                    <tr>
                        <td>
                            <select data-placeholder="" name="image_cat" id="sel_cat" class="chosen-select_image" required>
                                <option value=""></option>
                                    <?
                                    $allcatsdb = mysqli_query($dbcon, "SELECT * FROM ImageCats ORDER BY Name") or die(mysqli_error($dbcon));
                                    $catcounter = "0";
                                    while ($thiscat = mysqli_fetch_object($allcatsdb)) {
                                        echo '<option value="'.$thiscat->id.'"';
                                        if ($showcat == $thiscat->id OR ($showcat == "" && $catcounter == "0")) {
                                            echo " selected";
                                        }
                                        echo '>'.$thiscat->Name.'</option>';
                                        $catcounter++;
                                    }
                                    ?>
                            </select>
                        </td>

                        <td>
                            <a href="https://www.wow-petguide.com/?page=adm_images" target="_blank"><button type="submit" class="comedit">Manage / Upload Images</button></a>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <table>
                               <tr>
                                   <td>
                                       <p class="blogeven" style="font-size: 14px">Left:</p>
                                   </td>
                                   <td style="padding-right: 10px">
                                       <ul class="radiossmall">
                                           <li>
                                               <input class="lightblue" type="radio" id="nofloatleft" value="1" name="imgfloat">
                                               <label for="nofloatleft"></label>
                                               <div class="check"></div>
                                           </li>
                                       </ul>
                                   </td>

                                   <td>
                                       <p class="blogeven" style="font-size: 14px">Right:</p>
                                   </td>
                                   <td style="padding-right: 10px">
                                       <ul class="radiossmall">
                                           <li>
                                               <input class="lightblue" type="radio" id="nofloatright" value="2" name="imgfloat">
                                               <label for="nofloatright"></label>
                                               <div class="check"></div>
                                           </li>
                                       </ul>
                                   </td>

                                   <td>
                                       <p class="blogeven" style="font-size: 14px">Center:</p>
                                   </td>

                                   <td>
                                       <ul class="radiossmall">
                                           <li>
                                               <input class="lightblue" type="radio" id="floatcenter" value="3" name="imgfloat">
                                               <label for="floatcenter"></label>
                                               <div class="check"></div>
                                           </li>
                                       </ul>
                                   </td>

                                   <td>
                                       <p class="blogeven" style="font-size: 14px">Float-left:</p>
                                   </td>
                                   <td>
                                       <ul class="radiossmall">
                                           <li>
                                               <input class="lightblue" type="radio" id="floatleft" value="4" name="imgfloat">
                                               <label for="floatleft"></label>
                                               <div class="check"></div>
                                           </li>
                                       </ul>
                                   </td>

                                   <td>
                                       <p class="blogeven" style="font-size: 14px">Float-right:</p>
                                   </td>

                                   <td>
                                       <ul class="radiossmall">
                                           <li>
                                               <input class="lightblue" type="radio" id="floatright" value="5" name="imgfloat" checked>
                                               <label for="floatright"></label>
                                               <div class="check"></div>
                                           </li>
                                       </ul>
                                   </td>

                               </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="height: 400px; width: 100%" colspan="2">
                            <div style="height: 400px; width: 100%; overflow-x: hidden; overflow-y: auto;" id="gallerycontainer">
                            </div>
                        </td>

                    </tr>

                 <script>
                    $(".chosen-select_image").chosen({width: 250, placeholder_text_single: 'Select a Category'});

                    function adm_pullgallery(i) {
                        $('#gallerycontainer').empty();
                        $('#gallerycontainer').load('classes/ajax/adm_pullimages.php?g='+i+'&u=<? echo $user->id ?>&del=<? echo $user->ComSecret ?>&p=e');
                    }

                    $(".chosen-select_image").chosen().change(function(event){
                        var i = $('select[name=image_cat]').val();
                        adm_pullgallery(i);
                    });

                    var u = $('select[name=image_cat]').val();
                    if (u != "") {
                        adm_pullgallery(u);
                    }
                    $( document ).on( "click", ".galimg", function() {
                        bb_articles('img', this.dataset.imgid,'<? echo $area ?>');
                    });
                </script>
                </table>
            </span>
        </div>

        <script>
            $(document).ready(function() {
                $('.add_img_tt').tooltipster({
                    interactive: 'true',
                    animation: 'fade',
                    side: 'bottom',
                    height: '600',
                    theme: 'tooltipster-smallnote'
                });
            });
            $(".chosen-select_image").chosen({width: 400});
        </script>
    </td>
<? }

function bboptions_user($area) {
    global $user, $dbcon ?>
    <td>
        <span class="add_user_tt" data-tooltip-content="#bb_add_user" style="cursor: help;">
            <button type="button" class="bbbutton">Username</button>
        </span>

        <div style="display: none;">
            <span id="bb_add_user" style="height: 600px;">
                <table>
                    <tr>
                        <td>
                            <select data-placeholder="Enter Username" id="username" name="recipient" class="chosen-select_username">
                                <option value="0"></option>
                             </select>

                            <script type = "text/javascript">
                                $("#username").chosen({width: 325});
                            </script>
                        </td>
                        <td style="text-align: right; padding-left: 5px"><button onclick="bb_articles('username', '','<? echo $area ?>');" class="bnetlogin">Add</button>
                        </td>
                    </tr>
                </table>
                <br><br>If the username search is not working, briefly open the<br>
                Pet, Skill and Image tooltips and try again. <br>
                This is a nasty bug I can't seem to fix :/ <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            </span>
        </div>

        <script>
            $(document).ready(function() {
                $('.add_user_tt').tooltipster({
                    interactive: 'true',
                    animation: 'fade',
                    side: 'bottom',
                    height: '300',
                    width: '650',
                    theme: 'tooltipster-smallnote'
                });
            });


            var x_timer;
            $('.chosen-search-input').on('input',function(e){
                var searchterm = $('.chosen-search-input').val();
                clearTimeout(x_timer);
                x_timer = setTimeout(function(){
                    $(".no-results").text("<? echo _("PM_searching") ?>");
                    if (searchterm.length >= '2') {
                        clearTimeout(x_timer);
                        x_timer = setTimeout(function(){
                            $("#username").empty();
                            var xmlhttp = new XMLHttpRequest();
                            xmlhttp.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                    if (this.responseText == "[]") {
                                        $(".no-results").text("<? echo _("PM_ErrNoUser") ?>");
                                    }
                                    else {
                                        var data = this.responseText;
                                        data = JSON.parse(data);

                                        $.each(data, function (idx, obj) {
                                            $("#username").append('<option value="' + obj.id + '">' + obj.text + '</option>');
                                        });
                                        $("#username").trigger("chosen:updated");

                                        $("#username").chosen({width: 325});
                                        $('.chosen-search-input').val(searchterm);
                                    }
                                }
                            };
                            xmlhttp.open("GET", "classes/ajax/ac_writemessage.php?q=" + encodeURIComponent(searchterm) + "&e=f&u=<? echo $user->id ?>", true);
                            xmlhttp.send();
                        }, 1000);
                    }
                    else {
                        clearTimeout(x_timer);
                        x_timer = setTimeout(function(){
                            $("#username").empty();
                            $(".no-results").text("<? echo _("PM_ErrTooShort") ?>");
                        }, 300);
                    }
                }, 200);
            });
        </script>
    </td>
<? }