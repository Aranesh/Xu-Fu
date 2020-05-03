<?
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$lineid = $_REQUEST["lineid"];


if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

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
        $oldstep = mysqli_fetch_object($stepdb);
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$oldstep->SortingID'");
        if (mysqli_num_rows($stratdb) > "0") {
            $strat = mysqli_fetch_object($stratdb);
            if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {
                $stratdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = '$strat->id'");
                if (mysqli_num_rows($stratdb) == "1") {                
                    echo "lastline";
                }
                else {
                    $veri2 = "true";    
                }    
            }
        }
    }
}

if ($veri2 == "true") {
    mysqli_query($dbcon, "DELETE FROM Strategy WHERE id = '$oldstep->id'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "UPDATE Alternatives SET `Updated` = CURRENT_TIMESTAMP WHERE id = '$strat->id'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Strategy Step Deleted', 'Strategy $strat->id - Step $oldstep->id')") OR die(mysqli_error($dbcon));
    echo "OK";
}
else {
    echo "NOK";
}