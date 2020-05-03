<?php namespace ADMIN; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('Database.php');
require_once ('BattleNet.php');
require_once ('Growl.php');

function import_single_pet ($species) {
    $dbcon = $GLOBALS['dbcon'];
    echo "<p class='blogodd'><b>Fetching data for pet <font color='blue'>species ".htmlentities($species)."</font>...</b><br><br>";
    $apipet = blizzard_api_pets_singlepet($species);
    if ($apipet == "error") {
        echo "<b>There was a problem connecting to the API. Please try again. <br>If this persists, please contact Aranesh.</b>";
        echo "<br><br><br>";
        return;
    }
    $apipet = json_decode($apipet, TRUE);
    // echo "<pre>";
    // print_r($apipet);

    $abilities = array();
    if ($apipet['abilities'][0]['required_level']) {
        $abilities[$apipet['abilities'][0]['required_level']]['id'] = $apipet['abilities'][0]['ability']['id'];
        $abilities[$apipet['abilities'][0]['required_level']]['Name'] = $apipet['abilities'][0]['ability']['name']['en_US'];
    }

    if ($apipet['abilities'][1]['required_level']) {
        $abilities[$apipet['abilities'][1]['required_level']]['id'] = $apipet['abilities'][1]['ability']['id'];
        $abilities[$apipet['abilities'][1]['required_level']]['Name'] = $apipet['abilities'][1]['ability']['name']['en_US'];
    }
    if ($apipet['abilities'][2]['required_level']) {
        $abilities[$apipet['abilities'][2]['required_level']]['id'] = $apipet['abilities'][2]['ability']['id'];
        $abilities[$apipet['abilities'][2]['required_level']]['Name'] = $apipet['abilities'][2]['ability']['name']['en_US'];
    }
    if ($apipet['abilities'][3]['required_level']) {
        $abilities[$apipet['abilities'][3]['required_level']]['id'] = $apipet['abilities'][3]['ability']['id'];
        $abilities[$apipet['abilities'][3]['required_level']]['Name'] = $apipet['abilities'][3]['ability']['name']['en_US'];
    }
    if ($apipet['abilities'][4]['required_level']) {
        $abilities[$apipet['abilities'][4]['required_level']]['id'] = $apipet['abilities'][4]['ability']['id'];
        $abilities[$apipet['abilities'][4]['required_level']]['Name'] = $apipet['abilities'][4]['ability']['name']['en_US'];
    }
    if ($apipet['abilities'][5]['required_level']) {
        $abilities[$apipet['abilities'][5]['required_level']]['id'] = $apipet['abilities'][5]['ability']['id'];
        $abilities[$apipet['abilities'][5]['required_level']]['Name'] = $apipet['abilities'][5]['ability']['name']['en_US'];
    }
    if ($abilities) {
        ksort($abilities);
    }


    echo "<b>Name:</b> ".$apipet['name']['en_US']."<br>";
    echo "<b>Family:</b> ".$apipet['battle_pet_type']['name']['en_US']." - Family-ID ".convert_family($apipet['battle_pet_type']['name']['en_US'])."<br>";
    echo "<br>";
    echo "<b>Skill 1:</b> <a class='wowhead' target='_blank' href='http://www.wowhead.com/petability=".$abilities[1]['id']."'>".$abilities[1]['Name']."</a> (".$abilities[1]['id'].")<br>";
    echo "<b>Skill 2:</b> <a class='wowhead' target='_blank' href='http://www.wowhead.com/petability=".$abilities[2]['id']."'>".$abilities[2]['Name']."</a> (".$abilities[2]['id'].")<br>";
    echo "<b>Skill 3:</b> <a class='wowhead' target='_blank' href='http://www.wowhead.com/petability=".$abilities[4]['id']."'>".$abilities[4]['Name']."</a> (".$abilities[4]['id'].")<br>";
    echo "<b>Skill 4:</b> <a class='wowhead' target='_blank' href='http://www.wowhead.com/petability=".$abilities[10]['id']."'>".$abilities[10]['Name']."</a> (".$abilities[10]['id'].")<br>";
    echo "<b>Skill 5:</b> <a class='wowhead' target='_blank' href='http://www.wowhead.com/petability=".$abilities[15]['id']."'>".$abilities[15]['Name']."</a> (".$abilities[15]['id'].")<br>";
    echo "<b>Skill 6:</b> <a class='wowhead' target='_blank' href='http://www.wowhead.com/petability=".$abilities[20]['id']."'>".$abilities[20]['Name']."</a> (".$abilities[20]['id'].")<br>";
    echo "<br>";

    echo "<b>Description:</b> ".$apipet['description']['en_US']."<br>";
    echo "<b>Source Type:</b> ".$apipet['source']['type']."<br>";
    echo "<b>Source Description:</b> ".$apipet['source']['name']['en_US']."<br>";
    echo "<br>";
    echo "<b>Can be captured:</b> ";
    if ($apipet['is_capturable'] == false) {
        echo "No<br>";
        $input['is_capturable'] = 0;
    }
    if ($apipet['is_capturable'] == true) {
        echo "Yes<br>";
        $input['is_capturable'] = 1;
    }

    echo "<b>Can be caged:</b> ";
    if ($apipet['is_tradable'] == false) {
        echo "No<br>";
        $input['is_tradable'] = 2;
    }
    if ($apipet['is_tradable'] == true) {
        echo "Yes<br>";
        $input['is_tradable'] = 1;
    }

    echo "<b>Can battle:</b> ";
    if ($apipet['is_battlepet'] == false) {
        echo "No<br>";
        $input['special'] = 1;
    }
    if ($apipet['is_battlepet'] == true) {
        echo "Yes<br>";
        $input['special'] = 0;
    }

    echo "<b>Alliance only:</b> ";
    if ($apipet['is_alliance_only'] == false) {
        echo "No<br>";
        $input['is_alliance_only'] = 0;
    }
    if ($apipet['is_alliance_only'] == true) {
        echo "Yes<br>";
        $input['is_alliance_only'] = 1;
    }

    echo "<b>Horde only:</b> ";
    if ($apipet['is_horde_only'] == false) {
        echo "No<br>";
        $input['is_horde_only'] = 0;
    }
    if ($apipet['is_horde_only'] == true) {
        echo "Yes<br>";
        $input['is_horde_only'] = 1;
    }

    echo "<br>";
    echo "<b>Name es_MX:</b> ".$apipet['name']['es_MX']."<br>";
    echo "<b>Name pt_BR:</b> ".$apipet['name']['pt_BR']."<br>";
    echo "<b>Name de_DE:</b> ".$apipet['name']['de_DE']."<br>";
    echo "<b>Name es_ES:</b> ".$apipet['name']['es_ES']."<br>";
    echo "<b>Name fr_FR:</b> ".$apipet['name']['fr_FR']."<br>";
    echo "<b>Name it_IT:</b> ".$apipet['name']['it_IT']."<br>";
    echo "<b>Name ru_RU:</b> ".$apipet['name']['ru_RU']."<br>";
    echo "<b>Name ko_KR:</b> ".$apipet['name']['ko_KR']."<br>";
    echo "<b>Name zh_TW:</b> ".$apipet['name']['zh_TW']."<br>";
    echo "<b>Name zh_CN:</b> ".$apipet['name']['zh_CN']."<br>";

    $checkpetdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE RematchID = '$species' LIMIT 1") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($checkpetdb) > "0") {
        Database_UPDATE
          ( 'PetsUser'
          , [ 'Name'
            , 'PetID'
            , 'Skill1'
            , 'Skill2'
            , 'Skill3'
            , 'Skill4'
            , 'Skill5'
            , 'Skill6'
            , 'Family'
            , 'Name_es_MX'
            , 'Name_pt_BR'
            , 'Name_de_DE'
            , 'Name_es_ES'
            , 'Name_fr_FR'
            , 'Name_it_IT'
            , 'Name_ru_RU'
            , 'Name_ko_KR'
            , 'Name_zh_TW'
            , 'Name_zh_CN'
            , 'Description_en_US'
            , 'Description_es_MX'
            , 'Description_pt_BR'
            , 'Description_de_DE'
            , 'Description_es_ES'
            , 'Description_fr_FR'
            , 'Description_it_IT'
            , 'Description_ru_RU'
            , 'Description_ko_KR'
            , 'Description_zh_TW'
            , 'Description_zh_CN'
            , 'Source_en_US'
            , 'Source_es_MX'
            , 'Source_pt_BR'
            , 'Source_de_DE'
            , 'Source_es_ES'
            , 'Source_fr_FR'
            , 'Source_it_IT'
            , 'Source_ru_RU'
            , 'Source_ko_KR'
            , 'Source_zh_TW'
            , 'Source_zh_CN'
            , 'Cageable'
            , 'Source_Type'
            , 'Special'
            , 'Capturable'
            , 'Horde_Only'
            , 'Alliance_Only'
            , 'Icon'
            ]
          , 'WHERE RematchID = ?'
          , 'siiiiiiisssssssssssssssssssssssssssssssssisiiiisi'
            , $apipet['name']['en_US']
            , $apipet['creature']['id']
            , $abilities[1]['id']
            , $abilities[2]['id']
            , $abilities[4]['id']
            , $abilities[10]['id']
            , $abilities[15]['id']
            , $abilities[20]['id']
            , $apipet['battle_pet_type']['name']['en_US']
            , $apipet['name']['es_MX']
            , $apipet['name']['pt_BR']
            , $apipet['name']['de_DE']
            , $apipet['name']['es_ES']
            , $apipet['name']['fr_FR']
            , $apipet['name']['it_IT']
            , $apipet['name']['ru_RU']
            , $apipet['name']['ko_KR']
            , $apipet['name']['zh_TW']
            , $apipet['name']['zh_CN']
            , $apipet['description']['en_US']
            , $apipet['description']['es_MX']
            , $apipet['description']['pt_BR']
            , $apipet['description']['de_DE']
            , $apipet['description']['es_ES']
            , $apipet['description']['fr_FR']
            , $apipet['description']['it_IT']
            , $apipet['description']['ru_RU']
            , $apipet['description']['ko_KR']
            , $apipet['description']['zh_TW']
            , $apipet['description']['zh_CN']
            , $apipet['source']['name']['en_US']
            , $apipet['source']['name']['es_MX']
            , $apipet['source']['name']['pt_BR']
            , $apipet['source']['name']['de_DE']
            , $apipet['source']['name']['es_ES']
            , $apipet['source']['name']['fr_FR']
            , $apipet['source']['name']['it_IT']
            , $apipet['source']['name']['ru_RU']
            , $apipet['source']['name']['ko_KR']
            , $apipet['source']['name']['zh_TW']
            , $apipet['source']['name']['zh_CN']
            , $input['is_tradable']
            , $apipet['source']['type']
            , $input['special']
            , $input['is_capturable']
            , $input['is_horde_only']
            , $input['is_alliance_only']
            , $apipet['icon']
            , $apipet['id']
          );
            Growl_show_notice ( 'Pet entry updated!', 5000 );
    }
    else {
        Database_INSERT_INTO
          ( 'PetsUser'
          , [ 'Name'
            , 'RematchID'
            , 'PetID'
            , 'Skill1'
            , 'Skill2'
            , 'Skill3'
            , 'Skill4'
            , 'Skill5'
            , 'Skill6'
            , 'Family'
            , 'Name_es_MX'
            , 'Name_pt_BR'
            , 'Name_de_DE'
            , 'Name_es_ES'
            , 'Name_fr_FR'
            , 'Name_it_IT'
            , 'Name_ru_RU'
            , 'Name_ko_KR'
            , 'Name_zh_TW'
            , 'Name_zh_CN'
            , 'Description_en_US'
            , 'Description_es_MX'
            , 'Description_pt_BR'
            , 'Description_de_DE'
            , 'Description_es_ES'
            , 'Description_fr_FR'
            , 'Description_it_IT'
            , 'Description_ru_RU'
            , 'Description_ko_KR'
            , 'Description_zh_TW'
            , 'Description_zh_CN'
            , 'Source_en_US'
            , 'Source_es_MX'
            , 'Source_pt_BR'
            , 'Source_de_DE'
            , 'Source_es_ES'
            , 'Source_fr_FR'
            , 'Source_it_IT'
            , 'Source_ru_RU'
            , 'Source_ko_KR'
            , 'Source_zh_TW'
            , 'Source_zh_CN'
            , 'Cageable'
            , 'Source_Type'
            , 'Special'
            , 'Capturable'
            , 'Horde_Only'
            , 'Alliance_Only'
            , 'Icon'
            ]
          , 'siiiiiiiisssssssssssssssssssssssssssssssssisiiiis'
            , $apipet['name']['en_US']
            , $species
            , $apipet['creature']['id']
            , $apipet['abilities'][0]['ability']['id']
            , $apipet['abilities'][1]['ability']['id']
            , $apipet['abilities'][2]['ability']['id']
            , $apipet['abilities'][3]['ability']['id']
            , $apipet['abilities'][4]['ability']['id']
            , $apipet['abilities'][5]['ability']['id']
            , $apipet['battle_pet_type']['name']['en_US']
            , $apipet['name']['es_MX']
            , $apipet['name']['pt_BR']
            , $apipet['name']['de_DE']
            , $apipet['name']['es_ES']
            , $apipet['name']['fr_FR']
            , $apipet['name']['it_IT']
            , $apipet['name']['ru_RU']
            , $apipet['name']['ko_KR']
            , $apipet['name']['zh_TW']
            , $apipet['name']['zh_CN']
            , $apipet['description']['en_US']
            , $apipet['description']['es_MX']
            , $apipet['description']['pt_BR']
            , $apipet['description']['de_DE']
            , $apipet['description']['es_ES']
            , $apipet['description']['fr_FR']
            , $apipet['description']['it_IT']
            , $apipet['description']['ru_RU']
            , $apipet['description']['ko_KR']
            , $apipet['description']['zh_TW']
            , $apipet['description']['zh_CN']
            , $apipet['source']['name']['en_US']
            , $apipet['source']['name']['es_MX']
            , $apipet['source']['name']['pt_BR']
            , $apipet['source']['name']['de_DE']
            , $apipet['source']['name']['es_ES']
            , $apipet['source']['name']['fr_FR']
            , $apipet['source']['name']['it_IT']
            , $apipet['source']['name']['ru_RU']
            , $apipet['source']['name']['ko_KR']
            , $apipet['source']['name']['zh_TW']
            , $apipet['source']['name']['zh_CN']
            , $input['is_tradable']
            , $apipet['source']['type']
            , $input['special']
            , $input['is_capturable']
            , $input['is_horde_only']
            , $input['is_alliance_only']
            , $apipet['icon']
          );
          Growl_show_notice ( 'Pet imported!', 5000 );
    }
    echo "<br><br>";
}


function import_single_spell ($spellid) {
    $dbcon = $GLOBALS['dbcon'];
    $token = blizzard_api_cache_token('eu', battlenet_api_client_id, battlenet_api_client_secret, 'data/blizzard_api_token.json');
    $query_path = '/wow/pet/ability/'.$spellid;
    $apispell = blizzard_api_query('eu', $query_path, $token);
    if ($apispell == "error") {
        return "error";
    }
    $apispell = json_decode($apispell, TRUE);

    foreach (['de_DE', 'fr_FR', 'it_IT', 'es_ES', 'pl_PL', 'pt_PT', 'ru_RU', 'es_MX', 'pt_BR', 'ko_KR', 'zh_TW'] as $locale)
        {
            $region = "eu";
            switch ($locale) {
                case "pt_BR":
                    $region = "us";
                    break;
                case "es_MX":
                    $region = "us";
                    break;
                case "ko_KR":
                    $region = "kr";
                    break;
                case "zh_TW":
                    $region = "tw";
                    break;
            }
            $query_path = '/wow/pet/ability/'.$spellid.'?locale='.$locale;
            $apispelll = blizzard_api_query($region, $query_path, $token);
            if ($apispelll == "error") {
                return "error";
            }
            $apispelll = json_decode($apispelll, TRUE);
            if ($apispelll['name'] == "") { $apispelll['name'] = " "; }
            $apispell[$locale]['name'] = $apispelll['name'];
        }
        if ($apispell['hideHints'] == FALSE) {
            $apispell['hideHints'] = "0";
        }
        if ($apispell['isPassive'] == FALSE) {
            $apispell['isPassive'] = "0";
        }

    $checkspelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$spellid' LIMIT 1") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($checkspelldb) > "0") {
        Database_UPDATE
          ( 'Spells'
          , [ 'Icon'
            , 'Family'
            , 'Cooldown'
            , 'Rounds'
            , 'Passive'
            , 'HideHints'
            , 'en_US'
            , 'de_DE'
            , 'fr_FR'
            , 'it_IT'
            , 'es_ES'
            , 'pl_PL'
            , 'pt_PT'
            , 'ru_RU'
            , 'es_MX'
            , 'pt_BR'
            , 'ko_KR'
            , 'zh_TW'
            ]
          , 'WHERE SpellID = ?'
          , 'ssiiiissssssssssssi'
            , $apispell['icon']
            , convert_family($apispell['petTypeId'])
            , $apispell['cooldown']
            , $apispell['rounds']
            , $apispell['isPassive']
            , $apispell['hideHints']
            , $apispell['name']
            , $apispell['de_DE']['name']
            , $apispell['fr_FR']['name']
            , $apispell['it_IT']['name']
            , $apispell['es_ES']['name']
            , $apispell['pl_PL']['name']
            , $apispell['pt_PT']['name']
            , $apispell['ru_RU']['name']
            , $apispell['es_MX']['name']
            , $apispell['pt_BR']['name']
            , $apispell['ko_KR']['name']
            , $apispell['zh_TW']['name']
          , $apispell['id']
          );
    }
    else {
        Database_INSERT_INTO
          ( 'Spells'
          , [ 'SpellID'
            , 'Icon'
            , 'Family'
            , 'Cooldown'
            , 'Rounds'
            , 'Passive'
            , 'HideHints'
            , 'en_US'
            , 'de_DE'
            , 'fr_FR'
            , 'it_IT'
            , 'es_ES'
            , 'pl_PL'
            , 'pt_PT'
            , 'ru_RU'
            , 'es_MX'
            , 'pt_BR'
            , 'ko_KR'
            , 'zh_TW'
            , 'PetSpell'
            ]
          , 'issiiiissssssssssssi'
            , $apispell['id']
            , $apispell['icon']
            , convert_family($apispell['petTypeId'])
            , $apispell['cooldown']
            , $apispell['rounds']
            , $apispell['isPassive']
            , $apispell['hideHints']
            , $apispell['name']
            , $apispell['de_DE']['name']
            , $apispell['fr_FR']['name']
            , $apispell['it_IT']['name']
            , $apispell['es_ES']['name']
            , $apispell['pl_PL']['name']
            , $apispell['pt_PT']['name']
            , $apispell['ru_RU']['name']
            , $apispell['es_MX']['name']
            , $apispell['pt_BR']['name']
            , $apispell['ko_KR']['name']
            , $apispell['zh_TW']['name']
            , "1"
          );
    }
    return $apispell;
}


function import_npc_pet ($species) {
    $dbcon = $GLOBALS['dbcon'];
    $token = blizzard_api_cache_token('eu', battlenet_api_client_id, battlenet_api_client_secret, 'data/blizzard_api_token.json');
    $query_path = '/wow/pet/species/'.$species;
    $apipet = blizzard_api_query('eu', $query_path, $token);
    if ($apipet == "error") {
        return "no_pet";
    }
    $apipet = json_decode($apipet, TRUE);

    $apipet['PetID'] = $apipet['creatureId'];
    $apipet['Name'] = $apipet['name'];
    $apipet['Family'] = convert_family($apipet['petTypeId']);

    $skills = [0, 0, 0, 0, 0, 0];
    foreach ($apipet['abilities'] as $abilities)
    {
      $skills[$abilities['order']] = $abilities['id'];
    }

    foreach (['de_DE', 'fr_FR', 'it_IT', 'es_ES', 'pl_PL', 'pt_PT', 'ru_RU', 'es_MX', 'pt_BR', 'ko_KR', 'zh_TW'] as $locale)
        {
            $region = "eu";
            switch ($locale) {
                case "pt_BR":
                    $region = "us";
                    break;
                case "es_MX":
                    $region = "us";
                    break;
                case "ko_KR":
                    $region = "kr";
                    break;
                case "zh_TW":
                    $region = "tw";
                    break;
            }
            $query_path = '/wow/pet/species/'.$species.'?locale='.$locale;
            $apipetl = blizzard_api_query($region, $query_path, $token);
            if ($apipetl == "error") {
                return "error";
            }
            $apipetl = json_decode($apipetl, TRUE);
            if ($apipetl['name'] == "") { $apipetl['name'] = " "; }
            $apipet[$locale]['name'] = $apipetl['name'];
        }

    $apipet['Name_de_DE'] = $apipet['de_DE']['name'];
    $apipet['Name_fr_FR'] = $apipet['fr_FR']['name'];
    $apipet['Name_it_IT'] = $apipet['it_IT']['name'];
    $apipet['Name_es_ES'] = $apipet['es_ES']['name'];
    $apipet['Name_pl_PL'] = $apipet['pl_PL']['name'];
    $apipet['Name_pt_PT'] = $apipet['pt_PT']['name'];
    $apipet['Name_ru_RU'] = $apipet['ru_RU']['name'];
    $apipet['Name_es_MX'] = $apipet['es_MX']['name'];
    $apipet['Name_pt_BR'] = $apipet['pt_BR']['name'];
    $apipet['Name_ko_KR'] = $apipet['ko_KR']['name'];
    $apipet['Name_zh_TW'] = $apipet['zh_TW']['name'];

    $checkpetdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE Species = '$species' LIMIT 1") or die(mysqli_error($dbcon));
    if (mysqli_num_rows($checkpetdb) > "0") {
        Database_UPDATE
          ( 'PetsNPC'
          , [ 'Name'
            , 'PetID'
            , 'Species'
            , 'Skill1'
            , 'Skill2'
            , 'Skill3'
            , 'Skill4'
            , 'Skill5'
            , 'Skill6'
            , 'Family'
            , 'Icon'
            , 'Name_de_DE'
            , 'Name_fr_FR'
            , 'Name_it_IT'
            , 'Name_es_ES'
            , 'Name_pl_PL'
            , 'Name_pt_PT'
            , 'Name_ru_RU'
            , 'Name_es_MX'
            , 'Name_pt_BR'
            , 'Name_ko_KR'
            , 'Name_zh_TW'
            ]
          , 'WHERE Species = ?'
          , 'siiiiiiiisssssssssssssi'
            , $apipet['name']
            , $apipet['creatureId']
            , $apipet['speciesId']
            , $skills[0], $skills[1], $skills[2], $skills[3], $skills[4], $skills[5]
            , convert_family($apipet['petTypeId'])
            , $apipet['icon']
            , $apipet['de_DE']['name']
            , $apipet['fr_FR']['name']
            , $apipet['it_IT']['name']
            , $apipet['es_ES']['name']
            , $apipet['pl_PL']['name']
            , $apipet['pt_PT']['name']
            , $apipet['ru_RU']['name']
            , $apipet['es_MX']['name']
            , $apipet['pt_BR']['name']
            , $apipet['ko_KR']['name']
            , $apipet['zh_TW']['name']
          , $apipet['speciesId']
          );
    }
    else {
        Database_INSERT_INTO
          ( 'PetsNPC'
          , [ 'Name'
            , 'PetID'
            , 'Species'
            , 'Skill1'
            , 'Skill2'
            , 'Skill3'
            , 'Skill4'
            , 'Skill5'
            , 'Skill6'
            , 'Family'
            , 'Icon'
            , 'Name_de_DE'
            , 'Name_fr_FR'
            , 'Name_it_IT'
            , 'Name_es_ES'
            , 'Name_pl_PL'
            , 'Name_pt_PT'
            , 'Name_ru_RU'
            , 'Name_es_MX'
            , 'Name_pt_BR'
            , 'Name_ko_KR'
            , 'Name_zh_TW'
            ]
          , 'siiiiiiiisssssssssssss'
            , $apipet['name']
            , $apipet['creatureId']
            , $apipet['speciesId']
            , $skills[0], $skills[1], $skills[2], $skills[3], $skills[4], $skills[5]
            , convert_family($apipet['petTypeId'])
            , $apipet['icon']
            , $apipet['de_DE']['name']
            , $apipet['fr_FR']['name']
            , $apipet['it_IT']['name']
            , $apipet['es_ES']['name']
            , $apipet['pl_PL']['name']
            , $apipet['pt_PT']['name']
            , $apipet['ru_RU']['name']
            , $apipet['es_MX']['name']
            , $apipet['pt_BR']['name']
            , $apipet['ko_KR']['name']
            , $apipet['zh_TW']['name']
          );
    }
    return $apipet;
}
