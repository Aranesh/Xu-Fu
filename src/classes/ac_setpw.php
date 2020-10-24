<?php



?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><?php echo __("Password Retrieval") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>

<div class="blogentryfirst">

<div class="articlebottom">
</div>
<center>


<?php
if ($resetpage == "invalid") {
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
        <?php echo __("I am sorry, the link to reset your password is not valid.<br>Possible reasons are:") ?></td></tr>

<tr><td></td><td></td><td>
<ul>
<li><?php echo __("Another password reset was requested in the meanwhile. Only the most recent link is valid.") ?></li>
<li><?php echo __("Password reset links expire after 24 hours. If this mail is older, please request a new one.") ?></li>
</ul>
<?php echo __("Should you have any more trouble, please use the ") ?> <a href="index.php?page=acretrieve" class="wowhead"><?php echo __("account retrieval page") ?></a>.
            </p></td>
        </tr>
    </table>


<br><br><br><br><br>


<?php
die;
}



if ($resetpage == "enterpw") {
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
        <?php echo __("Please enter a new password for your account below.") ?><br>
        <?php echo __("Your password has to be at least 6 characters long. It is advisable to use a complex password.") ?><br>
        </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?page=setpw&pwstring=<?php echo $pwstring ?>" method="post">
    <input type="hidden" name="submitpw" value="true">

<table>

<tr>
<td align="right"><p class="blogodd"><b><?php if ($setpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("New password") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="2" placeholder="" type="password" id="passwordy" name="password" required></td>
</tr>



</td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><?php if ($setpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Repeat password") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="3" placeholder="" type="password" name="passwordrep" id="passwordz" required></td>
</tr>


<?
if ($setpasserror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$setpassprob.'</div></td></td></tr>';
}
?>


<tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><?php echo __("Warning! Caps Lock is on!") ?></div></td></td></tr>



<tr>
    <td colspan="4"><img src="images/blank.png" width="1" height="15"></td>
</tr>

<tr>

<td colspan="2"></td>
    <td colspan="4">

            <table border="0">
            <tr>
                <td>
                    <p class="blogodd"><button type="submit" tabindex="5" class="myGreenButton"><?php echo __("Save Password") ?></button></form>
                </td>
            </tr>
        </table>
    </td>

</tr>




</table>
</form>

<br><br><br><br><br><br>



</td></tr>
</table>

</div>


<?
die;
}






if ($resetpage == "pwset") {
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
<?php echo __("Your password was updated successfully!") ?><br>

<br><?php echo __("You have been logged in and will be redirected to the front page in <span id=\"counter\">5</span> seconds.") ?></p></td>

<META HTTP-EQUIV="refresh" CONTENT="5; URL=index.php?m=Home">
<script type="text/javascript">
function countdown() {
    var i = document.getElementById('counter');
    if (parseInt(i.innerHTML)<=0) {
        location.href = 'https://www.wow-petguide.com/index.php?m=Home';
    }
    i.innerHTML = parseInt(i.innerHTML)-1;
}
setInterval(function(){ countdown(); },1000);
</script>

</td>
  </tr>
    </table>


<br><br><br><br><br>



<?php
mysqli_close($dbcon);
die;
}