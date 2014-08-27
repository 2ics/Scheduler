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
		return View::make('project.edit')->with('project', Project::find($id))->with('processes', Process::all())->with('customers', Customer::all())->with('users', User::all())->with('date', $date);
	}

	public function editor()
	{
		return View::make('project.editor');
	}

	public function scheduler()
	{
		Assets::add('scheduler'); 

		$process = Process::first();
		return View::make('process_schedule')->with('process_id', $process->id)->with('processes', Process::all())->with('projects', Project::where('sent_to_schedule', '=', true)->get());
	}

	public function getAll(){
		$projects = Project::all();
		$allProjects = array();
		foreach ($projects as $project){
			$customer = Customer::find($project->customer_id);
			$user = User::find($project->user_id);
        	$completiontime = strtotime($project->due_date) - strtotime($project->created_at);
        	$now = time();
        	$overdue = strtotime($project->due_date) - $now;
        	$completed = count($project->tasks()->where('status', '<>', 'complete')->get()) > 0 ? false : true;
        	if ($completed){
        		$overdue = 0;
        	}else{
        		$overdue = (floor($overdue/(60*60*24)) < 0) ? "<span style='color: #FF0000; font-weight:bold;'>".floor($overdue/(60*60*24))."</span>" : floor($overdue/(60*60*24));
        	}
			$allProjects[] = array(
				'description' => $project->description,
				'docket'		=> $project->docket,
				'sheets'		=> $project->sheets,
				'stock'			=> $project->stock,
				'customer'		=> $customer->name,
				'notes'			=> $project->notes,
				'rep'			=> $user->first_name. " " .$user->last_name,
				'input_date'	=> $project->created_at->format('d-m-Y'),
				'due_date'		=> $project->due_date,
				'completion_time' => floor($completiontime/(60*60*24))+1 ." days",
				'overdue'		=> $overdue,
				'scheduled'		=> ($project->sent_to_schedule) ? "<img src='".asset('/img/ON_schedule.png')."' />" : '<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#scheduleModal"data-project="'.$project->id.'"> <img src="'.asset('/img/OFF_schedule.png').'" /></button>',
				'total_tasks'	=> count($project->tasks()->get()),
				'status'		=> count($project->tasks()->where('status', '<>', 'complete')->get()) > 0 ? "In Progress" : "Completed",
				'modify'		=> '<a href="'.action("ProjectController@edit", array($project->id)).'"><button type="button" class="btn btn-primary btn-sm" style="margin-right:5px;"><span class="glyphicon glyphicon-pencil"></span></button></a><button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-project="'.$project->id.'" data-target="#myModal"><span class="glyphicon glyphicon-trash"></span></button>'
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

		if (isset($data['project'])){
			if (isset($data['project_id'])){
				$project = Project::find($data['project_id']);
			}else{
				$project = new Project;
			}
			if (isset($data['add_to_schedule'])){
				if ($data['add_to_schedule']){
					$project->sent_to_schedule = true;
				}else{
					$project->sent_to_schedule = false;
				}
			}
			foreach($data['project'] as $field => $value){
           		if(Schema::hasColumn('projects', $field)){
					$project[$field] = $value;
				}
			}
			$project->save();
		}

		if (isset($data['tasks'])){
			foreach($data['tasks'] as $index => $task){
				if (isset($task['id'])){
					$newTask = Task::find($task['id']);
				}else{
					$newTask = new Task;
				}
				$newTask->project_id = $project->id;
				foreach($task as $field => $value){
           			if(Schema::hasColumn('tasks', $field)){
           				if ($field == "duration" && $newTask->duration != $value){
           					$newTask->start_date = null;
           					$newTask->end_date = null;
           				}
						$newTask[$field] = $value;
					}
				}
				$newTask->save();
			}
		}
	}

	public function delete($id)
	{
		$project = Project::find($id);

		$project->tasks()->delete();

		$project->delete();
	}

	public function schedule($id)
	{
		$project = Project::find($id);
		$project->sent_to_schedule = true;
		$project->save();
	}

}