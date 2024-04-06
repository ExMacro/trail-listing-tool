<!DOCTYPE html>
<html>
    <head>
        <title>Data retrieved from Trail Asset Management</title>
    </head>
    <body>
<?php

require_once('./config.php');

// set SEARCH variables
$freematch = ''; // set manually here if needed

if(isset($_GET['free'])) {
     $freematch = $_GET['free']; // get from url parameter, ?free=xxx
     
     // Replace Finnish alphabets
     $freematch = str_replace('å', '%C3%A5', $freematch);
     $freematch = str_replace('Å', '%C3%85', $freematch);
     $freematch = str_replace('ä', '%C3%A4', $freematch);
     $freematch = str_replace('Ä', '%C3%84', $freematch);
     $freematch = str_replace('ö', '%C3%B6', $freematch);
     $freematch = str_replace('Ö', '%C3%96', $freematch);
};

$model1 = ''; // set manually here if needed, for example 303979439
if(isset($_GET['model1'])) {
     $model1 = $_GET['model1']; // get from url parameter, ?model1=xxx
};

$model2 = ''; // set manually here if needed
if(isset($_GET['model2'])) {
     $model2 = $_GET['model2']; // get from url parameter, ?model2=xxx
};

$location1 = ''; // set manually here if needed, for example //29770
if(isset($_GET['location1'])) {
     $location1 = $_GET['location1']; // get from url parameter, ?location1=xxx
};

$marked = ''; // set manually here if needed
if(isset($_GET['marked'])) {
     $marked = $_GET['marked']; // get from url parameter, ?marked=0 or marked=1
};

$deleted = ''; // set manually here if needed
if(isset($_GET['deleted'])) {
     $deleted = $_GET['deleted']; // get from url parameter, ?deleted=0 or deleted=1
};

// Minimal error check
if($code == '') {
     echo '<p>API key not set.</p>';
     die;
};

if($department1 == '') {
     echo '<p>Department not set.</p>';
     die;
};

// Check if models are defined
if($model1 != '') {
     $model_category_id1 = '&search%5Bmodel_category_ids%5D%5B%5D='.$model1;
};

if($model2 != '') {
     $model_category_id2 = '&search%5Bmodel_category_ids%5D%5B%5D='.$model2;
};

// Check if department is defined instead of default from config.php
if(isset($_GET['department'])) {
     $department1 = $_GET['department'];
};

// Check if 2nd department is defined
if(isset($_GET['department2'])) {
     $department2 = $_GET['department2'];
};

if($department2 != '') {
     $department2 = '&search%5Bdepartment_ids%5D%5B%5D='.$department2;
};

// set POST variables
$url = 'https://api.trail.fi/api/v1/items?&search%5Bfree%5D='.$freematch.'&search%5Bdepartment_ids%5D%5B%5D='.$department1.''.$department2.'&search%5Blocations%5D%5B%5D='.$location1.''.$model_category_id1.''.$model_category_id2.'&search%5Bitem_type_id%5D=&search%5Bafter%5D=&search%5Bbefore%5D=&search%5Baudited_after%5D=&search%5Baudited_before%5D=&search%5Bexpires_after%5D=&search%5Bexpires_before%5D=&search%5Bprice_above%5D=&search%5Bprice_below%5D=&search%5Bcreated_after%5D=&search%5Bmarked%5D='.$marked.'&search%5Bdeleted%5D='.$deleted.'&search%5Bdeleted_after%5D=&search%5Bdeleted_before%5D=&search%5Bdelete_reason%5D=&search%5Breservable%5D=&page=1&per_page=50000';

// open connection
$ch = curl_init();

// set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-Type: text/json', 'Authorization: Basic '.$code));

// save response to variable $result
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// execute post
$json = curl_exec($ch);

// close connection
curl_close($ch);

// for debug only
     /* var_dump($result); */

// create PHP array from Trail JSON export
$array = json_decode($json, true);

echo '<table class="main">';
// table headings, currently hardcoded and including some example attributes
echo "<tr><td class='manufacturer'>Valmistaja</td><td class='modelname'>Malli</td><td>Kuvaus</td><td>Kampus</td><td>Sijainti</td><td>Tilakoodi</td><td class='serial'>Sarjanro</td></tr>";

// build 
foreach ($array['data'] as $thread) {
     $campus = explode(' ', $thread['root_location']);
     $room = $thread['location']['location']['name'];
     $roomcode = $campus['0']."_".$room;
     $cleancode = explode(' ', $roomcode);
     echo "<tr class='".$thread['category']."'><td class='manufacturer'>".$thread['manufacturer']."</td><td class='modelname'>".$thread['model']['name']."</td><td>".$thread['description']."</td><td>".$campus['0']."</td><td>".$thread['location']['location']['name']."</td><td>".$cleancode[0]."</td><td class='serial'>".$thread['serial']."</td></tr>";

}
?>
</table>

<?php
// give summary instead of device catalogue
if(isset($_GET['summary'])) {

     echo '<style>table.main {display: none;}</style>';
     // create smaller array with mfg + model combined
     $items = array();
     foreach ($array['data'] as $thread) {
          if ($thread['model']['name'] != null) {
               $items[] = $thread['manufacturer']." ".$thread['model']['name'];
          };
     }

     // create small array mfg + model combined
     $result = count($items); 
     if ($result != null) {
          $vals = array_count_values($items);
          // echo 'No. of NON Duplicate Items: '.count($vals).'<br><br>';
          // print_r($vals);
          echo '<table>';
          echo '<tr><td style="text-align: left;">Laite</td><td>lukumäärä</td></tr>';
          foreach($vals as $id => $amount) {
               echo '<tr><td>'.$id.'</td><td class="amount">'.$amount.'</td></tr>';
          };
          echo '<tr style="border-top: 2px solid black"><td>Laitteita yhteensä:</td><td class="amount">'.$result.'</td></tr>';
          echo '</table>';
     };
};
?>

<?php
// print API URI and PHP array for debugging purposes, set debug as url parameter
if(isset($_GET['debug'])) {
     echo '<h3>Query URL</h4>';
     echo $url;
     echo '<h3>PHP array</h4>';
     echo '<pre>'; print_r($array); echo '</pre>';
     echo '<p>end of report</p>';
};
?>

</body>
<style>
body {
     font-family: "Segoe UI", "Segoe UI Web (West European)", "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, "Helvetica Neue", sans-serif;
     font-size: 14px;
}

td {
     padding: 5px 10px;
}

table {
     max-width: 100%;
     border: 0;
     border-collapse: collapse;
}

table td:nth-child(2),
table td:nth-child(3) {
     white-space:nowrap;
     width: 1px;
     padding-right: 30px;
     margin: 10px;
}

table tr:nth-child(1) {
     font-weight: bold;
}

td.amount {
     text-align: center;
}

tr {
border-bottom: 1px solid white;
}

tr.Televisio {
     background: #930e5e;
     color: white;
     padding: 10px;
}

tr.Näyttö {
     background: #044664;
     color: white;
}

<?php
// hides model from output
if(isset($_GET['hide-model'])) {
          echo 'tr > td.modelname {';
          echo '     display: none;';
          echo '}';
};

// hides serial from output
if(isset($_GET['hide-serial'])) {
     echo 'tr > td.serial {';
     echo '     display: none;';
     echo '}';
};

// hides manufacturer from output
if(isset($_GET['hide-manufacturer'])) {
     echo 'tr > td.manufacturer {';
     echo '     display: none;';
     echo '}';
};

if(isset($_GET['clean'])) {
     echo 'tr {';
     echo '    background: white !important;';
     echo '    color: black !important';
     echo '}';
     echo 'tr:nth-child(2n) {';
     echo '    background: #dfdfdf !important;';
     echo '}';
};
?>
</style>
</html>
