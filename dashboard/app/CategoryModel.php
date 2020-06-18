<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_categories';

    public $timestamps = false;

    protected $guarded = ["id"];

    public function getNameAttribute($name)
    {
        return json_decode($name, true);
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        return $this->hasMany('App\StoreModel', 'category_id', 'id');
    }
    public function item()
    {
        return $this->hasMany('App\ItemModel', 'id', 'category_id');
    }
}
