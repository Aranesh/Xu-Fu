<?php namespace BattleNet; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('Client.php');
require_once ('Database.php');
require_once ('HTTP.php');
require_once ('Localization.php');
require_once ('Time.php');
require_once ('User.php');

class OAuthException extends \Exception
{
  public $toast;
  public $what;
  function __construct ($toast, $what) { $this->toast = $toast; $this->what = $what; }
};

class OAuth
{
  private $api;
  private $regionselect;
  private $scope;
  public $is_authed = false;
  public $locale;

  function __construct ($regionselect, $command, $url)
  {
    $this->regionselect = $regionselect;
    switch ($regionselect)
    {
    case 'china':
      $apiregion = 'CN';
      $locale = 'zh_TW';
      break;
    case 'eu':
      $apiregion = 'EU';
      $locale = 'en_GB';
      break;
    case 'us':
      $apiregion = 'US';
      $locale = 'en_US';
      break;
    case 'kr':
      $apiregion = 'KR';
      $locale = 'ko_KR';
      break;
    case 'tw':
      $apiregion = 'TW';
      $locale = 'zh_TW';
      break;
    default:
      $apiregion = 'US';
      $locale = 'en_US';
      break;
    }
    $this->locale = $locale;

    $this->api = new \OAuth2\oauthApi ( battlenet_api_client_id
                                      , battlenet_api_client_secret
                                      , $apiregion
                                      , $locale
                                      , 'https://' . $_ENV['SERVER_NAME']
                                      . $url . '&command=' . $command
                                      );

    if (\HTTP\argument_GET_or_default ('command', FALSE) === $command)
    {
      $this->handle_auth();
    }
  }

  function auth_url()
  {
    return $this->api->getAuthenticationUrl
      (array ('state' => 'XuFu', 'regionselect' => $this->regionselect));
  }

  function handle_auth()
  {
    if (\HTTP\argument_GET_or_default ('error', FALSE) === 'access_denied')
    {
      throw new OAuthException ('bnetregfail', 'access_denied');
    }

    if (\HTTP\argument_GET ('state') !== 'XuFu')
    {
      throw new OAuthException ('bnetregfail', 'tampering');
    }

    $response = $this->api->getAccessToken ( \OAuth2\oauthApi::GRANT_TYPE_AUTH_CODE
                                           , array ( 'code' => \HTTP\argument_GET ('code')
                                                   , 'auth_flow' => 'auth_code'
                                                   , 'state' => 'XuFu'
                                                   )
                                           );

    if ($response['http_code'] !== 200)
    {
      throw new OAuthException ('bnetapierror', 'unable to get auth code token');
    }

    $this->api->setAccessToken ($response['access_token']);
    $this->scope = $response['scope'];

    $this->is_authed = true;
  }

  function has_wow_access()
  {
    if (!$this->is_authed)
    {
      throw OAuthException ('bnetapierror', 'not authed');
    }

    //! \todo will break when oauth wrapper supports other scopes again
    //! \todo why is cn blocked?
    return $this->regionselect !== 'china' && $this->scope === 'wow.profile';
  }

  function maybe_fetch ($what, $region = FALSE)
  {
    if ($region !== FALSE)
    {
      $this->api->set_region (strtoupper($region));
    }

    $info = $this->api->fetch ($what);

    if ($info['http_code'] !== 200)
    {
      return FALSE;
    }
    unset ($info['http_code']);

    return $info;
  }
  function fetch ($what, $region = FALSE)
  {
    $info = $this->maybe_fetch ($what, $region);
    if ($info === FALSE)
    {
      throw new OAuthException ('bnetapierror', 'unable to get ' . $what . ' data');
    }
    return $info;
  }
};

/*sendtoast, user*/ function login_or_register ($language)
{
  try
  {
    $step = \HTTP\argument_GET_or_default ('command', 'redirect');

    $regionselect = \HTTP\argument_POST_or_GET_or_default ('regionselect', 'standard');
    $oauth = new OAuth ($regionselect, 'login', '/index.php?page=bnetlogin');

    if (!$oauth->is_authed)
    {
      \HTTP\redirect_and_die ($oauth->auth_url());
    }

    $useraccinfo = $oauth->fetch ('account');
    $bnetid = $useraccinfo['id'];
    $battletag = $useraccinfo['battletag'];

    $region = Localization_language_to_region ($language);
    $wowaccess = $oauth->has_wow_access();

    $bnetuser = Database_query_maybe_object
              ( 'SELECT User, id FROM UserBnet '
                . 'WHERE Region ' . ($region === 'cn' ? '=' : '!=') . '\'cn\' '
                . 'AND BnetID = \'' . $bnetid . '\''
              );
    $is_new_register = $bnetuser === FALSE;

    if ($is_new_register)
    {
      $user_id = User_create ($battletag, $language, $region);

      Database_insert ( 'UserBnet'
                      , ['User', 'BnetID', 'BattleTag', 'Region', 'WoWAccess']
                      , $user_id
                      , $bnetid
                      , $battletag
                      , $region
                      , $wowaccess
                      );

      $sendtoast = 'bnetregister';
    }
    else
    {
      $user_id = $bnetuser->User;

      Database_query ( 'UPDATE UserBnet '
                     . 'SET `WoWAccess` = \'' . (int) $wowaccess . '\' '
                     . 'WHERE id = \'' . $bnetuser->id . '\''
                     );

      $sendtoast = 'loginsuccess';
    }

    $user = Database_query_object ( 'SELECT * FROM Users '
                                  . 'WHERE id = \'' . $user_id . '\''
                                  );

    $_SESSION['logged_in'] = 'true';
    $_SESSION['userid'] = $user->id;

    $cookiehash = User_make_cookiehash ($user);

    \HTTP\set_cookie ('language_delimiter', base64_encode ($user->id), 4 * \Time\week_to_s);
    \HTTP\set_cookie ('language_stock', $cookiehash, 4 * \Time\week_to_s);

    Database_query ( 'UPDATE Users '
                   . 'SET `CHash` = \'' . $cookiehash . '\' '
                   . 'WHERE id = \'' . $user->id . '\''
                   );

    if ($is_new_register)
    {
      Database_protocol_user_activity_with_request
        ($user, 0, 'Registration completed', 'Via Battle.net');
    }
    else
    {
      Database_protocol_user_activity_with_request
        ($user, 1, 'Successful Login', 'Via Battle.net');
    }
    Database_protocol_user_activity
      ($user, 2, 'Battle.net Check Done', 'Successful');

    return [$sendtoast, $user];
  }
  catch (OAuthException $e)
  {
    echo '<!-- exception: ' . $e->what . ' -->';
    return [$e->toast, FALSE];
  }
  catch (\Exception $e)
  {
    echo '<!-- exception: ' . $e . ' -->';
    return ['bnetapierror', FALSE];
  }
}
