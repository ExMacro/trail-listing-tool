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
            border-collapse: collapse;
            border: 0;
            max-width: 100%;
            width: auto; /* Set automatic width */
          }
          table, th, td {
          }
          th, td {
            padding: 8px;
            text-align: center;
          }
          th:first-child,
          td:first-child {
            text-align: left; /* Align text in first cell to left  */
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

$location = ''; // set manually here if needed
if(isset($_GET['location'])) {
     $location = $_GET['location']; // get from url parameter, ?location=xxx
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

// set POST variables for seacrhing location given as URL parameter
$url_locations = 'https://api.trail.fi/api/v1/locations?search=' .$location;

// open connection
$ch = curl_init();

// set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url_locations);
curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.$code));

// save response to variable $result
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// execute post
$json = curl_exec($ch);

// close connection
curl_close($ch);

// create PHP array from Trail JSON export of location given as URL parameter
$array_locations = json_decode($json, true);


// set POST variables for getting a device list from specific location
$url = 'https://api.trail.fi/api/v1/items?&search%5Bfree%5D='.$freematch.'&search%5Bdepartment_ids%5D%5B%5D='.$department1.''.$department2.'&search%5Blocations%5D%5B%5D='.$location1.''.$model_category_id1.''.$model_category_id2.'&search%5Bitem_type_id%5D=&search%5Bafter%5D=&search%5Bbefore%5D=&search%5Baudited_after%5D=&search%5Baudited_before%5D=&search%5Bexpires_after%5D=&search%5Bexpires_before%5D=&search%5Bprice_above%5D=&search%5Bprice_below%5D=&search%5Bcreated_after%5D=&search%5Bmarked%5D=&search%5Bdeleted%5D='.$deleted.'&search%5Bdeleted_after%5D=&search%5Bdeleted_before%5D=&search%5Bdelete_reason%5D=&search%5Breservable%5D=&page=1&per_page=50000';

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
echo "<h3>The life cycle report of the equipment in this room</h3>";

// Format variables for different percentage ranges
$life_cycle_0_25 = 0;
$life_cycle_25_50 = 0;
$life_cycle_50_75 = 0;
$life_cycle_75_100 = 0;
$life_cycle_over_100 = 0;
$life_cycle_not_defined = 0;

// Total number of devices
$kokonaislaitteet = count($array['data']);

// Go through each device
foreach ($array['data'] as $item) {
    // Check if the purchase date and lifecycle length are defined
    if (!empty($item['purchased']) && !empty($item['estimated_lifespan_enddate'])) {
        // Calculate the lifecycle percentage
        $purchase_time = strtotime($item['purchased']);
        $current_time = time();
        $total_time = strtotime($item['estimated_lifespan_enddate']) - $purchase_time;
        $elapsed_time = $current_time - $purchase_time;

        // Calculate the lifecycle percentage
        if ($total_time != 0) {
            $life_cycle_percent = ($elapsed_time / $total_time) * 100;
        } else {
            // If the total time is zero, set the lifecycle percentage to 0
            $life_cycle_percent = 0;
        }

        // Group the device based on the percentage range
        if ($life_cycle_percent >= 0 && $life_cycle_percent <= 25) {
            $life_cycle_0_25++;
        } elseif ($life_cycle_percent > 25 && $life_cycle_percent <= 50) {
            $life_cycle_25_50++;
        } elseif ($life_cycle_percent > 50 && $life_cycle_percent <= 75) {
            $life_cycle_50_75++;
        } elseif ($life_cycle_percent > 75 && $life_cycle_percent <= 100) {
            $life_cycle_75_100++;
        } else {
            $life_cycle_over_100++;
        }
    } else {
        // If the purchase date or the duration of the lifecycle is not defined, a device is added to the counter for which the lifecycle cannot be calculated
        $life_cycle_not_defined++;
    }
}

// Calculate the percentage shares and round them to two decimal places
$percentage_0_25 = number_format(($life_cycle_0_25 / $kokonaislaitteet) * 100, 2);
$percentage_25_50 = number_format(($life_cycle_25_50 / $kokonaislaitteet) * 100, 2);
$percentage_50_75 = number_format(($life_cycle_50_75 / $kokonaislaitteet) * 100, 2);
$percentage_75_100 = number_format(($life_cycle_75_100 / $kokonaislaitteet) * 100, 2);
$percentage_over_100 = number_format(($life_cycle_over_100 / $kokonaislaitteet) * 100, 2);
$percentage_not_defined = number_format(($life_cycle_not_defined / $kokonaislaitteet) * 100, 2);

// Print the array
echo '<table class="table">';
echo '<tr><th>Life cycle %</th><th>0-25%</th><th>25-50%</th><th>50-75%</th><th>75-100%</th><th>Over 100%</th><th>Missing info</th></tr>';

echo "<tr>";
echo "<td></td>";
echo "<td style=\"position: relative; text-align: center;\"><div style=\"background: linear-gradient(to top, green 0%, yellow 50%, red 100%); height: 100px; width: 20px; position: relative; clip-path: inset(" . (100 - min($percentage_0_25, 100)) . "% 0 0 0); display: inline-block;\"></div></td>";
echo "<td style=\"position: relative; text-align: center;\"><div style=\"background: linear-gradient(to top, green 0%, yellow 50%, red 100%); height: 100px; width: 20px; position: relative; clip-path: inset(" . (100 - min($percentage_25_50, 100)) . "% 0 0 0); display: inline-block;\"></div></td>";
echo "<td style=\"position: relative; text-align: center;\"><div style=\"background: linear-gradient(to top, green 0%, yellow 50%, red 100%); height: 100px; width: 20px; position: relative; clip-path: inset(" . (100 - min($percentage_50_75, 100)) . "% 0 0 0); display: inline-block;\"></div></td>";
echo "<td style=\"position: relative; text-align: center;\"><div style=\"background: linear-gradient(to top, green 0%, yellow 50%, red 100%); height: 100px; width: 20px; position: relative; clip-path: inset(" . (100 - min($percentage_75_100, 100)) . "% 0 0 0); display: inline-block;\"></div></td>";
echo "<td style=\"position: relative; text-align: center;\"><div style=\"background: linear-gradient(to top, green 0%, yellow 50%, red 100%); height: 100px; width: 20px; position: relative; clip-path: inset(" . (100 - min($percentage_over_100, 100)) . "% 0 0 0); display: inline-block;\"></div></td>";
echo "<td style=\"position: relative; text-align: center;\"><div style=\"background: linear-gradient(to top, green 0%, yellow 50%, red 100%); height: 100px; width: 20px; position: relative; clip-path: inset(" . (100 - min($percentage_not_defined, 100)) . "% 0 0 0); display: inline-block;\"></div></td>";
echo "</tr>";

// Percentage shares
echo "<tr>";
echo "<th>Percentage</th>";
echo "<td>{$percentage_0_25}%</td>";
echo "<td>{$percentage_25_50}%</td>";
echo "<td>{$percentage_50_75}%</td>";
echo "<td>{$percentage_75_100}%</td>";
echo "<td>{$percentage_over_100}%</td>";
echo "<td>{$percentage_not_defined}%</td>";
echo "</tr>";

// The number of devices
echo "<tr>";
echo "<th>Device amount</th>";
echo "<td>{$life_cycle_0_25}</td>";
echo "<td>{$life_cycle_25_50}</td>";
echo "<td>{$life_cycle_50_75}</td>";
echo "<td>{$life_cycle_75_100}</td>";
echo "<td>{$life_cycle_over_100}</td>";
echo "<td>{$life_cycle_not_defined}</td>";
echo "</tr>";
echo "</table>";

// Find devices with lifecycles exceeding 100%
$devices_over_100 = [];
foreach ($array['data'] as $device) {
    if (!empty($device['purchased']) && !empty($device['estimated_lifespan_enddate'])) {
        $purchase_time = strtotime($device['purchased']);
        $current_time = time();
        $total_time = strtotime($device['estimated_lifespan_enddate']) - $purchase_time;
        $elapsed_time = $current_time - $purchase_time;
        $lifecycle_percentage = ($elapsed_time / $total_time) * 100;
        if ($lifecycle_percentage > 100) {
            $devices_over_100[$device['id']] = $lifecycle_percentage;
        }
    }
}

// Sort devices by lifecycle percentage in descending order
arsort($devices_over_100);

// Print the list of devices with lifecycles over 100%

echo "<h3>Top devices with lifecycles over 100%:</h3>";
$counter = 0;

echo '<table class="table">';

foreach ($devices_over_100 as $device_id => $percentage) {
    echo "<tr>";
    echo "<td></td>";

    // Search for the device information based on the device ID
    $device_name = '';
    foreach ($array['data'] as $device) {
        if ($device['id'] == $device_id) {
            $device_name = $device['model']['name'];
            break;
        }
    }

    echo "<td style='text-align: left;'><a href='$trail_items_baseurl{$device_id}' target='_blank'>{$device_name}</a> - " . number_format($percentage, 2) . "%</td>";
    echo "</tr>";
    $counter++;
    if ($counter >= 20) {
        break; // Show only top 20 devices
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
