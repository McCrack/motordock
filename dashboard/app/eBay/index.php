<?php

namespace App\eBay;

use App\Core\Config as Cng;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use View;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\eBay\Tools\Parser;

use App\eBay\Models\Finding;
use App\eBay\Models\Trading;
use App\eBay\Models\Shopping;

use App\User;
use App\ThingModel;
use App\StoreModel;
use App\ExtendedModel;
use App\BrandModel;
use App\LineupModel;
use App\SellerModel;
use App\WordlistModel;
use App\DictionaryModel;
use App\CategoryModel as CatModel;
use App\ExtendedModel as ExtModel;
use App\eBayCaheModel as CacheModel;

class index extends Controller
{
	public function index($category_id = null)
	{
		$cng = Cng::getInstance();
		$categories = CatModel::all();
		$favorites = $categories->where('favorite', 1);
		$category = $categories->find($category_id ?? $cng->{'default category'});
		$tree = [];
		if ($favorites->contains('id', $category->id)) {
			define('ALL_CATEGORIES', false);
		} else {
			define('ALL_CATEGORIES', true);
			foreach ($categories->toArray() as $row) {
				foreach ($row as $key => $val) {
					$tree[$row['parent_id']][$row['id']][$key] = $val;
				}
			}
		}
		return view("eBay.index", [
			'sellers'		=> SellerModel::all(),
			'tree'			=> $tree,
			'favorites'		=> $favorites,
			'category'		=> $category,
			'dictionary'	=> DictionaryModel::all()
		]);
	}

	public function allCategories(Request $request)
	{
		$categories = CatModel::all();
		$favorites = $categories->where('favorite', 1);
		$category = $categories->find(ARG_0);
		$tree = [];
		foreach ($categories->toArray() as $row) {
			foreach ($row as $key => $val) {
				$tree[$row['parent_id']][$row['id']][$key] = $val;
			}
		}
		define('ALL_CATEGORIES', true);
		return view("eBay.components.categories", [
			'tree'		=> $tree,
			'favorites'	=> $favorites,
			'category'	=> $category
		]);
	}
	public function ld_tab(Request $request)
	{
		$data = $this->{ARG_0}();
		return view("eBay.components." . ARG_0, $data);
	}
	private function options()
	{
		$cng = Cng::getInstance();
		$details = Trading::market($cng->eBay['myMarket'])->getDetails('ShippingServiceDetails');
		return [
			'cng'		=> $cng,
			'favorites' => CatModel::where('favorite', 1)->get(),
			'services'	=> $details['ShippingServiceDetails']
		];
	}
	private function sellers()
	{
		return [
			'cng'		=> Cng::getInstance(),
			'sellers'	=> $sellers = SellerModel::all()
		];
	}
	private function category()
	{
		$category_id = defined("ARG_1") ? ARG_1 : $cng->{'default category'};
		return [
			'cng'		=> Cng::getInstance(),
			'category'	=> CatModel::find($category_id),
		];
	}
	private function inStore()
	{
		$cng = Cng::getInstance();
		$timestamp = time();
		return [
			'cng'	=> $cng,
			'items'	=> Trading::market($cng->eBay['myMarket'])
				->StartTimeFrom($timestamp - 864000)
				->StartTimeTo($timestamp + 864000)
				->getItems()
		];
	}
	public function preparation(Request $request)
	{
		$cng = Cng::getInstance();

		View::share('Carbon', new Carbon);

		$items = Shopping::market($request->market)->getMultiple($request->items);
		$brands = BrandModel::select(
			'BrandID',
			'brand',
			'regular',
			'available'
		)->get();

		$dictionary = WordlistModel::getInstance();

		$specificsTemplate = [];
		foreach ($items as $item) {
			$PrimaryCategory = CatModel::where('EBAY-GB', $item->PrimaryCategoryID)->first();

			$item->CategoryID = $PrimaryCategory->id;
			$item->PrimaryCategoryID = $PrimaryCategory->{'EBAY-DE'};
			$item->PrimaryCategory = $PrimaryCategory->name['de'];
			$item->ShippingCost = $PrimaryCategory->delivery_price;
			if (isset($item->SecondaryCategoryID)) {
				$SecondaryCategory = CatModel::where('EBAY-GB', $item->SecondaryCategoryID)->first();
				$item->SecondaryCategoryID = $SecondaryCategory->{'EBAY-DE'};
				$item->SecondaryCategory = $SecondaryCategory->name['de'];
			}
			$item->price = Parser::price(
				$item->CurrentPrice,
				$PrimaryCategory->id
			);

			$keywords = [];
			$singular = Str::singular($PrimaryCategory->name['en']);
			$singular = preg_quote($singular, "/");
			$title = preg_replace("/\b({$singular})\b/i", "", $item->Title);


			$vendors = [];
			foreach ($brands as $vendor) {
				if (preg_match("/\b({$vendor->regular})\b/i", $title)) {
					$vendors[$vendor->BrandID] = $vendor->brand;
					$keywords[] = strtolower($vendor->brand);

					$title = preg_replace("/\b({$vendor->regular})\b/i", $vendor->brand, $title);

					$lineups = LineupModel::select(
						'line_id',
						'regular',
						'model'
					)->where('BrandID', $vendor->BrandID)->get();
					$models = [];
					foreach ($lineups as $lineup) {
						if (preg_match("/\b({$lineup->regular})\b/i", $title)) {
							$keywords[] = strtolower($lineup->model);
						}
					}
				}
			}
			$item->brands = $vendors;

			foreach ($dictionary as $word) {
				$en = preg_quote($word->en, "/");
				if (preg_match("/\b({$en})\b/i", $title)) {
					$title = preg_replace("/\b{$en}\b/i", $word->de, $title);
					$keywords[] = strtolower($word->de);
				}
			}
			$item->named = $item->PrimaryCategory;
			$item->keywords = array_map(function ($str) use ($keywords, $item) {
				$str = trim($str);
				if (in_array(strtolower($str), $keywords)) {
					$item->named .= " " . $str;
					return "
					<div class='keyword btn-dark' draggable='true' ondragstart='dragKeyword(event)'>
						<hr>
						<div contenteditable='true'>{$str}</div>
						<span class='drop' onclick='removeKeyword(this.parentNode)'></span>
					</div>";
				} elseif (strlen($str)) {
					$item->named .= " " . $str;
					return "
					<div class='keyword btn-light' draggable='true' ondragstart='dragKeyword(event)'>
						<hr>
						<div contenteditable='true'>{$str}</div>
						<span class='drop' onclick='removeKeyword(this.parentNode)'></span>
					</div>";
				}
			}, preg_split("/\b(" . implode(")\b|\b(", $keywords) . ")\b/i", $title, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));

			if (empty($specificsTemplate[$PrimaryCategory->id])) {
				$specificsTemplate[$PrimaryCategory->id] = Trading::GetSpecifics($PrimaryCategory->{'EBAY-DE'}, 77);
			}
			$specifics = $specificsTemplate[$PrimaryCategory->id];

			foreach ($item->ItemSpecifics['NameValueList'] as $option) {
				if (is_array($option)) {
					$specific = WordlistModel::getTranslate('en', 'de', $option['Name']);
					if (isset($specifics[$specific])) {
						$specifics[$specific]['Value'] = WordlistModel::getTranslate('en', 'de', $option['Value']);
					}
				}
			}
			$item->specifics = $specifics;
		}
		$details = Trading::market($cng->eBay['myMarket'])->getDetails('ShippingServiceDetails');
		return view("eBay.components.preparation", [
			'cng'				=> $cng,
			'items'				=> $items,
			'shippingServices'	=> $details['ShippingServiceDetails']
		]);
	}
	public function search(Request $request)
	{
		$cng = Cng::getInstance();
		$filters = [];
		$category = CatModel::find($request->category);
		$stores = SellerModel::find($request->sellers);

		$query = Finding::market($cng->markets[$request->market])
			->category($category->category{
				'EBAY-GB'})
			->keywords(base64_decode($request->queryRow));

		if ($stores->count() > 0) {
			foreach ($stores as $store) {
				$query->store($store->SellerName);
			}
		}
		foreach ($request->filters as $set => $values) {
			$query->filterset($set, $values);
		}
		$items = $query->advanced($request->offset ?? 1);

		foreach ($items as &$item) {
			$item->price = Parser::price(
				$item->sellingStatus['currentPrice']['__value__'],
				$request->category
			);
			if (CacheModel::where('src_id', $item->itemId)->exists()) {
				$item->itemId = null;
			}
			/*
			if (ExtModel::where('eBayID', $item->itemId)->exists()) {
				$item->itemId = null;
			}
			*/
		}

		return view('eBay.components.list', [
			'cng'		=> $cng,
			'items'		=> $items,
			'category'	=> $request->category
		]);
	}
	public function addItem(Request $request)
	{
		// 'Description'		=> $request->Title,
		$cng = Cng::getInstance();
		$result = Trading::market($cng->eBay['myMarket'])->create([
			'Title'				=> $request->Title,
			'SKU'				=> $request->SKU,
			'StartPrice'		=> $request->Price,
			'Quantity'			=> $request->Quantity,
			'DispatchTimeMax'	=> $request->DispatchTimeMax,
			'PrimaryCategory'	=> [
				'CategoryID'	=> $request->CategoryID,
			],
			'ConditionID'		=> $request->ConditionID,
			'ConditionDescription' => $request->ConditionDescription ?? "",
			"DispatchTimeMax"	=> $request->DispatchTimeMax,
			'PictureDetails'	=> [
				'GalleryType'	=> "Gallery",
				'PictureURL' 	=> $request->images,
			],
			'ItemSpecifics'		=> [
				'NameValueList'	=> $request->itemSpecifics,
			],
			'ShippingDetails'	=> array_merge($cng->eBay['ShippingDetails'], $request->ShippingDetails),
			"HitCounter"		=> $cng->eBay['HitCounter'],
			"Site"				=> $cng->eBay['Site'],
			"Country"			=> $cng->eBay['Country'],
			"Location"			=> $cng->eBay['Location'],
			"PostalCode"		=> $cng->eBay['PostalCode'],
			"Currency"			=> $cng->eBay['Currency'],
			"ListingDuration"	=> $cng->eBay['ListingDuration'],
			"PaymentMethods"	=> $cng->eBay['PaymentMethods'],
			"PayPalEmailAddress" => $cng->eBay['PayPalEmailAddress'],
			"PrivateListing"	=> $cng->eBay['PrivateListing'],
			'ReturnPolicy'		=> $cng->eBay['ReturnPolicy'],
		]);
		if ($result['Ack'] == "Success") {
			CacheModel::create([
				'src_id' => $request->SKU,
				'itm_id' => $result['ItemID']
			]);
		}

		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function import(Request $request)
	{
		$items = [];
		foreach ($request->all() as $itm) {
			$items[$itm['eBay_id']] = (object) $itm;
		}
		$xml = Client::GetMultipleItems($items);

		$timestamp = time();
		$sellers = SellerModel::all();

		$brands = BrandModel::select(
			'BrandID',
			'brand',
			'regular',
			'available'
		)->get();

		$lineups = LineupModel::all();
		$dictionary = DictionaryModel::select('word', 'de')
			->orderBy('sort_id', 'DESC')
			->get();
		$response = [];
		foreach ($xml->Item as $item) {
			$ItemID = (string) $item->ItemID;

			$named = Parser::named((string) $item->Title);
			$translate = Parser::translate($named, $dictionary);

			$options = Parser::options($item);

			$brand = strtolower($options['brand'] ?? "");
			$brand = Parser::getBrandID($brands, $brand, $named);

			$ThingID = ThingModel::create([
				'type'      => "showcase",
				'created'   => $timestamp,
				'modified'  => $timestamp
			])->ThingID;

			$MotorID = null;
			$ModelID = null;
			if (isset($brand)) {
				$BrandID = $brand->BrandID;
				if ($brand->available > 0) {

					if (in_array($items[$ItemID]->category, [179680, 174119])) {
						$LineID = null;
					} else {

						$lineup = Parser::checkLineup(
							($translate . " " . ($options['model'] ?? "")),
							$lineups->where('BrandID', $BrandID)
						);
						if (empty($lineup)) {
							$LineID = null;
							DB::table('cb_errors')
								->insert([
									'idx'       => 2,
									'ThingID'   => $ThingID,
									'status'    => "Lineup is undefined"
								]);
						} elseif (count($lineup) > 1) {
							$LineID = null;
							DB::table('cb_errors')
								->insert([
									'idx'       => 3,
									'ThingID'   => $ThingID,
									'status'    => "Lineup is ambiguous"
								]);
						} else {
							$LineID = $lineup[0];


							if ($items[$ItemID]->category == 225) {
								$motors = DB::table('motors_vs_lineups')
									->join('cb_motors', 'motors_vs_lineups.motor_id', '=', 'cb_motors.motor_id')
									->select('cb_motors.motor_id', 'article')
									->where('line_id', $LineID)
									->get();

								if ($motors->count() > 0) {
									$MotorID = Parser::checkMotor(
										($translate . " " . ($options['Part Number'] ?? "")),
										$motors
									);
								}
							}
						}
					}
				} else {
					$obj = ThingModel::find($ThingID);
					$obj->delete();
					continue;
				}
			} else {
				$LineID = null;
				$BrandID = null;
				DB::table('cb_errors')
					->insert([
						'idx'       => 1,
						'ThingID'   => $ThingID,
						'status'    => "Brand is undefined"
					]);
			}
			$mediaset = Parser::mediaset($item);

			if ($sellers->contains('SellerName', $items[$ItemID]->SellerName)) {
				$SellerID = $sellers->where('SellerName', $items[$ItemID]->SellerName)->first()->SellerID;
			} else {
				$SellerID = SellerModel::create([
					'StoreName'	=> $items[$ItemID]->StoreName,
					'SellerName' => $items[$ItemID]->SellerName,
					'alias'		=> $items[$ItemID]->StoreName,
					'market'	=> $items[$ItemID]->market
				])->SellerID;
			}



			StoreModel::create([
				'ThingID'   => $ThingID,
				'status'    => "available",
				'category_id' => $items[$ItemID]->category,

				'BrandID'   => $BrandID,
				'line_id'   => $LineID,
				'motor_id'	=> $MotorID,
				'named'     => \JSON::encode([
					'en' => $named,
					'de' => $translate
				]),

				'selling'   => Parser::price(
					(string) $item->CurrentPrice[0],
					$items[$ItemID]->category
				),
				'preview'   => (string) $item->PictureURL[0]
			]);
			ExtendedModel::create([
				'ThingID'   => $ThingID,
				'SellerID'  => $SellerID,
				'ReferenceID' => (string) $item->SKU,
				'eBayID'    => $ItemID,

				'purchase'  => (string) $item->CurrentPrice[0],
				'currency'  => (string) $item->CurrentPrice['currencyID'],

				'options'       => \JSON::encode($options),
				'images'        => \JSON::encode($mediaset)
			]);
			$response[] = $ItemID;
		}
		return \JSON::encode($response);
	}
	public function sv_category(Request $request)
	{

		$category = CatModel::find(ARG_0);
		foreach ($request->all() as $field => $value) {
			$category->{$field} = $value;
		}
		$category->save();
	}
	public function sv_options(Request $request)
	{
		$f = function ($f, $path, &$cng, $value) {
			$item = array_shift($path);
			if (empty($path)) {
				$cng[$item] = $value;
			} else {
				if (empty($cng[$item])) {
					$cng[$item] = [];
				}
				$f($f, $path, $cng[$item], $value);
			}
		};
		$user = User::find(\Auth::user()->id);
		$config = $user->config;
		foreach ($request->all() as $field => $value) {
			$path = explode(".", $field);
			$f($f, $path, $config['eBay'], $value);
		}
		$user->config = $config;
		$user->save();
		return 1;
	}
	public function sv_seller(Request $request)
	{
		$seller = SellerModel::find(ARG_0);
		$seller->alias = $request->alias;
		return (int) $seller->save();
	}
	public function rm_seller(Request $request)
	{
		$seller = SellerModel::find(ARG_0);
		return (int) $seller->delete(ARG_0);
	}
	public function ch_dictionary(Request $request)
	{
	}
}
