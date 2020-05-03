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
    ('SELECT SUM(NewComs) FROM Alternatives WHERE User = ' . $user->id);
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

  $wlcmsgmsg = Localization_string ('UM_WelcContent');
  $wlcmsgmsg = preg_replace('#<br\s*/?>#i', "\n", $wlcmsgmsg);
  Database_insert ( 'UserMessages', ['Sender', 'Receiver', 'Subject', 'Content', 'Type', 'Growl']
                  , 1
                  , $newuserid
                  , Database_escape_string (Localization_string ('UM_WelcSubject'))
                  , Database_escape_string ($wlcmsgmsg)
                  , 1
                  , 9
                  );

  return $newuserid;
}
