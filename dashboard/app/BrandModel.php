<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_brands';

    public $timestamps = false;
    protected $primaryKey = "BrandID";

    protected $guarded = ["BrandID"];

    public function store()
    {
        return $this->hasMany('App\StoreModel', 'BrandID');
    }
    public function item()
    {
        return $this->hasMany('App\ItemModel', 'BrandID', 'brand_id');
    }

    public function lineup()
    {
        return $this->hasMany('App\LineupModel', 'BrandID', 'BrandID');
    }
}
