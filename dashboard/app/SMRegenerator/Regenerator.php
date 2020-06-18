<?php

namespace App\SMRegenerator;

use App\StoreModel;
use Illuminate\Support\Facades\DB;

class Regenerator
{
    public static function refreshItemsMap($xml)
    {
        $map = DB::table('cb_store')
            ->select(
                'cb_store.ThingID AS id',
                'cb_things.created AS created_at',
                'cb_categories.slug AS category'
            )
            ->join('cb_things', function($join){
                $join->on('cb_things.ThingID', 'cb_store.ThingID');
            })
            ->join('cb_categories', function($join){
                $join->on('cb_categories.id', 'cb_store.category_id');
            })
            ->where('cb_store.status', "available")
            ->get();

        foreach($map as $item) {
            self::makeItem(
                $xml,
                "/{$item->category}-{$item->id}",
                "0.60",
                date('Y-m-d\TH:i:s+03:00', $item->created_at)
            );
        }

        return $xml;
    }
    public static function refreshMainMap($xml)
    {

        self::makeItem($xml,'',"1.00",date('Y-m-d\TH:i:s+03:00'),"weekly");

        $map = DB::table('gb_sitemap')
            ->select(
                'gb_pages.PageID AS id',
                'name AS slug',
                'parent',
                'modified AS updated_at'
            )
            ->join('gb_pages', function($join){
                $join->on('gb_pages.PageID', 'gb_sitemap.PageID');
            })
            ->where('published', "Published")
            ->get();
        foreach($map->where('parent', 'showcase') as $item) {
            self::makeItem(
                $xml,
                "/{$item->slug}",
                "0.90",
                date('Y-m-d\TH:i:s+03:00'),
                "daily"
            );
            self::categories($xml, $item);
        }

        foreach($map->where('parent', 'static') as $item) {
            self::makeItem(
                $xml,
                "/{$item->slug}",
                "0.50",
                date('Y-m-d\TH:i:s+03:00', $item->updated_at)
            );
        }

        return $xml;
    }
    public static function refreshMotorsMap($xml)
    {
        $motors = DB::table('cb_store')
            ->join('cb_motors', 'cb_store.motor_id', '=', 'cb_motors.motor_id')
            ->select('cb_store.motor_id')
            ->where('fullness', 'medium')
            ->where('status', 'available')
            ->whereNotNull('cb_store.motor_id')
            ->groupBy('cb_store.motor_id')
            ->get();

        foreach ($motors as $motor) {
            self::makeItem(
                $xml,
                "/motor/{$motor->motor_id}",
                "0.90",
                date('Y-m-d\TH:i:s+03:00'),
                "monthly"
            );
        }
        return $xml;
    }
    public static function refreshModelsMap($xml)
    {
        $categories = DB::table('cb_categories')
            ->select('id','slug')
            ->where('status', "enabled")
            ->where('favorite', '>', 0)
            ->get();
        $brands = DB::table('cb_brands')
            ->select('BrandID','slug')
            ->where('available', '>', 0)
            ->where('favorite', '>', 0)
            ->get();
        $lineups = DB::table('cb_lineups')
            ->select('line_id','BrandID','slug')
            ->get();

        foreach ($categories as $category) {
            $exists = DB::table('cb_store')
                ->where('category_id', $category->id)
                ->where('status', "available")
                ->exists();
            if (!$exists) {
                continue;
            }
            foreach ($brands as $brand) {
                $exists = DB::table('cb_store')
                    ->where('category_id', $category->id)
                    ->where('BrandID', $brand->BrandID)
                    ->where('status', "available")
                    ->exists();
                if (!$exists) {
                    continue;
                }
                foreach ($lineups->where('BrandID', $brand->BrandID) as $model) {
                    $exists = DB::table('cb_store')
                        ->where('category_id', $category->id)
                        ->where('BrandID', $brand->BrandID)
                        ->where('line_id', $model->line_id)
                        ->where('status', "available")
                        ->exists();
                    if (!$exists) {
                        continue;
                    }
                    self::makeItem(
                        $xml,
                        "/{$category->slug}/{$brand->slug}/{$model->slug}",
                        "0.80",
                        date('Y-m-d\TH:i:s+03:00'),
                        "daily"
                    );
                }
            }
        }
        return $xml;
    }

    private static function makeItem($xml, $slug, $priority, $lastmod, $changefreq = "monthly")
    {
        $item = $xml->addChild('url');
        $item->addChild('loc', "https://motordock.de{$slug}");
        $item->addChild('lastmod', $lastmod);
        $item->addChild('changefreq', $changefreq);
        $item->addChild('priority', $priority);

        return $item;
    }

    private static function categories($xml, $item)
    {
        $categories = DB::table('cb_categories')
            ->select('id','slug','favorite')
            ->where('status', "enabled")
            ->where('level', '>', 2)
            ->where('branch_id', $item->id)
            ->get();
        $makers = DB::table('cb_brands')
            ->select('BrandID','slug')
            ->where('available', '>', 0)
            ->where('favorite', '>', 0)
            ->get();
        foreach ($categories as $category) {
            self::makeItem(
                $xml,
                "/{$category->slug}",
                "0.70",
                date('Y-m-d\TH:i:s+03:00'),
                "daily"
            );
            if ($category->favorite > 0) {
                foreach ($makers as $maker) {
                    $exists = DB::table('cb_store')
                    ->where('category_id', $category->id)
                    ->where('BrandID', $maker->BrandID)
                    ->where('status', "available")
                    ->exists();

                    if ($exists) self::makeItem(
                        $xml,
                        "/{$category->slug}/{$maker->slug}",
                        "0.80",
                        date('Y-m-d\TH:i:s+03:00'),
                        "daily"
                    );
                }
            }
        }
    }
}