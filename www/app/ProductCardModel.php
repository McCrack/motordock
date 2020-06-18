<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCardModel extends Model
{
/*
CREATE OR REPLACE VIEW
	product_card AS
SELECT
	cb_things.ThingID AS id,
	cb_things.created AS created_at,
	cb_things.modified AS updated_at,
	cb_store.category_id,
	cb_store.named,
	cb_store.preview,
    cb_store.status,
	cb_store.selling AS price,
    cb_store.motor_id,
	cb_extended.options,
    cb_extended.compatibility,
	cb_extended.images AS imageset,
	cb_brands.brand AS make,
    cb_brands.slug AS brand_slug,
    cb_lineups.model,
    cb_lineups.slug AS model_slug,
    cb_categories.name AS category,
    cb_categories.slug AS category_slug,
    cb_categories.delivery_price
FROM
	cb_store
JOIN
	cb_things USING(ThingID)
JOIN
	cb_extended USING(ThingID)
JOIN
    cb_categories ON cb_categories.id = cb_store.category_id
LEFT JOIN
	cb_brands USING(BrandID)
LEFT JOIN
    cb_lineups USING(line_id)
*/
	/**
     * Таблица (VIEW) модели.
     *
     * @var string
     */
    protected $table = 'product_card';

    /**
     * Формат хранения отметок времени модели.
     *
     * @var string
     */
    protected $dateFormat = 'U';


    public function resolveRouteBinding($value)
    {
        $value = explode("-", $value);
        return $this->where('id', array_pop($value))->first() ?? abort(404);
    }

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

    public function getImagesetAttribute($json)
    {
        $imgset = json_decode($json, true);
        if (is_array($imgset) && count($imgset)) {
            return $imgset;
        } else {
            return [$this->preview];
        }
    }

    public function getOptionsAttribute($json)
    {
        return json_decode($json, true);
    }

    public function getDescriptionAttribute($value)
    {
        return gzdecode($value);
    }

    public function getPriceAttribute($price)
    {
        setlocale(LC_MONETARY, 'de_DE');
        return money_format("%i", $price + $this->delivery_price);
    }

    public function getFullPriceAttribute($price)
    {
        $currencyRate = get_defined_constants()[$this->currency];
        return round(($this->price * $currencyRate) / 10) * 10;
    }

    public function getSellingAttribute($price)
    {
        if ($this->DiscountID && (INT)$this->discount > 0) {
            return $this->fullPrice - floor($this->fullPrice * $this->discount / 100);
        } else {
            return $this->fullPrice;
        }
    }
}
