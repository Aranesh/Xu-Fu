<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$comsecret = $_REQUEST["delimiter"];
$rating = $_REQUEST["rating"];
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

if ($user && $user->ComSecret == $comsecret && $strat && ($rating == "1" OR $rating == "2" OR $rating == "3" OR $rating == "4" OR $rating == "5")) {
    // Passed verification
    $stratratingdb = mysqli_query($dbcon, "SELECT * FROM UserStratRating WHERE User = '$user->id' AND Strategy = '$strat'");
    if (mysqli_num_rows($stratratingdb) < "1"){
        mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Rate Strategy', '$strat - rated $rating stars')") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "INSERT INTO UserStratRating (`User`, `Strategy`, `Rating`) VALUES ('$user->id', '$strat', '$rating')") OR die(mysqli_error($dbcon));
    }
    else {
        $dbrating = mysqli_fetch_object($stratratingdb);
        if ($dbrating->Rating != $rating) {
            mysqli_query($dbcon, "UPDATE UserStratRating SET `Rating` = '$rating' WHERE User = '$user->id' AND Strategy = '$strat'");
            mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$userid', '$user_ip_adress', '1', 'Change Rating', '$strat - set rating to $rating')") OR die(mysqli_error($dbcon));
        }
    }
    $avgratingdb = mysqli_query($dbcon, "SELECT AVG(Rating) AS rating_avg FROM UserStratRating WHERE Strategy = '$strat'");
    $avgrating = $avgratingdb->fetch_assoc();
    echo round($avgrating['rating_avg'],1);    
}
else {
    echo "NOK";
}
mysqli_close($dbcon);
