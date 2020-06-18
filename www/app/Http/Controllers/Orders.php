<?php

namespace App\Http\Controllers;

use Mail;
use App\OrderModel;
use App\StoreModel;
use App\CommunityModel;
use App\ItemsInOrdersModel;
use App\LandingPageModel AS PageModel;
use Illuminate\Http\Request;

class Orders extends Controller
{
    public function createOrder(Request $request)
    {
		
		if (empty($request->signature)) {
			return false;
		}

		$citizen = CommunityModel::where('phone', $request->phone)->first();

		if (empty($citizen)) {
			$citizen = CommunityModel::create([
				'name'		=> $request->name,
				'last_name'	=> $request->lastName,
				'phone'		=> $request->phone,
				'email'		=> $request->email,
				'reputation'=> 1
			]);
		} else {
			$citizen->name = $request->name;
			$citizen->last_name = $request->lastName;
			//$citizen->phone = $request->phone;
			$citizen->email = $request->email;
			$citizen->reputation = $citizen->reputation+1;
			$citizen->save();
		}

		$timestamp = time();

		$orderNum = OrderModel::where('created_at', '>', mktime(0, 0, 0, date("m"), 1))
			->max('num') + 1;
		
		$order = OrderModel::create([
			'num'			=> $orderNum,
			'community_id'	=> $citizen->id,
			'price'			=> array_sum($request->cart),
			'delivery'		=> json_encode([
				"city"		=> $request->city,
				"address"	=> $request->address,
				"postcode"	=> $request->postcode
			]),
			'message'		=> $request->message,
			'signature'		=> $request->signature,
			'created_at'	=> $timestamp,
			'updated_at'	=> $timestamp
		]);

		$items = StoreModel::select(
			'id',
			'named',
			'price',
			'delivery_price'
		)->whereIn('id', array_keys($request->cart))
		 ->get();

		$deliveryPrice = 0;
		$total = 0;
		$relations = [];
		foreach ($items as $item) {
			$relations[] = [
				'OrderID'	=> $order->id,
				'ThingID'	=> $item->id
			];
			$total += $item->price;
			if ($item->delivery_price > $deliveryPrice) {
				$deliveryPrice = $item->delivery_price;
			}
		}

		ItemsInOrdersModel::insert($relations);

		$orderNum = str_pad($orderNum, 3, '0', STR_PAD_LEFT);
		$orderNum = date("mY{$orderNum}");

		/*
		Mail::send('mails.notification', [
        	'num'		=> $orderNum,
        	'items'		=> $items,
        	'total'		=> $total,
        	'citizen'	=> $citizen,
        	'request'	=> $request
        ], function($message) use($orderNum){
            $message->to('datamorg@gmail.com')
            //$message->to('info@motordock.de')
                ->subject("Bestellnummer {$orderNum}");
            $message->from('bestellung@motordock.de','Motordock Site');
        });
		*/
		
		Mail::send('mails.order', [
        	'num'	=> $orderNum,
        	'items'	=> $items,
        	'total'	=> $total,
        	'deliveryPrice' => $deliveryPrice,
        	'sum'	=> ($total + $deliveryPrice)
        ], function($message) use($citizen, $orderNum){
            $message->to($citizen->email)
                ->subject("Bestellnummer {$orderNum}");
            $message->from('bestellung@motordock.de','Motordock-Team');
        });
		
		$message = PageModel::where('slug', "on-order-message")->first();

		return view("layouts.message", [
            'header'	=> "Bestellnummer {$orderNum} zur Bearbeitung angenommen",
            'image'		=> $message->preview,
            'message'	=> $message->content
        ]);
    }
    public function callback(Request $request)
    {
    	Mail::send('mails.callback', [
        	'request'	=> $request
        ], function($message){
            //$message->to('datamorg@gmail.com')
            $message->to('info@motordock.de')
                ->subject("Rückrufformular");
            $message->from('bestellung@motordock.de','Motordock Site');
        });

    	$message = PageModel::where('slug', "on-callback-message")->first();

        return view("layouts.message", [
            'header'	=> "Ihre Anfrage wurde angenommen",
            'image'		=> $message->preview,
            'message'	=> $message->content
        ]);
	}
}
