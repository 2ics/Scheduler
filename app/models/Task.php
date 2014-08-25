<?php

class Task extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'tasks';
    
    public static $unguarded = true;
	
	protected $softDelete = true;
	
    public function customer()
    {
        return $this->belongsTo('Project');
    }
}