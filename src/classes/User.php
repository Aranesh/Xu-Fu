<?php

include_once ('classes/Database.php');
include_once ('classes/Localization.php');
include_once ('classes/functions.php');

function User_icon_url ($user)
{
  return !$user->UseWowAvatar
    ? ('/images/pets/' . $user->Icon . '.png')
    : ('/images/userpics/' . $user->id . '.jpg?lastmod=' . $user->IconUpdate);
}

function User_unread_strategy_comment_count ($user)
{
  return Database_query_single
    ('SELECT SUM(NewComs) FROM Alternatives WHERE Deleted = 0 AND User = ' . $user->id);
}

function User_unread_comment_count ($user)
{
  return Database_query_single
    ('SELECT COUNT(*) FROM Comments WHERE User = ' .$user->id . ' AND NewActivity = 1');
}

function User_unread_private_message_count ($user)
{
  return Database_query_single ( 'SELECT COUNT(*) '
                               . 'FROM UserMessages '
                               . 'WHERE DeletedRec = 0 '
                                 . 'AND Seen = 0 '
                                 . 'AND Receiver = ' . $user->id
                               );
}

function User_is_allowed ($user, ...$permissions)
{
  $userrights = format_userrights ($user->Rights);

  foreach ($permissions as $permission)
  {
    if ( !array_key_exists ($permission, $userrights)
      || $userrights[$permission] === 'off'
      || $userrights[$permission] === 'no'
       )
    {
      return false;
    }
  }
  return true;
}

//! \todo Why is this not just a random blob?
function User_make_cookiehash ($user)
{
  if ($user->Email)
  {
    $snippets = explode ('@', $user->Email);
    return md5 ( mb_substr ($user->Hash, 7, 5)
               . mb_substr ($user->Name, 0, 2)
               . mb_substr ($snippets[0], -7)
               . "2d8s2f"
               . mb_substr ($snippets[1], 0, 4)
               );
  }
  else
  {
    return md5 ( mb_substr ($user->Hash, 7, 5)
               . "2d8s2f5x"
               . mb_substr ($user->Name, 0, 2)
               );
  }
}

function User_make_unique_name ($preferred)
{
  $name = $preferred == '' ? uniqid() : $preferred;
  while (Database_query_single ('SELECT COUNT(*) FROM Users WHERE Name = \'' . $name . '\'') > 0)
  {
    $name = $name . '-2';
  }
  return $name;
}

function User_create ($preferred_name, $language, $region)
{
  global $user_ip_adress;

  $newuserid = Database_insert
                 ( 'Users', ['Name', 'NameChange', 'ComSecret', 'regip', 'Language', 'Region', 'Icon']
                 , User_make_unique_name ($preferred_name)
                 , 1
                 , rand (0, 999999999)
                 , $user_ip_adress
                 , $language
                 , $region
                 , Util_random_pet_icon_id()
                 );

  $wlcmsgmsg = Localization_string ("Thank you for signing up with Xu-Fu's Pet Guides!<br><br>There are many new options available to you now, most of which you can find in your account area right here.<br>I suggest going to your Pet Collection first to make sure your pet library has been imported correctly. This will power a lot of the more advanced features here!<br><br>If you go to My Profile, you can select what other battlers see when clicking on your profile. Maybe leave them a little introduction about yourself? Or just turn off all areas if you prefer a bit more privacy.<br><br>You might also want to check out the Settings. There you can not only change your profile picture but also find options for your saved email, password or preferred language.<br><br>I hope you have a pleasant stay here and wish you all the best in your battles!<br><br>Yours,<br>Xu-Fu");
  $wlcmsgmsg = preg_replace('#<br\s*/?>#i', "\n", $wlcmsgmsg);
  Database_insert ( 'UserMessages', ['Sender', 'Receiver', 'Subject', 'Content', 'Type', 'Growl']
                  , 1
                  , $newuserid
                  , Database_escape_string (Localization_string ("Welcome to Xu-Fu's Pet Guides!"))
                  , Database_escape_string ($wlcmsgmsg)
                  , 1
                  , 9
                  );

  return $newuserid;
}
