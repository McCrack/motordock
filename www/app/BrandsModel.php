<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandsModel extends Model
{
/*
CREATE OR REPLACE VIEW
	makers AS
SELECT
	BrandID AS id,
    type,
	slug,
	brand,
	logo,
    favorite
FROM
	cb_brands
WHERE
    available > 0
ORDER BY idx
*/

    /**
     * Таблица (VIEW), модели.
     *
     * @var string
     */
    protected $table = 'makers';

    public $timestamps = false;
}
