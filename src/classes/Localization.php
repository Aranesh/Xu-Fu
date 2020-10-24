<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

include_once ('classes/functions.php');
include_once ('Database.php');

function Localization_subdomain_for_language ($language)
{
  $sub = strtolower (decode_language ($language)['short']);
  if ($sub == 'kr') $sub = 'ko';
  if ($sub == 'tw') $sub = 'zh';
  return $sub;
}

function Localization_display_string_for_language ($language)
{
  return decode_language ($language)['long'];
}

function Localization_current_page_in_language ($language)
{
  return 'https://' . Localization_subdomain_for_language ($language) . '.wow-petguide.com' . $_SERVER['REQUEST_URI'];
}

function Localization_fight ($id, $fallback)
{
  return loc_mm ($id, $fallback, true);
}
function Localization_category ($id, $fallback)
{
  return loc_mm ($id, $fallback);
}
function Localization_string ($name)
{
  return __ ($name);
}

function Localization_language_to_region ($language)
{
  return $language === 'en_US' ? 'us'
       : $language === 'pt_BR' ? 'us'
       : $language === 'es_MX' ? 'us'
       : $language === 'ko_KR' ? 'kr'
       : $language === 'zh_TW' ? 'tw'
       : 'eu'
       ;
}

function Localization_pet_name_column ($language)
{
  return $language == 'en_US' ? 'Name' : ('Name_' . $language);
}

function Localization_pet_name ($id, $language)
{
  if (!is_numeric ($id))
  {
    throw new \Exception ('bad id ' . $id);
  }
  $column = Localization_pet_name_column ($language);
  $fallback_column = Localization_pet_name_column ('en_US');
  return Database_query_single
    ( 'SELECT IFNULL ( ( SELECT COALESCE ( NULLIF (' . $column . ', "")'
                                      . ', NULLIF (' . $fallback_column . ', "")'
                                      . ') '
                      . 'FROM PetsUser '
                      . 'WHERE PetID = ' . $id . ' '
                      . 'LIMIT 1'
                    . ')'
                    . ', "missing pet name ' . $id . '" '
                  . ')'
    );
}

function Localization_spell_name ($id, $language)
{
  if (!is_numeric ($id))
  {
    throw \Exception ('bad id ' . $id);
  }
  $column = Database_escape_string ($language);
  return Database_query_single
    ('SELECT ' . $column . ' FROM Pet_Abilities WHERE id = ' . $id . ' LIMIT 1');
}

$Localization_possible_languages = ['de_DE', 'en_US', 'es_ES', 'it_IT', 'fr_FR', 'ru_RU', 'pt_BR', 'ko_KR', 'zh_TW'];
