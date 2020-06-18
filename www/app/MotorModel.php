<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MotorModel extends Model
{
    /**
     * Таблица (VIEW) модели.
     *
     * @var string
     */
    protected $table = 'cb_motors';
    protected $primaryKey = "motor_id";
    public $timestamps = false;
    public $incrementing = false;

    public function getSpecificationsAttribute($specifications)
    {
        return json_decode($specifications, true);
    }
    public function getCompatibilityAttribute($compatibility)
    {
        return json_decode($compatibility, true);
    }
}
