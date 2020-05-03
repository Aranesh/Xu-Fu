<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('Time.php');

function _do_access_log()
{
  global $_POST, $_GET, $dbcon, $connect_try_counter, $last_error;

  // echo '<!-- connect tries: ' . $connect_try_counter . ', error: ' . $last_error . ' -->';

  $page = array_key_exists ('page', $_POST) ? $_POST['page']
        : array_key_exists ('page', $_GET) ? $_GET['page']
        : '';
  file_put_contents ( $_SERVER['DOCUMENT_ROOT'] . "/super_secret/access_log.txt"
                    , date (DATE_RFC822, $_SERVER['REQUEST_TIME']) . " "
                    . ($dbcon ? 'S' : 'E') . ' '
                    . ceil ((microtime (true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000.0) . " "
                    . $_SERVER['REQUEST_URI'] . " "
                    . $page . " UA="
                    . $_SERVER['HTTP_USER_AGENT']
                    . "\n"
                    , FILE_APPEND | LOCK_EX
                    );
}
if ( strpos ($_SERVER['HTTP_USER_AGENT'], 'SemrushBot') !== FALSE
  || strpos ($_SERVER['HTTP_USER_AGENT'], 'MegaIndex.ru') !== FALSE
  || strpos ($_SERVER['HTTP_USER_AGENT'], 'TencentTraveler') !== FALSE
  || strpos ($_SERVER['HTTP_USER_AGENT'], 'istellabot') !== FALSE
  )
{
  die ("Sorry, please look at robots.txt");
}
if ( ( strpos ($_SERVER['HTTP_USER_AGENT'], 'bingbot') !== FALSE
    || strpos ($_SERVER['HTTP_USER_AGENT'], 'DotBot') !== FALSE
     )
   && ( strpos ($_SERVER['REQUEST_URI'], '?Comment=') !== FALSE
     || strpos ($_SERVER['REQUEST_URI'], 'CTEnter=') !== FALSE
     || strpos ($_SERVER['REQUEST_URI'], 'CTID=') !== FALSE
     || strpos ($_SERVER['REQUEST_URI'], '=acretrieve') !== FALSE
     || strpos ($_SERVER['REQUEST_URI'], '=pwrecover') !== FALSE
      )
   )
{
  die ("Sorry, please look at robots.txt");
}
//register_shutdown_function ('_do_access_log');
//die ("Sorry, the site is experiencing a lot of pressure right now. Please give it some minutes to calm down.");

$dbcon = null;

$max_connect_tries = 40;
$sleep_per_failed_try = 200 * \Time\ms_to_us;

$last_error = 0;
$connect_try_counter = 0;
while (!$dbcon && $connect_try_counter < $max_connect_tries)
{
  $dbcon = @mysqli_connect (database_host, database_user, database_password, database_name);
  $last_error = mysqli_connect_errno();

  if (!$dbcon)
  {
    if ($last_error != 1203)
    {
      break;
    }

    usleep ($sleep_per_failed_try);
  }

  $connect_try_counter++;
}

if (!$dbcon)
{
  die ( '<br/><br/>Creating a database connection failed. This is a known issue '
      . 'and is happening due to increased load at the time. Please reload the '
      . 'page to try again. We are sorry for the inconvenience.<br/><br/>'
      . ($last_error !== 1203 ? ($last_error . " - " . mysqli_connect_error ($dbcon))  : '')
      );
}

mysqli_set_charset ($dbcon, "utf8");

// -- database documentation

// ALTER TABLE `UserCollection` ADD INDEX (`User`);
// ALTER TABLE `Comments` ADD INDEX(`Category`);
// ALTER TABLE `Comments` ADD INDEX( `Deleted`);
// ALTER TABLE `Comments` ADD INDEX( `Parent`);
// ALTER TABLE `Comments` ADD INDEX( `Language`);
// ALTER TABLE `Comments` ADD INDEX( `SortingID`);
// ALTER TABLE `UserProtocol` ADD INDEX( `User`);
