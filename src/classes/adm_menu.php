<?php
require_once ('HTTP.php');
require_once ('Database.php');
$action = \HTTP\argument_POST_or_GET_or_default('action', '');

if ($userrights['Edit_Menu'] == "yes" && $action == "add_item") {
    mysqli_query($dbcon, "INSERT INTO Menu_Primary (`Name_en_US`) VALUES ('New Menu Item')") OR die(mysqli_error($dbcon));
    $nav_page = "";
}

if ($userrights['Edit_Menu'] == "yes" && $action == "edit") {
    $item_id = \HTTP\argument_GET_or_default('item','');
    $item_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE id = $item_id");
    if (mysqli_num_rows($item_db) > 0) {
        $item = mysqli_fetch_object($item_db);
        $nav_page = "edit_item";
    }
    else {
        $nav_page = "";
    }
}

if ($userrights['Edit_Menu'] == "yes" && $action == "save_item") {
    $menu_item = \HTTP\argument_POST('menu_item');
    $new_name = \HTTP\argument_POST('name');
    $new_slug = \HTTP\argument_POST('slug');
    $new_link = \HTTP\argument_POST('link');

    $new_dng = 0;
    if ($_POST['dungeon'] == "on") { $new_dng = 1; }
    $new_hcdng = 0;
    if ($_POST['hcdungeon'] == "on") { $new_hcdng = 1; }
    
    Database_UPDATE
      ( 'Menu_Primary'
      , [ 'Name_en_US'
        , 'Slug'
        , 'Link'
        , 'Dungeon'
        , 'Dng_AlwaysHC'
        ]
      , 'WHERE id = ?'
      , 'sssiii'
        , mysqli_real_escape_string($dbcon, $new_name)
        , mysqli_real_escape_string($dbcon, $new_slug)
        , mysqli_real_escape_string($dbcon, $new_link)
        , $new_dng
        , $new_hcdng
      , $menu_item
      );    
     
}


// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================




?>
<script src="https://www.wow-petguide.com/data/jquery-sortable.js"></script>
<link rel="stylesheet" href="data/test.css">
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

<td width="100%"><h class="megatitle">Administration - Navigation Menu</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('admin');
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

    <?php print_admin_menu('menu'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td>
            <p class="blogodd">
                
            <?
            if ($nav_page == "") { // Core Menu - list of all items draggable etc. ?>
                
                Drag and drop the menu items. They will appear in the top menu exactly as you set them up here.<br>
                Items can be nested up to 3 levels (parent + child + child). If they show up in red, you went too far :D<br>
                All will be saved and up to 6 levels will be shown here, but the red ones will not display on the actual menu.<br><br>
                
                If you accidentally move something around, just reload, it only saves when you press the button ^^<br><br>
            
            <button onclick="save_nav_menu(<?php echo $user->id ?>,<?php echo $user->ComSecret ?>)" type="submit" class="comedit">Save Menu Order</button>
            
            <form action="?page=adm_menu" method="post" style="display: inline">
                <input type="hidden" name="action" value="add_item">
                <button type="submit" class="comsubmit">Add Item</button>
            </form>
            <br><br>
            
            
            <ul class="adm_menu" id="nav_menu">
                <?
                function print_one_menu_item($itemdata) {
                    $user = $GLOBALS['user'];
                    $eye_icon = "icon_eye_open";
                    $line_opacity = 1;
                    if ($itemdata->Active == 0) {
                        $eye_icon = "icon_eye_closed";
                        $line_opacity = 0.35;
                    }
                    ?>
                    <li id="<?php echo $itemdata->id ?>">
                    <div id="adm_op_line_<?php echo $itemdata->id ?>" class="adm_menu adm_line_trigger" data-lineid="<?php echo $itemdata->id ?>" style="opacity: <?php echo $line_opacity ?>">
                        <?php echo $itemdata->id ?> - <?php echo $itemdata->Name_en_US ?> - # of ydvfights -
                        <div class="adm_line_trigger" data-lineid="<?php echo $lineid ?>" style="float: right">
                            <div id="adm_line_<?php echo $itemdata->id ?>" class="adm_all_lines" style="opacity: 0">
                                <a href="?page=adm_menu&action=edit&item=<?php echo $itemdata->id ?>"><img class="adm_line_trigger" src="https://www.wow-petguide.com/images/icon_pen.png" style="width: 18px; height: 15px; padding: 2 5 0 0; float: right;"></a>
                                <img id="adm_eye_icon_<?php echo $itemdata->id ?>" class="adm_line_trigger" onclick="adm_menu_toggle_active('<?php echo $itemdata->id ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>')" src="https://www.wow-petguide.com/images/<?php echo $eye_icon ?>.png" style="width: 20px; height: 15px; padding: 2 6 0 0; float: right;">
                            </div>
                        </div>
                    </div>
                <?php }
                
                $depths = 0;
                $menu_primary_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = 0 ORDER BY Ordering ASC");
                
                while ($menu_primary_item = mysqli_fetch_object($menu_primary_db)) {
                      print_one_menu_item($menu_primary_item); ?>
                      <ul>
                        <?php $menu_child1_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = $menu_primary_item->id ORDER BY Ordering ASC");
                        while ($menu_d1 = mysqli_fetch_object($menu_child1_db)) {                            
                              print_one_menu_item($menu_d1); ?>
                              <ul>
                                <?php $menu_child2_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = $menu_d1->id ORDER BY Ordering ASC");
                                while ($menu_d2 = mysqli_fetch_object($menu_child2_db)) {                               
                                      print_one_menu_item($menu_d2); ?>
                                      <ul>
                                        <?php $menu_child3_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = $menu_d2->id ORDER BY Ordering ASC");
                                        while ($menu_d3 = mysqli_fetch_object($menu_child3_db)) {                                  
                                              print_one_menu_item($menu_d3); ?>
                                              <ul>
                                                <?php $menu_child4_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = $menu_d3->id ORDER BY Ordering ASC");
                                                while ($menu_d4 = mysqli_fetch_object($menu_child4_db)) {                                                 
                                                      print_one_menu_item($menu_d4); ?>
                                                      <ul>
                                                        <?php $menu_child5_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = $menu_d4->id ORDER BY Ordering ASC");
                                                        while ($menu_d5 = mysqli_fetch_object($menu_child5_db)) {                                                         
                                                              print_one_menu_item($menu_d5); ?>
                                                              <ul>
                                                                <?php $menu_child6_db = mysqli_query($dbcon, "SELECT * FROM Menu_Primary WHERE Parent = $menu_d5->id ORDER BY Ordering ASC");
                                                                while ($menu_d6 = mysqli_fetch_object($menu_child6_db)) {                                                                
                                                                    print_one_menu_item($menu_d6); ?>
                                                                      <ul></ul>
                                                                    </li>
                                                                <?php } ?>
                                                              </ul>
                                                            </li>
                                                        <?php } ?>
                                                      </ul>
                                                    </li>
                                                <?php } ?>
                                              </ul>
                                            </li>
                                        <?php } ?>
                                      </ul>
                                    </li>
                                <?php } ?>
                              </ul>
                            </li>
                        <?php } ?>
                      </ul>
                    </li>
               <?php } ?>          
           </ul>
            
            
            <script>            
                $("ul.adm_menu").sortable();
                
                $('.adm_line_trigger').mouseover(function() {
                    var lineid = this.getAttribute("data-lineid");
                    document.getElementById('adm_line_'+lineid).style.opacity = '1';
                });
                $('.adm_line_trigger').mouseout(function() {
                    var lineid = this.getAttribute("data-lineid");
                    document.getElementById('adm_line_'+lineid).style.opacity = '0';
                });
            </script>
            
            <?php } // End of Main menu
            
            
            
            
            // Editing of a single item
            
            if ($nav_page == "edit_item") {  ?>
                
                <br>
                Hover over any of the titles to see a description of what it does:<br><br>
                     
                <form action="?page=adm_menu" method="post">
                <input type="hidden" name="action" value="save_item">
                <input type="hidden" name="menu_item" value="<?php echo $item->id ?>">
                <table>
                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#name_tt"><p class="blogodd" style="font-weight: bold">Name (EN):</p></div>
                            <div style="display: none">
                                <span id="name_tt">Visible title of the menu item. This is what will be shown on the top menu in English.<br>
                                It cannot be blank.</span>
                            </div>
                        </td>
                        <td style="padding-left: 10px;">
                            <input class="editable_input" name="name" tabindex="1" placeholder="" style="width: 400px;" required="" width="230" value="<?php echo htmlentities($item->Name_en_US, ENT_QUOTES, "UTF-8") ?>">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#slug_tt"><p class="blogodd" style="font-weight: bold">URL-Name:</p></div>
                            <div style="display: none">
                                <span id="slug_tt">This is what the link will be called, like this: <br><br>http://www.wow-petguide.com/?m=<b><u>Draenor</u></b><br><br>
                                It cannot contain spaces or special characters.<br>
                                If you leave it blank, the item will not be clickable.</span>
                            </div>
                        </td>
                        <td style="padding-left: 10px;">
                            <input class="editable_input" name="slug" tabindex="1" placeholder="" style="width: 400px;" width="230" value="<?php echo htmlentities($item->Slug, ENT_QUOTES, "UTF-8") ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#link_tt"><p class="blogodd" style="font-weight: bold">Link:</p></div>
                            <div style="display: none">
                                <span id="link_tt">If you add a valid URL (link to a page) here, the menu item will simply link to this page and open it in a new browser tab.<br>
                                Useful for external tools like Magpie.</span>
                            </div>
                        </td>
                        <td style="padding-left: 10px;">
                            <input class="editable_input" name="link" tabindex="1" placeholder="" style="width: 400px;" width="230" value="<?php echo htmlentities($item->Link, ENT_QUOTES, "UTF-8") ?>">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#colour_tt"><p class="blogodd" style="font-weight: bold">Colour:</p></div>
                            <div style="display: none">
                                <span id="colour_tt">The base colour in which the category is shown. Only valid for first level menu items.</span>
                            </div>
                        </td>
                        <td style="padding-left: 10px;">
                            TBD
                        </td>
                    </tr>


                <tr><td colspan="3"><hr class="profile"></td></tr>

                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#rights_tt"><p class="blogodd" style="font-weight: bold">Editing Rights:</p></div>
                            <div style="display: none">
                                <span id="rights_tt">If you add a valid URL (link to a page) here, the menu item will simply link to this page and open it in a new browser tab.<br>
                                Useful for external tools like Magpie.</span>
                            </div>
                        </td>
                        <td style="padding-left: 10px;">
                            TBD
                        </td>
                    </tr>
                    
                <tr><td colspan="3"><hr class="profile"></td></tr>
                
                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#dungeon_tt"><p class="blogodd" style="font-weight: bold">Dungeon:</p></div>
                            <div style="display: none">
                                <span id="dungeon_tt">
                                    If this category contains strategies for a dungeon that has a Heroic mode (no healing possible), toggle this ON.<br>
                                </span>
                            </div>
                        </td>
                        <td style="padding-left: 15px;">
                            <div id="ttcolswitch" class="armoryswitch">
                                <input type="checkbox" name="dungeon" class="armoryswitch-checkbox" id="pr_col" <?php if ($item->Dungeon == 1) { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="pr_col">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#hcdungeon_tt"><p class="blogodd" style="font-weight: bold">HC-Dungeon only:</p></div>
                            <div style="display: none">
                                <span id="hcdungeon_tt">
                                    If this is a dungeon that only exists as "Heroic" (no healing possible), like the Celestial Tournament, toggle this option ON.<br>
                                    Only do so if there is <b>no normal mode</b> possible.
                                </span>
                            </div>
                        </td>
                        <td style="padding-left: 15px;">
                            <div id="ttcolswitch" class="armoryswitch">
                                <input type="checkbox" name="hcdungeon" class="armoryswitch-checkbox" id="pr_hcdungeon" <?php if ($item->Dng_AlwaysHC == 1) { echo "checked"; } ?>>
                                <label class="armoryswitch-label" for="pr_hcdungeon">
                                <span class="armoryswitch-inner"></span>
                                <span class="armoryswitch-switch"></span>
                                </label>
                            </div>
                        </td>
                    </tr>

                <tr><td colspan="3"><hr class="profile"></td></tr>
               
                <?php /* LOCA OPTIONS DISABLED - need a separate menu for those in Loc tab!!
                    * Always split admin from Loc!
                    
                    foreach (['de_DE', 'fr_FR', 'it_IT', 'es_ES', 'ru_RU', 'pt_BR', 'ko_KR', 'zh_TW'] as $locale) {
                    $urllink = "Name_".$locale; ?>
                    
                    <tr>
                        <td>
                            <div style="cursor: help;" class="description_tt" data-tooltip-content="#loc_<?php echo $locale ?>_tt"><p class="blogodd" style="font-weight: bold">Name <?php echo $locale ?>:</p></div>
                            <div style="display: none">
                                <span id="loc_<?php echo $locale ?>_tt">The translation for the title to be displayed in <?php echo $locale ?>.</span>
                            </div>
                        </td>
                        <td style="padding-left: 10px;">
                            <input class="editable_input" name="loc_<?php echo $locale ?>" placeholder="" style="width: 400px;" width="230" value="<?php echo htmlentities($item->$urllink, ENT_QUOTES, "UTF-8") ?>">
                        </td>
                    </tr>

                <?php } */ ?>







					<script>
						$(document).ready(function() {
							$('.description_tt').tooltipster({
								maxWidth: '350',
								theme: 'tooltipster-smallnote'
							});
						});
					</script>




                </table>

 
                    
                    
                    
                    <br><br>
                    
                    <button type="submit" class="comsubmit">Save and go back</button>
                </form>
        
            <?php } // End of Main menu ?>
        <br>
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