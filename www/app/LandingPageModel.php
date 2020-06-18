<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingPageModel extends Model
{
/*
CREATE OR REPLACE VIEW
	page AS
SELECT
	gb_sitemap.PageID AS id,
	gb_sitemap.name AS slug,
	gb_sitemap.parent AS parent_slug,
	gb_sitemap.language,
	gb_sitemap.header,
	gb_sitemap.preview,
	gb_sitemap.published,

	gb_pages.created AS created_at,
	gb_pages.modified AS updated_at,
	gb_pages.type,
	gb_pages.customizer,

	gb_static.content,
	gb_static.title,
	gb_static.context,
	gb_static.description,
	gb_static.module,
	gb_static.template,
	gb_static.optionset,
	gb_static.microdata,

    gb_media.mediaset
FROM
	gb_sitemap
JOIN
	gb_pages USING(PageID)
JOIN
	gb_static USING(PageID)
LEFT JOIN
    gb_media USING(SetID)
*/

   	/**
     * Таблица (VIEW), модели.
     *
     * @var string
     */
    protected $table = 'page';

    /**
     * Формат хранения отметок времени модели.
     *
     * @var string
     */
    protected $dateFormat = 'U';

    public $siteName = "motordock";
    public $root = "https://motordock.de";

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getCoverAttribute()
    {
        $path = parse_url($this->preview)['path'];
        $path = pathinfo($path);

        $images = [
            'default' => $this->preview
        ];
        foreach( glob(public_path($path['dirname']."/".$path['filename'].".*")) as $file){
            $images[pathinfo($file)['extension']] = asset($path['dirname']."/".basename($file));
        }
        return $images;
    }
    public function getContentAttribute($content)
    {
        return gzdecode($content);
    }
    public function getCreated_atAttribute($created_at)
    {
        return date("c", $created_at);
    }
    public function getUpdated_atAttribute($updated_at)
    {
        return date("c", $updated_at);
    }
    public function getBreadcrumbsAttribute()
    {
        return [
            '@context'  => "http://schema.org",
            '@type'     => "BreadcrumbList",
            "itemListElement" => [[
                "@type"     => "ListItem",
                "position"  => 1,
                "name"      => config('app.name'),
                "item"      => config('app.url')
            ],[
                "@type"     => "ListItem",
                "position"  => 2,
                "name"      => $this->header,
                "item"      => config('app.url')."/".$this->slug
            ]]
        ];
    }
    public function setBreadcrumbsAttribute($value)
    {
        $this->attributes['breadcrumbs'] = $value;
    }
    public function getMicrodataAttribute($list)
    {
        $schemes = [];
        foreach (explode(",", $list) as $scheme) {
            if (file_exists("../resources/schemes/".$scheme.".json")) {
                $microdata = file_get_contents("../resources/schemes/".$scheme.".json");
                try {
                    $microdata = json_decode($microdata, true);
                    $this->buildScheme($microdata);
                    $schemes[$scheme] = $microdata;
                } catch(Exception $e) {}
            }
        }
        return $schemes;
    }
    private function buildScheme(&$obj)
    {
        foreach ($obj as $key=>&$itm) {
            if ( is_array($itm) ) {
                $this->buildScheme($itm);
            } elseif ( preg_match("@^gl:(.*)@i", $itm, $matches) ) {
                $itm = $this->gl($matches[1]);
            } elseif ( preg_match("@^pg:(.*)@i", $itm, $matches) ) {
                $itm = $this->pg($matches[1]);
            }
        }
    }
    private function gl($key)
    {
        return config("app.{$key}");
    }
    private function pg($key)
    {
        return $this->{$key};
    }
}
