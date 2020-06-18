<?php

require_once "core/api/eBay/index.php";

define("TIMESTAMP", time());

switch (ARG_2) {
    case "gt_form":
        $form = file_get_contents("php://input");
        include_once "forms/".$form.".html";
        break;
    case "gt_sheet":
        if(defined("ARG_3") && file_exists("data/Excel/".ARG_3.".xlsx")){
          header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
          header("Content-Disposition: attachment;filename='".ARG_3.".xlsx'");
          header("Content-Length: ".filesize("data/Excel/".ARG_3.".xlsx"));
          readfile("data/Excel/".ARG_3.".xlsx");
        }
        break;
    case "import":
    case "searching":
    case "findcompleted":
        $p = JSON::load('php://input');

        $response = eBay::{ARG_3}($p['command'])
        ->post(function($client) use ($p){
            include_once "core/api/eBay/builders/".ARG_3."/".$p['command'].".php";
        });
        if ($response['code']!=200) {
            exit("ERROR - HTTP:".$response['code']);
        }

        $response = JSON::parse($response['data']);
        $response = current($response)[0];

        if ($response['ack'][0]!="Success") {
            exit("ERROR: ".$response['ack'][0]);
        }
        $answer = [];
        $pagination = &$response['paginationOutput'][0];

        if (ARG_2=="searching") {
            $answer = [
                "totalPages"=>$pagination['totalPages'][0],
                "totalEntries"=>$pagination['totalEntries'][0]
            ];
        } else {
            /** Parse of the Result and Import to Base**/
            include_once "core/api/eBay/parsers/".ARG_3."/".$p['command'].".php";

            JSON::save("modules/ebay-client/price-config.init", $p['price']);
        }

        print JSON::encode($answer);
        break;
    case "clean":
          $items = JSON::load("php://input");
          print $mySQL->inquiry(
            "DELETE FROM cb_things WHERE ThingID IN ({arr})",
            $items
          )['affected_rows'];
          break;
    default:
        break;
}
