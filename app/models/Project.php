<?php

class Project extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'projects';
    
	protected $softDelete = true;
	
    public function tasks()
    {
        return $this->hasMany('Task');
    }
}