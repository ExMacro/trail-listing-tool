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
               width: 1200px; /* NOTE: Change to relative values! */
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
          tr {
          border-bottom: 1px solid white;
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
     $location1 = $_GET['location1']; // get from url parameter, ?location=xxx
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

// set POST variables for getting a device list from specific location
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

// Display the room code and room name that user has input fetched from Trail
echo "<h1>Room ".$array['data'][0]['location']['location']['code']."</h1>";
echo "".$array['data'][0]['location']['location']['name']."";
echo "<h3>The life cycles of the equipment in this room</h3>";

// Create an array for the web page
echo "<table class='table'>";
echo "<tr><td>Manufacturer</td><td>Model</td><td>Life cycle</td></tr>";

foreach ($array['data'] as $device) {
    // Check that $device includes 'purchased' and 'calculated_estimated_lifespan' indexes
    if (isset($device['purchased']) && isset($device['calculated_estimated_lifespan'])) {
        // Store the purchase date as a variable
        $purchased_date = $device['purchased'];
        
        // Store the current date as a time stamp
        $current_date = time();
        
        // Format the variables
        $life_cycle_ends = "";
        $percentage = "";

        // Check that $purchased_date is valid
        if (strtotime($purchased_date)) {
            // Transform $purchased_date to time stamp
            $purchased_time = strtotime($purchased_date);
            
            // Find out the estimated life span of a device
            $life_cycle = $device['calculated_estimated_lifespan'];
            
            // Find out elapsed time from the purchase
            $elapsed_time = $current_date - $purchased_time;
            
            // Count the life cycle in seconds
            $life_cycle_in_seconds = $life_cycle * 365 * 24 * 60 * 60; // Convert the life cycle in years from Trail to seconds
            
            // Check that the life cycle is greater than zero
            if ($life_cycle_in_seconds > 0) {
                // Find out the position in the whole life cycle
                $percentage = ($elapsed_time / $life_cycle_in_seconds) * 100;
            }
        }

        // Output the information to an array on web page
        echo "<tr>";
        echo "<td>{$device['manufacturer']}</td>";
        echo "<td><a href='$trail_items_baseurl{$device['id']}' target='_blank'>{$device['model']['name']}</a></td>"; // Added link to the model name with target="_blank"
        if ($percentage !== "") {
              echo "<td style='position: relative; width: 400px;'>"; // Percentage bar max 400 pixels. NOTE: Change to relative values!

               $width = min(round($percentage, 2), 400);

               // Calculate how much to clip from the right side of the gradient
               $clip_path = "inset(0% " . (400 - $width) . "px 0% 0%)"; // NOTE: Change to relative values!

               // Output div element including the whole color gradient and cut the extra from right side with the clip-path
               echo "<div style='background: linear-gradient(to right, #4caf50, yellow 40px, red 100px); height: 10px; width: 100%; position: relative; clip-path: $clip_path;'>"; // NOTE: Change to relative values!
               echo "</div>";

               // Output the percentage value to the cell
               echo round($percentage, 2) . "%";
              echo "</td>";
        }
        echo "</tr>";
    } else {
        // If 'purchased' or 'calculated_estimated_lifespan' are missing, print all other info
        echo "<tr>";
        echo "<td>{$device['manufacturer']}</td>";
        echo "<td><a href='$trail_items_baseurl{$device['id']}' target='_blank'>{$device['model']['name']}</a></td>"; // Added link to the model
        echo "<td style='text-align: left;'>Purchase information missing...</td>"; // Combine cells and print text for missing info
        echo "</tr>";
    }
}

echo "</table>";

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
</html>
