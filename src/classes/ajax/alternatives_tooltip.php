<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

// error_reporting (E_ALL | E_WARNINGS);
// ini_set ('display_errors', 1);

require_once ('BBCode.php');
require_once ('Database.php');
require_once ('functions.php');
require_once ('HTML.php');
require_once ('HTTP.php');
require_once ('Localization.php');
require_once ('Time.php');
require_once ('User.php');
require_once ('Util.php');

$userid = \HTTP\argument_GET ('userid');
$user = $userid ? Database_SELECT_object ('Users', 'WHERE id = ?', 'i', $userid) : NULL;

if ($user) {
  $userrights = format_userrights($user->Rights);
}

$all_pets = get_all_pets('Name', 1);
$all_pets[0]['PetID'] = "0";
$all_pets[1]['PetID'] = "1";
$stratid = \HTTP\argument_GET ('strat');

$language = \HTTP\argument_GET ('l');
Localization_initialize_global_state ($language);

$all_tags = get_all_tags();

$strats = calc_strats_rating($stratid, $language, $user, 1);

$tt_container = HTML_create_root ('div');
$tt_container->setAttribute ('class', 'alternative_tooltip');

$tooltips_holder = HTML_append_element ($tt_container, 'div');
$tooltips_holder->setAttribute ('class', 'tooltips_holder');

function stat_icon ($alt_tt_stats, $icon)
          {
  $img = HTML_append_element ($alt_tt_stats, 'img');
  $img->setAttribute ('src', '/images/icon_' . $icon . '.png');
}

$stratcount = 1;
foreach ($strats as $thisstrat)
{
  $tt_usercomment_id = 'tt_usercomment_' . $thisstrat->id;

  $tt_stratbox = HTML_append_element ($tt_container, 'div');
  $tt_stratbox->setAttribute ( 'class'
                             , 'entry'
                             . ($thisstrat->Active ? ' active' : '')
                             . ($thisstrat->Published ? ' published' : '')
                             . ($thisstrat->Owned ? ' owned' : '')
                             );

  $tt_e1_c_link = HTML_append_element ($tt_stratbox, 'a');
  $tt_e1_c_link->setAttribute ('href', '/?Strategy=' . $thisstrat->id);
  $tt_e1_c_link->setAttribute ('class', 'number');
  HTML_append_text (HTML_append_element ($tt_e1_c_link, 'div'), $stratcount);
  $stratcount++;
  $lowestlevel = 25;
  
  for ($i = 0; $i < 3; $i++)
  {
    $tt_levelpet_id = 'tt_levelpet_' . $i . '_' . $thisstrat->id;

              switch ($i)
              {
              case 0:
      $fetchpet = $thisstrat->PetID1;
      $levelreq = $thisstrat->PetLevel1;
                  break;
              case 1:
      $fetchpet = $thisstrat->PetID2;
      $levelreq = $thisstrat->PetLevel2;
                  break;
              case 2:
      $fetchpet = $thisstrat->PetID3;
      $levelreq = $thisstrat->PetLevel3;
        break;
      }
    
    if ($levelreq == ""){
      $levelreq = "1+";
    }
    if ($fetchpet == 0) {
      if (intval($levelreq) < $lowestlevel OR $lowestlevel == 25) {
        $lowestlevel = intval($levelreq);
      }
    }
    
    $pet_status_to_string
      = [-1 => '', 0 => ' match-missing', 1 => ' match-acceptable', 2 => ' match-perfect'];

    $alt_tt_e2 = HTML_append_element ($tt_stratbox, 'div');
    $alt_tt_e2->setAttribute
      ('class', 'peticon' . $pet_status_to_string[$thisstrat->PetStatus[$i]]);

    $alt_tt_pimg = $alt_tt_e2->ownerDocument->createElement ('img');
    $icon_id = $all_pets[$fetchpet]['PetID'];
    $alt_tt_pimg->setAttribute ('src', Util_pet_icon_small ($icon_id));

              if (Util_is_special_pet_id_level ($fetchpet))
              {
      $alt_tt_pimg->setAttribute ('class', 'tt_levelpet');
      $alt_tt_pimg->setAttribute ('data-tooltip-content', '#' . $tt_levelpet_id);
      $alt_tt_e2->appendChild ($alt_tt_pimg);

      $level_tt_span = HTML_append_element ($tooltips_holder, 'span');
      $level_tt_span->setAttribute ('id', $tt_levelpet_id);
      $level_tt_div = HTML_append_element ($level_tt_span, 'div');
      $level_tt_div->setAttribute ('style', 'width:100%');

      //! \note Ensure there is always a trailing +, but not two.
      $levelreq = rtrim ($levelreq ? $levelreq : '1', '+') . '+';

      HTML_append_text
        ( $level_tt_div
        , $language == 'fr_FR'
        ? ( Localization_string ('PetCardAnyPetName') . ' '
          . Localization_string ('PetCardAnyLevelES') . ' '
          . $levelreq
          )
        : ( Localization_string ('PetCardLevel') . ' '
          . $levelreq . ' '
          . Localization_string ('PetCardPet')
          )
        );
              }
              else if (Util_is_special_pet_id ($fetchpet))
              {
      $alt_tt_e2->appendChild ($alt_tt_pimg);
              }
              else
              {
      $link = HTML_append_element ($alt_tt_e2, 'a');
      $link->setAttribute ('href', 'http://www.wowhead.com/npc=' . $icon_id);
      $link->setAttribute ('target', '_blank');
      $link->appendChild ($alt_tt_pimg);
    }
  }

  $details_block = HTML_append_element ($tt_stratbox, 'a');
  $details_block->setAttribute ('class', 'details');
  $details_block->setAttribute ('href', '/?Strategy=' . $thisstrat->id);

  $alt_tt_link = HTML_append_element ($details_block, 'div');
  $alt_tt_link->setAttribute ('class', 'bottom');

  $alt_tt_link_img = HTML_append_element ($alt_tt_link, 'img');
  $alt_tt_link_img->setAttribute ('src', '/images/icon_alt_tt_link.png');

  if ($thisstrat->Highlight)
          {
    HTML_append_text ($alt_tt_link, $thisstrat->Highlight);
          }

  $alt_tt_stats = HTML_append_element ($details_block, 'div');
  $alt_tt_stats->setAttribute ('class', 'top');

  if ($thisstrat->NumberOfFavs > 0)
  {
    HTML_append_text ($alt_tt_stats, $thisstrat->NumberOfFavs);
    stat_icon ($alt_tt_stats, 'alt_tt_heart' . $thisstrat->IsFavorited);
  }
  if ($thisstrat->number_of_visible_comments > 0)
  {
    HTML_append_text ($alt_tt_stats, $thisstrat->number_of_visible_comments);
    stat_icon ($alt_tt_stats, 'alt_tt_com');
  }
  if ((float)$thisstrat->Rating > 0.)
  {
    HTML_append_text ($alt_tt_stats, $thisstrat->Rating);
    stat_icon ($alt_tt_stats, 'star_rating');
  }

  $used_tags = $all_tags;
  $active_tags_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = '$thisstrat->id'");
  while ($this_tag = $active_tags_db->fetch_object())
  {
    if ($all_tags[$this_tag->Tag]['ID']) {
      $used_tags[$this_tag->Tag]['Active'] = 1;
    }
  }


	$used_tags[7]['Name'] = $used_tags[7]['Name']." ".$lowestlevel."+";
  
  // Output of all regular tags 
  foreach ($used_tags as $tag_id) {
    if ( ($tag_id['Active'] == 1 && $tag_id['Visible'] == 1) 
      || ($tag_id['Active'] == 1 && ($user && ($userrights['EditStrats'] == "yes" || $userrights['EditTags'] == "yes")))
       ) { 
      $tag_div = HTML_append_element ($details_block, 'div');
      $tag_div->setAttribute ('class', 'tag');
      $tag_div->setAttribute ('style', 'background-color:#'.$tag_id['Color']);
      HTML_append_text ($tag_div, $tag_id['Name']);
    }	
  }

  
  
  //! \todo Just always emit to avoid script not finding stuff at runtime?
  if ($thisstrat->CreatorName || $thisstrat->CreatorComment)
  {
    $tt_stratbox->setAttribute
      ('class', $tt_stratbox->getAttribute ('class') . ' tt_usercomment');
    $tt_stratbox->setAttribute ('data-tooltip-content', '#' . $tt_usercomment_id);

    $usercomment_tt_span = HTML_append_element ($tooltips_holder, 'span');
    $usercomment_tt_span->setAttribute ('id', $tt_usercomment_id);
    $usercomment_tt_div = HTML_append_element ($usercomment_tt_span, 'div');
    $usercomment_tt_div->setAttribute ('style', 'width:100%');

    if ($thisstrat->CreatorName)
          {
      HTML_append_text ( HTML_append_element ($usercomment_tt_div, 'b')
                       , 'Strategy created by: ' . $thisstrat->CreatorName
                       );

      //! \todo Just use padding or ps instead?
      if ($thisstrat->CreatorComment)
      {
        HTML_append_element ($usercomment_tt_div, 'br');
        HTML_append_element ($usercomment_tt_div, 'br');
      }
    }
    if ($thisstrat->CreatorComment)
    {
      \BBCode\append_to_HTML ($usercomment_tt_div, $thisstrat->CreatorComment, $language);
    }
  }

  HTML_append_element ($tt_stratbox, 'div')
    ->setAttribute ('style', 'clear:both');
}

HTML_append_element ($tt_container, 'div')
  ->setAttribute ('style', 'clear:both');

$tt_script = HTML_append_element ($tt_container, 'script');
HTML_append_text
  ( $tt_script
  , '$(document).ready(function() {
       $(".tt_usercomment").tooltipster({
         maxWidth: "400",
         animation: "fade",
         theme: "tooltipster-smallnote"
        });
       $(".tt_levelpet").tooltipster({
         maxWidth: "400",
         animation: "fade",
         side: ["right"],
         theme: "tooltipster-smallnote"
        });
     });'
  );

echo (HTML_to_string ($tt_container));
