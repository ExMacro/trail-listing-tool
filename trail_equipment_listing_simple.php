<!DOCTYPE html>
<html lang="en">
    <head>
         <title>Equipment listing from Trail</title>
         <style>
               body {
                    font-family: "Segoe UI", "Segoe UI Web (West European)", "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, "Helvetica Neue", sans-serif;
                    font-size: 14px;
               }
               table {
                    table-layout: auto;
                    width: auto;
                    border: 0;
                    border-collapse: collapse;
               }
               td {
                    width: auto;
                    white-space: nowrap;
                    padding: 2px 5px;
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
               th {
                    font-weight: bold;
                    padding: 10px 5px;
                    text-align: left;
               }

               tr {
               border-bottom: 1px solid white;
               }
               th.department-heading {
                    text-align: left;
                    padding: 10px 0;
                    font-weight: bold;
                    font-size: 18px;
               }
          </style>
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

$location1 = ''; // set manually here if needed
if(isset($_GET['location1'])) {
     $location1 = $_GET['location1']; // get from url parameter, ?location1=xxx
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

// Check if models are defined
if($model1 != '') {
     $model_category_id1 = '&search%5Bmodel_category_ids%5D%5B%5D='.$model1;
};

if($model2 != '') {
     $model_category_id2 = '&search%5Bmodel_category_ids%5D%5B%5D='.$model2;
};

// set POST variables
$url = 'https://api.trail.fi/api/v1/items?&search%5Bfree%5D='.$freematch.'&search%5Blocations%5D%5B%5D='.$location1.''.$model_category_id1.''.$model_category_id2.'&search%5Bitem_type_id%5D=&search%5Bafter%5D=&search%5Bbefore%5D=&search%5Baudited_after%5D=&search%5Baudited_before%5D=&search%5Bexpires_after%5D=&search%5Bexpires_before%5D=&search%5Bprice_above%5D=&search%5Bprice_below%5D=&search%5Bcreated_after%5D=&search%5Bmarked%5D=&search%5Bdeleted%5D='.$deleted.'&search%5Bdeleted_after%5D=&search%5Bdeleted_before%5D=&search%5Bdelete_reason%5D=&search%5Breservable%5D=&page=1&per_page=50000';

// open connection
$ch = curl_init();

// set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.$code));

// save response to variable $result
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// execute post
$json = curl_exec($ch);

// close connection
curl_close($ch);

// create PHP array from Trail JSON export
$array = json_decode($json, true);

// Display root location
echo "<h1>Room ".$array['data'][0]['root_location']."</h1>";
echo "<h3>This room has the following AV equipment and instruments</h3>";

// Create an array for the web page
echo "<table class='table'>";

// Format results array
$results = [];

// Use external desired keywords file
require_once('desired_keywords.php');

// Create an array that stores the information in the desired order
$sorted_results = [];

// Tracking the departments to avoid duplicate headings
$added_departments = [];

// Iterate over the keywords and items
foreach ($desired_keywords as $keyword) {
    foreach ($array['data'] as $item) {
        $model_name = $item['model']['name']; // Original model name from Trail
        $manufacturer = $item['manufacturer'];
        $description = $item['model_description'];
        $department = $item['department']; // Get the department information

        // Check if the item description includes a desired keyword
        if (stripos($description, $keyword) !== false) {
            // Add the information to $sorted_results array in desired order
            if (!isset($sorted_results[$model_name])) {
                $sorted_results[$model_name] = [
                    'count' => 1,                    // Count + 1 if there are multiple items of the same model
                    'manufacturer' => $manufacturer, // Add manufacturer to the array
                    'description' => $description,   // Add description to the array
                    'model_name' => $model_name,     // Add model name to the array
                    'department' => $department      // Add department to the array
                ];
            } else {
                $sorted_results[$model_name]['count']++;
            }
        }
    }
}

// Only output the array if it's not empty
if (!empty($sorted_results)) {
    foreach ($sorted_results as $information) {
        // Check the department and add a heading if it's not already added
        if (!in_array($information['department'], $added_departments)) {
            if ($information['department'] == "SibA Esitystekniikkapalvelut") {
                echo "<tr><th colspan='4' class='department-heading'>AV Equipment</th></tr>";
                echo "<tr><th>Amount</th><th>Manufacturer</th><th>Model</th><th>Description</th></tr>";
            } elseif ($information['department'] == "SibA Muut soittimet") {
                echo "<tr><th colspan='4' class='department-heading'>Other instruments</th></tr>";
                echo "<tr><th>Amount</th><th>Manufacturer</th><th>Model</th><th>Description</th></tr>";
            } elseif ($information['department'] == "SibA Pianohuolto") {
                echo "<tr><th colspan='4' class='department-heading'>Grand and upright pianos</th></tr>";
                echo "<tr><th>Amount</th><th>Manufacturer</th><th>Model</th><th>Description</th></tr>";
            }
            // Mark the department as added
            $added_departments[] = $information['department'];
        }

        // Output the rows
        echo "<tr>";
        echo "<td>{$information['count']} pcs</td>";
        echo "<td>{$information['manufacturer']}</td>";
        echo "<td>{$information['model_name']}</td>";
        echo "<td>{$information['description']}</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<p>";
echo "Please do <b>NOT</b> move equipment out of this room.<br>";
echo "If something is missing or broken, please contact<br><br>";
echo "<i>siba.avhelp@uniarts.fi</i> for AV equipment<br>";
echo "<i>mikko.pietinen@uniarts.fi</i> for instruments";
echo "</p>";

// print API URL and PHP array for debugging purposes, set debug as url parameter
if(isset($_GET['debug'])) {
     echo '<h3>Query URL</h4>';
     echo $url;
     echo '<h3>PHP array</h4>';
     echo '<pre>'; print_r($array); echo '</pre>';
     echo '<p>end of report</p>';
};

?>

</body>
</html>
