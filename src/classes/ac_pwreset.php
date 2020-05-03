<?php

$subname = mysqli_real_escape_string($dbcon, $_POST['username']);

if ($subname){
$pwresetfail = "false";


// Check database for users
$userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Name = '$subname'");
$usernum = mysqli_num_rows($userdb);

$usermaildb = mysqli_query($dbcon, "SELECT * FROM Users WHERE Email = '$subname'");
$usermailnum = mysqli_num_rows($usermaildb);


if ($usernum > "0") {
$checkuser = mysqli_fetch_object($userdb);
}
if ($usermailnum > "0") {
$checkuser = mysqli_fetch_object($usermaildb);
}


// Fail reason: No user found

if (!$checkuser) {
$pwresetfail = "true";
$pwresetfailreason = _("PWR_NoUser");
}



// Fail reason: user given but no email address entered

if ($checkuser && $checkuser->Email == ""){
$pwresetfail = "true";
$pwresetfailreason = _("PWR_NoEmail");
}



// Fail reason: too many password reset attempts within the last 30min (max 3)

$resetsdb = mysqli_query($dbcon, "SELECT * FROM UserProtocol WHERE User = '$checkuser->id' AND Activity = 'Password Reset Mail Requested' AND  Date >= NOW()- INTERVAL 30 MINUTE");

if (mysqli_num_rows($resetsdb) >= "3") {
$pwresetfail = "true";
$pwresetfailreason = _("PWR_Locked");
}


}






// Actual reset of password happens now

if ($pwresetfail == "false"){

   $resetcode = strtoupper(substr(md5(rand()), 0, 5));
   $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`, `Main`, `Sub`, `Alternative`) VALUES ('$checkuser->id', '$user_ip_adress', '0', 'Password Reset Mail Requested', '$resetcode', '$mainselector', '$subselector', '$alternative')") OR die(mysqli_error($dbcon));

   $update = mysqli_query($dbcon, "UPDATE Users SET `ResetCode` = '$resetcode' WHERE id = '$checkuser->id'");

   $recipient = $checkuser->Email;
   $recname = $checkuser->Name;
   $subject = "Password reset for WoW-Petguide.com";

   $content = '<br>'._("PWR_EMCont1").'<br><br><center><a style="display: inline;font-size:18px;font-family: Verdana,sans-serif" href="https://wow-petguide.com/index.php?page=setpw&pwstring='.$resetcode.'">'._("PWR_EMCont2").'</a>';
   $content = $content.'<br><br></center><p style="display: inline;font-size:14px;font-family: Verdana,sans-serif">'._("PWR_EMCont3").' <a style="display: inline;font-size:14px;font-family: Verdana,sans-serif" href="https://wow-petguide.com/index.php?page=acretrieve">'._("PWR_EMCont4").'</a>.<br>'._("PWR_EMCont5");

   $nonhtmlbody = _("PWR_EMCont1")." https://wow-petguide.com/index.php?page=setpw&pwstring=".$resetcode;

   xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody);

   $pwresetsuccess = "true";
}


?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><? echo _("PWR_PGTitle") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>

<div class="blogentryfirst">

<div class="articlebottom">
</div>
<center>



<?php
if ($pwresetsuccess != "true"){
?>


<table width="75%" border="0">
<tr>
<td>
    <center>
    <p class="blogodd">

    <table width="50%"><tr>
        <td><img src="/images/xufu_small.png"></td>
        <td><img src="/images/blank.png" width="10"></td>
        <td valign="top"><p class="blogodd"><br>
            <? echo _("PWR_Instruct") ?>
            </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" method="post">

<table>
<tr>
<td align="right"><p class="blogodd"><b><? if ($pwresetfail == "true"){ echo "<font color=\"red\">"; } ?><? echo _("UL_LogName") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="1" placeholder="" type="text" name="username" value="<? echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" required>
</td>
</tr>

<?
if ($pwresetfail == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$pwresetfailreason.'<br><a href="index.php?page=acretrieve&m='.$mainshow.'&s='.$subselector.'&a='.$alternative.'" class="loginbright loginlinklarger">'._("UL_RetrievAcc").'</a></div></td></tr>';
}
?>

<tr>
    <td colspan="4"><img src="images/blank.png" width="1" height="15"></td>
</tr>

<tr>

<td colspan="2"></td>
    <td colspan="4">

            <table border="0">
            <tr>
                <td>
                    <p class="blogodd"><button type="submit" tabindex="2" class="myGreenButton" name="page" value="pwrecover"><? echo _("PWR_BTReco") ?> </button></form> <? echo _("UL_MBor") ?>
                </td>
                <form class="form-style-login" action="index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
                <td>
                    <p class="blogeven"><button tabindex="3" type="submit" class="myRedButton"><? echo _("FormButtonCancel") ?></button>
                </td>
            </tr>
        </table>
    </td>

</tr>


<?
}
else if ($pwresetsuccess == "true"){
?>
<br><br><br><br>
<table width="75%" border="0">
<tr>
<td>
    <center>
    <p class="blogodd">

    <table width="50%"><tr>
        <td><img src="/images/xufu_small.png"></td>
        <td><img src="/images/blank.png" width="10"></td>
        <td valign="top"><p class="blogodd">
            <? echo _("PWR_Success") ?></p></td>

<META HTTP-EQUIV="refresh" CONTENT="10; URL=index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>">
<script type="text/javascript">
function countdown() {
    var i = document.getElementById('counter');
    if (parseInt(i.innerHTML)<=0) {
        location.href = 'https://www.wow-petguide.com/index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>';
    }
    i.innerHTML = parseInt(i.innerHTML)-1;
}
setInterval(function(){ countdown(); },1000);
</script>

        </tr>
    </table>

<?
}
?>

</table>
</form>

<br><br><br><br><br><br>
</td></tr>
</table>

</div>


<?
mysqli_close($dbcon);
die;
