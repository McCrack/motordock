<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoriesModel extends Model
{
    protected $table = "cb_categories";
    
    private $categories = null;
    private static $instance = null;

    public $timestamps = false;
    public $module = "category";

    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function getNameAttribute($name)
    {
        $lang = \App::getLocale();
        return json_decode($name, true)[$lang];
    }
    
    public static function getTree()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            self::$instance->categories = CategoriesModel::where('status', "enabled")
            ->orderBy('left_key')
            ->get();
        }
        $tree = [];
        foreach(self::$instance->categories->toArray() as $row){
            foreach($row as $key=>$val){
                $tree[$row['parent_id']][$row['id']][$key] = $val;
            }
        }
        return $tree;
    }

    public static function getCategory($slug){
        if (is_null(self::$instance)) {
            return CategoriesModel::where('slug', $slug)->first();
        } else {
            return self::$instance->categories->where('slug', $slug)->first();
        }
    }
    public static function getCategoryById($category_id){
        if (is_null(self::$instance)) {
            return CategoriesModel::find($category_id)->first();
        } else {
            return self::$instance->categories->where('id', $category_id)->first();
        }
    }
    public static function getBranch($category)
    {
        if (is_null(self::$instance)) {
            return CategoriesModel::where('status', "enabled")
                ->where('left_key', '>=', $category->left_key)
                ->where('right_key', '<=', $category->right_key)
                ->orderBy('left_key')
                ->get();
        } else {
            return self::$instance->categories
                ->where('left_key', '>=', $category->left_key)
                ->where('right_key', '<=', $category->right_key);
        }
    }
    public static function getCategoriesSet($category)
    {
        $branch = self::getBranch($category);
        $categories = [];
        foreach ($branch as $category) {
            $categories[] = $category->id;
        }
        return $categories;
    }
    public static function getBreadcrumbs($category, $level)
    {
        if (is_null(self::$instance)) {
            $branch = CategoriesModel::where('status', "enabled")
                ->where('level', '>=', $level)
                ->where('left_key', '<=', $category->left_key)
                ->where('right_key', '>=', $category->right_key)
                ->orderBy('left_key')
                ->get();
        } else {
            $branch = self::$instance->categories
                ->where('level', '>=', $level)
                ->where('left_key', '<=', $category->left_key)
                ->where('right_key', '>=', $category->right_key);
        }
        $breadcrumbs = [];
        foreach ($branch as $i=>$crumb) {
            $breadcrumbs[] = [
                '@type'     => "ListItem",
                'position'  => $level + $i,
                'name'      => $crumb->name,
                'item'      => config('app.url')."/".$crumb->slug
            ];
        }

        return $breadcrumbs;
    }
}
