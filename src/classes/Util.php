<?php

function Util_maybe_braced_count ($count)
{
  return $count ? (' (' . $count . ')') : '';
}

function Util_require_integer ($text)
{
  if (!preg_match("/^[1234567890]*$/is", $text))
  {
    throw new Exception ("Not a positive number: " . $text);
  }
}

function Util_require_integer_lt ($text, $max)
{
  Util_require_integer ($text);
  if ($text >= $max)
  {
    throw new Exception ("Number not smaller than " . $max . ": " . $text);
  }
}

function Util_require_level ($text)
{
  Util_require_integer ($text);
  if ($text < 1 || $text > 25)
  {
    throw new Exception ("Bad level: " . $text);
  }
}

function Util_require_comparison_operator ($text)
{
  if ($text != "<" && $text != ">" && $text != "=")
  {
    throw new Exception ("Bad comparison operator: " . $text);
  }
}
function Util_evaluate_comparison ($operator, $actual, $expected)
{
  Util_require_comparison_operator ($operator);
  if ($operator === '<')
    return $actual < $expected;
  else if ($operator === '=')
    return $actual == $expected;
  else
    return $actual > $expected;
}


function Util_is_special_pet_id_level ($id)
{
  return $id == 0;
}
function Util_is_special_pet_id_any ($id)
{
  return $id == 1;
}
function Util_is_special_pet_id_family ($id)
{
  return $id > 10 && $id <= 20;
}
function Util_is_special_pet_id ($id)
{
  return $id <= 20;
}
function Util_is_actual_pet_id ($id)
{
  return $id > 20;
}

function Util_pet_icon_small ($id)
{
  $prefix = '/images/pets/resize50/';
  $id_path = $prefix . $id . '.png';
  if (Util_is_special_pet_id_level ($id)) return $prefix . 'level.png';
  else if (Util_is_special_pet_id_any ($id)) return  $prefix . 'any.png';
  else if (file_exists ($_SERVER['DOCUMENT_ROOT'] . $id_path)) return $id_path;
  else return $prefix . 'unknown.png';
}

function Util_all_available_pet_icon_ids()
{
  // We want an ID entry that also is Util_is_actual_pet_id(). Since
  // we know that there are no ids with two digits to begin with, we
  // just request two digits (because then the text entries are out)
  // and then at least one more letter until we stop caring.
  $all = glob ( $_SERVER['DOCUMENT_ROOT'] . '/images/pets/[0-9][0-9]?*.png'
              , GLOB_NOSORT
              );
  if (!$all)
  {
    throw new Exception
      ('all_available_pet_icon_ids() was unable to find any file');
  }

  return array_map
    (function ($path) { return basename ($path, '.png'); }, $all);
}
function Util_random_pet_icon_id()
{
  $available = Util_all_available_pet_icon_ids();
  return $available[array_rand ($available)];
}

const Util_family_pet_id_to_family = [ 11 => 0
                                     , 12 => 5
                                     , 13 => 6
                                     , 14 => 3
                                     , 15 => 9
                                     , 16 => 2
                                     , 17 => 4
                                     , 18 => 8
                                     , 19 => 7
                                     , 20 => 1
                                     ];
