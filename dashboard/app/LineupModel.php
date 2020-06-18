<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineupModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_lineups';

    public $timestamps = false;
    protected $primaryKey = "line_id";

    protected $guarded = ["line_id"];

	    
    public function brand()
    {
        return $this->belongsTo('App\BrandModel', "BrandID");
    }
}
