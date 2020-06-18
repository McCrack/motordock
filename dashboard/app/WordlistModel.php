<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WordlistModel extends Model
{
    /**
     * Таблица модели.
     *
     * @var string
     */
    protected $table = 'cb_wordlist';

    public $timestamps = false;
    public $incrementing = false;

    private $data = [];
    private static $instance = null;

    protected $guarded = [];

    public static function getInstance()
    {
    	if (is_null(self::$instance)) {
			self::$instance = new self();
			self::$instance->data = self::all()->sortBy(function($word){
    			return 10 - str_word_count($word->en);
			})->values();
		}
		return self::$instance->data;
    }
    public static function getTranslate($from, $to, $term)
    {
    	self::getInstance();

    	if (self::$instance->data->contains($from, $term)) {
    		return self::$instance->data->where($from, $term)->first()->{$to};
    	} else {
    		return $term;
    	}
    }
}
