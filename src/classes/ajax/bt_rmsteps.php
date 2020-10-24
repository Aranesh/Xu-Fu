<?php
include("../../data/dbconnect.php");
include("../functions.php");
require_once ('../BBCode.php');
require_once ('../BBCode2.php');

$stratid = $_REQUEST["stratid"];
$language = $_REQUEST["lang"];

$petnext = "Name_".$language;
if ($language == "en_US") {
    $petnext = "Name";
}

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("../../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
  set_language_vars($language);

$all_pets = get_all_pets($petnext, $external);

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
    $stratcomment = \BBCode\bbparse_pets($stratcomment, 12, 'rematch');
    $stratcomment = str_replace('WLPz37f2','http',$stratcomment);
    $stratcomment = str_replace('MjwMhR9z','www',$stratcomment);
    $stratcomment = \BBCode\bbparse_simple($stratcomment, 'rematch');
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
    $showinstruction = \BBCode\bbparse_simple($showinstruction, 'rematch');   
    $showinstruction = str_replace(PHP_EOL, " ", $showinstruction);
    $showinstruction = \BBCode\bbparse_pets($showinstruction, 12, 'rematch');
    $showinstruction = stripslashes($showinstruction);
    if ($showturn == "") {
        echo $showinstruction."xzzuvwzzxn";
    }
    else {
        echo $showturn.": ".$showinstruction."xzzuvwzzxn";
    }
}
    
mysqli_close($dbcon);