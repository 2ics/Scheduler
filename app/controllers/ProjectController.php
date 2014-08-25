<?php
use Carbon\Carbon;

class ProjectController extends BaseController {

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

	public function create()
	{
		$date = date("d-m-Y", time());
		return View::make('project.create')->with('processes', Process::all())->with('customers', Customer::all())->with('users', User::all())->with('date', $date);
	}

	public function edit($id)
	{
		$date = date("d-m-Y", time());
		return View::make('project.edit')->with('processes', Process::all())->with('customers', Customer::all())->with('users', User::all())->with('date', $date);
	}

	public function editor()
	{
		return View::make('project.editor');
	}

	public function getAll(){
		$projects = Project::all();
		$allProjects = array();
		foreach ($projects as $project){
			$customer = Customer::find($project->customer_id);
			$user = User::find($project->user_id);
        	$completiontime = strtotime($project->due_date) - strtotime($project->created_at);
        	$now = time();
        	$overdue = $now - strtotime($project->due_date);
			$allProjects[] = array(
				'description' => $project->description,
				'docket'		=> $project->docket,
				'sheets'		=> $project->sheets,
				'stock'			=> $project->stock,
				'customer'		=> $customer->name,
				'rep'			=> $user->first_name. " " .$user->last_name,
				'input_date'	=> $project->created_at->format('d-m-Y'),
				'due_date'		=> $project->due_date,
				'completion_time' => floor($completiontime/(60*60*24))+1 ." days",
				'overdue'		=> floor($overdue/(60*60*24)) > 1 ? "YES" : "NO",
				'scheduled'		=> 'NO',
				'total_tasks'	=> count($project->tasks()->get()),
				'status'		=> count($project->tasks()->where('status', '<>', 'complete')) > 0 ? "In Progress" : "Completed",
				'modify'		=> '<a href="'.action("ProjectController@edit", array($project->id)).'"><button type="button" class="btn btn-primary btn-sm" style="margin-right:5px;"><span class="glyphicon glyphicon-pencil"></span></button></a><button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myModal"><span class="glyphicon glyphicon-trash"></span></button>'
			);
		}

		return Response::json(array('data' => $allProjects));
	}

	public function getEquipment()
	{
		$equipmentArray = array();
		foreach (Process::all() as $process){
			foreach($process->equipment()->get() as $equipment){
				$equipmentArray[$process->id][] = $equipment['attributes'];
			}
		}
		return $equipmentArray;
	}

	public function save()
	{
		$data = Input::all();
		print_r($data);
		$project = new Project;

		if (isset($data['project'])){
			foreach($data['project'] as $field => $value){
				$project[$field] = $value;
			}
		}

		$project->save();

		if (isset($data['tasks'])){
			foreach($data['tasks'] as $index => $task){
				$newTask = new Task;
				$newTask->project_id = $project->id;
				foreach($task as $field => $value){
					$newTask[$field] = $value;
				}
				$newTask->save();
			}
		}
	}

}