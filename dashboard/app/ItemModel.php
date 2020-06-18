<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
/*
CREATE OR REPLACE VIEW
	items AS
SELECT
	cb_things.ThingID AS id,
	cb_things.created AS created_at,
	cb_things.modified AS updated_at,

	cb_store.BrandID AS brand_id,
    cb_store.line_id,
    cb_store.motor_id,
	cb_store.category_id,
	cb_store.DiscountID AS discount_id,
	cb_store.named,
	cb_store.preview,
    cb_store.status,
	cb_store.selling,

	cb_extended.SellerID AS seller_id,
	cb_extended.eBayID AS eBay_id,
	cb_extended.ReferenceID AS reference_id,
	cb_extended.DescriptionID AS description_id,
	cb_extended.purchase,
	cb_extended.currency,
	cb_extended.images,
	cb_extended.options,
	cb_extended.compatibility
FROM
	cb_things
JOIN
	cb_store USING(ThingID)
JOIN
	cb_extended USING(ThingID)
*/
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'items';

    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function brand()
    {
        return $this->belongsTo('App\BrandModel', 'brand_id', 'BrandID');
    }
    public function category()
    {
        return $this->belongsTo('App\CategoryModel', 'category_id', 'id');
    }
    public function seller()
    {
        return $this->belongsTo('App\SellerModel', 'seller_id', 'SellerID');
    }
}
