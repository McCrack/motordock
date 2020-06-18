<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtendedModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_extended';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'ThingID';

    protected $guarded = [];

    public function thing()
    {
        return $this->belongsTo('App\ThingModel', 'ThingID');
    }
}
