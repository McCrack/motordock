<?php

namespace App\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Validator;

use App\User;

class index extends Controller
{
	public function index($id)
	{
		$users = User::orderBy('group')->get();

		$team = [];
		$group = null;
		foreach ($users as $user) {
			if ($group != $user->group) {
				$group = $user->group;
				$team[$group] = [];
			}
			$team[$group][] = $user;
		}

		return view("layouts.users", [
			'team'		=> $team,
			'user'		=> $id ? $users->find($id) : null
		]);
	}
	public function save(Request $request)
	{
		$fields = [
			'name'	=> $request->name,
			'email'	=> $request->email,
			'group'	=> $request->group,
			'token' => $request->token
		];
		if (isset($request->password)) {
			$fields['password'] = bcrypt($request->password);
		}

		if (empty($request->id)) {
			return User::create($fields)->id;
		} else {
			return User::where('id', $request->id)->update($fields);
		}
	}
	public function delete(Request $request, $cng)
	{
		return User::destroy(ARG_0);
	}
}
