<?php
use Gettext\Loader\PoLoader;
use Gettext\Generator\MoGenerator;

$submitbreeds = $_POST['submitbreeds'];


// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================




?>
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
<table style="width: 85%;" class="profile">

    <?php print_loc_menu(''); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">
            
            <?
                $action = \HTTP\argument_POST_or_default ('action', FALSE);
                if ($action == "import_all") {
                    import_strings('de_DE');
                    import_strings('fr_FR');
                    import_strings('it_IT');
                    import_strings('pt_BR');
                    import_strings('es_ES');
                    import_strings('ru_RU');
                    import_strings('pl_PL');
                    import_strings('ko_KR');
                    import_strings('zh_TW');
                }
                if ($action && $action != 'import_all') {
                    import_strings($action);
                }
                
                function import_strings($action) {
                
                    switch ($action) {
                      case "de_DE":
                         $import_language = "de";
                         $lang_output = "German";
                         break;
                      case "fr_FR":
                         $import_language = "fr";
                         $lang_output = "French";
                         break;
                      case "it_IT":
                         $import_language = "it";
                         $lang_output = "Italian";
                         break;
                      case "pt_BR":
                         $import_language = "pt";
                         $lang_output = "Portuguese";
                         break;
                        case "es_ES":
                         $import_language = "es";
                         $lang_output = "Spanish";
                         break;
                        case "ru_RU":
                         $import_language = "ru";
                         $lang_output = "Russian";
                         break;
                        case "pl_PL":
                         $import_language = "pl";
                         $lang_output = "Polish";
                         break;
                        case "ko_KR":
                         $import_language = "ko";
                         $lang_output = "Korean";
                         break;
                         case "zh_TW":
                         $import_language = "zh-CN";
                         $lang_output = "Chinese";
                         break;
                  }
                if ($import_language) {
                    echo '<center><div style="background-color: white; padding: 10; margin-top: 10px; width: 90%; text-align: left"><p class="commentodd"><b>Importing language strings for: '.$lang_output.'<br>';
                    echo '<br>Connecting to POEditor... ';
                        
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => 'https://api.poeditor.com/v2/projects/export',
                        CURLOPT_POST => 1,
                        CURLOPT_POSTFIELDS => [
                            'api_token' => poeditor_apikey,
                            'id' => poeditor_userid,
                            'language' => $import_language,
                            'type' => 'po'
                        ]
                    ]);
                    $resp = curl_exec($curl);
                    
                    if (!curl_exec($curl)) {
                        echo '<font style="color: red">unsuccessful. Encountered error "' . curl_error($curl) . '" - Code: ' . curl_errno($curl).'</font>';
                    }
                    else {
                        $resp = json_decode($resp);
                        if ($resp->response->status == 'fail') {
                            echo '<font style="color: red">unsuccessful. Encountered error "' . $resp->response->message . '" - Code: ' . $resp->response->code.'</font>';
                        }
                        else {
                            echo '<font style="color: green">success</font>';
                        }
                        $url = $resp->result->url;
                    }
                    // Close request to clear up some resources
                    curl_close($curl);
                    
                    // Step 2
                    if ($url) {
                        echo '<br>Importing new strings... ';
                        if (checkExternalFile($url) != 200) {
                            echo '<font style="color: red">unsuccessful. File could not be read.</font>';
                        }
                        else {
                            echo '<font style="color: green">success</font>';
                            $continue = true;
                        }
                    }
                    if ($continue == true) {
                        echo '<br>Saving new file... ';
                        require_once ("thirdparty/gettext_import/autoload.php");
                        $po_file = "Locale/" . $action . "/LC_MESSAGES/messages.po";
                        $mo_file = "Locale/" . $action . "/LC_MESSAGES/messages.mo";
                        if (file_exists($po_file)) unlink($po_file);
                        if (file_exists($mo_file)) unlink($mo_file);
                        
                        copy($url, $po_file);

                        $loader = new PoLoader();
                        $translations = $loader->loadFile($po_file);
                        
                        $generator = new MoGenerator();
                        $generator->generateFile($translations, $mo_file);
                        echo '<font style="color: green">success</font>';
                        echo "<br><br>Language file for ".$lang_output." imported.";
                    }
                    else {
                        echo "<br><br>Import was not successful. Please try again. If this problem persists, please check with Aranesh.";
                    }
                    echo '</b></div></center>';       
                }
            } 
            
            ?>
            
            
            
            <br>
            <div style="margin-left: 20px"><p class="blogodd"><b>Introduction</b><br></div>
            <br>
            <p class="commentodd">Welcome to the internal Localization hub for Xu-Fu :-)<br>
            In this overview you will find all the necessary information to get wow-petguide.com translated into your language!<br><br>
            
            The page uses a set of different localization methods that are outlined below:<br>
            <br>
            
            
            
            <hr class="profile">
            <br>
            <div style="margin-left: 20px"><p class="blogodd"><b>Static Assets / Strings</b><br></div>
            <br>
            <p class="commentodd">A large part of the static text, such as menu buttons or instructions, text that typically doesn't change much, is localized through the method <i>gettext</i>.<br>
            The text strings are saved and can be edited in a separate tool: <a href="https://poeditor.com/projects/view?id=318853" class="wowhead" style="font-size: 14px" target="_blank">POEditor.com</a>. <br>
            => If you don't have an account, please contact Aranesh to get set up.<br><br>
            After adding or changing translations in POEditor, you can import the new texts here and see them on the page directly:<br><br>

        <div style="width: 100%; display: table">
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="fr_FR">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="French">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="de_DE">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="German">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="it_IT">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Italian">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="pt_BR">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Portuguese">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="es_ES">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Spanish">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="ru_RU">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Russian">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="pl_PL">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Polish">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="ko_KR">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Korean">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="zh_TW">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="Chinese">
                </form>   
            </div>
            
            <div style="float: left">
                <form action="index.php?page=loc" method="post">
                    <input type="hidden" name="action" value="import_all">
                    <input type="submit" class="comedit" style="margin-left: 8px" value="All">
                </form>   
            </div>
        </div>
        
        
        
<br>
<hr class="profile">
<br>
<div style="margin-left: 20px"><p class="blogodd"><b>Tamer Names and descriptions</b><br></div>
<br>
<p class="commentodd">Use the menu above and select "Tamer Names". There you can translate:<br>
- the names of all tamers and fights<br>
- category separators (for example "Phase 2" in dungeons).<br>
- the flavour texts at the bottom of each fight<br><br>
<b>Word of warning</b>: It currently doesn't support multi-user administration. <br>
We aren't many people but to not overwrite other peoples work, please refresh the page before starting to work on it.<br>


<br><br
<hr class="profile">
<br>
<div style="margin-left: 20px"><p class="blogodd"><b>Articles</b><br></div>
<br>
<p class="commentodd">Articles are the longer texts in each sections, like the Pandaria Master Tamers, the Dungeon descriptions or all of the guides.<br>
With your access rights, you will see a link below the title to translate or edit them. Click on that and you'll see the editor. It works much like HTML editing with some convenience features.<br>
Take a look at the English texts to see how it works.<br>
You can use the English as a template or just write your own, as long as the information makes sense and is useful to the reader. <br>
<br>
If you see outdated info in the English article, please post a message on Discord so we can fix it!<br>


<br><br
<hr class="profile">
<br>
<div style="margin-left: 20px"><p class="blogodd"><b>Pets and Spells</b><br></div>
<br>
<p class="commentodd">Pet and spell names are imported in all available languages from the Blizzard API. No need to do any work :-)<br>


<br><br
<hr class="profile">
<br>
<div style="margin-left: 20px"><p class="blogodd"><b>News</b><br></div>
<br>
<p class="commentodd">We currently do News in English only.<br>





        </td>
    </tr>

</table>

</table>

</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
mysqli_close($dbcon);
echo "</body>";
die;
