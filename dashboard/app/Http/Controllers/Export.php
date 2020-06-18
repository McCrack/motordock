<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\ItemModel;
use App\SellerModel;
use App\CategoryModel;

use App\Office\Excel;

class Export extends Controller
{
    public function index($category, $period = 24, $sellers = null)
    {
    	$category = CategoryModel::where('slug', $category)->first();

        if (empty($category)) {
        	return "ERROR: Undefined Category!";
        }

        if (empty($sellers)) {
            $sellers = [];
            foreach (SellerModel::all() as $seller) {
                $sellers[] = $seller->SellerID;
            }
        } else {
            $sellers = explode('-', $sellers);
        }

    	$titles = ["Seller","Make","Named","Price","Image","Link"];
        $lastcol = range("A","Z")[count($titles)-1];

        $sheet = new Excel($lastcol);

        // Header
        $sheet->mergeCells("A1:{$lastcol}1");
        $sheet->setStyle("A1", [
            "font"=>[
                "color"=>["rgb" => "000000"],
                "bold" => true,
                "size" => 20
            ],
            "alignment" => ["horizontal" => "center"]
        ]);
        $sheet->fillCell("A1", date("d F, Y"));
        // Column Titles
        $sheet->colTitles($titles, "FFFFFF", "00ADF0");
        $sheet->setStyle("A2:{$lastcol}2", [
            "font"=>[
                "color"=>["rgb" => "FFFFFF"],
                "bold" => true,
                "size" => 11
            ],
            "fill"=>[
                "fillType" => "solid",
                "startColor" => ["rgb" => "00ADF0"]
            ],
            "alignment" => ["horizontal" => "center"]
        ]);

        $sheet->mergeCells("A3:{$lastcol}3");
		$sheet->setStyle("A3:{$lastcol}3", [
			"font"=>[
				"color"=>["rgb" => "FFFFFF"],
				"bold" => true,
				"size" => 15
			],
			"fill"=>[
				"fillType" => "solid",
				"startColor" => ["rgb" => "111111"]
			],
			"alignment" => ["horizontal" => "center"]
		]);
		$sheet->fillCell("A3", $category->name['de']);

        $timeoffset = time() - ($period * 3600);

        $table = ItemModel::where('created_at', '>', $timeoffset)
            ->where('category_id', $category->id)
            ->whereIn('seller_id', $sellers)
            ->where('status', 'available')
            ->orderBy('category_id')
            ->get();

        // Fill Sheet
        $y = 3;
        foreach ($table as &$row) {
            $y++;
            $row->slug = "https://motordock.de/{$category->slug}-{$row->id}";
            $row->named = json_decode($row->named, true)['de'] ?? "";
            $row->images = json_decode($row->images, true)[0] ?? "";
            
            $sheet->fillRow([
                $row->seller->alias,
                $row->brand->brand ?? "",
                $row->named." - ".$row->id,
                $row->selling,
                $row->images,
                $row->slug
            ], $y);
            if ($y % 2) {
                $sheet->setStyle("A{$y}:{$lastcol}{$y}", [
                    "fill"=>[
                        "fillType"   => "solid",
                        "startColor" => ["rgb"=>"EEEEEE"]
                    ]
                ]);
            }
        }
        // Set Border
        $sheet->setStyle("A2:{$lastcol}{$y}", [
            "borders" => [
                "outline" => ["borderStyle" => "thick"]
            ]
        ]);
        // Save Sheet
        $path = "/reports/{$category->slug}_".date("Y-m-d")."x{$period}.xlsx";
        $sheet->saveSheet(".{$path}");
        
        return redirect($path);
    }
}
