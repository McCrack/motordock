<?php

namespace App\Exceptions;

use Exception;
use App\LandingPageModel	AS LPModel;

class Custom extends Exception
{

	/**
	* Report the exception.
	*
	* @return void
	*/
	public function report()
	{}

	/**
	* Render the exception into an HTTP response.
	*
	* @param  \Illuminate\Http\Request
	* @return \Illuminate\Http\Response
	*/

	public function render($request)
	{
		$page = LPModel::whereSlug("404")->first();
		return response()->view('errors.404', [
			'page'			=> $page,
			'meta'          => ['description' => ""],
            'breadcrumbs'	=> $page->breadcrumbs
		], 404);
	}

}