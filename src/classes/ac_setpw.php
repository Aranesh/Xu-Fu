<?php



?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><? echo _("RP_Title") ?></h></td>
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
        <? echo _("RP_ErrInvLink1") ?></td></tr>

<tr><td></td><td></td><td>
<ul>
<li><? echo _("RP_ErrInvLink2") ?></li>
<li><? echo _("RP_ErrInvLink3") ?></li>
</ul>
<? echo _("RP_ErrInvLink4") ?> <a href="index.php?page=acretrieve" class="wowhead"><? echo _("PWR_EMCont4") ?></a>.
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
        <? echo _("RP_NPPrompt") ?><br>
        <? echo _("RG_PassRest1") ?><br>
        </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?page=setpw&pwstring=<? echo $pwstring ?>" method="post">
    <input type="hidden" name="submitpw" value="true">

<table>

<tr>
<td align="right"><p class="blogodd"><b><? if ($setpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RP_NPField") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="2" placeholder="" type="password" id="passwordy" name="password" required></td>
</tr>



</td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><? if ($setpasserror == "true"){ echo "<font color=\"red\">"; } ?><? echo _("RP_NPRepPW") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="3" placeholder="" type="password" name="passwordrep" id="passwordz" required></td>
</tr>


<?
if ($setpasserror == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$setpassprob.'</div></td></td></tr>';
}
?>


<tr><td></td><td></td><td><div id="capsWarningz" class="registerError"><p class="commenteven"><? echo _("UL_CapsOn") ?></div></td></td></tr>



<tr>
    <td colspan="4"><img src="images/blank.png" width="1" height="15"></td>
</tr>

<tr>

<td colspan="2"></td>
    <td colspan="4">

            <table border="0">
            <tr>
                <td>
                    <p class="blogodd"><button type="submit" tabindex="5" class="myGreenButton"><? echo _("RP_Save") ?></button></form>
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
<? echo _("RP_PWChanged1") ?><br>

<br><? echo _("RP_PWChanged2") ?></p></td>

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