<?php

namespace App\Office;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Excel
{
	public $lastcol;
	public $sheet;
	public $spreadsheet;

	public function __construct($lastcol)
	{
		$this->lastcol = $lastcol;
		$this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();

        $this->setDefaultStyle();
	}

	public function setDefaultStyle($fontFamily = "Arial", $fontSize = 10, $align = "center")
	{
		$this->spreadsheet
            ->getDefaultStyle()
            ->applyFromArray([
                "font"=>[
                    "name" => $fontFamily,
                    "size" => $fontSize
                ],
                "alignment"=>[
                    "horizontal" => $align
                ]
            ]);
	}
	public function setStyle($range, $options)
	{
		$this->sheet->getStyle($range)->applyFromArray($options);
	}
	public function colTitles($titles, $color = "000000", $background = "FFFFFF")
	{
		foreach ($titles as $i=>$title) {
            $this->sheet->getCellByColumnAndRow($i+1, 2)->setValue($title);

            $this->sheet->getColumnDimension(chr(64+($i+1)))
                ->setAutoSize("true");
        }
	}

	public function fillRow($row, $y)
	{
		foreach ($row as $x => $val) {
			$this->sheet
				->getCellByColumnAndRow($x + 1, $y)
				->setValue($val);
		}
		
	}
	public function mergeCells($range)
	{
		$this->sheet->mergeCells($range);
	}
	public function fillCell($offset, $value)
	{
		$this->sheet->setCellValue($offset, $value);
	}

	public function saveSheet($path)
	{
		$writer = IOFactory::createWriter($this->spreadsheet, "Xlsx");
        $writer->save($path);
        //$writer->save('php://output');
	}
}