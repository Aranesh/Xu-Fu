<?php
ini_set ('display_errors', 0);
include("../data/dbconnect.php");
include("functions.php");
require_once ('../data/blizzard_api.php');
require_once ('../classes/HTTP.php');
require_once ("../thirdparty/motranslator/vendor/autoload.php");
PhpMyAdmin\MoTranslator\Loader::loadFunctions();

$colerror = "";

$lang = strtolower($_GET['language']);
$user = $_GET['user'];

switch ($lang) {
    case "":
        $language = "en_US";
        $petnext = "Name";
        break;
    case "en":
        $language = "en_US";
        $petnext = "Name";
        break;
    case "de":
        $language = "de_DE";
        $petnext = "Name_".$language;
        break;
    case "fr":
        $language = "fr_FR";
        $petnext = "Name_".$language;
        break;
    case "it":
        $language = "it_IT";
        $petnext = "Name_".$language;
        break;
    case "es":
        $language = "es_ES";
        $petnext = "Name_".$language;
        break;
    case "pl":
        $language = "en_US";
        $petnext = "Name";
        break;
    case "pt":
        $language = "pt_PT";
        $petnext = "Name_".$language;
        break;
    case "ru":
        $language = "ru_RU";
        $petnext = "Name_".$language;
        break;
    case "ko":
        $language = "ko_KR";
        $petnext = "Name_".$language;
        break;
    case "zh":
        $language = "zh_TW";
        $petnext = "Name_".$language;
        break;
}
if (!$language) {
    $language = "en_US";
    $petnext = "Name";
}

// INIITIALIZE GETTEXT AND PULL LANGUAGE FILE
  _setlocale(LC_MESSAGES, $language);
  _textdomain('messages');
  _bindtextdomain('messages', __DIR__ . '/../Locale/');
  _bind_textdomain_codeset('messages', 'UTF-8');
set_language_vars($language);
// Get Collection from DB

if (!$user) {
    $region = \HTTP\argument_GET_or_default ('region', FALSE);
    $realm = \HTTP\argument_GET_or_default ('realm', FALSE);
    $name = \HTTP\argument_GET_or_default ('name', FALSE);
    $name = strtolower($name);
    $region = strtolower($region);
    $realm = strtolower($realm);
    
    if ($region != "kr" && $region != "tw" && $region != "us" && $region != "eu") {
        $colerror = "true";
    }
    if ($colerror != "true" && $realm && $name) {
        $petdata_source = blizzard_api_character_pets($region, $realm, $name, "../");
        if ($petdata_source == "error") {
            $colerror = "true";
        }
        else {
            $petdata = prepare_collection($petdata_source); 
        }
    }
}
    


if ($user) {

    $userdb = mysqli_query($dbcon, "SELECT * FROM Users WHERE id = '$user'");
    if (mysqli_num_rows($userdb) > "0"){
        $user = mysqli_fetch_object($userdb);
        $findcol = find_collection($user, 1);
        if ($findcol == "No Collection") {
            $colerror = "true";
        }
        else {
            $fp = fopen($findcol['Path'], 'r');
            $petdata = json_decode(fread($fp, filesize($findcol['Path'])), true);
        }
    }
    else {
        $colerror = "true";
    }
}

if (!$petdata){
    $colerror = "true";
}

if ($colerror == "true") {
    echo __("There was a problem exporting your collection. Please try again");
    die;
}

$all_pets = get_all_pets($petnext);
$collection = null;
$countcolpets = "0";
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

        // Check if pet is known in DB or not. If not, add placeholder and such for fields were no info is available
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
                    break;
                case "Dragonkin":
                    $collection[$countcolpets]['Family'] = __("Dragonkin");
                    break;
                case "Flying":
                    $collection[$countcolpets]['Family'] = __("Flying");
                    break;
                case "Undead":
                    $collection[$countcolpets]['Family'] = __("Undead");
                    break;
                case "Critter":
                    $collection[$countcolpets]['Family'] = __("Critter");
                    break;
                case "Magic":
                    $collection[$countcolpets]['Family'] = __("Magic");
                    break;
                case "Elemental":
                    $collection[$countcolpets]['Family'] = __("Elemental");
                    break;
                case "Beast":
                    $collection[$countcolpets]['Family'] = __("Beast");
                    break;
                case "Aquatic":
                    $collection[$countcolpets]['Family'] = __("Aquatic");
                    break;
                case "Mechanical":
                    $collection[$countcolpets]['Family'] = __("Mechanical");
                    break;
            }
        }
        
        if ($pet['Level'] == 25 && $pet['Quality'] == 3) {
            $collection[$countcolpets]['Maxed'] = TRUE;
        }
        if ($pet['Level'] < 25 || $pet['Quality'] < 3) {
            $collection[$countcolpets]['Maxed'] = FALSE;
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
        
        switch ($pet['Quality']) {
            case "0":
                $collection[$countcolpets]['QualityN'] = __("Poor");
                break;
            case "1":
                $collection[$countcolpets]['QualityN'] = __("Common");
                break;
            case "2":
                $collection[$countcolpets]['QualityN'] = __("Uncommon");
                break;
            case "3":
                $collection[$countcolpets]['QualityN'] = __("Rare");
                break;
        }
                
        if ($collection[$countcolpets]['InDB'] == TRUE) {
            $collection[$countcolpets]['Name'] = $all_pets[$pet['Species']]['Name'];
            if ($all_pets[$pet['Species']]['Cageable'] == "0" || $all_pets[$pet['Species']]['Cageable'] == "") $collection[$countcolpets]['Cageable'] = "N/A";
            if ($all_pets[$pet['Species']]['Cageable'] == "1") $collection[$countcolpets]['Cageable'] =  __("Yes");
            if ($all_pets[$pet['Species']]['Cageable'] == "2") $collection[$countcolpets]['Cageable'] = __("No");
        }
        $collection[$countcolpets]['Health'] = $all_pets[$pet['Species']]['Health'];
        $collection[$countcolpets]['Power'] = $all_pets[$pet['Species']]['Power'];
        $collection[$countcolpets]['Speed'] = $all_pets[$pet['Species']]['Speed'];
        $collection[$countcolpets]['Species'] = $all_pets[$pet['Species']]['Species'];
        $collection[$countcolpets]['PetID'] = $all_pets[$pet['Species']]['PetID'];
        $collection[$countcolpets]['Level'] = $pet['Level'];
        $collection[$countcolpets]['Quality'] = $pet['Quality'];
        $collection[$countcolpets]['Breed'] = $pet['Breed'];
        if ($all_pets[$pet['Species']]['Special'] == 1) {
            $collection[$countcolpets]['Breed'] = "-";
        }
        $collection[$countcolpets]['Collected'] = TRUE;
        $collection[$countcolpets]['CollectedN'] = __("Yes");

        $breedstring = "";
            if ($all_pets[$pet['Species']]['BB'] == 1) {
                $breedstring = "BB,";
            }
            if ($all_pets[$pet['Species']]['PP'] == 1) {
                $breedstring = $breedstring."PP,";
            }
            if ($all_pets[$pet['Species']]['SS'] == 1) {
                $breedstring = $breedstring."SS,";
            }
            if ($all_pets[$pet['Species']]['HH'] == 1) {
                $breedstring = $breedstring."HH,";
            }
            if ($all_pets[$pet['Species']]['HP'] == 1) {
                $breedstring = $breedstring."HP,";
            }
            if ($all_pets[$pet['Species']]['PS'] == 1) {
                $breedstring = $breedstring."PS,";
            }
            if ($all_pets[$pet['Species']]['HS'] == 1) {
                $breedstring = $breedstring."HS,";
            }
            if ($all_pets[$pet['Species']]['PB'] == 1) {
                $breedstring = $breedstring."PB,";
            }
            if ($all_pets[$pet['Species']]['SB'] == 1) {
                $breedstring = $breedstring."SB,";
            }
            if ($all_pets[$pet['Species']]['HB'] == 1) {
                $breedstring = $breedstring."HB,";
            }
        $collection[$countcolpets]['AvlBreeds'] = substr($breedstring, 0, -1);
        if (!$collection[$countcolpets]['AvlBreeds']) $collection[$countcolpets]['AvlBreeds'] = "N/A";
        $countcolpets++;
    }
}

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
            $collection[$countcolpets]['Quality'] = "-";
            $collection[$countcolpets]['Breed'] = "-";
            if ($pet['Cageable'] == "0" || $pet['Cageable'] == "") $collection[$countcolpets]['Cageable'] = "N/A";
            if ($pet['Cageable'] == "1") $collection[$countcolpets]['Cageable'] =  __("Yes");
            if ($pet['Cageable'] == "2") $collection[$countcolpets]['Cageable'] = __("No");
            $collection[$countcolpets]['Collected'] = FALSE;
            $collection[$countcolpets]['CollectedN'] = __("No");
            $collection[$countcolpets]['Health'] = $pet['Health']; 
            $collection[$countcolpets]['Power'] = $pet['Power']; 
            $collection[$countcolpets]['Speed'] = $pet['Speed'];
            $breedstring = "";
                if ($pet['BB'] == 1) {
                    $breedstring = "BB,";
                }
                if ($pet['PP'] == 1) {
                    $breedstring = $breedstring."PP,";
                }
                if ($pet['SS'] == 1) {
                    $breedstring = $breedstring."SS,";
                }
                if ($pet['HH'] == 1) {
                    $breedstring = $breedstring."HH,";
                }
                if ($pet['HP'] == 1) {
                    $breedstring = $breedstring."HP,";
                }
                if ($pet['PS'] == 1) {
                    $breedstring = $breedstring."PS,";
                }
                if ($pet['HS'] == 1) {
                    $breedstring = $breedstring."HS,";
                }
                if ($pet['PB'] == 1) {
                    $breedstring = $breedstring."PB,";
                }
                if ($pet['SB'] == 1) {
                    $breedstring = $breedstring."SB,";
                }
                if ($pet['HB'] == 1) {
                    $breedstring = $breedstring."HB,";
                }
            $collection[$countcolpets]['AvlBreeds'] = substr($breedstring, 0, -1);
            if (!$collection[$countcolpets]['AvlBreeds']) $collection[$countcolpets]['AvlBreeds'] = "N/A";
            $countcolpets++;
        }
        $stats['TotalPetsNum']++;
    }
}
sortBy('Name', $collection, 'asc');


// Error reporting
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This export only works when using a Web Browser');

require_once('../data/PhpSpreadsheet/Psr/autoloader.php');
require_once('../data/PhpSpreadsheet/autoloader.php');


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new PHPExcel object
$objPHPExcel = new Spreadsheet();
$date = date('Y-m-d H:i:s');

// Set document properties
if ($user) {
    $objPHPExcel->getProperties()->setCreator("Aranesh from wow-petguide.com")
							 ->setLastModifiedBy("Aranesh")
							 ->setTitle("Pet Collection of ".$user->Name)
							 ->setSubject("Pet Collection - status ".$date." - exported from wow-petguide.com")
							 ->setDescription("A table of all battle pets in World of Warcraft, based on the armory collection of ".$user->Name.". This export is provided by wow-petguide.com")
							 ->setKeywords("wow battle pets wow-petguide.com")
							 ->setCategory("Table");
}
else {
    $objPHPExcel->getProperties()->setCreator("Aranesh from wow-petguide.com")
							 ->setLastModifiedBy("Aranesh")
							 ->setTitle("Pet Collection of ".$outputchar." - ".$outputrealm."-".strtoupper($outputregion))
							 ->setSubject("Pet Collection - status ".$date." - exported from wow-petguide.com")
							 ->setDescription("A table of all battle pets in World of Warcraft, based on the armory collection of ".$outputchar." - ".$outputrealm."-".strtoupper($outputregion).". This export is provided by wow-petguide.com")
							 ->setKeywords("wow battle pets wow-petguide.com")
							 ->setCategory("Table");
}

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '#')
            ->setCellValue('B1', __("Name"))
            ->setCellValue('C1', 'Wowhead')
            ->setCellValue('D1', __("Level"))
            ->setCellValue('E1', __("Quality"))
            ->setCellValue('F1', __("Breed"))
            ->setCellValue('G1', __("Families"))
            ->setCellValue('H1', __("Duplicates"))
            ->setCellValue('I1', __("Tradable"))
            ->setCellValue('J1', __("Collected"))
            ->setCellValue('K1', "Base Health")
            ->setCellValue('L1', "Base Power")
            ->setCellValue('M1', "Base Speed")
            ->setCellValue('N1', "Available Breeds")
            ->setCellValue('O1', "Species");

foreach($collection as $key => $value) {

$keyplus = $key+1;
$keyplusz = $key+2;

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$keyplusz, $keyplus)
            ->setCellValue('B'.$keyplusz, $value['Name'])
            ->setCellValue('C'.$keyplusz, 'link')
            ->setCellValue('D'.$keyplusz, $value['Level'])
            ->setCellValue('E'.$keyplusz, $value['QualityN'])
            ->setCellValue('F'.$keyplusz, $value['Breed'])
            ->setCellValue('G'.$keyplusz, $value['Family'])
            ->setCellValue('H'.$keyplusz, $value['Dupecount'])
            ->setCellValue('I'.$keyplusz, $value['Cageable'])
            ->setCellValue('J'.$keyplusz, $value['CollectedN'])
            ->setCellValue('K'.$keyplusz, $value['Health'])
            ->setCellValue('L'.$keyplusz, $value['Power'])
            ->setCellValue('M'.$keyplusz, $value['Speed'])
            ->setCellValue('N'.$keyplusz, $value['AvlBreeds'])
            ->setCellValue('O'.$keyplusz, $value['Species']);

$objPHPExcel->getActiveSheet()->getCell('C'.$keyplusz)->getHyperlink()->setUrl('http://www.wowhead.com/npc='.$value['PetID']);
$objPHPExcel->getActiveSheet()->getCell('C'.$keyplusz)->getHyperlink()->setTooltip($value['Name']);

if ($value['Collected'] == "0") {
   $objPHPExcel->getActiveSheet()->getStyle('A'.$keyplusz.':O'.$keyplusz)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('efb3b3');
}
if ($value['Duplicate'] == "1") {
   $objPHPExcel->getActiveSheet()->getStyle('A'.$keyplusz.':O'.$keyplusz)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('edebb4');
}


if ($value['Quality'] == "3") {
    $objPHPExcel->getActiveSheet()->getStyle('E'.$keyplusz)->getFont()->setBold(true)->getColor()->setRGB('0058a5');
}
if ($value['Quality'] == "2") {
    $objPHPExcel->getActiveSheet()->getStyle('E'.$keyplusz)->getFont()->setBold(true)->getColor()->setRGB('147e09');
}
if ($value['Quality'] == "1") {
    $objPHPExcel->getActiveSheet()->getStyle('E'.$keyplusz)->getFont()->setBold(true)->getColor()->setRGB('a3a3a3');
}
if ($value['Quality'] == "0") {
    $objPHPExcel->getActiveSheet()->getStyle('E'.$keyplusz)->getFont()->setBold(true)->getColor()->setRGB('636363');
}



}

$numallpets = count($collection)+1;
$objPHPExcel->getActiveSheet()->getStyle('C2:F'.$numallpets)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('H2:M'.$numallpets)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('O2:O'.$numallpets)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Title Formatting
$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('123163');


$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(14);
$objPHPExcel->getActiveSheet()->freezePane('A2');


$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Collection');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


$writer = new Xlsx($objPHPExcel);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Pet_Collection_Export_XuFu.xlsx"');
header('Cache-Control: max-age=0');
header('Expires: Fri, 11 Nov 2011 11:11:11 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');
$writer->save('php://output');