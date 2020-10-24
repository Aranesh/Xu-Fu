<?php


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
    <img class="ut_icon" width="84" height="84" <?php echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle">Administration</h></td>
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

    <?php print_admin_menu('main'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">
            <br>

                <p class="blogodd" style="font-weight: bold">Strategies</b></p><br>
                <p class="blogodd">Lists all strategies available on the entire page with filters.
                <br><br>
                
            <?php if ($userrights['Edit_Menu'] == "yes") { ?>
                <p class="blogodd" style="font-weight: bold">Navigation Menu</b></p><br>
                <p class="blogodd">Edit the top menu items. Add new categories and fights or reorder them.
                <br><br>
            <?php } ?>
            
            <?php if ($userrights['AdmPetImport'] == "on") { ?>
                <p class="blogodd" style="font-weight: bold">Pet Data Import</b></p><br>
                <p class="blogodd">Pet Data for Xu-Fu comes from various sources. The main one is the Blizzard API. Through these menus you can control the import from the API and make manual adjustments.
                <br><br>
            <?php } ?>

            <?php /* if ($userrights['AdmBreeds'] == "on") { ?>
                <p class="blogodd" style="font-weight: bold">Breed Importer</b></p><br>
                <p class="blogodd">Update the breed database using an import function for the addon Pet Battle Breed ID.
                <br><br>
            <?php } */ ?>

            <?php if ($userrights['AdmPeticons'] == "on") { ?>
                <p class="blogodd" style="font-weight: bold">Pet Icons</b></p><br>
                <p class="blogodd">Add or update the icons stored for pets.
                <br><br>
            <?php } ?>

            <?php if ($userrights['AdmImages'] == "on") { ?>
                <p class="blogodd" style="font-weight: bold">Article Images</b></p><br>
                <p class="blogodd">Manage the image database for all images used in articles.
                <br><br>
            <?php } ?>
        <br>
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