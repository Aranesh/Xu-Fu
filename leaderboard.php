<?php
include("data/dbconnect.php");

$leaderboard_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard ORDER BY Unique_Pets DESC LIMIT 10000");
$rank = 1;
while($leaderboard_entry = mysqli_fetch_object($leaderboard_db)) {
    $user_db = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$leaderboard_entry->User'");
    $user = mysqli_fetch_object($user_db);
    echo "Rank ".$rank." with ".$leaderboard_entry->Unique_Pets." Pets - <a href='https://www.wow-petguide.com/?user=".$user->id."'>".$user->Name."</a><br>";
    $rank++;
}