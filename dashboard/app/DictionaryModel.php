<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_dictionary';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'word';

    protected $guarded = [];
}
