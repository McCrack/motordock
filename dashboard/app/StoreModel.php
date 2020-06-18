<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_store';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "ThingID";

    protected $guarded = [];

    public function thing()
    {
        return $this->belongsTo('App\ThingModel', "ThingID");
    }
    public function store()
    {
        return $this->belongsTo('App\CategoryModel', 'id', 'category_id');
    }
}
