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
				if ($index == "due_date"){
					$allTasks[$counter][$index] = "<div class='".$index."'>".date('d/m/Y', intval($attribute))."</div>";
				}else{
					$allTasks[$counter][$index] = "<div class='".$index."'>".$attribute."</div>";
				}
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
        if (Request::ajax()) {
            $data = Input::all();

            $task = Task::find($data['id']);

            $task[$data['field']] = $data['value'];

            $task->save();
        }
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function byDate()
	{
        if (Request::ajax()) {
            $data = Input::all();

			$tasks['options']['timeslotsPerHour'] = 1;
			$tasks['options']['timeslotHeight'] = 60;
			$tasks['options']['defaultFreeBusy']['free'] = true;
			$tasks['events'] = array();
	        $events = Task::where('start_date', '>=', $data['start'])->where('end_date', '<=', $data['end'])->get();

	        foreach ($events as $event){
	        	$new_event = array();
	        	$new_event['id'] = $event->id;
	        	$new_event['start'] = $event->start_date;
	        	$new_event['end'] = $event->end_date;
	        	$new_event['title'] = $event->description;
	        	$new_event['userId'] = $this->getEquipmentOrderId($event->equipment_id);
	        	$tasks['events'][] = $new_event;
	        }

            return $tasks;
        }
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function allProcesses()
	{
        return json_decode(Process::all()->toJson());
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getUnscheduledTasks()
	{
		$all_events = array();
        $events = Task::whereNull('start_date')->whereNull('end_date')->get();

        foreach ($events as $event){
        	$event['userId'] = $this->getEquipmentOrderId($event->equipment_id);
        	$all_events[] = $event['attributes'];
        }

        return $all_events;
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function saveEvent()
	{
        if (Request::ajax()) {
            $data = Input::all();

            $task = Task::find($data['id']);

            if (count($task) > 0){
            	$task->start_date = $data['start'];
            	$task->end_date = $data['end'];
            }
            Log::info($task);
            $task->save();
        }
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getProcessEquipment($id)
	{
		$data = array();
		$all_equipment = Process::find($id)->equipment()->get();

		foreach ($all_equipment as $index => $equipment) {
			$data[] = $equipment->name;
		}

        return $data;
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getEquipmentOrderId($id)
	{
		$all_equipment = Process::find(ProcessEquipment::where('id', '=', $id)->first()->process_id)->equipment()->get();

		foreach ($all_equipment as $index => $equipment) {
			if ($equipment->id == $id){
				return $index;
			}
		}
	}

}
