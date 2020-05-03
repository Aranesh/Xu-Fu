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
putenv("LANG=".$language.".UTF-8");
setlocale(LC_ALL, $language.".UTF-8");

$domain = "messages";
bindtextdomain($domain, "../../Locale");
textdomain($domain);

set_language_vars($language);

print_commentbox($category,$sortingid,$parent,$styleset,"0",$userid,$visitorid,"en");

mysqli_close($dbcon); 






