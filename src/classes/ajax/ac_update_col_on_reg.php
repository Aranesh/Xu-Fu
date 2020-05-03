<?php
include("../../data/dbconnect.php");
include("../functions.php");

$userid = $_POST["userid"];

    if (is_numeric($userid)) {
        $getcol = update_collection($userid, "1");
    }

mysqli_close($dbcon); 