<?php

class TaskController extends \BaseController {
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getTasks()
	{
		$tasks = Task::all();
		$allTasks = array();
			$counter = 0;
		foreach ($tasks as $task){
			foreach ($task['attributes'] as $index => $attribute){
				$allTasks[$counter][$index] = "<div class='".$index."'>".$attribute."</div>";
			}
			$counter++;
		}

		return Response::json(array('data' => $allTasks));
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function addTask()
	{
        if (Request::ajax()) {
            $data = Input::all();

           	$task = new Task;

            foreach ($data as $index => $var){
           		if(Schema::hasColumn('tasks', $index)){
           			$task[$index] = $var;
           		}
            }

           	$task->save();
        }
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function editColumn()
	{
        // if (Request::ajax()) {
            $data = Input::all();

           	Log::info($data);
        // }
	}

}
