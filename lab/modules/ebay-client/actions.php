<?php

switch(ARG_2){
    case "sv_itm":
        $p = JSON::load('php://input');

        $rows = [];
        $named = $p['de'];
        foreach ($p['dictionary'] as $key=>$translate) {
            $wordCount = str_word_count($key);
            $rows[] = "('{$key}','{$translate}',{$wordCount})";

            $named = preg_replace("/\s{$key}\s/i", " {$translate} ", $named);
        }

        $named = [
            'en' => $p['en'],
            'de' => $named
        ];
        $named = JSON::encode($named);

        $mySQL->inquiry(
            "UPDATE cb_store SET BrandID={int}, selling={str}, named={str} WHERE ThingID={int} LIMIT 1",
            $p['BrandID'],
            $p['Price'],
            $named,
            ARG_3
        );

        if (count($rows)) {
            $mySQL->inquiry("INSERT INTO cb_dictionary (word, de, sort_id) VALUES {prp}", implode(",", $rows));

            $items = $mySQL->get("SELECT ThingID, named FROM cb_store WHERE CategoryID={int}", $p['CatID']);

            foreach ($items as $item) {
                $named = JSON::parse($item['named']);
                foreach ($p['dictionary'] as $key=>$translate) {
                    $named['de'] = preg_replace("/\s{$key}\s/i", " {$translate} ", $named['de']);
                }

                $mySQL->inquiry(
                    "UPDATE cb_store SET named={str} WHERE ThingID={int} LIMIT 1",
                    JSON::encode($named),
                    $item['ThingID']
                );
            }
        }
        break;
    case "rm_itm":
        print $mySQL->inquiry("DELETE FROM cb_things WHERE ThingID={int} LIMIT 1", ARG_3)['affected_rows'];
        break;
    case "rm_all":
        print $mySQL->inquiry("DELETE FROM cb_things WHERE type='showcase'")['affected_rows'];
        break;
    case "sv_category":
        $p = JSON::load('php://input');
        $mySQL->inquiry("UPDATE cb_categories SET {set} WHERE CatID={int} LIMIT 1", [
            'name'      => [
                'en'    => $p['NameEN'],
                'de'    => $p['NameDE']
            ],
            'slug'      => $p['slug'],
            'status'    => $p['status'],
            'delivery_price'  => $p['delivery_price'],
        ], ARG_3);
        break;
    default:
        break;
}