<?php
include("../data/dbconnect.php");
include("functions.php");
?>

<!DOCTYPE html5>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Xu-Fu's Pet Battle Strategies - List of Pet Spells</title>
<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="../data/style.css?v=<?php echo $mtime;?>">
<script src="//wow.zamimg.com/widgets/power.js">
var wowhead_tooltips = { "colorlinks": false, "iconizelinks": true, "renamelinks": false }</script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="../data/functions.js?v=<?php echo $mtime;?>"></script>
<script src="../data/table.js"></script>
</head>

<body>
<center>
    <div style="margin-top: 10px;">
    <h class="megatitle">List of all pet spells</h>
    </div>
    <br><br> <br>
    <?

    echo '<table class="admin" style="width: 80%"><tr>';
    echo '<th class="admin">Ability ID</th>';
    echo '<th class="admin">Spell Family</th>';
    echo '<th class="admin">Name EN</th>';
    echo '<th class="admin">Name DE</th>';
    echo '<th class="admin">Name FR</th>';
    echo '<th class="admin">Name IT</th>';
    echo '<th class="admin">Name ES</th>';
    echo '<th class="admin">Name PL</th>';
    echo '<th class="admin">Name RU</th>';
    echo '<th class="admin">Name MX</th>';
    echo '<th class="admin">Name PT</th>';
    echo '<th class="admin">Name KR</th>';
    echo '<th class="admin">Name TW</th></tr>';

    $allpetsdb = mysqli_query($dbcon, "SELECT * FROM Pet_Abilities ORDER BY id DESC");
    while ($pet = mysqli_fetch_object($allpetsdb)){
        echo '<tr class="admin">';
        echo '<td class="admin">'.$pet->id.'</td>';
        echo '<td class="admin">'.$pet->Family.'</td>';
        echo '<td class="admin">'.$pet->en_US.'</td>';
        echo '<td class="admin">'.$pet->de_DE.'</td>';
        echo '<td class="admin">'.$pet->fr_FR.'</td>';
        echo '<td class="admin">'.$pet->it_IT.'</td>';
        echo '<td class="admin">'.$pet->es_ES.'</td>';
        echo '<td class="admin">'.$pet->pl_PL.'</td>';
        echo '<td class="admin">'.$pet->ru_RU.'</td>';
        echo '<td class="admin">'.$pet->es_MX.'</td>';
        echo '<td class="admin">'.$pet->pt_BR.'</td>';
        echo '<td class="admin">'.$pet->ko_KR.'</td>';
        echo '<td class="admin">'.$pet->zh_TW.'</td>';
        echo "</tr>";
    }






?>
</table>
</body>











