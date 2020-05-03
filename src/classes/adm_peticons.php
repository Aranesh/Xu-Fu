<?php
require_once ('Growl.php');
require_once ('Database.php');

$command = \HTTP\argument_POST_or_GET_or_default ('command', FALSE);


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

<td width="100%"><h class="megatitle">Administration - Pet Icons</h></td>
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

    <? print_admin_menu('adm_peticons'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">


<?
if ($command == "uploadicon") {
 $target_dir = "uploads/";
 $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
 $uploadOk = 1;
 $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

 if ($_FILES["fileToUpload"]["name"] == "") {
  Growl_show_error ( 'There was a problem uploading the file. Please try again.', 5000);
  $uploaderror = TRUE;
 }
 if ($uploaderror != TRUE) {
  $imageFileType = strtolower($imageFileType);
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
   Growl_show_error ( 'Only images with the format JPG, JPEG or PNG can be accepted.', 5000);
   $uploaderror = TRUE;
  }
 }
 if ($uploaderror != TRUE) {
  $picsizes = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if ($picsizes[0] == "") {
   Growl_show_error ( 'There was a problem uploading the file. Please try again.', 5000);
   $uploaderror = TRUE;
  }
 }
 if ($uploaderror != TRUE) {
  if ($picsizes[0] != $picsizes[1]) {
   Growl_show_error ( 'The image you uploaded is not square. It has the dimensions: '.$picsizes[0].'x'.$picsizes[1].' pixels.<br>Please upload a square image.', 5000);
   $uploaderror = TRUE;
  }
 }
 if ($uploaderror != TRUE) {
  if ($picsizes[0] < 50 OR $picsizes[1] < 50) {
   Growl_show_error ( 'The image you uploaded is too small. Please make sure it is at least 50x50 pixels in dimensions.', 5000);
   $uploaderror = TRUE;
  }
 }
 if ($uploaderror != TRUE) {
  $target = "images/pets/".$_POST['petid'].".png";
  $targetthumb = "images/pets/resize50/".$_POST['petid'].".png";
  switch ($imageFileType) {
   case 'jpg':
   case 'jpeg':
      $src = imagecreatefromjpeg($_FILES["fileToUpload"]["tmp_name"]);
   break;
   case 'png':
      $src = imagecreatefrompng($_FILES["fileToUpload"]["tmp_name"]);
   break;
  }
  if ($picsizes[0] >= "150") {
   $dst = ImageCreateTrueColor('150','150');
   imagecopyresampled($dst,$src,0,0,0,0,150,150,$picsizes[0],$picsizes[0]);
  }
  else {
   $dst = ImageCreateTrueColor($picsizes[0],$picsizes[0]);
   imagecopyresampled($dst,$src,0,0,0,0,$picsizes[0],$picsizes[0],$picsizes[0],$picsizes[0]);
  }
  imagepng($dst,$target);
  $dst = ImageCreateTrueColor('50','50');
  imagecopyresampled($dst,$src,0,0,0,0,50,50,$picsizes[0],$picsizes[0]);
  imagepng($dst,$targetthumb);
  imagedestroy($dst);
  imagedestroy($src);
   
  Growl_show_notice ( 'Image uploaded successfully:<br><br><center><img src='.$targetthumb.'>', 5000);
 }
} 



$petsdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID > '20' ORDER BY RematchID DESC");

while ($thispet =  mysqli_fetch_object($petsdb)) {
 $thisid = $thispet->RematchID;
 $iconpets[$thispet->PetID]['PetID'] = $thispet->PetID;
 $iconpets[$thispet->PetID]['Species'] = $thispet->RematchID;
 $iconpets[$thispet->PetID]['Name'] = $thispet->Name;
 $iconpets[$thispet->PetID]['Family'] = $thispet->Family;
 $iconpets[$thispet->PetID]['Fights'] = Database_query_single("SELECT COUNT(*) FROM Alternatives WHERE PetID1 = $thisid OR PetID2 = $thisid OR PetID3 = $thisid");
}

$dirrand = "images/pets";
$dhrand = opendir($dirrand);
while (false !== ($filename = readdir($dhrand))) {

$filesplits = explode(".",$filename);

if ($filesplits[1] == "png" && $filesplits[0] >= "25" && preg_match("/^[1234567890]*$/is", $filesplits[0])){
 list($width, $height) = getimagesize('images/pets/'.$filesplits[0].'.png');
 $iconpets[$filesplits[0]]['Icon'] = TRUE;
 if ($width < "150") {
  $iconpets[$filesplits[0]]['Quality'] = "Low-Resolution";
 }
 else {
  $iconpets[$filesplits[0]]['Quality'] = "Good";
 }
}
} ?>

<br>
<p class="blogodd">
Pet images need to be square format and at least 50x50 pixel. Ideally they should be 150x150 for best results. If they are larger than 150x150, they will be resized, which might lead to a bit of quality loss.<br>
Accepted file formats: JPG, JPEG and PNG.<br>
<b>Careful please, replacing an image overwrites the previous one!</b>

<table width="100%" id="t1" style="border-collapse: collapse;" class="admin example table-autosort table-autofilter table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
 
 <thead>
<tr class="admin">
    <th class="admin"><p class="blogodd"></th>
    <th class="admin"><p class="blogodd"></th>
    <th class="admin table-sortable:alphabetic" style="cursor: pointer"><p class="blogodd">Family</th>
    <th class="admin table-sortable:alphabetic" style="cursor: pointer"><p class="blogodd">Status</th>
    <th class="admin"><p class="blogodd"></th>
    <th class="admin"><p class="blogodd"></th>
    <th class="admin"><p class="blogodd"></th>
</tr>
<tr class="admin">
    <th class="admin table-sortable:numeric" style="cursor: pointer"><p class="blogodd">Species</th>
    <th class="admin table-sortable:alphabetic" style="cursor: pointer"><p class="blogodd">Name</th>
    <th align="center" style="padding-right: 8px" class="admin">
     <select class="petselect" onchange="Table.filter(this,this);">
      <option class="petselect" value="">All</option>
      <option class="petselect" value="Humanoid">Humanoid</option>
      <option class="petselect" value="Dragonkin">Dragonkin</option>
      <option class="petselect" value="Flying">Flying</option>
      <option class="petselect" value="Undead">Undead</option>
      <option class="petselect" value="Critter">Critter</option>
      <option class="petselect" value="Magic">Magic</option>
      <option class="petselect" value="Elemental">Elemental</option>
      <option class="petselect" value="Beast">Beast</option>
      <option class="petselect" value="Aquatic">Aquatic</option>
      <option class="petselect" value="Mechanical">Mechanical</option>
     </select>
    </th>
    <th align="center" style="padding-right: 8px" class="admin">
     <select class="petselect" onchange="Table.filter(this,this);">
      <option class="petselect" value="">All</option>
      <option class="petselect" value="Good">Good</option>
      <option class="petselect" value="Low-Resolution">Low-Resolution</option>
      <option class="petselect" value="Missing">Missing</option>
     </select>
    </th>
    <th class="admin table-sortable:numeric" style="cursor: pointer"><p class="blogodd">Fights</th>
    <th class="admin"><p class="blogodd">Link</th>
    <th class="admin"><p class="blogodd">Options</th>
</tr>
</thead>
 <tbody>
<?

foreach ($iconpets as $key => $value) { ?>
 <tr class="admin<? if ($value['Icon'] != TRUE) echo 'red'; if ($value['Quality'] == "Low-Resolution") echo 'orange'; if ($value['Quality'] == "Good") echo 'green'; ?>">
    <td class="admin" style="text-align: center"><? echo $value['Species'] ?></td>
    <td class="admin"><a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wowhead.com/npc=<? echo $value['PetID'] ?>'><? echo $value['Name'] ?></a></td>
    <td class="admin" style="text-align: center"><? echo $value['Family'] ?></td>
    <td class="admin" style="text-align: center"><? if ($value['Icon'] == TRUE) { echo $value['Quality']; } else echo "Missing"; ?></td>
    <td class="admin" style="text-align: center"><? echo $value['Fights'] ?></td>
    <td class="admin" style="text-align: center"><?
     if ($value['Icon'] == TRUE) { 
      echo "<a class='pr_contact' style='line-height: 14px;' target='_blank' href='http://www.wow-petguide.com/images/pets/".$value['PetID'].".png'>View</a>" ?>
    <? } else echo "-"; ?>
    </td>
    <td class="admin" style="text-align: center">
     <form style="display: inline;" action="index.php" method="post" enctype="multipart/form-data">
     <input type="hidden" name="page" value="adm_peticons">
     <input type="hidden" name="command" value="uploadicon">
     <input type="hidden" name="petid" value="<? echo $value['PetID'] ?>">
     <input type="file" name="fileToUpload" id="fileToUpload" class="inputfile" required>
     <input type="submit" class="cominputmedium" style="padding: 2px; margin:0px" value="<? if ($value['Icon'] == TRUE) echo 'Replace'; else echo 'Upload'; ?>" name="submit">
     </form>
    </td>
 
<? }
mysqli_close($dbcon);
echo " </tbody></table></body>";
die;
