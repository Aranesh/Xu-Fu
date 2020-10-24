<?
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$oldline = $_REQUEST["lineid"];

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
    $oldstepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE id = '$oldline'");
    if (mysqli_num_rows($oldstepdb) > "0") {
        $oldstep = mysqli_fetch_object($oldstepdb);
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$oldstep->SortingID'");
        if (mysqli_num_rows($stratdb) > "0") {
            $strat = mysqli_fetch_object($stratdb);
            if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {
                $veri2 = "true";   
            }
        }
    }
}

if ($veri2 == "true") {
    $language = $user->Language;
    $addstepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = $oldstep->SortingID AND id > $oldstep->id ORDER BY id");
    mysqli_query($dbcon, "INSERT INTO Strategy (`SortingID`, `Step`) VALUES ('$oldstep->SortingID', '')") OR die(mysqli_error($dbcon));
    $newline = mysqli_insert_id($dbcon);
    $new_line_db = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE id = '$newline'");
    $newline_full = mysqli_fetch_object($new_line_db);
    
    $petnext = "Name_".$user->Language;
    if ($user->Language == "en_US") {
        $petnext = "Name";
    }
   
    bt_stredit_printline($newline_full, $strat, $language, $user->id);
 
    while ($addsteps = mysqli_fetch_object($addstepdb)){
        $nowid = $addsteps->id;
        $newnowid = DuplicateMySQLRecord('Strategy', 'id', $nowid, $addsteps);
        echo "<script>$('#step_".$nowid."').remove();</script>";
        $new_line_db = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE id = '$newnowid'");
        $newline_full = mysqli_fetch_object($new_line_db);
        bt_stredit_printline($newline_full, $strat, $language, $user->id);
        $deleteit = mysqli_query($dbcon, "DELETE FROM Strategy WHERE id = '$nowid'") OR die(mysqli_error($dbcon));
    }

    mysqli_query($dbcon, "UPDATE Alternatives SET `Updated` = CURRENT_TIMESTAMP WHERE id = '$strat->id'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Strategy Step Added', 'Strategy $strat->id - Step $newline')") OR die(mysqli_error($dbcon));
    }
else {
    echo "NOK";
}