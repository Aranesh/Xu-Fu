<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$sub = $_REQUEST["sub"];
$strat = $_REQUEST["strat"];

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
$user_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$user_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
$user_ip_adress = $_SERVER['REMOTE_ADDR'];
}

$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
if (mysqli_num_rows($userdb) > "0") {
    $user = mysqli_fetch_object($userdb);
}


if ($user && $user->ComSecret == $comsecret && $sub && $strat) {
    // Passed verification

    $favstratdb = mysqli_query($dbcon, "SELECT * FROM UserFavStrats WHERE User = '$user->id' AND Sub = '$sub'");
    if (mysqli_num_rows($favstratdb) < "1"){
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Set Fav Strategy', '$sub - set fav to $strat')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "INSERT INTO UserFavStrats (`User`, `Sub`, `Strategy`) VALUES ('$user->id', '$sub', '$strat')") OR die(mysqli_error($dbcon));
        echo "FAV";
    }
    else {
        $faventry = mysqli_fetch_object($favstratdb);
        if ($faventry->Strategy == $strat) {
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Remove Fav Strategy', '$sub')") OR die(mysqli_error($dbcon));
            mysqli_query($dbcon, "DELETE FROM UserFavStrats WHERE User = '$user->id' AND Sub = '$sub'");
            echo "UNFAV";
        }
        else {
            mysqli_query($dbcon, "UPDATE UserFavStrats SET `Strategy` = '$strat' WHERE User = '$user->id' AND Sub = '$sub'");
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Set Fav Strategy', '$sub - set fav to $strat')") OR die(mysqli_error($dbcon));
            echo "FAV";
        }
    }
}
else {
    echo "NOK";
}
mysqli_close($dbcon);
