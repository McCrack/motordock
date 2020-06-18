<?php

use GoogleTranslator\Translator;

$IDs = [];
$set = [];
$translator = new Translator(["en"=>"de"]);

foreach ($response['searchResult'][0]['item'] as $item) {
    /** Skip if Status Not Active **/
    if ($item['sellingStatus'][0]['sellingState'][0]!="Active") {
        continue;
    }
    /*******************************/

    $IDs[] = $item['itemId'][0];
    $selling = &$item['sellingStatus'][0]['currentPrice'][0];
    $set[$item['itemId'][0]] = [
        "image"=>$item['galleryURL'][0],
        "items"=>[
            "RefID"=>$item['itemId'][0],
            "CatID"=>$item['primaryCategory'][0]['categoryId'][0],
            "SellerID"=>$p['SellerID'],
            "Named"=>$translator->translate($item['title'][0]),
            "Preview"=>$item['pictureURLLarge'][0],
            "State"=>$item['condition'][0]['conditionDisplayName'][0]
        ],
        "subItems"=>[
            "SubName"=>"default",
            "Status"=>"available",
            "Price"=>((INT)$selling['__value__'] * $p['PriceFactor']),
            "Currency"=>$selling['@currencyId']
        ],
        "extended"=>[]
    ];
}

/**
 * Exclude Item from result
 * which are already in the DataBase
**/
$refs = $mySQL->getGroup(
    "SELECT RefID FROM cb_items WHERE RefID IN ({arr})",
    $IDs
)['RefID'];
if (!empty($refs)) {
    $IDs = array_diff($IDs, $refs);
}
/**********************************/

foreach ($IDs as $i=>$id) {

    /** Create Thing **/
    $ThingID = $mySQL->inquiry("INSERT INTO cb_things SET type='showcase', created={int}, modified={int}",
        TIMESTAMP,
        TIMESTAMP
    )['last_id'];

    $set[$id]['items']['ThingID'] =
    $set[$id]['subItems']['ThingID'] =
    $set[$id]['extended']['ThingID'] = $ThingID;
    /******************/

    /** Create subItem **/
    $ItemID = $mySQL->inquiry(
        "INSERT INTO cb_subitems SET {set}",
        $set[$id]['subItems']
    )['last_id'];

    $set[$id]['items']['ItemID'] = $ItemID;
    /********************/

    /** Create Item **/
    $mySQL->inquiry(
        "INSERT INTO cb_items SET {set}",
        $set[$id]['items']
    );
    /*****************/

    $answer[] = [
        "preview"=>$set[$id]['items']['Preview'],
        "named"=>$set[$id]['items']['Named'],
        "price"=>$set[$id]['subItems']['Price'],
        "currency"=>$set[$id]['subItems']['Currency'],
        "CatID"=>$set[$id]['items']['CatID']
    ];
}

if (isset($p['getExtended'])) {
    /** Get Extended Data **/
    $extended = eBay::shopping("GetMultipleItems")
        ->post(function($client) use ($IDs){
        foreach ($IDs as $id) {
            $client->query->addChild("ItemID", $id);
        }
    });
    /***********************/

    if ($extended['code']==200) {
        $extended = new SimpleXMLElement($extended['data']);
        if ($extended->Ack=="Success") {
            foreach ($extended->Item as $item) {
                /** Parse Gallery **/
                $mediaset = [];
                foreach ($item->PictureURL as $img) {
                    $mediaset[] = [
                        "url"=>(STRING)$img,
                        "type"=>"img"
                    ];
                }
                /*******************/

                /** Parse Other Data
                * ...
                **/
                /** Save Extended Data **/
                $mySQL->inquiry(
                    "INSERT INTO cb_item_extended SET ThingID={int},Mediaset={str}",
                    $set[(STRING)$item->ItemID]['extended']['ThingID'],
                    JSON::encode($mediaset)
                );
            }
            /************************/
        }
    }
}
