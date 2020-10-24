<?php

function blizzard_api_cache_token($zone, $client_id, $client_secret, $cache_file) {
    if(!file_exists($cache_file)) {
        file_put_contents($cache_file, '{}');
    }

    $fp = fopen($cache_file, 'r+');
    $token = null;
    
    if(flock($fp, LOCK_EX)) {
        
        $cache = json_decode(fread($fp, filesize($cache_file)), true);

        if(!isset($cache[$zone]) || time() > $cache[$zone]['valid_until']) {
            try {
                $token_data = json_decode(blizzard_api_request_token($zone, $client_id, $client_secret), true);

                $token_data['valid_until'] = time() + $token_data['expires_in'] - 3600;

                $cache[$zone] = $token_data;

                ftruncate($fp, 0);
                fseek($fp, 0);
                fwrite($fp, json_encode($cache));
            }
            catch(Exception $ex) {
            }
        }
        
        $token = $cache[$zone]['access_token'];
    }

    flock($fp, LOCK_UN);

    fclose($fp);

    return $token;
}

function blizzard_api_request_token($zone, $client_id, $client_secret) {
    $url = 'https://' . $zone . '.battle.net/oauth/token';
    if ($zone = "tw") {
        $url = 'https://us.battle.net/oauth/token';
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch); 

    if($httpcode != 200) {
        print_r($data);
        throw new Exception($data, $httpcode);
    }
    else {
        return $data;
    }
}

function blizzard_api_character_pets($zone, $realm, $char, $prefix = "", $source = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $char = mb_strtolower($char);
    $char = rawurlencode($char);
    $realm = urlencode($realm);
    $url = 'https://'.$zone.'.api.blizzard.com/profile/wow/character/'.$realm.'/'.strtolower($char).'/collections/pets?namespace=profile-'.$zone.'&locale=en_US';
    $result = blizzard_api_execute_curl($url, $token, $source);
    return $result;
}

function blizzard_api_character_summary($zone, $realm, $char, $locale = "en_US", $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $char = mb_strtolower($char);
    $char = rawurlencode($char);
    $realm = urlencode($realm);
    $url = 'https://'.$zone.'.api.blizzard.com/profile/wow/character/'.$realm.'/'.strtolower($char).'?namespace=profile-'.$zone.'&locale='.$locale;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_character_media($zone, $realm, $char, $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $char = mb_strtolower($char);
    $char = rawurlencode($char);
    $realm = urlencode($realm);
    $url = 'https://'.$zone.'.api.blizzard.com/profile/wow/character/'.$realm.'/'.strtolower($char).'/character-media?namespace=profile-'.$zone.'&locale=en_US';
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_realm_status($zone, $locale, $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/realm/index?namespace=dynamic-'.$zone.'&locale='.$locale;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_races($zone, $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/playable-race/index?namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_classes($zone, $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/playable-class/index?namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_pets_masterlist($zone, $locale = "", $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/pet/index?locale='.$locale.'&namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_pets_singlepet($species, $zone = "us", $locale = "", $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/pet/'.$species.'?locale='.$locale.'&namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_abilities_masterlist($zone, $locale = "", $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/pet-ability/index?locale='.$locale.'&namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_ability_details($ability, $zone = "us", $locale = "", $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/pet-ability/'.$ability.'?locale='.$locale.'&namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_ability_media($ability, $zone = "us", $locale = "en_US", $prefix = "") {
    $token = blizzard_api_cache_token($zone, battlenet_api_client_id, battlenet_api_client_secret, $prefix.'data/blizzard_api_token.json');
    $url = 'https://'.$zone.'.api.blizzard.com/data/wow/media/pet-ability/'.$ability.'?locale='.$locale.'&namespace=static-'.$zone;
    $result = blizzard_api_execute_curl($url, $token);
    return $result;
}

function blizzard_api_execute_curl($url, $token, $source = "") {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $token));

    if($tofile == null) {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    }
    else {
        $fp = fopen($tofile, 'w+');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }

    $data = curl_exec($ch);
    
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($httpcode != 200) {
        // throw new Exception($data, $httpcode);
        if ($httpcode == 404 && $source == "cronjob") {
            return $httpcode;
        }
        else {
            return "error";
        }
    }
    else {
        return $data;
    }
}



?>
