<?php


$IDs = [];
$set = [];

$excludedCats = $mySQL->getGroup("SELECT CatID FROM cb_categories WHERE status='disabled'")['CatID'];

foreach ($response['searchResult'][0]['item'] as $item) {

    $categoryId = &$item['primaryCategory'][0]['categoryId'][0];
    $sellingStatus = &$item['sellingStatus'][0];

    /** Skip if Status Not Active **/
    if ($sellingStatus['sellingState'][0]!="Active") {
        continue;
    }
    /** Skip if Category_ID contained in Excluded Categories list **/
    if (in_array($categoryId, $excludedCats)) {
        continue;
    }
    /*******************************/

    // Price Formation

    $currency = $sellingStatus['currentPrice'][0]['@currencyId'];
    $purchase = (INT)$sellingStatus['currentPrice'][0]['__value__'];

    $price = $purchase * $p['price']['Currency Rate'] * $p['price']['eBay Tax'];
    if (isset($p['price']['special formules'][$categoryId])) {
        $factor = eval("return(".$p['price']['special formules'][$categoryId].");");
        $price *=  ($factor < 1.3) ? 1.3 : $factor;
    } else {
        foreach (array_reverse($p['price']['price gradation'], true) as $threshold => $ratio) {
            if ($price > $threshold) {
                $price *= $ratio;
                break;
            }
        }
    }
    $price *= $p['price']['Tax'];

    $IDs[] = $item['itemId'][0];
    $set[$item['itemId'][0]] = [
        'items' => [
            "CategoryID"=> $categoryId,
            "status"    => "new",
            "preview"   => $item['pictureURLLarge'][0],
            "selling"   => round($price),
        ],
        "extended"  => [
            "SellerID"  => $p['SellerID'],
            "eBayID"    => $item['itemId'][0],
            'purchase'  => $purchase,
            'currency'  => $currency,
            'images'    => [],
            'options'   => []
        ]
    ];
}

/**
 * Exclude Item from result
 * which are already in the DataBase
**/
$refs = $mySQL->getGroup(
    "SELECT eBayID FROM cb_extended WHERE eBayID IN ({arr})",
    $IDs
)['eBayID'];
if (!empty($refs)) {
    $IDs = array_diff($IDs, $refs);
}
/**********************************/

/** Get Extended Data **/
$extended = eBay::shopping("GetMultipleItems")
    ->post(function($client) use ($IDs){
        foreach ($IDs as $id) {
            $client->query->addChild("ItemID", $id);
        }
    });

if ($extended['code']==200) {
    $extended = new SimpleXMLElement($extended['data']);
    if ($extended->Ack=="Success") {
        
        $brands = $mySQL->getGroup("SELECT BrandID, slug FROM cb_brands");
        $dictionary = $mySQL->get("SELECT * FROM cb_dictionary ORDER BY sort_id DESC");

        foreach ($extended->Item as $item) {
            
            $eBayID = (STRING)$item->ItemID;
            
            $set[$eBayID]['extended']['ReferenceID'] = (STRING)$item->SKU;

            $named = (STRING)$item->Title;
            
            $named = preg_replace("/(\s&)*\sWARRANTY/i", '', $named);
            $named = preg_replace("/\s*-*\s*\d+$/", '', $named);
            $named = preg_replace("/\s{2,}/", ' ', $named);

            $de = preg_replace("/\sTo\s/i", ' bis ', $named);
            $de = preg_replace("/\sOn\s/i", ' ab ', $de);

            foreach ($dictionary as $row) {
                $de = preg_replace("/\s{$row['word']}\s/i", " {$row['de']} ", $de);
            }

            $set[$eBayID]['items']['named'] = [
                'en' => $named,
                'de' => $de,
            ];

            
            $set[$eBayID]['items']['status'] = "available";

            $set[$eBayID]['extended']['options']['condition'] = (STRING)$item->ConditionDisplayName;

            foreach($item->ItemSpecifics->NameValueList as $option) {
                switch ((STRING)$option->Name) {
                    case "Brand":
                        $brand = (STRING)$option->Value;
                        if (strtolower($brand) == "vauxhall") {
                            $brand = "Opel";
                        }
                        $set[$eBayID]['extended']['options']['brand'] = $brand;
                        $BrandID = NULL;
                        foreach ($brands['slug'] as $i=>$mark) {
                            if ($mark == strtolower($brand)) {
                                $BrandID = $brands['BrandID'][$i];
                            }
                        }
                        $set[$eBayID]['items']['BrandID'] = $BrandID;
                        break;
                    case "Model/Series":
                        $set[$eBayID]['extended']['options']['model'] = preg_replace("/\sTo\s/i", ' - ', (STRING)$option->Value);
                        break;
                    case "Year":
                        $set[$eBayID]['extended']['options']['year'] = (STRING)$option->Value;
                        break;
                    default:
                        break;
                }
            }

            /** Parse Gallery **/
            foreach ($item->PictureURL as $img) {
                $url = parse_url((STRING)$img);
                $imgkey = explode("/", $url['path'])[5];
                $set[$eBayID]['extended']['images'][] = "https://i.ebayimg.com/images/g/{$imgkey}/s-l600.jpg";
            }
            $set[$eBayID]['extended']['images'] = JSON::encode($set[$eBayID]['extended']['images']);
            /*******************/

            $answer[] = [
                'brand'     => $set[$eBayID]['extended']['options']['brand'],
                'named'     => $set[$eBayID]['items']['named']['de'],
                'preview'   => $set[$eBayID]['items']['preview'],
                'CategoryID'=> $set[$eBayID]['items']['CategoryID'],
                'selling'   => $set[$eBayID]['items']['selling'],
                'purchase'  => $set[$eBayID]['extended']['purchase'],
                'currency'  => $set[$eBayID]['extended']['currency']
            ];


            /** Create Thing **/
            $ThingID = $mySQL->inquiry("INSERT INTO cb_things SET type='showcase', created={int}, modified={int}",
                TIMESTAMP,
                TIMESTAMP
            )['last_id'];

            $set[$eBayID]['items']['ThingID'] =
            $set[$eBayID]['extended']['ThingID'] = $ThingID;

            
            $set[$eBayID]['items']['named'] = JSON::encode($set[$eBayID]['items']['named']);
            $set[$eBayID]['extended']['options'] = JSON::encode($set[$eBayID]['extended']['options']);

            /** Create Item **/
            $mySQL->inquiry("INSERT INTO cb_store SET {set}", $set[$eBayID]['items']);

            /** Save Extended Data **/
            $mySQL->inquiry("INSERT INTO cb_extended SET {set}", $set[$eBayID]['extended']);
        }
        /************************/
    }
}