<?
include("../data/dbconnect.php");
$searchstring = $_GET['s'];

$subtitle_results = mysqli_query($dbcon, "SELECT * FROM Sub WHERE (`Name` LIKE '%".$searchstring."%')") or die(mysqli_error($dbcon));

if (mysqli_num_rows($subtitle_results) > 0){
    $foundentry = mysqli_fetch_object($subtitle_results);
    $strat_results = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE Sub = '$foundentry->id' and Alternative = '1'") or die(mysqli_error($dbcon));
    $strat = mysqli_fetch_object($strat_results);
    echo "https://wow-petguide.com/?Strategy=".$strat->id;
}
else {
    echo "No entry found";
}
