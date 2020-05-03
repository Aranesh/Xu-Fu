<?php
include("../data/dbconnect.php");

$result = mysqli_query($dbcon, "SELECT PetID, RematchID FROM PetsUser WHERE Cageable = 1");

// Put them in array
for($i = 0; $array[$i] = mysqli_fetch_assoc($result); $i++) ; 
// Delete last empty one
array_pop($array);

header('Content-Type: application/json');
echo json_encode($array, JSON_NUMERIC_CHECK);

mysqli_close($dbcon);
