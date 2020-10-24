<?php

$submitbreeds = $_POST['submitbreeds'];



// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================




?>
<link rel="stylesheet" href="data/battletable.css">
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <?php echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle">Administration - Localization</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('loc');
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
<table style="width: 98%;" class="profile">

    <?php print_loc_menu('fights');
    
$locales = array(
    0 => array(
        'locale' => 'de_DE',
        'Name' => 'German'
    ),
    1 => array(
        'locale' => 'fr_FR',
        'Name' => 'French'
    ),
    2 => array(
        'locale' => 'it_IT',
        'Name' => 'Italian'
    ),
    3 => array(
        'locale' => 'es_ES',
        'Name' => 'Spanish'
    ),
    4 => array(
        'locale' => 'pl_PL',
        'Name' => 'Polish'
    ),
    5 => array(
        'locale' => 'pt_BR',
        'Name' => 'Portuguese'
    ),
    6 => array(
        'locale' => 'ru_RU',
        'Name' => 'Russian'
    ),
    7 => array(
        'locale' => 'ko_KR',
        'Name' => 'Korean'
    ),
    8 => array(
        'locale' => 'zh_TW',
        'Name' => 'Chinese'
    ),
);
    
    
    ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">
            <p class="smallodd">Quick guide: Activate the language(s) you want to translate to. <br>
            The word "Placeholder" does not need to be translated. If the title of a Placeholder is empty, the list item will be hidden. If you add a title, it will be shown as a header.<br>
            Don't forget to Save each line after adding your translation :-)</p>
                <table>
                    <tr>
                        <?php foreach ($locales as $locale) { ?>
                            <td><button class="articleedit_lng" id="atbt_<?php echo $locale['locale'] ?>" onclick="loc_toggle_lng('<?php echo $locale['locale'] ?>')"><?php echo $locale['Name'] ?></button></td>
                        <?php } ?>
                    </tr>
                </table>
                
                <table class="admin" style="margin: 0">
                    <tr>
                        <th class="admin">
                            English
                        </th>
                        <?php foreach ($locales as $locale) { ?>
                            <th class="admin field_<?php echo $locale['locale'] ?>" style="display: none">
                                <?php echo $locale['Name'] ?>
                            </th>
                        <?php } ?>
                        <th class="admin"></th>
                    </tr>
                <?
                $fights_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Parent = 0 ORDER BY Main, Prio ASC");
                while ($fight = mysqli_fetch_object ($fights_db))
                {
                    if ($fight->Main != $main_tracker OR !$main_tracker) {
                        $main_tracker = $fight->Main;
                        $main_headline_done = false;
                    }
                    if ($main_headline_done == false) {
                        $main_db = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = '$main_tracker'");
                        $main = mysqli_fetch_object ($main_db);
                        echo '<tr><th class="admin" style="background: #dfe281;" colspan="15">Category: '.$main->Name.'</th></tr>';
                        $main_headline_done = true;
                    }
                    ?>
                    <tr>
                        <td class="admin_top" style="width: 40%">
                            <b><a class="alternativessmall" style="margin-left: 0px; font-size: 13px;" href="?s=<?php echo $fight->id ?>" target="_blank"><?php echo stripslashes(htmlentities($fight->Name, ENT_QUOTES, "UTF-8")); ?></a></b>
                        </td>
                        <?php foreach ($locales as $locale) {
                            $ext_name = "Name_".$locale['locale']; ?>
                            <td class="admin_top field_<?php echo $locale['locale'] ?>" style="display: none">
                                <input type="text" maxlength="300" class="stredit_editline" style="width: 100%;" id="title_<?php echo $fight->id.'_'.$locale['locale'] ?>" value="<?php echo stripslashes(htmlentities($fight->{$ext_name}, ENT_QUOTES, "UTF-8")); ?>">
                            </td>
                        <?php } ?>

                        <td rowspan="2" class="admin_save" style="width: 10px">
                            <input class="cominputmedium" type="submit" value="Save" onclick="save_line('<?php echo $fight->id; ?>', '<?php echo $user->id; ?>', '<?php echo $user->ComSecret; ?>');">
                        </td>
                    </tr>
                        
                    <tr>
                        <td class="admin_bottom">
                            <?php echo stripslashes(htmlentities($fight->Comment, ENT_QUOTES, "UTF-8")); ?>
                        </td>
                        <?php foreach ($locales as $locale) {
                            $ext_comment = "Comment_".$locale['locale']; ?>
                            <td class="admin_bottom field_<?php echo $locale['locale'] ?>" style="display: none">
                                <input type="text" maxlength="1000" class="stredit_editline" style="width: 100%;" id="comment_<?php echo $fight->id.'_'.$locale['locale'] ?>" value="<?php echo stripslashes(htmlentities($fight->{$ext_comment}, ENT_QUOTES, "UTF-8")); ?>">
                            </td>
                        <?php } ?>
                    </tr>
                    
                <?php } ?>
                </table>             
        </td>
        
    </tr>

</table>

</table>



<script>
function loc_toggle_lng(lng) {
    var all_col=document.getElementsByClassName('field_' + lng);
    if (document.getElementById('atbt_' + lng).classList.contains('articleedit_lng_active') == false) {
        $("#atbt_"+lng).addClass('articleedit_lng_active');
        for (var i=0;i<all_col.length;i++)
        {
         all_col[i].style.display="table-cell";
        }
    }
    else {
        $("#atbt_"+lng).removeClass('articleedit_lng_active');
        for (var e=0;e<all_col.length;e++)
        {
         all_col[e].style.display="none";
        }  
    }
}

function save_line(line, u, s) {
    <?php foreach ($locales as $locale) { ?>
        var title_<?php echo $locale['locale'] ?> = document.getElementById('title_' + line + '_<?php echo $locale['locale'] ?>').value;
        var comment_<?php echo $locale['locale'] ?> = document.getElementById('comment_' + line + '_<?php echo $locale['locale'] ?>').value;
    <?php } ?>    
    var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText != 'OK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            if (this.responseText == 'OK') {
                $.growl.notice({ message: "Translation saved", duration: "3000", size: "large", location: "tc" });
            }
        }
        if (this.status != 200 && this.status != 0 && this.readyState != 4){
            $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
        }
    };
	xmlhttp.open("GET", "classes/ajax/loc_saveline.php?userid=" + encodeURIComponent(u)
    + "&stratid=" + encodeURIComponent(line)
    <?php foreach ($locales as $locale) { ?>
        + "&title_<?php echo $locale['locale'] ?>=" + encodeURIComponent(title_<?php echo $locale['locale'] ?>)
        + "&comment_<?php echo $locale['locale'] ?>=" + encodeURIComponent(comment_<?php echo $locale['locale'] ?>)
    <?php } ?>
	+ "&delimiter=" + encodeURIComponent(s), true);
	xmlhttp.send();     
}
</script>



</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
mysqli_close($dbcon);
echo "</body>";
die;