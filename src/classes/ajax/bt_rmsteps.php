<?php
include("../../data/dbconnect.php");
include("../functions.php");

$stratid = $_REQUEST["stratid"];
$language = $_REQUEST["lang"];

$petnext = "Name_".$language;
if ($language == "en_US") {
    $petnext = "Name";
}
     

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
putenv("LANG=".$language.".UTF-8");
setlocale(LC_ALL, $language.".UTF-8");

$domain = "messages";
bindtextdomain($domain, "../../Locale");
textdomain($domain);
set_language_vars($language);

$stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $stratid");
$strat = mysqli_fetch_object($stratdb);

if ($strat->User != "0") {
    $stratuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$strat->User'");
    if (mysqli_num_rows($stratuserdb) > "0") {
        $stratuser = mysqli_fetch_object($stratuserdb);
        $headertext = 'Strategy added by '.$stratuser->Name.'xzzuvwzzxn';
    }
    else {
        $stratusererror = "true";
    }
}

if ($strat->User == "0" OR $stratusererror == "true") {
    $stratusericon = '<img src="https://www.wow-petguide.com/images/userpics/del_acc.jpg" style="width:50px; height: 50px; float: left" class="commentpic">';
    if ($strat->CreatedBy == "") {
        $headertext = "";
    }
    else {
        $headertext = "Strategy added by ".$strat->CreatedBy.'xzzuvwzzxn';
    }
}

echo $headertext;


if ($strat->Comment != "") {
    
    $stratcomment = stripslashes(htmlentities($strat->Comment, ENT_QUOTES, "UTF-8"));
    // Replace URLs:
    if (strpos($stratcomment, '[url=') !== false && strpos($stratcomment, '[/url]') !== false) {
        $cutarticle = explode("[url=", $stratcomment);
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snippets1 = explode("[/url]", $value);
                $snippets2 = explode("]", $snippets1[0]);
                $replacestring = '[url='.$snippets2[0].']'.$snippets2[1].'[/url]';
                $maskurl = preg_replace('/^(?!https?:\/\/)/', 'http://', $snippets2[0]);
                $maskurl = str_replace('http','WLPz37f2',$maskurl);
                $maskurl = str_replace('www','MjwMhR9z',$maskurl);
                $replacewith = $snippets2[1].' ('.$maskurl.')';
                $stratcomment = str_replace($replacestring,$replacewith,$stratcomment);
            }
        }
    }

    // Replace Pets:
    if (strpos($stratcomment, '[pet=') !== false) {
        $cutarticle = explode("[pet=", $stratcomment);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snippets1 = explode("]", $value, 2);
                $allpetsdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID = '$snippets1[0]'") or die(mysqli_error($dbcon));
                $thispet = mysqli_fetch_object($allpetsdb);
                $beginning = $beginning.$thispet->${'petnext'}.$snippets1[1];
            }
        }
        $stratcomment = $beginning;
    }
    // Replace Spells:
    if (strpos($stratcomment, '[skill=') !== false) {
        $cutarticle = explode("[skill=", $stratcomment);
        $beginning = $cutarticle[0];
        foreach ($cutarticle as $key => $value) {
            if ($key > "0") {
                $snippets1 = explode("]", $value, 2);
                $allpetsdb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$snippets1[0]'") or die(mysqli_error($dbcon));
                $thisspell = mysqli_fetch_object($allpetsdb);
                $beginning = $beginning.$thisspell->${'language'}.$snippets1[1];
            }
        }
        $stratcomment = $beginning;
    }

    $stratcomment = str_replace('WLPz37f2','http',$stratcomment);
    $stratcomment = str_replace('MjwMhR9z','www',$stratcomment);
    // Replace simple formatting:
    $stratcomment = str_replace("[u]", "", $stratcomment);
    $stratcomment = str_replace("[/u]", "", $stratcomment);
    $stratcomment = str_replace("[i]", "", $stratcomment);
    $stratcomment = str_replace("[/i]", "", $stratcomment);
    $stratcomment = str_replace("[b]", "", $stratcomment);
    $stratcomment = str_replace("[/b]", "", $stratcomment);
    $stratcomment = str_replace("[s]", "", $stratcomment);
    $stratcomment = str_replace("[/s]", "", $stratcomment);
    $stratcomment = str_replace(PHP_EOL, "xzzuvwzzxn", $stratcomment);
    $stratcomment = preg_replace( "/\r|\n/", "", $stratcomment );

    echo $stratcomment."xzzuvwzzxnxzzuvwzzxn";
}

$stratdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = $stratid ORDER BY id");




while ($step = mysqli_fetch_object($stratdb)) {

    // Format Step and Instructions

    $showturn = stripslashes($step->Step);
    $showturn = translate_turn($showturn, $language);
    $showinstruction = translate_instruction($step->Instruction, $language);

    $showinstruction = str_replace("&#039;","'",$showinstruction);
    $showinstruction = str_replace("[u]", "", $showinstruction);
    $showinstruction = str_replace("[/u]", "", $showinstruction);
    $showinstruction = str_replace("[i]", "", $showinstruction);
    $showinstruction = str_replace("[/i]", "", $showinstruction);
    $showinstruction = str_replace("[b]", "", $showinstruction);
    $showinstruction = str_replace("[/b]", "", $showinstruction);    
    $showinstruction = str_replace(PHP_EOL, " ", $showinstruction);
    
    // =========== Gather Spell and Pet names and transform formatting accordingly =========

    $checkspells = extract_text($showinstruction);
    if ($checkspells[0] != ""){
        
        // A) Tansform Spells
        if (strpos($showinstruction, 'spell=') !== false) {
            foreach ($checkspells as $key => $value) {
                $cutinstruction = explode("spell=", $checkspells[$key]);
                $cutinstruction = explode("]", $cutinstruction[1]);
                $findspell = str_replace('&quot;','"',$cutinstruction[0]);
                $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE en_US = '$findspell' AND PetSpell = '1'");
                if (mysqli_num_rows($spelldb) > "0") {
                    $spell = mysqli_fetch_object($spelldb);
                    if ($spell->${'language'} != "") {
                        $spellfull = $spell->${'language'};   
                    }
                    else {
                        $spellfull = $spell->en_US;
                    }
                }
                else {
                    $spellfull = $cutinstruction[0];
                }
                $replacespell = "[spell=".$cutinstruction[0]."]";
                $showinstruction = str_replace($replacespell,$spellfull,$showinstruction);
                $cutinstruction = "";
                $spelldb = "";
                $spell = "";
            }
        }

        // B) Tansform Enemy Pets
        if (strpos($showinstruction, 'enemy=') !== false) {
            foreach ($checkspells as $key => $value) {
                $cutinstruction = explode("enemy=", $checkspells[$key]);
                $cutinstruction = explode("]", $cutinstruction[1]);
                $findpet = str_replace('&quot;','"',$cutinstruction[0]);
                $transpetdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE Name Like '$findpet'");
                if (mysqli_num_rows($transpetdb) > "0") {
                    $transpet = mysqli_fetch_object($transpetdb);
                    if ($transpet->${'petnext'} != "") {
                        $transpetfull = $transpet->${'petnext'};   
                    }
                    else {
                        $transpetfull = $transpet->Name;
                    }
                }
                else {
                    $transpetfull = $cutinstruction[0];
                }
                $replacepet = "[enemy=".$cutinstruction[0]."]";
                $showinstruction = str_replace($replacepet,$transpetfull,$showinstruction);
                $cutinstruction = "";
                $transpetdb = "";
                $transpet = "";
            }
        }
        
        // C) Tansform Pets
        if (strpos($showinstruction, 'pet=') !== false) {
            foreach ($checkspells as $key => $value) {
                $cutinstruction = explode("pet=", $checkspells[$key]);
                $cutinstruction = explode("]", $cutinstruction[1]);
                $findpet = str_replace('&quot;','"',$cutinstruction[0]);
                $transpetdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE Name Like '$findpet'");
                if (mysqli_num_rows($transpetdb) > "0") {
                    $transpet = mysqli_fetch_object($transpetdb);
                    if ($transpet->${'petnext'} != "") {
                        $transpetfull = $transpet->${'petnext'};   
                    }
                    else {
                        $transpetfull = $transpet->Name;
                    }
                }
                else {
                    $transpetfull = $cutinstruction[0];
                }
                $replacepet = "[pet=".$cutinstruction[0]."]";
                $showinstruction = str_replace($replacepet,$transpetfull,$showinstruction);
                $cutinstruction = "";
                $transpetdb = "";
                $transpet = "";
            }
        }
    }
    $showinstruction = stripslashes($showinstruction);
    if ($showturn == "") {
        echo $showinstruction."xzzuvwzzxn";
    }
    else {
        echo $showturn.": ".$showinstruction."xzzuvwzzxn";
    }
}
    
mysqli_close($dbcon);