<!DOCTYPE html>
<html>
    <head>
        <title>Data retrieved from Trail Asset Management</title>
    </head>
    <body>
    <table>
<?php

require_once('./config.php');

// set SEARCH variables
$freematch = ''; // set manually here if needed
if(isset($_GET['free'])) {
     $freematch = $_GET['free']; // get from url parameter, ?free=xxx
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

// Minimal error check
if($code == '') {
     echo '<p>API key not set.</p>';
     die;
};

if($department1 == '') {
     echo '<p>Department not set.</p>';
     die;
};

if($model1 != '') {
     $model_category_id1 = '&search%5Bmodel_category_ids%5D%5B%5D='.$model1;
};

if($model2 != '') {
     $model_category_id2 = '&search%5Bmodel_category_ids%5D%5B%5D='.$model2;
};

// Check if deparment is defined instead of default
if(isset($_GET['department'])) {
     $department1 = $_GET['department']; // get from url parameter, ?free=xxx
};

// set POST variables
$url = 'https://api.trail.fi/api/v1/items?&search%5Bfree%5D='.$freematch.'&search%5Bdepartment_ids%5D%5B%5D='.$department1.'&search%5Blocations%5D%5B%5D='.$location1.''.$model_category_id1.''.$model_category_id2.'&search%5Bitem_type_id%5D=&search%5Bafter%5D=&search%5Bbefore%5D=&search%5Baudited_after%5D=&search%5Baudited_before%5D=&search%5Bexpires_after%5D=&search%5Bexpires_before%5D=&search%5Bprice_above%5D=&search%5Bprice_below%5D=&search%5Bcreated_after%5D=&search%5Bmarked%5D=&search%5Bdeleted%5D=&search%5Bdeleted_after%5D=&search%5Bdeleted_before%5D=&search%5Bdelete_reason%5D=&search%5Breservable%5D=&page=1&per_page=50000';

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

// var_dump($result);

// create PHP array from Trail JSON export
$array = json_decode($json, true);

// table headings, currently hardcoded and including some example attributes
echo "<tr><td>Valmistaja</td><td>Malli</td><td>Kuvaus</td><td>Sijainti</td><td>Sarjanro</td></tr>";

foreach ($array['data'] as $thread) {   
     echo "<tr class='".$thread['category']."'><td>".$thread['manufacturer']."</td><td>".$thread['model']['name']."</td><td>".$thread['description']."</td><td>".$thread['location']['location']['name']."</td><td>".$thread['serial']."</td></tr>";
}
?>
</table>

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
if(isset($_GET['hide-model'])) {
          echo 'tr > th:nth-of-type(2),';
          echo 'tr > td:nth-of-type(2) {';
          echo '     display: none;';
          echo '}';
};
if(isset($_GET['hide-serial'])) {
     echo 'tr > th:nth-of-type(5),';
     echo 'tr > td:nth-of-type(5) {';
     echo '     display: none;';
     echo '}';
};
?>
</style>
</html>