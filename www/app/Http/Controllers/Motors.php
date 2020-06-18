<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\TranslatorModel;
use App\StoreModel;
use App\ProductCardModel;
use Illuminate\Support\Facades\DB;

class Motors extends Controller
{
    public function index($motor)
    {
    	$store = ProductCardModel::where('motor_id', $motor->motor_id)
    		->where('status', 'available')
    		->orderBy('price','DESC')
    		->get();

    	if ($store->count() == 1) {
    		$item = $store->first();
    	}

        $translator = TranslatorModel::getInstance();

        $lineups = DB::table('motors_vs_lineups')
            ->select('brand', 'model', 'modifications')
            ->join('cb_lineups', 'motors_vs_lineups.line_id', '=', 'cb_lineups.line_id')
            ->join('cb_brands', 'cb_lineups.BrandID', '=', 'cb_brands.BrandID')
            ->where('motor_id', $motor->motor_id)
            ->get();

        $title = "{$translator->engine} {$motor->article}";
        $description = [];
        foreach ($lineups as $lineup) {
            $description[] = "{$lineup->brand} {$lineup->model}";
        }
        $description = "Kaufen Sie einen Motor für - ".implode('✓ ', $description)." in Online Shop | Motordock";

    	return view("motors.{$motor->fullness}", [
    		'motor' => $motor,
    		'store'	=> $store,
            'item'  => $item ?? null,
    		'title'	=> $title,
            'description' => $description,
            'microdata' => [],
            'breadcrumbs'   => null
    	]);
    }
}
