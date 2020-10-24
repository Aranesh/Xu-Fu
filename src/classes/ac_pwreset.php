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
$pwresetfailreason = __("No user with that name or email address found.");
}



// Fail reason: user given but no email address entered

if ($checkuser && $checkuser->Email == ""){
$pwresetfail = "true";
$pwresetfailreason = __("There is no email address associated with this account.");
}



// Fail reason: too many password reset attempts within the last 30min (max 3)

$resetsdb = mysqli_query($dbcon, "SELECT * FROM UserProtocol WHERE User = '$checkuser->id' AND Activity = 'Password Reset Mail Requested' AND  Date >= NOW()- INTERVAL 30 MINUTE");

if (mysqli_num_rows($resetsdb) >= 36) {
$pwresetfail = "true";
$pwresetfailreason = __("There were too many attempts to reset the password of this account.<br>The function is now blocked for 30 minutes.");
}


}






// Actual reset of password happens now

if ($pwresetfail == "false"){

   $resetcode = strtoupper(substr(md5(rand()), 0, 5));
   $eintragen = mysqli_query($dbcon, "INSERT INTO UserProtocol (`User`, `IP`, `Priority`, `Activity`, `Comment`) VALUES ('$checkuser->id', '$user_ip_adress', '0', 'Password Reset Mail Requested', '$resetcode')") OR die(mysqli_error($dbcon));

   $update = mysqli_query($dbcon, "UPDATE Users SET `ResetCode` = '$resetcode' WHERE id = '$checkuser->id'");

   $recipient = $checkuser->Email;
   $recname = $checkuser->Name;
   $subject = "Password reset for WoW-Petguide.com";

   $content = '<br>'.__("Sorry to hear you have problems logging in. To set a new password for your account, please follow this link:").'<br><br><center><a style="display: inline;font-size:18px;font-family: Verdana,sans-serif" href="https://wow-petguide.com/index.php?page=setpw&pwstring='.$resetcode.'">'.__("Set new password").'</a>';
   $content = $content.'<br><br></center><p style="display: inline;font-size:14px;font-family: Verdana,sans-serif">'.__("This reset code is valid for 24 hours. If you need any additional help, you can use the").' <a style="display: inline;font-size:14px;font-family: Verdana,sans-serif" href="https://wow-petguide.com/index.php?page=acretrieve">'.__("account retrieval page").'</a>.<br>'.__("If you did not request this email, you can safely ignore it.<br><br>Yours,");

   $nonhtmlbody = __("Sorry to hear you have problems logging in. To set a new password for your account, please follow this link:")." https://wow-petguide.com/index.php?page=setpw&pwstring=".$resetcode;

   xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody, '');

   $pwresetsuccess = "true";
}


?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><?php echo __("Recover your Password") ?></h></td>
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
            <?php echo __("Please enter your username or email below. If you registered your email address when signing up, an email will be sent to you to reset your password.") ?>
            </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?m=<?php echo $mainshow ?>&s=<?php echo $subselector ?>&a=<?php echo $alternative ?>" method="post">

<table>
<tr>
<td align="right"><p class="blogodd"><b><?php if ($pwresetfail == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Name or email") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="1" placeholder="" type="text" name="username" value="<?php echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" required>
</td>
</tr>

<?
if ($pwresetfail == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$pwresetfailreason.'<br><a href="index.php?page=acretrieve&m='.$mainshow.'&s='.$subselector.'&a='.$alternative.'" class="loginbright loginlinklarger">'.__("Click here to retrieve your account.").'</a></div></td></tr>';
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
                    <p class="blogodd"><button type="submit" tabindex="2" class="comedit" name="page" value="pwrecover"><?php echo __("Recover password") ?> </button></form> <?php echo __("or") ?>
                </td>
                <form class="form-style-login" action="index.php?m=<?php echo $mainshow ?>&s=<?php echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
                <td>
                    <p class="blogeven"><button tabindex="3" type="submit" class="comdelete"><?php echo __("Cancel") ?></button>
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
            <?php echo __("An email was sent to your registered email address. Please follow the instructions in that mail to recover your account!<br> If you did not receive the email, please check your spam folder.<br><br>You will be redirect back to your previous page in <span id=\"counter\">10</span> seconds.") ?></p></td>

<META HTTP-EQUIV="refresh" CONTENT="10; URL=index.php?m=<?php echo $mainshow ?>&s=<?php echo $subselector ?>&a=<?php echo $alternative ?>">
<script type="text/javascript">
function countdown() {
    var i = document.getElementById('counter');
    if (parseInt(i.innerHTML)<=0) {
        location.href = 'https://www.wow-petguide.com/index.php?m=<?php echo $mainshow ?>&s=<?php echo $subselector ?>&a=<?php echo $alternative ?>';
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