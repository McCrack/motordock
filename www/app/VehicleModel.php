<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_models';
    public $timestamps = false;
    protected $primaryKey = "model_id";

    public function brand()
    {
        return $this->belongsTo('App\BrandsModel', "BrandID", "id");
    }
}