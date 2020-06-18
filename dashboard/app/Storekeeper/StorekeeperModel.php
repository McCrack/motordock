<?php

namespace App\Storekeeper;

use Illuminate\Database\Eloquent\Model;

class StorekeeperModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_storekeeper';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'timestamp';

    protected $guarded = [];
}
