<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class eBayCaheModel extends Model
{
    protected $table = 'cb_cache';

    public $timestamps = false;
    public $incrementing = false;

    protected $guarded = [];
}
