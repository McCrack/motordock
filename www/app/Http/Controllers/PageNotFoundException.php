<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\SiteMapModel        AS SMModel;
use App\LandingPageModel    AS LPModel;

class PageNotFoundException extends Controller
{
    public $map = null;
    public $policy = null;
    public $catTree = null;

    public function __construct()
    {
        $this->map = SMModel::getTree();
        $this->policy = LPModel::select('content')->where('slug','kurz-datenschutzerklrung')->first();
    }
    public function index($page)
    {
        dd("say hello");

        if (empty($page->module)) {
            return $this->typical($page);
        } else {
            return $this->{$page->module}($page);
        }
    }

    private function view($blade, $page, $variables)
    {
        $variables['page'] = $page;
        $variables['map'] = $this->map;
        $variables['policy'] = $this->policy;
        $variables['description'] = implode(' ✓ ', [
            $page->title,
            $page->description
        ]);

        return view($blade, $variables);
    }

    public function typical($page)
    {
        return $this->view("layouts.typical", $page, [
            'breadcrumbs'   => $page->breadcrumbs
        ]);
    }
}
