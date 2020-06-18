<?php

namespace App\Storekeeper;

use Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\eBay\Client;
use App\eBay\Tools\Parser;

use App\ItemModel;
use App\BrandModel;
use App\ThingModel;
use App\StoreModel;
use App\SellerModel;
use App\LineupModel;
//use App\VehicleModel;
use App\ExtendedModel;
use App\CategoryModel;
use App\DictionaryModel;

class Storekeeper
{
	public $taskmap;

    private $brands;
    private $models;
    private $categories;
    private $dictionary;

	public function __construct()
	{
        if (file_exists("../app/Storekeeper/taskmap.json")) {
            $taskmap = file_get_contents("../app/Storekeeper/taskmap.json");
            $this->taskmap = json_decode($taskmap, true);
        }
	}

    /*****************/
    public function createTask($task, $tag, $affected)
    {
        StorekeeperModel::create([
            'timestamp'   => time(),
            'tag'         => $tag,
            'task'        => $task,
            'affected'    => $affected
        ]);
    }
    /*****************/

    public function checkNewItems($tag)
    {
        $seller = SellerModel::find($tag);
        $timeoffset = StorekeeperModel::latest('timestamp')
            ->where('tag', $tag)
            ->first()
            ->timestamp ?? (time() - (3600 * 24));

        if ((time() - $timeoffset) > 3600) {
            $response = Client::findItemsIneBayStores($timeoffset, $seller->StoreName);
            if ($response['ack'] == "Success") {
                if (count($response['items']) > 0) {
                    $this->categories = CategoryModel::select('id', 'EBAY-GB')
                        ->where('status', "enabled")
                        ->get();

                    return $this->importItems(
                        $timeoffset,
                        $seller,
                        $response
                    );
                }
            }
        }
        return 0;
    }
    private function importItems($timeoffset, $seller, $data)
    {
        $amount = 0;
        foreach ($data['items'] as $item) {
            $exists = ExtendedModel::select('ThingID')
                ->where('eBayID', $item['itemId'][0])
                ->first()
                ->ThingID ?? false;
            if ($exists) {
                continue;
            }
            if ($this->categories->contains('EBAY-GB', $item['primaryCategory'][0]['categoryId'][0])) {
                $category_id = $this->categories->where('EBAY-GB', $item['primaryCategory'][0]['categoryId'][0])->first()->id;
                $timestamp = time();
                $ThingID = ThingModel::create([
                    'type'      => "showcase",
                    'created'   => $timestamp,
                    'modified'  => $timestamp
                ])->ThingID;
                StoreModel::create([
                    'ThingID'   => $ThingID,
                    'status'    => "new",
                    'category_id'=> $category_id,
                    'selling'   => Parser::price(
                        $item['sellingStatus'][0]['currentPrice'][0]['__value__'],
                        $category_id
                    ),
                    'preview'   => $item['pictureURLLarge'][0] ?? $item['galleryURL'][0]
                ]);
                ExtendedModel::create([
                    'ThingID'   => $ThingID,
                    'eBayID'    => $item['itemId'][0],
                    'SellerID'  => $seller->SellerID,
                    'purchase'  => $item['sellingStatus'][0]['currentPrice'][0]['__value__'],
                    'currency'  => $item['sellingStatus'][0]['currentPrice'][0]['@currencyId']
                ]);

                $amount++;
            }
        }
        if ($data['pagination']['pageNumber'][0] < $data['pagination']['totalPages'][0]) {
            $response = Client::findItemsIneBayStores(
                $timeoffset,
                $seller->StoreName,
                $data['pagination']['pageNumber'][0] + 1
            );
            if ($response['ack'] == "Success") {
                if ((INT)$response['pagination']['totalPages'][0] > 0) {
                    $amount += self::importItems(
                        $timeoffset,
                        $seller,
                        $response
                    );
                }
            }
        }
        return $amount;
    }

    /*****************/

    public function checkCompletedItems($tag)
    {
        $sellers = SellerModel::all();

        $timeoffset = StorekeeperModel::latest('timestamp')
            ->where('tag', $tag)
            ->first()
            ->timestamp ?? (time() - (3600 * 24));

        if ((time() - $timeoffset) > 3600) {
            $response = Client::findCompletedItems($timeoffset, $sellers);
            if ($response['ack'] == "Success") {
                if (count($response['items'])) {
                    return self::RemovingItems(
                        $timeoffset,
                        $sellers,
                        $response
                    );
                }
            }
        }
        return 0;
    }
    private static function RemovingItems($timeoffset, $sellers, $data)
    {
        $amount = 0;
        $timestamp = time();
        foreach ($data['items'] as $item) {
            $itm = ExtendedModel::where('eBayID', $item['itemId'][0])->first();
            if ((BOOL)$itm) {
                $itm->thing->update([
                    'modified'  => $timestamp
                ]);
                $itm->thing->store->update([
                    'status'    => "deleted"
                ]);
                $amount++;
            }
        }

        if ($data['pagination']['pageNumber'][0] < $data['pagination']['totalPages'][0]) {
            $response = Client::findCompletedItems(
                $timeoffset,
                $sellers,
                $data['pagination']['pageNumber'][0] + 1
            );

            if ($response['ack'] == "Success") {
                if ((INT)$response['pagination']['totalPages'][0] > 0) {
                    $amount += self::RemovingItems(
                        $timeoffset,
                        $sellers,
                        $response
                    );
                }
            }
        }
        return $amount;
    }

    /*****************/

    public function checkExtendedData()
    {
        if (StoreModel::where('status', 'new')->count() > 0) {
            $this->brands = BrandModel::select(
                'BrandID',
                'brand',
                'regular',
                'available'
            )->get();

            $this->lineups = LineupModel::all();

            $this->dictionary = DictionaryModel::select('word', 'de')
                ->orderBy('sort_id', 'DESC')
                ->get();

            $timestamp = time();
            return $this->getExtendedData($timestamp);
        }
        return 0;
    }

    private function getExtendedData($timestamp)
    {
        $items = ItemModel::select('id', 'eBay_id', 'seller_id', 'category_id')
            ->where('status', 'new')
            ->limit(20)
            ->get();
        $affected = 0;
        if ($items->count() > 0) {
            $xml = Client::GetMultipleItems($items);
            if ((STRING)$xml->Ack == "Success") {
                foreach ($xml->Item as $item) {
                    $record = $items->where('eBay_id', (STRING)$item->ItemID)->first();
                    $ThingID = $record->id;
                    $SellerID = $record->seller_id;

                    $exists = ExtendedModel::where('SellerID', $SellerID)
                       ->where('ReferenceID', (STRING)$item->SKU)
                        ->count();
                    if ($exists) {
                        $obj = ThingModel::find($ThingID);
                        $obj->delete();
                        continue;
                    }
                    $named = Parser::named((STRING)$item->Title);
                    $translate = Parser::translate($named, $this->dictionary);
                    $options = Parser::options($item);

                    $brand = strtolower($options['brand'] ?? "");
                    $brand = Parser::getBrandID($this->brands, $brand, $named);

                    $ModelID = null;
                    if (isset($brand)) {
                        $BrandID = $brand->BrandID;
                        if ($brand->available > 0) {
                            
                            if (in_array($record->category_id, [179680, 174119])) {
                                $LineID = null;
                            } else {
                                $lineup = Parser::checkLineup(
                                    ($options['model'] ?? $translate),
                                    $this->lineups->where('BrandID', $BrandID),
                                );
                                if (empty($lineup)) {
                                    $LineID = null;
                                    DB::table('cb_errors')
                                        ->insert([
                                            'idx'       => 2,
                                            'ThingID'   => $ThingID,
                                            'status'    => "Lineup is undefined"
                                        ]);
                                } elseif(count($lineup) > 1) {
                                    $LineID = null;
                                    DB::table('cb_errors')
                                        ->insert([
                                            'idx'       => 3,
                                            'ThingID'   => $ThingID,
                                            'status'    => "Lineup is ambiguous"
                                        ]);
                                } else {
                                    $LineID = $lineup[0];
                                    LineupModel::where('line_id', $LineID)
                                        ->update([
                                            'available' => 1
                                        ]);
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

                    StoreModel::find($ThingID)->update([
                        'BrandID'   => $BrandID,
                        'line_id'   => $LineID,
                        'status'    => "available",
                        'named'     => json_encode([
                            'en' => $named,
                            'de' => $translate,
                        ], JSON_UNESCAPED_UNICODE)
                    ]);
                    ExtendedModel::find($ThingID)->update([
                        'ReferenceID'   => (STRING)$item->SKU,
                        'options'       => json_encode($options, JSON_UNESCAPED_UNICODE),
                        'images'        => json_encode($mediaset, JSON_UNESCAPED_UNICODE)
                    ]);

                    $affected++;
                }

                if ((time() - $timestamp) < 30) {
                    // Recursion
                    sleep(3);
                    $affected += $this->getExtendedData($timestamp);
                }
            }
        }
        return $affected;
    }

    /*****************/

    public function checkCompatibility()
    {
        if (ExtendedModel::whereNull('compatibility')->count() > 0) {
            $timestamp = time();
            //$this->lineups = LineupModel::all();
            return $this->getCompatibility($timestamp);
        }
        return 0;
    }

    private function getCompatibility($timestamp)
    {
        $items = ItemModel::select('id', 'eBay_id', 'seller_id')
            ->where('status', 'available')
            ->whereNull('compatibility')
            ->limit(10)
            ->get();
        $affected = 0;
        if ($items->count() > 0) {
            foreach ($items as $i => $item) {
                $xml = Client::GetSingleItem($item->eBay_id);
                if ((STRING)$xml->Item->ListingStatus != "Active") {
                    StoreModel::find($item->id)
                        ->update([
                            'status' => "deleted"
                        ]);

                    ThingModel::find($item->id)
                        ->update([
                            'modified' => $timestamp
                        ]);
                } else {
                    $table = [];
                    if (isset($xml->Item->ItemCompatibilityList)) {
                        foreach ($xml->Item->ItemCompatibilityList->Compatibility as $row) {
                            foreach ($row->NameValueList as $cell) {
                                if (isset($cell->Name)) {
                                    $table[(STRING)$cell->Name][] = (STRING)$cell->Value;
                                }
                            }
                        }
                    }
                    ExtendedModel::find($item->id)
                        ->update([
                            'compatibility' => json_encode($table, JSON_UNESCAPED_UNICODE)
                        ]);

                    $affected++;
                }
            }

            if ((time() - $timestamp) < 30) {
                // Recursion
                sleep(2);
                $affected += $this->getCompatibility($timestamp);
            }
        }
        return $affected;
    }

    /*****************/

	public function createReport($task)
	{
		$tasks = StorekeeperModel::select(
            'task',
            DB::raw("SUM(amount) AS amount")
        )
        ->where('timestamp', '>', (time() - 86400))
        ->groupBy('task')
        ->get();

        foreach ($tasks as $item) {
         	if ($item->task == "getItems") {
         		$getItems = $item->amount;
         	} elseif ($item->task == "softDeleting") {
         		$softDelete = $item->amount;
         	}
         }

        $orders = DB::table('cb_orders')
            ->select('order_number','created_at','price')
            ->where('created_at', '>', (time() - 86400))
            ->get();
        foreach ($orders as $order) {
            $orderNum = str_pad($order->order_number, 3, '0', STR_PAD_LEFT);
            $order->order_number = date("mY{$orderNum}");
        }

        $path = $this->exportToSpreadsheet();

        Mail::send('mails.report', [
            'getRecords'    => $getItems,
            'softDeletion'  => $softDelete,
            'orders'  => $orders
        ], function($message) use ($path){
            //$message->to('datamorg@gmail.com')
            $message->to('info@motordock.de')
                ->subject("Motordock reports");
            $message->from('bestellung@motordock.de','Motordock Site');
            $message->attach($path, []);
        });

        $this->completeTask("SUCCESS");
	}

    /****************/

    public function checkOrders()
    {
        $orders = DB::table('cb_orders')
            ->join('cb_community', function($join){
                $join->on('cb_community.CommunityID','cb_orders.CommunityID');
            })
            ->where('status', "new")
            ->get();
        if ($orders->count() > 0) {
            $timestamp = time();

            foreach ($orders as $order) {
                DB::table('cb_orders')
                    ->where('OrderID', $order->OrderID)
                    ->update([
                        'status'    => "accepted",
                        'updated_at'=> $timestamp
                    ]);
            }

            self::buildOrders($orders);

            \Mail::send('mails.orders', [
                'orders'  => $orders
            ], function($message){
                //$message->to('datamorg@gmail.com')
                $message->to('info@motordock.de')
                    ->subject("Motordock reports");
                $message->from('bestellung@motordock.de','Motordock Site');
            });
        }
        return 0;
    }
    public static function buildOrders($orders)
    {
        foreach ($orders as &$order) {
            
            $items = DB::table('orders_vs_store')
            ->select(
                'cb_store.ThingID',
                'eBayID',
                'named',
                'preview',
                'selling',
                'StoreName',
                'delivery_price'
            )
            ->join('cb_store', function($join){
                $join->on('cb_store.ThingID', 'orders_vs_store.ThingID');
            })
            ->join('cb_categories', function($join){
                $join->on('cb_categories.id', 'cb_store.category_id');
            })
            ->join('cb_extended', function($join){
                $join->on('cb_extended.ThingID', 'orders_vs_store.ThingID');
            })
            ->join('cb_sellers', function($join){
                $join->on('cb_sellers.SellerID', 'cb_extended.SellerID');
            })
            ->where('OrderID', $order->OrderID)
            ->get();

            $order->delivery_price = 0;
            if (!empty($items)) {
                $order->items = [];
                foreach ($items as &$item) {
                    $item->named = json_decode($item->named, true)['de'];
                    if ($item->delivery_price > $order->delivery_price) {
                        $order->delivery_price = $item->delivery_price;
                    }
                    $order->items[$item->eBayID] = $item;
                    $item->eBay_id = $item->eBayID;
                }

                $xml = Client::GetMultipleItems($items);

                foreach ($xml->Item as $item) {
                    $eBayID = (STRING)(STRING)$item->ItemID;
                    $order->items[$eBayID]->status = (STRING)$item->ListingStatus;
                    $order->items[$eBayID]->tag = (STRING)$item->SKU;
                    $order->items[$eBayID]->link = (STRING)$item->ViewItemURLForNaturalSearch;
                }
            }
            $order->order_number = str_pad($order->order_number, 3, '0', STR_PAD_LEFT);
            $order->order_number = date("mY{$order->order_number}");

            $order->delivery = json_decode($order->delivery, true);
        }
    }

    /****************/

    public function cleaning()
    {
        $timestamp = time();
        $cnt = ItemModel::where('status', "available")
            ->where('updated_at', '<', ($timestamp - (3600 * 48)))
            ->count();
        if ($cnt) {
            return $this->cleanStoreBase($timestamp);
        }
        return 0;
    }
    private function cleanStoreBase($timestamp, $timeoffset = 0)
    {
        $items = ItemModel::select('id', 'eBay_id', 'updated_at')
            ->where('status', "available")
            ->whereBetween('updated_at', [$timeoffset, ($timestamp - (3600 * 48))])
            ->orderBy('updated_at')
            ->limit(20)
            ->get();

        $affected = 0;
        if ($items->count() > 0) {
            $xml = Client::GetMultipleItems($items);
            if ((STRING)$xml->Ack == "Success") {
                foreach ($xml->Item as $item) {
                    $record = $items->where('eBay_id', (STRING)$item->ItemID)->first();
                    $timeoffset = $record->updated_at;
                    if ((STRING)$item->ListingStatus != "Active") {
                        StoreModel::find($record->id)->update([
                            'status' => "deleted"
                        ]);
                        $affected++;
                    }
                    ThingModel::find($record->id)->update([
                        'modified' => $timestamp
                    ]);
                }

                if ((time() - $timestamp) < 30) {
                    // Recursion
                    sleep(3);
                    $affected += $this->cleanStoreBase($timestamp);
                }
            }
        }
        return $affected;

    }
}