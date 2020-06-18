<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
/* 
CREATE OR REPLACE VIEW
	orders AS
SELECT
	OrderID AS id,
    order_number AS num,
	CommunityID AS community_id,
	price,
	status,
	delivery,
	message,
	signature,
	created_at,
	updated_at
FROM
	cb_orders
*/

	/**
     * Таблица (VIEW), модели.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * Формат хранения отметок времени модели.
     *
     * @var string
     */
    protected $dateFormat = 'U';

    protected $fillable = [
        'num',
        'community_id',
        'price',
        'status',
        'delivery',
        'message',
        'signature',
        'created_at',
        'updated_at'
    ];
}
