<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreModel extends Model
{
/*
CREATE OR REPLACE VIEW
	store AS
SELECT
	cb_store.ThingID AS id,
	cb_store.BrandID AS maker_id,
	cb_store.category_id,
	cb_store.named,
	cb_store.preview,
	cb_store.selling AS price,
    cb_store.line_id,
    cb_store.motor_id,
	cb_brands.brand,
    cb_brands.slug AS brand_slug,
    cb_lineups.model,
    cb_lineups.slug AS lineup_slug,
    cb_categories.name AS category,
	cb_categories.slug AS category_slug,
    cb_categories.branch_id,
    cb_categories.delivery_price
FROM
	cb_store
JOIN
    cb_categories ON cb_categories.id = cb_store.category_id
LEFT JOIN
	cb_brands USING(BrandID)
LEFT JOIN
    cb_lineups USING(line_id)
WHERE
	cb_store.status='available'
ORDER BY ThingID DESC
*/

   	/**
     * Таблица (VIEW), модели.
     *
     * @var string
     */
    protected $table = 'store';

    /**
     * Формат хранения отметок времени модели.
     *
     * @var string
     */
    public $timestamps = false;


    public function getNamedAttribute($json)
    {
    	$lang = \App::getLocale();
    	return json_decode($json, true)[$lang];
    }
    public function getCategoryAttribute($json)
    {
        $lang = \App::getLocale();
        return json_decode($json, true)[$lang];
    }

    public function getSlugAttribute()
    {
    	return "{$this->category_slug}-{$this->id}";
    }
    /*
    public function getPriceAttribute($price)
    {
        setlocale(LC_MONETARY, 'de_DE');
        return money_format("%i", $price + $this->delivery_price);
    }
    */
    public function getSellingAttribute($price)
    {
        if ($this->DiscountID && (INT)$this->discount > 0) {
            return $this->fullPrice - floor($this->fullPrice * $this->discount / 100);
        } else {
            return $this->fullPrice;
        }
    }
}
