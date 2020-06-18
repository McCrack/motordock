<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunityModel extends Model
{
/* 
CREATE OR REPLACE VIEW
	community AS
SELECT
	CommunityID AS id,
	name,
	last_name,
	phone,
    email,
    reputation
FROM
	cb_community
*/

    /**
     * Таблица (VIEW) модели.
     *
     * @var string
     */
    protected $table = 'community';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'last_name',
        'phone',
        'email',
        'reputation'
    ];
}
