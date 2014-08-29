<?php

class ProcessEquipment extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'equipment';
	
	protected $softDelete = true;

    public function process()
    {
        return $this->hasOne('Process');
    }

    public function tasks()
    {
    	return Task::where('equipment_id', '=', $this->id)->get();
    }

    public function unscheduledTasks()
    {
    	$allTasks = array();
    	foreach (Task::where('equipment_id', '=', $this->id)->whereNull('start_date')->whereNull('end_date')->get() as $task){
    		if ($task->project()->first()->sent_to_schedule){
    			$allTasks[] = $task;
    		}
    	}

    	return $allTasks;
    }
    public function getAllScheduledNonComplete()
    {
        $tasks = Task::where('equipment_id', '=', $this->id)->whereNotNull('start_date')->whereNotNull('end_date')->where('status', '<>', 'complete')->orderBy('end_date', 'asc')->get();

        return $tasks;
    }
}