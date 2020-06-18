<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ThingModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_things';

    const CREATED_AT = 'created';
	const UPDATED_AT = 'modified';

    protected $primaryKey = 'ThingID';
    protected $dateFormat = 'U';

    protected $guarded = ["ThingID"];

    public function store()
    {
        return $this->hasOne('App\StoreModel', 'ThingID');
    }
    public function extended()
    {
        return $this->hasOne('App\ExtendedModel', 'ThingID');
    }
}
