<?php // Articles 2.0

if (!$mainid) {
    $mainid = $mainentry->id;
    $maintype = $mainentry->Type;
}


// An article was selected to review a previous version:
$revision = $_GET['rev'];
if ($revision) {
    $reverror = "0";
    $revarticledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE id = '$revision' AND Preview != '1'") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($revarticledb) > "0") {
        $revarticle = mysqli_fetch_object($revarticledb);
        if ($revarticle->Article != $mainid) {
            $reverror = "1";
        }
    }
    else {
        $reverror = "1";
    }
}

// Valid revision was selected, grab article:
if ($reverror == "0") {
    $article = $revarticle;
    $pagemode = "review";
    // Grab the actual current article for settings:
    $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainid' ORDER BY LastUpdate DESC") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($articledb) < "1") {
        // TODO - Referenced article not found. load dummy article that says that here is nothing there, it's an error
    }
    else {
        $curarticle = mysqli_fetch_object($articledb);
    }
}
else {    // No revision, get article from DB
    $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainid' AND Preview != '1' ORDER BY LastUpdate DESC") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($articledb) < "1") {
        // TODO - Referenced article not found. load dummy article that says that here is nothing there, it's an error
    }
    else {
        $article = mysqli_fetch_object($articledb);
    }
}



// Process saving of article from editor mode
$action = $_POST['action'];
$preview = $_POST['showprev']; // can be "Preview" or empty

if ($action == "save_article" && ($userrights[$article->UserRight] == "yes" OR $userrights['LocArticles'] == "yes") && mysqli_num_rows($articledb) > "0") {
    $update_reason = $_POST['update_reason'];
    if ($userrights[$article->UserRight] != "yes") {
        $update_reason == "";
    }
    if ($update_reason != "" OR $article->UpdateCounter >= "5" OR $preview == "Preview") {
        $articlecontent_refresh = mysqli_real_escape_string($dbcon, $article->Content_en_US);
        $articletitle_refresh = mysqli_real_escape_string($dbcon, $article->Title_en_US);
        mysqli_query($dbcon, "INSERT INTO Articles (`Article`, `Title_en_US`, `Content_en_US`, `UserRight`, `UpdateReason`, `Editors`, `PageWidth`) VALUES ('$article->Article', '$articletitle_refresh', '$articlecontent_refresh', '$article->UserRight', '$update_reason', '$article->Editors', '$article->PageWidth')") OR die(mysqli_error($dbcon));
        $newarticle = mysqli_insert_id($dbcon);
        $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE id = '$newarticle'") or die(mysqli_error($dbcon));
        $article = mysqli_fetch_object($articledb);
        if ($preview == "Preview") {
            mysqli_query($dbcon, "UPDATE Articles SET `Preview` = '1' WHERE id = '$article->id'");
        }
    }

    if ($userrights[$article->UserRight] == "yes") {  // Save Settings and EN, but only if the user has full editing rights
        $article_width = $_POST['article_width'];
        if (preg_match("/^[1234567890]*$/is", $article_width)) {
            if (($article_width <= "4000" && $article_width >= "500") or $article_width == "0") {
                mysqli_query($dbcon, "UPDATE Articles SET `PageWidth` = '$article_width' WHERE id = '$article->id'");
            }
            else {
                echo '<script>$.growl.error({ message: "Page Width not saved. Needs to be between 500 and 4000 pixels.<br>Enter 0 for full screen.", duration: "15000", size: "large", location: "tc" });</script>';
            }
        }
        $tocloc = $_POST['tocloc'];
        if ($tocloc == "1" OR $tocloc == "2" OR $tocloc == "0") {
            mysqli_query($dbcon, "UPDATE Articles SET `TOC` = '$tocloc' WHERE id = '$article->id'");
        }

        $alloca = $_POST['loca'];
        if ($alloca == "1" OR $alloca == "0") {
            mysqli_query($dbcon, "UPDATE Articles SET `Loca` = '$alloca' WHERE id = '$article->id'");
        }
        $articlecontent_en_US = mysqli_real_escape_string($dbcon, $_POST['article_content_en_US']);
        $articletitle_en_US = mysqli_real_escape_string($dbcon, $_POST['article_title_en_US']);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_en_US` = '$articlecontent_en_US', `Title_en_US` = '$articletitle_en_US' WHERE id = '$article->id'");
    }

    // Save translations for everyone who has edit or loca rights

    $articlecontent_de_DE = mysqli_real_escape_string($dbcon, $_POST['article_content_de_DE']);
    $articletitle_de_DE = mysqli_real_escape_string($dbcon, $_POST['article_title_de_DE']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_de_DE` = '$articlecontent_de_DE', `Title_de_DE` = '$articletitle_de_DE' WHERE id = '$article->id'");
    $articlecontent_fr_FR = mysqli_real_escape_string($dbcon, $_POST['article_content_fr_FR']);
    $articletitle_fr_FR = mysqli_real_escape_string($dbcon, $_POST['article_title_fr_FR']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_fr_FR` = '$articlecontent_fr_FR', `Title_fr_FR` = '$articletitle_fr_FR' WHERE id = '$article->id'");
    $articlecontent_it_IT = mysqli_real_escape_string($dbcon, $_POST['article_content_it_IT']);
    $articletitle_it_IT = mysqli_real_escape_string($dbcon, $_POST['article_title_it_IT']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_it_IT` = '$articlecontent_it_IT', `Title_it_IT` = '$articletitle_it_IT' WHERE id = '$article->id'");
    $articlecontent_es_ES = mysqli_real_escape_string($dbcon, $_POST['article_content_es_ES']);
    $articletitle_es_ES = mysqli_real_escape_string($dbcon, $_POST['article_title_es_ES']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_es_ES` = '$articlecontent_es_ES', `Title_es_ES` = '$articletitle_es_ES' WHERE id = '$article->id'");
    $articlecontent_pl_PL = mysqli_real_escape_string($dbcon, $_POST['article_content_pl_PL']);
    $articletitle_pl_PL = mysqli_real_escape_string($dbcon, $_POST['article_title_pl_PL']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_pl_PL` = '$articlecontent_pl_PL', `Title_pl_PL` = '$articletitle_pl_PL' WHERE id = '$article->id'");
    $articlecontent_pt_BR = mysqli_real_escape_string($dbcon, $_POST['article_content_pt_BR']);
    $articletitle_pt_BR = mysqli_real_escape_string($dbcon, $_POST['article_title_pt_BR']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_pt_BR` = '$articlecontent_pt_BR', `Title_pt_BR` = '$articletitle_pt_BR' WHERE id = '$article->id'");
    $articlecontent_ru_RU = mysqli_real_escape_string($dbcon, $_POST['article_content_ru_RU']);
    $articletitle_ru_RU = mysqli_real_escape_string($dbcon, $_POST['article_title_ru_RU']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_ru_RU` = '$articlecontent_ru_RU', `Title_ru_RU` = '$articletitle_ru_RU' WHERE id = '$article->id'");
    $articlecontent_ko_KR = mysqli_real_escape_string($dbcon, $_POST['article_content_ko_KR']);
    $articletitle_ko_KR = mysqli_real_escape_string($dbcon, $_POST['article_title_ko_KR']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_ko_KR` = '$articlecontent_ko_KR', `Title_ko_KR` = '$articletitle_ko_KR' WHERE id = '$article->id'");
    $articlecontent_zh_TW = mysqli_real_escape_string($dbcon, $_POST['article_content_zh_TW']);
    $articletitle_zh_TW = mysqli_real_escape_string($dbcon, $_POST['article_title_zh_TW']);
        mysqli_query($dbcon, "UPDATE Articles SET `Content_zh_TW` = '$articlecontent_zh_TW', `Title_zh_TW` = '$articletitle_zh_TW' WHERE id = '$article->id'");

    // Update/add users who edited the article:
    if ($userrights[$article->UserRight] == "yes") {  // Save Settings and EN only if the user has full editing rights
        if ($article->Editors == "") {
            mysqli_query($dbcon, "UPDATE Articles SET `Editors` = '$user->id' WHERE id = '$article->id'");
        }
        else {
            $userpieces = explode(",", $article->Editors);
            foreach ($userpieces as $key => $value) {
                if ($value == $user->id) {
                    $founduser = "true";
                }
            }
            if ($founduser != "true") {
                $newentryusers = $article->Editors.",".$user->id;
                mysqli_query($dbcon, "UPDATE Articles SET `Editors` = '$newentryusers' WHERE id = '$article->id'");
            }
        }
    }

    // Update/add users who localize the article:
    if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes") {
        if ($article->Translators == "") {
            mysqli_query($dbcon, "UPDATE Articles SET `Translators` = '$user->id' WHERE id = '$article->id'");
        }
        else {
            $userpieces = explode(",", $article->Translators);
            foreach ($userpieces as $key => $value) {
                if ($value == $user->id) {
                    $founduser = "true";
                }
            }
            if ($founduser != "true") {
                $newentryusers = $article->Translators.",".$user->id;
                mysqli_query($dbcon, "UPDATE Articles SET `Translators` = '$newentryusers' WHERE id = '$article->id'");
            }
        }
    }

    // Increase Update Count to +1
    $newupcount = $article->UpdateCounter + 1;
    mysqli_query($dbcon, "UPDATE Articles SET `UpdateCounter` = '$newupcount' WHERE id = '$article->id'");

    // Update Timestamp
    mysqli_query($dbcon, "UPDATE Articles SET `LastUpdate` = current_timestamp WHERE id = '$article->id'");

    // Update article info for output:
    $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE id = '$article->id'") or die(mysqli_error($dbcon));
    $article = mysqli_fetch_object($articledb);

    if ($preview != "Preview") {
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Article edited', '$article->id')") OR die(mysqli_error($dbcon));
        echo '<script>$.growl.notice({ message: "Article saved", duration: "5000", size: "large", location: "tc"  });</script>';
        mysqli_query($dbcon, "DELETE FROM Articles WHERE Preview = '1' AND LastUpdate < DATE_SUB(NOW(), INTERVAL 5 HOUR)") OR die(mysqli_error($dbcon));
    }
}



// Save article from Preview Mode
if ($action == "save_preview") {
    $prevarticle = $_POST['prevarticle'];

    $prevarticledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE id = '$prevarticle'") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($prevarticledb) > "0") {
        $prevarticle = mysqli_fetch_object($prevarticledb);
    }
    else {
        $savepreverr = "1";
    }

    if ($userrights[$article->UserRight] != "yes" && $userrights['LocArticles'] != "yes") {
       $savepreverr = "1";
    }

    if ($savepreverr != "1") {
        // Case 1 - new article entry should be saved
        if ($prevarticle->UpdateReason != "" OR $article->UpdateCounter >= "5") {
            mysqli_query($dbcon, "UPDATE Articles SET `Preview` = '0' WHERE id = '$prevarticle->id'");
            $saveprevsuc = "1";
        }
        else {  // Case 2 - update previous article and delete preview
            // Update Settings
            mysqli_query($dbcon, "UPDATE Articles SET `Editors` = '$prevarticle->Editors' WHERE id = '$article->id'");
            mysqli_query($dbcon, "UPDATE Articles SET `Translators` = '$prevarticle->Translators' WHERE id = '$article->id'");
            mysqli_query($dbcon, "UPDATE Articles SET `TOC` = '$prevarticle->TOC' WHERE id = '$article->id'");
            mysqli_query($dbcon, "UPDATE Articles SET `Loca` = '$prevarticle->Loca' WHERE id = '$article->id'");
            mysqli_query($dbcon, "UPDATE Articles SET `PageWidth` = '$prevarticle->PageWidth' WHERE id = '$article->id'");
            // Update Titles and Content
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_en_US);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_en_US);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_en_US` = '$inputcontent', `Title_en_US` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_de_DE);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_de_DE);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_de_DE` = '$inputcontent', `Title_de_DE` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_fr_FR);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_fr_FR);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_fr_FR` = '$inputcontent', `Title_fr_FR` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_it_IT);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_it_IT);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_it_IT` = '$inputcontent', `Title_it_IT` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_es_ES);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_es_ES);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_es_ES` = '$inputcontent', `Title_es_ES` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_pl_PL);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_pl_PL);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_pl_PL` = '$inputcontent', `Title_pl_PL` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_pt_BR);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_pt_BR);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_pt_BR` = '$inputcontent', `Title_pt_BR` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_ru_RU);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_ru_RU);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_ru_RU` = '$inputcontent', `Title_ru_RU` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_ko_KR);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_ko_KR);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_ko_KR` = '$inputcontent', `Title_ko_KR` = '$inputtitle' WHERE id = '$article->id'");
            $inputcontent = mysqli_real_escape_string($dbcon, $prevarticle->Content_zh_TW);
            $inputtitle = mysqli_real_escape_string($dbcon, $prevarticle->Title_zh_TW);
            mysqli_query($dbcon, "UPDATE Articles SET `Content_zh_TW` = '$inputcontent', `Title_zh_TW` = '$inputtitle' WHERE id = '$article->id'");
            // Increase Update Count to +1
            $newupcount = $article->UpdateCounter + 1;
            mysqli_query($dbcon, "UPDATE Articles SET `UpdateCounter` = '$newupcount' WHERE id = '$article->id'");
            // Update Timestamp
            mysqli_query($dbcon, "UPDATE Articles SET `LastUpdate` = current_timestamp WHERE id = '$article->id'");
            $saveprevsuc = "1";
            // $petdb = mysqli_query($dbcon, "DELETE FROM Articles WHERE id = '$prevarticle->id'");
        }
        // Update article info for output:
        $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainid' AND Preview != '1' ORDER BY LastUpdate DESC") or die(mysqli_error($dbcon));
        $article = mysqli_fetch_object($articledb);
        if ($article->Loca == "0") {
            $overrideurllng = "1";
        }
    }
    if ($saveprevsuc == "1") {
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Article edited', '$article->id')") OR die(mysqli_error($dbcon));
        echo '<script>$.growl.notice({ message: "Article saved", duration: "5000", size: "large", location: "tc"  });</script>';
        mysqli_query($dbcon, "DELETE FROM Articles WHERE Preview = '1' AND LastUpdate < DATE_SUB(NOW(), INTERVAL 5 HOUR)") OR die(mysqli_error($dbcon));
    }
    if ($savepreverr == "1") {
        echo '<script>$.growl.error({ message: "Error while saving article, please try again.", duration: "10000", size: "large", location: "tc"  });</script>';
    }
}






// Pre-process article information for output
if ($article->PageWidth == "0") {
    $pagewidth = "100%";
}
else {
    $pagewidth = $article->PageWidth;
}

$urllng = $_GET['lng'];

if ($preview == "Preview") {
    $urllng = $_POST['seleclang'];
}
if ($overrideurllng == "1") {
    $urllng = "en_US";
}
if (!$urllng) {
    $urllng = $language;
    $noforce = true;
}


if ($urllng == "en_US" OR $urllng == "de_DE" OR $urllng == "fr_FR" OR $urllng == "it_IT" OR $urllng == "es_ES" OR $urllng == "pl_PL" OR $urllng == "pt_BR" OR $urllng == "ru_RU" OR $urllng == "ko_KR" OR $urllng == "zh_TW") {
    $arttitleext = "Title_".$urllng;
    $artcontentext = "Content_".$urllng;
    if ($noforce != true) $forcelng = "1";
    $selectlng = $urllng;   // Used to push editor directly to this language in case it's changed through admin panel
}
else {
    $arttitleext = "Title_".$language;
    $artcontentext = "Content_".$language;
    $selectlng = $language;
}

if ($article->${'arttitleext'} != "" OR $forcelng == "1"){
    $publishtitle = stripslashes(htmlentities($article->${'arttitleext'}, ENT_QUOTES, "UTF-8"));
}
else {
    $publishtitle = stripslashes(htmlentities($article->Title_en_US, ENT_QUOTES, "UTF-8"));
}

$title_en_US = stripslashes(htmlentities($article->Title_en_US, ENT_QUOTES, "UTF-8"));
$title_de_DE = stripslashes(htmlentities($article->Title_de_DE, ENT_QUOTES, "UTF-8"));
$title_fr_FR = stripslashes(htmlentities($article->Title_fr_FR, ENT_QUOTES, "UTF-8"));
$title_it_IT = stripslashes(htmlentities($article->Title_it_IT, ENT_QUOTES, "UTF-8"));
$title_es_ES = stripslashes(htmlentities($article->Title_es_ES, ENT_QUOTES, "UTF-8"));
$title_pl_PL = stripslashes(htmlentities($article->Title_pl_PL, ENT_QUOTES, "UTF-8"));
$title_pt_BR = stripslashes(htmlentities($article->Title_pt_BR, ENT_QUOTES, "UTF-8"));
$title_ru_RU = stripslashes(htmlentities($article->Title_ru_RU, ENT_QUOTES, "UTF-8"));
$title_ko_KR = stripslashes(htmlentities($article->Title_ko_KR, ENT_QUOTES, "UTF-8"));
$title_zh_TW = stripslashes(htmlentities($article->Title_zh_TW, ENT_QUOTES, "UTF-8"));

$editarticle_en_US = stripslashes(htmlentities($article->Content_en_US, ENT_QUOTES, "UTF-8"));
$transarticle_en_US = str_replace(PHP_EOL, "<br>", $editarticle_en_US);
$editarticle_de_DE = stripslashes(htmlentities($article->Content_de_DE, ENT_QUOTES, "UTF-8"));
$editarticle_fr_FR = stripslashes(htmlentities($article->Content_fr_FR, ENT_QUOTES, "UTF-8"));
$editarticle_it_IT = stripslashes(htmlentities($article->Content_it_IT, ENT_QUOTES, "UTF-8"));
$editarticle_es_ES = stripslashes(htmlentities($article->Content_es_ES, ENT_QUOTES, "UTF-8"));
$editarticle_pl_PL = stripslashes(htmlentities($article->Content_pl_PL, ENT_QUOTES, "UTF-8"));
$editarticle_pt_BR = stripslashes(htmlentities($article->Content_pt_BR, ENT_QUOTES, "UTF-8"));
$editarticle_ru_RU = stripslashes(htmlentities($article->Content_ru_RU, ENT_QUOTES, "UTF-8"));
$editarticle_ko_KR = stripslashes(htmlentities($article->Content_ko_KR, ENT_QUOTES, "UTF-8"));
$editarticle_zh_TW = stripslashes(htmlentities($article->Content_zh_TW, ENT_QUOTES, "UTF-8"));

if ($article->${'artcontentext'} != "" OR $forcelng == "1"){
    $publisharticle = stripslashes(htmlentities($article->${'artcontentext'}, ENT_QUOTES, "UTF-8"));
}
else {
    $publisharticle = $editarticle_en_US;
}

$toc = \BBCode\process_main_article($publisharticle);
$publisharticle = $toc['article'];
unset($toc['article']);

// =========== EDITING MODAL BELOW ==============
if ($pagemode != "review" && ($userrights[$article->UserRight] == "yes" OR ($userrights['LocArticles'] == "yes" && $article->Loca == "1"))) {
?>

<div class="remodal_articles" data-remodal-id="edit_article">

    <table style="position: sticky; top:1px; z-index: 453599" class="profile">
        <tr class="profile">
            <?
            
            \BBCode\bboptions_simple('article');
            \BBCode\bboptions_spacer();
            \BBCode\bboptions_advanced('article');
            \BBCode\bboptions_spacer();
            \BBCode\bboptions_tables('article');
            \BBCode\bboptions_spacer();
            echo '<td><p class="blogodd">Add:</p></td>';
            \BBCode\bboptions_url('article');
            \BBCode\bboptions_pet('article');
            \BBCode\bboptions_ability('article');
            \BBCode\bboptions_image('article');
            \BBCode\bboptions_user('article');
            
            ?>
        </tr>
    </table>


    <table width="100%" class="profile">

        <?php if ($article->Loca == "1") { ?>
        <tr class="profile">
            <th colspan="2" width="5" class="profile">
                <table>
                    <tr>
                        <td><button class="articleedit_lng <?php if ($selectlng == "en_US") { echo "articleedit_lng_active"; } ?>" id="atbt_en_US" onclick="article_chlng('en_US')">English</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "de_DE") { echo "articleedit_lng_active"; } ?>" id="atbt_de_DE" onclick="article_chlng('de_DE')">German</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "fr_FR") { echo "articleedit_lng_active"; } ?>" id="atbt_fr_FR" onclick="article_chlng('fr_FR')">French</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "it_IT") { echo "articleedit_lng_active"; } ?>" id="atbt_it_IT" onclick="article_chlng('it_IT')">Italian</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "es_ES") { echo "articleedit_lng_active"; } ?>" id="atbt_es_ES" onclick="article_chlng('es_ES')">Spanish</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "pl_PL") { echo "articleedit_lng_active"; } ?>" id="atbt_pl_PL" onclick="article_chlng('pl_PL')">Polish</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "pt_BR") { echo "articleedit_lng_active"; } ?>" id="atbt_pt_BR" onclick="article_chlng('pt_BR')">Portuguese</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "ru_RU") { echo "articleedit_lng_active"; } ?>" id="atbt_ru_RU" onclick="article_chlng('ru_RU')">Russian</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "ko_KR") { echo "articleedit_lng_active"; } ?>" id="atbt_ko_KR" onclick="article_chlng('ko_KR')">Korean</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "zh_TW") { echo "articleedit_lng_active"; } ?>" id="atbt_zh_TW" onclick="article_chlng('zh_TW')">Chinese</button></td>
                    </tr>
                </table>
            </th>
        </tr>
        <?php } ?>

        <tr class="profile" <?php if ($userrights[$article->UserRight] != "yes") { echo 'style="display: none"'; } ?> >
            <td class="collectionbordertwo">
                <form method="post" style="display: inline">
                <input type="hidden" name="action" value="save_article">
                <input type="hidden" id="seleclang" name="seleclang" value="<?php echo $selectlng; ?>">
                <span id="article_lng" style="display: none"><?php echo $selectlng; ?></span>
                <table>
                    <tr>
                        <td>
                            <p class="blogodd" style="font-size: 14px">TOC Panel</p>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Left:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="left" value="1" name="tocloc" <?php if ($article->TOC == "1") { echo "checked"; } ?>>
                                    <label for="left"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Right:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="right" value="2" name="tocloc" <?php if ($article->TOC == "2") { echo "checked"; } ?>>
                                    <label for="right"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Off:</p>
                        </td>
                        <td style="padding-right: 10px">
                            <ul class="radiossmall">
                                <li>
                                    <input class="red" type="radio" id="off" value="0" name="tocloc" <?php if ($article->TOC == "0") { echo "checked"; } ?>>
                                    <label for="off"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td style="width: 1px; background-color: #757575">
                        </td>

                        <td style="padding: 0px 10px 0px 10px">
                            <p class="blogodd" style="font-size: 14">Page Width: </p><input class="petselect" style="width: 70px; font-weight: normal" type="field" name="article_width" value="<?php echo $article->PageWidth ?>">
                        </td>

                        <td style="width: 1px; background-color: #757575"></td>

                        <td style="padding: 0px 10px 0px 10px">
                            <p class="blogodd" style="font-size: 14px">Allow Localization:</p>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">Yes:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="lightblue" type="radio" id="locyes" value="1" name="loca" <?php if ($article->Loca == "1") { echo "checked"; } ?>>
                                    <label for="locyes"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                        <td>
                            <p class="blogodd" style="font-size: 14px">No:</p>
                        </td>
                        <td>
                            <ul class="radiossmall">
                                <li>
                                    <input class="red" type="radio" id="locno" value="0" name="loca" <?php if ($article->Loca == "0") { echo "checked"; } ?>>
                                    <label for="locno"></label>
                                    <div class="check"></div>
                                </li>
                            </ul>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>

        <tr class="profile">
            <td class="collectionbordertwo">
                <div id="article_en_US" class="language_input" <?php if ($selectlng != "en_US" && $article->Loca != "0") { echo 'style="display: none"'; } ?>>
                    <?php if ($userrights[$article->UserRight] == "yes") { ?>
                        <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_en_US" name="article_title_en_US" value="<?php echo $title_en_US ?>">
                        <textarea class="edit_article" id="article_ta_en_US" name="article_content_en_US" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_en_US ?></textarea>
                    <?php } ?>
                    <?php if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes") { ?>
                        <p class="blogodd" style="font-weight: bold">Title: <b><?php echo $title_en_US ?></b>
                        <hr class="home">
                        <p class="blogodd" style="font-size: 14"> <?php echo $transarticle_en_US ?></p>
                        <div style="display: none">
                            <input type="field" id="en_title_translator" value="<?php echo $title_en_US ?>">
                            <textarea id="en_article_translator"><?php echo $editarticle_en_US ?></textarea>
                        </div>
                    <?php } ?>
                </div>
                <?
                if ($userrights[$article->UserRight] == "yes") { $importtype = "editor"; }
                if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes") { $importtype = "translator"; }
                ?>
                <div id="article_de_DE" class="language_input" <?php if ($selectlng != "de_DE" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_de_DE" name="article_title_de_DE" value="<?php echo $title_de_DE ?>">
                    <button onclick="art_import_en('de_DE','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_de_DE" name="article_content_de_DE" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_de_DE ?></textarea>
                </div>
                <div id="article_fr_FR" class="language_input" <?php if ($selectlng != "fr_FR" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_fr_FR" name="article_title_fr_FR" value="<?php echo $title_fr_FR ?>">
                    <button onclick="art_import_en('fr_FR','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_fr_FR" name="article_content_fr_FR" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_fr_FR ?></textarea>
                </div>
                <div id="article_it_IT" class="language_input" <?php if ($selectlng != "it_IT" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_it_IT" name="article_title_it_IT" value="<?php echo $title_it_IT ?>">
                    <button onclick="art_import_en('it_IT','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_it_IT" name="article_content_it_IT" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_it_IT ?></textarea>
                </div>
                <div id="article_es_ES" class="language_input" <?php if ($selectlng != "es_ES" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_es_ES" name="article_title_es_ES" value="<?php echo $title_es_ES ?>">
                    <button onclick="art_import_en('es_ES','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_es_ES" name="article_content_es_ES" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_es_ES ?></textarea>
                </div>
                <div id="article_pl_PL" class="language_input" <?php if ($selectlng != "pl_PL" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_pl_PL" name="article_title_pl_PL" value="<?php echo $title_pl_PL ?>">
                    <button onclick="art_import_en('pl_PL','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_pl_PL" name="article_content_pl_PL" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_pl_PL ?></textarea>
                </div>
                <div id="article_pt_BR" class="language_input" <?php if ($selectlng != "pt_BR" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_pt_BR" name="article_title_pt_BR" value="<?php echo $title_pt_BR ?>">
                    <button onclick="art_import_en('pt_BR','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_pt_BR" name="article_content_pt_BR" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_pt_BR ?></textarea>
                </div>
                <div id="article_ru_RU" class="language_input" <?php if ($selectlng != "ru_RU" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_ru_RU" name="article_title_ru_RU" value="<?php echo $title_ru_RU ?>">
                    <button onclick="art_import_en('ru_RU','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_ru_RU" name="article_content_ru_RU" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_ru_RU ?></textarea>
                </div>
                <div id="article_ko_KR" class="language_input" <?php if ($selectlng != "ko_KR" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_ko_KR" name="article_title_ko_KR" value="<?php echo $title_ko_KR ?>">
                    <button onclick="art_import_en('ko_KR','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_ko_KR" name="article_content_ko_KR" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_ko_KR ?></textarea>
                </div>
                <div id="article_zh_TW" class="language_input" <?php if ($selectlng != "zh_TW" OR $article->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold">Title: </p><input class="petselect" style="width: 400px" type="field" id="article_title_zh_TW" name="article_title_zh_TW" value="<?php echo $title_zh_TW ?>">
                    <button onclick="art_import_en('zh_TW','<?php echo $importtype ?>')" type="button" class="comsubmit" style="margin-left: 30px" >Import English Title and Content</button>
                    <textarea class="edit_article" id="article_ta_zh_TW" name="article_content_zh_TW" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_zh_TW ?></textarea>
                </div>
            </td>
        </tr>
    </table>

    <table style="position: sticky;bottom:5px;z-index: 453599; width: 100%" class="profile">
        <tr class="profile">
            <td class="collectionbordertwo"><center>
                <table style="width: 100%">
                    <tr>
                        <?php if ($userrights[$article->UserRight] == "yes") { ?>
                            <td>
                                <p class="blogodd" style="white-space: nowrap">Reason for update (optional):</p>
                            </td>
                            <td >
                                <input class="petselect" style="width: 200px; font-weight: normal; font-size: 14px" type="field" name="update_reason" maxlength="200">
                            </td>
                        <?php }
                        else {
                        echo '<td style="width:100px"></td>';
                        } ?>

                        <td style="padding-left: 12px; width: 80%; text-align: center">
                            <input type="submit" class="comedit" name="showprev" style="margin-right: 15px" formaction="index.php?m=<?php echo $mainselector ?>&lng=<?php echo $urllng ?>" formtarget="preview_<?php echo $article->Article; ?>" value="Preview">
                            <input data-remodal-action="close" type="submit" class="comdelete" value="<?php echo __("Cancel"); ?>">
                        </td>
                        <td style="padding-left: 15px;">
                            <input type="submit" class="comsubmit" formaction="index.php?m=<?php echo $mainselector ?>&lng=<?php echo $urllng ?>" value="Save without preview">
                            </form>
                            </td>
                        <td style="padding-left: 15px;">
                            <p class="smallodd" style="white-space: nowrap"><span style="padding-right: 15px" class="commbbcbright" id="rsp_remaining_rmg"></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<script>
    var options = {
        hashTracking: false
    };
    $('[data-remodal-id=edit_article]').remodal(options);

    $(document).on('opened', '.remodal', function () {
        adjust_editfield();
    });

    function adjust_editfield() {
        var el = document.getElementById('article_ta_<?php echo $selectlng ?>');
        var h = el.scrollHeight;
        el.style.height = h+"px";
    }
</script>

<?php }




// =========== SOURCE CODE MODAL FOR REVIEW - MODAL ==============
if ($pagemode == "review" && ($userrights[$article->UserRight] == "yes" OR ($userrights['LocArticles'] == "yes" && $curarticle->Loca == "1"))) { ?>

<div class="remodal_articles" data-remodal-id="article_source">
    <table width="100%" class="profile">
        <?php if ($curarticle->Loca == "1") { ?>
        <tr class="profile">
            <th colspan="2" width="5" class="profile">
                <span id="article_lng" style="display: none"><?php echo $selectlng ?></span>
                <table>
                    <tr>
                        <td><button class="articleedit_lng <?php if ($selectlng == "en_US") { echo "articleedit_lng_active"; } ?>" id="atbt_en_US" onclick="article_chlng('en_US')">English</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "de_DE") { echo "articleedit_lng_active"; } ?>" id="atbt_de_DE" onclick="article_chlng('de_DE')">German</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "fr_FR") { echo "articleedit_lng_active"; } ?>" id="atbt_fr_FR" onclick="article_chlng('fr_FR')">French</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "it_IT") { echo "articleedit_lng_active"; } ?>" id="atbt_it_IT" onclick="article_chlng('it_IT')">Italian</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "es_ES") { echo "articleedit_lng_active"; } ?>" id="atbt_es_ES" onclick="article_chlng('es_ES')">Spanish</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "pl_PL") { echo "articleedit_lng_active"; } ?>" id="atbt_pl_PL" onclick="article_chlng('pl_PL')">Polish</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "pt_BR") { echo "articleedit_lng_active"; } ?>" id="atbt_pt_BR" onclick="article_chlng('pt_BR')">Portuguese</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "ru_RU") { echo "articleedit_lng_active"; } ?>" id="atbt_ru_RU" onclick="article_chlng('ru_RU')">Russian</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "ko_KR") { echo "articleedit_lng_active"; } ?>" id="atbt_ko_KR" onclick="article_chlng('ko_KR')">Korean</button></td>
                        <td><button class="articleedit_lng <?php if ($selectlng == "zh_TW") { echo "articleedit_lng_active"; } ?>" id="atbt_zh_TW" onclick="article_chlng('zh_TW')">Chinese</button></td>
                    </tr>
                </table>
            </th>
        </tr>
        <?php } ?>

        <tr class="profile">
            <td class="collectionbordertwo">
                <div id="article_en_US" class="language_input" <?php if ($selectlng != "en_US" && $curarticle->Loca != "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_en_US ?>
                    <textarea class="edit_article" id="article_ta_en_US" name="article_content_en_US" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_en_US ?></textarea>
                </div>
                <div id="article_de_DE" class="language_input" <?php if ($selectlng != "de_DE" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_de_DE ?>
                    <textarea class="edit_article" id="article_ta_de_DE" name="article_content_de_DE" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_de_DE ?></textarea>
                </div>
                <div id="article_fr_FR" class="language_input" <?php if ($selectlng != "fr_FR" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_fr_FR ?>
                    <textarea class="edit_article" id="article_ta_fr_FR" name="article_content_fr_FR" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_fr_FR ?></textarea>
                </div>
                <div id="article_it_IT" class="language_input" <?php if ($selectlng != "it_IT" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_it_IT ?>
                    <textarea class="edit_article" id="article_ta_it_IT" name="article_content_it_IT" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_it_IT ?></textarea>
                </div>
                <div id="article_es_ES" class="language_input" <?php if ($selectlng != "es_ES" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_es_ES ?>
                    <textarea class="edit_article" id="article_ta_es_ES" name="article_content_es_ES" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_es_ES ?></textarea>
                </div>
                <div id="article_pl_PL" class="language_input" <?php if ($selectlng != "pl_PL" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_pl_PL ?>
                    <textarea class="edit_article" id="article_ta_pl_PL" name="article_content_pl_PL" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_pl_PL ?></textarea>
                </div>
                <div id="article_pt_BR" class="language_input" <?php if ($selectlng != "pt_BR" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_pt_BR ?>
                    <textarea class="edit_article" id="article_ta_pt_BR" name="article_content_pt_BR" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_pt_BR ?></textarea>
                </div>
                <div id="article_ru_RU" class="language_input" <?php if ($selectlng != "ru_RU" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_ru_RU ?>
                    <textarea class="edit_article" id="article_ta_ru_RU" name="article_content_ru_RU" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_ru_RU ?></textarea>
                </div>
                <div id="article_ko_KR" class="language_input" <?php if ($selectlng != "ko_KR" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_ko_KR ?>
                    <textarea class="edit_article" id="article_ta_ko_KR" name="article_content_ko_KR" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_ko_KR ?></textarea>
                </div>
                <div id="article_zh_TW" class="language_input" <?php if ($selectlng != "zh_TW" OR $curarticle->Loca == "0") { echo 'style="display: none"'; } ?>>
                    <p class="blogodd" style="font-weight: bold"><?php echo $title_zh_TW ?>
                    <textarea class="edit_article" id="article_ta_zh_TW" name="article_content_zh_TW" style="height: 500; width: 100%; margin-top: 10px" onkeyup="auto_adjust_textarea_size(this); count_remaining_msgs(this,'rmg','100000')" maxlength="100000"><?php echo $editarticle_zh_TW ?></textarea>
                </div>
            </td>
        </tr>
    </table>
</div>

<script>
var options = {
    hashTracking: false
};
$('[data-remodal-id=article_source]').remodal(options);

$(document).on('opened', '.remodal', function () {
    adjust_editfield();
});

function adjust_editfield() {
    var el = document.getElementById('article_ta_<?php echo $selectlng ?>');
    var h = el.scrollHeight;
    el.style.height = h+"px";
}
</script>
<?php }








// =========== OUTPUT ARTICLE ==============  ?>

<?php if ($maintype == "0") { // Header for Articles with Strategies
    $subsdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = $mainid");
    if (mysqli_num_rows($subsdb) < "4" && $mainselector != "17") {
        $articleclass = "articlecontent2";
    }
    else {
        $articleclass = "articlecontent3";
    }

    ?>
    <div class="subtitle">
        <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
        <td width="100%"><h class="megatitle"><?php echo $publishtitle; ?></h>
                <?php if ($userrights[$article->UserRight] == "yes" && $pagemode != "review" && $preview != "Preview") { ?>
                    <br><a class="alternativessmall" style="color: white" data-remodal-target="edit_article" style="display:block">Edit Article</a>
                <?php } ?>
                <?php if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes" && $pagemode != "review" && $article->Loca == "1" && $preview != "Preview") { ?>
                    <br><a class="alternativessmall" style="color: white" data-remodal-target="edit_article" style="display:block">Edit Translations</a>
                <?php } ?>
                <?php if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes" && $pagemode != "review" && $article->Loca == "0" && $preview != "Preview") { ?>
                    <br><p class="alternativessmall" style="color: white" style="display:block">No translations required</p>
                <?php } ?>
                <?php if ($pagemode == "review" && $preview != "Preview" && ($userrights[$article->UserRight] == "yes" OR ($userrights['LocArticles'] == "yes" && $curarticle->Loca == "1"))) { ?>
                    <br><a class="alternativessmall" style="color: white" data-remodal-target="article_source" style="display:block">View Source</a>
                <?php } ?>
        </td>
        <td><img src="images/main_bg02_2.png"></td>
        </tr>
        </table>
    </div>

<div class="article">
   <div class="articlebottom"></div>
<div class="<?php echo $articleclass ?>">

<?php }
else { // Header for articles without strategies / full page ?>

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><img src="images/main_bg02_1.png"></td>
            <td width="100%"><center><h class="megatitle"><?php echo $publishtitle; ?></h>
            <?php if ($userrights[$article->UserRight] == "yes" && $pagemode != "review" && $preview != "Preview") { ?>
                <br><a class="alternativessmall" style="color: white" data-remodal-target="edit_article" style="display:block">Edit Article</a>
            <?php } ?>
            <?php if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes" && $pagemode != "review" && $article->Loca == "1" && $preview != "Preview") { ?>
                <br><a class="alternativessmall" style="color: white" data-remodal-target="edit_article" style="display:block">Edit Translations</a>
            <?php } ?>
            <?php if ($userrights[$article->UserRight] != "yes" AND $userrights['LocArticles'] == "yes" && $pagemode != "review" && $article->Loca == "0" && $preview != "Preview") { ?>
                <br><p style="color: white" style="display:block">No translation required</p>
            <?php } ?>
            <?php if ($pagemode == "review" && $preview != "Preview" && ($userrights[$article->UserRight] == "yes" OR ($userrights['LocArticles'] == "yes" && $curarticle->Loca == "1"))) { ?>
                <br><a class="alternativessmall" style="color: white" data-remodal-target="article_source" style="display:block">View Source</a>
            <?php } ?>
            </td>
            <td><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>

<div class="blogentryfirst">
    <div class="articlebottom"></div>
<div class="articlecontent2">
<?php }



if (($userrights[$article->UserRight] == "yes" OR $userrights['LocArticles'] == "yes") && $preview != "Preview") { ?>
    <table class="profile" style="position: sticky; float: left; top: 0px; border: 3px solid grey; margin: 0 15 15 0; z-index: 999">
        <tr class="profile" style=" background: #f4f4f4">
            <td class="profile" style="padding-right: 10px">
                <center><div id="admarrdw" style="cursor: pointer" onclick="$('#adminext').show(1000);$('#admarrdw').hide();$('#admarrup').show();"><b>+</b></div></center>
                <div id="admarrup" style="display: none; cursor: pointer" onclick="$('#adminext').hide(1000);$('#admarrdw').show();$('#admarrup').hide();"><b>-</b></div>
                <div style="display:none" id="adminext">
                    <p class="smallodd"><b>Admin Menu:</b><br>
                    <?php if ($article->Loca != "0" && $curarticle->Loca != "0") { ?>
                        Switch language:<br><br>
                        <?
                        if ($urllng == "en_US" OR !$urllng) { echo "<b><i>English</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=en_US">English</a><br>'; }
                        if ($urllng == "de_DE") { echo "<b><i>German</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=de_DE">German</a><br>'; }
                        if ($urllng == "fr_FR") { echo "<b><i>French</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=fr_FR">French</a><br>'; }
                        if ($urllng == "it_IT") { echo "<b><i>Italian</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=it_IT">Italian</a><br>'; }
                        if ($urllng == "es_ES") { echo "<b><i>Spanish</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=es_ES">Spanish</a><br>'; }
                        if ($urllng == "pl_PL") { echo "<b><i>Polish</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=pl_PL">Polish</a><br>'; }
                        if ($urllng == "pt_BR") { echo "<b><i>Portuguese</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=pt_BR">Portuguese</a><br>'; }
                        if ($urllng == "ru_RU") { echo "<b><i>Russian</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=ru_RU">Russian</a><br>'; }
                        if ($urllng == "ko_KR") { echo "<b><i>Korean</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=ko_KR">Korean</a><br>'; }
                        if ($urllng == "zh_TW") { echo "<b><i>Chinese</i></b><br>"; }
                        else { echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$revision.'&lng=zh_TW">Chinese</a><br>'; }
                        ?>
                        <br>
                    <?php } ?>
                    <?
                    $updatesdb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainid' AND Preview != '1' ORDER BY LastUpdate DESC") or die(mysqli_error($dbcon));
                    if (mysqli_num_rows($updatesdb) > "0") { ?>
                        <hr class="quickfacts">
                        <p class="smallodd">Full Changelog:<br>
                        <?
                        while ($oneupdate = mysqli_fetch_object($updatesdb)) {
                            $lastdate = explode(" ", $oneupdate->LastUpdate);
                            if ($oneupdate->UpdateReason == "") {
                                $updatereason = "Autosave";
                            }
                            else {
                                $updatereason = $oneupdate->UpdateReason;
                            }
                            if ($revision == $oneupdate->id) {
                                echo '<b><i><span name="time">'.$lastdate[0].'</span></i> - '.$updatereason.'</b><br>';
                            }
                            else {
                                echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$oneupdate->id.'&lng='.$urllng.'"><span name="time">'.$lastdate[0].'</span></a> - '.$updatereason.'<br>';
                            }
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
    </table>
<?php }






if ($pagemode == "review") { ?>
    <center>
    <table class="profile" style="position: sticky; top: 10px; height: 50px; min-width: 300px; border: 5px solid #e2d181; margin-bottom: 20px">
        <tr class="profile" style=" background: #f2ebcd">
            <td class="profile" style="padding-right: 10px">
                <center>
                    <?php $revdate = explode(" ", $article->LastUpdate); ?>
                    <p class="blogodd">You are viewing an older version of this article from <span name="time"><?php echo $revdate[0] ?></span>
                    <br>
                    <a class="comlinkdark" href="?m=<?php echo $mainselector ?>&lng=<?php echo $urllng ?>">Go to current version</a></p>
            </td>
        </tr>
    </table>
<?php } ?>

<?php if ($preview == "Preview") { ?>
    <center>
    <table class="profile" style="position: sticky; top: 10px; height: 50px; min-width: 300px; border: 5px solid #e2d181; margin-bottom: 20px">
        <tr class="profile" style=" background: #f2ebcd">
            <td class="profile" style="padding-right: 10px">
                <center>
                    <p class="blogodd">This is an unsaved preview. If you close this page, your changes will be discarded.
                    <?php if ($_POST['seleclang'] != "en_US" && $_POST['seleclang'] != "" && $article->Loca == "0") {
                        echo "<br><b>Note:</b> you are about to set localization off. This defaults all languages to English and the article you are viewing right now will not be visible anymore.";
                    } ?>
                    </p>
                    <br>
                    <form method="post" action="?m=<?php echo $mainselector ?>&lng=<?php echo $urllng ?>" style="display: inline">
                        <input type="hidden" name="prevarticle" value="<?php echo $article->id ?>">
                        <input type="hidden" name="action" value="save_preview">
                        <input type="submit" class="comsubmit" name="saveprev" value="Save & Publish Changes" style="margin: 10 0 10 0">
                    </form>
            </td>
        </tr>
    </table>
<?php }






// Quick info Box:
if ($article->TOC != "0") {
    switch ($article->TOC) {
        case "1":
            $qiposition = "art_qi_left";
            break;
        case "2":
            $qiposition = "art_qi_right";
            break;
    } ?>

    <div class="remodal-bg <?php echo $qiposition; ?>">
    <center>
    <table class="profile" style="width: 100%">
        <tr class="profile">
            <th class="profile" style="padding-top: 2px; padding-bottom: 2px">
                <p class="smallodd"><b>Quick Info:
            </th>

        <tr class="profile">
            <td class="profile">
                <table>
                    <tr>
                        <td style="padding-right: 7px; vertical-align:top">
                            <img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_art_author.png" />
                        </td>
                        <td>
                            <?
                            // List of editors
                            echo '<p class="smallodd">Article maintained by:<br>';
                            $userpieces = explode(",", $article->Editors);
                            foreach ($userpieces as $key => $value) {
                                $editordb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$value'") or die(mysqli_error($dbcon));
                                if (mysqli_num_rows($editordb) > "0") {
                                    $editor = mysqli_fetch_object($editordb);
                                    if ($key != "0") {
                                        echo ', ';
                                    }
                                    echo '<span class="username tooltipstered" style="text-decoration: none" rel="'.$editor->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$editor->id.'" class="usernamelink comheadbright com_role_99_bright" style="font-size: 14">'.$editor->Name.'</a></span>';
                                }
                            }
                            ?>
                        </td>
                    </tr>

                    <?php if ($article->Translators != "" && $language != "en_US") { ?>
                    <tr>
                        <td style="padding-right: 7px; vertical-align:top">
                            <img style="padding: 1px;" src="https://www.wow-petguide.com/images/icon_art_loca.png" />
                        </td>
                        <td>
                            <?
                            // List of translators
                            echo '<p class="smallodd">Localization:<br>';
                            $userpieces = explode(",", $article->Translators);
                            foreach ($userpieces as $key => $value) {
                                $editordb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$value'") or die(mysqli_error($dbcon));
                                if (mysqli_num_rows($editordb) > "0") {
                                    $editor = mysqli_fetch_object($editordb);
                                    if ($key != "0") {
                                        echo ', ';
                                    }
                                    echo '<span class="username tooltipstered" style="text-decoration: none" rel="'.$editor->id.'" value="'.$user->id.'"><a target="_blank" href="?user='.$editor->id.'" class="usernamelink comheadbright com_role_99_bright" style="font-size: 14">'.$editor->Name.'</a></span>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if ($toc) { ?>
                    <tr>
                        <td style="vertical-align:top; padding-top: 16px">
                            <img style="padding: 1px" src="https://www.wow-petguide.com/images/icon_art_toc.png" alt="" />
                        </td>
                        <td style="vertical-align:top; padding-top: 16px">
                            <?
                                echo '<p class="smallodd"><b>Table of Contents:</b><br><br>';
                                foreach ($toc as $key => $value) {
                                    switch ($value['type']) {
                                        case "1":
                                            echo '<a class="articles_toc_major" onclick="scrollto(\''.$value['anchor'].'\')">'.$value['title'].'</a><br>';
                                            break;
                                        case "2":
                                            echo '<a class="articles_toc_minor" onclick="scrollto(\''.$value['anchor'].'\')">'.$value['title'].'</a><br>';
                                            break;
                                        case "3":
                                            echo '<a class="articles_toc_smallest h3_bullet" onclick="scrollto(\''.$value['anchor'].'\')">'.$value['title'].'</a><br>';
                                            break;
                                    }
                                }
                            ?>
                            <br>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>

        <tr class="profile">
            <th class="profile" style="padding-top: 6px; padding-bottom: 6px">

                <?
                // Most recent update date
                $lastdate = explode(" ", $article->LastUpdate);
                echo '<p class="smallodd" style="font-weight: normal">Last updated <span name="time">'.$lastdate[0].'</span>';

                // List of most recent updates, expandable!
                $updatesdb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainid' AND Preview != '1' AND UpdateReason != '' ORDER BY LastUpdate DESC") or die(mysqli_error($dbcon));
                if (mysqli_num_rows($updatesdb) > "0") {
                    echo "<br>";

                    ?>
                    <div style="display:none; padding-top: 6px" id="updext">
                        <p class="smallodd" style="font-weight: normal">Changelog:<br>
                    <?

                    while ($oneupdate = mysqli_fetch_object($updatesdb)) {
                        $lastdate = explode(" ", $oneupdate->LastUpdate);
                        if ($revision == $oneupdate->id) {
                            echo '<b><i><span name="time">'.$lastdate[0].'</span></i> - '.$oneupdate->UpdateReason.'</b><br>';
                        }
                        else {
                            echo '<a class="comlinkdark" href="?m='.$mainselector.'&rev='.$oneupdate->id.'"><span name="time">'.$lastdate[0].'</span></a> - '.$oneupdate->UpdateReason.'<br>';
                        }
                    }
                    ?>
                    </div>
                    <center><div id="updarrdw"><img style="cursor: pointer; padding: 8 0 4 0" src="https://www.wow-petguide.com/images/icon_art_updatesdown.jpg" onclick="$('#updext').show(1000);$('#updarrdw').hide();$('#updarrup').show();"></div></center>
                    <center><div id="updarrup" style="display:none"><img style="cursor: pointer; padding: 8 0 4 0" src="https://www.wow-petguide.com/images/icon_art_updatesup.jpg" onclick="$('#updext').hide(1000);$('#updarrdw').show();$('#updarrup').hide();"></div></center>
                    <?
                }

                ?>
            </th>
        </tr>
     </table>

    </div>

<?php } ?>

    <center>
    <div style="max-width: <?php echo $pagewidth ?>; text-align: left; padding-bottom: 20px; overflow: auto">
        <?php echo $publisharticle; ?>  
    </div>     
</div>



<?php  // Automated Petlists
           
if ($mainentry->Pet_Table != 'None') { ?>
    <div class="pet_table" style="padding-top: 0px; float: left; width: 250px; z-index: 5">
        <img src="images/xu_fu_pettable.png" style="height: 220px">
    </div>  
    <div class="pet_table" id="pet_table">
    
        <?php if ($mainentry->Pet_Table == 'Standard') { // Regular table buttons ?>
            <div id="pet_table_buttons" style="display: table">
                Xu-Fu can scan this section for the most suitable strategies according to your preferences and show the required pets.<br>
                Depending on the amount of fights in the section, this can take up to a minute to process.<br>
                <br>
                <button tabindex="5" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>')">Load Pet Table</button>
                <br><br>
            </div>
        <?php } ?>
        
        <?php if ($mainentry->Pet_Table == 'Dungeon') { // Dungeon table ?>
            <div id="pet_table_buttons" style="display: table">
                Xu-Fu can scan this section for the most suitable strategies according to your preferences and show the required pets.<br>
                The Regular option will allow using pets multiple times.<br>
                Heroic mode will use each pet only once due to the restriction of no healing.<br>
                <br>
                <button tabindex="5" style="float: left" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>')">Regular Table</button>
                <button tabindex="5" style="float: left; margin-left: 30px" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','heroic')">Heroic Mode</button>
                <br><br>
            </div>
        <?php } ?>


        <?php if ($mainentry->Pet_Table == 'Celestial') { // Celestial Tournament selection
            
            // Week calculation
            $start = strtotime("2015-09-16"); 
            $end = strtotime(date('Y-m-d'));
            $difference = $end-$start;
            $weekdiff = floor($difference/604800);
            $ct_week = $weekdiff % 3;
            
            switch ($ct_week) {
                case "1":
                    $ct_week_us = 'Week 1 with Taran Zhu, Wrathion and Chen Stormstout';
                    $ct_week_eu = 'Week 2 with Shademaster Kiryn, Wise Mari and Blingtron 4000';
                    break;
                case "2":
                    $ct_week_us = 'Week 2 with Shademaster Kiryn, Wise Mari and Blingtron 4000';
                    $ct_week_eu = 'Week 3 with Sully "The Pickle" McLeary, Dr. Ion Goldbloom and Lorewalker Cho';
                    break;
                case "0":
                    $ct_week_us = 'Week 3 with Sully "The Pickle" McLeary, Dr. Ion Goldbloom and Lorewalker Cho';
                    $ct_week_eu = 'Week 1 with Taran Zhu, Wrathion and Chen Stormstout';
                    break;
            }
            
            
            
            ?>
            <div id="pet_table_buttons" style="display: table">
                Xu-Fu can scan this section for the most suitable strategies according to your preferences and show the required pets.<br>
                It is recommended to check the tables week by week since you won't have to face all 9 tamers in one run.<br>
                The tool will use every pet only once due to the restriction of no healing.<br>
                <br>
                Active:<br>
                <?php echo '<b>US: '.$ct_week_us.'<br>';
                echo 'EU: '.$ct_week_eu.'</b><br>';
                ?><br>
                <button tabindex="5" style="float: left" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','heroic','ct1')">Week 1</button>
                <button tabindex="5" style="float: left; margin-left: 10px" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','heroic','ct2')">Week 2</button>
                <button tabindex="5" style="float: left; margin-left: 10px" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','heroic','ct3')">Week 3</button>
                <button tabindex="5" style="float: left; margin-left: 30px" class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','heroic')">All Weeks</button>
                <br><br>

            </div>
        <?php } ?>
        
        
        
        <?php if ($mainentry->Pet_Table == 'Family') { // Family sections  ?>
            <div id="pet_table_buttons" style="display: table">
                Xu-Fu can scan this section for the most suitable strategies according to your preferences and show the required pets.<br>
                Pick a family below to check which pets you need.<br>
                <br>
                <a class="ffhumanoid" style="float: left; margin-right: 2px; margin-left: 15px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','humanoid')"></a>
                <a class="ffdragonkin" style="float: left; margin-right: 2px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','dragonkin')"></a>
                <a class="ffflying" style="float: left; margin-right: 2px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','flying')"></a>
                <a class="ffundead" style="float: left; margin-right: 2px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','undead')"></a>
                <a class="ffcritter" style="float: left" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','critter')"></a>
                <br><br>
                <a class="ffmagic" style="float: left; margin-right: 2px; margin-left: 15px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','magic')"></a>
                <a class="ffelemental" style="float: left; margin-right: 2px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','elemental')"></a>
                <a class="ffbeast" style="float: left; margin-right: 2px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','beast')"></a>
                <a class="ffaquatic" style="float: left; margin-right: 2px" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','aquatic')"></a>
                <a class="ffmechanical" style="float: left" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','mechanical')"></a>
              <br><br><br>
              <?php if ($mainentry->id == 42) { ?>
                <button class="bnetlogin" style="float: left; margin-right: 20px"" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>','','regular')">Regular strategies</button>
              <?php } ?>
              <button class="bnetlogin" type="submit" onclick="load_petlist('<?php echo $mainselector ?>','<?php echo $user->id ?>','<?php echo $language ?>')">All Families</button>
              <i>(1 min+ to load)</i><br><br><br>
            </div>
        <?php } ?>
        
        

                    
        <div id="loading_field" style="display: none; width: 450px; text-align: center; margin: 20px auto">
            <img src="images/loading.gif">
            <br>
            <i>Loading... this can take a while</i>
        </div>
    </div>
<?php } ?> 


<div class="maincomment">
   <table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
   <tr><td style="width:100%;padding-left: 240px">
   <br><br>
   <?
   // ==== COMMENT SYSTEM 3.0 FOR MAIN ARTICLES HAPPENS HERE ====
    print_comments_outer("0",$mainselector,"medium");
    echo "</div>";
