<?
include("../../data/dbconnect.php");
include("../functions.php");

$lineid = $_REQUEST["lineid"];
$lang = $_REQUEST["lang"];
$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];

$language = $lang;

if ($userid) {
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
    if (mysqli_num_rows($userdb) > "0") {
        $user = mysqli_fetch_object($userdb);
        $userrights = format_userrights($user->Rights);
        if ($user->ComSecret == $comsecret) {
            $veri1 = "true";
        }
    }
}
if ($veri1 == "true") {
    $stepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE id = '$lineid'");
    if (mysqli_num_rows($stepdb) > "0") {
        $step = mysqli_fetch_object($stepdb);
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$step->SortingID'");
        if (mysqli_num_rows($stratdb) > "0") {
            $strat = mysqli_fetch_object($stratdb);

            $petnext = "Name_".$user->Language;
            if ($user->Language == "en_US") {
                $petnext = "Name";
            }
            bt_stredit_printline($step, $strat, $language, $user->id);
            $veri2 = "true";
        }
    }
}

if ($veri2 != "true") {
    echo "NOK";
}