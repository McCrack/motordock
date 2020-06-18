<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\StoreModel;
use App\LineupModel         AS LModel;
use App\LandingPageModel	AS LPModel;
use App\ProductCardModel    AS PCModel;

use App\CategoriesModel     AS Categories;

class Showcase extends Controller
{
    public function card($item = null)
    {

        if (empty($item)) {
            $page = LPModel::whereSlug('404')->first();
            return response($this->view("errors.404", $page, [
                'breadcrumbs'   => $page->breadcrumbs
            ]), 404);
        } else {
            $category = Categories::find($item->category_id);
            $page = LPModel::find($category->branch_id);
        }

        $breadcrumbs = $page->breadcrumbs;
        $crumbs = Categories::getBreadcrumbs($category, 3);

        foreach ($crumbs as $crumb) {
            $breadcrumbs['itemListElement'][] = $crumb;
        }
        $breadcrumbs['itemListElement'][] = [
            '@type'     => "ListItem",
            'position'  => count($breadcrumbs['itemListElement'])+1,
            'name'      => $item->named,
            'item'      => config('app.url')."/".$item->slug
        ];

        $compatibility = json_decode($item['compatibility'] ?? '{"Car Make":[]}', true);
        if (isset($compatibility['Car Make'])) {

        } else $compatibility['Car Make'] = [];
            
        return view("layouts.showcase", [
        	'page'			=> $page,
            'title'         => "{$category->name} {$item->make} {$item->model}",
            'item'          => $item,
            'category'      => $category,
            'breadcrumbs'   => $breadcrumbs,
            'compatibility' => $compatibility,
            'meta'          => [
                'robots' => "noindex, nofollow",
                'description' => implode(' ✓ ', [
            		$page->title,
            		$item->named,
            		$page->context
        		])
            ]
        ]);

        /*
        $page->id = $item->id;
        $page->brand = $item->make;
        $page->price = (INT)$item->price;
        $page->priceValidUntil = (STRING)$item->updated_at->format("Y-m-d");
        $page->header = $category->name." ".$item->make." ".$item->model;
        $page->preview = $item->preview;
        $page->description = $item->named;
        $page->article = $item->options['Part Number'] ?? $item->id;
        $page->canonical = url()->current();
        */
    }

    public function lineup($category, $brand, $model, $subitem = null){

        $part = LPModel::find($category->branch_id);

        $breadcrumbs = $part->breadcrumbs;

        $categories = Categories::getCategoriesSet($category);

        $catalog = StoreModel::where('branch_id', $part->id)
            ->whereIN("category_id", $categories)
            ->where('brand_slug', $brand->slug)
            ->where('lineup_slug', $model->slug)
            ->get();

        $item = PCModel::find($subitem ?? $catalog->first()->id ?? null);

        if (empty($item)) {
            throw new \App\Exceptions\Custom('Page Not Found');
        }

        $category = Categories::find($item->category_id);

        foreach (Categories::getBreadcrumbs($category, 3) as $i => $crumb) {
            $breadcrumbs['itemListElement'][] = $crumb;
        }
        $breadcrumbs['itemListElement'][] = [
            "@type" => "ListItem",
            "position" => ($i + 4),
            "name" => $brand->brand,
            "item" => "https://motordock.de/{$category->slug}/{$brand->slug}"
        ];
        $breadcrumbs['itemListElement'][] = [
            "@type" => "ListItem",
            "position" => ($i + 5),
            "name" => $model->model,
            "item" => "https://motordock.de/{$category->slug}/{$brand->slug}/{$model->slug}"
        ];

        $models = [];
        $list = LModel::where('BrandID', $brand->id)->get();
        foreach ($list as $itm) {
            $key = strtolower($itm->model);
            $models[$key] = [
                'id'    => $itm->model_id,
                'slug'  => $itm->slug,
                'name'  => $itm->model
            ];
        }
        $catalog->where("maker_id", $brand->id);

        return view('layouts.model', [
        	'page'			=> $part,
            'title'         => "{$category->name} {$item->make} {$item->model}",
            'category'      => $category,
            'item'          => $item,
            'brand'         => $brand,
            'catalog'       => $catalog,
            'breadcrumbs'   => $breadcrumbs,
            'meta'          => [
                'description' => implode(' ✓ ', [
            		$part->title,
            		$item->named ?? "",
            		$part->context
        		])
            ]
        ]);
    }

    public function item($ThingID)
    {
        $item = PCModel::find($ThingID);
        return view('components.item', [
            'item'      => $item,
            'title'     => Categories::getCategoryById($item->category_id)->name." {$item->make} {$item->model}"

        ]);
    }
}
