<?php

namespace App\Http\Controllers;

use App\BrandsModel		 AS BModel;
use App\SiteMapModel	 AS SMModel;
use App\StoreModel;
use App\CategoriesModel	 AS CatModel;
use App\LandingPageModel AS PageModel;
use Illuminate\Http\Request;

class Search extends Controller
{
    public function index(Request $request){

    	$q = $request = $request->input('query');

    	$map = SMModel::getTree();

    	$page = PageModel::where('slug', "suchergebnisse")->first();

    	if (is_numeric($q)) {
  			$query = StoreModel::whereIn('id', [$q]);
    	} else {
    		$query = StoreModel::select('id', 'named', 'preview', 'price', 'brand','category_slug');
    		$this->matchMakes($request, $query);
    		$this->matchCategories($request, $query);
    		$words = preg_split("/\s+/", $request, -1, PREG_SPLIT_NO_EMPTY);
    		if (count($words) > 0) {
    			$query->where('named', 'LIKE', "%".implode("%", $words)."%");
    		}
    	}
    	$catalog = $query->paginate(20);

    	return view('layouts.search', [
            'map'		=> $map,
            'request'	=> $q,
            'catalog'	=> $catalog,
            'page'		=> $page,
            'description'=> $page->title." ✓ ".$page->description,
            'breadcrumbs'=> $page->breadcrumbs,
            'policy'    => PageModel::select('content')->where('slug','kurz-datenschutzerklrung')->first()
        ]);
    }

    private function matchMakes(&$request, &$query)
    {
    	$makes = BModel::select("id", "brand", "slug")->get();
    	foreach ($makes as $make) {
        	if (preg_match("/{$make->brand}/i", $request)) {
            	$query->where("maker_id", $make->id);
            	$request = preg_replace("/$make->brand/i", "", $request);
            	break;
        	}
    	}
    }
    private function matchCategories(&$request, &$query)
    {
    	$categories = CatModel::select("id", "category_name")
    			->where("level", ">", "2")
    			->get();
    	foreach ($categories as $category) {
    		$name = preg_quote($category->category_name, '/');
    		if (preg_match("/{$name}/i", $request)) {
            	$query->where("category_id", $category->id);
            	$request = preg_replace("/{$name}/i", "", $request);
            	break;
        	}
    	}
    }
}
