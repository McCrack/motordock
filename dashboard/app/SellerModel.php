<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellerModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_sellers';

    public $timestamps = false;
    protected $primaryKey = 'SellerID';

    protected $guarded = ["SellerID"];

    public function item()
    {
        return $this->hasMany('App\ItemModel', 'SellerID', 'seller_id');
    }
}
