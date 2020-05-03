<?php require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('BBCode.php');
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

function calc_strats_rating($stratid, $language, $user = "", $external = 0) {


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
  $findcol = find_collection($user, $path_level);
  if ($findcol != "No Collection") {
      $fp = fopen($findcol['Path'], 'r');
      $col_master = json_decode(fread($fp, filesize($findcol['Path'])), true);
      foreach ($col_master as $key => $pet) {
          $col_master[$key]['Family'] = convert_family($all_pets[$pet['Species']]['Family']);  // TODO - is this required!? remove?!
      }
  }
  list ($taglow, $taghigh) = tag_priorities ($user);
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
                                   . ', Published'
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
                              . 'WHERE Sub = (SELECT Sub FROM Alternatives WHERE id = ' . $stratid . ' LIMIT 1)'
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
  $rating_factor = $numratings <= 10 ? 0.5 : ($numratings < 20 ? 0.8 : 1.0);
  $rating_score = round ((($avgrating - 2.5) * $rating_factor + 2.5) * 100);


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
                              , $tag_score                   // 0..20
                                   , $thisstrat->Views            // 0..likely never 50k or something
                                   );
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
            $publishtitle = $backuptitle;
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
                $link[1] = _($main->POName);
            }
            else {
                $link[1] = $main->Name;
            }
        }
    }

    if ($category == "1") {
        $link[0] = "?m=Blog";
        $link[1] = _("MainbarBlog");
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
            <a class="<? if ($propage == "profile") { echo "active"; } ?>button" href="?page=profile" ><? echo _("UM_BTProfile") ?></a>
        </li>
        <li>
            <a class="<? if ($propage == "col") { echo "active"; } ?>button" href="?page=collection" ><? echo _("UM_PetCollection") ?></a>
        </li>
        <li>
            <a class="<? if ($propage == "strategies") { echo "active"; } ?>button" href="?page=strategies" >My Strategies <? echo $stratcomstotal ?></a>
        </li>
        <li>
            <a class="<? if ($propage == "mycomments") { echo "active"; } ?>button" href="?page=mycomments" ><? echo _("UM_BTComments") ?><? echo $mynewcomsout ?></a>
        </li>
        <li>
            <a class="<? if ($propage == "messages") { echo "active"; } ?>button" href="?page=messages"><? echo _("UM_BTMessages") ?><? echo $unreadmsgp ?></a>
        </li>
        <li>
            <a class="<? if ($propage == "settings" OR $propage == "notesettings") { echo "active"; } ?>button" href="?page=settings" ><? echo _("UM_BTSettings") ?></a>
        </li>
        <? if ($userrights['AdmPanel'] == "on") { ?>
        <li>
            <a class="<? if ($propage == "admin") { echo "active"; } ?>button" href="?page=admin" >Administration</a>
        </li>
        <? } ?>
        <? if (User_is_allowed ($user, 'LocArticles')) { ?>
        <li>
            <a class="<? if ($propage == "loc") { echo "active"; } ?>button" href="?page=loc" >Localization</a>
        </li>
        <? } ?>
        <li>
            <a class="button" href="?page=logout"><? echo _("UM_BTLogout") ?></a>
        </li>
    </ul>
<?
}










// ======================================= PRINT A LINE OF STRATEGY ===========================================================================

function bt_stredit_printline($lineid, $strat, $language, $userid = "") {
    $dbcon = $GLOBALS['dbcon'];
    if ($userid == "") {
        $user = $GLOBALS['user'];
    }
    else {
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

    $all_pets = $GLOBALS['all_pets'];
    if ($all_pets == "") {
      $all_pets = get_all_pets($petnext);
    }

    // INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
    putenv("LANG=".$language.".UTF-8");
    setlocale(LC_ALL, $language.".UTF-8");

    $domain = "messages";
    bindtextdomain($domain, "../../Locale");
    textdomain($domain);

    set_language_vars($language);

    $userrights = format_userrights($user->Rights);

    if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) {
        $instwidth = "615px";
    }
    else {
        $instwidth = "701px";
    }
    $stepdb = mysqli_query($dbcon, "SELECT * FROM Strategy WHERE id = '$lineid'");
    $step = mysqli_fetch_object($stepdb);

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

    // =========== Gather Spell and Pet names and transform formatting accordingly =========

    $checkspells = extract_text($showinstruction);
    if (sizeof($checkspells) != 0 && $checkspells[0] != ""){

        // A) Tansform Spells
        if (strpos($showinstruction, 'spell=') !== false) {
            foreach ($checkspells as $key => $value) {
                $cutinstruction = explode("spell=", $checkspells[$key]);
                $cutinstruction = explode("]", sizeof($cutinstruction) > 1 ? $cutinstruction[1] : '');
                $findspell = str_replace('&quot;','"',$cutinstruction[0]);
                $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE en_US = '$findspell' AND PetSpell = '1'");
                if (mysqli_num_rows($spelldb) == 0) {
                  $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE en_US = '$findspell'");
                }
                if (mysqli_num_rows($spelldb) > "0") {
                    $spell = mysqli_fetch_object($spelldb);
                    if (isset($spell->{$language}) && $spell->{$language} != "") {
                        $spellfull = $spell->{$language};
                    }
                    else {
                        $spellfull = $spell->en_US;
                    }
                    $replacewith = "<a href=\"http://".$wowhdomain.".wowhead.com/petability=".$spell->SpellID."\" target=\"_blank\" class=\"battletablespell\">".$spellfull."</a>";
                }
                else {
                    $replacepetwith = $cutinstruction[0];
                }
                $replacespell = "[spell=".$cutinstruction[0]."]";
                $showinstruction = str_replace($replacespell,$replacewith,$showinstruction);
                $cutinstruction = "";
                $spelldb = "";
                $spell = "";
                $findspell = '';
                $spellfull = '';
                $replacespell = '';
                $replacewith = '';
            }
        }

        // B) Tansform Enemy Pets
        if (strpos($showinstruction, 'enemy=') !== false) {
            foreach ($checkspells as $key => $value) {
                $cutinstruction = explode("enemy=", $checkspells[$key]);
                $cutinstruction = explode("]", sizeof($cutinstruction) > 1 ? $cutinstruction[1] : '');
                $findpet = str_replace('&quot;','"',$cutinstruction[0]);
                $transpetdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE Name Like '$findpet'");
                if (mysqli_num_rows($transpetdb) > "0") {
                    $transpet = mysqli_fetch_object($transpetdb);
                    if (isset($transpet->{$petnext}) && $transpet->{$petnext} != "") {
                        $transpetfull = $transpet->{$petnext};
                    }
                    else {
                        $transpetfull = $transpet->Name;
                    }
                    $replacepetwith = "<a href=\"http://".$wowhdomain.".wowhead.com/npc=".$transpet->PetID."\" target=\"_blank\" class=\"battletablespell\">".$transpetfull."</a>";
                }
                else {
                    $replacepetwith = $cutinstruction[0];
                }
                $replacepet = "[enemy=".$cutinstruction[0]."]";
                $showinstruction = str_replace($replacepet,$replacepetwith,$showinstruction);
                $cutinstruction = "";
                $transpetdb = "";
                $transpet = "";
            }
        }

        // C) Tansform Pets
        if (strpos($showinstruction, 'pet=') !== false) {
            foreach ($checkspells as $key => $value) {
                $cutinstruction = explode("pet=", $checkspells[$key]);
                $cutinstruction = explode("]", sizeof($cutinstruction) > 1 ? $cutinstruction[1] : '');
                $findpet = str_replace('&quot;','"',$cutinstruction[0]);
                $search_pet = searchForId($findpet, $all_pets, array());
                if ($search_pet) {
                  $replacepetwith = "<a href=\"http://".$wowhdomain.".wowhead.com/npc=".$all_pets[$search_pet[0]]['PetID']."\" target=\"_blank\" class=\"battletablespell\">".$all_pets[$search_pet[0]]['Name']."</a>";
                }
                else {
                    $replacepetwith = $cutinstruction[0];
                }
                $replacepet = "[pet=".$cutinstruction[0]."]";
                $showinstruction = str_replace($replacepet,$replacepetwith,$showinstruction);
                $cutinstruction = "";
                $transpetdb = "";
                $transpet = "";
            }
        }
    }
    $showinstruction = stripslashes($showinstruction);

    ?>

    <div id="step_<? echo $lineid ?>" class="bt_step_cont bt_adm_trigger" data-lineid="<? echo $lineid ?>">
        <div class="bt_step_turn"><? echo $showturn ?></div>
        <div class="bt_step_sepa"></div>
        <div class="bt_step_inst" style="width: <? echo $instwidth ?>"><? echo $showinstruction ?></div>
        <? if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) { ?>
            <div class="bt_step_admin bt_adm_trigger" data-lineid="<? echo $lineid ?>">
                <div id="bt_admin_panel_<? echo $lineid ?>" style="opacity: 0">
                    <img class="bt_step_editicon bt_step_quickfill_<? echo $lineid; ?>" data-tooltip-content="#bt_quickfill_tt_<? echo $lineid ?>" src="https://www.wow-petguide.com/images/icon_bt_quickfill.png">
                    <img class="bt_step_editicon bt_step_edit_<? echo $lineid; ?>" data-tooltip-content="#bt_step_edit_<? echo $lineid ?>" src="https://www.wow-petguide.com/images/icon_bt_edit.png">
                    <img class="bt_step_editicon bt_step_delete_<? echo $lineid; ?>" data-tooltip-content="#bt_delete_tt_<? echo $lineid ?>" src="https://www.wow-petguide.com/images/icon_bt_minus.png">
                    <img class="bt_step_editicon" onclick="stredit_addline('<? echo $lineid ?>','<? echo $user->id ?>','<? echo $user->ComSecret ?>', '<? echo $language ?>')" src="https://www.wow-petguide.com/images/icon_bt_plus.png">
                </div>
            </div>
        <? } ?>


    <div style="display:none">
        <span id="bt_delete_tt_<? echo $lineid ?>">
            <div class="stredit_qf_container">
                    <div class="stredit_qf_textbox" style="margin: 3px; width: 100px;">
                        <center>
                        <img class="bb_edit_save" style="padding-top: 4px" src="https://www.wow-petguide.com/images/icon_bt_x.png" onclick="$('.bt_step_delete_<? echo $lineid ?>').tooltipster('close');">
                        <img class="bb_edit_save" style="padding-top: 4px" src="https://www.wow-petguide.com/images/icon_bt_ok.png" onclick="stredit_removeline('<? echo $lineid ?>','<? echo $user->id ?>','<? echo $user->ComSecret ?>')">
                        </center>
                    </div>
                 <div style="clear: both"></div>
            </div>
        </span>
    </div>


    <div style="display:none">
        <span id="bt_quickfill_tt_<? echo $lineid ?>">
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

                            $bb_pets[$i]['ENName'] = mysqli_real_escape_string($dbcon, $all_pets[$fetchpet]['ENName']);

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
                            <div style="padding-left: 3px"><? echo $all_pets[$fetchpet]['Name']; ?></div>
                            <?
                            if ($skill1 != "*") {
                                $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$skill1'");
                                $thisspell = mysqli_fetch_object($spelldb);
                                $bb_spells[$i][1]['Type'] = "spell";
                                $bb_spells[$i][1]['PetID'] = $fetchpet;
                                $bb_spells[$i][1]['DisplayName'] = $thisspell->{$language};
                                $bb_spells[$i][1]['Name'] = mysqli_real_escape_string($dbcon, $thisspell->{$language});
                                $bb_spells[$i][1]['ENName'] = mysqli_real_escape_string($dbcon, $thisspell->en_US);
                                $bb_spells[$i][1]['Icon'] = $thisspell->Icon;
                                $bb_spells[$i][1]['Count'] = $i;
                                ?>

                                <div class="spell_tt" data-tooltip-content="#pet<? echo $i ?>_spell1_tt" style="float: left; margin-bottom: 3px;" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','0','<? echo $lineid; ?>','<? echo $thisspell->SpellID; ?>','<? echo $language; ?>')">
                                    <img class="stredit_qf_spell" style="cursor: pointer" src="https://blzmedia-a.akamaihd.net/wow/icons/36/<? echo $thisspell->Icon ?>.jpg">
                                </div>
                                <div style="display: none"><span id="pet<? echo $i ?>_spell1_tt"><? echo $thisspell->{$language} ?></span></div>
                                <?
                            }
                            else {
                                $bb_spells[$i][1]['Type'] = "wildcard";
                                $bb_spells[$i][1]['Count'] = $i;
                                echo '<img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">';
                            }

                            if ($skill2 != "*") {
                                $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$skill2'");
                                $thisspell = mysqli_fetch_object($spelldb);
                                $bb_spells[$i][2]['Type'] = "spell";
                                $bb_spells[$i][2]['PetID'] = $fetchpet;
                                $bb_spells[$i][2]['DisplayName'] = $thisspell->{$language};
                                $bb_spells[$i][2]['Name'] = mysqli_real_escape_string($dbcon, $thisspell->{$language});
                                $bb_spells[$i][2]['ENName'] = mysqli_real_escape_string($dbcon, $thisspell->en_US);
                                $bb_spells[$i][2]['Icon'] = $thisspell->Icon;
                                $bb_spells[$i][2]['Count'] = $i;
                                ?>

                                <div class="spell_tt" data-tooltip-content="#pet<? echo $i ?>_spell2_tt" style="float: left; margin-bottom: 3px;" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','0','<? echo $lineid; ?>','<? echo $thisspell->SpellID; ?>','<? echo $language; ?>')">
                                    <img class="stredit_qf_spell" style="cursor: pointer" src="https://blzmedia-a.akamaihd.net/wow/icons/36/<? echo $thisspell->Icon ?>.jpg">
                                </div>
                                <div style="display: none"><span id="pet<? echo $i ?>_spell2_tt"><? echo $thisspell->{$language} ?></span></div>
                                <?
                            }
                            else {
                                $bb_spells[$i][2]['Type'] = "wildcard";
                                $bb_spells[$i][2]['Count'] = $i;
                                echo '<img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">';
                            }

                            if ($skill3 != "*") {
                                $spelldb = mysqli_query($dbcon, "SELECT * FROM Spells WHERE SpellID = '$skill3'");
                                $thisspell = mysqli_fetch_object($spelldb);
                                $bb_spells[$i][3]['Type'] = "spell";
                                $bb_spells[$i][3]['PetID'] = $fetchpet;
                                $bb_spells[$i][3]['DisplayName'] = $thisspell->{$language};
                                $bb_spells[$i][3]['Name'] = mysqli_real_escape_string($dbcon, $thisspell->{$language});
                                $bb_spells[$i][3]['ENName'] = mysqli_real_escape_string($dbcon, $thisspell->en_US);
                                $bb_spells[$i][3]['Icon'] = $thisspell->Icon;
                                $bb_spells[$i][3]['Count'] = $i;
                                ?>

                                <div class="spell_tt" data-tooltip-content="#pet<? echo $i ?>_spell3_tt" style="float: left; margin-bottom: 3px;" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','0','<? echo $lineid; ?>','<? echo $thisspell->SpellID; ?>','<? echo $language; ?>')">
                                    <img class="stredit_qf_spell" style="cursor: pointer" src="https://blzmedia-a.akamaihd.net/wow/icons/36/<? echo $thisspell->Icon ?>.jpg">
                                </div>
                                <div style="display: none"><span id="pet<? echo $i ?>_spell3_tt"><? echo $thisspell->{$language} ?></span></div>
                                <?
                            }
                            else {
                                $bb_spells[$i][3]['Type'] = "wildcard";
                                $bb_spells[$i][3]['Count'] = $i;
                                echo '<img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">';
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
                                    $petcardtitle = _("PetCardAnyPetName")." "._("PetCardAnyLevelES")." ".$displayreqlvl;
                                }
                                else {
                                    $petcardtitle = _("PetCardLevel")." ".$displayreqlvl." "._("PetCardPet");
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
                                    $petcardtitle = _("PetCardAnyPetName");
                                }
                                else {
                                    if ($_SESSION["lang"] =="fr_FR"){
                                        $petcardtitle = _("PetCardAnyPetName")." "._("PetCardAnyLevelES")." ".$displayreqlvl;
                                    }
                                    else {
                                        $petcardtitle = _("PetCardLevel")." ".$displayreqlvl." "._("PetCardPet");
                                    }
                                }
                                $qp_anypet = "true";
                            }

                            // Any Family part
                           if ($fetchpet > "10" && $fetchpet <= "20") {
                                switch ($fetchpet) {
                                   case "11":
                                      $famname = _("PetFamiliesHumanoid");
                                      $famsuffix = _("PetCardSuffixHumanoid");
                                      $famid = "0";
                                      $rmfamid = "1";
                                      $qp_family[$countfamilies] = "Humanoid";
                                      $countfamilies++;
                                   break;
                                   case "12":
                                      $famname = _("PetFamiliesMagic");
                                      $famsuffix = _("PetCardSuffixMagic");
                                      $famid = "5";
                                      $rmfamid = "6";
                                      $qp_family[$countfamilies] = "Magic";
                                      $countfamilies++;
                                   break;
                                   case "13":
                                      $famname = _("PetFamiliesElemental");
                                      $famsuffix = _("PetCardSuffixElemental");
                                      $famid = "6";
                                      $rmfamid = "7";
                                      $qp_family[$countfamilies] = "Elemental";
                                      $countfamilies++;
                                   break;
                                   case "14":
                                      $famname = _("PetFamiliesUndead");
                                      $famsuffix = _("PetCardSuffixUndead");
                                      $famid = "3";
                                      $rmfamid = "4";
                                      $qp_family[$countfamilies] = "Undead";
                                      $countfamilies++;
                                   break;
                                   case "15":
                                      $famname = _("PetFamiliesMechanical");
                                      $famsuffix = _("PetCardSuffixMech");
                                      $famid = "9";
                                      $rmfamid = "A";
                                      $qp_family[$countfamilies] = "Mechanical";
                                      $countfamilies++;
                                   break;
                                   case "16":
                                      $famname = _("PetFamiliesFlying");
                                      $famsuffix = _("PetCardSuffixFlyer");
                                      $famid = "2";
                                      $rmfamid = "3";
                                      $qp_family[$countfamilies] = "Flying";
                                      $countfamilies++;
                                   break;
                                   case "17":
                                      $famname = _("PetFamiliesCritter");
                                      $famsuffix = _("PetCardSuffixCritter");
                                      $famid = "4";
                                      $rmfamid = "5";
                                      $qp_family[$countfamilies] = "Critter";
                                      $countfamilies++;
                                   break;
                                   case "18":
                                      $famname = _("PetFamiliesAquatic");
                                      $famsuffix = _("PetCardSuffixAquatic");
                                      $famid = "8";
                                      $rmfamid = "9";
                                      $qp_family[$countfamilies] = "Aquatic";
                                      $countfamilies++;
                                   break;
                                   case "19":
                                      $famname = _("PetFamiliesBeast");
                                      $famsuffix = _("PetCardSuffixBeast");
                                      $famid = "7";
                                      $rmfamid = "8";
                                      $qp_family[$countfamilies] = "Beast";
                                      $countfamilies++;
                                   break;
                                   case "20":
                                      $famname = _("PetFamiliesDragonkin");
                                      $famsuffix = _("PetCardSuffixDragonkin");
                                      $famid = "1";
                                      $rmfamid = "2";
                                      $qp_family[$countfamilies] = "Dragonkin";
                                      $countfamilies++;
                                   break;
                                }

                                // Create array for BB icons in direct edit window
                                $bb_pets[$i]['Name'] = _("PetCardPrefixAny")." ".$famsuffix;
                                $bb_pets[$i]['ENName'] = _("PetCardPrefixAny")." ".$famsuffix;
                                $bb_pets[$i]['Type'] = "special";
                                $bb_pets[$i]['Icon'] = 'images/pets/resize50/'.$fetchpet.'.png';

                                 $reqlevelpieces = explode("+", $reqlevel);
                                 $displayreqlvl = $reqlevelpieces[0]."+";

                                 if ($reqlevel == "" OR $reqlevelpieces[0] == "1") {
                                   $petcardtitle = _("PetCardPrefixAny")." ".$famsuffix;
                                }
                                else {
                                   if ($_SESSION["lang"] =="es_ES"){
                                      $petcardtitle = _("PetCardPrefixAny")." ".$famsuffix." "._("PetCardAnyLevelES")." ".$displayreqlvl;
                                   }
                                   else if ($_SESSION["lang"] =="fr_FR"){
                                      $petcardtitle = _("PetCardAnyPetName")." de type ".$famsuffix." "._("PetCardAnyLevelES")." ".$displayreqlvl;
                                   }
                                   else {
                                      $petcardtitle = _("PetCardLevel")." ".$displayreqlvl." ".$famsuffix;
                                   }
                                }
                            }

                            ?>
                            <div style="padding-left: 3px"><? echo $petcardtitle ?></div>
                            <img class="stredit_qf_spell" style="padding-bottom: 3px" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                            <img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                            <img class="stredit_qf_spell" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                        <? }
                        echo "</div>";
                        $i++;
                    } ?>

                    <div class="stredit_qf_textbox" style="margin-top: 0px; width: 558px">
                        <div class="stredit_qf_textitem" style="width: 558px" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','1','<? echo $lineid; ?>','Pass','<? echo $language; ?>')">
                            Pass
                        </div>
                    </div>

                    <div class="stredit_qf_textbox" style="margin-top: 0px">
                        <? // Bring in your pet new line
                        if ($qp_regular) {
                            foreach ($qp_regular as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','2','<? echo $lineid; ?>','<? echo $value['PetID']; ?>','<? echo $language; ?>')">
                                   <i>Bring in your <? echo $value['Name']; ?></i>
                               </div>
                            <? }
                        }
                        if ($qp_levelpet == "true") { ?>
                            <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','4','<? echo $lineid; ?>','','<? echo $language; ?>')">
                               <i>Bring in your Level Pet</i>
                           </div>
                        <? }
                        if ($qp_family) {
                            foreach ($qp_family as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','6','<? echo $lineid; ?>','<? echo $value; ?>','<? echo $language; ?>')">
                                   <i>Bring in your <? echo $value; ?> pet</i>
                               </div>
                            <? }
                        } ?>
                    </div>

                    <div class="stredit_qf_textbox" style="margin-top: 0px">
                        <? // Swaps to your pet
                        if ($qp_regular) {
                            foreach ($qp_regular as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','3','<? echo $lineid; ?>','<? echo $value['PetID']; ?>','<? echo $language; ?>')">
                                   Swap to your <? echo $value['Name']; ?>
                               </div>
                            <? }
                        }
                        if ($qp_levelpet == "true") { ?>
                            <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','5','<? echo $lineid; ?>','','<? echo $language; ?>')">
                               Swap to your Level Pet
                           </div>
                        <? }
                        if ($qp_family) {
                            foreach ($qp_family as $key => $value) { ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','7','<? echo $lineid; ?>','<? echo $value; ?>','<? echo $language; ?>')">
                                   Swap to your <? echo $value; ?> pet
                               </div>
                            <? }
                        } ?>
                    </div>


                    <? // Enemy pets come in

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
                            $petdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE PetID = '$fetchnpc'");
                            if (mysqli_num_rows($petdb) > "0") {
                            $enemy = mysqli_fetch_object($petdb);
                            if ($enemy->{$petnext} != "") {
                                $enemyfullname = $enemy->{$petnext};
                            }
                            else {
                                $enemyfullname = $enemy->Name;
                            }
                            ?>
                                <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','8','<? echo $lineid; ?>','<? echo $enemy->PetID; ?>','<? echo $language; ?>')">
                                    <i><? echo $enemyfullname; ?> comes in</i>
                                </div>
                            <? }
                        }
                        $p++;
                    }

                    if ($sub->Pet1 != "0" OR $sub->Pet2 != "0" OR $sub->Pet3 != "0") {
                        echo '</div>';
                    } ?>

                    <div class="stredit_qf_textbox" style="margin-top: 0px">
                        <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','9','<? echo $lineid; ?>','','<? echo $language; ?>')">
                            <i>An enemy pet comes in</i>
                        </div>
                        <div class="stredit_qf_textitem" onclick="stredit_quickfill('<? echo $user->id ?>','<? echo $user->ComSecret ?>','10','<? echo $lineid; ?>','','<? echo $language; ?>')">
                            Any standard attack will finish the fight
                        </div>
                    </div>

                    <div style="clear: both"></div>
                </div>

			</span>
		</div>


    <div style="display:none">
        <span id="bt_step_edit_<? echo $lineid ?>">

            <div style="width: 92px; float: left;">&nbsp;</div>
            <div class="stredit_edit_bb">
                <button type="button" style="float: left" class="bbbutton" onclick="bb_stredit('<? echo $lineid ?>','simple','b')"><b>[b]</b></button>
                <button type="button" style="float: left" class="bbbutton" onclick="bb_stredit('<? echo $lineid ?>','simple','i')"><i>[i]</i></button>
                <button type="button" style="float: left" class="bbbutton" onclick="bb_stredit('<? echo $lineid ?>','simple','u')"><u>[u]</u></button>

                <img class="bb_pet spell_tt" style="margin-left: 30px" onclick="bb_stredit('<? echo $lineid ?>','<? echo $bb_pets[1]['Type'] ?>','<? echo $bb_pets[1]['ENName'] ?>')" data-tooltip-content="#bb_pet_pet1" src="https://www.wow-petguide.com/<? echo $bb_pets[1]['Icon'] ?>">
                <div style="display: none"><span id="bb_pet_pet1"><? echo $bb_pets[1]['Name'] ?></span></div>
                <img class="bb_pet spell_tt" onclick="bb_stredit('<? echo $lineid ?>','<? echo $bb_pets[2]['Type'] ?>','<? echo $bb_pets[2]['ENName'] ?>')" data-tooltip-content="#bb_pet_pet2" src="https://www.wow-petguide.com/<? echo $bb_pets[2]['Icon'] ?>">
                <div style="display: none"><span id="bb_pet_pet2"><? echo $bb_pets[2]['Name'] ?></span></div>
                <img class="bb_pet spell_tt" style="margin-right: 30px" onclick="bb_stredit('<? echo $lineid ?>','<? echo $bb_pets[3]['Type'] ?>','<? echo $bb_pets[3]['ENName'] ?>')" data-tooltip-content="#bb_pet_pet3" src="https://www.wow-petguide.com/<? echo $bb_pets[3]['Icon'] ?>">
                <div style="display: none"><span id="bb_pet_pet3"><? echo $bb_pets[3]['Name'] ?></span></div>

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
                            <img class="bb_pet spell_tt" <? echo $lastmargin ?> onclick="bb_stredit('<? echo $lineid ?>','spell','<? echo $value['ENName']; ?>')" data-tooltip-content="#bb_spell_<? echo $value['Count'] ?>_<? echo $key ?>" src="https://blzmedia-a.akamaihd.net/wow/icons/36/<? echo $value['Icon'] ?>.jpg">
                            <div style="display: none"><span id="bb_spell_<? echo $value['Count'] ?>_<? echo $key ?>"><? echo $value['DisplayName'] ?></span></div>
                        <? }
                            else if ($value['Type'] == "wildcard") { ?>
                            <img class="bb_pet_wildcard" <? echo $lastmargin ?> src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                           <? }
                        }
                    }
                    else { ?>
                        <img class="bb_pet_wildcard" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                        <img class="bb_pet_wildcard" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                        <img class="bb_pet_wildcard" style="margin-right: 10px" src="https://www.wow-petguide.com/images/bt_edit_wildcard.png">
                    <? }

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
                        $petdb = mysqli_query($dbcon, "SELECT * FROM PetsNPC WHERE PetID = '$fetchnpc'");
                        if (mysqli_num_rows($petdb) > "0") {
                            $enemy = mysqli_fetch_object($petdb);
                            if ($enemy->{$petnext} != "") {
                                $enemyfullname = $enemy->{$petnext};
                            }
                            else {
                                $enemyfullname = $enemy->Name;
                            }
                            $enemybbname = str_replace('"','\"', $enemy->Name);
                        ?>
                            <img class="bb_pet spell_tt" style="float: right" onclick='bb_stredit("<? echo $lineid ?>","enemy","<? echo $enemybbname; ?>")' data-tooltip-content="#bb_enemy_<? echo $p ?>" src="https://blzmedia-a.akamaihd.net/wow/icons/36/<? echo $enemy->Icon ?>.jpg">
                            <div style="display: none"><span id="bb_enemy_<? echo $p ?>"><? echo $enemyfullname;  ?></span></div>
                        <? }
                    }
                    $p = $p-1;
                } ?>

            </div>

            <div class="stredit_qf_container" style="display: table-cell">
                <div class="stredit_edit_inputbox" style="display: table-cell">
                    <input type="text" maxlength="15" class="stredit_editline" style="width: 80px;" id="stredit_editstep_<? echo $lineid ?>" value="<? echo $raweditturn ?>">
                    <input type="text" maxlength="1000" class="stredit_editline" style="width: 625px;" id="stredit_editinst_<? echo $lineid ?>" value="<? echo $raweditinst ?>">
                    <div style="float: right; margin: 3 8 0 0">
                        <img class="bb_edit_save" src="https://www.wow-petguide.com/images/icon_bt_x.png" onclick="$('.bt_step_edit_<? echo $lineid ?>').tooltipster('close');">
                        <img class="bb_edit_save" src="https://www.wow-petguide.com/images/icon_bt_ok.png" onclick="bb_strsave('<? echo $user->id ?>','<? echo $user->ComSecret ?>','<? echo $lineid ?>','<? echo $language ?>');">
                    </div>
                </div>
                <script>
                var input = document.getElementById("stredit_editinst_<? echo $lineid ?>");
                input.addEventListener("keyup", function(event) {
                    event.preventDefault();
                    if (event.keyCode === 13) {
                        bb_strsave('<? echo $user->id ?>','<? echo $user->ComSecret ?>','<? echo $lineid ?>','<? echo $language ?>');
                    }
                });
                var inputz = document.getElementById("stredit_editstep_<? echo $lineid ?>");
                inputz.addEventListener("keyup", function(event) {
                    event.preventDefault();
                    if (event.keyCode === 13) {
                        bb_strsave('<? echo $user->id ?>','<? echo $user->ComSecret ?>','<? echo $lineid ?>','<? echo $language ?>');
                    }
                });
                </script>



            <? if ($language != "en_US") { ?>

                <div class="stredit_qf_petbox autoloc_tt" style="cursor: help; display: table-cell; margin: 0px; padding: 0px; width: 100%" data-tooltip-content="#autoloc_tt_<? echo $lineid ?>">
                    <b>Important:</b> Please enter your instructions in <b>English</b> or a large parts of the community will not be able to read them.<br>
                    Some typical sentences are translated automatically. Move your mouse over this part for more details.
                </div>
                <div style="display: none"><span id="autoloc_tt_<? echo $lineid ?>">
                    <div class="stredit_edit_inputbox" style="display: table-cell; width: 794px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #dfdfdf;">
                        The following strings are auto-translated if you enter them in the defined format. It is recommended to use them where possible:
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #cecece;">
                            [spell=Spellname] - [pet=Petname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #c1c1c1;">
                            [spell=Spellname] - [enemy=Enemyname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #cecece;">
                            [spell=Spellname] until [pet=Petname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #c1c1c1;">
                            [spell=Spellname] until [enemy=Enemyname] dies
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #cecece;">
                            Swap back to [pet=Petname]
                        </div>
                        <div style="padding-left: 14px; width: 780px; font-family: MuseoSans-500; font-size: 14px; color: #000000; background-color: #c1c1c1;">
                            [spell=Spellname] until the fight is won
                        </div>
                    </div>

                </span></div>
            <? } ?>
            </div>

        </span>
    </div>






    <? if ($userrights['EditStrats'] == "yes" OR $strat->User == $user->id) { ?>
        <script>
            $(document).ready(function() {
                $('.autoloc_tt').tooltipster({
                    theme: 'tooltipster-bbedit',
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
            bt_initialize_tooltips('<? echo $lineid ?>');
        </script>
    <? } ?>
    </div>
<? }






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
                        <a href="?page=admin" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "main") { echo "active"; } ?>" style="display: block">Explanation</button></a>
                    <? if ($userrights['Edit_Menu'] == "yes") { ?>
                        <a href="?page=adm_menu" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "menu") { echo "active"; } ?>" style="display: block">Navigation Menu</button></a>
                    <? } ?>
                        <a href="?page=adm_strategies" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "strategies") { echo "active"; } ?>" style="display: block">Strategies</button></a>
                    <? if ($userrights['EditStrats'] == "yes" && $user->id == 2) { ?>
                        <a href="?page=adm_comreports" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "adm_comreports") { echo "active"; } ?>" style="display: block">Comment Reports</button></a>
                    <? } ?>
                    <? if ($userrights['AdmPetImport'] == "on") { ?>
                        <a href="?page=adm_petimport" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "adm_petimport") { echo "active"; } ?>" style="display: block">Pet Data Import</button></a>
                    <? } ?>
                    <? if ($userrights['AdmBreeds'] == "on") { ?>
                        <a href="?page=adm_breeds" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "adm_breeds") { echo "active"; } ?>" style="display: block">Breed Importer</button></a>
                    <? } ?>
                    <? if ($userrights['AdmPeticons'] == "on") { ?>

                        <a href="?page=adm_peticons" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "adm_peticons") { echo "active"; } ?>" style="display: block">Pet Icons</button></a>

                    <? } ?>
                    <? if ($userrights['AdmImages'] == "on") { ?>

                        <a href="?page=adm_images" style="text-decoration: none; float: left; padding-right: 3px"><button class="settings<? if ($admpage == "adm_images") { echo "active"; } ?>" style="display: block">Article Images</button></a>

                    <? } ?>
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
                        <a href="?page=loc" style="text-decoration: none"><button class="settings<? if ($admpage == "mains" OR $admpage == "") { echo "active"; } ?>" style="display: block">Main Categories</button></a>
                    </td>
                    <td>
                        <a href="?page=loc_fights" style="text-decoration: none"><button class="settings<? if ($admpage == "fights") { echo "active"; } ?>" style="display: block">Fight Names</button></a>
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
        if (mysqli_num_rows($bnetuserdb) > "0") {
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
                mysqli_query($dbcon, "INSERT INTO Leaderboard (`User`, `Unique_Pets`, `Region`) VALUES ('$user->id', '$unique_pets', '$user->Region')") OR die(mysqli_error($dbcon));
              }
              else {
                mysqli_query($dbcon, "UPDATE Leaderboard SET Unique_Pets = '$unique_pets' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
                mysqli_query($dbcon, "UPDATE Leaderboard SET Region = '$user->Region' WHERE User = '$user->id'") OR die(mysqli_error($dbcon));
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

    // [spell=Spellname] - [enemy=Petname] dies
    if (preg_match('/^\[spell=[^\[]{0,9999}\] - \[enemy=[^\[]{0,9999}\] dies.{0,2}$/', $instruction)){
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

    // Spell X until enemy Y resurrects
    if (preg_match('/^\[spell=[^\[]{0,9999}\] until \[enemy=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction) OR preg_match('/^\[spell=[^\[]{0,9999}\] until the \[enemy=[^\[]{0,9999}\] resurrects.{0,2}$/', $instruction)){
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
       $returnfam = _("PetFamiliesHumanoid");
       break;
    case "Flying":
       $returnfam = _("PetFamiliesFlying");
       break;
    case "Magic":
       $returnfam = _("PetFamiliesMagic");
       break;
    case "Elemental":
       $returnfam = _("PetFamiliesElemental");
       break;
    case "Undead":
       $returnfam = _("PetFamiliesUndead");
       break;
    case "Mechanical":
       $returnfam = _("PetFamiliesMechanical");
       break;
    case "Critter":
       $returnfam = _("PetFamiliesCritter");
       break;
    case "Aquatic":
       $returnfam = _("PetFamiliesAquatic");
       break;
    case "Beast":
       $returnfam = _("PetFamiliesBeast");
       break;
    case "Dragonkin":
       $returnfam = _("PetFamiliesDragonkin");
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


// ======================================= Determine Special Pets like Any Pet or Any Frog ===========================================================================
// Function one - add here the name of all congregated pets like "Any Frog" and "Any Spider".

function check_specialpet($petdetails){

$foundspecialpet = "false";
switch ($petdetails) {
    case "Any Frog":
       $foundspecialpet = "true";
       break;
    case "Any Lantern":
       $foundspecialpet = "true";
       break;
    case "Any Spider":
       $foundspecialpet = "true";
       break;
    case "Any Fox":
       $foundspecialpet = "true";
       break;
    case "Any Snail":
       $foundspecialpet = "true";
       break;
    case "Any Moth":
       $foundspecialpet = "true";
       break;
    case "Any Rabbit":
       $foundspecialpet = "true";
       break;
    case "Any Snake":
       $foundspecialpet = "true";
       break;
    case "Any Roach":
       $foundspecialpet = "true";
       break;
}
return $foundspecialpet;
}


// Function two - add all details required for display for the special pet in question
/*
$specialpet['id'] 	    	= 	NPC ID of the pet in first slot
$specialpet['idtwo'] 	    	= 	NPC ID of the pet in second slot
$specialpet['idthree'] 		= 	NPC ID of the pet in third slot
$specialpet['family'] 		= 	Family of the pet
$specialpet['displayname']  	= 	"Any Lantern" oder "Level X+ Pet"
$specialpet['comment']  		= 	Specific comment like "Any level 25, blue quality fox will do in this slot."
*/

function get_specialpet($petdetails, $petlevel, $petcomment, $petid = NULL){

    $specialpet = NULL;

    if ($petid !== NULL && $petdetails == '')
    {
        switch ($petid)
        {
        case 0: $petdetails = 'Any Pet'; break;
        case 11: $petdetails = 'Any Humanoid'; break;
        case 12: $petdetails = 'Any Magic'; break;
        case 13: $petdetails = 'Any Elemental'; break;
        case 14: $petdetails = 'Any Undead'; break;
        case 15: $petdetails = 'Any Mech'; break;
        case 16: $petdetails = 'Any Flyer'; break;
        case 17: $petdetails = 'Any Critter'; break;
        case 18: $petdetails = 'Any Aquatic'; break;
        case 19: $petdetails = 'Any Beast'; break;
        case 20: $petdetails = 'Any Dragonkin'; break;
        }
    }

switch ($petdetails) {
    case "Level Pet":
        $specialpet['id'] = "0";
        $specialpet['idtwo'] = "0";
        $specialpet['idthree'] = "0";
        $specialpet['family'] = "level";
        $specialpet['displayfamily'] = "level";
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardLevelPetTitle");
                }
        else {
            if ($_SESSION["lang"] == "fr_FR"){
                $specialpet['displayname'] = _("PetCardAnyPetName")." "._("PetCardAnyLevelES")." ".$petlevel;
            }
            else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardPet");
            }
        }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardLevelPetStandard");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Pet":
        $specialpet['id'] = "0";
        $specialpet['idtwo'] = "0";
        $specialpet['idthree'] = "0";
        $specialpet['family'] = "any";
        $specialpet['displayfamily'] = "any";
        if ($petlevel == ""){
            $specialpet['displayname'] = _("PetCardAnyPetName");
        }
        else {
            if ($_SESSION["lang"] == "fr_FR"){
                $specialpet['displayname'] = _("PetCardAnyPetName")." "._("PetCardAnyLevelES")." ".$petlevel;
            }
            else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardPet");
            }
        }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyPetStandard");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Tanaan Pet":
        $specialpet['id'] = "80329";
        $specialpet['idtwo'] = "68659";
        $specialpet['idthree'] = "68850";
        $specialpet['rematchone'] = "80329";
        $specialpet['rematchtwo'] = "68659";
        $specialpet['rematchthree'] = "68850";
        $specialpet['family'] = "tanaan";
        $specialpet['displayfamily'] = "Tanaan";
        $specialpet['displayname'] = _("PetCardTanaanPet");
        $specialpet['comment'] = _("PetCardTanaanPetDesc");
        break;
    case "Any Frog":
        $specialpet['id'] = "62312";
        $specialpet['idtwo'] = "63002";
        $specialpet['idthree'] = "62997";
        $specialpet['rematchone'] = "62312";
        $specialpet['rematchtwo'] = "63002";
        $specialpet['rematchthree'] = "62997";
        $specialpet['family'] = "Aquatic";
        $specialpet['displayname'] = _("PetCardAnyFrog");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFrogDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Lantern":
        $specialpet['id'] = "46898";
        $specialpet['idtwo'] = "55574";
        $specialpet['idthree'] = "55571";
        $specialpet['rematchone'] = "46898";
        $specialpet['rematchtwo'] = "55574";
        $specialpet['rematchthree'] = "55571";
        $specialpet['family'] = "Magic";
        $specialpet['displayfamily'] = _("PetFamiliesMagic");
        $specialpet['displayname'] = _("PetCardAnyLantern");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyLanternDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Fox":
        $specialpet['id'] = "63550";
        $specialpet['idtwo'] = "63551";
        $specialpet['idthree'] = "62864";
        $specialpet['rematchone'] = "63550";
        $specialpet['rematchtwo'] = "63551";
        $specialpet['rematchthree'] = "62864";
        $specialpet['family'] = "Beast";
        $specialpet['displayfamily'] = _("PetFamiliesBeast");
        $specialpet['displayname'] = _("PetCardAnyFox");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFoxDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Spider":
        $specialpet['id'] = "61327";
        $specialpet['idtwo'] = "62186";
        $specialpet['idthree'] = "61320";
        $specialpet['rematchone'] = "61327";
        $specialpet['rematchtwo'] = "62186";
        $specialpet['rematchthree'] = "61320";
        $specialpet['family'] = "Beast";
        $specialpet['displayfamily'] = _("PetFamiliesBeast");
        $specialpet['displayname'] = _("PetCardAnySpider");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnySpiderDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Snail":
        $specialpet['id'] = "63001";
        $specialpet['idtwo'] = "64352";
        $specialpet['idthree'] = "62246";
        $specialpet['rematchone'] = "63001";
        $specialpet['rematchtwo'] = "64352";
        $specialpet['rematchthree'] = "62246";
        $specialpet['family'] = "Critter";
        $specialpet['displayfamily'] = _("PetFamiliesCritter");
        $specialpet['displayname'] = _("PetCardAnySnail");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnySnailDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Moth":
        $specialpet['id'] = "62177";
        $specialpet['idtwo'] = "61314";
        $specialpet['idthree'] = "65187";
        $specialpet['rematchone'] = "62177";
        $specialpet['rematchtwo'] = "61314";
        $specialpet['rematchthree'] = "65187";
        $specialpet['family'] = "Flying";
        $specialpet['displayfamily'] = _("PetFamiliesFlying");
        $specialpet['displayname'] = _("PetCardAnyMoth");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyMothDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Rabbit":
        $specialpet['id'] = "61080";
        $specialpet['idtwo'] = "63557";
        $specialpet['idthree'] = "62178";
        $specialpet['rematchone'] = "61080";
        $specialpet['rematchtwo'] = "63557";
        $specialpet['rematchthree'] = "62178";
        $specialpet['family'] = "Critter";
        $specialpet['displayfamily'] = _("PetFamiliesCritter");
        $specialpet['displayname'] = _("PetCardAnyRabbit");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyRabbitDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Snake":
        $specialpet['id'] = "61142";
        $specialpet['idtwo'] = "63004";
        $specialpet['idthree'] = "61325";
        $specialpet['rematchone'] = "61142";
        $specialpet['rematchtwo'] = "63004";
        $specialpet['rematchthree'] = "61325";
        $specialpet['family'] = "Beast";
        $specialpet['displayfamily'] = _("PetFamiliesBeast");
        $specialpet['displayname'] = _("PetCardAnySnake");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnySnakeDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Roach":
        $specialpet['id'] = "61169";
        $specialpet['idtwo'] = "61384";
        $specialpet['idthree'] = "61319";
        $specialpet['rematchone'] = "61169";
        $specialpet['rematchtwo'] = "61384";
        $specialpet['rematchthree'] = "61319";
        $specialpet['family'] = "Critter";
        $specialpet['displayfamily'] = _("PetFamiliesCritter");
        $specialpet['displayname'] = _("PetCardAnyRoach");
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyRoachDesc");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;

    case "Any Flyer":
        $specialpet['id'] = "16";
        $specialpet['rematchone'] = "61829";
        $specialpet['rematchtwo'] = "7389";
        $specialpet['rematchthree'] = "61322";
        $specialpet['family'] = "Flying";
        $specialpet['familyid'] = "2";
        $specialpet['displayfamily'] = _("PetFamiliesFlying");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixFlyer");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixFlyer")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixFlyer")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixFlyer");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesFlying");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Humanoid":
        $specialpet['id'] = "11";
        $specialpet['rematchone'] = "62526";
        $specialpet['rematchtwo'] = "76873";
        $specialpet['rematchthree'] = "97229";
        $specialpet['family'] = "Humanoid";
        $specialpet['familyid'] = "0";
        $specialpet['displayfamily'] = _("PetFamiliesHumanoid");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixHumanoid");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixHumanoid")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixHumanoid")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                    $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixHumanoid");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesHumanoid");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Magic":
        $specialpet['id'] = "12";
        $specialpet['rematchone'] = "20408";
        $specialpet['rematchtwo'] = "68806";
        $specialpet['rematchthree'] = "68819";
        $specialpet['family'] = "Magic";
        $specialpet['familyid'] = "5";
        $specialpet['displayfamily'] = _("PetFamiliesMagic");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixMagic");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixMagic")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixMagic")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixMagic");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesMagic");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Elemental":
        $specialpet['id'] = "13";
        $specialpet['rematchone'] = "61383";
        $specialpet['rematchtwo'] = "61703";
        $specialpet['rematchthree'] = "99394";
        $specialpet['family'] = "Elemental";
        $specialpet['familyid'] = "6";
        $specialpet['displayfamily'] = _("PetFamiliesElemental");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixElemental");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixElemental")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixElemental")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixElemental");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesElemental");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Undead":
        $specialpet['id'] = "14";
        $specialpet['rematchone'] = "97569";
        $specialpet['rematchtwo'] = "61905";
        $specialpet['rematchthree'] = "97324";
        $specialpet['family'] = "Undead";
        $specialpet['familyid'] = "3";
        $specialpet['displayfamily'] = _("PetFamiliesUndead");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixUndead");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixUndead")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixUndead")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixUndead");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesUndead");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Mech":
        $specialpet['id'] = "15";
        $specialpet['rematchone'] = "61160";
        $specialpet['rematchtwo'] = "62120";
        $specialpet['rematchthree'] = "68839";
        $specialpet['family'] = "Mechanical";
        $specialpet['familyid'] = "9";
        $specialpet['displayfamily'] = _("PetFamiliesMechanical");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixMech");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixMech")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixMech")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixMech");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesMechanical");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Critter":
        $specialpet['id'] = "17";
        $specialpet['rematchone'] = "63062";
        $specialpet['rematchtwo'] = "60649";
        $specialpet['rematchthree'] = "62893";
        $specialpet['family'] = "Critter";
        $specialpet['familyid'] = "4";
        $specialpet['displayfamily'] = _("PetFamiliesCritter");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixCritter");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixCritter")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixCritter")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixCritter");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesCritter");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Aquatic":
        $specialpet['id'] = "18";
        $specialpet['rematchone'] = "88473";
        $specialpet['rematchtwo'] = "94867";
        $specialpet['rematchthree'] = "62997";
        $specialpet['family'] = "Aquatic";
        $specialpet['familyid'] = "8";
        $specialpet['displayfamily'] = _("PetFamiliesAquatic");
        if ($petlevel == "")
        {
            $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixAquatic");
        }
        else
        {
            if ($_SESSION["lang"] =="es_ES")
            {
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixAquatic")." "._("PetCardAnyLevelES")." ".$petlevel;
            }
            else if ($_SESSION["lang"] =="fr_FR")
            {
                $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixAquatic")." "._("PetCardAnyLevelES")." ".$petlevel;
            }
            else
            {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixAquatic");
            }
        }
        if ($petcomment == "")
        {
            $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesAquatic");
        }
        else
        {
            $specialpet['comment'] = $petcomment;
        }
        break;
    case "Any Beast":
        $specialpet['id'] = "19";
        $specialpet['rematchone'] = "61325";
        $specialpet['rematchtwo'] = "62364";
        $specialpet['rematchthree'] = "7385";
        $specialpet['family'] = "Beast";
        $specialpet['familyid'] = "7";
        $specialpet['displayfamily'] = _("PetFamiliesBeast");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixBeast");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixBeast")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixBeast")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixBeast");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesBeast");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;
    case "Any Dragonkin":
        $specialpet['id'] = "20";
        $specialpet['rematchone'] = "97207";
        $specialpet['rematchtwo'] = "68820";
        $specialpet['rematchthree'] = "62201";
        $specialpet['family'] = "Dragonkin";
        $specialpet['familyid'] = "1";
        $specialpet['displayfamily'] = _("PetFamiliesDragonkin");
        if ($petlevel == ""){
                $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixDragonkin");
                }
        else {
                if ($_SESSION["lang"] =="es_ES"){
                    $specialpet['displayname'] = _("PetCardPrefixAny")." "._("PetCardSuffixDragonkin")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else if ($_SESSION["lang"] =="fr_FR"){
                    $specialpet['displayname'] = _("PetCardAnyPetName")." de type "._("PetCardSuffixDragonkin")." "._("PetCardAnyLevelES")." ".$petlevel;
                }
                else {
                $specialpet['displayname'] = _("PetCardLevel")." ".$petlevel." "._("PetCardSuffixDragonkin");
                }
                }
        if ($petcomment == ""){
                $specialpet['comment'] = _("PetCardAnyFamDesc")._("PetFamiliesDragonkin");
                }
        else {
                $specialpet['comment'] = $petcomment;
                }
        break;

}

echo '<!-- get_special_pet (' . $petdetails . ', ' . $petlevel . ', ' . $petcomment . ') = ';
var_dump ($specialpet);
echo '--!>';

return $specialpet;
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

function xufu_mail($recipient, $recname, $subject, $content, $nonhtmlbody)
{
  require_once ('PHPMailerAutoload.php');

//Create a new PHPMailer instance
$mail = new PHPMailer;
$mail->CharSet = 'UTF-8';
//Set who the message is to be sent from
$mail->setFrom('Xufu@wow-petguide.com', 'Xu-Fu');
//Set an alternative reply-to address
$mail->addReplyTo('Xufu@wow-petguide.com', 'Xu-Fu');
//Set who the message is to be sent to
if ($recname == ""){
$mail->addAddress($recipient); }
else {
$mail->addAddress($recipient, $recname);
}
//Set the subject line
$mail->Subject = $subject;

if ($recname == ""){
$recname = ",";
}
else {
$recname = " ".$recname.",";
}


// add body
$body = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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


$mail->msgHTML($body);


//Replace the plain text body with one created manually
if ($nonhtmlbody == ""){
$mail->AltBody = 'This is an HTML email. Please view it in a mail program or an online service that can display HTML emails. Thank you!';
}
else {
$mail->AltBody = $nonhtmlbody;
}

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
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




/* this still uses the old NPC ID table and needs to be bracketed out - needs to be updated in new project
 *
function print_pet_table ($onlycore = "on") {
    $maincat = $GLOBALS['mainselector'];
    $dbcon = $GLOBALS['dbcon'];
    $thispetext = $GLOBALS['petnext'];
    $thissubnameext = $GLOBALS['subnameext'];
    $user = $GLOBALS['user'];
    $collection = $GLOBALS['collection'];
    $language = $GLOBALS['language'];

    $petnext = $GLOBALS['petnext'];
    $allpets = get_allpets($petnext);

    $arr_allpetsmaster = null;
    $countpets = 0;
    $guildpet = [1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE];

    if ($collection) {
        foreach($collection as $pet) {
            $ignorepet = FALSE;
            // Mark the current pet to be ignored in counts (guild herald/page). This is because they can be in collections multiple times (via API), but are only shown once in-game
            if (($pet['ID'] == "49586" && $guildpet[1] == TRUE) || ($pet['ID'] == "49587" && $guildpet[2] == TRUE) || ($pet['ID'] == "49588" && $guildpet[3] == TRUE) || ($pet['ID'] == "49590" && $guildpet[4] == TRUE)) {
                $ignorepet = TRUE;
            }

            if ($ignorepet == FALSE) {
                // Mark hits of the guild herald / guild pages once, do not add again
                if ($pet['ID'] == "49586") $guildpet[1] = TRUE;
                if ($pet['ID'] == "49587") $guildpet[2] = TRUE;
                if ($pet['ID'] == "49588") $guildpet[3] = TRUE;
                if ($pet['ID'] == "49590") $guildpet[4] = TRUE;

                $arr_allpetsmaster[$countpets]['id'] = $pet['ID'];
                $arr_allpetsmaster[$countpets]['level'] = $pet['Level'];
                $arr_allpetsmaster[$countpets]['quality'] = $pet['Quality'];
                $arr_allpetsmaster[$countpets]['family'] = convert_family($allpets[$pet['ID']]['Family']);
                $arr_allpetsmaster[$countpets]['breed'] =  $pet['Breed'];
                $countpets++;

            }
        }
        sortBy('level', $arr_allpetsmaster, 'desc');
        foreach($arr_allpetsmaster as $key => $value) {
            $arr_allpetsmaster[$key]['uid'] = $key;
        }
    }

    if ($maincat == "17") {
        $findmaincat = "Main BETWEEN 17 AND 27";
    }
    else {
        $findmaincat = "Main = '$maincat'";
    }

    // Grab all relevant #1 strategies
    if ($onlycore == "on"){
        $altsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE $findmaincat AND Alternative = '1'")or die(mysqli_error($dbcon));
    }
    else {
        $altsdb = mysqli_query($dbcon, "SELECT * FROM Alternatives WHERE $findmaincat")or die(mysqli_error($dbcon));
    }
    $petcounter = "0";
    $petlist[]['id'] = "";
    $familylist[] = "";

    // Go through every strategy one by one
    while($thisalt = mysqli_fetch_object($altsdb)) {
        $multipets = "";
        $multipets[] = "";
        $arr_allpets = $arr_allpetsmaster;

        // Cycle through pet 1 to 3 for each strategy
        for ($i = 1; $i <= 3; $i++) {

            $petidext = "PetID".$i;
            $petlevelext = "PetLevel".$i;
            $petnameext = "PetName".$i;


            echo '<!-- id = ' . $i . ' -- id=' . $thisalt->{$petidext} . ' -- lvl=' . $thisalt->{$petlevelext} . ' -- nam=' . $thisalt->{$petnameext} . ' --!>';

            // BEGIN PART FOR SPECIFiC PET ID
            if ($thisalt->{$petidext} > "20") {

                // Check if pet is in collection
                if ($collection) {
                    if (array_search($thisalt->{$petidext}, array_column($arr_allpets, 'id')) !== FALSE) {
                        $searchFor = $thisalt->{$petidext};
                        $arr_unpets = array_filter($arr_allpets, function($element) use($searchFor){ return isset($element['id']) && $element['id'] == $searchFor;});
                        sortBy('level', $arr_unpets, 'desc');
                        foreach($arr_unpets as $key => $value) {
                            if ($value['level'] == "25" && $value['quality'] == "3") {
                                $inputmaxed = "Yes";
                                $inputlevel = "25";
                                $inputquality = "3";
                                unset($arr_allpets[$value['uid']]);
                                $arr_allpets = array_values($arr_allpets);
                                break;
                            }
                            else {
                                $inputmaxed = "Notmaxed";
                                $inputlevel = $value['level'];
                                $inputquality = $value['quality'];
                                unset($arr_allpets[$value['uid']]);
                                $arr_allpets = array_values($arr_allpets);
                                break;
                            }
                        }
                    }
                    else {
                        $inputmaxed = "Missing";
                    }
                }

                // Check if pet is already in the table:
                $multipcount = 0;
                foreach ($multipets as $item) {
                    if ($item == $thisalt->{$petidext}) {
                        $multipcount++;
                    }
                }
                $petlcount = 0;
                foreach ($petlist as $item) {
                    if ($item['id'] == $thisalt->{$petidext}) {
                        $petlcount++;
                    }
                }
                $multichecker = $multipcount-$petlcount;

                if ($multichecker < "0") {
                    $multikey = array_search($thisalt->{$petidext}, array_column($petlist, 'id'));
                    $petlist[$multikey]['subs'] = $petlist[$multikey]['subs']."-".$thisalt->Sub;
                    $petlist[$multikey]['occurences']++;
                }
                else {
                    $petlist[$petcounter]['id'] = $thisalt->{$petidext};
                    $petlist[$petcounter]['petid'] = $thisalt->{$petidext};
                    $petidchecker = $thisalt->{$petidext};
                    $petdb = mysqli_query($dbcon, "SELECT * FROM PetsUser WHERE PetID = '$petidchecker'")or die(mysqli_error($dbcon));
                    $thispet = mysqli_fetch_object($petdb);
                    if ($thispet->{$thispetext} == ""){
                        $petlist[$petcounter]['name'] = $thispet->Name;
                    }
                    else {
                        $petlist[$petcounter]['name'] = $thispet->{$thispetext};
                    }
                    $petlist[$petcounter]['family'] = localize_family($thispet->Family);
                    if (!in_array($thispet->Family, $familylist)) {
                        $familylist[] = $thispet->Family;
                    }
                    $petlist[$petcounter]['occurences'] = "1";
                    $petlist[$petcounter]['reqlevel'] = "25";
                    $petlist[$petcounter]['subs'] = isset ($petlist[$petcounter]['subs'])
                                                  ? $petlist[$petcounter]['subs'] . "-" . $thisalt->Sub
                                                  : $thisalt->Sub;
                    if ($collection) {
                        $petlist[$petcounter]['level'] = $inputlevel;
                        $petlist[$petcounter]['quality'] = $inputquality;
                        $petlist[$petcounter]['maxed'] = $inputmaxed;
                    }
                    $petcounter++;
                }
                $multipets[$i] = $thisalt->{$petidext};
            }
            // End for specific pet


            // BEGIN PART FOR ANY SPECIFIC FAMILY
            if ($thisalt->{$petidext} <= 20 && $thisalt->{$petidext} > 10) {

                $thispetlevel = "";
                if (!$thisalt->{$petlevelext}) {
                    $thispetlevel = "1";
                }
                else {
                    $thispetlevel = $thisalt->{$petlevelext};
                }
                $thispetlevel = str_replace('+', '', $thispetlevel);
                $pettest = get_specialpet($thisalt->{$petnameext}, "", "", $thisalt->{$petidext});

                if ($collection) {
                    if (array_search($pettest['familyid'], array_column($arr_allpets, 'family')) !== FALSE) {
                        $searchFor = $pettest['familyid'];
                        $arr_family = array_filter($arr_allpets, function($element) use($searchFor){ return isset($element['family']) && $element['family'] == $searchFor;});
                        sortBy('level', $arr_family, 'desc');
                        $foundmax = "false";
                        $thislast = "";

                        foreach($arr_family as $key => $value) {
                            if ($thispetlevel == "25" && $value['level'] == "25" && $value['quality'] == "3") {
                                // Make sure that the Any X pet is not needed in another slot, otherwise skip this specific pet
                                if (($i == "1" && ($value['id'] == $thisalt->PetID2 OR $value['id'] == $thisalt->PetID3)) OR ($i == "2" && $value['id'] == $thisalt->PetID3)) {  }
                                else {
                                    $inputmaxed = "Yes";
                                    $inputlevel = "25";
                                    $inputquality = "3";
                                    $inputpetid = $value['id'];
                                    $foundmax = "true";
                                    unset($arr_allpets[$value['uid']]);
                                    $arr_allpets = array_values($arr_allpets);
                                    break;
                                }
                            }
                        }

                        if ($foundmax != "true") {
                            sortBy('level', $arr_family, 'asc');
                            foreach($arr_family as $key => $value) {
                                if ($value['level'] >= $thispetlevel) {
                                    $inputmaxed = "Yes";
                                    $inputlevel = $value['level'];
                                    $inputquality = $value['quality'];
                                    $inputpetid = $value['id'];
                                    unset($arr_allpets[$value['uid']]);
                                    $arr_allpets = array_values($arr_allpets);
                                    break;
                                }
                                else {
                                    $inputmaxed = "Notmaxed";
                                    $inputlevel = $value['level'];
                                    $inputquality = $value['quality'];
                                    $inputpetid = $value['id'];
                                    $thislast = $value['uid'];
                                }
                            }
                            if ($thislast){
                                unset($arr_allpets[$thislast]);
                                $arr_allpets = array_values($arr_allpets);
                            }
                        }
                    }
                    else {
                        $inputmaxed = "Missing";
                    }
                }

                $multipcount = 0;
                foreach ($multipets as $item) {
                    if ($item == $thisalt->{$petidext}."|".$thispetlevel) {
                        $multipcount++;
                    }
                }
                $petlcount = 0;
                foreach ($petlist as $item) {
                    $testsplits = explode("|", $item['id']);
                    if (isset ($testsplits[1])) {
                        if ($testsplits[0] == $thisalt->{$petidext} && $testsplits[1] >= $thispetlevel) {
                            $petlcount++;
                        }
                    }
                }
                $multichecker = $multipcount-$petlcount;

                if ($multichecker < "0") {
                    $multikey = array_search($thisalt->{$petidext}."|".$thispetlevel, array_column($petlist, 'id'));
                    $petlist[$multikey]['subs'] = $petlist[$multikey]['subs']."-".$thisalt->Sub;
                    $petlist[$multikey]['occurences']++;
                }
                else {
                    $petlist[$petcounter]['id'] = $thisalt->{$petidext}."|".$thispetlevel;
                    $petlist[$petcounter]['name'] = $pettest['displayname'];
                    $petlist[$petcounter]['family'] = $pettest['displayfamily'];
                    $petlist[$petcounter]['occurences'] = "1";
                    $petlist[$petcounter]['reqlevel'] = $thispetlevel;
                    $petlist[$petcounter]['special'] = "anyfamily";
                    $petlist[$petcounter]['subs'] = isset ($petlist[$petcounter]['subs'])
                                                  ? $petlist[$petcounter]['subs'] . "-" . $thisalt->Sub
                                                  : $thisalt->Sub;
                    if ($collection) {
                        $petlist[$petcounter]['level'] = $inputlevel;
                        $petlist[$petcounter]['quality'] = $inputquality;
                        $petlist[$petcounter]['maxed'] = $inputmaxed;
                        $petlist[$petcounter]['petid'] = isset ($inputpetid) ? $inputpetid : NULL;
                    }
                    $petcounter++;
                }
                $multipets[$i] = $thisalt->{$petidext}."|".$thispetlevel;
            }
            // End of specific family
        }
    }

    array_shift($familylist);
    if ($collection) {
        sortBy('maxed', $petlist, 'asc');
    }
    else {
        sortBy('name', $petlist, 'asc');
    }

    ?>
    <table id="t1" style="border-collapse: collapse;" class="petlist example table-autosort table-autofilter table-autopage:30 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
        <thead>
             <? if ($collection) { ?>
                <tr>
                    <th class="petlistheaderfirst" colspan="4"></th>
                    <th align="center" rowspan="2" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black" style="margin-left: 0px;"><? echo _("TBL_ReqLevel1"); ?><br /><? echo _("TBL_ReqLevel2"); ?></th>
                    <th colspan="3" align="center" class="petlistheaderyourcol"><p class="blogodd"><? echo _("ColTableTitleNName") ?></th>
                </tr>
            <? } ?>

            <tr>
                <th align="center" class="petlistheaderfirst table-sortable:numeric" width="40"></th>
                <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="300"><p class="table-sortable-black" style="margin-left: 15px;"><? echo _("PetTableName") ?></p></th>
                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><? echo _("ColChartFamily") ?></th>
                <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black" style="margin-left: 0px;"><? echo _("PetTableOccur") ?></th>
                <? if (!$collection) { ?>
                    <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black" style="margin-left: 0px;"><? echo _("TBL_ReqLevel"); ?></th>
                <? }
                if ($collection) { ?>
                    <th align="center" class="petlistheaderyourcol table-sortable:numeric"><p class="table-sortable-black"><? echo _("ColChartLevel") ?></th>
                    <th align="center" class="petlistheaderyourcol table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartQuality") ?></th>
                    <th align="center" class="petlistheaderyourcol table-sortable:numeric"><p class="table-sortable-black"></th>
                <? } ?>
            </tr>

            <tr>
                <th align="center" class="petlistheadersecond table-sortable:numeric" width="40"><p class="table-sortable-black">#</th>

                <th align="left" class="petlistheadersecond" width="300">
                    <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                </th>

                <th align="center" class="petlistheadersecond">
                        <select class="petselect" style="width:150px;" id="familiesfilter" onchange="Table.filter(this,this)">
                                <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                <?
                                    if (in_array("Humanoid", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesHumanoid").'">'._("PetFamiliesHumanoid").'</option>';
                                    }
                                    if (in_array("Dragonkin", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesDragonkin").'">'._("PetFamiliesDragonkin").'</option>';
                                    }
                                    if (in_array("Flying", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesFlying").'">'._("PetFamiliesFlying").'</option>';
                                    }
                                    if (in_array("Undead", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesUndead").'">'._("PetFamiliesUndead").'</option>';
                                    }
                                    if (in_array("Critter", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesCritter").'">'._("PetFamiliesCritter").'</option>';
                                    }
                                    if (in_array("Magic", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesMagic").'">'._("PetFamiliesMagic").'</option>';
                                    }
                                    if (in_array("Elemental", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesElemental").'">'._("PetFamiliesElemental").'</option>';
                                    }
                                    if (in_array("Beast", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesBeast").'">'._("PetFamiliesBeast").'</option>';
                                    }
                                    if (in_array("Aquatic", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesAquatic").'">'._("PetFamiliesAquatic").'</option>';
                                    }
                                    if (in_array("Mechanical", $familylist)) {
                                        echo '<option class="petselect" value="'._("PetFamiliesMechanical").'">'._("PetFamiliesMechanical").'</option>';
                                    }
                                ?>
                        </select>
                </th>

                <th align="center" class="petlistheadersecond">
                    <select class="petselect" style="width:90px;" id="uniquefilter" onchange="Table.filter(this,this)">
                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                        <option class="petselect" value="1">1</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>1;}">> 1</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>5;}">> 5</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>10;}">> 10</option>
                        <option class="petselect" value="function(val){return parseFloat(val)>20;}">> 20</option>
                    </select>
                </th>

                <th align="center" style="padding-right: 8px" class="petlistheadersecond">
                    <select class="petselect" style="width:100px;" id="levelfilter" onchange="Table.filter(this,this)">
                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                        <option class="petselect" value="25">25</option>
                        <option class="petselect" value="function(val){return parseFloat(val)<25;}">< 25</option>
                        <option class="petselect" value="function(val){return parseFloat(val)<20;}">< 20</option>
                        <option class="petselect" value="function(val){return parseFloat(val)<15;}">< 15</option>
                        <option class="petselect" value="function(val){return parseFloat(val)<10;}">< 10</option>
                        <option class="petselect" value="function(val){return parseFloat(val)<5;}">< 5</option>
                        <option class="petselect" value="1">1</option>
                    </select>
                </th>

                <? if ($collection) { ?>
                    <th align="center" style="padding-left: 8px" class="petlistheadersyourcol">
                        <select class="petselect" style="width:100px;" id="levelfiltersec" onchange="Table.filter(this,this)">
                            <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                            <option class="petselect" value="<? echo _("ColChartMissing") ?>"><? echo _("ColChartMissing") ?></option>
                            <option class="petselect" value="25">25</option>
                            <option class="petselect" value="function(val){return parseFloat(val)<25;}">< 25</option>
                            <option class="petselect" value="function(val){return parseFloat(val)<20;}">< 20</option>
                            <option class="petselect" value="function(val){return parseFloat(val)<15;}">< 15</option>
                            <option class="petselect" value="function(val){return parseFloat(val)<10;}">< 10</option>
                            <option class="petselect" value="function(val){return parseFloat(val)<5;}">< 5</option>
                            <option class="petselect" value="1">1</option>
                        </select>
                    </th>

                    <th align="center" style="padding-right: 8px" class="petlistheadersyourcol">
                        <select class="petselect" style="width:100px;" id="qualityfilter" onchange="Table.filter(this,this)">
                            <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                            <option class="petselect" value="<? echo _("ColChartMissing") ?>"><? echo _("ColChartMissing") ?></option>
                            <option class="petselect" value="<? echo _("QualityRare") ?>"><? echo _("QualityRare") ?></option>
                            <option class="petselect" value="<? echo _("QualityUncommon") ?>"><? echo _("QualityUncommon") ?></option>
                            <option class="petselect" value="<? echo _("QualityCommon") ?>"><? echo _("QualityCommon") ?></option>
                            <option class="petselect" value="<? echo _("QualityPoor") ?>"><? echo _("QualityPoor") ?></option>
                        </select>
                    </th>

                    <th class="petlistheadersyourcol"></th>
                <? } ?>

            </tr>
        </thead>

        <tbody>

    <?php foreach($petlist as $key => $value) {

                                    echo '<!-- key=';
                                    var_dump ($key);
                                    echo ' value=';
                                    var_dump ($value);
                                    echo '--!>';


                                    ?>

        <tr class="petlist" <?
                    if ($value['maxed'] == "Notmaxed") {
                        echo 'style="background-color: #dbd998" ';
                    }
                    if ($value['maxed'] == "Missing") {
                        echo 'style="background-color: #e9a3a3" ';
                    } ?>>

            <td class="petlist"><center><p class="blogodd"><? echo $key+1 ?>.</td>
            <td class="petlist" align="left" style="padding-left: 4px;"><div style="white-space:nowrap"><a class="petlist" href="http://<? echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<? echo $value['petid'] ?>" target="_blank"><? echo $value['name'] ?></a></div></td>
            <td align="left" class="petlist" style="padding-left: 4px;"><p class="blogodd"><? echo $value['family'] ?></td>

            <td class="petlist"><center><span class="petocc_tooltip_<? echo $key ?>" data-tooltip-content="#atooltip_content_<? echo $key ?>"><p style="cursor: help" class="blogodd"><? echo $value['occurences'] ?></p></span></center></td>

            <div class="tooltip_templates" style="display:none">
                <span id="atooltip_content_<? echo $key ?>">
                    <strong><? echo $value['name'] ?> <? echo _("PetTableUsedAgainst") ?></strong><br /><br /><?

                    $subs = explode("-", $value['subs']);
                    foreach ($subs as $subkey => $subvalue){
                        $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $subvalue");
                        $subentry = mysqli_fetch_object($subdb);
                        if ($subentry->Parent != "0") {
                            $subdb = mysqli_query($dbcon, "SELECT * FROM Sub WHERE id = $subentry->Parent");
                            $subentry = mysqli_fetch_object($subdb);
                        }
                        if ($subentry->{$thispetext} == ""){
                            echo $subentry->Name;
                        }
                        else {
                            echo $subentry->{$thispetext};
                        }

                        echo "<br />";
                    }
                ?></span>
            </div>

            <script>
            $('.petocc_tooltip_<? echo $key ?>').tooltipster({
                theme: 'tooltipster-smallnote'
            });</script>

            <td class="petlist"><center><p class="blogodd"><? echo $value['reqlevel'] ?></center></td>

            <? if ($collection) { ?>
                <td align="center" class="petlist" <?
                    if ($value['maxed'] == "Notmaxed") {
                        echo 'style="background-color: #eae8a4" ';
                    }
                    else if ($value['maxed'] == "Missing") {
                        echo 'style="background-color: #e6b3b3" ';
                    }
                    else {
                        echo 'style="background-color: #c3c3c3" ';
                    } ?>><p class="blogodd"><?
                    if ($value['maxed'] == "Missing") {
                        echo _("ColChartMissing");
                    }
                    else {
                        echo $value['level'];
                    } ?></td>

                <td align="center" class="petlist" <?
                    if ($value['maxed'] == "Notmaxed") {
                        echo 'style="background-color: #eae8a4" ';
                    }
                    else if ($value['maxed'] == "Missing") {
                        echo 'style="background-color: #e6b3b3" ';
                    }
                    else {
                        echo 'style="background-color: #c3c3c3" ';
                    } ?>><p class="blogodd"><?
                     if ($value['maxed'] != "Missing") {
                        switch ($value['quality']) {
                            case "3":
                            echo '<font color="#0058a5">'._("QualityRare");
                            break;
                            case "2":
                            echo '<font color="#1aa624">'._("QualityUncommon");
                            break;
                            case "1":
                            echo '<font color="#ffffff">'._("QualityCommon");
                            break;
                            case "0":
                            echo '<font color="#6d6d6d">'._("QualityPoor");
                            break;
                        }
                    }
                    else {
                        echo _("ColChartMissing");
                    }
                     ?></td>
                    <td style="text-align: center; padding-right: 5px; padding-left: 5px;<?
                    if ($value['maxed'] == "Notmaxed") {
                        echo 'background-color: #eae8a4';
                    }
                    else if ($value['maxed'] == "Missing") {
                        echo 'background-color: #e6b3b3';
                    }
                    else {
                        echo 'background-color: #c3c3c3';
                    } ?>"><?

                    if (isset ($value['special']) && $value['special'] == "specialpet") { ?>
                    <span class="petcomment_tooltip_<? echo $key ?>" data-tooltip-content="#tooltip_content_<? echo $key ?>"><p style="cursor: help" class="blogodd">?</p></span>
                    <div class="tooltip_templates" style="display:none">
                        <span id="tooltip_content_<? echo $key ?>">
                            <strong><? echo _("TBL_AnyPetInst"); ?></strong><br /><br />
                            <? foreach ($value['catpets'] as $petitem) {
                                switch ($petitem['quality']) {
                                    case "3":
                                    $outputpet = '<font color="#45a5ff">'.$petitem['level']."</font>";
                                    break;
                                    case "2":
                                    $outputpet =  '<font color="#22d32f">'.$petitem['level']."</font>";
                                    break;
                                    case "1":
                                    $outputpet =  '<font color="#ffffff">'.$petitem['level']."</font>";
                                    break;
                                    case "0":
                                    $outputpet =  '<font color="#9a9a9a">'.$petitem['level']."</font>";
                                    break;
                                    case "":
                                    $outputpet =  "-";
                                    break;
                                }
                                echo $petitem['name']."  (".$outputpet.")<br />";
                            }
                            echo "<br /><i>"._("TBL_AnyPetInst2")."</i>";
                            ?>
                        </span>
                    </div><script>
                    $('.petcomment_tooltip_<? echo $key ?>').tooltipster({
                        theme: 'tooltipster-smallnote'
                    });</script><?
                    }

                    ?></td>

            <? } ?>

        </tr>
    <?php } ?>

    </tbody>
        <tfoot>
            <? if ($collection) { ?>
            <tr>
                <td colspan="2" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                <td colspan="1" align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                <td colspan="3" align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none;cursor: pointer" onclick="filter_reset()"><? echo _("ColTableReset") ?></a></div></td>
            </tr>
            <? }
            else { ?>
            <tr>
                <td colspan="2" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                <td colspan="1" align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                <td colspan="1" align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none;cursor: pointer" onclick="filter_reset()"><? echo _("ColTableReset") ?></a></div></td>
            </tr>
            <? } ?>
        </tfoot>
    </table>

<script>
    function filter_reset() {
        document.getElementById('namefilter').value = '';
        Table.filter(document.getElementById('namefilter'),document.getElementById('namefilter'));
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
        document.getElementById('uniquefilter').value = '';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
        document.getElementById('levelfilter').value = '';
        Table.filter(document.getElementById('levelfilter'),document.getElementById('levelfilter'));
        document.getElementById('levelfiltersec').value = '';
        Table.filter(document.getElementById('levelfiltersec'),document.getElementById('levelfilter'));
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
</script>


    <?

    if ($user && $user->id == 6302)
    {
        die;    }

}
*/



function get_all_tags() {
  global $dbcon, $language;
  $all_tags_db = mysqli_query($dbcon, "SELECT * FROM StrategyTags");
  while ($this_tag = $all_tags_db->fetch_object())
  {
    $all_tags[$this_tag->id]['ID'] = $this_tag->id;
    $all_tags[$this_tag->id]['Name'] = _("Tag_".$this_tag->PO_Name);
    $all_tags[$this_tag->id]['Slug'] = $this_tag->Slug;
    $all_tags[$this_tag->id]['Access'] = $this_tag->Access;
    $all_tags[$this_tag->id]['Visible'] = $this_tag->Visible;
    $all_tags[$this_tag->id]['Color'] = $this_tag->Color;
    $all_tags[$this_tag->id]['Description'] = _("Tag_Desc_".$this_tag->PO_Name);
    $all_tags[$this_tag->id]['Active'] = 0;
  }
  return $all_tags;
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


function print_collection ($petdata, $title = "0", $viewusername = "") {
  // $title = which header is being used.
  // 0 = standard, "Your Pet Collection" or "Pet Collection of XZY" depending on $viewusername
  // 1 = no header bar
  // $viewusername = Decides what title header is written

    $dbcon = $GLOBALS['dbcon'];
    $petnext = $GLOBALS['petnext'];
    $language = $GLOBALS['language'];
    $user = $GLOBALS['user'];
    $all_pets = $GLOBALS['all_pets'];

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
                        $collection[$countcolpets]['Family'] = _("PetFamiliesHumanoid");
                        if ($ignorepet == FALSE) { $stats['Humanoid']++; }
                        break;
                    case "Dragonkin":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesDragonkin");
                        if ($ignorepet == FALSE) { $stats['Dragonkin']++; }
                        break;
                    case "Flying":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesFlying");
                        if ($ignorepet == FALSE) { $stats['Flying']++; }
                        break;
                    case "Undead":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesUndead");
                        if ($ignorepet == FALSE) { $stats['Undead']++; }
                        break;
                    case "Critter":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesCritter");
                        if ($ignorepet == FALSE) { $stats['Critter']++; }
                        break;
                    case "Magic":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesMagic");
                        if ($ignorepet == FALSE) { $stats['Magic']++; }
                        break;
                    case "Elemental":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesElemental");
                        if ($ignorepet == FALSE) { $stats['Elemental']++; }
                        break;
                    case "Beast":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesBeast");
                        if ($ignorepet == FALSE) { $stats['Beast']++; }
                        break;
                    case "Aquatic":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesAquatic");
                        if ($ignorepet == FALSE) { $stats['Aquatic']++; }
                        break;
                    case "Mechanical":
                        $collection[$countcolpets]['Family'] = _("PetFamiliesMechanical");
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
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesHumanoid") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesHumanoid") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_dragonkin() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesDragonkin") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesDragonkin") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_flying() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesFlying") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesFlying") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_undead() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesUndead") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesUndead") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_critter() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesCritter") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesCritter") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_magic() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesMagic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesMagic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_elemental() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesElemental") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesElemental") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_beast() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesBeast") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesBeast") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_aquatic() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesAquatic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesAquatic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_mechanic() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesMechanical") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesMechanical") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}


function filter_rare() {
    if (document.getElementById('qualityfilter').value == '<? echo _("QualityRare") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<? echo _("QualityRare") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}
function filter_uncommon() {
    if (document.getElementById('qualityfilter').value == '<? echo _("QualityUncommon") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<? echo _("QualityUncommon") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}
function filter_common() {
    if (document.getElementById('qualityfilter').value == '<? echo _("QualityCommon") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<? echo _("QualityCommon") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}
function filter_poor() {
    if (document.getElementById('qualityfilter').value == '<? echo _("QualityPoor") ?>') {
        document.getElementById('qualityfilter').value = '';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    } else {
        document.getElementById('qualityfilter').value = '<? echo _("QualityPoor") ?>';
        Table.filter(document.getElementById('qualityfilter'),document.getElementById('qualityfilter'));
    }
}


function filter_unique() {
    if (document.getElementById('uniquefilter').value == '<? echo _("FormComButtonNo") ?>') {
        document.getElementById('uniquefilter').value = '';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    } else {
        document.getElementById('uniquefilter').value = '<? echo _("FormComButtonNo") ?>';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    }
}
function filter_dupe() {
    if (document.getElementById('uniquefilter').value == '<? echo _("FormComButtonYes") ?>') {
        document.getElementById('uniquefilter').value = '';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    } else {
        document.getElementById('uniquefilter').value = '<? echo _("FormComButtonYes") ?>';
        Table.filter(document.getElementById('uniquefilter'),document.getElementById('uniquefilter'));
    }
}



function filter_collected() {
    if (document.getElementById('collectedfilter').value == '<? echo _("FormComButtonYes") ?>') {
        document.getElementById('collectedfilter').value = '';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    } else {
        document.getElementById('collectedfilter').value = '<? echo _("FormComButtonYes") ?>';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    }
}
function filter_missing() {
    if (document.getElementById('collectedfilter').value == '<? echo _("FormComButtonNo") ?>') {
        document.getElementById('collectedfilter').value = '';
        Table.filter(document.getElementById('collectedfilter'),document.getElementById('collectedfilter'));
    } else {
        document.getElementById('collectedfilter').value = '<? echo _("FormComButtonNo") ?>';
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
    document.getElementById('collectedfilter').value = '<? echo _("FormComButtonYes") ?>';
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
                    { x: 2, y: <? echo $stats['Unique'] ?>, color: "#0081f2", click: filter_unique },
                    { x: 1, y: <? echo $stats['Duplicates'] ?>, color: "#1aa10c", click: filter_dupe },
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
                    { x: 2, y: <? echo $stats['Maxed'] ?>, color: "#0081f2" },
                    { x: 1, y: <? echo $stats['NotMaxed'] ?>, color: "#1aa10c" },
                    ]
                }
            ]
        });

    chartUniquepets.render();

var chartFamilies = new CanvasJS.Chart("chartFamilies",
    {
    title:{
        text: "<? echo _("ColChartFamilies") ?>",
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
                {  y: <? echo $stats['Humanoid'] ?>, legendText: "<? echo _("PetFamiliesHumanoid") ?>: <? echo $stats['Humanoid'] ?>", color: "#08adff", click: filter_humanoid },
                {  y: <? echo $stats['Dragonkin'] ?>, legendText: "<? echo _("PetFamiliesDragonkin") ?>: <? echo $stats['Dragonkin'] ?>", color: "#59bc11", click: filter_dragonkin },
                {  y: <? echo $stats['Flying'] ?>, legendText: "<? echo _("PetFamiliesFlying") ?>: <? echo $stats['Flying'] ?>", color: "#d4ca4f", click: filter_flying },
                {  y: <? echo $stats['Undead'] ?>, legendText: "<? echo _("PetFamiliesUndead") ?>: <? echo $stats['Undead'] ?>", color: "#9f6c73", click: filter_undead },
                {  y: <? echo $stats['Critter'] ?>, legendText: "<? echo _("PetFamiliesCritter") ?>: <? echo $stats['Critter'] ?>", color: "#7c5943", click: filter_critter },
                {  y: <? echo $stats['Magic'] ?>, legendText: "<? echo _("PetFamiliesMagic") ?>: <? echo $stats['Magic'] ?>", color: "#7341ee", click: filter_magic },
                {  y: <? echo $stats['Elemental'] ?>, legendText: "<? echo _("PetFamiliesElemental") ?>: <? echo $stats['Elemental'] ?>", color: "#eb7012", click: filter_elemental },
                {  y: <? echo $stats['Beast'] ?>, legendText: "<? echo _("PetFamiliesBeast") ?>: <? echo $stats['Beast'] ?>", color: "#ec2b22", click: filter_beast },
                {  y: <? echo $stats['Aquatic'] ?>, legendText: "<? echo _("PetFamiliesAquatic") ?>: <? echo $stats['Aquatic'] ?>", color: "#08aab7", click: filter_aquatic },
                {  y: <? echo $stats['Mechanic'] ?>, legendText: "<? echo _("PetFamiliesMechanical") ?>: <? echo $stats['Mechanic'] ?>", color: "#7e776d", click: filter_mechanic }
           ]
        }
        ]
    });

    chartFamilies.render();

var chartQuality = new CanvasJS.Chart("chartQuality",
    {
    title:{
        text: "<? echo _("ColChartQuality") ?>",
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
                {  y: <? echo $stats['Rare'] ?>, legendText: "<? echo _("QualityRare") ?>: <? echo $stats['Rare'] ?>", color: "#0081f2", click: filter_rare },
                {  y: <? echo $stats['Uncommon'] ?>, legendText: "<? echo _("QualityUncommon") ?>: <? echo $stats['Uncommon'] ?>", color: "#1aa10c", click: filter_uncommon },
                {  y: <? echo $stats['Common'] ?>, legendText: "<? echo _("QualityCommon") ?>: <? echo $stats['Common'] ?>", color: "#efefef", click: filter_common },
                {  y: <? echo $stats['Poor'] ?>, legendText: "<? echo _("QualityPoor") ?>: <? echo $stats['Poor'] ?>", color: "#898989", click: filter_poor },
           ]
        }
        ]
    });

    chartQuality.render();


var chartCollected = new CanvasJS.Chart("chartCollected",
    {
    title:{
        text: "<? echo _("ColChartCollectedofall") ?>",
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
                {  y: <? echo $stats['Unique']; ?>, legendText: "<? echo _("ColChartCollected") ?>: <? echo $stats['Unique']; ?>", color: "#1aa10c", click: filter_collected },
                {  y: <? echo $stats['TotalPetsNum']-$stats['Unique']; ?>, legendText: "<? echo _("ColChartMissing") ?>: <? echo $stats['TotalPetsNum']-$stats['Unique'] ?>", color: "#898989", click: filter_missing }
           ]
        }
        ]
    });

    chartCollected.render();

    var chartLevels = new CanvasJS.Chart("chartLevels",
    {
    title:{
        text: "<? echo _("ColChartLevelDist") ?>",
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
            { x: 1, y: <? echo $stats[1] ?>, label: "1", color: "#97d4ff"},
            { x: 2, y: <? echo $stats[2] ?>,  label: "2", color: "#94d2ff"},
            { x: 3, y: <? echo $stats[3] ?>,  label: "3", color: "#8fd0fe"},
            { x: 4, y: <? echo $stats[4] ?>,  label: "4", color: "#8acdfe"},
            { x: 5, y: <? echo $stats[5] ?>,  label: "5", color: "#85cafd"},
            { x: 6, y: <? echo $stats[6] ?>, label: "6", color: "#7fc7fd"},
            { x: 7, y: <? echo $stats[7] ?>,  label: "7", color: "#78c3fc"},
            { x: 8, y: <? echo $stats[8] ?>, label: "8", color: "#72bffc"},
            { x: 9, y: <? echo $stats[9] ?>,  label: "9", color: "#6bbcfb"},
            { x: 10, y: <? echo $stats[10] ?>,  label: "10", color: "#63b7fa"},
            { x: 11, y: <? echo $stats[11] ?>,  label: "11", color: "#5cb3fa"},
            { x: 12, y: <? echo $stats[12] ?>,  label: "12", color: "#54aff9"},
            { x: 13, y: <? echo $stats[13] ?>, label: "13", color: "#4cabf8"},
            { x: 14, y: <? echo $stats[14] ?>,  label: "14", color: "#45a7f8"},
            { x: 15, y: <? echo $stats[15] ?>, label: "15", color: "#3da3f7"},
            { x: 16, y: <? echo $stats[16] ?>,  label: "16", color: "#359ef7"},
            { x: 17, y: <? echo $stats[17] ?>,  label: "17", color: "#2e9af6"},
            { x: 18, y: <? echo $stats[18] ?>,  label: "18", color: "#2796f5"},
            { x: 19, y: <? echo $stats[19] ?>,  label: "19", color: "#2193f5"},
            { x: 20, y: <? echo $stats[20] ?>, label: "20", color: "#1a8ff4"},
            { x: 21, y: <? echo $stats[21] ?>,  label: "21", color: "#148cf4"},
            { x: 22, y: <? echo $stats[22] ?>,  label: "22", color: "#0f89f3"},
            { x: 23, y: <? echo $stats[23] ?>, label: "23", color: "#0986f3"},
            { x: 24, y: <? echo $stats[24] ?>,  label: "24", color: "#0584f2"},
            { x: 25, y: <? echo $stats[25] ?>,  label: "25", color: "#0182f2"}
            ]
        }
        ]
    });

    chartLevels.render();

    var chartFamiliesC = new CanvasJS.Chart("chartFamiliesC",
    {
    title:{
        text: "<? echo _("ColChartFamilies") ?>",
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
                {  x: 1, y: <? echo $stats['Humanoid'] ?>, label: "<? echo _("PetFamiliesHumanoid") ?>", color: "#08adff", click: filter_humanoid },
                {  x: 2, y: <? echo $stats['Dragonkin'] ?>, label: "<? echo _("PetFamiliesDragonkin") ?>", color: "#59bc11", click: filter_dragonkin },
                {  x: 3, y: <? echo $stats['Flying'] ?>, label: "<? echo _("PetFamiliesFlying") ?>", color: "#d4ca4f ", click: filter_flying },
                {  x: 4, y: <? echo $stats['Undead'] ?>, label: "<? echo _("PetFamiliesUndead") ?>", color: "#9f6c73", click: filter_undead },
                {  x: 5, y: <? echo $stats['Critter'] ?>, label: "<? echo _("PetFamiliesCritter") ?>", color: "#7c5943", click: filter_critter },
                {  x: 6, y: <? echo $stats['Magic'] ?>, label: "<? echo _("PetFamiliesMagic") ?>", color: "#7341ee", click: filter_magic },
                {  x: 7, y: <? echo $stats['Elemental'] ?>, label: "<? echo _("PetFamiliesElemental") ?>", color: "#eb7012", click: filter_elemental },
                {  x: 8, y: <? echo $stats['Beast'] ?>, label: "<? echo _("PetFamiliesBeast") ?>", color: "#ec2b22", click: filter_beast },
                {  x: 9, y: <? echo $stats['Aquatic'] ?>, label: "<? echo _("PetFamiliesAquatic") ?>", color: "#08aab7", click: filter_aquatic },
                {  x: 10, y: <? echo $stats['Mechanic'] ?>, label: "<? echo _("PetFamiliesMechanical") ?>", color: "#7e776d", click: filter_mechanic }
            ]
        }
        ]
    });

    chartFamiliesC.render();

    var chartQualityC = new CanvasJS.Chart("chartQualityC",
    {
    title:{
        text: "<? echo _("ColChartQuality") ?>",
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
                {  x: 1, y: <? echo $stats['Poor'] ?>, label: "<? echo _("QualityPoor") ?>", color: "#898989", click: filter_poor},
                {  x: 2, y: <? echo $stats['Common'] ?>, label: "<? echo _("QualityCommon") ?>", color: "#efefef", click: filter_common},
                {  x: 3, y: <? echo $stats['Uncommon'] ?>, label: "<? echo _("QualityUncommon") ?>", color: "#1aa10c", click: filter_uncommon},
                {  x: 4, y: <? echo $stats['Rare'] ?>, label: "<? echo _("QualityRare") ?>", color: "#0081f2", click: filter_rare}
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

<? if ($title == "0") { ?>
    <tr class="profile">
        <th colspan="3" width="5" class="profile">
            <table>
                <tr>
                    <td><img src="images/headericon_collection.png"></td>
                    <td><img src="images/blank.png" width="5" height="1"></td>
                    <td><p class="blogodd"><span style="white-space: nowrap;"><b><? if ($viewusername) { echo _("ColTableTitleWName")." ".$viewusername; } else { echo _("ColTableTitleNName"); } ?></span></td>
                </tr>
            </table>
        </th>
    </tr>
<? } ?>
    <tr class="profile">
        <td class="collectionborder" width="50%" valign="top">
            <table>
                <tr valign="top">
                    <td class="collection"><table><tr><td><img src="images/blank.png" width="165" height="22"></td></tr><tr><td align="right"><p class="collectionhuge"><span class="count"><? echo $stats['Unique']; ?></p></span></td></tr></table></td>
                    <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="line-height: 190%; text-align: right;"><? echo _("ColTableChartUniqueSingle") ?>: <span class="count"><? echo $stats['Unique']; ?></span><br><? echo _("ColTableChartDupes") ?>: <span class="count"><? echo $stats['Duplicates'] ?></span></div></td>
                    <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 180px;" id="chartAllpets"></div></td>
                </tr>
                <tr>
                    <td align="right" style="padding-right: 15px;"><p class="blogodd"><? echo _("ColTableChartUnique") ?></p></td>
                </tr>
            </table>
        </td>

        <td class="collectionborder" width="50%" valign="top">
            <table>
                <tr valign="top">
                    <td class="collection"><table><tr><td><img src="images/blank.png" width="165" height="22"></td></tr><tr><td align="right"><p class="collectionhuge"><span class="count"><? echo $stats['Unique']+$stats['Duplicates']; ?></p></span></td></tr></table></td>
                    <td class="collection" align="right"><div style="white-space:nowrap" class="ttmaximized"><p class="blogodd" style="line-height: 190%; text-align: right;"><? echo _("ColTableChartMaxed") ?> <span class="count"><? echo $stats['Maxed']; ?></span><br></div><div style="white-space:nowrap" class="ttnotmaxed"><p class="blogodd" style="line-height: 190%; text-align: right;"><? echo _("ColTableChartNotMaxed") ?> <span class="count"><? echo $stats['NotMaxed']; ?></span></div></td>
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
                    <td align="right" style="padding-right: 15px;"><p class="blogodd"><? echo _("ColTableChartAllpets") ?></p></td>
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
                        <button tabindex="1" type="submit" onclick="LevelStats()" id="ButtonLevels" class="statistics statisticsactive"><? echo _("ColChartLevelDist") ?></button>
                        <button tabindex="2" type="submit" onclick="FamilyStats()" id="ButtonFamily" class="statistics"><? echo _("ColChartFamilies") ?></button>
                        <button tabindex="3" type="submit" onclick="QualityStats()" id="ButtonQuality" class="statistics"><? echo _("ColChartQuality") ?></button>
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

                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="300"><p class="table-sortable-black" style="margin-left: 15px;"><? echo _("PetTableName") ?></p></th>
                                <th align="center" class="petlistheaderfirst table-sortable:numeric"><p class="table-sortable-black"><? echo _("ColChartLevel") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartQuality") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartBreed") ?></th>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><? echo _("ColChartFamily") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColTableChartDupes") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartTrade") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartCollected") ?></th>
                        </tr>

                            <tr>
                                <th align="left" class="petlistheadersecond" width="300">
                                    <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:100px;" id="levelfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("QualityRare") ?>"><? echo _("QualityRare") ?></option>
                                        <option class="petselect" value="<? echo _("QualityUncommon") ?>"><? echo _("QualityUncommon") ?></option>
                                        <option class="petselect" value="<? echo _("QualityCommon") ?>"><? echo _("QualityCommon") ?></option>
                                        <option class="petselect" value="<? echo _("QualityPoor") ?>"><? echo _("QualityPoor") ?></option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:70px;" id="breedfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesHumanoid") ?>"><? echo _("PetFamiliesHumanoid") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesDragonkin") ?>"><? echo _("PetFamiliesDragonkin") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesFlying") ?>"><? echo _("PetFamiliesFlying") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesUndead") ?>"><? echo _("PetFamiliesUndead") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesCritter") ?>"><? echo _("PetFamiliesCritter") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesMagic") ?>"><? echo _("PetFamiliesMagic") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesElemental") ?>"><? echo _("PetFamiliesElemental") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesBeast") ?>"><? echo _("PetFamiliesBeast") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesAquatic") ?>"><? echo _("PetFamiliesAquatic") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesMechanical") ?>"><? echo _("PetFamiliesMechanical") ?></option>
                                    </select>
                                </th>
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="uniquefilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>0;}"><? echo _("FormComButtonYes"); ?></option>
                                        <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
                                        <option class="petselect" value=2>2</option>
                                        <option class="petselect" value="3">3</option>
                                    </select>
                                </th>
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="tradeablefilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("FormComButtonYes"); ?>"><? echo _("FormComButtonYes"); ?></option>
                                        <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
                                        <option class="petselect" value="N/A">N/A</option>
                                    </select>
                                </th>
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="collectedfilter" onchange="Table.filter(this,this);">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option selected class="petselect" value="<? echo _("FormComButtonYes"); ?>"><? echo _("FormComButtonYes"); ?></option>
                                        <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
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
                                <td class="petlist" align="left" style="padding-left: 12px;"><div style="white-space:nowrap"><a class="petlist" href="http://<? echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<? echo $pet['PetID'] ?>" target="_blank"><? echo $pet['Name'] ?></a></div></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Level'] == 0) echo "-";
                                    else echo $pet['Level']; ?></td>
                                <td align="center" class="petlist"><p><?
                                    if ($pet['Quality'] == 3) { echo '<font color="#0058a5">'._("QualityRare"); }
                                    if ($pet['Quality'] == 2) { echo '<font color="#147e09">'._("QualityUncommon"); }
                                    if ($pet['Quality'] == 1) { echo '<font color="#ffffff">'._("QualityCommon"); }
                                    if ($pet['Quality'] == 0) { echo '<font color="#4d4d4d">'._("QualityPoor"); }
                                    if ($pet['Quality'] == 22) { echo '<font color="#000000">-'; } ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><? echo $pet['Breed'] ?></td>
                                <td align="left" class="petlist" style="padding-left: 12px;"><p class="blogodd"><? echo $pet['Family'] ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Duplicate'] == TRUE) echo $pet['Dupecount'];
                                    else echo _("FormComButtonNo"); ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Cageable'] == "1") echo _("FormComButtonYes");
                                    else if ($pet['Cageable'] == "2") echo _("FormComButtonNo");
                                    else if ($pet['Cageable'] == "0") echo "N/A"; ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Collected'] == TRUE) echo _("FormComButtonYes");
                                    else echo _("FormComButtonNo"); ?></td>
                            </tr>
                        <? } ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                <td colspan="2" align="center"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                                <td colspan="2" align="left" class="table-page:next" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                <td align="right"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><? echo _("ColTableReset") ?></a></div></td>
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
        <td><p class="blogodd"><? echo _("ColChartMissing") ?></td>
        <td style="width: 15px;"></td>
        <td style="width: 18px; background-color: #ccca92"> </td>
        <td><p class="blogodd"><? echo _("ColTableChartDupes") ?></td>
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
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesHumanoid") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesHumanoid") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_dragonkin() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesDragonkin") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesDragonkin") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_flying() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesFlying") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesFlying") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_undead() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesUndead") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesUndead") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_critter() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesCritter") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesCritter") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_magic() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesMagic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesMagic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_elemental() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesElemental") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesElemental") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_beast() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesBeast") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesBeast") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_aquatic() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesAquatic") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesAquatic") ?>';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    }
}
function filter_mechanic() {
    if (document.getElementById('familiesfilter').value == '<? echo _("PetFamiliesMechanical") ?>') {
        document.getElementById('familiesfilter').value = '';
        Table.filter(document.getElementById('familiesfilter'),document.getElementById('familiesfilter'));
    } else {
        document.getElementById('familiesfilter').value = '<? echo _("PetFamiliesMechanical") ?>';
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
    document.getElementById('tradeablefilter').value = '<? echo _("FormComButtonYes"); ?>';
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
        { y: <? echo $stats_1['Unique'] ?>, color: "<? echo $stats_1['Color'] ?>", label: " " }
      ]
      },
      {
        type: "stackedBar100",
              name: "Not Owned",
        dataPoints: [
          { y: <? echo count($all_pets)-$stats_1['Unique'] ?>, color: "#8f8f8f", label: " "  }
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
        { y: <? echo $stats_1['Maxed'] ?>, color: "<? echo $stats_1['Color'] ?>", label: " " }
      ]
      },
      {
        type: "stackedBar100",
              name: "Not Maximized",
        dataPoints: [
          { y: <? echo $stats_1['NotMaxed'] ?>, color: "#8f8f8f", label: " "  }
        ]
      }
    ]
  });
    chart_maxpets_1.render();    
    
    
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
        { y: <? echo round($stats_1['LevelAverage'],2) ?>, color: "<? echo $stats_1['Color'] ?>", label: " " }
      ]
      },
      {
        type: "stackedBar100",
              name: "",
        dataPoints: [
          { y: <? echo 25-round($stats_1['LevelAverage'],2) ?>, color: "#8f8f8f", label: " "  }
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
          { y: <? echo count($all_pets)-$stats_2['Unique'] ?>, color: "#8f8f8f", label: " "  }
        ]
      },
{
      type: "stackedBar100",
          name: "Unique pets",
      dataPoints: [
        { y: <? echo $stats_2['Unique'] ?>, color: "<? echo $stats_2['Color'] ?>", label: " " }
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
          { y: <? echo $stats_2['NotMaxed'] ?>, color: "#8f8f8f", label: " "  }
        ]
      },
{
      type: "stackedBar100",
          name: "Maximized (Level 25 Rare)",
      dataPoints: [
        { y: <? echo $stats_2['Maxed'] ?>, color: "<? echo $stats_2['Color'] ?>", label: " " }
      ]
      }
    ]
  });
    chart_maxpets_2.render();    
    
    
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
          { y: <? echo 25-round($stats_2['LevelAverage'],2) ?>, color: "#8f8f8f", label: " "  }
        ]
      },
{
      type: "stackedBar100",
          name: "Average Level",
      dataPoints: [
        { y: <? echo round($stats_2['LevelAverage'],2) ?>, color: "<? echo $stats_2['Color'] ?>", label: " " }
      ]
      }
    ]
  });
    chart_lvlaverage_2.render();    
    
    
    

   

    var chartLevels = new CanvasJS.Chart("chartLevels",
    {
    title:{
        text: "<? echo _("ColChartLevelDist") ?>",
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
            toolTipContent: "Level {label}: {y} (<? echo $chardata_avatar_1['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
            { x: 1, y: <? echo $stats_1[1] ?>, label: "1", color: "<? echo $stats_1['Color'] ?>"},
            { x: 2, y: <? echo $stats_1[2] ?>,  label: "2", color: "<? echo $stats_1['Color'] ?>"},
            { x: 3, y: <? echo $stats_1[3] ?>,  label: "3", color: "<? echo $stats_1['Color'] ?>"},
            { x: 4, y: <? echo $stats_1[4] ?>,  label: "4", color: "<? echo $stats_1['Color'] ?>"},
            { x: 5, y: <? echo $stats_1[5] ?>,  label: "5", color: "<? echo $stats_1['Color'] ?>"},
            { x: 6, y: <? echo $stats_1[6] ?>, label: "6", color: "<? echo $stats_1['Color'] ?>"},
            { x: 7, y: <? echo $stats_1[7] ?>,  label: "7", color: "<? echo $stats_1['Color'] ?>"},
            { x: 8, y: <? echo $stats_1[8] ?>, label: "8", color: "<? echo $stats_1['Color'] ?>"},
            { x: 9, y: <? echo $stats_1[9] ?>,  label: "9", color: "<? echo $stats_1['Color'] ?>"},
            { x: 10, y: <? echo $stats_1[10] ?>,  label: "10", color: "<? echo $stats_1['Color'] ?>"},
            { x: 11, y: <? echo $stats_1[11] ?>,  label: "11", color: "<? echo $stats_1['Color'] ?>"},
            { x: 12, y: <? echo $stats_1[12] ?>,  label: "12", color: "<? echo $stats_1['Color'] ?>"},
            { x: 13, y: <? echo $stats_1[13] ?>, label: "13", color: "<? echo $stats_1['Color'] ?>"},
            { x: 14, y: <? echo $stats_1[14] ?>,  label: "14", color: "<? echo $stats_1['Color'] ?>"},
            { x: 15, y: <? echo $stats_1[15] ?>, label: "15", color: "<? echo $stats_1['Color'] ?>"},
            { x: 16, y: <? echo $stats_1[16] ?>,  label: "16", color: "<? echo $stats_1['Color'] ?>"},
            { x: 17, y: <? echo $stats_1[17] ?>,  label: "17", color: "<? echo $stats_1['Color'] ?>"},
            { x: 18, y: <? echo $stats_1[18] ?>,  label: "18", color: "<? echo $stats_1['Color'] ?>"},
            { x: 19, y: <? echo $stats_1[19] ?>,  label: "19", color: "<? echo $stats_1['Color'] ?>"},
            { x: 20, y: <? echo $stats_1[20] ?>, label: "20", color: "<? echo $stats_1['Color'] ?>"},
            { x: 21, y: <? echo $stats_1[21] ?>,  label: "21", color: "<? echo $stats_1['Color'] ?>"},
            { x: 22, y: <? echo $stats_1[22] ?>,  label: "22", color: "<? echo $stats_1['Color'] ?>"},
            { x: 23, y: <? echo $stats_1[23] ?>, label: "23", color: "<? echo $stats_1['Color'] ?>"},
            { x: 24, y: <? echo $stats_1[24] ?>,  label: "24", color: "<? echo $stats_1['Color'] ?>"},
            { x: 25, y: <? echo $stats_1[25] ?>,  label: "25", color: "<? echo $stats_1['Color'] ?>"}
            ]
        },
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "10",
            indexLabelPlacement: "inside",
            toolTipContent: "Level {label}: {y} (<? echo $chardata_avatar_2['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
            { x: 1, y: <? echo $stats_2[1] ?>, label: "1", color: "<? echo $stats_2['Color'] ?>"},
            { x: 2, y: <? echo $stats_2[2] ?>,  label: "2", color: "<? echo $stats_2['Color'] ?>"},
            { x: 3, y: <? echo $stats_2[3] ?>,  label: "3", color: "<? echo $stats_2['Color'] ?>"},
            { x: 4, y: <? echo $stats_2[4] ?>,  label: "4", color: "<? echo $stats_2['Color'] ?>"},
            { x: 5, y: <? echo $stats_2[5] ?>,  label: "5", color: "<? echo $stats_2['Color'] ?>"},
            { x: 6, y: <? echo $stats_2[6] ?>, label: "6", color: "<? echo $stats_2['Color'] ?>"},
            { x: 7, y: <? echo $stats_2[7] ?>,  label: "7", color: "<? echo $stats_2['Color'] ?>"},
            { x: 8, y: <? echo $stats_2[8] ?>, label: "8", color: "<? echo $stats_2['Color'] ?>"},
            { x: 9, y: <? echo $stats_2[9] ?>,  label: "9", color: "<? echo $stats_2['Color'] ?>"},
            { x: 10, y: <? echo $stats_2[10] ?>,  label: "10", color: "<? echo $stats_2['Color'] ?>"},
            { x: 11, y: <? echo $stats_2[11] ?>,  label: "11", color: "<? echo $stats_2['Color'] ?>"},
            { x: 12, y: <? echo $stats_2[12] ?>,  label: "12", color: "<? echo $stats_2['Color'] ?>"},
            { x: 13, y: <? echo $stats_2[13] ?>, label: "13", color: "<? echo $stats_2['Color'] ?>"},
            { x: 14, y: <? echo $stats_2[14] ?>,  label: "14", color: "<? echo $stats_2['Color'] ?>"},
            { x: 15, y: <? echo $stats_2[15] ?>, label: "15", color: "<? echo $stats_2['Color'] ?>"},
            { x: 16, y: <? echo $stats_2[16] ?>,  label: "16", color: "<? echo $stats_2['Color'] ?>"},
            { x: 17, y: <? echo $stats_2[17] ?>,  label: "17", color: "<? echo $stats_2['Color'] ?>"},
            { x: 18, y: <? echo $stats_2[18] ?>,  label: "18", color: "<? echo $stats_2['Color'] ?>"},
            { x: 19, y: <? echo $stats_2[19] ?>,  label: "19", color: "<? echo $stats_2['Color'] ?>"},
            { x: 20, y: <? echo $stats_2[20] ?>, label: "20", color: "<? echo $stats_2['Color'] ?>"},
            { x: 21, y: <? echo $stats_2[21] ?>,  label: "21", color: "<? echo $stats_2['Color'] ?>"},
            { x: 22, y: <? echo $stats_2[22] ?>,  label: "22", color: "<? echo $stats_2['Color'] ?>"},
            { x: 23, y: <? echo $stats_2[23] ?>, label: "23", color: "<? echo $stats_2['Color'] ?>"},
            { x: 24, y: <? echo $stats_2[24] ?>,  label: "24", color: "<? echo $stats_2['Color'] ?>"},
            { x: 25, y: <? echo $stats_2[25] ?>,  label: "25", color: "<? echo $stats_2['Color'] ?>"}
            ]
        }
        ]
    });

    chartLevels.render();
    
    var chartFamiliesC = new CanvasJS.Chart("chartFamiliesC",
    {
    title:{
        text: "<? echo _("ColChartFamilies") ?>",
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
            toolTipContent: "{label}: {y} (<? echo $chardata_avatar_1['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <? echo $stats_1['Humanoid'] ?>, label: "<? echo _("PetFamiliesHumanoid") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_humanoid },
                {  x: 2, y: <? echo $stats_1['Dragonkin'] ?>, label: "<? echo _("PetFamiliesDragonkin") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_dragonkin },
                {  x: 3, y: <? echo $stats_1['Flying'] ?>, label: "<? echo _("PetFamiliesFlying") ?>", color: "<? echo $stats_1['Color'] ?> ", click: filter_flying },
                {  x: 4, y: <? echo $stats_1['Undead'] ?>, label: "<? echo _("PetFamiliesUndead") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_undead },
                {  x: 5, y: <? echo $stats_1['Critter'] ?>, label: "<? echo _("PetFamiliesCritter") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_critter },
                {  x: 6, y: <? echo $stats_1['Magic'] ?>, label: "<? echo _("PetFamiliesMagic") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_magic },
                {  x: 7, y: <? echo $stats_1['Elemental'] ?>, label: "<? echo _("PetFamiliesElemental") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_elemental },
                {  x: 8, y: <? echo $stats_1['Beast'] ?>, label: "<? echo _("PetFamiliesBeast") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_beast },
                {  x: 9, y: <? echo $stats_1['Aquatic'] ?>, label: "<? echo _("PetFamiliesAquatic") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_aquatic },
                {  x: 10, y: <? echo $stats_1['Mechanic'] ?>, label: "<? echo _("PetFamiliesMechanical") ?>", color: "<? echo $stats_1['Color'] ?>", click: filter_mechanic }
            ]
        },
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y} (<? echo $chardata_avatar_2['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <? echo $stats_2['Humanoid'] ?>, label: "<? echo _("PetFamiliesHumanoid") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_humanoid },
                {  x: 2, y: <? echo $stats_2['Dragonkin'] ?>, label: "<? echo _("PetFamiliesDragonkin") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_dragonkin },
                {  x: 3, y: <? echo $stats_2['Flying'] ?>, label: "<? echo _("PetFamiliesFlying") ?>", color: "<? echo $stats_2['Color'] ?> ", click: filter_flying },
                {  x: 4, y: <? echo $stats_2['Undead'] ?>, label: "<? echo _("PetFamiliesUndead") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_undead },
                {  x: 5, y: <? echo $stats_2['Critter'] ?>, label: "<? echo _("PetFamiliesCritter") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_critter },
                {  x: 6, y: <? echo $stats_2['Magic'] ?>, label: "<? echo _("PetFamiliesMagic") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_magic },
                {  x: 7, y: <? echo $stats_2['Elemental'] ?>, label: "<? echo _("PetFamiliesElemental") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_elemental },
                {  x: 8, y: <? echo $stats_2['Beast'] ?>, label: "<? echo _("PetFamiliesBeast") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_beast },
                {  x: 9, y: <? echo $stats_2['Aquatic'] ?>, label: "<? echo _("PetFamiliesAquatic") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_aquatic },
                {  x: 10, y: <? echo $stats_2['Mechanic'] ?>, label: "<? echo _("PetFamiliesMechanical") ?>", color: "<? echo $stats_2['Color'] ?>", click: filter_mechanic }
            ]
        }
        ]
    });

    chartFamiliesC.render();

    var chartQualityC = new CanvasJS.Chart("chartQualityC",
    {
    title:{
        text: "<? echo _("ColChartQuality") ?>",
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
            toolTipContent: "{label}: {y} (<? echo $chardata_avatar_1['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <? echo $stats_1['Poor'] ?>, label: "<? echo _("QualityPoor") ?>", color: "<? echo $stats_1['Color'] ?>"},
                {  x: 2, y: <? echo $stats_1['Common'] ?>, label: "<? echo _("QualityCommon") ?>", color: "<? echo $stats_1['Color'] ?>"},
                {  x: 3, y: <? echo $stats_1['Uncommon'] ?>, label: "<? echo _("QualityUncommon") ?>", color: "<? echo $stats_1['Color'] ?>"},
                {  x: 4, y: <? echo $stats_1['Rare'] ?>, label: "<? echo _("QualityRare") ?>", color: "<? echo $stats_1['Color'] ?>"}
            ]
        },
        {
            indexLabel: "{y}",
            indexLabelFontFamily: "MuseoSans-500",
            indexLabelFontColor : "black",
            indexLabelFontSize : "12",
            indexLabelPlacement: "inside",
            toolTipContent: "{label}: {y} (<? echo $chardata_avatar_2['character']['name'] ?>)",
            indexLabelMaxWidth: 50,
            dataPoints: [
                {  x: 1, y: <? echo $stats_2['Poor'] ?>, label: "<? echo _("QualityPoor") ?>", color: "<? echo $stats_2['Color'] ?>"},
                {  x: 2, y: <? echo $stats_2['Common'] ?>, label: "<? echo _("QualityCommon") ?>", color: "<? echo $stats_2['Color'] ?>"},
                {  x: 3, y: <? echo $stats_2['Uncommon'] ?>, label: "<? echo _("QualityUncommon") ?>", color: "<? echo $stats_2['Color'] ?>"},
                {  x: 4, y: <? echo $stats_2['Rare'] ?>, label: "<? echo _("QualityRare") ?>", color: "<? echo $stats_2['Color'] ?>"}
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
                    <td class="collection" align="right"><p class="blogodd" style="font-size: 24px; font-weight: bold"><span class="count"><? echo $stats_1['Unique'] ?></p></span></td>
                    <td class="collection"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 24px"><? echo _("ColTableChartUnique") ?></div></td>
                    <td valign="top"><div style="width: 200px;" id="chart_allpets_1"></div></td>
                </tr>
                
                <tr valign="top">
                    <td class="collection" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><? echo $stats_1['Maxed']; ?></p></span></td>
                    <td class="collection"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("Maximized") ?></div></td>
                    <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 200px;" id="chart_maxpets_1"></div></td>
                </tr>
                <tr valign="top">
                    <td class="collection" style="padding-top: 11px" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class=""><? echo round($stats_1['LevelAverage'], 2); ?></p></span></td>
                    <td class="collection" style="padding-top: 11px"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("Average Level") ?></div></td>
                    <td valign="top"><div style="width: 200px;" id="chart_lvlaverage_1"></div></td>
                </tr>
                
                <tr valign="top">
                    <td class="collection" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><? echo $stats_1['Maxed']+$stats_1['NotMaxed']; ?></p></span></td>
                    <td class="collection"  ><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("ColTableChartAllpets") ?></div></td>
                </tr>
                <tr valign="top">
                    <td class="collection" style="padding-top: 5px" align="right"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><? echo $stats_1['Duplicates']; ?></p></span></td>
                    <td class="collection"style="padding-top: 5px" ><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("ColTableChartDupes") ?></div></td>
                </tr>
                
            </table>
        </td>

        <td class="collectionborder" width="50%" valign="top" align="right">
            <table cellspacing="0" cellpadding="0" align="right">
                <tr valign="top">
                  <td valign="top" align="right"><div style="width: 200px;" id="chart_allpets_2"></div></td>
                  <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 24px"><? echo _("ColTableChartUnique") ?></div></td>
                  <td class="collection" align="left"><p class="blogodd" style="font-size: 24px; font-weight: bold"><span class="count"><? echo $stats_2['Unique'] ?></p></span></td> 
                </tr>
                
                <tr valign="top">
                  <td valign="top"><img src="images/blank.png" width="1" height="5"><br><div style="width: 200px;" id="chart_maxpets_2"></div></td>
                  <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("Maximized") ?></div></td>
                  <td class="collection" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><? echo $stats_2['Maxed']; ?></p></span></td>
                </tr>
                <tr valign="top">
                  <td valign="top" align="right"><div style="width: 200px;" id="chart_lvlaverage_2"></div></td>
                  <td class="collection" align="right" style="padding-top: 11px"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("Average Level") ?></div></td>
                  <td class="collection" style="padding-top: 11px" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class=""><? echo round($stats_2['LevelAverage'], 2); ?></p></span></td> 
                </tr>
                
                <tr valign="top">
                  <td></td>
                    <td class="collection" align="right"><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("ColTableChartAllpets") ?></div></td>
                    <td class="collection" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><? echo $stats_2['Maxed']+$stats_2['NotMaxed']; ?></p></span></td>
                </tr>
                <tr valign="top">
                  <td></td>
                    <td class="collection"  align="right" style="padding-top: 5px" ><div style="white-space:nowrap"><p class="blogodd" style="font-size: 18px"><? echo _("ColTableChartDupes") ?></div></td>
                    <td class="collection" style="padding-top: 5px" align="left"><p class="blogodd" style="font-size: 18px; font-weight: bold"><span class="count"><? echo $stats_2['Duplicates']; ?></p></span></td>
                </tr>
                

            </table>
        </td>
    </tr>

    <tr class="profile">
        <td class="collectionbordertwo" width="100%" valign="top" colspan="2">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="100" valign="top">
                        <button tabindex="1" type="submit" onclick="LevelStats()" id="ButtonLevels" class="statistics statisticsactive"><? echo _("ColChartLevelDist") ?></button>
                        <button tabindex="2" type="submit" onclick="FamilyStats()" id="ButtonFamily" class="statistics"><? echo _("ColChartFamilies") ?></button>
                        <button tabindex="3" type="submit" onclick="QualityStats()" id="ButtonQuality" class="statistics"><? echo _("ColChartQuality") ?></button>
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
                                      <a class="langselector" onclick="quickfilter('only_1','')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Pets <? echo $chardata_avatar_1['character']['name']; ?> owns but <? echo $chardata_avatar_2['character']['name']; ?> doesn't</a></br>
                                      <a class="langselector" onclick="quickfilter('only_2','')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Pets <? echo $chardata_avatar_2['character']['name']; ?> owns but <? echo $chardata_avatar_1['character']['name']; ?> doesn't</a></br>
                                      <br>
                                      <a class="langselector" onclick="quickfilter('only_1','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable pets <? echo $chardata_avatar_1['character']['name']; ?> owns but <? echo $chardata_avatar_2['character']['name']; ?> doesn't</a></br>
                                      <a class="langselector" onclick="quickfilter('only_2','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable pets <? echo $chardata_avatar_2['character']['name']; ?> owns but <? echo $chardata_avatar_1['character']['name']; ?> doesn't</a></br>
                                      <br>
                                      <a class="langselector" onclick="quickfilter('dupes_1','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable duplicates <? echo $chardata_avatar_1['character']['name']; ?> owns but <? echo $chardata_avatar_2['character']['name']; ?> doesn't</a></br>
                                      <a class="langselector" onclick="quickfilter('dupes_2','t')" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Tradeable duplicates <? echo $chardata_avatar_2['character']['name']; ?> owns but <? echo $chardata_avatar_1['character']['name']; ?> doesn't</a></br>
                                      <br>
                                      <a class="langselector" onclick="filter_reset()" style="cursor: pointer; text-transform: none; font-weight: regular; text-shadow: 0px">Reset Filters</a></br>
                                      
                                    </span>
                                  </div>
                                
                                </th>
                                <th colspan="4" class="petlistheaderfirst" style="background-color: <? echo $stats_1['Color']; ?>; border: 1px solid <? echo $stats_1['Color']; ?>"><p class="blogodd"><? echo $chardata_avatar_1['character']['name']; ?></p></th>
                                <th class="petlistheaderfirst"></th>
                                <th colspan="4" class="petlistheaderfirst" style="background-color: <? echo $stats_2['Color']; ?>; border: 1px solid <? echo $stats_2['Color']; ?>"><p class="blogodd"><? echo $chardata_avatar_2['character']['name']; ?></p></th>
                            </tr>
                            
                            <tr>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic" width="300"><p class="table-sortable-black" style="margin-left: 15px;"><? echo _("PetTableName") ?></p></th>
                                <th align="left" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 25px;"><? echo _("ColChartFamily") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartTrade") ?></th>
                                
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-left: 1px solid <? echo $stats_1['Color']; ?>"><p class="table-sortable-black"><? echo _("ColChartLevel") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartQuality") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartBreed") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-right: 1px solid <? echo $stats_1['Color']; ?>"><p class="table-sortable-black"><? echo _("Copies") ?></th>
                                <th class="petlistheaderfirst"></th>
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-left: 1px solid <? echo $stats_2['Color']; ?>"><p class="table-sortable-black"><? echo _("ColChartLevel") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartQuality") ?></th>
                                <th align="center" class="petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black"><? echo _("ColChartBreed") ?></th>                          
                                <th align="center" class="petlistheaderfirst table-sortable:numeric" style="border-right: 1px solid <? echo $stats_2['Color']; ?>"><p class="table-sortable-black"><? echo _("Copies") ?></th>
                            </tr>

                            <tr>
                                <th align="left" class="petlistheadersecond" width="300">
                                    <input class="petselect" name="filter" size="25" id="namefilter" onkeyup="Table.filter(this,this)">
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:150px;" id="familiesfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesHumanoid") ?>"><? echo _("PetFamiliesHumanoid") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesDragonkin") ?>"><? echo _("PetFamiliesDragonkin") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesFlying") ?>"><? echo _("PetFamiliesFlying") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesUndead") ?>"><? echo _("PetFamiliesUndead") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesCritter") ?>"><? echo _("PetFamiliesCritter") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesMagic") ?>"><? echo _("PetFamiliesMagic") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesElemental") ?>"><? echo _("PetFamiliesElemental") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesBeast") ?>"><? echo _("PetFamiliesBeast") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesAquatic") ?>"><? echo _("PetFamiliesAquatic") ?></option>
                                        <option class="petselect" value="<? echo _("PetFamiliesMechanical") ?>"><? echo _("PetFamiliesMechanical") ?></option>
                                    </select>
                                </th>
                                
                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:90px;" id="tradeablefilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("FormComButtonYes"); ?>"><? echo _("FormComButtonYes"); ?></option>
                                        <option class="petselect" value="<? echo _("FormComButtonNo"); ?>"><? echo _("FormComButtonNo"); ?></option>
                                        <option class="petselect" value="N/A">N/A</option>
                                    </select>
                                </th>
                                
                                <th align="center" class="petlistheadersecond" style="border-left: 1px solid <? echo $stats_1['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="levelfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("QualityRare") ?>"><? echo _("QualityRare") ?></option>
                                        <option class="petselect" value="<? echo _("QualityUncommon") ?>"><? echo _("QualityUncommon") ?></option>
                                        <option class="petselect" value="<? echo _("QualityCommon") ?>"><? echo _("QualityCommon") ?></option>
                                        <option class="petselect" value="<? echo _("QualityPoor") ?>"><? echo _("QualityPoor") ?></option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:70px;" id="breedfilter" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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

                                <th align="center" class="petlistheadersecond" style="border-right: 1px solid <? echo $stats_1['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="dupes_filter_1" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="0">0</option>
                                        <option class="petselect" value="1">1</option>
                                        <option class="petselect" value="function(val){return parseFloat(val)>1;}">2+</option>
                                        <option class="petselect" value="3">3</option>
                                    </select>
                                </th>
                                
                                <th class="petlistheadersecond"></th>

                                <th align="center" class="petlistheadersecond" style="border-left: 1px solid <? echo $stats_2['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="levelfilter2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
                                        <option class="petselect" value="<? echo _("QualityRare") ?>"><? echo _("QualityRare") ?></option>
                                        <option class="petselect" value="<? echo _("QualityUncommon") ?>"><? echo _("QualityUncommon") ?></option>
                                        <option class="petselect" value="<? echo _("QualityCommon") ?>"><? echo _("QualityCommon") ?></option>
                                        <option class="petselect" value="<? echo _("QualityPoor") ?>"><? echo _("QualityPoor") ?></option>
                                        <option class="petselect" value="-">-</option>
                                    </select>
                                </th>

                                <th align="center" class="petlistheadersecond">
                                    <select class="petselect" style="width:70px;" id="breedfilter2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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
                                
                                <th align="center" class="petlistheadersecond" style="border-right: 1px solid <? echo $stats_2['Color']; ?>">
                                    <select class="petselect" style="width:100px;" id="dupes_filter_2" onchange="Table.filter(this,this)">
                                        <option class="petselect" value=""><? echo _("ColTableAll") ?></option>
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
                                <td class="petlist" align="left" style="padding-left: 12px;"><div style="white-space:nowrap"><a class="petlist" href="http://<? echo $GLOBALS['wowhdomain'] ?>.wowhead.com/npc=<? echo $pet['PetID'] ?>" target="_blank"><? echo $pet['Name'] ?></a></div></td>
                                <td align="left" class="petlist" style="padding-left: 12px;"><p class="blogodd"><? echo $pet['Family'] ?></td>
                                <td align="center" class="petlist"><p class="blogodd"><?
                                    if ($pet['Cageable'] == "1") echo _("FormComButtonYes");
                                    else if ($pet['Cageable'] == "2") echo _("FormComButtonNo");
                                    else if ($pet['Cageable'] == "0") echo "N/A"; ?></td>


                                <td align="center" class="petlist" style="border-left: 1px solid <? echo $stats_1['Color'];
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "; background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_1'] == 0) echo "";
                                    else echo $pet['Level_1']; ?></td>
                                
                                <td align="center" class="petlist" <? if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo 'style="background-color: #d8a0a0"'; ?>><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo '-';
                                    if ($pet['Quality_1'] == 3) { echo '<font color="#0058a5">'._("QualityRare"); }
                                    else if ($pet['Quality_1'] == 2) { echo '<font color="#147e09">'._("QualityUncommon"); }
                                    else if ($pet['Quality_1'] == 1) { echo '<font color="#ffffff">'._("QualityCommon"); }
                                    else if ($pet['Quality_1'] == 0 && $pet['Level_1'] != 0) { echo '<font color="#4d4d4d">'._("QualityPoor"); } ?></td>
                                
                                <td align="center" class="petlist" style="<?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_1'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_1'] == 0) echo "";
                                    else { echo $pet['Breed_1']; } ?></td>

                                <td align="center" class="petlist" style="border-right: 1px solid <? echo $stats_1['Color']; ?>; <?
                                    if ($pet['Position'] == 0 && $pet['Copies_1'] < 1) { echo "background-color: #d8a0a0"; ?>"><p class="blogodd">0<? }
                                    if ($pet['Position'] == 0 && $pet['Copies_1'] > 0) echo '"><p class="blogodd">'.$pet['Copies_1'];
                                    if ($pet['Position'] != 0 && $pet['Copies_1'] < 1) echo '">';
                                    if ($pet['Position'] != 0 && $pet['Copies_1'] > 0) echo '"><p class="blogodd">'.$pet['Copies_1']; ?></td>

                                <td></td>

                                <td align="center" class="petlist" style="border-left: 1px solid <? echo $stats_2['Color'];
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "; background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_2'] == 0) echo "";
                                    else echo $pet['Level_2']; ?></td>
                                
                                <td align="center" class="petlist" <? if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo 'style="background-color: #d8a0a0"'; ?>><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo '-';
                                    if ($pet['Quality_2'] == 3) { echo '<font color="#0058a5">'._("QualityRare"); }
                                    else if ($pet['Quality_2'] == 2) { echo '<font color="#147e09">'._("QualityUncommon"); }
                                    else if ($pet['Quality_2'] == 1) { echo '<font color="#ffffff">'._("QualityCommon"); }
                                    else if ($pet['Quality_2'] == 0 && $pet['Level_2'] != 0) { echo '<font color="#4d4d4d">'._("QualityPoor"); } ?></td>
                                
                                <td align="center" class="petlist" style="<?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "background-color: #d8a0a0"; ?>"><p class="blogodd"><?
                                    if ($pet['Position'] == 0 && $pet['Level_2'] == 0) echo "-";
                                    if ($pet['Position'] != 0 && $pet['Level_2'] == 0) echo "";
                                    else { echo $pet['Breed_2']; } ?></td>
                                
                                <td align="center" class="petlist" style="border-right: 1px solid <? echo $stats_2['Color']; ?>; <?
                                    if ($pet['Position'] == 0 && $pet['Copies_2'] < 1) { echo "background-color: #d8a0a0"; ?>"><p class="blogodd">0<? }
                                    if ($pet['Position'] == 0 && $pet['Copies_2'] > 0) echo '"><p class="blogodd">'.$pet['Copies_2'];
                                    if ($pet['Position'] != 0 && $pet['Copies_2'] < 1) echo '">';
                                    if ($pet['Position'] != 0 && $pet['Copies_2'] > 0) echo '"><p class="blogodd">'.$pet['Copies_2']; ?></td>
        
                            </tr>
                        <? }
                        } ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" align="right" class="table-page:previous" style="cursor:pointer;"><a class="wowhead" style="text-decoration: none;">&lt; &lt; </a></td>
                                <td colspan="3" align="center" style="border-top: 1px solid <? echo $stats_1['Color']; ?>"><div style="white-space:nowrap"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                                <td align="left" class="table-page:next" style="cursor:pointer; border-top: 1px solid <? echo $stats_1['Color']; ?>"><a class="wowhead" style="text-decoration: none;"> &gt; &gt;</td>
                                <td></td>
                                <td colspan="4" align="right" style="border-top: 1px solid <? echo $stats_2['Color']; ?>"><div style="white-space:nowrap; margin-right:10px;"><a class="wowhead" style="text-decoration: none; cursor: pointer" onclick="filter_reset()"><? echo _("ColTableReset") ?></a></div></td>
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
