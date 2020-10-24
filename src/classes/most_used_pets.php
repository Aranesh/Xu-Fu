<?php
require_once ('HTTP.php');
require_once ('Database.php');

// =============  Header with Page Title  ============= ?>

<div class="blogtitle">
    <table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><img src="https://www.wow-petguide.com/images/main_bg02_1.png"></td>
            <td width="100%"><h class="megatitle"><?php echo __("Most Used Pets") ?></font></td>
            <td><img src="images/main_bg02_2.png"></td>
        </tr>
    </table>
</div>

<div class="article">
    <div class="articlebottom"></div>
<div>
    <center>
    <p class="blogodd"><br>
    <br>
    This tool loads a list of pets from the webpage that are used the most often.<br><br>
    Filters top:<br>
    PvP (off)<br>
    Special categories (falcos, elekk plush) (off)<br>
    <br>
    Extended menu:<br>
    All subcategories<br>



<div style="height: 150px">
<input type="checkbox" class="top_pets_toggler" style="background-image: url(../images/toppets_toggle_pvp.png);" id="check_one">

<input type="checkbox" class="top_pets_toggler" style="background-image: url(../images/toppets_toggle_falco.png);" id="check_one">

<input type="checkbox" class="top_pets_toggler" style="background-image: url(../images/toppets_toggle_elekk.png);" id="check_one">
</div>





<br>
<div class="maincomment">
    <br>
    <table class="maincomseven" width="100%" cellspacing="0" cellpadding="0" style="background-color:4D4D4D" align="center">
    <tr><td width="100%" align="center">
    <br><br>
    <?
    
    // ==== COMMENT SYSTEM 2.0 FOR MAIN ARTICLES HAPPENS HERE ====
    print_comments_outer("0",$mainselector,"medium");
echo "</div>";