<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'group', 'token', 'config'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getConfigAttribute($cng)
    {
        return json_decode($cng, true);
    }
    public function setConfigAttribute($cng)
    {
        $this->attributes['config'] = json_encode($cng, JSON_UNESCAPED_UNICODE);
    }
}
