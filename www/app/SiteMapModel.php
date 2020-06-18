<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteMapModel extends Model
{

/* 
CREATE OR REPLACE VIEW
	sitemap AS
SELECT
	PageID AS id,
	name AS slug,
	parent AS parent_slug,
	language,
	header,
	preview,
	published
FROM
	gb_sitemap
ORDER BY 
    SortID
*/

	/**
     * Таблица (VIEW), модели.
     *
     * @var string
     */
    protected $table = 'sitemap';

    /**
     * Формат хранения отметок времени модели.
     *
     * @var string
     */
    protected $dateFormat = 'U';

	public static function getTree()
    {

        $tree = [];
        $collection = self::select(["id","slug","parent_slug","header","preview","published"])->get();

        foreach($collection->toArray() as $row){
            foreach($row as $key=>$val){
                $tree[$row['parent_slug']][$row['slug']][$key] = $val;
            }
        }
        return $tree;
    }
}
