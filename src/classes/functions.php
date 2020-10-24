<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('BBCode.php');
require_once ('BBCode2.php');
require_once ('Database.php');
require_once ('HTML.php');
require_once ('HTTP.php');
require_once ('Localization.php');
require_once ('Time.php');
require_once ('User.php');
require_once ('Util.php');


function get_youtube_infos($videolink) {
  $video_id = getYoutubeIdFromUrl($videolink);
  $googleApiUrl = 'https://www.googleapis.com/youtube/v3/videos?id=' . $video_id . '&key=' . youtube_apikey . '&part=snippet';
    
	$ch = curl_init();
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
        
    curl_close($ch);
        
    $data = json_decode($response);
    $value = json_decode(json_encode($data), true);
    
    return $value; 
}


function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])){
        parse_str($parts['query'], $qs);
        if(isset($qs['v'])){
            return $qs['v'];
        }else if(isset($qs['vi'])){
            return $qs['vi'];
        }
    }
    if(isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
    }
    return false;
}


function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function resize_image($file, $ext, $w, $h, $crop=FALSE) {

    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    if ($ext == 'jpg' OR $ext == 'jpeg') {
        $src = imagecreatefromjpeg($file);
    }
    if ($ext == 'png') {
        $src = imagecreatefrompng($file);
    }
    if ($ext == 'bmp') {
        $src = imagecreatefrombmp($file);
    }
    if ($ext == 'gif') {
        $src = imagecreatefromgif($file);
    }
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}



function tag_priorities ($user)
{
  $taghigh = [];
  $taglow = [];

  $tags = [];
  $tagsdb = Database_query ( 'SELECT id, DefaultPrio '
                           . 'FROM StrategyTags '
                           . 'ORDER BY DefaultPrio'
                           );
  while ($tag = mysqli_fetch_object ($tagsdb))
  {
    $tags[$tag->id] = $tag->DefaultPrio;
  }

  if ($user && $user->TagPrio)
  {
    $cuttags = explode ("-", $user->TagPrio);

    foreach (explode (",", $cuttags[0]) as $tag)
    {
      $taghigh[] = $tag;
      unset ($tags[$tag]);
    }
    foreach (explode (",", $cuttags[1]) as $tag)
    {
      unset ($tags[$tag]);
    }
    foreach (explode (",", $cuttags[2]) as $tag)
    {
      $taglow[] = $tag;
      unset ($tags[$tag]);
    }
  }

  foreach ($tags as $tag_id => $priority)
  {
    $cutprio = explode("-", $priority);
    if ($cutprio[0] == "0")
    {
      $taghigh[] = $tag_id;
    }
    else if ($cutprio[0] == "2")
    {
      $taglow[] = $tag_id;
    }
  }

  return [$taglow, $taghigh];
}

// ======================================= UPDATE TAGS OF A STRATEGY ===========================================================================

function update_tags($stratid, $tags) {
  $all_tags = $GLOBALS['all_tags'];
  if (!$all_tags) {
    $all_tags = get_all_tags();
  }
  $dbcon = $GLOBALS['dbcon'];
  foreach ($tags as $key => $value) {
    if ($all_tags[$key]['ID'] != "") {
      if ($value == 0) {
        mysqli_query($dbcon, "DELETE FROM Strategy_x_Tags WHERE Strategy = '$stratid' AND Tag = '$key'") OR die(mysqli_error($dbcon));
      }
      if ($value == 1) {
        $check_tag_db = mysqli_query($dbcon, "SELECT * FROM Strategy_x_Tags WHERE Strategy = '$stratid' AND Tag = '$key' LIMIT 1");
        if (mysqli_num_rows($check_tag_db) == 0) {
          mysqli_query($dbcon, "INSERT INTO Strategy_x_Tags (`Strategy`, `Tag`) VALUES ($stratid, $key)") OR die(mysqli_error($dbcon));
        }
      }
    }
  }
}



// Search in multidimensional array

function searchForId($search_value, $array, $id_path) {

    // Iterating over main array
    foreach ($array as $key1 => $val1) {

        $temp_path = $id_path;

        // Adding current key to search path
        array_push($temp_path, $key1);

        // Check if this value is an array
        // with atleast one element
        if(is_array($val1) and count($val1)) {

            // Iterating over the nested array
            foreach ($val1 as $key2 => $val2) {

                if($val2 == $search_value) {

                    // Adding current key to search path
                    array_push($temp_path, $key2);
                    return $temp_path;
                }
            }
        }

        elseif($val1 == $search_value) {
            return $temp_path;
        }
    }

    return null;
}




// ======================================= CALCULATE RATING OF A STRATEGY ===========================================================================

function calc_strats_rating($stratid, $language, $user = '', $external = 0, $custom_collection = '') {

    $allbreeds = [ 'BB' => ['Health' => 0.5, 'Speed' => 0.5, 'Power' => 0.5 ]
             , 'PP' => ['Health' => 0.0, 'Speed' => 0.0, 'Power' => 2.0 ]
             , 'SS' => ['Health' => 0.0, 'Speed' => 2.0, 'Power' => 0.0 ]
             , 'HH' => ['Health' => 2.0, 'Speed' => 0.0, 'Power' => 0.0 ]
             , 'HP' => ['Health' => 0.9, 'Speed' => 0.0, 'Power' => 0.9 ]
             , 'PS' => ['Health' => 0.0, 'Speed' => 0.9, 'Power' => 0.9 ]
             , 'HS' => ['Health' => 0.9, 'Speed' => 0.9, 'Power' => 0.0 ]
             , 'PB' => ['Health' => 0.4, 'Speed' => 0.4, 'Power' => 0.9 ]
             , 'SB' => ['Health' => 0.4, 'Speed' => 0.9, 'Power' => 0.4 ]
             , 'HB' => ['Health' => 0.9, 'Speed' => 0.4, 'Power' => 0.4 ]
             ];

    $col_master = [];
    $allpets = [];
    $taglow = [];
    $taghigh = [];

if ($user)
{
  $all_pets = get_all_pets("Name", $external);
  $path_level = "";
  if ($external != 0) $path_level = 2;
  if (!$custom_collection) {
    $findcol = find_collection($user, $path_level);
    if ($findcol != "No Collection") {
        $fp = fopen($findcol['Path'], 'r');
        $col_master = json_decode(fread($fp, filesize($findcol['Path'])), true);
        foreach ($col_master as $key => $pet) {
            $col_master[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);  // TODO - is this required!? remove?!
        }
    }
  }
  list ($taglow, $taghigh) = tag_priorities ($user);
}
if ($custom_collection) {
  $col_master = $custom_collection;
}

$allstratsdb = Database_query ( 'SELECT Alternatives.id'

                                   // pets used
                                   . ', User, Breeds1, Breeds2, Breeds3'
                                   . ', Health1, Health2, Health3'
                                   . ', PetID1, PetID2, PetID3'
                                   . ', PetLevel1, PetLevel2, PetLevel3'
                                   . ', Power1, Power2, Power3'
                                   . ', Speed1, Speed2, Speed3'

                                   // strategy parameters 
                                   . ', Alternatives.id = ' . $stratid . ' Active'
                                   . ', Published, Deleted'
                                   . ', ' . ($user ? 'User = ' . $user->id : '0') . ' Owned'
                                   . ', Alternatives.Comment CreatorComment'
                                   . ', COALESCE (NULLIF (CreatedBy, ""), Users.Name) CreatorName '

                                   // score
                                   . ', IF ( Created >= DATE_SUB(NOW(), INTERVAL 1 WEEK), "New" '
                                        . ', IF ( Updated >= DATE_SUB(NOW(), INTERVAL 1 WEEK) '
                                             . ', "Updated" '
                                             . ', NULL '
                                             . ')'
                                        . ') Highlight'
                                   . ', IFNULL (Comments.c, 0) number_of_visible_comments'
                                   . ', IFNULL (UserFavStratsA.c, 0) NumberOfFavs'
                                   . ', ' . ($user ? 'IFNULL (UserFavStratsB.c, 0)' : '0') . ' IsFavorited'
                                   . ', IFNULL (UserStratRating.c, 0) num_rated'
                                   . ', IFNULL (UserStratRating.s, 0) sum_rated'
                                   . ', Views '

                              . 'FROM Alternatives '
                              . 'LEFT JOIN Users ON Alternatives.User = Users.id '
                              . 'LEFT JOIN ( SELECT SortingID, COUNT(*) c '
                                          . 'FROM Comments '
                                          . 'WHERE Category = 2 '
                                            . 'AND Deleted = 0 '
                                            . 'AND Parent = 0 '
                                            . 'AND (Language = "en_US" OR Language = "' . $language . '") '
                                          . 'GROUP BY SortingID'
                                        . ') Comments ON Alternatives.id = Comments.SortingID '
                              . 'LEFT JOIN ( SELECT Strategy, COUNT(*) c '
                                          . 'FROM UserFavStrats '
                                          . 'GROUP BY Strategy'
                                        . ') UserFavStratsA ON Alternatives.id = UserFavStratsA.Strategy '
                              . ( $user ? ( 'LEFT JOIN ( SELECT Strategy, COUNT(*) c '
                                                      . 'FROM UserFavStrats '
                                                      . 'WHERE User = ' . $user->id . ' '
                                                      . 'GROUP BY Strategy'
                                                    . ') UserFavStratsB ON Alternatives.id = UserFavStratsB.Strategy '
                                          )
                                : ''
                                )
                              . 'LEFT JOIN ( SELECT Strategy, COUNT(*) c, SUM(Rating) s '
                                          . 'FROM UserStratRating '
                                          . 'GROUP BY Strategy'
                                        . ') UserStratRating ON Alternatives.id = UserStratRating.Strategy '
                              . 'WHERE Deleted = 0 AND Sub = (SELECT Sub FROM Alternatives WHERE id = ' . $stratid . ' LIMIT 1)'
                              . ( $user
                                ? ( !User_is_allowed ($user, 'EditStrats')
                                  ? ' AND (User = ' . $user->id . ' OR Published = 1)'
                                  : ''
                                  )
                                : ' AND Published = 1'
                                )
                              );

  $strats = [];
  while ($thisstrat = $allstratsdb->fetch_object())
  {

  $strats[] = $thisstrat;

  $numratings = $thisstrat->num_rated + $thisstrat->NumberOfFavs;
  $avgrating = 2.5;
  $displayrating = 0;
  if ($numratings)
  {
      $sum_ratings = $thisstrat->sum_rated;
      $avgrating = ($sum_ratings + ($thisstrat->NumberOfFavs * 5)) / $numratings;
      if ($thisstrat->num_rated > 0) {
        $displayrating = $thisstrat->sum_rated/$thisstrat->num_rated;
      }
  }
  
    if ($thisstrat->User != 0) {
      $creator_db = Database_query ( 'SELECT Name '
                         . 'FROM Users '
                         . 'WHERE id = '.$thisstrat->User
                         );
      if (mysqli_num_rows($creator_db) > "0") {
          $creator_user = mysqli_fetch_object($creator_db);
          $thisstrat->CreatorName = $creator_user->Name;
      }
    }
  
  $thisstrat->Rating = number_format (round ($displayrating, 1), 1);
  $thisstrat->PetStatus = [-1, -1, -1];
  $collection = $col_master;

  // PETS COLLECTED
    if ($collection) {
        for ($p = 0; $p < 3; $p++)
        {
            $reqbreeds = "";
            $reqhp = "";
            $reqsp = "";
            $reqpw = "";
            $reqlevel = "";
            switch ($p)
            {
            case 0:
                $fetchpet = $thisstrat->PetID1;
                $reqlevel = $thisstrat->PetLevel1;
                $reqbreeds = $thisstrat->Breeds1;
                $reqhp = $thisstrat->Health1;
                $reqsp = $thisstrat->Speed1;
                $reqpw = $thisstrat->Power1;
                break;
            case 1:
                $fetchpet = $thisstrat->PetID2;
                $reqlevel = $thisstrat->PetLevel2;
                $reqbreeds = $thisstrat->Breeds2;
                $reqhp = $thisstrat->Health2;
                $reqsp = $thisstrat->Speed2;
                $reqpw = $thisstrat->Power2;
                break;
            case 2:
                $fetchpet = $thisstrat->PetID3;
                $reqlevel = $thisstrat->PetLevel3;
                $reqbreeds = $thisstrat->Breeds3;
                $reqhp = $thisstrat->Health3;
                $reqsp = $thisstrat->Speed3;
                $reqpw = $thisstrat->Power3;
                break;
            }

            if ($reqlevel == "")
            {
                $reqlevel = 1;
            }

            if (Util_is_special_pet_id_any ($fetchpet) || Util_is_special_pet_id_level ($fetchpet))
            {
                $thisstrat->PetStatus[$p] = 2;
            }
            else if (Util_is_special_pet_id_family ($fetchpet))
            {
                $famid = Util_family_pet_id_to_family[$fetchpet];
                $temp_petarray = array_filter($collection, function($element) use($famid){ return isset($element['Family']) && $element['Family'] == $famid;});

                $reqhpnumber = substr($reqhp, 1);
                $reqspnumber = substr($reqsp, 1);
                $reqpwnumber = substr($reqpw, 1);

                // Not a single pet of this family in collection
                if (count ($temp_petarray) == 0)
                {
                    $thisstrat->PetStatus[$p] = 0;
                }
                // No requirements at all, at least 1 pet of the family is collected
                else if ($reqlevel == 1 && $reqhp == "" && $reqsp == "" && $reqpw == "")
                {
                    $thisstrat->PetStatus[$p] = 2;
                }
                // Some requirements are set
                else {
                    $foundone = false;
                    foreach ($temp_petarray as $mypet)
                    {
                        if ($mypet['Level'] < $reqlevel)
                        {
                          continue;
                        }

                        $qualcals = [1.0, 1.1, 1.2, 1.3];
                        $qualcalc = $qualcals[$mypet['Quality']];
                        $thishp = round ( 100 + ( ( $all_pets[$mypet['Species']]['Health']
                                                  + $allbreeds[$mypet['Breed']]['Health']
                                                  )
                                                * $mypet['Level']
                                                * $qualcalc
                                                * 5
                                                )
                                        , 0
                                        , PHP_ROUND_HALF_DOWN
                                        );

                        if ($reqhp && !Util_evaluate_comparison ($reqhp[0], $thishp, $reqhpnumber))
                        {
                          continue;
                        }

                        $thissp = round ( ( $all_pets[$mypet['Species']]['Speed']
                                          + $allbreeds[$mypet['Breed']]['Speed']
                                          )
                                        * $mypet['Level']
                                        * $qualcalc
                                        , 0
                                        , PHP_ROUND_HALF_DOWN
                                        );

                        if ($reqsp && !Util_evaluate_comparison ($reqsp[0], $thissp, $reqspnumber))
                        {
                          continue;
                        }

                        $thispw = round ( ( $all_pets[$mypet['Species']]['Power']
                                          + $allbreeds[$mypet['Breed']]['Power']
                                          )
                                        * $mypet['Level']
                                        * $qualcalc
                                        , 0
                                        , PHP_ROUND_HALF_DOWN
                                        );

                        if ($reqpw && !Util_evaluate_comparison ($reqpw[0], $thispw, $reqpwnumber))
                        {
                          continue;
                        }

                        $foundone = true;
                        break;
                    }
                    $thisstrat->PetStatus[$p] = $foundone ? 2 : 1;
                }
            }
            else {
                $breedarray = [];

                $reqhpnumber = substr ($reqhp, 1);
                $reqpwnumber = substr ($reqpw, 1);
                $reqspnumber = substr ($reqsp, 1);
                $cutbreeds = explode (",", $reqbreeds);

                foreach ($allbreeds as $breed => $breedstats) {

                    if ($all_pets[$fetchpet][$breed] == 1) {
                        $thishp = round ( 100 + ( ( $all_pets[$fetchpet]['Health']
                                                  + $breedstats['Health']
                                                  )
                                                * 162.5
                                                )
                                        , 0
                                        , PHP_ROUND_HALF_DOWN
                                        );
                        $thispw = round ( ( $all_pets[$fetchpet]['Power']
                                          + $breedstats['Power']
                                          )
                                        * 32.5
                                        , 0
                                        , PHP_ROUND_HALF_DOWN
                                        );
                        $thissp = round ( ( $all_pets[$fetchpet]['Speed']
                                          + $breedstats['Speed']
                                          )
                                        * 32.5
                                        , 0
                                        , PHP_ROUND_HALF_DOWN
                                        );

                        $breedok = true;

                        if ($reqhp)
                        {
                          $breedok = $breedok
                                  && Util_evaluate_comparison ($reqhp[0], $thishp, $reqhpnumber);
                        }
                        if ($reqsp)
                        {
                          $breedok = $breedok
                                  && Util_evaluate_comparison ($reqsp[0], $thissp, $reqspnumber);
                        }
                        if ($reqpw)
                        {
                          $breedok = $breedok
                                  && Util_evaluate_comparison ($reqpw[0], $thispw, $reqpwnumber);
                        }

                        if ($reqbreeds)
                        {
                            $breedok = $breedok && in_array ($breed, $cutbreeds);
                        }

                        $breedarray[] = ['Breed' => $breed, 'BreedOK' => $breedok];
                    }
                }

                // Check against collection
                $colpets_array = array_filter($collection, function($element) use($fetchpet){ return isset($element['Species']) && $element['Species'] == $fetchpet;});
                $usedpet = FALSE;
                $ownedstatus = 0;

                foreach ($colpets_array as $mypetkey => $mypetvalue)
                {
                    $ownedstatus = 1;
                    $usedpet = $mypetkey;

                    if ($mypetvalue['Level'] == "25" && $mypetvalue['Quality'] == "3")
                    {
                        foreach ($breedarray as $_ => $info)
                        {
                            if ($info['BreedOK'] && $info['Breed'] == $mypetvalue['Breed'])
                            {
                                $ownedstatus = 2;
                                $usedpet = $mypetkey;
                            }
                        }
                        if (!$breedarray) {
                          $ownedstatus = 2;
                          $usedpet = $mypetkey;
                        }
                    }

                    if ($ownedstatus === 2)
                    {
                        break;
                    }
                }

                if ($usedpet)
                {
                    unset($collection[$usedpet]);
                    $collection = array_values($collection);
                }

                $thisstrat->PetStatus[$p] = $ownedstatus;
            }
        }
    }

    $tag_score = 50; // Default value for no modifying tags
    $active_tags_db = Database_query ( 'SELECT Tag '
                       . 'FROM Strategy_x_Tags '
                       . 'WHERE Strategy = '.$thisstrat->id
                       );
    while ($this_tag = $active_tags_db->fetch_object())
    {
      if (in_array ($this_tag->Tag, $taghigh)) { $tag_score++; }
      if (in_array ($this_tag->Tag, $taglow)) { $tag_score--; }
    }

  // Flatten the slope, but keep 2.5 in the center of rotation. This
  // favors ratings below 2.5 and dampens those above, for few votes.
  $rating_factor = $numratings <= 10 ? 0.5 : ($numratings < 25 ? 0.8 : 1.0);
  $rating_score = round ((($avgrating - 2.5) * $rating_factor + 2.5) * 100);

  
// echo '<!-- ';

  $thisstrat->Score = sprintf ( "%'01u"
                              . "%'01u"
                                   . "%'03u"
                              . "%'02u"
                                   . "%'020u"
                              , $thisstrat->IsFavorited   // 0..1
                              , array_reduce                 // 0..6
                                  ( $thisstrat->PetStatus
                                  , function ($carry, $i)
                                    {
                                      return max ($i, 0) + $carry;
                                    }
                                  , 0
                                  )
                                   , $rating_score                // 0..500
                              , $tag_score                   // 0..50
                                   , $thisstrat->Views            // 0..likely never 50k or something
                                   );
                                   
                                   // var_dump ($thisstrat->id, $avgrating, $numratings,  $rating_score, $tag_score);  
                                   // echo '-->';
}


usort ( $strats
      , function ($lhs, $rhs)
{
          return -strcmp ($lhs->Score, $rhs->Score);
        }
      );
  return $strats;
}


// ======================================= AGGREGATE USER SETTINGS ===========================================================================

function format_usersettings($settings) {
    $usets = explode("|", $settings);
    if ($usets[8] == "" OR $usets[8] == "0") {
        $usets['BetaAccess'] = "off";
    }
    else if ($usets[8] == "1") {
        $usets['BetaAccess'] = "on";
    }
    if ($usets[9] == "" OR $usets[9] == "1") {
        $usets['RematchSteps'] = "on";
    }
    else if ($usets[9] == "0") {
        $usets['RematchSteps'] = "off";
    }
    $usets['RecentComments'] = $usets[10];
    return $usets;
}


// ======================================= AGGREGATE USER RIGHTS ===========================================================================

function format_userrights($rights) {
    $urights = explode("|", $rights);

    $urights['AdmBreeds'] = "off";
    $urights['AdmImages'] = "off";
    $urights['AdmPanel'] = "off";
    $urights['AdmPeticons'] = "off";
    $urights['AdmPetImport'] = "off";
    $urights['DeleteStrats'] = "no";
    $urights['EditBeginnerGuide'] = "no";
    $urights['EditComments'] = "no";
    $urights['EditDevlog'] = "no";
    $urights['EditGuides'] = "no";
    $urights['EditMains'] = "no";
    $urights['EditNews'] = "no";
    $urights['EditPvP'] = "no";
    $urights['EditShenk'] = "no";
    $urights['EditStrats'] = "no";
    $urights['EditTags'] = "no";
    $urights['EditTDScripts'] = "no";
    $urights['LocArticles'] = "no";

    if ($urights[1] == 1) {
        $urights['EditComments'] = "yes";
    }
    if ($urights[5] == "1") {
        $urights['AdmBreeds'] = "on";
        $urights['AdmPanel'] = "on";
    }
    if ($urights[6] == "1") {
        $urights['AdmPetImport'] = "on";
        $urights['AdmPanel'] = "on";
    }
    if ($urights[7] == "1") {
        $urights['EditGuides'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[8] == "1") {
        $urights['LocArticles'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[9] == "1") {
        $urights['EditMains'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[10] == "1") {
        $urights['EditDevlog'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[11] == "1") {
        $urights['EditStrats'] = "yes";
        $urights['AdmPanel'] = "on";
    }
    if ($urights[13] == "1") {
        $urights['EditTags'] = "yes";
    }
    if ($urights[14] == "1") {
        $urights['EditTDScripts'] = "yes";
    }
    if ($urights[12] == "1") {
        $urights['EditPvP'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[15] == "1") {
        $urights['DeleteStrats'] = "yes";
    }
    if ($urights[16] == "1") {
        $urights['AdmPeticons'] = "on";
        $urights['AdmPanel'] = "on";
    }
    if ($urights[17] == "1") {
        $urights['EditShenk'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[18] == "1") {
        $urights['EditBeginnerGuide'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[19] == "1") {
        $urights['EditNews'] = "yes";
        $urights['AdmPanel'] = "on";
        $urights['AdmImages'] = "on";
        $urights['EditTestpage'] = "yes";
    }
    if ($urights[20] == "1") {
        $urights['Edit_Menu'] = "yes";
        $urights['AdmPanel'] = "on";
    }
    if ($urights[21] == 1) {
        $urights['Edit_Home_Videos'] = true;
    }
    if ($urights[22] == 1) {
        $urights['Edit_Home_Leveling'] = true;
    }
    return $urights;
}

// ======================================= FORMAT DATE ===========================================================================

function format_date($givendate) {
    $language = $GLOBALS['language'];
    $datesplits = explode(" ", $givendate);
    $datesplits = explode("-", $datesplits[0]);
    if ($language != "en_US") {
        $outdate = $datesplits[2].".".$datesplits[1].".".$datesplits[0];
    }
    else {
        $outdate = $datesplits[1]."/".$datesplits[2]."/".$datesplits[0];
    }
    return $outdate;
}

// ======================================= LOCALIZE MAIN MENU ENTRIES ===========================================================================

function loc_mm($mainselector, $backuptitle, $singlestrat = "0") {
    $dbcon = $GLOBALS['dbcon'];
    $language = $GLOBALS['language'];
    $arttitleext = "Title_".$language;

    if ($singlestrat == "1") {
        $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE Main = '$mainselector'") or die(mysqli_error($dbcon));
        if (mysqli_num_rows($subdb) < "1") {
            $publishtitle = __($backuptitle);
        }
        else {
            $sub = mysqli_fetch_object($subdb);
            $subext = "Name";
            if ($language != "en_US") {
                $subext = "Name_".$language;
            }
            if ($sub->{$subext} != ""){
                $publishtitle = stripslashes(htmlentities($sub->{$subext}, ENT_QUOTES, "UTF-8"));
            }
            else {
                $publishtitle = stripslashes(htmlentities($sub->Name, ENT_QUOTES, "UTF-8"));
            }
        }
    }
    else {
        $articledb = mysqli_query($dbcon, "SELECT * FROM Articles WHERE Article = '$mainselector' AND Preview != '1' ORDER BY LastUpdate DESC LIMIT 1") or die(mysqli_error($dbcon));
        if (mysqli_num_rows($articledb) < "1") {
            $publishtitle = $backuptitle;
        }
        else {
            $article = mysqli_fetch_object($articledb);
            if ($article->{$arttitleext} != ""){
                $publishtitle = stripslashes(htmlentities($article->{$arttitleext}, ENT_QUOTES, "UTF-8"));
            }
            else {
                $publishtitle = stripslashes(htmlentities($article->Title_en_US, ENT_QUOTES, "UTF-8"));
            }
        }
    }

    return $publishtitle;
}


function remove_emojis($string) {
    return preg_replace('%(?:
          \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
    )%xs', ' ', $string);    
}

// ======================================= CREATE REMATCH STRING FOR PET TABLES ===========================================================================

function create_rematch_string($strategy, $language, $breeds = '') {
  global $dbcon;
  $all_abilities = get_all_abilities();
  if ($language == 'en_US' OR $language == '') {
    $subnameext = 'Name';
  }
  else {
    $subnameext = 'Name_'.$language;
  }
  $all_pets = get_all_pets($subnameext);
  // print_r($all_pets);
  $strat_db = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = '$strategy' LIMIT 1") OR die(mysqli_error($dbcon));
  if (mysqli_num_rows($strat_db) > 0) {
    $strategy = mysqli_fetch_object($strat_db);
    $sub_db = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = '$strategy->Sub' LIMIT 1") OR die(mysqli_error($dbcon));
    if (mysqli_num_rows($sub_db) > 0) {
      $sub = mysqli_fetch_object($sub_db);
      if ($sub->Parent != "0") {
        $subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = '$sub->Parent' LIMIT 1");
        $sub = mysqli_fetch_object($subnamedb);
      }
      $fight_name = $sub->{$subnameext};
      $fight_name = htmlentities($fight_name, ENT_QUOTES, "UTF-8");
      $fight_id = strtoupper(base_convert($sub->RematchID,10,32));
       
      $rm_string = $fight_name.':'.$fight_id;
      
      for ($p = 0; $p < 3; $p++) {
          switch ($p) {
              case 0:
                  $species = $strategy->PetID1;
                  $reqhp = substr($strategy->Health1, 1);
                  $reqlvl = $strategy->PetLevel1;
                  $skills = $strategy->SkillPet11.$strategy->SkillPet12.$strategy->SkillPet13;
                  break;
              case 1:
                  $species = $strategy->PetID2;
                  $reqhp = substr($strategy->Health2, 1);
                  $reqlvl = $strategy->PetLevel2;
                  $skills = $strategy->SkillPet21.$strategy->SkillPet22.$strategy->SkillPet23;
                  break;
              case 2:
                  $species = $strategy->PetID3;
                  $reqhp = substr($strategy->Health3, 1);
                  $reqlvl = $strategy->PetLevel3;
                  $skills = $strategy->SkillPet31.$strategy->SkillPet32.$strategy->SkillPet33;
                  break;
          }
          
          if ($species == 0) { // Level Pet
              $rm_string = $rm_string.':ZL';
              if ($min_hp < $reqhp) {
                  $min_hp = $reqhp;
              }
              if ($min_lvl < $reqlvl) {
                  $min_lvl = $reqlvl;
              }
          }
          if ($species == 1) { // Any Pet
              $rm_string = $rm_string.':ZR0';
          }
          if ($species > 10 && $species < 21) {  // Any Family Pet
              switch ($species) {
                 case "11":
                    $rm_string = $rm_string.':ZR1';
                 break;
                 case "12":
                    $rm_string = $rm_string.':ZR6';
                 break;
                 case "13":
                    $rm_string = $rm_string.':ZR7';
                 break;
                 case "14":
                    $rm_string = $rm_string.':ZR4';
                 break;
                 case "15":
                    $rm_string = $rm_string.':ZRA';
                 break;
                 case "16":
                    $rm_string = $rm_string.':ZR3';
                 break;
                 case "17":
                    $rm_string = $rm_string.':ZR5';
                 break;
                 case "18":
                    $rm_string = $rm_string.':ZR9';
                 break;
                 case "19":
                    $rm_string = $rm_string.':ZR8';
                 break;
                 case "20":
                    $rm_string = $rm_string.':ZR2';
                 break;
              }
          }
          if ($species > 20) { // Normal Pet
            
            $outputbreed = 0;
              switch ($breeds[$p]) {
                 case "PP":
                 $outputbreed = "4";
                 break;
                 case "SS":
                 $outputbreed = "5";
                 break;
                 case "HH":
                 $outputbreed = "6";
                 break;
                 case "HP":
                 $outputbreed = "7";
                 break;
                 case "PS":
                 $outputbreed = "8";
                 break;
                 case "HS":
                 $outputbreed = "9";
                 break;
                 case "PB":
                 $outputbreed = "A";
                 break;
                 case "SB":
                 $outputbreed = "B";
                 break;
                 case "HB":
                 $outputbreed = "C";
                 break;
                 case "BB":
                 $outputbreed = "3";
                 break;
              }
              $rm_string = $rm_string.':'.$skills.$outputbreed.strtoupper(base_convert($species,10,32));
          }
      }
      
      if ($min_hp OR $min_lvl) { // Adding level pet requirements as preferences
          $rm_string = $rm_string.':P:'.$min_hp.'::::'.$min_lvl.':';
      }
      $rm_string = $rm_string.":N:Xu-Fu's Pet Guides =^_^=xzzuvwzzxnhttps://wow-petguide.com/?Strategy=".$strategy->id."xzzuvwzzxnxzzuvwzzxn";
    
      if ($strategy->User != "0") {
        $stratuserdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$strategy->User'");
        if (mysqli_num_rows($stratuserdb) > "0") {
          $stratuser = mysqli_fetch_object($stratuserdb);
          $rm_string = $rm_string.'Strategy added by '.$stratuser->Name.'xzzuvwzzxn';
        }
      }
      if ($strategy->User == 0 && $strat->CreatedBy != '') {
        $rm_string = $rm_string.'Strategy added by '.$strat->CreatedBy.'xzzuvwzzxn';
      }

      if ($strategy->Comment != "") {
          $stratcomment = stripslashes(htmlentities($strategy->Comment, ENT_QUOTES, "UTF-8"));
          // Replace URLs:
          if (strpos($stratcomment, '[url=') !== false && strpos($stratcomment, '[/url]') !== false) {
              $cutarticle = explode("[url=", $stratcomment);
              foreach ($cutarticle as $key => $value) {
                  if ($key > "0") {
                      $snippets1 = explode("[/url]", $value);
                      $snippets2 = explode("]", $snippets1[0]);
                      $replacestring = '[url='.$snippets2[0].']'.$snippets2[1].'[/url]';
                      $maskurl = preg_replace('/^(?!https?:\/\/)/', 'http://', $snippets2[0]);
                      $maskurl = str_replace('http','WLPz37f2',$maskurl);
                      $maskurl = str_replace('www','MjwMhR9z',$maskurl);
                      $replacewith = $snippets2[1].' ('.$maskurl.')';
                      $stratcomment = str_replace($replacestring,$replacewith,$stratcomment);
                  }
              }
          }
          $stratcomment = \BBCode\bbparse_pets($stratcomment, 12, 'rematch');

          $stratcomment = str_replace('WLPz37f2','http',$stratcomment);
          $stratcomment = str_replace('MjwMhR9z','www',$stratcomment);
          // Replace simple formatting:
          $stratcomment = \BBCode\bbparse_simple($stratcomment, 'rematch');
          $stratcomment = str_replace(PHP_EOL, "xzzuvwzzxn", $stratcomment);
          $stratcomment = preg_replace( "/\r|\n/", "", $stratcomment );

          $rm_string = $rm_string.$stratcomment."xzzuvwzzxnxzzuvwzzxn";
      }

      $steps_db = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE SortingID = '$strategy->id' ORDER BY id");
      while ($step = mysqli_fetch_object($steps_db)) {
          // Format Step and Instructions
          $showturn = stripslashes(htmlentities($step->Step, ENT_QUOTES, "UTF-8"));
          $showturn = translate_turn($showturn, $language);
          $showinstruction = translate_instruction($step->Instruction, $language);
          $showinstruction = str_replace("&#039;","'",$showinstruction);
          $showinstruction = \BBCode\bbparse_simple($showinstruction, 'rematch');
          $showinstruction = str_replace(PHP_EOL, " ", $showinstruction);
          $showinstruction = \BBCode\bbparse_pets($showinstruction, 12, 'rematch');
          $showinstruction = stripslashes(htmlentities($showinstruction, ENT_QUOTES, "UTF-8"));
          if ($showturn == "") {
            $rm_string = $rm_string.$showinstruction."xzzuvwzzxn";
          }
          else {
            $rm_string = $rm_string.$showturn.": ".$showinstruction."xzzuvwzzxn";
          }
      }
      $rm_string = str_replace("xzzuvwzzxn", "\ughughughn", $rm_string);
      $rm_string = str_replace("ughughugh", "", $rm_string);
      return $rm_string;
    }
  }
}


// ======================================= RETURN NAME AND LINK OF A SUB PAGE ===========================================================================

function decode_sortingid($category,$sortingid) {
    $dbcon = $GLOBALS['dbcon'];
    $language = $GLOBALS['language'];
    $subnameext = $GLOBALS['subnameext'];

    if ($category == "0") {
        $link[0] = "?m=".$sortingid;
        $maindb = mysqli_query($dbcon, "SELECT * FROM Main WHERE id = $sortingid");
        if (mysqli_num_rows($maindb) > "0") {
            $main = mysqli_fetch_object($maindb);
            if ($main->POName != "") {
                $link[1] = __($main->POName);
            }
            else {
                $link[1] = $main->Name;
            }
        }
    }

    if ($category == "1") {
        $link[0] = "?m=News";
        $link[1] = __("News");
    }

    if ($category == "2") {
        $link[0] = "?Strategy=".$sortingid;
        $altdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE id = $sortingid");
        if (mysqli_num_rows($altdb) > "0") {
            $alt = mysqli_fetch_object($altdb);

            $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $alt->Sub");
            if (mysqli_num_rows($subdb) > "0") {
                $sub = mysqli_fetch_object($subdb);
                $link['Family'] = $sub->Family;
                if ($sub->Parent != "0") {
                    $subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $sub->Parent");
                    $sub = mysqli_fetch_object($subnamedb);
                }
                if ($sub->{$subnameext} == "") {
                    $link[1] = $sub->Name;
                }
                else {
                    $link[1] = $sub->{$subnameext};
                }
            }
            else {
                $link[1] = "Could not read page name";
            }
        }
        else {
            $link[1] = "Could not read page name";
        }
    }
    
    if ($category == 3) {
        $link[0] = "?s=".$sortingid;
        $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $sortingid");
        if (mysqli_num_rows($subdb) > "0") {
            $sub = mysqli_fetch_object($subdb);
            $link['Family'] = $sub->Family;
            if ($sub->Parent != "0") {
                $subnamedb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $sub->Parent");
                $sub = mysqli_fetch_object($subnamedb);
            }
            if ($sub->{$subnameext} == "") {
                $link[1] = $sub->Name;
            }
            else {
                $link[1] = $sub->{$subnameext};
            }
        }
        else {
            $link[1] = "Could not read page name";
        }
    }
    
    return $link;
}

// ======================================= PRINT MENU OF PROFILE SETTINGS AND USER MENUS ===========================================================================

function print_profile_menu($propage) {
    global $threads;
    $dbcon = $GLOBALS['dbcon'];
    $user = $GLOBALS['user'];
    $stratcomstotal = $GLOBALS['stratcomstotal'];
    $userrights = format_userrights($user->Rights);
    $usersettings = format_usersettings($user->Settings);

    $mynewcomsout = "";
    $unreadmsgp = '<span id="pmsgsin"></span><span id="pmsgscount"></span><span id="pmsgsout"></span>';
    {
        $unreadcounter = 0;
        foreach ($threads as $thread)
        {
            if ($thread['direction'] == "in" && $thread['seen'] == "0")
            {
                $unreadcounter++;
            }
        }
        if ($unreadcounter > 0)
        {
            $unreadmsgp = '<span id="pmsgsin"> (</span><span id="pmsgscount">'.$unreadcounter.'</span><span id="pmsgsout">)</span>';
        }

        $mynewcomsdb = mysqli_query($dbcon, "SELECT * FROM Comments WHERE User = '$user->id' AND NewActivity = '1'");
        $mynewcoms = mysqli_num_rows($mynewcomsdb);
        if (mysqli_num_rows($mynewcomsdb) > 0)
        {
            $mynewcomsout = " (".$mynewcoms.")";
        }
    }

?>
    <img src="images/blank.png" width="1" height="176">
    <br>
    <ul class="vertical-list">
        <li>
            <a class="<?php if ($propage == "profile") { echo "active"; } ?>button" href="?page=profile" ><?php echo __("My Profile") ?></a>
        </li>
        <li>
            <a class="<?php if ($propage == "col") { echo "active"; } ?>button" href="?page=collection" ><?php echo __("Pet Collection") ?></a>
        </li>
        <li>
            <a class="<?php if ($propage == "strategies") { echo "active"; } ?>button" href="?page=strategies" ><?php echo __("My Strategies") ?> <?php echo $stratcomstotal ?></a>
        </li>
        <li>
            <a class="<?php if ($propage == "mycomments") { echo "active"; } ?>button" href="?page=mycomments" ><?php echo __("My Comments") ?><?php echo $mynewcomsout ?></a>
        </li>
        <li>
            <a class="<?php if ($propage == "messages") { echo "active"; } ?>button" href="?page=messages"><?php echo __("Messages") ?><?php echo $unreadmsgp ?></a>
        </li>
        <li>
            <a class="<?php if ($propage == "settings" OR $propage == "notesettings") { echo "active"; } ?>button" href="?page=settings" ><?php echo __("Settings") ?></a>
        </li>
        <?php if ($userrights['AdmPanel'] == "on") { ?>
        <li>
            <a class="<?php if ($propage == "admin") { echo "active"; } ?>button" href="?page=admin" >Administration</a>
        </li>
        <?php } ?>
        <?php if (User_is_allowed ($user, 'LocArticles')) { ?>
        <li>
            <a class="<?php if ($propage == "loc") { echo "active"; } ?>button" href="?page=loc" >Localization</a>
        </li>
        <?php } ?>
        <li>
            <a class="button" href="?page=logout"><?php echo __("Logout") ?></a>
        </li>
    </ul>
<?
}










// ======================================= PRINT A LINE OF STRATEGY ===========================================================================

function bt_stredit_printline($step, $strat, $language, $userid = "") {
    $lineid = $step->id;
    global $dbcon, $all_pets, $all_abilities, $user;
    if (!$user && $userid) {
        $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid'");
        if (mysqli_num_rows($userdb) > "0") {
            $user = mysqli_fetch_object($userdb);
            $language = $user->Language;
        }
    }
    
    $stepext = "Step_".$language;
    $instext = "Instruction_".$language;
    $petnext = "Name_".$language;
    if ($language == "en_US") {
        $stepext = "Step";
        $instext = "Instruction";
        $petnext = "Name";
    }

    if (!$all_pets) {
      $all_pets = get_all_pets($petnext);
    }
    if (!$all_abilities) {
      $all_abilities = get_all_abilities();
    }
    
  $wowhdomain = "en";
  if ($language == "de_DE") {
   $wowhdomain = "de";
  }
  else if ($language == "it_IT") {
   $wowhdomain = "it";
  }
  else if ($language == "es_ES") {
   $wowhdomain = "es";
  }
  else if ($language == "fr_FR") {
   $wowhdomain = "fr";
  }
  else if ($language == "pt_BR") {
   $wowhdomain = "pt";
  }
  else if ($language == "ru_RU") {
   $wowhdomain = "ru";
  }
  else if ($language == "pl_PL") {
   $wowhdomain = "pl";
  }
  else if ($language == "ko_KR") {
   $wowhdomain = "ko";
  }
  else if ($language == "zh_TW") {
   $wowhdomain = "cn";
  }



    // INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
require_once ("thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
 set_language_vars($language);
    $userrights = format_userrights($user->Rights);

    if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {
        $instwidth = "615px";
    }
    else {
        $instwidth = "701px";
    }

    // Format Step and Instructions

    $showturn = stripslashes(htmlentities($step->Step, ENT_QUOTES, "UTF-8"));
    $showturn = translate_turn($showturn, $language);
    $raweditturn = stripslashes(htmlentities($step->Step, ENT_QUOTES, "UTF-8"));

    $showinstruction = htmlentities($step->Instruction, ENT_QUOTES, "UTF-8");
    $showinstruction = translate_instruction($showinstruction, $language);
    
    $raweditinst = stripslashes(htmlentities($step->Instruction, ENT_QUOTES, "UTF-8"));

    $showinstruction = str_replace("&#039;","'",$showinstruction);
    $showinstruction = str_replace("[u]", "<u>", $showinstruction);
    if (false === strpos ($showinstruction, '[/u]')) $showinstruction .= '[/u]';
    $showinstruction = str_replace("[/u]", "</u>", $showinstruction);
    $showinstruction = str_replace("[i]", "<i>", $showinstruction);
    if (false === strpos ($showinstruction, '[/i]')) $showinstruction .= '[/i]';
    $showinstruction = str_replace("[/i]", "</i>", $showinstruction);
    $showinstruction = str_replace("[b]", "<b>", $showinstruction);
    if (false === strpos ($showinstruction, '[/b]')) $showinstruction .= '[/b]';
    $showinstruction = str_replace("[/b]", "</b>", $showinstruction);
    $showinstruction = str_replace(PHP_EOL, "<br>", $showinstruction);

    // transform pet and spell names
    $showinstruction = \BBCode\bbparse_pets($showinstruction, 16, 'strategy_step');
    $showinstruction = stripslashes($showinstruction);
    ?>

    <div id="step_<?php echo $lineid ?>" class="bt_step_cont bt_adm_trigger" data-lineid="<?php echo $lineid ?>">
        <div class="bt_step_turn"><?php echo $showturn ?></div>
        <div class="bt_step_sepa"></div>
        <div class="bt_step_inst" style="width: <?php echo $instwidth ?>"><?php echo $showinstruction ?></div>
        <?php if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) { ?>
            <div class="bt_step_admin bt_adm_trigger" data-lineid="<?php echo $lineid ?>">
                <div id="bt_admin_panel_<?php echo $lineid ?>" style="opacity: 0">
                    <img class="bt_step_editicon bt_step_quickfill_<?php echo $lineid; ?>" data-tooltip-content="#bt_quickfill_tt_<?php echo $lineid ?>" src="https://www.wow-petguide.com/images/icon_bt_quickfill.png">
                    <img class="bt_step_editicon bt_step_edit_<?php echo $lineid; ?>" data-tooltip-content="#bt_step_edit_<?php echo $lineid ?>" src="https://www.wow-petguide.com/images/icon_bt_edit.png">
                    <img class="bt_step_editicon bt_step_delete_<?php echo $lineid; ?>" data-tooltip-content="#bt_delete_tt_<?php echo $lineid ?>" src="https://www.wow-petguide.com/images/icon_bt_minus.png">
                    <img class="bt_step_editicon" onclick="stredit_addline('<?php echo $lineid ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>', '<?php echo $language ?>')" src="https://www.wow-petguide.com/images/icon_bt_plus.png">
                </div>
            </div>
        <?php } ?>


    <div style="display:none">
        <span id="bt_delete_tt_<?php echo $lineid ?>">
            <div class="stredit_qf_container">
                    <div class="stredit_qf_textbox" style="margin: 3px; width: 100px;">
                        <center>
                        <img class="bb_edit_save" style="padding-top: 4px" src="https://www.wow-petguide.com/images/icon_bt_x.png" onclick="$('.bt_step_delete_<?php echo $lineid ?>').tooltipster('close');">
                        <img class="bb_edit_save" style="padding-top: 4px" src="https://www.wow-petguide.com/images/icon_bt_ok.png" onclick="stredit_removeline('<?php echo $lineid ?>','<?php echo $user->id ?>','<?php echo $user->ComSecret ?>')">
                        </center>
                    </div>
                 <div style="clear: both"></div>
            </div>
        </span>
    </div>


    <div style="display:none">
        <span id="bt_quickfill_tt_<?php echo $lineid ?>">
                <div class="stredit_qf_container">
                    <?
                    $i = "1";
                    $countfamilies = "0";
                    while ($i < "4") {
                        switch ($i) {
                            case "1":
                              $fetchpet = $strat->PetID1;
                                if ($strat->SkillPet11 == "0") { $skill1 = "*";}
                                if ($strat->SkillPet11 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1'];}
                                if ($strat->SkillPet11 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4'];}
                                if ($strat->SkillPet12 == "0") { $skill2 = "*";}
                                if ($strat->SkillPet12 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2']; }
                                if ($strat->SkillPet12 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5']; }
                                if ($strat->SkillPet13 == "0") { $skill3 = "*";}
                                if ($strat->SkillPet13 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3']; }
                                if ($strat->SkillPet13 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6']; }
                                $marginleft = "3px";
                                $reqlevel = $strat->PetLevel1;
                                break;
                            case "2":
                              $fetchpet = $strat->PetID2;
                                if ($strat->SkillPet21 == "0") { $skill1 = "*";}
                                if ($strat->SkillPet21 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1'];}
                                if ($strat->SkillPet21 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4'];}
                                if ($strat->SkillPet22 == "0") { $skill2 = "*";}
                                if ($strat->SkillPet22 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2'];}
                                if ($strat->SkillPet22 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5'];}
                                if ($strat->SkillPet23 == "0") { $skill3 = "*";}
                                if ($strat->SkillPet23 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3'];}
                                if ($strat->SkillPet23 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6'];}
                                $marginleft = "0px";
                                $reqlevel = $strat->PetLevel2;
                                break;
                            case "3":
                              $fetchpet = $strat->PetID3;
                                if ($strat->SkillPet31 == "0") { $skill1 = "*";}
                                if ($strat->SkillPet31 == "1") { $skill1 = $all_pets[$fetchpet]['Skill1'];}
                                if ($strat->SkillPet31 == "2") { $skill1 = $all_pets[$fetchpet]['Skill4'];}
                                if ($strat->SkillPet32 == "0") { $skill2 = "*";}
                                if ($strat->SkillPet32 == "1") { $skill2 = $all_pets[$fetchpet]['Skill2'];}
                                if ($strat->SkillPet32 == "2") { $skill2 = $all_pets[$fetchpet]['Skill5'];}
                                if ($strat->SkillPet33 == "0") { $skill3 = "*";}
                                if ($strat->SkillPet33 == "1") { $skill3 = $all_pets[$fetchpet]['Skill3'];}
                                if ($strat->SkillPet33 == "2") { $skill3 = $all_pets[$fetchpet]['Skill6'];}
                                $marginleft = "0px";
                                $reqlevel = $strat->PetLevel3;
                                break;
                        }
                        $bb_pets[$i]['PetID'] = $fetchpet;

                        echo '<div class="stredit_qf_petbox" style="margin-left: '.$marginleft.'">';

                        if ($fetchpet > "20") {
                            // Create array for BB icons in direct edit window
                            $bb_pets[$i]['Name'] = $all_pets[$fetchpet]['Name'];

                            $temp_petname = mysqli_real_escape_string($dbcon, $all_pets[$fetchpet]['ENName']);
                            $temp_petname= htmlentities($temp_petname, ENT_QUOTES, "UTF-8");
                            $bb_pets[$i]['ENName'] = $all_pets[$fetchpet]['PetID'].':'.$temp_petname;
                            // $bb_pets[$i]['ENName'] = mysqli_real_escape_string($dbcon, $all_pets[$fetchpet]['ENName']);

                            $bb_pets[$i]['Type'] = "pet";
                            if (file_exists('images/pets/resize50/'.$all_pets[$fetchpet]['PetID'].'.png')) {
                                $bb_pets[$i]['Icon'] = 'images/pets/resize50/'.$all_pets[$fetchpet]['PetID'].'.png';
                            }
                            else {
                                $bb_pets[$i]['Icon'] = 'images/pets/resize50/unknown.png';
                            }
                            // Create array for quick fill icons
                            $qp_regular[$i]['Name'] = $all_pets[$fetchpet]['Name'];
                            $qp_regular[$i]['PetID'] = $all_pets[$fetchpet]['PetID'];
                            ?>
                            <div style="padding-left: 3px"><?php echo $all_pets[$fetchpet]['Name']; ?></div>
                            <?
                            
                            // Display Abilities
                            for ($sk = 1; $sk < 4; $sk++) {
                              switch ($sk) {
                                case "1":
                                  $spell_id = $skill1;
                                  break;
                                case "2":
                                  $spell_id = $skill2;
                                  break;
                                case "3":
                                  $spell_id = $skill3;
                                  break;
                              }
                              
                              if ($spell_id != "*") {
                                  $bb_spells[$i][$sk]['Type'] = "spell";
                                  $bb_spells[$i][$sk]['ID'] = $spell_id;
                                  $bb_spells[$i][$sk]['PetID'] = $fetchpet;
                                  $bb_spells[$i][$sk]['DisplayName'] = $all_abilities[$spell_id]['Name'];
                                  $bb_spells[$i][$sk]['Name'] = mysqli_real_escape_string($dbcon, $all_abilities[$spell_id]['Name']);
                                  $temp_spellname = mysqli_real_escape_string($dbcon, $all_abilities[$spell_id]['ENName']);
                                  $temp_spellname= htmlentities($temp_spellname, ENT_QUOTES, "UTF-8");
                                  $bb_spells[$i][$sk]['ENName'] = $spell_id.':'.$temp_spellname;
                                  $bb_spells[$i][$sk]['Icon'] = "images/pet_abilities/".$spell_id.".png";
                                  $bb_spells[$i][$sk]['Count'] = $i;
                                  ?>
  
                                  <div class="spell_tt" data-tooltip-content="#pet<?php echo $i ?>_spell<?php echo $sk ?>_tt" style="float: left; margin-bottom: 3px;" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','0','<?php echo $lineid; ?>','<?php echo $spell_id; ?>','<?php echo $language; ?>')">
                                      <img class="stredit_qf_spell" style="cursor: pointer" src="images/pet_abilities/<?php echo $spell_id ?>.png">
                                  </div>
                                  <div style="display: none"><span id="pet<?php echo $i ?>_spell<?php echo $sk ?>_tt"><?php echo $all_abilities[$spell_id]['Name'] ?></span></div>
                                  <?
                              }
                              else {
                                  $bb_spells[$i][$sk]['Type'] = "wildcard";
                                  $bb_spells[$i][$sk]['Count'] = $i;
                                  echo '<img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">';
                              }
                            }
                        }
                        else {
                            // Level Pet
                            if ($fetchpet == "0") {
                                // Create array for BB icons in direct edit window
                                $bb_pets[$i]['Name'] = "Level Pet";
                                $bb_pets[$i]['ENName'] = "Level Pet";
                                $bb_pets[$i]['Type'] = "special";
                                $bb_pets[$i]['Icon'] = 'images/pets/resize50/level.png';

                                if ($reqlevel == ""){
                                    $reqlevel = "1+";
                                }
                                    $reqlevelpieces = explode("+", $reqlevel);
                                    $displayreqlvl = $reqlevelpieces[0]."+";
                                    if ($_SESSION["lang"] =="fr_FR"){
                                    $petcardtitle = __("Any Pet")." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
                                }
                                else {
                                    $petcardtitle = __("Any Level")." ".$displayreqlvl." ".__("Pet");
                                }
                                $qp_levelpet = "true";
                            }

                            // Any Pet
                            if ($fetchpet == "1") {
                                // Create array for BB icons in direct edit window
                                $bb_pets[$i]['Name'] = "Any Pet";
                                $bb_pets[$i]['ENName'] = "Any Pet";
                                $bb_pets[$i]['Type'] = "special";
                                $bb_pets[$i]['Icon'] = 'images/pets/resize50/any.png';

                                $reqlevelpieces = explode("+", $reqlevel);
                                $displayreqlvl = $reqlevelpieces[0]."+";
                                if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
                                    $petcardtitle = __("Any Pet");
                                }
                                else {
                                    if ($_SESSION["lang"] =="fr_FR"){
                                        $petcardtitle = __("Any Pet")." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
                                    }
                                    else {
                                        $petcardtitle = __("Any Level")." ".$displayreqlvl." ".__("Pet");
                                    }
                                }
                                $qp_anypet = "true";
                            }

                            // Any Family part
                           if ($fetchpet > "10" && $fetchpet <= "20") {
                                switch ($fetchpet) {
                                   case "11":
                                      $famname = __("Humanoid");
                                      $famsuffix = __("Humanoid");
                                      $famid = "0";
                                      $rmfamid = "1";
                                      $qp_family[$countfamilies] = "Humanoid";
                                      $countfamilies++;
                                   break;
                                   case "12":
                                      $famname = __("Magic");
                                      $famsuffix = __("Magic");
                                      $famid = "5";
                                      $rmfamid = "6";
                                      $qp_family[$countfamilies] = "Magic";
                                      $countfamilies++;
                                   break;
                                   case "13":
                                      $famname = __("Elemental");
                                      $famsuffix = __("Elemental");
                                      $famid = "6";
                                      $rmfamid = "7";
                                      $qp_family[$countfamilies] = "Elemental";
                                      $countfamilies++;
                                   break;
                                   case "14":
                                      $famname = __("Undead");
                                      $famsuffix = __("Undead");
                                      $famid = "3";
                                      $rmfamid = "4";
                                      $qp_family[$countfamilies] = "Undead";
                                      $countfamilies++;
                                   break;
                                   case "15":
                                      $famname = __("Mechanical");
                                      $famsuffix = __("Mech");
                                      $famid = "9";
                                      $rmfamid = "A";
                                      $qp_family[$countfamilies] = "Mechanical";
                                      $countfamilies++;
                                   break;
                                   case "16":
                                      $famname = __("Flying");
                                      $famsuffix = __("Flyer");
                                      $famid = "2";
                                      $rmfamid = "3";
                                      $qp_family[$countfamilies] = "Flying";
                                      $countfamilies++;
                                   break;
                                   case "17":
                                      $famname = __("Critter");
                                      $famsuffix = __("Critter");
                                      $famid = "4";
                                      $rmfamid = "5";
                                      $qp_family[$countfamilies] = "Critter";
                                      $countfamilies++;
                                   break;
                                   case "18":
                                      $famname = __("Aquatic");
                                      $famsuffix = __("Aquatic");
                                      $famid = "8";
                                      $rmfamid = "9";
                                      $qp_family[$countfamilies] = "Aquatic";
                                      $countfamilies++;
                                   break;
                                   case "19":
                                      $famname = __("Beast");
                                      $famsuffix = __("Beast");
                                      $famid = "7";
                                      $rmfamid = "8";
                                      $qp_family[$countfamilies] = "Beast";
                                      $countfamilies++;
                                   break;
                                   case "20":
                                      $famname = __("Dragonkin");
                                      $famsuffix = __("Dragon");
                                      $famid = "1";
                                      $rmfamid = "2";
                                      $qp_family[$countfamilies] = "Dragonkin";
                                      $countfamilies++;
                                   break;
                                }

                                // Create array for BB icons in direct edit window
                                $bb_pets[$i]['Name'] = __("Any")." ".$famsuffix;
                                $bb_pets[$i]['ENName'] = __("Any")." ".$famsuffix;
                                $bb_pets[$i]['Type'] = "special";
                                $bb_pets[$i]['Icon'] = 'images/pets/resize50/'.$fetchpet.'.png';

                                 $reqlevelpieces = explode("+", $reqlevel);
                                 $displayreqlvl = $reqlevelpieces[0]."+";

                                 if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
                                   $petcardtitle = __("Any")." ".$famsuffix;
                                }
                                else {
                                   if ($_SESSION["lang"] =="es_ES"){
                                      $petcardtitle = __("Any")." ".$famsuffix." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
                                   }
                                   else if ($_SESSION["lang"] =="fr_FR"){
                                      $petcardtitle = __("Any Pet")." de type ".$famsuffix." ".__("PetCardAnyLevelES")." ".$displayreqlvl;
                                   }
                                   else {
                                      $petcardtitle = __("Any Level")." ".$displayreqlvl." ".$famsuffix;
                                   }
                                }
                            }

                            ?>
                            <div style="padding-left: 3px"><?php echo $petcardtitle ?></div>
                            <img class="stredit_qf_spell" style="padding-bottom: 3px" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                            <img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                            <img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                        <?php }
                        echo "</div>";
                        $i++;
                    } ?>

                    <div class="stredit_qf_textbox" style="margin-top: 0px; width: 558px">
                        <div class="stredit_qf_textitem" style="width: 558px" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','1','<?php echo $lineid; ?>','Pass','<?php echo $language; ?>')">
                            Pass
                        </div>
                    </div>

                    <div class="stredit_qf_textbox" style="margin-top: 0px">
                        <?php // Bring in your pet new line
                        if ($qp_regular) {
                            foreach ($qp_regular as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','2','<?php echo $lineid; ?>','<?php echo $value['PetID']; ?>','<?php echo $language; ?>')">
                                   <i>Bring in your <?php echo $value['Name']; ?></i>
                               </div>
                            <?php }
                        }
                        if ($qp_levelpet == "true") { ?>
                            <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','4','<?php echo $lineid; ?>','','<?php echo $language; ?>')">
                               <i>Bring in your Level Pet</i>
                           </div>
                        <?php }
                        if ($qp_family) {
                            foreach ($qp_family as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','6','<?php echo $lineid; ?>','<?php echo $value; ?>','<?php echo $language; ?>')">
                                   <i>Bring in your <?php echo $value; ?> pet</i>
                               </div>
                            <?php }
                        } ?>
                    </div>

                    <div class="stredit_qf_textbox" style="margin-top: 0px">
                        <?php // Swaps to your pet
                        if ($qp_regular) {
                            foreach ($qp_regular as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','3','<?php echo $lineid; ?>','<?php echo $value['PetID']; ?>','<?php echo $language; ?>')">
                                   Swap to your <?php echo $value['Name']; ?>
                               </div>
                            <?php }
                        }
                        if ($qp_levelpet == "true") { ?>
                            <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','5','<?php echo $lineid; ?>','','<?php echo $language; ?>')">
                               Swap to your Level Pet
                           </div>
                        <?php }
                        if ($qp_family) {
                            foreach ($qp_family as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','7','<?php echo $lineid; ?>','<?php echo $value; ?>','<?php echo $language; ?>')">
                                   Swap to your <?php echo $value; ?> pet
                               </div>
                            <?php }
                        } ?>
                    </div>


                    <?php // Enemy pets come in

                    $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $strat->Sub");
                    $sub = mysqli_fetch_object($subdb);
                    if ($sub->Parent != "0") {
                        $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $sub->Parent");
                        $sub = mysqli_fetch_object($subdb);
                    }

                    if ($sub->Pet1 != "0" OR $sub->Pet2 != "0" OR $sub->Pet3 != "0") {
                        echo '<div class="stredit_qf_textbox" style="margin-top: 0px">';
                    }

                    $p = "1";
                    while ($p < "4") {
                        switch ($p) {
                            case "1":
                                $fetchnpc = $sub->Pet1;
                            break;
                            case "2":
                                $fetchnpc = $sub->Pet2;
                            break;
                            case "3":
                                $fetchnpc = $sub->Pet3;
                            break;
                        }

                        if ($fetchnpc != "0") {
                            $petdb = mysqli_query($dbcon, "SELECT * FROM Pets_NPC WHERE id = '$fetchnpc'");
                            if (mysqli_num_rows($petdb) > "0") {
                            $enemy = mysqli_fetch_object($petdb);
                            if ($enemy->{$language} != "") {
                                $enemyfullname = $enemy->{$language};
                            }
                            else {
                                $enemyfullname = $enemy->en_US;
                            }
                            ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','8','<?php echo $lineid; ?>','<?php echo $enemy->id; ?>','<?php echo $language; ?>')">
                                    <i><?php echo $enemyfullname; ?> comes in</i>
                                </div>
                            <?php }
                        }
                        $p++;
                    }

                    if ($sub->Pet1 != "0" OR $sub->Pet2 != "0" OR $sub->Pet3 != "0") {
                        echo '</div>';
                    } ?>

                    <div class="stredit_qf_textbox" style="margin-top: 0px">
                        <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','9','<?php echo $lineid; ?>','','<?php echo $language; ?>')">
                            <i>An enemy pet comes in</i>
                        </div>
                        <div class="stredit_qf_textitem" onclick="stredit_quickfill('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','10','<?php echo $lineid; ?>','','<?php echo $language; ?>')">
                            Any standard attack will finish the fight
                        </div>
                    </div>

                    <div style="clear: both"></div>
                </div>

			</span>
		</div>


    <div style="display:none">
        <span id="bt_step_edit_<?php echo $lineid ?>">

            <div style="width: 92px; float: left;">&nbsp;</div>
            <div class="stredit_edit_bb">
                <button type="button" style="float: left" class="bbbutton" onclick="bb_stredit('<?php echo $lineid ?>','simple','b')"><b>[b]</b></button>
                <button type="button" style="float: left" class="bbbutton" onclick="bb_stredit('<?php echo $lineid ?>','simple','i')"><i>[i]</i></button>
                <button type="button" style="float: left" class="bbbutton" onclick="bb_stredit('<?php echo $lineid ?>','simple','u')"><u>[u]</u></button>

                <img class="bb_pet spell_tt" style="margin-left: 30px" onclick="bb_stredit('<?php echo $lineid ?>','<?php echo $bb_pets[1]['Type'] ?>','<?php echo $bb_pets[1]['ENName'] ?>')" data-tooltip-content="#bb_pet_pet1" src="https://www.wow-petguide.com/<?php echo $bb_pets[1]['Icon'] ?>">
                <div style="display: none"><span id="bb_pet_pet1"><?php echo $bb_pets[1]['Name'] ?></span></div>
                <img class="bb_pet spell_tt" onclick="bb_stredit('<?php echo $lineid ?>','<?php echo $bb_pets[2]['Type'] ?>','<?php echo $bb_pets[2]['ENName'] ?>')" data-tooltip-content="#bb_pet_pet2" src="https://www.wow-petguide.com/<?php echo $bb_pets[2]['Icon'] ?>">
                <div style="display: none"><span id="bb_pet_pet2"><?php echo $bb_pets[2]['Name'] ?></span></div>
                <img class="bb_pet spell_tt" style="margin-right: 30px" onclick="bb_stredit('<?php echo $lineid ?>','<?php echo $bb_pets[3]['Type'] ?>','<?php echo $bb_pets[3]['ENName'] ?>')" data-tooltip-content="#bb_pet_pet3" src="https://www.wow-petguide.com/<?php echo $bb_pets[3]['Icon'] ?>">
                <div style="display: none"><span id="bb_pet_pet3"><?php echo $bb_pets[3]['Name'] ?></span></div>

                <?
                $c = "1";
                while ($c < "4") {
                    if ($bb_spells[$c][1]['PetID'] != "" OR $bb_spells[$c][2]['PetID'] != "" OR $bb_spells[$c][3]['PetID'] != "") {
                        foreach ($bb_spells[$c] as $key => $value) {
                            $lastmargin = '';
                            if ($key == "3") {
                                $lastmargin = 'style="margin-right: 8px"';
                            }
                            if ($value['Type'] == "spell") { ?>
                            <img class="bb_pet spell_tt" <?php echo $lastmargin ?> onclick="bb_stredit('<?php echo $lineid ?>','spell','<?php echo $value['ENName']; ?>')" data-tooltip-content="#bb_spell_<?php echo $value['Count'] ?>_<?php echo $key ?>" src="<?php echo $value['Icon'] ?>">
                            <div style="display: none"><span id="bb_spell_<?php echo $value['Count'] ?>_<?php echo $key ?>"><?php echo $value['DisplayName'] ?></span></div>
                        <?php }
                            else if ($value['Type'] == "wildcard") { ?>
                            <img class="bb_pet_wildcard" <?php echo $lastmargin ?> src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                           <?php }
                        }
                    }
                    else { ?>
                        <img class="bb_pet_wildcard" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                        <img class="bb_pet_wildcard" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                        <img class="bb_pet_wildcard" style="margin-right: 10px" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                    <?php }

                    $c++;
                }



                $p = "3";
                while ($p > "0") {
                    switch ($p) {
                        case "1":
                            $fetchnpc = $sub->Pet1;
                        break;
                        case "2":
                            $fetchnpc = $sub->Pet2;
                        break;
                        case "3":
                            $fetchnpc = $sub->Pet3;
                        break;
                    }

                    if ($fetchnpc != "0") {
                        $petdb = mysqli_query($dbcon, "SELECT * FROM Pets_NPC WHERE id = '$fetchnpc'");
                        if (mysqli_num_rows($petdb) > "0") {
                            $enemy = mysqli_fetch_object($petdb);
                            if ($enemy->{$language} != "") {
                                $enemyfullname = htmlentities($enemy->{$language}, ENT_QUOTES, "UTF-8");
                            }
                            else {
                                $enemyfullname = htmlentities($enemy->en_US, ENT_QUOTES, "UTF-8");
                            }
                            
                            $enemybbname = mysqli_real_escape_string($dbcon, $enemy->en_US);
                            $enemybbname = htmlentities($enemybbname, ENT_QUOTES, "UTF-8");
                            $enemybbname = $enemy->id.':'.$enemybbname;

                        ?>
                            <img class="bb_pet spell_tt" style="float: right" onclick='bb_stredit("<?php echo $lineid ?>","enemy","<?php echo $enemybbname; ?>")' data-tooltip-content="#bb_enemy_<?php echo $p ?>" src="https://blzmedia-a.akamaihd.net/wow/icons/36/<?php echo $enemy->Icon ?>.jpg">
                            <div style="display: none"><span id="bb_enemy_<?php echo $p ?>"><?php echo $enemyfullname;  ?></span></div>
                        <?php }
                    }
                    $p = $p-1;
                } ?>

            </div>

            <div class="stredit_qf_container" style="display: table-cell">
                <div class="stredit_edit_inputbox" style="display: table-cell">
                    <input type="text" maxlength="15" class="stredit_editline" style="width: 80px;" id="stredit_editstep_<?php echo $lineid ?>" value="<?php echo $raweditturn ?>">
                    <input type="text" maxlength="1000" class="stredit_editline" style="width: 625px;" id="stredit_editinst_<?php echo $lineid ?>" value="<?php echo $raweditinst ?>">
                    <div style="float: right; margin: 3 8 0 0">
                        <img class="bb_edit_save" src="https://www.wow-petguide.com/images/icon_bt_x.png" onclick="$('.bt_step_edit_<?php echo $lineid ?>').tooltipster('close');">
                        <img class="bb_edit_save" src="https://www.wow-petguide.com/images/icon_bt_ok.png" onclick="bb_strsave('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $lineid ?>','<?php echo $language ?>');">
                    </div>
                </div>
                <script>
                  var input = document.getElementById("stredit_editinst_<?php echo $lineid ?>");
                  input.addEventListener("keyup", function(event) {
                      event.preventDefault();
                      if (event.keyCode === 13) {
                          bb_strsave('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $lineid ?>','<?php echo $language ?>');
                      }
                  });
                  var inputz = document.getElementById("stredit_editstep_<?php echo $lineid ?>");
                  inputz.addEventListener("keyup", function(event) {
                      event.preventDefault();
                      if (event.keyCode === 13) {
                          bb_strsave('<?php echo $user->id ?>','<?php echo $user->ComSecret ?>','<?php echo $lineid ?>','<?php echo $language ?>');
                      }
                  });
                </script>



            <?php if ($language != "en_US") { ?>

                <div class="stredit_qf_petbox autoloc_tt" style="cursor: help; display: table-cell; margin: 0px; padding: 0px; width: 100%" data-tooltip-content="#autoloc_tt_<?php echo $lineid ?>">
                    <b>Important:</b> Please enter your instructions in <b>English</b> or a large parts of the community will not be able to read them.<br>
                    Some typical sentences are translated automatically. Move your mouse over this part for more details.
                </div>
                <div style="display: none"><span id="autoloc_tt_<?php echo $lineid ?>">
                    <div class="stredit_edit_inputbox" style="display: table-cell; width: 794px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #dfdfdf;">
                        The following strings are auto-translated if you enter them in the defined format. It is recommended to use them where possible:
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #cecece;">
                            [ability=123:Spellname] - [pet=123:Petname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #c1c1c1;">
                            [ability=123:Name] - [enemy=123:Enemyname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #cecece;">
                            [ability=123:Name] until [pet=123:Petname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #c1c1c1;">
                            [ability=123:Name] until [enemy=123:Enemyname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #cecece;">
                            Swap back to [pet=123:Petname]
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #c1c1c1;">
                            [ability=123:Name] until the fight is won
                        </div>
                    </div>

                </span></div>
            <?php } ?>
            </div>

        </span>
    </div>






    <?php if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) { ?>
        <script>
            $(document).ready(function() {
                $('.autoloc_tt').tooltipster({
                    theme: 'tooltipster-bbedit',
                    interactive: true,
 					maxWidth: '800',
					arrow: false,
                    functionPosition: function(instance, helper, position){
                        position.coord.top -= 6;
                        position.coord.left -= 3;
                        return position;
                    },
                    side: ['bottom']
                });
            });
            $(document).ready(function() {
                $('.spell_tt').tooltipster({
                    maxWidth: '250',
                    theme: 'tooltipster-smallnote'
                });
            });
            $('.bt_adm_trigger').mouseover(function() {
                var lineid = this.getAttribute("data-lineid");
                document.getElementById('bt_admin_panel_'+lineid).style.opacity = '1';
            });
            $('.bt_adm_trigger').mouseout(function() {
                var lineid = this.getAttribute("data-lineid");
                document.getElementById('bt_admin_panel_'+lineid).style.opacity = '0';
            });
            bt_initialize_tooltips('<?php echo $lineid ?>');
        </script>
    <?php } ?>
    </div>
<?php }






// ======================================= PRINT ADMIN MENU ===========================================================================


function get_collection_rank($userid, $region){
  $dbcon = $GLOBALS['dbcon'];

  if ($region == "global") {
    $sql = "SELECT count(User)
    FROM Leaderboard
    WHERE Unique_pets > (
    SELECT max( Unique_pets )
    FROM Leaderboard
    WHERE `User` = '$userid'
    GROUP BY `User` )";
  }
  else if ($region) {
    $sql = "SELECT count(User)
    FROM Leaderboard
    WHERE Unique_pets > (
    SELECT max( Unique_pets )
    FROM Leaderboard
    WHERE `User` = '$userid'
    GROUP BY `User` ) AND Region = '$region'";
  }

  $rank_db = mysqli_query($dbcon, $sql);
  $rank_result = mysqli_fetch_assoc($rank_db);

  $rank = $rank_result['count(User)'];
  $rank++;
  
  return $rank;
}





// ======================================= PRINT ADMIN MENU ===========================================================================

function print_admin_menu($admpage) {
    $dbcon = $GLOBALS['dbcon'];
    $user = $GLOBALS['user'];
    $userrights = format_userrights($user->Rights);
?>
    <tr class="profile">
        <th class="profile">
            <table>
                <tr>
                    <td>
                        <a href="?page=admin" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "main") { echo "active"; } ?>" style="display: block">Explanation</button></a>
                    <?php if ($userrights['Edit_Menu'] == "yes") { ?>
                        <a href="?page=adm_menu" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "menu") { echo "active"; } ?>" style="display: block">Navigation Menu</button></a>
                    <?php } ?>
                        <a href="?page=adm_strategies" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "strategies") { echo "active"; } ?>" style="display: block">Strategies</button></a>
                    <?php if ($userrights['EditStrats'] == "yes" && $user->id == 2) { ?>
                        <a href="?page=adm_comreports" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "adm_comreports") { echo "active"; } ?>" style="display: block">Comment Reports</button></a>
                    <?php } ?>
                    <?php if ($userrights['AdmPetImport'] == "on") { ?>
                        <a href="?page=adm_petimport" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "adm_petimport") { echo "active"; } ?>" style="display: block">Pet Data Import</button></a>
                    <?php } ?>
                    <?php  if ($userrights['AdmBreeds'] == "on") { ?>
                        <a href="?page=adm_breeds" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "adm_breeds") { echo "active"; } ?>" style="display: block">Pet Stats Importer</button></a>
                    <?php } ?>
                    <?php if ($userrights['AdmPeticons'] == "on") { ?>

                        <a href="?page=adm_peticons" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "adm_peticons") { echo "active"; } ?>" style="display: block">Pet Icons</button></a>

                    <?php } ?>
                    <?php if ($userrights['AdmImages'] == "on") { ?>

                        <a href="?page=adm_images" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<?php if ($admpage == "adm_images") { echo "active"; } ?>" style="display: block">Article Images</button></a>

                    <?php } ?>
                    </td>
                </tr>
            </table>
        </th>
    </tr>
<?
}


// ======================================= PRINT ADMIN MENU ===========================================================================

function print_loc_menu($admpage) {
    $dbcon = $GLOBALS['dbcon'];
    $user = $GLOBALS['user'];
    $userrights = format_userrights($user->Rights);
?>
    <tr class="profile">
        <th class="profile">
            <table>
                <tr>
                    <td>
                        <a href="?page=loc" style="text-decoration: none"><button class="settings<?php if ($admpage == "mains" OR $admpage == "") { echo "active"; } ?>" style="display: block">Main Categories</button></a>
                    </td>
                    <td>
                        <a href="?page=loc_fights" style="text-decoration: none"><button class="settings<?php if ($admpage == "fights") { echo "active"; } ?>" style="display: block">Tamer Names</button></a>
                    </td>
                </tr>
            </table>
        </th>
    </tr>
<?
}


// ======================================= DECODE LANGUAGE AND OUTPUT NAMES AND SHORTS ===========================================================================

function decode_language($language)
{
    $language = strtolower($language);
    if ($language == "de" OR $language == "de_de") {
        $outlang['short'] = "DE";
        $outlang['long'] = "Deutsch";
    }
    if ($language == "en" OR $language == "en_us") {
        $outlang['short'] = "EN";
        $outlang['long'] = "English";
    }
    if ($language == "es" OR $language == "es_es") {
        $outlang['short'] = "ES";
        $outlang['long'] = "Español";
    }
    if ($language == "it" OR $language == "it_it") {
        $outlang['short'] = "IT";
        $outlang['long'] = "Italiano";
    }
    if ($language == "fr" OR $language == "fr_fr") {
        $outlang['short'] = "FR";
        $outlang['long'] = "Français";
    }
    if ($language == "pl" OR $language == "pl_pl") {
        $outlang['short'] = "pl";
        $outlang['long'] = "Polski";
    }
    if ($language == "ru" OR $language == "ru_ru") {
        $outlang['short'] = "RU";
        $outlang['long'] = "Русский";
    }
    if ($language == "pt" OR $language == "pt_br") {
        $outlang['short'] = "PT";
        $outlang['long'] = "Português";
    }
    if ($language == "ko" OR $language == "ko_kr" OR $language == "kr") {
        $outlang['long'] = "한국어";
        $outlang['short'] = "kr";
    }
    if ($language == "tw" OR $language == "zh_tw" OR $language == "ch" OR $language == "cn") {
        $outlang['long'] = "中文";
        $outlang['short'] = "tw";
    }
return $outlang;
}

// ======================================= CHECK IF EXTERNAL FILE EXISTS ===========================================================================

function checkExternalFile($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $retCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $retCode;
}

// ======================================= ADJUST USER RIGHTS ===========================================================================

function set_rights($user,$right,$setto)
{
    $dbcon = $GLOBALS['dbcon'];
    $user = $GLOBALS['user'];
    $userrights = explode("|", $user->Rights);
    foreach($userrights as $key => $value) {
        if ($key == $right) {
            $newrights = $newrights."|".$setto;
        }
        else {
            $newrights = $newrights."|".$value;
        }
    }
    $newrights = substr($newrights, 1);
    mysqli_query($dbcon, "UPDATE Users SET `Rights` = '$newrights' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
}


// ======================================= ADJUST USER SETTINGS ===========================================================================

function set_settings($user,$right,$setto)
{
    $dbcon = $GLOBALS['dbcon'];
    $user = $GLOBALS['user'];
    $usersettings = explode("|", $user->Settings);
    foreach($usersettings as $key => $value) {
        if ($key == $right) {
            $newsettings = $newsettings."|".$setto;
        }
        else {
            $newsettings = $newsettings."|".$value;
        }
    }
    $newsettings = substr($newsettings, 1);
    mysqli_query($dbcon, "UPDATE Users SET `Settings` = '$newsettings' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
}

// ======================================= UPDATE PET COLLECTION FROM ARMORY ===========================================================================

function find_collection($user, $pathlevel = 0, $prefix = "") {
    if ($prefix == "" && $pathlevel == 1) $prefix = "../";
    if ($prefix == "" && $pathlevel == 2) $prefix = "../../";

    $foldern = ceil($user->id / 1000) * 1000;
    if (is_dir($prefix.'Collections/'.$foldern.'/'.$user->id)) {
        $coldir = scandir($prefix.'Collections/'.$foldern.'/'.$user->id, '1');
        $colfile = "";
        foreach ($coldir as $value) {
            $filesplits = explode(".",$value);
            if ($filesplits[1] == "json") {
                $colfile = $filesplits;
                break;
            }
        }
        if ($colfile) {
            $filen = str_replace('_',' ',$colfile[0]);
            $filen = str_replace('x',':',$filen);
            $result['Date'] = $filen;
            $result['Path'] = $prefix.'Collections/'.$foldern.'/'.$user->id.'/'.$colfile[0].'.json';
            $fp = fopen($result['Path'], 'r');
            $check_file = json_decode(fread($fp, filesize($result['Path'])), true);
            // echo $check_file;
            if ($check_file == "" OR $check_file == "empty") {
              unlink($result['Path']);
              return "No Collection";
            }
            else {
              return $result;
            }
        }
        else {
            return "No Collection";
        }
    }
    else {
        return "No Collection";
    }
}

function delete_collection($userid, $prefix = "") {
    $foldern = ceil($userid / 1000) * 1000;
    if (is_dir($prefix.'Collections/'.$foldern.'/'.$userid)) {
        array_map('unlink', array_filter((array) glob($prefix.'Collections/'.$foldern.'/'.$userid.'/*')));
        rmdir($prefix.'Collections/'.$foldern.'/'.$userid);
    }
}


function update_collection ($userid, $forceupdate = "0", $prefix = "", $source = "") {
    $dbcon = $GLOBALS['dbcon'];
    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$userid' LIMIT 1");           // Get User data
    if (mysqli_num_rows($userdb) < "1") {
        $getcol = "error";
        $getcoldesc = "No user found with this ID";
    }
    else {
        $user = mysqli_fetch_object($userdb);
    }

    if ($getcol != "error") { 
        $findcol = find_collection($user, "", $prefix); // Get latest collection
        if ($findcol == "No Collection") {
            $updatetype = "new";
        }
        else {
            if (strtotime($findcol['Date']) < strtotime('-2 days')) {
                $updatetype = "update";  // set this to "new" if you want to create a new collection file once a week
            }
            else {
                if ($forceupdate == "0") {
                    $getcol = "error";
                    $getcoldesc = "No update required, there is already an entry younger than 48h";
                }
                else {
                    $updatetype = "update";
                }
            }
        }
    }

    if ($getcol != "error") { // Update required. Check if user is connected via bnet, then get set character and attempt with that
        $bnetuserdb = mysqli_query($dbcon, "SELECT * FROM UserBnet WHERE User = '$user->id'");
        if (mysqli_num_rows($bnetuserdb) > "0" && $user->UseWoWForCol != 1) {
            $bnetuser = mysqli_fetch_object($bnetuserdb);
            if ($bnetuser->CharName != ""){
                $thisrealm = str_replace(' ', '-', $bnetuser->CharRealm);
                $thisrealm = str_replace('\'', '', $thisrealm);
                $thisrealm = strtolower($thisrealm);
                $thischarenc = strtolower($bnetuser->CharName);
                $apiregion = strtolower($bnetuser->Region);

            }
            else {
                $getcol = "error";
                $getcoldesc = "bnetcharincorrect";
            }
            if ($bnetuser->Region == "cn"){
                 $getcol = "error";
                 $getcoldesc = "China - not supported";
            }
        }
        if (!$bnetuser OR $user->UseWoWForCol == 1){                                                                  // No battle.net account linked, get armory data using saved wow character
            if ($user->CharName != ""){
                $thisrealm = str_replace(' ', '-', $user->CharRealm);
                $thisrealm = str_replace('\'', '', $thisrealm);
                $thisrealm = strtolower($thisrealm);
                $thischarenc = strtolower($user->CharName);
                $apiregion = strtolower($user->CharRegion);
            }
            else {
                $getcol = "error";
                $getcoldesc = "User has no valid character saved";
            }
        }
    }

    if ($getcol != "error") { // All tests seem fine, now processing API data

        $petdata_source = blizzard_api_character_pets($apiregion, $thisrealm, $thischarenc, $prefix, $source);
        if ($petdata_source == "error") {
            $getcol = "error";
            $getcoldesc = "generic_import_error";
        }
        else if ($petdata_source == "404") {
          if ($source == "cronjob") {
            if ($user->ColUpdateStrikes > 3) {
              $getcol = "error";
              mysqli_query($dbcon, "UPDATE Users SET ColUpdateStrikes = '0' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
              delete_collection($user->id, $prefix);
              mysqli_query($dbcon, "DELETE FROM Leaderboard WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
              if ($bnetuser) {
                mysqli_query($dbcon, "UPDATE UserBnet SET `CharRealm` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE UserBnet SET `CharName` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE UserBnet SET `CharLevel` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE UserBnet SET `CharClass` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE UserBnet SET `CharRace` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE UserBnet SET `CharIcon` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              }
              mysqli_query($dbcon, "UPDATE Users SET `CharRegion` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharRealm` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharRealmFull` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharName` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharLevel` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharClass` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharRace` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `CharIcon` = '' WHERE id = '$bnetuser->id'") OR die(mysqli_error($dbcon));
              $getcoldesc = "Collection update repeatedly failed with error 404. Removed collection data.";
              mysqli_query($dbcon, "UPDATE Users SET `LastColUpdate` = '$updatetime' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
            }
            else {
              mysqli_query($dbcon, "UPDATE Users SET ColUpdateStrikes = ColUpdateStrikes + 1 WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
              $getcol = "error";
              $strikes = $user->ColUpdateStrikes+1;
              $getcoldesc = "Character not found via API, error 404 - strike count set to ".$strikes." - Rechecking in 48h";
              mysqli_query($dbcon, "UPDATE Users SET `LastColUpdate` = '$updatetime' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
            }
          }
          else {
            $getcol = "error";
            $getcoldesc = "generic_import_error";
          }
        }
        else {
            $colpets = prepare_collection($petdata_source);
            if ($colpets == "empty") {
              $getcol = "error";
              $getcoldesc = "no_pets_in_import";
            }
            else {
              $foldern = ceil($user->id / 1000) * 1000;
              if ($updatetype == "update") {
                delete_collection($user->id, $prefix);
              }
              createPath($prefix.'Collections/'.$foldern);
              createPath($prefix.'Collections/'.$foldern.'/'.$user->id);

              $fp = fopen($prefix.'Collections/'.$foldern.'/'.$user->id.'/'.date("Y-m-d_hxi").'.json', 'w');
              fwrite($fp, json_encode($colpets));
              fclose($fp);
              $updatetime = date('Y-m-d H:i:s');
              mysqli_query($dbcon, "UPDATE Users SET ColUpdateStrikes = '0' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
              mysqli_query($dbcon, "UPDATE Users SET `LastColUpdate` = '$updatetime' WHERE id = '$user->id'") OR die(mysqli_error($dbcon));
              $getcol = "success";
              $getcoldesc = "Collection saved";
              $unique_pets = count_unique_pets($colpets);
              $leaderboard_db = mysqli_query($dbcon, "SELECT * FROM Leaderboard WHERE User = '$user->id'");
              if (mysqli_num_rows($leaderboard_db) < "1") {
                mysqli_query($dbcon, "INSERT INTO Leaderboard (`User`, `Unique_Pets`, `Region`) VALUES ('$user->id', '$unique_pets', '$apiregion')") OR die(mysqli_error($dbcon));
              }
              else {
                mysqli_query($dbcon, "UPDATE Leaderboard SET Unique_Pets = '$unique_pets' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE Leaderboard SET Region = '$apiregion' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
              }
            }
        }
    }
    $results[0] = $getcol;
    $results[1] = $getcoldesc;
    return $results;
}


function count_unique_pets($petdata) {
  $count_unique = 0;
  $countcolpets = 0;
  $collection = array();

  foreach($petdata as $pet) {
      $ignorepet = FALSE;

      // Mark the current pet to be ignored in counts (guild herald/page). This is because they can be in collections multiple times (via API), but are only shown once in-game
      if (($pet['Species'] == "280" && $guildpet[1] == TRUE) || ($pet['Species'] == "282" && $guildpet[2] == TRUE) || ($pet['Species'] == "281" && $guildpet[3] == TRUE) || ($pet['Species'] == "283" && $guildpet[4] == TRUE)) {
        $ignorepet = TRUE;
      }

      if ($ignorepet == FALSE) {
        // Mark hits of the guild herald / guild pages once, do not add again
        if ($pet['Species'] == "280") {
            $guildpet[1] = TRUE;
        }
        if ($pet['Species'] == "282") {
            $guildpet[2] = TRUE;
        }
        if ($pet['Species'] == "281") {
            $guildpet[3] = TRUE;
        }
        if ($pet['Species'] == "283") {
            $guildpet[4] = TRUE;
        }

        if(array_search($pet['Species'], array_column($collection, 'Species')) !== false) {
            // $stats['Duplicates']++;
        }
        else {
            $count_unique++;
        }
        // Adding species to collection array after this to make sure dupe check is working
        $collection[$countcolpets]['Species'] = $pet['Species'];
        $countcolpets++;
      }
  }
  return $count_unique;
}




// Reformats user pet collection data from the API to JSON format that is used to store collections
function prepare_collection($petdata) {

    $pets = json_decode($petdata, TRUE);

    if ($pets['pets'][0]['species'] == "") {
      return "empty";
    }
    foreach($pets['pets'] as $key => $value) {
        if ($value['stats']['breed_id'] == "3" OR $value['stats']['breed_id'] == "13"){
            $pet_breed = "BB";
        }
        if ($value['stats']['breed_id'] == "4" OR $value['stats']['breed_id'] == "14"){
            $pet_breed = "PP";
        }
        if ($value['stats']['breed_id'] == "5" OR $value['stats']['breed_id'] == "15"){
            $pet_breed = "SS";
        }
        if ($value['stats']['breed_id'] == "6" OR $value['stats']['breed_id'] == "16"){
            $pet_breed = "HH";
        }
        if ($value['stats']['breed_id'] == "7" OR $value['stats']['breed_id'] == "17"){
            $pet_breed = "HP";
        }
        if ($value['stats']['breed_id'] == "8" OR $value['stats']['breed_id'] == "18"){
            $pet_breed = "PS";
        }
        if ($value['stats']['breed_id'] == "9" OR $value['stats']['breed_id'] == "19"){
            $pet_breed = "HS";
        }
        if ($value['stats']['breed_id'] == "10" OR $value['stats']['breed_id'] == "20"){
            $pet_breed = "PB";
        }
        if ($value['stats']['breed_id'] == "11" OR $value['stats']['breed_id'] == "21"){
            $pet_breed = "SB";
        }
        if ($value['stats']['breed_id'] == "12" OR $value['stats']['breed_id'] == "22"){
            $pet_breed = "HB";
        }
        switch ($value['quality']['type']) {
          case "RARE":
             $pet_quality = 3;
             break;
          case "UNCOMMON":
             $pet_quality = 2;
             break;
          case "COMMON":
             $pet_quality = 1;
             break;
          case "POOR":
             $pet_quality = 0;
             break;
      }
        $colpets[] = array("Species" => $value['species']['id'], "Breed"=> $pet_breed, "Level"=> $value['level'], "Quality" => $pet_quality);
    }
    return $colpets;
}


// Creates a path if it does not exist already
function createPath($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = createPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}


// ======================================= AUTO TRANSLATE INSTRUCTIONS IN STRATGIES ===========================================================================

function translate_instruction($instruction, $language) {

    // [pet=Name] comes in
    if (preg_match('/^\[i\]\[pet=[^\[]{0,9999}\] comes in.{0,1}\[\/i\].{0,1}$/', $instruction)) {
        $pieces = explode("[pet=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        if ($language == "de_DE") { $instruction = "[i][pet=".$piecestwo[0]."] betritt den Kampf[/i]"; }
        if ($language == "es_ES") { $instruction = "[i]Entra [pet=".$piecestwo[0]."].[/i]"; }
        if ($language == "fr_FR") { $instruction = "[i][pet=".$piecestwo[0]."] rejoint le combat[/i]"; }
        if ($language == "it_IT") { $instruction = "[i][pet=".$piecestwo[0]."] si unisce allo scontro[/i]"; }
        if ($language == "ru_RU") { $instruction = "[i][pet=".$piecestwo[0]."] вступает в бой[/i]"; }
        if ($language == "pt_BR") { $instruction = "[i]Entra [pet=".$piecestwo[0]."].[/i]"; }
    }

    // [pet=Name] comes in PT 2
    if (preg_match('/^\[pet=[^\[]{0,9999}\] comes in.{0,1}$/', $instruction)) {
        $pieces = explode("[pet=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        if ($language == "de_DE") { $instruction = "[pet=".$piecestwo[0]."] betritt den Kampf"; }
        if ($language == "es_ES") { $instruction = "Entra [pet=".$piecestwo[0]."]."; }
        if ($language == "fr_FR") { $instruction = "[pet=".$piecestwo[0]."] rejoint le combat"; }
        if ($language == "it_IT") { $instruction = "[pet=".$piecestwo[0]."] si unisce allo scontro"; }
        if ($language == "ru_RU") { $instruction = "[pet=".$piecestwo[0]."] вступает в бой"; }
        if ($language == "pt_BR") { $instruction = "Entra [pet=".$piecestwo[0]."]."; }
    }

    // [enemy=Name] comes in
    if (preg_match('/^\[i\]\[enemy=[^\[]{0,9999}\] comes in.{0,1}\[\/i\].{0,1}$/', $instruction)) {
        $pieces = explode("[enemy=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        if ($language == "de_DE") { $instruction = "[i][enemy=".$piecestwo[0]."] betritt den Kampf[/i]"; }
        if ($language == "es_ES") { $instruction = "[i]Entra [enemy=".$piecestwo[0]."].[/i]"; }
        if ($language == "fr_FR") { $instruction = "[i][enemy=".$piecestwo[0]."] rejoint le combat[/i]"; }
        if ($language == "it_IT") { $instruction = "[i][enemy=".$piecestwo[0]."] si unisce allo scontro[/i]"; }
        if ($language == "ru_RU") { $instruction = "[i][enemy=".$piecestwo[0]."] вступает в бой[/i]"; }
        if ($language == "pt_BR") { $instruction = "[i]Entra [enemy=".$piecestwo[0]."].[/i]"; }
    }

    // [enemy=Name] comes in PT 2
    if (preg_match('/^\[enemy=[^\[]{0,9999}\] comes in.{0,1}$/', $instruction)) {
        $pieces = explode("[enemy=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        if ($language == "de_DE") { $instruction = "[enemy=".$piecestwo[0]."] betritt den Kampf"; }
        if ($language == "es_ES") { $instruction = "Entra [enemy=".$piecestwo[0]."]."; }
        if ($language == "fr_FR") { $instruction = "[enemy=".$piecestwo[0]."] rejoint le combat"; }
        if ($language == "it_IT") { $instruction = "[enemy=".$piecestwo[0]."] si unisce allo scontro"; }
        if ($language == "ru_RU") { $instruction = "[enemy=".$piecestwo[0]."] вступает в бой"; }
        if ($language == "pt_BR") { $instruction = "Entra [enemy=".$piecestwo[0]."]."; }
    }

    // Bring in your [pet=Name]
    if (preg_match('/^\[i\]Bring in your \[pet=[^\[]{0,9999}\].{0,1}\[\/i\].{0,1}$/', $instruction) OR preg_match('/^\[i\]Bring in \[pet=[^\[]{0,9999}\].{0,1}\[\/i\].{0,1}$/', $instruction)){
        $pieces = explode("[pet=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        if ($language == "de_DE") { $instruction = "[i]Aktiviere [pet=".$piecestwo[0]."][/i]"; }
        if ($language == "es_ES") { $instruction = "[i]Saca tu [pet=".$piecestwo[0]."].[/i]"; }
        if ($language == "fr_FR") { $instruction = "[i]Faites rentrer votre [pet=".$piecestwo[0]."].[/i]"; }
        if ($language == "it_IT") { $instruction = "[i]Fai entrare [pet=".$piecestwo[0]."].[/i]"; }
        if ($language == "ru_RU") { $instruction = "[i]Выберите своего питомца [pet=".$piecestwo[0]."][/i]"; }
        if ($language == "pt_BR") { $instruction = "[i]Troque sua mascote para [pet=".$piecestwo[0]."].[/i]"; }
    }

    // Bring in your [pet=Name] PT 2
    if (preg_match('/^Bring in your \[pet=[^\[]{0,9999}\].{0,2}$/', $instruction) OR preg_match('/^Bring in \[pet=[^\[]{0,9999}\].{0,2}$/', $instruction)){
        $pieces = explode("[pet=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        if ($language == "de_DE") { $instruction = "Aktiviere [pet=".$piecestwo[0]."]"; }
        if ($language == "es_ES") { $instruction = "Saca tu [pet=".$piecestwo[0]."]."; }
        if ($language == "fr_FR") { $instruction = "Faites rentrer votre [pet=".$piecestwo[0]."]."; }
        if ($language == "it_IT") { $instruction = "Fai entrare [pet=".$piecestwo[0]."]."; }
        if ($language == "ru_RU") { $instruction = "Выберите своего питомца [pet=".$piecestwo[0]."]"; }
        if ($language == "pt_BR") { $instruction = "Troque sua mascote para [pet=".$piecestwo[0]."]."; }
    }

    // [spell=Spellname] - [pet=Petname] dies 
    if (preg_match('/^\[spell=[^\[]{0,9999}\] - \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] - your \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("your","dein",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace(" -",".",$instruction); $instruction = str_replace("dies","muere",$instruction); $instruction = str_replace("your","Tu",$instruction); }
        if ($language == "fr_FR") { $instruction = $instruction = str_replace("dies","meurt",$instruction); $instruction = str_replace("your ","votre",$instruction); }
        if ($language == "it_IT") { $instruction = $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "pt_BR") { $instruction = $instruction = str_replace("dies","morrer",$instruction); $instruction = str_replace("your ","seu",$instruction); }
    }
    // [ability=] - [pet=Petname] dies 
    if (preg_match('/^\[ability=[^\[]{0,9999}\] - \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[ability=[^\[]{0,9999}\] - your \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("your","dein",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace(" -",".",$instruction); $instruction = str_replace("dies","muere",$instruction); $instruction = str_replace("your","Tu",$instruction); }
        if ($language == "fr_FR") { $instruction = $instruction = str_replace("dies","meurt",$instruction); $instruction = str_replace("your ","votre",$instruction); }
        if ($language == "it_IT") { $instruction = $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "pt_BR") { $instruction = $instruction = str_replace("dies","morrer",$instruction); $instruction = str_replace("your ","seu",$instruction); }
    }
    
    // [spell=Spellname] - [pet=Petname] dies 
    if (preg_match('/^\[spell=[^\[]{0,9999}\] - \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] - your \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("your","dein",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace(" -",".",$instruction); $instruction = str_replace("dies","muere",$instruction); $instruction = str_replace("your","Tu",$instruction); }
        if ($language == "fr_FR") { $instruction = $instruction = str_replace("dies","meurt",$instruction); $instruction = str_replace("your ","votre",$instruction); }
        if ($language == "it_IT") { $instruction = $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "pt_BR") { $instruction = $instruction = str_replace("dies","morrer",$instruction); $instruction = str_replace("your ","seu",$instruction); }
    }
    // [ability=] - [pet=Petname] dies 
    if (preg_match('/^\[ability=[^\[]{0,9999}\] - \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[ability=[^\[]{0,9999}\] - your \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("your","dein",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace(" -",".",$instruction); $instruction = str_replace("dies","muere",$instruction); $instruction = str_replace("your","Tu",$instruction); }
        if ($language == "fr_FR") { $instruction = $instruction = str_replace("dies","meurt",$instruction); $instruction = str_replace("your ","votre",$instruction); }
        if ($language == "it_IT") { $instruction = $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "pt_BR") { $instruction = $instruction = str_replace("dies","morrer",$instruction); $instruction = str_replace("your ","seu",$instruction); }
    }

    // [spell=Spellname] - [enemy=Petname] dies
    if (preg_match('/^\[spell=[^\[]{0,9999}\] - \[enemy=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("dies","stirbt",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace(" -",".",$instruction); $instruction = str_replace("dies","muere",$instruction); }
        if ($language == "fr_FR") { $instruction = $instruction = str_replace("dies","meurt",$instruction); }
        if ($language == "it_IT") { $instruction = $instruction = str_replace("dies","muore",$instruction); }
        if ($language == "pt_BR") { $instruction = $instruction = str_replace("dies","morrer",$instruction); }
    }
    // [ability=Spellname] - [enemy=Petname] dies
    if (preg_match('/^\[ability=[^\[]{0,9999}\] - \[enemy=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("dies","stirbt",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace(" -",".",$instruction); $instruction = str_replace("dies","muere",$instruction); }
        if ($language == "fr_FR") { $instruction = $instruction = str_replace("dies","meurt",$instruction); }
        if ($language == "it_IT") { $instruction = $instruction = str_replace("dies","muore",$instruction); }
        if ($language == "pt_BR") { $instruction = $instruction = str_replace("dies","morrer",$instruction); }
    }

    // Pass
    if (preg_match('/^Pass.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = "Passe"; }
        if ($language == "es_ES") { $instruction = "Pasar."; }
        if ($language == "fr_FR") { $instruction = "Passer"; }
        if ($language == "it_IT") { $instruction = "Passa"; }
        if ($language == "ru_RU") { $instruction = "Пропустите ход"; }
        if ($language == "pt_BR") { $instruction = "Passe"; }
    }

    // Swap to your level pet pt2
    if (preg_match('/^\[i\]Swap to your Level Pet.{0,1}\[\/i\].{0,1}$/', $instruction) OR preg_match('/^\[i\]Bring in your Level Pet.{0,1}\[\/i\].{0,1}$/', $instruction)){
        if ($language == "de_DE") { $instruction = "[i]Wechsle zu deinem Level Pet[/i]"; }
        if ($language == "es_ES") { $instruction = "[i]Saca tu mascota de nivel bajo.[/i]"; }
        if ($language == "fr_FR") { $instruction = "[i]Faites rentrer votre mascotte de bas niveau[/i]"; }
        if ($language == "it_IT") { $instruction = "[i]Fai entrare la mascotte di livello basso[/i]"; }
        if ($language == "ru_RU") { $instruction = "[i]Выберите питомца, которого прокачиваете[/i]"; }
        if ($language == "pt_BR") { $instruction = "[i]Traga seu mascote de nível baixo[/i]"; }
    }

    // Swap to your level pet
    if (preg_match('/^Swap to your Level Pet.{0,2}$/', $instruction) OR preg_match('/^Bring in your Level Pet.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = "Wechsle zu deinem Level Pet"; }
        if ($language == "es_ES") { $instruction = "Saca tu mascota de nivel bajo."; }
        if ($language == "fr_FR") { $instruction = "Faites rentrer votre mascotte de bas niveau"; }
        if ($language == "it_IT") { $instruction = "Fai entrare la mascotte di livello basso"; }
        if ($language == "ru_RU") { $instruction = "Выберите питомца, которого прокачиваете"; }
        if ($language == "pt_BR") { $instruction = "Traga seu mascote de nível baixo"; }
    }

    // An enemy pet comes in
    if (preg_match('/^\[i\]An enemy pet comes in.{0,1}\[\/i\].{0,1}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("An enemy pet comes in","Ein gegnerisches Haustier wird aktiv",$instruction); }
    }


    // Spell X until pet Y resurrects
    if (preg_match('/^\[spell=[^\[]{0,9999}\] until \[pet=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until the \[pet=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("resurrects","wieder aufersteht",$instruction); $instruction = str_replace("the ","",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la résurrection de",$instruction); $instruction = str_replace(" resurrects","",$instruction); $instruction = str_replace("the ","",$instruction); }
    }
    // ability X until pet Y resurrects
    if (preg_match('/^\[ability=[^\[]{0,9999}\] until \[pet=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until the \[pet=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("resurrects","wieder aufersteht",$instruction); $instruction = str_replace("the ","",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la résurrection de",$instruction); $instruction = str_replace(" resurrects","",$instruction); $instruction = str_replace("the ","",$instruction); }
    }

    // Spell X until enemy Y resurrects
    if (preg_match('/^\[spell=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until the \[enemy=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("resurrects","wieder aufersteht",$instruction); $instruction = str_replace("the ","",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la résurrection de",$instruction); $instruction = str_replace(" resurrects","",$instruction); $instruction = str_replace("the ","",$instruction); }
    }
    // ability X until enemy Y resurrects
    if (preg_match('/^\[ability=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until the \[enemy=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("resurrects","wieder aufersteht",$instruction); $instruction = str_replace("the ","",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la résurrection de",$instruction); $instruction = str_replace(" resurrects","",$instruction); $instruction = str_replace("the ","",$instruction); }
    }

    // Spell X until the fight is won
    if (preg_match('/^\[spell=[^\[]{0,9999}\] until the fight is won.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("until the fight is won","bis der Kampf gewonnen ist",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("until the fight is won","hasta ganar el combate",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until the fight is won","jusqu'à la fin du combat",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("until the fight is won","fino a quando lo scontro è vinto",$instruction); }
        if ($language == "ru_RU") { $instruction = str_replace(" until the fight is won",", пока не одержите победу",$instruction); $instruction = "Используйте ".$instruction; }
        if ($language == "pt_BR") { $instruction = str_replace("until the fight is won","até a luta terminar",$instruction); }
    }
    // ability X until the fight is won
    if (preg_match('/^\[ability=[^\[]{0,9999}\] until the fight is won.{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("until the fight is won","bis der Kampf gewonnen ist",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("until the fight is won","hasta ganar el combate",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until the fight is won","jusqu'à la fin du combat",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("until the fight is won","fino a quando lo scontro è vinto",$instruction); }
        if ($language == "ru_RU") { $instruction = str_replace(" until the fight is won",", пока не одержите победу",$instruction); $instruction = "Используйте ".$instruction; }
        if ($language == "pt_BR") { $instruction = str_replace("until the fight is won","até a luta terminar",$instruction); }
    }

    // Swap back to (your) pet X
    if (preg_match('/^\Swap back to \[pet=[^\[]{0,9999}\].{0,2}$/', $instruction) OR preg_match('/^\Swap back to your \[pet=[^\[]{0,9999}\].{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("Swap back to","Wechsle zurück zu",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("Swap back to","Vuelve a sacar tu",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("Swap back to","Faites revenir votre",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("Swap back to","Fai rientrare",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "ru_RU") { $instruction = str_replace("Swap back to","Переключитесь на своего питомца",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "pt_BR") { $instruction = str_replace("Swap back to","Troque sua mascote para",$instruction); $instruction = $instruction." de volta"; $instruction = str_replace("your ","",$instruction); }
    }

    // Swap to (your) pet X
    if (preg_match('/^\Swap to \[pet=[^\[]{0,9999}\].{0,2}$/', $instruction) OR preg_match('/^\Swap to your \[pet=[^\[]{0,9999}\].{0,2}$/', $instruction)){
        if ($language == "de_DE") { $instruction = str_replace("Swap to","Wechsle zu",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("Swap to","Sacar tu",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("Swap to","Faites rentrer votre",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("Swap to","Fai entrare",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "ru_RU") { $instruction = str_replace("Swap to","Переключитесь на своего питомца",$instruction); $instruction = str_replace("your ","",$instruction); }
        if ($language == "pt_BR") { $instruction = str_replace("Swap to","Traga seu",$instruction); $instruction = str_replace("your ","",$instruction); }
    }

    // [spell=x] until [pet=Y] dies
    if (preg_match('/^\[spell=[^\[]{0,9999}\] until \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until \[pet=[^\[]{0,9999}\] is dead.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until your \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until your \[pet=[^\[]{0,9999}\] is dead.{0,2}$/', $instruction)) {
        $pieces = explode("[spell=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        $piecess = explode("[pet=",$instruction);
        $piecesthree = explode("]",$piecess[1]);
        $instruction = str_replace("your ","",$instruction);
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("is dead","stirbt",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("until","hasta que",$instruction); $instruction = str_replace("dies","muera",$instruction); $instruction = str_replace("is dead","muera",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la mort de",$instruction); $instruction = str_replace("dies","",$instruction); $instruction = str_replace("is dead","",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("until","fino a quando",$instruction); $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("is dead","muore",$instruction); }
        if ($language == "ru_RU") { $instruction = "Используйте [spell=".$piecestwo[0]."], пока [pet=".$piecesthree[0]."] не умрет"; }
        if ($language == "pt_BR") { $instruction = str_replace("until","até",$instruction); $instruction = str_replace("dies","morrer.",$instruction); $instruction = str_replace("is dead","morrer.",$instruction);}
    }
    // [ability=x] until [pet=Y] dies
    if (preg_match('/^\[ability=[^\[]{0,9999}\] until \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[ability=[^\[]{0,9999}\] until \[pet=[^\[]{0,9999}\] is dead.{0,2}$/', $instruction) OR preg_match('/^\[ability=[^\[]{0,9999}\] until your \[pet=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[ability=[^\[]{0,9999}\] until your \[pet=[^\[]{0,9999}\] is dead.{0,2}$/', $instruction)) {
        $pieces = explode("[ability=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        $piecess = explode("[pet=",$instruction);
        $piecesthree = explode("]",$piecess[1]);
        $instruction = str_replace("your ","",$instruction);
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("is dead","stirbt",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("until","hasta que",$instruction); $instruction = str_replace("dies","muera",$instruction); $instruction = str_replace("is dead","muera",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la mort de",$instruction); $instruction = str_replace("dies","",$instruction); $instruction = str_replace("is dead","",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("until","fino a quando",$instruction); $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("is dead","muore",$instruction); }
        if ($language == "ru_RU") { $instruction = "Используйте [spell=".$piecestwo[0]."], пока [pet=".$piecesthree[0]."] не умрет"; }
        if ($language == "pt_BR") { $instruction = str_replace("until","até",$instruction); $instruction = str_replace("dies","morrer.",$instruction); $instruction = str_replace("is dead","morrer.",$instruction);}
    }

    // [spell=x] until [enemy=Y] dies
    if (preg_match('/^\[spell=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] is dead.{0,2}$/', $instruction)) {
        $pieces = explode("[spell=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        $piecess = explode("[enemy=",$instruction);
        $piecesthree = explode("]",$piecess[1]);
        $instruction = str_replace("your ","",$instruction);
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("is dead","stirbt",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("until","hasta que",$instruction); $instruction = str_replace("dies","muera",$instruction); $instruction = str_replace("is dead","muera",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la mort de",$instruction); $instruction = str_replace("dies","",$instruction); $instruction = str_replace("is dead","",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("until","fino a quando",$instruction); $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("is dead","muore",$instruction); }
        if ($language == "ru_RU") { $instruction = "Используйте [spell=".$piecestwo[0]."], пока [enemy=".$piecesthree[0]."] не умрет"; }
        if ($language == "pt_BR") { $instruction = str_replace("until","até",$instruction); $instruction = str_replace("dies","morrer.",$instruction); $instruction = str_replace("is dead","morrer.",$instruction);}
    }
    // [spell=x] until [enemy=Y] dies
    if (preg_match('/^\[ability=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] dies.{0,2}$/', $instruction) OR preg_match('/^\[ability=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] is dead.{0,2}$/', $instruction)) {
        $pieces = explode("[ability=",$instruction);
        $piecestwo = explode("]",$pieces[1]);
        $piecess = explode("[enemy=",$instruction);
        $piecesthree = explode("]",$piecess[1]);
        $instruction = str_replace("your ","",$instruction);
        if ($language == "de_DE") { $instruction = str_replace("until","bis",$instruction); $instruction = str_replace("dies","stirbt",$instruction); $instruction = str_replace("is dead","stirbt",$instruction); }
        if ($language == "es_ES") { $instruction = str_replace("until","hasta que",$instruction); $instruction = str_replace("dies","muera",$instruction); $instruction = str_replace("is dead","muera",$instruction); }
        if ($language == "fr_FR") { $instruction = str_replace("until","jusqu'à la mort de",$instruction); $instruction = str_replace("dies","",$instruction); $instruction = str_replace("is dead","",$instruction); }
        if ($language == "it_IT") { $instruction = str_replace("until","fino a quando",$instruction); $instruction = str_replace("dies","muore",$instruction); $instruction = str_replace("is dead","muore",$instruction); }
        if ($language == "ru_RU") { $instruction = "Используйте [spell=".$piecestwo[0]."], пока [enemy=".$piecesthree[0]."] не умрет"; }
        if ($language == "pt_BR") { $instruction = str_replace("until","até",$instruction); $instruction = str_replace("dies","morrer.",$instruction); $instruction = str_replace("is dead","morrer.",$instruction);}
    }
    return $instruction;
}



// ======================================= AUTO TRANSLATE TURNS IN STRATEGIES ===========================================================================

function translate_turn($step, $language) {

if (preg_match('/Turn/',$step) AND !preg_match('/Turns/',$step))
    {
        $teile = explode(" ", $step);
        if ($language == "de_DE") { $step = str_replace("Turn","Runde",$step); }
        if ($language == "es_ES") { $step = str_replace("Turn","Turno",$step); }
        if ($language == "fr_FR") { $step = str_replace("Turn","Tour",$step); }
        if ($language == "pl_PL") { $step = str_replace("Turn","Tura",$step); }
        if ($language == "it_IT") { $step = str_replace("Turn","Turno",$step); }
        if ($language == "ru_RU") { $step = $teile[1]." раунд"; }
        if ($language == "pt_BR") { $step = str_replace("Turn","Rodada",$step); }
    }

if (preg_match('/Turns/',$step))
    {
        $teile = explode(" ", $step);
        if ($language == "de_DE") { $step = str_replace("Turns","Runde",$step); }
        if ($language == "es_ES") { $step = str_replace("Turns","Turnos",$step); }
        if ($language == "fr_FR") { $step = str_replace("Turns","Tours",$step); }
        if ($language == "pl_PL") { $step = str_replace("Turns","Tura",$step); }
        if ($language == "it_IT") { $step = str_replace("Turns","Turni",$step); }
        if ($language == "ru_RU") { $step = $teile[1]." раунды"; }
        if ($language == "pt_BR") { $step = str_replace("Turns","Rodadas",$step); }
    }
if (preg_match('/No/',$step) && strlen($step) == 2)
    {
        if ($language == "de_DE") { $step = str_replace("No","Nein",$step); }
        if ($language == "es_ES") { $step = $step; }
        if ($language == "fr_FR") { $step = str_replace("No","Non",$step); }
        if ($language == "pl_PL") { $step = str_replace("No","Nie",$step); }
        if ($language == "it_IT") { $step = $step; }
        if ($language == "ru_RU") { $step = str_replace("No","Нет",$step); }
        if ($language == "pt_BR") { $step = str_replace("No","Não",$step); }
    }
if (preg_match('/Yes/',$step))
    {
        if ($language == "de_DE") { $step = str_replace("Yes","Ja",$step); }
        if ($language == "es_ES") { $step = str_replace("Yes","Sí",$step); }
        if ($language == "fr_FR") { $step = str_replace("Yes","Oui",$step); }
        if ($language == "pl_PL") { $step = str_replace("Yes","Tak",$step); }
        if ($language == "it_IT") { $step = str_replace("Yes","Sì",$step); }
        if ($language == "ru_RU") { $step = str_replace("Yes","Да",$step); }
        if ($language == "pt_BR") { $step = str_replace("Yes","Sim",$step); }
    }
if (preg_match('/If/',$step))
    {
        if ($language == "de_DE") { $step = str_replace("If","Wenn",$step); }
        if ($language == "es_ES") { $step = str_replace("If","Si",$step); }
        if ($language == "fr_FR") { $step = str_replace("If","Si",$step); }
        if ($language == "pl_PL") { $step = str_replace("If","Wtedy",$step); }
        if ($language == "it_IT") { $step = str_replace("If","Se",$step); }
        if ($language == "ru_RU") { $step = str_replace("If","Если",$step); }
        if ($language == "pt_BR") { $step = str_replace("If","Se",$step); }
    }
if (preg_match('/Then/',$step))
    {
        if ($language == "de_DE") { $step = str_replace("Then","Dann",$step); }
        if ($language == "es_ES") { $step = str_replace("Then","Entonces",$step); }
        if ($language == "fr_FR") { $step = str_replace("Then","Ensuite",$step); }
        if ($language == "pl_PL") { $step = str_replace("Then","Potem",$step); }
        if ($language == "it_IT") { $step = str_replace("Then","Allora",$step); }
        if ($language == "ru_RU") { $step = str_replace("Then","Тогда",$step); }
        if ($language == "pt_BR") { $step = str_replace("Then","Então",$step); }
    }
if (preg_match('/Either/',$step))
    {
        if ($language == "de_DE") { $step = str_replace("Either","Entweder",$step); }
        if ($language == "es_ES") { $step = str_replace("Either","O bien",$step); }
        if ($language == "fr_FR") { $step = str_replace("Either","Ou bien",$step); }
        if ($language == "pl_PL") { $step = str_replace("Either","Albo",$step); }
        if ($language == "it_IT") { $step = str_replace("Either","O",$step); }
        if ($language == "ru_RU") { $step = str_replace("Either","Или",$step); }
        if ($language == "pt_BR") { $step = str_replace("Either","Ou",$step); }
    }
if (preg_match('/Or/',$step))
    {
        if ($language == "de_DE") { $step = str_replace("Or","Oder",$step); }
        if ($language == "es_ES") { $step = str_replace("Or","O",$step); }
        if ($language == "fr_FR") { $step = str_replace("Or","Ou",$step); }
        if ($language == "pl_PL") { $step = str_replace("Or","Albo",$step); }
        if ($language == "it_IT") { $step = str_replace("Or","O",$step); }
        if ($language == "ru_RU") { $step = str_replace("Or","Или",$step); }
        if ($language == "pt_BR") { $step = str_replace("Or","Ou",$step); }
    }
if (preg_match('/Prio/',$step))
    {
        if ($language == "de_DE") { $step = $step; }
        if ($language == "es_ES") { $step = str_replace("Prio","Prioridad",$step); }
        if ($language == "fr_FR") { $step = str_replace("Prio","Priorité",$step); }
        if ($language == "pl_PL") { $step = $step; }
        if ($language == "it_IT") { $step = str_replace("Prio","Priorità",$step); }
        if ($language == "ru_RU") { $step = str_replace("Prio","Прио",$step); }
        if ($language == "pt_BR") { $step = str_replace("Prio","Prioridade",$step); }
    }
    return $step;
}


// Get all tags into array

function get_all_tags() {
  global $dbcon, $language;
  $all_tags_db = mysqli_query($dbcon, "SELECT * FROM StrategyTags");
  while ($this_tag = $all_tags_db->fetch_object())
  {
    $all_tags[$this_tag->id]['ID'] = $this_tag->id;
    $all_tags[$this_tag->id]['Name'] = __($this_tag->Name);
    $all_tags[$this_tag->id]['Slug'] = $this_tag->Slug;
    $all_tags[$this_tag->id]['Access'] = $this_tag->Access;
    $all_tags[$this_tag->id]['Visible'] = $this_tag->Visible;
    $all_tags[$this_tag->id]['Color'] = $this_tag->Color;
    $all_tags[$this_tag->id]['DefaultPrio'] = $this_tag->DefaultPrio;
    $all_tags[$this_tag->id]['Description'] = __($this_tag->Description);
    $all_tags[$this_tag->id]['Active'] = 0;
  }
  return $all_tags;
}


// ======================================= SET LANGUAGE GLOBAL VARIABLES ===========================================================================


function set_language_vars($lang) {

if ($lang == "de_DE") {
 $GLOBALS['wowhdomain'] = "de";
 $GLOBALS['xufudomain'] = "de";
}
else if ($lang == "it_IT") {
 $GLOBALS['wowhdomain'] = "it";
 $GLOBALS['xufudomain'] = "it";
}
else if ($lang == "es_ES") {
 $GLOBALS['wowhdomain'] = "es";
 $GLOBALS['xufudomain'] = "es";
}
else if ($lang == "fr_FR") {
 $GLOBALS['wowhdomain'] = "fr";
 $GLOBALS['xufudomain'] = "fr";
}
else if ($lang == "pt_BR") {
 $GLOBALS['wowhdomain'] = "pt";
 $GLOBALS['xufudomain'] = "pt";
}
else if ($lang == "ru_RU") {
 $GLOBALS['wowhdomain'] = "ru";
 $GLOBALS['xufudomain'] = "ru";
}
else if ($lang == "pl_PL") {
 $GLOBALS['wowhdomain'] = "pl";
 $GLOBALS['xufudomain'] = "pl";
}
else if ($lang == "ko_KR") {
 $GLOBALS['wowhdomain'] = "ko";
 $GLOBALS['xufudomain'] = "ko";
}
else if ($lang == "zh_TW") {
 $GLOBALS['wowhdomain'] = "cn";
 $GLOBALS['xufudomain'] = "zh";
}

 $GLOBALS['stepext'] = "Step_".$lang;
 $GLOBALS['instext'] = "Instruction_".$lang;
 $GLOBALS['subnameext'] = "Name_".$lang;
 $GLOBALS['subcomext'] = "Comment_".$lang;
 $GLOBALS['petnext'] = "Name_".$lang;
 $GLOBALS['altcommentext'] = "Comment_".$lang;

if ($lang == "en_US") {
  $GLOBALS['wowhdomain'] = "en";
 $GLOBALS['stepext'] = "Step";
 $GLOBALS['instext'] = "Instruction";
 $GLOBALS['subnameext'] = "Name";
 $GLOBALS['subcomext'] = "Comment";
 $GLOBALS['petnext'] = "Name";
 $GLOBALS['altcommentext'] = "Comment";
}

}

// ======================================= Replace URLs ===========================================================================


function replace_url( $text )
    {
    	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);
    	$ret = ' ' . $text;
    	// Replace Links with http://
    	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" class=\"comlink\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);
    	// Replace Links without http://
    	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" class=\"comlink\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);
    	// Replace Email Addressesf
    	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a class=\"comlink\" href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
    	$ret = substr($ret, 1);
    	return $ret;
}

function replace_url_discord( $text )
    {
    	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);
    	$ret = ' ' . $text;
    	// Replace Links with http://
    	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<\\2>", $ret);
    	// Replace Links without http://
    	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<\\2>", $ret);
    	// Replace Email Addressesf
    	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<\\2@\\3\>", $ret);
    	$ret = substr($ret, 1);
    	return $ret;
}

function replace_url_dark( $text )
    {
    	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);
    	$ret = ' ' . $text;
    	// Replace Links with http://
    	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" class=\"comlinkdark\"  target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);
    	// Replace Links without http://
    	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" class=\"comlinkdark\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);
    	// Replace Email Addressesf
    	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a class=\"comlinkdark\" href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
    	$ret = substr($ret, 1);
    	return $ret;
}

function AutoLinkUrls($str, $popup = 0, $linkstyle = "bright", $tsize = "14")
{
if ($popup == "1") {
    $pop = ' target="_blank" ';
}
if ($linkstyle == "bright") {
    if ($tsize != "14") {
        $desstyle = ' class="comlink" style="font-size: '.$tsize.'px" ';
    }
    else {
        $desstyle = ' class="comlink" ';
    }
}
if ($linkstyle == "dark") {
    if ($tsize != "14") {
        $desstyle = ' class="comlinkdark" style="font-size: '.$tsize.'px" ';
    }
    else {
        $desstyle = ' class="comlinkdark" ';
    }
}
if ($linkstyle == "strategy") {
    if ($tsize != "14") {
        $desstyle = ' class="comlink" style="font-size: '.$tsize.'px" ';
    }
    else {
        $desstyle = ' class="comlink" ';
    }
}
if ($linkstyle == "home") {
    if ($tsize != "14") {
        $desstyle = ' class="weblink" style="font-size: '.$tsize.'px" ';
    }
    else {
        $desstyle = ' class="weblink" ';
    }
}

$find=array('`((?:https?|ftp)://\S+[[:alnum:]]/?)`si','`((?<!//)(www\.\S+[[:alnum:]]/?))`si');
$replace=array('<a href="$1" '.$pop.' '.$desstyle.'>$1</a>', '<a href="http://$1" '.$pop.' '.$desstyle.'>$1</a>');

return preg_replace($find,$replace,$str);
}


// ======================================= Turn Pet Family Number into Name ===========================================================================


function convert_family($famnumber) {
    switch ($famnumber) {
        case "0":
            $spellfam = "Humanoid";
            break;
        case "1":
            $spellfam = "Dragonkin";
            break;
        case "2":
            $spellfam = "Flying";
            break;
        case "3":
            $spellfam = "Undead";
            break;
        case "4":
            $spellfam = "Critter";
            break;
        case "5":
            $spellfam = "Magic";
            break;
        case "6":
            $spellfam = "Elemental";
            break;
        case "7":
            $spellfam = "Beast";
            break;
        case "8":
            $spellfam = "Aquatic";
            break;
        case "9":
            $spellfam = "Mechanical";
            break;
        case "Humanoid":
            $spellfam = "0";
            break;
        case "Dragonkin":
            $spellfam = "1";
            break;
        case "Flying":
            $spellfam = "2";
            break;
        case "Undead":
            $spellfam = "3";
            break;
        case "Critter":
            $spellfam = "4";
            break;
        case "Magic":
            $spellfam = "5";
            break;
        case "Elemental":
            $spellfam = "6";
            break;
        case "Beast":
            $spellfam = "7";
            break;
        case "Aquatic":
            $spellfam = "8";
            break;
        case "Mechanical":
            $spellfam = "9";
            break;
        case "Mechanic":
            $spellfam = "9";
            break;
    }
    return $spellfam;
}


// ======================================= Localize Pet Family Names ===========================================================================


function localize_family( $famname )
    {
switch ($famname) {
    case "Humanoid":
       $returnfam = __("Humanoid");
       break;
    case "Flying":
       $returnfam = __("Flying");
       break;
    case "Magic":
       $returnfam = __("Magic");
       break;
    case "Elemental":
       $returnfam = __("Elemental");
       break;
    case "Undead":
       $returnfam = __("Undead");
       break;
    case "Mechanical":
       $returnfam = __("Mechanical");
       break;
    case "Critter":
       $returnfam = __("Critter");
       break;
    case "Aquatic":
       $returnfam = __("Aquatic");
       break;
    case "Beast":
       $returnfam = __("Beast");
       break;
    case "Dragonkin":
       $returnfam = __("Dragonkin");
       break;
}
    return $returnfam;
}


// ======================================= Duplicate SQL Entry ===========================================================================

function DuplicateMySQLRecord ($table, $id_field, $id) {
    $dbcon = $GLOBALS['dbcon'];
    // load the original record into an array
    $result = mysqli_query($dbcon, "SELECT * FROM {$table} WHERE {$id_field}={$id}");
    $original_record = mysqli_fetch_assoc($result);

    // insert the new record and get the new auto_increment id
    mysqli_query($dbcon, "INSERT INTO {$table} (`{$id_field}`) VALUES (NULL)");
    $newid = mysqli_insert_id($dbcon);

    // generate the query to update the new record with the previous values
    $query = "UPDATE {$table} SET ";
    foreach ($original_record as $key => $value) {
        if ($key != $id_field) {
            $query .= '`'.$key.'` = "'.mysqli_real_escape_string($dbcon, $value).'", ';
        }
    }
    $query = substr($query,0,strlen($query)-2); // lop off the extra trailing comma
    $query .= " WHERE {$id_field}={$newid}";
    mysqli_query($dbcon, $query);

    // return the new id
    return $newid;
}



// =======================================  ===========================================================================

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}


// ======================================= Find brackets [ ] and return contents in array ===========================================================================
   function extract_text($string)
   {
    $text_outside=array();
    $text_inside=array();
    $t="";
    for($i=0;$i<strlen($string);$i++)
    {
        if($string[$i]=='[')
        {
            $text_outside[]=$t;
            $t="";
            $t1="";
            $i++;
            while($string[$i]!=']')
            {
                $t1.=$string[$i];
                $i++;
            }
            $text_inside[] = $t1;

        }
        else {
            if($string[$i]!=']')
            $t.=$string[$i];
            else {
                continue;
            }
        }
    }
    if($t!="")
    $text_outside[]=$t;

    return $text_inside;
  }





// =======================================================================================================================

function lookup_char_class($classid){
  global $dbcon, $language;
  $classesdb = mysqli_query($dbcon, "SELECT Name_$language FROM Character_Classes WHERE id = $classid") or die(mysqli_error($dbcon));
  if (mysqli_num_rows($classesdb) > 0) {
    $classame = mysqli_fetch_object($classesdb);
    $dbname = "Name_".$language;
    return $classame->{$dbname};
  }
}

// =======================================================================================================================

function lookup_char_race($raceid){
  global $dbcon, $language;
  $racesdb = mysqli_query($dbcon, "SELECT Name_$language FROM Character_Races WHERE id = $raceid") or die(mysqli_error($dbcon));
  if (mysqli_num_rows($racesdb) > 0) {
    $racename = mysqli_fetch_object($racesdb);
    $dbname = "Name_".$language;
    return $racename->{$dbname};
  }
}



// =======================================================================================================================


function check_pet_array($array, $find)
{
    if($array)
    {
        foreach($array as $item)
        {
            if($item['id'] == $find)
            {
                $item['found'] = "true";
                return $item;
            }
        }
    }
    $item['found'] = "false";
    return $item;
}


// =======================================================================================================================
use PHPMailer\PHPMailer\PHPMailer;
function xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody, $path = '../') {
  require_once $path.'thirdparty/PHPMailer/PHPMailer.php'; 
  require_once $path.'thirdparty/PHPMailer/SMTP.php'; 
  require_once $path.'thirdparty/PHPMailer/Exception.php';
  
  $mail = new PHPMailer();
  
  // $mail->SMTPDebug = 3; // Error output 0 = none, 1-3 = the higher the more detailed
  $mail->isSMTP();
  $mail->Host = 'sslout.df.eu';
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = false;
  $mail->SMTPAutoTLS = false;
  $mail->Username = xufu_mail_user;
  $mail->Password = xufu_mail_password;
  $mail->SMTPSecure = 'tls';
  $mail->Port = 25;

  if ($recname == "") $recname = ",";
  else $recname = " ".$recname.","; 
  
  $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
   <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Email from Xu-Fu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style type="text/css">
      table td { border-collapse: collapse; }
      .msoFix { mso-table-lspace:-1pt; mso-table-rspace:-1pt; }
    </style>
  </head>
  
  
  <body bgcolor="#e4e3da" link="#010b8e">
  
  <table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#edece2">
   <tr>  <td style="padding:15.0pt 15.0pt 15.0pt 15.0pt">
    <table align="center" border="0" cellspacing="0" cellpadding="0" width="700" bgcolor="#edece2">
     <tr>
      <td style="padding:0px 0px 15px;" valign="top"><center>
         <a href="https://www.wow-petguide.com"><img alt="Xu-Fu\'s WoW Pet Guides" border="1" style="display:block" src="https://www.wow-petguide.com/images/xufu_mail_header.jpg"></img></a>
         </center>
      </td>
     </tr>
  
     <tr>
      <td style="padding:padding:0px 0px 20px;" valign="top">
         <img border="0" alt="" style="display:block" align="left" src="https://www.wow-petguide.com/images/xufu_mail.png"></img>
         <h2 style="display: inline;font-size:22px;font-family: Verdana,sans-serif">Hey'.$recname.'</h2><br></br>
         <p style="display: inline;font-size:14px;font-family: Verdana,sans-serif">'.$content.'</p>
         <br></br>
         <p align="left" style="text-indent: 60px;line-height:13.5pt;font-size:18px;font-family:Verdana,sans-serif;color:#0a35b5"><i>Xu-Fu</i></p>
      </td>
     </tr>
    </table>
   </td>
   </tr>
  </table>
  
  </body>
  </html>';

  $mail->setFrom('xufu@wow-petguide.com', 'Xu-Fu');
  $mail->addReplyTo('xufu@wow-petguide.com', 'Xu-Fu');
  $mail->addAddress($recipient, $recname); 
  $mail->Subject = $subject;
  $mail->isHTML(true);
  $mail->Body = $body;
  $mail->msgHTML($body);
  
  //Replace the plain text body with one created manually
  if ($nonhtmlbody == "") $mail->AltBody = 'This is an HTML email. Please view it in a mail program or an online service that can display HTML emails. Thank you!';
  else $mail->AltBody = $nonhtmlbody;
  
  if($mail->send()){
  }
  else{
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
}


// =======================================================================================================================

function is_odd( $int )
{
return( $int & 1 );
}

// =======================================================================================================================


function sortBy($field, &$array, $direction = 'asc', $reindex = 'no_reindex')
{
  if ($reindex == 'no_reindex'){ 
    uasort($array, function($a, $b) use ($field, $direction) {
        $a = $a[$field];
        $b = $b[$field];

        if ($a == $b)
        {
            return 0;
        }
        if ($direction == 'desc')
        {
          return $a > $b ? -1 : 1;
        }
        else
        {
          return $a < $b ? -1 : 1;
        }
    });
  }
  if ($reindex == 'reindex'){ 
    usort($array, function($a, $b) use ($field, $direction) {
        $a = $a[$field];
        $b = $b[$field];

        if ($a == $b)
        {
            return 0;
        }
        if ($direction == 'desc')
        {
          return $a > $b ? -1 : 1;
        }
        else
        {
          return $a < $b ? -1 : 1;
        }
    });
  }
    return true;
}



// =======================================================================================================================

function find_key_value($array, $key, $val)
{
    foreach ($array as $item)
    {
       if (is_array($item))
       {
           find_key_value($item, $key, $val);
       }

        if (isset($item[$key]) && $item[$key] == $val) return true;
    }
    return false;
}



// =======================================================================================================================

function get_all_pets ($petnext = "Name", $external = 0) {
    $dbcon = $GLOBALS['dbcon'];
    $all_pets = array();
    if ($external ==  0) {
      $all_pets = $GLOBALS['all_pets'];
    }
    if (!$all_pets) {
        $allpetsdb = mysqli_query($dbcon, "SELECT $petnext, Name, RematchID, PetID, Skill1, Skill2, Skill3, Skill4, Skill5, Skill6, Family, Health, Power, Speed, BB, PP, SS, HH, HP, PS, HS, PB, SB, HB, Source, Cageable, Special, Obtainable FROM PetsUser");
        while ($row_allpets = mysqli_fetch_object($allpetsdb)) {
            $all_pets[$row_allpets->RematchID]['Name'] = $row_allpets->{$petnext};
            $all_pets[$row_allpets->RematchID]['ENName'] = $row_allpets->Name;
            $all_pets[$row_allpets->RematchID]['Species'] = $row_allpets->RematchID;
            $all_pets[$row_allpets->RematchID]['PetID'] = $row_allpets->PetID;
            $all_pets[$row_allpets->RematchID]['Skill1'] = $row_allpets->Skill1;
            $all_pets[$row_allpets->RematchID]['Skill2'] = $row_allpets->Skill2;
            $all_pets[$row_allpets->RematchID]['Skill3'] = $row_allpets->Skill3;
            $all_pets[$row_allpets->RematchID]['Skill4'] = $row_allpets->Skill4;
            $all_pets[$row_allpets->RematchID]['Skill5'] = $row_allpets->Skill5;
            $all_pets[$row_allpets->RematchID]['Skill6'] = $row_allpets->Skill6;
            $all_pets[$row_allpets->RematchID]['Family'] = $row_allpets->Family;
            $all_pets[$row_allpets->RematchID]['Health'] = $row_allpets->Health;
            $all_pets[$row_allpets->RematchID]['Power'] = $row_allpets->Power;
            $all_pets[$row_allpets->RematchID]['Speed'] = $row_allpets->Speed;
            $all_pets[$row_allpets->RematchID]['BB'] = $row_allpets->BB;
            $all_pets[$row_allpets->RematchID]['PP'] = $row_allpets->PP;
            $all_pets[$row_allpets->RematchID]['SS'] = $row_allpets->SS;
            $all_pets[$row_allpets->RematchID]['HH'] = $row_allpets->HH;
            $all_pets[$row_allpets->RematchID]['HP'] = $row_allpets->HP;
            $all_pets[$row_allpets->RematchID]['PS'] = $row_allpets->PS;
            $all_pets[$row_allpets->RematchID]['HS'] = $row_allpets->HS;
            $all_pets[$row_allpets->RematchID]['PB'] = $row_allpets->PB;
            $all_pets[$row_allpets->RematchID]['SB'] = $row_allpets->SB;
            $all_pets[$row_allpets->RematchID]['HB'] = $row_allpets->HB;
            $all_pets[$row_allpets->RematchID]['Source'] = $row_allpets->Source;
            $all_pets[$row_allpets->RematchID]['Cageable'] = $row_allpets->Cageable;
            $all_pets[$row_allpets->RematchID]['Special'] = $row_allpets->Special;
            $all_pets[$row_allpets->RematchID]['Obtainable'] = $row_allpets->Obtainable;
        }
    }

    return $all_pets;
}



// =======================================================================================================================

function get_all_abilities ($external = 0) {
    global $dbcon, $language;
    $all_abilities = array();
    if ($external ==  0) {
      $all_abilities = $GLOBALS['all_abilities'];
    }
    if (!$all_abilities) {
        $allpetsdb = mysqli_query($dbcon, "SELECT * FROM Pet_Abilities ORDER BY en_US");
        while ($row_allpets = mysqli_fetch_object($allpetsdb)) {
            $all_abilities[$row_allpets->id]['id'] = $row_allpets->id;
            $all_abilities[$row_allpets->id]['Name'] = $row_allpets->{$language};
            $all_abilities[$row_allpets->id]['ENName'] = $row_allpets->en_US;
            $all_abilities[$row_allpets->id]['Family'] = $row_allpets->Family;
            $all_abilities[$row_allpets->id]['Cooldown'] = $row_allpets->Cooldown;
            $all_abilities[$row_allpets->id]['Rounds'] = $row_allpets->Rounds;
            $all_abilities[$row_allpets->id]['LastImport'] = $row_allpets->LastImport;
        }
    }
    return $all_abilities;
}


function get_all_npc_pets ($external = 0) {
    global $dbcon, $language;
    $all_npc_pets = array();
    if ($external ==  0) {
      $all_npc_pets = $GLOBALS['all_npc_pets'];
    }
    if (!$all_npc_pets) {
        $row_allpets_db = mysqli_query($dbcon, "SELECT * FROM Pets_NPC ORDER BY en_US");
        while ($row_allpets = mysqli_fetch_object($row_allpets_db)) {
            $all_npc_pets[$row_allpets->id]['id'] = $row_allpets->id;
            $all_npc_pets[$row_allpets->id]['Name'] = $row_allpets->{$language};
            $all_npc_pets[$row_allpets->id]['ENName'] = $row_allpets->en_US;
            $all_npc_pets[$row_allpets->id]['Family'] = $row_allpets->Family;
            $all_npc_pets[$row_allpets->id]['Species'] = $row_allpets->Species;
        }
    }
    return $all_npc_pets;
}

function get_collection_stats($collection, $petnext = "") {
    if (!$petnext) {
        $petnext = $GLOBALS['petnext'];
    }
    $all_pets = get_all_pets($petnext);
    // Prepare Statistic Variables:
    
    $stats = array("Humanoid" => 0, "Dragonkin" => 0, "Flying" => 0, "Undead" => 0, "Critter" => 0, "Magic" => 0, "Elemental" => 0, "Beast" => 0,
                     "Aquatic" => 0, "Mechanic" => 0, "Rare" => 0, "Uncommon" => 0, "Common" => 0, "Poor" => 0, "1" => 0, "2" => 0,
                     "3" => 0, "4" => 0, "5" => 0, "6" => 0, "7" => 0, "8" => 0, "9" => 0, "10" => 0, "11" => 0,
                     "12" => 0, "13" => 0, "14" => 0, "15" => 0, "16" => 0, "17" => 0, "18" => 0, "19" => 0, "20" => 0,
                     "21" => 0, "22" => 0, "23" => 0, "24" => 0, "25" => 0, 'Maxed' => 0, 'NotMaxed' => 0, 'Duplicates' => 0, 'Unique' => 0, 'NotNull' => 0,
                     'LevelSum' => 0, 'LevelAverage' => 0);
  
    $temp_collection = null;
    $countcolpets = "0";
    foreach($collection as $pet) {
        $temp_collection['ID'] = $countcolpets;
        $ignorepet = FALSE;

        // Mark the current pet to be ignored in counts (guild herald/page). This is because they can be in collections multiple times (via API), but are only shown once in-game
        if (($pet['Species'] == "280" && $guildpet[1] == TRUE) || ($pet['Species'] == "282" && $guildpet[2] == TRUE) || ($pet['Species'] == "281" && $guildpet[3] == TRUE) || ($pet['Species'] == "283" && $guildpet[4] == TRUE)) {
            $ignorepet = TRUE;
        }

        if ($ignorepet == FALSE) {
            // Mark hits of the guild herald / guild pages once, do not add again
            if ($pet['Species'] == "280") {
                $guildpet[1] = TRUE;
            }
            if ($pet['Species'] == "282") {
                $guildpet[2] = TRUE;
            }
            if ($pet['Species'] == "281") {
                $guildpet[3] = TRUE;
            }
            if ($pet['Species'] == "283") {
                $guildpet[4] = TRUE;
            }

            // Count Family occurrences
            if ($all_pets[$pet['Species']]['Name'] != "") {
                switch ($all_pets[$pet['Species']]['Family'] ) {
                    case "Humanoid":
                        $stats['Humanoid']++;
                        break;
                    case "Dragonkin":
                        $stats['Dragonkin']++;
                        break;
                    case "Flying":
                        $stats['Flying']++;
                        break;
                    case "Undead":
                        $stats['Undead']++;
                        break;
                    case "Critter":
                        $stats['Critter']++;
                        break;
                    case "Magic":
                        $stats['Magic']++;
                        break;
                    case "Elemental":
                        $stats['Elemental']++;
                        break;
                    case "Beast":
                        $stats['Beast']++;
                        break;
                    case "Aquatic":
                        $stats['Aquatic']++;
                        break;
                    case "Mechanical":
                        $stats['Mechanic']++;
                        break;
                }
            }

            if ($pet['Level'] == 25 && $pet['Quality'] == 3) {
                $stats['Maxed']++;
            }
            if ($pet['Level'] < 25 || $pet['Quality'] < 3) {
                $stats['NotMaxed']++;
            }

            if(array_search($pet['Species'], array_column($temp_collection, 'Species')) !== false) {
                $stats['Duplicates']++;
            }
            else {
                $stats['Unique']++;
            }
            
            switch ($pet['Quality'] ) {
                case "0":
                    $stats['Poor']++;
                    break;
                case "1":
                    $stats['Common']++;
                    break;
                case "2":
                    $stats['Uncommon']++;
                    break;
                case "3":
                    $stats['Rare']++;
                    break;
            }
            if ($pet['Level'] > 0) {
                $stats[$pet['Level']]++;
                $stats['NotNull']++;
                $stats['LevelSum'] = $stats['LevelSum']+$pet['Level'];
            }
            
            $temp_collection[$countcolpets]['Species'] = $pet['Species'];
            $countcolpets++;
        }
    }
    $stats['LevelAverage'] = $stats['LevelSum']/$stats['NotNull'];
    return $stats;
}


function print_collection ($petdata, $title = "0", $viewusername = "", $viewuser = "") {
  // $title = which header is being used.
  // 0 = standard, "Your Pet Collection" or "Pet Collection of XZY" depending on $viewusername
  // 1 = no header bar
  // $viewusername = Decides what title header is written
  
  global $dbcon, $petnext, $language, $user, $all_pets, $collection;
  $viewer_collection = $collection;

    // Prepare Statistic Variables:
    $stats = array("Humanoid" => 0, "Dragonkin" => 0, "Flying" => 0, "Undead" => 0, "Critter" => 0, "Magic" => 0, "Elemental" => 0, "Beast" => 0,
                     "Aquatic" => 0, "Mechanic" => 0, "Rare" => 0, "Uncommon" => 0, "Common" => 0, "Poor" => 0, "1" => 0, "2" => 0,
                     "3" => 0, "4" => 0, "5" => 0, "6" => 0, "7" => 0, "8" => 0, "9" => 0, "10" => 0, "11" => 0,
                     "12" => 0, "13" => 0, "14" => 0, "15" => 0, "16" => 0, "17" => 0, "18" => 0, "19" => 0, "20" => 0,
                     "21" => 0, "22" => 0, "23" => 0, "24" => 0, "25" => 0, 'Maxed' => 0, 'NotMaxed' => 0, 'Duplicates' => 0, 'Unique' => 0, 'Notnull' => 0,
                     'Levelsum' => 0, 'LevelAverage' => 0, 'TotalPetsNum' => 0);
    $collection = null;

    $countcolpets = "0";

    // echo "<pre>";
    // print_r($all_pets);
    // $petdata[2000]['Species'] = 6000;
    // $petdata[2000]['Level'] = 6000;
    // print_r($petdata);
    foreach($petdata as $pet) {

        $ignorepet = FALSE;
        // Mark the current pet to be ignored in counts (guild herald/page). This is because they can be in collections multiple times (via API), but are only shown once in-game
        if (($pet['Species'] == "280" && $guildpet[1] == TRUE) || ($pet['Species'] == "282" && $guildpet[2] == TRUE) || ($pet['Species'] == "281" && $guildpet[3] == TRUE) || ($pet['Species'] == "283" && $guildpet[4] == TRUE)) {
            $ignorepet = TRUE;
        }

        if ($ignorepet == FALSE) {

            $collection[$countcolpets]['Collected'] = TRUE;
            $collection[$countcolpets]['Level'] = $pet['Level'];
            $collection[$countcolpets]['Quality'] = $pet['Quality'];
            $collection[$countcolpets]['Breed'] = $pet['Breed'];

            // Check if pet is known in DB or not. If not, add placeholder and such for fields were no info is not available
            if ($all_pets[$pet['Species']]['Species'] == "") {
                $collection[$countcolpets]['InDB'] = FALSE;
                $collection[$countcolpets]['Name'] = "Unknown Pet";
                $collection[$countcolpets]['Family'] = "N/A";
                $collection[$countcolpets]['Cageable'] = "0";
                $collection[$countcolpets]['Species'] = $pet['Species'];

            }
            else {
                $collection[$countcolpets]['InDB'] = TRUE;
                $collection[$countcolpets]['PetID'] = $all_pets[$pet['Species']]['PetID'];
                if ($all_pets[$pet['Species']]['Special'] == 1) {
                    $pet['Level'] = 0;
                    $collection[$countcolpets]['Breed'] = "-";
                    $collection[$countcolpets]['Level'] = "0";
                }
            }


            // Mark hits of the guild herald / guild pages once, do not add again
            if ($pet['Species'] == "280") {
                $guildpet[1] = TRUE;
            }
            if ($pet['Species'] == "282") {
                $guildpet[2] = TRUE;
            }
            if ($pet['Species'] == "281") {
                $guildpet[3] = TRUE;
            }
            if ($pet['Species'] == "283") {
                $guildpet[4] = TRUE;
            }

            // Add Family Info
            if ($collection[$countcolpets]['InDB'] == TRUE) {
                switch ($all_pets[$pet['Species']]['Family'] ) {
                    case "Humanoid":
                        $collection[$countcolpets]['Family'] = __("Humanoid");
                        if ($ignorepet == FALSE) { $stats['Humanoid']++; }
                        break;
                    case "Dragonkin":
                        $collection[$countcolpets]['Family'] = __("Dragonkin");
                        if ($ignorepet == FALSE) { $stats['Dragonkin']++; }
                        break;
                    case "Flying":
                        $collection[$countcolpets]['Family'] = __("Flying");
                        if ($ignorepet == FALSE) { $stats['Flying']++; }
                        break;
                    case "Undead":
                        $collection[$countcolpets]['Family'] = __("Undead");
                        if ($ignorepet == FALSE) { $stats['Undead']++; }
                        break;
                    case "Critter":
                        $collection[$countcolpets]['Family'] = __("Critter");
                        if ($ignorepet == FALSE) { $stats['Critter']++; }
                        break;
                    case "Magic":
                        $collection[$countcolpets]['Family'] = __("Magic");
                        if ($ignorepet == FALSE) { $stats['Magic']++; }
                        break;
                    case "Elemental":
                        $collection[$countcolpets]['Family'] = __("Elemental");
                        if ($ignorepet == FALSE) { $stats['Elemental']++; }
                        break;
                    case "Beast":
                        $collection[$countcolpets]['Family'] = __("Beast");
                        if ($ignorepet == FALSE) { $stats['Beast']++; }
                        break;
                    case "Aquatic":
                        $collection[$countcolpets]['Family'] = __("Aquatic");
                        if ($ignorepet == FALSE) { $stats['Aquatic']++; }
                        break;
                    case "Mechanical":
                        $collection[$countcolpets]['Family'] = __("Mechanical");
                        if ($ignorepet == FALSE) { $stats['Mechanic']++; }
                        break;
                }
            }

            if ($pet['Level'] == 25 && $pet['Quality'] == 3) {
                $collection[$countcolpets]['Maxed'] = TRUE;
                $stats['Maxed']++;
            }
            if ($pet['Level'] < 25 || $pet['Quality'] < 3) {
                $collection[$countcolpets]['Maxed'] = FALSE;
                $stats['NotMaxed']++;
            }

            $dupecount = 0;
            foreach ($petdata as $item) {
              if ($item['Species'] === $pet['Species'] && $item['Species'] != 280 && $item['Species'] != 281 && $item['Species'] != 282 && $item['Species'] != 283) { // excluding guild pages and heralds from dupe search, see beginning of function for explanation
                $dupecount++;
              }
            }
            switch ($dupecount) {
                case "1":
                    $collection[$countcolpets]['Duplicate'] = FALSE;
                    $collection[$countcolpets]['Dupecount'] = 0;
                    break;
                case "2":
                    $collection[$countcolpets]['Duplicate'] = TRUE;
                    $collection[$countcolpets]['Dupecount'] = 2;
                    break;
                case "3":
                    $collection[$countcolpets]['Duplicate'] = TRUE;
                    $collection[$countcolpets]['Dupecount'] = 3;
                    break;
            }

            if(array_search($pet['Species'], array_column($collection, 'Species')) !== false) {
                $stats['Duplicates']++;
            }
            else {
                $stats['Unique']++;
            }
            // Adding species to collection array after this to make sure dupe check is working
            $collection[$countcolpets]['Species'] = $pet['Species'];

            switch ($pet['Quality'] ) {
                case "0":
                    $stats['Poor']++;
                    break;
                case "1":
                    $stats['Common']++;
                    break;
                case "2":
                    $stats['Uncommon']++;
                    break;
                case "3":
                    $stats['Rare']++;
                    break;
            }
            if ($pet['Level'] > 0) {
                $stats[$pet['Level']]++;
                $stats['NotNull']++;
                $stats['LevelSum'] = $stats['LevelSum']+$pet['Level'];
            }

            if ($collection[$countcolpets]['InDB'] == TRUE) {
                $collection[$countcolpets]['Name'] = $all_pets[$pet['Species']]['Name'];
                $collection[$countcolpets]['Cageable'] = $all_pets[$pet['Species']]['Cageable'];
            }
            $countcolpets++;
        }
    }

    $stats['LevelAverage'] = $stats['LevelSum']/$stats['NotNull'];

    // Add missing pets from database
    foreach ($all_pets as $pet) {
        if ($pet['PetID'] > 20) {
            if(array_search($pet['PetID'], array_column($collection, 'PetID')) !== false ) {}
            else {
                $collection[$countcolpets]['InDB'] = TRUE;
                $collection[$countcolpets]['Family'] = $pet['Family'];
                $collection[$countcolpets]['Species'] = $pet['Species'];
                $collection[$countcolpets]['Maxed'] = "-";
                $collection[$countcolpets]['Duplicate'] = FALSE;
                $collection[$countcolpets]['PetID'] = $pet['PetID'];
                $collection[$countcolpets]['Name'] = $pet['Name'];
                $collection[$countcolpets]['Level'] = "-";
                $collection[$countcolpets]['Quality'] = "22";
                $collection[$countcolpets]['Breed'] = "-";
                $collection[$countcolpets]['Cageable'] = $pet['Cageable'];
                $collection[$countcolpets]['Collected'] = FALSE;
                $countcolpets++;
            }
            $stats['TotalPetsNum']++;
        }
    }
sortBy('Name', $collection, 'asc');
?>



<script type="text/javascript">

function filter_humanoid() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Humanoid") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Humanoid") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_dragonkin() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Dragonkin") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Dragonkin") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_flying() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Flying") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Flying") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_undead() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Undead") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Undead") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_critter() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Critter") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Critter") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_magic() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Magic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Magic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_elemental() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Elemental") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Elemental") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_beast() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Beast") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Beast") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_aquatic() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Aquatic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Aquatic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_mechanic() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Mechanical") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Mechanical") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}


function filter_rare() {
    if (document.getElementById('qualityfilter').value == '<?php echo __("Rare") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<?php echo __("Rare") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}
function filter_uncommon() {
    if (document.getElementById('qualityfilter').value == '<?php echo __("Uncommon") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<?php echo __("Uncommon") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}
function filter_common() {
    if (document.getElementById('qualityfilter').value == '<?php echo __("Common") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<?php echo __("Common") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}
function filter_poor() {
    if (document.getElementById('qualityfilter').value == '<?php echo __("Poor") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<?php echo __("Poor") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}


function filter_unique() {
    if (document.getElementById('uniquefilter').value == '<?php echo __("No") ?>') {
        document.getElementById('uniquefilter').value = '';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    } else {
        document.getElementById('uniquefilter').value = '<?php echo __("No") ?>';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    }
}
function filter_dupe() {
    if (document.getElementById('uniquefilter').value == '<?php echo __("Yes") ?>') {
        document.getElementById('uniquefilter').value = '';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    } else {
        document.getElementById('uniquefilter').value = '<?php echo __("Yes") ?>';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    }
}



function filter_collected() {
    if (document.getElementById('collectedfilter').value == '<?php echo __("Yes") ?>') {
        document.getElementById('collectedfilter').value = '';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    } else {
        document.getElementById('collectedfilter').value = '<?php echo __("Yes") ?>';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    }
}
function filter_missing() {
    if (document.getElementById('collectedfilter').value == '<?php echo __("No") ?>') {
        document.getElementById('collectedfilter').value = '';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    } else {
        document.getElementById('collectedfilter').value = '<?php echo __("No") ?>';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    }
}


function filter_reset() {
    document.getElementById('uniquefilter').value = '';
    Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    document.getElementById('qualityfilter').value = '';
    Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    document.getElementById('familiesfilter').value = '';
    Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    document.getElementById('breedfilter').value = '';
    Table.filter(document.getElementById('breedfilter'),document.getElementById('breedfilter'));
    document.getElementById('levelfilter').value = '';
    Table.filter(document.getElementById('levelfilter'),document.getElementById('levelfilter'));
    document.getElementById('namefilter').value = '';
    Table.filter(document.getElementById('namefilter'),document.getElementById('namefilter'));
    document.getElementById('collectedfilter').value = '<?php echo __("Yes") ?>';
    Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    document.getElementById('tradeablefilter').value = '';
    Table.filter(document.getElementById('tradeablefilter'),document.getElementById('tradeablefilter'));
}


window.onload = function () {
    $('.count').each(function () {
        $(this).show();
        $(this).prop('Counter',0).animate({
            Counter: $(this).text()
        }, {
            duration: 800,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });
    });

    var chartAllpets = new CanvasJS.Chart("chartAllpets",
        {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 170,
            height: 76,
            title:{
                text: ""
            },
            toolTip:{
                enabled: false,
            },
            dataPointMaxWidth: 20,
            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            data: [
                {
                    type: "bar",
                    dataPoints: [
                    { x: 2, y: <?php echo $stats['Unique'] ?>, color: "#0081f2", click: filter_unique },
                    { x: 1, y: <?php echo $stats['Duplicates'] ?>, color: "#1aa10c", click: filter_dupe },
                    ]
                }
            ]
        });

    chartAllpets.render();

    var chartUniquepets = new CanvasJS.Chart("chartUniquepets",
        {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 170,
            height: 76,
            title:{
                text: ""
            },
            toolTip:{
                enabled: false,
            },
            dataPointMaxWidth: 20,
            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            data: [
                {
                    type: "bar",
                    dataPoints: [
                    { x: 2, y: <?php echo $stats['Maxed'] ?>, color: "#0081f2" },
                    { x: 1, y: <?php echo $stats['NotMaxed'] ?>, color: "#1aa10c" },
                    ]
                }
            ]
        });

    chartUniquepets.render();

var chartFamilies = new CanvasJS.Chart("chartFamilies",
    {
    title:{
        text: "<?php echo __("Families") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black"
    },
    interactivityEnabled: true,
    animationEnabled: true,
    animationDuration: 800,
    backgroundColor: null,
    width: 210,
    height: 210,
    data: [
        {
            type: "doughnut",
            startAngle:270,
            innerRadius: "60%",
            showInLegend: false,
            toolTipContent: "{legendText}",
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            indexLabelMaxWidth: 50,
            indexLabelWrap: true,
            dataPoints: [
                {  y: <?php echo $stats['Humanoid'] ?>, legendText: "<?php echo __("Humanoid") ?>: <?php echo $stats['Humanoid'] ?>", color: "#08adff", click: filter_humanoid },
                {  y: <?php echo $stats['Dragonkin'] ?>, legendText: "<?php echo __("Dragonkin") ?>: <?php echo $stats['Dragonkin'] ?>", color: "#59bc11", click: filter_dragonkin },
                {  y: <?php echo $stats['Flying'] ?>, legendText: "<?php echo __("Flying") ?>: <?php echo $stats['Flying'] ?>", color: "#d4ca4f", click: filter_flying },
                {  y: <?php echo $stats['Undead'] ?>, legendText: "<?php echo __("Undead") ?>: <?php echo $stats['Undead'] ?>", color: "#9f6c73", click: filter_undead },
                {  y: <?php echo $stats['Critter'] ?>, legendText: "<?php echo __("Critter") ?>: <?php echo $stats['Critter'] ?>", color: "#7c5943", click: filter_critter },
                {  y: <?php echo $stats['Magic'] ?>, legendText: "<?php echo __("Magic") ?>: <?php echo $stats['Magic'] ?>", color: "#7341ee", click: filter_magic },
                {  y: <?php echo $stats['Elemental'] ?>, legendText: "<?php echo __("Elemental") ?>: <?php echo $stats['Elemental'] ?>", color: "#eb7012", click: filter_elemental },
                {  y: <?php echo $stats['Beast'] ?>, legendText: "<?php echo __("Beast") ?>: <?php echo $stats['Beast'] ?>", color: "#ec2b22", click: filter_beast },
                {  y: <?php echo $stats['Aquatic'] ?>, legendText: "<?php echo __("Aquatic") ?>: <?php echo $stats['Aquatic'] ?>", color: "#08aab7", click: filter_aquatic },
                {  y: <?php echo $stats['Mechanic'] ?>, legendText: "<?php echo __("Mechanical") ?>: <?php echo $stats['Mechanic'] ?>", color: "#7e776d", click: filter_mechanic }
           ]
        }
        ]
    });

    chartFamilies.render();

var chartQuality = new CanvasJS.Chart("chartQuality",
    {
    title:{
        text: "<?php echo __("Quality") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black"
    },
    interactivityEnabled: true,
    animationEnabled: true,
    animationDuration: 800,
    backgroundColor: null,
    width: 210,
    height: 210,
    data: [
        {
            type: "doughnut",
            startAngle:270,
            innerRadius: "60%",
            showInLegend: false,
            toolTipContent: "{legendText}",
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            indexLabelMaxWidth: 50,
            indexLabelWrap: true,
            dataPoints: [
                {  y: <?php echo $stats['Rare'] ?>, legendText: "<?php echo __("Rare") ?>: <?php echo $stats['Rare'] ?>", color: "#0081f2", click: filter_rare },
                {  y: <?php echo $stats['Uncommon'] ?>, legendText: "<?php echo __("Uncommon") ?>: <?php echo $stats['Uncommon'] ?>", color: "#1aa10c", click: filter_uncommon },
                {  y: <?php echo $stats['Common'] ?>, legendText: "<?php echo __("Common") ?>: <?php echo $stats['Common'] ?>", color: "#efefef", click: filter_common },
                {  y: <?php echo $stats['Poor'] ?>, legendText: "<?php echo __("Poor") ?>: <?php echo $stats['Poor'] ?>", color: "#898989", click: filter_poor },
           ]
        }
        ]
    });

    chartQuality.render();


var chartCollected = new CanvasJS.Chart("chartCollected",
    {
    title:{
        text: "<?php echo __("Collected of all") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black"
    },
    interactivityEnabled: true,
    animationEnabled: true,
    animationDuration: 800,
    backgroundColor: null,
    width: 210,
    height: 210,
    data: [
        {
            type: "doughnut",
            startAngle:270,
            innerRadius: "60%",
            showInLegend: false,
            toolTipContent: "{legendText}",
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            indexLabelMaxWidth: 50,
            indexLabelWrap: true,
            dataPoints: [
                {  y: <?php echo $stats['Unique']; ?>, legendText: "<?php echo __("Collected") ?>: <?php echo $stats['Unique']; ?>", color: "#1aa10c", click: filter_collected },
                {  y: <?php echo $stats['TotalPetsNum']-$stats['Unique']; ?>, legendText: "<?php echo __("Missing") ?>: <?php echo $stats['TotalPetsNum']-$stats['Unique'] ?>", color: "#898989", click: filter_missing }
           ]
        }
        ]
    });

    chartCollected.render();

    var chartLevels = new CanvasJS.Chart("chartLevels",
    {
    title:{
        text: "<?php echo __("Level Distribution") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black",
        fontSize : "16"
    },
        interactivityEnabled: true,
        animationEnabled: true,
        animationDuration: 800,
        backgroundColor: null,
        width: 880,
        height: 250,

            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 5,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null",
                interval: 1
            },

        data: [
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "Level {label}: {y}",
            indexLabelMaxWidth: 50,
            dataPoints: [
            { x: 1, y: <?php echo $stats[1] ?>, label: "1", color: "#97d4ff"},
            { x: 2, y: <?php echo $stats[2] ?>,  label: "2", color: "#94d2ff"},
            { x: 3, y: <?php echo $stats[3] ?>,  label: "3", color: "#8fd0fe"},
            { x: 4, y: <?php echo $stats[4] ?>,  label: "4", color: "#8acdfe"},
            { x: 5, y: <?php echo $stats[5] ?>,  label: "5", color: "#85cafd"},
            { x: 6, y: <?php echo $stats[6] ?>, label: "6", color: "#7fc7fd"},
            { x: 7, y: <?php echo $stats[7] ?>,  label: "7", color: "#78c3fc"},
            { x: 8, y: <?php echo $stats[8] ?>, label: "8", color: "#72bffc"},
            { x: 9, y: <?php echo $stats[9] ?>,  label: "9", color: "#6bbcfb"},
            { x: 10, y: <?php echo $stats[10] ?>,  label: "10", color: "#63b7fa"},
            { x: 11, y: <?php echo $stats[11] ?>,  label: "11", color: "#5cb3fa"},
            { x: 12, y: <?php echo $stats[12] ?>,  label: "12", color: "#54aff9"},
            { x: 13, y: <?php echo $stats[13] ?>, label: "13", color: "#4cabf8"},
            { x: 14, y: <?php echo $stats[14] ?>,  label: "14", color: "#45a7f8"},
            { x: 15, y: <?php echo $stats[15] ?>, label: "15", color: "#3da3f7"},
            { x: 16, y: <?php echo $stats[16] ?>,  label: "16", color: "#359ef7"},
            { x: 17, y: <?php echo $stats[17] ?>,  label: "17", color: "#2e9af6"},
            { x: 18, y: <?php echo $stats[18] ?>,  label: "18", color: "#2796f5"},
            { x: 19, y: <?php echo $stats[19] ?>,  label: "19", color: "#2193f5"},
            { x: 20, y: <?php echo $stats[20] ?>, label: "20", color: "#1a8ff4"},
            { x: 21, y: <?php echo $stats[21] ?>,  label: "21", color: "#148cf4"},
            { x: 22, y: <?php echo $stats[22] ?>,  label: "22", color: "#0f89f3"},
            { x: 23, y: <?php echo $stats[23] ?>, label: "23", color: "#0986f3"},
            { x: 24, y: <?php echo $stats[24] ?>,  label: "24", color: "#0584f2"},
            { x: 25, y: <?php echo $stats[25] ?>,  label: "25", color: "#0182f2"}
            ]
        }
        ]
    });

    chartLevels.render();

    var chartFamiliesC = new CanvasJS.Chart("chartFamiliesC",
    {
    title:{
        text: "<?php echo __("Families") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black",
        fontSize : "16"
    },
        interactivityEnabled: true,
        animationEnabled: true,
        animationDuration: 800,
        backgroundColor: null,
        width: 880,
        height: 250,

            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 5,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null",
                labelFontSize: 14,
                labelFontFamily: "MuseoSans-300",
                labelFontColor : "black",
                interval: 1
            },

        data: [
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y}",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <?php echo $stats['Humanoid'] ?>, label: "<?php echo __("Humanoid") ?>", color: "#08adff", click: filter_humanoid },
                {  x: 2, y: <?php echo $stats['Dragonkin'] ?>, label: "<?php echo __("Dragonkin") ?>", color: "#59bc11", click: filter_dragonkin },
                {  x: 3, y: <?php echo $stats['Flying'] ?>, label: "<?php echo __("Flying") ?>", color: "#d4ca4f ", click: filter_flying },
                {  x: 4, y: <?php echo $stats['Undead'] ?>, label: "<?php echo __("Undead") ?>", color: "#9f6c73", click: filter_undead },
                {  x: 5, y: <?php echo $stats['Critter'] ?>, label: "<?php echo __("Critter") ?>", color: "#7c5943", click: filter_critter },
                {  x: 6, y: <?php echo $stats['Magic'] ?>, label: "<?php echo __("Magic") ?>", color: "#7341ee", click: filter_magic },
                {  x: 7, y: <?php echo $stats['Elemental'] ?>, label: "<?php echo __("Elemental") ?>", color: "#eb7012", click: filter_elemental },
                {  x: 8, y: <?php echo $stats['Beast'] ?>, label: "<?php echo __("Beast") ?>", color: "#ec2b22", click: filter_beast },
                {  x: 9, y: <?php echo $stats['Aquatic'] ?>, label: "<?php echo __("Aquatic") ?>", color: "#08aab7", click: filter_aquatic },
                {  x: 10, y: <?php echo $stats['Mechanic'] ?>, label: "<?php echo __("Mechanical") ?>", color: "#7e776d", click: filter_mechanic }
            ]
        }
        ]
    });

    chartFamiliesC.render();

    var chartQualityC = new CanvasJS.Chart("chartQualityC",
    {
    title:{
        text: "<?php echo __("Quality") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black",
        fontSize : "16"
    },
        interactivityEnabled: true,
        animationEnabled: true,
        animationDuration: 800,
        backgroundColor: null,
        width: 880,
        height: 250,

            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 5,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null",
                labelFontSize: 14,
                labelFontFamily: "MuseoSans-300",
                labelFontColor : "black",
                interval: 1
            },

        data: [
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y}",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <?php echo $stats['Poor'] ?>, label: "<?php echo __("Poor") ?>", color: "#898989", click: filter_poor},
                {  x: 2, y: <?php echo $stats['Common'] ?>, label: "<?php echo __("Common") ?>", color: "#efefef", click: filter_common},
                {  x: 3, y: <?php echo $stats['Uncommon'] ?>, label: "<?php echo __("Uncommon") ?>", color: "#1aa10c", click: filter_uncommon},
                {  x: 4, y: <?php echo $stats['Rare'] ?>, label: "<?php echo __("Rare") ?>", color: "#0081f2", click: filter_rare}
            ]
        }
        ]
    });

    chartQualityC.render();

    Table.filter(this.document.getElementById('collectedfilter'),this.document.getElementById('collectedfilter'));
    document.getElementById('loading').style.display='none';
    document.getElementById('collection').style.display='block';
}
</script>


<table class="profile">

<?php if ($title == "0") { ?>
    <tr class="profile">
        <th colspan="3" width="5" class="profile">
            <table>
                <tr>
                    <td><img src="images/headericon_collection.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><span style="white-space: nowrap;"><b><?php if ($viewusername) { echo __("Pet Collection of")." ".$viewusername; } else { echo __("Your Pet Collection"); } ?></span></td>
                    <?php if ($viewusername && $viewuser->id != $user->id && $viewer_collection) { ?>
                      <td style="width: 100%; text-align: right">
                        <a href="?m=Compare&user_1=<?php echo $user->id ?>&user_2=<?php echo $viewuser->id ?>">
                          <button style="white-space:nowrap; height: 30px; padding: 4px 8px 4px 8px; margin-top: 0px;" type="submit" tabindex="4" class="bnetlogin"><?php echo __('Compare against your collection'); ?></button>
                        </a>
                      </td>
                    <?php } ?>
                </tr>
            </table>
        </th>
    </tr>
<?php } ?>
    <tr class="profile">
        <td class="collectionborder" width="50%" valign="top">
            <table>
                <tr valign="top">
                    <td class="collection"><table><tr><td><img src="images/blank.png" width="165" height="22"></td></tr><tr><td align="right"><p class="collectionhuge"><span class="count"><?php echo $stats['Unique']; ?></p></span></td></tr></table></td>
                    <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="line-height: 190%; text-align: right;"><?php echo __("Unique") ?>: <span class="count"><?php echo $stats['Unique']; ?></span><br><?php echo __("Duplicates") ?>: <span class="count"><?php echo $stats['Duplicates'] ?></span></div></td>
                    <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 180px;" id="chartAllpets"></div></td>
                </tr>
                <tr>
                    <td align="right" style="padding-right: 15px;"><p class="blogodd"><?php echo __("Unique pets") ?></p></td>
                </tr>
            </table>
        </td>

        <td class="collectionborder" width="50%" valign="top">
            <table>
                <tr valign="top">
                    <td class="collection"><table><tr><td><img src="images/blank.png" width="165" height="22"></td></tr><tr><td align="right"><p class="collectionhuge"><span class="count"><?php echo $stats['Unique']+$stats['Duplicates']; ?></p></span></td></tr></table></td>
                    <td class="collection" align="right"><div style="white-space:nowrap" class="ttmaximized"><p class="blogodd" style="line-height: 190%; text-align: right;"><?php echo __("Maximized:") ?> <span class="count"><?php echo $stats['Maxed']; ?></span><br></div><div style="white-space:nowrap" class="ttnotmaxed"><p class="blogodd" style="line-height: 190%; text-align: right;"><?php echo __("Not Maxed:") ?> <span class="count"><?php echo $stats['NotMaxed']; ?></span></div></td>
                    <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 180px;" id="chartUniquepets"></div></td>
                </tr>
                <script>
                    $('.ttmaximized').tooltipster({
                        content: 'Maximized = level 25 and rare quality',
                        theme: 'tooltipster-smallnote',
                        updateAnimation: 'null',
                        animationDuration: 350,
                    });
                    $('.ttnotmaxed').tooltipster({
                        content: 'Either not rare quality or below level 25',
                        theme: 'tooltipster-smallnote',
                        updateAnimation: 'null',
                        animationDuration: 350,
                    });
                </script>
                <tr>
                    <td align="right" style="padding-right: 15px;"><p class="blogodd"><?php echo __("Pets overall") ?></p></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr class="profile">
        <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="33%" class="collectionborder">
                        <center>
                        <div style="height: 210px; width: 210px;">
                            <div id="chartFamilies" style="height: 100%; width: 100%;"></div>
                        </div>

                    </td>
                    <td width="33%" class="collectionborder">
                        <center>
                        <div style="height: 210px; width: 210px;">
                            <div id="chartQuality" style="height: 100%; width: 100%;"></div>
                        </div>
                    </td>
                    <td width="33%">
                        <center>
                        <div style="height: 210px; width: 210px;">
                            <div id="chartCollected" style="height: 100%; width: 100%;"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>


    <tr class="profile">
        <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="100" valign="top">
                        <button tabindex="1" type="submit" onclick="LevelStats()" id="ButtonLevels" class="statistics statisticsactive"><?php echo __("Level Distribution") ?></button>
                        <button tabindex="2" type="submit" onclick="FamilyStats()" id="ButtonFamily" class="statistics"><?php echo __("Families") ?></button>
                        <button tabindex="3" type="submit" onclick="QualityStats()" id="ButtonQuality" class="statistics"><?php echo __("Quality") ?></button>
                    </td>

                    <td width="900" style="padding-top: 10px;">
                        <center><div id="chartLevels" class="chartLevels" style="height: 260px; width: 900px;"></div>
                        <center><div id="chartFamiliesC" class="chartFamiliesC" style="height: 260px; width: 900px; display:none"></div>
                        <center><div id="chartQualityC" class="chartQualityC" style="height: 260px; width: 900px; display:none"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr class="profile">
        <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td>
                        <table width="100%" id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:30 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                        <thead>

                            <tr>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="300"><p class="table-sortable-black" style="margin-left: 15px;"><?php echo __("Name") ?></p></th>
                                <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><?php echo __("Level") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Quality") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Breed") ?></th>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><?php echo __("Families") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Duplicates") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Tradable") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Collected") ?></th>
                            </tr>

                            <tr>
                                <th align="left" class="petlistheadersecond" width="300">
                                    <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:100px;" id="levelfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="25">25</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<25;}">< 25</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<20;}">< 20</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<15;}">< 15</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<10;}">< 10</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<5;}">< 5</option>
                                        <option class="petselect" value="1">1</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>0;}">1-25</option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:100px;" id="qualityfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Rare") ?>"><?php echo __("Rare") ?></option>
                                        <option class="petselect" value="<?php echo __("Uncommon") ?>"><?php echo __("Uncommon") ?></option>
                                        <option class="petselect" value="<?php echo __("Common") ?>"><?php echo __("Common") ?></option>
                                        <option class="petselect" value="<?php echo __("Poor") ?>"><?php echo __("Poor") ?></option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:70px;" id="breedfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="PP">PP</option>
                                        <option class="petselect" value="SS">SS</option>
                                        <option class="petselect" value="HH">HH</option>
                                        <option class="petselect" value="PB">PB</option>
                                        <option class="petselect" value="SB">SB</option>
                                        <option class="petselect" value="HB">HB</option>
                                        <option class="petselect" value="PS">PS</option>
                                        <option class="petselect" value="HS">HS</option>
                                        <option class="petselect" value="HP">HP</option>
                                        <option class="petselect" value="BB">BB</option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:150px;" id="familiesfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Humanoid") ?>"><?php echo __("Humanoid") ?></option>
                                        <option class="petselect" value="<?php echo __("Dragonkin") ?>"><?php echo __("Dragonkin") ?></option>
                                        <option class="petselect" value="<?php echo __("Flying") ?>"><?php echo __("Flying") ?></option>
                                        <option class="petselect" value="<?php echo __("Undead") ?>"><?php echo __("Undead") ?></option>
                                        <option class="petselect" value="<?php echo __("Critter") ?>"><?php echo __("Critter") ?></option>
                                        <option class="petselect" value="<?php echo __("Magic") ?>"><?php echo __("Magic") ?></option>
                                        <option class="petselect" value="<?php echo __("Elemental") ?>"><?php echo __("Elemental") ?></option>
                                        <option class="petselect" value="<?php echo __("Beast") ?>"><?php echo __("Beast") ?></option>
                                        <option class="petselect" value="<?php echo __("Aquatic") ?>"><?php echo __("Aquatic") ?></option>
                                        <option class="petselect" value="<?php echo __("Mechanical") ?>"><?php echo __("Mechanical") ?></option>
                                    </select>
                                </th>
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="uniquefilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>0;}"><?php echo __("Yes"); ?></option>
                                        <option class="petselect" value="<?php echo __("No"); ?>"><?php echo __("No"); ?></option>
                                        <option class="petselect" value=2>2</option>
                                        <option class="petselect" value="3">3</option>
                                    </select>
                                </th>
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="tradeablefilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Yes"); ?>"><?php echo __("Yes"); ?></option>
                                        <option class="petselect" value="<?php echo __("No"); ?>"><?php echo __("No"); ?></option>
                                        <option class="petselect" value="N/A">N/A</option>
                                    </select>
                                </th>
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="collectedfilter" onchange="Table.filter(this,this);">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option selected class="petselect" value="<?php echo __("Yes"); ?>"><?php echo __("Yes"); ?></option>
                                        <option class="petselect" value="<?php echo __("No"); ?>"><?php echo __("No"); ?></option>
                                    </select>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                        <?
                        foreach($collection as $pet) { ?>
                            <tr class="petlist" <?
                            if ($pet['Collected'] == FALSE) echo 'style="background-color: #d8a0a0" ';
                            if ($pet['Duplicate'] == TRUE && $pet['InDB'] == TRUE) echo 'style="background-color: #ccca92" ';
                            if ($pet['InDB'] == FALSE) echo 'style="background-color: #ff6363" ';
                            ?>>
                                <td class="petlist" align="left" style="padding-left: 12px;"><div style="white-space:nowrap"><a class="petlist" href="http://<?php echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<?php echo $pet['PetID'] ?>" target="_blank"><?php echo $pet['Name'] ?></a></div></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Level'] == 0) echo "-";
                                    else echo $pet['Level']; ?></td>
                                <td align="center" class="petlist"><p><?
                                    if ($pet['Quality'] == 3) { echo '<font color="#0058a5">'.__("Rare"); }
                                    if ($pet['Quality'] == 2) { echo '<font color="#147e09">'.__("Uncommon"); }
                                    if ($pet['Quality'] == 1) { echo '<font color="#ffffff">'.__("Common"); }
                                    if ($pet['Quality'] == 0) { echo '<font color="#4d4d4d">'.__("Poor"); }
                                    if ($pet['Quality'] == 22) { echo '<font color="#000000">-'; } ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?php echo $pet['Breed'] ?></td>
                                <td align="left" class="petlist" style="padding-left: 12px;"><p class="blogodd"><?php echo $pet['Family'] ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Duplicate'] == TRUE) echo $pet['Dupecount'];
                                    else echo __("No"); ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Cageable'] == "1") echo __("Yes");
                                    else if ($pet['Cageable'] == "2") echo __("No");
                                    else if ($pet['Cageable'] == "0") echo "N/A"; ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Collected'] == TRUE) echo __("Yes");
                                    else echo __("No"); ?></td>
                            </tr>
                        <?php } ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                <td colspan="2" align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                                <td colspan="2" align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><?php echo __("Reset Filters") ?></a></div></td>
                            </tr>
                        </tfoot>
                      </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

</table>

<table>
    <tr>
        <td style="height: 8px;"></td>
    </tr>
    <tr>
        <td style="width: 15px;"></td>
        <td style="width: 18px; background-color: #d8a0a0"> </td>
        <td><p class="blogodd"><?php echo __("Missing") ?></td>
        <td style="width: 15px;"></td>
        <td style="width: 18px; background-color: #ccca92"> </td>
        <td><p class="blogodd"><?php echo __("Duplicates") ?></td>
    </tr>
</table>


<?
}






function print_collection_comparison ($petdata_1, $petdata_2,$chardata_avatar_1,$chardata_avatar_2) {
    $dbcon = $GLOBALS['dbcon'];
    $petnext = $GLOBALS['petnext'];
    $language = $GLOBALS['language'];
    $user = $GLOBALS['user'];
    $all_pets = $GLOBALS['all_pets'];

    $stats_1 = get_collection_stats($petdata_1);
    $stats_2 = get_collection_stats($petdata_2);
    $stats_1['Color'] = "#6798dc";
    $stats_2['Color'] = "#61a14e";
    
    // Step 1: Create array of all pets each 3 times to allow for duplicates
    sortBy('Name', $all_pets, 'asc');
    $comparison = array();
    foreach ($all_pets as $pet) {
      if ($pet['Species'] > 20) {
        $i = 0;
        while ($i < 3) {
          $comparison[$pet['Species']][$i]['Position'] = $i;
          $comparison[$pet['Species']][$i]['Family'] = $pet['Family'];
          $comparison[$pet['Species']][$i]['Species'] = $pet['Species'];
          $comparison[$pet['Species']][$i]['PetID'] = $pet['PetID'];
          $comparison[$pet['Species']][$i]['Name'] = $pet['Name'];
          $comparison[$pet['Species']][$i]['Cageable'] = $pet['Cageable'];
          $comparison[$pet['Species']][$i]['Owned_1'] = false;
          $comparison[$pet['Species']][$i]['Owned_2'] = false;
          $i++;
        }
      }
    }

    // Step 2: Add collection info of first player:
    sortBy('Level', $petdata_1, 'desc');
    $guildpet[1] = false;
    $guildpet[2] = false;
    $guildpet[3] = false;
    $guildpet[4] = false;

    foreach($petdata_1 as $pet) {
      // Mark the current pet to be ignored in counts (guild herald/page). This is because they can be in collections multiple times (via API), but are only shown once in-game
      $ignorepet = false;
      if (($pet['Species'] == "280" && $guildpet[1] == TRUE) || ($pet['Species'] == "282" && $guildpet[2] == TRUE) || ($pet['Species'] == "281" && $guildpet[3] == TRUE) || ($pet['Species'] == "283" && $guildpet[4] == TRUE)) {
        $ignorepet = TRUE;
      }
      
      if ($ignorepet == FALSE) {
        // Mark hits of the guild herald / guild pages once, do not add again
        if ($pet['Species'] == "280") {
            $guildpet[1] = TRUE;
        }
        if ($pet['Species'] == "282") {
            $guildpet[2] = TRUE;
        }
        if ($pet['Species'] == "281") {
            $guildpet[3] = TRUE;
        }
        if ($pet['Species'] == "283") {
            $guildpet[4] = TRUE;
        }
        // Check if pet is known in DB or not. If not, add placeholder and such for fields were no info is not available
        if ($all_pets[$pet['Species']]['Species'] == "") {
          $comparison[$pet['Species']][0]['Unknown_pet'] = true;
          $comparison[$pet['Species']][0]['Name'] = "Unknown Pet";
          $comparison[$pet['Species']][0]['Family'] = "N/A";
          $comparison[$pet['Species']][0]['Cageable'] = "0";
          $comparison[$pet['Species']][0]['Species'] = $pet['Species'];
        }
        else {
          if ($comparison[$pet['Species']][0]['Owned_1'] == false) {
            $addto = 0;
          }
          else if ($comparison[$pet['Species']][1]['Owned_1'] == false) {
            $addto = 1;
          }
          else {
            $addto = 2;
          }
          $comparison[$pet['Species']][$addto]['Owned_1'] = true;
          $comparison[$pet['Species']][$addto]['Level_1'] = $pet['Level'];
          $comparison[$pet['Species']][$addto]['Quality_1'] = $pet['Quality'];
          $comparison[$pet['Species']][$addto]['Breed_1'] = $pet['Breed'];
          $comparison[$pet['Species']][$addto]['Copies_1'] = array_count_values(array_column($petdata_1, 'Species'))[$pet['Species']];
          // Set guild herald etc. to 1 by default
          if ($pet['Species'] == "280" || $pet['Species'] == "282" || $pet['Species'] == "281" || $pet['Species'] == "283") {
            $comparison[$pet['Species']][$addto]['Copies_1'] = 1;
          }
        }
      }
    }
    
    
    
    // Step 3: Add collection info of second player:
    sortBy('Level', $petdata_2, 'desc');
    $guildpet[1] = false;
    $guildpet[2] = false;
    $guildpet[3] = false;
    $guildpet[4] = false;
    
    foreach($petdata_2 as $pet) {
      // Mark the current pet to be ignored in counts (guild herald/page). This is because they can be in collections multiple times (via API), but are only shown once in-game
      $ignorepet = false;
      if (($pet['Species'] == "280" && $guildpet[1] == TRUE) || ($pet['Species'] == "282" && $guildpet[2] == TRUE) || ($pet['Species'] == "281" && $guildpet[3] == TRUE) || ($pet['Species'] == "283" && $guildpet[4] == TRUE)) {
        $ignorepet = TRUE;
      }
      
      if ($ignorepet == FALSE) {
        // Mark hits of the guild herald / guild pages once, do not add again
        if ($pet['Species'] == "280") {
            $guildpet[1] = TRUE;
        }
        if ($pet['Species'] == "282") {
            $guildpet[2] = TRUE;
        }
        if ($pet['Species'] == "281") {
            $guildpet[3] = TRUE;
        }
        if ($pet['Species'] == "283") {
            $guildpet[4] = TRUE;
        }
        // Check if pet is known in DB or not. If not, add placeholder and such for fields were no info is not available
        if ($all_pets[$pet['Species']]['Species'] == "") {
          $comparison[$pet['Species']][0]['Unknown_pet'] = true;
          $comparison[$pet['Species']][0]['Name'] = "Unknown Pet";
          $comparison[$pet['Species']][0]['Family'] = "N/A";
          $comparison[$pet['Species']][0]['Cageable'] = "0";
          $comparison[$pet['Species']][0]['Species'] = $pet['Species'];
        }
        else {
          if ($comparison[$pet['Species']][0]['Owned_2'] == false) {
            $addto = 0;
          }
          else if ($comparison[$pet['Species']][1]['Owned_2'] == false) {
            $addto = 1;
          }
          else {
            $addto = 2;
          }
          $comparison[$pet['Species']][$addto]['Owned_2'] = true;
          $comparison[$pet['Species']][$addto]['Level_2'] = $pet['Level'];
          $comparison[$pet['Species']][$addto]['Quality_2'] = $pet['Quality'];
          $comparison[$pet['Species']][$addto]['Breed_2'] = $pet['Breed'];
          $comparison[$pet['Species']][$addto]['Copies_2'] = array_count_values(array_column($petdata_2, 'Species'))[$pet['Species']];
          // Set guild herald etc. to 1 by default
          if ($pet['Species'] == "280" || $pet['Species'] == "282" || $pet['Species'] == "281" || $pet['Species'] == "283") {
            $comparison[$pet['Species']][$addto]['Copies_2'] = 1;
          }
        }
      }
    }
    
    // Step 4: Remove pets that no one owns
    foreach($comparison as $pet) {
      if ($pet[2]['Owned_1'] == false && $pet[2]['Owned_2'] == false) {
       unset($comparison[$pet[0]['Species']][2]);
      } 
      if ($pet[1]['Owned_1'] == false && $pet[1]['Owned_2'] == false) {
       unset($comparison[$pet[0]['Species']][1]);
      }
      if ($pet[0]['Owned_1'] == false && $pet[0]['Owned_2'] == false) {
       unset($comparison[$pet[0]['Species']][0]);
      } 
    }
?>



<script type="text/javascript">

function filter_humanoid() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Humanoid") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Humanoid") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_dragonkin() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Dragonkin") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Dragonkin") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_flying() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Flying") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Flying") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_undead() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Undead") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Undead") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_critter() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Critter") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Critter") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_magic() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Magic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Magic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_elemental() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Elemental") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Elemental") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_beast() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Beast") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Beast") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_aquatic() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Aquatic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Aquatic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_mechanic() {
    if (document.getElementById('familiesfilter').value == '<?php echo __("Mechanical") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<?php echo __("Mechanical") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}


function filter_reset() {
    document.getElementById('namefilter').value = '';
    Table.filter(document.getElementById('namefilter'),document.getElementById('namefilter'));
    document.getElementById('familiesfilter').value = '';
    Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    document.getElementById('tradeablefilter').value = '';
    Table.filter(document.getElementById('tradeablefilter'),document.getElementById('tradeablefilter'));
    document.getElementById('breedfilter').value = '';
    Table.filter(document.getElementById('breedfilter'),document.getElementById('breedfilter'));
    document.getElementById('levelfilter').value = '';
    Table.filter(document.getElementById('levelfilter'),document.getElementById('levelfilter'));
    document.getElementById('qualityfilter').value = '';
    Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    document.getElementById('breedfilter2').value = '';
    Table.filter(document.getElementById('breedfilter2'),document.getElementById('breedfilter2'));
    document.getElementById('levelfilter2').value = '';
    Table.filter(document.getElementById('levelfilter2'),document.getElementById('levelfilter2'));
    document.getElementById('qualityfilter2').value = '';
    Table.filter(document.getElementById('qualityfilter2'),document.getElementById('qualityfilter2'));
    document.getElementById('dupes_filter_1').value = '';
    Table.filter(document.getElementById('dupes_filter_1'),document.getElementById('dupes_filter_1'));
    document.getElementById('dupes_filter_2').value = '';
    Table.filter(document.getElementById('dupes_filter_2'),document.getElementById('dupes_filter_2'));
}

function quickfilter(x,y) {
  if (x == 'only_1') {
      filter_reset();
      document.getElementById('dupes_filter_2').value = '0';
      Table.filter(document.getElementById('dupes_filter_2'),document.getElementById('dupes_filter_2'));
  }
  if (x == 'only_2') {
      filter_reset();
      document.getElementById('dupes_filter_1').value = '0';
      Table.filter(document.getElementById('dupes_filter_1'),document.getElementById('dupes_filter_1'));
  }
  if (x == 'dupes_1') {
      filter_reset();
      document.getElementById('dupes_filter_1').value = 'function(val){return parseFloat(val)>1;}';
      Table.filter(document.getElementById('dupes_filter_1'),document.getElementById('dupes_filter_1'));
      document.getElementById('dupes_filter_2').value = '0';
      Table.filter(document.getElementById('dupes_filter_2'),document.getElementById('dupes_filter_2'));
  }
  if (x == 'dupes_2') {
      filter_reset();
      document.getElementById('dupes_filter_2').value = 'function(val){return parseFloat(val)>1;}';
      Table.filter(document.getElementById('dupes_filter_2'),document.getElementById('dupes_filter_2'));
      document.getElementById('dupes_filter_1').value = '0';
      Table.filter(document.getElementById('dupes_filter_1'),document.getElementById('dupes_filter_1'));
  }
  if (y == 't') {
    document.getElementById('tradeablefilter').value = '<?php echo __("Yes"); ?>';
    Table.filter(document.getElementById('tradeablefilter'),document.getElementById('tradeablefilter'));
  }
}

$(document).ready(function() {
  $('.quickfilter').tooltipster({
    maxWidth: '400',
    theme: 'tooltipster-smallnote',
    interactive: true,
    side: ['right']
  });
});

window.onload = function () {
    $('.count').each(function () {
        $(this).show();
        $(this).prop('Counter',0).animate({
            Counter: $(this).text()
        }, {
            duration: 800,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });
    });

    var chart_allpets_1 = new CanvasJS.Chart("chart_allpets_1",
         {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 200,
            height: 55,
            title:{
                text: ""
            },
    toolTip:{
      shared: true
    },
      dataPointWidth: 22,
      axisY:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
      axisX:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
    data: [{
      type: "stackedBar100",
          name: "Unique pets",
      dataPoints: [
        { y: <?php echo $stats_1['Unique'] ?>, color: "<?php echo $stats_1['Color'] ?>", label: " " }
      ]
      },
      {
        type: "stackedBar100",
              name: "Not Owned",
        dataPoints: [
          { y: <?php echo count($all_pets)-$stats_1['Unique'] ?>, color: "#8f8f8f", label: " "  }
        ]
      }
    ]
  });
    chart_allpets_1.render();    

    
   var chart_maxpets_1 = new CanvasJS.Chart("chart_maxpets_1",
         {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 200,
            height: 40,
            title:{
                text: ""
            },
    toolTip:{
      shared: true
    },
      dataPointWidth: 16,
      axisY:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
      axisX:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
    data: [{
      type: "stackedBar100",
          name: "Maximized (Level 25 Rare)",
      dataPoints: [
        { y: <?php echo $stats_1['Maxed'] ?>, color: "<?php echo $stats_1['Color'] ?>", label: " " }
      ]
      },
      {
        type: "stackedBar100",
              name: "Not Maximized",
        dataPoints: [
          { y: <?php echo $stats_1['NotMaxed'] ?>, color: "#8f8f8f", label: " "  }
        ]
      }
    ]
  });
    chart_maxpets_1.render();
    
    
    <?
    $lvl_display = str_replace(".", ",", $stats_1['LevelAverage']);
    $lvl_display = round($lvl_display,2)
    ?>
 
   var chart_lvlaverage_1 = new CanvasJS.Chart("chart_lvlaverage_1",
         {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 200,
            height: 42,
            title:{
                text: ""
            },
    toolTip:{
      shared: true
    },
      dataPointWidth: 16,
      axisY:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
      axisX:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
    data: [{
      type: "stackedBar100",
          name: "Average Level",
      dataPoints: [
        { y: <?php echo $lvl_display ?>, color: "<?php echo $stats_1['Color'] ?>", label: " " }
      ]
      },
      {
        type: "stackedBar100",
              name: "",
        dataPoints: [
          { y: <?php echo 25-$lvl_display ?>, color: "#8f8f8f", label: " "  }
        ]
      }
    ]
  });
    chart_lvlaverage_1.render();    
    
    

    
   

    
    var chart_allpets_2 = new CanvasJS.Chart("chart_allpets_2",
         {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 200,
            height: 55,
            title:{
                text: ""
            },
    toolTip:{
      shared: true
    },
      dataPointWidth: 22,
      axisY:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
      axisX:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
    data: [
      {
        type: "stackedBar100",
              name: "Not Owned",
        dataPoints: [
          { y: <?php echo count($all_pets)-$stats_2['Unique'] ?>, color: "#8f8f8f", label: " "  }
        ]
      },
{
      type: "stackedBar100",
          name: "Unique pets",
      dataPoints: [
        { y: <?php echo $stats_2['Unique'] ?>, color: "<?php echo $stats_2['Color'] ?>", label: " " }
      ]
      }
    ]
  });
    chart_allpets_2.render();    

    
   var chart_maxpets_2 = new CanvasJS.Chart("chart_maxpets_2",
         {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 200,
            height: 40,
            title:{
                text: ""
            },
    toolTip:{
      shared: true
    },
      dataPointWidth: 16,
      axisY:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
      axisX:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
    data: [
      {
        type: "stackedBar100",
              name: "Not Maximized",
        dataPoints: [
          { y: <?php echo $stats_2['NotMaxed'] ?>, color: "#8f8f8f", label: " "  }
        ]
      },
{
      type: "stackedBar100",
          name: "Maximized (Level 25 Rare)",
      dataPoints: [
        { y: <?php echo $stats_2['Maxed'] ?>, color: "<?php echo $stats_2['Color'] ?>", label: " " }
      ]
      }
    ]
  });
    chart_maxpets_2.render();    
    

    <?
    $lvl_display = str_replace(".", ",", $stats_2['LevelAverage']);
    $lvl_display = round($lvl_display,2)
    ?>

   var chart_lvlaverage_2 = new CanvasJS.Chart("chart_lvlaverage_2",
         {
            animationEnabled: true,
            animationDuration: 800,
            backgroundColor: null,
            width: 200,
            height: 42,
            title:{
                text: ""
            },
    toolTip:{
      shared: true
    },
      dataPointWidth: 16,
      axisY:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
      axisX:{
          valueFormatString: " ",
          tickLength: 0,
          tickColor: "null",
          tickThickness: 0,
          lineThickness: 0,
          gridColor: "null",
          lineColor: "null"
      },
    data: [
      {
        type: "stackedBar100",
              name: "",
        dataPoints: [
          { y: <?php echo 25-$lvl_display ?>, color: "#8f8f8f", label: " "  }
        ]
      },
{
      type: "stackedBar100",
          name: "Average Level",
      dataPoints: [
        { y: <?php echo $lvl_display ?>, color: "<?php echo $stats_2['Color'] ?>", label: " " }
      ]
      }
    ]
  });
    chart_lvlaverage_2.render();    
    
    
    

   

    var chartLevels = new CanvasJS.Chart("chartLevels",
    {
    title:{
        text: "<?php echo __("Level Distribution") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black",
        fontSize : "16"
    },
        interactivityEnabled: true,
        animationEnabled: true,
        animationDuration: 800,
        backgroundColor: null,
        width: 880,
        height: 250,

            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 5,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null",
                interval: 1
            },

        data: [
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "10",
            indexLabelPlacement: "inside",
            toolTipContent: "Level {label}: {y} (<?php echo $chardata_avatar_1['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
            { x: 1, y: <?php echo $stats_1[1] ?>, label: "1", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 2, y: <?php echo $stats_1[2] ?>,  label: "2", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 3, y: <?php echo $stats_1[3] ?>,  label: "3", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 4, y: <?php echo $stats_1[4] ?>,  label: "4", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 5, y: <?php echo $stats_1[5] ?>,  label: "5", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 6, y: <?php echo $stats_1[6] ?>, label: "6", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 7, y: <?php echo $stats_1[7] ?>,  label: "7", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 8, y: <?php echo $stats_1[8] ?>, label: "8", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 9, y: <?php echo $stats_1[9] ?>,  label: "9", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 10, y: <?php echo $stats_1[10] ?>,  label: "10", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 11, y: <?php echo $stats_1[11] ?>,  label: "11", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 12, y: <?php echo $stats_1[12] ?>,  label: "12", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 13, y: <?php echo $stats_1[13] ?>, label: "13", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 14, y: <?php echo $stats_1[14] ?>,  label: "14", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 15, y: <?php echo $stats_1[15] ?>, label: "15", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 16, y: <?php echo $stats_1[16] ?>,  label: "16", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 17, y: <?php echo $stats_1[17] ?>,  label: "17", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 18, y: <?php echo $stats_1[18] ?>,  label: "18", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 19, y: <?php echo $stats_1[19] ?>,  label: "19", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 20, y: <?php echo $stats_1[20] ?>, label: "20", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 21, y: <?php echo $stats_1[21] ?>,  label: "21", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 22, y: <?php echo $stats_1[22] ?>,  label: "22", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 23, y: <?php echo $stats_1[23] ?>, label: "23", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 24, y: <?php echo $stats_1[24] ?>,  label: "24", color: "<?php echo $stats_1['Color'] ?>"},
            { x: 25, y: <?php echo $stats_1[25] ?>,  label: "25", color: "<?php echo $stats_1['Color'] ?>"}
            ]
        },
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "10",
            indexLabelPlacement: "inside",
            toolTipContent: "Level {label}: {y} (<?php echo $chardata_avatar_2['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
            { x: 1, y: <?php echo $stats_2[1] ?>, label: "1", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 2, y: <?php echo $stats_2[2] ?>,  label: "2", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 3, y: <?php echo $stats_2[3] ?>,  label: "3", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 4, y: <?php echo $stats_2[4] ?>,  label: "4", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 5, y: <?php echo $stats_2[5] ?>,  label: "5", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 6, y: <?php echo $stats_2[6] ?>, label: "6", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 7, y: <?php echo $stats_2[7] ?>,  label: "7", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 8, y: <?php echo $stats_2[8] ?>, label: "8", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 9, y: <?php echo $stats_2[9] ?>,  label: "9", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 10, y: <?php echo $stats_2[10] ?>,  label: "10", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 11, y: <?php echo $stats_2[11] ?>,  label: "11", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 12, y: <?php echo $stats_2[12] ?>,  label: "12", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 13, y: <?php echo $stats_2[13] ?>, label: "13", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 14, y: <?php echo $stats_2[14] ?>,  label: "14", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 15, y: <?php echo $stats_2[15] ?>, label: "15", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 16, y: <?php echo $stats_2[16] ?>,  label: "16", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 17, y: <?php echo $stats_2[17] ?>,  label: "17", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 18, y: <?php echo $stats_2[18] ?>,  label: "18", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 19, y: <?php echo $stats_2[19] ?>,  label: "19", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 20, y: <?php echo $stats_2[20] ?>, label: "20", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 21, y: <?php echo $stats_2[21] ?>,  label: "21", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 22, y: <?php echo $stats_2[22] ?>,  label: "22", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 23, y: <?php echo $stats_2[23] ?>, label: "23", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 24, y: <?php echo $stats_2[24] ?>,  label: "24", color: "<?php echo $stats_2['Color'] ?>"},
            { x: 25, y: <?php echo $stats_2[25] ?>,  label: "25", color: "<?php echo $stats_2['Color'] ?>"}
            ]
        }
        ]
    });

    chartLevels.render();
    
    var chartFamiliesC = new CanvasJS.Chart("chartFamiliesC",
    {
    title:{
        text: "<?php echo __("Families") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black",
        fontSize : "16"
    },
        interactivityEnabled: true,
        animationEnabled: true,
        animationDuration: 800,
        backgroundColor: null,
        width: 880,
        height: 250,

            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 5,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null",
                labelFontSize: 14,
                labelFontFamily: "MuseoSans-300",
                labelFontColor : "black",
                interval: 1
            },

        data: [
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y} (<?php echo $chardata_avatar_1['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <?php echo $stats_1['Humanoid'] ?>, label: "<?php echo __("Humanoid") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_humanoid },
                {  x: 2, y: <?php echo $stats_1['Dragonkin'] ?>, label: "<?php echo __("Dragonkin") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_dragonkin },
                {  x: 3, y: <?php echo $stats_1['Flying'] ?>, label: "<?php echo __("Flying") ?>", color: "<?php echo $stats_1['Color'] ?> ", click: filter_flying },
                {  x: 4, y: <?php echo $stats_1['Undead'] ?>, label: "<?php echo __("Undead") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_undead },
                {  x: 5, y: <?php echo $stats_1['Critter'] ?>, label: "<?php echo __("Critter") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_critter },
                {  x: 6, y: <?php echo $stats_1['Magic'] ?>, label: "<?php echo __("Magic") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_magic },
                {  x: 7, y: <?php echo $stats_1['Elemental'] ?>, label: "<?php echo __("Elemental") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_elemental },
                {  x: 8, y: <?php echo $stats_1['Beast'] ?>, label: "<?php echo __("Beast") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_beast },
                {  x: 9, y: <?php echo $stats_1['Aquatic'] ?>, label: "<?php echo __("Aquatic") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_aquatic },
                {  x: 10, y: <?php echo $stats_1['Mechanic'] ?>, label: "<?php echo __("Mechanical") ?>", color: "<?php echo $stats_1['Color'] ?>", click: filter_mechanic }
            ]
        },
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y} (<?php echo $chardata_avatar_2['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <?php echo $stats_2['Humanoid'] ?>, label: "<?php echo __("Humanoid") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_humanoid },
                {  x: 2, y: <?php echo $stats_2['Dragonkin'] ?>, label: "<?php echo __("Dragonkin") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_dragonkin },
                {  x: 3, y: <?php echo $stats_2['Flying'] ?>, label: "<?php echo __("Flying") ?>", color: "<?php echo $stats_2['Color'] ?> ", click: filter_flying },
                {  x: 4, y: <?php echo $stats_2['Undead'] ?>, label: "<?php echo __("Undead") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_undead },
                {  x: 5, y: <?php echo $stats_2['Critter'] ?>, label: "<?php echo __("Critter") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_critter },
                {  x: 6, y: <?php echo $stats_2['Magic'] ?>, label: "<?php echo __("Magic") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_magic },
                {  x: 7, y: <?php echo $stats_2['Elemental'] ?>, label: "<?php echo __("Elemental") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_elemental },
                {  x: 8, y: <?php echo $stats_2['Beast'] ?>, label: "<?php echo __("Beast") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_beast },
                {  x: 9, y: <?php echo $stats_2['Aquatic'] ?>, label: "<?php echo __("Aquatic") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_aquatic },
                {  x: 10, y: <?php echo $stats_2['Mechanic'] ?>, label: "<?php echo __("Mechanical") ?>", color: "<?php echo $stats_2['Color'] ?>", click: filter_mechanic }
            ]
        }
        ]
    });

    chartFamiliesC.render();

    var chartQualityC = new CanvasJS.Chart("chartQualityC",
    {
    title:{
        text: "<?php echo __("Quality") ?>",
        fontFamily: "MuseoSans-500",
        fontWeight: "normal",
        fontColor: "black",
        fontSize : "16"
    },
        interactivityEnabled: true,
        animationEnabled: true,
        animationDuration: 800,
        backgroundColor: null,
        width: 880,
        height: 250,

            axisY:{
                valueFormatString: " ",
                tickLength: 0,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null"
            },
            axisX:{
                valueFormatString: " ",
                tickLength: 5,
                tickColor: "null",
                tickThickness: 0,
                lineThickness: 0,
                gridColor: "null",
                lineColor: "null",
                labelFontSize: 14,
                labelFontFamily: "MuseoSans-300",
                labelFontColor : "black",
                interval: 1
            },

        data: [
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y} (<?php echo $chardata_avatar_1['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <?php echo $stats_1['Poor'] ?>, label: "<?php echo __("Poor") ?>", color: "<?php echo $stats_1['Color'] ?>"},
                {  x: 2, y: <?php echo $stats_1['Common'] ?>, label: "<?php echo __("Common") ?>", color: "<?php echo $stats_1['Color'] ?>"},
                {  x: 3, y: <?php echo $stats_1['Uncommon'] ?>, label: "<?php echo __("Uncommon") ?>", color: "<?php echo $stats_1['Color'] ?>"},
                {  x: 4, y: <?php echo $stats_1['Rare'] ?>, label: "<?php echo __("Rare") ?>", color: "<?php echo $stats_1['Color'] ?>"}
            ]
        },
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y} (<?php echo $chardata_avatar_2['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <?php echo $stats_2['Poor'] ?>, label: "<?php echo __("Poor") ?>", color: "<?php echo $stats_2['Color'] ?>"},
                {  x: 2, y: <?php echo $stats_2['Common'] ?>, label: "<?php echo __("Common") ?>", color: "<?php echo $stats_2['Color'] ?>"},
                {  x: 3, y: <?php echo $stats_2['Uncommon'] ?>, label: "<?php echo __("Uncommon") ?>", color: "<?php echo $stats_2['Color'] ?>"},
                {  x: 4, y: <?php echo $stats_2['Rare'] ?>, label: "<?php echo __("Rare") ?>", color: "<?php echo $stats_2['Color'] ?>"}
            ]
        }
        ]
    });
    chartQualityC.render();
    
}
</script>


<table class="profile">
    <tr class="profile">
        <td class="collectionborder" width="50%" valign="top">
            <table cellspacing="0" cellpadding="0">
                <tr valign="top">
                    <td class="collection" align="right"><p class="blogodd" style="font-size: 24px; font-weight: bold"><span class="count"><?php echo $stats_1['Unique'] ?></p></span></td>
                    <td class="collection"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 24px"><?php echo __("Unique pets") ?></div></td>
                    <td valign="top"><div style="width: 200px;" id="chart_allpets_1"></div></td>
                </tr>
                
                <tr valign="top">
                    <td class="collection" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><?php echo $stats_1['Maxed']; ?></p></span></td>
                    <td class="collection"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Maximized") ?></div></td>
                    <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 200px;" id="chart_maxpets_1"></div></td>
                </tr>
                <tr valign="top">
                    <td class="collection" style="padding-top: 11px" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class=""><?php echo round($stats_1['LevelAverage'], 2); ?></p></span></td>
                    <td class="collection" style="padding-top: 11px"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Average Level") ?></div></td>
                    <td valign="top"><div style="width: 200px;" id="chart_lvlaverage_1"></div></td>
                </tr>
                
                <tr valign="top">
                    <td class="collection" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><?php echo $stats_1['Maxed']+$stats_1['NotMaxed']; ?></p></span></td>
                    <td class="collection"  ><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Pets overall") ?></div></td>
                </tr>
                <tr valign="top">
                    <td class="collection" style="padding-top: 5px" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><?php echo $stats_1['Duplicates']; ?></p></span></td>
                    <td class="collection"style="padding-top: 5px" ><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Duplicates") ?></div></td>
                </tr>
                
            </table>
        </td>

        <td class="collectionborder" width="50%" valign="top" align="right">
            <table cellspacing="0" cellpadding="0" align="right">
                <tr valign="top">
                  <td valign="top" align="right"><div style="width: 200px;" id="chart_allpets_2"></div></td>
                  <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 24px"><?php echo __("Unique pets") ?></div></td>
                  <td class="collection" align="left"><p class="blogodd" style="font-size: 24px; font-weight: bold"><span class="count"><?php echo $stats_2['Unique'] ?></p></span></td> 
                </tr>
                
                <tr valign="top">
                  <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 200px;" id="chart_maxpets_2"></div></td>
                  <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Maximized") ?></div></td>
                  <td class="collection" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><?php echo $stats_2['Maxed']; ?></p></span></td>
                </tr>
                <tr valign="top">
                  <td valign="top" align="right"><div style="width: 200px;" id="chart_lvlaverage_2"></div></td>
                  <td class="collection" align="right" style="padding-top: 11px"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Average Level") ?></div></td>
                  <td class="collection" style="padding-top: 11px" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class=""><?php echo round($stats_2['LevelAverage'], 2); ?></p></span></td> 
                </tr>
                
                <tr valign="top">
                  <td></td>
                    <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Pets overall") ?></div></td>
                    <td class="collection" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><?php echo $stats_2['Maxed']+$stats_2['NotMaxed']; ?></p></span></td>
                </tr>
                <tr valign="top">
                  <td></td>
                    <td class="collection"  align="right" style="padding-top: 5px" ><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><?php echo __("Duplicates") ?></div></td>
                    <td class="collection" style="padding-top: 5px" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><?php echo $stats_2['Duplicates']; ?></p></span></td>
                </tr>
                

            </table>
        </td>
    </tr>

    <tr class="profile">
        <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="100" valign="top">
                        <button tabindex="1" type="submit" onclick="LevelStats()" id="ButtonLevels" class="statistics statisticsactive"><?php echo __("Level Distribution") ?></button>
                        <button tabindex="2" type="submit" onclick="FamilyStats()" id="ButtonFamily" class="statistics"><?php echo __("Families") ?></button>
                        <button tabindex="3" type="submit" onclick="QualityStats()" id="ButtonQuality" class="statistics"><?php echo __("Quality") ?></button>
                    </td>

                    <td width="900" style="padding-top: 10px;">
                        <center><div id="chartLevels" class="chartLevels" style="height: 260px; width: 900px;"></div>
                        <center><div id="chartFamiliesC" class="chartFamiliesC" style="height: 260px; width: 900px; display:none"></div>
                        <center><div id="chartQualityC" class="chartQualityC" style="height: 260px; width: 900px; display:none"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr class="profile">
        <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td>
                        <table width="100%" id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:30 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                        <thead>

                            <tr>
                                <th colspan="3" class="petlistheaderfirst" style="text-align: left">
                                  <div style="margin-left: 20px; display: inline" class="quickfilter" data-tooltip-content="#quickfilter_tt">
                                    <a class="creatorlink" style="cursor: pointer">Quickfilter</a>
                                  </div>
                                
                                  <div style="display: none">
                                    <span id="quickfilter_tt">
                                      Common filter settings:<br>
                                      <br>
                                      <a class="langselector" onclick="quickfilter('only_1','')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Pets <?php echo $chardata_avatar_1['character']['name']; ?> owns but <?php echo $chardata_avatar_2['character']['name']; ?> doesn't</a></br>
                                      <a class="langselector" onclick="quickfilter('only_2','')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Pets <?php echo $chardata_avatar_2['character']['name']; ?> owns but <?php echo $chardata_avatar_1['character']['name']; ?> doesn't</a></br>
                                      <br>
                                      <a class="langselector" onclick="quickfilter('only_1','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable pets <?php echo $chardata_avatar_1['character']['name']; ?> owns but <?php echo $chardata_avatar_2['character']['name']; ?> doesn't</a></br>
                                      <a class="langselector" onclick="quickfilter('only_2','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable pets <?php echo $chardata_avatar_2['character']['name']; ?> owns but <?php echo $chardata_avatar_1['character']['name']; ?> doesn't</a></br>
                                      <br>
                                      <a class="langselector" onclick="quickfilter('dupes_1','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable duplicates <?php echo $chardata_avatar_1['character']['name']; ?> owns but <?php echo $chardata_avatar_2['character']['name']; ?> doesn't</a></br>
                                      <a class="langselector" onclick="quickfilter('dupes_2','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable duplicates <?php echo $chardata_avatar_2['character']['name']; ?> owns but <?php echo $chardata_avatar_1['character']['name']; ?> doesn't</a></br>
                                      <br>
                                      <a class="langselector" onclick="filter_reset()" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Reset Filters</a></br>
                                      
                                    </span>
                                  </div>
                                
                                </th>
                                <th colspan="4" class="petlistheaderfirst" style="background-color: <?php echo $stats_1['Color']; ?>; border: 1px solid <?php echo $stats_1['Color']; ?>"><p class="blogodd"><?php echo $chardata_avatar_1['character']['name']; ?></p></th>
                                <th class="petlistheaderfirst"></th>
                                <th colspan="4" class="petlistheaderfirst" style="background-color: <?php echo $stats_2['Color']; ?>; border: 1px solid <?php echo $stats_2['Color']; ?>"><p class="blogodd"><?php echo $chardata_avatar_2['character']['name']; ?></p></th>
                            </tr>
                            
                            <tr>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="300"><p class="table-sortable-black" style="margin-left: 15px;"><?php echo __("Name") ?></p></th>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><?php echo __("Families") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Tradable") ?></th>
                                
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-left: 1px solid <?php echo $stats_1['Color']; ?>"><p class="table-sortable-black"><?php echo __("Level") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Quality") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Breed") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-right: 1px solid <?php echo $stats_1['Color']; ?>"><p class="table-sortable-black"><?php echo __("Copies") ?></th>
                                <th class="petlistheaderfirst"></th>
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-left: 1px solid <?php echo $stats_2['Color']; ?>"><p class="table-sortable-black"><?php echo __("Level") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Quality") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><?php echo __("Breed") ?></th>                          
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-right: 1px solid <?php echo $stats_2['Color']; ?>"><p class="table-sortable-black"><?php echo __("Copies") ?></th>
                            </tr>

                            <tr>
                                <th align="left" class="petlistheadersecond" width="300">
                                    <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:150px;" id="familiesfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Humanoid") ?>"><?php echo __("Humanoid") ?></option>
                                        <option class="petselect" value="<?php echo __("Dragonkin") ?>"><?php echo __("Dragonkin") ?></option>
                                        <option class="petselect" value="<?php echo __("Flying") ?>"><?php echo __("Flying") ?></option>
                                        <option class="petselect" value="<?php echo __("Undead") ?>"><?php echo __("Undead") ?></option>
                                        <option class="petselect" value="<?php echo __("Critter") ?>"><?php echo __("Critter") ?></option>
                                        <option class="petselect" value="<?php echo __("Magic") ?>"><?php echo __("Magic") ?></option>
                                        <option class="petselect" value="<?php echo __("Elemental") ?>"><?php echo __("Elemental") ?></option>
                                        <option class="petselect" value="<?php echo __("Beast") ?>"><?php echo __("Beast") ?></option>
                                        <option class="petselect" value="<?php echo __("Aquatic") ?>"><?php echo __("Aquatic") ?></option>
                                        <option class="petselect" value="<?php echo __("Mechanical") ?>"><?php echo __("Mechanical") ?></option>
                                    </select>
                                </th>
                                
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="tradeablefilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Yes"); ?>"><?php echo __("Yes"); ?></option>
                                        <option class="petselect" value="<?php echo __("No"); ?>"><?php echo __("No"); ?></option>
                                        <option class="petselect" value="N/A">N/A</option>
                                    </select>
                                </th>
                                
                                <th align="center" class="petlistheadersecond" style="border-left: 1px solid <?php echo $stats_1['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="levelfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="25">25</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<25;}">< 25</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<20;}">< 20</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<15;}">< 15</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<10;}">< 10</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<5;}">< 5</option>
                                        <option class="petselect" value="1">1</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>0;}">1-25</option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:100px;" id="qualityfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Rare") ?>"><?php echo __("Rare") ?></option>
                                        <option class="petselect" value="<?php echo __("Uncommon") ?>"><?php echo __("Uncommon") ?></option>
                                        <option class="petselect" value="<?php echo __("Common") ?>"><?php echo __("Common") ?></option>
                                        <option class="petselect" value="<?php echo __("Poor") ?>"><?php echo __("Poor") ?></option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:70px;" id="breedfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="PP">PP</option>
                                        <option class="petselect" value="SS">SS</option>
                                        <option class="petselect" value="HH">HH</option>
                                        <option class="petselect" value="PB">PB</option>
                                        <option class="petselect" value="SB">SB</option>
                                        <option class="petselect" value="HB">HB</option>
                                        <option class="petselect" value="PS">PS</option>
                                        <option class="petselect" value="HS">HS</option>
                                        <option class="petselect" value="HP">HP</option>
                                        <option class="petselect" value="BB">BB</option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond" style="border-right: 1px solid <?php echo $stats_1['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="dupes_filter_1" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="0">0</option>
                                        <option class="petselect" value="1">1</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>1;}">2+</option>
                                        <option class="petselect" value="3">3</option>
                                    </select>
                                </th>
                                
                                <th class="petlistheadersecond"></th>

                                <th align="center" class="petlistheadersecond" style="border-left: 1px solid <?php echo $stats_2['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="levelfilter2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="25">25</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<25;}">< 25</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<20;}">< 20</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<15;}">< 15</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<10;}">< 10</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)<5;}">< 5</option>
                                        <option class="petselect" value="1">1</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>0;}">1-25</option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:100px;" id="qualityfilter2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="<?php echo __("Rare") ?>"><?php echo __("Rare") ?></option>
                                        <option class="petselect" value="<?php echo __("Uncommon") ?>"><?php echo __("Uncommon") ?></option>
                                        <option class="petselect" value="<?php echo __("Common") ?>"><?php echo __("Common") ?></option>
                                        <option class="petselect" value="<?php echo __("Poor") ?>"><?php echo __("Poor") ?></option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:70px;" id="breedfilter2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="PP">PP</option>
                                        <option class="petselect" value="SS">SS</option>
                                        <option class="petselect" value="HH">HH</option>
                                        <option class="petselect" value="PB">PB</option>
                                        <option class="petselect" value="SB">SB</option>
                                        <option class="petselect" value="HB">HB</option>
                                        <option class="petselect" value="PS">PS</option>
                                        <option class="petselect" value="HS">HS</option>
                                        <option class="petselect" value="HP">HP</option>
                                        <option class="petselect" value="BB">BB</option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>
                                
                                <th align="center" class="petlistheadersecond" style="border-right: 1px solid <?php echo $stats_2['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="dupes_filter_2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><?php echo __("All") ?></option>
                                        <option class="petselect" value="0">0</option>
                                        <option class="petselect" value="1">1</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>1;}">2+</option>
                                        <option class="petselect" value="3">3</option>
                                    </select>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                        <?
                        foreach($comparison as $pet_array) {
                          foreach ($pet_array as $pet) { ?>
                            <tr class="petlist">
                                <td class="petlist" align="left" style="padding-left: 12px;"><div style="white-space:nowrap"><a class="petlist" href="http://<?php echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<?php echo $pet['PetID'] ?>" target="_blank"><?php echo $pet['Name'] ?></a></div></td>
                                <td align="left" class="petlist" style="padding-left: 12px;"><p class="blogodd"><?php echo $pet['Family'] ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Cageable'] == "1") echo __("Yes");
                                    else if ($pet['Cageable'] == "2") echo __("No");
                                    else if ($pet['Cageable'] == "0") echo "N/A"; ?></td>


                                <td align="center" class="petlist" style="border-left: 1px solid <?php echo $stats_1['Color'];
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "; background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_1'] == 0) echo "";
                                    else echo $pet['Level_1']; ?></td>
                                
                                <td align="center" class="petlist" <?php if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo 'style="background-color: #d8a0a0"'; ?>><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo '-';
                                    if ($pet['Quality_1'] == 3) { echo '<font color="#0058a5">'.__("Rare"); }
                                    else if ($pet['Quality_1'] == 2) { echo '<font color="#147e09">'.__("Uncommon"); }
                                    else if ($pet['Quality_1'] == 1) { echo '<font color="#ffffff">'.__("Common"); }
                                    else if ($pet['Quality_1'] == 0 && $pet['Level_1'] != 0) { echo '<font color="#4d4d4d">'.__("Poor"); } ?></td>
                                
                                <td align="center" class="petlist" style="<?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_1'] == 0) echo "";
                                    else { echo $pet['Breed_1']; } ?></td>

                                <td align="center" class="petlist" style="border-right: 1px solid <?php echo $stats_1['Color']; ?>; <?
                                    if ($pet['Position'] == 0 && $pet['Copies_1'] < 1) { echo "background-color: #d8a0a0"; ?>"><p class="blogodd">0<?php }
                                    if ($pet['Position'] == 0 && $pet['Copies_1'] > 0) echo '"><p class="blogodd">'.$pet['Copies_1'];
                                    if ($pet['Position'] != 0 && $pet['Copies_1'] < 1) echo '">';
                                    if ($pet['Position'] != 0 && $pet['Copies_1'] > 0) echo '"><p class="blogodd">'.$pet['Copies_1']; ?></td>

                                <td></td>

                                <td align="center" class="petlist" style="border-left: 1px solid <?php echo $stats_2['Color'];
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "; background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_2'] == 0) echo "";
                                    else echo $pet['Level_2']; ?></td>
                                
                                <td align="center" class="petlist" <?php if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo 'style="background-color: #d8a0a0"'; ?>><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo '-';
                                    if ($pet['Quality_2'] == 3) { echo '<font color="#0058a5">'.__("Rare"); }
                                    else if ($pet['Quality_2'] == 2) { echo '<font color="#147e09">'.__("Uncommon"); }
                                    else if ($pet['Quality_2'] == 1) { echo '<font color="#ffffff">'.__("Common"); }
                                    else if ($pet['Quality_2'] == 0 && $pet['Level_2'] != 0) { echo '<font color="#4d4d4d">'.__("Poor"); } ?></td>
                                
                                <td align="center" class="petlist" style="<?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_2'] == 0) echo "";
                                    else { echo $pet['Breed_2']; } ?></td>
                                
                                <td align="center" class="petlist" style="border-right: 1px solid <?php echo $stats_2['Color']; ?>; <?
                                    if ($pet['Position'] == 0 && $pet['Copies_2'] < 1) { echo "background-color: #d8a0a0"; ?>"><p class="blogodd">0<?php }
                                    if ($pet['Position'] == 0 && $pet['Copies_2'] > 0) echo '"><p class="blogodd">'.$pet['Copies_2'];
                                    if ($pet['Position'] != 0 && $pet['Copies_2'] < 1) echo '">';
                                    if ($pet['Position'] != 0 && $pet['Copies_2'] > 0) echo '"><p class="blogodd">'.$pet['Copies_2']; ?></td>
        
                            </tr>
                        <?php }
                        } ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                <td colspan="3" align="center" style="border-top: 1px solid <?php echo $stats_1['Color']; ?>"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                                <td align="left" class="table-page:next" style="cursor:pointer; border-top: 1px solid <?php echo $stats_1['Color']; ?>"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                <td></td>
                                <td colspan="4" align="right" style="border-top: 1px solid <?php echo $stats_2['Color']; ?>"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><?php echo __("Reset Filters") ?></a></div></td>
                            </tr>
                        </tfoot>
                      </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?
}
