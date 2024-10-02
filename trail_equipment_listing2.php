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
            border: none;
            border-collapse: collapse;
        }
        td {
            border: none;
            width: auto;
            white-space: nowrap;
            padding: 2px 0px;
        }
        table td:nth-child(2),
        table td:nth-child(3) {
            white-space: nowrap;
            width: 1px;
            padding-right: 30px;
            margin: 10px;
        }
        tr {
            border: none;
        }
        .left-align {
            text-align: left;
    }
    </style>
</head>
<body>
<?php

require_once('./config.php');

// Set SEARCH variables
$freematch = ''; // set manually here if needed

if (isset($_GET['free'])) {
    $freematch = $_GET['free']; // get from url parameter, ?free=xxx
    
    // Replace Finnish alphabets
    $freematch = str_replace('å', '%C3%A5', $freematch);
    $freematch = str_replace('Å', '%C3%85', $freematch);
    $freematch = str_replace('ä', '%C3%A4', $freematch);
    $freematch = str_replace('Ä', '%C3%84', $freematch);
    $freematch = str_replace('ö', '%C3%B6', $freematch);
    $freematch = str_replace('Ö', '%C3%96', $freematch);
}

$model1 = ''; // set manually here if needed, for example 303979439
if (isset($_GET['model1'])) {
    $model1 = $_GET['model1']; // get from url parameter, ?model1=xxx
}

$model2 = ''; // set manually here if needed
if (isset($_GET['model2'])) {
    $model2 = $_GET['model2']; // get from url parameter, ?model2=xxx
}

$location1 = ''; // set manually here if needed
if (isset($_GET['location1'])) {
    $location1 = $_GET['location1']; // get from url parameter, ?location1=xxx
}

$deleted = ''; // set manually here if needed
if (isset($_GET['deleted'])) {
    $deleted = $_GET['deleted']; // get from url parameter, ?deleted=0 or deleted=1
}

// Minimal error check
if ($code == '') {
    echo '<p>API key not set.</p>';
    die;
}

if ($department1 == '') {
    echo '<p>Department not set.</p>';
    die;
}

// Check if models are defined
$model_category_id1 = '';
$model_category_id2 = '';
if ($model1 != '') {
    $model_category_id1 = '&search%5Bmodel_category_ids%5D%5B%5D=' . $model1;
}

if ($model2 != '') {
    $model_category_id2 = '&search%5Bmodel_category_ids%5D%5B%5D=' . $model2;
}

// Check if department is defined instead of default from config.php
if (isset($_GET['department'])) {
    $department1 = $_GET['department'];
}

// Check if 2nd department is defined
if (isset($_GET['department2'])) {
    $department2 = $_GET['department2'];
}

if ($department2 != '') {
    $department2 = '&search%5Bdepartment_ids%5D%5B%5D=' . $department2;
}

// Set POST variables
$url = 'https://api.trail.fi/api/v1/items?&search%5Bfree%5D=' . $freematch . '&search%5Bdepartment_ids%5D%5B%5D=' . $department1 . '' . $department2 . '&search%5Blocations%5D%5B%5D=' . $location1 . '' . $model_category_id1 . '' . $model_category_id2 . '&search%5Bitem_type_id%5D=&search%5Bafter%5D=&search%5Bbefore%5D=&search%5Baudited_after%5D=&search%5Baudited_before%5D=&search%5Bexpires_after%5D=&search%5Bexpires_before%5D=&search%5Bprice_above%5D=&search%5Bprice_below%5D=&search%5Bcreated_after%5D=&search%5Bmarked%5D=&search%5Bdeleted%5D=' . $deleted . '&search%5Bdeleted_after%5D=&search%5Bdeleted_before%5D=&search%5Bdelete_reason%5D=&search%5Breservable%5D=&page=1&per_page=50000';

// Open connection
$ch = curl_init();

// Set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $code));

// Save response to variable $result
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Execute post
$json = curl_exec($ch);

// Close connection
curl_close($ch);

// Create PHP array from Trail JSON export
$array = json_decode($json, true);

// Display root location
echo "<h1>Room " . htmlspecialchars($array['data'][0]['root_location']) . "</h1>";
echo "<h3>This room has the following equipment</h3>";

$results = [];

// Grouping devices by model
foreach ($array['data'] as $item) {
    $name = $item['model']['name'] ?? 'Unknown Model';
    $manufacturer = $item['manufacturer'] ?? 'Unknown Manufacturer';
    $description = $item['model_description'] ?? 'No description available';
    $model_id = $item['model']['id'] ?? 0;
    $item_id = $item['id']; // Unique item ID
    $identity = $item['identity'] ?? 'No identity available'; // Get identity

    // Adding device to the group
    if (!isset($results[$name])) {
        $results[$name] = [
            'count' => 0,
            'manufacturer' => $manufacturer,
            'model_description' => $description, // Store model description
            'model_id' => $model_id,
            'items' => [] // List of individual devices
        ];
    }
    $results[$name]['count']++;
    $results[$name]['items'][] = [
        'manufacturer' => $manufacturer,
        'model_id' => $model_id,
        'description' => $description,
        'item_id' => $item_id, // Store the unique ID for each item
        'identity' => $identity, // Store the identity for each item
        'item' => $item // You can add more details here
    ];
}

echo "<table>";
echo "<tr><th></th><th class='left-align'>Amount</th><th class='left-align'>Manufacturer</th><th class='left-align'>Model</th><th class='left-align'>Description</th></tr>"; // Table headers

foreach ($results as $name => $information) {
    // Output model information with toggle button
    echo "<tr>";
    echo "<td><button class='toggle' onclick='toggleVisibility(\"{$name}\", this)'>+</button></td>"; // Toggle button
    echo "<td>{$information['count']} pcs</td>";
    echo "<td>{$information['manufacturer']}</td>";
    echo "<td><a href='$trail_models_baseurl{$information['model_id']}' target='_blank'>{$name}</a></td>"; // Model name and link
    echo "<td>{$information['model_description']}</td>"; // Model description
    echo "<td></td>"; // Placeholder for identity
    echo "</tr>";

    // Output detailed information for each device
    echo "<tr id='{$name}' class='item-list' style='display:none;'>"; // Initially hidden
    echo "<td colspan='6'>"; // Adjust colspan to include identity column
    echo "<ul style='list-style-type:none; padding-left: 50px;'>"; // No bullets and indent
    foreach ($information['items'] as $item) {
        // Display manufacturer, model, and identity for each item
        echo "<li><a href='$trail_items_baseurl{$item['item_id']}' target='_blank'>{$item['manufacturer']} - {$name} ({$item['identity']})</a></li>";
    }
    echo "</ul></td></tr>";
}
echo "</table>";

?>

<script>
function toggleVisibility(name, button) {
    var row = document.getElementById(name);
    if (row.style.display === "none") {
        row.style.display = "table-row"; // Show the row
        button.textContent = '-'; // Change button text to minus
    } else {
        row.style.display = "none"; // Hide the row
        button.textContent = '+'; // Change button text back to plus
    }
}
</script>

<p>
Please do <b>NOT</b> move equipment out of this room.<br>
If something is missing or broken, please contact <i>siba.avhelp@uniarts.fi</i>
</p>

<!-- Print API URL and PHP array for debugging purposes -->
<?php if (isset($_GET['debug'])): ?>
    <h3>Query URL</h3>
    <?php echo htmlspecialchars($url); ?>
    <h3>PHP array</h3>
    <pre><?php print_r($array); ?></pre>
    <p>end of report</p>
<?php endif; ?>
</body>
</html>
