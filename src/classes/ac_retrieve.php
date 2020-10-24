<?php

$retrievepage = "retrieveform";

$retrievesubmit = $_POST['retrievesubmit'];
if ($retrievesubmit == "true"){
$submail = $_POST['email'];
$subtext = $_POST['acrecovery'];

if (!filter_var($submail, FILTER_VALIDATE_EMAIL)) {
$acretrievefail = "true";
$acretrievefailreason = __("Please enter a valid email address.");
}

$submail = mysqli_real_escape_string($dbcon, $submail);
$submail = stripslashes(htmlentities($submail, ENT_QUOTES, "UTF-8"));
$subtext = stripslashes(htmlentities($subtext, ENT_QUOTES, "UTF-8"));

$subsendtext = $_POST['acrecovery'];
$subsendtext = htmlspecialchars($subsendtext, ENT_QUOTES);
$subsendtext = stripslashes($subsendtext);
$subsendtext = nl2br($subsendtext);

if ($acretrievefail != "true" ){

    // Send message to admin
    $recipient = "xufu@wow-petguide.com";
    $recname = "Aranesh";
    $subject = "! - Account Retrieval Request - !".$mtime;

    $content = '<br>An account retrieval request was sent from <b>'.$submail.'</b><br><br>Below is the information that was submitted: <br><br>';
    $content = $content.'<hr><i>'.$subsendtext.'</i><hr><br><br><p style="display: inline;font-size:14px;font-family: Verdana,sans-serif">Yours,';

    $nonhtmlbody = "";

    xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody, '');


    // Send confirmation mail to Requester
    $recipient = $submail;
    $recname = "";
    $subject = __("Account Retrieval Request");

    $content = '<br>'.__("WoW-Petguide.com received an account retrieval request from your email address.<br> If you did not send any request, you can safely ignore this email. <br><br>Below is the information that was submitted:").' <br><br>';
    $content = $content.'<hr><i>'.$subsendtext.'</i><hr><br><br><p style="display: inline;font-size:14px;font-family: Verdana,sans-serif">'.__("Yours,");

    $nonhtmlbody = __("WoW-Petguide.com received an account retrieval request from your email address.<br> If you did not send any request, you can safely ignore this email. <br><br>Below is the information that was submitted:").' '.$subtext;

    xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody, '');

    $retrievepage = "mailsent";
}




}







?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/main_bg02_1.png"></td>
<td width="100%"><center><h class="megatitle"><?php echo __("Retrieve Your Account") ?></h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>

<div class="blogentryfirst">

<div class="articlebottom">
</div>
<center>



<?php
if ($retrievepage == "retrieveform"){
?>


<table width="75%" border="0">
<tr>
<td>
    <center>
    <p class="blogodd">

    <table width="50%"><tr>
        <td valign="top"><img src="/images/xufu_small.png"></td>
        <td><img src="/images/blank.png" width="10"></td>
        <td valign="top"><p class="blogodd"><br>
            <?php echo __("If you are unable to login or recover your password, you can use below form to request help.") ?><br><br>
            <?php echo __("Please add every bit of info you might find relevant, such as your username, the icon you used, which character you linked through the armory or how many comments you wrote.<br> Anything you remember might help us recover your account for you. Please note that we cannot guarantee a successful retrieval of your account.") ?><br><br>
            </p></td>
        </tr>
    </table>

    <br>
    <br>

<form class="form-style-register" action="index.php?page=acretrieve" method="post">
    <input type="hidden" name="retrievesubmit" value="true">
<table>
<tr>
<td align="right"><p class="blogodd"><b><?php if ($pwresetfail == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Email") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><input tabindex="1" placeholder="" type="text" name="email" value="<?php echo $submail ?>" maxlength="50" required>
</td>
</tr>

<?
if ($acretrievefail == "true"){
echo '<tr><td></td><td></td><td><div class="registerError"><p class="commenteven">'.$acretrievefailreason.'</div></td></tr>';
}
?>

<tr>
    <td colspan="4"><img src="images/blank.png" width="1" height="15"></td>
</tr>

<tr>
<td align="right"><p class="blogodd"><b><?php if ($pwresetfail == "true"){ echo "<font color=\"red\">"; } ?><?php echo __("Your details") ?>:</b></td>
<td><img src="images/blank.png" width="5" height="1"/></td>
<td><textarea tabindex="2" name="acrecovery" rows="4" onClick="auto_adjust_textarea_size(this)" onkeyup="auto_adjust_textarea_size(this)" required="true" required><?php echo $subtext ?></textarea>
</td>
</tr>


<tr>

<td colspan="2"></td>
    <td colspan="4">

            <table border="0">
            <tr>
                <td>
                    <p class="blogodd"><button type="submit" tabindex="3" class="comedit"><?php echo __("Send Retrieval request") ?></button></form> <?php echo __("or") ?>
                </td>
                <form class="form-style-login" action="index.php" method="post">
                <td>
                    <p class="blogeven"><button tabindex="4" type="submit" class="comdelete"><?php echo __("Cancel") ?></button></form>
                </td>
            </tr>
        </table>
    </td>

</tr>
</table>
<br><br><br><br><br>
<?
die;
}



if ($retrievepage == "mailsent"){
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
<?php echo __("Your request was sent successfully. We will get back to you as soon as possible.<br><br>A copy of your request was also sent to your email address as confirmation.") ?>
<br><br>
<?php echo __("You will be redirected to the front page in <span id=\"counter\">10</span> seconds.") ?></p></td>

<META HTTP-EQUIV="refresh" CONTENT="10; URL=index.php?m=Home">
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