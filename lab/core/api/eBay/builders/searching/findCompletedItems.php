<?php

$seller = $GLOBALS['mySQL']->getRow(
    "SELECT * FROM cb_sellers WHERE SellerID = {int} LIMIT 1",
    $p['SellerID']
);

$filters = [[
  "name"=>"Seller",
  "value"=>$seller['SellerName']
]];

if ($p['period']=="custom") {
    $filters[] = [
        "name"=>"StartTimeFrom",
        "value"=>gmdate("Y-m-d\T00:00:00", $p['from'])
    ];
    $filters[] = [
        "name"=>"StartTimeTo",
        "value"=>gmdate("Y-m-d\T00:00:00", $p['to'])
    ];
} elseif ($p['period']=="today") {
    $filters[] = [
        "name"=>"StartTimeFrom",
        "value"=>gmdate("Y-m-d\T00:00:00")
    ];
} else {
    $filters[] = [
        "name"=>"StartTimeFrom",
        "value"=>gmdate("Y-m-d\TH:i:s", time() - 86400)
    ];
}

$query = &$client->query['findCompletedItemsRequest'];

$query['paginationInput']['pageNumber'] = $p['page'];
$query['itemFilter'] = &$filters;

if (isset($p['save'])) {
    JSON::save("core/api/eBay/queries/searching/findItemsIneBayStores.json", $client->query);
}
