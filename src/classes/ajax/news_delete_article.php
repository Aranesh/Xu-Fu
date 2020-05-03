<?php
include("../../data/dbconnect.php");
$image_id = $_REQUEST['id'];
mysqli_query($dbcon, "DELETE FROM News_Articles WHERE id = '$image_id'") OR die('NOK');
echo "OK";
?>
