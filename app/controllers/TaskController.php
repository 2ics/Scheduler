<?php

class TaskController extends \BaseController {

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
		        	$new_event['project_id'] = $event->project_id;
		        	$new_event['start'] = $event->start_date;
		        	$new_event['end'] = $event->end_date;
		        	$new_event['title'] = $event->project()->first()->description;
		        	$new_event['description'] = $event->project()->first()->docket."<br />".$event->project()->first()->customer()->first()->name."<br />".strtoupper($event->status);
		        	$new_event['locked'] = true;
		        	$new_event['hasnote'] = (strlen($event->notes) > 0) ? true : false;
		        	$new_event['userId'] = $this->getEquipmentOrderId($event->equipment_id);
		        	$new_event['colour'] = User::find($event->project()->first()->user_id)->colour;
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
	public function individual($id)
	{
		return View::make('task')->with('task', Task::find($id))->with('project', Project::find(Task::find($id)->project_id));
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function allTasks($process_id)
	{
		$process = Process::find($process_id);
		return View::make('alltasks')->with('process_id', $process->id)->with('processes', Process::all())->with('projects', Project::where('sent_to_schedule', '=', true)->get());
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
        	$event['description'] = User::find($event->user_id)->colour;
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
	public function reschedule($id)
	{
		$task = Task::find($id);
		$task->start_date = null;
		$task->end_date = null;
		$task->save();
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
