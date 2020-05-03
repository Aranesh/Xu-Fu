<?php

include_once ('classes/Database.php');
include_once ('classes/HTML.php');
include_once ('classes/Localization.php');
include_once ('classes/User.php');
include_once ('classes/Util.php');

class MenuEntry
{
  private $name;
  private $target;

  function __construct ($name, $target)
  {
    $this->name = $name;
    $this->target = substr ($target, 0, 4) == 'http' ? $target : ('/index.php?m=' . $target);
  }

  function append_to ($where)
  {
    $li = HTML_append_element ($where, 'li');
    $a = HTML_append_link_with_text ($li, $this->name, $this->target, substr ($this->target, 0, 4) == 'http' ? '_blank' : FALSE);
    $a->setAttribute ('class', 'submenu');
  }
};

class Menu
{
  private $name;
  private $highlight_key;
  private $target;

  function __construct ($name, $highlight_key, $target)
  {
    $this->name = $name;
    $this->highlight_key = $highlight_key;
    $this->target = $target == '#' ? '#' : ('/index.php?m=' . $target);
  }

  function sub ($name, $target)
  {
    $this->entries[] = new MenuEntry ($name, $target);
  }

  function append_to ($where, $current_highlight, $is_toplevel)
  {
    $li = HTML_append_element ($where, 'li');
    if ($this->highlight_key == $current_highlight)
    {
      $li->setAttribute ('class', 'active');
    }

    $a = HTML_append_link_with_text ($li, $this->name, $this->target);
    $a->setAttribute ('class', $is_toplevel ? 'top' : 'submenu');
    if ($this->target == '#')
    {
      $a->setAttribute ('style', 'cursor: default');
    }

    $ul = HTML_append_element ($li, 'ul');
    foreach ($this->entries as $entry)
    {
      $entry->append_to ($ul);
    }
  }
};

class TopMenu_MenuBuilder
{
  private $menus;
  static function begin()
  {
    return new TopMenu_MenuBuilder();
  }
  function top ($name, $highlight_key, $target = '#')
  {
    $this->menus[] = $menu = new Menu ($name, $highlight_key, $target);
    return $menu;
  }
  function finalize()
  {
    return $this->menus;
  }
};

function TopMenu_append_bnet_login_form ($where, $action_suffix)
{
  $form = HTML_append_element ($where, 'form');
  $form->setAttribute ('action', '/index.php?page=bnetlogin&' . $action_suffix);
  $form->setAttribute ('method', 'POST');

  $list = HTML_append_element ($form, 'ul');
  $list->setAttribute ('class', 'radioslight');

  $item = HTML_append_element ($list, 'li');
  $input = HTML_append_element ($item, 'input');
  $input->setAttribute ('class', 'lightblue');
  $input->setAttribute ('type', 'radio');
  $input->setAttribute ('id', 'bnet_region_standard');
  $input->setAttribute ('name', 'regionselect');
  $input->setAttribute ('value', 'standard');
  $input->setAttribute ('checked', '');
  HTML_append_paragraph_text
    (HTML_append_label ($item, 'bnet_region_standard'), _ ('UL_RegionsW'));
  HTML_append_element ($item, 'div')->setAttribute ('class', 'check');

  $item = HTML_append_element ($list, 'li');
  $input = HTML_append_element ($item, 'input');
  $input->setAttribute ('class', 'lightblue');
  $input->setAttribute ('type', 'radio');
  $input->setAttribute ('id', 'bnet_region_china');
  $input->setAttribute ('name', 'regionselect');
  $input->setAttribute ('value', 'china');
  HTML_append_paragraph_text
    (HTML_append_label ($item, 'bnet_region_china'), _ ('UL_RegionsC'));
  HTML_append_element ($item, 'div')->setAttribute ('class', 'check');

  $button = HTML_append_element ($form, 'button');
  $button->setAttribute ('tabindex', 5);
  $button->setAttribute ('class', 'bnetlogin');
  $button->setAttribute ('type', 'submit');
  $button->setAttribute ('onclick', 'loadingbnetlogin()');
  HTML_append_text ($button, _ ('UL_LogBnet'));
}

//! \todo Clean up this mess.
function TopMenu_append_normal_login_form ($where, $action_suffix)
{
  $where->setAttribute ('class', 'form-style-login');

  $form = HTML_append_element ($where, 'form');
  $form->setAttribute ('action', '/index.php?page=login&' . $action_suffix);
  $form->setAttribute ('method', 'POST');

  $table = HTML_append_element ($form, 'table');

  $t = HTML_append_element ($table, 'tr');
  $x = HTML_append_element ($t, 'td');
  $x->setAttribute ('align', 'right');
  $x->setAttribute ('valign', 'bottom');
  $y = HTML_append_element ($x, 'p');
  $y->setAttribute ('class', 'blogeven');
  HTML_append_text ($y, _ ('UL_LogName') . ':');

  HTML_append_spacer_image ($t, ['td'], 5, 1);

  $x = HTML_append_elements ($t, ['td', 'input']);
  $x->setAttribute ('maxlength', '250');
  $x->setAttribute ('tabindex', 1);
  $x->setAttribute ('type', 'text');
  $x->setAttribute ('name', 'username');
  $x->setAttribute ('onblur', 'document.registerform.username.value = this.value;');
  $x->setAttribute ('required', '');

  $t = HTML_append_element ($table, 'tr');
  HTML_append_element ($t, 'td')
    ->setAttribute ('colspan', 2);
  HTML_append_link_with_text ( HTML_append_element ($t, 'td')
                               , _ ('UL_ForgUsername')
                             , '/index.php?page=acretrieve&' . $action_suffix
                             )
    ->setAttribute ('class', 'loginbright');

  $t = HTML_append_element ($table, 'tr');
  $x = HTML_append_element ($t, 'td');
  $x->setAttribute ('colspan', 3);

  HTML_append_spacer_image ($x, [], 1, 5);

  $t = HTML_append_element ($table, 'tr');
  $x = HTML_append_element ($t, 'td');
  $x->setAttribute ('align', 'right');
  $x->setAttribute ('valign', 'bottom');
  $y = HTML_append_element ($x, 'p');
  $y->setAttribute ('class', 'blogeven');
  HTML_append_text ($y, _ ('UL_LogPass') . ':');

  HTML_append_spacer_image ($t, ['td'], 5, 1);

  $x = HTML_append_element ($t, 'td');

  $y = HTML_append_element ($x, 'div');
  $y->setAttribute ('id', 'capsWarning');
  $y->setAttribute ('class', 'capsWarning');
  $y->setAttribute ('style', 'display:none');
  $z = HTML_append_element ($y, 'p');
  $z->setAttribute ('class', 'smalleven');
  HTML_append_text ($z, 'Caps Lock is on!');

  $y = HTML_append_element ($x, 'input');
  $y->setAttribute ('tabindex', 2);
  $y->setAttribute ('type', 'password');
  $y->setAttribute ('name', 'password');
  $y->setAttribute ('id', 'password');
  $y->setAttribute ('required', '');

  $t = HTML_append_element ($table, 'tr');
  HTML_append_element ($t, 'td')
    ->setAttribute ('colspan', 2);
  HTML_append_link_with_text ( HTML_append_element ($t, 'td')
                             , _ ('UL_ForgPass')
                             , '/index.php?page=pwrecover&' . $action_suffix
                             )
    ->setAttribute ('class', 'loginbright');

  $t = HTML_append_element ($table, 'tr');
  $x = HTML_append_element ($t, 'td');
  $x->setAttribute ('colspan', 3);

  HTML_append_spacer_image ($x, [], 1, 5);

  $t = HTML_append_element ($table, 'tr');
  HTML_append_element ($t, 'td')
    ->setAttribute ('colspan', 2);
  $x = HTML_append_element ($t, 'td');
  $x->setAttribute ('valign', 'bottom');
  $y = HTML_append_elements ($x, ['label', 'p']);
  $y->setAttribute ('class', 'smalleven');
  $z = HTML_append_element ($y, 'input');
  $z->setAttribute ('tabindex', 3);
  $z->setAttribute ('type', 'checkbox');
  $z->setAttribute ('name', 'remember');
  $z->setAttribute ('value', 'true');
  $z->setAttribute ('checked', '');
  HTML_append_text ($y, _ ('UL_RemLogin'));

  $t = HTML_append_element ($table, 'tr');
  $x = HTML_append_element ($t, 'td');
  $x->setAttribute ('colspan', 3);

  HTML_append_spacer_image ($x, [], 1, 5);

  $t = HTML_append_element ($table, 'tr');
  HTML_append_element ($t, 'td')
    ->setAttribute ('colspan', 2);

  $x = HTML_append_elements ($t, ['td', 'table', 'tr']);

  $y = HTML_append_elements ($x, ['td', 'p']);
  $y->setAttribute ('class', 'blogeven');
  $z = HTML_append_element ($y, 'button');
  $z->setAttribute ('tabindex', 4);
  $z->setAttribute ('type', 'submit');
  $z->setAttribute ('name', 'page');
  $z->setAttribute ('class', 'comedit');
  $z->setAttribute ('value', 'login');
  HTML_append_text ($z, _ ('UL_MBLogin'));
  HTML_append_text ($y, ' ' . _ ('UL_MBor'));

  $y = HTML_append_elements ($x, ['td', 'p']);
  $y->setAttribute ('class', 'blogeven');
  $z = HTML_append_element ($y, 'button');
  $z->setAttribute ('tabindex', 5);
  $z->setAttribute ('type', 'button');
  $z->setAttribute ('onclick', 'document.registerform.submit()');
  $z->setAttribute ('class', 'comsubmit');
  HTML_append_text ($z, _ ('UL_MBRegister'));
}

function TopMenu_append_search_field ($where, $additional_action_args)
{
  $top = HTML_append_elements ($where, ['td', 'div']);
  $top->setAttribute ('class', 'searcherfield');
  $top->setAttribute ('style', 'float: left');

  $form = HTML_append_element ($top, 'form');
  $form->setAttribute ('class', 'search-field');
  $form->setAttribute ('action', '/index.php?page=search&' . $additional_action_args);
  $form->setAttribute ('method', 'POST');

  $input = HTML_append_element ($form, 'input');
  $input->setAttribute ('type', 'search');
  $input->setAttribute ('name', 'petsearch');
  $input->setAttribute ('placeholder', _ ('SearchField'));

  HTML_append_elements ($form, ['button', 'img'])
    ->setAttribute ('src', '/images/magglass.png');
}

function TopMenu_user_dropdown_elem ($content, $target, $icon, $text = '')
{
  $elem = HTML_append_element ($content, 'div');
  $elem->setAttribute ('class', 'dropdownelement');

  $a = HTML_append_element ($elem, 'a');
  $a->setAttribute ('href', '?page=' . $target);
  $a->setAttribute ('class', 'langselectorsmall');

  $img = HTML_append_element ($a, 'img');
  $img->setAttribute ('class', 'userdd_icon');
  $img->setAttribute ('src', '/images/userdd_' . $icon . '.png');

  HTML_append_text ($a, $text);

  return $a;
}

function TopMenu_append_corner ($top_menu)
{
  $div = HTML_append_element ($top_menu, 'div');
  $div->setAttribute ('class', 'top_menu_left_corner');

  HTML_append_link_with_text ($div, '', '/')
    ->setAttribute ('class', 'smhome');

  HTML_append_link_with_text ($div, '', 'mailto:XuFu@WoW-Petguide.com', '_blank')
    ->setAttribute ('class', 'smmail');
}

function TopMenu_append_menu ($top_menu, $menus, $current_menu_highlight)
{
  $desktop = HTML_append_element ($top_menu, 'div');
  $desktop->setAttribute ('class', 'remodal-bg container tmenudesktop');
  $desktop_list = HTML_append_elements ($desktop, ['nav', 'ul']);

  foreach ($menus as $menu)
  {
    $menu->append_to ($desktop_list, $current_menu_highlight, true);
  }

  $mobile = HTML_append_element ($top_menu, 'div');
  $mobile->setAttribute ('class', 'remodal-bg container tmenumobile');
  $mobile_proxy = HTML_append_elements ($mobile, ['nav', 'ul', 'li']);

  $mobile_root = HTML_append_link_with_text ($mobile_proxy, 'Menu', '#');
  $mobile_root->setAttribute ('class', 'top');
  $mobile_root->setAttribute ('style', 'cursor: default');
  $mobile_list = HTML_append_element ($mobile_proxy, 'ul');

  foreach ($menus as $menu)
  {
    $menu->append_to ($mobile_list, $current_menu_highlight, false);
  }
}

function TopMenu_append_login_forms ($top_menu, $action_suffix)
{
  $modal = HTML_append_element ($top_menu, 'div');
  $modal->setAttribute ('class', 'remodal remodalsuggest modallogin');
  $modal->setAttribute ('data-remodal-id', 'modallogin');
  $modal_div = HTML_append_element ($modal, 'div');
  $modal_div->setAttribute ('class', 'indexloginform');

  $login_normal = HTML_append_element ($modal_div, 'div');
  $divider = HTML_append_element ($modal_div, 'div');
  $login_bnet = HTML_append_element ($modal_div, 'div');

  $login_normal->setAttribute ('style', 'float:left;');
  TopMenu_append_normal_login_form ($login_normal, $action_suffix);

  $login_bnet->setAttribute ('style', 'float:left; padding: 1.5em;');
  TopMenu_append_bnet_login_form ($login_bnet, $action_suffix);

  $divider->setAttribute ('style', 'float:left;');
  HTML_append_element ($divider, 'hr')
    ->setAttribute ('class', 'vertical');
  $divider_or = HTML_append_element ($divider, 'p');
  $divider_or->setAttribute ('class', 'blogeven');
  HTML_append_text ($divider_or, 'or');
  HTML_append_element ($divider, 'hr')
    ->setAttribute ('class', 'vertical');

  $registerform = HTML_append_element ($top_menu, 'form');
  $registerform->setAttribute ('name', 'registerform');
  $registerform->setAttribute ('action', '/index.php?page=register&' . $action_suffix);
  $registerform->setAttribute ('method', 'POST');
  $registerform_username = HTML_append_element ($registerform, 'input');
  $registerform_username->setAttribute ('type', 'hidden');
  $registerform_username->setAttribute ('name', 'username');
  $registerform_firstclick = HTML_append_element ($registerform, 'input');
  $registerform_firstclick->setAttribute ('type', 'hidden');
  $registerform_firstclick->setAttribute ('name', 'firstclick');
  $registerform_firstclick->setAttribute ('value', 'true');

  $loading_screen = HTML_append_element ($modal, 'div');
  $loading_screen->setAttribute ('class', 'indexloadingscreen');
  $loading_screen->setAttribute ('style', 'width:570px;height:205px;display:none');
  HTML_append_element ($loading_screen, 'img')
    ->setAttribute ('src', '/images/loading.gif');
}

function TopMenu_append_user_dropdown ($where, $user, $action_suffix)
{
  $dropdown = HTML_append_element ($where, 'div');
  $dropdown->setAttribute ('class', 'userdd');
  $dropdown_row = HTML_append_elements ($dropdown, ['table', 'tr']);

  $dropdown_icon = HTML_append_elements ($dropdown_row, ['td', 'img']);
  $dropdown_icon->setAttribute ('class', 'usericonsmall');
  $dropdown_icon->setAttribute ('src', User_icon_url ($user));

  $dropdown_name_cell = HTML_append_element ($dropdown_row, 'td');
  $dropdown_name_cell->setAttribute ('style', 'padding-left: 5px');

  $dropdown_name_link = HTML_append_link_with_text
    ($dropdown_name_cell, $user->Name . ' ▾', '?page=profile');
  $dropdown_name_link->setAttribute ('class', 'langselector');

  $dropdown_content = HTML_append_element ($dropdown_name_cell, 'div');
  $dropdown_content->setAttribute ('class', 'userdd-content');

  TopMenu_user_dropdown_elem
    ($dropdown_content, 'profile', 'profile', _ ('UM_BTProfile'));
  TopMenu_user_dropdown_elem
    ($dropdown_content, 'collection', 'collection', _ ('UM_PetCollection'));

  $new_strategy_comments = User_unread_strategy_comment_count ($user);
  TopMenu_user_dropdown_elem
    ( $dropdown_content
    , 'strategies', 'strategies'
    , 'My Strategies' . Util_maybe_braced_count ($new_strategy_comments)
    );

  $new_comments = User_unread_comment_count ($user);
  TopMenu_user_dropdown_elem
    ( $dropdown_content
    , 'mycomments', 'comments'
    , _ ('UM_BTComments') . Util_maybe_braced_count ($new_comments)
    );

  $messages_elem = TopMenu_user_dropdown_elem
    ($dropdown_content, 'messages', 'messages', _ ('UM_BTMessages'));

  $new_private_messages = User_unread_private_message_count ($user);
  $messages_elem_in = HTML_append_element ($messages_elem, 'span');
  $messages_elem_in->setAttribute ('id', 'topmsgsin');
  HTML_append_text ($messages_elem_in, $new_private_messages ? ' (' : '');
  $messages_elem_count = HTML_append_element ($messages_elem, 'span');
  $messages_elem_count->setAttribute ('id', 'topmsgscount');
  HTML_append_text ($messages_elem_count, $new_private_messages ? $new_private_messages : '');
  $messages_elem_out = HTML_append_element ($messages_elem, 'span');
  $messages_elem_out->setAttribute ('id', 'topmsgsout');
  HTML_append_text ($messages_elem_out, $new_private_messages ? ')' : '');

  TopMenu_user_dropdown_elem
    ($dropdown_content, 'settings', 'settings', _ ('UM_BTSettings'));
  if (User_is_allowed ($user, 'AdmPanel'))
  {
    TopMenu_user_dropdown_elem
    ($dropdown_content, 'admin', 'admin', 'Administration');
  }
    if (User_is_allowed ($user, 'LocArticles'))
  {
    TopMenu_user_dropdown_elem
    ($dropdown_content, 'loc', 'loc', 'Localization');
  }

  TopMenu_user_dropdown_elem
    ($dropdown_content, 'logout&' . $action_suffix, 'bye', _ ('UM_BTLogout'));
}

function TopMenu_append_language_dropdown ($where, $active_language)
{
  global $Localization_possible_languages;

  $dropdown = HTML_append_element ($where, 'div');
  $dropdown->setAttribute ('class', 'langdropdown');

  $span = HTML_append_element ($dropdown, 'span');
  $link = HTML_append_link_with_text
    ( $span
    , Localization_display_string_for_language ($active_language) . ' ▾'
    , Localization_current_page_in_language ($active_language)
    );
  $link->setAttribute ('class', 'langselector');

  $content = HTML_append_element ($dropdown, 'div');
  $content->setAttribute ('class', 'langdropdown-content');

  foreach ($Localization_possible_languages as $other)
  {
    if ($active_language == $other)
    {
      continue;
    }

    $elem = HTML_append_element ($content, 'div');
    $elem->setAttribute ('class', 'langdropdownelement');
    $link = HTML_append_link_with_text
      ( $elem
      , Localization_display_string_for_language ($other)
      , Localization_current_page_in_language ($other)
      );
    $link->setAttribute ('class', 'langselectorsmall');
  }
}

function TopMenu_create_html ($menus, $user, $action_suffix, $current_menu_highlight, $current_language)
{
  $top_menu = HTML_create_root ('div');
  $top_menu->setAttribute ('class', 'remodal-bg wrapper');

  TopMenu_append_corner ($top_menu);
  TopMenu_append_menu ($top_menu, $menus, $current_menu_highlight);

  $top_menu_rhs = HTML_append_element ($top_menu, 'div');
  $top_menu_rhs->setAttribute ('class', 'searcher');
  $top_menu_rhs_section = HTML_append_element ($top_menu_rhs, 'section');
  $top_menu_rhs_section->setAttribute ('class', 'top-menu');
  $top_menu_rhs_table = HTML_append_element ($top_menu_rhs_section, 'table');
  $top_menu_rhs_table->setAttribute ('width', '100%');
  $top_menu_rhs_table->setAttribute ('align', 'right');
  $top_menu_rhs_table_row = HTML_append_element ($top_menu_rhs_table, 'tr');

  if (!$user)
  {
    $top_menu_login = HTML_append_element ($top_menu_rhs_table_row, 'td');
    $top_menu_login->setAttribute ('style', 'padding-right: 20px');

    $top_menu_login_link = HTML_append_link_with_text ($top_menu_login, _ ('UL_MBLogin'), '#modallogin');
    $top_menu_login_link->setAttribute ('class', 'langselector');
    $top_menu_login_link->setAttribute ('style', 'display:block');
    $top_menu_login_link->setAttribute ('onclick', 'hideloadingbnetlogin()');

    TopMenu_append_login_forms ($top_menu, $action_suffix);
  }

  TopMenu_append_search_field ($top_menu_rhs_table_row, $action_suffix);

  $top_menu_rightmost_cell = HTML_append_element ($top_menu_rhs_table_row, 'td');
  $top_menu_rightmost_cell->setAttribute ('style', 'padding-left: 20px');

  if ($user)
  {
    TopMenu_append_user_dropdown ($top_menu_rightmost_cell, $user, $action_suffix);
  }
  else
  {
    TopMenu_append_language_dropdown ($top_menu_rightmost_cell, $current_language);
  }

  return $top_menu;
}
