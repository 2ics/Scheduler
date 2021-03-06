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
    protected $access = array(
        'create'    	 => array('Super Admin', 'Admin'),
        'edit'   		 => array('Super Admin', 'Admin'),
        'editor'         => null,
        'scheduler'		 => null,
        'getAll'		 => null,
        'getEquipment'   => null,
        'save'			 => array('Super Admin', 'Admin'),
        'delete'		 => array('Super Admin', 'Admin'),
        'schedule' 		 => array('Super Admin', 'Admin')
    );
    /**
     * Constructor
     */
    public function __construct()
    {
        // Establish Filters
        $this->beforeFilter('auth');
        parent::checkPermissions($this->access);
    }

	public function create()
	{
		$date = date("d-m-Y", time());
		$allUsers = array();

		foreach (User::all() as $user){
			$groups = $user->groups()->where('id', '=', '1')->get();
			if (count($groups) == 0){
				$allUsers[] = $user;
			}
		}
		return View::make('project.create')->with('processes', Process::orderBy('order', 'asc')->get())->with('customers', Customer::all())->with('users', $allUsers)->with('date', $date);
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

		$process = Process::orderBy('order', 'asc')->first();
		Session::reflash();
		return Redirect::action('HomeController@scheduleProcess', $process->name);
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
        	if ($project->sent_to_schedule == true){
        		$schedule = "<img src='".asset('/img/ON_schedule.png')."' />";
        	}else{
        		if (Sentry::check() && (Sentry::getUser()->hasAccess('Super Admin') || Sentry::getUser()->hasAccess('Admin'))){
        			$schedule = '<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#scheduleModal" data-project="'.$project->id.'"><img src="'.asset('/img/OFF_schedule.png').'" /></button>';
        		}else{
        			$schedule = '<img src="'.asset('/img/OFF_schedule.png').'" />';
        		}
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
				'scheduled'		=> $schedule,
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
		foreach (Process::orderBy('order', 'asc')->get() as $process){
			foreach($process->equipment()->orderBy('order', 'asc')->get() as $equipment){
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
				if ($data['add_to_schedule'] == "true"){
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
           				if ($field == "equipment_id" && $newTask->equipment_id != $value){
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