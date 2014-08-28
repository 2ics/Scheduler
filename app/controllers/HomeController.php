<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showWelcome()
	{
		return View::make('hello');
	}

	public function planner()
	{
		return View::make('planner')->with(array('customers' => Customer::all()))->with(array('users' => User::all()));
	}

	public function scheduler()
	{		
		Assets::add('scheduler');
		return View::make('scheduler')->with(array('processes' => Process::all()));
	}

	public function scheduleProcess($process_name)
	{		
		Assets::add('scheduler'); 

		$process = Process::where('name', '=', $process_name)->first();
		return View::make('process_schedule')->with('process_id', $process->id)->with('processes', Process::orderBy('order', 'ASC')->get())->with('projects', Project::where('sent_to_schedule', '=', true)->get());
	}

}