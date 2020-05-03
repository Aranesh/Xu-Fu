<?
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$lineid = $_REQUEST["lineid"];
$turn = $_REQUEST["turn"];
$inst = $_REQUEST["inst"];

$inst = mysqli_real_escape_string($dbcon, $inst);
$turn = mysqli_real_escape_string($dbcon, $turn);

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
        $step = mysqli_fetch_object($stepdb);
        $stratdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$step->SortingID'");
        if (mysqli_num_rows($stratdb) > "0") {
            $strat = mysqli_fetch_object($stratdb);
            if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {
                $veri2 = "true";   
            }
        }
    }
}

if ($veri2 == "true") {
    $turn = mysqli_real_escape_string($dbcon, $turn);
    $inst = mysqli_real_escape_string($dbcon, $inst);
    
        
    $strlen = strlen($inst); // cache string length for performance
    $openbraces = 0;

    for ($i = 0; $i < $strlen; $i++) {
        $c = $inst[$i];
        if ($c == '[') // count opening bracket
            $openbraces++;
        if ($c == ']') // count closing bracket
            $openbraces--;
    }
    
    if ($openbraces > "0") {
        while ($openbraces > "0") {
            $inst = $inst."__]";
            $openbraces--;
            $prefix = "1";
        }
    }
    else if ($openbraces < "0") {
        $prefix = "2";
    }
    else if ($openbraces == "0") {
        $prefix = "0";
    }  
    
  
    mysqli_query($dbcon, "UPDATE Strategy SET `Step` = '$turn', `Instruction` = '$inst' WHERE id = '$lineid'");
    
    $stepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = '$strat->id' AND id < '$lineid' ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($stepdb) > "0") {
       $prevstep = mysqli_fetch_object($stepdb);
       echo $prefix."-".$prevstep->id;
    }
    else {
       echo $prefix."-"."firstline";
    }
    
    mysqli_query($dbcon, "UPDATE Alternatives SET `Updated` = CURRENT_TIMESTAMP WHERE id = '$strat->id'") OR die(mysqli_error($dbcon));
    mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$user->id', '$user_ip_adress', '4', 'Strategy Step Edited - Manual', 'Strategy $strat->id - Step $lineid')") OR die(mysqli_error($dbcon));
}
else {
    echo "NOK";
}


