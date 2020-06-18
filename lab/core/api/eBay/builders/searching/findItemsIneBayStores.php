<?php

$seller = $GLOBALS['mySQL']->getRow(
    "SELECT * FROM cb_sellers WHERE SellerID = {int} LIMIT 1",
    $p['SellerID']
);

$query = &$client->query['findItemsIneBayStoresRequest'];

$query['storeName'] = $seller['StoreName'];
$query['paginationInput']['pageNumber'] = $p['page'];

if (isset($p['CatID'])) {
    $query['categoryId'] = $p['CatID'];
}

$filters = [];

if (isset($p['pricelimits'])) {
    $filters[] = [
        "name"=>"MinPrice",
        "value"=>$p['MinPrice']
    ];
    $filters[] = [
        "name"=>"MaxPrice",
        "value"=>$p['MaxPrice']
    ];
}
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

foreach($p['filters'] as $set=>$values){
    $filters[] = [
        "name"=>$set,
        "value"=>$values
    ];
}

$client->query['findItemsIneBayStoresRequest']['itemFilter'] = $filters;

if (isset($p['save'])) {
    JSON::save("core/api/eBay/queries/searching/findItemsIneBayStores.json", $client->query);
}
