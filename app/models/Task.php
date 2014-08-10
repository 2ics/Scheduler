<?php

class Task extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'tasks';
	
	protected $softDelete = true;
	
    public function equipment()
    {
        return $this->hasOne('ProcessEquipment');
    }
    
    public function customer()
    {
        return $this->hasOne('Customer');
    }
}