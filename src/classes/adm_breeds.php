<?php

$submitbreeds = $_POST['submitbreeds'];



// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================




?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <? echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle">Administration - Breed Importer</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('admin');
    ?>
</div>




<div class="blogentryfirst">
<div class="articlebottom">
</div>

<table style="width: 100%;">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>
            <td>




<table style="width: 100%;">
<tr>
<td>
<table style="width: 85%;" class="profile">

    <? print_admin_menu('adm_breeds'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">

            <? if ($submitbreeds == "true") {
                $breedinfo = $_POST['breedinfo'];

                // Validate File Integrity
                if (strpos($breedinfo, 'GLOBALS: BPBID_Arrays') === false OR strpos($breedinfo, 'BREED STATS') === false OR strpos($breedinfo, 'end') === false) {
                    echo '<center><br><p class="blogodd" style="font-weight: bold">The data you submitted could not be read, please try again</p><br><br><hr style="width: 70%"><br></center>';
                    $showinputfield = "true";
                }

                // Begin taking the file apart
                if ($showinputfield != "true") {
                    $basestatsone = explode("-- BASE STATS", $breedinfo);
                    $basestatstwo = explode("-- AVAILABLE BREEDS", $basestatsone[1]);
                    $basestatsthr = explode("[", $basestatstwo[0]);
                    array_shift($basestatsthr);
                    foreach($basestatsthr as $key => $value) {
                        $idsplitter = explode("]", $value);
                        if (strpos($idsplitter[1], 'false') === false) {
                            $petstats[$idsplitter[0]]['species'] = $idsplitter[0];
                            $idsplittert = explode("{", $idsplitter[1]);
                            $idsplittert = explode("}", $idsplittert[1]);
                            $idsplittert = explode(",", $idsplittert[0]);
                            $petstats[$idsplitter[0]]['health'] = $idsplittert[0];
                            $petstats[$idsplitter[0]]['power'] = $idsplittert[1];
                            $petstats[$idsplitter[0]]['speed'] = $idsplittert[2];
                        }
                    }
                    $breedstats = explode("end", $basestatstwo[1]);
                    $breedstats = explode("[", $breedstats[0]);
                    array_shift($breedstats);
                    foreach($breedstats as $key => $value) {
                        $idsplitter = explode("]", $value);
                        $endpoint = $idsplitter[0];
                        if (strpos($idsplitter[1], 'false') === false) {
                            $petstats[$idsplitter[0]]['species'] = $idsplitter[0];
                            $idsplittert = explode("{", $idsplitter[1]);
                            $idsplittert = explode("}", $idsplittert[1]);
                            $petstats[$idsplitter[0]]['breeds'] = $idsplittert[0];
                        }
                    }

                    // Get all pet data from own database
                    $petsdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID != '0' ORDER BY RematchID");
                    while($thispet = mysqli_fetch_object($petsdb)) {
                        $dbpets[$thispet->RematchID]['species'] = $thispet->RematchID;
                        $dbpets[$thispet->RematchID]['name'] = $thispet->Name;
                        $dbpets[$thispet->RematchID]['health'] = $thispet->Health;
                        $dbpets[$thispet->RematchID]['power'] = $thispet->Power;
                        $dbpets[$thispet->RematchID]['speed'] = $thispet->Speed;
                        if ($thispet->BB == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = "BB,";
                        }
                        if ($thispet->PP == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."PP,";
                        }
                        if ($thispet->SS == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."SS,";
                        }
                        if ($thispet->HH == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."HH,";
                        }
                        if ($thispet->HP == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."HP,";
                        }
                        if ($thispet->PS == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."PS,";
                        }
                        if ($thispet->HS == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."HS,";
                        }
                        if ($thispet->PB == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."PB,";
                        }
                        if ($thispet->SB == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."PP,";
                        }
                        if ($thispet->HB == "1") {
                            $dbpets[$thispet->RematchID]['breeds'] = $dbpets[$thispet->RematchID]['breeds']."HB,";
                        }
                        $dbpets[$thispet->RematchID]['breeds'] = substr($dbpets[$thispet->RematchID]['breeds'], 0, -1);
                    }

                    // Output overview table
                    echo '<table class="admin"><tr>';
                    echo '<th class="admin"></th>';
                    echo '<th class="admin"></th>';
                    echo '<th class="admin" colspan="4">Database Info</th>';
                    echo '<th class="admin" colspan="4">BPBID Info</th>';
                    echo '<th class="admin"></th></tr>';

                    echo '<th class="admin">SpeciesID</th>';
                    echo '<th class="admin">Name</th>';

                    echo '<th class="admin">Health</th>';
                    echo '<th class="admin">Power</th>';
                    echo '<th class="admin">Speed</th>';
                    echo '<th class="admin">Breeds</th>';

                    echo '<th class="admin">Health</th>';
                    echo '<th class="admin">Power</th>';
                    echo '<th class="admin">Speed</th>';
                    echo '<th class="admin">Breeds</th>';

                    echo '<th class="admin">Status</th></tr>';



                    $upcounter = "1";
                    while($upcounter <= $endpoint) {
                        $thisresult = "";
                        $errorcatcher = "";

                        if ($dbpets[$upcounter]['name'] == "" && $petstats[$upcounter]['health']) {
                            // skip line
                        }
                        else {
                            if ($dbpets[$upcounter]['name'] != "" && $petstats[$upcounter]['health'] == "" && $petstats[$upcounter]['breeds'] == "") {
                                echo '<tr class="admin">';
                                $thisresult = "<center>No breed/stat data in addon files found for this pet";
                            }
                            if ($dbpets[$upcounter]['name'] != "" && ($petstats[$upcounter]['health'] != "" OR $petstats[$upcounter]['breeds'] != "")) {
                                echo '<tr class="admingreen">';
                                $newhp = $petstats[$upcounter]['health'];
                                $newpw = $petstats[$upcounter]['power'];
                                $newsp = $petstats[$upcounter]['speed'];
                                $newbr = "";

                                mysqli_query($dbcon, "UPDATE PetsUser SET `Health` = '$newhp' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                mysqli_query($dbcon, "UPDATE PetsUser SET `Power` = '$newpw' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                mysqli_query($dbcon, "UPDATE PetsUser SET `Speed` = '$newsp' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));

                                $allbreeds = explode(", ", $petstats[$upcounter]['breeds']);
                                foreach ($allbreeds as $key => $value) {
                                    switch ($value) {
                                        case "3":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `BB` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = "BB,";
                                            break;
                                        case "4":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `PP` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."PP,";
                                            break;
                                        case "5":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `SS` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."SS,";
                                            break;
                                        case "6":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `HH` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."HH,";
                                            break;
                                        case "7":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `HP` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."HP,";
                                            break;
                                        case "8":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `PS` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."PS,";
                                            break;
                                        case "9":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `HS` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."HS,";
                                            break;
                                        case "10":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `PB` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."PB,";
                                            break;
                                        case "11":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `SB` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."SB,";
                                            break;
                                        case "12":
                                            mysqli_query($dbcon, "UPDATE PetsUser SET `HB` = '1' WHERE RematchID = '$upcounter'") OR die(mysqli_error($dbcon));
                                            $newbr = $newbr."HB,";
                                            break;
                                    }
                                }
                                $thisresult = "<center>Updated";
                                $newbr = substr($newbr, 0, -1);
                                echo '<td class="admin">'.$upcounter.'</td>';
                                echo '<td class="admin">'.$dbpets[$upcounter]['name'].'</td>';

                                echo '<td class="admin">'.$dbpets[$upcounter]['health'].'</td>';
                                echo '<td class="admin">'.$dbpets[$upcounter]['power'].'</td>';
                                echo '<td class="admin">'.$dbpets[$upcounter]['speed'].'</td>';
                                echo '<td class="admin">'.$dbpets[$upcounter]['breeds'].'</td>';

                                echo '<td class="admin">'.$petstats[$upcounter]['health'].'</td>';
                                echo '<td class="admin">'.$petstats[$upcounter]['power'].'</td>';
                                echo '<td class="admin">'.$petstats[$upcounter]['speed'].'</td>';
                                echo '<td class="admin">'.$newbr.'</td>';

                                echo '<td class="admin">'.$thisresult.'</td></tr>';
                            }

                        }
                        $upcounter++;
                    }
                }
            } ?>



            <? if ($submitbreeds != "true" OR $showinputfield == "true") { ?>
                <br>
                <p class="blogodd">To update the database of pet breeds, follow these steps:
                <ul>
                    <li><p class="blogodd">Download the latest version of <a href="https://www.curseforge.com/wow/addons/battle_pet_breedid" class="wowhead" target="_blank">Battle Pet BreedID</a></li>
                    <li><p class="blogodd">Locate the file <i>PetData.lua</i> and open it with a text editor.</a></li>
                    <li><p class="blogodd">Paste the entire content into the form below and press Submit.</a></li>
                </ul>
                <br>
                <form action="index.php?page=adm_breeds" method="POST">
                    <input type="hidden" name="submitbreeds" value="true">
                    <textarea class="cominputbright" name="breedinfo" style="height: 300px; width: 700px;" required="true"></textarea><br> <br>
                    <input class="cominputmedium"  type="submit" value="<? echo _("FormComButtonSubmit"); ?>">
                 </form>
            <? } ?>







        </td>
    </tr>

</table>

</table>







</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
mysqli_close($dbcon);
echo "</body>";
die;