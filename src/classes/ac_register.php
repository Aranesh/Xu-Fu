<?php
?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><? echo _("RG_Title") ?></h></td>
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
            <? echo _("RG_Instructions") ?>
            </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?page=register&m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
    <input type="hidden" name="signup" value="true">

<table>
<tr>
<td align="right"><p class="blogodd"><b><? if ($regnameerror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RG_HNick") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="1" placeholder="" type="text" name="username" value="<? echo stripslashes(htmlentities($subname, ENT_QUOTES, "UTF-8")); ?>" maxlength="15" required>
</td>
<td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
    <em><? echo _("RG_HNick") ?></em>
    <? echo _("RG_NickDesc") ?><br>
    <ul>
        <li><? echo _("RG_NickRest1") ?></li>
        <li><? echo _("RG_NickRest2") ?> # < > [ ] | { } " ' / \ $ ?</li>
        <li><? echo _("RG_NickRest3") ?></li>
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
<td align="right"><p class="blogodd"><b><? if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("UL_LogPass") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="2" placeholder="" type="password" id="passwordy" name="password" required></td>
<td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?<span class="alternativespan">
    <img class="xufu" src="images/xufu_tooltip.png" alt="Alternative 3" height="48" width="48" />
    <em><? echo _("UL_LogPass") ?></em>
    <? echo _("RG_PassRest1") ?><br><br>
    <? echo _("RG_PassRest2") ?><br>
</span></a>

</td>
</tr>



</td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><? if ($regpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RG_RepPass") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="3" placeholder="" type="password" name="passwordrep" id="passwordz" required></td>
</tr>


<?
if ($regpasserror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$regpassprob.'</div></td></td></tr>';
}
?>


<tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><? echo _("UL_CapsOn") ?></div></td></td></tr>






<tr>
    <td colspan="3"><img src="images/blank.png" width="1" height="7"></td>
</tr>

<td align="right"><p class="blogodd"><b><? if ($regmailerror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RG_RepMail2") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="4" placeholder="" type="text" name="email" value="<? echo stripslashes(htmlentities($submail, ENT_QUOTES, "UTF-8")); ?>" ></td>
<td><a class="alternativessmall" style="display:block;" href="#"><font size="3">?</font><span class="alternativespan">
    <img class="xufu" src="images/xufu_tooltip.png" height="48" width="48" />
    <em><? echo _("RG_RepMail1") ?></em>
    <? echo _("RG_MailInfo1") ?><br>
    <? echo _("RG_MailInfo2") ?>
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
                    <p class="blogodd"><button type="submit" tabindex="5" class="comedit" name="page" value="register"><? echo _("UL_MBRegister") ?></button></form> <? echo _("UL_MBor") ?>
                </td>
                <form class="form-style-login" action="index.php?m=<? echo $mainshow ?>&s=<? echo $subselector ?>&a=<?php echo $alternative ?>" method="post">
                <td>
                    <p class="blogeven"><button tabindex="6" type="submit" class="comdelete"><? echo _("FormButtonCancel") ?></button>
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
