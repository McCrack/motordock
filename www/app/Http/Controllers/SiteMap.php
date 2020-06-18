<?php

namespace App\Http\Controllers;

use DB;
//use App\PreviewsModel       AS PModel;
use App\BrandsModel         AS BModel;
use App\VehicleModel        AS VModel;
use App\LineupModel         AS LModel;
use App\LandingPageModel	AS LPModel;
use App\StoreModel;
use App\ProductListModel    AS PLModel;
use App\ProductCardModel    AS PCModel;

use App\CategoriesModel     AS Categories;

use Illuminate\Http\Request;

class SiteMap extends Controller
{
    public function index($page)
    {
        if (empty($page->module)) {
            return $this->typical($page);
        } else {
            return $this->{$page->module}($page);
        }
    }
    public function typical($page)
    {
        return view("layouts.typical", [
            'page'          => $page,
            'meta'          => [
                'description'   => implode(' ✓ ', [
                    $page->title,
                    $page->description,
                    $page->context
                ])
            ],
            'breadcrumbs'   => $page->breadcrumbs
        ]);
    }

    public function poligon($page)
    {
        return view('layouts.poligon', [
            'page'          => $page,
            'title'         => "Poligon",
            'breadcrumbs'   => $page->breadcrumbs,
            'meta'          => [
                'robots' => "noindex, nofollow",
                'description' => ""
            ]
        ]);
    }

    public function home(){
        $page = LPModel::where('slug', 'start')->first();
        return view('layouts.home', [
            'page'  => $page,
            'meta'  => [
                'description' => ""
            ],
            'catTree' => Categories::getTree(),
            'breadcrumbs'   => [
                '@context'  => "http://schema.org",
                '@type'     => "BreadcrumbList",
                "itemListElement" => [[
                    "@type"     => "ListItem",
                    "position"  => 1,
                    "name"      => config('app.name'),
                    "item"      => config('app.url')
                ]]
            ]
        ]);
    }
    public function part($part)
    {
        $tree = Categories::getTree();
        $category = Categories::getCategory($part->slug);

        $catalog = StoreModel::where("branch_id", $part->id)->paginate(20);
        
        return view('layouts.category', [
            'page'          => $part,
            'title'         => $category->name,
            'category'      => $category,
            'catTree'       => $tree,
            'brands'        => BModel::whereType("vehicles")->get(),
            'catalog'       => $catalog,
            'breadcrumbs'   => $part->breadcrumbs,
            'meta'          => [
                'description' => implode(' ✓ ', [
                    $part->title,
                    (function($brands) use (&$catalog){
                        foreach ($catalog as $item) {
                            $brands[] = $item->brand;
                        }
                        $brands = array_unique($brands);
                        return implode(", ", $brands);
                    })([]),
                    $part->context
                ])
            ]
        ]);
    }

    public function category($category, $brand = null)
    {
        $part = LPModel::find($category->branch_id);

        $desc = [$part->title];
        $breadcrumbs = $part->breadcrumbs;

        $tree = Categories::getTree();

        if ($category->slug != $part->slug) {
            $crumbs = Categories::getBreadcrumbs($category, 3);
            foreach ($crumbs as $crumb) {
                $desc[] = $crumb['name'];
                $breadcrumbs['itemListElement'][] = $crumb;
            }
            $part->title = implode(' ✓ ', $desc);
        }

        $categories = Categories::getCategoriesSet($category);
        $catalog = StoreModel::where('branch_id', $part->id)->whereIN("category_id", $categories);

        if(isset($brand)) {
            $lineup = DB::table('cb_store')
                ->join('cb_lineups', function($join){
                    $join->on('cb_lineups.line_id', 'cb_store.line_id');
                })
                ->select(
                    'model',
                    'slug',
                    'image',
                    'modifications',
                    DB::raw('COUNT(ThingID) AS cnt')
                )
                ->whereNotNull('cb_store.line_id')
                ->where('status', "available")
                ->where('cb_store.BrandID', $brand->id)
                ->whereIn('category_id', $categories)
                ->groupBy('cb_store.line_id')
                ->get();

            foreach ($lineup as $line) {
                $uri = parse_url($line->image);
                $path = pathinfo($uri['path']);

                $line->images = [];
                foreach( glob("../../media".$path['dirname']."/".$path['filename'].".*") as $file){
                    $line->images[pathinfo($file)['extension']] = 
                        $uri['scheme']
                        ."://"
                        .$uri['host']
                        .$path['dirname']
                        ."/".basename($file);
                }
            }

            $catalog->where("maker_id", $brand->id);

            $breadcrumbs['itemListElement'][] = [
                "@type" => "ListItem",
                "position" => count($breadcrumbs['itemListElement']) + 1,
                "name" => $brand->brand,
                "item" => "https://motordock.de/{$category->slug}/{$brand->slug}"
            ];
        }
        $catalog = $catalog->paginate(20);

        return view('layouts.category', [
            'page'          => $part,
            'category'      => $category,
            'catTree'       => $tree,
            'brand'         => $brand,
            'brands'        => BModel::whereType("vehicles")->get(),
            'lineups'       => $lineup ?? [],
            'catalog'       => $catalog,
            'breadcrumbs'   => $breadcrumbs,
            'title'         => (function($title) use ($brand){
                if (isset($brand)) {
                    $title .= " ".$brand->brand;
                }
                return $title;
            })($category->name),
            'meta'          => [
                'description' => implode(' ✓ ', [
                    $part->title,
                    (function($brands) use (&$catalog){
                        foreach ($catalog as $item) {
                            $brands[] = $item->brand;
                        }
                        $brands = array_unique($brands);
                        return implode(", ", $brands);
                    })([]),
                    $part->context
                ])
            ]
        ]);
    }

    public function subpart($part)
    {
        $category = Categories::getCategory($part->slug);
        $category->content = $part->content;

        return $this->category($category);
    }

    public function message($page)
    {
        return view("layouts.{$page->template}", compact('page'));
    }
}
