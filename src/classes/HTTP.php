<?php namespace HTTP; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

function redirect_and_die ($location)
{
  if (headers_sent())
  {
    echo ('<meta http-equiv="refresh" content="0; URL=' . $location . '">');
  }
  else
  {
    header ('Location: ' . $location);
  }
  die();
}

function has_GET ($name)
{
  global $_GET;
  return array_key_exists ($name, $_GET);
}
function has_POST ($name)
{
  global $_POST;
  return array_key_exists ($name, $_POST);
}

function argument_GET ($name)
{
  global $_GET;
  if (!has_GET ($name))
  {
    throw new \InvalidArgumentException
      ('argument ' . $name . ' missing in GET');
  }

  return $_GET[$name];
}
function argument_GET_or_default ($name, $default)
{
  global $_GET;
  return !has_GET ($name) ? $default : $_GET[$name];
}

function argument_POST ($name)
{
  global $_POST;
  if (!has_POST ($name))
  {
    throw new \InvalidArgumentException
      ('argument ' . $name . ' missing in POST');
  }

  return $_POST[$name];
}
function argument_POST_or_default ($name, $default)
{
  global $_POST;
  return !has_POST ($name) ? $default : $_POST[$name];
}

function argument_POST_or_GET_or_default ($name, $default)
{
  global $_POST;
  return !has_POST ($name)
      ? argument_GET_or_default ($name, $default)
      : $_POST[$name];
}

function set_cookie ($name, $value, $duration)
{
  setcookie ($name, $value, time() + $duration, '/', '.wow-petguide.com');
}
