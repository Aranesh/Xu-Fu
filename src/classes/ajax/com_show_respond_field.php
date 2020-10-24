<?php
include("../../data/dbconnect.php");
include("../functions.php");
include("../com_functions.php");

$language = $_POST["language"];
$userid = $_POST["userid"];
$category = $_POST["category"];
$sortingid = $_POST["sortingid"];
$parent = $_POST["parent"];
$styleset = $_POST["styleset"];
$visitorid = $_POST["visitorid"];

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("../../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
set_language_vars($language);
print_commentbox($category,$sortingid,$parent,$styleset,"0",$userid,$visitorid,"en");

mysqli_close($dbcon); 






