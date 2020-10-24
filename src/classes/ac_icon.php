<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('BattleNet.php');
require_once ('Database.php');
require_once ('File.php');
require_once ('Growl.php');
require_once ('HTML.php');
require_once ('HTTP.php');
require_once ('Util.php');

$command_raw = \HTTP\argument_GET_or_default ('command', FALSE);
$command_pieces = explode('_', $command_raw);
$command = $command_pieces[0];
$show_all_chars = $command_pieces[1];

$show_all_chars_form = \HTTP\argument_POST_or_default ('show_all_chars', FALSE);
if ($show_all_chars_form === 'true') {
  $show_all_chars = 1;
}


if ($command === 'charicon') {
  $settingspage = 'wowavatars';
}
else {
  $settingspage = \HTTP\argument_POST_or_default ('settingspage', FALSE);
}


try {
  if ($settingspage == 'profilepic') {
    $newicon = \HTTP\argument_POST ('newicon');

    // Used for side effect of throwing if missing.
    //! \todo Foreign-key Users.Icon to PetsUser.PetID and let
    //! database handle that.
    Database_query_object ("SELECT * FROM PetsUser WHERE PetID = '$newicon' LIMIT 1");

    Database_query ( 'UPDATE Users '
                   . 'SET Icon = \'' . $newicon . '\''
                     . ', UseWowAvatar = 0 '
                   . 'WHERE id = ' . $user->id
                   );

    Database_protocol_user_activity
      ($user, 0, 'New pet Avatar selected', $newicon);

    \HTTP\redirect_and_die
      ('/index.php?page=icon&sendtoast=iconchanged');
  }
  else if ($settingspage == 'wowpic')
  {
    $iconpath = \HTTP\argument_POST ('addcharpic');
    $targetpath = 'images/userpics/' . $user->id . '.jpg';

    $thumbnail_split = explode('character/', $iconpath);
    $thumbnail = $thumbnail_split[1];

    \File\fetch_remote_file_if_exists ($iconpath, $targetpath);

    Database_query ( 'UPDATE Users '
                   . 'SET UseWowAvatar = 1'
                     . ',IconUpdate = NOW() '
                   . 'WHERE id = ' . $user->id
                   );
    Database_query ( 'UPDATE UserBnet '
                   . 'SET CharIcon = \'' . $thumbnail . '\' '
                   . 'WHERE User = ' . $user->id
                   );

    Database_protocol_user_activity
      ($user, 0, 'New Wow Avatar saved', $thumbnail);

    \HTTP\redirect_and_die
      ('/index.php?page=icon&sendtoast=iconchanged');
  }
}
catch (Exception $ex)
{
  Growl_show_error ('There was a problem adding your new icon. Please try again');
  $settingspage = '';
}

function create_blogtitle ($user, $title)
{
  $outer = HTML_create_root ('div');
  $outer->setAttribute ('class', 'blogtitlev2');

  HTML_append_element ($outer, 'img')
    ->setAttribute ('src', User_icon_url ($user));

  HTML_append_text (HTML_append_element ($outer, 'h1'), $title);

  return $outer;
}

//! \todo This is very similar to TopMenu_append_user_dropdown, merge
//! or at least move together.
function create_profile_menu ($user, $active_page)
{
  $new_strategy_comments = User_unread_strategy_comment_count ($user);
  $new_comments = User_unread_comment_count ($user);
  $new_private_messages = User_unread_private_message_count ($user);

  $outer = HTML_create_root ('div');
  $outer->setAttribute ('class', 'remodal-bg leftmenuv2');

  $ul = HTML_append_element ($outer, 'ul');
  $ul->setAttribute ('class', 'vertical-list');

  function cpm__entry ($ul, $page, $text, $is_active)
  {
    $a = HTML_append_link_with_text (HTML_append_element ($ul, 'li'), $text, '?page=' . $page);
    $a->setAttribute ('class', 'button' . ($is_active ? ' activebuttonv2' : ''));
    return $a;
  }

  cpm__entry ( $ul, 'profile'
             , Localization_string ('My Profile')
             , $active_page === 'profile'
             );
  cpm__entry ( $ul, 'collection'
             , Localization_string ('Pet Collection')
             , $active_page === 'col'
             );

  cpm__entry ( $ul, 'strategies'
             , Localization_string ('My Strategies') . Util_maybe_braced_count ($new_strategy_comments)
             , $active_page === 'strategies'
             );
  cpm__entry ( $ul, 'mycomments'
             , Localization_string ('My Comments') . Util_maybe_braced_count ($new_comments)
             , $active_page === 'mycomments'
             );

  $messages_elem = cpm__entry ( $ul, 'messages'
                              , Localization_string ('Messages')
                              , $active_page === 'messages'
                              );

  $messages_elem_in = HTML_append_element ($messages_elem, 'span');
  $messages_elem_in->setAttribute ('id', 'topmsgsin');
  HTML_append_text ($messages_elem_in, $new_private_messages ? ' (' : '');
  $messages_elem_count = HTML_append_element ($messages_elem, 'span');
  $messages_elem_count->setAttribute ('id', 'topmsgscount');
  HTML_append_text ($messages_elem_count, $new_private_messages ? $new_private_messages : '');
  $messages_elem_out = HTML_append_element ($messages_elem, 'span');
  $messages_elem_out->setAttribute ('id', 'topmsgsout');
  HTML_append_text ($messages_elem_out, $new_private_messages ? ')' : '');

  cpm__entry ( $ul, 'settings'
             , Localization_string ("Settings")
             , $active_page === 'settings' || $active_page === 'notesettings' || $active_page === 'settings'
             );

  if (User_is_allowed ($user, 'AdmPanel'))
  {
    cpm__entry ($ul, 'admin', 'Administration', $active_page === 'admin');
  }
  
  if (User_is_allowed ($user, 'LocArticles'))
  {
    cpm__entry ($ul, 'loc', 'Localization', $active_page === 'loc');
  }
  

  cpm__entry ($ul, 'logout', Localization_string ('Logout'), false);

  return $outer;
}

function ac_profile_top_menu ($content_table, $active)
{
  $tr = HTML_append_element ($content_table, 'th');

  foreach (['profile', 'icon', 'tooltip'] as $sub)
  {
    $a = HTML_append_element ($tr, 'a');
    $a->setAttribute ('href', '?page=' . $sub);
    $b = HTML_append_element ($a, 'button');
    $b->setAttribute ('class', 'category' . ($active == $sub ? ' activecategory' : ''));
        switch ($sub) {
        case "profile":
            HTML_append_text ($b, Localization_string ('Profile Info'));
            break;
        case "icon":
            HTML_append_text ($b, Localization_string ('Your Icon'));
            break;
        case "tooltip":
            HTML_append_text ($b, Localization_string ('Tooltip Settings'));
            break;
        }
    
  }
}

$blogentryfirst = HTML_create_root ('div');
$blogentryfirst->setAttribute ('class', 'blogentryfirstv2');
HTML_append_element ($blogentryfirst, 'div')
  ->setAttribute ('class', 'articlebottom');

$content_table = HTML_append_element ($blogentryfirst, 'table');
$content_table->setAttribute ('class', 'profilev2');
ac_profile_top_menu ($content_table, 'icon');

$td = HTML_append_elements ($content_table, ['tr', 'td']);

// ============= MODULE 2 - Profile Pic =============

if ($settingspage != 'wowavatars')
{
  if ($bnetuser->Region != 'cn' && $bnetuser)
  {
    $form = HTML_append_form ($td, '/?page=icon', 'post', 'avatar_starter');
    HTML_append_hidden_form_input ($form, 'settingspage', 'wowavatars');

    $intro = HTML_append_element ($form, 'p');
    $intro->setAttribute ('class', 'blogodd');

    HTML_append_text ($intro, Localization_string ('Pick an icon of your choice or click the button if you prefer a picture of your WoW character as your icon:'));

    $button = HTML_append_element ($intro, 'button');
    $button->setAttribute ('tabindex', 30);
    $button->setAttribute ('type', 'submit');
    $button->setAttribute ('class', 'bnetlogin');
    HTML_append_text ($button, Localization_string ('Show Available Characters'));
  }
  $iconlist = array_map ( function ($id) use ($language)
                          {
                            return [ 'name' => Localization_pet_name ($id, $language)
                                   , 'id' => $id
                                   ];
                            }
                        , Util_all_available_pet_icon_ids()
                        );
  $iconlist = array_filter ( $iconlist
                           , function ($elem)
                             {
                               return $elem['name'];
                            }
                           );
  sort ($iconlist); // by first child = name

  $form = HTML_append_form ($td, '/?page=icon', 'post');
  HTML_append_hidden_form_input ($form, 'settingspage', 'profilepic');

  $iconlistdiv = HTML_append_element ($form, 'div');
  $iconlistdiv->setAttribute ('class', 'iconlistv2');

  $textchooser = HTML_append_element ($iconlistdiv, 'div');
  $textchooser->setAttribute ('class', 'header');
  $textchooser_select = HTML_append_element ($textchooser, 'select');
  $textchooser_select->setAttribute ('name', 'newicon');
  $textchooser_select->setAttribute ('tabindex', 35);
  $textchooser_select->setAttribute ('id', 'select-iconlist');
  $textchooser_select->setAttribute ('class', 'chosen-select');
  $textchooser_select->setAttribute ('required', false);

  foreach ($iconlist as $icon)
  {
    $option = HTML_append_element ($textchooser_select, 'option');
    $option->setAttribute ('value', $icon['id']);
    HTML_append_text ($option, $icon['name']);
  }

  $save_button = HTML_append_element ($textchooser, 'button');
  $save_button->setAttribute ('tabindex', 36);
  $save_button->setAttribute ('type', 'submit');
  $save_button->setAttribute ('class', 'comedit');
  HTML_append_text ($save_button, Localization_string ('Save'));

  foreach ($iconlist as $icon)
  {
    $div = HTML_append_element ($iconlistdiv, 'div');
    $div->setAttribute ('id', 'icon_' . $icon['id']);
    $div->setAttribute ('class', 'icon');
    $div->setAttribute ('onclick', 'select_icon(\'' . $icon['id'] . '\')');

    HTML_append_element ($div, 'img')
      ->setAttribute ('src', '/images/pets/resize50/' . $icon['id'] . '.png');
  }

  HTML_append_text
    ( HTML_append_element ($iconlistdiv, 'script')
    , '$(".chosen-select").chosen ({width: 350});'
    . '$(".chosen-select").chosen().change (function (event) {'
    . '  $(".icon").removeClass ("selected_icon");'
    . '  $("#icon_" + $("select[name=newicon]").val()).addClass ("selected_icon");'
    . '});'
    . 'function select_icon(i) {'
    . '  $("#select-iconlist").val (i).change();'
    . '  $(".chosen-select").trigger ("chosen:updated");'
    . '}'
    . 'select_icon (' . $user->Icon . ');'
    );
}
else
// ============= MODULE 2.1 - WoW Character Picture Selection =============
{
  $loadingicon = HTML_append_elements ($td, ['center', 'div']);
  $loadingicon->setAttribute ('id', 'loading');
  HTML_append_element ($loadingicon, 'img')
    ->setAttribute ('src', '/images/loading.gif');

  HTML_append_text
    ( HTML_append_element ($td, 'script')
    , 'window.onload = function() { $("#loading").css ("display", "none"); }'
    );


  $charlist = [];

  try
  {
    $use_region = $bnetuser->Region;
    if ($bnetuser->Region == "cn") $use_region = "china";
    $oauth = new \BattleNet\OAuth ($use_region, 'charicon_'.$show_all_chars, '/index.php?page=icon');

    if (!$oauth->is_authed)
    {
      \HTTP\redirect_and_die ($oauth->auth_url());
    }

    if (!$oauth->has_wow_access())
    {
      \HTTP\redirect_and_die
        ('/index.php?page=icon&sendtoast=nowowscope');
    }

    if ($oauth->fetch ('account')['id'] != $bnetuser->BnetID)
    {
      \HTTP\redirect_and_die
        ('/index.php?page=icon&sendtoast=wrongbnet');
    }
    

    // Import 
    $wowcharsinfo = $oauth->fetch ('wowprofile');

    // Go through all characters and add to an array
    $all_chars = array();
    $count_chars = 0;
    foreach ($wowcharsinfo['wow_accounts'] as $account) {
      foreach ($account['characters'] as $char) {
        $icon_chars[$count_chars]['Name'] = $char['name'];
        $icon_chars[$count_chars]['Realm'] = $char['realm']['name'];
        $icon_chars[$count_chars]['Slug'] = $char['realm']['slug'];
        $icon_chars[$count_chars]['Class'] = $char['playable_class']['id'];
        $icon_chars[$count_chars]['Race'] = $char['playable_race']['id'];
        $icon_chars[$count_chars]['Level'] = $char['level'];
        $count_chars++;
      }
    }
    sortBy ('Level', $icon_chars, 'desc');
        
    $otherchars = 0;
    foreach ($icon_chars as $char)
    {
      if (($char['Level'] > 99 AND $show_all_chars == false) OR $show_all_chars == true) {
        $chardata_avatar_source = blizzard_api_character_media($bnetuser->Region, $char['Slug'], $char['Name']);
        $chardata_avatar = json_decode($chardata_avatar_source, TRUE);
        $avatar = $chardata_avatar['avatar_url'];
        $charlist[] = [ 'icon' => $avatar
                     , 'armory' => 'https://worldofwarcraft.com/' . $oauth->locale
                                  . '/character/' . $char['Slug']
                                  . '/' . $char['Name']
                      , 'name' => $char['Name']

                      , 'desc' => Localization_string ('Level') . ' '
                                . $char['Level'] . ' ' . lookup_char_race ($char['Race'])
                                . ' ' . lookup_char_class ($char['Class'])
                      , 'realm' => $char['Realm'] . '-' . strtoupper ($bnetuser->Region)
                      ];
      }
      else {
        $otherchars++;
      }
      
    }

  }
  catch (\BattleNet\OAuthException $e)
  {
    echo '<!-- exception: ' . $e->what . ' -->';
    \HTTP\redirect_and_die ('/index.php?page=icon&sendtoast=bnetregfail');
  }

  if (sizeof ($charlist) > 0)
  {
    HTML_append_paragraph_text ($td, __("Click on the picture of a character to select it as your avatar."), 'blogodd');
  
   
    $characters = HTML_append_element ($td, 'div');
    $characters->setAttribute ('class', 'avatarlist_container');

    $tabindex = 40;
    foreach ($charlist as $info)
    {
      $character_div = HTML_append_element ($characters, 'div');
      $character_div->setAttribute ('class', 'avatarlistv2');

      $character = HTML_append_form ($character_div, '/?page=icon', 'post');
      HTML_append_hidden_form_input ($character, 'settingspage', 'wowpic');
      HTML_append_hidden_form_input ($character, 'addcharpic', $info['icon']);

      $icon = HTML_append_element ($character, 'input');
      $icon->setAttribute ('tabindex', $tabindex);
      $icon->setAttribute ('type', 'image');
      $icon->setAttribute ('src', $info['icon']);

      HTML_append_link_with_text ($character, $info['name'], $info['armory'], '_blank');
      HTML_append_paragraph_text ($character, $info['desc']);
      HTML_append_paragraph_text ($character, $info['realm']);

      $tabindex++;
    }
    
    if ($show_all_chars == false) {
      HTML_append_paragraph_text ($td, __("Only characters level 100+ are shown. You can display all lower level characters but please be aware this might take a while to load."), 'blogodd');

      $form = HTML_append_form ($td, '/?page=icon', 'post', 'avatar_starter');
      HTML_append_hidden_form_input ($form, 'settingspage', 'wowavatars');
      HTML_append_hidden_form_input ($form, 'show_all_chars', 'true');
  
      $intro = HTML_append_element ($form, 'p');
      $intro->setAttribute ('class', 'blogodd');
    
      $button = HTML_append_element ($intro, 'button');
      $button->setAttribute ('tabindex', 30);
      $button->setAttribute ('type', 'submit');
      $button->setAttribute ('class', 'bnetlogin');
      HTML_append_text ($button, 'Show all characters');
    }
    
  }
  else
  {
    $intro = HTML_append_element ($td, 'p');
    $intro->setAttribute ('class', 'blogodd');
    $intro->setAttribute ('style', 'display: block');
    HTML_append_text
      ( HTML_append_element ($intro, 'b')
      , Localization_string ('I checked for your characters in a specific region but could not find any. The region I checked was:') . ' ' . strtoupper ($bnetuser->Region)
      );

    HTML_append_paragraph_text
      ( $td
      , 'This error occurs either if you do not have any characters in that '
      . 'region, or if the connection to the Blizzard servers could not be '
      . 'established.'
      , 'blogodd'
      )->setAttribute ('style', 'display: block');

    HTML_append_paragraph_text ($td, Localization_string ('If your characters are on another region, please go to your Account Settings to change it.'), 'blogodd')
      ->setAttribute ('style', 'display: block');
  }
}

// ============= END OF MODULE 2.1 =============

switch ($sendtoast)
{
    case 'bnetregfail':
     Growl_show_error (Localization_string ('The Battle.net authorization was declined. To login using your Battle.net Account please try again and authorize Xu-Fu.<br>Note: Your personal data (password, email address) will never be shared by Battle.net to authorized apps.'), 10000);
        break;
    case 'genericerror':
     Growl_show_error (Localization_string ('There was an error processing your data, I am sorry. Please try again.'), 7000);
        break;
    case 'iconchanged':
     Growl_show_notice (Localization_string ('Your new avatar was saved :-)'), 5000);
        break;
    case 'nowowaccess':
     Growl_show_error (Localization_string ('Xu-Fu has no access to your WoW character list. Adding a character picture as your avatar is therefore not possible. <br>Please check below for instructions on how to fix this.'), 7000);
        break;
    case 'bnetapierror':
     Growl_show_error (Localization_string ('There was a problem with the Battle.net Account service, most likely a timeout or perhaps the servers are in maintenance. Please try again later.'), 10000);
        break;
    case 'nowowscope':
     Growl_show_error ( 'When signing up with Battle.net, you have not '
                      . 'allowed Xu-Fu to access your WoW data. This data '
                      . 'is required to check for your character icons. '
                      . 'Check the Account Settings to learn how to '
                      . 'change this.'
                      , 10000
                      );
        break;
    case 'wrongbnet':
     Growl_show_error ( 'You seem to be logged into a different Battle.net '
                      . 'account on Blizzards web pages than the one you '
                      . 'used to log into Xu-Fu. Please head over to '
                      . 'Battle.net and make sure you log into the correct '
                      . 'Battle.net account before trying again.'
                      , 10000
                      );
        break;
}

$htmls = [ create_blogtitle ($user, Localization_string ('Edit Your Profile'))
         , create_profile_menu ($user, 'profile')
         , $blogentryfirst
         ];

foreach ($htmls as $elem) echo (HTML_to_string ($elem));

die;
