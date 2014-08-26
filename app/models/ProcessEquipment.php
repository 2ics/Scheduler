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
    	return Task::where('equipment_id', '=', $this->id)->whereNull('start_date')->whereNull('end_date')->get();
    }
}