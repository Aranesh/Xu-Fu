<?php
?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><? echo _("UL_ErrHead") ?></h></td>
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

    <table width="50%"><tr>
        <td><img src="/images/xufu_sad.png"></td>
        <td><img src="/images/blank.png" width="10"></td>
        <td valign="top"><p class="blogodd"><br>
            <? echo _("UL_ErrDesc") ?>
            </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
    <input type="hidden" name="signup" value="true">

<table>
<tr>
<td align="right"><p class="blogodd"><b><? if ($loginnamefail == "true"){ echo "<font color=\"red\">"; } ?><? echo _("UL_LogName") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="1" placeholder="" type="text" name="username" value="<? echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" maxlength="250" required>
</td>
</tr>

<?
if ($loginnamefail == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$loginnamefailreason.'<br><a href="index.php?page=acretrieve&m='.$mainshow.'&s='.$subselector.'&a='.$alternative.'" class="loginbright loginlinklarger">'._("UL_RetrievAcc").'</a></div></td></tr>';
}
else {
?>

<tr>
    <td colspan="2"></td>
    <td><a href="index.php?page=acretrieve&m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" class="logindark"><? echo _("UL_ForgUsername") ?></a></td>
</tr>

<?
}
?>




 <tr>
    <td colspan="3"><img src="images/blank.png" width="1" height="7"></td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><? if ($loginpassfail == "true"){ echo "<font color=\"red\">"; } ?><? echo _("UL_LogPass") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="2" placeholder="" type="password" id="passwordy" name="password" required>
</td>
</tr>


<?
if ($loginpassfail == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$loginpassreason.'<br><a href="index.php?page=pwrecover&m='.$mainshow.'&s='.$subselector.'&a='.$alternative.'" class="loginbright loginlinklarger">'._("UL_RetrievAcc").'</a></div></td></tr>';
}
else {
?>

<tr>
    <td colspan="2"></td>
    <td><a href="index.php?page=pwrecover&m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" class="logindark"><? echo _("UL_ForgPass") ?></a></td>
</tr>

<?
}
?>


<tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><? echo _("UL_CapsOn") ?></div></td></tr>




<tr>
        <td colspan="2"></td>
    <td valign="bottom"><p class="smallodd"><input tabindex="3" type="checkbox" name="remember" value="true" <? if ($loginremember == "true") { echo "checked";} ?>> <? echo _("UL_RemLogin") ?></td>
</tr>





<tr>
    <td colspan="4"><img src="images/blank.png" width="1" height="15"></td>
</tr>

<tr>

<td colspan="2"></td>
    <td colspan="4">

            <table border="0">
            <tr>
                <td>
                    <p class="blogodd"><button type="submit" tabindex="5" class="myGreenButton" name="page" value="login"><? echo _("UL_MBLogin") ?></button></form> <? echo _("UL_MBor") ?>
                </td>
                <form class="form-style-login" action="index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
                <td>
                    <p class="blogeven"><button tabindex="6" type="submit" class="myRedButton"><? echo _("FormButtonCancel") ?></button>
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