<?php

class Task extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'tasks';
    
    public static $unguarded = true;
	
	protected $softDelete = true;
	
    public function project()
    {
        return $this->belongsTo('Project');
    }

    public function getCalendarUserId()
    {
        $all_equipment = Process::find(ProcessEquipment::where('id', '=', $this->equipment_id)->first()->process_id)->equipment()->get();

        foreach ($all_equipment as $index => $equipment) {
            if ($equipment->id == $this->equipment_id){
                return $index;
            }
        }
    }
}