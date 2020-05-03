<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_REQUEST["userid"];
$usersecret = $_REQUEST["delimiter"];
$menustring = $_REQUEST["menustring"];

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
    if ($user->ComSecret == $usersecret) {
        $usercheck = "OK";
    }
}

$menustring = substr($menustring, 0, -1);

if ($usercheck == "OK" && $menustring){
    $menu_items_raw = explode("-",$menustring);
    foreach ($menu_items_raw as $value) {  // Create Array of all menu items. Required since the Parent ID is using the order ID from JS array, not the actual ID from the DB
        $splits = explode(",",$value);
        $menu_items[$splits[2]]['MenuID'] = $splits[0];
        $menu_items[$splits[2]]['ParentID'] = $splits[1];
        $menu_items[$splits[2]]['MyID'] = $splits[2];
        $menu_items[$splits[2]]['Level'] = "";
        if ($splits[1] == 0) {
            $menu_items[$splits[2]]['Level'] = 1;
        }
    }
    
    /* This part adds the depth level of all items - if required, activate and they will be in the array
    $missing_levels = true;
    while ($missing_levels == true) {
        $missing_levels = false;
        foreach ($menu_items as $value) {
            if ($value['Level'] == "") {
                if ($menu_items[$value['ParentID']]['Level'] == "") {
                    // $missing_levels = true;
                }
                else {
                    $menu_items[$value['MyID']]['Level'] = $menu_items[$value['ParentID']]['Level']+1;
                }
            }
        }   
    }
    */
    
    foreach ($menu_items as $value) {
        $item_id = $value['MenuID'];
        $item_parent = 0;
        if ($value['ParentID'] != 0) {
            $item_parent = $menu_items[$value['ParentID']]['MenuID'];
        }
        $item_order = $value['MyID'];
        mysqli_query($dbcon, "UPDATE Menu_Primary SET `Parent` = '$item_parent' WHERE id = '$item_id'") OR die(mysqli_error($dbcon));
        mysqli_query($dbcon, "UPDATE Menu_Primary SET `Ordering` = '$item_order' WHERE id = '$item_id'") OR die(mysqli_error($dbcon));
    }
    
    echo "OK";
}

mysqli_close($dbcon); 