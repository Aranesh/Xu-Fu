<?php
?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><?php echo __("Register Your Account") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>

<div class="blogentryfirst">

<div class="articlebottom">
</div>
<center>

<table width="75%" border="0">
<tr>
<td>
    <center>
    <p class="blogodd">

    <table width="70%"><tr>
        <td><img src="/images/xufu_small.png"></td>
        <td><img src="/images/blank.png" width="10"></td>
        <td valign="top"><p class="blogodd">
            <?php echo __("Thank you for your interest!<br>Creating an account is super easy and will allow you to use all features of Xu-Fu's Pet Guides.") ?>
            </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?page=register&m=<?php echo $mainshow ?>&s=<?php echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
    <input type="hidden" name="signup" value="true">

<table>
<tr>
<td align="right"><p class="blogodd"><b><?php if ($regnameerror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Nickname") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="1" placeholder="" type="text" name="username" value="<?php echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" maxlength="15" required>
</td>
<td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
    <em><?php echo __("Nickname") ?></em>
    <?php echo __("Your nickname is the name under which your comments and info are shown.<br> The following restrictions are in place:") ?><br>
    <ul>
        <li><?php echo __("Usernames must be between <b>2</b> and <b>15</b> characters long.") ?></li>
        <li><?php echo __("Empty spaces and the following characters are not allowed:") ?> # < > [ ] | { } " ' / \ $ ?</li>
        <li><?php echo __("No offensive names :P") ?></li>
    </ul>
</span></a>
</td>
</tr>

<?
if ($regnameerror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regnameprob.'</div></td></td></tr>';
}
?>




 <tr>
    <td colspan="3"><img src="images/blank.png" width="1" height="7"></td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><?php if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Password") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="2" placeholder="" type="password" id="passwordy" name="password" required></td>
<td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
    <em><?php echo __("Password") ?></em>
    <?php echo __("Your password has to be at least 6 characters long. It is advisable to use a complex password.") ?><br><br>
    <?php echo __("Your password will be encrypted. No one, including the site admin, can see your real password.") ?><br>
</span></a>

</td>
</tr>



</td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><?php if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Repeat password") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="3" placeholder="" type="password" name="passwordrep" id="passwordz" required></td>
</tr>


<?
if ($regpasserror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regpassprob.'</div></td></td></tr>';
}
?>


<tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><?php echo __("Warning! Caps Lock is on!") ?></div></td></td></tr>






<tr>
    <td colspan="3"><img src="images/blank.png" width="1" height="7"></td>
</tr>

<td align="right"><p class="blogodd"><b><?php if ($regmailerror == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Email (optional)") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="4" placeholder="" type="text" name="email" value="<?php echo stripslashes(htmlentities($submail, ENT_QUOTES, "UTF-8")); ?>" ></td>
<td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?</font><span class="alternativespan">
    <img class="xufu" src="images/xufu_tooltip.png" height="48" width="48" />
    <em><?php echo __("Email") ?></em>
    <?php echo __("There is no requirement to enter your email address, however it is recommended in case you lose access to your account and need to reset your password.") ?><br>
    <?php echo __("If you enter an email address, it will never be published. It will also not be used to send you emails unless you request them (for example to recover your password or as a confirmation when you submit strategy suggestions).") ?>
</span></a>
</td>
</tr>




<?
if ($regmailerror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regmailprob.'</div></td></td></tr>';
}
?>


<?
/*
<tr>
    <td colspan="3"><img src="images/blank.png" width="1" height="7"></td>
</tr>

<td align="right" valign="top"><input tabindex="3" type="checkbox" name="data_consent" value="true"></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td colspan="2"><p class="smallodd">I allow Xu-Fu's Pet Guides to save the data entered into this form and on my personal user profile.<br>
<a class="pr_contact" href="https://www.wow-petguide.com/index.php?m=GDPR" target="_blank">Data Protection Info</a></td>
</tr>


<?
if ($regmailerror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regmailprob.'</div></td></td></tr>';
}
?>
*/
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
                    <p class="blogodd"><button type="submit" tabindex="5" class="comedit" name="page" value="register"><?php echo __("Register Account") ?></button></form> <?php echo __("or") ?>
                </td>
                <form class="form-style-login" action="index.php?m=<?php echo $mainshow ?>&s=<?php echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
                <td>
                    <p class="blogeven"><button tabindex="6" type="submit" class="comdelete"><?php echo __("Cancel") ?></button>
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
mysqli_close($dbcon);
die;