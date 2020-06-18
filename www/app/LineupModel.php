<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineupModel extends Model
{
    /**
     * Таблица (VIEW) модели.
     *
     * @var string
     */
    protected $table = 'cb_lineups';
    public $timestamps = false;
    protected $primaryKey = "line_id";

    public function brand()
    {
        return $this->belongsTo('App\BrandsModel', "BrandID", "id");
    }
}
