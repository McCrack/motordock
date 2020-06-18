<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$p = JSON::load("php://input");

$where = [$mySQL->parse(
  "created BETWEEN {int} AND {int}",
  $p['period'][0],
  $p['period'][1]
)];
if (isset($p['SellerID'])) {
  $where[] = $mySQL->parse(
    "SellerID = {int}",
    $p['SellerID']
  );
}
if (isset($p['MaxPrice'])) {
  $where[] = $mySQL->parse(
    "Price BETWEEN {int} AND {int}",
    ($p['MinPrice'] ?? 1),
    $p['MaxPrice']
  );
}
if (count($p['categories'])>1) {
  $where[] = $mySQL->parse(
    "CatID IN ({arr})",
    $p['categories']
  );
} else if(count($p['categories'])) {
  $where[] = $mySQL->parse(
    "CatID = {int}",
    $p['categories'][0]
  );
}

print $mySQL->parse("
  SELECT
    {prp}
  FROM
    cb_things
  JOIN
    cb_store USING(ThingID)
  JOIN
    cb_extended USING(ThingID)
  JOIN
    cb_categories ON cb_categories.CatID = cb_store.CategoryID
  WHERE
    {prp}
",
  implode(",", $p['fields']),
  implode(" AND ", $where)
);
exit;
$titles = array_keys($rows[0]);
$lastcol = range("A","Z")[count($titles)-1];
$fields = [
  "ID"=>5,
  "eBayID"=>10,
  "Named"=>20,
  "Image URI"=>20,
  "Condition"=>5,
  "CategoryID"=>5,
  "Category"=>10,
  "Price"=>5,
];
ini_set('display_errors', 1);
// 1. Create SpreadSheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$spreadsheet->getDefaultStyle()
  ->applyFromArray([
    "font"=>[
      "name"=>"Arial",
      "size"=>10
    ],
    "alignment"=>[
      "horizontal"=>"center"
    ]
  ]);

// 2. Heading
$sheet->mergeCells("A1:".$lastcol."1");
$sheet->getStyle("A1")
      ->getFont()
      ->setSize(20);
$sheet->setCellValue("A1", $p['header']);

$sheet->getStyle("A1")
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);
// 3. Fill titles row
$sheet->getStyle("A2:".$lastcol."2")->applyFromArray([
  "font"=>[
    "color"=>[
      "rgb"=>"FFFFFF"
    ],
    "bold"=>true,
    "size"=>11
  ],
  "fill"=>[
    "fillType"=>Fill::FILL_SOLID,
    "startColor"=>[
      "rgb"=>"00ADF0"
    ]
  ],
  "alignment"=>[
    "horizontal"=>"center"
  ]
]);
foreach ($titles as $i=>$title) {
  $sheet->getCellByColumnAndRow($i+1, 2)->setValue($title);

  $sheet->getColumnDimension(chr(64+($i+1)))
        ->setAutoSize("true");
}

// 4. Fill Spreadsheet
$evenRow = [
  "fill"=>[
    "fillType"=>Fill::FILL_SOLID,
    "startColor"=>[
      "rgb"=>"EEEEEE"
    ]
  ]
];
$y = 2;
foreach ($rows as $row) {
  $y++;
  $x = 0;
  foreach ($row as $key=>$cell) {
    if ($key=="Image") {
      $imgset = JSON::parse($cell);
      $cell = $imgset[0]['url'];
    }

    $sheet->getCellByColumnAndRow(++$x, $y)
      ->setValue($cell);

    switch ($key) {
      case "backlink":

        break;
      case "Date":
        $sheet->getCellByColumnAndRow($x, $y)
          ->setValue(Date::PHPToExcel($cell));
        $sheet->getStyle(chr(64+$x)."".$y)
          ->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);
        break;
      case "Price":
        $sheet->getStyle(chr(64+$x)."".$y)
          ->getNumberFormat();
        break;
      case "eBayID":
        $sheet->getStyle(chr(64+$x)."".$y)
          ->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_NUMBER);
    }
  }
  if ($y % 2) {
    $sheet->getStyle("A".$y.":".$lastcol."".$y)
          ->applyFromArray($evenRow);
  }
}

$sheet->getStyle("A2:".$lastcol."".$y)->applyFromArray([
  "borders" => [
    "outline" => [
      "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK
    ]
  ]
]);

// 5. Save Spreadsheet to file
$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
$writer->save("data/Excel/".$p['endfile'].".xlsx");
//$writer->save('php://output');

// 6. Return file name
print "/data/Excel/".$p['endfile'].".xlsx";
