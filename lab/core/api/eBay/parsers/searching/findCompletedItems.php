<?php

$set = [];
foreach ($response['searchResult'][0]['item'] as $item) {
    //$IDs[] = $item['itemId'][0];
    $set[$item['itemId'][0]] = $item['sellingStatus'][0]['sellingState'][0];
}
$entries = $mySQL->get(
  "SELECT ThingID,RefID,Preview,Named FROM cb_items WHERE RefID IN ({arr})",
  array_keys($set)
);
$answer = [
  "totalEntries"=>$pagination['totalEntries'][0],
  "inBase"=>count($entries),
  "items"=>[]
];
foreach ($entries as $item) {
  $answer['items'][] = [
    "ThingID"=>$item['ThingID'],
    "named"=>$item['Named'],
    "preview"=>$item['Preview'],
    "status"=>$set[$item['RefID']]
  ];
}
