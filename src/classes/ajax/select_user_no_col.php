<?php
include("../../data/dbconnect.php");
include("../functions.php");

$seluser = $_GET['q'];

if ($seluser){
    $userdb = mysqli_query($dbcon, "SELECT id, Name FROM Users WHERE Name LIKE '%$seluser%'");
    $json = [];
    while($user = mysqli_fetch_object($userdb)){
        
        // echo $user->Name;
    
        $findcol = find_collection($user, 2);
        // print_r($findcol);
    
        if ($findcol != 'No Collection') {
            $json[] = ['id'=>$user->id, 'text'=>$user->Name];
        }
        // Senior#11628
        
    }
    echo json_encode($json);
}
mysqli_close($dbcon);