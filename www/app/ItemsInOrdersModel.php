<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemsInOrdersModel extends Model
{
    /**
     * Таблица, модели.
     *
     * @var string
     */
    protected $table = 'orders_vs_store';

    public $timestamps = false; 

    protected $fillable = [
        'OrderID',
        'ThingID',
        'amount'
    ];
}
