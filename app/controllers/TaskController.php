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
				}else if ($index == "customer_id"){
					$allTasks[$counter]['customer'] = "<div class='customer'>".Customer::find($attribute)->name."</div>";
				}else if ($index == "equipment_id"){
					$allTasks[$counter]['equipment'] = "<div class='equipment'>".ProcessEquipment::find($attribute)->name."</div>";
					$allTasks[$counter]['process'] = "<div class='process'>".Process::find(ProcessEquipment::find($attribute)->process_id)->name."</div>";
				}else if ($index == "user_id"){
					$allTasks[$counter]['user'] = "<div class='user'>".User::find($attribute)->first_name." " . User::find($attribute)->last_name ."</div>";
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
	public function processByDate($process_id)
	{
        if (Request::ajax()) {
            $data = Input::all();

			$tasks['options']['timeslotsPerHour'] = 1;
			$tasks['options']['timeslotHeight'] = 60;
			$tasks['options']['defaultFreeBusy']['free'] = true;
			$tasks['events'] = array();
	        $events = Task::where('start_date', '>=', $data['start'])->where('end_date', '<=', $data['end'])->get();

	        foreach ($events as $event){
	        	if (ProcessEquipment::find($event->equipment_id)->process_id == $process_id){
		        	$new_event = array();
		        	$new_event['id'] = $event->id;
		        	$new_event['start'] = $event->start_date;
		        	$new_event['end'] = $event->end_date;
		        	$new_event['title'] = $event->description;
		        	$new_event['userId'] = $this->getEquipmentOrderId($event->equipment_id);
		        	$new_event['colour'] = User::find($event->user_id)->colour;
		        	$tasks['events'][] = $new_event;
		        }
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
	public function allProcessesSelect()
	{
        $processes = Process::all();

        $all_processes = array();
        foreach ($processes as $process){
            $temp_process['value'] = $process->id;
            $temp_process['text'] = $process->name;
            $all_processes[] = $temp_process;
        }

        return $all_processes;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function allEquipmentByProcessSelect($process_id)
	{
        $equipments = ProcessEquipment::where('process_id', '=', $process_id)->get();

        $all_equipments = array();
        foreach ($equipments as $equipment){
            $temp_equipment['value'] = $equipment->id;
            $temp_equipment['text'] = $equipment->name;
            $all_equipments[] = $temp_equipment;
        }

        return $all_equipments;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function allEquipmentProcessSelect()
	{
        $equipments = ProcessEquipment::all();

        $all_equipments = array();
        foreach ($equipments as $equipment){
            $temp_equipment['value'] = $equipment->id;
            $temp_equipment['text'] = $equipment->name;
            $all_equipments[$equipment->process_id][] = $temp_equipment;
        }

        return $all_equipments;
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
        	$equipment = ProcessEquipment::find($event->equipment_id);
        	$process = Process::find($equipment->process_id);
        	$event['userId'] = $this->getEquipmentOrderId($event->equipment_id);
        	$event['colour'] = User::find($event->user_id)->colour;
        	$event['customer'] = Customer::find($event->customer_id)->name;
        	$all_events[$process->name][$equipment->name][] = $event['attributes'];
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
