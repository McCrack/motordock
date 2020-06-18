<?php

namespace App\eBay\Tools;

use Illuminate\Support\Str;

class Parser
{
	public static function named($named)
	{
		$named = preg_replace("/(\s&)*\s*WARRANTY/i", '', $named);
		$named = preg_replace("/\s*-*\s*\d+$/", '', $named);
		$named = preg_replace("/\s{2,}/", ' ', $named);

		return $named;
	}
	public static function price($purchase, $categoryId = 0)
	{
		//$factors = \JSON::load(app_path()."/eBay/config.json")['price'];
		$factors = \Auth::user()->config['eBay']['price'];
		//dd(\Auth::user()->config);

		$price = $purchase * $factors['Currency Rate'] * $factors['eBay Tax'];
		if (isset($factors['special formules'][$categoryId])) {
			$factor = eval("return(" . $factors['special formules'][$categoryId] . ");");
			$price *=  ($factor < 1.3) ? 1.3 : $factor;
		} else {
			foreach (array_reverse($factors['price gradation'], true) as $threshold => $ratio) {
				if ($price > $threshold) {
					$price *= $ratio;
					break;
				}
			}
		}
		$price *= $factors['Tax'];
		return round($price);
	}
	public static function checkMotor($str, $motors)
	{
		$article = null;
		foreach ($motors as $motor) {
			$reg = preg_quote($motor->article, "/");
			if (preg_match("/\b({$reg})\b/i", $str)) {
				$article = $motor->motor_id;
			}
		}
		return $article;
	}
	public static function checkLineup($str, $models)
	{
		$lineup = [];
		foreach ($models as $model) {
			if (preg_match("/\b({$model->regular})\b/i", $str)) {
				$lineup[] = $model->line_id;
			}
		}
		return $lineup;
	}
	public static function options($item)
	{
		$options = [
			'condition' => (string) $item->ConditionDisplayName
		];
		$brands = [
			'vauxhall'	=> "Opel",
			'mercedes'	=> "Mercedes-Benz",
			'vw'		=> "Volkswagen"
		];
		$models = [
			'1 series'	=> "1er",
			'2 series'	=> "2er",
			'3 series'	=> "3er",
			'4 series'	=> "4er",
			'5 series'	=> "5er",
			'6 series'	=> "6er",
			'7 series'	=> "7er",
			'8 series'	=> "8er",
			'9 series'	=> "9er"
		];
		foreach ($item->ItemSpecifics->NameValueList as $option) {
			switch ((string) $option->Name) {
				case "Brand":
					$brand = (string) $option->Value;
					if (isset($brands[strtolower($brand)])) {
						$brand = $brands[strtolower($brand)];
					}
					$options['brand'] = $brand;
					break;
				case "Model/Series":
					$model = (string) $option->Value;
					$options['model'] = preg_replace("/series/i", "er", $model);
					break;
				case "Year":
					$options['year'] = (string) $option->Value;
					break;
				case "Manufacturer Part Number":
					$options['Part Number'] = (string) $option->Value;
					break;
				default:
					break;
			}
		}
		return $options;
	}
	public static function mediaset($item)
	{
		$mediaset = [];
		foreach ($item->PictureURL as $img) {
			$url = parse_url((string) $img);
			$imgkey = explode("/", $url['path'])[5];
			$mediaset[] = "https://i.ebayimg.com/images/g/{$imgkey}/s-l600.jpg";
		}
		return $mediaset;
	}

	public static function getBrandID($brands, $brand, $named)
	{
		return $brands->where('brand', $brand)->first() ?? (function () use ($brands, $named) {
			$brand = null;

			if (empty($brand)) {
				foreach ($brands as $itm) {
					if (preg_match("/\b({$itm->regular})\b/i", $named)) {
						$brand = $itm;
						break;
					}
				}
			}
			return $brand;
		})();
	}

	public static function translate($str, $dictionary = [])
	{
		foreach ($dictionary as $row) {
			$str = preg_replace("/\b{$row->word}\b/i", $row->de, $str);
		}
		return $str;
	}
}
