<?
include("../data/dbconnect.php");
include("../classes/functions.php");

$db_r = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE User = 4320 AND Deleted = 0 Order by id") or die(mysqli_error($dbcon));

  while ($strat = $db_r->fetch_object()) {
    $subid = $strat->Sub;
    $db_sub = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $subid") or die(mysqli_error($dbcon));
    $sub = $db_sub->fetch_object();
    $subname = $sub->Name;
    if ($sub->Parent != 0) {
      $subid = $sub->Parent;
      $db_subs = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $subid") or die(mysqli_error($dbcon));
      $subn = $db_subs->fetch_object();
      
      $subname = $subn->Name;
    }
    echo $subname.' - https://wow-petguide.com/?Strategy='.$strat->id.'<br>';
  }